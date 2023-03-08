<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Hr\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use library\Constants\StatusConstants;
use Predis\Command\Redis\DUMP;

class DepartmentController extends Controller
{



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $model =  "App\Models\common\\".\common::getControllerName();


        $columns = ['*'];
        $pageName = 'bio';
        $current_page = request('current_page') ? request('current_page') : 1;;
        $perPage = request('perPage') ? request('perPage') : 10;
        $pid = request('pid') ;
        $structure_id = request('structure_id') ;
        $id = request('id') ;
        $group_type = request('group_type') ;
        $group_type_child = [];

        // $whereIn = [1,2];
        if ($group_type){
            // $whereIn = [$group_type];
            $group_type_child['group_type'] = $group_type;
            $where['group_type'] = $group_type;
        }

        define('GROUP_TYPE', $group_type_child); // 定义当前系统类型


        $where['pid'] = $pid;

        $result = $model::where($where)
            ->select('id','name','structure_id','pid','encode','order','created_at','updated_at','leader')
            ->with(['children:id,name,structure_id,pid,encode,order,created_at,updated_at,leader'])
            ->with(['leader:account,id'])
            ->orderBy('order', 'desc')
            ->paginate($perPage, $columns, $pageName, $current_page);




        $response['code'] = count($result) > 0  ?'200':'404';
        $response['msg']  = count($result) > 0  ?'success':'数据不存在';
        $response['data'] = $result ;

        return \Common::format_return_result($response['code'],$response['msg'],$response['data']);





    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {




        try {

            // 验证...
            $model =  "App\Models\common\\".\common::getControllerName();

            $pid = $request->input('pid')?$request->input('pid'):0;
            $name = $request->input('name');
            $order = $request->input('order');
            $encode = $request->input('encode');
            $structure_id = $request->input('structure_id');
            $group_type = $request->input('group_type');
            $leader = $request->input('leader');

            if (!$name or !$group_type){
                return \Common::format_return_result(404,'缺少参数','400');
            }





            //增加
            $result = $model::create([
                'name' => $name,
                'pid' => $pid,
                'encode' => $encode,
                'order' => $order,
                'structure_id' => $structure_id,
                'group_type' => $group_type,
                'leader' => $leader,
            ]);

            $response['code'] = 200;
            $response['msg']  = 'success';
            $response['data'] = $result ;

            return \Common::format_return_result($response['code'],$response['msg'],$response['data']);

        } catch (\Throwable $e) {
            // \dump($e);
            return \Common::format_return_result($e->getCode(),$e->getMessage(),[]);
        }





    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model =  "App\Models\common\\".\common::getControllerName();

        $result = $model::where('id', '=', $id)
            ->select('id','name','structure_id','pid','encode','order','group_type','leader')
            ->first();

        $array=explode(",",$result->rules);
        $result->test_date=$array;


        $response['code'] = $result->id > 0  ? '200':'404';
        $response['msg']  = $result->id > 0  ? 'success':'数据不存在';
        $response['data'] = $result ;

        return \Common::format_return_result($response['code'],$response['msg'],$response['data']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $model =  "App\Models\common\\".\common::getControllerName();

        try {
            // 验证...

            $pid = $request->input('pid');
            $name = $request->input('name');
            $status = $request->input('status');
            $order = $request->input('order');
            $encode = $request->input('encode');
            $structure_id = $request->input('structure_id');
            $leader = $request->input('leader');


            $result = $model::where('id', $id)
                ->update([
                    'name' => $name,
                    'status' => $status,
                    'pid' => $pid,
                    'encode' => $encode,
                    'order' => $order,
                    'structure_id' => $structure_id,
                    'leader' => $leader,
                ]);

            $response['code'] = $result > 0  ?'200':'404';
            $response['msg']  = $result > 0  ?'success':'更新失败';
            $response['data'] = $result ;

            return \Common::format_return_result($response['code'],$response['msg'],$response['data']);


        } catch (\Exception $e) {

            $response['code'] = StatusConstants::ERROR_DATA_NUMERIC_VALUE_EXIST;
            return \Common::format_return_result($response['code'],'','');

 ;
        }





    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model =  "App\Models\common\\".\common::getControllerName();
        $result = $model::where('id', $id)->delete();

        $response['code'] = $result > 0  ?'200':'404';
        $response['msg']  = $result > 0  ?'success':'数据不存在';
        $response['data'] = $result ;
        return json_encode($response);

    }




}
