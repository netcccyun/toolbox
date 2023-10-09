<?php

namespace app\controller;

use think\facade\Db;
use think\facade\View;
use think\facade\Validate;

class Index extends Base
{
    const CACHE_TIME = 0;

    public function index()
    {
        $category = Db::name('category')->cache('categorys', self::CACHE_TIME)->field('id,title,icon')->where('enable', 1)->order('weight','desc')->select();
        $link = Db::name('link')->cache('links', self::CACHE_TIME)->field('id,name,url')->where('enable', 1)->order('weight','desc')->select();

        $tool = Db::name('plugin')->cache('plugins', self::CACHE_TIME)->field('id,title,alias,keyword,request_count,category_id,level')->where('enable', 1)->order('weight','desc')->select();
        $list = [];
        foreach($category as $item){
            $list2 = [];
            foreach($tool as $row){
                if(!plugin_userlevel($row['level'])) continue;
                if($row['category_id'] == $item['id']){
                    $row['url'] = get_plugin_url($row['alias']);
                    $row['out'] = substr($row['alias'],0,1) == '/' || substr($row['alias'],0,7) == 'http://' || substr($row['alias'],0,8) == 'https://';
                    $list2[] = $row;
                }
            }
            $list[] = ['id'=>$item['id'], 'title'=>$item['title'], 'icon'=>$item['icon'], 'items'=>$list2];
        }

        View::assign('category', $category);
        View::assign('tool', $list);
        View::assign('link', $link);
        return view();
    }

    public function stars()
    {
        if(request()->isAjax()){
            if(!request()->islogin){
                return msg('error', '登录后才能收藏工具');
            }
            $uid = request()->user['id'];
            $do = input('post.do');
            if($do == 'clear'){
                Db::name('user')->where('id', $uid)->update(['stars'=>'']);
                return msg();
            }
            $id = input('post.id/d');
            if(empty($do) || empty($id)) return msg('error', 'param error');
            $plugin = Db::name('plugin')->where('id', $id)->where('enable',1)->find();
            if(!$plugin) return msg('error', '工具不存在');
            $stars = explode(',',request()->user['stars']);
            if ($do == 'add' && !in_array($id, $stars)) {
                array_push($stars, $id);
            } elseif ($do == 'del') {
                if (($key = array_search($id, $stars)) !== false) {
                    unset($stars[$key]);
                }
            }
            $stars = implode(',', array_unique(array_values($stars)));
            Db::name('user')->where('id', $uid)->update(['stars'=>$stars]);
            return msg('ok', $do == 'add' ? '添加收藏成功！' : '取消收藏成功！');
        }

        if(!request()->islogin){
            return $this->alert('info', '请先登录', '/login');
        }

        $category = Db::name('category')->cache('categorys', self::CACHE_TIME)->field('id,title,icon')->where('enable', 1)->order('weight','desc')->select();
        $list = [];

        $stars = request()->user['stars'];
        if(strlen($stars)>0){
            $stars = explode(',',$stars);
            $tool = Db::name('plugin')->cache('plugins', self::CACHE_TIME)->field('id,title,alias,keyword,request_count,category_id,level')->where('enable', 1)->order('weight','desc')->select();
            $list = [];
            foreach($category as $item){
                foreach($tool as $row){
                    if(!plugin_userlevel($row['level'])) continue;
                    if($row['category_id'] == $item['id'] && in_array($row['id'],$stars)){
                        $row['url'] = get_plugin_url($row['alias']);
                        $row['out'] = substr($row['alias'],0,1) == '/' || substr($row['alias'],0,7) == 'http://' || substr($row['alias'],0,8) == 'https://';
                        $list[] = $row;
                    }
                }
            }
        }

        View::assign('category', $category);
        View::assign('tool', $list);
        return view();
    }

    public function history()
    {
        if(request()->isAjax()){
            $do = input('post.do');
            if($do == 'clear'){
                cookie('tools', null);
                return msg();
            }
        }

        $category = Db::name('category')->cache('categorys', self::CACHE_TIME)->field('id,title,icon')->where('enable', 1)->order('weight','desc')->select();
        $list = [];

        $history = cookie('tools');
        if(strlen($history)>0){
            $history = array_reverse(array_unique(explode(',',$history)));
            $tool = Db::name('plugin')->cache('plugins', self::CACHE_TIME)->field('id,title,alias,keyword,request_count,category_id,level')->where('enable', 1)->order('weight','desc')->select();
            $newtool = [];
            foreach($tool as $row){
                $newtool[$row['id']] = $row;
            }
            $list = [];
            foreach($history as $id){
                if(!isset($newtool[$id])) continue;
                $row = $newtool[$id];
                if(!plugin_userlevel($row['level'])) continue;
                $row['url'] = get_plugin_url($row['alias']);
                $row['out'] = substr($row['alias'],0,1) == '/' || substr($row['alias'],0,7) == 'http://' || substr($row['alias'],0,8) == 'https://';
                $list[] = $row;
            }
        }

        View::assign('category', $category);
        View::assign('tool', $list);
        return view();
    }

    public function comment(){
        if(request()->isAjax()){
            $do = input('param.do');
            if($do == 'add'){
                if(!request()->islogin){
                    return msg('error', '登录后才能提交留言');
                }
                $email = input('post.email', null, 'trim,strip_tags,htmlspecialchars');
                $content = input('post.content', null, 'trim,strip_tags,htmlspecialchars');

                $data = [
                    'uid' => request()->user['id'],
                    'email' => $email,
                    'content' => $content,
                    'enable' => 0,
                    'create_time' => date("Y-m-d H:i:s"),
                    'update_time' => date("Y-m-d H:i:s")
                ];
                $validate = Validate::rule([
                    'email|电子邮箱' => 'require|email',
                    'content|留言内容' => 'require',
                ]);
                if (!$validate->check($data)) {
                    return msg('error', $validate->getError());
                }

                $captcha_result = verify_captcha4_slide();
                if($captcha_result !== true){
                    return msg('error', $captcha_result);
                }

                $last = Db::name('comment')->where('uid', request()->user['id'])->order('id','desc')->find();
                if($last && time() - strtotime($last['create_time']) < 600){
                    return msg('error', '你发表留言的速度太快了，过段时间再来吧');
                }

                Db::name('comment')->insert($data);
                return msg();
            }else{
                $page = input('get.page/d');
                $limit = 5;
                $uid = request()->islogin ? request()->user['id'] : 0;
                $select = Db::name('comment')->alias('A')->leftJoin('user B', 'A.uid=B.id')->field('A.id,A.uid,content,reply,A.enable,A.create_time,A.update_time,B.username,B.avatar_url')->where('A.enable', 1)->whereOr('A.uid', $uid);
                $total = $select->count();
                $comment = $select->order('id','desc')->page($page, $limit)->select();
                $items = [];
                foreach($comment as $item){
                    if(!$item['avatar_url']) $item['avatar_url'] = '/static/images/user.png';
                    $item['time'] = dgmdate($item['create_time']);
                    $items[] = $item;
                }
                return msg('ok', 'success', ['total'=>$total, 'page'=>$page, 'pagenum'=>ceil($total/$limit), 'items'=>$items]);
            }
        }
        return view();
    }

    public function captcha(){
        $GtSdk = new \app\lib\GeetestLib(config_get('captcha_id'), config_get('captcha_key'));
        $data = array(
            'user_id' => request()->islogin?request()->user['id']:'public',
            'client_type' => "web",
            'ip_address' => $this->clientip
        );
        $result = $GtSdk->pre_process($data);
        session('gtserver', $result['success']);
        return json($result);
    }

    public function statistics(){
        $id = input('post.id/d');
        if(!$id) return msg('error', 'param error');
        $plugin = Db::name('plugin')->where('id', $id)->where('enable', 1)->find();
        if(!$plugin) return msg('error', '工具不存在');
        Db::name('plugin')->where('id', $id)->inc('request_count')->update();
        
        $history = cookie('tools');
        if(!$history) $history = [];
        else $history = array_unique(explode(',',$history));
        if(in_array($id, $history)){
            $key = array_search($id,$history);
            unset($history[$key]);
        }
        $history[] = $id;
        if(count($history) > 20){
            $history = array_splice($history, 0, 20);
        }
        cookie('tools', implode(',', $history));

        return msg();
    }

}
