<?php
/**
 * 在线图片编辑
 */

namespace plugin\utility\image_editor;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}