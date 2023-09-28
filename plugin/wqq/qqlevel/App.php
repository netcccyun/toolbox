<?php
/**
 * QQ等级查询
 */

namespace plugin\wqq\qqlevel;

use app\Plugin;
use Exception;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $uin = input('post.uin', null, 'trim');
        if(!$uin) return msg('error','no uin');

        if(!is_numeric($uin) || strlen($uin)>11){
            return msg('error', 'QQ号码格式不正确！');
        }

        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }
        
        try{
            $result = $this->queryapi($uin);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }
        
        $msg['QQ号码'] = $uin;
        $msg['QQ昵称'] = $result['sNickName'];
        $msg['QQ等级'] = $result['iQQLevel'].'级';
        $msg['等级图标'] = $this->Level($result['iQQLevel']);
        $msg['注册时长'] = $result['iTotalActiveDay'].'天';
        $msg['下次升级天数'] = $result['iNextLevelDay'].'天';
        $msg['今日成长天数'] = $result['iRealDays'].'天';
        $Time = @round((($result['iNoHideOnlineTime'] + $result['iPCQQOnlineTime']) / 60 / 60) , 2);
        $msg['今日在线时长'] = $Time.'小时';

        return msg('ok','success',$msg);
    }

    private function queryapi($uin){
        //此处需填写QQAPI的接口地址（https://github.com/netcccyun/qqapi）
        $url = 'http://qqapiurl/api.php?act=getqqlevel';
        $post = 'key=查询密钥&uin='.$uin;
        $data = get_curl($url, $post);
        $arr = json_decode($data, true);
        if(isset($arr['code']) && $arr['code']==0){
            return $arr['data'];
        }elseif(isset($arr['msg'])){
            throw new Exception($arr['msg']);
        }else{
            throw new Exception('接口请求失败');
        }
    }

    private function Level($Level){
        if($Level == 0){
            return '☆';
        }
        $String = '';
        $h = Intval($Level / 64);
        $Level_h = Intval($Level - ($h * 64));
        $t = Intval($Level_h / 16);
        $Level_t = Intval($Level_h - ($t * 16));
        $y = Intval($Level_t / 4);
        $Level_y = Intval($Level_t - ($y * 4));
        $x = Intval($Level_y);
        $Level_t = Intval($Level_h - $x);
        for($i = 0 ; $i < $h ; $i++){
            $String .= '👑';
        }
        for($i = 0 ; $i < $t ; $i++){
            $String .= '☀️';
        }
        for($i = 0 ; $i < $y ; $i++){
            $String .= '🌙';
        }
        for($i = 0 ; $i < $x ; $i++){
            $String .= '⭐';
        }
        return $String;
    }

}