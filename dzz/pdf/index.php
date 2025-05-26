<?php
/**
 * 当前版本: 2.5.207 2020/06/01
 * 版本更新: https://github.com/mozilla/pdf.js/releases
 * 搜索:    var userOptions = Object.create(null);
 * 添加:    userOptions =  window.pdfOptions || userOptions;
 */
if (!defined('IN_DZZ') || !$_GET['path']) {
    exit('Access Denied');
}
if (!$path = dzzdecode($_GET['path'])) {
    showmessage('parameter_error', dreferer());
}
$meta = IO::getMeta($path);
if (!$meta) showmessage(lang('file_not_exist'));
$perm_download = 1;
$perm_print = 1;
if ($meta['rid']) {
    if (!perm_check::checkperm('read', $meta)) showmessage(lang('file_read_no_privilege'), dreferer());
    if (!perm_check::checkperm('download', $meta)) {
        $perm_download = 0;
        $perm_print = 0;
    }
}
$file = $_G['siteurl'] . 'index.php?mod=io&op=getStream&path=' . $_GET['path'] . '&filename=' . $meta['name'];
$navtitle = $meta['name'];
include template('viewer');
exit();