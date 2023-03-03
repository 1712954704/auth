<?php

namespace App\Models\common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory;
    use HasFactory;
    use SoftDeletes;
    protected $connection = 'mysql_common';
    protected $guarded = [];



    /**
     * 获取与用户相关的电话记录
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id','pid');
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
        return $this->child()->with('children');
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
