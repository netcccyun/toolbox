<?php
/**
 * 短网址还原
 */

namespace plugin\utility\unshorturl;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $url = input('post.url', null, 'trim');
        if(!$url) return msg('error','no url');
        if(!filter_var($url,FILTER_VALIDATE_URL)){
            return msg('error','输入的URL不符合规范');
        }

        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        $errno = curl_errno($ch);
		if ($errno) {
			$msg = '短网址还原失败:' . curl_error($ch);
			return msg('error',$msg);
		}
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

        if($httpcode >= 300 && $httpcode < 400){
            preg_match("/location:(.*?)\r\n/i", $data, $match);
            if(isset($match[1])){
                $longurl = trim($match[1]);
                return msg('ok','success',$longurl);
            }
        }
        return msg('error','短网址还原失败('.$httpcode.')');
    }

}