<?php

namespace App\Http\Service\common;


use Illuminate\Support\Facades\Redis;

class UserService
{

    public function __construct()
    {

    }


    /**
     * 获取用户信息
     * @param string $token
     * @return mixed
     */
    public function get_user_info_by_token($token)
    {

        // 获取用户缓存信息
        $user_info = 1;
//        $res = Redis::hget('runoobkey');
//        $res = Redis::hmset('test_key',['name'=>'test']);
//        $res = Redis::hmget('test_key','name');
//        $res = Redis::hgetall('test_key');
//        $res = Redis::hmget('runoobkey','name');
//        $res = Redis::hgetall($token);
//        var_dump($res);exit();

        $user_info = Redis::hgetall($token);  // cache 缓存常量定义

        return $user_info;
    }


    /**
     * 使用token获取用户信息
     * @param string $token 用户token
     * @param string $fields 字段名
     * @return mixed
    */
    public function check_auth($token,$fields=''){
        // 使用token获取用户缓存信息
        if ($fields){
            $user_info = Redis::hmget($token,$fields);  // 获取全部信息
        }else{
            $user_info = Redis::hgetall($token);  // 获取全部信息
        }
        $data = array(
            'code' => 401,
            'msg' => '',
            'data' => []
        );
        if (!$user_info){  // token不存在
            $data['msg'] = 'Token Not Found';
            return $data;
        }
        return $user_info;
    }

    /**
     * test
     */
    public function test()
    {
        var_dump(23121);
    }

}
