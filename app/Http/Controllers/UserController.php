<?php

namespace App\Http\Controllers;

use App\Models\igrate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'phone' => 'required',
            'name' => 'required',

        ]);

        $arrayData = [
            'name' => $request->name,
            'password' => hash('sha256', $request->password),
            'phone' => $request->phone,

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
