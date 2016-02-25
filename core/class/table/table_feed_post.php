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

class table_feed_post extends dzz_table
{
	public function __construct() {

		$this->_table = 'feed_post';
		$this->_pk    = 'pid';
		$this->_pre_cache_key = 'feed_post_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_pid($pids){
	   if(!$pids) return false;
	   if(!is_array($pids)){
		   $pids=array($pids);
	   }
	   $pids=array_unique($pids);
	  //删除@
	   C::t('feed_at')->delete_by_pid($pids);
	   //删除附件
	   C::t('feed_attach')->delete_by_pid($pids);
	   //删除回复表
	   C::t('feed_reply')->delete_by_pid($pids);
	   return self::delete($pids);
   }
	public function delete_by_tid($tids){
	   $pids=array();
	   foreach(DB::fetch_all("select pid from %t where tid IN (%n) ",array('feed_post',$tids)) as $value){
		   $pids[]=$value['pid'];
	   }
	   return self::delete_by_pid($pids);
   }
}

?>
