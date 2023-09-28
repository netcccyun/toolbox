<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class imgdd implements api
{
    public function upload($filepath, $filename){
        $url = 'https://imgdd.com/api/v1/upload';
        $referer = 'https://imgdd.com/';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'image' => $file,
        ];
        $data = get_curl($url,$param,$referer);
        $arr = json_decode($data,true);
        if(isset($arr['url'])){
            return ['url'=>$arr['url']];
        }elseif(isset($arr['message'])){
            throw new Exception('上传失败请重试（'.$arr['message'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}