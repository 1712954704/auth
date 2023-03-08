<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Common;

use App\Http\Manager\Cache\UserManager;
use App\Models\Common\Nation;
use App\Models\Common\Structure;
use App\Models\common\User;
use App\Models\common\UserInfo;
use App\Models\common\UserToken;
use App\Http\Service\ServiceBase;
use App\Models\Hr\UserRole;
use Illuminate\Support\Facades\DB;
use library\Constants\Model\ModelConstants;
use library\Constants\Model\UserConstants;
use library\Constants\StatusConstants;
use zjkal\TimeHelper;

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
     * @param string $user_id 用户id
     * @param array $fields 字段名
     * @return array
     */
    public function get_user_info_by_id($user_id,$fields=[])
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
        $return_data['data'] = $data;
        return $return_data;
    }

    /**
     * 根据用户id获取用户权限信息
     * @param string $user_id 用户id
     * @param array $fields 字段名
     * @return array
     */
    public function get_user_auth_info_by_id($user_id,$fields=[])
    {
        try {
            $user_manager = new UserManager();
            $redis_key = $user_manager->get_user_auth_cache_key($user_id);
            // 使用token获取用户缓存信息
            if ($fields){
                $data = $this->_redis->hmget($redis_key,$fields);  // 获取全部信息
            }else{
                $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
            }
            if ( !$this->_redis->exists($redis_key) || !$data || array_values($fields) != array_keys($data)){  // 用户缓存信息查不到则生成
                // todo 目前只获取了hr系统的权限 后续需要扩充
                $user_auth_info = $this->_inner_get_user_auth_info_for_cache($user_id);
                if ($user_auth_info){
//                $user_auth_info_new = array_column($user_auth_info,'name'); // 只获取单列
                    // 数组转json存储
                    foreach($user_auth_info as &$item){
                        $item = json_encode($item,JSON_UNESCAPED_UNICODE);
                    }
                    $this->_redis->hMset($redis_key, $user_auth_info);
                    $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
                }
            }
            // 解码
            foreach ($data as &$value){
                $value = json_decode($value,true);
            }
            return $data;
        }catch (\Exception $e){
            // 记录错误日志
        }
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
        $user = \Common::laravel_to_array($user);
        // 查询副表用户信息
        $user_info_model = new UserInfo();
        $user_info = $user_info_model->get_user_info($user_id);
        $user_info = \Common::laravel_to_array($user_info);
        // 合并用户信息数组并返回
        return array_merge($user,$user_info);
    }


    /**
     * 获取要存储到权限缓存里的用户信息数据
     * @scope 内部使用
     *
     * @param int $user_id 用户id
     * @param string $database_name 数据库连接
     *
     * @return array
     */
    public function _inner_get_user_auth_info_for_cache($user_id,$database_name='mysql_hr')
    {
        // 查询用户权限信息
        $sql = "select DISTINCT(a.id), a.name,a.type,a.code,a.pid,a.method,a.title from hr_auth_rule a left join hr_role_auth_rule b on a.id = b.auth_rule_id and b.status = 1
        left join hr_user_role c on b.role_id=c.role_id and c.status = 1 where a.status = 1 and c.user_id = ".$user_id;
        $result = DB::connection($database_name)->select($sql);
//        $return_data[NOW_SYSTEM_TYPE] = array_column(array_map('get_object_vars', $result),'name');
        $return_data[NOW_SYSTEM_TYPE] = $this->clear_auth_array_data(array_map('get_object_vars', $result));
        return $return_data;
    }

    /**
     * 整理路由规则数据格式
     * @param array $data
     * @return array
    */
    public function clear_auth_array_data($data)
    {
        $arr = [];
        foreach ($data as $item){
            if ($item['name']){
                $arr[$item['name']][] = $item['method'];
            }
        }
        return $arr;
    }


    /**
     * 获取要存储到权限缓存里的用户信息数据
     * @scope 内部使用
     *
     * @param int $user_id 用户id
     * @param string $database_name 数据库连接
     *
     * @return array
     */
    public function _inner_get_user_routes_info_for_cache($user_id,$database_name='mysql_hr')
    {
        // 查询用户权限信息
        $sql = "select DISTINCT(a.id),a.code,a.pid,a.type,a.title from hr_auth_rule a left join hr_role_auth_rule b on a.id = b.auth_rule_id
        left join hr_user_role c on b.role_id=c.role_id where c.user_id = ".$user_id;
        $result = DB::connection($database_name)->select($sql);
        return array_map('get_object_vars', $result);
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
            $user_manager = new UserManager();
            $my_config = \Common::get_config();
            // 检测用户是否锁定
            $key = $user_manager->get_last_key(UserConstants::HASH_USER_LOGIN_LIMIT,$account);
            $expire_time = \Common::get_config('user_safe')['login_fail_lock_time'];
            $is_lock = $this->_redis->hMGet($key,['is_lock'])['is_lock'] ?? 0;
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
                $lock_data = $this->user_login_limit($expire_time,$account,UserConstants::USER_LOGIN_LIMIT_TYPE_ERROR);
                $login_verify_num = \Common::get_config('user_safe')['login_verify_num'];
                $login_lock_num = \Common::get_config('user_safe')['login_lock_num'];
                if ($lock_data['num'] >= $login_verify_num && $lock_data['num'] < $login_lock_num){
                    throw new \Exception('',StatusConstants::ERROR_UPGRADE_PASSWORD_ERROR);
                }elseif ($lock_data['num'] >= $login_lock_num){
                    $this->lock_user(\Common::laravel_to_array($user_info));
                    throw new \Exception('',StatusConstants::ERROR_UPGRADE_AUTH_LOCK);
                }else{
                    throw new \Exception('',StatusConstants::ERROR_PASSWORD_CHECK_FAIL);
                }
            }
            // 更新用户token 1.生成用户token 2.查询用户token是否存在,存在更新不存在则创建
            $where = [
                'user_id' => $user_info->id,
            ];
            $result = UserToken::where($where)->first();
            $result = \Common::laravel_to_array($result);
            $token_data = [
                'token' => \Common::gen_token($user_info->id),
                'status' => UserConstants::COMMON_STATUS_NORMAL,
                'type' => $type,
            ];
            if ($result){
                UserToken::where($where)->update($token_data);
            }else{
                $token_data['user_id'] = $user_info->id;
                UserToken::where($where)->insert($token_data);
            }
            // 第一次登录无token
            if ($result && isset($result['token'])){
                $token_key = $user_manager->get_token_key($result['token']);
                // 查询旧token是否存在,并删除 创建新token保存
                if ($this->_redis->exists($token_key)){
                    $this->_redis->del($token_key);
                }
            }
            $data = [
                'id' => $user_info->id,
                'type' => $my_config['system_type'][$type],
            ];
            $new_token_key = $user_manager->get_token_key($token_data['token']);
            $this->_redis->hmset($new_token_key,$data);  // 设置token信息
            // 登录成功操作处理
            $this->user_login_limit($expire_time,$account,UserConstants::USER_LOGIN_LIMIT_TYPE_SUCCESS);
            $this->return_data['data']['token'] = $token_data['token'];
        }catch (\Exception $e){
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
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
        $user_manager = new UserManager();
        //检测redis是否存在对应的键值，不存在存默认1，存在自增
        $key = $user_manager->get_last_key(UserConstants::HASH_USER_LOGIN_LIMIT,$account);
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
            $data = [
                'num' => $result,
                'is_lock' => $result['is_lock'] ?? 0,
            ];
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

    /**
     * 锁定用户
     * @param array $user_info
     * @return mixed
    */
    public function lock_user($user_info)
    {
        // 用户锁定操作 todo 后续需要修改为mq异步操作
        User::where('id',$user_info['id'])->update(['status'=>UserConstants::COMMON_STATUS_LOCK]);
    }

    /**
     * 清除用户锁定(缓存)
     * @author jack
     * @dateTime 2023-03-02 13:21
     * @param string $account         用户账号
     * @return array
     */
    public function clear_user_lock($account)
    {
        try {
            $user_manager = new UserManager();
            $expire_time = \Common::get_config('user_safe')['login_fail_lock_time'];
            $key = $user_manager->get_last_key(UserConstants::HASH_USER_LOGIN_LIMIT,$account);
            //重置redis值
            $this->_redis->hMSet($key, [
                'num' => 0,
                'is_lock' => 0
            ]);
            $this->_redis->expire($key, $expire_time);
        }catch (\Exception $e){
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }


    /**
     * 获取用户路由表信息
     * @author jack
     * @dateTime 2023-03-02 13:21
     * @param string $user_id         用户token
     * @param string $system_type  系统类型
     * @return array
     */
    public function user_route_info($user_id,$system_type)
    {
        try {
            // 获取用户权限缓存信息
            $my_config = \Common::get_config();
            $type = $my_config['system_type'][$system_type];
            $user_manager = new UserManager();
            $redis_key = $user_manager->get_user_route_cache_key($user_id);
            // 获取对应系统的路由表
            $fields = [$my_config['system_type'][$system_type]];
            // 使用token获取用户缓存信息
            if ($fields){
                $data = $this->_redis->hmget($redis_key,$fields);  // 获取全部信息
            }else{
                $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
            }
            if (!$this->_redis->exists($redis_key) || !$data){  // 用户缓存信息查不到则生成
                $user_route_info = $this->_inner_get_user_routes_info_for_cache($user_id);
                if ($user_route_info){
                    $tree = $this->getTree($user_route_info, 0);
                    if ($tree){
                        $route_new[$type] = $tree;
                        // 数组转json存储
                        foreach($route_new as &$item){
                            $item = json_encode($item,JSON_UNESCAPED_UNICODE);
                        }
                        $this->_redis->hMset($redis_key, $route_new);
                        $data = $this->_redis->hgetall($redis_key);  // 获取全部信息
                    }
                }
            }
            // 解码
            foreach ($data as &$value){
                $value = json_decode($value,true);
            }
            $this->return_data['data'] = $data[$type] ?? [];
        }catch (\Exception $e){
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }

    /**
     * 整理路由表无限级分类
     * @param array $data
     * @param int $pid
     * @return array
    */
    public function getTree($data,$pid = 0)
    {
        $tree = [];
        foreach($data as $k => $v)
        {
            if($v['pid'] == $pid)
            {
                $arr['code'] = $v['code'];
                $arr['type'] = $v['type'];
                $arr['title'] = $v['title'];
                $arr['child'] = $this->getTree($data, $v['id']);
                $tree[] = $arr;
                unset($arr);
                unset($data[$k]);
            }
        }
        return $tree;
    }


    /**
     * 注册 todo 缺少更新用户缓存信息步骤
     * @author jack
     * @dateTime 2023-03-03 12:55
     * @param array $params
     * @param int $type 1=新增 2=编辑
     * @return mixed
     */
    public function register_or_edit($params,$type)
    {
        try {
            $user_id = $params['user_id'];
            // 主表信息
            $user = [
                'account' => $params['account'],
                'name' => $params['name'],
                'gender' => $params['gender'],
                'job_number' => $params['job_number'],
                'email' => $params['email'],
                'structure_id' => $params['structure_id'],
                'department_id' => $params['department_id'],
                'manager_id' => $params['manager_id'],
                'position_id' => $params['position_id'],
                'job_type' => $params['job_type'],
                'status' => $params['status'],
                'phone' => $params['phone'],
                'landline_phone' => $params['landline_phone'],
                'avatar' => $params['avatar'],
            ];
            // 副表信息
            $user_info = [
                'nation_id' => $params['nation_id'],
                'native_place' => $params['native_place'],
                'entry_date' => $params['entry_date'],
                'become_data' => $params['become_data'],
                'id_number' => $params['id_number'],
                'birth_date' => $params['birth_date'],
                'education' => $params['education'],
                'address' => $params['address'],
                'emergency_contact_name' => $params['emergency_contact_name'],
                'emergency_contact_relation' => $params['emergency_contact_relation'],
                'emergency_contact_phone' => $params['emergency_contact_phone'],
                'emergency_contact_address' => $params['emergency_contact_address'],
                'remark' => $params['remark'],
            ];
            // 开启事务
            DB::connection('mysql_common')->beginTransaction();
            switch ($type)
            {
                case 1:  // 新增
                    // 加密用户密码 使用配置默认密码
                    $my_config = \Common::get_config();
                    $salt = \Common::get_random_str(4);
                    $user['uuid'] = \Common::guid();
                    $user['salt'] = $salt;
                    $user['pwd'] = sha1($salt.sha1($my_config['user_safe']['default_password'])); // 初始密码为默认设置;
                    $create_user_result = User::create($user);
                    $user_id = $create_user_result->id;
                    $user_info['user_id'] = $user_id;
                    UserInfo::create($user_info);  // todo 需要修改为创建完成后设置缓存 改为rabbitmq操作
                    // 用户角色关系 创建关联关系
                    $role_id = $params['role_id'];
                    if ($role_id && is_array($role_id)){
                        $user_role_insert_arr = [];
                        foreach ($role_id as $item){
                            $user_role_insert_arr[] = [
                                'user_id' => $create_user_result->id,
                                'role_id' => $item,
                            ];
                        }
                        UserRole::insert($user_role_insert_arr);
                    }
                    break;
                case 2:
                    $where = [
                        'id' => $user_id,
                    ];
                    User::where($where)->update($user);
                    $user_info['user_id'] = $user_id;
                    UserInfo::where($where)->update($user_info);  // todo 需要修改为创建完成后设置缓存 改为rabbitmq操作
                    // 用户角色关系 先删除再创建关联关系
                    $role_id = $params['role_id'];
                    if ($role_id && is_array($role_id)){
                        UserRole::where(['user_id'=>$user_id])->update(['status' => ModelConstants::COMMON_STATUS_DELETE]);
                        $user_role_insert_arr = [];
                        foreach ($role_id as $item){
                            $user_role_insert_arr[] = [
                                'user_id' => $user_id,
                                'role_id' => $item,
                            ];
                        }
                        UserRole::insert($user_role_insert_arr);
                    }
                    break;
                default:
                    break;
            }
            DB::connection('mysql_common')->commit();
            // 更新用缓存
            $this->user_reset([$user_id],1);
        }catch (\Exception $e){
            DB::connection('mysql_common')->rollBack();
            var_dump($e->getLine());
            var_dump($e->getMessage());die();
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }


    /**
     * 清除用户锁定(缓存)
     * @author jack
     * @dateTime 2023-03-02 13:21
     * @param string $user_id         用户id
     * @return array
     */
    public function user_del($user_id)
    {
        try {
            $where['id'] = $user_id;
            DB::connection('mysql_common')->beginTransaction();
            User::where($where)->update(['status' => ModelConstants::COMMON_STATUS_DELETE]);
            DB::connection('mysql_common')->commit();
        }catch (\Exception $e){
            DB::connection('mysql_common')->rollBack();
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }


    /**
     * 清除用户锁定(缓存)
     * @author jack
     * @dateTime 2023-03-06 18:10
     * @param array $params         密码参数
     * @param string $user_id        用户id
     * @return array
     */
    public function user_reset_pwd($params,$user_id)
    {
        try {
            $old_pwd = $params['old_pwd'];
            $pwd = $params['pwd'];
            $repeat_pwd = $params['repeat_pwd'];
            // 获取用户缓存信息
            $user_info = $this->get_user_info_by_id($user_id)['data'];
            // 检测旧密码是否正确
            $old_pwd = sha1($user_info['salt'].sha1($old_pwd));
            if ($old_pwd != $user_info['pwd']){
                throw new \Exception('',StatusConstants::ERROR_PASSWORD_CHECK_FAIL);
            }
            // 检测两次密码是否一致
            if ($pwd != $repeat_pwd){
                throw new \Exception('',StatusConstants::ERROR_PASSWORD_CHECK_FAIL);
            }
            $new_pwd = sha1($user_info['salt'].sha1($pwd));
            $where['id'] = $user_id;
            DB::connection('mysql_common')->beginTransaction();
            User::where($where)->update(['pwd' => $new_pwd]);
            DB::connection('mysql_common')->commit();
        }catch (\Exception $e){
            DB::connection('mysql_common')->rollBack();
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }

    /**
     * 获取用户列表
     * @param array $params [
     *      name => xxx   // 待搜索的值
     * ]
     * @param int $id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function get_list($params,$id = null,$offset = 0,$limit = 10)
    {
        $job_number = $params['job_number'] ?? '';
        $job_type = $params['job_type'] ?? '';
        $department_id = $params['department_id'] ?? '';
        $where = [];
        $status = $params['status'];
        $where['status'] = $status;
        if ($job_number){
            $where[] = ['job_number','like','%' .$job_number .'%'];
        }
        if ($job_type){
            $where['job_type'] = $job_type;
        }
        if ($department_id){
            $where['department_id'] = $department_id;
        }
        if ($id){
            $where['id'] = $id;
        }

        try {
            $time = date('Y-m-d');
            // 只获取id 然后从缓存获取用户信息
            $need_fields = ['id'];
            $result = User::where($where)->offset($offset)->limit($limit)->select($need_fields)->get();
            $this->return_data['data']['total'] = User::where($where)->count();
            if (!$result){
                throw new \Exception('',StatusConstants::ERROR_DATABASE);
            }
            $this->return_data['data']['data'] = \Common::laravel_to_array($result);
            foreach ($this->return_data['data']['data'] as &$item){
                // 获取用户缓存信息
                $user_info = $this->get_user_info_by_id($item['id'])['data'] ?? '';
                $item['job_number'] = $user_info['job_number'] ?? '';
                $item['account'] = $user_info['account'] ?? '';
                $item['name'] = $user_info['name'] ?? '';
                $item['phone'] = $user_info['phone'] ?? '';
                $item['job_type'] = $user_info['job_type'] ?? '';
                $item['department_id'] = $user_info['department_id'] ?? '';
                $item['entry_date'] = isset($user_info['entry_date']) && !empty($user_info['entry_date']) ? intval($user_info['entry_date']) : '';
                if ($item['entry_date']){  // 计算时间差值
                    list($year,$month,$days) = $this->DiffDate(date('Y-m-d',$item['entry_date']),$time);
                    $item['entry_date'] = $year.'年'.$month.'月'.$days.'日';
                }else{
                    $item['entry_limit'] = '';
                }
                $item['position_name'] = '';
            }
            $department_ids = array_unique(array_column($this->return_data['data']['data'],'department_id'));
            $department_list = Structure::whereIn('id',$department_ids)->select(['id','name'])->get();
            $department_list = array_column(\Common::laravel_to_array($department_list),'name','id');
            foreach ($this->return_data['data']['data'] as &$item){
                // 获取用户缓存信息
                $item['department_name'] = $department_list[$item['department_id']] ?? '';
            }
        }catch (\Exception $e){
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }

    /**
    +----------------------------------------------------------
     * 功能：计算两个日期相差 年 月 日
    +----------------------------------------------------------
     * @param date   $date1 起始日期
     * @param date   $date2 截止日期日期
    +----------------------------------------------------------
     * @return array
    +----------------------------------------------------------
     */
    public function DiffDate($date1, $date2) {
        if (strtotime($date1) > strtotime($date2)) {
            $ymd = $date2;
            $date2 = $date1;
            $date1 = $ymd;
        }
        list($y1, $m1, $d1) = explode('-', $date1);
        list($y2, $m2, $d2) = explode('-', $date2);
        $y = $m = $d = $_m = 0;
        $math = ($y2 - $y1) * 12 + $m2 - $m1;
        $y = round($math / 12);
        $m = intval($math % 12);
        $d = (mktime(0, 0, 0, $m2, $d2, $y2) - mktime(0, 0, 0, $m2, $d1, $y2)) / 86400;
        if ($d < 0) {
            $m -= 1;
            $d += date('j', mktime(0, 0, 0, $m2, 0, $y2));
        }
        $m < 0 && $y -= 1;
        return array($y, $m, $d);
    }


    /**
     * 重置用户缓存
     * @param array $ids
     * @param int $system_type 1=hr 默认1
     * @return array
     */
    public function user_reset($ids,$system_type = 1)
    {
        try {
            $my_config = \Common::get_config();
            $type = $my_config['system_type'][$system_type];
            $user_manager = new UserManager();
            foreach ($ids as $user_id){
                // 更新info信息
                $redis_key_info = $user_manager->get_user_cache_key($user_id);

                $user_info = $this->_inner_get_user_info_for_cache($user_id);
                if ($user_info){
                    // 数组转json存储
                    foreach($user_info as &$item){
                        $item = json_encode($item);
                    }
                    $this->_redis->hMset($redis_key_info, $user_info);
                }
                // 更新auth权限信息
                $redis_key_auth = $user_manager->get_user_auth_cache_key($user_id);
                // todo 目前只获取了hr系统的权限 后续需要扩充
                $user_auth_info = $this->_inner_get_user_auth_info_for_cache($user_id);
                if ($user_auth_info){
                    // 数组转json存储
                    foreach($user_auth_info as &$item){
                        $item = json_encode($item,JSON_UNESCAPED_UNICODE);
                    }
                    $this->_redis->hMset($redis_key_auth, $user_auth_info);
                }
                // 更新用户路由表信息 route
                $redis_key_routes = $user_manager->get_user_route_cache_key($user_id);
                $user_route_info = $this->_inner_get_user_routes_info_for_cache($user_id);
                if ($user_route_info){
                    $tree = $this->getTree($user_route_info, 0);
                    if ($tree){
                        $route_new[$type] = $tree;
                        // 数组转json存储
                        foreach($route_new as &$item){
                            $item = json_encode($item,JSON_UNESCAPED_UNICODE);
                        }
                        $this->_redis->hMset($redis_key_routes, $route_new);
                    }
                }
            }
        }catch (\Exception $e){
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }


    /**
     * 获取民族列表
    */
    public function get_nation_list()
    {
        $this->return_data['data'] = Nation::select(['id','name'])->get();
        return $this->return_data;
    }
}
