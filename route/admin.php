<?php

use think\facade\Route;

Route::group('admin', function () {
    Route::get('/menu', 'Menu/get');
    Route::get('/clear', 'System/clear');

    Route::get('/category/list', 'Category/list');
    Route::get('/category/get', 'Category/get');
    Route::post('/category/add', 'Category/add');
    Route::post('/category/edit', 'Category/edit');
    Route::post('/category/enable', 'Category/enable');
    Route::get('/category/delete', 'Category/delete');

    Route::get('/user/list', 'User/list');
    Route::get('/user/get', 'User/get');
    Route::post('/user/enable', 'user/enable');
    Route::post('/user/edit', 'user/edit');
    Route::get('/user/delete', 'User/delete');
    Route::get('/user/slogin', 'User/slogin');

    Route::get('/comment/list', 'Comment/list');
    Route::get('/comment/get', 'Comment/get');
    Route::post('/comment/edit', 'Comment/edit');
    Route::post('/comment/enable', 'Comment/enable');
    Route::get('/comment/delete', 'Comment/delete');
    Route::get('/comment/uploadlog', 'Comment/uploadlog');

    Route::get('/link/list', 'Link/list');
    Route::get('/link/get', 'Link/get');
    Route::post('/link/add', 'Link/add');
    Route::post('/link/edit', 'Link/edit');
    Route::post('/link/enable', 'Link/enable');
    Route::get('/link/delete', 'Link/delete');

    Route::get('/plugin/list', 'Plugin/list');
    Route::get('/plugin/get', 'Plugin/get');
    Route::post('/plugin/add', 'Plugin/add');
    Route::post('/plugin/edit', 'Plugin/edit');
    Route::post('/plugin/enable', 'Plugin/enable');
    Route::get('/plugin/delete', 'Plugin/delete');
    Route::post('/plugin/upload', 'Plugin/upload');

    Route::get('/system/analysis', 'System/analysis');
    Route::get('/system/templates', 'System/templates');
    Route::get('/system/plugin_templates', 'System/plugin_templates');
    Route::get('/system/info', 'System/info');
    Route::get('/system/all', 'System/all');
    Route::get('/system/get', 'System/get');
    Route::post('/system/set', 'System/set');
    Route::post('/system/setpwd', 'System/setpwd');
    Route::get('/system/iptype', 'System/iptype');

    Route::get('/update/check', 'Update/check');
    Route::get('/update/update', 'Update/update');
    Route::get('/update/database', 'Update/updateDatabase');
    Route::get('/update/script', 'Update/updateScript');
    
})->prefix('admin.')
    ->middleware(\app\middleware\LoadConfig::class)
    ->middleware(\app\middleware\AuthAdmin::class)
    ->middleware(\app\middleware\RefererCheck::class);

