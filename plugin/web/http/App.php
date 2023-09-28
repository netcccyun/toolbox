<?php
/**
 * 查看HTTP请求
 */

namespace plugin\web\http;

use app\Plugin;
use think\facade\View;

class App extends Plugin
{

    public function index()
    {
        $ip = real_ip();
        $new = new \app\lib\IpLocation();
        $arr = $new->getlocation($ip);
        $location = '';
        if($arr){
            $location = $arr['province'].$arr['city'].' '.$arr['area'];
        }

        $cookie = '';
        foreach($_COOKIE as $cookie_key=>$cookie_value){
            if($cookie_key == 'admin_token' || $cookie_key == 'user_token' || $cookie_key == 'PHPSESSID') continue;
            $cookie .= $cookie_key.'='.$cookie_value.'; ';
        }
        $_SERVER['HTTP_COOKIE'] = $cookie;

        $line = ['<b>'.$_SERVER['REQUEST_METHOD'].'</b> '.$_SERVER['REQUEST_URI'].' '.$_SERVER['SERVER_PROTOCOL']];

        foreach ($_SERVER as $name => $value)
        {
            if (substr($name, 0, 5) == 'HTTP_')
            {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $line[] = '<b>'.$name.'</b>: '.$value;
            }
        }
        
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $line[] = '';
            $line[] = file_get_contents('php://input');
        }

        View::assign('ip', $ip);
        View::assign('location', $location);
        View::assign('line', $line);
        return $this->view();
    }
}