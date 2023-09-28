<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class suo_yt implements api
{

    public function create($url): string
    {
        $post = [
            'longUrl' => base64_encode($url),
        ];
        $data = get_curl('https://suo.yt/short', http_build_query($post));
        $json = json_decode($data);
        if (!empty($json) && !empty($json->ShortUrl)) {
            return $json->ShortUrl;
        }
        if (!empty($json->Message)) {
            throw new \Exception($json->Message);
        }
        throw new \Exception('生成失败');

    }
}