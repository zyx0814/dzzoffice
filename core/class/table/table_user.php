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
	/*public function fetch_safebindstatus($uid){
	    $uid = intval($uid);
        $result = DB::fetch_first("select emailstatus,phonestatus from %t where uid = %d",array($this->_table,$uid));
        return $result;
    }*/
    public function add_user($userArr){

        global $_G;

        if(empty($userArr)) return ;
        $salt=substr(uniqid(rand()), -6);
        $groupid = '';
        if($_G['setting']['regverify']) {
            $groupid = 8;
        } else {
            $groupid =$_G['setting']['newusergroupid'];
        }
        $setarr=array(
            'username'=>addslashes($userArr['username']),
            'email'=>isset($userArr['email']) ? $userArr['email']:'' ,
            'salt'=>$salt,
            'password'=>md5(md5($userArr['password']).$salt),
            'regdate'=>TIMESTAMP,
            'regip'=>$_G['clientip'],
            'groupid'=>$groupid
        );
        $setarr['uid'] = parent::insert($setarr,1);
        return $setarr;
    }
    public function update_password($uid,$password){
        $uid = intval($uid);
        if(parent::update($uid,array('password'=>$password))){
            return true;
        }
        return false;
    }
	public function user_register($userArr,$addorg = 1){

        if(empty($userArr)) return ;

        if($userArr['username'] && ($status = uc_user_checkname($userArr['username'])) < 0) {
            return $status;
        }

        if(($status = uc_user_checkemail($userArr['email'])) < 0) {

            return $status;
        }

        $uid =self::add_user($userArr);

        //默认机构
        if($addorg && is_array($uid) && getglobal('setting/defaultdepartment') && DB::fetch_first("select orgid from %t where orgid=%d ",array('organization',getglobal('setting/defaultdepartment')))){
            C::t('organization_user')->insert_by_orgid(getglobal('setting/defaultdepartment'),$uid['uid']);
        }

        return $uid;
    }
	public function delete_by_uid($uid){
		$user=parent::fetch($uid);
		if(self::checkfounder($user)){//创始人不能删除
			return false;
		}
			  
		if(parent::delete($uid)){
			C::t('user_field')->delete($uid);
			C::t('user_profile')->delete($uid);
			C::t('user_status')->delete($uid);
			C::t('user_setting')->delete_by_uid($uid);
			C::t('organization_user')->delete_by_uid($uid,0);
			
			//删除用户文件
			if($homefid=DB::result_first("select fid from %t where uid=%d and flag='home' ",array('folder',$uid))){
				C::t('folder')->delete_by_fid($homefid,true);
			}
			 
			Hook::listen('syntoline_user',$uid,'del');//删除对应到三方用户表
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
			/*if(C::t('organization_admin')->fetch_orgids_by_uid($uid)){
				$groupid=2;
			}*/
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

	public function fetch_userbasic_by_uid($uid){

	    return DB::fetch_first("select uid,email,username from %t where uid = %d",array($this->_table,$uid));
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
		}
		return $users;
	}
	/*
	 * 新增
	 * 以uid查询用户数据
	 * **/
	public function get_user_by_uid($uid){

	    $uid = intval($uid);

        static $users = array();

        if($uid && empty($users[$uid])) {

           $users[$uid] =  DB::fetch_first("select * from %t  where uid = %d",array($this->_table,$uid));
        }

        if($users[$uid]['adminid']==1) $users[$uid]['self'] = 2;

        return $users[$uid];

    }

	public function fetch_uid_by_username($username, $fetch_archive = 0) {
		$uid = 0;
		if($username) {
			$uid = DB::result_first('SELECT uid FROM %t WHERE username=%s', array($this->_table, $username));
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
    public function chk_email_by_uid($email,$uid){

        if(parent::fetch_all("select uid from %t where email = %s and uid != %d",array($this->_table,$email,$uid))){

            return true;
        }

        return false;
    }
	public function fetch_by_email($email, $fetch_archive = 0) {
		$user = array();
		if($email) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE email=%s', array($this->_table, $email));
		}
		return $user;
	}
	public function fetch_by_username($username, $fetch_archive = 0) {
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE username=%s', array($this->_table, $username));
		}
		return $user;
	}
	public function fetch_by_nickname($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE nickname=%s', array($this->_table, $username));
		}
		return $user;
	}
	public function fetch_by_phone($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE phone=%s', array($this->_table, $username));
		}
		return $user;
	}
	public function fetch_by_weixinid($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE weixinid=%s', array($this->_table, $username));
		}
		return $user;
	}
	public function fetch_by_wechat_userid($username, $fetch_archive = 0) {
		
		$user = array();
		if($username) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE wechat_userid=%s', array($this->_table, $username));
		}
		return $user;
	}
	public function fetch_by_uid($uid, $fetch_archive = 0) {
		$user = array();
		if($uid) {
			$user = DB::fetch_first('SELECT * FROM %t WHERE uid=%d', array($this->_table, $uid));
		}
		return $user;
	}

	public function fetch_all_by_email($emails, $fetch_archive = 1) {
		$users = array();
		if(!empty($emails)) {
			$users = DB::fetch_all('SELECT * FROM %t WHERE %i', array($this->_table, DB::field('email', $emails)), 'email');
		}
		return $users;
	}

	public function count_by_email($email, $fetch_archive = 0) {
		$count = 0;
		if($email) {
			$count = DB::result_first('SELECT COUNT(*) FROM %t WHERE email=%s', array($this->_table, $email));
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
	//根据用户组id，查询用户id
	public function fetch_uid_by_groupid($groupid){
	    $groupid = intval($groupid);
        return DB::fetch_all("select uid from %t where groupid = %d",array($this->_table,$groupid));
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
			C::t('user_profile')->update($uid,$profile);
		}
	}
	public function insert_user($userarr,$groupid = 9,$profilearr=array()){
	    global $_G;
        if(empty($userarr)){
            return false;
        }
        $ip = $_G['clientip'];
        $salt=substr(uniqid(rand()), -6);
        $setarr=array(
            'username'=>addslashes($userarr['username']),
            'email'=>$userarr['email'],
            'salt'=>$salt,
            'password'=>md5(md5($userarr['password']).$salt),
            'regdate'=>TIMESTAMP,
            'regip'=>$ip,
            'groupid'=>$groupid,
            'phone'=>$userarr['phone'],
            'phonestatus'=>$userarr['phonestatus']
        );
        $uid = parent::insert($setarr,1);
        if($uid){
            $status = array(
                'uid' => $uid,
                'regip' => (string)$ip,
                'lastip' => (string)$ip,
                'lastvisit' => TIMESTAMP,
                'lastactivity' => TIMESTAMP,
                'lastsendmail' => 0
            );
            C::t('user_status')->insert($status,1);
            if(!empty($profilearr)){
                C::t('user_profile')->update($uid,$profilearr);
            }
            $setarr['uid'] = $uid;
            return $setarr;
        }else{
            return false;
        }



    }
	public function insert_user_setarr($setarr){
		if(empty($setarr)) return ;
		return parent::insert($setarr,1);
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

	public function max_uid() {
		return DB::result_first('SELECT MAX(uid) FROM %t', array($this->_table));
	}

	public function range_by_uid($from, $limit) {
		return DB::fetch_all('SELECT * FROM %t WHERE uid >= %d ORDER BY uid LIMIT %d', array($this->_table, $from, $limit), $this->_pk);
	}

	public function update_groupid_by_groupid($source, $target) {
		return DB::query('UPDATE %t SET groupid=%d WHERE adminid <= 0 AND groupid=%d', array($this->_table, $target, $source));
	}
	public function fetch_all_user(){
	    return DB::fetch_all("select * from %t",array($this->_table));
    }
    //获取用户独享空间配置值,若未分配，则获取用户已使用空间
    public function get_allotspace(){
        global $_G;
        $setting = $_G['setting'];
        $userallotspace = 0;
        $uids = array();
        foreach(DB::fetch_all("select uid from %t",array($this->_table)) as $v){
            $uids[] = $v['uid'];
        }
        foreach(DB::fetch_all("select userspace,usesize from %t where uid in(%n)",array('user_field',$uids)) as $val){
            if($val['userspace'] > 0){
                $userallotspace += $val['userspace']*1024*1024;
            }else{
                $userallotspace += $val['usesize'];
            }
        }
        return $userallotspace;
    }
    //获取用户信息包含头像信息
    public function fetch_user_avatar_by_uids($uids){
        if(!is_array($uids)) $uids = array($uids);
        $uids = array_unique($uids);
        $users = array();
        foreach(DB::fetch_all("select u.*,s.svalue from %t u left join %t s on u.uid=s.uid and s.skey=%s where u.uid in(%n)",array('user','user_setting','headerColor',$uids)) as $v){
            if($v['avatarstatus'] == 1){
               $v['avatarstatus'] = 1;
            }else{
                $v['avatarstatus'] = 0;
                $v['headerColor'] = $v['svalue'];
            }
            $users[$v['uid']] = $v;
        }

        return $users;
    }
    public function fetch_userinfo_detail_by_uid($uid){
        $uid = intval($uid);
        $users = DB::fetch_first("select u.uid,u.phone,u.email,ug.* from %t u left join %t ug  on u.groupid=ug.groupid where uid = %d",array('user','usergroup',$uid));
        foreach(DB::fetch_all("select * from %t where uid = %d",array('user_profile',$uid)) as $v){
            if(!$v['privacy']){
                $users['information'][$v['fieldid']] = $v['value'];
            }
        }
        return $users;
    }
    public function fetch_all_user_data(){
        return DB::fetch_all("select * from %t where 1",array($this->_table));
    }
    /*//获取用户信息，包含资料等信息
    public function fetch_user_infomessage_by_uid($uid){
        $users = array();
        foreach(DB::fetch_all("select u.*,s.svalue from %t u left join %t s on u.uid=s.uid and s.skey=%s where u.uid =%d",array('user','user_setting','headerColor',$uid)) as $v){
            if($v['avatarstatus'] == 1){
                $v['avatarstatus'] = 1;
            }else{
                $v['avatarstatus'] = 0;
                $v['headerColor'] = $v['svalue'];
            }
            $users[$v['uid']] = $v;
        }

        return $users;
    }*/
}
