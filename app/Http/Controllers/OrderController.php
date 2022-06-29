<?php

namespace App\Http\Controllers;

use App\Models\igrate;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{


    public function getPackageOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
        ]);


        $products = DB::table('package')
            ->where('user_id', '=', $request->user_id)
            ->select('*')->get();

        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->product = DB::table('product')->select('*')->where('id', '=', $products[$i]->product_id)->get()[0];

            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            //get location

            $address = DB::table('address')->select('*')->where("id_ref", "=", $products[$i]->address_id_ref)->get();

            $products[$i]->product->colors = $colors;
            $products[$i]->product->images = $images;
            $products[$i]->product->details = $details;
            $products[$i]->product->address = $address;
        }

        return $products;
    }


    public function getDeliveryOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
        ]);


        $products = DB::table('orders')
            ->where('user_id', '=', $request->user_id)
            ->where('delivery_type', '=', 1)
            ->select('*')->get();

        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->product = DB::table('product')->select('*')->where('id', '=', $products[$i]->product_id)->get()[0];
            $products[$i]->qty = $products[$i]->qty;
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            //get location

            $address = DB::table('address')->select('*')->where("id_ref", "=", $products[$i]->address_id_ref)->get();

            $products[$i]->product->colors = $colors;
            $products[$i]->product->images = $images;
            $products[$i]->product->details = $details;
            $products[$i]->product->address = $address;
        }

        return $products;
    }


    public function getPickupOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
        ]);


        $products = DB::table('orders')
            ->where('user_id', '=', $request->user_id)
            ->where('delivery_type', '=', 0)
            ->select('*')->get();

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



    public function packageOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
            'address_id_ref' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'status' => 'required',
        ]);


        try {


            $orderPackage = array(
                'user_id' => $request->user_id,
                'product_id' => $request->product_id,
                'address_id_ref' => $request->address_id_ref,
                'status' => $request->status
            );

            $address = array(
                'id_ref' => $request->address_id_ref,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            );



            DB::table('address')->insert($address);
            DB::table('package')->insert($orderPackage);

            return response()->json([
                [
                    'message' => 'added to package order successfully',
                    'status' => '200',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                [
                    'messsage' => $e->getMessage(),
                    'status' => '402',
                ]
            ]);
        }
    }


    public function deliveryOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
            'color_id' => 'required',
            'qty' => 'required',
            'delivery_type' => 'required',
            'status' => 'required',
            'address_id_ref' => 'required',
            "latitude" => 'required',
            "longitude" => 'required',
            'is_buy_from_cart' => 'required',
        ]);

        try {

            if ($request->is_buy_from_cart == 0) {
                $product = DB::table('cart')
                    ->select('*')
                    ->where('user_id', '=', $request->user_id)
                    ->where('product_id', '=', $request->product_id)
                    ->where('color_id', '=', $request->color_id)
                    ->get();
                DB::table('cart')->delete($product[0]->id);
            }

            $location = array("id_ref" => $request->address_id_ref, "latitude" => $request->latitude, "longitude" => $request->longitude);

            $request = $request->except(['is_buy_from_cart', 'latitude', 'longitude']);

            DB::table('orders')->insert($request);
            DB::table('address')->insert($location);

            return response()->json([
                [
                    'message' => 'added to order successfully',
                    'status' => '200',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                [
                    'messsage' => $e->getMessage(),
                    'status' => '402',
                ]
            ]);
        }
    }


    public function order(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'product_id' => 'required',
            'color_id' => 'required',
            'qty' => 'required',
            'delivery_type' => 'required',
            'status' => 'required',
            'address_id_ref' => 'required',
            'is_buy_from_cart' => 'required',
        ]);

        try {

            if ($request->is_buy_from_cart == 0) {
                $product = DB::table('cart')
                    ->select('*')
                    ->where('user_id', '=', $request->user_id)
                    ->where('product_id', '=', $request->product_id)
                    ->where('color_id', '=', $request->color_id)
                    ->get();
                DB::table('cart')->delete($product[0]->id);
            }

            $request = $request->except('is_buy_from_cart');
            DB::table('orders')->insert($request);

            return response()->json([
                [
                    'message' => 'added to order successfully',
                    'status' => '200',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                [
                    'messsage' => $e->getMessage(),
                    'status' => '402',
                ]
            ]);
        }
    }


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
