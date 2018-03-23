<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

function build_cache_setting() {
	global $_G;

	$skipkeys = array('backupdir','custombackup');
	$serialized = array('verify','unRunExts','iconview','storage','reginput', 'memory',   'secqaa', 'sitemessage','disallowfloat', 
		'seccodedata', 'strongpw','upgrade','desktop_default','loginset','at_range','thumbsize');

	$data = array();

	foreach(C::t('setting')->fetch_all_not_key($skipkeys) as $setting) {
		if($setting['skey'] == 'attachdir') {
			$setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
			$setting['svalue'] = str_replace('\\', '/', substr($setting['svalue'], 0, 2) == './' ? DZZ_ROOT.$setting['svalue'] : $setting['svalue']);
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
		} elseif($setting['skey'] == 'attachurl') {
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
	
		} elseif(in_array($setting['skey'], $serialized)) {
			$setting['svalue'] = @dunserialize($setting['svalue'], $setting['skey']);
			if($setting['skey'] == 'search') {
				foreach($setting['svalue'] as $key => $val) {
					foreach($val as $k => $v) {
						$setting['svalue'][$key][$k] = max(0, intval($v));
					}
				}
			}
		}
		$_G['setting'][$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
	}

	include_once DZZ_ROOT.'./core/core_version.php';
	$_G['setting']['version'] = $data['version'] = CORE_VERSION;

	$data['sitemessage']['time'] = !empty($data['sitemessage']['time']) ? $data['sitemessage']['time'] * 1000 : 0;
	
	$data['disallowfloat'] = is_array($data['disallowfloat']) ? implode('|', $data['disallowfloat']) : '';

	if(!$data['imagelib']) unset($data['imageimpath']);
	
	$data['iconview']=C::t('iconview')->fetch_all();
	

	$data['seccodedata'] = is_array($data['seccodedata']) ? $data['seccodedata'] : array();
	if($data['seccodedata']['type'] == 2) {
		if(extension_loaded('ming')) {
			unset($data['seccodedata']['background'], $data['seccodedata']['adulterate'],
			$data['seccodedata']['ttf'], $data['seccodedata']['angle'],
			$data['seccodedata']['color'], $data['seccodedata']['size'],
			$data['seccodedata']['animator']);
		} else {
			$data['seccodedata']['animator'] = 0;
		}
	} elseif($data['seccodedata']['type'] == 99) {
		$data['seccodedata']['width'] = 50;
		$data['seccodedata']['height'] = 34;
	}

	$data['watermarktype'] = !empty($data['watermarktype']) ? dunserialize($data['watermarktype']) : array();
	$data['watermarktext'] = !empty($data['watermarktext']) ? dunserialize($data['watermarktext']) : array();
	foreach($data['watermarktype'] as $k => $v) {
		if($data['watermarktype'][$k] == 'text' && $data['watermarktext']['text'][$k]) {
			if($data['watermarktext']['text'][$k] && strtoupper(CHARSET) != 'UTF-8') {
				$data['watermarktext']['text'][$k] = diconv($data['watermarktext']['text'][$k], CHARSET, 'UTF-8', true);
			}
			$data['watermarktext']['text'][$k] = bin2hex($data['watermarktext']['text'][$k]);
			if(file_exists('static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k];
			} elseif(file_exists('static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k];
			} else {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/'.$data['watermarktext']['fontpath'][$k];
			}
			$data['watermarktext']['color'][$k] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', function($matches) { return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]); }, $data['watermarktext']['color'][$k]);
			$data['watermarktext']['shadowcolor'][$k] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', function($matches) { return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]); }, $data['watermarktext']['shadowcolor'][$k]);
			
			
		} else {
			$data['watermarktext']['text'][$k] = '';
			$data['watermarktext']['fontpath'][$k] = '';
			$data['watermarktext']['color'][$k] = '';
			$data['watermarktext']['shadowcolor'][$k] = '';
		}
	}
	if(!$data['jspath']) {
		$data['jspath'] = 'static/js/';
	}

	
	$reginputbwords = array('username', 'password', 'password2', 'email');
	if(in_array($data['reginput']['username'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['username'])) {
		$data['reginput']['username'] = random(6);
	}
	if(in_array($data['reginput']['password'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password'])) {
		$data['reginput']['password'] = random(6);
	}
	if(in_array($data['reginput']['password2'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password2'])) {
		$data['reginput']['password2'] = random(6);
	}
	if(in_array($data['reginput']['email'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['email'])) {
		$data['reginput']['email'] = random(6);
	}

	$data['verhash']=random(3);
	
	$data['output'] = $output;
	
	savecache('setting', $data);
	$_G['setting'] = $data;
}

function parsehighlight($highlight) {
	if($highlight) {
		$colorarray = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
		$string = sprintf('%02d', $highlight);
		$stylestr = sprintf('%03b', $string[0]);

		$style = ' style="';
		$style .= $stylestr[0] ? 'font-weight: bold;' : '';
		$style .= $stylestr[1] ? 'font-style: italic;' : '';
		$style .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$style .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
		$style .= '"';
	} else {
		$style = '';
	}
	return $style;
}

?>
