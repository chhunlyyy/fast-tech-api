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





Route::get('/order-statistic', [OrderController::class, 'getOrderStatistic']); // get orderstatistic
Route::post('/order-status', [OrderController::class, 'updateOrderStatus']); // update order status
//
Route::get('/package-order', [OrderController::class, 'getPackageOrder']); // get package order
Route::get('/delivery-order', [OrderController::class, 'getDeliveryOrder']); // get delivery order
Route::get('/pickup-order', [OrderController::class, 'getPickupOrder']); // get pick up order
Route::post('/order', [OrderController::class, 'order']); // add pick up order
Route::post('/delivery-order', [OrderController::class, 'deliveryOrder']); // add delivery order
Route::post('/package-order', [OrderController::class, 'packageOrder']); // add order package
//
Route::post('/remove-cart', [OrderController::class, 'removeCart']); // get cart
Route::get('/cart', [OrderController::class, 'getCart']); // get cart
Route::post('/cart', [OrderController::class, 'addToCart']); // add to cart
//
Route::get('/camera', [ProductController::class, 'getAllCamera']); // get all camera
Route::get('/product', [ProductController::class, 'getAllProduct']); // get all products
Route::get('/search', [ProductController::class, 'search']); // search 
// post method
Route::post('/product', [ProductController::class, 'addProduct']); // add image
Route::post('/image', [ProductController::class, 'addImage']); // add image
Route::post('/detail', [ProductController::class, 'addDetail']); // add detail
Route::post('/color', [ProductController::class, 'addColor']); // add detail
// user controller
Route::delete('/role', [UserController::class, 'deleteRole']); // delete role
Route::post('/role', [UserController::class, 'addRole']); // add new phone to admin
Route::get('/admin-user', [UserController::class, 'getAdminUser']); // get All Admin user
Route::post('/user-register', [UserController::class, 'register']); // register user
Route::post('/admin-user', [UserController::class, 'checkAdmin']); // check admin user
Route::post('/user-login', [UserController::class, 'login']); // register user
Route::post('/user-logout', [UserController::class, 'logOut']); // register user
Route::get('/user', [UserController::class, 'getUser']); //get user
