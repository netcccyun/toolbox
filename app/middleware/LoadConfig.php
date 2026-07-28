<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Db;
use think\facade\Config;
use think\facade\Cache;
use think\helper\Str;

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
        if (empty($res['syskey'])) {
            $syskey = Str::random(16);
            config_set('syskey', $syskey);
            Cache::delete('configs');
            $res['syskey'] = $syskey;
        }
        Config::set($res, 'sys');

        return $next($request);
    }
}
