<?php
/**
 * 百度网盘操作类
 *
 * @author 消失的彩虹海
 * @website www.cccyun.cc
 * @version 2.1
 */
class Baidupan
{
	public $msg;
	private $cookie;
	private $mstring = 'devuid=257744010452368&clienttype=1&channel=android_4.4.2_MI%206%20_bd-netdisk_1523a&version=8.5.0&vip=2&network_type=wifi&apn_id=1_0&freeisp=0&queryfree=0';
	public function __construct($bduss)
	{
		$this->cookie='BDUSS='.$bduss.';';
    }

	/**
     * 检测BUDSS是否有效
     * @return int
     */
	public function checkcookie() {
		$url='https://pan.baidu.com/api/quota?clienttype=1&app_id=250528&web=1';
		$data=$this->get_curl($url,0,0,$this->cookie);
		$arr=json_decode($data,true);
		if($arr['errno']==0){
			return true;
		}else{
			return false;
		}
	}


	/**
     * 获取文件列表
	 * @param string $path 文件路径
	 * @param int $num 显示数量
	 * @param string $order 按什么排序
	 * @param int $desc 是否为降序
	 * @param int $page 页数
     * @return array
     */
	public function getlist($path='/', $num=100, $order='name', $desc=0, $page=1) {
		$url='https://pan.baidu.com/api/list?dir='.urlencode($path).'&num='.$num.'&order='.$order.'&desc='.$desc.'&showempty=0&page='.$page.'&web=1&'.$this->mstring;
		$data=$this->get_curl($url,0,0,$this->cookie);
		$arr=json_decode($data,true);
		if(array_key_exists('errno',$arr) && $arr['errno']==0){
			return $arr['list'];
		}elseif($arr['errno']==-6){
			$this->msg='BDUSS已经失效';
			return false;
		}elseif($arr['errno']==-9){
			$this->msg='路径不存在';
			return false;
		}else{
			$this->msg='参数错误';
			return false;
		}
	}

	/**
     * 获取单个文件信息
	 * @param string $path 文件路径
	 * @param int $media 是否多媒体文件
     * @return array
     */
	public function getmeta($path, $media=0) {
		$target=urlencode('['.json_encode($path).']');
		$url='https://pan.baidu.com/api/filemetas?target='.$target.'&media='.$media.'&dlink=1&'.$this->mstring;
		$data=$this->get_curl($url,0,0,$this->cookie);
		$arr=json_decode($data,true);
		if(array_key_exists('errno',$arr) && $arr['errno']==0){
			return $arr['info'];
		}elseif($arr['errno']==-6){
			$this->msg='BDUSS已经失效';
			return false;
		}elseif($arr['errno']==12){
			$this->msg='该文件不存在';
			return false;
		}else{
			$this->msg='参数错误';
			return false;
		}
	}

	/**
     * 获取文件下载直链
	 * @param string $path 文件路径
     * @return string
     */
	public function getlink($path) {
		$url='https://d.pcs.baidu.com/rest/2.0/pcs/file?method=locatedownload&path='.urlencode($path).'&ver=2.0&dtype=0&esl=1&ehps=0&app_id=250528&check_blue=1&'.$this->mstring.'&time='.time().'225&cuid=E5043F7C37B7BB71B2A94D932F30B2AE%7C257744010452368';
		$data=$this->get_curl($url,0,0,$this->cookie,0,$_SERVER['HTTP_USER_AGENT']);
		$arr=json_decode($data,true);
		if(array_key_exists('urls',$arr)){
			return $arr['urls'][0]['url'].'&vip=2';
		}elseif($arr['error_code']==31045){
			$this->msg='BDUSS已经失效';
			return false;
		}elseif($arr['error_code']==31066){
			$this->msg='该文件不存在';
			return false;
		}else{
			$this->msg='['.$arr['errno'].']'.$arr['error_msg'];
			return false;
		}
	}

	/**
     * 极速秒传
	 * @param string $path 上传的文件路径
	 * @param string $content_md5 文件的MD5
	 * @param string $slice_md5 文件校验段的MD5(校验段为文件的前256KB)
	 * @param string $content_crc32 文件的CRC32
	 * @param string $content_length 文件大小(字节)
     * @return string
     */
	public function rapidupload($path,$content_md5,$slice_md5,$content_crc32,$content_length) {
		$url='https://pan.baidu.com/api/rapidupload?clienttype=6&version=2.0.0.3';
		$post='path='.urlencode($path).'&content-md5='.$content_md5.'&slice-md5='.$slice_md5.'&content-crc32='.$content_crc32.'&content-length='.$content_length;
		$data=$this->get_curl($url,$post,0,$this->cookie,0,'netdisk;2.0.0.3;PC;PC-Windows;10.0.16299;uploadplugin');
		$arr=json_decode($data,true);
		if(array_key_exists('errno',$arr) && $arr['errno']==0){
			return $arr['info']['path'];
		}elseif($arr['errno']==404){
			$this->msg='该链接已失效';
			return false;
		}elseif($arr['errno']==-6){
			$this->msg='BDUSS已经失效';
			return false;
		}elseif($arr['errno']==-8){
			$this->msg='已存在重名文件';
			return false;
		}else{
			$this->msg='转存失败['.$arr['errno'].']';
			return false;
		}
	}

	public function get_curl($url,$post=0,$referer=0,$cookie=0,$header=0,$ua=0,$nobaody=0){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$httpheader[] = "Accept:*/*";
		$httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
		$httpheader[] = "Connection:close";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		if($header){
			curl_setopt($ch, CURLOPT_HEADER, TRUE);
		}
		if($cookie){
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		if($referer){
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
		if($ua){
			curl_setopt($ch, CURLOPT_USERAGENT,$ua);
		}else{
			curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 4.4.2; zh-cn) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36');
		}
		if($nobaody){
			curl_setopt($ch, CURLOPT_NOBODY,1);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
}