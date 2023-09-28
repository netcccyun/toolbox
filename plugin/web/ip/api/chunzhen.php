<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"ip":"113.97.33.248","beginip":"113.97.17.0","endip":"113.97.81.255","country":"\u5e7f\u4e1c\u7701\u6df1\u5733\u5e02","area":"\u7535\u4fe1","province":"\u5e7f\u4e1c\u7701","city":"\u6df1\u5733\u5e02"}
 */
class chunzhen implements api
{
    public function query($ip){
        $new = new \app\lib\IpLocation();
        $arr = $new->getlocation($ip);
        if($arr){
            return ['IP所在地'=>$arr['province'].$arr['city'], '运营商'=>$arr['area']];
        }else{
            throw new Exception('查无此IP数据');
        }
    }
}