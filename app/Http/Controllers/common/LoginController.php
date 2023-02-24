<?php

namespace App\Http\Controllers\common;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

class LoginController extends BaseController
{

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
