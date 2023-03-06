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
use library\Constants\Model\UserConstants;
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
                $data = $user_service->login($account, $pwd, $this->system_type);
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
            case 'GET': // 获取用户信息
                $data['data']['info'] = $this->user_info;
                $routes = $user_service->user_route_info($this->user_info['id'],$this->system_type);
                $data['code'] = $routes['code'];
                $data['msg'] = $routes['msg'];
                $data['data']['routes'] = $routes['data'];
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
                $params['gender'] = $this->check_param('gender');  // 性别 1=男 2=女
                $params['job_number'] = $this->check_param('job_number');  // 员工工号
                $params['email'] = $this->check_param('email');  // 邮箱
                $params['structure_id'] = $this->check_param('structure_id');  // 所属组织
                $params['department_id'] = $this->check_param('department_id');  // 所属部门
                $params['manager_id'] = $this->check_param('manager_id');  // 直属主管
                $params['position_id'] = $this->check_param('position_id');  // 所属岗位
                $params['role_id'] = $this->data_arr['role_id'] ?? [];  // 所属角色 数组形式 可以有多个角色
                $params['job_type'] = $this->check_param('job_type');  // 用户类型 1=在职 2=离职
                $params['status'] = $this->check_param('status');  // 状态 1=在职(正常) 2=锁定 3=禁用 -1=删除
                $params['phone'] = $this->check_param('phone');  // 手机
                $params['landline_phone'] = $this->data_arr['landline_phone'] ?? '';  // 办公室座机
                $params['avatar'] = $this->data_arr['avatar'] ?? '';  // 头像
                $params['nation_id'] = $this->data_arr['nation_id'] ?? null;  // 民族
                $params['native_place'] = $this->data_arr['native_place'] ?? '';  // 籍贯
                $params['entry_date'] = $this->data_arr['entry_date'] ?? null;  // 正式入职时间
                $params['become_data'] = $this->data_arr['become_data'] ?? null;  // 转正时间
                $params['id_number'] = $this->data_arr['id_number'] ?? '';  // 身份证号
                $params['birth_date'] = $this->data_arr['birth_date'] ?? null;  // 出生日期
                $params['education'] = $this->data_arr['education'] ?? null;  // 学历 1=小学 2=初中 3=高中 4=中专 5=大专 6=本科 7=研究生 8=博士及以上
                $params['address'] = $this->data_arr['address'];  // 现住址
                $params['emergency_contact_name'] = $this->data_arr['emergency_contact_name'] ?? '';  // 紧急联系人姓名
                $params['emergency_contact_relation'] = $this->data_arr['emergency_contact_relation'] ?? '';  // 紧急联系人关系
                $params['emergency_contact_phone'] = $this->data_arr['emergency_contact_phone'] ?? '';  // 紧急联系人电话
                $params['emergency_contact_address'] = $this->data_arr['emergency_contact_address'];  // 紧急联系人现住址
                $params['remark'] = $this->data_arr['remark'];  // 备注
                $data = $user_service->register($params);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }


    /**
     * 获取用户列表
     */
    public function user_operate()
    {
        $user_service = new UserService();
        switch ($this->method) {
            case 'GET': // 获取用户列表
                $page             = $this->get_safe_int_param('page',1);
                $limit            = $this->get_safe_int_param('limit',10);
                if (isset($this->data_arr['status']) && in_array($this->data_arr['status'],[UserConstants::COMMON_STATUS_NORMAL,UserConstants::COMMON_STATUS_LOCK,UserConstants::COMMON_STATUS_DISABLE])){
                    $params['status'] = $this->data_arr['status'];
                }else{
                    $params['status'] = [UserConstants::COMMON_STATUS_NORMAL,UserConstants::COMMON_STATUS_LOCK,UserConstants::COMMON_STATUS_DISABLE];
                }
                $id               = $this->data_arr['id'] ?? null;     // 主键id
                $params['job_number'] = $this->data_arr['job_number'] ?? null;     // 员工工号
                $params['job_type'] = $this->data_arr['job_type'] ?? null;     // 员工类型
                $params['department_id'] = $this->data_arr['department_id'] ?? null;     // 员工类型
                $offset  = ($page - 1) * $limit;
                $data = $user_service->get_list($params,$id,$offset,$limit);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 重置用户缓存信息
     */
    public function user_reset()
    {
        $user_service = new UserService();
        switch ($this->method) {
            case 'POST': // 添加路由配置
                // 检测参数
                $ids = $this->check_param('ids');  // 账号
                $data = $user_service->user_reset($ids,$this->system_type);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL, 'Invalid Method');
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }

}
