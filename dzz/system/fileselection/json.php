<?php
/* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$data = [];
$data['myuid'] = $_G['uid'];
$data['deletefinally'] = 0;
if (isset($_G['setting']['explorer_finallydelete'])) {
    if (intval($_G['setting']['explorer_finallydelete']) === 0) {
        $data['deletefinally'] = 1;
    }
}
$applist = C::t('app_market')->fetch_all_by_default($_G['uid'],true);
//获取打开方式
$data['extopen']['all'] = C::t('app_open')->fetch_all_ext();
$data['extopen']['ext'] = C::t('app_open')->fetch_all_orderby_ext($_G['uid'], $data['extopen']['all'], $applist);
$data['extopen']['user'] = C::t('app_open_default')->fetch_all_by_uid($_G['uid']);

$data['formhash'] = $_G['formhash'];

$data['sourcedata'] = [
    'icos' => [],
    'folder' => []
];

$data['space'] = [
    'self' => ($_G['adminid'] == 1) ? 2 : 0,
    'uid' => $_G['uid'],
];
$data['mulitype'] = $mulitype;
$data['fileselectiontype'] = $type;
if ($exttype) {
    $exttype = str_replace(['&quot;', '|', '$'], ['"', '(', ')'], $exttype);
}
$data['allowselecttype'] = json_decode($exttype);
$data['defaultfilename'] = isset($filename) ? $filename : '';
$data['defaultselect'] = $_GET['defaultselect'];
$data['allowcreate'] = $_GET['allowcreate'];
$data['permfilter'] = $_GET['perm'];
exit(json_encode($data));