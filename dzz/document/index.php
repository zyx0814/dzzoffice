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
$icoid = intval(dzzdecode($_GET['icoid']));
if (!$icoarr = C::t('icos') -> fetch_by_icoid($icoid)) {
	showmessage('file_not_exist_or_deleted');
}
$did = $icoarr['did'];
$navtitle = $icoarr['name'];
$do = trim($_GET['do']);
$version = intval($_GET['v']);
if ($do == 'deleteVersion') {
	if ($_G['adminid'] != 1 && $_G['uid'] != $icoarr['uid']) {
		showmessage('privilege', dreferer());
	}
	if ($ver = C::t('document_reversion') -> delete_by_version($did, $version)) {
		showmessage('do_success', DZZSCRIPT . "?mod=document&icoid=" . dzzencode($icoid));
	} else {
		showmessage('delete_version_failed', dreferer());
	}
} elseif ($do == 'applyVersion') {
	if ($ver = C::t('document_reversion') -> reversion($did, $version, $_G['uid'], $_G['username'])) {
		showmessage('do_success', DZZSCRIPT . "?mod=document&icoid=" . dzzencode($icoid) . "&v=$ver");
	} else {
		showmessage(lang('use_release_failure'), dreferer());
	}
} else {

	if ($document = C::t('document') -> fetch_by_did($did)) {
		$document['dateline'] = dgmdate($document['dateline'], 'u');
		//获取此文件的所有版本
		$versions = C::t('document_reversion') -> fetch_all_by_did($did);
		if ($version > 0) {//版本比较模式，显示当前版本与前一版本的差异
			$current = $versions[$version];
			if (isset($versions[$version])) {
				$dzzpath = getDzzPath($versions[$version]);
				$str_new = IO::getFileContent($dzzpath);
				//str_replace(array("\r\n", "\r", "\n"), "",);
			} else {
				$dzzpath = getDzzPath($document);
				$str_new = IO::getFileContent($dzzpath);
				//str_replace(array("\r\n", "\r", "\n"), "",);
			}
			if ($versions[$version - 1]) {
				$dzzpath_old = getDzzPath($versions[$version - 1]);
				$str_old = IO::getFileContent($dzzpath_old);
				//str_replace(array("\r\n", "\r", "\n"), "",IO::getFileContent($dzzpath_old));

			} else {
				$str_old = $str_new;
			}
			include_once dzz_libfile('class/html_diff', 'document');
			$diff = new html_diff();
			$str = $diff -> compare($str_old, $str_new);
		} else {
			$current = $document;
			$dzzpath = getDzzPath($document);
			$str = IO::getFileContent($dzzpath);
			//str_replace(array("\r\n", "\r", "\n"), "",);
			$navtitle = $document['subject'];
		}
	} else {
		showmessage('file_not_exist_or_deleted', dreferer());
	}
	$dicoid = dzzencode($icoid);
	$editperm = perm_check::checkperm('edit', $icoarr);

	include template('document_view');
}
?>
