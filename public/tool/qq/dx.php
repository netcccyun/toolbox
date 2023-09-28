<?php
error_reporting(0);
function getGTK($skey){
	$len = strlen($skey);
	$hash = 5381;
	for ($i = 0; $i < $len; $i++) {
		$hash += ($hash << 5 & 2147483647) + ord($skey[$i]) & 2147483647;
		$hash &= 2147483647;
	}
	return $hash & 2147483647;
}
function get_curl($url, $post=0, $referer=0, $cookie=0, $header=0, $ua=0, $nobaody=0)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
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

@header("Content-Type: text/html; charset=UTF-8");
$uin = $_POST["uin"];
$skey = $_POST["skey"];
$pskey = $_POST["pskey"];
$touin = $_POST["touin"];
if(!$uin||!$skey||!$touin)exit;

$uins=explode(',',$touin);

$gtk=getGTK($pskey);
$cookie='pt2gguin=o0'.$uin.'; uin=o0'.$uin.'; skey='.$skey.'; p_skey='.$pskey.'; p_uin=o0'.$uin;

$dxrow['code']=0;
$dxrow['msg']='suc';
foreach($uins as $touin){
	if(!$touin)continue;

	$url='https://client.show.qq.com/cgi-bin/qqshow_client_showinfo?g_tk='.$gtk.'&omode=4&uin='.$uin.'&touin='.$touin.'&cmd=10413';
	$data=get_curl($url,0,$url,$cookie);
	$arr = json_decode($data,true);
	if ($arr['code']=='-1')$result=array('touin'=>$touin,'is'=>'0');
	else $result=array('touin'=>$touin,'is'=>'1');

	/*$url='http://social.show.qq.com/cgi-bin/qqshow_camera_noname?g_tk='.$gtk;
	$post="uin=".$touin."%7C".$uin."&friend=1";
	$data=get_curl($url,$post,$url,$cookie);
	preg_match('/xfriend=\"(\d+)\"/',$data,$match);
	if($match[1] === '0')$result=array('touin'=>$touin,'is'=>'0');
	else $result=array('touin'=>$touin,'is'=>'1');*/

	$dxrow['data'][]=$result;
}
exit(json_encode($dxrow));
