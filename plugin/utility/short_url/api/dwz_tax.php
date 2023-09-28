<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class dwz_tax implements api
{

    public function create($url)
    {
        $post = [
            'token' => 'k9ah7rn36d1dsu51jpjeboopg4',
            'longurl' => $url,
            'alias' => '',
            'expiry' => '',
            'password' => '',
        ];
        $data = get_curl('https://dwz.tax/api/shorten', http_build_query($post), 'https://dwz.tax/');
        $json = json_decode($data);
        if (!empty($json) && !empty($json->short)) {
            return $json->short;
        }
        if (!empty($json->msg)) {
            throw new \Exception($json->msg);
        }
        throw new \Exception('生成失败');

    }
}