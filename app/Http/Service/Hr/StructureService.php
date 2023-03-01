<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Hr;

use App\Http\Service\ServiceBase;
use App\Models\Hr\Structure;
use library\Constants\Model\ModelConstants;
use library\Constants\StatusConstants;

class StructureService extends ServiceBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取组织列表列表
     * @param array $params [
     *      name => xxx   // 待搜索的值
     * ]
     * @return array
     */
    public function get_list($params)
    {
        $name = $params['name'] ?? '';
        $limit = $params['limit'] ?? 0;
        $offset = $params['offset'] ?? 10;
        $where = [];
        $where['status'] = ModelConstants::COMMON_STATUS_NORMAL;
        if ($name){
            $where['name'] = $name;
        }
        $need_fields = ['name', 'number','code','type','area_name','build_time'];
        $result = Structure::where($where)->offset($limit)->limit($offset)->select($need_fields)->get();
        $this->return_data['data'] = \Common::laravel_to_array($result);
        return $this->return_data;
    }

    /**
     * 获取组织列表列表
     * @param array $id 主键id
     * @param int $status 默认为 -1=已删除
     * @return array
     */
    public function change_status($id,$status = ModelConstants::COMMON_STATUS_DELETE)
    {
        $where['id'] = $id;
        $data = ['status'=>$status];
        $result = Structure::where($where)->update($data);
        if ($result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        return $this->return_data;
    }

    /**
     * 获取组织列表列表
     * @param array $id 主键id
     * @param int $status 默认为 -1=已删除
     * @return array
     */
    public function add_structure($params)
    {
        $result = Structure::insert($params);
        if ($result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        return $this->return_data;
    }

    /**
     * 获取组织列表列表
     * @param array $id 主键id
     * @param int $status 默认为 -1=已删除
     * @return array
     */
    public function update_structure($id,$params)
    {
        $where['id'] = $id;
        $result = Structure::where($where)->update($params);
        if ($result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        return $this->return_data;
    }

}
