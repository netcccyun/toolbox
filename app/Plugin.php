<?php

namespace app;

use think\facade\View;

abstract class Plugin
{
    protected $plugin;
    protected $clientip;

    public function __construct()
    {
    }

    public function initialize($plugin){
        $this->plugin = $plugin;
        $this->clientip = real_ip();
    }

    protected function view($tpl = null){
        if($tpl == null) $tpl = strtolower(request()->param("method", "index"));
        $template = plugin_path_get($this->plugin['class']) . '/'.$tpl.'.html';
        View::assign("plugin", $this->plugin);
        return view($template);
    }

    public function alert($code, $msg = '', $url = null, $wait = 3)
    {
        if ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : (string)$this->app->route->buildUrl($url);
        }
        if(empty($msg)) $msg = '未知错误';
        
        View::assign([
            'code' => $code,
            'msg' => $msg,
            'url' => $url,
            'wait' => $wait,
        ]);
        return View::fetch(app()->getRootPath().'view/dispatch_jump.html');
    }

}