<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @version     DzzOffice 1.0 release  2014.3.30
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

$do=$_GET['do'];
if($do=='poll'){
	if($_GET['type']=='short'){
		$list=_getNotification();
	}else{
		 set_time_limit(0);
		 $no_msg_count = 0;
		 $num=0;
		 $start = time();
	     do{
			$list=_getNotification();
			$num=count($list);
			if(!$num) {
				$no_msg_count++;
				sleep(1.5 + min($no_msg_count * 1.5, 7.5));
			}else{
				break;
			}
        } while(!$num && (time() - $start < 60));
	}
	echo json_encode(array('msg'=>'success','noticelist'=>$list));
	exit();
}
function _getNotification(){
	global $_G;
	$wherearr = array();
	$new = 1;
	$newnotify = false;
	$list=array();
	foreach(C::t('notification')->fetch_all_by_uid($_G['uid'], $new, $type) as $value) {
		$newnotify = true;
		$value['style'] = 'color:#000;font-weight:bold;';
		
		 $value['from_num'] = intval($value['from_num']);
		if(empty($value['title'])){
			$value['title']=lang($value['type'].'_title');
		}
		$value['note1']=strip_tags($value['note']);
		$list[] = $value;
	}
	C::t('notification')->ignore($_G['uid'], true, true);
	return $list;
}

?>
