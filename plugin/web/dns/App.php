<?php
/**
 * 域名DNS查询
 */

namespace plugin\web\dns;

use app\Plugin;
use Exception;

class App extends Plugin
{

    const DOH_API = [
        'alidns' => [
            'url' => 'https://dns.alidns.com/resolve',
            'isproxy' => false,
        ],
        'dnspod' => [
            'url' => 'https://doh.pub/resolve',
            'isproxy' => false,
        ],
        '360' => [
            'url' => 'https://doh.360.cn/resolve',
            'isproxy' => false,
        ],
        'google' => [
            'url' => 'https://dns.google/resolve',
            'isproxy' => true,
        ],
    ];

    const DNS_TYPE = [
        1 => 'A',
        5 => 'CNAME',
        16 => 'TXT',
        28 => 'AAAA',
        2 => 'NS',
        6 => 'SOA',
    ];

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $name = input('post.name', null, 'trim');
        $type = input('post.type/d');
        $doh = input('?post.doh') ? input('post.doh', null, 'trim') : 'alidns';
        if(!$name || !$type) return msg('error','no name');

        try{
            $result = $this->doh_resolve($doh, $type, $name);
            $list = [];
            foreach($result as $row){
                $row['typename'] = isset(self::DNS_TYPE[$row['type']]) ? self::DNS_TYPE[$row['type']] : $row['type'];
                $list[] = $row;
            }
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }


        return msg('ok','success',$list);
    }


    private function doh_resolve($doh, $type, $name){
        if(!array_key_exists($doh, self::DOH_API)) throw new Exception('不存在该DNS服务器');
        $url = self::DOH_API[$doh]['url'].'?name='.urlencode($name).'&type='.$type;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        $arr = json_decode($data, true);
        if(!$arr){
            throw new Exception('DOH接口查询失败');
        }else{
            if(isset($arr['Answer'])){
                return $arr['Answer'];
            }else{
                return [];
            }
        }
    }
}