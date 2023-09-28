<?php
/**
 * 设置空白昵称
 */

namespace plugin\wqq\setqqnick;

use app\Plugin;
use Exception;
use think\facade\View;

class App extends Plugin
{

    public function index()
    {
        $logininfo = session('qq_cookie_qzone');
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

    public function setnick(){
        $nickname = input('post.nickname', null);
        if(!$nickname) return msg('error','新昵称不能为空');

        $logininfo = session('qq_cookie_qzone');
        if(!$logininfo){
            return msg('error', '请先登录');
        }

        try{
            $qqtool = new \app\lib\QQTool($logininfo['uin'], $logininfo['cookie']);
            $qqtool->setnickname($nickname);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        return msg();
    }

}