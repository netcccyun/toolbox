<?php

namespace app\controller\admin;


use app\BaseController;
use think\facade\Db;
use think\facade\Request;
use think\facade\Session;
use think\facade\Validate;

class Plugin extends BaseController
{
    public function list()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'page' => 'integer',
            'limit' => 'integer',
            'category_id' => 'integer',
            'class' => 'is_legal_plugin_class',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        $page = isset($params['page']) ? intval($params['page']) : 1;
        $limit = isset($params['limit']) ? intval($params['limit']) : 50;
        $orderby = ['id' => 'desc'];

        $select = Db::name('plugin');
        if(!empty($params['title'])){
            $select->where('title', 'like', '%' . $params['title'] . '%');
        }
        if(!empty($params['alias'])){
            $select->where('alias', $params['alias']);
        }
        if(!empty($params['class'])){
            $select->where('class', $params['class']);
        }
        if(!empty($params['enable'])){
            $select->where('enable', '1');
        }
        if(!empty($params['category_id'])){
            $select->where('category_id', $params['category_id']);
            $orderby = ['weight' => 'desc', 'id' => 'desc'];
        }
        $total = $select->count();
        $items = $select->order($orderby)->page($page, $limit)->select();

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

        $item = Db::name('plugin')->where('id', $params['id'])->findOrEmpty();

        return msg('ok', 'success', $item);
    }

    public function add()
    {
        $params = Request::param();

        $validate = Validate::rule([
            'title|插件标题' => 'require|unique:plugin',
            'alias|路由别名' => 'require|unique:plugin',
            'class|插件类名' => 'require|unique:plugin|is_legal_plugin_class',
            'category_id|分类' => 'require|integer',
            'level|用户等级限制' => 'integer',
            'enable' => 'integer',
            'weight|权重' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('plugin')->cache('plugins')->insert([
            'title' => trim($params['title']),
            'alias' => trim($params['alias']),
            'class' => trim($params['class']),
            'keyword' => trim($params['keyword']),
            'weight' => $params['weight'],
            'enable' => $params['enable'],
            'category_id' => $params['category_id'],
            'level' => $params['level'],
            'login' => $params['login'],
            'desc' => $params['desc'],
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
            'title|插件标题' => 'require|unique:plugin',
            'alias|路由别名' => 'unique:plugin',
            'class|插件类名' => 'unique:plugin|is_legal_plugin_class',
            'category_id|分类' => 'integer',
            'level|用户等级限制' => 'integer',
            'enable' => 'integer',
            'weight|权重' => 'integer',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        if(isset($params['level']) && isset($params['enable'])){
            Db::name('plugin')->cache('plugins')->where('id', $params['id'])->update([
                'title' => trim($params['title']),
                'alias' => trim($params['alias']),
                'class' => trim($params['class']),
                'keyword' => trim($params['keyword']),
                'weight' => $params['weight'],
                'enable' => $params['enable'],
                'category_id' => $params['category_id'],
                'level' => $params['level'],
                'login' => $params['login'],
                'desc' => $params['desc'],
                'update_time' => date("Y-m-d H:i:s")
            ]);
        }else{
            Db::name('plugin')->cache('plugins')->where('id', $params['id'])->update([
                'title' => trim($params['title']),
                'alias' => trim($params['alias']),
                'class' => trim($params['class']),
                'category_id' => $params['category_id'],
                'desc' => $params['desc'],
                'update_time' => date("Y-m-d H:i:s")
            ]);
        }
        
        return msg();
    }

    public function enable(){
        $params = Request::param();

        $validate = Validate::rule([
            'id' => 'require',
            'category_id|分类' => 'integer',
            'enable' => 'integer'
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        Db::name('plugin')->cache('plugins')->where('id', $params['id'])->update($params);
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

        $plugin = Db::name('plugin')->where('id', $params['id'])->find();
        if(!$plugin) return msg('ok', 'success');

        Db::startTrans();
        try {
            $classPath = plugin_path_get() . '/'.$plugin['class'].'/Install.php';
            if (file_exists($classPath)) {
                require $classPath;
                $class = 'plugin\\'.$plugin['class'].'\\Install';
                if (class_exists($class)) {
                    $uninstall = new $class();
                    $uninstall->UnInstall();
                }
            }
            Db::name('plugin')->cache('plugins')->where('id', $params['id'])->delete();
            if (!empty($plugin['class'])) {
                del_tree(plugin_path_get($plugin['class']));
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return msg('error', $e->getMessage(), $e);
        }
        return msg('ok', 'success');
    }

    public function upload()
    {
        $params = Request::file();

        $validate = Validate::rule([
            'file' => 'require|file',
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }
        $uploadedFile = Request::file('file');
        $plugin = new \app\lib\Plugin();
        $zipFilepath = $plugin->getZipFilepath();
        $uploadedFile->move(dirname($zipFilepath), basename($zipFilepath));
        return $plugin->install();
    }
}
