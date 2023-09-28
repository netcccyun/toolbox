<?php
/**
 * 批量删除群公告
 */

namespace plugin\wqq\group_announce;

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
                $group = $qqgroup->grouplist(true);
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
        $start=input('?get.start')?input('get.start/d'):-1;

        $logininfo = session('qq_cookie_qun');
        if(!$logininfo){
            return msg('error', '请先登录');
        }
        
        try{
            $qqgroup = new \app\lib\QQGroup($logininfo['uin'], $logininfo['cookie']);
            $list = $qqgroup->announcelist($groupid, $start);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        $data = array();
		$data['code'] = 0;
		$data['count'] = count($list);
		$data['data'] = $list;
		$data['next'] = count($list)>=10;

        return msg('ok','success',$data);
    }

    public function del(){
        $groupid = input('get.groupid', null, 'trim');
        if(!$groupid) return msg('error','no groupid');
        $fid = input('get.fid', null, 'trim');
        if(!$fid) return msg('error','no fid');

        $logininfo = session('qq_cookie_qun');
        if(!$logininfo){
            return msg('error', '请先登录');
        }
        
        try{
            $qqgroup = new \app\lib\QQGroup($logininfo['uin'], $logininfo['cookie']);
            $qqgroup->delannounce($groupid, $fid);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        return msg();
    }


}