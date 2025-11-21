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
$h0 = array('username' => lang('username'), 'email' => lang('email'), 'nickname' => lang('username'), 'birth' => lang('date_birth'), 'mobile' => lang('cellphone'), 'weixinid' => lang('weixin'), 'orgname' => lang('category_department'), 'job' => lang('department_position'));
function getProfileForImport() {
    global $_G;
    if (empty($_G['cache']['profilesetting'])) {
        loadcache('profilesetting');
    }
    $profilesetting = $_G['cache']['profilesetting'];
    $ret = array();
    foreach ($profilesetting as $key => $value) {
        if (in_array($key, array('department', 'birthyear', 'birthmonth', 'birthday'))) {
            continue;
        } elseif ($value['formtype'] == 'file') {
            continue;
        } else {
            $ret[$key] = $value['title'];
        }
    }
    return $ret;
}
function getColIndex($index) {
    $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $ret = '';
    if ($index > 255) return '';
    for ($i = 0; $i < floor($index / strlen($string)); $i++) {
        $ret = $string[$i];
    }
    $ret .= $string[($index % (strlen($string)))];
    return $ret;
}