<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Hr;

use App\Models\Hr\AuthRule;
use App\Http\Service\ServiceBase;
use library\Constants\Model\AuthConstants;
use library\Constants\Model\ModelConstants;
use library\Constants\StatusConstants;

class AuthService extends ServiceBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取路由规则
     * @param array $params
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_auth_rule($params,$limit,$offset)
    {
        $code = $params['code'];
        $where = [
            'status' => [
                AuthConstants::COMMON_STATUS_NORMAL,
                AuthConstants::COMMON_STATUS_OUTAGE,
            ]
        ];
        if ($code){
            $where['code'] = $code;
        }

        $fields = ['id','name','title','pid','type','remark','method','code','status','order','icon'];
        $result = AuthRule::with('child')->where($where)->limit($limit)->offset($offset)->select($fields)->get();
//        $result = AuthRule::with('child')->where($where)->select($fields)->get();
        $this->return_data['data']['total'] = AuthRule::where($where)->select($fields)->count();

        if (!$result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        $this->return_data['data'] = \Common::laravel_to_array($result);
        return $this->return_data;
    }

    /**
     * 添加路由规则
     * @param array $data
     * @return mixed
    */
    public function add_auth_rule($data)
    {
        $res = AuthRule::insert($data);
        if (!$res){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        return $this->return_data;
    }

    /**
     * 更新路由规则
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update_auth_rule($id,$data)
    {
        try {
            $where = [
                'id' => $id
            ];
            $result = AuthRule::where($where)->update($data);
            if (!$result){
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }catch (\Exception $e){
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
            $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }

    /**
     * 更新路由规则
     * @param int $id
     * @return mixed
     */
    public function del_auth_rule($id)
    {
        try {
            $where = [
                'id' => $id
            ];
            $data = [
                'status' => AuthConstants::COMMON_STATUS_DELETE
            ];
            $result = AuthRule::where($where)->update($data);
            if (!$result){
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }catch (\Exception $e){
            $code = $e->getCode();
            if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                $this->return_data['code'] = $code;
            }else{
                $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
            }
        }
        return $this->return_data;
    }

}
