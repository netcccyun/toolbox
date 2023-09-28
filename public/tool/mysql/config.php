<?php
error_reporting(0);
// language pack
include "lang/index.php";

function daddslashes($string) {
	if(!get_magic_quotes_gpc()) {
		$string = addslashes($string);
	}
	return $string;
}

$k=trim($_GET['k']);
if($k=="") die('error');
$kz=base64_decode($k);
$kz=explode("^^^",$kz);
$host=daddslashes($kz[0]);
$user=daddslashes($kz[1]);
$pass=daddslashes($kz[2]);
$dbn=daddslashes($kz[3]);
$port=daddslashes($kz[4]);
$conn = @mysqli_connect($host, $user, $pass, $dbn, $port) or die ($lang["No_connection_to_mysql"].'(' . mysqli_connect_errno().')'.mysqli_connect_error());
mysqli_query($conn,"set sql_mode = ''");
mysqli_query($conn,"set names utf8");
?>