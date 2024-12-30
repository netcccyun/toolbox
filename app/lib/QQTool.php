<?php
namespace app\lib;
/**
 * QQ空间工具类
 */

use Exception;

class QQTool{
	private $uin;
	private $cookie;
	private $gtk;
	private $skey;

	public function __construct($uin,$cookie,$is_skey = false){
		$this->uin=$uin;
		$this->cookie=$cookie;
		if($is_skey){
			$this->skey=getSubstr($cookie, 'skey=', ';');
			$this->gtk=$this->getGTK($this->skey);
		}else{
			$pskey=getSubstr($cookie, 'p_skey=', ';');
			$this->gtk=$this->getGTK($pskey);
		}
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

	private function getGTK2($skey){
		$salt = 5381;
		$md5key = 'tencentQQVIP123443safde&!%^%1282';
		$hash = array();
		$hash[] = ($salt << 5);
		for($i = 0; $i < strlen($skey); $i ++)
		{
			$ASCIICode = mb_convert_encoding($skey[$i], 'UTF-32BE', 'UTF-8');
			$ASCIICode = hexdec(bin2hex($ASCIICode));
			$hash[] = (($salt << 5) + $ASCIICode);
			$salt = $ASCIICode;
		}
		$md5str = md5(implode($hash) . $md5key);
		return $md5str;
	}

	//好友与分组列表
	public function friendlist(){
		$url = 'https://mobile.qzone.qq.com/friend/mfriend_list?g_tk='.$this->gtk.'&res_uin='.$this->uin.'&res_type=normal&format=json&count_per_page=10&page_index=0&page_type=0&mayknowuin=&qqmailstat=';
		$json = get_curl($url,0,1,$this->cookie);
		$json = mb_convert_encoding($json, "UTF-8", "UTF-8");
		$arr = json_decode($json, true);
		if(!$arr){
			throw new Exception('好友列表获取失败！');
		}elseif(isset($arr['code']) && $arr['code']==0){
			return $arr["data"];
		}elseif ($arr["code"] == -3000) {
			session('qq_cookie_qzone', null);
			throw new Exception('当前QQ登录状态已失效，请重新登录！');
		}elseif (isset($arr["message"])) {
			throw new Exception('好友列表获取失败！'.$arr["message"]);
		}else{
			throw new Exception('好友列表获取失败！');
		}
	}

	//说说列表
	public function shuoshuolist($count){
		$url='https://mobile.qzone.qq.com/list?g_tk='.$this->gtk.'&res_attach=&format=json&list_type=shuoshuo&action=0&res_uin='.$this->uin.'&count='.$count;
		$data = get_curl($url,0,1,$this->cookie);
		$arr=json_decode($data,true);
		if (isset($arr['code']) && $arr['code']==0) {
			if(isset($arr['data']['vFeeds']))
				return $arr['data']['vFeeds'];
			else
				return $arr['data']['feeds']['vFeeds'];
		}elseif ($arr["code"] == -3000) {
			session('qq_cookie_qzone', null);
			throw new Exception('当前QQ登录状态已失效，请重新登录！');
		}elseif (isset($arr["message"])) {
			throw new Exception('说说列表获取失败！'.$arr["message"]);
		}else{
			throw new Exception('说说列表获取失败！');
		}
	}

	//说说最多点赞数
	public function shuoshuozancount($count){
		$zan = 0;
		$list = $this->shuoshuolist($count);
		foreach($list as $row){
			if($row['like']['num']>$zan) $zan=$row['like']['num'];
		}
		return $zan;
	}

	//秒赞检测
	public function mzjc(){
		$arr = $this->friendlist();
		$friend=$arr["list"];
		$gpnames=$arr["gpnames"];

		foreach($gpnames as $gprow){
			$gpid=$gprow['gpid'];
			$gpname[$gpid]=$gprow['gpname'];
		}

		$arr = $this->shuoshuolist('5');
		$qqrow=array();
		$qquins=array();
		foreach ($arr as $row ) {
			$url='https://users.qzone.qq.com/cgi-bin/likes/get_like_list_app?uin='.$this->uin.'&unikey='.urlencode($row['comm']['curlikekey']).'&begin_uin=0&query_count=200&if_first_page=1&g_tk='.$this->gtk;
			$data2 = get_curl($url,0,'https://user.qzone.qq.com/',$this->cookie);
			if(!$data2){
				throw new Exception('说说点赞列表获取失败！可更新SKEY后重试');
			}
			preg_match('/_Callback\((.*?)\)\;/is',$data2,$json);
			$arr2=json_decode($json[1],true);
			$data2=$arr2['data']['like_uin_info'];
			foreach ($data2 as $row2 ) {
				$fuin=$row2['fuin'];
				if(isset($qqrow[$fuin])){$qqrow[$fuin]++;}
				else {$qqrow[$fuin]=1;$qquins[]=$fuin;}
			}
		}

		$mzcount=count($qqrow);
		foreach ($friend as $row3 ) {
			$fuin=$row3['uin'];
			if(isset($qqrow[$fuin]))$list['mz']=$qqrow[$fuin];
			else $list['mz']=0;
			$list['uin']=$row3['uin'];
			$list['name']=$row3['nick'];
			if($row3['remark'])$list['remark']=$row3['remark'];
			else $list['remark']=$row3['nick'];
			$list['groupid']=$row3['groupid'];
			$result['friend'][]=$list;
			unset($list);
		}
		rsort($result['friend']);
		$friend=$result['friend'];
		$fcount=count($friend);
		$array=array();
		foreach($friend as $nrow){
			if($nrow['mz']) $array[$nrow['groupid']]['mzcount']=$array[$nrow['groupid']]['mzcount']+1;
			$array[$nrow['groupid']][]=$nrow;
		}
		$friend=$array;

		return [$fcount, $mzcount, $friend, $gpnames];
	}

	//查询当前是否VIP
	public function getisvip(){
		$data=get_curl('https://cgi.vip.qq.com/unipay/init?format=json&aid=vipminipay.pingtai.vipsite.nav_new&platform=pc&version=-1&isbreak=0&g_tk='.$this->getGTK2($this->skey),0,'https://vip.qq.com/',$this->cookie);
		$arr=json_decode($data,true);
		if($arr['ret']==-7) {
			throw new Exception('SKEY已失效！');
		}
		$isqqvip=$arr['recParam']['is_vip'];
		return $isqqvip;
	}

	//修改QQ昵称
	public function setnickname($nickname){
		$url="https://h5.qzone.qq.com/proxy/domain/w.qzone.qq.com/cgi-bin/user/cgi_apply_updateuserinfo_new?g_tk=".$this->gtk;
		$data="qzreferrer=http%3A%2F%2Fctc.qzs.qq.com%2Fqzone%2Fv6%2Fsetting%2Fprofile%2Fprofile.html%3Ftab%3Dbase&nickname=".urlencode($nickname)."&emoji=&sex=1&birthday=2015-01-01&province=0&city=PAR&country=FRA&marriage=6&bloodtype=5&hp=0&hc=PAR&hco=FRA&career=&company=&cp=0&cc=0&cb=&cco=0&lover=&islunar=0&mb=1&uin=".$this->uin."&pageindex=1&nofeeds=1&fupdate=1&format=json";
		$return=get_curl($url,$data,$url,$this->cookie);
		$arr=json_decode($return,true);
		if(!$arr){
			throw new Exception('更换昵称失败');
		}elseif(isset($arr['code']) && $arr['code']==0){
			return true;
		}elseif($arr["code"] == -3000) {
			session('qq_cookie_qzone', null);
			throw new Exception('当前QQ登录状态已失效，请重新登录！');
		}elseif(isset($arr['message'])){
			throw new Exception($arr['message']);
		}else{
			throw new Exception('更换昵称失败');
		}
	}

	public function set_online_status($model, $desc, $imei){
		$pt4_token = getSubstr($this->cookie, 'pt4_token=', ';');
		$referer = 'https://club.vip.qq.com/onlinestatus/set?_wv=67109895&_wvx=10&_proxy=1&src=2';
		$ua = 'Mozilla/5.0 (Linux; Android 12; IN2010 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/97.0.4692.98 Mobile Safari/537.36 V1_AND_SQ_8.8.68_2538_YYB_D A_8086800 QQ/8.8.88 NetType/4G';
		$param = ['servicesName'=>'VIP.CustomOnlineStatusServer.CustomOnlineStatusObj', 'cmd'=>'SetCustomOnlineStatus', 'args'=>[['sModel'=>$model, 'iAppType'=>3, 'sIMei'=>$imei, 'sVer'=>'8.8.88', 'sManu'=>'', 'lUin'=>intval($this->uin), 'bShowInfo'=>true, 'sDesc'=>$desc, 'sModelShow'=> $model]]];
		$url = 'https://club.vip.qq.com/srf-cgi-node?srfname=VIP.CustomOnlineStatusServer.CustomOnlineStatusObj.SetCustomOnlineStatus&ts='.time().'000&daid=18&g_tk='.$this->gtk.'&pt4_token='.urlencode($pt4_token);
		$data = get_curl($url, json_encode($param), $referer, $this->cookie, 0, $ua, 0, ['Content-Type: application/json']);
		$arr = json_decode($data, true);
		if(!$arr){
			throw new Exception('修改在线状态失败，返回数据解析失败');
		}elseif(isset($arr['ret']) && $arr['ret']==0){
			if(isset($arr['data']['rsp']['iRet']) && $arr['data']['rsp']['iRet']==0){
				return true;
			}elseif(isset($arr['data']['rsp']['sMsg'])){
				throw new Exception('修改在线状态失败，'.$arr['data']['rsp']['sMsg']);
			}else{
				throw new Exception('修改在线状态失败，'.$data);
			}
		}else{
			throw new Exception('修改在线状态失败，'.$data);
		}
	}
}