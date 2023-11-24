<?php
/**
 * 证书信息查看
 */

namespace plugin\dev\cert;

use app\Plugin;
use Exception;

error_reporting(0);

class App extends Plugin
{

    private static $key_type = ['RSA', 'DSA', 'DH', 'EC'];

    public function index()
    {
        return $this->view();
    }

    public function query(){
        if(input('post.type/d') == 2){
            $domain = input('post.domain', null, 'trim');
            if(!$domain) return msg('error','no domain');
            $port = 443;
            if(strpos($domain, ':')){
                $a = explode(':', $domain);
                $domain = $a[0];
                if(is_numeric($a[1])) $port = intval($a[1]);
            }
            if(!checkdomain($domain)) return msg('error','域名格式不正确');
            try{
                $cert = $this->getSSLCertificate($domain, $port);
            }catch(Exception $e){
                return msg('error', $e->getMessage());
            }
        }else{
            $cert = input('post.cert');
            if(!$cert) return msg('error','no cert');
        }

        $arr = openssl_x509_parse($cert);
        if(!$arr){
            return msg('error', '证书文件错误');
        }
        $pkey_detail = openssl_pkey_get_details(openssl_pkey_get_public($cert));

        $subject_info = [
            '通用名称(CN)' => $this->format($arr['subject']['CN']),
            '国家(C)' => $this->format($arr['subject']['C']),
            '省份(S)' => $this->format($arr['subject']['ST']),
            '城市(L)' => $this->format($arr['subject']['L']),
            '组织(O)' => $this->format($arr['subject']['O']),
            '部门(OU)' => $this->format($arr['subject']['OU']),
        ];
        $issuer_info = [
            '通用名称(CN)' => $this->format($arr['issuer']['CN']),
            '国家(C)' => $this->format($arr['issuer']['C']),
            '组织(O)' => $this->format($arr['issuer']['O']),
        ];
        $cert_info = [
            '序列号' => $arr['serialNumberHex'],
            '签名算法' => $arr['signatureTypeSN'],
            '密钥类型' => isset(self::$key_type[$pkey_detail['type']]) ? self::$key_type[$pkey_detail['type']] : '未知',
            '密钥强度' => $pkey_detail['bits'],
            '颁发时间' => date('Y-m-d H:i:s', $arr['validFrom_time_t']),
            '过期时间' => date('Y-m-d H:i:s', $arr['validTo_time_t']),
            '有效期' => round(($arr['validTo_time_t']-time()) / 3600 / 24).'天',
            '备用名' => $arr['extensions']['subjectAltName'],
        ];

        return msg('ok','success',['subject_info'=>$subject_info, 'issuer_info'=>$issuer_info, 'cert_info'=>$cert_info]);
    }

    private function format($mixed){
        if(is_array($mixed)){
            return $mixed[array_key_first($mixed)];
        }
        return $mixed;
    }

    private function getSSLCertificate($domain, $port = 443) {
        $context = stream_context_create([
            "ssl" => [
                "capture_peer_cert" => true,
            ],
        ]);
    
        $socket = stream_socket_client('ssl://'.$domain.':'.$port, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
    
        if ($socket) {
            $params = stream_context_get_params($socket);
            if(!isset($params["options"]["ssl"]["peer_certificate"])){
                throw new Exception('域名未找到SSL证书信息');
            }
            fclose($socket);
            return $params["options"]["ssl"]["peer_certificate"];
        } else {
            $errstr = mb_convert_encoding($errstr, 'UTF-8', 'GB2312');
            throw new Exception('域名443端口连接失败，'.$errstr);
        }
    }

}