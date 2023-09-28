<?php
/**
 * 中文域名转码
 */

namespace plugin\web\punycode;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}