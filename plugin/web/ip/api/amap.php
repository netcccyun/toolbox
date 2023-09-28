<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"status":"1","info":"OK","infocode":"10000","province":"北京市","city":"北京市","adcode":"110000","rectangle":"116.0119343,39.66127144;116.7829835,40.2164962"}
 */
class amap implements api
{
    public function query($ip){
        $type = '4';
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
            $type = '6';
        }
        $url = 'https://restapi.amap.com/v5/ip?key=0113a13c88697dcea6a445584d535837&type='.$type.'&ip='.$ip;
        $data = get_curl($url);
        $arr = json_decode($data, true);
        if (isset($arr['status']) && $arr['status']=='1') {
            if(empty($arr['country'])){
                throw new Exception('接口查询失败：该IP信息不存在');
            }
            $address = $arr['country'].(isset($arr['province'])&&$arr['province']!=$arr['country']?$arr['province']:'').(isset($arr['city'])?$arr['city']:'').(isset($arr['district'])?$arr['district']:'');
            $result['IP所在地'] = $address;
            if(isset($arr['isp'])) $result['运营商'] = $arr['isp'];
            if(isset($arr['location']) && $arr['location']!='null,null') $result['经纬度'] = $arr['location'];
            return $result;
        }elseif (isset($arr['info'])) {
            throw new Exception('接口查询失败：'.$arr['info']);
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}