<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\common\Department;
use App\Models\Hr\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use library\Constants\StatusConstants;
use Predis\Command\Redis\DUMP;

class DepartmentController extends BaseController
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
        $perPage = request('perPage') ? request('perPage') : 2;
        $pid = request('pid') ;
        $structure_id = request('structure_id') ;
        $id = request('id') ;


        $result = $model::where('structure_id',  $structure_id )
            ->where('id',  $id )
            ->where('pid',  $pid )
            ->select('id','name','structure_id','pid','encode','order')
            ->with(['children:id,name,structure_id,pid,encode,order'])
            ->orderBy('order', 'desc')
            ->paginate($perPage, $columns, $pageName, $current_page);;


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

        $model = "App\Models\Hr\\"."Position";

        $pid = $request->input('pid');
        $name = $request->input('name');
        $rules = $request->input('rules');

        if (!$name){
            return \Common::format_return_result(404,'缺少参数','400');
        }

        //增加
        $result = $model::create([
            'pid' => $pid,
            'name' =>$name,
            'rules' =>$rules,
        ]);



        $response['code'] = 200;
        $response['msg']  = 'success';
        $response['data'] = $result ;

        return \Common::format_return_result($response['code'],$response['msg'],$response['data']);

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
            ->select('id','name','structure_id','pid','encode','order')
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


            $result = $model::where('id', $id)
                ->update([
                    'name' => $name,
                    'status' => $status,
                    'pid' => $pid,
                    'encode' => $encode,
                    'order' => $order,
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
