<?php
/**
 * 在线语音合成
 */

namespace plugin\fun\speech_synthesis;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }
}