<?php
/**
 * QQ高速图床
 */

namespace plugin\utility\imghosting;

use app\Plugin;
use think\facade\Request;
use think\facade\Db;
use Exception;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function upload(){
        if(!isset($_FILES['file']))return json(['code'=>-1, 'msg'=>'请选择文件']);
        $filepath = $_FILES["file"]["tmp_name"];
        $filename = $_FILES["file"]["name"];
        if($_FILES['file']['error']>0 || $filepath == ""){
            return json(['code'=>-1, 'msg'=>'文件损坏！']);
        }
        if($_FILES['file']['size']>10*1024*1024){
            return json(['code'=>-1, 'msg'=>'文件最大10M']);
        }
        $apitype = input('?post.apitype')?input('post.apitype'):'qq';

        $classname = 'plugin\\utility\\imghosting\\api\\'.$apitype;
        if(class_exists($classname)){
            $instance = new $classname();
            try{
                $result = $instance->upload($filepath, $filename);
                
                Db::name('uploadlog')->insert([
                    'uid' => request()->islogin ? request()->user['id'] : 0,
                    'type' => 'image',
                    'source' => $apitype,
                    'filename' => $filename,
                    'fileurl' => $result['url'],
                    'ip' => $this->clientip,
                    'addtime' => date("Y-m-d H:i:s")
                ]);

                return json(['code'=>0, 'msg'=>'success', 'data'=>$result]);
            }catch(Exception $e){
                return json(['code'=>-1, 'msg'=>$e->getMessage()]);
            }
        }else{
            return json(['code'=>-1, 'msg'=>'该上传类型不存在']);
        }
    }
    
    public function uploadurl(){
        exit;
        $url = input('post.url');
        if(!$url) return json(['code'=>-1, 'msg'=>'no url']);
        $apitype = input('?post.apitype')?input('post.apitype'):'qq';

        $file_data = get_curl($url);
        if(!$file_data) return json(['code'=>-1, 'msg'=>'获取图片数据失败']);
        $filepath = tempnam(sys_get_temp_dir(), 'IMG');
        file_put_contents($filepath, $file_data);
        $filename = time().'.jpg';

        $classname = 'plugin\\utility\\imghosting\\api\\'.$apitype;
        if(class_exists($classname)){
            $instance = new $classname();
            try{
                $result = $instance->upload($filepath, $filename);
                unlink($filepath);

                Db::name('uploadlog')->insert([
                    'uid' => request()->islogin ? request()->user['id'] : 0,
                    'type' => 'image',
                    'source' => $apitype,
                    'filename' => $filename,
                    'fileurl' => $result['url'],
                    'ip' => $this->clientip,
                    'addtime' => date("Y-m-d H:i:s")
                ]);

                return json(['code'=>0, 'msg'=>'success', 'data'=>$result]);
            }catch(Exception $e){
                unlink($filepath);
                return json(['code'=>-1, 'msg'=>$e->getMessage()]);
            }
        }else{
            unlink($filepath);
            return json(['code'=>-1, 'msg'=>'该上传类型不存在']);
        }
    }
}