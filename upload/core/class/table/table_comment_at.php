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

class table_comment_at extends dzz_table
{
	public function __construct() {

		$this->_table = 'comment_at';
		$this->_pk    = '';

		parent::__construct();
	}
	public function fetch_all_cids_by_uid($uid,$timestamp=0,$count=0){
		$cids=array();
		if($count) return DB::result_first("select COUNT(*) from %t where uid=%d and dateline>%d",array($this->_table,$uid,$timestamp));
		foreach(DB::fetch_all("select cid from %t where uid=%d and dateline>%d",array($this->_table,$uid,$timestamp)) as $value){
			$tids[]=$value['cid'];
		}
		return array_unique($cids);
	}
    public function insert_by_cid($cid,$uids){
		if(!$cid || !$uids) return false;
		foreach($uids as $uid){
			parent::insert(array('cid'=>$cid,'uid'=>$uid,'dateline'=>TIMESTAMP),0,1);
		}
		
	}
	public function delete_by_cid($cids){
	   if(!$cids) return false;
	   if(!is_array($cids)){
		   $cids=array($cids);
	   }
	   return DB::delete($this->_table,"cid IN (".dimplode($cids).")");
	}
	public function copy_by_cid($ocid,$cid){
		foreach(DB::fetch_all("select * from %t where cid=%d",array($this->_table,$ocid)) as $value){
			$value['cid']=$cid;
			parent::insert($value,0,1);
		}
		return 0;
	}
	
}

?>
