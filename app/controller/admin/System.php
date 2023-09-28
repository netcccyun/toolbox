<?php

namespace app\controller\admin;


use app\BaseController;
use think\facade\Db;
use think\facade\Request;
use think\facade\Validate;
use think\facade\Cache;

class System extends BaseController
{
    public function analysis(){
        $data['count1'] = Db::name('plugin')->count();
        $data['count2'] = Db::name('comment')->count();
        $data['count3'] = Db::name('user')->count();
        $data['count4'] = Db::name('user')->whereDay('create_time', date("Y-m-d"))->count();
        return msg('ok', 'success', $data);
    }

    public function info()
    {

        $tmp = 'version()';
        $mysqlVersion = Db::query("select version()")[0][$tmp];
        $data = [
            'framework_version' => app()::VERSION,
            'php_version' => PHP_VERSION,
            'mysql_version' => $mysqlVersion,
            'software' => $_SERVER['SERVER_SOFTWARE'],
            'os' => php_uname(),
            'date' => date("Y-m-d H:i:s"),
            'checkupdate' => '//auth.cccyun.cc/app/tool.php?version='.config('app.version').'&ver='.config('app.ver')
        ];
        return msg('ok', 'success', $data);

    }

    public function all()
    {
        $all = Db::name('config')->select();
        return msg('ok', 'success', $all);
    }

    public function get()
    {
        $key = Request::param('key');
        $value = config_get($key);
        return msg('ok', 'success', $value);
    }

    public function set()
    {
        $params = Request::param();

        foreach ($params as $v) {
            if (empty($v['key'])) {
                continue;
            }
            config_set($v['key'], $v['value']);
        }
        cache('configs', NULL);
        $all = Db::name('config')->select();
        return msg('ok', 'success', $all);
    }

    public function setpwd()
    {
        $params = Request::param();
        if(isset($params['username']))$params['username']=trim($params['username']);
        if(isset($params['oldpwd']))$params['oldpwd']=trim($params['oldpwd']);
        if(isset($params['newpwd']))$params['newpwd']=trim($params['newpwd']);
        if(isset($params['newpwd2']))$params['newpwd2']=trim($params['newpwd2']);

        $validate = Validate::rule([
            'username|用户名' => 'require|chsAlphaNum',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        config_set('admin_username', $params['username']);

        if(!empty($params['oldpwd']) && !empty($params['newpwd']) && !empty($params['newpwd2'])){
            $oldpwd = config_get('admin_password');
            if($oldpwd && !password_verify($params['oldpwd'], $oldpwd)){
                return msg('error', '旧密码不正确');
            }
            if($params['newpwd'] != $params['newpwd2']){
                return msg('error', '两次新密码输入不一致');
            }
            config_set('admin_password', password_hash($params['newpwd'], PASSWORD_DEFAULT));
        }
        cache('configs', NULL);
        cookie('admin_token', null);
        return msg();
    }

    public function templates()
    {
        $glob = glob(app()->getRootPath() . config("view.view_dir_name") . '/index/*');
        $arr = [];
        foreach ($glob as $v) {
            if (is_dir($v)) {
                array_push($arr, basename($v));
            }
        }
        return msg('ok', 'success', $arr);

    }

    public function clear(){
        Cache::clear();
        reset_opcache();
        return msg();
    }

    public function iptype(){
        $result = [
            ['name'=>'0_X_FORWARDED_FOR', 'ip'=>real_ip(0), 'city'=>get_ip_city(real_ip(0))],
            ['name'=>'1_X_REAL_IP', 'ip'=>real_ip(1), 'city'=>get_ip_city(real_ip(1))],
            ['name'=>'2_REMOTE_ADDR', 'ip'=>real_ip(2), 'city'=>get_ip_city(real_ip(2))]
        ];
        return msg('ok', 'success', $result);
    }
}
