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

class table_usergroup extends dzz_table
{
	public function __construct() {

		$this->_table = 'usergroup';
		$this->_pk    = 'groupid';

		parent::__construct();
	}

	public function fetch_by_credits($credits, $type = 'member') {
		if(is_array($credits)) {
			$creditsf = intval($credits[0]);
			$creditse = intval($credits[1]);
		} else {
			$creditsf = $creditse = intval($credits);
		}
		return DB::fetch_first('SELECT grouptitle, groupid FROM %t WHERE '.($type ? DB::field('type', $type).' AND ' : '').'%d>=creditshigher AND %d<creditslower LIMIT 1', array($this->_table, $creditsf, $creditse));
	}

	public function fetch_all_by_type($type = '', $radminid = null, $allfields = false) {
		$parameter = array($this->_table);
		$wherearr = array();
		if(!empty($type)) {
			$parameter[] = $type;
			$wherearr[] = is_array($type) ? 'type IN(%n)' : 'type=%s';
		}
		if($radminid !== null) {
			$parameter[] = $radminid;
			$wherearr[] = 'radminid=%d';
		}
		$wheresql = !empty($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::fetch_all('SELECT '.($allfields ? '*' : 'groupid, grouptitle').' FROM %t '.$wheresql, $parameter, $this->_pk);
	}

	public function update($id, $data, $type = '') {
		if(!is_array($data) || !$data || !is_array($data) || !$id) {
			return null;
		}
		$condition = DB::field('groupid', $id);
		if($type) {
			$condition .= ' AND '.DB::field('type', $type);
		}
		return DB::update($this->_table, $data, $condition);
	}

	public function delete($id, $type = '') {
		if(!$id) {
			return null;
		}
		$condition = DB::field('groupid', $id);
		if($type) {
			$condition .= ' AND '.DB::field('type', $type);
		}
		return DB::delete($this->_table, $condition);
	}


	public function fetch_all_by_groupid($gid) {
		if(!$gid) {
			return null;
		}
		return DB::fetch_all('SELECT groupid FROM %t WHERE groupid IN (%n) AND type=\'special\' AND radminid>0', array($this->_table, $gid), $this->_pk);
	}

	public function fetch_all_by_not_groupid($gid) {
		return DB::fetch_all('SELECT groupid, type, grouptitle, creditshigher, radminid FROM %t WHERE type=\'member\' AND creditshigher=\'0\' OR (groupid NOT IN (%n) AND radminid<>\'1\' AND type<>\'member\') ORDER BY (creditshigher<>\'0\' || creditslower<>\'0\'), creditslower, groupid', array($this->_table, $gid), $this->_pk);
	}

	public function fetch_all_not($gid, $creditnotzero = false) {
		return DB::fetch_all('SELECT groupid, radminid, type, grouptitle, creditshigher, creditslower FROM %t WHERE groupid NOT IN (%n) ORDER BY '.($creditnotzero ? "(creditshigher<>'0' || creditslower<>'0'), " : '').'creditshigher, groupid', array($this->_table, $gid), $this->_pk);
	}

	public function fetch_new_groupid($fetch = false) {
		$sql = 'SELECT groupid, grouptitle FROM '.DB::table($this->_table)." WHERE type='member' AND creditslower>'0' ORDER BY creditslower LIMIT 1";
		if($fetch) {
			return DB::fetch_first($sql);
		} else {
			return DB::result_first($sql);
		}
	}
	public function fetch_all($ids) {
		if(!$ids) {
			return null;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('groupid', $ids).' ORDER BY type, radminid, creditshigher', array($this->_table), $this->_pk);
	}

	public function fetch_all_switchable($ids) {
		if(!$ids) {
			return null;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE (type=\'special\' AND system<>\'private\' AND radminid=\'0\') OR groupid IN (%n) ORDER BY type, system', array($this->_table, $ids), $this->_pk);
	}

	public function range_orderby_credit() {
		return DB::fetch_all('SELECT * FROM %t ORDER BY (creditshigher<>\'0\' || creditslower<>\'0\'), creditslower, groupid', array($this->_table), $this->_pk);
	}

	public function range_orderby_creditshigher() {
		return DB::fetch_all('SELECT * FROM %t ORDER BY creditshigher', array($this->_table), $this->_pk);
	}

	public function fetch_all_by_radminid($radminid, $glue = '>', $orderby = 'type'){
		$ordersql = '';
		if($ordersql = DB::order($orderby, 'DESC')) {
			$ordersql = ' ORDER BY '.$ordersql;
		}
		return DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, DB::field('radminid', intval($radminid), $glue) . $ordersql), 'groupid');
	}

	public function fetch_table_struct($result = 'FIELD') {
		$datas = array();
		$query = DB::query('DESCRIBE %t', array($this->_table));
		while($data = DB::fetch($query)) {
			$datas[$data['Field']] = $result == 'FIELD' ? $data['Field'] : $data;
		}
		return $datas;
	}

	public function buyusergroup_exists() {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE type='special' and system>0", array($this->_table));
	}
}

?>
