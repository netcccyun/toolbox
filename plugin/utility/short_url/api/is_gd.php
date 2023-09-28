<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class is_gd implements api
{

    public function create($url): string
    {
        $data = get_curl("https://is.gd/create.php?format=json&url=".urlencode($url));
        $json = json_decode($data);
        if (!empty($json) && !empty($json->shorturl)) {
            return $json->shorturl;
        }
        if (!empty($json->errormessage)) {
            throw new \Exception($json->errormessage);
        }
        throw new \Exception('生成失败');

    }
}