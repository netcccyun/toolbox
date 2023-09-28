<?php

namespace plugin\utility\short_url\api;

use plugin\utility\short_url\api;

class p_sogou_com implements api
{

    public function create($url)
    {
        if(substr(parse_url($url)['host'], -9) != 'sogou.com')
            $url = 'https://m.sogou.com/web/confirm.jsp?url='.urlencode($url);
        $data = get_curl('http://sa.sogou.com/gettiny?url='.urlencode($url).'&mid=ff92866333010593695&xid=87b53ac05cc35e678d172752bd690bc3dcb7&imsi=460072887441781&ts=1501395961638&crypto=9agMSumkNV29lKu3jelybeLPZ+c4yDU2Vu7YaMj1OucASKqPnn+Pdo/cQMXTLY3+&ver=5.2.1.0');
        if (!empty($data)) {
            return $data;
        }
        throw new \Exception('生成失败');
    }
}