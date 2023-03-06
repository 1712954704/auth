<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Service\Hr;

use App\Http\Service\ServiceBase;
use App\Models\Common\Structure;
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
     * @param array $params
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_role($params,$limit,$offset)
    {
        $name = $params['name'];
        $department_id = $params['department_id'];
        $where = [
            'status' => [1,2]
        ];
        if ($name){
            $where[] = ['name','like','%' .$name .'%'];
        }
        if ($department_id){
            $where['department_id'] = $department_id;
        }

        $fields = ['id','name','pid','type','code','department_id','status','created_at'];
        $result = Role::where($where)->limit($limit)->offset($offset)->select($fields)->get();
        $this->return_data['data']['total'] = Role::where($where)->select($fields)->count();
        if (!$result){
            $this->return_data['code'] = StatusConstants::ERROR_DATABASE;
        }
        $this->return_data['data']['data'] = \Common::laravel_to_array($result);
        // 查询所有部门名称
        $department_ids = array_column($this->return_data['data']['data'],'department_id');
        $department_list = Structure::whereIn('id',$department_ids)->select(['id','name'])->get();
        $department_list = array_column(\Common::laravel_to_array($department_list),'name','id');
        foreach ($this->return_data['data']['data'] as &$item){
            $item['department_name'] = $department_list[$item['department_id']] ?? '';
        }
        return $this->return_data;
    }

    /**
     * 添加角色
     * @param array $params
     * @param array $auth
     * @return mixed
     */
//    public function add_role($params,$auth = [])
    public function add_role($params)
    {
        try {
            DB::connection('mysql_hr')->beginTransaction();
            $result = Role::create($params);
            if (!$result){
                throw new \Exception('DATABASE ERROR',StatusConstants::ERROR_DATABASE);
            }
//            if ($auth){
//                // 添加角色组权限
//                $insert_data = [];
//                foreach ($auth as $item){
//                    $insert_data[] = [
//                        'role_id' => $result->id,
//                        'auth_rule_id' => $item,
//                    ];
//                }
//                $res = RoleAuthRule::insert($insert_data);
//                if (!$res){
//                    throw new \Exception('DATABASE ERROR',StatusConstants::ERROR_DATABASE);
//                }
//            }
            DB::connection('mysql_hr')->commit();
        }catch (\Exception $e){
            DB::connection('mysql_hr')->rollBack();
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
