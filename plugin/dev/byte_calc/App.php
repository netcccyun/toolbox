<?php
/**
 * 字节计算器
 */

namespace plugin\dev\byte_calc;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}