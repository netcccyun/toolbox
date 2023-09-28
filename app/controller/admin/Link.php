<?php

namespace app\controller\admin;

use app\controller\Base;
use think\facade\Request;
use think\facade\Validate;
use think\facade\Db;

class Link extends Base
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
        
        $page = intval($params['page']);
        $limit = intval($params['limit']);

        $select = Db::name('link');
        if(!empty($params['keyword'])){
            $select->where('name|url', 'like', '%' . $params['keyword'] . '%');
        }
        $total = $select->count();
        $items = $select->order('weight','desc')->page($page, $limit)->select();

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

        $item = Db::name('link')->where('id', $params['id'])->findOrEmpty();

        return msg('ok', 'success', $item);
    }

    public function add()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'name|名称' => 'require|chsAlphaNum|unique:link',
            'url|地址' => 'require',
            'weight|权重' => 'require|integer',
            'enable' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('link')->cache('links')->insert([
            'name' => trim($params['name']),
            'url' => trim($params['url']),
            'weight' => $params['weight'],
            'enable' => $params['enable'],
            'create_time' => date("Y-m-d H:i:s"),
            'update_time' => date("Y-m-d H:i:s")
        ]);

        return msg();
    }

    public function edit()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'id' => 'require',
            'name|名称' => 'require|chsAlphaNum|unique:link',
            'url|地址' => 'require',
            'weight|权重' => 'require|integer',
            'enable' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('link')->cache('links')->where('id', $params['id'])->update([
            'name' => trim($params['name']),
            'url' => trim($params['url']),
            'weight' => $params['weight'],
            'enable' => $params['enable'],
            'update_time' => date("Y-m-d H:i:s")
        ]);

        return msg();
    }

    public function enable()
    {
        $id = input('post.id/d');
        $enable = input('post.enable/d');

        Db::name('link')->cache('links')->where('id', $id)->update([
            'enable' => $enable,
            'update_time' => date("Y-m-d H:i:s")
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

        Db::name('link')->cache('links')->where('id', $params['id'])->delete();

        return msg();
    }
}