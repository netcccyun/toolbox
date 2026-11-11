<?php

namespace plugin\utility\videoparse\api;

use Exception;
use plugin\utility\videoparse\api;

class qmkg implements api
{
    public function parse($url){
        if(!preg_match('/\?s=(.*)/', $url, $match)) throw new Exception('视频id获取失败');
        $id = $match[1];
        $requrl = 'https://kg.qq.com/node/play?s=' . $id;
        $text = get_curl($requrl);
        preg_match('/<title>(.*?)-(.*?)-/', $text, $video_title);
        preg_match('/cover\":\"(.*?)\"/', $text, $video_cover);
        preg_match('/playurl_video\":\"(.*?)\"/', $text, $video_url);
        if(!isset($video_url[1]))preg_match('/playurl\":\"(.*?)\"/', $text, $video_url);
        preg_match('/{\"activity_id\":0\,\"avatar\":\"(.*?)\"/', $text, $video_avatar);
        preg_match('/<p class=\"singer_more__time\">(.*?)<\/p>/', $text, $video_time);
        if (isset($video_url[1])) {
            return [
                'title' => $video_title[2] ?? '',
                'cover' => $video_cover[1] ?? '',
                'url' => $video_url[1],
                'author' => $video_title[1] ?? '',
                'avatar' => $video_avatar[1] ?? '',
                'time' => $video_time[1] ?? '',
            ];
        }else{
            throw new Exception('视频解析失败');
        }
    }
}