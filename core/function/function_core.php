<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

if (!function_exists('sys_get_temp_dir')) {
    function sys_get_temp_dir()
    {
        if (!empty($_ENV['TMP'])) {
            return realpath($_ENV['TMP']);
        }
        if (!empty($_ENV['TMPDIR'])) {
            return realpath($_ENV['TMPDIR']);
        }
        if (!empty($_ENV['TEMP'])) {
            return realpath($_ENV['TEMP']);
        }
        $tempfile = tempnam(__FILE__, '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
            return realpath(dirname($tempfile));
        }
        return null;
    }
}

function getfileinfo($icoid)
{
    if (preg_match('/^dzz:[gu]id_\d+:.+?/i', $icoid)) {
        $dir = dirname($icoid) . '/';

        if (!$pfid = C::t('resources_path')->fetch_fid_bypath($dir)) {
            return false;
        }
        $filename = basename($icoid);
        if (!$rid = DB::result_first("select rid from %t where pfid = %d and name = %s", array('resources', $pfid, $filename))) {
            return false;
        }
        return C::t('resources')->fetch_by_rid($rid);
    } elseif (preg_match('/\w{32}/i', $icoid)) {
        return C::t('resources')->fetch_by_rid($icoid);
    }
}

function dzzMD5($file, $maxchunk = 100, $chunksize_first = 256)
{
    /*
  获取文件的dzzhash值
  $file:文件地址,仅支持本地文件地址；
  $maxchunk:获取多少块数据
  $chunksize_first:每块取多少字节计算md5;
  return:第一块md5和所有块的md5;
*/
    if (!is_file($file)) return false;
    $filesize = filesize($file);
    $chunk = round($filesize / $maxchunk);
    if ($chunk < $chunksize_first) $chunk = $chunksize_first;
    if (!$fp = fopen($file)) {
        return false;
    }
    $i = 0;
    $arr = array();
    while (!feof($fp)) {
        fseek($fp, $chunk * $i, SEEK_SET);
        $arr[] = md5(fread($fp, $chunksize_first));
        $i++;
    }
    fclose($fp);
    return array($arr[0], md5(implode('', $arr)));
}

function getCode62($url)
{//获取url的code62码
    $url = crc32($url);
    $x = sprintf("%u", $url);
    $show = '';
    while ($x > 0) {
        $s = $x % 62;
        if ($s > 35) {
            $s = chr($s + 61);
        } elseif ($s > 9 && $s <= 35) {
            $s = chr($s + 55);
        }
        $show .= $s;
        $x = floor($x / 62);
    }
    return $show;
}

function hookscriptoutput()
{
}

define('DZZ_CORE_FUNCTION', true);
function getOauthRedirect($url)
{//获取链接的auth地址
    $wx = new qyWechat(array('appid' => getglobal('setting/CorpID'), 'appsecret' => getglobal('setting/CorpSecret')));
    return $wx->getOauthRedirect(getglobal('siteurl') . 'index.php?mod=system&op=wxredirect&url=' . dzzencode($url));
}
 
function fix_integer_overflow($size)
{ //处理整数溢出
    if ($size < 0) {
        $size += 2.0 * (PHP_INT_MAX + 1);
    }
    return $size;
}

function formatsize($size)
{
    $prec = 3;
    $size = round(abs($size));
    $units = array(0 => " B ", 1 => " KB", 2 => " MB", 3 => " GB", 4 => " TB");
    if ($size < 0) return '';//增加负数判断
    if ($size == 0) return str_repeat(" ", $prec) . "0$units[0]";
    $unit = min(4, floor(log($size) / log(2) / 10));
    $size = $size * pow(2, -10 * $unit);
    $digi = $prec - 1 - floor(log($size) / log(10));
    $size = round($size * pow(10, $digi)) * pow(10, -$digi);
    return $size . $units[$unit];
}

function url_implode($gets)
{
    $arr = array();
    foreach ($gets as $key => $value) {
        if ($value) {
            $arr[] = $key . '=' . urlencode($value);
        }
    }
    return implode('&', $arr);
}

function getstr($string, $length = 0, $in_slashes = 0, $out_slashes = 0, $bbcode = 0, $html = 0)
{
    global $_G;

    $string = trim($string);
    $sppos = strpos($string, chr(0) . chr(0) . chr(0));
    if ($sppos !== false) {
        $string = substr($string, 0, $sppos);
    }
    if ($in_slashes) {
        $string = dstripslashes($string);
    }
    $string = preg_replace("/\[hide=?\d*\](.*?)\[\/hide\]/is", '', $string);
    if ($html < 0) {
        $string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
    } elseif ($html == 0) {
        $string = dhtmlspecialchars($string);
    }

    if ($length) {
        $string = cutstr($string, $length);
    }

    if ($bbcode) {
        require_once DZZ_ROOT . './core/class/class_bbcode.php';
        $bb = &bbcode::instance();
        $string = $bb->bbcode2html($string, $bbcode);
    }
    if ($out_slashes) {
        $string = daddslashes($string);
    }
    return trim($string);
}

function getuserprofile($field)
{
    global $_G;
    if (isset($_G['member'][$field])) {
        return $_G['member'][$field];
    }
    static $tablefields = array(
        'status' => array('regip', 'lastip', 'lastvisit', 'lastactivity', 'lastsendmail'),
        //'profile'	=> (C::t('user_profile_setting')->fetch_all_fields_by_available()),
    );
    $profiletable = '';
    foreach ($tablefields as $table => $fields) {
        if (in_array($field, $fields)) {
            $profiletable = $table;
            break;
        }
    }
    if ($profiletable) {

        if (is_array($_G['member']) && $_G['member']['uid']) {
            space_merge($_G['member'], $profiletable);
        } else {
            foreach ($tablefields[$profiletable] as $k) {
                $_G['member'][$k] = '';
            }
        }
        return $_G['member'][$field];
    }
    return null;
}

function cpurl($type = 'parameter', $filters = array('sid', 'frames'))
{
    parse_str($_SERVER['QUERY_STRING'], $getarray);
    $extra = $and = '';
    foreach ($getarray as $key => $value) {
        if (!in_array($key, $filters)) {
            @$extra .= $and . $key . ($type == 'parameter' ? '%3D' : '=') . rawurlencode($value);
            $and = $type == 'parameter' ? '%26' : '&';
        }
    }
    return $extra;
}

function stripsearchkey($string)
{
    $string = trim($string);
    $string = str_replace('*', '%', addcslashes($string, '%_'));
    return $string;
}


function system_error($message, $show = true, $save = true, $halt = true)
{
    dzz_error::system_error($message, $show, $save, $halt);
}

function updatesession()
{
    return C::app()->session->updatesession();
}

function setglobal($key, $value, $group = null)
{
    global $_G;
    if (is_null($group) && C::setConfig($key, $value)) {
        return true;
    }
    $key = explode('/', $group === null ? $key : $group . '/' . $key);
    $p = &$_G;
    foreach ($key as $k) {
        if (!isset($p[$k]) || !is_array($p[$k])) {
            $p[$k] = array();
        }
        $p = &$p[$k];
    }
    $p = $value;
    return true;
}

function getglobal($key, $group = null)
{
    global $_G;
    if (isset($_config[$key])) {
        return $_config[$key];
    }
    $key = explode('/', $group === null ? $key : $group . '/' . $key);
    $v = &$_G;
    foreach ($key as $k) {
        if (!isset($v[$k])) {
            return null;
        }
        $v = &$v[$k];
    }
    return $v;
}

function getgpc($k, $type = 'GP')
{
    $type = strtoupper($type);
    switch ($type) {
        case 'G':
            $var = &$_GET;
            break;
        case 'P':
            $var = &$_POST;
            break;
        case 'C':
            $var = &$_COOKIE;
            break;
        default:
            if (isset($_GET[$k])) {
                $var = &$_GET;
            } else {
                $var = &$_POST;
            }
            break;
    }

    return isset($var[$k]) ? $var[$k] : NULL;

}

function getuserbyuid($uid, $fetch_archive = 0)
{
    static $users = array();
    if (empty($users[$uid])) {
        $users[$uid] = C::t('user')->fetch($uid);
    }
    if (!isset($users[$uid]['self']) && $uid == getglobal('uid') && getglobal('uid')) {

    }
    if ($users[$uid]['adminid'] == 1) $users[$uid]['self'] = 2;
    return $users[$uid];
}

function chk_submitroule($type)
{

    if (empty($_GET['formhash']) || $_GET['formhash'] != formhash()) {

        showTips(array('error' => '提交方式不合法', 'error_code' => 403), $type, 'common/illegal_operation');
    }
}

function daddslashes($string, $force = 1)
{
    if (is_array($string)) {
        $keys = array_keys($string);
        foreach ($keys as $key) {
            $val = $string[$key];
            unset($string[$key]);
            $string[addslashes($key)] = daddslashes($val, $force);
        }
    } else {
        $string = addslashes($string);
    }
    return $string;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0, $ckey_length = 4)
{
    //$ckey_length = 4;
    $key = md5($key != '' ? $key : getglobal('authkey'));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr(str_replace(array('_', '-'), array('/', '+'), $string), $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace(array('/', '+'), array('_', '-'), str_replace('=', '', base64_encode($result)));
    }
}
function urlsafe_b64encode($string) {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
}

function urlsafe_b64decode($string) {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}
//key的格式以|隔开，参数支持全局函数，如地址为 index.php?mod=io&op=getStream&path=***&key=uid|setting/authkey|username
//这种格式，加密时，需要把|分割的每个参数都带上，dzzencode($string,'1|'.getglobal('setting/authkey').'|管理员',$expiry);
//如果解密时，|隔开的部分使用getglobal函数获取不到值，将会使用原值，如index.php?mod=io&op=getStream&path=***&key=xxxxx|ppppp
//解密时的key会使用原值 xxxxx|ppppp ;
function dzzencode($string, $key = '', $expiry = 0, $ckey_length = 0)
{
    $key = md5($key != '' ? $key : getglobal('setting/authkey'));
    return urlsafe_b64encode(authcode($string, 'ENCODE', $key, $expiry, $ckey_length));
}

function dzzdecode($string, $key = '', $ckey_length = 0)
{
    if ($key) {
        $tarr = explode('|', $key);
        foreach ($tarr as $key => $v) {
            if (getglobal($v)) $tarr[$key] = getglobal($v);
        }
        $key = implode('|', $tarr);
    }
    $key = md5($key != '' ? $key : getglobal('setting/authkey'));
    if (!$ret = authcode(urlsafe_b64decode($string), 'DECODE', $key, 0, $ckey_length)) {
        $ret = authcode(urlsafe_b64decode($string), 'DECODE', $key, 0, 4);
    }
    return $ret;
}

function fsocketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15)
{
    $fp = '';
    if (function_exists('fsockopen')) {
        $fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
    } elseif (function_exists('pfsockopen')) {
        $fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
    } elseif (function_exists('stream_socket_client')) {
        $fp = @stream_socket_client($hostname . ':' . $port, $errno, $errstr, $timeout);
    }
    return $fp;
}

function dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE, $position = 0, $files = array()) {
	require_once libfile('function/filesock');
	return _dfsockopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block, $encodetype, $allowcurl, $position, $files);
}

function dhtmlspecialchars($string, $flags = null)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val, $flags);
        }
    } else {
        if ($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
            if (strpos($string, '&amp;#') !== false) {
                $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (strtolower(CHARSET) == 'utf-8') {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }
    return $string;
}

function dexit($message = '')
{
    echo $message;
    output();
    exit();
}

function dheader($string, $replace = true, $http_response_code = 0)
{
    $islocation = substr(strtolower(trim($string)), 0, 8) == 'location';
    if (defined('IN_MOBILE') && strpos($string, 'mobile') === false && $islocation) {
        if (strpos($string, '?') === false) {
            $string = $string . '?mobile=' . IN_MOBILE;
        } else {
            if (strpos($string, '#') === false) {
                $string = $string . '&mobile=' . IN_MOBILE;
            } else {
                $str_arr = explode('#', $string);
                $str_arr[0] = $str_arr[0] . '&mobile=' . IN_MOBILE;
                $string = implode('#', $str_arr);
            }
        }
    }
    $string = str_replace(array("\r", "\n"), array('', ''), $string);
    if (empty($http_response_code) || PHP_VERSION < '4.3') {
        @header($string, $replace);
    } else {
        @header($string, $replace, $http_response_code);
    }
    if ($islocation) {
        exit();
    }
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false)
{

    global $_G;

    $config = $_G['config']['cookie'];

    $_G['cookie'][$var] = $value;
    $var = ($prefix ? $config['cookiepre'] : '') . $var;
    $_COOKIE[$var] = $value;

    if ($value == '' || $life < 0) {
        $value = '';
        $life = -1;
    }

    if (defined('IN_MOBILE')) {
        $httponly = false;
    }

    $life = $life > 0 ? getglobal('timestamp') + $life : ($life < 0 ? getglobal('timestamp') - 31536000 : 0);
    $path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'] . '; HttpOnly' : $config['cookiepath'];

    $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
    if (PHP_VERSION < '5.2.0') {
        setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
    } else {
        setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
    }
}

function getcookie($key)
{
    global $_G;
    return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

function fileext($filename)
{
    return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}

function formhash($specialadd = '')
{
    global $_G;
    $hashadd = defined('IN_ADMINCP') ? 'Only For Dzz! Admin Control Panel' : '';
    return substr(md5(substr($_G['timestamp'], 0, -7) . $_G['username'] . $_G['uid'] . $_G['authkey'] . $hashadd . $specialadd), 8, 8);
}

function checkrobot($useragent = '')
{
    static $kw_spiders = array('bot', 'crawl', 'spider', 'slurp', 'sohu-search', 'lycos', 'robozilla');
    static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

    $useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
    if (strpos($useragent, 'http://') === false && dstrpos($useragent, $kw_browsers)) return false;
    if (dstrpos($useragent, $kw_spiders)) return true;
    return false;
}

function checkmobile()
{
    global $_G;
    $mobile = array();
    static $mobilebrowser_list = array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
        'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod', 'nokia', 'samsung',
        'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma', 'docomo', 'up.browser',
        'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad', 'webos', 'techfaith', 'palmsource',
        'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom', 'bunjalloo', 'maui', 'smartphone',
        'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech', 'gionee', 'portalmmm', 'jig browser', 'hiptop',
        'benq', 'haier', '^lct', '320x320', '240x320', '176x220');
    static $wmlbrowser_list = array('cect', 'compal', 'ctl', 'lg', 'nec', 'tcl', 'alcatel', 'ericsson', 'bird', 'daxian', 'dbtel', 'eastcom',
        'pantech', 'dopod', 'philips', 'haier', 'konka', 'kejian', 'lenovo', 'benq', 'mot', 'soutec', 'nokia', 'sagem', 'sgh',
        'sed', 'capitel', 'panasonic', 'sonyericsson', 'sharp', 'amoi', 'panda', 'zte');

    $pad_list = array('pad', 'gt-p1000');

    $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);

    if (dstrpos($useragent, $pad_list)) {
        return false;
    }
    if (($v = dstrpos($useragent, $mobilebrowser_list, true))) {
        $_G['mobile'] = $v;
        return '2';
    }
    if (($v = dstrpos($useragent, $wmlbrowser_list))) {
        $_G['mobile'] = $v;
        return '3'; //wml版
    }
    $brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
    if (dstrpos($useragent, $brower)) return false;

    $_G['mobile'] = 'unknown';
    if (isset($_G['mobiletpl'][$_GET['mobile']])) {
        return true;
    } else {
        return false;
    }
}

function dstrpos($string, $arr, $returnvalue = false)
{
    if (empty($string)) return false;
    foreach ((array)$arr as $v) {
        if (strpos($string, $v) !== false) {
            $return = $returnvalue ? $v : true;
            return $return;
        }
    }
    return false;
}

function isemail($email)
{
    return strlen($email) > 6 && strlen($email) <= 32 && preg_match("/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/", $email);
}

function quescrypt($questionid, $answer)
{
    return $questionid > 0 && $answer != '' ? substr(md5($answer . md5($questionid)), 16, 8) : '';
}

function random($length, $numeric = 0)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
    if ($numeric) {
        $hash = '';
    } else {
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
    }
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed{mt_rand(0, $max)};
    }
    return $hash;
}

function strexists($string, $find)
{
    return !(strpos($string, $find) === FALSE);
}

function avatar($uid, $size = 'middle', $returnsrc = FALSE, $real = FALSE, $static = FALSE, $ucenterurl = '')
{
    global $_G;

    static $staticavatar;
    if ($staticavatar === null) {
        $staticavatar = $_G['setting']['avatarmethod'];
    }

    $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
    $uid = abs(intval($uid));
    if (!$staticavatar && !$static) {
        return $returnsrc ? 'avatar.php?uid=' . $uid . '&size=' . $size : '<img src="avatar.php?uid=' . $uid . '&size=' . $size . ($real ? '&type=real' : '') . '" />';
    } else {
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        $file = 'data/avatar/' . $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . ($real ? '_real' : '') . '_avatar_' . $size . '.jpg';
        return $returnsrc ? $file : '<img src="' . $file . '" onerror="this.onerror=null;this.src=\'data/avatar/noavatar_' . $size . '.gif\'" />';
    }
}

/*获取用户头像模板，如果没有会生成背景+首字母的头像
 * param:$uid    		需要生成的用户UID;
 * param:$headercolors  传递的用户头像信息数组格式为array('1'=>'#e9308d','2'=>'#e74856'),键为UID，值为颜色值
 */
function avatar_block($uid=0,$headercolors=array(),$class="Topcarousel"){
	static $colors=array('#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255');
   
	if(!$uid){
		$uid=getglobal('uid');
	}
	if($uid){
		$user=getuserbyuid($uid);
	}else{
		$user=array('uid' => 0, 'username' => 'guest', 'avatarstatus' => 0 ,'adminid' => 0, 'groupid' => 7, 'credits' => 0, 'timeoffset' => 9999);
	}
	if(empty($user)) return '';
	if($user['avatarstatus']){//用户已经上传头像
		return '<img src="avatar.php?uid='.$user['uid'].'" class="img-circle special_avatar_class" title="'.$user['username'].'">';
	}else{//没有上传头像，使用背景+首字母
		if($uid){
			if($headercolors[$uid]) $headerColor=$headercolors[$uid];
			else $headerColor = C::t('user_setting')->fetch_by_skey('headerColor',$user['uid']);
			if(empty($headerColor)){//没有设置时，创建头像背景色，并且入库
				$colorkey = rand(1,15);
    			$headerColor = $colors[$colorkey];
				C::t('user_setting')->insert_by_skey('headerColor',$headerColor,$user['uid']);
			}
		}else{//游客默认使用第一个值；
			$headerColor = $colors[0];
		}
		return '<span class="'.$class.'" style="background:'.$headerColor.'" title="'.$user['username'].'">'. new_strsubstr(ucfirst($user['username']),1,'').'</span>';
	}
}
/*获取群组机构头像模板，如果没有会生成背景+首字母的头像
 * param:$gid    		需要生成的群组机构的gid;
 * param:$groupcolors  传递的群组机构(organization表的记录；array('1'=>array('aid'=>'#e9308d','orgname'=>'机构群组名称'),键为gid，值为organization表的记录(最少包含aid和orgname字段)；
 */
function avatar_group($gid,$groupcolors=array(),$class='iconFirstWord'){
    static $colors=array('#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255');
    $gid = intval($gid);
	if($groupcolors[$gid]){
		if($groupcolor = $groupcolors[$gid]['aid']){
			if(preg_match('/^\#.+/',$groupcolor)){
				return '<span class="iconFirstWord" style="background:'.$groupcolor.';" title="'.$groupcolors[$gid]['orgname'].'">'.strtoupper(new_strsubstr($groupcolors[$gid]['orgname'],1,'')).'</span>';
			}elseif(preg_match('/^\d+$/',$groupcolor) && $groupcolors > 0){
				return '<img src="index.php?mod=io&op=thumbnail&width=24&height=24&path='. dzzencode('attach::' . $groupcolor).'" class="img-circle" title="'.$groupcolors[$gid]['orgname'].'">';
			}
		}else{
			$colorkey = rand(1,15);
			$groupcolor = $colors[$colorkey];
			C::t('organization')->update($gid,array('aid'=>$groupcolor));
			return '<span class="iconFirstWord" style="background:'.$groupcolor.';"  title="'.$groupcolors[$gid]['orgname'].'">'.strtoupper(new_strsubstr($groupcolors[$gid]['orgname'],1,'')).'</span>';
		} 
	}else{
		 if(!$groupinfo = C::t('organization')->fetch($gid)){
			return '<span class="dzz dzz-group"></span>';
		}
		if($groupinfo['aid']){
			if(preg_match('/^\#.+/',$groupinfo['aid'])){
				return '<span class="iconFirstWord" style="background:'.$groupinfo['aid'].';" title="'.$groupinfo['orgname'].'">'.strtoupper(new_strsubstr($groupinfo['orgname'],1,'')).'</span>';
			}elseif(preg_match('/^\d+$/',$groupinfo['aid']) && $groupinfo['aid'] > 0){
				return '<img src="index.php?mod=io&op=thumbnail&width=24&height=24&path='. dzzencode('attach::' . $groupinfo['aid']).'" class="img-circle" title="'.$groupinfo['orgname'].'">';
			}
		}else{

			$colorkey = rand(1,15);
			$groupcolor = $colors[$colorkey];
			C::t('organization')->update($gid,array('aid'=>$groupcolor));
			return '<span class="iconFirstWord" style="background:'.$groupcolor.';" title="'.$groupinfo['orgname'].'">'.strtoupper(new_strsubstr($groupinfo['orgname'],1,'')).'</span>';
		} 
	}
}
function getResourceByLang($flag){
	$langset=getglobal('language');
	if(empty($langset)) return '';
	switch($flag){
		case 'select2':
			$t="static/select2/select2_locale_{lang}.js";
			$src=str_replace('{lang}',$langset,$t);
			if(file_exists($src)){
				return $src;
			}else{
				return '';
			}
			break;
		case 'datepicker':
			$t="static/datepicker/i18n/datepicker-{lang}.js";
			$src=str_replace('{lang}',$langset,$t);
			if(file_exists($src)){
				return $src;
			}else{
				return '';
			}
			break;
		case 'timepicker':
			$t="static/datepicker/timepicker/i18n/jquery-ui-timepicker-{lang}.js";
			$src=str_replace('{lang}',$langset,$t);
			if(file_exists($src)){
				return $src;
			}else{
				return '';
			}
			break;
		case 'ueditor':
			$t="dzz/system/ueditor/lang/{lang}/{lang}.js";
			$src=str_replace('{lang}',strtolower($langset),$t);
			if(file_exists($src)){
				return $src;
			}else{
				return '';
			}
			break;
				
	}
}
function checkLanguage()
{
    global $_G;
    $uid = getglobal('uid');
    $langList = $_G['config']['output']['language_list'];
    $langSet = '';

    /*if($_G['cookie']['language']) $langSet=$_G['cookie']['language'];
	else*/
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {// 自动侦测浏览器语言
        preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
        $langSet = strtolower($matches[1]);
        if ($langSet == 'zh-hans-cn' || $langSet == 'zh-cn') {
            $langSet = 'zh-cn';
        } elseif ($langSet == 'zh-tw') {
            $langSet = 'zh-tw';
        } else {
            $langSet = $matches[1];
        }
    }
    if (!in_array($langSet, array_keys($langList))) { // 非法语言参数
        $langSet = $_G['config']['output']['language'];
    }
    return $langSet;
}

function lang($langvar = null, $vars = array(), $default = null, $curpath = '')
{

    global $_G;
    $checkLanguage = $_G['language'];
    if ($curpath) {
        include DZZ_ROOT . './' . $curpath . '/language/' . $checkLanguage . '/' . 'lang.php';
        $_G['lang']['template'] = $lang;
    } else {
        if (defined('CURSCRIPT')) {
            $key1 = CURSCRIPT . '_template';
        }
        if (defined('CURSCRIPT') && defined('CURMODULE')) {
            $key2 = CURSCRIPT . '_' . CURMODULE . '_template';
        }

        if (!isset($_G['lang']['template'])) {
            $_G['lang']['template'] = array();

            if (file_exists(DZZ_ROOT . './core/language/' . $checkLanguage . '/' . 'lang.php')) {
                include DZZ_ROOT . './core/language/' . $checkLanguage . '/' . 'lang.php';
                $_G['lang']['template'] = $lang;
            }
        }

        if (isset($key1) && !isset($_G['lang'][$key1])) {
            if (file_exists(DZZ_ROOT . './' . CURSCRIPT . '/language/' . $checkLanguage . '/' . 'lang.php')) {
                include DZZ_ROOT . './' . CURSCRIPT . '/language/' . $checkLanguage . '/' . 'lang.php';
                $_G['lang']['template'] = array_merge($_G['lang']['template'], $lang);

            }
        }

        if (isset($key2) && !isset($_G['lang'][$key2])) {
            if (file_exists(DZZ_ROOT . './' . CURSCRIPT . '/' . CURMODULE . '/language/' . $checkLanguage . '/' . 'lang.php')) {

                include DZZ_ROOT . './' . CURSCRIPT . '/' . CURMODULE . '/language/' . $checkLanguage . '/' . 'lang.php';
                $_G['lang']['template'] = array_merge($_G['lang']['template'], $lang);
            }
        }

    }
    $returnvalue = &$_G['lang'];

    $return = $langvar !== null ? (isset($returnvalue['template'][$langvar]) ? $returnvalue['template'][$langvar] : null) : $returnvalue['template'];
    $return = $return === null ? ($default !== null ? $default : $langvar) : $return;
    $searchs = $replaces = array();

    if ($vars && is_array($vars)) {

        foreach ($vars as $k => $v) {
            $searchs[] = '{' . $k . '}';
            $replaces[] = $v;
        }
    }

    if (is_string($return) && strpos($return, '{_G/') !== false) {
        preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
        foreach ($gvar[0] as $k => $v) {

            $searchs[] = $v;
            $replaces[] = getglobal($gvar[1][$k]);
        }
    }

    $return = str_replace($searchs, $replaces, $return);
    return $return;
}

/*
 * 模板函数
 * $file=>模板,$tpldir=>模板文件夹，$templateNotMust=>模板不存在时返回空字符串，屏蔽错误提示，默认不开启
 * */
function template($file, $tpldir = '', $templateNotMust = false)
{
    global $_G;
    static $tplrefresh, $timestamp, $targettplname;

    $file .= !empty($_G['inajax']) && ($file == 'common/header' || $file == 'common/footer') ? '_ajax' : '';

    $tplfile = $file;

    if ($tplrefresh === null) {
        $tplrefresh = getglobal('config/output/tplrefresh');
        $timestamp = getglobal('timestamp');
    }

    if (empty($timecompare) || $tplrefresh == 1 || ($tplrefresh > 1 && !($timestamp % $tplrefresh))) {
        require_once DZZ_ROOT . '/core/class/class_template.php';
        $template = new template();
        $cachefile = $template->fetch_template($tplfile, $tpldir, $templateNotMust);
        return $cachefile;
    }
    return FALSE;

}

function dsign($str, $length = 16)
{
    return substr(md5($str . getglobal('security/authkey')), 0, ($length ? max(8, $length) : 16));
}

function modauthkey($id)
{
    return md5(getglobal('username') . getglobal('uid') . getglobal('authkey') . substr(TIMESTAMP, 0, -7) . $id);
}


function loadcache($cachenames, $force = false)
{
    global $_G;
    static $loadedcache = array();
    $cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
    $caches = array();
    foreach ($cachenames as $k) {
        if (!isset($loadedcache[$k]) || $force) {
            $caches[] = $k;
            $loadedcache[$k] = true;
        }
    }

    if (!empty($caches)) {

        $cachedata = C::t('syscache')->fetch_all($caches);
        foreach ($cachedata as $cname => $data) {
            if ($cname == 'setting') {
                $_G['setting'] = $data;
            } elseif ($cname == 'usergroup_' . $_G['groupid']) {
                $_G['cache'][$cname] = $_G['group'] = $data;
            } else {
                $_G['cache'][$cname] = $data;
            }
        }
    }
    return true;
}

function getpath($path)
{
    $path = trim($path);
    $path = substr(strrchr($path, ':'), 1);
    $path = array_filter(explode('/', $path));
    return $path;
}

function dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '')
{
    global $_G;
    $format == 'u' && !$_G['setting']['dateconvert'] && $format = 'dt';
    static $dformat, $tformat, $dtformat, $offset, $lang;
    if ($dformat === null) {
        $dformat = getglobal('setting/dateformat');
        $tformat = getglobal('setting/timeformat');
        $dtformat = $dformat . ' ' . $tformat;
        $offset = getglobal('member/timeoffset');
        $lang = lang('date');
    }
    $timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
    $timestamp += $timeoffset * 3600;
    $format = empty($format) || $format == 'dt' ? $dtformat : ($format == 'd' ? $dformat : ($format == 't' ? $tformat : $format));
    if ($format == 'u') {
        $todaytimestamp = TIMESTAMP - (TIMESTAMP + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
        $s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
        $time = TIMESTAMP + $timeoffset * 3600 - $timestamp;
        if ($timestamp >= $todaytimestamp) {
            if ($time > 3600) {
                $return = intval($time / 3600) . '&nbsp;' . $lang['hour'] . $lang['before'];
            } elseif ($time > 1800) {
                $return = $lang['half'] . $lang['hour'] . $lang['before'];
            } elseif ($time > 60) {
                $return = intval($time / 60) . '&nbsp;' . $lang['min'] . $lang['before'];
            } elseif ($time > 0) {
                $return = $time . '&nbsp;' . $lang['sec'] . $lang['before'];
            } elseif ($time == 0) {
                $return = $lang['now'];
            } else {
                $return = $s;
            }
            if ($time >= 0 && !defined('IN_MOBILE')) {
                $return = '<span  title="' . $s . '">' . $return . '</span>';
            }
        } elseif (($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
            if ($days == 0) {
                $return = $lang['yday'] . '&nbsp;' . gmdate($tformat, $timestamp);
            } elseif ($days == 1) {
                $return = $lang['byday'] . '&nbsp;' . gmdate($tformat, $timestamp);
            } else {
                $return = ($days + 1) . '&nbsp;' . $lang['day'] . $lang['before'];
            }
            if (!defined('IN_MOBILE')) {
                $return = '<span  title="' . $s . '">' . $return . '</span>';
            }
        } else {
            $return = gmdate('Y-m-d', $timestamp) . '&nbsp;<span class="hidden-xs" title="' . $s . '">' . gmdate('H:s', $timestamp) . '</span>';
        }
        return $return;
    } else {
        return gmdate($format, $timestamp);
    }
}

function dmktime($date)
{
    if (strpos($date, '-')) {
        $time = explode('-', $date);
        return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
    }
    return 0;
}

function dnumber($number)
{
    return abs($number) > 10000 ? '<span title="' . $number . '">' . intval($number / 10000) . lang('10k') . '</span>' : $number;
}

function savecache($cachename, $data)
{
    C::t('syscache')->insert($cachename, $data);
}

function save_syscache($cachename, $data)
{
    savecache($cachename, $data);
}


function dimplode($array)
{

    if (!empty($array)) {

        $array = array_map('addslashes', $array);

        return "'" . implode("','", is_array($array) ? $array : array($array)) . "'";
    } else {
        return 0;
    }
}

function libfile($libname, $folder = '', $curpath = '')
{ //$path 标志是那个模块内的,不指定则调用默认当前模块和核心模块的
    $libpath = '';
    if (strstr($libname, '/')) {
        list($pre, $name) = explode('/', $libname);
        $path = "{$pre}/{$pre}_{$name}";
    } else {
        $path = "{$libname}";
    }
    if ($curpath) {
        $libpath = DZZ_ROOT . '/' . $curpath . '/' . $path . '.php';
    } else {
        if ($folder) {
            $libpath0 = DZZ_ROOT . './core/' . $folder;
            if (defined('CURSCRIPT')) {
                $libpath1 = DZZ_ROOT . './' . CURSCRIPT . '/' . $folder;
                if (defined('CURMODULE')) $libpath2 = DZZ_ROOT . '/' . CURSCRIPT . '/' . CURMODULE . '/' . $folder;
            }
        } else {
            $libpath0 = DZZ_ROOT . './core';
            if (defined('CURSCRIPT')) {
                $libpath1 = DZZ_ROOT . './' . CURSCRIPT;
                if (defined('CURMODULE')) $libpath2 = DZZ_ROOT . '/' . CURSCRIPT . '/' . CURMODULE;
            }
        }
        if (isset($libpath0) && file_exists($libpath0 . '/' . $path . '.php')) {
            $libpath = $libpath0 . '/' . $path . '.php';
        } elseif (isset($libpath2) && file_exists($libpath2 . '/' . $path . '.php')) {
            $libpath = $libpath2 . '/' . $path . '.php';
        } elseif ((isset($libpath1) && file_exists($libpath1 . '/' . $path . '.php'))) {
            $libpath = $libpath1 . '/' . $path . '.php';
        }
    }
    /*if(empty($libpath)){
		exit('dfdfd');
		return false;
	}*/

    return $libpath;
}

function dstrlen($str)
{
    if (strtolower(CHARSET) != 'utf-8') {
        return strlen($str);
    }
    $count = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $value = ord($str[$i]);
        if ($value > 127) {
            $count++;
            if ($value >= 192 && $value <= 223) $i++;
            elseif ($value >= 224 && $value <= 239) $i = $i + 2;
            elseif ($value >= 240 && $value <= 247) $i = $i + 3;
        }
        $count++;
    }
    return $count;
}

function showTips($message = '', $type = 'json', $template = 'common/showtips')
{
    core\dzz\Datareturn::data_return($type, $message, $template);
}

function cutstr($string, $length, $dot = ' ...')
{
    if (strlen($string) <= $length) {
        return $string;
    }

    $pre = chr(1);
    $end = chr(1);
    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), $string);

    $strcut = '';
    if (strtolower(CHARSET) == 'utf-8') {

        $n = $tn = $noc = 0;
        while ($n < strlen($string)) {

            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t <= 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }

            if ($noc >= $length) {
                break;
            }

        }
        if ($noc > $length) {
            $n -= $tn;
        }

        $strcut = substr($string, 0, $n);

    } else {
        $_length = $length - 1;
        for ($i = 0; $i < $length; $i++) {
            if (ord($string[$i]) <= 127) {
                $strcut .= $string[$i];
            } else if ($i < $_length) {
                $strcut .= $string[$i] . $string[++$i];
            }
        }
    }

    $strcut = str_replace(array($pre . '&' . $end, $pre . '"' . $end, $pre . '<' . $end, $pre . '>' . $end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

    $pos = strrpos($strcut, chr(1));
    if ($pos !== false) {
        $strcut = substr($strcut, 0, $pos);
    }
    return $strcut . $dot;
}

function dstripslashes($string)
{
    if (empty($string)) return $string;
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = dstripslashes($val);
        }
    } else {
        $string = stripslashes($string);
    }
    return $string;
}

function aidencode($aid, $type = 0, $tid = 0)
{
    global $_G;
    $s = !$type ? $aid . '|' . substr(md5($aid . md5($_G['config']['security']['authkey']) . TIMESTAMP . $_G['uid']), 0, 8) . '|' . TIMESTAMP . '|' . $_G['uid'] . '|' . $tid : $aid . '|' . md5($aid . md5($_G['config']['security']['authkey']) . TIMESTAMP) . '|' . TIMESTAMP;
    return rawurlencode(base64_encode($s));
}


function output()
{
    global $_G;
    if (defined('DZZ_OUTPUTED')) {
        return;
    } else {
        define('DZZ_OUTPUTED', 1);
    }
    if ($_G['config']['rewritestatus']) {
        $content = ob_get_contents();
        $content = output_replace($content);
        ob_end_clean();
        $_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
        echo $content;
    }
    if (defined('DZZ_DEBUG') && DZZ_DEBUG && @include(libfile('function/debug'))) {
        function_exists('debugmessage') && debugmessage();
    }
}


function outputurl( $url="" )
{
    global $_G;
    if ($_G['config']['rewritestatus']) {
        $url = output_replace($url);
    }
    return $url;
}

function output_replace($content)
{
    global $_G;
    if (defined('IN_ADMINCP')) return $content;
    if (!empty($_G['setting']['output']['str']['search'])) {
        
        $content = str_replace($_G['setting']['rewrite']['str']['search'], $_G['setting']['rewrite']['str']['replace'], $content);
    }
    if (!empty($_G['config']['rewrite']['preg']['search'])) {
        
        //处理js中 app_url,mod_url
        $string1 = "APP_URL='".MOD_URL."'";//",APP_URL='".MOD_URL."',MOD_URL = '".MOD_URL."'";
        $string2 = "MOD_URL='".MOD_URL."'";
        $string=array($string1,$string2);
        $md5[]=md5($string1);
        $md5[]=md5($string2); 
        //end
        
        //处理非本地连接
        $reg = "/(https?|ftp|news):[\/]{2}([\w+\d+]+[.]{1})?[\w+\d]+[.]{1}[\w+\d]*+([^(\s|\"|\')]+)/"; 
        preg_match_all($reg,$content,$links);
        if( isset($links[0]) && $links[0]){
            $siteurl =  $_G["siteurl"];
            //echo $siteurl."******";
            foreach($links[0] as $k=>$v){
                //echo $v."------------";
                if( strpos($v,$siteurl)!==false){
                    //echo $v."----------<br/>";
                }else{
                     $string[]=$v;
                     $md5[]=md5($v);
                }
            }
        }
		//end

        $content=str_replace($string,$md5,$content);
        
        $search_arr =  $_G['config']['rewrite']['preg']['search'];
        $replace_arr = $_G['config']['rewrite']['preg']['replace'];
        $search_new=array();
        $replace_new=array();
        foreach($search_arr as $k=>$v ){
            $s=$v; 
            $v2 = substr_replace($v, '\&amp;/i',-2,2);
            array_push($search_new,$v2); 
            $v = substr_replace($v, '\&/i',-2,2);
            array_push($search_new,$v);
            array_push($search_new,$s);
            array_push($replace_new,$replace_arr[$k]."?");
            array_push($replace_new,$replace_arr[$k]."?");
            array_push($replace_new,$replace_arr[$k]);  
        }
        $content = preg_replace($search_new, $replace_new, $content);
        
        $content=str_replace($md5,$string,$content); 
    }

    return $content;
}

function output_ajax()
{
    global $_G;

    $s = ob_get_contents();
    ob_end_clean();
    $s = preg_replace("/([\\x01-\\x08\\x0b-\\x0c\\x0e-\\x1f])+/", ' ', $s);
    $s = str_replace(array(chr(0), ']]>'), array(' ', ']]&gt;'), $s);
    if (defined('DZZ_DEBUG') && DZZ_DEBUG && @include(libfile('function/debug'))) {
        function_exists('debugmessage') && $s .= debugmessage(1);
    }

    $havedomain = isset($_G['setting']['domain']['app']) ? implode('', $_G['setting']['domain']['app']) : '';
    if ((isset($_G['setting']['rewritestatus']) && $_G['setting']['rewritestatus']) || !empty($havedomain)) {
        $s = output_replace($s);
    }
    return $s;
}


function debug($var = null, $vardump = false)
{
    echo '<pre>';
    $vardump = empty($var) ? true : $vardump;
    if ($vardump) {
        var_dump($var);
    } else {
        print_r($var);
    }
    exit();
}

function debuginfo()
{
    global $_G;
    if (getglobal('config/debug')) {
        $db = &DB::object();
        $_G['debuginfo'] = array(
            'time' => number_format((microtime(true) - $_G['starttime']), 6),
            'queries' => $db->querynum,
            'memory' => ucwords(C::memory()->type)
        );
        if ($db->slaveid) {
            $_G['debuginfo']['queries'] = 'Total ' . $db->querynum . ', Slave ' . $db->slavequery;
        }
        return TRUE;
    } else {
        return FALSE;
    }
}

function check_seccode($value, $idhash)
{
    return helper_form::check_seccode($value, $idhash);
}

function check_secqaa($value, $idhash)
{
    return helper_form::check_secqaa($value, $idhash);
}

function showmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0)
{
    require_once libfile('function/message');
    return dshowmessage($message, $url_forward, $values, $extraparam, $custom);
}

function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0)
{
    if (!getgpc($var)) {
        return FALSE;
    } else {
        return helper_form::submitcheck($var, $allowget, $seccodecheck, $secqaacheck);
    }
}

function multi($num, $perpage, $curpage, $mpurl, $classname = '', $maxpages = 0, $page = 5, $autogoto = FALSE, $simple = FALSE, $jsfunc = FALSE)
{
    return $num > $perpage ? helper_page::multi($num, $perpage, $curpage, $mpurl, $classname, $maxpages, $page, $autogoto, $simple, $jsfunc) : '';
}

function simplepage($num, $perpage, $curpage, $mpurl)
{
    return helper_page::simplepage($num, $perpage, $curpage, $mpurl);
}

function censor($message)
{
    $censor = dzz_censor::instance();
    return $censor->replace($message);
}

function space_merge(&$values, $tablename, $isarchive = false)
{
    global $_G;

    $uid = empty($values['uid']) ? $_G['uid'] : $values['uid'];
    $var = "user_{$uid}_{$tablename}";
    if ($uid) {
        $ext = '';//$isarchive ? '_archive' :'' ;
        if (!isset($_G[$var])) {
            if (($_G[$var] = C::t('user_' . $tablename . $ext)->fetch($uid)) !== false) {
                //C::t('user_'.$tablename.$ext)->insert(array('uid'=>$uid));

                if ($tablename == 'field') {
                    $_G['setting']['privacy'] = empty($_G['setting']['privacy']) ? array() : (is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : dunserialize($_G['setting']['privacy']));
                    $_G[$var]['privacy'] = empty($_G[$var]['privacy']) ? array() : is_array($_G[$var]['privacy']) ? $_G[$var]['privacy'] : dunserialize($_G[$var]['privacy']);
                } elseif ($tablename == 'profile') {
                    if ($_G[$var]['department']) {
                        $_G[$var]['department_tree'] = C::t('organization')->getPathByOrgid(intval($_G[$var]['department']));
                    } else {
                        $_G[$var]['department_tree'] = lang('please_select_a_organization_or_department');
                    }
                }
            } else {
                $_G[$var] = array();
            }
        }
        $values = array_merge($values, $_G[$var]);
    }
}

function runlog($file, $message, $halt = 0)
{
    helper_log::runlog($file, $message, $halt);
}


function dmkdir($dir, $mode = 0777, $makeindex = TRUE)
{
    if (!is_dir($dir)) {
        dmkdir(dirname($dir), $mode, $makeindex);
        @mkdir($dir, $mode);
        if (!empty($makeindex)) {
            @touch($dir . '/index.html');
            @chmod($dir . '/index.html', 0777);
        }
    }
    return true;
}

function dreferer($default = '')
{
    global $_G;

    $default = '';
    $_G['referer'] = !empty($_GET['referer']) ? $_GET['referer'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
    $_G['referer'] = substr($_G['referer'], -1) == '?' ? substr($_G['referer'], 0, -1) : $_G['referer'];

    if (strpos($_G['referer'], 'user.php?mod=login&op=logging&action=login')) {
        $_G['referer'] = $default;
    }
    $_G['referer'] = dhtmlspecialchars($_G['referer'], ENT_QUOTES);
    $_G['referer'] = str_replace('&amp;', '&', $_G['referer']);
    $reurl = parse_url($_G['referer']);

    if ($reurl['port']) $reurl['host'] .= ':' . $reurl['port'];
    if (!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.' . $reurl['host']))) {
        $_G['referer'] = 'index.php';

    } elseif (empty($reurl['host'])) {
        $_G['referer'] = $_G['siteurl'] . './' . $_G['referer'];
    }

    return strip_tags($_G['referer']);
}


function diconv($str, $in_charset, $out_charset = CHARSET, $ForceTable = FALSE)
{
    global $_G;

    $in_charset = strtoupper($in_charset);
    $out_charset = strtoupper($out_charset);

    if (empty($str) || $in_charset == $out_charset) {
        return $str;
    }

    $out = '';

    if (!$ForceTable) {
        if (function_exists('iconv')) {
            $out = iconv($in_charset, $out_charset . '//IGNORE', $str);
        } elseif (function_exists('mb_convert_encoding')) {
            $out = mb_convert_encoding($str, $out_charset, $in_charset);
        }
    }

    if ($out == '') {
        $chinese = new Chinese($in_charset, $out_charset, true);
        $out = $chinese->Convert($str);
    }

    return $out;
}


function renum($array)
{
    $newnums = $nums = array();
    foreach ($array as $id => $num) {
        $newnums[$num][] = $id;
        $nums[$num] = $num;
    }
    return array($nums, $newnums);
}

function sizecount($size)
{
    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' GB';
    } elseif ($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' MB';
    } elseif ($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' KB';
    } else {
        $size = $size . ' Bytes';
    }
    return $size;
}

function swapclass($class1, $class2 = '')
{
    static $swapc = null;
    $swapc = isset($swapc) && $swapc != $class1 ? $class1 : $class2;
    return $swapc;
}

function writelog($file, $log)
{
    helper_log::writelog($file, $log);
}

function getstatus($status, $position)
{
    $t = $status & pow(2, $position - 1) ? 1 : 0;
    return $t;
}

function setstatus($position, $value, $baseon = null)
{
    $t = pow(2, $position - 1);
    if ($value) {
        $t = $baseon | $t;
    } elseif ($baseon !== null) {
        $t = $baseon & ~$t;
    } else {
        $t = ~$t;
    }
    return $t & 0xFFFF;
}


function memory($cmd, $key = '', $value = '', $ttl = 0, $prefix = '')
{
    if ($cmd == 'check') {
        return C::memory()->enable ? C::memory()->type : '';
    } elseif (C::memory()->enable && in_array($cmd, array('set', 'get', 'rm', 'inc', 'dec'))) {
        if (defined('DZZ_DEBUG') && DZZ_DEBUG) {
            if (is_array($key)) {
                foreach ($key as $k) {
                    C::memory()->debug[$cmd][] = ($cmd == 'get' || $cmd == 'rm' ? $value : '') . $prefix . $k;
                }
            } else {
                C::memory()->debug[$cmd][] = ($cmd == 'get' || $cmd == 'rm' ? $value : '') . $prefix . $key;
            }
        }
        switch ($cmd) {
            case 'set':
                return C::memory()->set($key, $value, $ttl, $prefix);
                break;
            case 'get':
                return C::memory()->get($key, $value);
                break;
            case 'rm':
                return C::memory()->rm($key, $value);
                break;
            case 'inc':
                return C::memory()->inc($key, $value ? $value : 1);
                break;
            case 'dec':
                return C::memory()->dec($key, $value ? $value : -1);
                break;
        }
    }
    return null;
}

function ipaccess($ip, $accesslist)
{
    return preg_match("/^(" . str_replace(array("\r\n", ' '), array('|', ''), preg_quote($accesslist, '/')) . ")/", $ip);
}

function ipbanned($onlineip)
{
    global $_G;

    if ($_G['setting']['ipaccess'] && !ipaccess($onlineip, $_G['setting']['ipaccess'])) {
        return TRUE;
    }

    loadcache('ipbanned');
    if (empty($_G['cache']['ipbanned'])) {
        return FALSE;
    } else {
        if ($_G['cache']['ipbanned']['expiration'] < TIMESTAMP) {
            require_once libfile('function/cache');
            updatecache('ipbanned');
        }
        return preg_match("/^(" . $_G['cache']['ipbanned']['regexp'] . ")$/", $onlineip);
    }
}


function sysmessage($message)
{
    helper_sysmessage::show($message);
}

function getexpiration()
{
    global $_G;
    $date = getdate($_G['timestamp']);
    return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']) + 86400;
}

function return_bytes($val)
{
    $val = trim($val);
    $last = strtolower($val{strlen($val) - 1});
    switch ($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}


function getimgthumbname($fileStr, $extend = '.thumb.jpg', $holdOldExt = true)
{
    if (empty($fileStr)) {
        return '';
    }
    if (!$holdOldExt) {
        $fileStr = substr($fileStr, 0, strrpos($fileStr, '.'));
    }
    $extend = strstr($extend, '.') ? $extend : '.' . $extend;
    return $fileStr . $extend;
}


function dintval($int, $allowarray = false)
{
    $ret = intval($int);
    if ($int == $ret || !$allowarray && is_array($int)) return $ret;
    if ($allowarray && is_array($int)) {
        foreach ($int as &$v) {
            $v = dintval($v, true);
        }
        return $int;
    } elseif ($int <= 0xffffffff) {
        $l = strlen($int);
        $m = substr($int, 0, 1) == '-' ? 1 : 0;
        if (($l - $m) === strspn($int, '0987654321', $m)) {
            return $int;
        }
    }
    return $ret;
}

function strhash($string, $operation = 'DECODE', $key = '')
{
    $key = md5($key != '' ? $key : getglobal('authkey'));
    if ($operation == 'DECODE') {
        $hashcode = gzuncompress(base64_decode(($string)));
        $string = substr($hashcode, 0, -16);
        $hash = substr($hashcode, -16);
        unset($hashcode);
    }

    $vkey = substr(md5($string . substr($key, 0, 16)), 4, 8) . substr(md5($string . substr($key, 16, 16)), 18, 8);

    if ($operation == 'DECODE') {
        return $hash == $vkey ? $string : '';
    }

    return base64_encode(gzcompress($string . $vkey));
}

function dunserialize($data)
{
    if (($ret = unserialize($data)) === false) {
        $ret = unserialize(stripslashes($data));
    }
    return $ret;
}

function browserversion($type)
{
    static $return = array();
    static $types = array('ie' => 'msie', 'firefox' => '', 'chrome' => '', 'opera' => '', 'safari' => '', 'mozilla' => '', 'webkit' => '', 'maxthon' => '', 'qq' => 'qqbrowser');
    if (!$return) {
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $other = 1;
        foreach ($types as $i => $v) {
            $v = $v ? $v : $i;
            if (strpos($useragent, $v) !== false) {
                preg_match('/' . $v . '(\/|\s)([\d\.]+)/i', $useragent, $matches);
                $ver = $matches[2];
                $other = $ver !== 0 && $v != 'mozilla' ? 0 : $other;
            } else {
                $ver = 0;
            }
            $return[$i] = $ver;
        }
        $return['other'] = $other;
    }
    return $return[$type];
}

function removedirectory($dirname, $keepdir = FALSE, $time = 0)
{
    $dirname = str_replace(array("\n", "\r", '..'), array('', '', ''), $dirname);

    if (!is_dir($dirname)) {
        return FALSE;
    }
    $handle = opendir($dirname);
    while (($file = readdir($handle)) !== FALSE) {
        if ($file != '.' && $file != '..') {
            $dir = $dirname . DIRECTORY_SEPARATOR . $file;
            $mtime = filemtime($dir);
            is_dir($dir) ? removedirectory($dir) : (((TIMESTAMP - $mtime) > $time) ? unlink($dir) : '');
        }
    }
    closedir($handle);
    return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}

global $documentexts, $textexts, $unRunExts, $docexts, $imageexts, $idtype2type;
$documentexts = array('DZZDOC', 'HTM', 'HTML', 'SHTM', 'SHTML', 'HTA', 'HTC', 'XHTML', 'STM', 'SSI', 'JS', 'JSON', 'AS', 'ASC', 'ASR', 'XML', 'XSL', 'XSD', 'DTD', 'XSLT', 'RSS', 'RDF', 'LBI', 'DWT', 'ASP', 'ASA', 'ASPX', 'ASCX', 'ASMX', 'CONFIG', 'CS', 'CSS', 'CFM', 'CFML', 'CFC', 'TLD', 'TXT', 'PHP', 'PHP3', 'PHP4', 'PHP5', 'PHP-DIST', 'PHTML', 'JSP', 'WML', 'TPL', 'LASSO', 'JSF', 'VB', 'VBS', 'VTM', 'VTML', 'INC', 'SQL', 'JAVA', 'EDML', 'MASTER', 'INFO', 'INSTALL', 'THEME', 'CONFIG', 'MODULE', 'PROFILE', 'ENGINE', 'DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX', 'ODT', 'ODS', 'ODG', 'RTF', 'ET', 'DPX', 'WPS');
$textexts = array('DZZDOC', 'HTM', 'HTML', 'SHTM', 'SHTML', 'HTA', 'HTC', 'XHTML', 'STM', 'SSI', 'JS', 'JSON', 'AS', 'ASC', 'ASR', 'XML', 'XSL', 'XSD', 'DTD', 'XSLT', 'RSS', 'RDF', 'LBI', 'DWT', 'ASP', 'ASA', 'ASPX', 'ASCX', 'ASMX', 'CONFIG', 'CS', 'CSS', 'CFM', 'CFML', 'CFC', 'TLD', 'TXT', 'PHP', 'PHP3', 'PHP4', 'PHP5', 'PHP-DIST', 'PHTML', 'JSP', 'WML', 'TPL', 'LASSO', 'JSF', 'VB', 'VBS', 'VTM', 'VTML', 'INC', 'SQL', 'JAVA', 'EDML', 'MASTER', 'INFO', 'INSTALL', 'THEME', 'CONFIG', 'MODULE', 'PROFILE', 'ENGINE');
$unRunExts = array('htm', 'html', 'js', 'php', 'jsp', 'asp', 'aspx', 'xml', 'htc', 'shtml', 'shtm', 'vbs'); //需要阻止运行的后缀名；
$docexts = array('DOC', 'DOCX', 'XLS', 'XLSX', 'PPT', 'PPTX', 'ODT', 'ODS', 'ODG', 'RTF', 'ET', 'DPX', 'WPS');
//echo strtolower(implode(',',$docexts));
$imageexts = array('JPG', 'JPEG', 'GIF', 'PNG', 'BMP');
$videoexts =
$idtype2type = array(
    'picid' => 'image',
    'lid' => 'link',
    'mid' => 'music',
    'vid' => 'video',
    'did' => 'document',
    'appid' => 'app',
    'qid' => 'attach',
    'uid' => 'user'
);
function get_os()
{
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = false;

    if (eregi('win', $agent) && eregi('nt 5.1', $agent)) {
        $os = 'Windows XP';
    } else if (eregi('win', $agent) && eregi('nt 5.0', $agent)) {
        $os = 'Windows 2000';
    } else if (eregi('win', $agent) && eregi('nt 5.2', $agent)) {
        $os = 'Windows 2003';
    } else if (eregi('win', $agent) && eregi('nt 6.0', $agent)) {
        $os = 'Windows 2008';
    } else if (eregi('win', $agent) && eregi('6.0', $agent)) {
        $os = 'Windows vista';
    } else if (eregi('win', $agent) && eregi('6.1', $agent)) {
        $os = 'Windows 7';
    } else if (eregi('win', $agent) && eregi('6.2', $agent)) {
        $os = 'Windows 8';
    } else if (eregi('win', $agent) && eregi('nt', $agent)) {
        $os = 'Windows NT';
    } else if (eregi('win', $agent) && ereg('32', $agent)) {
        $os = 'Windows 32';
    } else if (eregi('linux', $agent) && ereg('Android', $agent)) {
        $os = 'Android';
    } else if (eregi('linux', $agent)) {
        $os = 'Linux';
    } else if (eregi('unix', $agent)) {
        $os = 'Unix';
    } else if (eregi('sun', $agent) && eregi('os', $agent)) {
        $os = 'SunOS';
    } else if (eregi('ibm', $agent) && eregi('os', $agent)) {
        $os = 'IBM OS/2';
    } else if (eregi('Mac', $agent) && eregi('Macintosh', $agent)) {
        $os = 'Macintosh';
    } else if (eregi('PowerPC', $agent)) {
        $os = 'PowerPC';
    } /* else if (eregi('AIX', $agent))
     {
       $os = 'AIX';
     }
     else if (eregi('HPUX', $agent))
     {
       $os = 'HPUX';
     }
     else if (eregi('NetBSD', $agent))
     {
       $os = 'NetBSD';
     }
     else if (eregi('BSD', $agent))
     {
       $os = 'BSD';
     }
     else if (ereg('OSF1', $agent))
     {
       $os = 'OSF1';
     }
     else if (ereg('IRIX', $agent))
     {
       $os = 'IRIX';
     }
     else if (eregi('FreeBSD', $agent))
     {
       $os = 'FreeBSD';
     }
     else if (eregi('teleport', $agent))
     {
       $os = 'teleport';
     }
     else if (eregi('flashget', $agent))
     {
       $os = 'flashget';
     }
     else if (eregi('webzip', $agent))
     {
       $os = 'webzip';
     }
     else if (eregi('offline', $agent))
     {
       $os = 'offline';
     }*/
    else {
        $os = 'Unknown';
    }
    return $os;
}

function array_sort($arr, $keys, $type = 'asc')
{ //二维数组排序；
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}


if (!function_exists('json_decode')) {
    function json_decode($content, $assoc = false)
    {
        require_once DZZ_ROOT . '/dzz/class/class_json.php';
        if ($assoc) {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON;
        }
        return $json->decode($content);
    }
}

if (!function_exists('json_encode')) {
    function json_encode($content)
    {
        require_once DZZ_ROOT . '/dzz/class/class_json.php';
        $json = new Services_JSON;
        return $json->encode($content);
    }
}

function arr_encode(&$array)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arr_encode($array[$key]);
        } else {
            $array[$key] = (diconv(stripslashes($value), CHARSET, 'UTF-8'));
        }
    }
}

function json_encode_gbk($array)
{
    global $_G;
    arr_encode($array);
    $json = json_encode($array);
    return ($json);
}

function getThames()
{//处理风格
    global $_G;
    $thames = DB::fetch_first("SELECT * FROM " . DB::table('user_thame') . " WHERE uid='{$_G['uid']}'");
    $return = $data = array();
    $arr = array();
    if (empty($thames['thame']) || (!$arr = DB::fetch_first("select * from " . DB::table('thame') . " where id='{$thames['thame']}'"))) {
        $arr = DB::fetch_first("select * from " . DB::table('thame') . " where 1 ORDER BY `default` DESC LIMIT 1");
    }
    if (empty($arr['folder'])) $arr['folder'] = 'colorful';
    $arr['modules'] = unserialize(stripslashes($arr['modules']));

    if (empty($arr['modules']['window'])) {
        $arr['modules']['window'] = 'colorful';
    }
    if (empty($arr['modules']['filemanage'])) {
        $arr['modules']['filemanage'] = 'window_jd';
    }
    if (empty($arr['modules']['icoblock'])) {
        $arr['modules']['icoblock'] = 'default';
    }
    if (empty($arr['modules']['menu'])) {
        $arr['modules']['menu'] = 'default';
    }
    if (empty($arr['modules']['startmenu'])) {
        $arr['modules']['startmenu'] = 'default';
    }
    if (empty($arr['modules']['taskbar'])) {
        $arr['modules']['taskbar'] = 'default';
    }
    if (!$arr['backimg']) $arr['backimg'] = 'dzz/styles/thame/' . $arr['folder'] . '/back.jpg';
    $data['system'] = $arr;
    $data['custom'] = array(
        'custom_backimg' => !empty($thames['custom_backimg']) ? $thames['custom_backimg'] : '',
        'custom_url' => !empty($thames['custom_url']) ? $thames['custom_url'] : '',
        'custom_color' => !empty($thames['custom_color']) ? $thames['custom_color'] : '',
        'custom_btype' => !empty($thames['custom_btype']) ? $thames['custom_btype'] : '',

    );

    $return['data'] = $data;
    $return['thame'] = array(
        'folder' => $arr['folder'],
        'backimg' => !empty($thames['custom_backimg']) ? $thames['custom_backimg'] : $arr['backimg'],
        'color' => !empty($arr['enable_color']) ? (!empty($thames['custom_color']) ? $thames['custom_color'] : $arr['color']) : '',
        'modules' => $arr['modules'],
    );
    return $return;
}

function getTableBytype($type)
{
    switch ($type) {
        case 'folder':
            return array('fid', 'folder');
        case 'attach':
            return array('qid', 'source_attach');
        case 'document':
            return array('did', 'source_document');
        case 'image':
            return array('picid', 'source_image');
        case 'link':
            return array('lid', 'source_link');
        case 'video':
            return array('vid', 'source_video');
        case 'music':
            return array('mid', 'source_music');
        case 'topic':
            return array('tid', 'source_topic');
        case 'app':
            return array('appid', 'app_market');
        case 'user':
            return array('uid', 'user');
    }
    return false;
}

function getsource_by_idtype($type, $oid)
{
    global $_G;
    if ($arr = getTableBytype($type)) {
        return C::t($arr[1])->fetch($oid);
    } else {
        return false;
    }
}

function topshowmessage($msg)
{
    include template('common/header_common');
    echo "<script type=\"text/javascript\">";
    echo "try{if(top._config){top.showDialog('" . $msg . "');}else{alert('" . $msg . "');}}catch(e){alert('" . $msg . "');}";
    echo "</script>";
    include template('common/footer_reload');
    exit();
}

function SpaceSize($size, $gid=0, $isupdate = 0, $uid=0)
{
    //size: 增加的话为正值，减少的话为负值；
    //gid : 大于零位群组空间，否则为$_G['uid']的空间，
    //isupdate: 为true，则实际改变空间，否则只是检查是否有空间
    //$uid:留空为当前用户
    global $_G, $space;
    if (empty($uid)) $uid = $_G['uid'];
    if ($gid > 0) {
        if (!$org = C::t('organization')->fetch($gid)) { //机构不存在时，返回错误；
            //return false;
        }
        $spacearr['usesize'] = intval($org['usesize']);

        $spacearr['maxspacesize'] = C::t('organization')->get_usespace_size_by_orgid($gid);
    } else {
        if (!$space) {
            $space = dzzgetspace($uid);
        } else {
            $space['usesize'] = DB::result_first("select usesize from %t where uid=%d", array('user_field', $uid));
        }
        $spacearr = $space;
    }
    if ($isupdate) {
        $new_usesize = ($spacearr['usesize'] + $size) > 0 ? ($spacearr['usesize'] + $size) : 0;
        if ($gid > 0) {
            C::t('organization')->update($gid, array('usesize' => $new_usesize));
        } else {
            C::t('user_field')->update($uid, array('usesize' => $new_usesize));
        }
        return true;
    } else {
        if ($gid) {
            if ($spacearr['maxspacesize'] == 0) return true; //机构最大空间为0 表示不限制
            if ($size > $spacearr['maxspacesize']) {
                return false;
            } else {
                return true;
            }
            return true;
        } else {
            if ($space['maxspacesize'] == 0) return true; //用户组最大空间为0 表示不限制
            elseif ($space['maxspacesize'] < 0) return false; //用户组最大空间<0 表示没有空间
            if (($spacearr['usesize'] + $size) > $spacearr['maxspacesize']) {
                return false;
            } else {
                return true;
            }
        }
    }
}

function getPositionName($fid)
{
    $return = '';
    $folder = C::t('folder')->fetch($fid);
    if ($folder['flag'] == 'dock') {
        $return = lang('dock');

    } elseif ($folder['flag'] == 'desktop') {
        $return = lang('desktop');
    } else {
        $return = $folder['fname'];
    }
    if ($return) return '"' . $return . '"';
    else return '';
}

function getPathByPfid($pfid, $arr = array(), $count = 0)
{
    //static $arr=array();
    //static $count=0;
    if ($count > 100) return $arr; //防止死循环；
    else $count++;
    if ($value = DB::fetch_first("select pfid,fid,fname from " . DB::table('folder') . " where fid='{$pfid}'")) {
        $arr[$value['fid']] = $value['fname'];
        if ($value['pfid'] > 0 && $value['pfid'] != $pfid) $arr = getPathByPfid($value['pfid'], $arr, $count);
    }
    //$arr=array_reverse($arr);

    return $arr;

}



//返回自己和上级目录fid数组；
function getTopFid($fid, $i = 0, $arr = array())
{
    $arr[] = $fid;
    if ($i > 100) return $arr; //防止死循环；
    else $i++;
    if ($pfid = DB::result_first("select pfid from " . DB::table('folder') . " where fid='{$fid}'")) {
        if ($pfid != $fid) $arr = getTopFid($pfid, $i, $arr);
    }
    return $arr;
}

function getGidByContainer($container)
{
    global $_G;
    if (strpos($container, 'icosContainer_folder_') !== false) {
        $fid = intval(str_replace('icosContainer_folder_', '', $container));
        if ($fid > 0) return DB::result_first("select gid from " . DB::table('folder') . " where fid='{$fid}'");
        else return 0;
    } else {
        return 0;
    }
}

function getFidByContainer($container)
{
    global $_G;
    if (strpos($container, 'icosContainer_body_') !== false) {
        $fid = intval(str_replace('icosContainer_folder_', '', $container));
        return DB::result_first("select fid from " . DB::table('folder') . " where flag='desktop' and uid='" . $_G['uid'] . "'");
    } elseif (strpos($container, 'icosContainer_folder_') !== false) {
        $fid = intval(str_replace('icosContainer_folder_', '', $container));
        return DB::result_first("select fid from " . DB::table('folder') . " where fid='{$fid}'");
    } elseif (strpos($container, '_dock_') !== false) {
        return DB::result_first("select fid from " . DB::table('folder') . " where flag='dock' and uid='" . $_G['uid'] . "'");
    } elseif ($container == '_dock') {
        return DB::result_first("select fid from " . DB::table('folder') . " where flag='dock' and uid='" . $_G['uid'] . "'");
    } else {
        return false;
    }
}

function getContainerByFid($pfid)
{
    global $_G;
    $folder = C::t('folder')->fetch($pfid);
    switch ($folder['flag']) {
        case 'desktop':
            return 'icosContainer_body_' . $pfid;
        case 'dock':
            return '_dock';
        case 'folder':
            return 'icosContainer_folder_' . $pfid;
        case 'organization':
            return 'icosContainer_folder_' . $pfid;
        default:
            return '';
    }
}


/*function replace_remote($icoarr){
	global $_G;
	switch($icoarr['type']){
		case 'attach':case 'document':
			$icoarr['url']='';
			break;
		case 'image':
			if($icoarr['thumb']) $icoarr['img']=$_G['setting']['attachurl'].getimgthumbname($icoarr['url']);
			else $icoarr['img']=getAttachUrl(array('attachment'=>$icoarr['url'],'remote'=>$icoarr['remote']),true);
			$icoarr['_bz']=$bz;
			$icoarr['url']=getAttachUrl(array('attachment'=>$icoarr['url'],'remote'=>$icoarr['remote']),true);
			break;
	}
	return $icoarr;
}*/
function replace_canshu($str, $data = array())
{
    global $_G;
    $replacearr = array('{dzzscript}' => 'index.php', '{DZZSCRIPT}' => 'index.php', '{adminscript}' => 'admin.php', '{ADMINSCRIPT}' => 'admin.php', '{uid}' => $_G['uid']);
    $search = array();
    $replace = array();
    foreach ($replacearr as $key => $value) {
        $search[] = $key;
        $replace[] = $value;
    }
    return str_replace($search, $replace, $str);
}

function dzz_libfile($libname, $folder = '')
{
    $libpath = DZZ_ROOT . '/dzz/' . $folder;
    if (strstr($libname, '/')) {
        list($pre, $name) = explode('/', $libname);
        return realpath("{$libpath}/{$pre}/{$pre}_{$name}.php");
    } else {
        return realpath("{$libpath}/{$libname}.php");
    }
}

function dzzlang($file, $langvar = null, $vars = array(), $default = null)
{
    global $_G;
//	return lang($file,$langvar,$vars,$defualt,'dzz/admin');
    list($path, $file) = explode('/', $file);
    if (!$file) {
        $file = $path;
        $path = '';
    }

    if ($path == '') {
        $vars1 = explode(':', $file);
        if (count($vars1) == 2) {
            list($plugfolder, $file) = explode(':', $file);
            $key = 'plugin_' . $plugfolder . '_' . $file;
            if (!isset($_G['lang'][$key])) {
                include DZZ_ROOT . './dzz/plugin/' . $plugfolder . '/language/' . 'lang_' . $file . '.php';
                $_G['lang'][$key] = $lang;
            }
        } else {
            $key = $file;
            if (!isset($_G['lang'][$key])) {
                include DZZ_ROOT . './dzz/language/' . ($path == '' ? '' : $path . '/') . 'lang_' . $file . '.php';
                $_G['lang'][$key] = $lang;
            }
        }
        $returnvalue = &$_G['lang'];
    } else {
        $key = $path == '' ? $file : $path . '_' . $file;
        if (!isset($_G['lang'][$key])) {
            include DZZ_ROOT . './dzz/' . $path . '/language/lang_' . $file . '.php';
            $_G['lang'][$key] = $lang;
        }

        $returnvalue = &$_G['lang'];
    }
    $return = $langvar !== null ? (isset($returnvalue[$key][$langvar]) ? $returnvalue[$key][$langvar] : null) : $returnvalue[$key];
    $return = $return === null ? ($default !== null ? $default : $langvar) : $return;
    $searchs = $replaces = array();
    if ($vars && is_array($vars)) {
        foreach ($vars as $k => $v) {
            $searchs[] = '{' . $k . '}';
            $replaces[] = $v;
        }
    }
    if (is_string($return) && strpos($return, '{_G/') !== false) {
        preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
        foreach ($gvar[0] as $k => $v) {
            $searchs[] = $v;
            $replaces[] = getglobal($gvar[1][$k]);
        }
    }
    $return = str_replace($searchs, $replaces, $return);
    return $return;
}

function getFileTypeName($type, $ext)
{
    $typename = '';
    switch ($type) {
        case 'image':
            $typename = lang('typename_image');
            break;
        case 'video':
            $typename = lang('typename_video');
            break;
        case 'music':
            $typename = lang('typename_music');
            break;
        case 'attach':
            $typename = lang('typename_attach');
            break;
        case 'app':
            $typename = lang('typename_app');
            break;
        case 'user':
            $typename = lang('typename_user');
            break;
        case 'link':
            $typename = lang('typename_link');
            break;
        case 'folder':
            $typename = lang('typename_folder');
            break;
        case 'document':
            $typename = lang('typename_document');
            break;
        case 'pan':
            $typename = lang('typename_pan');
            break;
        case 'storage':
            $typename = lang('typename_storage');
            break;
        case 'shortcut':
            $typename = lang('typename_shortcut');
            return $typename;
    }

    $name = '';
    if ($ext == 'dzzdoc') {
        $name = lang('extname_dzzdoc');
    } elseif ($ext == 'txt') {
        $name = lang('extname_txt');
    } else {
        $name = strtoupper($ext) . ' ' . $typename;
    }

    return $name;
}



function dzzgetspace($uid)
{
    global $_G;
    $space = array();
    if ($uid == 0) {
        $space = array('uid' => 0, 'self' => 0, 'username' => '', 'adminid' => 0, 'groupid' => 7, 'credits' => 0, 'timeoffset' => 9999, 'usesize' => 0, 'maxspacesize' => -1, 'attachextensions' => '');
    } else {
        $space = getuserbyuid($uid);
    }

    if ($_G['adminid'] == 1) {
        $space['self'] = 2;
    }
    //用户组信息
    if (!isset($_G['cache']['usergroups'])) loadcache('usergroups');
    $usergroup = $_G['cache']['usergroups'][$space['groupid']];
    //$space['groupsize']=$usergroup['maxspacesize']*1024*1024;

    //获取相关设置信息
    $setting = $_G['setting'];
    if ($config = DB::fetch_first("select usesize,attachextensions,maxattachsize,addsize,buysize,perm,taskbar,userspace from " . DB::table('user_field') . " where uid='{$uid}'")) {
        $config['perm'] = ($config['perm'] < 1) ? $usergroup['perm'] : $config['perm'];
        $config['attachextensions'] = ($config['attachextensions'] < 0) ? $usergroup['attachextensions'] : $config['attachextensions'];
        $config['maxattachsize'] = ($config['maxattachsize'] < 0) ? $usergroup['maxattachsize'] * 1024 * 1024 : $config['maxattachsize'] * 1024 * 1024;

       /* //如果用户存储功能未开启,用户空间大小为-1
        if (isset($setting['usermemoryOn']) && !$setting['usermemoryOn']) {

            $config['maxspacesize'] = -1;

        } else {*/
            //判断是否有用户独享空间设置
            if ($config['userspace'] > 0 || $config['userspace'] == -1) {
                $config['maxspacesize'] = ($config['userspace'] > 0) ? $config['userspace'] * 1024 * 1024 : $config['userspace'];

            } elseif ($config['userspace'] == 0) {//如果未设置用户空间

               /* //判断是否为指定用户开启用户存储
                $spaceon = isset($setting['mermoryusersetting']) ? $setting['mermoryusersetting'] : '';
                $memorySpace = isset($setting['memorySpace']) ? $setting['memorySpace'] : '';

                //指定用户时,并指定用户空间
                if ($spaceon == 'appoint' && $memorySpace != 0) {

                    $usersarr = explode(',', $setting['memoryorgusers']);
                    $uesrs = array();
                    foreach ($usersarr as $v) {
                        //群组id
                        if (preg_match('/\d+/', $v)) {
                            foreach (C::t('organization_user')->fetch_user_byorgid($v) as $val) {
                                $users[] = $val['uid'];
                            }
                        } elseif ($v == 'other') {
                            foreach (C::t('user')->fetch_uid_by_groupid(9) as $val) {
                                $users[] = $val['uid'];
                            }
                        } elseif (preg_match('/uid_\d+/', $v)) {
                            $users[] = preg_replace('/uid_/', '');
                        }

                    }
                    $users = array_unique($users);
                    //判断用户是否在指定用户中
                    if (in_array($uid, $users)) {
                        $config['maxspacesize'] = ($memorySpace > 0) ? $memorySpace * 1024 * 1024 : $memorySpace;
                    }else{
                        $config['maxspacesize'] = -1;
                    }
                } else {*///如果未指定开启存储用户或设置指定用户空间为0
                    //用户组空间(去掉额外空间和购买空间)
                    if ($usergroup['maxspacesize'] == 0) {
                        $config['maxspacesize'] = 0;
                    } elseif ($usergroup['maxspacesize'] < 0) {
                        /*if(($config['addsize']+$config['buysize'])>0){
                                $config['maxspacesize']=($config['addsize']+$config['buysize'])*1024*1024;
                            }else{*/
                        $config['maxspacesize'] = -1;
                        //}
                    } else {
                        //$config['maxspacesize']=($usergroup['maxspacesize']+$config['addsize']+$config['buysize'])*1024*1024;
                        $config['maxspacesize'] = $usergroup['maxspacesize'] * 1024 * 1024;
                    }
               // }
            }
       // }
        $space = array_merge($space, $config);
    }
    $space['fusesize'] = formatsize($space['usesize']);
    if ($space['maxspacesize'] > 0) {
        $space['fmaxspacesize'] = formatsize($space['maxspacesize']);
    } elseif ($space['maxspacesize'] == 0) {
        $space['fmaxspacesize'] = '无限制';
    } else {
        $space['fmaxspacesize'] = '未分配空间';
    }
    $space['attachextensions'] = str_replace(' ', '', $space['attachextensions']);
    $typefid = array();

    $space['typefid'] = C::t('folder')->fetch_typefid_by_uid($uid);
    $space['maxChunkSize'] = $_G['setting']['maxChunkSize'];
    return $space;

}

function microtime_float()
{
    list($usec, $sec) = explode(' ', microtime());
    return (floatval($usec) + floatval($sec));
}

function dzz_file_get_contents($source, $redirect = 0, $proxy = '')
{
    if (function_exists('curl_init') !== false) {
        return curl_file_get_contents($source, $redirect, $proxy);
    } else {
        return file_get_contents($source);
    }
}

function curl_file_get_contents($durl, $redirect = 0, $proxy = '')
{
    global $_SERVER;
    set_time_limit(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $durl);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    if ($proxy) {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_USERAGENT, '');
    curl_setopt($ch, CURLOPT_REFERER, '');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    if ($redirect) $r = curl_redir_exec($ch);
    else $r = curl_exec($ch);
    curl_close($ch);
    return $r;
}

function curl_redir_exec($ch, $debug = "")
{
    static $curl_loops = 0;
    static $curl_max_loops = 20;
    set_time_limit(0);
    if ($curl_loops++ >= $curl_max_loops) {
        $curl_loops = 0;
        return FALSE;
    }
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $debbbb = $data;
    list($header, $data) = explode("\n\n", $data, 2);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302) {
        $matches = array();
        preg_match('/Location:(.*?)\n/', $header, $matches);
        $url = @parse_url(trim(array_pop($matches)));
        if (!$url) {
            //couldn't process the url to redirect to
            $curl_loops = 0;
            return $data;
        }
        $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        /*    if (!$url['scheme'])
                $url['scheme'] = $last_url['scheme'];
            if (!$url['host'])
                $url['host'] = $last_url['host'];
            if (!$url['path'])
                $url['path'] = $last_url['path'];*/
        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
        curl_setopt($ch, CURLOPT_URL, $new_url);
        //    debug('Redirecting to', $new_url);

        return curl_redir_exec($ch);
    } else {
        $curl_loops = 0;
        return $debbbb;
    }
}

function ico_png($source, $target, $proxy = '')
{
    $ext = strtolower(substr(strrchr($source, '.'), 1, 10));
    $imgexts = array('png', 'jpg', 'jpeg', 'gif');
    if (in_array($ext, $imgexts)) {
        exit($source);
        $data = dzz_file_get_contents($source, 0, $proxy);
        if ($data && file_put_contents($target, $data)) {
            return true;
        } else {
            return false;
        }
    } elseif ($ext == 'ico') {
        require_once dzz_libfile('class/ico');
        $oico = new Ico($source, $proxy);
        $max = -1;
        $data_length = 0;
        for ($i = 0; $i < $oico->TotalIcons(); $i++) {
            $data = $oico->GetIconInfo($i);
            if ($data['data_length'] > $data_length) {
                $data_length = $data['data_length'];
                $max = $i;
            }
        }
        if ($max >= 0 && imagepng($oico->GetIcon($max), $target)) {
            return true;
        } else return false;
    } else {
        return false;
    }
}

function check_remote_file_exists($url, $proxy = '')
{
    set_time_limit(0);
    $u = parse_url($url);
    if (!$u || !isset($u['host'])) return false;
    if (function_exists('curl_init') !== false) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 500);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

        if ($proxy) {
            curl_setopt($curl, CURLOPT_PROXY, $proxy);
        }
        // 不取回数据
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_REFERER, '');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); //不加这个会返回403，加了才返回正确的200，原因不明
        // 发送请求
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败
        if ($result !== false) {
            // 再检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $found = true;
            }
        }
        curl_close($curl);
        return $found;
    } else {
        $h = get_headers($url);
        //print_r($h);
        if (!$h || !isset($h[0])) return false;
        $status = $h[0];
        //echo $status;
        return preg_match("/.*?200\s*OK/i", $status) ? true : false;
    }
}

function imagetolocal($source, $dir = 'appimg', $target = '')
{
    global $_G;
    if (empty($source)) return false;
    if (!$data = dzz_file_get_contents($source)) {
        return false;
    }
    if ($target == 'dzz/images/default/icodefault.png' || $target == 'dzz/images/default/widgetdefault.png' || preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $target)) {
        $target = '';
    }
    if (!$target) {
        $imageext = array('jpg', 'jpeg', 'png', 'gif');
        $ext = strtolower(substr(strrchr($source, '.'), 1, 10));
        if (!in_array($ext, $imageext)) return false;
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        $target1 = $_G['setting']['attachdir'] . $dir . '/' . $subdir . '' . date('His') . '' . strtolower(random(16)) . '.' . $ext;
        $target = str_replace($_G['setting']['attachdir'], '', $target1);
    } else {
        $target1 = $_G['setting']['attachdir'] . $target;
    }
    $targetpath = dirname($target1);
    dmkdir($targetpath);
    if (file_put_contents($target1, $data)) {
        if (@filesize($target1) < 200) {
            @unlink($target1);
            return false;
        }
        return $target;
    } else return false;
}

function image_to_icon($source, $target, $domain)
{
    global $_G;
    if (!$data = dzz_file_get_contents($source)) {
        return false;
    }
    if (!$target) {
        $imageext = array('jpg', 'jpeg', 'png', 'gif');
        $ext = str_replace("/\?.+?/i", '', strtolower(substr(strrchr($source, '.'), 1, 10)));
        if (!in_array($ext, $imageext)) $ext = 'jpg';
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        $target = 'icon/' . $subdir . '' . $domain . '_' . strtolower(random(8)) . '.' . $ext;
        $target_attach = $_G['setting']['attachdir'] . $target;
    } else {
        $target_attach = $_G['setting']['attachdir'] . $target;
    }
    $targetpath = dirname($target_attach);
    dmkdir($targetpath);
    if (file_put_contents($target_attach, $data)) {
        return $target;
    } else {
        return false;
    }
}


function getTxtAttachByMd5($message, $filename_title, $ext)
{
    global $_G;
    @set_time_limit(0);
    $filename = date('His') . '' . strtolower(random(16));
    //$ext=strtolower(substr(strrchr($filename_title, '.'), 1, 10));

    if (!$ext) $ext = 'dzzdoc';
    if ($ext && in_array($ext, $_G['setting']['unRunExts'])) {
        $unrun = 1;
    } else {
        $unrun = 0;
    }
    //保存附件并且生成附件MD5;
    $subdir = $subdir1 = $subdir2 = '';
    $subdir1 = date('Ym');
    $subdir2 = date('d');
    $subdir = $subdir1 . '/' . $subdir2 . '/';
    $target1 = 'dzz/' . $subdir . 'index.html';
    $target = 'dzz/' . $subdir;
    $target_attach = $_G['setting']['attachdir'] . $target1;
    $targetpath = dirname($target_attach);
    dmkdir($targetpath);

    if (is_resource($message)) {
        while (!feof($message)) {
            if (!file_put_contents($_G['setting']['attachdir'] . $target . $filename . '.' . ($unrun ? 'dzz' : $ext), fread($message, 8192), FILE_APPEND)) {
                return false;
            }
        }
        fclose($message);
    } else {
        if ($message == '') $message = ' ';
        if (!file_put_contents($_G['setting']['attachdir'] . $target . $filename . '.' . ($unrun ? 'dzz' : $ext), $message)) {
            return false;
        }
    }
    $size = @filesize($_G['setting']['attachdir'] . $target . $filename . '.' . ($unrun ? 'dzz' : $ext));

    $md5 = md5_file($_G['setting']['attachdir'] . $target . $filename . '.' . ($unrun ? 'dzz' : $ext));
    if ($md5 && $attach = C::t('attachment')->fetch_by_md5($md5)) {
        $attach['filename'] = $filename_title;
        $attach['filetype'] = strtolower($ext);
        @unlink($_G['setting']['attachdir'] . $target . $filename . '.' . ($unrun ? 'dzz' : $ext));

    } else {
        $remote = 0;
        $attach = array(
            'filesize' => $size,
            'attachment' => $target . $filename . '.' . ($unrun ? 'dzz' : $ext),
            'filetype' => strtolower($ext),
            'filename' => $filename_title,
            'remote' => $remote,
            'copys' => 0,
            'md5' => $md5,
            'unrun' => $unrun,
            'dateline' => $_G['timestamp'],
        );
        if (!$attach['aid'] = C::t('attachment')->insert($attach, 1)) {
            return false;
        }
        try {
            if ($remoteid = io_remote::MoveToSpace($attach)) {
                $attach['remote'] = $remoteid;
                C::t('attachment')->update($attach['aid'], array('remote' => $remoteid));
                @unlink($_G['setting']['attachdir'] . $target . $filename . '.' . ($unrun ? 'dzz' : $ext));
            }
        } catch (Exception $e) {
            //return array('error'=>$e->getMessage());
            return false;
        }
    }
    return $attach;
}


function checkCopy($icoid = 0, $sourcetype = '', $iscut = 0, $obz, $tbz)
{
    global $_G;
    $copy = 1;
    if ($sourcetype == 'uid') {
        return 1;
    } elseif ($iscut == 2) {
        return 1;
    } elseif ($iscut == 1) {
        return 0;
    } elseif ($obz != $tbz) {
        return 1;//不同api之间复制	;
    } elseif ($obz == $tbz) {
        return 0;//相同api之间移动;

    }
    return $copy;
}

function delete_icoid_from_container($icoid, $pfid)
{
    global $_G;
    $typefid = C::t('folder')->fetch_typefid_by_uid($_G['uid']);
    if ($pfid == $typefid['dock']) {
        $docklist = DB::result_first("select docklist from " . DB::table('user_field') . " where uid='{$_G[uid]}'");
        $docklist = $docklist ? explode(',', $docklist) : array();
        foreach ($docklist as $key => $value) {
            if ($value == $icoid) {
                unset($docklist[$key]);
            }
        }
        C::t('user_field')->update($_G['uid'], array('docklist' => implode(',', $docklist)));
    } elseif ($pfid == $typefid['desktop']) {

        $icos = DB::result_first("select screenlist from " . DB::table('user_field') . " where uid='{$_G[uid]}'");
        $icos = $icos ? explode(',', $icos) : array();
        foreach ($icos as $key => $value) {
            if ($value == $icoid) {
                unset($icos[$key]);
            }
        }
        C::t('user_field')->update($_G['uid'], array('screenlist' => implode(',', $icos)));
    }
}

function dzz_update_source($type, $oid, $data, $istype = false)
{
    $idtypearr = array('lid', 'vid', 'mid', 'qid', 'picid', 'did', 'fid');
    $typearr = array('link', 'video', 'music', 'attach', 'image', 'document', 'folder');
    $table = '';
    $idtype = '';
    $pre = 'source_';
    if ($isidtype) {
        if (in_array($type, $idtypearr)) {
            if ($type == 'fid') $pre = '';
            $table = '' . $pre . str_replace($idtypearr, $typearr, $type);
            $idtype = $type;
        }
    } else {
        if ($type == 'folder') $pre = '';
        if (in_array($type, $typearr)) {
            $table = '' . $pre . $type;
            $idtype = str_replace($typearr, $idtypearr, $type);
        }
    }
    if ($table) return C::t($table)->update($oid, $data);
    else return false;
}

function getAttachUrl($attach, $absolute = false)
{
    global $_G;
    $attachment = '';
    $bz = io_remote::getBzByRemoteid($attach['remote']);
    if ($bz == 'dzz') {
        if ($absolute) {
            $attachment = $_G['setting']['attachdir'] . './' . $attach['attachment'];
        } else {
            $attachment = $_G['siteurl'] . $_G['setting']['attachurl'] . $attach['attachment'];
        }
        return $attachment;
    } elseif (strpos($bz, 'FTP') === 0) {
        return $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=getStream&path=' . dzzencode($bz . '/' . $attach['attachment']);
    } else {
        return IO::getFileUri($bz . '/' . $attach['attachment']);
    }

}

function getBzByPath($path)
{
    $bzarr = explode(':', $path);
    $allowbz = C::t('connect')->fetch_all_bz();
    if (strpos($path, 'dzz::') !== false) {
        return '';
    } elseif (strpos($path, 'attach::') !== false) {
        return '';
    } elseif (is_numeric($bzarr[0])) {
        return '';
    } elseif (in_array($bzarr[0], $allowbz)) {
        return $bzarr[0];
    } else {
        return '';
    }
}

function getDzzPath($attach)
{
    global $_G;
    $url = '';
    $bz = io_remote::getBzByRemoteid($attach['remote']);
    if ($bz == 'dzz') {
        $url = 'attach::' . $attach['aid'];
    } else {
        $url = $bz . '/' . $attach['attachment'];
    }
    return $url;
}

function geticonfromext($ext, $type='')
{
    global $_G;
    $img = 'dzz/images/extimg/' . strtolower($ext) . '.png';
    if (!is_file(DZZ_ROOT . $img)) {
        switch ($type) {
            case 'video':
                $img = 'dzz/images/extimg/video.png';
                break;
            case 'music':
                $img = 'dzz/images/extimg/music.png';
                break;
            case 'document':
                $img = 'dzz/images/extimg/document.png';
                break;
            case 'folder':
                $img = '';
                break;
            case 'link':
                $img = 'dzz/images/extimg/link.png';
                break;
            case 'dzzdoc':
                $img = 'dzz/images/extimg/dzzdoc.png';
                break;
            case 'topic':
                $img = 'dzz/images/extimg/topic.png';
                break;
            default:
                $img = 'dzz/images/extimg/unknow.png';
        }
    }
    return $img;
}

function getUrlIcon($link)
{
    global $_G;
    $rarr = array();
    $parse_url = parse_url($link);
    $host = $parse_url['host'];
    $host = preg_replace("/^www./", '', $host);//strstr('.',$host);
    //查询网址特征库

    if ($icon = C::t('icon')->fetch_by_link($link)) {
        return array('img' => $_G['setting']['attachurl'] . $icon['pic'], 'did' => $icon['did'], 'ext' => $icon['ext']);
    } else {

        require_once dzz_libfile('class/caiji');
        $caiji = new caiji($link);
        $source = $caiji->getFavicon();
        if ($source) {
            $subdir = $subdir1 = $subdir2 = '';
            $subdir1 = date('Ym');
            $subdir2 = date('d');
            $subdir = $subdir1 . '/' . $subdir2 . '/';
            $target = 'icon/' . $subdir . '' . $host . '_' . strtolower(random(8)) . '.png';
            $target_attach = $_G['setting']['attachdir'] . $target;
            $targetpath = dirname($target_attach);
            dmkdir($targetpath);
            ico_png($source, $target_attach, $caiji->getProxy());
            if (is_file($target_attach)) {
                if ($did = C::t("icon")->insert(array('domain' => $host, 'pic' => $target, 'check' => 0, 'dateline' => $_G['timestamp'], 'uid' => $_G['uid'], 'username' => $_G['username'], 'copys' => 0), 1)) {
                    return array('img' => $_G['setting']['attachurl'] . $target, 'did' => $did);
                }
            }
        }
    }
    return array('img' => 'dzz/images/default/e.png', 'did' => 0);
}

function addtoconfig($icoarr, $ticoid = 0)
{
    global $_G, $space;
    $oposition = 10000;
    $icoid = $icoarr['rid'];
    if ($folder = C::t('folder')->fetch($icoarr['pfid'])) {
        if ($folder['flag'] == 'dock') {
            if ($docklistarr = C::t('user_field')->fetch($_G['uid'])) {
                $docklist = $docklistarr['docklist'] ? explode(',', $docklistarr['docklist']) : array();
                if (in_array($icoid, $docklist)) {//已经存在则先删除
                    foreach ($docklist as $key => $id) {
                        if (intval($id) < 0 || $id == $icoid) {
                            unset($docklist[$key]);
                            $oposition = $key;
                        }
                    }
                }
                if ($ticoid && in_array($ticoid, $docklist)) {
                    $temp = array();
                    foreach ($docklist as $key => $id) {
                        if ($id == $ticoid) {
                            if ($oposition > $key) {
                                $temp[] = $icoid;
                                $temp[] = $id;
                            } else {
                                $temp[] = $id;
                                $temp[] = $icoid;
                            }
                        } else {
                            $temp[] = $id;
                        }
                    }
                    $docklist = $temp;
                } else {
                    $docklist[] = $icoid;
                }
                C::t('user_field')->update($_G['uid'], array('docklist' => trim(implode(',', $docklist), ',')));
            }


        } elseif ($folder['flag'] == 'desktop') {

            if ($nav = C::t('user_field')->fetch($_G['uid'])) {
                $icos = $nav['screenlist'] ? explode(',', $nav['screenlist']) : array();
                if (in_array($icoid, $icos)) {//已经存在则先删除
                    foreach ($icos as $key => $id) {
                        if (intval($id) < 0 || $id == $icoid) {
                            unset($icos[$key]);
                            $oposition = $key;
                        }
                    }
                }
                if ($ticoid && in_array($ticoid, $icos)) {
                    $temp = array();
                    foreach ($icos as $key => $id) {
                        if ($id == $ticoid) {
                            if ($oposition > $key) {
                                $temp[] = $icoid;
                                $temp[] = $id;
                            } else {
                                $temp[] = $id;
                                $temp[] = $icoid;
                            }
                        } else {
                            $temp[] = $id;
                        }
                    }
                    $icos = $temp;
                } else {
                    $icos[] = $icoid;
                }
                C::t('user_field')->update($_G['uid'], array('screenlist' => implode(',', $icos)));
            }
        }
        if ($icoarr['type'] == 'folder' && $icoarr['flag'] == '') {
            C::t('folder')->update($icoarr['oid'], array('pfid' => $folder['fid'], 'gid' => $folder['gid']));
        }
    }
    return true;
}

function is_upload_files($source)
{
    return $source && ($source != 'none') && (is_uploaded_file($source) || is_uploaded_file(str_replace('\\\\', '\\', $source)));
}

function save_to_local($source, $target)
{
    $targetpath = dirname($target);
    dmkdir($targetpath);
    if (!is_upload_files($source)) {
        $succeed = false;
    } elseif (@copy($source, $target)) {
        $succeed = true;
    } elseif (function_exists('move_uploaded_file') && @move_uploaded_file($source, $target)) {
        $succeed = true;
    } elseif (@is_readable($source) && (@$fp_s = fopen($source, 'rb')) && (@$fp_t = fopen($target, 'wb'))) {
        while (!feof($fp_s)) {
            $s = @fread($fp_s, 1024 * 512);
            @fwrite($fp_t, $s);
        }
        fclose($fp_s);
        fclose($fp_t);
        $succeed = true;
    }

    if ($succeed) {
        @chmod($target, 0644);
        @unlink($source);
    }
    return $succeed;
}


function uploadtolocal($upload, $dir = 'appimg', $target = '', $exts = array('jpg', 'jpeg', 'png', 'gif'))
{
    global $_G;
    if ($target == 'dzz/images/default/icodefault.png' || $target == 'dzz/images/default/widgetdefault.png' || preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $target)) {
        $target = '';
    }
    $source = $upload['tmp_name'];
    $ext = strtolower(substr(strrchr($upload['name'], '.'), 1, 10));
    if (!in_array($ext, $exts)) return false;
    if ($target) {
        $target1 = $_G['setting']['attachdir'] . $target;
    } else {
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        $target1 = $_G['setting']['attachdir'] . $dir . '/' . $subdir . '' . date('His') . '' . strtolower(random(16)) . '.' . $ext;
        $target = str_replace($_G['setting']['attachdir'], '', $target1);
    }

    if (save_to_local($source, $target1)) {
        return $target;
    } else {
        return false;
    }
}

function upload_to_icon($upload, $target, $domain='')
{
    global $_G;
    $source = $upload['tmp_name'];
    if (!$target) {
        $imageext = array('jpg', 'jpeg', 'png', 'gif');
        $ext = strtolower(substr(strrchr($upload['name'], '.'), 1, 10));
        if (!in_array($ext, $imageext)) return false;
        $subdir = $subdir1 = $subdir2 = '';
        $subdir1 = date('Ym');
        $subdir2 = date('d');
        $subdir = $subdir1 . '/' . $subdir2 . '/';
        $target = 'icon/' . $subdir . '' . $domain . '_' . strtolower(random(8)) . '.' . $ext;
        $target_attach = $_G['setting']['attachdir'] . $target;
    } else {
        $target_attach = $_G['setting']['attachdir'] . $target;
    }
    if (save_to_local($source, $target_attach)) {
        return $target;
    } else {
        return false;
    }
}

function dzz_app_pic_save($FILE, $dir = 'appimg')
{
    global $_G;
    $imageext = array('jpg', 'jpeg', 'png', 'gif');
    $ext = strtolower(substr(strrchr($FILE['name'], '.'), 1, 10));
    if (!in_array($ext, $imageext)) return '文件格式不允许';
    $subdir = $subdir1 = $subdir2 = '';
    $subdir1 = date('Ym');
    $subdir2 = date('d');
    $subdir = $subdir1 . '/' . $subdir2 . '/';
    $target = $dir . '/' . $subdir;
    $filename = date('His') . '' . strtolower(random(16));
    if (!$attach = io_dzz::UploadSave($FILE)) {
        return '应用图片上传失败';
    }
    $setarr = array(
        'uid' => $_G['uid'],
        'username' => $_G['username'],
        'dateline' => $_G['timestamp'],
        'aid' => $attach['aid'],
    );
    if ($setarr['picid'] = DB::insert('app_pic', $setarr, 1)) {
        C::t('attachment')->addcopy_by_aid($attach['aid']);
        return $setarr;
    }
    return false;
}

function get_permsarray()
{
    $perms = array_merge_recursive(perm_binPerm::getPowerTitle(), perm_binPerm::getPowerArr(),perm_binPerm::getPowerIcos());//获取所有权限
    unset($perms['flag']);
    return $perms;
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix)
{
    if (is_object($mix)) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * 字符串命名风格转换
 * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
 * @param string $name 字符串
 * @param integer $type 转换类型
 * @return string
 */
function parse_name($name, $type = 0)
{
    if ($type) {
        return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
            return strtoupper($match[1]);
        }, $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

//增加函数处理网盘权限判断
function get_resources_some_setting()
{
    global $_G;
    $setting = $_G['setting'];
    $data = array(
        'useronperm' => false,
        'orgonperm' => false,
        'grouponperm' => false,
        'fileVersion' => true,
        'fileVersionNumber' => 0,
        'userallowonperm' => array(),
        'left_topcontent' => true,
        'allownewgroup'=>false,
        'allownewcat'=>false,
        'finallydelete'=>false
    );
    if (!isset($setting['explorer_usermemoryOn'])) {
        $data['useronperm'] = true;
    } else {
        //用户存储开启
        if ($setting['explorer_usermemoryOn'] == 1) {
            $spaceon = isset($setting['explorer_mermoryusersetting']) ? $setting['explorer_mermoryusersetting'] : '';
            if ($spaceon == 'appoint') {//指定用户时
                $usersarr = explode(',', $setting['explorer_memoryorgusers']);
                $uesrs = array();
                foreach ($usersarr as $v) {
                    //群组id
                    if (preg_match('/^\d+$/', $v)) {
                        foreach (C::t('organization_user')->get_all_user_byorgid($v) as $val) {
                            $users[] = $val['uid'];
                        }
                    } elseif ($v == 'other') {
                        foreach (C::t('user')->fetch_uid_by_groupid(9) as $val) {
                            $users[] = $val['uid'];
                        }
                    } elseif (preg_match('/^uid_\d+$/', $v)) {
                        $users[] = preg_replace('/uid_/', '',$v);
                    }

                }
                $users = array_unique($users);
                $data['userallowonperm'] = $users;
                if (in_array($_G['uid'], $data['userallowonperm'])) {
                    $data['useronperm'] = true;
                }

            } else {//未指定用户时
                $data['useronperm'] = true;
            }
        }
    }

    if (!isset($setting['fileVersion']) || (isset($setting['fileVersion']) && $setting['fileVersion'] == 1)) {
        $data['fileVersion'] = true;
    } else {
        $data['fileVersion'] = false;
    }
    if (isset($setting['fileVersionNumber']) && $setting['fileVersionNumber']) {
        $data['fileVersionNumber'] = $setting['fileVersionNumber'];
    }

    if ((isset($setting['explorer_organizationOn']) && $setting['explorer_organizationOn'] == 1) || !isset($setting['explorer_organizationOn'])) {
        $data['orgonperm'] = true;
    }

    if ((isset($setting['explorer_groupOn']) && $setting['explorer_groupOn'] == 1) || !isset($setting['explorer_groupOn'])) {
        $data['grouponperm'] = true;
    }

    if (!$data['grouponperm'] && !$data['useronperm'] && !$data['orgonperm']) {
        $data['left_topcontent'] = false;
    }
    if(!isset($setting['explorer_groupcreate'])){
        $data['allownewgroup'] = true;
    }else{
        if ($setting['explorer_groupcreate'] == 1) {
            $groupcreateon = isset($setting['explorer_mermorygroupsetting']) ? $setting['explorer_mermorygroupsetting'] : '';
            if ($groupcreateon == 'appoint') {//指定用户时
                $usersarr = explode(',', $setting['explorer_memorygroupusers']);
                $uesrs = array();
                foreach ($usersarr as $v) {
                    //群组id
                    if (preg_match('/^\d+$/', $v)) {
                        foreach (C::t('organization_user')->get_all_user_byorgid($v) as $val) {
                            $users[] = $val['uid'];
                        }
                    } elseif ($v == 'other') {
                        foreach (C::t('user')->fetch_uid_by_groupid(9) as $val) {
                            $users[] = $val['uid'];
                        }
                    } elseif (preg_match('/^uid_\d+$/', $v)) {
                        $users[] = preg_replace('/uid_/', '',$v);
                    }

                }
                $users = array_unique($users);
                $data['userallowgroupcreate'] = $users;
                if (in_array($_G['uid'], $data['userallowgroupcreate'])) {
                    $data['allownewgroup'] = true;
                }

            } else {//未指定用户时
                $data['allownewgroup'] = true;
            }
        }
    }
    if(!isset($setting['explorer_catcreate'])){
        $data['allownewcat'] = true;
    }else{
        $data['allownewcat'] =  ($setting['explorer_catcreate'] == 1) ? true:false;
    }
    if(!isset($setting['explorer_finallydelete']) || $setting['explorer_finallydelete'] < 0){
        $data['finallydelete'] = false;
    }else{
        $data['finallydelete'] = intval($setting['explorer_finallydelete']);
    }
    return $data;
}

//增加字符串截取函数
function new_strsubstr($string,$length=1,$dot ='...')
{
    if(strlen($string) <= $length )
    {
        return $string;
    }
    else
    {
        $i = 0;
        while ($i < $length)
        {
            $stringTMP = substr($string,$i,1);
            if ( ord($stringTMP) >=224 )
            {
                $stringTMP = substr($string,$i,3);
                $i = $i + 3;
            }
            elseif( ord($stringTMP) >=192 )
            {
                $stringTMP = substr($string,$i,2);
                $i = $i + 2;
            }
            else
            {
                $i = $i + 1;
            }
            $stringLast[] = $stringTMP;
        }
        $stringLast = implode("",$stringLast);
        return $stringLast.$dot;
    }
}

/**
 * 获取需要更新的应用数量
 * @return string
 */
function get_update_app_num()
{
    $map = array();
    $map["upgrade_version"] = array("neq", "");
    $map["available"] = array("gt", "0");
    $num = DB::result_first("select COUNT(*) from %t where `available`>0 and upgrade_version!=''",array('app_market'));// C::tp_t('app_market')->where($map)->count("*");
    return $num;
}

function getimportdata($name = '', $addslashes = 0, $ignoreerror = 0,$data='') {
	global $_G;

	if(empty($data)){
		if($_GET['importtype'] == 'file') {
			$data = @implode('', file($_FILES['importfile']['tmp_name']));
			@unlink($_FILES['importfile']['tmp_name']);
		} else {
			if(!empty($_GET['importtxt'])) {
				$data = $_GET['importtxt'];
			} else {
				$data = $GLOBALS['importtxt'];

			}
		}
	}
	require_once libfile('class/xml');

	$xmldata = xml2array($data);
    $_attributes=xmlattribute($data); //item 属性获取 
	if(!is_array($xmldata) || !$xmldata) {
		if(!$ignoreerror) {
			showmessage('data_import_error', dreferer());
		} else {
			return array();
		}
	} else {
		if($name && $name != $xmldata['Title']) {
			if(!$ignoreerror) {
				showmessage('function_admin_error');
			} else {
				return array();
			}
		}
		$data = exportarray($xmldata['Data'], 0);
	}
	if($addslashes) {
		$data = daddslashes($data, 1);
	}
    if($data && $_attributes) $data["_attributes"]=$_attributes["Data"];
	return $data;
}

function exportarray($array, $method='') {
	$tmp = $array;
	if($method) {
		foreach($array as $k => $v) {
			if(is_array($v)) {
				$tmp[$k] = exportarray($v, 1);
			} else {
				$uv = unserialize($v);
				if($uv && is_array($uv)) {
					$tmp['__'.$k] = exportarray($uv, 1);
					unset($tmp[$k]);
				} else {
					$tmp[$k] = $v;
				}
			}
		}
	} else {
		foreach($array as $k => $v) {
			if(is_array($v)) {
				if(substr($k, 0, 2) == '__') {
					$tmp[substr($k, 2)] = serialize(exportarray($v, 0));
					unset($tmp[$k]);
				} else {
					$tmp[$k] = exportarray($v, 0);
				}
			} else {
				$tmp[$k] = $v;
			}
		}
	}
	return $tmp;
}
function set_space_progress($usespace,$totalspace = 0){
    if($usespace == 0 && $totalspace >= 0){
        return 0;
    }elseif($totalspace < 0){
        return -1;
    }elseif($usespace > 0 && $totalspace == 0){
        $k = 10;
        $p = 1;
        $totalspace = 1024;
        $num = floor(log10($usespace));
        if($usespace < 1024){
            $k = 1000;
        }else{
            $p = floor(log($usespace) / log(2) / 10);
            if($num == 6) $k=0.9;
            elseif($num == 7) $k=8;
            elseif($num == 8) $k=50;
            elseif($num == 9) $k=2;
            for($i = 0; $i < $p;$i++){
                $totalspace *= 200;
            }
        }

        if($p <= 0) $p =1;
        $percent = round(($usespace/$totalspace)*$p,5)*100/$k;
        if($percent > 100) $percent = 90;
    }else{
        $percent=  round(($usespace/$totalspace),2)*100;
        if($percent > 100) $percent = 100;
    }
    return $percent;

}

//ajax返回成功信息数据
function success($info="",$data=array(),$ajax=true){
	$return= array(
		'status' => 1, 
		'info' => $info ? $info : "操作成功",
		'data' =>$data
	);
	if( $ajax ){
		// 返回JSON数据格式到客户端 包含状态信息
		header('Content-Type:application/json; charset=utf-8');
		exit(json_encode($return));
	}else{
		return $return;
	}
	
}

//ajax返回错误信息数据
function error($info="",$data=array(),$ajax=true){
	$return= array(
		'status' => 0, 
		'info' => $info ? $info : "操作失败",
		'data' =>$data
	);
	if( $ajax ){
		// 返回JSON数据格式到客户端 包含状态信息
		header('Content-Type:application/json; charset=utf-8');
		exit(json_encode($return));
	}else{
		return $return;
	}
}
function dzz_userconfig_init()
{  //初始化用户信息
    global $_G;
    //建立用户设置主表
    $userconfig = array(
        'uid' => $_G['uid'],
        'applist' => array(),
        'screenlist' => array(),
        'docklist' => array(),
        'dateline' => $_G['timestamp'],
        'updatetime' => $_G['timestamp'],
        'wins' => serialize(array()),
        'perm' => 0,
        'iconview' => $_G['setting']['desktop_default']['iconview'] ? $_G['setting']['desktop_default']['iconview'] : 2,
        'taskbar' => $_G['setting']['desktop_default']['taskbar'] ? $_G['setting']['desktop_default']['taskbar'] : 'bottom',
        'iconposition' => intval($_G['setting']['desktop_default']['iconposition']),
        'direction' => intval($_G['setting']['desktop_default']['direction']),
    );
   

    //处理理默认应用;
    $apps = C::t('app_market')->fetch_all_by_default($_G['uid']);

    foreach ($apps as $appid => $app) {

        $userconfig['applist'][] = $appid;
        if ($app['position'] == 1) {
            continue;
        } elseif ($app['position'] == 2) { //桌面
            $fid = DB::result_first("select fid from " . DB::table('folder') . " where uid='{$_G[uid]}' and flag='desktop'");
        } else { //dock条
            $fid = DB::result_first("select fid from " . DB::table('folder') . " where uid='{$_G[uid]}' and flag='dock'");
        }
        if (!$fid) continue;
        if ($rid = DB::result_first("select rid from " . DB::table('resources') . " where uid='{$_G[uid]}' and oid='{$appid}' and type='app'")) {
            C::t('resources')->update_by_rid($rid, array('pfid' => $fid, 'isdelete' => 0));
            if ($app['position'] == 2) $userconfig['screenlist'][] = $rid;
            else $userconfig['docklist'][] = $rid;
        } else {
            $icoarr = array(
                'uid' => $_G['uid'],
                'username' => $_G['username'],
                'oid' => $appid,
                'name' => '',
                'type' => 'app',
                'dateline' => $_G['timestamp'],
                'pfid' => $fid,
                'ext' => '',
                'size' => 0,
            );
            if ($icoarr['rid'] = C::t('resources')->insert_data($icoarr, 1)) {
                if ($app['position'] == 2) $userconfig['screenlist'][] = $icoarr['rid'];
                else $userconfig['docklist'][] = $icoarr['rid'];
            }
        }

    }
    $userconfig['applist'] = $userconfig['applist'] ? implode(',', $userconfig['applist']) : '';
    $userconfig['screenlist'] = $userconfig['screenlist'] ? implode(',', $userconfig['screenlist']) : '';
    $userconfig['docklist'] = $userconfig['docklist'] ? implode(',', $userconfig['docklist']) : '';
	C::t('user_field')->insert($userconfig, false, true);
	if ($userconfig['applist']) C::t('app_user')->insert_by_uid($_G['uid'], $userconfig['applist'], 1);
	return C::t('user_field')->fetch($_G['uid']);
}
/*判断字符串是否是序列化后的数据*/
/* @param string $data   Value to check to see if was serialized.
 * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
 * @return bool False if not serialized and true if it was.
 */
 function is_serialized( $data, $strict = true ) {
		// if it isn't a string, it isn't serialized.
		if ( ! is_string( $data ) ) {
				return false;
		}
		$data = trim( $data );
		if ( 'N;' == $data ) {
				return true;
		}
		if ( strlen( $data ) < 4 ) {
				return false;
		}
		if ( ':' !== $data[1] ) {
				return false;
		}
		if ( $strict ) {
				$lastc = substr( $data, -1 );
				if ( ';' !== $lastc && '}' !== $lastc ) {
						return false;
				}
		} else {
				$semicolon = strpos( $data, ';' );
				$brace     = strpos( $data, '}' );
				// Either ; or } must exist.
				if ( false === $semicolon && false === $brace )
						return false;
				// But neither must be in the first X characters.
				if ( false !== $semicolon && $semicolon < 3 )
						return false;
				if ( false !== $brace && $brace < 4 )
						return false;
		}
		$token = $data[0];
		switch ( $token ) {
				case 's' :
						if ( $strict ) {
								if ( '"' !== substr( $data, -2, 1 ) ) {
										return false;
								}
						} elseif ( false === strpos( $data, '"' ) ) {
								return false;
						}
				case 'a' :
				case 'O' :
						return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
				case 'b' :
				case 'i' :
				case 'd' :
						$end = $strict ? '$' : '';
						return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}
		return false;
 }
 /**
  * 短信发送函数
  * @$param $tplsign string 模板标识
  * @$param $to number 短信接收手机号
  * @$param $params  array 拓展参数 expire 过期时间 codelength 验证码长度 gateways指定网关
  * @return 如果发送成功则返回 验证码发送时间 验证码 过期时间,如果失败返回错误信息
  * */
 function sms($tplsign,$to,$params=array('expire'=>15,'codelength'=>6)){
     $params['tplsign'] = $tplsign;
     $params['to'] = $to;
    $result = Hook::listen('sms',$params);
     return $result[0];

 }