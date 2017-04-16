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

class table_user extends dzz_table
{
	public function __construct() {

		$this->_table = 'user';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'user_';
		
		parent::__construct();
	}
	public function delete_by_uid($uid){
		$user=parent::fetch($uid);
		if(self::checkfounder($user)){//创始人不能删除
			return false;
		}
		if(parent::delete($uid)){
			C::t('user_field')->delete($uid);
			C::t('user_profile1')->delete($uid);
			C::t('user_status')->delete($uid);
			DB::delete('user_qqconnect',"uid='{$uid}'"); //删除QQ登陆
			C::t('organization_user')->delete_by_uid($uid,0);
			DB::delete('user_thame',"uid='{$uid}'");//删除用户主题
			DB::delete('user_playlist',"uid='{$uid}'"); //删除播放列表
			//删除用户文件
			foreach(DB::fetch_all("select fid from %t where uid=%d and gid<1 ",array('folder',$uid)) as $value){
				C::t('folder')->delete_by_fid($value['fid'],true);
			}
			//删除用户云链接
			foreach(DB::fetch_all("select * from %t where 1",array('connect')) as $cloud){
				if($cloud['dname']) C::t($cloud['dname'])->delete_by_uid($uid);
			}
			wx_deleteUser($uid);
			return true;
		}
		return false;
	}
	public function  checkfounder($user) {

		$founders = str_replace(' ', '', getglobal('config/admincp/founder'));
		if(!$user['uid'] || $user['groupid'] != 1 || $user['adminid'] != 1) {
			return false;
		} elseif(empty($founders)) {
			return false;
		} elseif(strexists(",$founders,", ",$user[uid],")) {
			return true;
		} elseif(!is_numeric($user['nickname']) && strexists(",$founders,", ",$user[nickname],")) {
			return true;
		} else {
			return false;
		}
	}
	public function setAdministror($uid,$groupid){
		$user=getuserbyuid($uid);
		//if($user['adminid']==$adminid) return true;
		if(self::checkfounder($user)){ //创始人不允许修改
			return true;
		}
		$arr=array();
		if($groupid==1){
			parent::update($uid,array('adminid'=>1,'groupid'=>1));
		}else{
			if(empty($groupid)) $groupid=9;
			if(C::t('organization_admin')->fetch_orgids_by_uid($uid)){
				$groupid=2;
			}
			parent::update($uid,array('adminid'=>0,'groupid'=>$groupid));
		}
	}
	public function update_credits($uid, $credits) {
		if($uid) {
			$data = array('credits'=>intval($credits));
			DB::update($this->_table, $data, array('uid' => intval($uid)), 'UNBUFFERED');
			$this->update_cache($uid, $data);
		}
	}

	public function update_by_groupid($groupid, $data) {
		$uids = array();
		$groupid = dintval($groupid, true);
		if($groupid && $this->_allowmem) {
			$uids = array_keys($this->fetch_all_by_groupid($groupid));
		}
		if($groupid && !empty($data) && is_array($data)) {
			DB::update($this->_table, $data, DB::field('groupid', $groupid), 'UNBUFFERED');
		}
		if($uids) {
			$this->update_cache($uids, $data);
		}
	}

	public function increase($uids, $setarr) {
		$uids = dintval((array)$uids, true);
		$sql = array();
		$allowkey = array('newprompt');
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

	

	public function fetch_all_by_username($usernames, $fetch_archive = 1) {
		$users = array();
		if(!empty($usernames)) {
			$users = DB::fetch_all('SELECT * FROM %t WHERE username IN (%n)', array($this->_table, (array)$usernames), 'username');
			/*if($fetch_archive && count($usernames) !== count($users)) {
				$users += C::t($this->_table.'_archive')->fetch_all_by_username($usernames, 0);
			}*/
		}
		return $users;
	}

	public function fetch_uid_by_username($username, $fetch_archive = 0) {
		$uid = 0;
		if($username) {
			$uid = DB::result_first('SELECT uid FROM %t WHERE username=%s', array($this->_table, $username));
			/*if($fetch_archive && empty($uid)) {
				$uid = C::t($this->_table.'_archive')->fetch_uid_by_username($username, 0);
			}*/
		}
		return $uid;
	}

	public function fetch_all_uid_by_username($usernames, $fetch_archive = 1) {
		$uids = array();
		if($usernames) {
			foreach($this->fetch_all_by_username($usernames, $fetch_archive) as $username => $value) {
				$uids[$username] = $value['uid'];
			}
		}
		return $uids;
	}

	public function fetch_all_by_adminid($adminids, $fetch_archive = 1) {
		$users = array();
		$adminids = dintval((array)$adminids, true);
		if($adminids) {
			$users = DB::fetch_all('SELECT * FROM %t WHERE adminid IN (%n) ORDER BY adminid, uid', array($this->_table, (array)$adminids), $this->_pk);
			/*if( $fetch_archive) {
				$users += C::t($this->_table.'_archive')->fetch_all_by_adminid($adminids, 0);
			}*/
		}
		return $users;
	}

	public function fetch_all_username_by_uid($uids) {
		$users = array();
		if(($uids = dintval($uids, true))) {
			foreach($this->fetch_all($uids) as $uid => $value) {
				$users[$uid] = $value['username'];
			}
		}
		return $users;
	}

	public function count_by_groupid($groupid) {
		return $groupid ? DB::result_first('SELECT COUNT(*) FROM %t WHERE '.DB::field('groupid', $groupid), array($this->_table)) : 0;
	}

	public function fetch_all_by_groupid($groupid, $start = 0, $limit = 0) {
		$users = array();
		if(($groupid = dintval($groupid, true))) {
			$users = DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('groupid', $groupid).' '.DB::limit($start, $limit), null, 'uid');
		}
		return $users;
	}

	public function fetch_all_groupid() {
		return DB::fetch_all('SELECT DISTINCT(groupid) FROM '.DB::table($this->_table), null, 'groupid');
	}

	public function fetch_all_by_allowadmincp($val, $glue = '=') {
		return DB::fetch_all('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('allowadmincp', intval($val), $glue), NULL, 'uid');
	}

	public function update_admincp_manage($uids) {
		if(($uids = dintval($uids, true))) {
			$data = DB::query('UPDATE '.DB::table($this->_table).' SET allowadmincp=allowadmincp | 1 WHERE uid IN ('.dimplode($uids).')');
			$this->reset_cache($uids);
			return $data;
		}
		return false;
	}

	public function clean_admincp_manage($uids) {
		if(($uids = dintval($uids, true))) {
			$data = DB::query('UPDATE '.DB::table($this->_table).' SET allowadmincp=allowadmincp & 0xFE WHERE uid IN ('.dimplode($uids).')');
			$this->reset_cache($uids);
			return $data;
		}
		return false;
	}

	

	public function fetch_by_email($email, $fetch_archive = 0) {
		$user = array();
		if($email) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE email=%s', array($this->_table, $email));
			/*if( $fetch_archive && empty($user)) {
				$user = C::t($this->_table.'_archive')->fetch_by_email($email, 0);
			}*/
		}
		return $user;
	}
	public function fetch_by_username($username, $fetch_archive = 0) {
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE username=%s', array($this->_table, $username));
			/*if( $fetch_archive && empty($user)) {
				$user = C::t($this->_table.'_archive')->fetch_by_username($username, 0);
			}*/
		}
		return $user;
	}
	public function fetch_by_nickname($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE nickname=%s', array($this->_table, $username));
			/*if( $fetch_archive && empty($user)) {
				$user = C::t($this->_table.'_archive')->fetch_by_nickname($username, 0);
			}*/
		}
		return $user;
	}
	public function fetch_by_phone($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE phone=%s', array($this->_table, $username));
			/*if( $fetch_archive && empty($user)) {
				$user = C::t($this->_table.'_archive')->fetch_by_nickname($username, 0);
			}*/
		}
		return $user;
	}
	public function fetch_by_weixinid($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE weixinid=%s', array($this->_table, $username));
			/*if( $fetch_archive && empty($user)) {
				$user = C::t($this->_table.'_archive')->fetch_by_nickname($username, 0);
			}*/
		}
		return $user;
	}
	public function fetch_by_wechat_userid($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE wechat_userid=%s', array($this->_table, $username));
			/*if( $fetch_archive && empty($user)) {
				$user = C::t($this->_table.'_archive')->fetch_by_nickname($username, 0);
			}*/
		}
		return $user;
	}
	public function fetch_by_uid($uid, $fetch_archive = 0) {
		$user = array();
		if($uid) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE uid=%d', array($this->_table, $uid));
			/*if( $fetch_archive && empty($user)) {
				$user = C::t($this->_table.'_archive')->fetch_by_uid($uid, 0);
			}*/
		}
		return $user;
	}

	public function fetch_all_by_email($emails, $fetch_archive = 1) {
		$users = array();
		if(!empty($emails)) {
			$users = DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, DB::field('email', $emails)), 'email');
			/*if( $fetch_archive && count($emails) !== count($users)) {
				$users += C::t($this->_table.'_archive')->fetch_all_by_email($emails, 0);
			}*/
		}
		return $users;
	}

	public function count_by_email($email, $fetch_archive = 0) {
		$count = 0;
		if($email) {
			$count = DB::result_first('SELECT COUNT(*) FROM %t WHERE email=%s', array($this->_table, $email));
			/*if( $fetch_archive) {
				$count += C::t($this->_table.'_archive')->count_by_email($email, 0);
			}*/
		}
		return $count;
	}

	public function fetch_all_by_like_username($username, $start = 0, $limit = 0) {
		$data = array();
		if($username) {
			$data = DB::fetch_all('SELECT * FROM %t WHERE username LIKE %s'.DB::limit($start, $limit), array($this->_table, stripsearchkey($username).'%'), 'uid');
		}
		return $data;
	}

	public function count_by_like_username($username) {
		return !empty($username) ? DB::result_first('SELECT COUNT(*) FROM %t WHERE username LIKE %s', array($this->_table, stripsearchkey($username).'%')) : 0;
	}


	public function fetch_runtime() {
		return DB::result_first("SELECT (MAX(regdate)-MIN(regdate))/86400 AS runtime FROM ".DB::table($this->_table));
	}

	public function count_admins() {
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->_table)." WHERE adminid<>'0' AND adminid<>'-1'");
	}

	public function count_by_regdate($timestamp) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE regdate>%d', array($this->_table, $timestamp));
	}

	public function fetch_all_stat_memberlist($username, $orderby = '', $sort = '', $start = 0, $limit =  0) {
		$orderby = in_array($orderby, array('uid','credits','regdate', 'gender','username','posts','lastvisit'), true) ? $orderby : 'uid';
		$sql = '';

		$sql = !empty($username) ? " WHERE username LIKE '".addslashes(stripsearchkey($username))."%'" : '';

		$memberlist = array();
		$query = DB::query("SELECT m.uid, m.username, mp.gender, m.email, m.regdate, ms.lastvisit, mc.posts, m.credits
			FROM ".DB::table($this->_table)." m
			LEFT JOIN ".DB::table('user_profile')." mp ON mp.uid=m.uid
			LEFT JOIN ".DB::table('user_status')." ms ON ms.uid=m.uid
			$sql ORDER BY ".DB::order($orderby, $sort).DB::limit($start, $limit));
		while($member = DB::fetch($query)) {
			$member['usernameenc'] = rawurlencode($member['username']);
			$member['regdate'] = dgmdate($member['regdate']);
			$member['lastvisit'] = dgmdate($member['lastvisit']);
			$memberlist[$member['uid']] = $member;
		}
		return $memberlist;
	}

	

	public function insert($uid, $ip, $groupid, $extdata, $adminid = 0) {
		if(($uid = dintval($uid))) {
			$profile = isset($extdata['profile']) ? $extdata['profile'] : array();
			//$profile['uid'] = $uid;
			$base = array(
				'uid' => $uid,
				'adminid' => intval($adminid),
				'groupid' => intval($groupid),
				'regdate' => TIMESTAMP,
				'emailstatus' => intval($extdata['emailstatus']),
				
			);
			$status = array(
				'uid' => $uid,
				'regip' => (string)$ip,
				'lastip' => (string)$ip,
				'lastvisit' => TIMESTAMP,
				'lastactivity' => TIMESTAMP,
				'lastsendmail' => 0
			);
			
			$ext = array('uid' => $uid);
			parent::update($uid,$base);
			C::t('user_status')->insert($status, false, true);
			C::t('user_profile1')->update($uid,$profile);
		}
	}

	public function delete($val, $unbuffered = false, $fetch_archive = 0) {
		$ret = false;
		
		if(($val = dintval($val, true))) {
			foreach((array)$val as $key=> $uid) {
				if($uid==1) unset($val[$key]); //暂时限制uid=1的用户不允许删除
			}
			$ret = parent::delete($val, $unbuffered, $fetch_archive);
			if($this->_allowmem) {
				$data = ($data = memory('get', 'deleteuids')) === false ? array() : $data;
				foreach((array)$val as $uid) {
					$data[$uid] = $uid;
				}
				memory('set', 'deleteuids', $data, 86400*2);
			}
		}
		return $ret;
	}


	/*public function fetch_all_for_spacecp_search($wherearr, $fromarr, $start = 0, $limit = 100) {
		if(!$start && !$limit) {
			$start = 100;
		}
		if(!$wherearr) {
			$wherearr[] = '1';
		}
		if(!$fromarr) {
			$fromarr[] = DB::table($this->_table);
		}
		return DB::fetch_all("SELECT s.* FROM ".implode(',', $fromarr)." WHERE ".implode(' AND ', $wherearr).DB::limit($start, $limit));
	}*/


	public function max_uid() {
		return DB::result_first('SELECT MAX(uid) FROM %t', array($this->_table));
	}

	public function range_by_uid($from, $limit) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid >= %d ORDER BY uid LIMIT %d', array($this->_table, $from, $limit), $this->_pk);
	}

	public function update_groupid_by_groupid($source, $target) {
		return DB::query('UPDATE %t SET groupid=%d WHERE adminid <= 0 AND groupid=%d', array($this->_table, $target, $source));
	}
}

?>
