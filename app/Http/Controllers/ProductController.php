<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function getAllProduct(Request $request)
    {
        $pageSize = $request->query('pageSize', 10);
        $pageIndex = $request->query('pageIndex', 0);
        $getProductQuery = "
        SELECT
           *
        FROM product
        ORDER BY product.id DESC
        LIMIT $pageSize OFFSET $pageIndex 
        ";

        $products = DB::select(DB::raw($getProductQuery));

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
                [
                    'message' => 'image added successfully',
                    'status' => '200',
                ]
            ]);
        } else {
            throw new \Exception('Only files with extension ' . implode(", ", $allowedfileExtension) . ' are allowed!');
        }
    }

    public function addDetail(Request $request)
    {
        $request->validate([
            'product_id_ref' => 'required',
            'detail' => 'required',
            'descs' => 'required',
        ]);
        try {
            DB::table('detail')->insert($request->all());
            return response()->json([
                [
                    'message' => 'added detail successfully',
                    'status' => '200',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                [
                    'messsage' => 'added detail unsuccessfully',
                    'status' => '402',
                ]
            ]);
        }
    }

    public function addColor(Request $request)
    {
        $request->validate([
            'product_id_ref' => 'required',
            'color' => 'required',
            'color_code' => 'required',
        ]);
        try {
            DB::table('color')->insert($request->all());
            return response()->json([
                [
                    'message' => 'added color successfully',
                    'status' => '200',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                [
                    'messsage' => 'added color unsuccessfully',
                    'status' => '402',
                ]
            ]);
        }
    }
}
