<?php
/**
 * 在线进制转换
 */

namespace plugin\dev\hex_convert;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}