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
$qid = intval($_GET['qid']);
if (!$qid) {
    topshowmessage(lang('parameter_error'));
}
if (!$fid = intval($_GET['fid'])) {
    topshowmessage(lang('parameter_error'));
}
$attach = C::t('comment_attach')->fetch($qid);
if (!$attach || !$attach['aid']) {
    topshowmessage(lang('attachment_nonexistence'));
}
$attach['filename'] = $attach['title'];
$icoarr = io_dzz::uploadToattachment($attach, $fid, false, true);
exit(json_encode($icoarr));
