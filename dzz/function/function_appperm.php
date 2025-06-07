<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if (!defined('IN_DZZ')) { //所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}
if (CURMODULE) {
    global $_G;
    $appinfo = C::t('app_market')->fetch_by_allidentifier(CURMODULE);
    if ($appinfo['appid']) {
        global $global_appinfo;
        $global_appinfo = $appinfo;
        if ($_G['adminid']) return;
        if (!$appinfo['available']) showmessage($appinfo['appname'] . ' 应用已关闭，请联系管理员。');
        if ($appinfo['group'] == 0) return;
        if ($_G['uid']) {
            if ($appinfo['group'] == -1) showmessage($appinfo['appname'] . ' 应用仅限游客访问，请联系管理员。');
            if ($appinfo['group'] == 3) showmessage($appinfo['appname'] . ' 应用仅限管理员访问，请联系管理员。');
            $apps = C::t('app_market')->fetch_all_by_default($_G['uid'], true);
            $allowed = false;
            $allowed = in_array($appinfo['appid'], $apps);
            if (!$allowed) {
                showmessage('您当前账号暂无(' . $appinfo['appname'] . ')应用的访问权限，建议联系管理员获取相应权限。');
            }
        } elseif ($appinfo['group'] == -1) {
            return;
        } else {
            Hook::listen('check_login');
        }
    }
}