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
if (!$_G['cache']['usergroups']) {
	loadcache('usergroups');
}
$op=$_GET['op'];
//error_reporting(E_ALL);
//资料审核员和实名认证员跳转到对应的页面
if ($_G['member']['grid'] == '4') {
	if ($_G['setting']['verify'][1]['available']) {
		$op = 'verify';
		$_GET['vid'] = 1;
		require MOD_PATH.'./admin/member/verify.php';
		exit();

	} else {
		showmessage('contact_administrator');
	}
} elseif ($_G['member']['grid'] == '5') {
	$op = 'verify';
	$_GET['vid'] = 0;
	require MOD_PATH.'/verify.php';
	exit();

}else{
	$op = 'verify';
	require MOD_PATH.'/verify.php';
	exit();
}
