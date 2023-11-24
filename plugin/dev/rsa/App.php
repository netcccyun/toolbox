<?php
/**
 * RSA生成与加密
 */

namespace plugin\dev\rsa;

use app\Plugin;

class App extends Plugin
{

    /**
     * 密钥配置
     * @var array
     */
    public $config = [
        //指定应该使用多少位来生成私钥  512 1024  2048  4096等
        "private_key_bits" => 1024,
        //选择在创建CSR时应该使用哪些扩展。可选值有 OPENSSL_KEYTYPE_DSA, OPENSSL_KEYTYPE_DH, OPENSSL_KEYTYPE_RSA 或 OPENSSL_KEYTYPE_EC. 默认值是 OPENSSL_KEYTYPE_RSA.
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
        "curve_name" => ''
    ];
    private $passPhrase;
    private $rsaPrivateKey;
    private $rsaPublicKey;
    private $data;
    private $sign;
    private $origin;
    private $coded;
    private $opensslPadding;
    private $opensslAlgo = 1;


    public function __construct()
    {
        $this->passPhrase = request()->param("pass_phrase");
        if($this->passPhrase == '') $this->passPhrase = null;
        $this->rsaPrivateKey = request()->param("private_key");
        $this->rsaPublicKey = $this->getPemKey(request()->param("public_key"), 'PUBLIC KEY');
        $this->config["private_key_type"] = request()->param("private_key_type/d");
        $this->config["private_key_bits"] = request()->param("private_key_bits/d");
        $this->config["curve_name"] = request()->param("curve_name");
        $this->data = request()->param("data");
        $this->origin = request()->param("origin");
        $this->coded = request()->param("coded");
        $this->sign = request()->param("sign");
        $this->opensslPadding = request()->param("openssl_padding");
        $this->opensslAlgo = intval(request()->param("openssl_algo"));
    }

    public function index()
    {
        return $this->view();
    }

    /**
     * 生成密钥对
     */
    public function generate()
    {
        // 生成公钥私钥资源
        $res = openssl_pkey_new($this->config);
        $privateKey = '';
        // 导出私钥
        openssl_pkey_export($res, $privateKey, $this->passPhrase, $this->config);
        //  导出公钥
        $pubKey = openssl_pkey_get_details($res);
        openssl_pkey_free($res);

        return msg('ok', 'success', [
            "public_key" => $pubKey["key"],
            "private_key" => $privateKey,
        ]);
    }

    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public function private_encrypt()
    {
        if (!is_string($this->origin)) {
            return msg('error', '加密失败');
        }
        try {
            if (openssl_private_encrypt($this->origin, $encrypted, $this->getRsaPrivateKey(), $this->opensslPadding)) {
                return msg('ok', 'success', base64_encode($encrypted));
            }
            return msg('error', '加密失败');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public function public_encrypt()
    {
        if (!is_string($this->origin)) {
            return msg('error', '加密失败');
        }
        $dataLength = mb_strlen($this->origin);
        $offet = 0;
        $length = 128;
        $i = 0;
        $string = '';
        try {
            while ($dataLength - $offet > 0) {
                if ($dataLength - $offet > $length) {
                    $str = mb_substr($this->origin, $offet, $length);
                } else {
                    $str = mb_substr($this->origin, $offet, $dataLength - $offet);
                }
                $encrypted = '';
                openssl_public_encrypt($str, $encrypted, $this->rsaPublicKey, $this->opensslPadding);
                $string .= $encrypted;
                $i++;
                $offet = $i * $length;
            }

            if ($string) {
                return msg('ok', 'success', base64_encode($string));
            }
            return msg('error', '加密失败');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public function private_decrypt()
    {
        if (!is_string($this->coded)) {
            return msg('error', '解密失败');
        }
        try {
            if (openssl_private_decrypt(base64_decode($this->coded), $decrypted, $this->getRsaPrivateKey(), $this->opensslPadding)) {
                return msg('ok', 'success', $decrypted);
            }
            return msg('error', '解密失败');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }

    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public function public_decrypt()
    {
        if (!is_string($this->coded)) {
            return msg('error', '解密失败');
        }

        try {
            if (openssl_public_decrypt(base64_decode($this->coded), $decrypted, $this->rsaPublicKey, $this->opensslPadding)) {
                return msg('ok', 'success', $decrypted);
            }
            return msg('error', '解密失败');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    /**
     * 生成签名
     *
     * @param string 签名材料
     * @param string 签名编码（base64/hex/bin）
     * @return string 签名值
     */
    public function sign()
    {
        try {
            if (openssl_sign($this->data, $ret, $this->getRsaPrivateKey(), $this->opensslAlgo)) {
                $ret = base64_encode($ret);
                return msg('ok', 'success', $ret);
            }
            return msg('error', '签名失败');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    /**
     * 验证签名
     *
     * @param string 签名材料
     * @param string 签名值
     * @param string 签名编码（base64/hex/bin）
     * @return \think\response\Json
     */
    public function verify()
    {
        try {
            $ret = false;
            $sign = base64_decode($this->sign);
            if ($sign !== false) {
                if (openssl_verify($this->data, $sign, $this->rsaPublicKey, $this->opensslAlgo) === 1) {
                    $ret = true;
                }
            }
            if ($ret) {
                return msg('ok', '验签成功，签名正确', $ret);
            }
            return msg('error', '验签失败，签名不正确');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    public function check()
    {
        try {
            openssl_sign('cccyun', $ret, $this->getRsaPrivateKey());
            if (openssl_verify('cccyun', $ret, $this->rsaPublicKey) === 1) {
                return msg('ok', '验证成功，公私钥匹配');
            }
            return msg('error', '公私钥不匹配');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    public function output()
    {
        $type = input('post.type/d');
        try {
            if($type == 1){
                $cert = input('post.cert');
                $pkey_res = openssl_pkey_get_public($cert);
                if(!$pkey_res){
                    return msg('error', '导出失败，公钥证书不正确');
                }
                $pkey_detail = openssl_pkey_get_details($pkey_res);
                return msg('ok', '导出成功', $pkey_detail['key']);
            }elseif($type == 2){
                $csr = input('post.csr');
                $pkey_res = openssl_csr_get_public_key($csr);
                if(!$pkey_res){
                    return msg('error', '导出失败，CSR不正确');
                }
                $pkey_detail = openssl_pkey_get_details($pkey_res);
                return msg('ok', '导出成功', $pkey_detail['key']);
            }
            $openssl_pkey_get_details = openssl_pkey_get_details($this->getRsaPrivateKey());
            if ($openssl_pkey_get_details && !empty($openssl_pkey_get_details['key'])) {
                return msg('ok', '导出成功', $openssl_pkey_get_details['key']);
            }
            return msg('error', '导出失败，私钥不正确');
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    public function key_encrypt()
    {
        try {
            $privateKey = openssl_pkey_get_private($this->getPemKey($this->rsaPrivateKey, 'PRIVATE KEY'));
            if ($privateKey === false) {
                throw new \Exception('私钥不正确或已被加密');
            }
            openssl_pkey_export($privateKey, $encrypted, $this->passPhrase);
            return msg('ok', '加密成功', $encrypted);
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    public function key_decrypt()
    {
        try {
            openssl_pkey_export($this->getRsaPrivateKey(), $decrypted);
            return msg('ok', '解密成功', $decrypted);
        } catch (\Exception $e) {
            return msg('error', $e->getMessage());
        }
    }

    /**
     * @return array|mixed|null
     */
    public function getRsaPrivateKey()
    {
        $privateKey = openssl_pkey_get_private($this->getPemKey($this->rsaPrivateKey, $this->passPhrase ? 'ENCRYPTED PRIVATE KEY' : 'PRIVATE KEY'), $this->passPhrase);
        if ($privateKey === false) {
            throw new \Exception('私钥不正确或密码错误');
        }
        return $privateKey;
    }

    private function getPemKey($str, $type){
        if(empty($str) || strpos($str, '-----BEGIN')!==false) return $str;
        $pem = "-----BEGIN ".$type."-----\n" .
                wordwrap($str, 64, "\n", true) .
                "\n-----END ".$type."-----";
        return $pem;
    }
}