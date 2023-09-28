<?php
namespace app\lib;
/*
 * 快捷登录接口
 */

class Oauth{
	private $apiurl;
	private $appid;
	private $appkey;
	private $callback;

	function __construct($config){
		$this->apiurl = $config['apiurl'].'connect.php';
		$this->appid = $config['appid'];
		$this->appkey = $config['appkey'];
		$this->callback = $config['callback'];
	}

	//获取登录跳转url
	public function login($type, $state){

		//-------构造请求参数列表
		$keysArr = array(
			"act" => "login",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"redirect_uri" => $this->callback,
			"state" => $state
		);
		$login_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = get_curl($login_url);
		$arr = json_decode($response,true);
		return $arr;
	}

	//登录成功返回网站
	public function callback($code){
		//-------请求参数列表
		$keysArr = array(
			"act" => "callback",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"code" => $code
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = get_curl($token_url);

		$arr = json_decode($response,true);
		return $arr;
	}

	//查询用户信息
	public function query($type, $social_uid){
		//-------请求参数列表
		$keysArr = array(
			"act" => "query",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"social_uid" => $social_uid
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = get_curl($token_url);

		$arr = json_decode($response,true);
		return $arr;
	}
}
