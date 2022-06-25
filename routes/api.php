<?php

use App\Http\Controllers\OrderController;
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


Route::post('/remove-cart', [OrderController::class, 'removeCart']); // get cart
Route::get('/cart', [OrderController::class, 'getCart']); // get cart
Route::post('/cart', [OrderController::class, 'addToCart']); // add to cart
//
Route::get('/product', [ProductController::class, 'getAllProduct']); // get all products
Route::get('/search', [ProductController::class, 'search']); // search 
// post method
Route::post('/product', [ProductController::class, 'addProduct']); // add image
Route::post('/image', [ProductController::class, 'addImage']); // add image
Route::post('/detail', [ProductController::class, 'addDetail']); // add detail
Route::post('/color', [ProductController::class, 'addColor']); // add detail
// user controller
Route::post('/user-register', [UserController::class, 'register']); // register user
Route::post('/user-login', [UserController::class, 'login']); // register user
Route::get('/user', [UserController::class, 'getUser']); //get user
