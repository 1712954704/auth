<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Http\Service\Hr\AssessmentService;
use App\Models\common\Department;
use App\Models\Hr\Assessment;
use Illuminate\Http\Request;

class AssessmentController extends BaseController
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 接收参数
        $columns = ['*'];
        $user_id = $this->check_param('user_id',0);
        $page = $this->check_param('page',0);
        $limit = $this->check_param('limit',10);
        $offset = ($page - 1) * $limit;
        $test_number = 0;
        // $where['user_id'] = $user_id;
        // $where[] = ['type','>=',$test_number ];
        //
        // // 模型层处理数据库数据
        // $result = $this->model::index($columns,$limit,$offset,$where);

        // Sever层处理页面逻辑
        $AssessmentService  = new AssessmentService();
        $result = $AssessmentService->index($user_id,$limit,$offset);



        // // 返回结果
        // $response['code'] = count($result) > 0  ?'200':'404';
        // $response['msg']  = count($result) > 0  ?'success':'数据不存在';
        // $response['data'] = $result ;

        return \Common::format_return_result($result['code'],$result['msg'],$result['data']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model =  \common::getModelPath();
        $date = new $model;
        $result = $date->show($id);

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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model =  \common::getModelPath();
        $result = $model::where('id', $id)->delete();

        $response['code'] = $result > 0  ?'200':'404';
        $response['msg']  = $result > 0  ?'success':'数据不存在';
        $response['data'] = $result ;
        return json_encode($response);
    }
}
