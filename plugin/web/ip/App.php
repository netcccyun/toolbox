<?php
/**
 * IP地址查询
 */

namespace plugin\web\ip;

use app\Plugin;
use Exception;
use think\facade\Db;
use think\facade\View;

class App extends Plugin
{

    const CACHE_TIME = 172800;

    public function index()
    {
        View::assign('myip', real_ip());
        return $this->view();
    }

    public function query(){
        $ip = input('post.ip', null, 'trim');
        $apitype = input('?post.apitype')?input('post.apitype'):'pconline';
        if(!$ip || !$apitype) return msg('error','no ip');
        if(is_numeric($ip)) $ip = long2ip($ip);
        if(filter_var($ip, FILTER_VALIDATE_IP)){
            $type = 'ip';
        }elseif(checkdomain($ip)){
            $type = 'domain';
        }else{
            return msg('error', 'IP或域名格式不正确！');
        }

        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }

        if($type == 'domain'){
            $ip = gethostbyname($ip);
            if(!$ip || !filter_var($ip, FILTER_VALIDATE_IP)){
                return msg('error', '未查询到该域名的解析记录');
            }
        }
        $ipnum = bindec(decbin(ip2long($ip)));
        
        if(self::CACHE_TIME > 0){
            $cache = Db::name('querycache')->where('type', 'ip')->where('key', $apitype.'-'.$ip)->find();
            if($cache && time() - strtotime($cache['uptime']) <= self::CACHE_TIME){
                $array = json_decode($cache['content'], true);
                return msg('ok','success',['data'=>$array, 'ip'=>$ip, 'ipnum'=>$ipnum, 'type'=>$type]);
            }
        }
        

        $classname = 'plugin\\web\\ip\\api\\'.$apitype;
        if(class_exists($classname)){
            $instance = new $classname();
            try{
                $result = $instance->query($ip);
            }catch(Exception $e){
                return msg('error', $e->getMessage());
            }
        }else{
            return msg('error', '该查询接口不存在');
        }

        if(self::CACHE_TIME > 0 && $apitype != 'chunzhen' && $apitype != 'ip2regoin'){
            Db::name('querycache')->duplicate([
                'content' => json_encode($result),
                'uptime' => date('Y-m-d H:i:s')
            ])->insertGetId([
                'type' => 'ip',
                'key' => $apitype.'-'.$ip,
                'content' => json_encode($result),
                'uptime' => date('Y-m-d H:i:s')
            ]);
        }

        return msg('ok','success',['data'=>$result, 'ip'=>$ip, 'ipnum'=>$ipnum, 'type'=>$type]);
    }

    public function item(){
        $id = input('post.id');
        if(!$id) return msg('error','no id');
        $cache = Db::name('querycache')->where('id', $id)->find();
        if($cache){
            $array = json_decode($cache['content'], true);
            return msg('ok','success',$array);
        }else{
            return msg('error','记录不存在');
        }
    }
}