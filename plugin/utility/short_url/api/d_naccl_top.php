<?php


namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class d_naccl_top implements api
{

    public function create($url)
    {
        $post = ['longURL' => $url];
        $data = get_curl('https://d.naccl.top/generate', http_build_query($post));
        $json = json_decode($data);
        if (!empty($json) && !empty($json->data) && $json->code === 200) {
            return $json->data;
        }
        if (!empty($json->msg)) {
            throw new \Exception($json->msg);
        }
        throw new \Exception('生成失败');

    }
}