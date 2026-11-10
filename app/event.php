<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [
            // PHP 8.5 起 curl_close 等函数会产生废弃提示，框架默认将其转为异常
            function () {
                error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
            },
        ],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
    ],
];
