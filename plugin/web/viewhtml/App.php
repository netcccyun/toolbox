<?php
/**
 * 网页源代码查看
 */

namespace plugin\web\viewhtml;

use app\Plugin;
use Exception;

class App extends Plugin
{

    const ualist = [
        'pc' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36',
        'android' => 'Mozilla/5.0 (Linux; U; Android 10; zh-cn; Mi 10 Build/QKQ1.191117.002) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/66.0.3359.126 MQQBrowser/10.2 Mobile Safari/537.36',
        'ios' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.4 Mobile/15E148 Safari/604.1',
        'harmonyos' => 'Mozilla/5.0 (Linux; Android 10; HarmonyOS; LYA-AL00; HMSCore 6.4.0.312; GMSCore 20.15.16) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.105 HuaweiBrowser/12.0.5.302 Mobile Safari/537.36',
        'wechat' => 'Mozilla/5.0 (Linux; Android 11; M2011K2C Build/RKQ1.200928.002; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/77.0.3865.120 MQQBrowser/6.2 TBS/045713 Mobile Safari/537.36 MMWEBID/2820 MicroMessenger/8.0.11.1980(0x28000B3B) Process/tools WeChat/arm64 Weixin NetType/5G Language/zh_CN ABI/arm64',
        'qq' => 'Mozilla/5.0 (Linux; Android 12; IN2010 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/97.0.4692.98 Mobile Safari/537.36 V1_AND_SQ_8.8.68_2538_YYB_D A_8086800 QQ/8.8.68.7265 NetType/4G WebP/0.4.1 Pixel/1080 StatusBarHeight/108 SimpleUISwitch/0 QQTheme/3445 InMagicWin/0 StudyMode/0 CurrentMode/0 CurrentFontScale/1.0 GlobalDensityScale/0.90000004 AppId/537112599',
        'alipay' => 'Mozilla/5.0 (Linux; U; Android 11; zh-CN; MI 10 Build/RKQ1.200928.002) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/69.0.3497.100 UWS/3.22.2.19 Mobile Safari/537.36 UCBS/3.22.2.19_210818212654 NebulaSDK/1.8.100112 Nebula AlipayDefined(nt:3G,ws:411|0|2.625) AliApp(AP/10.2.30.7000) AlipayClient/10.2.30.7000 Language/zh-Hans useStatusBar/true isConcaveScreen/true Region/CN NebulaX/1.0.0 Ariver/1.0.0',
        'baidu' => 'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
        'mbaidu' => 'Mozilla/5.0 (Linux;u;Android 4.2.2;zh-cn;) AppleWebKit/534.46 (KHTML,like Gecko) Version/5.1 Mobile Safari/10600.6.3 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
        'google' => 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)',
        'qqmgr' => 'Mozilla/5.0 (Linux; U; Android 4.4.2; zh-cn; GT-I9500 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.0 QQ-URL-Manager',
    ];

    public function index()
    {
        return $this->view();
    }

    public function getdata(){
        $url = input('post.url', null, 'trim');
        $ua = input('post.ua');
        $useragent = $ua == 'diy' ? input('post.uastr', null, 'trim') : self::ualist[$ua];
        $referer = input('post.referer', null, 'trim');
        $post = input('post.post');
        $cookie = input('post.cookie');
        
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
        if(!empty($post) && substr($post, 0, 1) == '{' && substr($post, -1 ,1) == '}'){
            $httpheader[] = "Content-Type: application/json; charset=utf-8";
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        if(input('post.header')=='1'){
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        if(!empty($referer)){
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if(!empty($cookie)){
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        if(!empty($post)){
            curl_setopt($ch,CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $post);
        }
        $data = curl_exec($ch);
        $errno = curl_errno($ch);
		if ($errno) {
			$msg = 'Curl error: ' . curl_error($ch);
			return msg('error',$msg);
		}
		curl_close($ch);


        $data = (input('post.encoding')=='') ? $data : mb_convert_encoding($data, 'UTF-8', input('post.encoding'));
        switch(input('post.text')){
            case 'links':
            preg_match_all("'<\s*a\s.*?href\s*=\s*			# find <a href=
                            ([\"\'])?					# find single or double quote
                            (?(1) (.*?)\\1 | ([^\s\>]+))		# if quote found, match up to next matching
                                                        # quote, otherwise match up to next space
                            'isx",$data,$links);
            $data = '';
            foreach($links[2] as $val){
                if(!empty($val)) $data .= $val."\n";
            }
            foreach($links[3] as $val){
                if(!empty($val)) $data .= $val."\n";
            }
            break;
            case 'form':
            preg_match_all("'<\/?(FORM|INPUT|SELECT|TEXTAREA|(OPTION))[^<>]*>(?(2)(.*(?=<\/?(option|select)[^<>]*>[\r\n]*)|(?=[\r\n]*))|(?=[\r\n]*))'Usi",$data,$elements);
            $data = implode("\r\n",$elements[0]);
            break;
            case 'text':			
            $data = strip_tags($data);
            break;
        }

        return msg('ok','success',$data);
    }

}