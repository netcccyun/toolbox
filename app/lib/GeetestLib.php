<?php
namespace app\lib;
/**
 * 极验3.0 lib
 */
class GeetestLib
{
    const SDK_VERSION = 'php_3.0.0';
    const JSON_FORMAT = "1";
    
    private $geetest_id;
    private $geetest_key;

    public function __construct($geetest_id, $geetest_key) {
        $this->geetest_id  = $geetest_id;
        $this->geetest_key = $geetest_key;
    }

    //验证初始化
    public function pre_process($params) {
        if(!empty($this->geetest_id) && !empty($this->geetest_key)){
            return $this->pre_process_api($params);
        }else{
            return $this->pre_process_demo($params);
        }
    }

    private function pre_process_api($params) {
        $public_params = [
            'digestmod' => 'md5',
            'gt' => $this->geetest_id,
            'sdk' => self::SDK_VERSION,
            'json_format' => self::JSON_FORMAT
        ];
        $params = array_merge($params, $public_params);
        $url = 'http://api.geetest.com/register.php?' . http_build_query($params);
        $res = get_curl($url);
        $arr = json_decode($res, true);
        if($arr && isset($arr['challenge'])){
            return $this->success_process($arr['challenge']);
        }else{
            return $this->failback_process();
        }
    }

    private function success_process($challenge) {
        $challenge      = md5($challenge . $this->geetest_key);
        $result         = array(
            'success'   => 1,
            'gt'        => $this->geetest_id,
            'challenge' => $challenge,
            'new_captcha'=>true
        );
        return $result;
    }

    private function failback_process() {
        $challenge      = md5(uniqid(mt_rand(), true) . microtime());
        $result         = array(
            'success'   => 0,
            'gt'        => $this->geetest_id,
            'challenge' => $challenge,
            'new_captcha'=>true
        );
        return $result;
    }

    private function pre_process_demo($params) {
        $url = 'https://www.geetest.com/demo/gt/register-fullpage?t=' . time() . "123";
        $referer = 'https://www.geetest.com/demo/slide-popup.html';
        $data = get_curl($url, 0, $referer);
        $arr = json_decode($data, true);
        if($arr && isset($arr['challenge'])){
            return $arr;
        }else{
            return $this->failback_process();
        }
    }

    //正常流程下（即验证初始化成功），二次验证
    public function success_validate($challenge, $validate, $seccode, $params) {
        if(!empty($this->geetest_id) && !empty($this->geetest_key)){
            return $this->success_validate_api($challenge, $validate, $seccode, $params);
        }else{
            return $this->success_validate_demo($challenge, $validate, $seccode);
        }
    }

    private function success_validate_api($challenge, $validate, $seccode, $params) {
        if (!$this->check_validate($challenge, $validate)) {
            return false;
        }
        $public_params = [
            'seccode' => $seccode,
            'challenge' => $challenge,
            'captchaid' => $this->geetest_id,
            'sdk' => self::SDK_VERSION,
            'json_format' => self::JSON_FORMAT
        ];
        $params = array_merge($params, $public_params);
        $url = 'http://api.geetest.com/validate.php';
        $res = get_curl($url, http_build_query($params));
        $arr = json_decode($res, true);
        if($arr && isset($arr['seccode'])){
            if($arr['seccode'] == md5($seccode)){
                return true;
            }
        }
        return false;
    }

    private function check_validate($challenge, $validate) {
        if (strlen($validate) != 32) {
            return false;
        }
        if (md5($this->geetest_key . 'geetest' . $challenge) != $validate) {
            return false;
        }
        return true;
    }

    private function success_validate_demo($challenge, $validate, $seccode) {
        $params = [
            'geetest_challenge' => $challenge,
            'geetest_validate' => $validate,
            'geetest_seccode' => $seccode
        ];
        $url = 'https://www.geetest.com/demo/gt/validate-fullpage';
        $referer = 'https://www.geetest.com/demo/slide-popup.html';
        $data = get_curl($url, http_build_query($params), $referer);
        $arr = json_decode($data, true);
        if($arr && $arr['status'] == 'success'){
            return true;
        }
        return false;
    }

    //异常流程下（即验证初始化失败，宕机模式），二次验证
    public function fail_validate($challenge, $validate, $seccode) {
        if(md5($challenge) == $validate){
            return true;
        }else{
            return false;
        }
    }
}