<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubCategoryController;
use Illuminate\Http\Request;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'users'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/', [AuthController::class, 'register']);
    Route::get('/', [AuthController::class, 'userList']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::put('/profile', [AuthController::class, 'updateUserProfile']);

    Route::get('/{id}', [AuthController::class, 'userProfile']);
    Route::delete('/{id}', [AuthController::class, 'destroy']);
    Route::put('/{id}', [AuthController::class, 'updateUser']);
});

Route::group([
    'prefix' => 'products'
], function () {
    Route::get('/top', [ProductController::class, 'topProducts']);
    Route::get('/', [ProductController::class, 'products']);
    Route::post('/', [ProductController::class, 'store'])->middleware('api');

    Route::get('/{slug}', [ProductController::class, 'productBySlug']);
    Route::get('/{price}/{gt}', [ProductController::class, 'productsByPrice']);
   
   
    Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('api');
    Route::put('/{id}', [ProductController::class, 'update'])->middleware('api');

    Route::post('/{id}/reviews', [ProductController::class, 'addProductReview'])->middleware('api');

    Route::post('/uploads', [ProductController::class, 'uploadImage'])->middleware('api');
    
    Route::put('/{id}/attachment', [ProductController::class, 'addAttachment'])->middleware('api');
    
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'orders'
], function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/', [OrderController::class, 'orders']);
    Route::get('/myorders', [OrderController::class, 'myOrders']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::put('/{id}/pay', [OrderController::class, 'updatePayment']);
    Route::put('/{id}/deliver', [OrderController::class, 'updateDeliver']);
});

Route::middleware('auth:api')->get('/config/paypal', function () {
    return response()->json(env('PAYPAL_CLIENT_ID', 0), 200);
});
Route::group([
    'prefix' => 'categories'
], function () {
    Route::get('/subCategories', 'App\Http\Controllers\SubCategoryController@subCategories');
    Route::get('/', [CategoryController::class, 'categories']);
    Route::get('/products', [CategoryController::class, 'products']);
});
Route::group([
    'middleware' => 'api',
    'prefix' => 'categories'
], function () {
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{id}', [CategoryController::class, 'categoryBySlug']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});

Route::get('/subCategories', [SubCategoryController::class, 'subCategories']);
Route::group([
    'middleware' => 'api',
    'prefix' => 'subCategories'
], function () {
    Route::post('/', [SubCategoryController::class, 'store']);
    Route::get('/{id}', [SubCategoryController::class, 'subCategoryBySlug']);
    Route::put('/{id}', [SubCategoryController::class, 'update']);
    Route::delete('/{id}', [SubCategoryController::class, 'destroy']);
});
