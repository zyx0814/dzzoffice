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
$icoid = $_GET['icoid'];
if (isset($_GET['do']) && $_GET['do'] == 'autosave') {
	if (!$path = dzzdecode($_GET['path'])) {
		exit(json_encode(array('msg' => lang('parameter_error'))));
	}
	$msg = array();
	$message = $_GET['message'];
	try {
		if ($_GET['code'])
			$message = diconv($message, CHARSET, $_GET['code']);
		$icoarr = IO::setFileContent($path, $message);
		if ($icoarr) {
			if ($icoarr['error']) {
				echo json_encode(array('msg' => $icoarr['error']));
				exit();
			} else {
				echo json_encode(array('msg' => 'success', 'icodata' => $icoarr));
				exit();
			}
		} else {
			echo json_encode(array('msg' => lang('save_unsuccess')));
			exit();
		}

	} catch(Exception $e) {
		//var_dump($e);
		echo json_encode(array('msg' => $e -> getMessage()));
		exit();
	}
} elseif (strpos($icoid, 'preview') !== false) {//此处兼容feed内文本文档的查看
	$path = dzzdecode($_GET['path']);
	$isadmin = 0;
	//无权限
	$str = (IO::getFileContent($path));
	require_once DZZ_ROOT . './dzz/class/class_encode.php';
	$p = new Encode_Core();
	$code = $p -> get_encoding($str);
	if ($code)
		$str = diconv($str, $code, CHARSET);
	$str = htmlspecialchars($str);
	include template('textviewer');
} else {

	if (!$path = dzzdecode($_GET['path'])) {
		showmessage('parameter_error');
	}

	$dpath = dzzencode($path);
	$error = '';
	$table = '';

	$icoarr = IO::getMeta($path);

	$maxputsize = 0;
	//get_config_bytes(ini_get('post_max_size'));
	if (!$maxputsize)
		$maxputsize = 2000000;
	if ($icoarr['size'] > $maxputsize) {
		$url = DZZSCRIPT . '?mod=textviewer&path=' . dzzencode($path);
		header("Location: $url");
		exit();
	}
	if (isset($icoarr['error'])) {
		exit($icoarr['error']);
	}
	//根据ext获取加载codemirror的mode；
	$ext = isset($icoarr['ext']) ? $icoarr['ext'] : '';
	require_once DZZ_ROOT . './dzz/document/codemirror/exttomodes.php';
	$mode = isset($modes[$ext]) ? $modes[$ext] : '';
	//获取文件地址，如果文件可以执行的话（如php,html,js等) 必须使用绝对地址，否则会得到运行后的内容；
	//判断有无编辑权限
	$isadmin = perm_check::checkperm('edit', $icoarr);

	$str = IO::getFileContent($path);

	require_once DZZ_ROOT . './dzz/class/class_encode.php';
	$p = new Encode_Core();
	$code = $p -> get_encoding($str);
	if ($code)
		$str = diconv($str, $code, CHARSET);
	$str = htmlspecialchars($str);
	include template('textviewer');
}
function getUrlMimeType($url) {
	if (function_exists('mime_content_type ')) {
		return mime_content_type($url);
	} else if (extension_loaded('fileinfo')) {
		$buffer = file_get_contents($url);
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		return $finfo -> buffer($buffer);
	} else {
		return false;
	}
}

function get_config_bytes($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val) - 1]);
	switch($last) {
		case 'g' :
			$val *= 1024;
		case 'm' :
			$val *= 1024;
		case 'k' :
			$val *= 1024;
	}
	return $val;
}
?>
