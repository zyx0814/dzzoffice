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

class table_cron extends dzz_table
{
	public function __construct() {

		$this->_table = 'cron';
		$this->_pk    = 'cronid';

		parent::__construct();
	}

	public function fetch_nextrun($timestamp) {
		$timestamp = intval($timestamp);
		return DB::fetch_first('SELECT * FROM '.DB::table($this->_table)." WHERE available>'0' AND nextrun<='$timestamp' ORDER BY nextrun LIMIT 1");
	}

	public function fetch_nextcron() {
		return DB::fetch_first('SELECT * FROM '.DB::table($this->_table)." WHERE available>'0' ORDER BY nextrun LIMIT 1");
	}

	public function get_cronid_by_filename($filename) {
		return DB::result_first('SELECT cronid FROM '.DB::table($this->_table)." WHERE filename='$filename'");
	}

}

?>
