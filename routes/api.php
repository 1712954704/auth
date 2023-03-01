<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Hr\AuthController;
use \App\Http\Controllers\Hr\RoleController;

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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::controller(AuthController::class)->group(function () {
    Route::get('/auth/rule', 'rule_operate');   // 获取路由列表
    Route::post('/auth/rule', 'rule_operate');  // 添加路由规则
    Route::put('/auth/rule', 'rule_operate');   // 修改路由规则
});


Route::controller(RoleController::class)->group(function () {
    Route::get('/auth/role', 'role_operate');   // 获取角色列表
    Route::post('/auth/role', 'role_operate');  // 添加角色
    Route::put('/auth/role', 'role_operate');   // 修改角色信息
    Route::post('/auth/change_user_role', 'change_user_role');   // 添加用户角色关联
    Route::put('/auth/change_user_role', 'change_user_role');   // 修改用户角色关联
});


Route::controller(\App\Http\Controllers\Hr\StructureController::class)->group(function () {
    Route::get('/structure', 'structure_operate');   // 获取组织架构列表
    Route::post('/structure', 'structure_operate');   // 添加组织
    Route::put('/structure', 'structure_operate');   // 更新组织
    Route::delete('/structure', 'structure_operate');   // 删除组织架构
    Route::get('/region', 'get_region');   // 获取地区信息
    Route::get('/structure/group', 'get_group_list');   // 获取上级单位信息
});


Route::apiResource('department',\App\Http\Controllers\DepartmentController::class);
Route::apiResource("hr/check",\App\Http\Controllers\Hr\CheckController::class);
Route::apiResource("hr/position",\App\Http\Controllers\Hr\PositionController::class);
