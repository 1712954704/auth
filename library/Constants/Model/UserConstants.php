<?php
/**
 * 用户配置
 * User: Jack
 * Date: 2023/02/27
 */

namespace library\Constants\Model;

class UserConstants extends ModelConstants
{

    /**
     * 通用常量
    */
    const USER_LOGIN_LIMIT_TYPE_ERROR = 1; //用户登录失败
    const USER_LOGIN_LIMIT_TYPE_SUCCESS = 2; //用户登录、登出成功
    const USER_LOGIN_LIMIT_TYPE_INFO = 3; //用户登录信息

    /**
     * 数据状态常量
    */
    const COMMON_STATUS_LOCK = 2;      // 状态 - 锁定
    const COMMON_STATUS_DISABLE = 3;      // 状态 - 禁用


    /**
     * redis缓存
    */
    const HASH_USER_INFO_PREFIX = \CacheConstants::MODULE_USER . 'info:';                  // 用户信息缓存 user:info:用户id
    const HASH_USER_AUTH_PREFIX = \CacheConstants::MODULE_USER . 'auth:';          // 用户权限信息缓存 user:auth:用户id
    const HASH_USER_ROUTE_PREFIX = \CacheConstants::MODULE_USER . 'route:';        // 用户路由表信息缓存(和前端对照使用) user:route:用户id

    // token key前缀
    const CACHE_REDIS_TOKEN_KEY_PREFIX = 'token:';

    //用户登录封禁限制 %s(1) 登录账号
    const HASH_USER_LOGIN_LIMIT = \CacheConstants::MODULE_USER.'user_login_limit:%s';
}
