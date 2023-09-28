<?php
error_reporting(0);

require 'qqlogin.class.php';
$login=new qq_login();
if($_GET['do']=='getqrpic'){
	$array = $login->getqrpic();
}
if($_GET['do']=='qrlogin'){
	$array = $login->qrlogin($_GET['qrsig']);
}
if($_GET['do']=='qqconnect'){
	$array = $login->qqconnect($_POST['redirect_uri'], $_POST['mkey']);
}
if($_GET['do']=='getwxpic'){
	$array = $login->getwxpic();
}
if($_GET['do']=='wxlogin'){
	$array = $login->wxlogin($_GET['uuid'], $_GET['last']);
}
echo json_encode($array);