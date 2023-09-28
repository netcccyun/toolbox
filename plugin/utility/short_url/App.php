<?php
/**
 * 短网址生成
 */

namespace plugin\utility\short_url;

use app\Plugin;
use think\facade\Validate;
use Exception;

class App extends Plugin
{

    public function index()
    {
        return $this->view();
    }

    public function generate(){
        $params = request()->param();
        $validate = Validate::rule([
            'apitype' => 'require',
            'url' => 'require|url'
        ]);
        if (!$validate->check($params)) {
            return msg('error', $validate->getError());
        }

        $captcha_result = verify_captcha4_slide();
        if($captcha_result !== true){
            return msg('error', $captcha_result);
        }

        $classname = 'plugin\\utility\\short_url\\api\\'.$params['apitype'];
        if(class_exists($classname)){
            $instance = new $classname();
            try{
                $shortUrl = $instance->create($params['url']);
                return json(['code'=>0, 'msg'=>'success', 'data'=>$shortUrl]);
            }catch(Exception $e){
                return json(['code'=>-1, 'msg'=>$e->getMessage()]);
            }
        }else{
            return json(['code'=>-1, 'msg'=>'该接口不存在']);
        }
    }
    
}