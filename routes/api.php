<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Hr\AuthController;
use \App\Http\Controllers\Hr\RoleController;
use \App\Http\Controllers\Common\UserController;
use \App\Http\Controllers\Hr\StructureController;
use \App\Http\Controllers\common\FileController;
use \App\Http\Controllers\Common\PositionController;

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


Route::controller(UserController::class)->group(function () {
    Route::post('/user/login', 'login');   // 登录
    Route::get('/user/info', 'user_info');   // 获取用户信息
    Route::post('/user/register', 'user_register_or_edit');   // 用户新增
    Route::post('/user/clear_user_lock', 'clear_user_lock');   // 清除用户锁定
    Route::get('/user/list', 'user_list');   // 获取用户列表
    Route::post('/user/user_reset', 'user_reset');   // 重置用户缓存信息
    Route::put('/user/edit', 'user_register_or_edit');   // 编辑用户
    Route::delete('/user/del', 'user_del');   // 删除用户
    Route::post('/user/reset_pwd', 'user_reset_pwd');   // 用户重置密码
    Route::get('/nation_list', 'get_nation_list');   // 获取民族列表
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/auth/rule', 'rule_operate');   // 获取路由列表
    Route::post('/auth/rule', 'rule_operate');  // 添加路由规则
    Route::put('/auth/rule', 'rule_operate');   // 修改路由规则
    Route::delete('/auth/rule', 'rule_operate');// 删除路由规则
});


Route::controller(RoleController::class)->group(function () {
    Route::get('/auth/role', 'role_operate');   // 获取角色列表
    Route::post('/auth/role', 'role_operate');  // 添加角色
    Route::put('/auth/role', 'role_operate');   // 修改角色信息
    Route::delete('/auth/role', 'role_operate');   // 删除角色信息
    Route::post('/auth/change_user_role', 'change_user_role');   // 添加角色用户关联
    Route::post('/auth/change_role_auth', 'change_role_auth');   // 添加角色权限关联
//    Route::put('/auth/change_user_role', 'change_user_role');   // 修改用户角色关联 已废弃
});


Route::controller(StructureController::class)->group(function () {
    Route::get('/structure', 'structure_operate');   // 获取组织架构列表
    Route::post('/structure', 'structure_operate');   // 添加组织
    Route::put('/structure', 'structure_operate');   // 更新组织
    Route::delete('/structure', 'structure_operate');   // 删除组织架构
    Route::get('/region', 'get_region');   // 获取地区信息
    Route::get('/structure/group', 'get_group_list');   // 获取上级单位信息
//    Route::get('/structure/tree_list', 'get_tree_list');   // 获取组织结构树形结构 已废弃
});

Route::controller(PositionController::class)->group(function () {
    Route::get('/position', 'position_operate');   // 获取职务列表
    Route::post('/position', 'position_operate');  // 添加角色
    Route::put('/position', 'position_operate');   // 修改角色信息
    Route::delete('/position', 'position_operate');   // 删除角色信息
//    Route::post('/auth/change_user_role', 'change_user_role');   // 添加角色用户关联
});


Route::controller(FileController::class)->group(function () {
    Route::post('/file/add_file', 'add_file');   // 文件上传
});

Route::apiResource('department',\App\Http\Controllers\common\DepartmentController::class);
Route::apiResource("hr/check",\App\Http\Controllers\Hr\CheckController::class);
Route::apiResource("hr/position",\App\Http\Controllers\Hr\PositionController::class);
Route::apiResource("hr/assessment",\App\Http\Controllers\Hr\AssessmentController::class);
