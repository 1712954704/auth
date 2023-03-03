<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\common\Department;
use App\Models\Hr\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends BaseController
{



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $model =  \common::getControllerName();
        $model =  "App\Models\common\\".\common::getControllerName();


        $columns = ['*'];
        $pageName = 'bio';
        $current_page = request('current_page') ? request('current_page') : 1;;
        $perPage = request('perPage') ? request('perPage') : 2;

        $result = $model::where('id', '>', 0)->paginate($perPage, $columns, $pageName, $current_page);;


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

        $result = DB::connection('mysql_hr')->table('positions')
            ->where('id', '=', $id)
            ->where('deleted_at', '=', null)
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

        try {
            // 验证...

            $pid = $request->input('pid');
            $name = $request->input('name');
            $status = $request->input('status');
            $rules = $request->input('rules');


            $result = DB::connection('mysql_hr')->table('positions')
                ->where('id', $id)
                ->update([
                    'name' => $name,
                    'status' => $status,
                    'pid' => $pid,
                    'rules' => $rules,
                ]);

            $response['code'] = $result > 0  ?'200':'404';
            $response['msg']  = $result > 0  ?'success':'更新失败';
            $response['data'] = $result ;

            return \Common::format_return_result($response['code'],$response['msg'],$response['data']);


        } catch (\Exception $e) {

            echo $e->getCode();
            report($e);


            return false;
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

        $result = Position::where('id', $id)->delete();


        $response['code'] = $result > 0  ?'200':'404';
        $response['msg']  = $result > 0  ?'success':'数据不存在';
        $response['data'] = $result ;
        return json_encode($response);

    }




}
