<?php

namespace App\Models\Hr;

use App\Models\Common\Department;
use App\Models\Common\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use function MongoDB\BSON\toJSON;
use function Symfony\Component\Mime\Header\get;

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
    public static function index($columns,$limit,$offset,$where){

        // offset 设置从哪里开始
        // limit 设置想要查询多少条数据
        $model =  \common::getModelPath();
        $result['data'] = $model::select($columns)
            ->where($where)
            ->orderBy('id', 'desc')
            ->with(['user:account,id'])
            ->limit($limit)
            ->offset($offset)
            ->get();
       // $result['total'] =  $model::where($where)->count();
       $result['total'] =  $model::where($where)->count();
        return $result;
    }
    /**
     * 获取考评的所有绩效
     */
    public function assessment_detail()
    {
        return $this->hasMany(Assessments_detail::class, 'assessment_id');
    }


    // 远程一对多
    public function deployments()
    {

        // 第一个参数是我们最终想要访问的模型的名称
        // 第二个参数是中间模型的名称
        // 第三个参数是中间表的外键名
        // 第四个参数是最终想要访问的模型的外键名
        // 第五个参数是当前模型的本地键名
        // 第六个参数是中间模型的本地键名
        return $this->hasManyThrough(
            Deployment::class,
            Environment::class,
            'project_id', // environments 表的外键名
            'environment_id', // deployments 表的外键名
            'id', // projects 表的本地键名
            'id' // environments 表的本地键名
        );
    }

    public function show($id){

        $model =  \common::getModelPath();
        $result = $model::where('id', '=', $id)
            ->select('*')
            ->with(['user:account,id,structure_id'])
            ->with(['assessment_detail:id,assessment_id'])
            ->first();


        return $result;

    }

    /**
     * 获取与用户相关的电话记录
     */
    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id');
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
