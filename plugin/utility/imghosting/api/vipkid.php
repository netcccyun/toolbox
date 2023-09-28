<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class vipkid implements api
{
    public function upload($filepath, $filename){
        $url = 'https://www.vipkid.com/rest/gw/api/upload/vos';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'file' => $file,
            'uploadType' => 'IM'
        ];
        $data = get_curl($url,$param,$url,0,0,0,0,['vk-cr-code: kr']);
        $arr = json_decode($data,true);
        if(isset($arr['code']) && $arr['code']==200){
            return ['url'=>$arr['data']['url']];
        }elseif(isset($arr['msg'])){
            throw new Exception('上传失败请重试（'.$arr['msg'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}