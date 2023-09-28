<?php

namespace app\controller\admin;

use app\controller\Base;
use think\facade\Request;
use think\facade\Validate;
use think\facade\Db;

class Comment extends Base
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

        $select = Db::name('comment');
        if(!empty($params['uid'])){
            $select->where('uid', $params['uid']);
        }
        if(!empty($params['content'])){
            $select->where('content', 'like', '%' . $params['content'] . '%');
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

        $item = Db::name('comment')->where('id', $params['id'])->findOrEmpty();

        return msg('ok', 'success', $item);
    }

    public function edit()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'id' => 'require',
            'content|留言内容' => 'require',
            'enable' => 'require|integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('comment')->where('id', $params['id'])->update([
            'content' => $params['content'],
            'reply' => $params['reply'],
            'enable' => $params['enable'],
            'update_time' => date("Y-m-d H:i:s")
        ]);

        return msg();
    }

    public function enable()
    {
        $id = input('post.id/d');
        $enable = input('post.enable/d');

        Db::name('comment')->where('id', $id)->update([
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

        Db::name('comment')->where('id', $params['id'])->delete();

        return msg();
    }


    public function uploadlog()
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

        $select = Db::name('uploadlog');
        if(!empty($params['uid'])){
            $select->where('uid', $params['uid']);
        }
        if(!empty($params['ip'])){
            $select->where('ip', $params['ip']);
        }
        if(!empty($params['fileurl'])){
            $select->where('fileurl', $params['fileurl']);
        }
        $total = $select->count();
        $items = $select->order('id','desc')->page($page, $limit)->select();

        return msg("ok", "success", ['total'=>$total, 'items'=>$items]);
    }
}