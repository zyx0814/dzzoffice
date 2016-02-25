<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
define('MP3_DIR','dzz/player/mp3/');
$do=$_GET['do'];
if($do=='saveplaylist'){
		$paylist=trim($_POST['data']);
		DB::insert('user_playlist',(array('uid'=>$_G['uid'],'playlist'=>$paylist,'updatetime'=>$_G['timestamp'])),1,1);
	
	exit();
}elseif($do=='getplaylist'){
	if(!$playarr=dstripslashes(unserialize(stripslashes(DB::result_first("select playlist from ".DB::table('user_playlist')." where   uid='{$_G[uid]}'"))))){
		$playarr=array();
	}
	$return=array('playlist'=>$playarr,'isadmin'=>1);
	echo json_encode($return);
	exit();
}else{
	//exit('dddd==='.template('player:mp3/index'));
	$icoid=trim($_GET['icoid']);
	include  template('jplayer');
	//exit('dfdfd');
}
?>
