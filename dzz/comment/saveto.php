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

$qid = intval($_GET['qid']);
$attach = C::t('comment_attach') -> fetch_by_qid($qid);
if (!$attach) {
	topshowmessage(lang('attachment_nonexistence'));
}$attach['filename'] = $attach['title'];
$pfid = $_GET['fid'];
$icoarr = io_dzz::uploadToattachment($attach, $pfid);
exit(json_encode($icoarr));
