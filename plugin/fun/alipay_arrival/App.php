<?php
/**
 * 支付宝到账语音
 */

namespace plugin\fun\alipay_arrival;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}