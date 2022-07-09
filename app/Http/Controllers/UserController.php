<?php

namespace App\Http\Controllers;

use App\Models\igrate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{


    public function checkAdmin(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $result = DB::table('admin_user')
            ->select('id')
            ->where('phone', '=', $request->phone)
            ->get();

        return response()->json(
            [
                'status' => count($result) == 0 ? '400' : '200',
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

            $checkToken = DB::table('user')
                ->select('id')
                ->where('token', '=', $request->token)
                ->where('phone', '=', $request->phone)
                ->get();


            if (count($checkToken) == 0) {
                $arrayData = [
                    'name' => $checkUser[0]->name,
                    'password' => $request->password,
                    'phone' => $request->phone,
                    'token'  => $request->token,
                ];
                DB::table('user')->insert($arrayData);
            }
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
            return response()->json([
                [
                    'message' => 'លេខទូរស័ព្ទ ត្រូវបានប្រើប្រាស់រួចរាល់',
                    'status' => '422',
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
