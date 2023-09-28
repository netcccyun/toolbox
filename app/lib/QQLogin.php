<?php
namespace app\lib;

use Zxing\QrReader;

class QQLogin{
	private $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

	public function getqrpic($daid){
		if(empty($daid))return array('saveOK'=>-1,'msg'=>'daid不能为空');
		if($daid == '5'){
			$url='https://ssl.ptlogin2.qq.com/ptqrshow?appid=549000912&e=2&l=M&s=4&d=72&v=4&t=0.5409099'.time().'&daid=5&pt_3rd_aid=0&u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone';
			$referer='https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone';
		}else{
			$url='https://ssl.ptlogin2.qq.com/ptqrshow?appid=716027609&e=2&l=M&s=4&d=72&v=4&t=0.5409099'.time().'&daid='.$daid.'&pt_3rd_aid=100384226';
			$referer='https://xui.ptlogin2.qq.com/cgi-bin/xlogin?daid='.$daid.'&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&target=self&s_url=https:%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html?para%3Dizone&pt_no_auth=0&appid=716027609&pt_3rd_aid=100384226';
		}
		$arr=$this->get_curl_split($url,$referer);
		preg_match('/qrsig=(.*?);/',$arr['header'],$match);
		if($qrsig=$match[1]){
			$qrcode = new QrReader($arr['body'], QrReader::SOURCE_TYPE_BLOB);
			$code_url = $qrcode->text();
			return array('saveOK'=>0,'qrsig'=>$qrsig,'data'=>base64_encode($arr['body']),'url'=>$code_url);
		}else{
			return array('saveOK'=>1,'msg'=>'二维码获取失败');
		}
	}
	public function qrlogin($daid,$s_url,$qrsig){
		if(empty($daid)||empty($s_url))return array('saveOK'=>-1,'msg'=>'daid和s_url不能为空');
		if(empty($qrsig))return array('saveOK'=>-1,'msg'=>'qrsig不能为空');
		if($daid == '5'){
			$url='https://ssl.ptlogin2.qq.com/ptqrlogin?u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken='.$this->getqrtoken($qrsig).'&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-'.time().'000&js_ver=23042119&js_type=1&login_sig=&pt_uistyle=40&aid=549000912&daid=5&';
		}else{
			$url='https://ssl.ptlogin2.qq.com/ptqrlogin?u1='.urlencode($s_url).'&ptqrtoken='.$this->getqrtoken($qrsig).'&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-'.time().'0000&js_ver=10194&js_type=1&login_sig=&pt_uistyle=40&aid=716027609&daid='.$daid.'&pt_3rd_aid=100384226&';
		}
		$ret = $this->get_curl($url,0,'https://xui.ptlogin2.qq.com/','qrsig='.$qrsig.'; ',1);
		if(preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)){
			$r=explode("','",str_replace("', '","','",$arr[1]));
			if($r[0]==0){
				preg_match('/uin=(\d+)&/',$ret,$uin);
				$uin=$uin[1];
				preg_match('/skey=@(.{9});/',$ret,$skey);
				preg_match('/superkey=(.*?);/',$ret,$superkey);
				$data=$this->get_curl($r[2],0,0,0,1);
				if($data) {
					preg_match_all('/Set-Cookie: (.*?);/i',$data,$matchs);
					$cookie='';
					foreach ($matchs[1] as $val) {
						if(substr($val,-1)=='=')continue;
						$cookie.=$val.'; ';
					}
					$cookie = substr($cookie,0,-2);
				}
				if($cookie){
					return array('saveOK'=>0,'uin'=>$uin,'cookie'=>$cookie,'nickname'=>$r[5]);
				}else{
					return array('saveOK'=>6,'msg'=>'登录成功，获取相关信息失败！'.$r[2]);
				}
			}elseif($r[0]==65){
				return array('saveOK'=>1,'msg'=>'二维码已失效。');
			}elseif($r[0]==66){
				return array('saveOK'=>2,'msg'=>'二维码未失效。');
			}elseif($r[0]==67){
				return array('saveOK'=>3,'msg'=>'正在验证二维码。');
			}else{
				return array('saveOK'=>6,'msg'=>$r[4]);
			}
		}else{
			return array('saveOK'=>6,'msg'=>$ret);
		}
	}
	private function getqrtoken($qrsig){
        $len = strlen($qrsig);
        $hash = 0;
        for($i = 0; $i < $len; $i++){
            $hash += (($hash << 5) & 2147483647) + ord($qrsig[$i]) & 2147483647;
			$hash &= 2147483647;
        }
        return $hash & 2147483647;
	}
	private function get_curl($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0,$noproxy=0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$httpheader[] = "Accept: application/json";
		$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
		$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
		$httpheader[] = "Connection: keep-alive";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if($header){
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
		}
		if($cookie){
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		if($referer){
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
		if($ua){
			curl_setopt($ch, CURLOPT_USERAGENT,$ua);
		}else{
			curl_setopt($ch, CURLOPT_USERAGENT,$this->ua);
		}
		if($nobaody){
			curl_setopt($ch, CURLOPT_NOBODY,1);

		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
	private function get_curl_split($url,$referer=0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$httpheader[] = "Accept: */*";
		$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
		$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
		$httpheader[] = "Connection: keep-alive";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT,$this->ua);
		if($referer){
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$ret = curl_exec($ch);
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($ret, 0, $headerSize);
		$body = substr($ret, $headerSize);
		$ret=array();
		$ret['header']=$header;
		$ret['body']=$body;
		curl_close($ch);
		return $ret;
	}
}