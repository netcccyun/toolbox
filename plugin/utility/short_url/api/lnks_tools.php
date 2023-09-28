<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class lnks_tools implements api
{

    public function create($url): string
    {
        $data = get_curl('https://lnks.tools', json_encode([
            'url' => $url,
        ]));
        $json = json_decode($data);
        if (!empty($json) && !empty($json->key)) {
            return 'https://lnks.tools' . $json->key;
        }
        throw new \Exception('生成失败');

    }
}