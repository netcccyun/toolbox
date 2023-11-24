<?php
/**
 * 国密公私钥工具
 */

namespace plugin\dev\guomi;

use app\Plugin;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function output()
    {
        $type = input('post.type/d');
        if($type == 1){
            $cert = input('post.cert');
            $pkey_res = openssl_pkey_get_public($cert);
            if(!$pkey_res){
                return msg('error', '导出失败，公钥证书不正确');
            }
            $pkey_detail = openssl_pkey_get_details($pkey_res);
            $pkey_hex = bin2hex($this->pem2der($pkey_detail['key']));
            if(strlen($pkey_hex) > 130) $pkey_hex = substr($pkey_hex, -130);
            return msg('ok', '导出成功', $pkey_hex);
        }elseif($type == 2){
            $csr = input('post.csr');
            $pkey_res = openssl_csr_get_public_key($csr);
            if(!$pkey_res){
                return msg('error', '导出失败，CSR不正确');
            }
            $pkey_detail = openssl_pkey_get_details($pkey_res);
            $pkey_hex = bin2hex($this->pem2der($pkey_detail['key']));
            if(strlen($pkey_hex) > 130) $pkey_hex = substr($pkey_hex, -130);
            return msg('ok', '导出成功', $pkey_hex);
        }
    }

    private function pem2der($pem_data)
    {
        $begin = "-----";
        $end   = "-----END";
        $pem_data = substr($pem_data, strpos($pem_data, $begin, 6) + strlen($begin));
        $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
        $der = base64_decode($pem_data);
        return $der;
    }
}