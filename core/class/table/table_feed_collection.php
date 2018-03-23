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

class table_feed_collection extends dzz_table
{
	public function __construct() {

		$this->_table = 'feed_collection';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_all_tids_by_uid($uid){
		$tids=array();
		foreach(DB::fetch_all("select tid from %t where uid=%d",array($this->_table,$uid)) as $value){
			$tids[]=$value['tid'];
		}
		return $tids;
	}
    public function insert_by_tid_uid($tid,$uid){
		if(!$tid || !$uid) return false;
		return parent::insert(array('tid'=>$tid,'uid'=>$uid),0,1);
	}
	public function delete_by_tid_uid($tid,$uid){
		if(!$tid || !$uid) return false;
		return DB::delete($this->_table,"tid='{$tid}' and uid='{$uid}'");
	}
	public function delete_by_tid($tids){
		$tids=(array)$tids;
	   return DB::delete($this->_table,"tid IN(".dimplode($tid).")");
	}
	
}

?>
