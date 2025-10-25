<?php
/**
 * 当前版本: 5.3.31
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
if (!$meta) showmessage('file_not_exist');
if($meta['error']) showmessage($meta['error']);
$perm_download = 1;
if ($meta['rid']) {
    if (!perm_check::checkperm('read', $meta)) showmessage('file_read_no_privilege', dreferer());
    if (!perm_check::checkperm('download', $meta)) {
        $perm_download = 0;
    }
}
$file = $_G['siteurl'] . 'index.php?mod=io&op=getStream&path=' . $_GET['path'] . '&filename=' . $meta['name'];
$navtitle = $meta['name'];
if($_G['language']=='zh-cn'){
    $lang = 'zh-CN';
}elseif($_G['language']=='en-us'){
    $lang = 'en-US';
} else{
    $lang = $_G['language'];
}
include template('viewer');
exit();