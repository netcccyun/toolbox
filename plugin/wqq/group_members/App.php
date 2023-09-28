<?php
/**
 * 提取QQ群成员
 */

namespace plugin\wqq\group_members;

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
            $group = [];
            try{
                $qqgroup = new \app\lib\QQGroup($logininfo['uin'], $logininfo['cookie']);
                $group = $qqgroup->grouplist();
            }catch(Exception $e){
                $error = $e->getMessage();
            }
            View::assign('group', $group);
        }else{
            View::assign('isqqlogin', 0);
        }
        View::assign('groupid', input('get.groupid'));
        View::assign('error', $error);
        return $this->view();
    }

    public function list(){
        $groupid = input('get.groupid', null, 'trim');
        if(!$groupid) return msg('error','no groupid');
        $start=input('?get.start')?input('get.start'):0;
        $end=$start+40;

        $logininfo = session('qq_cookie_qun');
        if(!$logininfo){
            return msg('error', '请先登录');
        }
        
        try{
            $qqgroup = new \app\lib\QQGroup($logininfo['uin'], $logininfo['cookie']);
            $data = $qqgroup->groupmemberlist($groupid, $start, $end);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        return msg('ok','success',$data);
    }

}