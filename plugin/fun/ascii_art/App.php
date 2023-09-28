<?php
/**
 * ASCII艺术字生成
 */

namespace plugin\fun\ascii_art;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}