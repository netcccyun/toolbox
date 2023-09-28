<?php

use think\facade\Route;

Route::get('/install/database', 'Install/database');
Route::get('/install/init_data', 'Install/init_data');
Route::get('/install/admin', 'Install/admin');
Route::get('/install', 'Install/index');


Route::group(function () {
    Route::any('/stars', 'stars');
    Route::any('/history', 'history');
    Route::any('/comment', 'comment');
    Route::get('/captcha', 'captcha');
    Route::get('/zcaptcha', 'zcaptcha');
    Route::post('/statistics', 'statistics');
    Route::post('/clitool', 'statistics');
    Route::get('/', 'index');
})->prefix('Index/')
    ->middleware(\app\middleware\LoadConfig::class)
    ->middleware(\app\middleware\AuthUser::class)
    ->middleware(\app\middleware\ViewOutput::class);


Route::group(function () {
    Route::get('/oauth/callback', 'callback');
    Route::post('/oauth/login', 'oauth');
    
    Route::any('/login', 'login')->middleware(\app\middleware\ViewOutput::class);
    Route::get('/logout', 'logout');

    Route::get('/verifycode', 'verifycode');

    Route::post('/adminlogin', 'adminlogin');
    Route::get('/adminlogout', 'adminlogout');

    Route::get('/qqlogin', 'qqlogin')->middleware(\app\middleware\ViewOutput::class);
    Route::get('/qqlogin_api', 'qqlogin_api')->middleware(\app\middleware\RefererCheck::class);
})->prefix('Auth/')
    ->middleware(\app\middleware\LoadConfig::class)
    ->middleware(\app\middleware\AuthUser::class);
