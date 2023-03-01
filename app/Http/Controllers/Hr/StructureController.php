<?php
/**
 * User: Jack
 * Date: 2023/03/1
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseController;
use App\Http\Service\Hr\RoleService;
use App\Http\Service\Hr\StructureService;
use library\Constants\StatusConstants;

class StructureController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 组织架构相关
     *
     */
    public function structure_operate()
    {
        $structure_service = new StructureService();
        switch ($this->method) {
            case 'GET':  // 获取组织架构列表
                // 检测参数
                $params['limit']  = $this->get_safe_int_param('limit',0);
                $params['offset'] = $this->get_safe_int_param('offset',10);
                $params['name']   = $this->data_arr['name'] ?? '';
                $data = $structure_service->get_list($params);
                break;
//            case 'POST':  // 添加角色
//                // 检测参数
//                $params['name']           = $this->check_param('name');
//                $params['is_menu']        = $this->check_param('is_menu');  // 是否菜单
//                $params['title']          = $this->data_arr['title'] ?? '';
//                $params['remark']         = $this->data_arr['remark'] ?? '';
//                $params['pid']            = $this->check_param('pid',0);
//                $auth                     = $this->check_param('auth');
//                $data = $structure_service->add_role($params,$auth);
//                break;
//            case 'PUT':  // 更新角色
//                // 检测参数
//                $id                      = $this->check_param('id');
//                $params['name']          = $this->data_arr['name'];
//                $params['is_menu']       = $this->data_arr['is_menu'];  // 是否菜单
//                $params['title']         = $this->data_arr['title'] ?? '';
//                $params['remark']        = $this->data_arr['remark'] ?? '';
//                $params['pid']           = $this->data_arr('pid',0);
//                $data = $structure_service->update_role($id,$params);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL,'Invalid Method');
        }
        return \Common::format_return_result($data['code'],$data['msg'],$data['data']);
    }

}
