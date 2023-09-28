<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"status":"0","t":"","set_cache_time":"","data":[{"ExtendedLocation":"","OriginQuery":"113.97.33.248","appinfo":"","disp_type":0,"fetchkey":"113.97.33.248","location":"广东省深圳市 电信","origip":"113.97.33.248","origipquery":"113.97.33.248","resourceid":"6006","role_id":0,"shareImage":1,"showLikeShare":1,"showlamp":"1","titlecont":"IP地址查询","tplt":"ip"}]}
 */
class baidu implements api
{
    public function query($ip){
        $url = 'https://sp0.baidu.com/8aQDcjqpAAV3otqbppnN2DJv/api.php?query='.$ip.'&resource_id=6006&ie=utf8&format=json';
        $data = get_curl($url);
        $data = mb_convert_encoding($data, 'UTF-8', 'GBK');
        $arr = json_decode($data, true);
        if (isset($arr['data']) && count($arr['data'])>0) {
            return ['IP所在地'=>$arr['data'][0]['location']];
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}