<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;
use think\helper\Str;

class pngcm implements api
{
    public function upload($filepath, $filename){
        $url = 'https://png.cm/app/upload.php';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'name' => $filename,
            'uuid' => 'o_'.Str::random(27),
            'sign' => time(),
            'file' => $file,
        ];
        $data = get_curl($url,$param,'https://png.cm/');
        $arr = json_decode($data,true);
        if(isset($arr['code']) && $arr['code']==200){
            return ['url'=>$arr['url']];
        }elseif(isset($arr['message'])){
            throw new Exception('上传失败请重试（'.$arr['message'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}