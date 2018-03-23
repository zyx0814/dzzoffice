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
    $return = array();
    foreach($sids as $v){
        $result = C::t('shares')->delete_by_id($v);
        if($result['success']){
            $return['msg'][$v]=$result;
        }elseif ($result['error']){
            $return['msg'][$v] = $result['error'];
        }
    }
    exit(json_encode($return));
	

} elseif ($_GET['do'] == 'forbidden') {
	$sids = $_GET['sids'];
	if ($_GET['flag'] == 'forbidden') {
		$status = -4;
	} else {
		$status = 0;
	}
	if ($sids && C::t('shares') -> update($sids, array('status' => $status))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('share_screen_failure'))));
	}
}
?>
