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

define('NOROBOT', TRUE);

if ($_GET['uid'] && $_GET['id']) {

	$member = getuserbyuid($_GET['uid']);
	/*if($member && $member['groupid'] == 8) {
	 $member = array_merge(C::t('user_field')->fetch($member['uid']), $member);
	 } else {
	 showmessage('activate_illegal', 'index.php');
	 }*/
	list($dateline, $operation, $idstring) = explode("\t", $member['authstr']);

	if ($operation == 2 && $idstring == $_GET['id']) {
		if ($member['groupid'] == 8) {
			$newusergroupid = $_G['setting']['newusergroupid'];
		} else {
			$newusergroupid = $member['groupid'];
		}
		C::t('user') -> update($member['uid'], array('groupid' => $newusergroupid, 'authstr' => '', 'emailstatus' => '1'));
		//加入默认机构
		if (getglobal('setting/defaultdepartment') && DB::fetch_first("select orgid from %t where orgid=%d ", array('organization', getglobal('setting/defaultdepartment')))) {
			C::t('organization_user') -> insert($member['uid'], getglobal('setting/defaultdepartment'));
		}
		showmessage('activate_succeed', 'index.php', array('username' => $member['username']));
	} else {
		showmessage('activate_illegal', 'index.php');
	}

}
?>
