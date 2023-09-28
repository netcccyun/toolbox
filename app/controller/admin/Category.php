<?php

namespace app\controller\admin;

use app\controller\Base;
use think\facade\Request;
use think\facade\Validate;
use think\facade\Db;

class Category extends Base
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

        $select = Db::name('category');
        if(!empty($params['title'])){
            $select->where('title', 'like', '%' . $params['title'] . '%');
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

        $item = Db::name('category')->where('id', $params['id'])->findOrEmpty();

        return msg('ok', 'success', $item);
    }

    public function add()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'title|分类标题' => 'require|unique:category',
            'icon|小图标' => 'require',
            'weight|权重' => 'require|integer',
            'enable' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('category')->cache('categorys')->insert([
            'title' => trim($params['title']),
            'icon' => trim($params['icon']),
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
            'icon|小图标' => 'require',
            'title|分类标题' => 'require|unique:category',
            'weight|权重' => 'require|integer',
            'enable' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('category')->cache('categorys')->where('id', $params['id'])->update([
            'title' => trim($params['title']),
            'icon' => trim($params['icon']),
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

        Db::name('category')->cache('categorys')->where('id', $id)->update([
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

        Db::name('category')->cache('categorys')->where('id', $params['id'])->delete();

        return msg();
    }
}