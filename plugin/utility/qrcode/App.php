<?php
/**
 * 二维码生成
 */

namespace plugin\utility\qrcode;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}