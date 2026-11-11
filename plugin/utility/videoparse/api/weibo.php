<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class weibo implements api
{
    public function parse($url, $retry = 0){
        if(preg_match('/tv\/show\/([0-9:]+)/', $url, $matches) || preg_match('/fid=([0-9:]+)/', $url, $matches)){
            $oid = $matches[1];
            $page = '/tv/show/'.$oid;
            $api_url = 'https://weibo.com/tv/api/component?page='.urlencode($page);
            $post = 'data={"Component_Play_Playinfo":{"oid":"'.$oid.'"}}';
            $referer = 'https://weibo.com/tv/show/'.$oid;
            $cookie = cache('weibo_cookie');
            if(!$cookie) $cookie = $this->genvisitor();
            $data = get_curl($api_url, $post, $referer, $cookie);
            $arr = json_decode($data, true);
            if(isset($arr['code']) && $arr['code']=='100000'){
                if(isset($arr['data']['Component_Play_Playinfo'])){
                    $info = $arr['data']['Component_Play_Playinfo'];
                    if(!empty($info['urls'])){
                        $hd_keys = ['超清 4K', '超清 2K', '高清 1080P', '高清 720P', '高清 480P'];
                        $play_url = null;
                        foreach($hd_keys as $key){
                            if(isset($info['urls'][$key])){
                                $play_url = $info['urls'][$key];
                                break;
                            }
                        }
                        if(!$play_url) $play_url = $info['urls'][array_key_first($info['urls'])];
                        $result = ['title'=>$info['title'], 'author'=>$info['author'], 'avatar'=>$info['avatar'], 'url'=>$play_url, 'cover'=>$info['cover_image'], 'time'=>date('Y-m-d H:i:s', $info['real_date']), 'duration'=>$info['duration']];
                        return $result;
                    }else{
                        throw new Exception('解析视频失败：视频地址不存在');
                    }
                }else{
                    throw new Exception('解析视频失败：视频不存在');
                }
            }elseif(isset($arr['msg'])){
                throw new Exception('解析视频失败：'.$arr['msg']);
            }elseif(empty($data) && $retry == 0){
                cache('weibo_cookie', null);
                return $this->parse($url, 1);
            }else{
                throw new Exception('解析视频失败：接口请求失败');
            }
        }else{
            throw new Exception('视频id获取失败');
        }
    }

    private function genvisitor(){
        $url = 'https://passport.weibo.com/visitor/genvisitor2';
        $post = 'cb=visitor_gray_callback&tid=&from=weibo';
        $referer = 'https://passport.weibo.com/visitor/visitor';
        $data = get_curl($url, $post, $referer);
        if(preg_match('/visitor_gray_callback\((.*?)\)/', $data, $matches)){
            $arr = json_decode($matches[1], true);
            if(isset($arr['retcode']) && $arr['retcode'] == 20000000){
                $cookie = 'SUB='.$arr['data']['sub'].'; SUBP='.$arr['data']['subp'].';';
                cache('weibo_cookie', $cookie);
                return $cookie;
            }
        }
        throw new Exception('生成访客cookie失败');
    }
}