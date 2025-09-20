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

class table_regip extends dzz_table
{
	public function __construct() {

		$this->_table = 'regip';
		$this->_pk    = '';

		parent::__construct();
	}

	public function fetch_by_ip_dateline($clientip, $dateline) {
		return DB::fetch_first('SELECT count FROM %t WHERE ip=%s AND count>0 AND dateline>%d', array($this->_table, $clientip, $dateline));
	}

	public function count_by_ip_dateline($ctrlip, $dateline) {
		if(!empty($ctrlip)) {
			return DB::result_first('SELECT COUNT(*) FROM %t WHERE '.DB::field('ip', $ctrlip, 'like').' AND count=-1 AND dateline>%d  LIMIT 1', array($this->_table, $dateline));
		}
		return 0;
	}

	public function update_count_by_ip($clientip) {
		return DB::query('UPDATE %t SET count=count+1 WHERE ip=%s AND count>0', array($this->_table, $clientip));
	}

	public function delete_by_dateline($dateline) {
		return DB::query('DELETE FROM %t WHERE dateline<=%d', array($this->_table, $dateline), false, true);
	}

}
?>