<?php
/**
 * 随机密码生成
 */

namespace plugin\utility\rand_password;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}