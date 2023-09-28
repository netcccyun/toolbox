<?php
/**
 * B站视频解析
 */

namespace plugin\utility\bili_video;

use app\Plugin;
use app\lib\BilibiliHelper;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function test(){
        $bilibili = new BilibiliHelper();
        $play_info = $bilibili->pgc_video_parse_tv('7419870', '12131254', '97499');
        return json($play_info);
    }

    public function query(){
        $video_url = input('post.video_url', null, 'trim');
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
            }elseif(preg_match('!/au(\d{1,})!',$video_url,$match)){
                $vid = $match[1];
                $type = 'audio';
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
            }elseif(substr($vid,0,2) == 'au' && is_numeric(substr($vid,2))){
                $vid = substr($vid,2);
                $type = 'audio';
            }else{
                return msg('error','视频链接输入格式错误');
            }
        }

        $bilibili = new BilibiliHelper();
        try{
            if($type == 'ugc'){
                $video_info = $bilibili->ugc_video_info($querystring);
                $link = 'https://www.bilibili.com/video/'.$video_info['bvid'];
                if(count($video_info['pages']) > 1){
                    foreach($video_info['pages'] as $pagerow){
                        if($page == $pagerow['page']){
                            $video_info['cid'] = $pagerow['cid'];
                            $video_info['duration'] = $pagerow['duration'];
                            $video_info['title'] .= ' - '.$pagerow['part'];
                            $link .= '?p='.$page;
                            continue;
                        }
                    }
                }
                $play_info = $bilibili->get_video_url($video_info['aid'], $video_info['cid']);

                $result = [
                    'type' => 0,
                    'aid' => $video_info['aid'],
                    'cid' => $video_info['cid'],
                    'bvid' => $video_info['bvid'],
                    'link' => $link,
                    'danmu' => 'https://comment.bilibili.com/'.$video_info['cid'].'.xml',
                    'title' => $video_info['title'],
                    'desc' => $video_info['desc'],
                    'pic' => $video_info['pic'],
                    'duration' => $video_info['duration'],
                    'owner' => isset($video_info['owner'])?$video_info['owner']:null,
                    'video' => $play_info,
                ];
            }elseif($type == 'pgc' || $type == 'pgc_ss'){
                $video_info = $type == 'pgc_ss' ? $bilibili->pgc_video_info_by_ssid($vid) : $bilibili->pgc_video_info($vid);

                $result = [
                    'type' => 0,
                    'aid' => $video_info['aid'],
                    'cid' => $video_info['cid'],
                    'bvid' => $video_info['bvid'],
                    'link' => $video_info['link'],
                    'danmu' => 'https://comment.bilibili.com/'.$video_info['cid'].'.xml',
                    'title' => $video_info['share_copy'],
                    'desc' => null,
                    'pic' => $video_info['cover'],
                    'duration' => $video_info['duration'],
                    'owner' => false,
                    'video' => false,
                ];
            }elseif($type == 'audio'){
                $audio_info = $bilibili->get_audio_info($vid);
                $link = 'https://www.bilibili.com/audio/au'.$audio_info['id'];
                $play_info = $bilibili->get_audio_url($audio_info['id']);

                $result = [
                    'type' => 1,
                    'aid' => $audio_info['id'],
                    'link' => $link,
                    'lyric' => $audio_info['lyric'],
                    'title' => $audio_info['title'],
                    'desc' => $audio_info['intro'],
                    'pic' => $audio_info['cover'],
                    'duration' => $audio_info['duration'],
                    'owner' => ['mid'=>$audio_info['uid'], 'name'=>$audio_info['uname']],
                    'video' => $play_info,
                ];
            }else{
                return msg('error','该视频类型不支持在线解析');
            }
            
            return msg('ok', 'success', $result);

        }catch(\Exception $e){
            return msg('error',$e->getMessage());
        }

        
        
        
    }

}