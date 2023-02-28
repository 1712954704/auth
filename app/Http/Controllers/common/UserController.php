<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Http\Controllers\Common;


use App\Http\Controllers\BaseController;
use App\Http\Service\Hr\RoleService;
use App\Http\Service\ServiceBase;
use App\Http\Service\common\UserService;
use App\Models\common\UserToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;


class UserController extends BaseController
{

    public function __construct()
    {
//        $path = Request::path();
//        $length = strripos($path,'/');
//        $function_name = substr($path,$length+1);
//        if ($function_name == 'login'){
//            $this->is_login = 0;
//        }
        parent::__construct();
    }

    /**
     *test
    */
    public function home()
    {
//        $role_service = new RoleService();
//        $data['name']           = $this->check_param('name');
//        $data['is_menu']        = $this->check_param('is_menu');  // 是否菜单
//        $data['title']          = $this->data_arr['title'] ?? '';
//        $data['remark']         = $this->data_arr['remark'] ?? '';
////        $data['pid']            = $this->data_arr['pid'] ?? 0;
//        $data['pid']            = $this->check_param('pid',0);
//        $auth                   = $this->check_param('auth',[1,2]);
//        $data = $role_service->add_role($data,$auth);
//        var_dump($data);die();

        $user = new ServiceBase();
        $redis = $user->get_redis();
        $data = ['hr' => ['api/home'=>'get','api/login'=>'post']];
        // 数组转json存储
        foreach($data as &$item){
            $item = json_encode($item);
        }
//        $redis->hMSet('test_auth_key',$data);
        $list = $redis->hGetAll('test_auth_key');
        // 解码
        foreach ($list as &$value){
            $value = json_decode($value,true);
        }
        var_dump($list['hr']['api/home']);die();


//        Redis::hmset('test_key',['type'=>'1']);

//        $res = Redis::hmget('runoobkey','name');
        $fields = ['name','type'];
        $res = Redis::hMGet('test_key',$fields);
//        $res = Redis::hmget('runoobkey',$fields);
        var_dump($res);die();

        $where = [
            'token' => '12'
        ];
        $list = UserToken::where($where)->first();
        $list = \Common::laravel_to_array($list);
//        $list = UserToken::where($where)->get()->toArray();
        var_dump($list);die();


        $token = 'runoobkey';
        $result = resolve(UserService::class)->get_user_info_by_token($token);
        var_dump($result);die();

//        resolve(UserService::class)->test();die();
//
//        $user_service = app()->make(UserService::class);
//        $user_service->test();
        die();
//        $list = User::get()->toArray();
//        $list = User::find(1);

//        var_dump($list);die();
//        $url = \Common::get_config('url');
//        var_dump($url);
//        die();

        $user_id = 1;

        $res = URL::getRequest();
//        $res = Request::url();
        $path = Request::path();
//        dd($res);
//        var_dump($path);die();


        $length = strripos($path,'/');
        $function_name = substr($path,$length+1);
        $name = __FUNCTION__;
        var_dump($name);
        var_dump($function_name);exit();
        var_dump($length);exit();

//        $list = DB::select('select * from hr_user where user_id = '.$user_id);
//        $list = DB::select('select * from hr_role_auth_rule,hr_role,hr_user_role where user_id = '.$user_id);
        $role_list = DB::select('select * from hr_user_role where user_id = '.$user_id);  // 查询角色
//        $role_list = $this->object_array($role_list);

        $role_ids = []; // 角色id
        foreach ($role_list as $item){
//            var_dump($item['role_id']);die();
//            $role_ids = array_merge($role_ids, $item['role_id']);
            array_push($role_ids, $item->role_id);
        }
        $role_ids = implode(',',array_unique($role_ids));
        $rule_list = DB::select('select b.name from hr_role_auth_rule a join hr_auth_rule b on a.auth_rule_id = b.id where a.role_id in ('.$user_id.')');  // 查询权限

        $rules = [];
        foreach ($rule_list as $value){
            array_push($rules, $value->name);
        }
        $rules = array_unique($rules);
//        var_dump(in_array($path,$rules));die();
        if (in_array($path,$rules)){
//            return true;
            return 1;
        }
//        return false;
        return 2;
    }

    public function object_array($array) {
        if(is_object($array)) {
            $array = (array)$array;
        } if(is_array($array)) {
            foreach($array as $key=>$value) {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }

    /**
     * 登录
    */
    public function login()
    {
        // 验证 ip地址 (公司内部使用)

        // 验证登录信息 (用户名,密码)

        // 刷新用户信息缓存

    }

}
