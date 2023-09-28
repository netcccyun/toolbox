<?php
/*删除好友
Author:消失的彩虹海
*/
error_reporting(0);
function get_curl($url, $post=0, $referer=0, $cookie=0, $header=0, $ua=0, $nobaody=0)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$httpheader[] = "Accept:application/json";
	$httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
	$httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
	$httpheader[] = "Connection:close";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	if ($post) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if ($header) {
		curl_setopt($ch, CURLOPT_HEADER, true);
	}
	if ($cookie) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	}
	if($referer){
		if($referer==1){
			curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
		}else{
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
	}
	if ($ua) {
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	}
	else {
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0");
	}
	if ($nobaody) {
		curl_setopt($ch, CURLOPT_NOBODY, 1);
	}
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}
function getGTK($skey){
	$len = strlen($skey);
	$hash = 5381;
	for ($i = 0; $i < $len; $i++) {
		$hash += ($hash << 5 & 2147483647) + ord($skey[$i]) & 2147483647;
		$hash &= 2147483647;
	}
	return $hash & 2147483647;
}

header("Content-type: text/html; charset=utf-8"); 
$uin = $_POST["uin"];
$skey = $_POST["skey"];
$pskey = $_POST["pskey"];
$touin = $_POST["touin"];
if(!$uin||!$skey||!$touin)exit;

$gtk=getGTK($pskey);
$cookie='pt2gguin=o0'.$uin.'; uin=o0'.$uin.'; skey='.$skey.'; p_skey='.$pskey.'; p_uin=o0'.$uin;
$ua='Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.152 Safari/537.36';
$url='https://h5.qzone.qq.com/proxy/domain/w.qzone.qq.com/cgi-bin/tfriend/friend_delete_qqfriend.cgi?g_tk='.$gtk;
$post='uin='.$uin.'&fupdate=1&num=1&fuin='.$touin.'&format=json&qzreferrer=http://user.qzone.qq.com/'.$uin.'/myhome/friends';

do{
$data=get_curl($url,$post,'https://user.qzone.qq.com/'.$uin.'/myhome/friends',$cookie,0,$ua);
$arr=json_decode($data,true);
if(@array_key_exists('code',$arr) && $arr['code']==0) {
	if($arr['data']['ret']==0) echo('{"code":0}');
	else echo('{"code":-2,"msg":"'.$arr["message"].'"}');
} elseif($arr['code']==-3000) {
	echo('{"code":-1,"msg":"SKEY已过期!"}');
} elseif(array_key_exists('code',$arr)) {
	echo('{"code":-2,"msg":"'.$arr["message"].'"}');
}
}while(!array_key_exists('code',$arr));
