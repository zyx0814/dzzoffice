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

class table_iconview extends dzz_table
{
	public function __construct() {

		$this->_table = 'iconview';
		$this->_pk    = 'id';
		/*$this->_pre_cache_key = 'iconview_';
		$this->_cache_ttl = 0;*/
		parent::__construct();
	}
	public function fetch_all(){
		return DB::fetch_all("select * from %t where 1",array($this->_table),'id');
	}
}

?>
