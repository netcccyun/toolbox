<?php
/**
 * QQ强制聊天
 */

namespace plugin\wqq\qqchat;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

}