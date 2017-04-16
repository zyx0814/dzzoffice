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
	$dels = array();
	foreach (DB::fetch_all("select sid from %t where sid IN(%n) and uid=%d",array('share',$sids,$_G['uid'])) as $value) {
		$dels[] = $value['sid'];
	}
	if ($dels && C::t('share') -> delete($dels)) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => '{lang delete_unsuccess}')));
	}

}
?>
