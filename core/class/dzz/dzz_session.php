<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_session {

	public $sid = null;
	public $var;
	public $isnew = false;
	private $newguest = array('sid' => 0, 'ip1' => 0, 'ip2' => 0, 'ip3' => 0, 'ip4' => 0,
		'uid' => 0, 'username' => '', 'groupid' => 0, 'invisible' => 0, 'action' => 0,
		'lastactivity' => 0,  'lastolupdate' => 0);

	private $old =  array('sid' =>  '', 'ip' =>  '', 'uid' =>  0);

	private $table;

	public function __construct($sid = '', $ip = '', $uid = 0) {
		$this->old = array('sid' =>  $sid, 'ip' =>  $ip, 'uid' =>  $uid);
		$this->var = $this->newguest;

		$this->table = C::t('session');

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
		$this->old = array('sid' =>  $sid, 'ip' =>  $ip, 'uid' =>  $uid);
		$session = array();
		if($sid) {
			$session = $this->table->fetch($sid, $ip, $uid);
		}

		if(empty($session) || $session['uid'] != $uid) {
			$session = $this->create($ip, $uid);
		}

		$this->var = $session;
		$this->sid = $session['sid'];
	}

	public function create($ip, $uid) {

		$this->isnew = true;
		$this->var = $this->newguest;
		$this->set('sid', random(6));
		$this->set('uid', $uid);
		$this->set('ip', $ip);
		$uid && $this->set('invisible', getuserprofile('invisible'));
		$this->set('lastactivity', time());
		$this->sid = $this->var['sid'];

		return $this->var;
	}

	public function delete() {

		return $this->table->delete_by_session($this->var, getglobal('setting/onlinehold'), 60);

	}

	public function update() {
		if($this->sid !== null) {

			if($this->isnew) {
				$this->delete();
				$this->table->insert($this->var, false, false, true);
			} else {
				$this->table->update($this->var['sid'], $this->var);
			}
			setglobal('sessoin', $this->var);
			dsetcookie('sid', $this->sid, 86400);
		}
	}

	public function count($type = 0) {
		return $this->table->count($type);
	}

	public function fetch_member($ismember = 0, $invisible = 0, $start = 0, $limit = 0) {
		return $this->table->fetch_member($ismember, $invisible, $start, $limit);
	}

	public function count_invisible($type = 1) {
		return $this->table->count_invisible($type);
	}

	public function update_by_ipban($ip1, $ip2, $ip3, $ip4) {
		return $this->table->update_by_ipban($ip1, $ip2, $ip3, $ip4);
	}

	public function update_max_rows($max_rows) {
		return $this->table->update_max_rows($max_rows);
	}

	public function clear() {
		return $this->table->clear();
	}


	

	public function fetch_by_uid($uid) {
		return $this->table->fetch_by_uid($uid);
	}

	public function fetch_all_by_uid($uids, $start = 0, $limit = 0) {
		return $this->table->fetch_all_by_uid($uids, $start, $limit);
	}

	public function update_by_uid($uid, $data) {
		return $this->table->update_by_uid($uid, $data);
	}

	public function count_by_ip($ip) {
		return $this->table->count_by_ip($ip);
	}

	public function fetch_all_by_ip($ip, $start = 0, $limit = 0) {
		return $this->table->fetch_all_by_ip($ip, $start, $limit);
	}

	public static function updatesession() {
		static $updated = false;
		if(!$updated) {
			global $_G;
			if($_G['uid']) {
				if($_G['cookie']['ulastactivity']) {
					$ulastactivity = authcode($_G['cookie']['ulastactivity'], 'DECODE');
				} else {
					$ulastactivity = getuserprofile('lastactivity');
					dsetcookie('ulastactivity', authcode($ulastactivity, 'ENCODE'), 31536000);
				}
			}
			$oltimespan = $_G['setting']['oltimespan'];
			$lastolupdate = C::app()->session->var['lastolupdate'];
			if($_G['uid'] && $oltimespan && TIMESTAMP - ($lastolupdate ? $lastolupdate : $ulastactivity) > $oltimespan * 60) {
				$isinsert = false;
				if(C::app()->session->isnew) {
					$oldata = C::t('onlinetime')->fetch($_G['uid']);
					if(empty($oldata)) {
						$isinsert = true;
					} else if(TIMESTAMP - $oldata['lastupdate'] > $oltimespan * 60) {
						C::t('onlinetime')->update_onlinetime($_G['uid'], $oltimespan, $oltimespan, TIMESTAMP);
					}
				} else {
					$isinsert = !C::t('onlinetime')->update_onlinetime($_G['uid'], $oltimespan, $oltimespan, TIMESTAMP);
				}
				if($isinsert) {
					C::t('onlinetime')->insert(array(
						'uid' => $_G['uid'],
						'thismonth' => $oltimespan,
						'total' => $oltimespan,
						'lastupdate' => TIMESTAMP,
					));
				}
				C::app()->session->set('lastolupdate', TIMESTAMP);
			}
			foreach(C::app()->session->var as $k => $v) {
				if(isset($_G['member'][$k]) && $k != 'lastactivity') {
					C::app()->session->set($k, $_G['member'][$k]);
				}
			}
			if(isset($_G['action'])){
                foreach($_G['action'] as $k => $v) {
                    C::app()->session->set($k, $v);
                }
            }


			C::app()->session->update();

			if($_G['uid'] && TIMESTAMP - $ulastactivity > 21600) {
				if($oltimespan && TIMESTAMP - $ulastactivity > 43200) {
					$onlinetime = C::t('onlinetime')->fetch($_G['uid']);
					//C::t('user_count')->update($_G['uid'], array('oltime' => round(intval($onlinetime['total']) / 60)));
				}
				dsetcookie('ulastactivity', authcode(TIMESTAMP, 'ENCODE'), 31536000);
				C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastactivity' => TIMESTAMP, 'lastvisit' => TIMESTAMP));
			}
			$updated = true;
		}
		return $updated;
	}
}
?>