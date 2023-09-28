<?php
/**
 * 银行卡归属地查询验证
 */

namespace plugin\utility\bankcard;

use app\Plugin;
use Exception;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $cardnum = input('post.cardnum', null, 'trim');
        if(!$cardnum) return msg('error','no cardnum');

        if(strlen($cardnum) <8){
            return msg('error', '银行卡号有误，请输入正确的银行卡号');
        }

        $msg['card_num'] = $cardnum;
        $res = $this->getBankCardInfo($cardnum);
        $msg['validated'] = $res['validated'];
        if($res['validated']){
            $bank_arr = json_decode(file_get_contents(dirname(__FILE__).'/bank.json'), true);
            $type_arr = ['DC'=>'储蓄卡', 'CC'=>'信用卡'];
            $msg['bank_code'] = $res['bank'];
            $msg['bank_name'] = $bank_arr[$res['bank']] ? $bank_arr[$res['bank']] : $res['bank'];
            $msg['card_type'] = $type_arr[$res['cardType']];
        }else{
            $msg['error'] = $res['messages'][0]['errorCodes'];
        }

        return msg('ok','success',$msg);
    }

    private function getBankCardInfo($cardNum){
        $url = 'https://ccdcapi.alipay.com/validateAndCacheCardInfo.json?_input_charset=utf-8&cardNo='.$cardNum.'&cardBinCheck=true';
        $data = get_curl($url);
        $arr = json_decode($data, true);
        return $arr;
    }

}