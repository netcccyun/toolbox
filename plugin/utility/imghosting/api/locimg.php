<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class locimg implements api
{
    public function upload($filepath, $filename){
        $url = 'https://www.locimg.com/upload/upload.html';
        $referer = 'https://www.locimg.com/';
        $file = new \CURLFile($filepath, 'image/jpeg', $filename);
        $param = [
            'image' => $file,
            'fileId' => $filename,
        ];
        $data = get_curl($url,$param,$referer,0,0,0,0,['X-Requested-With: XMLHttpRequest']);
        $arr = json_decode($data,true);
        if(isset($arr['data']['url'])){
            return ['url'=>$arr['data']['url']];
        }elseif(isset($arr['msg'])){
            throw new Exception('上传失败请重试（'.$arr['msg'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}