<?php
/**
 * 图片转base64 
 */

namespace plugin\utility\image2base64;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}