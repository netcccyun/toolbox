<?php
/**
 * ICP备案查询
 */

namespace plugin\web\icp;

use app\Plugin;
use Exception;
use think\facade\Db;

class App extends Plugin
{

    const CACHE_TIME = 604800;

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $domain = input('post.domain', null, 'trim');
        if(!$domain) return msg('error','no domain');
        if(strpos($domain,'.') && !checkdomain($domain)){
            return msg('error', '域名格式不正确！');
        }

        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }

        $cache = Db::name('querycache')->where('type', 'icplist')->where('key|subkey', $domain)->find();
        if($cache && time() - strtotime($cache['uptime']) <= self::CACHE_TIME){
            $array = json_decode($cache['content'], true);
            $data = Db::name('querycache')->where('type', 'icpitem')->whereIn('id', implode(',',$array['list']))->select();
            $list = [];
            foreach($data as $row){
                $list[] = json_decode($row['content'], true);
            }
            return msg('ok','success',['total'=>$array['total'], 'list'=>$list]);
        }

        $cache = Db::name('querycache')->where('type', 'icpitem')->where('key|subkey', $domain)->find();
        if($cache && time() - strtotime($cache['uptime']) <= self::CACHE_TIME){
            $array = json_decode($cache['content'], true);
            return msg('ok','success',['total'=>1, 'list'=>[$array]]);
        }

        try{
            $result = $this->execapi($domain);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        if($result['total'] > 1 && count($result['data']) > 1){
            $i = 0;
            foreach($result['data'] as $row){
                $id = Db::name('querycache')->duplicate([
                    'subkey' => $row['webLicence'],
                    'content' => json_encode($row),
                    'uptime' => date('Y-m-d H:i:s')
                ])->insertGetId([
                    'type' => 'icpitem',
                    'key' => $row['domain'],
                    'subkey' => $row['webLicence'],
                    'content' => json_encode($row),
                    'uptime' => date('Y-m-d H:i:s')
                ]);
                $result['data'][$i++]['id'] = $id;
                $ids[] = $id;
            }
            Db::name('querycache')->duplicate([
                'subkey' => $result['data'][0]['mainLicence'],
                'content' => json_encode(['total'=>$result['total'], 'list'=>$ids]),
                'uptime' => date('Y-m-d H:i:s')
            ])->insert([
                'type' => 'icplist',
                'key' => $result['data'][0]['unitName'],
                'subkey' => $result['data'][0]['mainLicence'],
                'content' => json_encode(['total'=>$result['total'], 'list'=>$ids]),
                'uptime' => date('Y-m-d H:i:s')
            ]);
        }elseif($result['total'] == 1 && count($result['data']) > 0){
            $id = Db::name('querycache')->duplicate([
                'subkey' => $result['data'][0]['webLicence'],
                'content' => json_encode($result['data'][0]),
                'uptime' => date('Y-m-d H:i:s')
            ])->insertGetId([
                'type' => 'icpitem',
                'key' => $result['data'][0]['domain'],
                'subkey' => $result['data'][0]['webLicence'],
                'content' => json_encode($result['data'][0]),
                'uptime' => date('Y-m-d H:i:s')
            ]);
            $result['data'][0]['id'] = $id;
        }

        return msg('ok','success',['total'=>$result['total'], 'list'=>$result['data']]);
    }

    public function item(){
        $id = input('post.id');
        if(!$id) return msg('error','no id');
        $cache = Db::name('querycache')->where('id', $id)->find();
        if($cache){
            $array = json_decode($cache['content'], true);
            return msg('ok','success',['total'=>1, 'list'=>[$array]]);
        }else{
            return msg('ok','success',['total'=>0, 'list'=>[]]);
        }
    }

    private function execapi($domain){
        $timeStamp = time();
        $authKey = md5("testtest" . $timeStamp);
        $referer = 'https://beian.miit.gov.cn/';
        $headers = ['Origin: https://beian.miit.gov.cn'];
        $url = 'https://hlwicpfwc.miit.gov.cn/icpproject_query/api/auth';
        $post = 'authKey='.$authKey.'&timeStamp='.$timeStamp;
        $response = get_curl($url, $post, $referer, 0, 1, 0, 0, $headers);
        $body = substr($response, strpos($response, '{"'));
        $arr = json_decode($body, true);
        if(isset($arr['code']) && $arr['code']==200){
            $cookie = '';
            preg_match_all('/set-cookie: (.*?);/i', $response, $matchs);
            foreach ($matchs[1] as $val) {
                if(substr($val,-1)=='=')continue;
                $cookie.=$val.'; ';
            }
            
            $token = $arr['params']['bussiness'];

            $url = 'https://hlwicpfwc.miit.gov.cn/icpproject_query/api/icpAbbreviateInfo/queryByCondition';
            $post = json_encode(['pageNum'=>'','pageSize'=>'','unitName'=>$domain,'serviceType'=>1]);
            $headers[] = 'Content-Type: application/json; charset=UTF-8';
            $headers[] = 'token: '.$token;
            $response = get_curl($url, $post, $referer, $cookie, 0, 0, 0, $headers);
            $arr = json_decode($response, true);
            if(isset($arr['code']) && $arr['code']==200){
                $list = [];
                foreach($arr['params']['list'] as $row){
                    $list[] = ['domain'=>$row['domain'], 'mainLicence'=>$row['mainLicence'], 'webLicence'=>$row['serviceLicence'], 'unitName'=>$row['unitName'], 'unitType'=>$row['natureName'], 'updateTime'=>$row['updateRecordTime'], 'limitAccess'=>$row['limitAccess'], 'contentTypeName'=>$row['contentTypeName']];
                }
                return ['code'=>0, 'total'=>$arr['params']['total'], 'data'=>$list];
            }elseif(isset($arr['msg'])){
                throw new Exception($arr['msg']);
            }else{
                throw new Exception('查询接口(query)请求失败');
            }

        }elseif(isset($arr['msg'])){
            throw new Exception($arr['msg']);
        }else{
            throw new Exception('查询接口(auth)请求失败');
        }
    }
}