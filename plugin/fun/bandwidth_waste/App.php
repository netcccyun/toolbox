<?php
/**
 * 让流量消失
 */

namespace plugin\fun\bandwidth_waste;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}