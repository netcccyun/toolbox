<?php
require 'inc.php';

$act=daddslashes($_GET['act']);

if($act=='save'){

$bduss=trim(daddslashes($_POST['bduss']));
$path=trim(daddslashes($_POST['path']));
$link=trim(daddslashes($_POST['link']));
if(empty($path))$path='/';
if(substr($path,0,1)!='/')exit('{"code":-1,"msg":"路径填写错误，路径如果需要自定义请以/开头"}');

$link_arr = parselink($link);
if(!$link_arr)exit('{"code":-1,"msg":"分享链接错误，请填写以bdpan://开头的专用分享链接"}');

$x=new Baidupan($bduss);

if(substr($path,-1,1)!='/')$path=$path.'/';
$path = '/'.$path.$link_arr['filename'];

if($result = $x->rapidupload($path,$link_arr['content_md5'],$link_arr['slice_md5'],$link_arr['content_crc32'],$link_arr['content_length'])){
	$result=array('code'=>0,'filename'=>$link_arr['filename'],'msg'=>'转存成功','path'=>$result);
}else{
	$result=array('code'=>-2,'filename'=>$link_arr['filename'],'msg'=>$x->msg);
}
echo json_encode($result);

}elseif($act=='check'){
	$bduss=trim(daddslashes($_POST['bduss']));
	$x=new Baidupan($bduss);
	if($x->checkcookie()){
		exit('{"code":0}');
	}else{
		exit('{"code":-1}');
	}
}