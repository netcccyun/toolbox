<?php
class qq_login{
	public function getwxpic(){
		$url = 'https://open.weixin.qq.com/connect/qrconnect?appid=wx85f17c29f3e648bf&response_type=code&scope=snsapi_login&redirect_uri=https%3A%2F%2Fpassport.baidu.com%2Fphoenix%2Faccount%2Fafterauth&state='.time().'&display=page&traceid=';
		$ret = $this->get_curl($url);
		preg_match('!connect/qrcode/(.*?)\"!',$ret,$match);
		if($uuid = $match[1])
			return array('code'=>0,'uuid'=>$uuid,'imgurl'=>'https://open.weixin.qq.com/connect/qrcode/'.$uuid);
		else
			return array('code'=>1,'msg'=>'获取二维码失败');
	}
	public function wxlogin($uuid, $last=null){
		if(empty($uuid))return array('code'=>-1,'msg'=>'uuid不能为空');
		$last=$last?'&last='.$last:null;
		$url='https://long.open.weixin.qq.com/connect/l/qrconnect?uuid='.$uuid.$last.'&_='.time().'000';
		$ret = $this->get_curl($url,0,'https://open.weixin.qq.com/connect/qrconnect');
		if(preg_match("/wx_errcode=(\d+);window.wx_code=\'(.*?)\'/", $ret, $match)){
			$errcode = $match[1];
			$code = $match[2];
			if($errcode == 408){
				return array('code'=>'1','msg'=>'二维码未失效');
			}elseif($errcode == 404){
				return array('code'=>'2','msg'=>'请在微信中点击确认即可登录');
			}elseif($errcode == 402){
				return array('code'=>'3','msg'=>'二维码已失效');
			}elseif($errcode == 405){
				$data=$this->get_curl('https://passport.baidu.com/phoenix/account/startlogin?type=42&tpl=pp&u=https%3A%2F%2Fpassport.baidu.com%2F&display=popup&act=optional',0,0,0,1);
				preg_match('/mkey=(.*?);/',$data,$mkey);
				if($mkey = $mkey[1]){
					$url = 'https://passport.baidu.com/phoenix/account/afterauth?mkey='.$mkey.'&appid=wx85f17c29f3e648bf&traceid=&code='.$code.'&state='.time();
					$data=$this->get_curl($url,0,0,'mkey='.$mkey.';',1);
					preg_match('/BDUSS=(.*?);/',$data,$BDUSS);
					preg_match('/STOKEN=(.*?);/',$data,$STOKEN);
					preg_match('/PTOKEN=(.*?);/',$data,$PTOKEN);
					preg_match('/passport_uname: \'(.*?)\'/',$data,$uname);
					preg_match('/displayname: \'(.*?)\'/',$data,$displayname);
				}else{
					return array('code'=>-1,'msg'=>'登录成功，获取mkey失败！');
				}
				if($BDUSS[1] && $STOKEN[1] && $PTOKEN[1]){
					return array('code'=>0,'uid'=>$this->getUserid($uname[1]),'user'=>$uname[1],'displayname'=>$displayname[1],'bduss'=>$BDUSS[1],'ptoken'=>$PTOKEN[1],'stoken'=>$STOKEN[1]);
				}else{
					return array('code'=>-1,'msg'=>'登录成功，回调百度失败！');
				}
			}else{
				return array('code'=>-1,'msg'=>$ret);
			}
		}elseif($ret){
			return array('code'=>-1,'msg'=>$ret);
		}else{
			return array('code'=>1);
		}
	}

	public function getqrpic(){
		$url='https://ssl.ptlogin2.qq.com/ptqrshow?appid=716027609&e=2&l=M&s=4&d=72&v=4&t=0.2616844'.time().'&daid=383&pt_3rd_aid=100312028';
		$arr=$this->get_curl($url,0,'https://xui.ptlogin2.qq.com/cgi-bin/xlogin?appid=716027609&daid=383&style=33&theme=2&login_text=%E6%8E%88%E6%9D%83%E5%B9%B6%E7%99%BB%E5%BD%95&hide_title_bar=1&hide_border=1&target=self&s_url=https%3A%2F%2Fgraph.qq.com%2Foauth2.0%2Flogin_jump&pt_3rd_aid=100312028&pt_feedback_link=https%3A%2F%2Fsupport.qq.com%2Fproducts%2F77942%3FcustomInfo%3Dwww.baidu.com.appid100312028',0,1,0,0,1);
		preg_match('/qrsig=(.*?);/',$arr['header'],$match);
		if($qrsig=$match[1])
			return array('code'=>0,'qrsig'=>$qrsig,'data'=>base64_encode($arr['body']));
		else
			return array('code'=>-1,'msg'=>'二维码获取失败');
	}
	public function qrlogin($qrsig){
		if(empty($qrsig))return array('code'=>-1,'msg'=>'qrsig不能为空');
		$url='https://ssl.ptlogin2.qq.com/ptqrlogin?u1=https%3A%2F%2Fgraph.qq.com%2Foauth2.0%2Flogin_jump&ptqrtoken='.$this->getqrtoken($qrsig).'&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=1-0-'.time().'000&js_ver=10289&js_type=1&login_sig=fCmEYUeoOds1DTeFIFt2IpGUVa471vZXwy6vQlhx2bOL1CnNRtnCe8J0kv9fTQ1Y&pt_uistyle=40&aid=716027609&daid=383&pt_3rd_aid=100312028&';
		$ret = $this->get_curl($url,0,'https://xui.ptlogin2.qq.com/cgi-bin/xlogin','qrsig='.$qrsig.'; ',1);
		if(preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)){
			$r=explode("','",str_replace("', '","','",$arr[1]));
			if($r[0]==0){
				preg_match('/uin=(\d+)&/',$ret,$uin);
				$uin=$uin[1];
				$data=$this->get_curl($r[2],0,'https://xui.ptlogin2.qq.com/cgi-bin/xlogin',0,1);
				if($data) {
					$cookie='';
					preg_match_all('/Set-Cookie: (.*);/iU',$data,$matchs);
					foreach ($matchs[1] as $val) {
						if(substr($val,-1)=='=')continue;
						$cookie.=$val.'; ';
					}
					preg_match('/p_skey=(.*?);/',$cookie,$pskey);
					$cookie = substr($cookie,0,-2);
					$data=$this->get_curl('https://passport.baidu.com/phoenix/account/startlogin?type=15&tpl=pp&u=https%3A%2F%2Fpassport.baidu.com%2F&display=popup&act=optional',0,0,0,1);
					preg_match('/mkey=(.*?);/',$data,$mkey);
					if($mkey = $mkey[1]){
						$url = 'https://graph.qq.com/oauth2.0/authorize';
						$post = 'response_type=code&client_id=100312028&redirect_uri=https%3A%2F%2Fpassport.baidu.com%2Fphoenix%2Faccount%2Fafterauth%3Fmkey%3D'.$mkey.'&scope=get_user_info%2Cadd_share%2Cget_other_info%2Cget_fanslist%2Cget_idollist%2Cadd_idol%2Cget_simple_userinfo&state='.time().'&switch=&from_ptlogin=1&src=1&update_auth=1&openapi=80901010&g_tk='.$this->getGTK($pskey[1]).'&auth_time='.time().'928&ui=D693AB27-C4CD-4C11-A090-A3EFE7C218EC';
						$data=$this->get_curl($url,$post,0,$cookie,1);
						preg_match("/(Location:|location:) (.*?)\r\n/", $data, $match);
						if($match[2]){
							return array('code'=>0,'msg'=>'succ','uin'=>$uin,'redirect_uri'=>$match[2],'mkey'=>$mkey);
						}else{
							return array('code'=>-1,'uin'=>$uin,'msg'=>'登录QQ成功，回调网站失败！');
						}
					}else{
						return array('code'=>-1,'msg'=>'登录QQ成功，获取mkey失败！');
					}
				}else{
					return array('code'=>-1,'uin'=>$uin,'msg'=>'登录QQ成功，获取相关信息失败！');
				}
			}elseif($r[0]==65){
				return array('code'=>1,'uin'=>$uin,'msg'=>'二维码已失效。');
			}elseif($r[0]==66){
				return array('code'=>2,'uin'=>$uin,'msg'=>'二维码未失效。');
			}elseif($r[0]==67){
				return array('code'=>3,'uin'=>$uin,'msg'=>'正在验证二维码。');
			}else{
				return array('code'=>-1,'uin'=>$uin,'msg'=>$r[4]);
			}
		}else{
			return array('code'=>-1,'msg'=>$ret);
		}
	}
	public function qqconnect($redirect_uri, $mkey){
		$url_arr = parse_url($redirect_uri);
		if(empty($redirect_uri) || $url_arr['host']!='passport.baidu.com')return array('code'=>-1,'msg'=>'回调地址错误');
		if(empty($mkey))return array('code'=>-1,'msg'=>'mkey不能为空');
		$data=$this->get_curl($redirect_uri,0,0,'mkey='.$mkey.';',1);
		preg_match('/BDUSS=(.*?);/',$data,$BDUSS);
		preg_match('/STOKEN=(.*?);/',$data,$STOKEN);
		preg_match('/PTOKEN=(.*?);/',$data,$PTOKEN);
		preg_match('/passport_uname: \'(.*?)\'/',$data,$uname);
		preg_match('/displayname: \'(.*?)\'/',$data,$displayname);
		if($BDUSS[1] && $STOKEN[1] && $PTOKEN[1]){
			return array('code'=>0,'msg'=>'succ','uid'=>$this->getUserid($uname[1]),'user'=>$uname[1],'displayname'=>$displayname[1],'bduss'=>$BDUSS[1],'ptoken'=>$STOKEN[1],'stoken'=>$PTOKEN[1]);
		}else{
			return array('code'=>-1,'msg'=>'登录QQ成功，获取百度登录信息失败！');
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
	private function getUserid($uname){
		$ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36';
		$data = $this->get_curl('https://tieba.baidu.com/home/get/panel?ie=utf-8&un='.urlencode($uname),0,0,0,0,$ua);
		$arr = json_decode($data,true);
		$userid = $arr['data']['id'];
		return $userid;
	}
	private function getGTK($skey){
        $len = strlen($skey);
        $hash = 5381;
        for ($i = 0; $i < $len; $i++) {
            $hash += ($hash << 5 & 2147483647) + ord($skey[$i]) & 2147483647;
            $hash &= 2147483647;
        }
        return $hash & 2147483647;
    }
	private function get_curl($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0,$split=0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$httpheader[] = "Accept: application/json";
		$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
		$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
		$httpheader[] = "Connection: close";
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
			curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36');
		}
		if($nobaody){
			curl_setopt($ch, CURLOPT_NOBODY,1);

		}
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$ret = curl_exec($ch);
		if ($split) {
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = substr($ret, 0, $headerSize);
			$body = substr($ret, $headerSize);
			$ret=array();
			$ret['header']=$header;
			$ret['body']=$body;
		}
		curl_close($ch);
		return $ret;
	}
}