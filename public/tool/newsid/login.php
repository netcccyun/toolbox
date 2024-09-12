<?php
error_reporting(0);

require 'login.class.php';
$login=new qq_login();
if($_GET['do']=='checkvc'){
	$array=$login->checkvc($_POST['uin']);
}
elseif($_GET['do']=='dovc'){
	$array=$login->dovc($_POST['uin'],$_POST['sig'],$_POST['ans'],$_POST['cap_cd'],$_POST['sess'],$_POST['websig'],$_POST['cdata'],$_POST['sid']);
}
elseif($_GET['do']=='getvc'){
	$array=$login->getvc($_POST['uin'],$_POST['sig'],$_POST['sess'],$_POST['sid'],$_POST['websig']);
}
elseif($_GET['do']=='qqlogin'){
	$array=$login->qqlogin($_POST['uin'],$_POST['pwd'],$_POST['p'],$_POST['vcode'],$_POST['pt_verifysession'],$_POST['sid'],$_POST['cookie'],$_POST['sms_code'],$_POST['sms_ticket']);
}
elseif($_GET['do']=='smscode'){
	$array=$login->send_sms_code($_POST['uin'],$_POST['sms_ticket'],$_POST['cookie']);
}
elseif($_GET['do']=='getqrpic'){
	$array=$login->getqrpic();
}
elseif($_GET['do']=='qrlogin'){
	if(isset($_GET['findpwd']))session_start();
	$array=$login->qrlogin($_GET['qrsig']);
}
elseif($_GET['do']=='getqrpic3rd'){
	$array=$login->getqrpic3rd($_GET['daid'],$_GET['appid']);
}
elseif($_GET['do']=='qrlogin3rd'){
	$array=$login->qrlogin3rd($_GET['daid'],$_GET['appid'],$_GET['qrsig']);
}
header('Content-type: application/json');
echo json_encode($array);
