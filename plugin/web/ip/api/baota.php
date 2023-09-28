<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"113.97.33.248":{"continent":"亚洲","country":"中国","province":"广东","city":"深圳","region":"南山","carrier":"电信","division":"440305","en_country":"China","en_short_code":"CN","longitude":"113.93029","latitude":"22.53291"}}
 */
class baota implements api
{
    public function query($ip){
        $url = 'https://www.bt.cn/api/panel/get_ip_info?ip='.$ip;
        $data = get_curl($url);
        $arr = json_decode($data, true);
        if (isset($arr[$ip])) {
            return ['IP所在地'=>$arr[$ip]['country'].''.$arr[$ip]['province'].''.$arr[$ip]['city'].''.$arr[$ip]['region'], 'ISP运营商'=>$arr[$ip]['carrier'], '经纬度'=>$arr[$ip]['longitude'].','.$arr[$ip]['latitude']];
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}