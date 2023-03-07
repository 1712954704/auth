<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
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
        $job_number = 5;
        $where[] = ['type','>=',$job_number ];
        // return $where;
        $columns = ['*'];
        $user_id = $this->check_param('user_id',0);

        $page = $this->check_param('page',0);
        $limit = $this->check_param('limit',10);;
        $offset = ($page - 1) * $limit;
        $where['user_id'] = $user_id;

        $result = $this->model::index($columns,$limit,$offset,$user_id,$where);

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
