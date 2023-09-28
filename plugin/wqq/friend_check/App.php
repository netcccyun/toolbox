<?php
/**
 * 单向好友检测
 */

namespace plugin\wqq\friend_check;

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
            $skey = getSubstr($logininfo['cookie'], 'skey=', ';');
            $pskey = getSubstr($logininfo['cookie'], 'p_skey=', ';');
            View::assign('skey', $skey);
            View::assign('pskey', $pskey);
            $list = [];
            try{
                $qqtool = new \app\lib\QQTool($logininfo['uin'], $logininfo['cookie']);
                $res = $qqtool->friendlist();
                $list = $res["list"];
            }catch(Exception $e){
                $error = $e->getMessage();
            }
            View::assign('list', $list);
        }else{
            View::assign('isqqlogin', 0);
        }
        View::assign('error', $error);
        return $this->view();
    }

}