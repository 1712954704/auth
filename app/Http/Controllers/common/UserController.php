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
use library\Constants\StatusConstants;


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
    public function user_info()
    {
        $user_service = new UserService();
        switch ($this->method) {
            case 'GET': // 添加路由配置
                $data = $user_service->user_info($this->token,$this->system_type);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }


    /**
     * 清除用户锁定
    */
    public function clear_user_lock()
    {
        $user_service = new UserService();
        switch ($this->method) {
            case 'POST': // 添加路由配置
                // 检测参数
                $account = $this->check_param('account');  // 账号
                $data = $user_service->clear_user_lock($account);
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
                $params['account'] = $this->check_param('account');  // 账号
                $params['name'] = $this->check_param('name');  // 姓名
                $params['job_number'] = $this->check_param('job_number');  // 员工工号
                $params['email'] = $this->check_param('email');  // 邮箱
                $params['gender'] = $this->check_param('gender');  // 性别
                $params['structure_id'] = $this->check_param('structure_id');  // 所属组织
                $params['department_id'] = $this->check_param('department_id');  // 所属部门
                $params['manager_id'] = $this->check_param('manager_id');  // 所属主管
                $params['position_id'] = $this->data_arr['position_id'];  // 所属岗位
                $params['role_id'] = $this->data_arr['role_id'];  // 所属角色 数组形式 可以有多个角色
                $params['position_id'] = $this->data_arr['position_id'];  // 所属岗位
                $params['job_type'] = $this->data_arr['job_type'];  // 用户类型 1=在职 2=离职
                $params['status'] = $this->data_arr['status'];  // 状态 1=在职 2=离职
                $params['position_id'] = $this->data_arr['position_id'];  // 所属岗位



                $data = $user_service->register($params);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }

}
