<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Hr;

use App\Http\Service\ServiceBase;
use App\Models\Hr\Structure;
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
        if ($name){
            $where['name'] = $name;
        }
        $need_fields = ['name', 'number','code','type','area_name','crated_at'];
        $result = Structure::where($where)->offset($limit)->limit($offset)->select($need_fields)->get();
        $this->return_data['data'] = \Common::laravel_to_array($result);
        return $this->return_data;
    }

}
