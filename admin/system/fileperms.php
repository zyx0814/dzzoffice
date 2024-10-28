<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
include_once libfile('function/admin');
$oparr = array('updatecache', 'database','cron', 'log','fileperms');
$leftmenu = array();
$op = $_GET['op'];
foreach ($oparr as $key => $value) {
	$leftmenu[$value] = array('title' => lang($value), 'active' => '');
	if ($value == $op)
		$leftmenu[$value]['active'] = 'class="nav-link active"';
}
$navtitle = lang('fileperms') . ' - ' . lang('appname');
$step = max(1, intval($_GET['step']));
if ($step == 1) {
} elseif ($step == 2) {
	$type = implode('_', (array)$_GET['type']);
} elseif ($step == 3) {
	$type = explode('_', $_GET['type']);
	try {
		$entryarray = array(
			'data',
			'data/attachment',
			'data/attachment/album',
			'data/attachment/category',
			'data/attachment/common',
			'data/attachment/forum',
			'data/attachment/group',
			'data/attachment/portal',
			'data/attachment/profile',
			'data/attachment/swfupload',
			'data/attachment/temp',
			'data/cache',
			'data/log',
			'data/template',
			'data/threadcache',
			'data/diy'
		);
		$result = '';
		foreach($entryarray as $entry) {
			$fullentry = DZZ_ROOT.'./'.$entry;
			if(!is_dir($fullentry) && !file_exists($fullentry)) {
				continue;
			} else {
				if(!dir_writeable($fullentry)) {
					$result .= '<li class="list-group-item list-group-item-danger">'.(is_dir($fullentry) ? lang('dir') : lang('file')).'./'.$entry.'<span class="badge bg-danger rounded-pill">无法写入</span></li>';
				}
			}
		}
		$result .= '<li class="list-group-item list-group-item-success">文件及目录属性全部正确<span class="badge bg-primary rounded-pill">都能写入</span></li>';
	} catch (Exception $e) {
		$result .= "<li class=\"list-group-item list-group-item-danger\">发生错误：".$e->getMessage()."</li>";
	}
}
include template('fileperms');
?>
