<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    /** @var bool 用户是否登录 */
    public $islogin = false;

    /** @var array|null 当前登录用户 */
    public $user = null;
}
