<?php

namespace App\Http\Service\Hr;

use App\Http\Manager\Cache\UserManager;
use App\Models\common\User;
use App\Models\common\UserInfo;
use App\Models\common\UserToken;
use App\Models\Hr\AuthRule;
use Illuminate\Support\Facades\Redis;
use App\Http\Service\ServiceBase;
use library\Constants\Model\UserConst;
use library\Constants\StatusConstants;

class AuthService extends ServiceBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加路由规则
     * @param array $data
     * @return mixed
     */
    public function get_auth_rule($data)
    {
        $result = AuthRule::get($data);
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
     * 添加路由规则
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update_auth_rule($id,$data)
    {
        $where = [
            'id' => $id
        ];
        $result = AuthRule::where($where)->update($data);
        if (!$result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        $this->return_data['data'] = \Common::laravel_to_array($result);
        return $this->return_data;
    }

}
