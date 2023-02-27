<?php

namespace App\Http\Controllers;

use App\Http\Controllers\common\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeesController extends Controller
{
    //
    /**
     * 显示给定用户的个人资料。
     *
     * @param  int  $id
     * @return false|string
     */
    public function show($id)
    {

        $user = DB::connection('mysql_hr')->table('hr_usernew')->find($id);

        $response['data'] = ['name'=>"jason",'age'=>"18",'id'=>$id];

        $response['data'] = $user ;
        $response['status'] = '2';
        $response['msg'] = 'success';
        return json_encode($response);

    }
    public function add()
    {
        return 123;

    }
}
