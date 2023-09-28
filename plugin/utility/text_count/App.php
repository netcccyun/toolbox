<?php
/**
 * 字数统计与排版
 */

namespace plugin\utility\text_count;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}