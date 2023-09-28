<?php
/**
 * 数字IP地址转换
 */

namespace plugin\web\ip_num;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}