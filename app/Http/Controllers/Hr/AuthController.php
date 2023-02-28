<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseController;
use App\Http\Service\Hr\AuthService;
use library\Constants\StatusConstants;

class AuthController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 路由相关
     *
     */
    public function rule()
    {
        $auth_service = new AuthService();
        switch ($this->method) {
            case 'GET':  // 获取路由配置
                // 检测参数
                $data = $auth_service->get_auth_rule();
                break;
            case 'POST': // 添加路由配置
                // 检测参数
                $data['name']            = $this->check_param('name');
                $data['is_menu']            = $this->check_param('is_menu');  // 是否菜单
                $data['title']         = $this->data_arr['title'] ?? '';
                $data['remark']         = $this->data_arr['remark'] ?? '';
                $data['pid']            = $this->check_param('pid',0);
                $data = $auth_service->add_auth_rule($data);
                break;
            case 'PUT':  // 更新路由配置
                // 检测参数
                $id                    = $this->check_param('id');
                $data['name']          = $this->data_arr['name'];
                $data['is_menu']       = $this->data_arr['is_menu'];  // 是否菜单
                $data['title']         = $this->data_arr['title'] ?? '';
                $data['remark']        = $this->data_arr['remark'] ?? '';
                $data['pid']           = $this->data_arr('pid',0);
                $data = $auth_service->update_auth_rule($id,$data);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL,'Invalid Method');
        }
        return \Common::format_return_result($data['code'],$data['msg'],$data['data']);
    }

}
