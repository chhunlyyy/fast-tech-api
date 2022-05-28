<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    //

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
}
