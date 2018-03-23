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

class table_feed_thread extends dzz_table
{
	public function __construct() {

		$this->_table = 'feed_thread';
		$this->_pk    = 'tid';
		$this->_pre_cache_key = 'feed_thread_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
   public function delete_by_tid($tids){
	   $tids=(array)$tids;
	   C::t('feed_post')->delete_by_tid($tids);
	   
	   //删除收藏
	   C::t('feed_collection')->delete_by_tid($tids);
	   //删除投票
	   C::t('vote')->delete_by_id_idtype($tids,'feed');
	   parent::delete($tids);
   }
   
	public function increase($tids, $fieldarr) {
		$tids = dintval((array)$tids, true);
		$sql = array();
		$num = 0;
		$allowkey = array('replies', 'lastposter', 'lastpost');
		foreach($fieldarr as $key => $value) {
			if(in_array($key, $allowkey)) {
				if(is_array($value)) {
					$sql[] = DB::field($key, $value[0]);
				} else {
					$value = dintval($value);
					$sql[] = "`$key`=`$key`+'$value'";
				}
			} else {
				unset($fieldarr[$key]);
			}
		}
		if($getsetarr) {
			return $sql;
		}
		if(!empty($sql)){
			$cmd = "UPDATE " ;
			$num = DB::query($cmd.DB::table($this->_table)." SET ".implode(',', $sql)." WHERE tid IN (".dimplode($tids).")", 'UNBUFFERED');
			$this->increase_cache($tids, $fieldarr);
		}
		return $num;
	}
}

?>
