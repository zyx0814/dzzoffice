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

class table_usergroup_field extends dzz_table
{
	public function __construct() {

		$this->_table = 'usergroup_field';
		$this->_pk    = 'groupid';

		parent::__construct();
	}

	public function fetch_all() {
		return DB::fetch_all("SELECT * FROM %t where 1", array($this->_table),$this->_pk);
	}

}

?>
