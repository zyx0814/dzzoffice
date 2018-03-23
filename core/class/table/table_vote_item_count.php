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

class table_vote_item_count extends dzz_table
{
	public function __construct() {

		$this->_table = 'vote_item_count';
		$this->_pk    = '';

		parent::__construct();
	}
	
	public function insert_by_itemid($itemids,$uid){
		$itemids=(array)$itemids;
		$ret=0;
		foreach($itemids as $itemid){
			$ret+=parent::insert(array('uid'=>$uid,'itemid'=>$itemid,'dateline'=>TIMESTAMP),0,1);
		}
	   return $ret;
	}
	public function delete_by_itemid($itemids){
		$itemids=(array)$itemids;
		return DB::delete($this->_table,"itemid IN (".dimplode($itemids).")");
	}
}
?>
