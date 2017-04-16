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

class table_shou_record extends dzz_table
{
	public function __construct() {

		$this->_table = 'shou_record';
		$this->_pk    = 'rid';
		
		parent::__construct();
	}
	public function delete_by_rid($rid){
		$data=parent::fetch($rid);
		if(parent::delete($rid)){
			C::t('attachment')->delete_by_aid($data['aid']);
			return true;
		}
		return false;
	}
}
?>
