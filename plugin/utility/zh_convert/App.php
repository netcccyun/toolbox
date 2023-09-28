<?php
/**
 * 中文简繁体转化
 */

namespace plugin\utility\zh_convert;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}