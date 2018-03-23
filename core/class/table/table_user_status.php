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

class table_user_status extends dzz_table
{
	public function __construct() {

		$this->_table = 'user_status';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'user_status_';

		parent::__construct();
	}

	public function increase($uids, $setarr) {
		$uids = array_map('intval', (array)$uids);
		$sql = array();
		$allowkey = array('buyercredit', 'sellercredit', 'favtimes', 'sharetimes');
		foreach($setarr as $key => $value) {
			if(($value = intval($value)) && in_array($key, $allowkey)) {
				$sql[] = "`$key`=`$key`+'$value'";
			}
		}
		if(!empty($sql)){
			DB::query("UPDATE ".DB::table($this->_table)." SET ".implode(',', $sql)." WHERE uid IN (".dimplode($uids).")", 'UNBUFFERED');
			$this->increase_cache($uids, $setarr);
		}
	}

	public function count_by_ip($ips) {
		return !empty($ips) ? DB::result_first('SELECT COUNT(*) FROM %t WHERE regip IN(%n) OR lastip IN (%n)', array($this->_table, $ips, $ips)) : 0;
	}

	public function fetch_all_by_ip($ips, $start, $limit) {
		$data = array();
		if(!empty($ips) && $limit) {
			$data = DB::fetch_all('SELECT * FROM %t WHERE regip IN(%n) OR lastip IN (%n) LIMIT %d, %d', array($this->_table, $ips, $ips, $start, $limit), 'uid');
		}
		return $data;
	}

	public function fetch_all_orderby_lastpost($uids, $start, $limit) {
		$uids = dintval($uids, true);
		if($uids) {
			return DB::fetch_all('SELECT * FROM %t WHERE uid IN(%n) ORDER BY lastpost DESC '.DB::limit($start, $limit), array($this->_table, $uids), $this->_pk);
		}
		return array();
	}

	public function count_by_lastactivity_invisible($timestamp, $invisible = 0) {
		$addsql = '';
		if($invisible === 1) {
			$addsql = ' AND invisible = 1';
		} elseif($invisible === 2) {
			$addsql = ' AND invisible = 0';
		}
		return $timestamp ? DB::result_first('SELECT COUNT(*) FROM %t WHERE lastactivity >= %d'.$addsql, array($this->_table, $timestamp)) : 0;
	}


	public function fetch_all_by_lastactivity_invisible($timestamp, $invisible = 0, $start = 0, $limit = 0) {
		$data = array();
		if($timestamp) {
			$addsql = '';
			if($invisible === 1) {
				$addsql = ' AND invisible = 1';
			} elseif($invisible === 2) {
				$addsql = ' AND invisible = 0';
			}
			$data = DB::fetch_all('SELECT * FROM %t WHERE lastactivity >= %d'.$addsql.' ORDER BY lastactivity DESC'.DB::limit($start, $limit), array($this->_table, $timestamp), $this->_pk);
		}
		return $data;
	}

	public function fetch_all_onlines($uids, $lastactivity, $start = 0, $limit = 0) {
		$data = array();
		$uids = dintval($uids, true);
		if(!empty($uids)) {
			$ppp = ($ppp = getglobal('ppp')) ? $ppp + 30 : 100;
			if(count($uids) > $ppp) {
				$uids = array_slice($uids, 0, $ppp);
			}
			$length = $limit ? $limit : $start;
			$i = 0;
			foreach($this->fetch_all($uids) as $uid => $member) {
				if($member['lastactivity'] >= $lastactivity) {
					$data[$uid] = $member;
					if($length && $i >= $length) {
						break;
					}
					$i++;
				}
			}
		}
		return $data;
	}
}

?>
