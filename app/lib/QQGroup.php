<?php
namespace app\lib;
/**
 * QQ群相关操作类
 */

use Exception;

class QQGroup{
	private $uin;
	private $cookie;
	private $gtk;
	private $ua = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36';

	public function __construct($uin, $cookie){
		$this->uin=$uin;
		preg_match('/skey=(.{10});/',$cookie,$skey);
		$this->gtk=$this->getGTK($skey[1]);
		$this->cookie=$cookie;
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

	//QQ群列表
	public function grouplist($onlyadmin = false){
		$url = 'https://qun.qq.com/cgi-bin/qun_mgr/get_group_list';
		$post = 'bkn='.$this->gtk;
		$data = get_curl($url,$post,'https://qun.qq.com/member.html',$this->cookie,0,$this->ua);
		$arr = json_decode($data,true);
		//print_r($arr);exit;
		if(!$arr){
			throw new Exception('QQ群列表获取失败！');
		} elseif(isset($arr['ec']) && $arr['ec']==0) {
			$group = [];
			if(isset($arr['create'])){
				foreach($arr['create'] as $row){
					$group[] = $row;
				}
			}
			if(isset($arr['manage'])){
				foreach($arr['manage'] as $row){
					$group[] = $row;
				}
			}
			if(!$onlyadmin && isset($arr['join'])){
				foreach($arr['join'] as $row){
					$group[] = $row;
				}
			}
			return $group;
		} elseif($arr['ec']==1 || $arr['ec']==4) {
			session('qq_cookie_qun', null);
			throw new Exception('当前QQ登录状态已失效，请重新登录！');
		} else {
			throw new Exception('QQ群列表获取失败！'.$arr['em']);
		}
	}

	//QQ群成员列表
	public function groupmemberlist($groupid, $start, $end){
		$url='https://qun.qq.com/cgi-bin/qun_mgr/search_group_members';
		$post='gc='.$groupid.'&st='.$start.'&end='.$end.'&sort=0&bkn='.$this->gtk;
		$data = get_curl($url,$post,'https://qun.qq.com/member.html',$this->cookie,0,$this->ua);
		$arr = json_decode($data,true);
		if (!$arr) {
			throw new Exception('QQ群成员获取失败！');
		}elseif ($arr["ec"] == 1) {
			throw new Exception('SKEY已失效！');
		}elseif ($arr["ec"]!=0){
			throw new Exception('QQ群成员获取失败！'.$arr['em']);
		}
		$data = array();
		$data['code'] = 0;
		$data['count'] = $arr['count'];
		$data['mems'] = $arr['mems'];
		if($end<$arr['count'])$data['start'] = $end+1;
		else $data['start'] = 0;
		return $data;
	}

	//群公告列表
	public function announcelist($groupid, $start){
		$url='https://web.qun.qq.com/cgi-bin/announce/list_announce';
		$post='bkn='.$this->gtk.'&qid='.$groupid.'&ft=23&s='.$start.'&n=10&ni=1&i=1';
		$data = get_curl($url,$post,'https://web.qun.qq.com/announce/index.html',$this->cookie,0,$this->ua);
		$arr = json_decode($data,true);
		if (!$arr) {
			throw new Exception('公告列表获取失败！');
		}elseif ($arr["ec"] == 1) {
			session('qq_cookie_qun', null);
			throw new Exception('当前QQ登录状态已失效，请重新登录！');
		}elseif ($arr["ec"]!=0){
			throw new Exception('公告列表获取失败！'.$arr['em']);
		}
		if(!isset($arr['feeds']) || !$arr['feeds'])return [];
		$uinlist = [];
		foreach($arr['ui'] as $uin => $row){
			$uinlist[$uin] = $row['n'];
		}
		$list = [];
		foreach($arr['feeds'] as $row){
			$msg = $row['msg']['text'];
			if(mb_strlen($msg, 'utf-8')>30)$msg=mb_substr($msg, 0, 30, 'utf-8').'...';
			if($row['pinned']==1)$msg = '<font color="red">[顶]</font>'.$msg;
			$list[] = ['fid'=>$row['fid'], 'uin'=>$row['u'], 'nick'=>$uinlist[$row['u']]?$uinlist[$row['u']]:$row['u'], 'time'=>date("Y-m-d H:i:s", $row['pubt']), 'msg'=>$msg];
		}
		return $list;
	}

	//删除群公告
	public function delannounce($groupid, $fid){
		$url='https://web.qun.qq.com/cgi-bin/announce/del_feed';
		$post='fid='.$fid.'&ft=23&bkn='.$this->gtk.'&qid='.$groupid.'&op=0';
		$data = get_curl($url,$post,'https://web.qun.qq.com/announce/index.html',$this->cookie,0,$this->ua);
		$arr = json_decode($data,true);
		if(isset($arr["ec"]) && ($arr["ec"]==0 || $arr["ec"]==14)){
			return true;
		}elseif ($arr["ec"] == 1) {
			throw new Exception('SKEY已失效！');
		}else{
			throw new Exception('公告删除失败！'.$arr['em']);
		}
	}

	//解散群
	public function dismissgroup($groupuin){
		$resultarr = array(11=>'需要验证码', 13=>'号码异常，暂时不允许解散', 15=>'为了企业信息安全，请登录企业帐户中心进行解散操作。', 16=>'公益群暂不支持解散。', 17=>'该群被转让不足28天，暂时还不能解散。', 25=>'付费2000人群不可解散。', 51=>'您的群已绑定了教育机构，如需进行此操作，请先与机构解绑。');

		$url = 'https://id.qq.com/qun/dismiss_group';
		$referrer = 'https://id.qq.com/proxy.html';
		$post = 'vc=undefined&gc='.$groupuin.'&uin='.$this->uin.'&s=1&bkn='.$this->gtk;
		$data = get_curl($url,$post,$referrer,$this->cookie,0,$this->ua);
		$arr = json_decode($data,true);
		if(isset($arr["ec"]) && $arr["ec"]==0){
			return true;
		}elseif ($arr["ec"] == 1) {
			session('qq_cookie_qqid', null);
			throw new Exception('当前QQ登录状态已失效，请重新登录！');
		}elseif(isset($arr['ec']) && array_key_exists($arr['ec'],$resultarr)){
			throw new Exception($resultarr[$arr['ec']]);
		}else{
			throw new Exception('解散群失败，可能非群主或群不存在。返回信息：'.$data);
		}
	}

	//获取加群链接
	public function getjoinlink($groupuin){
		$url = 'https://admin.qun.qq.com/cgi-bin/qun_admin/get_join_link';
		$referrer = 'https://admin.qun.qq.com/create/share/index.html?ptlang=2052&groupUin='.$groupuin;
		$post = 'gc='.$groupuin.'&type=1&bkn='.$this->gtk;
		$data = get_curl($url,$post,$referrer,$this->cookie);
		$arr = json_decode($data,true);
		if (isset($arr["ec"]) && $arr['ec']==0) {
			return $arr['url'];
		}elseif($arr['ec']==1){
			session('qq_cookie_qun', null);
			throw new Exception('加群链接获取失败，原因：SKEY已失效');
		}else{
			throw new Exception('加群链接获取失败 '.$arr['em']);
		}
	}
}