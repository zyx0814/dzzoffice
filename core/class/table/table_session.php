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

class table_session extends dzz_table
{
	public function __construct() {

		$this->_table = 'session';
		$this->_pk    = 'sid';

		parent::__construct();
	}

	public function fetch($sid, $ip = false, $uid = false) {
		if(empty($sid)) {
			return array();
		}
		$this->checkpk();
		$session = parent::fetch($sid);
		if($session && $ip !== false && $ip != "{$session['ip1']}.{$session['ip2']}.{$session['ip3']}.{$session['ip4']}") {
			$session = array();
		}
		if($session && $uid !== false && $uid != $session['uid']) {
			$session = array();
		}
		return $session;
	}

	public function fetch_member($ismember = 0, $invisible = 0, $start = 0, $limit = 0) {
		$sql = array();
		if($ismember === 1) {
			$sql[] = 'uid > 0';
		} elseif($ismember === 2) {
			$sql[] = 'uid = 0';
		}
		if($invisible === 1) {
			$sql[] = 'invisible = 1';
		} elseif($invisible === 2) {
			$sql[] = 'invisible = 0';
		}
		$wheresql = !empty($sql) && is_array($sql) ? ' WHERE '.implode(' AND ', $sql) : '';
		$sql = 'SELECT * FROM %t '.$wheresql.' ORDER BY lastactivity DESC'.DB::limit($start, $limit);
		return DB::fetch_all($sql, array($this->_table), $this->_pk);
	}

	public function count_invisible($type = 1) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE invisible=%d', array($this->_table, $type));
	}

	public function count($type = 0) {
		$condition = $type == 1 ? ' WHERE uid>0 ' : ($type == 2 ? ' WHERE uid=0 ' : '');
		return DB::result_first("SELECT count(*) FROM ".DB::table($this->_table).$condition);

	}

	public function delete_by_session($session, $onlinehold, $guestspan) {
		if(!empty($session) && is_array($session)) {
			$onlinehold = time() - $onlinehold;
			$guestspan = time() - $guestspan;
			$session = daddslashes($session);

			$condition = " sid='{$session[sid]}' ";
			$condition .= " OR lastactivity<$onlinehold ";
			$condition .= " OR (uid='0' AND ip1='{$session['ip1']}' AND ip2='{$session['ip2']}' AND ip3='{$session['ip3']}' AND ip4='{$session['ip4']}' AND lastactivity>$guestspan) ";
			$condition .= $session['uid'] ? " OR (uid='{$session['uid']}') " : '';
			DB::delete('session', $condition);
		}
	}

	public function fetch_by_uid($uid) {
		return !empty($uid) ? DB::fetch_first('SELECT * FROM %t WHERE uid=%d', array($this->_table, $uid)) : false;
	}

	public function fetch_all_by_uid($uids, $start = 0, $limit = 0) {
		$data = array();
		if(!empty($uids)) {
			$data = DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('uid', $uids).DB::limit($start, $limit), array($this->_table), null, 'uid');
		}
		return $data;
	}

	public function update_by_ipban($ip1, $ip2, $ip3, $ip4) {
		$ip1 = intval($ip1);
		$ip2 = intval($ip2);
		$ip3 = intval($ip3);
		$ip4 = intval($ip4);
		return DB::query('UPDATE '.DB::table('session')." SET groupid='6' WHERE ('$ip1'='-1' OR ip1='$ip1') AND ('$ip2'='-1' OR ip2='$ip2') AND ('$ip3'='-1' OR ip3='$ip3') AND ('$ip4'='-1' OR ip4='$ip4')");
	}

	public function update_max_rows($max_rows) {
		return DB::query('ALTER TABLE '.DB::table('session').' MAX_ROWS='.dintval($max_rows));
	}

	public function clear() {
		return DB::query('DELETE FROM '.DB::table('session'));
	}

	public function count_by_fid($fid) {
		return ($fid = dintval($fid)) ? DB::result_first('SELECT COUNT(*) FROM '.DB::table('session')." WHERE uid>'0' AND fid='$fid' AND invisible='0'") : 0;
	}

	public function fetch_all_by_fid($fid, $limit = 12) {
		return ($fid = dintval($fid)) ? DB::fetch_all('SELECT uid, groupid, username, invisible, lastactivity FROM '.DB::table('session')." WHERE uid>'0' AND fid='$fid' AND invisible='0' ORDER BY lastactivity DESC".DB::limit($limit)) : array();
	}

	public function update_by_uid($uid, $data){
		if(($uid = dintval($uid)) && !empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, DB::field('uid', $uid));
		}
		return 0;
	}

	public function count_by_ip($ip) {
		$count = 0;
		if(!empty($ip) && ($ip = explode('.', $ip)) && count($ip) > 2 ) {
			$count = DB::result_first('SELECT COUNT(*) FROM '.DB::table('session')." WHERE ip1='$ip[0]' AND ip2='$ip[1]' AND ip3='$ip[2]'");
		}
		return $count;
	}

	public function fetch_all_by_ip($ip, $start = 0, $limit = 0) {
		$data = array();
		if(!empty($ip) && ($ip = explode('.', $ip)) && count($ip) > 2 ) {
			$data = DB::fetch_all('SELECT * FROM %t WHERE ip1=%d AND ip2=%d AND ip3=%d ORDER BY lastactivity DESC'.DB::limit($start, $limit), array($this->_table, $ip[0], $ip[1], $ip[2]), null);
		}
		return $data;
	}
}

?>
