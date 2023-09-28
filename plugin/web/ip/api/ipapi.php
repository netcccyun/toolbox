<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"status":"success","country":"加拿大","countryCode":"CA","region":"QC","regionName":"Quebec","city":"蒙特利尔","zip":"H1K","lat":45.6085,"lon":-73.5493,"timezone":"America/Toronto","isp":"Le Groupe Videotron Ltee","org":"Videotron Ltee","as":"AS5769 Videotron Telecom Ltee","query":"24.48.0.1"}
 */
class ipapi implements api
{
    public function query($ip){
        $url = 'http://ip-api.com/json/'.$ip.'?lang=zh-CN';
        $data = get_curl($url);
        $arr = json_decode($data, true);
        if (isset($arr['status']) && $arr['status']=='success') {
            return ['IP所在地'=>$arr['country'].' '.$arr['regionName'].' '.$arr['city'], 'AS编号'=>$arr['as'], 'ISP运营商'=>$arr['isp'], '当地时区'=>$arr['timezone']];
        }elseif (isset($arr['message'])) {
            throw new Exception('接口查询失败：'.$arr['message']);
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}