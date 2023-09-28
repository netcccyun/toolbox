<?php
namespace plugin\utility\sport;

use Exception;

class XiaomiSport
{

    private function getAccess($username, $password){
		if(!strpos($username, '@')) $username = '+86'.$username;
        $url = 'https://api-user.huami.com/registrations/'.$username.'/tokens';
        $data['client_id'] = 'HuaMi';
        $data['password'] = $password;
        $data['redirect_uri'] = 'https://s3-us-west-2.amazonaws.com/hm-registration/successsignin.html';
        $data['token'] = 'access';
        $response = $this->curl($url, $data);
        preg_match("/access=(.*?)&/", $response['header'], $access);
        if(isset($access[1])){
            return $access[1];
        }elseif(strpos($response['header'], 'error=')){
            throw new Exception('账号或密码错误！');
        }else{
            throw new Exception('登录token接口请求失败');
        }
    }

    public function login($username, $password){
        $access = $this->getAccess($username, $password);
        $url = 'https://account.huami.com/v2/client/login';
        $data = [
            'app_name' => 'com.xiaomi.hm.health',
            'app_version' => '4.6.0',
            'code' => $access,
            'country_code' => 'CN',
            'device_id' => '2C8B4939-0CCD-4E94-8CBA-CB8EA6E613A1',
            'device_model' => 'phone',
            'grant_type' => 'access_token',
            'third_name' => 'huami',
        ];
        $response = $this->curl($url, $data);
        $arr = json_decode($response['body'], true);
        if(!$arr){
            throw new Exception('登录接口请求失败');
        }elseif(isset($arr['result']) && $arr['result']=='ok'){
            $token = $arr['token_info']['app_token'];
            $userid = $arr['token_info']['user_id'];
            return ['token' => $token, 'userid' => $userid];
        }else{
            throw new Exception('登录失败'.$response['body']);
        }
    }

    public function step($userid, $token, $step){
        $url = "https://api-mifit-cn.huami.com/v1/data/band_data.json?&t=" . time();

        $json = '[{"data_hr":"\/\/\/\/\/\/9L\/\/\/\/\/\/\/\/\/\/\/\/Vv\/\/\/\/\/\/\/\/\/\/\/0v\/\/\/\/\/\/\/\/\/\/\/9e\/\/\/\/\/0n\/a\/\/\/S\/\/\/\/\/\/\/\/\/\/\/\/0b\/\/\/\/\/\/\/\/\/\/1FK\/\/\/\/\/\/\/\/\/\/\/\/R\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/9PTFFpaf9L\/\/\/\/\/\/\/\/\/\/\/\/R\/\/\/\/\/\/\/\/\/\/\/\/0j\/\/\/\/\/\/\/\/\/\/\/9K\/\/\/\/\/\/\/\/\/\/\/\/Ov\/\/\/\/\/\/\/\/\/\/\/zf\/\/\/86\/zr\/Ov88\/zf\/Pf\/\/\/0v\/S\/8\/\/\/\/\/\/\/\/\/\/\/\/\/Sf\/\/\/\/\/\/\/\/\/\/\/z3\/\/\/\/\/\/0r\/Ov\/\/\/\/\/\/S\/9L\/zb\/Sf9K\/0v\/Rf9H\/zj\/Sf9K\/0\/\/N\/\/\/\/0D\/Sf83\/zr\/Pf9M\/0v\/Ov9e\/\/\/\/\/\/\/\/\/\/\/\/S\/\/\/\/\/\/\/\/\/\/\/\/zv\/\/z7\/O\/83\/zv\/N\/83\/zr\/N\/86\/z\/\/Nv83\/zn\/Xv84\/zr\/PP84\/zj\/N\/9e\/zr\/N\/89\/03\/P\/89\/z3\/Q\/9N\/0v\/Tv9C\/0H\/Of9D\/zz\/Of88\/z\/\/PP9A\/zr\/N\/86\/zz\/Nv87\/0D\/Ov84\/0v\/O\/84\/zf\/MP83\/zH\/Nv83\/zf\/N\/84\/zf\/Of82\/zf\/OP83\/zb\/Mv81\/zX\/R\/9L\/0v\/O\/9I\/0T\/S\/9A\/zn\/Pf89\/zn\/Nf9K\/07\/N\/83\/zn\/Nv83\/zv\/O\/9A\/0H\/Of8\/\/zj\/PP83\/zj\/S\/87\/zj\/Nv84\/zf\/Of83\/zf\/Of83\/zb\/Nv9L\/zj\/Nv82\/zb\/N\/85\/zf\/N\/9J\/zf\/Nv83\/zj\/Nv84\/0r\/Sv83\/zf\/MP\/\/\/zb\/Mv82\/zb\/Of85\/z7\/Nv8\/\/0r\/S\/85\/0H\/QP9B\/0D\/Nf89\/zj\/Ov83\/zv\/Nv8\/\/0f\/Sv9O\/0ZeXv\/\/\/\/\/\/\/\/\/\/\/1X\/\/\/\/\/\/\/\/\/\/\/9B\/\/\/\/\/\/\/\/\/\/\/\/TP\/\/\/1b\/\/\/\/\/\/0\/\/\/\/\/\/\/\/\/\/\/\/9N\/\/\/\/\/\/\/\/\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+\/v7+","date":"' . date('Y-m-d') . '","data":[{"start":0,"stop":1439,"value":"UA8AUBQAUAwAUBoAUAEAYCcAUBkAUB4AUBgAUCAAUAEAUBkAUAwAYAsAYB8AYB0AYBgAYCoAYBgAYB4AUCcAUBsAUB8AUBwAUBIAYBkAYB8AUBoAUBMAUCEAUCIAYBYAUBwAUCAAUBgAUCAAUBcAYBsAYCUAATIPYD0KECQAYDMAYB0AYAsAYCAAYDwAYCIAYB0AYBcAYCQAYB0AYBAAYCMAYAoAYCIAYCEAYCYAYBsAYBUAYAYAYCIAYCMAUB0AUCAAUBYAUCoAUBEAUC8AUB0AUBYAUDMAUDoAUBkAUC0AUBQAUBwAUA0AUBsAUAoAUCEAUBYAUAwAUB4AUAwAUCcAUCYAUCwKYDUAAUUlEC8IYEMAYEgAYDoAYBAAUAMAUBkAWgAAWgAAWgAAWgAAWgAAUAgAWgAAUBAAUAQAUA4AUA8AUAkAUAIAUAYAUAcAUAIAWgAAUAQAUAkAUAEAUBkAUCUAWgAAUAYAUBEAWgAAUBYAWgAAUAYAWgAAWgAAWgAAWgAAUBcAUAcAWgAAUBUAUAoAUAIAWgAAUAQAUAYAUCgAWgAAUAgAWgAAWgAAUAwAWwAAXCMAUBQAWwAAUAIAWgAAWgAAWgAAWgAAWgAAWgAAWgAAWgAAWREAWQIAUAMAWSEAUDoAUDIAUB8AUCEAUC4AXB4AUA4AWgAAUBIAUA8AUBAAUCUAUCIAUAMAUAEAUAsAUAMAUCwAUBYAWgAAWgAAWgAAWgAAWgAAWgAAUAYAWgAAWgAAWgAAUAYAWwAAWgAAUAYAXAQAUAMAUBsAUBcAUCAAWwAAWgAAWgAAWgAAWgAAUBgAUB4AWgAAUAcAUAwAWQIAWQkAUAEAUAIAWgAAUAoAWgAAUAYAUB0AWgAAWgAAUAkAWgAAWSwAUBIAWgAAUC4AWSYAWgAAUAYAUAoAUAkAUAIAUAcAWgAAUAEAUBEAUBgAUBcAWRYAUA0AWSgAUB4AUDQAUBoAXA4AUA8AUBwAUA8AUA4AUA4AWgAAUAIAUCMAWgAAUCwAUBgAUAYAUAAAUAAAUAAAUAAAUAAAUAAAUAAAUAAAUAAAWwAAUAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAeSEAeQ8AcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBcAcAAAcAAAcCYOcBUAUAAAUAAAUAAAUAAAUAUAUAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcCgAeQAAcAAAcAAAcAAAcAAAcAAAcAYAcAAAcBgAeQAAcAAAcAAAegAAegAAcAAAcAcAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcCkAeQAAcAcAcAAAcAAAcAwAcAAAcAAAcAIAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcCIAeQAAcAAAcAAAcAAAcAAAcAAAeRwAeQAAWgAAUAAAUAAAUAAAUAAAUAAAcAAAcAAAcBoAeScAeQAAegAAcBkAeQAAUAAAUAAAUAAAUAAAUAAAUAAAcAAAcAAAcAAAcAAAcAAAcAAAegAAegAAcAAAcAAAcBgAeQAAcAAAcAAAcAAAcAAAcAAAcAkAegAAegAAcAcAcAAAcAcAcAAAcAAAcAAAcAAAcA8AeQAAcAAAcAAAeRQAcAwAUAAAUAAAUAAAUAAAUAAAUAAAcAAAcBEAcA0AcAAAWQsAUAAAUAAAUAAAUAAAUAAAcAAAcAoAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAYAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBYAegAAcAAAcAAAegAAcAcAcAAAcAAAcAAAcAAAcAAAeRkAegAAegAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAEAcAAAcAAAcAAAcAUAcAQAcAAAcBIAeQAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBsAcAAAcAAAcBcAeQAAUAAAUAAAUAAAUAAAUAAAUBQAcBYAUAAAUAAAUAoAWRYAWTQAWQAAUAAAUAAAUAAAcAAAcAAAcAAAcAAAcAAAcAMAcAAAcAQAcAAAcAAAcAAAcDMAeSIAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcAAAcBQAeQwAcAAAcAAAcAAAcAMAcAAAeSoAcA8AcDMAcAYAeQoAcAwAcFQAcEMAeVIAaTYAbBcNYAsAYBIAYAIAYAIAYBUAYCwAYBMAYDYAYCkAYDcAUCoAUCcAUAUAUBAAWgAAYBoAYBcAYCgAUAMAUAYAUBYAUA4AUBgAUAgAUAgAUAsAUAsAUA4AUAMAUAYAUAQAUBIAASsSUDAAUDAAUBAAYAYAUBAAUAUAUCAAUBoAUCAAUBAAUAoAYAIAUAQAUAgAUCcAUAsAUCIAUCUAUAoAUA4AUB8AUBkAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAAfgAA","tz":32,"did":"DA932FFFFE8816E7","src":24}],"summary":"{\"v\":6,\"slp\":{\"st\":1628296479,\"ed\":1628296479,\"dp\":0,\"lt\":0,\"wk\":0,\"usrSt\":-1440,\"usrEd\":-1440,\"wc\":0,\"is\":0,\"lb\":0,\"to\":0,\"dt\":0,\"rhr\":0,\"ss\":0},\"stp\":{\"ttl\":' . $step . ',\"dis\":10627,\"cal\":510,\"wk\":41,\"rn\":50,\"runDist\":7654,\"runCal\":397,\"stage\":[{\"start\":327,\"stop\":341,\"mode\":1,\"dis\":481,\"cal\":13,\"step\":680},{\"start\":342,\"stop\":367,\"mode\":3,\"dis\":2295,\"cal\":95,\"step\":2874},{\"start\":368,\"stop\":377,\"mode\":4,\"dis\":1592,\"cal\":88,\"step\":1664},{\"start\":378,\"stop\":386,\"mode\":3,\"dis\":1072,\"cal\":51,\"step\":1245},{\"start\":387,\"stop\":393,\"mode\":4,\"dis\":1036,\"cal\":57,\"step\":1124},{\"start\":394,\"stop\":398,\"mode\":3,\"dis\":488,\"cal\":19,\"step\":607},{\"start\":399,\"stop\":414,\"mode\":4,\"dis\":2220,\"cal\":120,\"step\":2371},{\"start\":415,\"stop\":427,\"mode\":3,\"dis\":1268,\"cal\":59,\"step\":1489},{\"start\":428,\"stop\":433,\"mode\":1,\"dis\":152,\"cal\":4,\"step\":238},{\"start\":434,\"stop\":444,\"mode\":3,\"dis\":2295,\"cal\":95,\"step\":2874},{\"start\":445,\"stop\":455,\"mode\":4,\"dis\":1592,\"cal\":88,\"step\":1664},{\"start\":456,\"stop\":466,\"mode\":3,\"dis\":1072,\"cal\":51,\"step\":1245},{\"start\":467,\"stop\":477,\"mode\":4,\"dis\":1036,\"cal\":57,\"step\":1124},{\"start\":478,\"stop\":488,\"mode\":3,\"dis\":488,\"cal\":19,\"step\":607},{\"start\":489,\"stop\":499,\"mode\":4,\"dis\":2220,\"cal\":120,\"step\":2371},{\"start\":500,\"stop\":511,\"mode\":3,\"dis\":1268,\"cal\":59,\"step\":1489},{\"start\":512,\"stop\":522,\"mode\":1,\"dis\":152,\"cal\":4,\"step\":238}]},\"goal\":8000,\"tz\":\"28800\"}","source":24,"type":0}]';

        $data = [
            'data_json' => $json,
            'userid' => $userid,
            'device_type' => '0',
            'last_sync_data_time' => time().'',
            'last_deviceid' => 'DA932FFFFE8816E7',
        ];

        $response = $this->curl($url, $data, $token);
        $arr = json_decode($response['body'], true);
        if(!$arr){
            throw new Exception('修改步数接口请求失败');
        }elseif(isset($arr['code']) && $arr['code']==1){
            return true;
        }else{
            throw new Exception('修改步数失败，'.isset($arr['message'])?$arr['message']:$response['body']);
        }
    }


    private function curl($url, $data=null, $app_token=null){
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		$httpheader[] = "Accept: application/json";
		$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
		$httpheader[] = "Connection: keep-alive";
        if($app_token){
            $httpheader[] = "apptoken: ".$app_token;
        }
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		if($data){
			if(is_array($data)) $data=http_build_query($data);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_POST,1);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/7.0.12(0x17000c2d) NetType/WIFI Language/zh_CN');
		curl_setopt($ch, CURLOPT_HEADER, 1);
        $ret = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($ret, 0, $headerSize);
        $body = substr($ret, $headerSize);
        $ret = array();
        $ret['header'] = $header;
        $ret['body'] = $body;
        curl_close($ch);
        return $ret;
	}
}