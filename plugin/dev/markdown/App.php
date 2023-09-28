<?php
/**
 * Markdown在线编辑
 */

namespace plugin\dev\markdown;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}