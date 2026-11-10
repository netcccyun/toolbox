<?php

namespace app\controller\admin;

use app\controller\Base;
use think\facade\Request;
use think\facade\Validate;
use think\facade\Db;

class User extends Base
{

    public function list()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'page' => 'integer',
            'limit' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        $page = isset($params['page']) ? intval($params['page']) : 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 50;
        if($page < 1) $page = 1;
        if($limit < 1) $limit = 50;

        $select = Db::name('user');
        if(!empty($params['id'])){
            $select->where('id|openid', $params['id']);
        }
        if(!empty($params['username'])){
            $select->where('username', 'like', '%' . $params['username'] . '%');
        }
        $total = $select->count();
        $items = $select->order('id','desc')->page($page, $limit)->select();

        return msg("ok", "success", ['total'=>$total, 'items'=>$items]);
    }

    public function get()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'id' => 'require|integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        $item = Db::name('user')->where('id', $params['id'])->findOrEmpty();

        return msg('ok', 'success', $item);
    }

    public function enable(){
        $params = Request::param();

        $validate = Validate::rule([
            'id' => 'require',
            'enable' => 'require|integer'
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('user')->where('id', $params['id'])->update([
            'enable' => intval($params['enable'])
        ]);
        return msg();
    }

    public function edit()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'id' => 'require',
            'level' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('user')->where('id', $params['id'])->update([
            'level' => $params['level']
        ]);

        return msg();
    }

    public function delete()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'id' => 'require|integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('user')->where('id', $params['id'])->delete();

        return msg('ok', 'success');
    }

    public function slogin(){
        $id = input('get.id/d');
        $item = Db::name('user')->where('id', $id)->find();
        if(!$item){
            return $this->alert('error', '用户不存在');
        }

        $session = md5($id.$item['password']);
        $expire = 30744000;
        $expiretime = time()+$expire;
        $token = authcode("{$id}\t{$session}\t{$expiretime}", 'ENCODE', config_get('syskey'));
        cookie('user_token', $token, ['expire' => $expire, 'httponly' => true]);

        return redirect("/");
    }

}