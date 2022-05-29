<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::get('/product', [ProductController::class, 'getAllProduct']); // get all products

// post method
Route::post('/image', [ProductController::class, 'addImage']); // add image
Route::post('/detail', [ProductController::class, 'addDetail']); // add detail
Route::post('/color', [ProductController::class, 'addColor']); // add detail


//

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
