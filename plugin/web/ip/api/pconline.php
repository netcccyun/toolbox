<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"ip":"218.89.171.143","pro":"四川省","proCode":"510000","city":"成都市","cityCode":"510100","region":"","regionCode":"0","addr":"四川省成都市 电信","regionNames":"","err":""}
 */
class pconline implements api
{
    public function query($ip){
        $url = 'http://whois.pconline.com.cn/ipJson.jsp?json=true&ip='.$ip;
        $data = get_curl($url);
        $data = mb_convert_encoding($data, "UTF-8", "GB2312");
        $arr = json_decode($data, true);
        if (isset($arr['addr'])) {
            return ['IP所在地'=>$arr['addr']];
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}