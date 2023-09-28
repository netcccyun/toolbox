<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"status":0,"message":"Success","request_id":"345c35a0-97d5-4965-8950-5c4643a28bec","result":{"ip":"113.97.33.248","location":{"lat":22.53332,"lng":113.93041},"ad_info":{"nation":"中国","province":"广东省","city":"深圳市","district":"南山区","adcode":440305}}}
 */
class tencent implements api
{
    public function query($ip){
        $key = 'HOFBZ-A4AK6-BQGSI-ES7F6-HCBN2-SNFQF';
        $url = 'https://apis.map.qq.com/ws/location/v1/ip?ip='.$ip.'&key='.$key;
        $data = get_curl($url);
        $arr = json_decode($data,true);
        if(isset($arr['status']) && $arr['status']==0){
            $result['code']=0;
            $location = $arr['result']['location']['lng'].','.$arr['result']['location']['lat'];
            $address = $arr['result']['ad_info']['nation'].$arr['result']['ad_info']['province'].$arr['result']['ad_info']['city'].$arr['result']['ad_info']['district'];
            return ['IP所在地'=>$address, '经纬度'=>$location];
        }else{
            throw new Exception('接口查询失败：'.$arr['message']);
        }
    }
}