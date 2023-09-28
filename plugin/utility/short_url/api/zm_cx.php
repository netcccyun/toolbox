<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class zm_cx implements api
{

    public function create($url): string
    {
        $post = json_encode([
            'url' => $url,
        ]);
        $data = get_curl('https://zm.cx/api/set.php', $post);
        $json = json_decode($data);
        if (!empty($json) && !empty($json->content->url)) {
            return $json->content->url;
        }
        if (!empty($json->content)) {
            throw new \Exception($json->content);
        }
        throw new \Exception('生成失败');

    }
}