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

class table_app_relative extends dzz_table
{
	public function __construct() {

		$this->_table = 'app_relative';
		$this->_pk    = 'rid';
		//$this->_pre_cache_key = 'app_relative_';
		//$this->_cache_ttl = 0;

		parent::__construct();
	}

	public function update_by_appid($appid,$tagids){
		//先删除有关此appid的部分
		self::delete_by_appid($appid);
		foreach($tagids as $tagid){
			DB::insert($this->_table,array('appid'=>intval($appid),'tagid'=>intval($tagid)));
		}
	}
	public function delete_by_appid($appid){
		$tagids=array();
		foreach(DB::fetch_all("select tagid from %t where appid=%d",array($this->_table,$appid)) as $value){
			$tagids[]=$value['tagid'];
		}
		C::t('app_tag')->delete_by_tagid($tagids);
		DB::query("DELETE FROM %t WHERE appid= %d ",array($this->_table,$appid));
	}
	public function fetch_all_by_tagid($tagid,$count=false){
		$tagid=intval($tagid);
		if($count) return DB::result_first("select COUNT(*) from %t WHERE　tagid= %d ",array($this->_table,$tagid));
		else return DB::fetch_all("SELECT * FROM %t WHERE tagid = %d ",array($this->_table,$tagid));
	}
	public function fetch_all_by_appid($appid,$count=false){
		$appid=intval($appid);
		if($count) return DB::result_first("select COUNT(*) from %t r LEFT JOIN %t t ON r.tagid=t.tagid WHERE t.tagid>0 and  r.appid = %d ",array($this->_table,'app_tag',$appid));
		else return DB::fetch_all("SELECT t.* FROM %t r LEFT JOIN %t t ON r.tagid=t.tagid WHERE t.tagid>0 and  r.appid = %d ",array($this->_table,'app_tag',$appid));
	}
	public function fetch_appids_by_tagid($tagid){
		$appids=array();
		foreach(DB::fetch_all("select appid from %t where tagid=%d",array($this->_table,$tagid)) as $value){
			$appids[]=$value['appid'];	
		}
		return $appids;
	}
		
}

?>
