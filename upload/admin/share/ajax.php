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
if ($_GET['do'] == 'delete') {
	$sids = $_GET['sids'];

	if ($sids && C::t('share') -> delete($sids)) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('delete_unsuccess'))));
	}

} elseif ($_GET['do'] == 'forbidden') {
	$sids = $_GET['sids'];
	if ($_GET['flag'] == 'forbidden') {
		$status = -4;
	} else {
		$status = 0;
	}
	if ($sids && C::t('share') -> update($sids, array('status' => $status))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('share_screen_failure'))));
	}
}
?>
