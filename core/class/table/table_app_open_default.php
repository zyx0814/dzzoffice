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

class table_app_open_default extends dzz_table
{
	public function __construct() {

		$this->_table = 'app_open_default';
		$this->_pk    = '';
		//$this->_pre_cache_key = 'app_open_';
		//$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_extid($extid){
		return DB::delete($this->_table," extid='{$extid}'");
	}

	public function insert_default_by_uid($uid,$extid,$ext){
		DB::insert($this->_table,array('uid'=>$uid,'ext'=>$ext,'extid'=>$extid,'dateline'=>TIMESTAMP),0,1);
		return true;
	}
	public function fetch_all_by_uid($uid){
		$data=array();
		$query=DB::query("SELECT ext,extid FROM %t WHERE uid= %d ",array($this->_table,$uid));
		while($value=DB::fetch($query)){
			$data[$value['ext']]=$value['extid'];
		}
		return $data;
	}
	
}

?>
