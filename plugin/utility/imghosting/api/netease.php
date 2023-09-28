<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class netease implements api
{
    public function upload($filepath, $filename){
        $url = 'http://upload.buzz.163.com/picupload';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'file' => $file,
            'from' => 'neteasecode_mp',
        ];
        $data = get_curl($url,$param,$url);
        $arr = json_decode($data,true);
        if(isset($arr['code']) && $arr['code']==200){
            return ['url'=>str_replace('http://','https://',$arr['data']['url'])];
        }elseif(isset($arr['msg'])){
            throw new Exception('上传失败请重试（'.$arr['msg'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}