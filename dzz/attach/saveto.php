<?php
/* @authorcode  codestrings
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
    topshowmessage(lang('no_login_operation'));
}
$explorer_setting = get_resources_some_setting();
if (!$explorer_setting['useronperm']) {
    topshowmessage(lang('no_privilege'));
}
$fid = DB::result_first("select fid from %t where flag='home' and uid= %d", array('folder', $_G['uid']));
if (!$fid) {
    topshowmessage('未查询到我的网盘目录');
}
if ($_GET['type'] == 'link') {
    if (!perm_check::checkperm_Container($fid, 'upload')) {
        topshowmessage(lang('target_not_accept_link'));
    }
    $link = empty($_GET['link']) ? '' : trim($_GET['link']);
    if (!$link) topshowmessage(lang('parameter_error'));
    //检查网址合法性
    if (!preg_match("/^(http|ftp|https|mms)\:\/\/.{5,300}$/i", ($link))) {
        $parseurl = parse_url($link);
        if (!$parseurl['host']) $link = $_G['siteurl'] . $link;
        else $link = 'http://' . preg_replace("/^(http|ftp|https|mms)\:\/\//i", '', $link);
    }
    if (!preg_match("/^(http|ftp|https|mms)\:\/\/.{4,300}$/i", ($link))) topshowmessage(lang('invalid_format_url'));
    $icoarr = io_dzz::linktourl($link, $fid);
} else {
    $aid = empty($_GET['aid']) ? 0 : intval($_GET['aid']);
    $attach = C::t('attachment')->fetch($aid);
    if (!$attach) {
        topshowmessage(lang('attachment_nonexistence'));
    }
    if (!empty($_GET['filename'])) $attach['filename'] = trim($_GET['filename']);
    $icoarr = io_dzz::uploadToattachment($attach, $fid, false, true);
}
if (isset($icoarr['error'])) topshowmessage($icoarr['error']);
topshowmessage(lang('saved_my_documents'));
?>
