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
if (empty($_G['uid'])) {
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();}catch(e){}";
	echo "try{win.Close();}catch(e){}";
	echo "</script>";
	include template('common/footer_reload');
	exit('<a href="user.php?mod=logging&action=login">'.lang('need_login').'</a>');
}
if (submitcheck('edit')) {
	$did = intval($_GET['did']);
	$icoid = intval($_GET['icoid']);
	$icoarr = C::t('icos') -> fetch_by_icoid($icoid);
	if (!perm_check::checkperm('edit', $icoarr)) {
		showmessage('no_privilege', dreferer());
	}
	//桌面上的文档 $area=='' && $areaid=0;
	//项目内文档  $area=='project' && $areaid==$pjid;
	$area = ($_GET['area'] == 'folder') ? '' : trim($_GET['area']);
	$areaid = ($_GET['area'] == 'folder') ? 0 : trim($_GET['areaid']);
	$new = intval($_GET['newversion']);
	$autosave = intval($_GET['autosave']);
	if ($autosave)
		$new = 0;
	//存储文档内容到文本文件内
	$_GET['message'] = helper_security::checkhtml($_GET['message']);
	$message = $_GET['message'];
	//str_replace(array("\r\n", "\r", "\n"), "",$_GET['message']); //去除换行
	if (!$attach = getTxtAttachByMd5($message, $icoarr['name'] . '.dzzdoc')) {
		showmessage('error_saving_documents', dreferer());
	}
	//获取文档内附件
	$attachs = getAidsByMessage($message);
	$setarr = array('uid' => $_G['uid'], 'username' => $_G['username'], 'aid' => $attach['aid'], 'did' => $did, );
	if (!$did = C::t('document') -> insert($setarr, $attachs, $area, $areaid, $new)) {
		showmessage('error_saving_documents1');
	}
	$return = array('did' => $did, 'autosave' => $autosave, 'icoid' => dzzencode($icoid));
	showmessage('do_success', dreferer(), array('data' => rawurlencode(json_encode($return))), array('showmsg' => true));

} else {
	$navtitle = '';
	$icoid = intval(dzzdecode($_GET['icoid']));
	if ($icoid && $icoarr = C::t('icos') -> fetch_by_icoid($icoid)) {

		if (!perm_check::checkperm('edit', $icoarr)) {
			showmessage('no_privilege');
		}
		$did = $icoarr['did'];
	} else {
		showmessage('document_not_exist');
	}
	if ($document = C::t('document') -> fetch_by_did($did)) {
		$dzzpath = getDzzPath($document);
		$str = trim(IO::getFileContent($dzzpath));
		$navtitle = $document['subject'];
	} else {
		$navtitle = lang('new_document');
	}
	include template('document_edit');
}
function getAidsByMessage($message) {
	$aids = array();
	if (preg_match_all("/" . rawurlencode('attach::') . "(\d+)/i", $message, $matches)) {
		$aids = $matches[1];
	}
	if (preg_match_all("/path=\"attach::(\d+)\"/i", $message, $matches1)) {
		if ($matches1[1])
			$aids = array_merge($aids, $matches1[1]);
	}
	return array_unique($aids);
}
?>
