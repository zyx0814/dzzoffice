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
include libfile('function/cache');
$navtitle = lang('updatecache') . ' - ' . lang('appname');
$step = max(1, intval($_GET['step']));
$op = isset($_GET['op']) ? $_GET['op'] : '';
if ($step == 1) {
} elseif ($step == 2) {
    $type = implode('_', (array)$_GET['type']);
} elseif ($step == 3) {
    $type = explode('_', $_GET['type']);
    if (in_array('data', $type)) {
        updatecache();
    }
    if (in_array('tpl', $type) && $_G['config']['output']['tplrefresh']) {
        cleartemplatecache();
    }
    if (in_array('perm',$type)) {
        clearpermcache();
    }
    if (in_array('memory', $type)) {
        C::memory()->clear();
        DB::delete('cache',' 1 ');
    }
}
include template('updatecache');
?>
