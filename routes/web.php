<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
   // return view('welcome');
    return file_get_contents(public_path()."/swagger.html");
});
Route::get('/routes', function () {
    return response()->download("./routesl.txt","routesl.txt",[],"inline");
});
Route::get("/login", function (){
 return response()->json(["message"=>"you should
  login or register first From API "],200);
})->name("login");
