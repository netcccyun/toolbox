<?php
/**
 * 短视频去水印解析
 */

namespace plugin\utility\videoparse;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $video_url = input('post.video_url', null, 'trim');
        if(!$video_url) return msg('error','视频链接不能为空');

        try{
            $result = $this->parse($video_url);
            return json(['code'=>0, 'msg'=>'success', 'data'=>$result]);
        }catch(\Exception $e){
            return json(['code'=>-1, 'msg'=>$e->getMessage()]);
        }
    }

    public function parse($url){
        $url = 'https://yuanxiapi.cn/api/jiexi_video/?url='.urlencode($url);
        $data = get_curl($url);
        $arr = json_decode($data, true);
        if(isset($arr['code']) && $arr['code']==200){
            if(isset($arr['video'])){
                $resurl = $arr['video'];
            }elseif(isset($arr['images'])){
                $resurl = $arr['images'];
            }else{
                throw new \Exception('解析url返回异常');
            }
            return [
                'title' => $arr['desc'],
                'cover' => $arr['cover'],
                'url' => $resurl,
            ];
        }else{
            throw new \Exception('视频解析失败');
        }
    }

}