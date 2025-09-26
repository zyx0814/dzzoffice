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
if ($_GET['path']) {
    if (!$path = dzzdecode($_GET['path'])) {
        showmessage('parameter_error');
    }
    include_once libfile('function/appperm');
    $meta = IO::getMeta($path);
    if (!$meta) showmessage('file_not_exist');
    if($meta['error']) showmessage($meta['error']);
    //判断有无查看权限
    if ($meta['rid']) {
        if (!perm_check::checkperm('read', $meta)) showmessage('file_read_no_privilege', dreferer());
    }
    if ($meta['name']) {
        $navtitle = $meta['name'];
        $navtitle = str_replace(strrchr($navtitle, "."), "", $navtitle);
    } else {
        $navtitle = '视频';
    }
    $src = $_G['siteurl'] . 'index.php?mod=io&op=getStream&path=' . $_GET['path'] . '&filename=' . $meta['name'];
} elseif ($_GET['url']) {
    $src = urldecode($_GET['url']);
}
include template('index');
?>