<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    var_dump(111);die();
    return $request->user();
});

//Route::get('/user', [UserController::class, 'index']);
Route::get('/home', [\App\Http\Controllers\common\UserController::class, 'home']);

Route::get('/greeting', function () {
    return 'Hello World';
});
