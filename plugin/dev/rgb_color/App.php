<?php
/**
 * RGB颜色对照表
 */

namespace plugin\dev\rgb_color;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}