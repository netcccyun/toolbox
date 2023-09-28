<?php

namespace plugin\utility\imghosting\api;

use Exception;
use plugin\utility\imghosting\api;

class dianping implements api
{
    public function upload($filepath, $filename){
        $url = 'https://kf.dianping.com/api/file/burstUploadFile';
        $file = new \CURLFile($filepath);
        $file->setPostFilename($filename);
        $param = [
            'files' => $file,
            'fileName' => $filename,
            'part' => '0',
            'partSize' => '1',
            'fileID' => time().rand(111,999)
        ];
        $header = [
            'CSC-VisitId: '.$this->getvisitid()
        ];
        $data = get_curl($url,$param,'https://h5.dianping.com/',0,0,0,0,$header);
        $arr = json_decode($data,true);
        if(isset($arr['code']) && $arr['code'] == 200){
            $picurl = str_replace('http://','https://',$arr['data']['uploadPath']);
            return ['url'=>$picurl];
        }elseif(isset($arr['errMsg'])){
            throw new Exception('上传失败！'.$arr['errMsg']);
        }else{
            throw new Exception('上传失败！接口错误');
        }
    }

    private function getvisitid(){
        $visitid = cache('dianping_visitid');
        if($visitid) return $visitid;
        $url = 'https://kf.dianping.com/csCenter/access/dealOrder_Help_DP_PC';
        $url = $this->get_location_url($url);
        if($url){
            $visitid = getSubstr($url, 'visitId=', '&');
            if($visitid){
                cache('dianping_visitid', $visitid, 86400);
                return $visitid;
            }
        }
        throw new Exception('上传失败！获取visitId异常');
    }

    private function get_location_url($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36");
        curl_exec($ch);
        $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $final_url;
    }
}