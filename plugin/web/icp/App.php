<?php
/**
 * ICP备案查询
 */

namespace plugin\web\icp;

use app\Plugin;
use Exception;
use think\facade\Db;

class App extends Plugin
{

    const CACHE_TIME = 604800;

    public function index()
    {
        return $this->view();
    }

    public function query(){
        $domain = input('post.domain', null, 'trim');
        $type = input('post.type', 'web', 'trim');
        if(!$domain) return msg('error','no domain');
        if(strpos($domain,'.') && !checkdomain($domain)){
            return msg('error', '域名格式不正确！');
        }

        $captcha_result = verify_captcha4();
        if($captcha_result !== true){
            return msg('error', '验证失败，请重新验证');
        }

        $cache = Db::name('querycache')->where('type', $type.'list')->where('key|subkey', $domain)->find();
        if($cache && time() - strtotime($cache['uptime']) <= self::CACHE_TIME){
            $array = json_decode($cache['content'], true);
            $data = Db::name('querycache')->where('type', $type.'item')->whereIn('id', implode(',',$array['list']))->select();
            $list = [];
            foreach($data as $row){
                $list[] = json_decode($row['content'], true);
            }
            return msg('ok','success',['total'=>$array['total'], 'list'=>$list]);
        }

        $cache = Db::name('querycache')->where('type', $type.'item')->where('key|subkey', $domain)->find();
        if($cache && time() - strtotime($cache['uptime']) <= self::CACHE_TIME){
            $array = json_decode($cache['content'], true);
            return msg('ok','success',['total'=>1, 'list'=>[$array]]);
        }

        try{
            $result = $this->execapi($type, $domain);
        }catch(Exception $e){
            return msg('error', $e->getMessage());
        }

        if($result['total'] > 1 && count($result['data']) > 1){
            $i = 0;
            foreach($result['data'] as $row){
                $id = Db::name('querycache')->duplicate([
                    'content' => json_encode($row),
                    'uptime' => date('Y-m-d H:i:s')
                ])->insertGetId([
                    'type' => $type.'item',
                    'key' => $row['domain'],
                    'subkey' => $row['webLicence'],
                    'content' => json_encode($row),
                    'uptime' => date('Y-m-d H:i:s')
                ]);
                $result['data'][$i++]['id'] = $id;
                $ids[] = $id;
            }
            Db::name('querycache')->duplicate([
                'content' => json_encode(['total'=>$result['total'], 'list'=>$ids]),
                'uptime' => date('Y-m-d H:i:s')
            ])->insert([
                'type' => $type.'list',
                'key' => $domain == $result['data'][0]['domain'] ? $result['data'][0]['domain'] : $result['data'][0]['unitName'],
                'subkey' => $domain == $result['data'][0]['domain'] ? $result['data'][0]['webLicence'] : $result['data'][0]['mainLicence'],
                'content' => json_encode(['total'=>$result['total'], 'list'=>$ids]),
                'uptime' => date('Y-m-d H:i:s')
            ]);
        }elseif($result['total'] == 1 && count($result['data']) > 0){
            $id = Db::name('querycache')->duplicate([
                'content' => json_encode($result['data'][0]),
                'uptime' => date('Y-m-d H:i:s')
            ])->insertGetId([
                'type' => $type.'item',
                'key' => $result['data'][0]['domain'],
                'subkey' => $result['data'][0]['webLicence'],
                'content' => json_encode($result['data'][0]),
                'uptime' => date('Y-m-d H:i:s')
            ]);
            $result['data'][0]['id'] = $id;
        }

        return msg('ok','success',['total'=>$result['total'], 'list'=>$result['data']]);
    }

    public function item(){
        $id = input('post.id');
        if(!$id) return msg('error','no id');
        $cache = Db::name('querycache')->where('id', $id)->find();
        if($cache){
            $array = json_decode($cache['content'], true);
            return msg('ok','success',['total'=>1, 'list'=>[$array]]);
        }else{
            return msg('ok','success',['total'=>0, 'list'=>[]]);
        }
    }

    /**
     * 对接工信部备案查询接口
     * 参考 https://github.com/HG-ha/ICP_Query
     */
    private function execapi($type, $domain){
        $types = [
            'web' => [1, false], 'app' => [6, false], 'mapp' => [7, false], 'kapp' => [8, false],
            'bweb' => [0, true], 'bapp' => [6, true], 'bmapp' => [7, true], 'bkapp' => [8, true],
        ];
        if(!isset($types[$type])) throw new Exception('不支持的查询类型');
        [$serviceType, $black] = $types[$type];

        $auth = null;
        $params = null;
        $lastError = '';
        for($i = 0; $i < 3; $i++){
            try{
                $auth = $this->miitCaptcha();
                $params = $this->miitQuery($serviceType, $domain, $auth, $black);
                break;
            }catch(Exception $e){
                $lastError = $e->getMessage();
                if(strpos($lastError, '验证码') === false) throw $e;
            }
        }
        if($params === null) throw new Exception($lastError ?: '查询接口请求失败');

        $list = [];
        foreach($params['list'] ?? [] as $row){
            if(!$black && $serviceType != 1 && !empty($row['dataId'])){
                $detail = $this->miitQueryDetail($row['dataId'], $serviceType, $auth);
                if($detail) $row = array_merge($row, $detail);
            }
            $list[] = [
                'domain' => $row['domain'] ?? ($row['serviceName'] ?? ''),
                'mainLicence' => $row['mainLicence'] ?? '',
                'webLicence' => $row['serviceLicence'] ?? '',
                'unitName' => $row['unitName'] ?? '',
                'unitType' => $row['natureName'] ?? '',
                'updateTime' => $row['updateRecordTime'] ?? '',
                'contentTypeName' => $row['contentTypeName'] ?? '',
            ];
        }

        return ['code' => 0, 'total' => $params['total'] ?? count($list), 'data' => $list];
    }

    private function miitCaptcha(){
        $headers = $this->miitHeaders();
        $token = $this->miitToken($headers);

        $requestHeaders = array_merge($headers, ['Content-Type: application/json', 'token: '.$token]);
        $arr = json_decode($this->miitPost($this->miitUrl('image/getCheckImagePoint'), json_encode(['clientUid' => 'point-'.$this->miitUid()]), $requestHeaders), true);
        if(!isset($arr['params']['uuid'], $arr['params']['smallImage'], $arr['params']['bigImage'])){
            throw new Exception('验证码获取失败');
        }
        $uuid = $arr['params']['uuid'];

        [$ok, $offset] = $this->miitSolveOffset($arr['params']['smallImage'], $arr['params']['bigImage']);
        if(!$ok) throw new Exception('验证码识别失败');

        $arr = json_decode($this->miitPost($this->miitUrl('image/checkImage'), json_encode(['key' => $uuid, 'value' => (string)$offset]), $requestHeaders), true);
        if(empty($arr['success']) || !isset($arr['params'])) throw new Exception('验证码校验失败');

        return [
            'token' => $token,
            'uuid' => $uuid,
            'sign' => is_string($arr['params']) ? $arr['params'] : json_encode($arr['params']),
            'headers' => $headers,
        ];
    }

    private function miitToken($headers){
        $timeStamp = (int)round(microtime(true) * 1000);
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $body = http_build_query(['authKey' => md5('testtest'.$timeStamp), 'timeStamp' => $timeStamp]);
        $arr = json_decode($this->miitPost($this->miitUrl('auth'), $body, $headers), true);
        if(empty($arr['params']['bussiness'])){
            throw new Exception(($arr['msg'] ?? '') ?: '获取查询令牌失败');
        }
        return $arr['params']['bussiness'];
    }

    private function miitQuery($serviceType, $keyword, $auth, $black = false){
        $headers = $auth['headers'];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'token: '.$auth['token'];
        $headers[] = 'sign: '.$auth['sign'];
        $headers[] = 'uuid: '.$auth['uuid'];
        if($black){
            if($serviceType == 0){
                $path = 'blackListDomain/queryByCondition';
                $data = ['domainName' => $keyword];
            }else{
                $path = 'blackListDomain/queryByCondition_appAndMini';
                $data = ['serviceName' => $keyword, 'serviceType' => $serviceType];
            }
        }else{
            $path = 'icpAbbreviateInfo/queryByCondition';
            $data = ['pageNum' => '', 'pageSize' => '', 'unitName' => $keyword, 'serviceType' => $serviceType];
        }
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);
        $arr = json_decode($this->miitPost($this->miitUrl($path), $body, $headers), true);
        if(isset($arr['code']) && $arr['code'] == 200 && !empty($arr['success']) && isset($arr['params'])){
            return $arr['params'];
        }
        throw new Exception(($arr['msg'] ?? '') ?: '查询接口请求失败');
    }

    private function miitQueryDetail($dataId, $serviceType, $auth){
        $headers = $auth['headers'];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'token: '.$auth['token'];
        $headers[] = 'sign: '.$auth['sign'];
        $headers[] = 'uuid: '.$auth['uuid'];
        $body = json_encode(['dataId' => $dataId, 'serviceType' => $serviceType], JSON_UNESCAPED_UNICODE);
        try{
            $arr = json_decode($this->miitPost($this->miitUrl('icpAbbreviateInfo/queryDetailByAppAndMiniId'), $body, $headers), true);
        }catch(Exception $e){
            return null;
        }
        return (!empty($arr['success']) && isset($arr['params'])) ? $arr['params'] : null;
    }

    private function miitUrl($path){
        return 'https://hlwicpfwc.miit.gov.cn/icpproject_query/api/'.$path;
    }

    private function miitHeaders(){
        return [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/101.0.4951.41 Safari/537.36 Edg/101.0.1210.32',
            'Origin: https://beian.miit.gov.cn',
            'Referer: https://beian.miit.gov.cn/',
            'Cookie: __jsluid_s='.bin2hex(random_bytes(16)),
            'Accept: application/json, text/plain, */*',
        ];
    }

    private function miitUid(){
        $chars = '0123456789abcdef';
        $id = '';
        for($i = 0; $i < 36; $i++) $id .= $chars[random_int(0, 15)];
        $id[14] = '4';
        $id[19] = $chars[(3 & hexdec($id[19])) | 8];
        foreach([8, 13, 18, 23] as $pos) $id[$pos] = '-';
        return $id;
    }

    private function miitPost($url, $body, $headers){
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_ENCODING => 'gzip',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if($response === false) throw new Exception('查询接口请求失败，请稍后重试');
        if(strpos($response, '当前访问疑似黑客攻击') !== false){
            throw new Exception('当前访问已被拦截，请稍后重试');
        }
        return $response;
    }

    /**
     * 计算滑块缺口横向偏移：在大图中寻找与滑块同尺寸的纯色近正方形区域
     */
    private function miitSolveOffset($smallImage, $bigImage){
        if(!function_exists('imagecreatefromstring')) throw new Exception('服务器未安装 GD 扩展，无法识别验证码');
        $small = @imagecreatefromstring(base64_decode($smallImage));
        $big = @imagecreatefromstring(base64_decode($bigImage));
        if(!$small || !$big) return [false, '验证码图片解析失败'];
        if(!imageistruecolor($big)) imagepalettetotruecolor($big);

        $sw = imagesx($small);
        $sh = imagesy($small);
        $w = intdiv(imagesx($big), 2);
        $h = intdiv(imagesy($big), 2);
        $minSide = max(1, intval(min($sw, $sh) * 0.25));
        $skipLeft = intdiv($sw, 4);
        $goodEnough = intdiv($minSide * $minSide * 3, 2);

        $colors = [];
        $counts = [];
        for($y = 0; $y < $h; $y++){
            for($x = 0; $x < $w; $x++){
                $rgb = imagecolorat($big, $x * 2, $y * 2);
                $r = (($rgb >> 16) & 0xFF) >> 2;
                $g = (($rgb >> 8) & 0xFF) >> 2;
                $b = ($rgb & 0xFF) >> 2;
                $color = ($r << 2) | (($g << 2) << 8) | (($b << 2) << 16);
                $colors[$y][$x] = $color;
                $counts[$color] = ($counts[$color] ?? 0) + 1;
            }
        }
        arsort($counts);
        $top = array_slice(array_keys($counts), 0, 3);

        $bestArea = 0;
        $bestX = 0;
        foreach($top as $color){
            $colRun = [];
            for($y = 0; $y < $h; $y++){
                for($x = 0; $x < $w; $x++){
                    $colRun[$y][$x] = $colors[$y][$x] === $color ? (($y > 0 ? $colRun[$y - 1][$x] : 0) + 1) : 0;
                }
            }
            for($y = $minSide; $y < $h; $y++){
                $x = $skipLeft;
                while($x < $w){
                    if($colRun[$y][$x] < $minSide){
                        $x++;
                        continue;
                    }
                    $start = $x;
                    while($x < $w && $colRun[$y][$x] >= $minSide) $x++;
                    $runW = $x - $start;
                    $runH = $colRun[$y][$start];
                    $ratio = $runW / $runH;
                    $area = $runW * $runH;
                    if($ratio > 0.7 && $ratio < 1.4 && $area > $bestArea){
                        $bestArea = $area;
                        $bestX = $start;
                        if($bestArea >= $goodEnough) return [true, $bestX * 2];
                    }
                }
            }
        }

        return $bestArea == 0 ? [false, '验证码识别失败'] : [true, $bestX * 2];
    }
}