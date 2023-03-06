<?php

use App\Http\Controllers\EmployeesController;
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
    return view('welcome');
});



Route::get('/ehr/user/{id}', function () {
    return ['test','id'];
});


// BIO员工登录接口
Route::post('/login', [\App\Http\Controllers\common\LoginController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\common\LoginController::class, 'logout']);





