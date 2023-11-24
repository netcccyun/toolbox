<?php
/**
 * CSR生成与查看
 */

namespace plugin\dev\csr;

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
        $csr = input('post.csr');
        if(!$csr) return msg('error','no csr');

        $subject_info = openssl_csr_get_subject($csr);
        if(!$subject_info){
            return msg('error', 'CSR文件错误');
        }

        $pkey_detail = openssl_pkey_get_details(openssl_csr_get_public_key($csr));

        $info = [
            '通用名称(CN)' => $this->format($subject_info['CN']),
            '国家(C)' => $this->format($subject_info['C']),
            '省份(S)' => $this->format($subject_info['ST']),
            '城市(L)' => $this->format($subject_info['L']),
            '组织(O)' => $this->format($subject_info['O']),
            '部门(OU)' => $this->format($subject_info['OU']),
            '密钥类型' => isset(self::$key_type[$pkey_detail['type']]) ? self::$key_type[$pkey_detail['type']] : '未知',
            '密钥强度' => $pkey_detail['bits'],
        ];

        return msg('ok','success',['info'=>$info]);
    }

    private function format($mixed){
        if(is_array($mixed)){
            return $mixed[array_key_first($mixed)];
        }
        return $mixed;
    }

    public function generate(){
        $commonName = input('post.commonName', null, 'trim');//通用名
        $countryName = input('post.countryName', null, 'trim');//国家
        $stateOrProvinceName = input('post.stateOrProvinceName', null, 'trim');//省份
        $localityName = input('post.localityName', null, 'trim');//城市
        $organizationName = input('post.organizationName', null, 'trim');//组织
        $organizationalUnitName = input('post.organizationalUnitName', null, 'trim');//部门
        $subjectAltName = input('post.subjectAltName', null, 'trim');//备用名
        $emailAddress = input('post.emailAddress', null, 'trim');//邮箱
        
        $private_key_type = input('post.private_key_type/d');//密钥算法
        $private_key_bits = input('post.private_key_bits/d');//密钥长度
        $curve_name = input('post.curve_name');//曲线名称
        $digest_alg = input('post.digest_alg');//签名算法
        $pass_phrase = input('post.pass_phrase', null, 'trim');//私钥密码

        //生成公私钥
        $config = [
            "private_key_bits" => $private_key_bits,
            "private_key_type" => $private_key_type,
            "curve_name" => $curve_name
        ];
        $privkey = openssl_pkey_new($config);
        if(!$privkey){
            return msg('error', '私钥生成失败'.openssl_error_string());
        }
        $privateKey = '';
        openssl_pkey_export($privkey, $privateKey, $pass_phrase?$pass_phrase:null, $config);
        $pubKey = openssl_pkey_get_details($privkey);
        $publicKey = $pubKey['key'];

        //生成CSR
        $dn = array(
            "countryName" => $countryName,
            "stateOrProvinceName" => $stateOrProvinceName,
            "localityName" => $localityName,
            "organizationName" => $organizationName,
            "organizationalUnitName" => $organizationalUnitName,
            "commonName" => $commonName,
            "emailAddress" => $emailAddress,
            "subjectAltName" => $subjectAltName
        );
        $dn = array_filter($dn);
        $csr = openssl_csr_new($dn, $privkey, array('digest_alg' => $digest_alg));
        if(!$csr){
            return msg('error', 'CSR生成失败'.openssl_error_string());
        }
        openssl_csr_export($csr, $csrout);

        return msg('ok', 'success', ['public_key'=>$publicKey, 'private_key'=>$privateKey, 'csr'=>$csrout]);
    }

}