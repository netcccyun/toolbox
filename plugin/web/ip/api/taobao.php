<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"data":{"area":"","country":"中国","isp_id":"100017","queryIp":"113.97.33.248","city":"深圳","ip":"113.97.33.248","isp":"电信","county":"","region_id":"440000","area_id":"","county_id":null,"region":"广东","country_id":"CN","city_id":"440300"},"msg":"query success","code":0}
 */
class taobao implements api
{
    public function query($ip){
        $url = 'https://ip.taobao.com/outGetIpInfo';
        $post = 'ip='.$ip.'&accessKey=alibaba-inc';
        $data = get_curl($url, $post, 'https://ip.taobao.com/ipSearch');
        $arr = json_decode($data, true);
        if (isset($arr['code']) && $arr['code']==0) {
            return ['IP所在地'=>$arr['data']['country'].$arr['data']['region'].$arr['data']['city'], '运营商'=>$arr['data']['isp']];
        }elseif (isset($arr['msg'])) {
            throw new Exception('接口查询失败：'.$arr['msg']);
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}