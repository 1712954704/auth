<?php

namespace App\Models\Hr;

use App\Models\common\Department;
use App\Models\Common\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function MongoDB\BSON\toJSON;

class Assessment extends Model
{
    use HasFactory;
    use HasFactory;
    use SoftDeletes;
    protected $connection = 'mysql_hr';
    protected $guarded = [];

    protected $casts = [
        'updated_at'  => 'date:Y-m-d',
        'created_at' => 'datetime:Y-m-d',
    ];

    // 查询列表
    public function index($columns,$perPage,$current_page){

        $result = Department::select($columns)
            ->orderBy('order', 'desc')
            ->paginate($perPage, $columns, '', $current_page);
        $result->first_page_url ='2';
        return $result;
    }

    /**
     * 获取与用户相关的电话记录
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id','pid');
    }
    /**
     * 获取与用户相关的名称
     */
    public function leader()
    {
        return $this->hasOne(User::class, 'id','leader');
    }
    /**
     * 获取与用户相关的电话记录
     */
    public function pid()
    {
        return $this->hasOne(Department::class, 'id','pid');
    }

    public function child()
    {
        return $this->hasMany(self::class,'pid');
    }

    // 递归子级
    public function children()
    {
        return $this->child()->with('children:id,name,structure_id,pid,encode,order,created_at,updated_at');
    }

    public function father()
    {
        return $this->hasMany(self::class,'id','pid');
    }

    // 递归父级
    public function parents()
    {
        return $this->father()->with('parents');
    }

}
