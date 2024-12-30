<?php
/**
 * 自定义在线机型
 */

namespace plugin\wqq\online_device;

use app\Plugin;
use Exception;
use think\facade\View;

class App extends Plugin
{

    public function index()
    {
        $logininfo = session('qq_cookie_vip');
        $error = null;
        if($logininfo){
            View::assign('isqqlogin', 1);
            View::assign('logininfo', $logininfo);
        }else{
            View::assign('isqqlogin', 0);
        }
        View::assign('groupid', input('get.groupid'));
        View::assign('error', $error);
        return $this->view();
    }

    public function change(){
        $model = input('post.model', null, 'trim');
        if(!$model) return msg('error','自定义机型不能为空');
        $desc = input('post.desc', null, 'trim');
        $imei = input('post.imei', null, 'trim');
        if(!$imei) return msg('error','IMEI不能为空');

        $logininfo = session('qq_cookie_vip');
        if(!$logininfo){
            return msg('error', '请先登录');
        }

        try{
            $qqtool = new \app\lib\QQTool($logininfo['uin'], $logininfo['cookie'], true);
            $qqtool->set_online_status($model, $desc, $imei);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        return msg();
    }

}