<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class ucloud implements api
{
    public function upload($filepath, $filename){
        $url = 'https://spt.ucloud.cn/im/client/upload';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'file' => $file,
        ];
        $data = get_curl($url,$param,$url,0,0,0,0,['Authorization: UCloud TOKEN_12134567890']);
        $arr = json_decode($data,true);
        if(isset($arr['Files']) && !empty($arr['Files'])){
            return ['url'=>'https://uchat.cn-bj.ufileos.com/'.$arr['Files'][0]];
        }elseif(isset($arr['Message'])){
            throw new Exception('上传失败请重试（'.$arr['Message'].'）');
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}