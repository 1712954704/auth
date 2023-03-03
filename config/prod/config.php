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
        'api/user/login',
        'api/user/clear_user_lock',
        'api/user/register'
    ],  // 免登录接口

    /** 业务配置 */
    'is_singapore'                       => 0,
    //用户安全
    'user_safe'=>[
        'default_password'                    => 'password@123',  // 用户密码(默认值)
        'login_verify_num'                    => 3,  // 用户密码登录错误超过次数开启验证码
        'login_lock_num'                      => 6,  // 用户密码登录错误超过锁定次数
        'login_fail_lock_time'=>CacheConstants::CACHE_EXPIRE_TIME_TWELVE_HOURS,//登录失败账号锁定12小时
    ],

    // 上传相关
    'img_domain_url'=>'https://www.bio-cloud.com.cn/img/', // 图片域名地址
    'img_file' => dirname(__FILE__) . '/../../file/img/', // 用于后台上传图片确定存储位置

];
