<?php
/**
 * 腾讯域名拦截查询
 */

namespace plugin\web\checkurl;

use app\Plugin;
use Exception;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $link = input('post.url', null, 'trim');
        $type = input('post.type');
        if(!$link) return msg('error','no url');
        
        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }

        try{
            if($type == 'wx'){
                $msg = $this->query_wx($link);
            }else{
                $msg = $this->query_qq($link);
            }
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        return msg('ok','success',$msg);
    }

    private function query_qq_old($link){
        $url = 'https://cgi.urlsec.qq.com/index.php?m=gwComplainMergeIntoWechat&a=checkBlackStatus&callback=url_query&url='.urlencode($link);
        $data=$this->guanjia_curl($url);
        $arr = jsonp_decode($data, true);
        if(!$arr) throw new Exception('查询接口返回数据解析失败');
        $msg['检测URL'] = $link;
        if($arr['reCode']==0 && $arr['data']==1) {
            $msg['域名状态'] = '<font color="red">已拦截</font>';
        }elseif($arr['reCode']==-202){
            $msg['域名状态'] = '<font color="green">未拦截</font>';
        }elseif($arr['reCode']==-203){
            $msg['域名状态'] = '<font color="orange">仅微信拦截</font>';
        }else{
            $msg['查询失败'] = ''.$arr['data'];
        }

        return $msg;
    }

    private function query_qq($link){
        $url='https://cgi.urlsec.qq.com/index.php?m=check&a=check&callback=url_query&url='.urlencode($link);
        $data=$this->guanjia_curl($url);
        $arr = jsonp_decode($data, true);
        if(!$arr) throw new Exception('查询接口返回数据解析失败');
        if(isset($arr['reCode']) && $arr['reCode']==0) {
            $arr = $arr['data']['results'];
            //print_r($arr);
            $msg['检测URL'] = $arr['url'];
            
            if($arr['whitetype']==3||$arr['whitetype']==4){
                $msg['域名状态'] = '<font color="green">白名单</font>';
            }elseif($arr['whitetype']==2){
                $msg['域名状态'] = '<font color="red">已拦截</font>';
                $msg['拦截原因'] = $arr['WordingTitle'];
                $msg['拦截详情'] = $arr['Wording'];
            }elseif($arr['whitetype']==1){
                if($arr['eviltype']!=0){
                    if($arr['eviltype']==2800 || $arr['eviltype']==2804)
                        $msg['域名状态'] = '<font color="orange">QQ内拦截</font>';
                    else
                        $msg['域名状态'] = '<font color="orange">其他拦截('.$arr['eviltype'].')</font>';
                }else{
                    $msg['域名状态'] = '<font color="green">未拦截</font><br/>';
                }
                $msg['安全联盟认证'] = ($arr['certify']==1?'是':'否');
            }
            if($arr['detect_time']!=0){
                $msg['记录时间'] = date("Y-m-d H:i:s", $arr['detect_time']);
            }
            if($arr['isDomainICPOk']==1){
                $msg['是否已备案'] = '是';
                $msg['备案主体'] = $arr['Orgnization'];
                $msg['备案号'] = $arr['ICPSerial'];
            }else{
                $msg['是否已备案'] = '否';
            }

        }else{
            $msg['检测URL'] = $arr['url'];
            $msg['查询失败'] = $arr['data'];
        }

        return $msg;
    }

    private function query_wx($link){
        $url = 'https://mp.weixinbridge.com/mp/wapredirect?url='.urlencode($link);
        $data=get_curl($url,0,0,0,1);
        $msg['检测URL'] = $link;
        if(strpos($data,'https://weixin110.qq.com/')!==false){
            preg_match('/location: (.*?)\r\n/i', $data, $match);
            $data = get_curl($match[1]);
            preg_match('/var cgiData = (.*?)};/', $data, $match);
            if($arr = json_decode($match[1].'}', true)){
                if($arr['type']=='block'){
                    $msg['微信拦截状态'] = '<font color="red">已拦截</font>';
                    $msg['微信拦截原因'] = $arr['desc'];
                }else{
                    $msg['微信拦截状态'] = '<font color="green">未拦截</font>';
                }
            }else{
                $msg['微信拦截状态'] = '<font color="red">已拦截</font>';
            }
        }else{
            $msg['微信拦截状态'] = '<font color="green">未拦截</font>';
        }

        return $msg;
    }

    private function guanjia_curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: application/json";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_REFERER, 'https://urlsec.qq.com/check.html');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

}