<?php
// uniCloud云存储客户端

namespace plugin\utility\unicloud;

use Exception;

class UnicloudClient {
	private $spaceId;
	private $clientSecret;
	private $endpoint = "https://api.bspapp.com";
	private $accessToken = null;

	function __construct($spaceId, $clientSecret){
		$this->spaceId = $spaceId;
		$this->clientSecret = $clientSecret;
	}

	public function set_access_token($accessToken){
		$this->accessToken = $accessToken;
	}

	// 获取AccessToken
	public function get_access_token(){
		if(!empty($this->accessToken)) return $this->accessToken;
		$param = [
			'method' => 'serverless.auth.user.anonymousAuthorize',
			'params' => '{}',
			'spaceId' => $this->spaceId,
			'timestamp' => $this->msec_time()
		];
		$sign = $this->get_sign($param);
		$payload = json_encode($param);
		$header = [
			'x-serverless-sign: '.$sign
		];
		$result = $this->curl_post($payload, $header);
		$this->accessToken = $result['accessToken'];
		return $this->accessToken;
	}

	// 获取文件上传信息
	public function pre_upload_file($filename){
		$method = 'serverless.file.resource.generateProximalSign';
		$params = [
			'env' => 'public',
			'filename' => $filename
		];
		$result = $this->send_request($method, $params);
		return $result;
	}

	// 完成文件上传
	public function complete_upload_file($id){
		$method = 'serverless.file.resource.report';
		$params = [
			'id' => $id
		];
		$this->send_request($method, $params);
		return true;
	}

	// 删除文件（不支持阿里云）
	public function delete_file($id){
		$method = 'serverless.file.resource.delete';
		$params = [
			'id' => $id
		];
		$this->send_request($method, $params);
		return true;
	}

	private function send_request($method, $params){
		$access_token = $this->get_access_token();
		$postparam = [
			'method' => $method,
			'params' => json_encode($params),
			'spaceId' => $this->spaceId,
			'timestamp' => $this->msec_time(),
			'token' => $access_token
		];
		$sign = $this->get_sign($postparam);
		$payload = json_encode($postparam);
		$header = [
			'x-basement-token: '.$access_token,
			'x-serverless-sign: '.$sign
		];
		$result = $this->curl_post($payload, $header);
		return $result;
	}

	private function get_sign($param){
		$signPars = "";
		ksort($param);
		foreach ($param as $k => $v) {
			if ($v != '') {
				$signPars .= $k . '=' . $v . '&';
			}
		}
		$signPars = substr($signPars, 0, -1);
		$sign = hash_hmac('md5', $signPars, $this->clientSecret);
		return $sign;
	}

	private function msec_time() {
		list($msec, $sec) = explode(' ', microtime());
		$msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
		return $msectime;
	}

	private function curl_post($payload, $header){
		$url = $this->endpoint.'/client';
		$httpheader[] = "accept: */*";
		$httpheader[] = "accept-encoding: gzip,deflate,sdch";
		$httpheader[] = "accept-language: zh-CN,zh;q=0.8";
		$httpheader[] = "cache-control: no-cache";
		$httpheader[] = "content-type: application/json";
		$httpheader[] = "pragma: no-cache";
		$httpheader[] = "connection: close";
		$httpheader = array_merge($httpheader, $header);
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36');
		$json=curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode==200){
			$arr=json_decode($json,true);
			if($arr['success'] == true){
				return $arr['data'];
			}else{
				throw new Exception($arr['error']['message'] ? $arr['error']['message'] : '未知错误');
			}
		}else{
			throw new Exception('curl error! httpcode='.$httpCode);
		}
	}
}
