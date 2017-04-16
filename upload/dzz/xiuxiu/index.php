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
$do = empty($_GET['do']) ? '' : trim($_GET['do']);
if ($do == 'save') {//图片保存接口
	$path = rawurldecode($_GET['path']);
	//文件保存位置
	$tpath = empty($_GET['tpath']) ? '' : rawurldecode($_GET['tpath']);
	//覆盖原有文件
	$name = rawurldecode($_GET['name']);
	//文件名
	$post_input = 'php://input';

	if ($tpath) {//覆盖原有文件
		//获取文件内容
		$fileContent = '';
		$handle = fopen('php://input', 'r');
		while (!feof($handle)) {
			$fileContent .= fread($handle, 8192);
		}
		$icoarr = IO::setFileContent($tpath, $fileContent);
	} else {//新建文件
		$re = IO::uploadStream($post_input, $name, $path);
		//上传文件到服务器
		if (empty($re['error'])) {
			$icoarr = $re['icoarr'][0];
		} else {
			$icoarr = $re;
		}
	}
	echo json_encode($icoarr);
	//返回
	exit();
} else {
	$path = rawurldecode($_GET['path']);
	//根据文件路径打开文件
	if ($path)
		$stream = $_G['siteurl'] . DZZSCRIPT . '?mod=io&op=thumbnail&width=1440&height=900&path=' . ($path) . '&original=1';
	else
		$stream = '';

	include template('xiuxiu');
	//调用模板文件
}
?>
