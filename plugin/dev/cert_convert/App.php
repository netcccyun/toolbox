<?php
/**
 * RSA证书格式转换
 */

namespace plugin\dev\cert_convert;

use app\Plugin;
use CURLFile;
use Exception;

class App extends Plugin
{

    private static $apiurl = 'http://keytool.odata.cc/api.php';

    public function index()
    {
        return $this->view();
    }

    public function convert()
    {
        $from = input('post.from');
        $to = input('post.to');
        if(empty($from) || empty($to)) return msg('error', '必填项不能为空');

        try{
            $params = [];
            if($from == 'PEM' && $to != 'DER' && $to != 'PKCS7'){
                if(!isset($_FILES['private'])) return msg('error', 'PEM私钥文件不存在');
                $private_path = $_FILES['private']['tmp_name'];
                $this->check_cert('PEM私钥', $private_path);
    
                if(!isset($_FILES['cert'])) return msg('error', 'PEM证书文件不存在');
                $cert_path = $_FILES['cert']['tmp_name'];
                $this->check_cert('PEM证书', $cert_path);
    
                if(isset($_FILES['cacert'])){
                    $cacert_path = $_FILES['cacert']['tmp_name'];
                    $this->check_cert('PEM证书', $cacert_path);
                    file_put_contents($cert_path, PHP_EOL.file_get_contents($cacert_path), FILE_APPEND);
                }
    
                $params['private'] = new CURLFile($private_path);
                $params['cert'] = new CURLFile($cert_path);
                $params['pass'] = input('post.pass', null, 'trim');
                $this->check_pass('证书密码', $params['pass'], true);
                $act = $to == 'JKS' ? 'pem_to_jks' : 'pem_to_pfx';
    
            }elseif($from == 'PEM' && $to == 'DER'){
                if(!isset($_FILES['cert'])) return msg('error', 'PEM证书文件不存在');
                $cert_path = $_FILES['cert']['tmp_name'];
                $this->check_cert('PEM证书', $cert_path);
                $params['cert'] = new CURLFile($cert_path);
                $act = 'pem_to_der';
    
            }elseif($from == 'DER' && $to == 'PEM'){
                if(!isset($_FILES['cert'])) return msg('error', 'DER证书文件不存在');
                $cert_path = $_FILES['cert']['tmp_name'];
                $this->check_cert('DER证书', $cert_path);
                $params['cert'] = new CURLFile($cert_path);
                $act = 'der_to_pem';
    
            }elseif($from == 'PKCS12'){
                if(!isset($_FILES['cert'])) return msg('error', 'PKCS12证书文件不存在');
                $cert_path = $_FILES['cert']['tmp_name'];
                $this->check_cert('PKCS12证书', $cert_path);
                $params['cert'] = new CURLFile($cert_path);
                if($to == 'JKS'){
                    $params['srcpass'] = input('post.srcpass', null, 'trim');
                    $params['destpass'] = input('post.destpass', null, 'trim');
                    $this->check_pass('原证书密码', $params['srcpass'], true);
                    $this->check_pass('新证书密码', $params['destpass']);
                    $act = 'pfx_to_jks';
                }elseif($to == 'PEM'){
                    $params['pass'] = input('post.pass', null, 'trim');
                    $this->check_pass('原证书密码', $params['pass'], true);
                    $act = 'pfx_to_pem';
                }
    
            }elseif($from == 'JKS'){
                if(!isset($_FILES['cert'])) return msg('error', 'JKS证书文件不存在');
                $cert_path = $_FILES['cert']['tmp_name'];
                $this->check_cert('JKS证书', $cert_path);
                $params['cert'] = new CURLFile($cert_path);
                if($to == 'PKCS12'){
                    $params['srcpass'] = input('post.srcpass', null, 'trim');
                    $params['destpass'] = input('post.destpass', null, 'trim');
                    $this->check_pass('原证书密码', $params['srcpass']);
                    $this->check_pass('新证书密码', $params['destpass'], true);
                    if(empty($params['destpass'])) $params['destpass'] = $params['srcpass'];
                    $act = 'jks_to_pfx';
                }elseif($to == 'PEM'){
                    $params['pass'] = input('post.pass', null, 'trim');
                    $this->check_pass('原证书密码', $params['pass']);
                    $act = 'jks_to_pem';
                }

            }elseif($from == 'PEM' && $to == 'PKCS7'){
                if(!isset($_FILES['cert'])) return msg('error', 'PEM证书文件不存在');
                $cert_path = $_FILES['cert']['tmp_name'];
                $this->check_cert('PEM证书', $cert_path);
    
                if(isset($_FILES['cacert'])){
                    $cacert_path = $_FILES['cacert']['tmp_name'];
                    $this->check_cert('PEM证书', $cacert_path);
                    file_put_contents($cert_path, PHP_EOL.file_get_contents($cacert_path), FILE_APPEND);
                }
    
                $params['cert'] = new CURLFile($cert_path);
                $act = 'pem_to_p7b';
    
            }elseif($from == 'PKCS7' && $to == 'PEM'){
                if(!isset($_FILES['cert'])) return msg('error', 'PKCS7证书文件不存在');
                $cert_path = $_FILES['cert']['tmp_name'];
                $this->check_cert('PKCS7证书', $cert_path);
                $params['cert'] = new CURLFile($cert_path);
                $act = 'p7b_to_pem';
    
            }else{
                return msg('error', '转换类型不存在');
            }

            $response = $this->requestApi($act, $params);

            $files = [];
            if($to == 'PEM' && $from != 'DER' && $from != 'PKCS7' && substr($response, 0, 1) == '{'){
                $arr = json_decode($response, true);
                $files[] = ['name'=>'PEM 服务器证书', 'file'=>'cert.pem', 'data'=>base64_encode($arr['cert'])];
                if(!empty($arr['extra_certs'])){
                    $cacert = implode(PHP_EOL, $arr['extra_certs']);
                    $files[] = ['name'=>'PEM 中间/根证书', 'file'=>'cacert.pem', 'data'=>base64_encode($cacert)];
                }
                $files[] = ['name'=>'PEM 私钥', 'file'=>'privatekey.pem', 'data'=>base64_encode($arr['private_key'])];
                $files[] = ['name'=>'PEM 公钥', 'file'=>'publickey.pem', 'data'=>base64_encode($arr['public_key'])];
            }elseif($to == 'PEM'){
                $files[] = ['name'=>'PEM 证书文件', 'file'=>'cert.pem', 'data'=>base64_encode($response)];
            }elseif($to == 'DER'){
                $files[] = ['name'=>'DER 证书文件', 'file'=>'cert.der', 'data'=>base64_encode($response)];
            }elseif($to == 'PKCS12'){
                $files[] = ['name'=>'PKCS12 证书文件', 'file'=>'cert.pfx', 'data'=>base64_encode($response)];
            }elseif($to == 'JKS'){
                $files[] = ['name'=>'JKS 证书文件', 'file'=>'cert.jks', 'data'=>base64_encode($response)];
            }elseif($to == 'PKCS7'){
                $files[] = ['name'=>'PKCS7 证书文件', 'file'=>'cert.p7b', 'data'=>base64_encode($response)];
            }

            return msg('ok', 'success', ['files'=>$files]);

        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }
    }

    private function check_cert($name, $path){
        $file_size = filesize($path);
        if($file_size < 100 || $file_size > 50 * 1024){
            throw new Exception($name.'文件大小超过限制');
        }
    }

    private function check_pass($name, $value, $empty = false){
        if($empty && empty($value)) return;
        if(empty($value)){
            throw new Exception($name.'不能为空');
        }
        if(strlen($value) < 6){
            throw new Exception($name.'至少6个字符');
        }
        if(strlen($value) > 32){
            throw new Exception($name.'最多32个字符');
        }
    }

    private function requestApi($act, $params){
        $requrl = self::$apiurl . '?act=' . $act;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);

        if (curl_errno($ch) > 0) {
            curl_close($ch);
            throw new Exception('接口请求失败');
        }

        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpStatusCode != 200) {
            curl_close($ch);
            $arr = json_decode($response, true);
            throw new Exception($arr ? $arr['msg'] : '接口返回数据解析失败');
        }

        curl_close($ch);
        return $response;
    }

}