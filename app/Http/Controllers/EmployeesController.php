<?php

namespace App\Http\Controllers;

use App\Http\Controllers\common\BaseController;
use App\Http\Service\ServiceBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;


class EmployeesController extends Controller
{


    //
    /**
     * 显示给定用户的个人资料。
     *
     * @param  int  $id
     * @return false|string
     */
    public function show($id)
    {

        $user = DB::connection('mysql_hr')->table('usernew')->find($id);

        // 设定返回接口code 1 假的 2 真的
        $response['code'] = $user?'2':'1';
        $response['msg']  = $user?'success':'账号不存在';
        $response['data'] = $user ;
        // 参数1：中文不转为unicode ，对应的数字 256
        return json_encode($response,256);

    }
    public function add()
    {


       // $user = new ServiceBase();
       // $redis = $user->get_redis();
       // $redis->set('test_key','123456');
       // return  $redis->get('test_key');

        Redis::set('name', 'guwenjie');
        $values = Redis::get('name');
        dd($values);


        return 123;

    }
}
