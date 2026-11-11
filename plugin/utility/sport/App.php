<?php
/**
 * 小米运动步数修改
 */

namespace plugin\utility\sport;

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

    public function login(){
        $username = input('post.username');
        $password = input('post.password');
        if(!$username || !$password) return msg('error','参数不能为空');

        if((strlen($username)!=11 || !is_numeric($username)) && !filter_var($username,FILTER_VALIDATE_EMAIL)) return msg('error','手机号或邮箱不正确');

        try{
            $sport = new XiaomiSport();
            $result = $sport->login($username, $password);
        }catch(Exception $e){
            return msg('error',$e->getMessage());
        }

        return msg('ok','success',$result);
    }

    public function submit(){
        $userid = input('post.userid');
        $token = input('post.token');
        $step = input('post.step');
        if(!$userid || !$token || !$step) return msg('error','参数不能为空');
        if(!ctype_digit((string)$step) || $step > 98000) return msg('error','步数不正确');
        $step = intval($step);

        try{
            $sport = new XiaomiSport();
            $sport->step($userid, $token, $step);
        }catch(Exception $e){
            return msg('error',$e->getMessage());
        }

        return msg();
    }

    public function bind_device(){
        $userid = input('post.userid');
        $token = input('post.token');
        if(!$userid || !$token) return msg('error','参数不能为空');

        try{
            $sport = new XiaomiSport();
            $data = $sport->bind($userid, $token);
            return msg('ok','success',$data);
        }catch(Exception $e){
            return msg('error',$e->getMessage());
        }
    }

}