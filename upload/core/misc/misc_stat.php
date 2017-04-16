<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
ignore_user_abort(1);
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

$setarr=array('ip'=>$_G['clientip'],
			  'agent'=>$_SERVER['HTTP_USER_AGENT'],
			  'os'=>$_SERVER['OS'],
			  'dateline'=>$_G['timestamp']
			  );
DB::insert('count_down',$setarr);
exit();
?>
