<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class gg_gg implements api
{

    public function create($url)
    {
        $post = [
            'custom_path' => '',
            'use_norefs' => '0',
            'long_url' => $url,
            'app' => 'site',
            'version' => '0.1',
        ];
        $data = get_curl('http://gg.gg/create', http_build_query($post), 'http://gg.gg/');
        if (!empty($data)) {
            return $data;
        }
        throw new \Exception('生成失败');
    }
}