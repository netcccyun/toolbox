<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class cdn58 implements api
{
    public function upload($filepath, $filename){
        $url = 'https://upload.58cdn.com.cn/json/nowater/webim/big/';
        $referer = 'https://ai.58.com/pc/';
        $imgdata = base64_encode(file_get_contents($filepath));
        $params = [
            'Pic-Data' => $imgdata,
            'Pic-Encoding' => 'base64',
            'Pic-Path' => '/nowater/webim/big/',
            'Pic-Size' => '0*0'
        ];
        $data = get_curl($url,json_encode($params),$referer,0,0,0,0,['application/json']);
        if(strpos($data, 'n_v2')!==false){
            $imgurl = 'https://pic'.rand(1,8).'.58cdn.com.cn/nowater/webim/big/'.$data;
            return ['url'=>$imgurl];
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }
}