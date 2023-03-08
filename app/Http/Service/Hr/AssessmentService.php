<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Hr;

use App\Models\Hr\AuthRule;
use App\Http\Service\ServiceBase;
use library\Constants\StatusConstants;

class AssessmentService extends ServiceBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取路由规则
     * @param int $user_id
     * @param string $test_number
     * @return mixed
     */
    public function index($user_id,$limit,$offset)
    {
        $where['user_id'] = $user_id;
        // $where[] = ['type','>=',$test_number ];

        // 模型层处理数据库数据
        $columns = ['*'];
        $result = $this->model::index($columns,$limit,$offset,$where);
        // $this->return_data['data'] = \Common::laravel_to_array($result);
        $this->return_data['data'] = $result;
        return $this->return_data;
    }


}
