<?php
/*
 * 下载
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
	include  template('common/footer_reload');
	exit('<a href="user.php?mod=login&action=login">'.lang('need_login').'</a>');
}
$qid = intval($_GET['qid']);
$attach = C::t('comment_attach') -> fetch_by_qid($qid);
if (!$attach) {
	topshowmessage(lang('attachment_nonexistence'));
}$attach['filename'] = $attach['title'];
$pfid = DB::result_first("select fid from %t where flag='document' and uid= %d", array('folder', $_G['uid']));
$icoarr = io_dzz::uploadToattachment($attach, $pfid);
if (isset($icoarr['error']))
	topshowmessage($icoarr['error']);
include template('common/header_simple');
echo "<script type=\"text/javascript\">";
echo "try{top._ico.createIco(" . json_encode($icoarr) . ");}catch(e){alert(".lang('saved_my_documents').")}";
echo "try{top.Alert('”" . $attach['title'] . lang('successfully_added_desktop')."',3,'','','info');}catch(e){}";
echo "</script>";
include template('common/footer');
exit();
?>
