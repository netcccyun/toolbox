<?php
error_reporting(0);
@header('Content-Type: text/html; charset=UTF-8');
function httpPost($url,$data){
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	$res = curl_exec($curl);
	curl_close($curl);
	return $res;
}

$image=trim($_POST['image']);
$data=httpPost('http://qrcode.api.cccyun.cc/qrcode_mine.php','image='.urlencode($image));
$arr=json_decode($data,true);
if($arr['code']==1) {
	exit('{"code":0,"msg":"succ","url":"'.$arr['url'].'"}');
}elseif(array_key_exists('msg',$arr)){
	exit('{"code":-1,"msg":"'.$arr['msg'].'"}');
}else{
	exit('{"code":-1,"msg":"'.$data.'"}');
}

?>