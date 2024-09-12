<?php
class qq_login{
	private $loginapi = ''; //可填写登录API
	private $ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';
	private $trace_x;
	private $trace_y;
	private $trace_time;
	private $referrer;
	public function setLoginApi($url){
		$this->loginapi = $url;
	}
	public function dovc($uin,$sig,$ans,$cap_cd,$sess,$websig,$cdata,$sid){
		if(empty($uin))return array('saveOK'=>-1,'msg'=>'QQ不能为空');
		if(empty($ans))return array('saveOK'=>-1,'msg'=>'验证码不能为空');
		if(empty($cap_cd))return array('saveOK'=>-1,'msg'=>'cap_cd不能为空');
		if(empty($sess))return array('saveOK'=>-1,'msg'=>'sess不能为空');
		if(empty($sid))return array('saveOK'=>-1,'msg'=>'sid不能为空');
		$width = explode(',',$ans);
		$width = $width[0];

		//$collect=$this->getcollect($width, $sid);
		//$cookie = 'TDC_itoken='.urlencode($collect['TDC_itoken']);

		$url='https://t.captcha.qq.com/cap_union_new_verify';
		$post='aid=549000912&captype=&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=0&color=&showtype=&fb=1&theme=&lang=2052&ua='.urlencode(base64_encode($this->ua)).'&grayscale=1&subsid=2&sess='.$sess.'&fwidth=0&sid='.$sid.'&forcestyle=0&wxLang=&tcScale=1&uid='.$uin.'&cap_cd='.$cap_cd.'&rnd='.rand(100000,999999).'&TCapIframeLoadTime=99&prehandleLoadTime=48&createIframeStart='.time().'758&rand=0.330608'.time().'&subcapclass=&vsig='.$sig.'&ans='.$ans.'&collect='.$collect['collectdata'].'&websig='.$websig.'&cdata='.$cdata.'&eks='.$collect['eks'].'&tlg='.strlen($collect['collectdata']).'&vlg=0_0_1';
		$data=$this->get_curl($url,$post,$this->referrer);
		$arr=json_decode($data,true);
		if(array_key_exists('errorCode',$arr) && $arr['errorCode']==0){
			return array('rcode'=>0,'randstr'=>$arr['randstr'],'sig'=>$arr['ticket']);
		}elseif($arr['errorCode']==50){
			return array('rcode'=>50,'errmsg'=>'验证码输入错误！','sess'=>$arr['sess']);
		}elseif($arr['errorCode']==12){
			return array('rcode'=>12,'errmsg'=>$arr['errMessage'],'sess'=>$arr['sess']);
		}else{
			return array('rcode'=>9,'errmsg'=>$arr['errMessage'],'sess'=>$arr['sess']);
		}
	}
	public function getvcpic($uin,$sig,$cap_cd,$sess,$sid){
		if(empty($uin))return array('saveOK'=>-1,'msg'=>'QQ不能为空');
		if(empty($sig))return array('saveOK'=>-1,'msg'=>'sig不能为空');
		$url='https://ssl.captcha.qq.com/cap_union_new_getcapbysig?aid=549000912&captype=&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=0&uid='.$uin.'&color=&showtype=&fb=1&lang=2052&ua='.urlencode(base64_encode($this->ua)).'&grayscale=1&cap_cd='.$cap_cd.'&rnd='.rand(100000,999999).'&rand=0.02398118'.time().'&sess='.$sess.'&sid='.$sid.'&vsig='.$sig.'&ischartype=1';
		return $this->get_curl($url);
	}
	public function getvcpic2($uin,$imageid,$cap_cd,$sess,$sid,$img_index=0){
		if(empty($uin))return array('saveOK'=>-1,'msg'=>'QQ不能为空');
		if(empty($imageid))return array('saveOK'=>-1,'msg'=>'imageid不能为空');
		$url='https://t.captcha.qq.com/hycdn?index='.$img_index.'&image='.$imageid.'?aid=549000912&captype=&curenv=inner&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=0&color=&showtype=&fb=1&theme=&lang=2052&ua='.urlencode(base64_encode($this->ua)).'&enableDarkMode=0&grayscale=1&subsid=3&sess='.$sess.'&fwidth=0&sid='.$sid.'&forcestyle=undefined&wxLang=&tcScale=1&uid='.$uin.'&cap_cd='.$cap_cd.'&rnd='.rand(100000,999999).'&TCapIframeLoadTime=60&prehandleLoadTime=135&createIframeStart='.time().'487&rand='.rand(100000,999999).'&websig=&vsig=&img_index='.$img_index;
		return $url;
	}
	public function qqlogin($uin,$pwd,$p,$vcode,$pt_verifysession,$sid,$cookie,$sms_code=null,$sms_ticket=null){
		if(empty($uin))return array('saveOK'=>-1,'msg'=>'QQ不能为空');
		if(empty($pwd))return array('saveOK'=>-1,'msg'=>'pwd不能为空');
		if(empty($p))return array('saveOK'=>-1,'msg'=>'密码不能为空');
		if(empty($vcode))return array('saveOK'=>-1,'msg'=>'验证码不能为空');
		if(empty($pt_verifysession))return array('saveOK'=>-1,'msg'=>'pt_verifysession不能为空');
		if(strpos($vcode,'!')!==false){
			$v1=0;
		}else{
			$v1=1;
		}
		preg_match("/pt_login_sig=(.*?);/", $cookie, $match);
		$pt_login_sig = $match[1];
		preg_match("/ptdrvs=(.*?);/", $cookie, $match);
		$ptdrvs = $match[1];
		$url='https://ssl.ptlogin2.qq.com/login?u='.$uin.'&verifycode='.$vcode.'&pt_vcode_v1='.$v1.'&pt_verifysession_v1='.$pt_verifysession.'&p='.$p.'&pt_randsalt=2&u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=5-10-'.time().'487&js_ver=22030810&js_type=1&login_sig='.$pt_login_sig.'&pt_uistyle=40&aid=549000912&daid=5&ptdrvs='.$ptdrvs.'&sid='.$sid;
		if(!empty($sms_code)){
			$url.='&pt_sms_code='.$sms_code;
			$cookie.='pt_sms_ticket='.$sms_ticket.'; pt_sms='.$sms_code.';';
		}
		$referrer='https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=0&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&pt_no_auth=0';
		$ret = $this->get_curl($url,0,$referrer,$cookie,1);
		if(preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)){
			$r=explode("','",str_replace("', '","','",$arr[1]));
			if($r[0]==0){
				preg_match('/skey=@(.{9});/',$ret,$skey);
				preg_match('/superkey=(.*?);/',$ret,$superkey);
				$data=$this->get_curl($r[2],0,0,0,1);
				if($data) {
					preg_match("/p_skey=(.*?);/", $data, $matchs);
					$pskey = $matchs[1];
				}
				if($skey[1] && $pskey){
					return array('saveOK'=>0,'uin'=>$uin,'skey'=>'@'.$skey[1],'pskey'=>$pskey,'superkey'=>$superkey[1],'nick'=>urlencode($r[5]),'loginurl'=>$r[2]);
				}else{
					if(!$pskey)
						return array('saveOK'=>-3,'msg'=>'登录成功，获取P_skey失败！'.$r[2]);
					elseif(!$skey[1])
						return array('saveOK'=>-3,'msg'=>'登录成功，获取Skey失败！');
				}
			}elseif($r[0]==4){
				return array('saveOK'=>4,'msg'=>'验证码错误');
			}elseif($r[0]==3){
				return array('saveOK'=>3,'msg'=>'密码错误');
			}elseif($r[0]==19){
				return array('saveOK'=>19,'uin'=>$uin,'msg'=>'您的帐号暂时无法登录，请到 http://aq.qq.com/007 恢复正常使用');
			}elseif($r[0]==10009){
				preg_match('/pt_sms_ticket=(.*?);/',$ret,$sms_ticket);
				preg_match('/ptdrvs=(.*?);/',$ret,$ptdrvs_new);
				$cookie = str_replace($ptdrvs, $ptdrvs_new[1], $cookie);
				return array('saveOK'=>10009,'sms_ticket'=>$sms_ticket[1],'cookie'=>$cookie,'msg'=>'请通过密保手机'.$r[4].'获取短信验证码');
			}elseif($r[0]==10010){
				return array('saveOK'=>10010,'msg'=>$r[4]);
			}elseif($r[0]==10005||$r[0]==10006||$r[0]==22009){
				return array('saveOK'=>10005,'msg'=>'登录环境异常（异地登录或IP存在风险），请使用QQ手机版扫码登录！');
			}else{
				return array('saveOK'=>-6,'msg'=>$r[4]);
			}
		}else{
			return array('saveOK'=>-2,'msg'=>$ret);
		}
	}
	public function send_sms_code($uin, $sms_ticket, $cookie){
		if(empty($uin))return array('saveOK'=>-1,'msg'=>'QQ不能为空');
		if(empty($sms_ticket))return array('saveOK'=>-1,'msg'=>'sms_ticket不能为空');
		$cookie .= '; pt_sms_ticket='.$sms_ticket;
		$url = 'https://ssl.ptlogin2.qq.com/send_sms_code?bkn=&uin='.$uin.'&aid=549000912&pt_sms_ticket='.$sms_ticket;
		$data=$this->get_curl($url,0,'https://ui.ptlogin2.qq.com/web/verify/iframe?uin='.$uin.'&appid=549000912',$cookie);
		if(preg_match("/ptui_sendSMS_CB\('(.*?)'\)/", $data, $arr)){
			$r=explode("', '",$arr[1]);
			if($r[0]=='10012'){
				return array('saveOK'=>0,'msg'=>'短信发送成功！');
			}else{
				if(!empty($r[1]))
					return array('saveOK'=>-1,'msg'=>$r[1]);
				else
					return array('saveOK'=>-1,'msg'=>'短信发送失败，请刷新重试');
			}
		}else{
			return array('saveOK'=>-2,'msg'=>$data);
		}
	}
	public function getvc($uin,$sig,$sess,$sid,$websig){
		if(empty($uin))return array('saveOK'=>-1,'msg'=>'请先输入QQ号码');
		if(empty($sig))return array('saveOK'=>-1,'msg'=>'SIG不能为空');
		if(!preg_match("/^[1-9][0-9]{4,11}$/",$uin)) exit('{"saveOK":-2,"msg":"QQ号码不正确"}');
		if($sess=='0'){
			$url='https://t.captcha.qq.com/cap_union_prehandle?aid=549000912&captype=&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=0&color=&showtype=&fb=1&theme=&lang=2052&ua='.urlencode(base64_encode($this->ua)).'&grayscale=1&sid='.$sid.'&cap_cd='.$sig.'&uid='.$uin.'&subsid=1&callback=&sess=';
			$data=$this->get_curl($url,0,'https://xui.ptlogin2.qq.com/cgi-bin/xlogin');
			$data=substr($data,1,-1);
			$arr=json_decode($data,true);
			$sess=$arr['sess'];
			$sid=$arr['sid'];
			if(!$sess)return array('saveOK'=>-3,'msg'=>'获取验证码参数失败');

			$url='https://t.captcha.qq.com/cap_union_new_show?aid=549000912&captype=&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=0&color=&showtype=&fb=1&theme=&lang=2052&ua='.urlencode(base64_encode($this->ua)).'&enableDarkMode=0&grayscale=1&subsid=2&sess='.$sess.'&fwidth=0&sid='.$sid.'&forcestyle=0&wxLang=&tcScale=1&uid='.$uin.'&cap_cd='.$sig.'&rnd='.rand(100000,999999).'&TCapIframeLoadTime=48&prehandleLoadTime=46&createIframeStart='.time().'353';
			$this->referrer = $url;
			$data=$this->get_curl($url,0,'https://xui.ptlogin2.qq.com/cgi-bin/xlogin');
			if(preg_match("/sess:\"([0-9a-zA-Z\*\_\-]+)\"/", $data, $match1) && preg_match('/spt:\"(\d+)\"/', $data, $Number)){
				$sess = $match1[1];
				$height = $Number[1];
				preg_match('/&image=(\d+)\"/', $data, $imageid);
				preg_match('/collectdata:\"([0-9a-zA-Z]+)\"/', $data, $collectname);
				preg_match("/vsig:\"([0-9a-zA-Z\*\_\-]+)\"/", $data, $vsig);
				preg_match('/websig:\"([0-9a-f]{128})\"/', $data, $websig);
				$cdata=0;
				$imgA = $this->getvcpic2($uin,$imageid[1],$sig,$sess,$sid,1);
				$imgB = $this->getvcpic2($uin,$imageid[1],$sig,$sess,$sid,0);
				$width = $this->captcha($imgA, $imgB);
				$ans = $width.','.$height.';';
				return array('saveOK'=>2,'vc'=>$vsig[1],'sess'=>$sess,'collectname'=>$collectname[1],'websig'=>$websig[1],'ans'=>$ans,'cdata'=>$cdata,'sid'=>$sid);
			}else{
				return array('saveOK'=>-3,'msg'=>'获取验证码失败');
			}
		}else{
			$url='https://t.captcha.qq.com/cap_union_new_getsig';
			$post='aid=549000912&captype=&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=0&color=&showtype=&fb=1&theme=&lang=2052&ua='.urlencode(base64_encode($this->ua)).'&enableDarkMode=0&grayscale=1&subsid=2&sess='.$sess.'&sid='.$sid.'&uid='.$uin.'&cap_cd='.$sig.'&rnd='.rand(100000,999999).'&TCapIframeLoadTime=99&prehandleLoadTime=48&createIframeStart='.time().'758&rand=0.3944965'.time();
			$referrer='https://t.captcha.qq.com/cap_union_new_show?aid=549000912&captype=&protocol=https&clientype=2&disturblevel=&apptype=2&noheader=0&color=&showtype=&fb=1&theme=&lang=2052&ua='.urlencode(base64_encode($this->ua)).'&grayscale=1&subsid=2&sess='.$sess.'&fwidth=0&sid='.$sid.'&forcestyle=0&wxLang=&tcScale=1&uid='.$uin.'&cap_cd='.$sig.'&rnd='.rand(100000,999999).'&TCapIframeLoadTime=48&prehandleLoadTime=46&createIframeStart='.time().'353';
			$this->referrer = $referrer;
			$data=$this->get_curl($url,$post,$referrer);
			$arr=json_decode($data,true);
			$cdata=0;
			if($arr['initx'] && $arr['inity']){
				$sess = $arr['sess'];
				$height = $arr['inity'];
				$imageid = substr($arr['cdnPic1'], strpos($arr['cdnPic1'], '&image=')+7);
				$imgA = $this->getvcpic2($uin,$imageid,$sig,$sess,$sid,1);
				$imgB = $this->getvcpic2($uin,$imageid,$sig,$sess,$sid,0);
				$width = $this->captcha($imgA, $imgB);
				$ans = $width.','.$height.';';
				return array('saveOK'=>2,'vc'=>null,'sess'=>$sess,'ans'=>$ans,'cdata'=>$cdata);
			}elseif($arr['vsig']){
				$image = $this->getvcpic($uin,$arr['vsig'],$sig,$sess,$sid);
				return array('saveOK'=>0,'vc'=>$arr['vsig'],'sess'=>$sess,'cdata'=>$cdata,'image'=>base64_encode($image));
			}else{
				return array('saveOK'=>-3,'msg'=>'获取验证码失败');
			}
		}
	}
	public function checkvc($uin){
		if(empty($uin))return array('saveOK'=>-1,'msg'=>'请先输入QQ号码');
		if(!preg_match("/^[1-9][0-9]{4,13}$/",$uin)) exit('{"saveOK":-2,"msg":"QQ号码不正确"}');
		$url='https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&pt_no_auth=0';
		$data=$this->get_curl($url,0,0,0,1);
		$cookie='';
		preg_match_all('/Set-Cookie: (.*?);/i',$data,$matchs);
		foreach ($matchs[1] as $val) {
			$cookie.=$val.'; ';
		}
		preg_match("/pt_login_sig=(.*?);/", $cookie, $match);
		$pt_login_sig = $match[1];
		preg_match("/ver\/(\d+)/", $data, $match);
		$js_ver = $match[1];
		$url2='https://ssl.ptlogin2.qq.com/check?regmaster=&pt_tea=2&pt_vcode=1&uin='.$uin.'&appid=549000912&js_ver='.$js_ver.'&js_type=1&login_sig='.$pt_login_sig.'&u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&r=0.'.time().'722706&pt_uistyle=25';
		$data=$this->get_curl($url2,0,$url,$cookie,1);
		if(preg_match("/ptui_checkVC\('(.*?)'\)/", $data, $arr)){
			preg_match_all('/Set-Cookie: (.*);/iU',$data,$matchs);
			foreach ($matchs[1] as $val) {
				$cookie.=$val.'; ';
			}
			$r=explode("','",$arr[1]);
			if($r[0]==0){
				return array('saveOK'=>0,'uin'=>$uin,'vcode'=>$r[1],'pt_verifysession'=>$r[3],'sid'=>$r[6],'cookie'=>$cookie);
			}else{
				return array('saveOK'=>1,'uin'=>$uin,'sig'=>$r[1],'sid'=>$r[6],'cookie'=>$cookie);
			}
		}else{
			return array('saveOK'=>-3,'msg'=>'获取验证码失败'.$data);
		}
	}
	public function getqrpic(){
		$url='https://ssl.ptlogin2.qq.com/ptqrshow?s=8&e=0&appid=549000912&type=1&t=0.492909'.time().'&daid=5&pt_3rd_aid=0&u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone';
		$referer='https://xui.ptlogin2.qq.com/cgi-bin/xlogin?proxy_url=https%3A//qzs.qq.com/qzone/v6/portal/proxy.html&daid=5&&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&appid=549000912&style=22&target=self&s_url=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone';
		$arr=$this->get_curl_split($url,$referer);
		preg_match('/qrsig=(.*?);/',$arr['header'],$match);
		if($qrsig=$match[1]){
			preg_match('/\((.*?)\)/',$arr['body'],$match);
			$arr = json_decode($match[1], true);
			$qrcodedata = $this->getqrcode($arr['qrcode']);
			return array('saveOK'=>0,'qrsig'=>$qrsig,'qrcode'=>$arr['qrcode'],'data'=>base64_encode($qrcodedata));
		}else{
			return array('saveOK'=>1,'msg'=>'二维码获取失败');
		}
	}
	public function qrlogin($qrsig){
		if(empty($qrsig))return array('saveOK'=>-1,'msg'=>'qrsig不能为空');
		$url='https://ssl.ptlogin2.qq.com/ptqrlogin?u1=https%3A%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html%3Fpara%3Dizone&ptqrtoken='.$this->getqrtoken($qrsig).'&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-'.time().'000&js_ver=23042119&js_type=1&login_sig='.$sig.'&pt_uistyle=40&aid=549000912&daid=5&';
		$cookie = 'qrsig='.$qrsig.'; ';
		$ret = $this->get_curl($url,0,'https://xui.ptlogin2.qq.com/',$cookie,1);
		if(preg_match("/ptuiCB\('(.*?)'\)/", $ret, $arr)){
			$r=explode("','",str_replace("', '","','",$arr[1]));
			if($r[0]==0){
				preg_match('/uin=(\d+)&/',$ret,$uin);
				$uin=$uin[1];
				preg_match('/skey=@(.{9});/',$ret,$skey);
				preg_match('/superkey=(.*?);/',$ret,$superkey);
				$data=$this->get_curl($r[2],0,0,0,1);
				if($data) {
					preg_match("/p_skey=(.*?);/", $data, $matchs);
					$pskey = $matchs[1];
				}
				if($pskey){
					if(isset($_GET['findpwd'])){
						$_SESSION['findpwd_qq']=$uin;
					}
					return array('saveOK'=>0,'uin'=>$uin,'skey'=>'@'.$skey[1],'pskey'=>$pskey,'superkey'=>$superkey[1],'nick'=>$r[5]);
				}else{
					return array('saveOK'=>6,'msg'=>'登录成功，获取相关信息失败！'.$r[2]);
				}
			}elseif($r[0]==65){
				return array('saveOK'=>1,'msg'=>'二维码已失效。');
			}elseif($r[0]==66){
				return array('saveOK'=>2,'msg'=>'二维码未失效。');
			}elseif($r[0]==67){
				return array('saveOK'=>3,'msg'=>'正在验证二维码。');
			}elseif($r[0]==10009){
				return array('saveOK'=>6,'msg'=>'需要手机验证码才能登录，此次登录失败');
			}else{
				return array('saveOK'=>6,'msg'=>$r[4]);
			}
		}else{
			return array('saveOK'=>6,'msg'=>$ret);
		}
	}
	public function getqrpic3rd($daid,$appid){
		if(empty($daid)||empty($appid))return array('saveOK'=>-1,'msg'=>'daid和appid不能为空');
		$url='https://ssl.ptlogin2.qq.com/ptqrshow?s=8&e=0&appid=716027609&type=1&t=0.492909'.time().'&daid='.$daid.'&pt_3rd_aid=100384226';
		$referer='https://xui.ptlogin2.qq.com/cgi-bin/xlogin?daid='.$daid.'&hide_title_bar=1&low_login=0&qlogin_auto_login=1&no_verifyimg=1&link_target=blank&target=self&s_url=https:%2F%2Fqzs.qq.com%2Fqzone%2Fv5%2Floginsucc.html?para%3Dizone&pt_no_auth=0&appid=716027609&pt_3rd_aid=100384226';
		$arr=$this->get_curl_split($url,$referer);
		preg_match('/qrsig=(.*?);/',$arr['header'],$match);
		if($qrsig=$match[1]){
			preg_match('/\((.*?)\)/',$arr['body'],$match);
			$arr = json_decode($match[1], true);
			$qrcodedata = $this->getqrcode($arr['qrcode']);
			return array('saveOK'=>0,'qrsig'=>$qrsig,'qrcode'=>$arr['qrcode'],'data'=>base64_encode($qrcodedata));
		}else{
			return array('saveOK'=>1,'msg'=>'二维码获取失败');
		}
	}
	public function qrlogin3rd($daid,$appid,$qrsig){
		if(empty($daid)||empty($appid))return array('saveOK'=>-1,'msg'=>'daid和appid不能为空');
		if(empty($qrsig))return array('saveOK'=>-1,'msg'=>'qrsig不能为空');
		if($daid==73)$s_url = 'https://qun.qq.com/';
		else if($daid==1)$s_url = 'https://id.qq.com/index.html';
		else $s_url = 'https://qzs.qq.com/qzone/v5/loginsucc.html';
		$url='https://ssl.ptlogin2.qq.com/ptqrlogin?u1='.urlencode($s_url).'&ptqrtoken='.$this->getqrtoken($qrsig).'&ptredirect=0&h=1&t=1&g=1&from_ui=1&ptlang=2052&action=0-0-'.time().'0000&js_ver=10194&js_type=1&login_sig=&pt_uistyle=40&aid=716027609&daid='.$daid.'&pt_3rd_aid=100384226&';
		$ret = $this->get_curl($url,0,$url,'qrsig='.$qrsig.'; ',1);
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
	private function getqrcode($url){
		require 'phpqrcode.php';
		$QRcode = new QRcode();
		ob_start();
		$QRcode->png($url, false, 'L', 4, 3);
		$qrcodedata = ob_get_contents();
		ob_end_clean();
		return $qrcodedata;
	}
	private function captcha($imgAurl,$imgBurl){
		$imgA = imagecreatefromstring($this->get_curl($imgAurl,0,$this->referrer));
		$imgB = imagecreatefromstring($this->get_curl($imgBurl,0,$this->referrer));
		$imgWidth = imagesx($imgA);
		$imgHeight = imagesy($imgA);
		
		$t=0;$r=0;
		for ($y=20; $y<$imgHeight-20; $y++){
		   for ($x=20; $x<$imgWidth-20; $x++){
			   $rgbA = imagecolorat($imgA,$x,$y);
			   $rgbB = imagecolorat($imgB,$x,$y);
			   if(abs($rgbA-$rgbB)>1800000){
				   $t++;
				   $r+=$x;
			   }
		   }
		}
		return round($r/$t)-55;
	}
	private function getcdata($ans,$M,$randstr){
		for ($r = 0; $r < $M && $r < 1000; $r++) {
			$c = $randstr . $r;
			$d = md5 ($c);
			if ($ans == $d) {
				$a = $r;
				break;
			}
		}
		return $a;
	}
	private function generate_slideValue($width){
		$sx = rand(700,730);
		$sy = rand(295,300);
		$this->trace_x=$sx;
		$this->trace_y=$sy;
		$ex = $sx+intval(($width-55)/2);
		$stime = rand(100,300);
		$res = '['.$sx.','.$sy.','.$stime.'],';
		$randy = array(0,0,0,0,0,0,1,1,1,2,3,-1,-1,-1,-2);
		while($sx<$ex){
			$x=rand(3,9);
			$sx+=$x;
			$y=$randy[array_rand($randy)];
			$time=rand(9,18);
			$stime+=$time;
			$res .= '['.$x.','.$y.','.$time.'],';
		}
		$res .= '[0,0,'.rand(10,25).']';
		return $res;
	}
	private function generate_mousemove($width){
		$sx = rand(720,810);
		$sy = rand(270,290);
		$stime = rand(800,1000);
		$res = '['.$sx.','.$sy.','.$stime.'],';
		while($sx>$this->trace_x || $sy<$this->trace_y){
			if($sx>$this->trace_x)$x=rand(-5,-1);
			else $x=0;
			if($sy<$this->trace_y)$y=rand(1,2);
			else $y=0;
			$sx+=$x;
			$sy+=$y;
			$time=rand(9,16);
			$stime+=$time;
			$res .= '['.$x.','.$y.','.$time.'],';
		}
		$ex = $this->trace_x+intval(($width-55)/2);
		while($sx<$ex){
			$x=rand(1,6);
			$sx+=$x;
			$y=0;
			$time=rand(9,16);
			$stime+=$time;
			$res .= '['.$x.','.$y.','.$time.'],';
		}
		$res = substr($res,0,-1);
		$this->trace_time=ceil($stime/1000);
		$this->trace_x=$sx;
		$this->trace_y=$sy;
		return $res;
	}
	public function getcollect($width, $sid){
		$slideValue = $this->generate_slideValue($width);
		return $this->tdcData($sid, $slideValue);
	}
	private function tdcData($sid, $slideValue){
		$script = $this->get_curl('https://t.captcha.qq.com/tdc.js?app_data='.$sid.'&t='.time(), 0, 0, 0, 0, 0, 0, 1);
		$data = $this->get_curl('http://collect.qqzzz.net/',array('script'=>$script, 'slideValue'=>$slideValue, 'sid'=>$sid), 0, 0, 0, 0, 0, 1);
		return json_decode($data, true);
	}
	private function get_curl($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0,$noproxy=0){
		if($this->loginapi && $noproxy==0)return $this->get_curl_proxy($url,$post,$referer,$cookie,$header,$ua,$nobaody);
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
	private function get_curl_proxy($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0){
		$data = array('url'=>$url, 'post'=>$post, 'referer'=>$referer, 'cookie'=>$cookie, 'header'=>$header, 'ua'=>$ua, 'nobaody'=>$nobaody);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_URL,$this->loginapi);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
}