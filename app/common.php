<?php
// 应用公共文件
use think\facade\Db;
ini_set("display_errors", 1);

function template_path_get(): string
{
    return app()->getRootPath() . config("view.view_dir_name") . '/index/' . config_get('template') . DIRECTORY_SEPARATOR;
}

function plugin_alias_get()
{
    return trim(request()->param("alias"), '\\/');
}

function plugin_method_get()
{
    return request()->param("method", "index");
}

function plugin_current_class_get($namespace)
{
    return str_replace('plugin\\', '', $namespace);
}

function plugin_path_get($class = '')
{
    $class = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $class);
    return realpath(app()->getRootPath() . "/plugin/$class");
}

function plugin_info_get($alias = '')
{
    $plugin = Db::name('plugin')->where('alias',$alias)->where('enable',1)->find();
    if(!$plugin) return null;
    if(!plugin_userlevel($plugin['level'])) return null;
    $plugin['is_star'] = 0;
    if(request()->islogin){
        $stars = explode(',', request()->user['stars']);
        if(in_array($plugin['id'], $stars)){
            $plugin['is_star'] = 1;
        }
    }
    return $plugin;
}


function msg($status = "ok", $message = "success", $data = [])
{
    return json([
        "status" => $status,
        "message" => $message,
        "data" => $data,
    ]);
}

function reset_opcache()
{
    if (function_exists('opcache_reset')) opcache_reset();
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if(((int)substr($result, 0, 10) == 0 || (int)substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

function get_curl($url, $post=0, $referer=0, $cookie=0, $header=0, $ua=0, $nobody=0, $addheader=0)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$httpheader[] = "Accept: */*";
	$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
	$httpheader[] = "Connection: close";
	if($addheader){
		$httpheader = array_merge($httpheader, $addheader);
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	if ($post) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if ($header) {
		curl_setopt($ch, CURLOPT_HEADER, true);
	}
	if ($cookie) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	}
	if($referer){
		curl_setopt($ch, CURLOPT_REFERER, $referer);
	}
	if ($ua) {
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	}
	else {
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Safari/537.36");
	}
	if ($nobody) {
		curl_setopt($ch, CURLOPT_NOBODY, 1);
	}
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}

function jsonp_decode($jsonp, $assoc = false)
{
	$jsonp = trim($jsonp);
	if(isset($jsonp[0]) && $jsonp[0] !== '[' && $jsonp[0] !== '{') {
		$begin = strpos($jsonp, '(');
		if(false !== $begin)
		{
			$end = strrpos($jsonp, ')');
			if(false !== $end)
			{
				$jsonp = substr($jsonp, $begin + 1, $end - $begin - 1);
			}
		}
	}
	return json_decode($jsonp, $assoc);
}

function dgmdate($timestamp, $d_format = 'Y-m-d H:i') {
	$timestamp=strtotime($timestamp);
	$timestamp += 8 * 3600;
	$todaytimestamp = time() - (time() + 8 * 3600) % 86400 + 8 * 3600;
	$s = gmdate($d_format, $timestamp);
	$time = time() + 8 * 3600 - $timestamp;
	if($timestamp >= $todaytimestamp) {
		if($time > 3600) {
			return '<span title="'.$s.'">'.intval($time / 3600).'&nbsp;小时前</span>';
		} elseif($time > 1800) {
			return '<span title="'.$s.'">半小时前</span>';
		} elseif($time > 60) {
			return '<span title="'.$s.'">'.intval($time / 60).'&nbsp;分钟前</span>';
		} elseif($time > 0) {
			return '<span title="'.$s.'">'.$time.'&nbsp;秒前</span>';
		} elseif($time == 0 || $time < 0) {
			return '<span title="'.$s.'">刚刚</span>';
		} else {
			return $s;
		}
	} elseif(($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
		if($days == 0) {
			return '<span title="'.$s.'">昨天&nbsp;'.gmdate('H:i', $timestamp).'</span>';
		} elseif($days == 1) {
			return '<span title="'.$s.'">前天&nbsp;'.gmdate('H:i', $timestamp).'</span>';
		} else {
			return '<span title="'.$s.'">'.($days + 1).'&nbsp;天前</span>';
		}
	} else {
		return $s;
	}
}

function unzip($filepath, $filename)
{
    if (!file_exists($filepath)) {
        return false;
    }
    $zip = new ZipArchive;

    if ($zip->open($filepath) === true) {
        $zip->extractTo($filename);
        $zip->close();
        return true;
    }
    return false;
}

//多维转一维数组
function multi2one($data, $dir = '', $step = '')
{
    $list = [];
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            $list = array_merge($list, multi2one($v, $dir . $step . $k, $step));
        } else {
            $list[] = ltrim($dir . $step . $v, '\\/');
        }
    }
    return $list;
}

function tree_relative($dir)
{
    if (!is_dir($dir)) {
        return [basename($dir)];
    }
    $arr = [];
    $scandir = scandir($dir);
    foreach ($scandir as $v) {
        if ($v != '.' && $v != '..') {
            if (is_dir("$dir/$v")) {
                $arr[$v] = tree_relative("$dir/$v");
            } else {
                $arr[] = $v;
            }
        }
    }
    return $arr;
}

function copy_dir($src, $target)
{
    if (!is_dir($target)) {
        mkdir($target, 0777, true);
    }
    foreach (glob($src . '/*') as $filename) {
        $targetFilename = $target . '/' . basename($filename);
        if (is_dir($filename)) {
            // 如果是目录，递归合并子目录下的文件。
            copy_dir($filename, $targetFilename);
        } elseif (is_file($filename)) {
            copy($filename, $targetFilename);
        }
    }
}

function del_tree($dir)
{
    if (!file_exists($dir)) {
        return true;
    }
    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? del_tree("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}

function config_get($key, $default = null)
{
    $value = config('sys.'.$key);
    return $value ?: $default;
}

function config_set($key, $value)
{
    $res = Db::name('config')->replace()->insert(['key'=>$key, 'value'=>$value]);
    return $res!==false;
}

function get_version()
{
    return VERSION;
}

function format_date($timestamp = null)
{
    if ($timestamp === null) {
        $timestamp = time();
    }
    return date('Y-m-d H:i:s', $timestamp);
}

//当前命名空间的包名
function base_space_name($space)
{
    $str_replace = str_replace('\\', '/', $space);
    return basename($str_replace);
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($str, $start)
    {
        return (@substr_compare($str, $start, 0, strlen($start)) == 0);
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        $needle_len = strlen($needle);
        return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, -$needle_len));
    }
}

if (!function_exists('is_valid_url')) {

    function is_valid_url($url = null)
    {
        if (empty($url)) return false;
        if (!is_string($url)) return false;
        $filter_var = boolval(filter_var($url, FILTER_VALIDATE_URL));
        if ($filter_var) return $filter_var;
        $parse_url = parse_url($url);
        $path = array_pop($parse_url);

        $url = str_ireplace($path, '/' . urlencode($path), $url);
        return boolval(filter_var($url, FILTER_VALIDATE_URL));
    }

}

function real_ip($type=0){
    $ip = $_SERVER['REMOTE_ADDR'];
    if($type<=0 && isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (filter_var($xip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                $ip = $xip;
                break;
            }
        }
    } elseif ($type<=0 && isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ($type<=1 && isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif ($type<=1 && isset($_SERVER['HTTP_ALI_CDN_REAL_IP']) && filter_var($_SERVER['HTTP_ALI_CDN_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ip = $_SERVER['HTTP_ALI_CDN_REAL_IP'];
    } elseif ($type<=1 && isset($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    return $ip;
}

function get_ip_city($ip){
    $new = new \app\lib\IpLocation();
    $arr = $new->getlocation($ip);
    if($arr){
        return $arr['province'].$arr['city'];
    }else{
        return false;
    }
}

function get_plugin_url($alias){
    if(substr($alias,0,1) == '/' || substr($alias,0,7) == 'http://' || substr($alias,0,8) == 'https://'){
        $url = $alias;
    }else{
        $url = '/'.$alias;
    }
    return $url;
}

//极验3.0服务端验证
function verify_captcha(){
    if(session('gtserver') === null)return '验证加载失败';
    $GtSdk = new \app\lib\GeetestLib(config_get('captcha_id'), config_get('captcha_key'));
    $data = array(
        'user_id' => request()->islogin?request()->user['id']:'public',
        'client_type' => "web",
        'ip_address' => real_ip()
    );
    if (session('gtserver') == 1) {   //服务器正常
        if ($GtSdk->success_validate(input('post.geetest_challenge'), input('post.geetest_validate'), input('post.geetest_seccode'), $data)) {
            return true;
        }
    }else{  //服务器宕机,走failback模式
        if ($GtSdk->fail_validate(input('post.geetest_challenge'), input('post.geetest_validate'), input('post.geetest_seccode'))) {
            return true;
        }
    }
    return '验证失败，请重新验证';
}

//极验4.0服务端验证（无感）
function verify_captcha4(){
    if(!input('?post.captcha_id') || !input('?post.lot_number') || !input('?post.pass_token') || !input('?post.gen_time') || !input('?post.captcha_output')) return false;
    $real_ip = real_ip();
    $url = 'http://gt4.geetest.com/demov4/demo/login';
    $param = ['captcha_id'=>input('post.captcha_id'), 'lot_number'=>input('post.lot_number'), 'pass_token'=>input('post.pass_token'), 'gen_time'=>input('post.gen_time'), 'captcha_output'=>input('post.captcha_output')];
    $referer = 'http://gt4.geetest.com/demov4/invisible-bind-zh.html';
    $httpheader[] = "X-Real-IP: ".$real_ip;
	$httpheader[] = "X-Forwarded-For: ".$real_ip;
    $data = get_curl($url.'?'.http_build_query($param),0,$referer,0,0,0,0,$httpheader);
    $arr = json_decode($data, true);
    if(isset($arr['result']) && $arr['result'] == 'success'){
        return true;
    }
    return false;
}

//极验4.0服务端验证（滑动）
function verify_captcha4_slide(){
    return verify_captcha4();
    if(!input('?post.captcha_id') || !input('?post.lot_number') || !input('?post.pass_token') || !input('?post.gen_time') || !input('?post.captcha_output')) return false;
    $url = 'http://gcaptcha4.geetest.com/validate?captcha_id='.input('post.captcha_id');
    $param = ['lot_number'=>input('post.lot_number'), 'pass_token'=>input('post.pass_token'), 'gen_time'=>input('post.gen_time'), 'captcha_output'=>input('post.captcha_output')];
    $param['sign_token'] = hash_hmac('sha256', $param['lot_number'], config_get('captcha_key'));
    $data = get_curl($url, http_build_query($param));
    $arr = json_decode($data, true);
    if(isset($arr['status']) && $arr['status']=='success'){
        if(isset($arr['result']) && $arr['result'] == 'success'){
            return true;
        }else{
            return '验证失败，'.$arr['reason'];
        }
    }else{
        return '验证失败，'.($arr['msg']?$arr['msg']:'请重新验证');
    }
}

function checkdomain($domain){
	if(empty($domain))return false;
	if (!preg_match('/^[a-zA-Z0-9:\_\.\-]{2,512}$/i', $domain) || strpos($domain, '.') === false || substr($domain, -1) == '.' || substr($domain, 0 ,1) == '.' || strpos($domain, '*') !== false) {
		return false;
	}
	return true;
}

/**
 * 取中间文本
 * @param string $str
 * @param string $leftStr
 * @param string $rightStr
 */
function getSubstr($str, $leftStr, $rightStr)
{
	$left = strpos($str, $leftStr);
	$start = $left+strlen($leftStr);
	$right = strpos($str, $rightStr, $start);
	if($left < 0) return '';
	if($right>0){
		return substr($str, $start, $right-$start);
	}else{
		return substr($str, $start);
	}
}

function plugin_userlevel($level){
    if($level > 0){
        if(!request()->islogin) return false;
        if(request()->user['level']<$level) return false;
    }
    return true;
}

function checkRefererHost(){
    if(!request()->header('referer'))return false;
    $url_arr = parse_url(request()->header('referer'));
    $http_host = request()->header('host');
    if(strpos($http_host,':'))$http_host = substr($http_host, 0, strpos($http_host, ':'));
    return $url_arr['host'] === $http_host;
}