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
use library\Constants\StatusConstants;

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
     * @param array $fields 字段名
     * @return array
     */
    public function get_user_auth_info_by_id($user_id,$fields=[])
    {
        $user_manager = new UserManager();
        $redis_key = $user_manager->get_user_auth_cache_key($user_id);

//        // 数组转json存储
//        $auth_data = ['hr'=>['*']];
//        foreach($auth_data as &$item){
//            $item = json_encode($item);
//        }
//        $this->_redis->hMSet($redis_key,$auth_data);  // 获取全部信息
//        var_dump(111);die();
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
        $user = $user_model->get_user_by_id($user_id,UserConstants::COMMON_STATUS_NORMAL);

        if (empty($user)) {
            return array();
        }

        // 查询副表用户信息
        $user_info_model = new UserInfo();

        $user_info = $user_info_model->get_user_info($user_id);
        if (empty($user_info)){
            return array();
        }

        $user = \Common::laravel_to_array($user);
        $user_info = \Common::laravel_to_array($user_info);
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
            $user_manager = new UserManager();
            return $this->_redis->expire($user_manager->get_token_key($token), $expire_time);
        }
        return 0;
    }


    /**
     * 登录
     *
     * @param string $account
     * @param string $pwd
     * @param string $type 登录系统类型
     * @return array
     */
    public function login($account,$pwd,$type)
    {
        try {
            // 检测用户是否锁定
            $key = $this->get_last_key(UserConstants::HASH_USER_LOGIN_LIMIT,$account);
            $expire_time = \Common::get_config('user_safe')['login_fail_lock_time'];
            $is_lock = $this->_redis->hMGet($key,'is_lock')['is_lock'] ?? 0;
            if ($is_lock == 1){
                throw new \Exception('',StatusConstants::ERROR_UPGRADE_AUTH_LOCK);
            }
            // 查询用户信息
            $where = [
                'account' => $account,
                'status' => UserConstants::COMMON_STATUS_NORMAL,
            ];
            $user_info = User::where($where)->first();
            if (!$user_info){
                throw new \Exception('',StatusConstants::ERROR_PASSWORD_OR_ACCOUNT);
            }
            // 检测密码
            if ($user_info->pwd != sha1($user_info->salt.sha1($pwd))){
                // 密码错误次数记录 超过次数则锁定
                $this->user_login_limit($expire_time,$account,UserConstants::USER_LOGIN_LIMIT_TYPE_ERROR);
                throw new \Exception('',StatusConstants::ERROR_PASSWORD_CHECK_FAIL);
            }
            // 更新用户token 1.生成用户token 2.查询用户token是否存在,存在更新不存在则创建
            $where = [
                'user_id' => $user_info->id,
            ];
            $result = UserToken::where($where)->first();
            $token_data = [
                'token' => \Common::gen_token(),
                'status' => UserConstants::COMMON_STATUS_NORMAL,
            ];
            if ($result){
                UserToken::where($where)->updated($token_data);
            }else{
                UserToken::where($where)->insert($token_data);
            }
            $user_manager = new UserManager();
            $token_key = $user_manager->get_token_key($result->token);
            // 查询旧token是否存在,并删除 创建新token保存
            if ($this->_redis->exists($token_key)){
                $this->_redis->del($token_key);
            }
            $data = [
                'id' => $user_info->id,
                'type' => $type,
            ];
            $this->_redis->hmset($token_key,$data);  // 设置token信息
            // 登录成功操作处理
            $this->user_login_limit($expire_time,$account,UserConstants::USER_LOGIN_LIMIT_TYPE_SUCCESS);
        }catch (\Exception $e){
            $this->return_data['code'] = $e->getCode();
            $this->return_data['msg'] = $e->getMessage();
        }
        return $this->return_data;
    }


    /**
     * 登录限制redis处理
     * @param int $expire_time 登录账号
     * @param string $account 登录账号
     * @param int $type 1登录失败处理  2登录成功处理  3检测redis记录
     */
    public function user_login_limit($expire_time, $account, $type)
    {
        //检测redis是否存在对应的键值，不存在存默认1，存在自增
        $key = $this->get_last_key(UserConstants::HASH_USER_LOGIN_LIMIT,$account);
        $data = [];
        if ($type == 1) {
            $result = $this->_redis->hIncrBy($key, 'num', 1);
            $login_lock_num = \Common::get_config('user_safe')['login_lock_num'];
            //锁定账号
            if ($result >= $login_lock_num) {
                $this->_redis->hMSet($key, ['is_lock' => 1]);
                //有效期12小时3600*12
                $this->_redis->expire($key, $expire_time);
            }
        } else if ($type == 2) {
            //重置redis值
            $this->_redis->hMSet($key, [
                'num' => 0,
                'is_lock' => 0
            ]);
            $this->_redis->expire($key, $expire_time);
        } else if ($type == 3) {
            $result = $this->_redis->hGetAll($key);
            if (isset($result['num'])) {
                $data = [
                    'num' => $result['num'],
                    'is_lock' => $result['is_lock'] ?? 0,
                ];
            }
        }
        return $data;
    }

}
