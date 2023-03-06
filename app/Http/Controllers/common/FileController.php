<?php

namespace App\Http\Controllers\common;

use App\Http\Controllers\BaseController;
use App\Models\Common\File;
use library\Constants\StatusConstants;

class FileController extends BaseController
{
    // 图片允许的后缀名
    protected $allow_postfix_img = ["gif", "jpeg", "jpg", "png","image/jpeg"];

    /**
     * 图片上传
     */
    public function add_file()
    {
        $user_id = $this->user_info['id'];
        $data = [
            'code' => StatusConstants::SUCCESS,
            'msg' => 'ok',
            'data' => [],
        ];
        switch($this->method) {
            case 'POST':
                try {
                    $duration = $this->data_arr['duration'] ?? '';
                    $my_config = \Common::get_config();

                    $arr = explode('.',$_FILES['file']['name']);
                    $postfix = array_pop($arr);  // 获取文件后缀名
                    if (!in_array($postfix,$this->allow_postfix_img)){
                        throw new \Exception('postfix not allow',StatusConstants::ERROR_INVALID_PARAMS);
                    }
                    // 生成文件名,转换base64为图片并保存文件,文件夹是否存在,不存在则创建
                    $file_id = \Common::guid();
                    $date_url = date('Ymd').'/';
                    $imgName = $file_id.'.'.$postfix;
                    $path = $my_config['img_file'] . $date_url;
                    if (!file_exists($path)){
                        mkdir($path,0777,true);
                    }
                    // 移动文件到指定位置
                    $move_res = move_uploaded_file($_FILES["file"]["tmp_name"],$path.$imgName);
                    if (!$move_res){
                        throw new \Exception('图片上传失败',StatusConstants::ERROR_DATABASE);
                    }
                    $file_url = $my_config['img_domain_url'].$date_url.$imgName;
                    // 入库
                    $res = File::insert(array(
                        'user_id' => $user_id,
                        'content_type' => 1, // 1 图片；2 视频3 音频 4文件
                        'file_id' => $file_id,
                        'file_url' => $file_url,
                        'duration' => $duration,
                        'status' => 2, // 1：创建中 2：已上传
                    ));
                    if(!$res){
                        throw new \Exception('数据写入失败');
                    }

                    $data['data'] =  [
                        'file_id' => $file_id,
                        'file_url' => $file_url
                    ];
                }catch(\Exception $e){
                    $code = $e->getCode();
                    $msg = $e->getMessage();
                    if (in_array($code,array_keys(StatusConstants::STATUS_TO_CODE_MAPS))){
                        $return_data['code'] = $code;
                    }else{
                        $return_data['code'] = StatusConstants::ERROR_DATABASE;
                    }
                    $return_data['msg'] = $msg;
                }
                break;
            default:
                \Common::format_return_result(StatusConstants::ERROR_INVALID_REQUEST_METHOD,'',[]);
        }
        return \Common::format_return_result($data['code'], $data['msg'], $data['data']);
    }

    /**
     * 图片上传(base64)
     */
    public function add_base64_file()
    {
        $user_id = $this->user_info['id'];

        switch($this->method) {
            case 'POST':
                try {
                    $json_raw = file_get_contents('php://input');
                    $params = json_decode($json_raw,true);
                    $base64 = $params['base64'] ?? '';
                    $duration = $params['duration'] ?? '';
                    if (!$base64){
                        \Common::format_return_result(StatusConstants::ERROR_INVALID_PARAMS,'field base64 not found',[]);
                    }

                    // 获取图片后缀名
                    if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64, $result)){
                        \Common::format_return_result(StatusConstants::ERROR_INVALID_PARAMS,'base64 code error',[]);
                    }
                    $postfix = $result[2];
                    // 替换前端传值
                    $base64 = str_replace('data:image/'.$postfix.';base64,', '', $base64);
                    // 生成文件名,转换base64为图片并保存文件,文件夹是否存在,不存在则创建
                    $file_id = \Common::guid();
                    $date_url = date('Ymd').'/';
                    $imgName = $file_id.'.'.$postfix;
                    $path = IMG_FILE.$date_url;
                    if (!file_exists($path)){
                        mkdir($path,0777,true);
                    }
                    $current = file_put_contents($path.$imgName, base64_decode($base64));
                    if($current == false){
                        throw new Exception('图片上传失败');
                    }
                    $my_config = \Common::get_config();
                    $file_url = $my_config['img_domain_url'].$date_url.$imgName;
                    // 入库
                    $res = File::insert(array(
                        'user_id' => $user_id,
                        'content_type' => 1, // 1 图片；2 视频3 音频 4文件
                        'file_id' => $file_id,
                        'file_url' => $file_url,
                        'duration' => $duration,
                        'status' => 2, // 1：创建中 2：已上传
                    ));
                    if(!$res){
                        throw new Exception('数据写入失败');
                    }
                    \Common::format_return_result(StatusConstants::SUCCESS,'ok',[
                        'file_id' => $file_id,
                        'file_url' => $file_url,
                    ]);
                }catch(Exception $e){
                    \Common::format_return_result(StatusConstants::ERROR_DATABASE,$e->getMessage(),[]);
                }
                break;
            default:
                \Common::format_return_result(StatusConstants::ERROR_INVALID_REQUEST_METHOD,'',[]);
        }
    }

}
