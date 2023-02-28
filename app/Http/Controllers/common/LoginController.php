<?php

namespace App\Http\Controllers\common;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Predis\Command\Redis\AUTH;

class LoginController extends Controller
{

    /**
     * 登录
     */
    public function login(Request $request)
    {
        // 验证 ip地址 (公司内部使用)

        // 验证登录信息 (用户名,密码)


        // $data = array();
        // $data['username'] = Session::get('user_login');
        // $data['time'] = Redis::get('STRING_SINGLETOKEN_MAJOR_' . $data['username']);
        // $data['token'] = Redis::get('SINGLETOKEN_MAJOR_' . $data['username']);

        // $info['time']=$request->input('time');
        // $info['username']=$request->input('username');
        // $info['token']=$request->input('token');

        // 获取post用户名和密码

        // public function signin(Request $request){
        $username = $request->input('username');
        $password = $request->input('password');
        $password = md5($password);


        // 查询数据库，校验用户名和密码
        if($username==''){
            exit(json_encode(array('code'=>401,'msg'=>'用户名不能为空')));
        }
        if($password==''){
            exit(json_encode(array('code'=>401,'msg'=>'密码不能为空')));
        }
        // 验证用户名和密码（数组里面的key必须要对应数据表中的字段名，要求一模一样）
        // if(!Auth::attempt(['username'=>$username,'password'=>$password])){
        //     exit(json_encode(array('code'=>401,'msg'=>'用户名或者密码错误')));
        // }

        $user = DB::connection('mysql_common')
            ->table('user')
            ->where('account', '=', $username)
            ->where('pwd', '=', $password)
            ->first();


        // 设定返回接口code 1 假的 2 真的
        $response['code']  = $user?'2':'1';
        $response['msg']   = $user?'success':'账号或密码错误';
        $response['data']  = $user ;
        $string = md5(uniqid());
        $response['token'] = $string;



        // 刷新用户信息缓存
        Redis::set('token_id', $username);
        Redis::set('token_key', $string);

        return json_encode($response);


    }

}
