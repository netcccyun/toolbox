<?php
//error_reporting(E_ALL); ini_set("display_errors", 1);
error_reporting(0);
define('ROOT', dirname(__FILE__).'/');
date_default_timezone_set('Asia/Shanghai');
$date = date("Y-m-d H:i:s");

require_once(ROOT."baidupan.class.php");


function daddslashes($string, $force = 0, $strip = FALSE) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}

function parselink($link){
	if(substr($link,0,8)!='bdpan://')return false;
	$arr = explode('|',$link);
	$filename = $arr[1];
	$content_md5 = $arr[2];
	$slice_md5 = $arr[3];
	$content_crc32 = $arr[4];
	$content_length = $arr[5];
	if($filename && $content_md5 && $slice_md5 && $content_crc32 && $content_length){
		return array('filename'=>$filename, 'content_md5'=>$content_md5, 'slice_md5'=>$slice_md5, 'content_crc32'=>$content_crc32, 'content_length'=>$content_length);
	}else{
		return false;
	}
}