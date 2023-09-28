<?php

namespace plugin\web\ip\api;

use Exception;
use plugin\web\ip\api;

/**
 * {"status":"success","country":"加拿大","countryCode":"CA","region":"QC","regionName":"Quebec","city":"蒙特利尔","zip":"H1K","lat":45.6085,"lon":-73.5493,"timezone":"America/Toronto","isp":"Le Groupe Videotron Ltee","org":"Videotron Ltee","as":"AS5769 Videotron Telecom Ltee","query":"24.48.0.1"}
 */
class ip138 implements api
{
    public function query($ip){
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
            return $this->query_ipv6($ip);
        }
        //return $this->query_chinaz($ip);
        $url = 'https://m.ip138.com/iplookup.asp?ip='.$ip.'&action=2';
        $data = get_curl($url);
        preg_match('!<td class="th">ASN归属地</td><td>(.*?)</td>!', $data, $match);
        if (isset($match[1])) {
            return ['IP所在地'=>$match[1]];
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }

    public function query_chinaz($ip){
        $url = 'http://mip.chinaz.com/?query='.$ip;
        $data = get_curl($url);
        if(strpos($data,'错误的IP地址')){
            throw new Exception('错误的IP地址');
        }
        $data = getSubstr($data, '<td class="bg-3fa z-tc ww-5">物理地址</td>', '</td>');
        $data = getSubstr($data, '<td class="z-tc">', '<br />');
        $data = trim(str_replace('&amp;',' ',$data));
        if ($data) {
            return ['IP所在地'=>$data];
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }

    public function query_ipv6($ip){
        $url = 'http://ip.zxinc.org/api.php?type=json&ip='.$ip;
        $data = get_curl($url);
        $arr = json_decode($data, true);
        if (isset($arr['code']) && $arr['code']==0) {
            return ['IP所在地'=>$arr['data']['location']];
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }

    public function query_api($ip){
        $url = 'http://api.ip138.com/ip/?ip='.$ip;
        $header = ['token: c10d9edc249963398634d009413758f4'];
        $data = get_curl($url,0,0,0,0,0,0,$header);
        $arr = json_decode($data, true);
        if (isset($arr['ret']) && $arr['ret']=='ok') {
            return ['IP所在地'=>$arr['data'][0].$arr['data'][1].$arr['data'][2].$arr['data'][3].' '.$arr['data'][4]];
        }elseif (isset($arr['msg'])) {
            throw new Exception('接口查询失败：'.$arr['msg']);
        }else{
            throw new Exception('接口查询失败，返回结果错误');
        }
    }
}