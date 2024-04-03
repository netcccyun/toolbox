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
        if(!checkdomain($domain)){
            return msg('error', '域名格式不正确！');
        }

        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }

        $cache = Db::name('querycache')->where('type', 'icp')->where('key', $domain)->find();
        if($cache && time() - strtotime($cache['uptime']) <= self::CACHE_TIME){
            $array = json_decode($cache['content'], true);
            return msg('ok','success',$array);
        }

        try{
            $result = $this->queryapi($domain);
            if(!$result){
                return msg('ok','success',null);
            }
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        Db::name('querycache')->duplicate([
            'type' => 'icp',
            'key' => $result['Domain'],
            'content' => json_encode($result),
            'uptime' => date('Y-m-d H:i:s')
        ])->insert([
            'type' => 'icp',
            'key' => $result['Domain'],
            'content' => json_encode($result),
            'uptime' => date('Y-m-d H:i:s')
        ]);

        return msg('ok','success',$result);
    }

    private function queryapi($domain){
        $url = config_get('qqapi_url').'api.php?act=icpquery';
        $post = 'key='.config_get('qqapi_key').'&domain='.$domain;
        $data = get_curl($url, $post);
        $arr = json_decode($data, true);
        if(isset($arr['code']) && $arr['code']==0){
            return $arr['data'];
        }elseif(isset($arr['msg'])){
            throw new Exception($arr['msg']);
        }else{
            throw new Exception('接口请求失败');
        }
    }

}