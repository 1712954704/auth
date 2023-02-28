<?php
/**
 * 这里的都是是不太敏感的配置，优先使用my_config.ini里的配置
 * 注意类型要与my_config.ini一致，以免出bug
 */

//线上环境-api-域名常量
//define('PROD_API_MICRO_WORLD_URL', 'https://api.onemicroworld.com');

return [
    /** 系统内部相关配置 */
    'default_time_zone' => 'PRC', // 默认时区

    'enable_record_timeout_sql' => 1, // 开启记录超时sql
    'sms_mob_limit' => 10, // 针对手机号获取验证码次数限制
    'sms_mob_limit_expire' => 86400, // 针对手机号获取验证码次数限制key过期时间
    'sms_ip_limit' => 100, // 针对ip获取验证码次数限制
    'sms_ip_limit_expire' => 86400, // 针对ip获取验证码次数限制key过期时间
    'system_type' => [
        1 => 'hr'
    ],   // 系统类型对照表
    'no_login' => [
        'api/login'
    ],  // 免登录接口

    /** 业务配置 */
    'is_singapore'                       => 0,
    //用户安全
    'user_safe'=>[
        'login_lock_num'                      => 3,  // 用户密码登录错误超过锁定次数
        'login_fail_lock_time'=>CacheConstants::CACHE_EXPIRE_TIME_TWELVE_HOURS,//登录失败账号锁定12小时
    ],

];
