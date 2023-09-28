<?php
/**
 * Linux命令查询
 */

namespace plugin\dev\linux_command;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}