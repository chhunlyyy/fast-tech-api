<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{


    public function deleteColor(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            DB::table('color')->delete($request->id);
            return response()->json([

                'status' => '200',

            ]);
        } catch (\Exception $e) {
            return response()->json([

                'message' => $e,
                'status' => '400',

            ]);
        }
    }
    public function deleteDetail(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            DB::table('detail')->delete($request->id);
            return response()->json([

                'status' => '200',

            ]);
        } catch (\Exception $e) {
            return response()->json([

                'message' => $e,
                'status' => '400',

            ]);
        }
    }

    public function deleteImage(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        $imagePath = DB::table('image')->select('image')->where('id', '=', $request->id)->get()[0];

        $path = substr($imagePath->image, 1);

        if (file_exists($path)) {
            unlink($path);
        }

        try {
            DB::table('image')->delete($request->id);

            return response()->json([

                'status' => '200',

            ]);
        } catch (\Exception $e) {
            return response()->json([

                'message' => $e,
                'status' => '400',

            ]);
        }
    }

    public function deleteProduct(Request $request)
    {
        $request->validate([
            'id_ref' => 'required',
        ]);
        $id = $request->id_ref;


        try {
            DB::table('product')->where('id_ref', '=', $id)->delete();
            DB::table('detail')->where('product_id_ref', '=', $id)->delete();
            DB::table('color')->where('product_id_ref', '=', $id)->delete();

            // delete image
            $imagePath = DB::table('image')->select('image')->where('product_id_ref', '=', $id)->get();

            for ($i = 0; $i < count($imagePath); $i++) {
                $path = substr($imagePath[$i]->image, 1);

                if (file_exists($path)) {
                    unlink($path);
                }
            }

            DB::table('image')->where('product_id_ref', '=', $id)->delete();

            //

            return response()->json([

                'message' => 'លុបរួចរាល់',
                'status' => '200',

            ]);
        } catch (\Exception $e) {
            return response()->json([

                'message' => $e,
                'status' => '402',

            ]);
        }
    }

    public function insertDetail(Request $request)
    {
        $request->validate([
            'product_id_ref' => 'required',
            'detail' => 'required',
            'descs' => 'required',
        ]);

        try {
            DB::table('detail')->insert($request->all());
            return response()->json(
                [
                    'status' => '200',
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                [
                    'message' => $e,
                    'status' => '400',
                ]
            ]);
        }
    }

    public function insertProduct(Request $request)
    {
        $request->validate([
            'id_ref' => 'required',
            'name' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'price_after_discount' => 'required',
            'is_warranty' => 'required',
            'warranty_period' => 'required',
            'min_qty' => 'required',
            'is_camera' => 'required',
        ]);

        try {
            DB::table('product')->insert($request->all());
            return response()->json(
                [
                    'status' => '200',
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                [
                    'message' => $e,
                    'status' => '400',
                ]
            ]);
        }
    }
    //
    public function getProductById(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $product = DB::table('product')
            ->select('*')
            ->where('id', '=', $request->id)
            ->get()[0];

        // get colors
        $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $product->id_ref)->get();
        // get images
        $images = DB::table('image')->select('*')->where("product_id_ref", "=", $product->id_ref)->get();
        // get details
        $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $product->id_ref)->get();
        $product->colors = $colors;
        $product->images = $images;
        $product->details = $details;

        return $product;
    }

    //
    public function getAllCamera(Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $pageIndex = $request->query('pageIndex', 0);

        $products = DB::table('product')
            ->select('*')
            ->where('is_camera', '=', 1)
            ->offset($pageIndex)
            ->limit($pageSize)
            ->orderBy('id', 'DESC')
            ->get();

        for ($i = 0; $i < count($products); $i++) {
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();

            $products[$i]->colors = $colors;
            $products[$i]->images = $images;
            $products[$i]->details = $details;
        }

        return $products;
    }

    public function getAllProduct(Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $pageIndex = $request->query('pageIndex', 0);

        $isGetCameraProduct = $request->query('isGetCameraProduct', 0);
        $isGetElectronicProduct = $request->query('isGetElectronicProduct', 0);

        $isMinQtyOne =  $request->query('isMinQtyOne', 1);

        if ($pageIndex != 0) {
            $pageIndex = $pageSize * $pageIndex;
        }

        $operator = $isMinQtyOne == 1 ? '=' : '>';

        if ($isGetCameraProduct == 1) {
            $products = DB::table('product')
                ->select('*')
                ->where('is_camera', '=', 1)
                ->where('min_qty', $operator, 1)
                ->offset($pageIndex)
                ->limit($pageSize)
                ->orderBy('id', 'DESC')
                ->get();
        } else if ($isGetElectronicProduct == 1) {
            $products = DB::table('product')
                ->select('*')
                ->where('is_camera', '=', 0)
                ->where('min_qty', $operator, 1)
                ->offset($pageIndex)
                ->limit($pageSize)
                ->orderBy('id', 'DESC')
                ->get();
        } else {
            $products = DB::table('product')
                ->select('*')
                ->offset($pageIndex)
                ->limit($pageSize)
                ->orderBy('id', 'DESC')
                ->get();
        }



        for ($i = 0; $i < count($products); $i++) {
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();

            $products[$i]->colors = $colors;
            $products[$i]->images = $images;
            $products[$i]->details = $details;
        }

        return $products;
    }

    public function search(Request $request)
    {
        $request->validate([
            'product_name' => 'nullable',
            'start_price'=>'nullable',
            'to_price'=>'nullable',
        ]);
        $pageSize = $request->query('pageSize', 10);
        $pageIndex = $request->query('pageIndex', 0);

        $productName = $request->product_name;

        if ($pageIndex != 0) {
            $pageIndex = $pageSize * $pageIndex;
        }
        // get phone data //

        $startPrice =  $request->start_price ??0;
        $toPrice =  $request->to_price??10000000;

        $qeury = "
            SELECT
               *
            FROM product
            WHERE product.name LIKE '%$productName%' && 
            product.price >= $startPrice &&
            product.price <= $toPrice
            ORDER BY product.id DESC
            LIMIT $pageSize OFFSET $pageIndex";

        $products  = DB::select(DB::raw($qeury));

        for ($i = 0; $i < count($products); $i++) {
            // get colors
            $colors = DB::table('color')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();
            // get images
            $images = DB::table('image')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();
            // get details
            $details = DB::table('detail')->select('*')->where("product_id_ref", "=", $products[$i]->id_ref)->get();

            $products[$i]->colors = $colors;
            $products[$i]->images = $images;
            $products[$i]->details = $details;
        }

        return $products;
    }

    public function addImage(Request $request)
    {
        $request->validate([
            'product_id_ref' => 'required',
            'image' => 'required',
        ]);
        $image = $request->file('image');

        $allowedfileExtension = ['jpg', 'jpeg', 'png',];
        $path = 'public/uploads/image/';

        $filename = $image->getClientOriginalName();


        if (in_array(strtolower($image->getClientOriginalExtension()), $allowedfileExtension)) {
            $image->storeAs($path, $filename);
            $returnPath = '/storage/uploads/image/';
            $imagePath = $returnPath . $filename;
            $postData = ['product_id_ref' => $request->product_id_ref, 'image' => $imagePath];

            DB::table('image')->insert($postData);
            return response()->json([

                'message' => 'image added successfully',
                'status' => '200',

            ]);
        } else {
            throw new \Exception('Only files with extension ' . implode(", ", $allowedfileExtension) . ' are allowed!');
        }
    }

    public function addDetail(Request $request)
    {
        $request->validate([
            'id' => 'nullable',
            'is_edit' => 'required',
            'product_id_ref' => 'required',
            'detail' => 'required',
            'descs' => 'required',
        ]);
        try {
            if ($request->is_edit == 1) {
                DB::table('detail')->where('id', '=', $request->id)->update($request->except('is_edit', 'id'));
            } else {
                DB::table('detail')->insert($request->except('is_edit', 'id'));
            }

            return response()->json([
                'message' => 'added detail successfully',
                'status' => '200',
            ]);
        } catch (\Exception $e) {
            return response()->json([

                'messsage' => 'added detail unsuccessfully',
                'status' => '402',

            ]);
        }
    }

    public function addColor(Request $request)
    {
        $request->validate([
            'id' => 'nullable',
            'is_edit' => 'required',
            'product_id_ref' => 'required',
            'color' => 'required',
            'color_code' => 'required',
        ]);
        try {
            if ($request->is_edit == 1) {
                DB::table('color')->where('id', '=', $request->id)->update($request->except('is_edit', 'id'));
            } else {
                DB::table('color')->insert($request->except('is_edit', 'id'));
            }
            return response()->json([

                'message' => 'added color successfully',
                'status' => '200',

            ]);
        } catch (\Exception $e) {
            return response()->json([

                'messsage' => 'added color unsuccessfully',
                'status' => '402',

            ]);
        }
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'is_edit' => 'required',
            'id_ref' => 'required',
            'name' => 'required',
            'price' => 'required',
            'discount' => 'required',
            'price_after_discount' => 'required',
            'is_warranty' => 'required',
            'warranty_period' => 'required',
            'min_qty' => 'required',
        ]);
        try {
            if ($request->is_edit == 1) {
                DB::table('product')->where('id_ref', '=', $request->id_ref)->update($request->except('is_edit'));
            } else {
                DB::table('product')->insert($request->except('is_edit'));
            }


            return response()->json(
                [
                    'message' => 'added product successfully',
                    'status' => '200',
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'messsage' => $e,
                    'status' => '402',
                ]
            );
        }
    }
}
