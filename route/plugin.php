<?php

use think\facade\Route;

Route::rule('api/:alias/[:method]', function () {
    $alias = plugin_alias_get();
    $method = plugin_method_get();
    $model = plugin_info_get($alias);
    if (!$model){
        return msg("error", "该插件不存在");
    }
    $class = "plugin\\{$model['class']}\\App";
    if (!class_exists($class)) {
        return msg("error", "该插件类不存在");
    }
    $app = new $class();
    if (!method_exists($app, $method)) {
        return msg("error", "该插件不存在".$method."方法");
    }
    if($model['login'] == 1 && !request()->islogin){
        return msg("error", "请先登录");
    }
    $app->initialize($model);
    return $app->$method();
})->middleware(\app\middleware\LoadConfig::class)
    ->middleware(\app\middleware\AuthUser::class)
    ->middleware(\app\middleware\RefererCheck::class);

Route::get(':alias/[:method]', function () {
    $alias = plugin_alias_get();
    $method = plugin_method_get();
    $model = plugin_info_get($alias);
    if (!$model){
        abort(404, '页面不存在');
    }
    $class = "plugin\\{$model['class']}\\App";
    if (!class_exists($class)) {
        abort(404, '该插件类不存在');
    }
    $app = new $class();
    if (!method_exists($app, $method)) {
        abort(404, '该插件不存在'.$method.'方法');
    }
    if($model['login'] == 1 && !request()->islogin){
        return $app->alert('info', '请先登录', '/login');
    }
    $app->initialize($model);
    return $app->$method();
})->middleware(\app\middleware\LoadConfig::class)
    ->middleware(\app\middleware\AuthUser::class)
    ->middleware(\app\middleware\ViewOutput::class);
    