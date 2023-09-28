<?php
/**
 * UUID生成器
 */

namespace plugin\dev\uuid;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}