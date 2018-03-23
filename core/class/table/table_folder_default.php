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

class table_folder_default extends dzz_table
{
	public function __construct() {

		$this->_table = 'folder_default';
		$this->_pk    = 'fid';
		$this->_pre_cache_key = 'folder_default_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function fetch_all(){
		if(($data=$this->fetch_cache('all'))===false){
			$data=DB::fetch_all("SELECT * FROM %t WHERE 1 ORDER BY display", array($this->_table));
		}
		return $data;
	}
	public function fetch_all_by_default(){
		if(($data=$this->fetch_cache('all'))===false){
			$data=DB::fetch_all("SELECT * FROM %t WHERE `default`!='' ORDER BY display DESC", array($this->_table));
		}
		return $data;
	}
	
}

?>
