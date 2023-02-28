<?php
/**
 * User: Jack
 * Date: 2023/02/28
 * Email: <1712954704@qq.com>
 */
namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
//    use HasFactory;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 设置当前模型使用的数据库连接名。
     *
     * @var string
     */
    protected $connection = 'mysql_common';

    /**
     * 与模型关联的数据表.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * 与数据表关联的主键.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 指示模型是否主动维护时间戳。
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 获取用户信息的查询
     * @date 2023/02/27
     * @param $user_id
     * @param null $status
     * @param string $field
     * @return bool|mixed
     */
    public function get_user_by_id($user_id,$status = null,$field = "*")
    {
        $where['id']= $user_id;

        if($status !== null){
            $where['status']= $status;
        }
        return $this->select($field)->where($where)->get();
    }

}
