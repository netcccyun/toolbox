<?php
/**
 * 文章生成器
 */

namespace plugin\fun\bullshit_generator;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}