<?php
/**
 * 在线钢琴
 */

namespace plugin\fun\piano;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}