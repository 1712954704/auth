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

Route::get('/user', [UserController::class, 'index']);

Route::get('/test', function () {
    return ['test','id'];
});

Route::get('/ehr/user/{id}', function () {
    return ['test','id'];
});

// BIO 员工详情接口
Route::get('/user/show/{id}', [EmployeesController::class, 'show']);
Route::get('/add', [EmployeesController::class, 'add']);

