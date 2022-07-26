<?php

namespace App\Http\Controllers;

use App\Models\igrate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\CommonMark\Extension\Table\Table;

class UserController extends Controller
{



    public function deleteRole(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        try {
            DB::table('admin_user')->where('phone', '=', $request->phone)->delete();

            return response()->json(
                [
                    'status' => '200',
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => '422',
                ]
            );
        }
    }
    public function addRole(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'role' => 'required',
        ]);

        try {

            $checkUser = DB::table('user')->select('id')->where('phone', '=', $request->phone)->get();

            if (count($checkUser) == 0) {
                return response()->json(
                    [
                        'status' => '400',
                    ]
                );
            }

            DB::table('admin_user')->insert($request->toArray());

            return response()->json(
                [
                    'status' => '200',
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => '422',
                ]
            );
        }
    }


    public function getAdminUser()
    {
        $phone = DB::table('admin_user')->select('*')->get();

        for ($i = 0; $i < count($phone); $i++) {
            $phone[$i] = DB::table('user')
                ->select('id', 'name', 'phone')
                ->where('phone', '=', $phone[$i]->phone)
                ->get()[0];
        }

        return  $phone;
    }


    public function logOut(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'token' => 'required',
        ]);

        return  DB::table('user')
            ->where('phone', '=', $request->phone)
            ->where('token', '=', $request->token)
            ->update(array('token' => null));
    }


    public function checkAdmin(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $result = DB::table('admin_user')
            ->select('*')
            ->where('phone', '=', $request->phone)
            ->get();


        if (count($result) == 0) {
            $status = '400';
        } else {
            if ($result[0]->role == 0) {
                $status = '200';
            } else {
                $status = '201';
            }
        }
        return response()->json(
            [
                'status' => $status,
            ]
        );;
    }


    public function getUser(Request $request)
    {
        $request->validate([
            'token' => 'required',
        ]);
        return DB::table('user')->select('id', 'name', 'phone')->where("token", '=', $request->token)->get();
    }


    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'phone' => 'required',
            'token' => 'required'
        ]);

        $password = hash('sha256', $request->password);

        $checkUser =  DB::table('user')
            ->select('id', 'name', 'phone')
            ->where("phone", '=', $request->phone)
            ->where("password", '=', $password)
            ->get();


        if (count($checkUser) == 0) {

            return response()->json([
                [
                    'message' => 'លេខទូរស័ព្ទ និងពាក្យសម្ងាត់មិនត្រឹមត្រូវ',
                    'status' => '422',
                ]
            ]);
        } else {

            DB::table('user')->where('id', '=', $checkUser[0]->id)->update(array('token' => $request->token));

            //

            return response()->json([
                [
                    'user' => $checkUser[0],
                    'status' => '200',
                ]
            ]);
        }
    }


    public function register(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'phone' => 'required',
            'name' => 'required',
            'token' => 'required',
        ]);

        $arrayData = [
            'name' => $request->name,
            'password' => hash('sha256', $request->password),
            'phone' => $request->phone,
            'token'  => $request->token,
        ];

        $checkUser =   DB::table('user')->select('id')->where('phone', '=', $request->phone)->get();

        if (count($checkUser) != 0) {
            DB::table('user')->where('id', '=', $checkUser[0]->id)->update($arrayData);
            return response()->json([
                [
                    'user' => DB::table('user')->select('id', 'name', 'phone')->where("phone", '=', $request->phone)->get(),
                    'status' => '200',
                ]
            ]);
        } else {
            DB::table('user')->insert($arrayData);

            $checkResutl =  DB::table('user')->select('id', 'name', 'phone')->where("phone", '=', $request->phone)->get();

            if (count($checkResutl) != 0) {
                return response()->json([
                    [
                        'message' => 'គណនីបង្កើតបានដោយជោគជ័យ',
                        'status' => '200',
                        'user' => $checkResutl,
                    ]
                ]);
            }
        }
    }
}
