<?php
declare (strict_types=1);

namespace app\middleware;

use think\facade\Db;

class AuthUser
{
    public function handle($request, \Closure $next)
    {
        $islogin = false;
        $cookie = cookie('user_token');
        $user = null;
        if($cookie){
            $token=authcode($cookie, 'DECODE', config_get('syskey', ''));
            $parts = $token ? explode("\t", $token) : [];
            if(count($parts) === 3){
                list($uid, $sid, $expiretime) = $parts;
                $user = Db::name('user')->where('id', $uid)->find();
                if($user && $user['enable']==1){
                    $session=md5($user['id'].$user['password']);
                    if(hash_equals($session, $sid) && $expiretime>time()) {
                        if(!$user['avatar_url']) $user['avatar_url'] = '/static/images/user.png';
                        $islogin = true;
                    }
                }elseif($user && $user['enable']==0 && !session('user_block')){
                    session('user_block', '1');
                }
            }
        }
        $request->islogin = $islogin;
        $request->user = $islogin ? $user : null;
        /*if (!$islogin) {
            if ($request->isAjax() || !$request->isGet()) {
                return msg('error','请登录');
            }
            return redirect((string)url('/login'));
        }*/
        return $next($request);
    }
}
