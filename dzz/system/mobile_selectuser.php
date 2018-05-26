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
include_once libfile('function/organization');
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
$ismobile = helper_browser::ismobile();
$ids = isset($_GET['ids']) ? rawurldecode($_GET['ids']) : '';
$zero = $_GET['zero'] ? urldecode($_GET['zero']) : lang('no_institution_users');//无机构用户名称
$nouser = intval($_GET['nouser']);//不显示用户
$stype = intval($_GET['stype']); //0:可以选择机构和用户；1：仅选择机构和部门：2：仅选择用户
$moderator = intval($_GET['moderator']);//是否仅可以选择我管理的群组或部门
$range = intval($_GET['range']);//0：所有部门和群组；1：仅部门；2：仅群组
$multiple = intval($_GET['multiple']); //是否允许多线
//$callback=$_GET['callback']?$_GET['callback']:'callback_selectuser';//回调函数名称
$callbackurl = isset($_GET['callbackurl']) ? trim($_GET['callbackurl']) : '';//回调地址
$token = htmlspecialchars($_GET['token']);
$gets = array(
    'zero' => $zero,
    'nouser' => nouser,
    'stype' => $stype,
    'moderator' => $moderator,
    'range' => $range,
    'multiple' => $multiple,
    'nosearch' => 1,
    'ctrlid' => 'seluser'
);
//获取选中项
$ids = explode(',', $ids);
$selectorgids = $selectuids = array();
foreach ($ids as $value) {
    if (strpos($value, 'g_') !== false) {
        if ($stype == 2) continue;//仅选择用户时，忽略部门和群组
        $orgid = intval(str_replace($value, 'g_', ''));
        $selectorgids[$orgid] = $orgid;

    } elseif ($uid = intval($value)) {
        if ($stype == 1) continue; //仅选择部门和群组时，忽略用户；
        $selectuids[$uid] = $uid;
    }
}
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
//获取所有机构和部门
$orgdatas = $orgids = $orgnames = $selectorginfo = array();
foreach (DB::fetch_all("select pathkey from %t where `type` = %d", array('organization', 0)) as $v) {
    $param = array('organization', 0);
    $pathkey = $pathkey . '.*';
    foreach (DB::fetch_all("select * from %t where pathkey regexp %s and `type` = 0", array('organization', $pathkey)) as $val) {
        if (intval($val['aid']) == 0) {
            $val['img'] = avatar_group($val['orgid'], array($val['orgid'] => array('aid' => $val['aid'], 'orgname' => $val['orgname'])));
        } else {
            $val['icon'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $val['aid']);
        }
        $orgdatas[$val['pathkey']] = $val;
        $orgdatas[$val['pathkey']]['user_count'] = 0;
        $orgdatas[$val['pathkey']]['user_select'] = 0;
        if(in_array($val['orgid'],$selectorgids)) $orgdatas[$val['pathkey']]['selected'] = true;
        $orgids[] = $val['orgid'];
        $orgnames[$val['orgid']] = $val['orgname'];
    }
}
$wheresql = 'ou.orgid in(%n) ';
$param = array('organization_user', 'organization', 'user', 'user_setting', 'headerColor', $orgids);
if ($keyword) {
    $wheresql .= ' and (u.username LIKE %s or u.email LIKE %s or u.phone LIKE %s)';
    $param[] = '%' . $keyword . '%';
    $param[] = '%' . $keyword . '%';
    $param[] = '%' . $keyword . '%';
}
$selectuserinfo = array();
$selectnum = ($stype == 1) ? count($selectorgids): count($selectuids);
if(!$nouser) {
    $data = DB::fetch_all("select ou.orgid,o.pathkey,u.uid,u.username,u.avatarstatus,s.svalue from %t ou left join %t o on o.orgid=ou.orgid
        left join %t u on ou.uid=u.uid left join %t s on u.uid=s.uid and s.skey=%s where $wheresql", $param);
    //获取机构和部门下的用户
    foreach ($data as $v) {
        //获取用户头像相关信息
        if (!$v['avatarstatus']) {
            $v['avatarstatus'] = 0;
            $v['headerColor'] = $v['svalue'];
            $v['firstword'] = strtoupper(new_strsubstr($v['username'], 1, ''));
        }
        if ($orgdatas[$v['pathkey']]) {
            if(in_array($v['uid'],$selectuids) || in_array($v['orgid'],$selectorgids)){
                $v['selected'] = true;
                $selectuserinfo[] = $v;
                $orgdatas[$v['pathkey']]['user_select'] += 1;
            }
            $orgdatas[$v['pathkey']]['users'][$v['uid']] = $v;
            $orgdatas[$v['pathkey']]['user_count'] += 1;
        }
    }
}

ksort($orgdatas);
foreach ($orgdatas as $k => $v) {
    $pathkeyarr = explode('-', str_replace('_', '', $k));
    $orgdatas[$v['pathkey']]['title'] = '';
    foreach ($pathkeyarr as $val) {
        $orgdatas[$v['pathkey']]['title'] .= $orgnames[$val] . '-';
    }
    $orgdatas[$v['pathkey']]['title'] = substr($orgdatas[$v['pathkey']]['title'], 0, -1);
}
include template('mobile_selectuser');
dexit();

