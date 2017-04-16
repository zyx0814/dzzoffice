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

class table_feed_reply extends dzz_table
{
	public function __construct() {

		$this->_table = 'feed_reply';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_all_tids_by_ruid($uid,$timestamp=0,$count=0){
		$tids=array();
		if($count) return DB::result_first("select COUNT(*) from %t where ruid=%d and dateline>%d",array($this->_table,$uid,$timestamp));
		foreach(DB::fetch_all("select pid,tid from %t where ruid=%d and dateline>%d",array($this->_table,$uid,$timestamp)) as $value){
			$tids[]=$value['tid'];
		}
		return array_unique($tids);
	}
  
	public function delete_by_pid($pids){
	   if(!$pids) return false;
	   if(!is_array($pids)){
		   $pids=array($pids);
	   }
	   return DB::delete($this->_table,"pid IN (".dimplode($pids).")");
	}
	
}

?>
