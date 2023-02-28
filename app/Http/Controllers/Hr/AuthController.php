<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseController;

class AuthController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 添加路由规则
     */
    public function add_rule()
    {
        // 验证 ip地址 (公司内部使用)

        // 验证登录信息 (用户名,密码)

        // 刷新用户信息缓存

    }

}
