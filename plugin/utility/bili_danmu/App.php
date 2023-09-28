<?php
/**
 * 查B站弹幕发送者
 */

namespace plugin\utility\bili_danmu;

use app\Plugin;
use app\lib\BilibiliHelper;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $video_url = input('post.video_url', null, 'trim');
        $danmu_kw = input('post.danmu_kw', null, 'trim');
        if(!$video_url) return msg('error','视频链接不能为空');

        $vid = $video_url;
        $type = 'ugc';
        $page = 1;
        if(strpos($video_url,'http://')!==false || strpos($video_url,'https://')!==false){
            if(preg_match('!/BV(\w+)!i',$video_url,$match)){
                $vid = 'BV'.$match[1];
                $querystring = 'bvid='.$vid;
            }elseif(preg_match('!/av(\d{1,})!',$video_url,$match)){
                $vid = $match[1];
                $querystring = 'aid='.$vid;
            }elseif(preg_match('!/ep(\d{1,})!',$video_url,$match)){
                $vid = $match[1];
                $type = 'pgc';
                if(strpos($video_url,'/cheese/')!==false){
                    $type = 'pugv';
                }
            }elseif(preg_match('!/ss(\d{1,})!',$video_url,$match)){
                $vid = $match[1];
                $type = 'pgc_ss';
            }else{
                return msg('error','视频链接输入格式错误');
            }
            if($type == 'ugc' && preg_match('!p=(\d{1,})!',$video_url,$match)){
                $page = $match[1];
            }
        }else{
            if(substr($vid,0,2) == 'av' && is_numeric(substr($vid,2))){
                $querystring = 'aid='.substr($vid,2);
            }elseif(substr($vid,0,2) == 'ep' && is_numeric(substr($vid,2))){
                $vid = substr($vid,2);
                $type = 'pgc';
            }elseif(substr($vid,0,2) == 'ss' && is_numeric(substr($vid,2))){
                $vid = substr($vid,2);
                $type = 'pgc_ss';
            }elseif(substr($vid,0,2) == 'BV' && preg_match('/^[a-zA-Z0-9]+$/',$vid)){
                $querystring = 'bvid='.$vid;
            }else{
                return msg('error','视频链接输入格式错误');
            }
        }

        $bilibili = new BilibiliHelper();
        try{
            if($type == 'pgc'){
                $video_info = $bilibili->pgc_video_info($vid);
            }elseif($type == 'pgc_ss'){
                $video_info = $bilibili->pgc_video_info_by_ssid($vid);
            }elseif($type == 'pugv'){
                $video_info = $bilibili->pugv_video_info($vid);
            }else{
                $video_info = $bilibili->ugc_video_info($querystring);
                if(count($video_info['pages']) > 1){
                    foreach($video_info['pages'] as $pagerow){
                        if($page == $pagerow['page']){
                            $video_info['cid'] = $pagerow['cid'];
                            continue;
                        }
                    }
                }
            }
            $result = $bilibili->get_video_comment($video_info['cid']);

            $danmu_list = [];
            foreach($result as $row){
                if(!empty($danmu_kw)){
                    if(strpos($row['#text'], $danmu_kw)===false) continue;
                }
                $arr = explode(',',$row['p']);
                $danmu_list[] = ['time'=>$arr[0],'content'=>$row['#text'],'hash'=>$arr[6]];
            }
            array_multisort(array_column($danmu_list, 'time'), SORT_ASC, $danmu_list);

            return msg('ok','success', $danmu_list);

        }catch(\Exception $e){
            return msg('error',$e->getMessage());
        }
    }

}
