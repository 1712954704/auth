<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Common;

use App\Http\Manager\Cache\UserManager;
use App\Models\common\User;
use App\Models\common\UserInfo;
use App\Models\common\UserToken;
use App\Http\Service\ServiceBase;
use library\Constants\Model\UserConstants;

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
        $return_data = [
            'code' => 200,
            'data' => []
        ];
        $user_manager = new UserManager();
        $token_key = $user_manager->get_token_key($token);
        // 判断key是否存在
        if (!$this->_redis->exists($token_key)){
            $return_data['code'] = 201;
            return $return_data;
        }

        // 使用token获取用户缓存信息
        if ($fields){
            $data = $this->_redis->hmget($token_key,$fields);  // 获取全部信息
        }else{
            $data = $this->_redis->hgetall($token_key);  // 获取全部信息
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
                $this->_redis->hmset($token_key,$data);  // 设置token信息
                $data = $this->_redis->hmget($token_key,$fields);  // 获取全部信息
            }
        }
        $return_data['data'] = $data;
        return $return_data;
    }


    /**
     * 根据用户id获取用户信息
     * @param string $token 用户token
     * @param string $fields 字段名
     * @return array
     */
    public function get_user_info_by_id($user_id,$fields='')
    {
        $return_data = [
            'code' => 200,
            'data' => []
        ];
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
        $return_data['data'] = $data;
        return $return_data;
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
            $value = strtolower(json_decode($value,true));
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

    /**
     * 缓存token
     *
     * @param $token
     * @param array $data
     * @param int $expire_time
     *
     * @return bool
     */
    public function cache_token($token, array $data, $expire_time = \CacheConstants::CACHE_EXPIRE_TIME_HALF_TWO_HOUR) {
        if (empty($token)) return false;
        $token = $this->get_token_key($token);
        // 最近一次刷新token时间
        $data['refresh_token_time'] = time();
        $redis = $this->get_redis();
        $bool = $redis->hMset($token, $data);
        if ($bool) {
            $redis->expire($token, $expire_time);
        }
        return $bool;
    }

    /**
     * 刷新token过期时间
     *
     * @param $token
     * @param int $expire_time
     * @param int $refresh_token_time 最近一次刷新token时间
     *
     * @return int
     */
    public function refresh_token_expire($token, $refresh_token_time, $expire_time = \CacheConstants::CACHE_EXPIRE_TIME_HALF_TWO_HOUR) {
        if (empty($token)) return 0;
        // 一个小时刷新一次，避免频繁刷新
        if ((time() - $refresh_token_time) >= 3600) {
//            return $this->get_redis()->expire($this->get_token_key($token), $expire_time);
            return $this->_redis->expire($this->get_token_key($token), $expire_time);
        }
        return 0;
    }

}
