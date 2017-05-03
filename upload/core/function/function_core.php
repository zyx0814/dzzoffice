<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

if (!function_exists('sys_get_temp_dir')) { 
  function sys_get_temp_dir() { 
    if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); } 
    if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); } 
    if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); } 
    $tempfile=tempnam(__FILE__,''); 
    if (file_exists($tempfile)) { 
      unlink($tempfile); 
      return realpath(dirname($tempfile)); 
    } 
    return null; 
  } 
} 
function dzzMD5($file,$maxchunk=100,$chunksize_first=256){
/*
  获取文件的dzzhash值
  $file:文件地址,仅支持本地文件地址；
  $maxchunk:获取多少块数据
  $chunksize_first:每块取多少字节计算md5;
  return:第一块md5和所有块的md5;
*/
  if(!is_file($file)) return false;
  $filesize=filesize($file);
  $chunk=round($filesize/$maxchunk);
  if($chunk<$chunksize_first) $chunk=$chunksize_first;
  if(!$fp=fopen($file)){
	 return false; 
  }
  $i=0;
  $arr=array();
  while(!feof($fp)){
	  fseek($fp,$chunk*$i,SEEK_SET);
	  $arr[]=md5(fread($fp,$chunksize_first));
	  $i++;
  }
  fclose($fp);
  return array($arr[0],md5(implode('',$arr)));
}
function getCode62($url) {//获取url的code62码
   $url = crc32($url);
   $x = sprintf("%u", $url);
   $show = '';
	while($x > 0) {
	  $s = $x % 62;
	  if ($s > 35) {
		$s = chr($s+61);
	  } elseif ($s > 9 && $s <=35) {
		$s = chr($s + 55);
	  }
	  $show .= $s;
	  $x = floor($x/62);
	}
	return $show;
}
function hookscriptoutput(){}
define('DZZ_CORE_FUNCTION', true);
function getOauthRedirect($url){//获取链接的auth地址
	$wx=new qyWechat(array('appid'=>getglobal('setting/CorpID'),'appsecret'=>getglobal('setting/CorpSecret')));
	return $wx->getOauthRedirect(getglobal('siteurl').'index.php?mod=system&op=wxredirect&url='.dzzencode($url));
}
function wx_deleteUser($uid){
	if(!getglobal('setting/CorpID') || !getglobal('setting/CorpSecret')) return true;
	$wx=new qyWechat(array('appid'=>getglobal('setting/CorpID'),'appsecret'=>getglobal('setting/CorpSecret'),'agentid'=>0));
	if($wx->deleteUser('dzz-'.$uid)){
		return true;
	}else{
		$message='deleteUser：errCode:'.$wx->errCode.';errMsg:'.$wx->errMsg;
		runlog('wxlog', $message);
	}
	return false;
}
function wx_updateUser($uids){
	@set_time_limit(0);
	if(!getglobal('setting/CorpID') || !getglobal('setting/CorpSecret')) return;
	$uids=(array)$uids;
	$wx=new qyWechat(array('appid'=>getglobal('setting/CorpID'),'appsecret'=>getglobal('setting/CorpSecret'),'agentid'=>0));
	$ret=0;
	$syngids=array();
	if($syngid=getglobal('setting/synorgid')){ //设置的需要同步的部门
		include_once libfile('function/organization');
		$syngids=getOrgidTree($syngid);
	}
	foreach($uids as $uid){
		if(!$user=C::t('user')->fetch($uid)) continue;
		$worgids=array();
		if($orgids=C::t('organization_user')->fetch_orgids_by_uid($uid)){
			if($syngids ){
				$orgids=array_intersect($orgids,$syngids);
			}
			if($orgids){
				foreach(C::t('organization')->fetch_all($orgids) as $value){
			 		if($value['worgid']) $worgids[]=$value['worgid'];
					else{
						if($worgid=C::t('organization')->wx_update($value['orgid'])){
							$worgids[]=$worgid;
						}
					}
				}
			}
		}
		
		
		if(!$worgids){//用户不在机构和部门中，微信中应该禁用此用户
			$data=array( "userid" => "dzz-".$user['uid'],
						"enable"=>0,
						"department"=>1,
						);
			if($wx->updateUser($data)){
				$ret+=1;
			}else{
				$message='deleteUser：errCode:'.$wx->errCode.';errMsg:'.$wx->errMsg;
				runlog('wxlog', $message);
			}
		}else{ 
			$profile=C::t('user_profile1')->fetch_all($user['uid']);
			if($wxuser=$wx->getUserInfo('dzz-'.$user['uid'])){//更新用户信息
				$data=array(
						 "userid" => "dzz-".$user['uid'],
						 "name" => $user['username'],
						 "enable"=>1,
						 //"position" => '',
						 "email" =>$user['email'],
						 "enable"=>$user['status']?0:1
					 );
				  if(array_diff($wxuser['department'],$worgids)){
					 $data['department']=$worgids;
				  }
				  if($user['phone']  && $user['phone']!=$wxuser['mobile']){
					  $data['mobile']=$user['phone'];
				  }
				  if($user['weixinid'] && $wxuser['wechat_status']==4){
					  $data['weixinid']=$user['weixinid'];
				  }
				  if($profile['telephone'] && $profile['telephone']!=$wxuser['tel']){
					  $data['tel']=$profile['telephone'];
				  }
				  if($profile['gender'] && ($profile['gender']-1)!=$wxuser['gender']){
					  $data['gender']=$profile['gender']-1;
				  }
				
				if($wx->updateUser($data)){
					$ret+=1;
				}else{
					$message='updateUser：errCode:'.$wx->errCode.';errMsg:'.$wx->errMsg;
					runlog('wxlog', $message);
				}
				$setarr=array('wechat_status'=>$wxuser['status']);
				$setarr['weixinid']=empty($wxuser['weixinid'])?$user['weixinid']:$wxuser['weixinid'];
				$setarr['phone']=empty($user['phone'])?$wxuser['phone']:$user['phone'];
				$setarr['wechat_userid']='dzz-'.$user['uid'];
				C::t('user')->update($user['uid'],$setarr);
				
			}else{ //创建用户信息
			   
				$data=array(
						 "userid" => "dzz-".$user['uid'],
						 "name" => $user['username'],
						 "department" => $worgids,
						 //"position" => '',
						 "email" =>$user['email'],
						 "weixinid" => $user['wechat'],
						 "enable"=>$user['status']?0:1
					 );
				  if($user['phone']){
					  $data['mobile']=$user['phone'];
				  }
				  if($profile['telephone']){
					  $data['tel']=$profile['telephone'];
				  }
				  if($profile['gender']){
					  $data['gender']=$profile['gender']-1;
				  }
				
				if($wx->createUser($data)){
					C::t('user')->update($user['uid'],array('wechat_userid'=>'dzz-'.$user['uid']));
					$ret+=1;
				}else{
					$message='createUser：errCode:'.$wx->errCode.';errMsg:'.$wx->errMsg;
					runlog('wxlog', $message);
				}
			}
		}
	}
	return $ret;
}
function fix_integer_overflow($size) { //处理整数溢出
	if ($size < 0) {
		$size += 2.0 * (PHP_INT_MAX + 1);
	}
	return $size;
}
function formatsize($size) {
	$prec=3;
	$size = round(abs($size));
	$units = array(0=>" B ", 1=>" KB", 2=>" MB", 3=>" GB", 4=>" TB");
	if ($size==0) return str_repeat(" ", $prec)."0$units[0]";
	$unit = min(4, floor(log($size)/log(2)/10));
	$size = $size * pow(2, -10*$unit);
	$digi = $prec - 1 - floor(log($size)/log(10));
	$size = round($size * pow(10, $digi)) * pow(10, -$digi);
	return $size.$units[$unit];
}
function url_implode($gets) {
	$arr = array();
	foreach ($gets as $key => $value) {
		if($value) {
			$arr[] = $key.'='.urlencode($value);
		}
	}
	return implode('&', $arr);
}
function getstr($string, $length=0, $in_slashes=0, $out_slashes=0, $bbcode=0, $html=0) {
	global $_G;

	$string = trim($string);
	$sppos = strpos($string, chr(0).chr(0).chr(0));
	if($sppos !== false) {
		$string = substr($string, 0, $sppos);
	}
	if($in_slashes) {
		$string = dstripslashes($string);
	}
	$string = preg_replace("/\[hide=?\d*\](.*?)\[\/hide\]/is", '', $string);
	if($html < 0) {
		$string = preg_replace("/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", ' ', $string);
	} elseif ($html == 0) {
		$string = dhtmlspecialchars($string);
	}

	if($length) {
		$string = cutstr($string, $length);
	}

	if($bbcode) {
		require_once DZZ_ROOT.'./core/class/class_bbcode.php';
		$bb = & bbcode::instance();
		$string = $bb->bbcode2html($string, $bbcode);
	}
	if($out_slashes) {
		$string = daddslashes($string);
	}
	return trim($string);
}
function getuserprofile($field) {
	global $_G;
	if(isset($_G['member'][$field])) {
		return $_G['member'][$field];
	}
	static $tablefields = array(
		'status'	=> array('regip','lastip','lastvisit','lastactivity','lastsendmail'),
		//'profile'	=> (C::t('user_profile_setting')->fetch_all_fields_by_available()),
	);
	$profiletable = '';
	foreach($tablefields as $table => $fields) {
		if(in_array($field, $fields)) {
			$profiletable = $table;
			break;
		}
	}
	if($profiletable) {

		if(is_array($_G['member']) && $_G['member']['uid']) {
			space_merge($_G['member'], $profiletable);
		} else {
			foreach($tablefields[$profiletable] as $k) {
				$_G['member'][$k] = '';
			}
		}
		return $_G['member'][$field];
	}
	return null;
}
function cpurl($type = 'parameter', $filters = array('sid', 'frames')) {
	parse_str($_SERVER['QUERY_STRING'], $getarray);
	$extra = $and = '';
	foreach($getarray as $key => $value) {
		if(!in_array($key, $filters)) {
			@$extra .= $and.$key.($type == 'parameter' ? '%3D' : '=').rawurlencode($value);
			$and = $type == 'parameter' ? '%26' : '&';
		}
	}
	return $extra;
}
function stripsearchkey($string) {
	$string = trim($string);
	$string = str_replace('*', '%', addcslashes($string, '%_'));
	return $string;
}


function system_error($message, $show = true, $save = true, $halt = true) {
	dzz_error::system_error($message, $show, $save, $halt);
}

function updatesession() {
	return C::app()->session->updatesession();
}

function setglobal($key , $value, $group = null) {
	global $_G;
	$key = explode('/', $group === null ? $key : $group.'/'.$key);
	$p = &$_G;
	foreach ($key as $k) {
		if(!isset($p[$k]) || !is_array($p[$k])) {
			$p[$k] = array();
		}
		$p = &$p[$k];
	}
	$p = $value;
	return true;
}

function getglobal($key, $group = null) {
	global $_G;
	$key = explode('/', $group === null ? $key : $group.'/'.$key);
	$v = &$_G;
	foreach ($key as $k) {
		if (!isset($v[$k])) {
			return null;
		}
		$v = &$v[$k];
	}
	return $v;
}

function getgpc($k, $type='GP') {
	$type = strtoupper($type);
	switch($type) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_GET[$k])) {
				$var = &$_GET;
			} else {
				$var = &$_POST;
			}
			break;
	}

	return isset($var[$k]) ? $var[$k] : NULL;

}

function getuserbyuid($uid, $fetch_archive = 0) {
	static $users = array();
	if(empty($users[$uid])) {
		$users[$uid] = C::t('user')->fetch($uid);
		/*if($fetch_archive === 1 && empty($users[$uid])) {
			$users[$uid] = C::t('user_archive')->fetch($uid);
		}*/
	}
	if(!isset($users[$uid]['self']) && $uid == getglobal('uid') && getglobal('uid')) {
		
	}
	if($users[$uid]['adminid']==1) $users[$uid]['self'] = 2;
	return $users[$uid];
}



function daddslashes($string, $force = 1) {
	if(is_array($string)) {
		$keys = array_keys($string);
		foreach($keys as $key) {
			$val = $string[$key];
			unset($string[$key]);
			$string[addslashes($key)] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0,$ckey_length=4) {
	//$ckey_length = 4;
	$key = md5($key != '' ? $key : getglobal('authkey'));
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ?base64_decode(substr(str_replace(array('_','-'),array('/','+'),$string), $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
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
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace(array('/','+'),array('_','-'),str_replace('=', '', base64_encode($result)));
	}
}
//key的格式以|隔开，参数支持全局函数，如地址为 index.php?mod=io&op=getStream&path=***&key=uid|setting/authkey|username
//这种格式，加密时，需要把|分割的每个参数都带上，dzzencode($string,'1|'.getglobal('setting/authkey').'|管理员',$expiry);
//如果解密时，|隔开的部分使用getglobal函数获取不到值，将会使用原值，如index.php?mod=io&op=getStream&path=***&key=xxxxx|ppppp
//解密时的key会使用原值 xxxxx|ppppp ;
function dzzencode($string,$key='',$expiry=0,$ckey_length=0){ 
	$key = md5($key != '' ? $key : getglobal('setting/authkey'));
	return base64_encode(authcode($string,'ENCODE',$key,$expiry,$ckey_length));
}
function dzzdecode($string,$key='',$ckey_length=0){
	if($key){
		$tarr=explode('|',$key);
		foreach($tarr as $key => $v){
			if(getglobal($v)) $tarr[$key]=getglobal($v);
		}
		$key=implode('|',$tarr);
	}
	$key = md5($key != '' ? $key : getglobal('setting/authkey'));
	if(!$ret=authcode(base64_decode($string),'DECODE',$key,0,$ckey_length)){
		$ret=authcode(base64_decode($string),'DECODE',$key,0,4);
	}
	return $ret;
}
function fsocketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
	$fp = '';
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}

function dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE, $position = 0) {
	require_once libfile('function/filesock');
	return _dfsockopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block, $encodetype, $allowcurl, $position);
}

function dhtmlspecialchars($string, $flags = null) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val, $flags);
		}
	} else {
		if($flags === null) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		} else {
			if(PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				if(strtolower(CHARSET) == 'utf-8') {
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

function dexit($message = '') {
	echo $message;
	output();
	exit();
}

function dheader($string, $replace = true, $http_response_code = 0) {
	$islocation = substr(strtolower(trim($string)), 0, 8) == 'location';
	if(defined('IN_MOBILE') && strpos($string, 'mobile') === false && $islocation) {
		if (strpos($string, '?') === false) {
			$string = $string.'?mobile='.IN_MOBILE;
		} else {
			if(strpos($string, '#') === false) {
				$string = $string.'&mobile='.IN_MOBILE;
			} else {
				$str_arr = explode('#', $string);
				$str_arr[0] = $str_arr[0].'&mobile='.IN_MOBILE;
				$string = implode('#', $str_arr);
			}
		}
	}
	$string = str_replace(array("\r", "\n"), array('', ''), $string);
	if(empty($http_response_code) || PHP_VERSION < '4.3' ) {
		@header($string, $replace);
	} else {
		@header($string, $replace, $http_response_code);
	}
	if($islocation) {
		exit();
	}
}

function dsetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {

	global $_G;

	$config = $_G['config']['cookie'];

	$_G['cookie'][$var] = $value;
	$var = ($prefix ? $config['cookiepre'] : '').$var;
	$_COOKIE[$var] = $value;

	if($value == '' || $life < 0) {
		$value = '';
		$life = -1;
	}

	if(defined('IN_MOBILE')) {
		$httponly = false;
	}

	$life = $life > 0 ? getglobal('timestamp') + $life : ($life < 0 ? getglobal('timestamp') - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'].'; HttpOnly' : $config['cookiepath'];

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	if(PHP_VERSION < '5.2.0') {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure);
	} else {
		setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
	}
}

function getcookie($key) {
	global $_G;
	return isset($_G['cookie'][$key]) ? $_G['cookie'][$key] : '';
}

function fileext($filename) {
	return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}

function formhash($specialadd = '') {
	global $_G;
	$hashadd = defined('IN_ADMINCP') ? 'Only For Dzz! Admin Control Panel' : '';
	return substr(md5(substr($_G['timestamp'], 0, -7).$_G['username'].$_G['uid'].$_G['authkey'].$hashadd.$specialadd), 8, 8);
}

function checkrobot($useragent = '') {
	static $kw_spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
	static $kw_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');

	$useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
	if(strpos($useragent, 'http://') === false && dstrpos($useragent, $kw_browsers)) return false;
	if(dstrpos($useragent, $kw_spiders)) return true;
	return false;
}
function checkmobile() {
	global $_G;
	$mobile = array();
	static $mobilebrowser_list =array('iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi', 'opera mini',
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

	if(dstrpos($useragent, $pad_list)) {
		return false;
	}
	if(($v = dstrpos($useragent, $mobilebrowser_list, true))){
		$_G['mobile'] = $v;
		return '2';
	}
	if(($v = dstrpos($useragent, $wmlbrowser_list))) {
		$_G['mobile'] = $v;
		return '3'; //wml版
	}
	$brower = array('mozilla', 'chrome', 'safari', 'opera', 'm3gate', 'winwap', 'openwave', 'myop');
	if(dstrpos($useragent, $brower)) return false;

	$_G['mobile'] = 'unknown';
	if(isset($_G['mobiletpl'][$_GET['mobile']])) {
		return true;
	} else {
		return false;
	}
}

function dstrpos($string, $arr, $returnvalue = false) {
	if(empty($string)) return false;
	foreach((array)$arr as $v) {
		if(strpos($string, $v) !== false) {
			$return = $returnvalue ? $v : true;
			return $return;
		}
	}
	return false;
}

function isemail($email) {
	return strlen($email) > 6 && strlen($email) <= 32 && preg_match("/^([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-]+[.][A-Za-z0-9\-.]+)$/", $email);
}

function quescrypt($questionid, $answer) {
	return $questionid > 0 && $answer != '' ? substr(md5($answer.md5($questionid)), 16, 8) : '';
}

function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	if($numeric) {
		$hash = '';
	} else {
		$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
		$length--;
	}
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

function avatar($uid, $size = 'middle', $returnsrc = FALSE, $real = FALSE, $static = FALSE, $ucenterurl = '') {
	global $_G;
	
	static $staticavatar;
	if($staticavatar === null) {
		$staticavatar = $_G['setting']['avatarmethod'];
	}

	$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
	$uid = abs(intval($uid));
	if(!$staticavatar && !$static) {
		return $returnsrc ? 'avatar.php?uid='.$uid.'&size='.$size : '<img src="avatar.php?uid='.$uid.'&size='.$size.($real ? '&type=real' : '').'" />';
	} else {
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$file = 'data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).($real ? '_real' : '').'_avatar_'.$size.'.jpg';
		return $returnsrc ? $file : '<img src="'.$file.'" onerror="this.onerror=null;this.src=\'data/avatar/noavatar_'.$size.'.gif\'" />';
	}
}
function checkLanguage(){
	global $_G;
	$uid = getglobal('uid');
	$langList = $_G['config']['output']['language_list'];
	$langSet='';
	
	/*if($_G['cookie']['language']) $langSet=$_G['cookie']['language'];
	else*/
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){// 自动侦测浏览器语言
		preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);	 
		$langSet = strtolower($matches[1]);	
		if($langSet == 'zh-hans-cn' || $langSet == 'zh-cn'){
			$langSet = 'zh-cn';
		}elseif($langSet == 'zh-tw'){
			$langSet = 'zh-tw';
		}else{
			$langSet = $matches[1];
		}
	}
	if(!in_array($langSet, array_keys($langList))) { // 非法语言参数
        $langSet = $_G['config']['output']['language'];
    }
	return $langSet;
}

function lang($langvar = null, $vars = array(), $default = null,$curpath='') {

	global $_G;
	$checkLanguage = $_G['language'];
	if($curpath){
		include DZZ_ROOT.'./'.$curpath.'/language/'.$checkLanguage.'/'.'lang.php';
		$_G['lang']['template'] = $lang;
	}else{
		if(defined('CURSCRIPT')){
			$key1 = CURSCRIPT.'_template';
		} 
		if(defined('CURSCRIPT') && defined('CURMODULE')){		
			$key2=CURSCRIPT.'_'.CURMODULE.'_template';
		} 

		if(!isset($_G['lang']['template'])) {
			$_G['lang']['template']=array();
			
			if(file_exists(DZZ_ROOT.'./core/language/'.$checkLanguage.'/'.'lang.php')){
				include DZZ_ROOT.'./core/language/'.$checkLanguage.'/'.'lang.php';
				$_G['lang']['template'] = $lang;
			}
		}

		if(isset($key1) && !isset($_G['lang'][$key1])) {		
			if(file_exists (DZZ_ROOT.'./'.CURSCRIPT.'/language/'.$checkLanguage.'/'.'lang.php')){							
				include DZZ_ROOT.'./'.CURSCRIPT.'/language/'.$checkLanguage.'/'.'lang.php';	
				$_G['lang']['template']=array_merge($_G['lang']['template'],$lang);
	
			}
		}

		if(isset($key2) && !isset($_G['lang'][$key2])) {
			if(file_exists (DZZ_ROOT.'./'.CURSCRIPT.'/'.CURMODULE.'/language/'.$checkLanguage.'/'.'lang.php')){
				
				include DZZ_ROOT.'./'.CURSCRIPT.'/'.CURMODULE.'/language/'.$checkLanguage.'/'.'lang.php';
				$_G['lang']['template']=array_merge($_G['lang']['template'],$lang);
			}
		}
		
	}
	$returnvalue = &$_G['lang'];
	
	$return = $langvar !== null ? (isset($returnvalue['template'][$langvar]) ? $returnvalue['template'][$langvar] : null) : $returnvalue['template'];
	$return = $return === null ? ($default !== null ? $default : $langvar) : $return;
	$searchs = $replaces = array();

	if($vars && is_array($vars)) {

		foreach($vars as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
	}
	
	if(is_string($return) && strpos($return, '{_G/') !== false) {
		preg_match_all('/\{_G\/(.+?)\}/', $return, $gvar);
		foreach($gvar[0] as $k => $v) {
			
			$searchs[] = $v;
			$replaces[] = getglobal($gvar[1][$k]);
		}
	}

	$return = str_replace($searchs, $replaces, $return);
	return $return;
}

function template($file, $tpldir = '' ) {
	global $_G;
	static $tplrefresh, $timestamp, $targettplname;

	$file .= !empty($_G['inajax']) && ($file == 'common/header' || $file == 'common/footer') ? '_ajax' : '';

	$tplfile = $file;

	if($tplrefresh === null) {
		$tplrefresh = getglobal('config/output/tplrefresh');
		$timestamp = getglobal('timestamp');
	}

	if(empty($timecompare) || $tplrefresh == 1 || ($tplrefresh > 1 && !($timestamp % $tplrefresh))) {
			require_once DZZ_ROOT.'/core/class/class_template.php';
			$template = new template();
			$cachefile = $template->fetch_template($tplfile, $tpldir);
			return $cachefile;
	}
	return FALSE;

}

function dsign($str, $length = 16){
	return substr(md5($str.getglobal('security/authkey')), 0, ($length ? max(8, $length) : 16));
}

function modauthkey($id) {
	return md5(getglobal('username').getglobal('uid').getglobal('authkey').substr(TIMESTAMP, 0, -7).$id);
}


function loadcache($cachenames, $force = false) {
	global $_G;
	static $loadedcache = array();
	$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
	$caches = array();
	foreach ($cachenames as $k) {
		if(!isset($loadedcache[$k]) || $force) {
			$caches[] = $k;
			$loadedcache[$k] = true;
		}
	}

	if(!empty($caches)) {
		
		$cachedata = C::t('syscache')->fetch_all($caches);
		foreach($cachedata as $cname => $data) {
			if($cname == 'setting') {
				$_G['setting'] = $data;
			} elseif($cname == 'usergroup_'.$_G['groupid']) {
				$_G['cache'][$cname] = $_G['group'] = $data;
			} else {
				$_G['cache'][$cname] = $data;
			}
		}
	}
	return true;
}

function dgmdate($timestamp, $format = 'dt', $timeoffset = '9999', $uformat = '') {
	global $_G;
	$format == 'u' && !$_G['setting']['dateconvert'] && $format = 'dt';
	static $dformat, $tformat, $dtformat, $offset, $lang;
	if($dformat === null) {
		$dformat = getglobal('setting/dateformat');
		$tformat = getglobal('setting/timeformat');
		$dtformat = $dformat.' '.$tformat;
		$offset = getglobal('member/timeoffset');
		$lang = lang('date');
	}
	$timeoffset = $timeoffset == 9999 ? $offset : $timeoffset;
	$timestamp += $timeoffset * 3600;
	$format = empty($format) || $format == 'dt' ? $dtformat : ($format == 'd' ? $dformat : ($format == 't' ? $tformat : $format));
	if($format == 'u') {
		$todaytimestamp = TIMESTAMP - (TIMESTAMP + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
		$s = gmdate(!$uformat ? $dtformat : $uformat, $timestamp);
		$time = TIMESTAMP + $timeoffset * 3600 - $timestamp;
		if($timestamp >= $todaytimestamp) {
			if($time > 3600) {
				$return = intval($time / 3600).'&nbsp;'.$lang['hour'].$lang['before'];
			} elseif($time > 1800) {
				$return = $lang['half'].$lang['hour'].$lang['before'];
			} elseif($time > 60) {
				$return = intval($time / 60).'&nbsp;'.$lang['min'].$lang['before'];
			} elseif($time > 0) {
				$return = $time.'&nbsp;'.$lang['sec'].$lang['before'];
			} elseif($time == 0) {
				$return = $lang['now'];
			} else {
				$return = $s;
			}
			if($time >=0 && !defined('IN_MOBILE')) {
				$return = '<span  title="'.$s.'">'.$return.'</span>';
			}
		} elseif(($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
			if($days == 0) {
				$return = $lang['yday'].'&nbsp;'.gmdate($tformat, $timestamp);
			} elseif($days == 1) {
				$return = $lang['byday'].'&nbsp;'.gmdate($tformat, $timestamp);
			} else {
				$return = ($days + 1).'&nbsp;'.$lang['day'].$lang['before'];
			}
			if(!defined('IN_MOBILE')) {
				$return = '<span  title="'.$s.'">'.$return.'</span>';
			}
		} else {
			$return = gmdate('Y-m-d', $timestamp).'&nbsp;<span class="hidden-xs" title="'.$s.'">'.gmdate('H:s', $timestamp).'</span>'; 
		}
		return $return;
	} else {
		return gmdate($format, $timestamp);
	}
}

function dmktime($date) {
	if(strpos($date, '-')) {
		$time = explode('-', $date);
		return mktime(0, 0, 0, $time[1], $time[2], $time[0]);
	}
	return 0;
}

function dnumber($number) {
	return abs($number) > 10000 ? '<span title="'.$number.'">'.intval($number / 10000).lang('10k').'</span>' : $number;
}

function savecache($cachename, $data) {
	C::t('syscache')->insert($cachename, $data);
}

function save_syscache($cachename, $data) {
	savecache($cachename, $data);
}



function dimplode($array) {
	if(!empty($array)) {
		$array = array_map('addslashes', $array);
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return 0;
	}
}

function libfile($libname, $folder = '' ,$curpath='') { //$path 标志是那个模块内的,不指定则调用默认当前模块和核心模块的
	$libpath = '';
	if(strstr($libname, '/')) {
		list($pre, $name) = explode('/', $libname);
		$path = "{$pre}/{$pre}_{$name}";
	} else {
		$path = "{$libname}";
	}
	if($curpath){
		$libpath=DZZ_ROOT.$curpath.'/'.$path.'.php';
	}else{
		if($folder){
			$libpath0 = DZZ_ROOT.'./core/'.$folder;
			if(defined('CURSCRIPT')) {
				$libpath1= DZZ_ROOT.'./'.CURSCRIPT.'/'.$folder;
				if(defined('CURMODULE')) $libpath2=DZZ_ROOT.'/'.CURSCRIPT.'/'.CURMODULE.'/'.$folder;
			}				
		}else{
			 $libpath0 = DZZ_ROOT.'./core';
			 if(defined('CURSCRIPT')) {
				$libpath1= DZZ_ROOT.'./'.CURSCRIPT;
				if(defined('CURMODULE')) $libpath2=DZZ_ROOT.'/'.CURSCRIPT.'/'.CURMODULE;
			}
		}
		if(isset($libpath0) && file_exists ($libpath0.'/'.$path.'.php')) {
			$libpath=$libpath0.'/'.$path.'.php';
		}elseif(isset($libpath2) && file_exists ($libpath2.'/'.$path.'.php')) {
			$libpath=$libpath2.'/'.$path.'.php';
		}elseif((isset($libpath1) && file_exists ($libpath1.'/'.$path.'.php'))) {
			$libpath=$libpath1.'/'.$path.'.php';
		}
	}
	/*if(empty($libpath)){
		exit('dfdfd');
		return false;
	}*/
	
	return $libpath;
}

function dstrlen($str) {
	if(strtolower(CHARSET) != 'utf-8') {
		return strlen($str);
	}
	$count = 0;
	for($i = 0; $i < strlen($str); $i++){
		$value = ord($str[$i]);
		if($value > 127) {
			$count++;
			if($value >= 192 && $value <= 223) $i++;
			elseif($value >= 224 && $value <= 239) $i = $i + 2;
			elseif($value >= 240 && $value <= 247) $i = $i + 3;
	    	}
    		$count++;
	}
	return $count;
}

function cutstr($string, $length, $dot = ' ...') {
	if(strlen($string) <= $length) {
		return $string;
	}

	$pre = chr(1);
	$end = chr(1);
	$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), $string);

	$strcut = '';
	if(strtolower(CHARSET) == 'utf-8') {

		$n = $tn = $noc = 0;
		while($n < strlen($string)) {

			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}

			if($noc >= $length) {
				break;
			}

		}
		if($noc > $length) {
			$n -= $tn;
		}

		$strcut = substr($string, 0, $n);

	} else {
		$_length = $length - 1;
		for($i = 0; $i < $length; $i++) {
			if(ord($string[$i]) <= 127) {
				$strcut .= $string[$i];
			} else if($i < $_length) {
				$strcut .= $string[$i].$string[++$i];
			}
		}
	}

	$strcut = str_replace(array($pre.'&'.$end, $pre.'"'.$end, $pre.'<'.$end, $pre.'>'.$end), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

	$pos = strrpos($strcut, chr(1));
	if($pos !== false) {
		$strcut = substr($strcut,0,$pos);
	}
	return $strcut.$dot;
}

function dstripslashes($string) {
	if(empty($string)) return $string;
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dstripslashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function aidencode($aid, $type = 0, $tid = 0) {
	global $_G;
	$s = !$type ? $aid.'|'.substr(md5($aid.md5($_G['config']['security']['authkey']).TIMESTAMP.$_G['uid']), 0, 8).'|'.TIMESTAMP.'|'.$_G['uid'].'|'.$tid : $aid.'|'.md5($aid.md5($_G['config']['security']['authkey']).TIMESTAMP).'|'.TIMESTAMP;
	return rawurlencode(base64_encode($s));
}


function output() {

	global $_G;


	if(defined('DZZ_OUTPUTED')) {
		return;
	} else {
		define('DZZ_OUTPUTED', 1);
	}
	if($_G['config']['rewritestatus']) {
		$content = ob_get_contents();
		$content = output_replace($content);
		ob_end_clean();
		$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
		echo $content;
	}
	if(defined('DZZ_DEBUG') && DZZ_DEBUG && @include(libfile('function/debug'))) {
		function_exists('debugmessage') && debugmessage();
	}
}
function output_replace($content) {
	global $_G;
	if(defined('IN_ADMINCP')) return $content;
	if(!empty($_G['setting']['output']['str']['search'])) {
		/*if(empty($_G['setting']['domain']['app']['default'])) {
			$_G['setting']['output']['str']['replace'] = str_replace('{CURHOST}', $_G['siteurl'], $_G['setting']['output']['str']['replace']);
		}*/
		$content = str_replace($_G['setting']['rewrite']['str']['search'], $_G['setting']['rewrite']['str']['replace'], $content);
	}
	if(!empty($_G['config']['rewrite']['preg']['search'])) {
		$content = preg_replace($_G['config']['rewrite']['preg']['search'], $_G['config']['rewrite']['preg']['replace'], $content);
	}
	
	return $content;
}

function output_ajax() {
	global $_G;
	
	$s = ob_get_contents();
	ob_end_clean();
	$s = preg_replace("/([\\x01-\\x08\\x0b-\\x0c\\x0e-\\x1f])+/", ' ', $s);
	$s = str_replace(array(chr(0), ']]>'), array(' ', ']]&gt;'), $s);
	if(defined('DZZ_DEBUG') && DZZ_DEBUG && @include(libfile('function/debug'))) {
		function_exists('debugmessage') && $s .= debugmessage(1);
	}
	
	$havedomain =isset($_G['setting']['domain']['app'])?implode('', $_G['setting']['domain']['app']):'';
	if((isset($_G['setting']['rewritestatus']) && $_G['setting']['rewritestatus']) || !empty($havedomain)) {
        $s = output_replace($s);
	}
	return $s;
}


function debug($var = null, $vardump = false) {
	echo '<pre>';
	$vardump = empty($var) ? true : $vardump;
	if($vardump) {
		var_dump($var);
	} else {
		print_r($var);
	}
	exit();
}

function debuginfo() {
	global $_G;
	if(getglobal('config/debug')) {
		$db = & DB::object();
		$_G['debuginfo'] = array(
		    'time' => number_format((microtime(true) - $_G['starttime']), 6),
		    'queries' => $db->querynum,
		    'memory' => ucwords(C::memory()->type)
		    );
		if($db->slaveid) {
			$_G['debuginfo']['queries'] = 'Total '.$db->querynum.', Slave '.$db->slavequery;
		}
		return TRUE;
	} else {
		return FALSE;
	}
}

function check_seccode($value, $idhash) {
	return helper_form::check_seccode($value, $idhash);
}

function check_secqaa($value, $idhash) {
	return helper_form::check_secqaa($value, $idhash);
}

function showmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
	require_once libfile('function/message');
	return dshowmessage($message, $url_forward, $values, $extraparam, $custom);
}

function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
	if(!getgpc($var)) {
		return FALSE;
	} else {
		return helper_form::submitcheck($var, $allowget, $seccodecheck, $secqaacheck);
	}
}

function multi($num, $perpage, $curpage, $mpurl, $classname='', $maxpages = 0, $page = 5, $autogoto = FALSE, $simple = FALSE, $jsfunc = FALSE) {
	return $num > $perpage ? helper_page::multi($num, $perpage, $curpage, $mpurl, $classname,$maxpages, $page, $autogoto, $simple, $jsfunc) : '';
}

function simplepage($num, $perpage, $curpage, $mpurl) {
	return helper_page::simplepage($num, $perpage, $curpage, $mpurl);
}

function censor($message) {
	$censor = dzz_censor::instance();
	return $censor->replace($message);
}

function space_merge(&$values, $tablename, $isarchive = false) {
	global $_G;

	$uid = empty($values['uid'])?$_G['uid']:$values['uid'];
	$var = "user_{$uid}_{$tablename}";
	if($uid) {
		$ext = '';//$isarchive ? '_archive' :'' ;
		if(!isset($_G[$var])) {
			if(($_G[$var] = C::t('user_'.$tablename.$ext)->fetch($uid)) !== false) {
				//C::t('user_'.$tablename.$ext)->insert(array('uid'=>$uid));
				
				if($tablename == 'field') {
					$_G['setting']['privacy'] = empty($_G['setting']['privacy']) ? array() : (is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : dunserialize($_G['setting']['privacy']));
					$_G[$var]['privacy'] = empty($_G[$var]['privacy'])? array() : is_array($_G[$var]['privacy']) ? $_G[$var]['privacy'] : dunserialize($_G[$var]['privacy']);
				}elseif($tablename=='profile1'){
					if($_G[$var]['department']){
						$_G[$var]['department_tree']=C::t('organization')->getPathByOrgid(intval($_G[$var]['department']));
					}else{
						$_G[$var]['department_tree']=lang('please_select_a_organization_or_department');
					}
				}
			}else{
				$_G[$var] = array();
			}
		}
		$values = array_merge($values, $_G[$var]);
	}
}

function runlog($file, $message, $halt=0) {
	helper_log::runlog($file, $message, $halt);
}


function dmkdir($dir, $mode = 0777, $makeindex = TRUE){
	if(!is_dir($dir)) {
		dmkdir(dirname($dir), $mode, $makeindex);
		@mkdir($dir, $mode);
		if(!empty($makeindex)) {
			@touch($dir.'/index.html'); @chmod($dir.'/index.html', 0777);
		}
	}
	return true;
}

function dreferer($default = '') {
	global $_G;

	$default ='';
	$_G['referer'] = !empty($_GET['referer']) ? $_GET['referer'] : (isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'');
	$_G['referer'] = substr($_G['referer'], -1) == '?' ? substr($_G['referer'], 0, -1) : $_G['referer'];

	if(strpos($_G['referer'], 'user.php?mod=logging')) {
		$_G['referer'] = $default;
	}
	$_G['referer'] = dhtmlspecialchars($_G['referer'], ENT_QUOTES);
	$_G['referer'] = str_replace('&amp;', '&', $_G['referer']);
	$reurl = parse_url($_G['referer']);
	
	if($reurl['port']) $reurl['host'].=':'.$reurl['port'];
	if(!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.'.$_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.'.$reurl['host']))) {
		$_G['referer'] = 'index.php';
		
	} elseif(empty($reurl['host'])) {
		$_G['referer'] = $_G['siteurl'].'./'.$_G['referer'];
	}

	return strip_tags($_G['referer']);
}



function diconv($str, $in_charset, $out_charset = CHARSET, $ForceTable = FALSE) {
	global $_G;

	$in_charset = strtoupper($in_charset);
	$out_charset = strtoupper($out_charset);

	if(empty($str) || $in_charset == $out_charset) {
		return $str;
	}

	$out = '';

	if(!$ForceTable) {
		if(function_exists('iconv')) {
			$out = iconv($in_charset, $out_charset.'//IGNORE', $str);
		} elseif(function_exists('mb_convert_encoding')) {
			$out = mb_convert_encoding($str, $out_charset, $in_charset);
		}
	}

	if($out == '') {
		$chinese = new Chinese($in_charset, $out_charset, true);
		$out = $chinese->Convert($str);
	}

	return $out;
}


function renum($array) {
	$newnums = $nums = array();
	foreach ($array as $id => $num) {
		$newnums[$num][] = $id;
		$nums[$num] = $num;
	}
	return array($nums, $newnums);
}

function sizecount($size) {
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}

function swapclass($class1, $class2 = '') {
	static $swapc = null;
	$swapc = isset($swapc) && $swapc != $class1 ? $class1 : $class2;
	return $swapc;
}

function writelog($file, $log) {
	helper_log::writelog($file, $log);
}

function getstatus($status, $position) {
	$t = $status & pow(2, $position - 1) ? 1 : 0;
	return $t;
}

function setstatus($position, $value, $baseon = null) {
	$t = pow(2, $position - 1);
	if($value) {
		$t = $baseon | $t;
	} elseif ($baseon !== null) {
		$t = $baseon & ~$t;
	} else {
		$t = ~$t;
	}
	return $t & 0xFFFF;
}


function memory($cmd, $key='', $value='', $ttl = 0, $prefix = '') {
	if($cmd == 'check') {
		return  C::memory()->enable ? C::memory()->type : '';
	} elseif(C::memory()->enable && in_array($cmd, array('set', 'get', 'rm', 'inc', 'dec'))) {
		if(defined('DZZ_DEBUG') && DZZ_DEBUG) {
			if(is_array($key)) {
				foreach($key as $k) {
					C::memory()->debug[$cmd][] = ($cmd == 'get' || $cmd == 'rm' ? $value : '').$prefix.$k;
				}
			} else {
				C::memory()->debug[$cmd][] = ($cmd == 'get' || $cmd == 'rm' ? $value : '').$prefix.$key;
			}
		}
		switch ($cmd) {
			case 'set': return C::memory()->set($key, $value, $ttl, $prefix); break;
			case 'get': return C::memory()->get($key, $value); break;
			case 'rm': return C::memory()->rm($key, $value); break;
			case 'inc': return C::memory()->inc($key, $value ? $value : 1); break;
			case 'dec': return C::memory()->dec($key, $value ? $value : -1); break;
		}
	}
	return null;
}

function ipaccess($ip, $accesslist) {
	return preg_match("/^(".str_replace(array("\r\n", ' '), array('|', ''), preg_quote($accesslist, '/')).")/", $ip);
}

function ipbanned($onlineip) {
	global $_G;

	if($_G['setting']['ipaccess'] && !ipaccess($onlineip, $_G['setting']['ipaccess'])) {
		return TRUE;
	}

	loadcache('ipbanned');
	if(empty($_G['cache']['ipbanned'])) {
		return FALSE;
	} else {
		if($_G['cache']['ipbanned']['expiration'] < TIMESTAMP) {
			require_once libfile('function/cache');
			updatecache('ipbanned');
		}
		return preg_match("/^(".$_G['cache']['ipbanned']['regexp'].")$/", $onlineip);
	}
}


function sysmessage($message) {
	helper_sysmessage::show($message);
}

function getexpiration() {
	global $_G;
	$date = getdate($_G['timestamp']);
	return mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']) + 86400;
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}


function getimgthumbname($fileStr, $extend='.thumb.jpg', $holdOldExt=true) {
	if(empty($fileStr)) {
		return '';
	}
	if(!$holdOldExt) {
		$fileStr = substr($fileStr, 0, strrpos($fileStr, '.'));
	}
	$extend = strstr($extend, '.') ? $extend : '.'.$extend;
	return $fileStr.$extend;
}


function dintval($int, $allowarray = false) {
	$ret = intval($int);
	if($int == $ret || !$allowarray && is_array($int)) return $ret;
	if($allowarray && is_array($int)) {
		foreach($int as &$v) {
			$v = dintval($v, true);
		}
		return $int;
	} elseif($int <= 0xffffffff) {
		$l = strlen($int);
		$m = substr($int, 0, 1) == '-' ? 1 : 0;
		if(($l - $m) === strspn($int,'0987654321', $m)) {
			return $int;
		}
	}
	return $ret;
}

function strhash($string, $operation = 'DECODE', $key = '') {
	$key = md5($key != '' ? $key : getglobal('authkey'));
	if($operation == 'DECODE') {
		$hashcode = gzuncompress(base64_decode(($string)));
		$string = substr($hashcode, 0, -16);
		$hash = substr($hashcode, -16);
		unset($hashcode);
	}

	$vkey = substr(md5($string.substr($key, 0, 16)), 4, 8).substr(md5($string.substr($key, 16, 16)), 18, 8);

	if($operation == 'DECODE') {
		return $hash == $vkey ? $string : '';
	}

	return base64_encode(gzcompress($string.$vkey));
}

function dunserialize($data) {
	if(($ret = unserialize($data)) === false) {
		$ret = unserialize(stripslashes($data));
	}
	return $ret;
}

function browserversion($type) {
	static $return = array();
	static $types = array('ie' => 'msie', 'firefox' => '', 'chrome' => '', 'opera' => '', 'safari' => '', 'mozilla' => '', 'webkit' => '', 'maxthon' => '', 'qq' => 'qqbrowser');
	if(!$return) {
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$other = 1;
		foreach($types as $i => $v) {
			$v = $v ? $v : $i;
			if(strpos($useragent, $v) !== false) {
				preg_match('/'.$v.'(\/|\s)([\d\.]+)/i', $useragent, $matches);
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
function removedirectory($dirname, $keepdir = FALSE ,$time=0) {
	$dirname = str_replace(array( "\n", "\r", '..'), array('', '', ''), $dirname);

	if(!is_dir($dirname)) {
		return FALSE;
	}
	$handle = opendir($dirname);
	while(($file = readdir($handle)) !== FALSE) {
		if($file != '.' && $file != '..') {
			$dir = $dirname . DIRECTORY_SEPARATOR . $file;
			$mtime=filemtime($dir);
			is_dir($dir) ? removedir($dir) : (((TIMESTAMP-$mtime)>$time)? unlink($dir):'');
		}
	}
	closedir($handle);
	return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}

?>
