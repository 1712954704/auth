<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Hr\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use App\Exceptions\InvalidOrderException;

class PositionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        echo "index";
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
        // \Common::guid();
        // 检索，如果重复，会抛 `MultipleRecordsFoundException` 异常，并断言重复的条数...
        $result  =  Position::where(['id'  =>  $id])->sole();

        $response['code'] = $result?'2':'200';
        $response['msg']  = $result?'success':'数据不存在';
        $response['data'] = $result ;
        return json_encode($response);
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

        // $result = Position::find($id);
        // $result->deleted_at  = 'Paris to London';
        // $result->save();
        $result  =  Position::destroy($id);

        $response['code'] = $result?'2':'200';
        $response['msg']  = $result?'success':'数据不存在';
        $response['data'] = $result ;
        return json_encode($response);

    }
}
