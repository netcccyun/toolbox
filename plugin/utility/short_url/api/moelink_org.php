<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class moelink_org implements api
{

    public function create($url): string
    {
        $post = [
            'url' => $url,
        ];
        $data = get_curl('https://moelink.org/shorten', http_build_query($post));
        $json = json_decode($data);
        if (!empty($json) && !empty($json->data->shorturl) && $json->error === false) {
            return $json->data->shorturl;
        }
        if (!empty($json->message)) {
            throw new \Exception($json->message);
        }
        throw new \Exception('生成失败');

    }
}