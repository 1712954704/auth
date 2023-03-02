<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Controllers\Common;


use App\Http\Controllers\BaseController;
use App\Http\Service\Hr\RoleService;
use App\Http\Service\ServiceBase;
use App\Http\Service\common\UserService;
use App\Models\common\UserToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;


class UserController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 登录
     */
    public function login()
    {
        // 验证 ip地址
        $user_service = new UserService();
        switch ($this->method) {
            case 'POST': // 添加路由配置
                // 检测参数
                $account = $this->check_param('account');  // 账号
                $pwd = $this->check_param('pwd');  // 密码
                $type = $this->check_param('type');  // 登录系统类型
                $data = $user_service->login($account, $pwd, $type);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 获取用户信息包含权限个及个人配置等(登录后获取)
     */
    public function get_user_info()
    {
        $user_service = new UserService();
        switch ($this->method) {
            case 'GET': // 添加路由配置
                // 检测参数
                $data = $user_service->get_user_info($this->token,$this->system_type);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }



    /**
     * 注册
     */
    public function register()
    {
        $user_service = new UserService();
        switch ($this->method) {
            case 'POST': // 添加路由配置
                // 检测参数
                $params = $this->check_param('account');  // 账号

                $data = $user_service->register($params);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }

}
