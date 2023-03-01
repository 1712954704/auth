<?php
/**
 * User: Jack
 * Date: 2023/03/1
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseController;
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
            case 'POST':  // 添加组织
                // 检测参数
                $params['name']           = $this->check_param('name');     // 单位名称
                $params['code']           = $this->check_param('code');  // 编号
                $params['type']           = $this->check_param('type');  // 单位性质 1=集体企业
                $params['build_time']     = $this->data_arr['build_time'] ?? null;  // 成立时间
                $params['faxes']          = $this->data_arr['faxes'] ?? null;  // 单位传真
                $params['address']        = $this->data_arr['address'] ?? null;  // 单位地址
                $params['order']          = $this->data_arr['order'] ?? 0;  // 排序
                $params['remark']         = $this->data_arr['remark'] ?? '';  // 备注说明
                $params['short_name']     = $this->data_arr['short_name'] ?? '';  // 单位简称
                $params['area_id']        = $this->check_param('area_id');  // 所属地区
                $params['pid']            = $this->check_param('pid',0); // 上级单位
                $params['phone']          = $this->data_arr['phone'] ?? '';  // 单位电话
                $params['home_page']      = $this->data_arr['home_page'] ?? '';  // 单位主页
                $data = $structure_service->add_structure($params);
                break;
            case 'PUT':  // 更新组织信息
                // 检测参数
                $id                      = $this->check_param('id');  // 主键id
                $params['name']           = $this->check_param('name');     // 单位名称
                $params['code']           = $this->check_param('code');  // 编号
                $params['type']           = $this->check_param('type');  // 单位性质 1=集体企业
                $params['build_time']     = $this->data_arr['build_time'] ?? null;  // 成立时间
                $params['faxes']          = $this->data_arr['faxes'] ?? null;  // 单位传真
                $params['address']        = $this->data_arr['address'] ?? null;  // 单位地址
                $params['order']          = $this->data_arr['order'] ?? null;  // 排序
                $params['remark']         = $this->data_arr['remark'] ?? null;  // 备注说明
                $params['short_name']     = $this->data_arr['short_name'] ?? '';  // 单位简称
                $params['area_id']        = $this->check_param('area_id');  // 所属地区 todo 需要换成id表关联形式.做一个地址表 暂时用文字输入
                $params['pid']            = $this->check_param('pid',0); // 上级单位
                $params['phone']          = $this->data_arr['phone'] ?? '';  // 单位电话
                $params['home_page']      = $this->data_arr['home_page'] ?? '';  // 单位主页
                $data = $structure_service->update_structure($id,$params);
                break;
            case 'DELETE':  // 删除组织
                // 检测参数
                $id                      = $this->check_param('id'); // 主键id
                $data = $structure_service->change_status($id);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL,'Invalid Method');
        }
        return \Common::format_return_result($data['code'],$data['msg'],$data['data']);
    }

    /**
     * 地区相关
     *
     */
    public function get_region()
    {
        $structure_service = new StructureService();
        switch ($this->method) {
            case 'GET':  // 获取组织架构列表
                // 检测参数
                $id  = $this->get_safe_int_param('id',1);
                $data = $structure_service->get_region($id);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL,'Invalid Method');
        }
        return \Common::format_return_result($data['code'],$data['msg'],$data['data']);
    }

    /**
     * 获取上级单位列表
     *
     */
    public function get_group_list()
    {
        $structure_service = new StructureService();
        switch ($this->method) {
            case 'GET':  // 获取组织架构列表
                // 检测参数
                $id  = $this->get_safe_int_param('id',0);
                $group_type           = $this->check_param('group_type',1);     // 组织部门类型 默认为1=组织
                $data = $structure_service->get_group_list($id,$group_type);
                break;
            default:
                return \Common::format_return_result(StatusConstants::ERROR_ILLEGAL,'Invalid Method');
        }
        return \Common::format_return_result($data['code'],$data['msg'],$data['data']);
    }

}
