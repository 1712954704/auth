<?php

namespace App\Http\Controllers\common;


use App\Http\Service\common\UserService;

class BaseController
{
    protected $is_login = 1;  // 是否需要登录 1:是 0:否

    protected $my_config;  // 配置

    protected $method;     // 请求方式

    /**
     * 初始化
     * @param int $is_login 是否需要登录 1:是 0:否
     * @param string $token     token
     * @return mixed
    */
    public function __construct($token='')
    {

        $this->my_config = \Common::get_config();

        // 设置默认时区
        date_default_timezone_set($this->my_config['default_time_zone'] ?? 'PRC');

        //调试日志
//        if ($this->my_config['flag']['write_debug']){
//            $this->write_request_info();
//        }

        //admin模块
//        $this->admin_model = new Admin();

        //跨域
        $this->cross_domain();

        //服务器时间 毫秒
        header('X-Server-Time: '.\Common::get_micro_time());

        //强制不缓存
        header("Cache-Control: no-cache");

        // 权限验证
        if ($this->is_login){
            $this->check_auth($token);
        }


    }

    /**
     * 跨域设置
    */
    private function cross_domain()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: DNT,X-CustomHeader,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization');
        header('Access-Control-Allow-Methods: GET,POST,OPTIONS,PUT,DELETE');
    }

    /**
     * log记录
    */
    public function __destruct()
    {
        //ip检测
        $this->check_ip();
        //更新未操作过期时间
        $this->update_no_action_time();
        //调试日志
        // _C_NOT_FOUND_ACTION = 是否是未找到action的响应，如果是就不记录请求日志
//        if (isset($this->my_config['flag']['write_debug']) && $this->my_config['flag']['write_debug'] && !isset($_SERVER['_C_NOT_FOUND_ACTION'])){

//            $this->request_log['user_id'] = isset($this->admin_info['id']) ? $this->admin_info['id'] : 0;
//            $this->request_log['update_at'] = date('Y-m-d H:i:s');
//            $return_code = isset($_SERVER['RETURN_CODE']) ? $_SERVER['RETURN_CODE'] : "";
//
//            $return_content = ob_get_contents();
//            // 响应结果保留长度
//            $response_body_keep_length = \Common::get_config('response_body_keep_length', 1000);
//            // 如果配置为0，记录全部
//            if ($response_body_keep_length > 0) {
//                $return_content = \Common::cut_middle_str($return_content, $response_body_keep_length);
//            }
//            $this->request_log['result'] = $return_code;
//            $this->request_log['http_code'] = http_response_code();
//            $this->request_log['body'] = $return_content;
//            $this->request_log['running_time'] = round(ETS::get_elapsed_time(ETS::STAT_ET_REQUEST, false), 3);
//            // 记录这个接口一共使用了多少条sql
//            $this->request_log['sql_count'] = Counter::fast_instance()->get(Counter::SQL);

//            $this->log_info("Backend request log", $this->request_log);
//        }
        if(ob_get_level()>0){
            ob_end_flush();
        }
    }

    public function update_no_action_time(){
        //左侧菜单红点请求不算 导出不算
//        if(isset($this->admin_info['account']) && !strstr($_SERVER['REQUEST_URI'],'/backend/check/overview') && !(isset($_GET['action']) && $_GET['action'] == 'export')){
//            $expire_time = \Common::get_config('admin_safe')['no_operation_logout_time'];
//            $this->_container->SM_Cache_Admin()->set_admin_user_no_action_tab($this->admin_info['account'],$expire_time);
//            $this->_container->S_Admin_Admin()->update_token_expire_time_by_id($this->admin_info['id']);
//        }
    }

    public function check_ip(){
//        if(isset($this->admin_info['account']) && isset($this->admin_info['id'])){
//            //重新获取客户端ip与redis进行比对
//            $ip = \Common::get_ip2();
//            $ip_redis = $this->_container->SM_Cache_Admin()->get_user_login_ip($this->admin_info['account']);
//            if($ip!=$ip_redis){
//                $result = $this->_container->S_Admin_Admin()->update_admin_login_ip($this->admin_info['account'],$ip);
//                if($result['code']==500){
//                    $this->log_error(__FUNCTION__ . ' 用户同步ip地址失败',[
//                        'result'=>$result
//                    ]);
//                }
//            }
//        }
    }

    public function check_auth($token=''){
        if (!$token){
            if((isset($_SERVER['HTTP_AUTHORIZATION']) && $_SERVER['HTTP_AUTHORIZATION']) || (isset($_SERVER['HTTP_AUTHORIZATION_2']) && $_SERVER['HTTP_AUTHORIZATION_2']) ) {
                $HTTP_AUTHORIZATION = !empty($_SERVER['HTTP_AUTHORIZATION_2'])?$_SERVER['HTTP_AUTHORIZATION_2']: $_SERVER['HTTP_AUTHORIZATION'];
                $a = explode(" ", $HTTP_AUTHORIZATION);
                if (isset($a[1]) && $a[1]) {
                    $token = $a[1];
                } else {
                    \Common::response_error_header(401, 'invalid token');
                }
            } else{
                \Common::response_error_header(401, 'invalid token 2');
            }
        }

        $user_service = new UserService();
        $check_result = $user_service->get_user_info_by_token($token);
        if (!$check_result){ // token错误
            return false;
        }
        // 获取用户权限信息并验证
        $auth_result = $user_service->get_user_auth_info_by_id($check_result['id']);
        

    }



}
