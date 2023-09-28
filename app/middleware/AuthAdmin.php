<?php
declare (strict_types=1);

namespace app\middleware;


class AuthAdmin
{
    public function handle($request, \Closure $next)
    {
        $islogin = false;
        $cookie = cookie('admin_token');
        if($cookie){
            $token=authcode($cookie, 'DECODE', config_get('syskey'));
            if($token){
                list($user, $sid, $expiretime) = explode("\t", $token);
                $session=md5(config_get('admin_username').config_get('admin_password'));
                if($session==$sid && $expiretime>time()) {
                    $islogin = true;
                }
            }
        }
        if (!$islogin) {
            if ($request->isAjax() || !$request->isGet()) {
                return msg('error', '请登录')->code(401);
            }
            return redirect((string)url('/admin/login.html'));
        }
        return $next($request);
    }
}
