<?php
/**
 * GitHub下载加速
 */

namespace plugin\dev\github;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}