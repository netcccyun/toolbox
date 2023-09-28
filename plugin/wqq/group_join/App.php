<?php
/**
 * 生成加群链接
 */

namespace plugin\wqq\group_join;

use app\Plugin;
use Exception;
use think\facade\View;

class App extends Plugin
{

    public function index()
    {
        $logininfo = session('qq_cookie_qun');
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

    public function getlink(){
        $groupid = input('get.groupid', null, 'trim');
        if(!$groupid) return msg('error','no groupid');
        if(!is_numeric($groupid)){
            return msg('error','群号不正确');
        }

        $logininfo = session('qq_cookie_qun');
        if(!$logininfo){
            return msg('error', '请先登录');
        }
        
        try{
            $qqgroup = new \app\lib\QQGroup($logininfo['uin'], $logininfo['cookie']);
            $joinlink = $qqgroup->getjoinlink($groupid);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        return msg('ok', 'success', $joinlink);
    }

}