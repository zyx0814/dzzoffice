<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
	exit('Access Denied');
}

class table_test extends dzz_table {
	public function __construct() {

		$this -> _table = 'test';
		$this -> _pk = 'testid';

		parent::__construct();
	}
	
	public function fetchall(){
		$data=array();
		foreach(DB::fetch_all("select * from %t ",array($this->_table)) as $value){
			$data[]=$value;
		}
		return $data;
	}
}
?>
