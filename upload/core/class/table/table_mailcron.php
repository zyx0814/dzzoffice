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

class table_mailcron extends dzz_table
{
	public function __construct() {

		$this->_table = 'mailcron';
		$this->_pk    = 'cid';

		parent::__construct();
	}

	public function delete_by_touid($touids) {
		if(empty($touids)) {
			return false;
		}
		return DB::query('DELETE FROM mc, mq USING %t AS mc, %t AS mq WHERE mc.'.DB::field('touid', $touids).' AND mc.cid=mq.cid',
				array($this->_table, 'mailqueue'), false, true);
	}

	public function fetch_all_by_email($email, $start, $limit) {
		return DB::fetch_all('SELECT * FROM %t WHERE email=%s '.DB::limit($start, $limit), array($this->_table, $email));
	}

	public function fetch_all_by_touid($touid, $start, $limit) {
		return DB::fetch_all('SELECT * FROM %t WHERE touid=%d '.DB::limit($start, $limit), array($this->_table, $touid));
	}

	public function fetch_all_by_sendtime($sendtime, $start, $limit) {
		return DB::fetch_all('SELECT * FROM %t WHERE sendtime<=%d ORDER BY sendtime '.DB::limit($start, $limit), array($this->_table, $sendtime));
	}
}

?>
