<?php

namespace app\lib;

use Exception;

class BilibiliHelper
{
    private $cookie;
    private $token;
    private $mixinKey;

    public static $qualitys = ['127'=>'8K 超高清', '126'=>'杜比视界', '125'=>'HDR 真彩', '120'=>'4K 超清', '116'=>'1080P 高帧率', '112'=>'1080P 高码率', '80'=>'1080P 高清', '74'=>'720P 高帧率', '64'=>'720P 高清', '48'=>'720P 高清', '32'=>'480P 清晰', '16'=>'360P 流畅', '6'=>'240P 极速'];
    public static $qualitys_audio = ['30216'=>'64K', '30232'=>'132K', '30280'=>'192K'];
    

    public function __construct($cookie = null, $token = null)
    {
        $this->cookie = $cookie; //For WEB
        $this->token = $token; //For APP/TV
    }

    //获取登录信息
    public function login_info()
    {
        $url = 'https://api.bilibili.com/x/web-interface/nav';
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取登录状态失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            return true;
        }elseif($arr['code'] == -101){
            throw new Exception('COOKIE已失效');
        }else{
            throw new Exception('获取登录状态失败 '.$arr['message']);
        }
    }

    //获取用户上传视频信息
    public function ugc_video_info($querystring){
        $url = 'https://api.bilibili.com/x/web-interface/view?'.$querystring;
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频信息失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            return $arr['data'];
        }else{
            throw new Exception('获取视频信息失败：'.$arr['message']);
        }
    }

    //获取正版视频信息
    public function pgc_video_info($ep_id){
        $url = 'https://api.bilibili.com/pgc/view/web/season?ep_id='.$ep_id;
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频信息失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['result']['episodes'])) throw new Exception('获取视频信息失败，返回内容错误');
            $data = null;
            foreach($arr['result']['episodes'] as $row){
                if($ep_id == $row['id']){
                    $data = $row;
                }
            }
            if(empty($data))throw new Exception('获取视频信息失败，未找到对应视频信息');
            return $data;
        }else{
            throw new Exception('获取视频信息失败：'.$arr['message']);
        }
    }

    //获取正版视频信息
    public function pgc_video_info_by_ssid($season_id){
        $url = 'https://api.bilibili.com/pgc/view/web/season?season_id='.$season_id;
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频信息失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['result']['episodes'])) throw new Exception('获取视频信息失败，返回内容错误');
            $data = $arr['result']['episodes'][0];
            if(empty($data))throw new Exception('获取视频信息失败，未找到对应视频信息');
            return $data;
        }else{
            throw new Exception('获取视频信息失败：'.$arr['message']);
        }
    }

    //获取课堂视频信息
    public function pugv_video_info($ep_id){
        $url = 'https://api.bilibili.com/pugv/view/web/season?ep_id='.$ep_id;
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频信息失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['data']['episodes'])) throw new Exception('获取视频信息失败，返回内容错误');
            $data = null;
            foreach($arr['data']['episodes'] as $row){
                if($ep_id == $row['id']){
                    $data = $row;
                }
            }
            if(empty($data))throw new Exception('获取视频信息失败，未找到对应视频信息');
            return $data;
        }else{
            throw new Exception('获取视频信息失败：'.$arr['message']);
        }
    }

    //获取视频弹幕
    public function get_video_comment($cid){
        $danmu_xml = $this->curl('https://comment.bilibili.com/'.$cid.'.xml');
        if(!$danmu_xml){
            return msg('error','获取弹幕内容失败');
        }
        $dom = new \DOMDocument();
        $dom->loadXML($danmu_xml);
        $result = $this->getArray($dom->documentElement);
        return isset($result['d']) ? $result['d'] : [];
    }

    //用户上传视频解析（支持外链）
    public function get_video_url($aid, $cid){
        $param = [
            'avid' => $aid,
            'cid' => $cid,
            'qn' => '120',
            'otype' => 'json',
            'fourk' => '1',
            'fnver' => '0',
            'fnval' => '128',
            'player' => '3',
            'platform' => 'html5',
            'high_quality' => '1',
        ];
        $url = 'https://api.bilibili.com/x/player/playurl?'.http_build_query($param);
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频下载链接失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['data']['durl'])) throw new Exception('获取视频下载链接失败，返回内容错误');
            $url = $arr['data']['durl'][0]['url'];
            $size = $arr['data']['durl'][0]['size'];
            $quality = $arr['data']['support_formats'][0]['new_description'];
            return ['url'=>$url, 'size'=>$size, 'quality'=>$quality, 'format'=>$arr['data']['format'], 'codec'=>$this->get_codec($arr['data']['video_codecid'])];
        }else{
            throw new Exception('获取视频下载链接失败 '.$arr['message']);
        }
    }

    //用户上传视频解析
    public function ugc_video_parse($aid, $cid){
        $param = [
            'avid' => $aid,
            'cid' => $cid,
            'qn' => '0',
            'type' => '',
            'otype' => 'json',
            'fourk' => '1',
            'fnver' => '0',
            'fnval' => '4048',
        ];
        $url = 'https://api.bilibili.com/x/player/playurl?'.http_build_query($param);
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频下载链接失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['data']['dash'])) throw new Exception('获取视频下载链接失败，返回内容错误');
            return $this->video_data_handle($arr['data']);
        }else{
            throw new Exception('获取视频下载链接失败 '.$arr['message']);
        }
    }

    //用户上传视频解析（TV接口）
    public function ugc_video_parse_tv($aid, $cid){
        $param = [
            'avid' => $aid,
            'cid' => $cid,
            'qn' => '0',
            'type' => '',
            'otype' => 'json',
            'fnver' => '0',
            'fnval' => '4048',
            'device' => 'android',
            'platform' => 'android',
            'mobi_app' => 'android_tv_yst',
            'npcybs' => '0',
            'force_host' => '2',
            'build' => '102801',
        ];
        if($this->token){
            $param['access_key'] = $this->token;
        }
        $url = 'https://api.snm0516.aisee.tv/x/tv/ugc/playurl?'.http_build_query($param);
        $ret = $this->curl($url);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频下载链接失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['dash'])) throw new Exception('获取视频下载链接失败，返回内容错误');
            return $this->video_data_handle($arr);
        }else{
            throw new Exception('获取视频下载链接失败 '.$arr['message']);
        }
    }

    //正版视频解析
    public function pgc_video_parse($aid, $cid, $epid, $is_cheese=false){
        $param = [
            'avid' => $aid,
            'cid' => $cid,
            'qn' => '0',
            'type' => '',
            'otype' => 'json',
            'fourk' => '1',
            'fnver' => '0',
            'fnval' => '4048',
            'module' => 'bangumi',
            'ep_id' => $epid,
            'session' => ''
        ];
        $url = 'https://api.bilibili.com/pgc/player/web/playurl?'.http_build_query($param);
        if($is_cheese){
            $url = str_replace('/pgc/','/pugv/',$url);
        }
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频下载链接失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['result']['dash'])) throw new Exception('获取视频下载链接失败，返回内容错误');
            return $this->video_data_handle($arr['result']);
        }elseif($arr['code'] == -10403 && !$is_cheese){
            $url = 'https://www.bilibili.com/bangumi/play/ep'.$epid;
            $ret = $this->curl($url, null, $this->cookie.';CURRENT_FNVAL=4048;');
            preg_match('!window\.__playinfo__=([\s\S]*?)<\/script>!',$ret,$match);
            if(isset($match[1])){
                $arr = json_decode($match[1], true);
            }else{
                throw new Exception('获取视频下载链接失败 '.$arr['message']);
            }
        }else{
            throw new Exception('获取视频下载链接失败 '.$arr['message']);
        }
    }

    //正版视频解析（TV接口）
    public function pgc_video_parse_tv($aid, $cid, $epid, $is_cheese=false){
        $param = [
            'appkey' => '4409e2ce8ffd12b8',
            'aid' => $aid,
            'cid' => $cid,
            'qn' => '0',
            'module' => 'bangumi',
            'ep_id' => $epid,
            'expire' => '0',
            'fnval' => '80',
            'fnver' => '0',
            'fourk' => '1',
            'mid' => '0',
            'otype' => 'json',
            'device' => 'android',
            'platform' => 'android',
            'mobi_app' => 'android_tv_yst',
            'npcybs' => '0',
            'build' => '102801',
            'ts' => time()  
        ];
        if($this->token){
            $param['access_key'] = $this->token;
        }
        $param['sign'] = $this->tv_get_sign($param);
        $url = 'https://api.snm0516.aisee.tv/pgc/player/api/playurltv?'.http_build_query($param);
        if($is_cheese){
            $url = str_replace('/pgc/','/pugv/',$url);
        }
        $ret = $this->curl($url);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取视频下载链接失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['dash'])) throw new Exception('获取视频下载链接失败，返回内容错误');
            return $this->video_data_handle($arr);
        }else{
            throw new Exception('获取视频下载链接失败 '.$arr['message']);
        }
    }

    private function video_data_handle($data){
        $video = [];
        $audio = [];
        $timelength = round($data['timelength']/1000);
        if($data['dash']['video']){
            foreach($data['dash']['video'] as $row){
                if(preg_match('!://(.*:\\d+)/!',$row['base_url'],$match)){ //替换PCDN
                    $row['base_url'] = str_replace($match[1], 'upos-sz-mirrorcoso1.bilivideo.com', $row['base_url']);
                }
                $size = round($timelength * $row['bandwidth'] / 8);
                $video[] = ['url'=>$row['base_url'], 'quality'=>self::$qualitys[$row['id']], 'bandwidth'=>round($row['bandwidth']/1000), 'size' => $size, 'codec'=>$this->get_codec($row['codecid']), 'ratio'=>$row['width'].'×'.$row['height'], 'fps'=>$row['frame_rate']];
            }
        }
        if($data['dash']['audio']){
            foreach($data['dash']['audio'] as $row){
                $size = round($timelength * $row['bandwidth'] / 8);
                $audio[] = ['url'=>$row['base_url'], 'quality'=>self::$qualitys_audio[$row['id']], 'bandwidth'=>round($row['bandwidth']/1000), 'size' => $size, 'codec'=>str_replace(['mp4a.40.2','ec-3'], ['M4A', 'AC3'], $row['codecs'])];
            }
        }
        return ['video'=>$video, 'audio'=>$audio];
    }

    //获取音乐信息
    public function get_audio_info($sid){
        $url = 'https://www.bilibili.com/audio/music-service-c/web/song/info?sid='.$sid;
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取音乐信息失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            return $arr['data'];
        }else{
            throw new Exception('获取音乐信息失败：'.$arr['message']);
        }
    }

    //音乐解析
    public function get_audio_url($sid){
        $url = 'https://www.bilibili.com/audio/music-service-c/web/url?sid='.$sid.'&privilege=2&quality=2';
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('获取音乐下载链接失败');
        }elseif(isset($arr['code']) && $arr['code'] == 0){
            if(!isset($arr['data']['cdns'])) throw new Exception('获取音乐下载链接失败，返回内容错误');
            $url = $arr['data']['cdns'][0];
            $size = $arr['data']['size'];
            return ['url'=>$url, 'size'=>$size, 'quality'=>'MP3（192K）'];
        }else{
            throw new Exception('获取音乐下载链接失败：'.$arr['message']);
        }
    }

    private function curl($url,$data=null,$cookie=null,$referer=null){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        $httpheader[] = "Accept: application/json";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Connection: keep-alive";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        if($data){
            if(is_array($data)) $data=http_build_query($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
            curl_setopt($ch, CURLOPT_POST,1);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_REFERER, $referer?$referer:'https://www.bilibili.com/');
        if($cookie){
            curl_setopt($ch,CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36 Edg/95.0.1020.44');
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $ret=curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    private function get_codec($codecid){
        switch($codecid){
            case 13:
                return 'AV1';break;
            case 12:
                return 'HEVC';break;
            case 7:
                return 'AVC';break;
            default:
                return 'UNKNOWN';break;
        }
    }

    private function tv_get_sign($param){
        $key = '59b43e04ad6965f34319062b478f83dd';
        ksort($param);
        $signstr = http_build_query($param);
        return md5($signstr.$key);
    }

    private function getArray($node) {
        $array = false;
      
        if ($node->hasAttributes()) {
          foreach ($node->attributes as $attr) {
            $array[$attr->nodeName] = $attr->nodeValue;
          }
        }
      
        if ($node->hasChildNodes()) {
          if ($node->childNodes->length == 1) {
            $array[$node->firstChild->nodeName] = $this->getArray($node->firstChild);
          } else {
            foreach ($node->childNodes as $childNode) {
            if ($childNode->nodeType != XML_TEXT_NODE) {
              $array[$childNode->nodeName][] = $this->getArray($childNode);
            }
          }
        }
        } else {
          return $node->nodeValue;
        }
        return $array;
    }


    private function encWbi($params){
        $mixin_key = $this->getMixinKey();
        $curr_time = time();
        $chr_filter = "/[!'()*]/";

        $query = [];
        $params['wts'] = $curr_time;

        ksort($params);

        foreach ($params as $key => $value) {
            $value = preg_replace($chr_filter, '', $value);
            $query[] = urlencode($key) . '=' . urlencode($value);
        }

        $query = implode('&', $query);
        $wbi_sign = md5($query . $mixin_key);

        return $query . '&w_rid=' . $wbi_sign;
    }

    private function getMixinKey(){
        if(!empty($this->mixinKey)) return $this->mixinKey;

        $url = 'https://api.bilibili.com/x/web-interface/nav';
        $ret = $this->curl($url, null, $this->cookie);
        $arr = json_decode($ret, true);
        if(!$arr){
            throw new Exception('请求失败');
        }
        if(!isset($arr['data']['wbi_img'])){
            throw new Exception('获取WbiKeys失败');
        }

        $img_url = $arr['data']['wbi_img']['img_url'];
        $sub_url = $arr['data']['wbi_img']['sub_url'];
        $img_key = substr(basename($img_url), 0, strpos(basename($img_url), '.'));
        $sub_key = substr(basename($sub_url), 0, strpos(basename($sub_url), '.'));
        $key = $img_key . $sub_key;

        $mixinKeyEncTab = [
            46, 47, 18, 2, 53, 8, 23, 32, 15, 50, 10, 31, 58, 3, 45, 35, 27, 43, 5, 49,
            33, 9, 42, 19, 29, 28, 14, 39, 12, 38, 41, 13, 37, 48, 7, 16, 24, 55, 40,
            61, 26, 17, 0, 1, 60, 51, 30, 4, 22, 25, 54, 21, 56, 59, 6, 63, 57, 62, 11,
            36, 20, 34, 44, 52
        ];

        $t = '';
        foreach ($mixinKeyEncTab as $n) $t .= $key[$n];
        $this->mixinKey = substr($t, 0, 32);
        return $this->mixinKey;
    }
    
}