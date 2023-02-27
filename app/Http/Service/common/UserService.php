<?php

namespace App\Http\Service\common;

use App\Models\common\UserToken;
use Illuminate\Support\Facades\Redis;

class UserService extends ServiceBase
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
    public function check_auth($token,$fields='')
    {

        var_dump($this->_redis);die();
//        $redis = $this->get_redis();
        $redis = $this->_redis;


        $data = [];
        // 使用token获取用户缓存信息
        if ($fields){
            $data[$fields] = Redis::hmget($token,$fields);  // 获取全部信息
        }else{
            $data = Redis::hgetall($token);  // 获取全部信息
        }

        if (!$data){  // token缓存不存在则查询数据库用户token是否存在及状态
            $where = [
                'token' => $token
            ];
            $list = UserToken::where($where)->first();
            $list = \Common::laravel_to_array($list);
            // 用户token存在则重新设置用户信息
            if ($list['status'] == UserToken::STATUS_NORMAL){
                $data = [
                    'id' => $list['id'],
                    'type' => $list['type'],
                ];
                Redis::hmset($token,$data);  // 设置token信息
                $data = Redis::hmget($token,$fields);  // 获取全部信息
            }
        }
        return $data;
    }

    /**
     * test
     */
    public function test()
    {
        var_dump(23121);
    }

}
