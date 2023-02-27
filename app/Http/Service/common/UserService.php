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



    public function check_auth($token){
        $sql = "select * from tbl_admin_token where token=:token and status=1";
        $sth = $this->adapter->query($sql);
        $result = $sth -> execute([
            ':token' => $token,
        ]);
        $selected_token = $result->current();







        if (!empty($selected_token)){
//        if(1){
            $user_info = $this->get_admin_info_by_id($selected_token['admin_id']);
//            $user_info = $this->get_admin_info_by_id(1);
            if ($user_info['code'] == 200){
                return array(
                    'code' => 200,
                    'data' => array(
                        'token' => $selected_token['token'],
                        'admin_info' => array(
                            'id' => $user_info['data']['id'],
                            'type' => $user_info['data']['type'],
                            'account' => $user_info['data']['account'],
                            'rule_id_array'=> $this->get_admin_rule_id_list($user_info['data']['id'], $user_info['data']['type'])
                        ),
                    )
                );
            }else{
                return array(
                    'code' => 401,
                    'data' => 'Admin Not Found'
                );
            }
        } else{
            return array(
                'code' => 401,
                'data' => 'Token Not Found'
            );
        }
    }

    /**
     * test
     */
    public function test()
    {
        var_dump(23121);
    }

}
