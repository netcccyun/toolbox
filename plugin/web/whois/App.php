<?php
/**
 * 域名Whois查询
 */

namespace plugin\web\whois;

use app\Plugin;
use Exception;

class App extends Plugin
{

    // https://help.aliyun.com/document_detail/35793.html
    const status_name = ['ok'=>'正常状态', 'addPeriod'=>'域名新注册期', 'clientDeleteProhibited'=>'注册商设置禁止删除', 'serverDeleteProhibited'=>'注册局设置禁止删除', 'clientUpdateProhibited'=>'注册商设置禁止更新', 'serverUpdateProhibited'=>'注册局设置禁止更新', 'clientTransferProhibited'=>'注册商设置禁止转移', 'serverTransferProhibited'=>'注册局设置禁止转移', 'pendingVerification'=>'注册信息审核期', 'clientHold'=>'注册商设置暂停解析', 'serverHold'=>'注册局设置暂停解析', 'inactive'=>'非激活状态', 'clientRenewProhibited'=>'注册商设置禁止续费', 'serverRenewProhibited'=>'注册局设置禁止续费', 'pendingTransfer'=>'转移过程中', 'redemptionPeriod'=>'赎回期', 'pendingDelete'=>'待删除'];
    
    public function index()
    {
        return $this->view();
    }

    public function query(){
        $domain = input('post.domain', null, 'trim');
        if(!$domain) return msg('error','no domain');
        if(filter_var($domain, FILTER_VALIDATE_IP)){
            $type = 'ip';
        }elseif(checkdomain($domain)){
            $type = 'domain';
        }else{
            return msg('error', '域名或IP格式不正确！');
        }

        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }


        $url = 'https://whois.aite.xyz/?ajax&domain='.urlencode($domain);
        $data = get_curl($url,0,'https://whois.aite.xyz/');
        if(!$data) return msg('error', '查询失败，接口返回内容错误');

        if(strpos($data,'For more information on')){
            $data = substr($data, 0, strpos($data,'For more information on'));
        }

        return msg('ok','success',$data);
    }

}