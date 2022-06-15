<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
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
Route::get('/search', [ProductController::class, 'search']); // search 
// post method
Route::post('/product', [ProductController::class, 'addProduct']); // add image
Route::post('/image', [ProductController::class, 'addImage']); // add image
Route::post('/detail', [ProductController::class, 'addDetail']); // add detail
Route::post('/color', [ProductController::class, 'addColor']); // add detail
// user controller
Route::post('/user-register', [UserController::class, 'register']); // register user

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
