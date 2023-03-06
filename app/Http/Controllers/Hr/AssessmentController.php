<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\common\Department;
use App\Models\Hr\Assessment;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $model =  \common::getModelPath();
        $columns = ['id','name','structure_id','pid','encode','order','created_at','updated_at','leader'];
        $current_page = request('current_page') ? request('current_page') : 1;;
        $perPage = request('perPage') ? request('perPage') : 2;

        $date = new $model;
        $result = $date->index($columns,$perPage,$current_page);

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
        //
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
        //
    }
}
