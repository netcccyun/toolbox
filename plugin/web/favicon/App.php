<?php
/**
 * 网站Favicon获取
 */

namespace plugin\web\favicon;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}