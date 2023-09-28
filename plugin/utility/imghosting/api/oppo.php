<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class oppo implements api
{
    public function upload($filepath, $filename){
        $url = 'https://api.open.oppomobile.com/api/utility/upload';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'file' => $file,
            'type' => 'feedback',
        ];
        $data = get_curl($url,$param,$url);
        $arr = json_decode($data,true);
        if(isset($arr['errno']) && $arr['errno']==0){
            return ['url'=>str_replace('store2.heytapimage.com', 'store.heytapimage.com', $arr['data']['url'])];
        }elseif(isset($arr['data']['message'])){
            throw new Exception('上传失败请重试（'.$arr['data']['message'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}