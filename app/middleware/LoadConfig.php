<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Db;
use think\facade\Config;

class LoadConfig
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        if (!file_exists(app()->getRootPath().'.env')){
            return redirect((string)url('/install'));
        }

        $res = Db::name('config')->cache('configs',0)->column('value','key');
        Config::set($res, 'sys');

        return $next($request);
    }
}
