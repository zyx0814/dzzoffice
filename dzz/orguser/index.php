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
include_once libfile('function/appperm');
Hook::listen('check_login');
$navtitle = $global_appinfo['appname'] ? $global_appinfo['appname'] : lang('appname');
$orgid = 'other';
$orgtree = array();
if ($_G['adminid'] != 1) {
    //获取用户的有权限的部门树
    $orgids = C::t('organization_admin')->fetch_orgids_by_uid($_G['uid']);
    foreach ($orgids as $orgid) {
        $arr = C::t('organization')->fetch_parent_by_orgid($orgid, true);
        $count = count($arr);
        if ($orgtree[$arr[$count - 1]]) {
            if (count($orgtree[$arr[$count - 1]]) > $count)
                $orgtree[$arr[count($arr) - 1]] = $arr;
        } else {
            $orgtree[$arr[$count - 1]] = $arr;
        }
    }
    if (!$orgids) {
        showmessage('no_parallelism_jurisdiction', dreferer());
    }
    $orgid = reset($orgids);
}
$orgtree = json_encode($orgtree);
if (!$_G['cache']['usergroups']) loadcache('usergroups');
include template('main');
?>
