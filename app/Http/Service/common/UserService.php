<?php

namespace App\Http\Service\common;

use App\Http\Manager\Cache\UserManager;
use App\Models\common\User;
use App\Models\common\UserInfo;
use App\Models\common\UserToken;
use Illuminate\Support\Facades\Redis;
use App\Http\Service\ServiceBase;
use library\Constants\Model\UserConst;

class UserService extends ServiceBase
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据token获取用户信息
     * @param string $token 用户token
     * @param string $fields 字段名
     * @return array
    */
    public function get_user_info_by_token($token,$fields='')
    {
        // 判断key是否存在
        if (!$this->_redis->exists($token)){
            return [];
        }

        // 使用token获取用户缓存信息
        if ($fields){
            $data = $this->_redis->hmget($token,$fields);  // 获取全部信息
        }else{
            $data = $this->_redis->hgetall($token);  // 获取全部信息
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
                    'id' => $list['user_id'],
                    'type' => $list['type'],
                ];
                $this->_redis->hmset($token,$data);  // 设置token信息
                $data = $this->_redis->hmget($token,$fields);  // 获取全部信息
            }
        }
        return $data ?? [];
    }


    /**
     * 根据用户id获取用户信息
     * @param string $token 用户token
     * @param string $fields 字段名
     * @return array
     */
    public function get_user_info_by_id($user_id,$fields='')
    {
        $user_manager = new UserManager();
        $redis_key = $user_manager->get_user_cache_key($user_id);

        // 使用token获取用户缓存信息
        if ($fields){
            $data = $this->_redis->hmget($redis_key,$fields);  // 获取全部信息
        }else{
            $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
        }

        if (!$data && !$this->_redis->exists($redis_key)){  // 用户缓存信息查不到则生成
            $user_info = $this->_inner_get_user_info_for_cache($user_id);
            if ($user_info){
                $this->_redis->hMset($redis_key, $user_info);
                $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
            }
        }
        return $data;
    }

    /**
     * 根据用户id获取用户权限信息
     * @param string $token 用户token
     * @param string $fields 字段名
     * @return array
     */
    public function get_user_auth_info_by_id($user_id,$fields='')
    {
        $user_manager = new UserManager();
        $redis_key = $user_manager->get_user_auth_cache_key($user_id);

        // 使用token获取用户缓存信息
        if ($fields){
            $data = $this->_redis->hmget($redis_key,$fields);  // 获取全部信息
        }else{
            $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
        }

        if (!$data && !$this->_redis->exists($redis_key)){  // 用户缓存信息查不到则生成
            $user_info = $this->_inner_get_user_info_for_cache($user_id);
            if ($user_info){
                // 数组转json存储
                foreach($user_info as &$item){
                    $item = json_encode($item);
                }
                $this->_redis->hMset($redis_key, $user_info);
                $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
            }
        }

        // 解码
        foreach ($data as &$value){
            $value = json_decode($value,true);
        }

        return $data;
    }


    /**
     * 获取要存储到缓存里的用户信息数据
     * @scope 内部使用
     *
     * @param int $user_id 用户id
     *
     * @return array
     */
    public function _inner_get_user_info_for_cache($user_id)
    {
        $user_model = new User();
        $user = $user_model->get_user_by_id($user_id,UserConst::COMMON_STATUS_NORMAL);

        if (empty($user)) {
            return array();
        }

        // 查询副表用户信息
        $user_info_model = new UserInfo();

        $user_info = $user_info_model->get_user_info($user_id);
        if (empty($user_info)){
            return array();
        }
        // 合并用户信息数组
        $user_info = array_merge($user,$user_info);

        return $user_info;
    }

}
