<?php
/**
 * emoji表情
 */

namespace plugin\utility\emoji;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}