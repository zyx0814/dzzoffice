<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_session_close {

	private $onlinehold;
	private $oltimestamp;

	public $sid = null;
	public $var;
	public $isnew = false;
	protected $newguest = array('sid' => 0, 'ip1' => 0, 'ip2' => 0, 'ip3' => 0, 'ip4' => 0,
		'uid' => 0, 'username' => '', 'groupid' => 0, 'invisible' => 0, 'action' => 0,
		'lastactivity' => 0, 'fid' => 0, 'tid' => 0, 'lastolupdate' => 0);

	protected $table;

	public function __construct($sid = '', $ip = '', $uid = 0) {
		$this->old = array('sid' =>  $sid, 'ip' =>  $ip, 'uid' =>  $uid);
		$this->var = $this->newguest;
		$this->onlinehold = getglobal('setting/onlinehold');
		$this->oltimestamp = TIMESTAMP - $this->onlinehold;

		$this->table = C::t('user_status');

		if(!empty($ip)) {
			$this->init($sid, $ip, $uid);
		}
	}

	public function set($key, $value) {
		if(isset($this->newguest[$key])) {
			$this->var[$key] = $value;
		} elseif ($key == 'ip') {
			$ips = explode('.', $value);
			$this->set('ip1', $ips[0]);
			$this->set('ip2', $ips[1]);
			$this->set('ip3', $ips[2]);
			$this->set('ip4', $ips[3]);
		}
	}

	public function get($key) {
		if(isset($this->newguest[$key])) {
			return $this->var[$key];
		} elseif ($key == 'ip') {
			return $this->get('ip1').'.'.$this->get('ip2').'.'.$this->get('ip3').'.'.$this->get('ip4');
		}
	}

	public function init($sid, $ip, $uid) {
		if(($uid = intval($uid)) > 0) {
			$this->var = $this->newguest;
			$this->set('sid', 0);
			$this->set('uid', $uid);
			$this->set('username', getglobal('member/username'));
			$this->set('groupid', getglobal('member/groupid'));
			$this->set('ip', $ip);
			if(($ulastactivity = getglobal('cookie/ulastactivity'))) {
				list($lastactivity, $invisible) = explode('|', $ulastactivity);
				$lastactivity = intval($lastactivity);
				$invisible = intval($invisible);
			}
			if(!$lastactivity) {
				$lastactivity = getuserprofile('lastactivity');
				$invisible = getuserprofile('invisible');
				dsetcookie('ulastactivity', $lastactivity.'|'.$invisible, 31536000);
			}
			if($this->oltimestamp >= $lastactivity) {
				$this->isnew = true;
			}
			$this->set('invisible', $invisible);
			$this->set('lastactivity', $lastactivity);
			$this->sid = 0;
		}
	}

	public function create($ip, $uid) {
		return $this->var;
	}

	public function delete() {
		return true;

	}

	public function update() {
		return true;
	}

	public function count($type = 0) {
		loadcache('onlinecount');
		$onlinecount = getglobal('cache/onlinecount');
		if($onlinecount && $onlinecount['dateline'] > TIMESTAMP - 600) {
			$count = $onlinecount['count'];
		} else {
			$count = $this->table->count_by_lastactivity_invisible($this->oltimestamp);
			savecache('onlinecount', array('count' => $count, 'dateline' => TIMESTAMP));
		}
		if($type == 1) {
			return $count;
		}

		if(!($multiple = getglobal('setting/onlineguestsmultiple'))) $multiple = 11;
		$add = mt_rand(0, $multiple);
		if($type == 2) {
			return intval($count * $multiple) + $add - $count;
		} else {
			return intval($count * $multiple) + $add;
		}
	}

	public function fetch_member($ismember = 0, $invisible = 0, $start = 0, $limit = 0) {
		return $this->table->fetch_all_by_lastactivity_invisible($this->oltimestamp, $invisible, $start, $limit);
	}

	public function count_invisible($type = 1) {
		return $this->table->count_by_lastactivity_invisible($this->oltimestamp, $type);
	}

	public function update_by_ipban($ip1, $ip2, $ip3, $ip4) {
		return false;
	}

	public function update_max_rows($max_rows) {
		return false;
	}

	public function clear() {
		return false;
	}

	public function count_by_fid($fid) {
		return 0;
	}

	public function fetch_all_by_fid($fid, $limit) {
		return array();
	}

	public function fetch_by_uid($uid) {
		if(($member = $this->table->fetch($uid)) && $member['lastactivity'] >= $this->oltimestamp) {
			return $member;
		}
		return array();
	}

	public function fetch_all_by_uid($uids, $start = 0, $limit = 0) {
		return $this->table->fetch_all_onlines($uids, $this->oltimestamp, $start, $limit);
	}

	public function update_by_uid($uid, $data) {
		return false;
	}

	public function count_by_ip($ip) {
		return 0;
	}

	public function fetch_all_by_ip($ip, $start = 0, $limit = 0) {
		return array();
	}

	public function updatesession() {
		static $updated = false;
		if(!$updated && $this->isnew) {
			global $_G;
			C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastactivity' => TIMESTAMP, 'lastvisit' => TIMESTAMP));
			dsetcookie('ulastactivity', TIMESTAMP.'|'.getuserprofile('invisible'), 31536000);
			$updated = true;
		}
		return $updated;
	}
}
?>