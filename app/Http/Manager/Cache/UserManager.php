<?php
namespace App\Http\Manager\Cache;

use App\Http\Manager\ManagerBase;
use library\Constants\Model\UserConstants;

class UserManager extends ManagerBase
{

    /**
     * 获取用户信息key
     *
     * @param $user_id
     *
     * @return string
     */
    public function get_user_cache_key($user_id)
    {
//        $user_id = \CacheConstants::HASH_USER_INFO_PREFIX . $user_id;
        $user_id = UserConstants::HASH_USER_INFO_PREFIX . $user_id;
        return $user_id;
    }


    /**
     * 获取用户权限信息key
     *
     * @param $user_id
     *
     * @return string
     */
    public function get_user_auth_cache_key($user_id)
    {
//        $user_id = \CacheConstants::HASH_USER_AUTH_INFO_PREFIX . $user_id;
        $user_id = UserConstants::HASH_USER_AUTH_INFO_PREFIX . $user_id;
        return $user_id;
    }

    /**
     * 生成token key
     *
     * @param $token
     *
     * @return string|array
     */
    public function get_token_key($token) {
        if (is_array($token)) {
            foreach ($token as &$v) {
                $v = UserConstants::CACHE_REDIS_TOKEN_KEY_PREFIX . $v;
            }
        }
        else {
            $token = UserConstants::CACHE_REDIS_TOKEN_KEY_PREFIX . $token;
        }
        return $token;
    }


}
