<?php

namespace App\Http\Controllers;

use App\Models\igrate;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{


    public function removeCart(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        try {
            DB::table('cart')->delete($request->id);
            return response()->json([
                [
                    'messsage' => 'delete cart successfully',
                    'status' => '200',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                [
                    'messsage' => 'delete cart unsuccessfully',
                    'status' => '402',
                ]
            ]);
        }
    }



    public function getCart(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
        ]);

        $products = DB::table('cart')->select('*')->get();


        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->product = DB::table('product')->select('*')->where('id', '=', $products[$i]->product_id)->get()[0];
            $products[$i]->qty = $products[$i]->qty;
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();

            $products[$i]->product->colors = $colors;
            $products[$i]->product->images = $images;
            $products[$i]->product->details = $details;
        }




        return $products;
    }
    public function addToCart(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
            'color_id' => 'required',
            'qty' => 'required',
        ]);

        try {


            $product = DB::table('cart')
                ->select('*')
                ->where('user_id', '=', $request->user_id)
                ->where('product_id', '=', $request->product_id)
                ->where('color_id', '=', $request->color_id)
                ->get();

            if (count($product) == 0) {
                DB::table('cart')->insert($request->all());
            } else {
                DB::table('cart')->where('id', '=', $product[0]->id)->update(array('qty' => $product[0]->qty + $request->qty));
            }



            return response()->json([
                [
                    'message' => 'added to cart successfully',
                    'status' => '200',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                [
                    'messsage' => 'added to cart unsuccessfully',
                    'status' => '402',
                ]
            ]);
        }
    }
}
