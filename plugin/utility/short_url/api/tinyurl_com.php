<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class tinyurl_com implements api
{

    public function create($url): string
    {
        $post = [
            'url' => $url,
            'format' => 'json',
        ];
        $get = get_curl('https://tinyurl.com/api-create.php', http_build_query($post));
        if (is_valid_url($get)) {
            return $get;
        }
        throw new \Exception('生成失败');
    }
}