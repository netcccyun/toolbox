<?php
/* Name: 百度登录操作类
 * Author: 消失的彩虹海
 * Website: blog.cccyun.cc
 * QQ: 1277180438
*/
error_reporting(0);

require 'login.class.php';
$login=new baiduLogin();
if($_GET['do']=='time'){
	$array=$login->servertime();
}
if($_GET['do']=='checkvc'){
	$array=$login->checkvc($_POST['user']);
}
if($_GET['do']=='sendcode'){
	$array=$login->sendcode($_POST['type'],$_POST['token'],$_POST['lurl'],$_POST['BAIDUID']);
}
if($_GET['do']=='login'){
	$array=$login->login($_POST['time'],$_POST['user'],$_POST['pwd'],$_POST['p'],$_POST['vcode'],$_POST['vcodestr'],$_POST['BAIDUID']);
}
if($_GET['do']=='login2'){
	$array=$login->login2($_POST['type'],$_POST['token'],$_POST['lurl'],$_POST['BAIDUID'],$_POST['vcode']);
}
if($_GET['do']=='getvcpic'){
	header('content-type:image/jpeg');
	echo $login->getvcpic($_GET['vcodestr']);
	exit;
}
if($_GET['do']=='getphone'){
	$array=$login->getphone($_POST['phone']);
}
if($_GET['do']=='sendsms'){
	$array=$login->sendsms($_POST['phone'],$_POST['BAIDUID'],$_POST['vcode'],$_POST['vcodestr'],$_POST['vcodesign']);
}
if($_GET['do']=='login3'){
	$array=$login->login3($_POST['phone'],$_POST['BAIDUID'],$_POST['smsvc']);
}
if($_GET['do']=='getqrcode'){
	$array=$login->getqrcode();
}
if($_GET['do']=='qrlogin'){
	$array=$login->qrlogin($_POST['sign']);
}
echo json_encode($array);