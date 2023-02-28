<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseController;
use App\Http\Service\Hr\AuthService;
use App\Http\Service\Hr\RoleService;
use library\Constants\StatusConstants;

class RoleController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 角色权限相关
     *
     */
    public function role_operate()
    {
        $role_service = new RoleService();
        switch ($this->method) {
            case 'GET':  // 获取角色列表
                // 检测参数
                $data = $role_service->get_role();
                break;
            case 'POST':  // 添加角色
                // 检测参数
                $params['name']           = $this->check_param('name');
                $params['is_menu']        = $this->check_param('is_menu');  // 是否菜单
                $params['title']          = $this->data_arr['title'] ?? '';
                $params['remark']         = $this->data_arr['remark'] ?? '';
                $params['pid']            = $this->check_param('pid',0);
                $auth                     = $this->check_param('auth');
                $data = $role_service->add_role($params,$auth);
                break;
            case 'PUT':  // 更新角色
                // 检测参数
                $id                      = $this->check_param('id');
                $params['name']          = $this->data_arr['name'];
                $params['is_menu']       = $this->data_arr['is_menu'];  // 是否菜单
                $params['title']         = $this->data_arr['title'] ?? '';
                $params['remark']        = $this->data_arr['remark'] ?? '';
                $params['pid']           = $this->data_arr('pid',0);
                $data = $role_service->update_role($id,$params);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL,'Invalid Method');
        }
        return \Common::format_return_result($data['code'],$data['msg'],$data['data']);
    }


    /**
     * 用户角色相关
     *
     */
    public function change_user_role()
    {
        $role_service = new RoleService();
        switch ($this->method) {
            case 'POST':  // 添加用户角色关联
                // 检测参数
                $user_id        = $this->data_arr['user_id'] ?? $this->user_info['id']; // 可不传默认为当前请求用户
                $role           = $this->check_param('role');  // 角色数组
                $data = $role_service->add_user_role($user_id,$role);
                break;
            case 'PUT':  // 更新角色
                // 检测参数
                $user_id        = $this->data_arr['user_id'] ?? $this->user_info['id']; // 可不传默认为当前请求用户
                $role           = $this->check_param('role');  // 角色数组
                $data = $role_service->update_user_role($user_id,$role);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL,'Invalid Method');
        }
        return \Common::format_return_result($data['code'],$data['msg'],$data['data']);
    }

}
