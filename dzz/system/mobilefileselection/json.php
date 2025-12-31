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
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$space = dzzgetspace($uid);
$space['self'] = intval($space['self']);
$data = [];
$data['myuid'] = $uid;
$applist = C::t('app_market')->fetch_all_by_default($_G['uid'],true);
//获取系统桌面设置信息
$icosdata = [];
$data['formhash'] = $_G['formhash'];
$data['sourcedata'] = [
    'icos' => [],
    'folder' => []
];

$data['space'] = $space;
$data['mulitype'] = $mulitype;
$data['fileselectiontype'] = $type;
$data['callback_url'] = $callback;
if ($exttype) {
    $exttype = str_replace(['&quot;', '|', '$'], ['"', '(', ')'], $exttype);
}
$data['allowselecttype'] = json_decode($exttype);
$data['defaultfilename'] = isset($filename) ? $filename : '';
echo json_encode($data);
exit();