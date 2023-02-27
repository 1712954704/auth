<?php
namespace App\Http\Manager\Cache;

use App\Http\Manager\ManagerBase;

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
//        if (is_array($user_id)) {
//            foreach ($user_id as &$v) {
//                $v = \CacheConstants::HASH_USER_INFO_PREFIX . $v;
//            }
//        } else {
//            $user_id = \CacheConstants::HASH_USER_INFO_PREFIX . $user_id;
//        }
        $user_id = \CacheConstants::HASH_USER_INFO_PREFIX . $user_id;
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
        $user_id = \CacheConstants::HASH_USER_AUTH_INFO_PREFIX . $user_id;
        return $user_id;
    }


}
