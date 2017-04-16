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

class table_statuser extends dzz_table
{
	public function __construct() {

		$this->_table = 'statuser';
		$this->_pk    = '';

		parent::__construct();
	}

	public function check_exists($uid, $daytime, $type) {

		$setarr = array(
			'uid' => intval($uid),
			'daytime' => intval($daytime),
			'type' => $type
		);
		if(DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table).' WHERE '.DB::implode_field_value($setarr, ' AND '))) {
			return true;
		} else {
			return false;
		}
	}

	public function clear_by_daytime($daytime) {
		$daytime = intval($daytime);
		DB::delete('statuser', "`daytime` != '$daytime'");
	}
}

?>
