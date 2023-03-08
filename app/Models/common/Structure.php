<?php
/**
 * User: Jack
 * Date: 2023/03/1
 * Email: <1712954704@qq.com>
 */
namespace App\Models\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use library\Constants\Model\ModelConstants;

class Structure extends Authenticatable
{
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
    protected $table = 'structure';

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
     * 使用关联模型获取顶级分类
     */
    public function subset() {
        return $this->hasMany(get_class($this), 'pid' ,'id');
    }

    /**
     * 获取所有子集分类
     */
    public function child() {
        $fields = ['id','name','type','pid','group_type'];
        return $this->subset()->with( 'child' )->where(['status'=>ModelConstants::COMMON_STATUS_NORMAL])->select($fields);
    }


    public function childhood()
    {
        return $this->hasMany(self::class,'pid');
    }

    // 递归子级
    public function children()
    {
        // return $this->child()->with('children:id,name,structure_id,pid,encode,order,created_at,updated_at');
        return $this->childhood()->with('children:id,name,pid,code,order,created_at,updated_at')->where(GROUP_TYPE);
    }

    /**
     * 获取与用户相关的名称
     */
    public function leader()
    {
        return $this->hasOne(User::class, 'id','leader');
    }

}
