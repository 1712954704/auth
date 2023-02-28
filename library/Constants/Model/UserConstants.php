<?php
/**
 * 用户配置
 * User: Jack
 * Date: 2023/02/27
 */

namespace library\Constants\Model;

class UserConstants extends ModelConstants
{
    const HASH_USER_INFO_PREFIX = self::MODULE_USER . 'info:';                  // 用户信息缓存 user:info:用户id
    const HASH_USER_AUTH_INFO_PREFIX = self::MODULE_USER . 'auth:info:';        // 用户权限信息缓存 user:auth:info:用户id

    // token key前缀
    const CACHE_REDIS_TOKEN_KEY_PREFIX = 'token:';
}
