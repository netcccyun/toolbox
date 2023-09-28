<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"ret":0,"data":{"country_code":"CN","country":"\u4e2d\u56fd","province":"\u5e7f\u4e1c","city":"\u6df1\u5733","isp":"chinatelecom.com.cn","asn":["AS4134 - CHINANET-BACKBONE - No.31,Jin-rong Street, CN"],"ports":[],"ip":"113.97.33.248"},"dns":[{"country_code":"CN","country":"\u4e2d\u56fd","province":"\u5e7f\u4e1c","city":"\u6df1\u5733","isp":"chinatelecom.com.cn","asn":["AS4134 - CHINANET-BACKBONE - No.31,Jin-rong Street, CN"],"ports":[],"ip":"113.97.33.248"}]}
 */
class ipip implements api
{
    public function query($ip){
        $url = 'https://clientapi.ipip.net/browser/chrome?ip='.$ip.'&l=zh-CN';
        $data = get_curl($url);
        $arr = json_decode($data, true);
        if (isset($arr['ret']) && $arr['ret']==0) {
            return ['IP所在地'=>$arr['data']['country'].''.$arr['data']['province'].''.$arr['data']['city'], 'ISP运营商'=>$arr['data']['isp'], 'AS编号'=>$arr['data']['asn'][0]];
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}