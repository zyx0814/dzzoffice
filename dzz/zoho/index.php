<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}

$app = C::t('app_market') -> fetch_by_identifier('zoho');
$app['extra'] = unserialize($app['extra']);
$ZohoAPIKey = $app['extra']['ZohoAPIKey'];
if (empty($ZohoAPIKey))
	showmessage('ZohoAPIKey_empty_cannot_install');

$path = dzzdecode(rawurldecode($_GET['path']));
$ismobile = helper_browser::ismobile();
if ($ismobile) {
	$patharr = explode(':', $path);
	if ($patharr[0] == 'ftp') {
		$stream = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=getStream&path=' . rawurldecode($_GET['path']);
	} else {
		$stream = IO::getFileUri($path);
		$stream = str_replace('-internal.aliyuncs.com', '.aliyuncs.com', $stream);
	}

	//转向地址按您的office web app 要求改写；
	header("location: http://view.officeapps.live.com/op/view.aspx?src=" . urlencode($stream));
	exit();
}
if ($_GET['do'] == 'savefile') {
	$path = dzzdecode($_GET['path']);
	$cache = './cache/' . md5($path) . '.tmp';
	$_G['uid'] = intval($_GET['id']);
	$tmp_filename = $_FILES['content']['tmp_name'];
	$msg = '';
	$upload_status = move_uploaded_file($tmp_filename, $_G['setting']['attachdir'] . $cache);
	if (!$upload_status) {
		$msg = 'save failure!';
	}
	$content = file_get_contents($_G['setting']['attachdir'] . $cache);
	if (!$msg && ($re = IO::setFileContent($path, $content, true))) {
		if ($re['error'])
			$msg = $re['error'];
		@unlink($_G['setting']['attachdir'] . $cache);
	}
	if ($msg) {
		@header('HTTP/1.1 500 Not Found');
		@header('Status: 500 Not Found');
		exit();
	} else {
		@header('HTTP/1.1 200 Not Found');
		exit();
	}
} elseif ($_GET['do'] == 'send') {

	$docexts = array('doc', 'docx', 'rtf', 'odt', 'htm', 'html', 'txt');
	$sheetexts = array('xls', 'xlsx', 'ods', 'sxc', 'csv', 'tsv');
	$showexts = array('ppt', 'pptx', 'pps', 'ppsx', 'odp', 'sxi');

	$data = IO::getMeta($path);
	if (!perm_check::checkperm('edit', $data)) {
		$mode = 'collabview';
	} else {
		$mode = 'collabedit';
	}
	$posturl = '';
	if (in_array($data['ext'], $docexts)) {
		$posturl = 'https://writer.zoho.com.cn/remotedoc.im';
	} elseif (in_array($data['ext'], $sheetexts)) {
		$posturl = 'https://sheet.zoho.com.cn/remotedoc.im';
	} elseif (in_array($data['ext'], $showexts)) {
		$posturl = 'https://show.zoho.com.cn/remotedoc.im';
	} else {
		exit(json_encode(array('error' => lang('unsupported_media_type'))));
	}
	$stream = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=getStream&path=' . dzzencode($path);
	$saveurl = $_G['siteurl'] . DZZSCRIPT . '?mod=zoho&do=savefile&path=' . dzzencode($path);
	$lockfile = $_G['setting']['attachdir'] . 'cache/lock_zoho_' . md5($path) . '.lock';
	$post_data = array('apikey' => $ZohoAPIKey, 'url' => $stream, 'saveurl' => $saveurl, 'output' => 'url', 'mode' => $mode, 'filename' => $data['name'], 'lang' => 'zh', 'id' => md5($path), 'username' => $_G['username'], 'format' => $data['ext'], );
	if (is_file($lockfile) && (TIMESTAMP - filemtime($lockfile) < 60 * 60 * 10)) {//设置10小时过期时间
		if ($documentid = file_get_contents($lockfile)) {
			$post_data['documentid'] = $documentid;
		}
	}
	$ret = (getConvertUrl($posturl, $post_data));
	if ($ret['documentid']) {
		file_put_contents($lockfile, $ret['documentid']);
	}
	exit(json_encode($ret));
}
include  template('zoho');
function getConvertUrl($posturl, $post_data) {
	//CURLOPT_URL 是指提交到哪里？相当于表单里的“action”指定的路径
	//$url = "http://local.jumei.com/DemoIndex/curl_pos/";
	//$posturl.='?'.http_build_query($post_data);
	$ch = curl_init();
	//    设置变量
	curl_setopt($ch, CURLOPT_URL, $posturl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//执行结果是否被返回，0是返回，1是不返回
	//curl_setopt($ch, CURLOPT_HEADER, 0);//参数设置，是否显示头部信息，1为显示，0为不显示

	//伪造网页来源地址,伪造来自百度的表单提交
	//curl_setopt($ch, CURLOPT_REFERER, '');

	//表单数据，是正规的表单设置值为非0
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	//设置curl执行超时时间最大是多少

	//使用数组提供post数据时，CURL组件大概是为了兼容@filename这种上传文件的写法，
	//默认把content_type设为了multipart/form-data。虽然对于大多数web服务器并
	//没有影响，但是还是有少部分服务器不兼容。本文得出的结论是，在没有需要上传文件的
	//情况下，尽量对post提交的数据进行http_build_query，然后发送出去，能实现更好的兼容性，更小的请求数据包。
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

	//执行并获取结果
	if (!$r = curl_exec($ch)) {
		return ( array('error' => curl_error($ch)));
	}
	curl_close($ch);
	$arr = preg_split("/\n/", $r);
	$ret = array();
	foreach ($arr as $value) {
		if ($value) {
			$temp = explode('=', $value);
			$key = $temp[0];
			unset($temp[0]);
			$ret[$key] = implode('=', $temp);
		}
	}
	if ($ret['RESULT'] == 'TRUE') {
		return ( array('msg' => 'success', 'url' => $ret['URL'], 'documentid' => $ret['DOCUMENTID']));
	} elseif ($ret['ERROR_CODE']) {
		return ( array('error' => "ERROR_CODE:" . $ret['ERROR_CODE'] . ' ' . $ret['WARNING']));
	} else {
		return ( array('error' => $r, 'error_code' => 500));
	}
}
?>
