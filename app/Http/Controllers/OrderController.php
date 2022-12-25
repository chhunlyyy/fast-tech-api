<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\igrate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Arr;

class OrderController extends Controller
{

    public function getOrderStatistic(Request $request)
    {
        $request->validate([

            'user_id' => 'required',
        ]);


        if ($this->isAdminUser($request->user_id)) {
            $delivery = DB::table('orders')->where('delivery_type', '=', 1)->where('status', '=', 3)->count();
            $pickup = DB::table('orders')->where('delivery_type', '=', 0)->where('status', '=', 3)->count();
            $package = DB::table('package')->where('status', '=', 3)->count();
            return response()->json(
                [
                    'deliveryCount' => $delivery,
                    'pickupCount' => $pickup,
                    'packageCount' => $package,
                ]
            );
        } else {
            $delivery = DB::table('orders')->where('user_id', '=', $request->user_id)->where('delivery_type', '=', 1)->where('status', '=', 3)->count();
            $pickup = DB::table('orders')->where('user_id', '=', $request->user_id)->where('delivery_type', '=', 0)->where('status', '=', 3)->count();
            $package = DB::table('package')->where('user_id', '=', $request->user_id)->where('status', '=', 3)->count();
            return response()->json(
                [
                    'deliveryCount' => $delivery,
                    'pickupCount' => $pickup,
                    'packageCount' => $package,
                ]
            );
        }
    }


    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'status' => 'required',
            'is_package' => 'required',

        ]);

        try {
            if ($request->is_package) {
                DB::table('package')
                    ->where('id', '=', $request->order_id)
                    ->update(array('status' => $request->status));
            } else {
                DB::table('orders')
                    ->where('id', '=', $request->order_id)
                    ->update(array('status' => $request->status));
            }


            return response()->json(
                [
                    'status' => '200',
                ]
            );
        } catch (Exception $e) {
            return response()->json(
                [
                    'messsage' => $e->getMessage(),
                    'status' => '402',
                ]
            );
        }
    }

    public function getOrderReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required',
            'to_date' => 'required',
        ]);

        $startDate = $request->start_date;
        $toDate = $request->to_date;
        $products =    DB::table('orders')->select('*')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $toDate)
            ->where('status', '=', '3')
            ->get();

        for ($i = 0; $i < count($products); $i++) {

            $products[$i]->user_name =  DB::table('user')->select('name')->first()->name;
            $products[$i]->phone =  DB::table('user')->select('phone')->first()->phone;
            $products[$i]->phone =  DB::table('user')->select('phone')->first()->phone;
            $products[$i]->product_name =  DB::table('product')->select('name')->first()->name;
            $products[$i]->final_price =  DB::table('product')->select('price_after_discount')->first()->price_after_discount;
            $products[$i]->discount =  DB::table('product')->select('discount')->first()->discount;
            unset($products[$i]->user_id);
            unset($products[$i]->id);
            unset($products[$i]->product_id);
            unset($products[$i]->color_id);
            unset($products[$i]->delivery_type);
            unset($products[$i]->status);
            unset($products[$i]->address_id_ref);
        }

        return  $products;
    }


    public function getPackageOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'is_done' => 'required',
        ]);

        $pageSize = $request->query('pageSize', 10);
        $pageIndex = $request->query('pageIndex', 0);
        if ($pageIndex != 0) {
            $pageIndex = $pageSize * $pageIndex;
        }


        if ($this->isAdminUser($request->user_id)) {
            if ($request->is_done == 1) {
                $products = DB::table('package')
                    ->where('status', '=', 3)
                    ->offset($pageIndex)
                    ->limit($pageSize)
                    ->orderBy('id', 'DESC')
                    ->select('*')->get();
            } else {
                $products = DB::table('package')
                    ->where('status', '!=', 3)
                    ->select('*')->get();
            }
        } else {
            if ($request->is_done == 1) {
                $products = DB::table('package')
                    ->where('status', '=', 3)
                    ->where('user_id', '=', $request->user_id)
                    ->offset($pageIndex)
                    ->limit($pageSize)
                    ->orderBy('id', 'DESC')
                    ->select('*')->get();
            } else {
                $products = DB::table('package')
                    ->where('user_id', '=', $request->user_id)
                    ->where('status', '!=', 3)
                    ->select('*')->get();
            }
        }


        for ($i = 0; $i < count($products); $i++) {

            $products[$i]->product = DB::table('product')->select('*')->where('id', '=', $products[$i]->product_id)->get()[0];
             // get camera type
             if ($products[$i]->product->camera_type_id != null) {
                $products[$i]->product->camera_type = DB::table('camera_type')->select('type')->where('id','=',$products[$i]->product->camera_type_id)->first()->type;
            } else {
                $products[$i]->product->camera_type = null;
            }
        
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            //get location

            $address = DB::table('address')->select('*')->where("id_ref", "=", $products[$i]->address_id_ref)->get();

            $user =  DB::table('user')->select('id', 'name', 'phone')->where("id", "=", $products[$i]->user_id)->get();

            $products[$i]->product->colors = $colors;
            $products[$i]->product->images = $images;
            $products[$i]->product->details = $details;
            $products[$i]->product->address = $address;
            $products[$i]->user = $user[0];
        }

        return $products;
    }


    public function getDeliveryOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'is_done' => 'required',
        ]);
        $pageSize = $request->query('pageSize', 10);
        $pageIndex = $request->query('pageIndex', 0);
        if ($pageIndex != 0) {
            $pageIndex = $pageSize * $pageIndex;
        }

        if ($this->isAdminUser($request->user_id)) {
            if ($request->is_done == 1) {
                $products = DB::table('orders')
                    ->where('delivery_type', '=', 1)
                    ->offset($pageIndex)
                    ->limit($pageSize)
                    ->orderBy('id', 'DESC')
                    ->where('status', '=', 3)
                    ->select('*')->get();
            } else {
                $products = DB::table('orders')
                    ->where('delivery_type', '=', 1)
                    ->where('status', '!=', 3)
                    ->select('*')->get();
            }
        } else {
            if ($request->is_done == 1) {


                $products = DB::table('orders')
                    ->where('user_id', '=', $request->user_id)
                    ->where('status', '=', 3)
                    ->offset($pageIndex)
                    ->limit($pageSize)
                    ->orderBy('id', 'DESC')
                    ->where('delivery_type', '=', 1)
                    ->select('*')->get();
            } else {
                $products = DB::table('orders')
                    ->where('user_id', '=', $request->user_id)
                    ->where('delivery_type', '=', 1)
                    ->where('status', '!=', 3)
                    ->select('*')->get();
            }
        }


        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->product = DB::table('product')->select('*')->where('id', '=', $products[$i]->product_id)->get()[0];
            $products[$i]->qty = $products[$i]->qty;
             // get camera type
             if ($products[$i]->product->camera_type_id != null) {

                $products[$i]->product->camera_type = DB::table('camera_type')->select('type')->where('id','=',$products[$i]->product->camera_type_id)->first()->type;
            } else {
                $products[$i]->product->camera_type = null;
            }
           
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            //get location
            $address = DB::table('address')->select('*')->where("id_ref", "=", $products[$i]->address_id_ref)->get();

            $user =  DB::table('user')->select('id', 'name', 'phone')->where("id", "=", $products[$i]->user_id)->get();

            $products[$i]->product->colors = $colors;
            $products[$i]->product->images = $images;
            $products[$i]->product->details = $details;
            $products[$i]->product->address = $address;
            $products[$i]->user =  $user[0];
        }

        return $products;
    }


    public function getPickupOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'is_done' => 'required',
        ]);
        $pageSize = $request->query('pageSize', 10);
        $pageIndex = $request->query('pageIndex', 0);
        if ($pageIndex != 0) {
            $pageIndex = $pageSize * $pageIndex;
        }


        if ($this->isAdminUser($request->user_id)) {
            if ($request->is_done == 1) {
                $products = DB::table('orders')
                    ->where('delivery_type', '=', 0)
                    ->where('status', '=', 3)
                    ->offset($pageIndex)
                    ->limit($pageSize)
                    ->orderBy('id', 'DESC')
                    ->select('*')->get();
            } else {
                $products = DB::table('orders')
                    ->where('delivery_type', '=', 0)
                    ->where('status', '!=', 3)
                    ->select('*')->get();
            }
        } else {
            if ($request->is_done == 1) {
                $products = DB::table('orders')
                    ->where('user_id', '=', $request->user_id)
                    ->where('delivery_type', '=', 0)
                    ->where('status', '=', 3)
                    ->offset($pageIndex)
                    ->limit($pageSize)
                    ->orderBy('id', 'DESC')
                    ->select('*')->get();
            } else {
                $products = DB::table('orders')
                    ->where('user_id', '=', $request->user_id)
                    ->where('delivery_type', '=', 0)
                    ->where('status', '!=', 3)
                    ->select('*')->get();
            }
        }


        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->product = DB::table('product')->select('*')->where('id', '=', $products[$i]->product_id)->get()[0];
            $products[$i]->qty = $products[$i]->qty;
            // get camera type
            if ($products[$i]->product->camera_type_id != null) {
                $products[$i]->product->camera_type = DB::table('camera_type')->select('type')->where('id','=',$products[$i]->product->camera_type_id)->first()->type;
            } else {
                $products[$i]->product->camera_type = null;
            }
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->product->id_ref)->get();

            $user =  DB::table('user')->select('id', 'name', 'phone')->where("id", "=", $products[$i]->user_id)->get();


            $products[$i]->product->colors = $colors;
            $products[$i]->product->images = $images;
            $products[$i]->product->details = $details;
            $products[$i]->user = $user[0];
        }

        return $products;
    }

    private function isAdminUser($userId)
    {
        $phone =  DB::table('user')->select('phone')->where('id', '=', $userId)->get();


        if (count($phone) == 0) {
            return 0;
        }

        $result = DB::table('admin_user')->select('id')->where('phone', '=', $phone[0]->phone)->get();

        return count($result) > 0 ? '1' : '0';
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

        $products = DB::table('cart')
            ->where('user_id', '=', $request->user_id)
            ->select('*')->get();


        for ($i = 0; $i < count($products); $i++) {
            $products[$i]->product = DB::table('product')->select('*')->where('id', '=', $products[$i]->product_id)->get()[0];
            $products[$i]->qty = $products[$i]->qty;
             // get camera type
             if ($products[$i]->product->camera_type_id != null) {
                $products[$i]->product->camera_type = DB::table('camera_type')->select('type')->where('id','=',$products[$i]->product->camera_type_id)->first()->type;
            } else {
                $products[$i]->product->camera_type = null;
            }
           
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
