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

class table_user_profile_setting extends dzz_table
{
	public function __construct() {

		$this->_table = 'user_profile_setting';
		$this->_pk    = 'fieldid';
		$this->_pre_cache_key = 'user_profile_setting_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
   public function delete_by_fieldid($fieldid){
	  $this->clear_cache('fields_0');
	   $this->clear_cache('fields_1');
	   return parent::delete($fieldid);
   }
    public function insert($data,$return_insert_id = false, $replace = false, $silent = false){
	    $this->clear_cache('fields_0');
	    $this->clear_cache('fields_1');
	   return parent::insert($data,$return_insert_id,$replace,$silent);
   }
	public function range($start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' ORDER BY available DESC, displayorder'.DB::limit($start, $limit), null, $this->_pk);
	}
	public function fetch_all_by_available_unchangeable($available, $unchangeable) {
		return DB::fetch_all('SELECT * FROM %t WHERE available=%d AND unchangeable=%d ORDER BY displayorder', array($this->_table, $available, $unchangeable), $this->_pk);
	}
    public function fetch_register_info(){
        return DB::fetch_all("SELECT * FROM %t WHERE `showinregister` = %d AND `available` = %d  ORDER BY displayorder",array($this->_table,1,1));
    }
	public function fetch_all_by_available($available) {
		return DB::fetch_all('SELECT * FROM %t WHERE available=%d ORDER BY displayorder', array($this->_table, $available), $this->_pk);
	}

	public function fetch_all_by_available_formtype($available, $formtype) {
		return DB::fetch_all('SELECT * FROM %t WHERE available=%d AND formtype=%s', array($this->_table, $available, $formtype), $this->_pk);
	}

	public function fetch_all_by_available_required($available, $required) {
		return DB::fetch_all('SELECT * FROM %t WHERE available=%d AND required=%d', array($this->_table, $available, $required), $this->_pk);
	}

	public function fetch_all_by_available_showinregister($available, $showinregister) {
		return DB::fetch_all('SELECT * FROM %t WHERE available=%d AND showinregister=%d', array($this->_table, $available, $showinregister), $this->_pk);
	}
	public function fetch_all_fields_by_available($available=1){//获取资料设置里的fieldid数组
		if(!$available) $available=0;
			$fieldids=array();
			if($available){
				$sql=' and available>0';
			}
			foreach(DB::fetch_all("select fieldid from %t where 1 $sql ",array($this->_table)) as $value){
				$fieldids[]=$value['fieldid'];
			}
		return $fieldids;
	}
	
}
