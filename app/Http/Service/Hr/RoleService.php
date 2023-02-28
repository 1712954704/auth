<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Hr;

use App\Http\Service\ServiceBase;
use App\Models\Hr\Role;
use App\Models\Hr\RoleAuthRule;
use App\Models\Hr\UserRole;
use Illuminate\Support\Facades\DB;
use library\Constants\StatusConstants;

class RoleService extends ServiceBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取角色列表
     * @return mixed
     */
    public function get_role()
    {
        $result = Role::get();
        if (!$result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        $this->return_data['data'] = \Common::laravel_to_array($result);
        return $this->return_data;
    }

    /**
     * 添加角色
     * @param array $params
     * @param array $auth
     * @return mixed
     */
    public function add_role($params,$auth = [])
    {
        try {
            DB::beginTransaction();
            $result = Role::create($params);
            if (!$result){
                throw new \Exception('DATABASE ERROR',StatusConstants::ERROR_DATABASE);
            }
            if ($auth){
                // 添加角色组权限
                $insert_data = [];
                foreach ($auth as $item){
                    $insert_data[] = [
                        'role_id' => $result->id,
                        'auth_rule_id' => $item,
                    ];
                }
                $res = RoleAuthRule::insert($insert_data);
                if (!$res){
                    throw new \Exception('DATABASE ERROR',StatusConstants::ERROR_DATABASE);
                }
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            $this->return_data['code'] = $e->getCode();
            $this->return_data['msg'] = $e->getMessage();
        }
        return $this->return_data;
    }

    /**
     * 更新角色信息
     * @param int $id
     * @param array $params
     * @return mixed
     */
    public function update_role($id,$params)
    {
        $where = [
            'id' => $id
        ];
        $result = Role::where($where)->update($params);
        if (!$result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        $this->return_data['data'] = \Common::laravel_to_array($result);
        return $this->return_data;
    }


    /**
     * 添加用户角色关联
     * @param int $user_id
     * @param array $role
     * @return mixed
     */
    public function add_user_role($user_id,array $role)
    {
        if (is_array($role) && !$role){
            $this->return_data['code'] = StatusConstants::ERROR_ILLEGAL_PARAMS;
            return $this->return_data;
        }

        try {
            DB::beginTransaction();
            if ($role){
                // 添加角色组权限
                $insert_data = [];
                foreach ($role as $item){
                    $insert_data[] = [
                        'user_id' => $user_id,
                        'role_id' => $item,
                    ];
                }
                $res = UserRole::insert($insert_data);
                if (!$res){
                    throw new \Exception('DATABASE ERROR',StatusConstants::ERROR_DATABASE);
                }
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            $this->return_data['code'] = $e->getCode();
            $this->return_data['msg'] = $e->getMessage();
        }
        return $this->return_data;
    }

    /**
     * 添加用户角色关联
     * @param int $user_id
     * @param array $role
     * @return mixed
     */
    public function update_user_role($user_id,array $role)
    {
        if (is_array($role) && !$role){
            $this->return_data['code'] = StatusConstants::ERROR_ILLEGAL_PARAMS;
            return $this->return_data;
        }
        $where = ['user_id' => $user_id];
        try {
            DB::beginTransaction();
            // 先删除再添加
            $result = UserRole::where($where)->delete();
            if (!$result){
                throw new \Exception('DATABASE ERROR',StatusConstants::ERROR_DATABASE);
            }
            if ($role){
                // 添加角色组权限
                $insert_data = [];
                foreach ($role as $item){
                    $insert_data[] = [
                        'user_id' => $user_id,
                        'role_id' => $item,
                    ];
                }
                $res = UserRole::insert($insert_data);
                if (!$res){
                    throw new \Exception('DATABASE ERROR',StatusConstants::ERROR_DATABASE);
                }
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            $this->return_data['code'] = $e->getCode();
            $this->return_data['msg'] = $e->getMessage();
        }
        return $this->return_data;
    }

}
