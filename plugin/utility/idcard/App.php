<?php
/**
 * 身份证号码归属地查询验证
 */

namespace plugin\utility\idcard;

use app\Plugin;
use Exception;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $idcard = input('post.idcard', null, 'trim');
        if(!$idcard) return msg('error','no idcard');

        if(strlen($idcard) != 18 && strlen($idcard) != 15){
            return msg('error', '身份证号码有误，请输入正确的号码');
        }

        $id_arr = strlen($idcard) == 18 ? array(substr($idcard,0,6),substr($idcard,6,4),substr($idcard,10,2),substr($idcard,12,2),substr($idcard,16,1)) : array(substr($idcard,0,6),'19'.substr($idcard,6,2),substr($idcard,8,2),substr($idcard,10,2),substr($idcard,14,1));

        $msg['身份证号码'] = $idcard;
        $msg['性别'] = $id_arr[4]%2==1 ? '男' : '女';
        $msg['出生日期'] = $id_arr[1].'年'.$id_arr[2].'月'.$id_arr[3].'日';
        $msg['发证地'] = $this->getareabycode(substr($idcard, 0, 6));
        
        if(strlen($idcard) == 18 && !$this->is_idcard($idcard)){
            $msg['提示'] = '该18位身份证号校验位不正确';
        }

        return msg('ok','success',$msg);
    }

    private function getareabycode($code){
        $codedata = include(dirname(__FILE__).'/data.php');
        $code = preg_replace('/(00)+$/', '', $code);
        $codeLength = strlen($code);
        if ($codeLength < 2 || $codeLength > 6 || $codeLength % 2 !== 0) {
            return null;
        }

        $provinceCode = substr($code, 0, 2) . '0000';

        if (!isset($codedata[$provinceCode])) {
            return null;
        }

        $province = $codedata[$provinceCode];

        if ($codeLength === 2) {
            return $province;
        }

        $prefectureCode = substr($code, 0, 4) . '00';

        if (!isset($codedata[$prefectureCode])) {
            return $province;
        }

        $area = $codedata[$prefectureCode];

        if ($codeLength === 4) {
            return $province . ' ' . $area;
        }

        if (!isset($codedata[$code])) {
            return $province . ' ' . $area;
        }

        $name = $codedata[$code];

        return $province . ' ' . $area . ' ' . $name;
    }

    private function is_idcard( $id )
    {
        $id = strtoupper($id);
        $regx = "/(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if(strlen($id)!=18 || !preg_match($regx, $id))
        {
            return false;
        }
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
        if(!strtotime($dtm_birth)) //检查生日日期是否正确
        {
            return false;
        }
        else
        {
            //检验18位身份证的校验码是否正确。
            //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
            $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
            $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
            $sign = 0;
            for ( $i = 0; $i < 17; $i++ )
            {
                $b = (int) $id[$i];
                $w = $arr_int[$i];
                $sign += $b * $w;
            }
            $n = $sign % 11;
            $val_num = $arr_ch[$n];
            if ($val_num != substr($id,17, 1))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }

}