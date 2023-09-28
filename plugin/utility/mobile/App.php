<?php
/**
 * 手机归属地查询
 */

namespace plugin\utility\mobile;

use app\Plugin;
use Exception;
use think\facade\Db;
use think\facade\View;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $mobile = input('post.mobile', null, 'trim');
        if(!$mobile) return msg('error','no mobile');

        if(!is_numeric($mobile) || strlen($mobile)>11){
            return msg('error', '手机号码格式不正确！');
        }

        $result = $this->queryapi($mobile);
        if(!$result){
            return msg('error', '查询失败');
        }
        $msg['归属地'] = $result['province'].' '.$result['city'];
        $msg['运营商'] = $result['sp'];

        return msg('ok','success',$msg);
    }

    private function queryapi($mobile){
        $url = 'https://cx.shouji.360.cn/phonearea.php?number='.$mobile;
        $data = get_curl($url);
        $arr = json_decode($data, true);
        if(isset($arr['code']) && $arr['code']==0){
            return $arr['data'];
        }
        return false;
    }

}