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

class table_organization_admin extends dzz_table
{
	public function __construct() {

		$this->_table = 'organization_admin';
		$this->_pk    = 'id';
		
		parent::__construct();
	}
	public function insert($uid, $orgid,$admintype = 1) {
		if(!$uid || !$orgid) return 0;
		if(!C::t('organization_user')->fetch_num_by_orgid_uid($orgid,$uid)){
            $ret = C::t('organization_user')->insert_by_orgid($orgid,$uid);
		}
		$id=parent::insert(array("orgid"=>$orgid,'uid'=>$uid,'opuid'=>getglobal('uid'),'dateline'=>TIMESTAMP,'admintype'=>$admintype),1,1);
        self::update_groupid_by_uid($uid);


		return DB::result_first('select id from %t where uid=%d and orgid=%d',array($this->_table,$uid,$orgid));
	}
	public function update_perm($uid,$orgid,$admintype){
		if(DB::result_first("select count(*) from %t where orgid = %d and uid = %d",array($this->_table,$orgid,$uid))){
			if($admintype == 0){
				return DB::delete($this->_table,array('orgid'=>$orgid,'uid'=>$uid));
			}else{
				return DB::update($this->_table,array('admintype'=>$admintype),array('orgid'=>$orgid,'uid'=>$uid));
			}
		}else{
			return self::insert($uid,$orgid,$admintype);
		}

	}
	public function delete_by_id($id){
		$data=self::fetch($id);
        if($data['admintye'] == 2) return false;
		if($return=parent::delete($id)){
            self::update_groupid_by_uid($data['uid']);
		}
		return $return;
	}
	//判断是否具有当前部门或机构管理员权限
	public function is_admin_by_orgid($orgid,$uid){
		$currentpathkey = DB::result_first("select pathkey from %t where orgid = %d",array('organization',$orgid));
		$orgids = explode('-',str_replace('_','',$currentpathkey));
		if(DB::result_first("select count(*) from %t where orgid in (%n) and uid = %d",array($this->_table,$orgids,$uid)) > 0){
			return true;
		}
		return false;
	}
	/*//判断用户是否是当前机构或部门管理员，或者是下级部门成员
	public function is_curentadmin_or_childmember($orgid,$uid){
	    if($this->chk_memberperm($orgid,$uid)) return true;
        $currentpathkey = DB::result_first("select pathkey from %t where orgid = %d",array('organization',$orgid));
        $like = $currentpathkey.'.+';
        foreach (DB::fetch_all("select orgid from %t where pathkey REGEXP %s", array('organization', $like)) as $value) {
            $gids[] = $value['orgid'];
        }
        if(count($gids) > 0){
           return  DB::result_first("select count(*) from %t where orgid in(%n)",array('organization_user',$gids));
        }
    }*/
	public function fetch_group_creater($orgid){
		if(!$orgid) return false;
		$uid = DB::result_first("select uid from %t where orgid = %d and admintype = %d",array($this->_table,$orgid,2));
		$username = DB::result_first("select username from %t where uid = %d ",array('user',$uid));
		return $username;
	}
	public function update_groupid_by_uid($uid){
	    return true;
		$user=getuserbyuid($uid);
		if($user['groupid']==1) return ;
		//判断当前用户是否仍为机构和部门管理员
		if(DB::result_first("select COUNT(*) from %t a left join %t o on o.orgid = a.orgid where a.uid=%d and o.type = 0 ",array($this->_table,'organization',$uid))){
			$groupid=2;
		}else{
			$groupid=9;
		}
		return C::t('user')->update($uid,array('groupid'=>$groupid));
	}
	public function delete_by_uid($uid) {
		if($return=DB::delete($this->_table, "uid='{$uid}'")){
			self::update_groupid_by_uid($uid);
			return $return;
		}else return false;
	}
	public function delete_by_orgid($orgids) {
		$orgids=(array)$orgids;
		$uids=array();
		foreach(DB::fetch_all("select uid from %t where orgid IN (%n) ",array($this->_table,$orgids)) as $value){
			$uids[$value['uid']]=$value['uid'];
		}
		if($return=DB::delete($this->_table, "orgid IN (".dimplode($orgids).")")){
			foreach($uids as $uid){
				self::update_groupid_by_uid($uid);
			}
			return $return;
		}
		return false;
	}
	public function delete_by_uid_orgid($uid,$orgid) {

		if($return=DB::delete($this->_table, "uid='{$uid}' and orgid='{$orgid}'")){
			self::update_groupid_by_uid($uid);
			return $return;
		}else return false;
	}
	
	public function fetch_uids_by_orgid($orgids){
		$uids=array();
		if(!is_array($orgids)) $orgids=array($orgids);
		$query=DB::query("select uid from %t where orgid IN(%n)",array($this->_table,$orgids));
		while($value=DB::fetch($query)){
			$uids[]=$value['uid'];
		}
		return $uids;
	}
	
	public function fetch_moderators_by_orgid($orgids,$count=false){
		if(!is_array($orgids)) $orgids=array($orgids);
		if($count) return DB::result_first("select COUNT(*) from %t where orgid IN (%n)",array($this->_table,$orgids));
		return DB::fetch_all("select o.* ,u.username,u.email,u.uid from ".DB::table($this->_table). " o LEFT JOIN ".DB::table('user')." u ON o.uid=u.uid where o.orgid IN(".dimplode($orgids).") order by o.dateline DESC");
	}
	public function fetch_orgids_by_uid($uids,$orgtype = 0){
		$uids=(array)$uids;
		$orgids=array();
	
		$param=array($this->_table);
		if($orgtype>-1){
			$sql = "select u.orgid from %t u LEFT JOIN %t o ON u.orgid=o.orgid where u.uid IN(%n) and o.type=%d";
			$param[]='organization';
			$param[]=$uids;
			$param[]=$orgtype;
		}else{
			$sql = "select orgid from %t where uid IN(%n)";
			$param[]=$uids;
		}
		foreach(DB::fetch_all($sql,$param) as $value){
			$orgids[$value['orgid']]=$value['orgid'];
		}
		return $orgids;
	}
	
	public function ismoderator_by_uid_orgid($orgid,$uid,$up=1){
		global $_G;
		include_once libfile('function/organization');
		if($_G['adminid']==1) return true;
		if($up) $orgids=C::t('organization')->fetch_parent_by_orgid($orgid);
		else $orgids=array($orgid);
		return DB::result_first("select COUNT(*) from %t where orgid IN (%n) and uid=%d ",array($this->_table,$orgids,$uid));
	}

	public function fetch_toporgids_by_uid($uid){
		$ret=array();
		$orgids=self::fetch_orgids_by_uid($uid);
		foreach(C::t('organization')->fetch_all($orgids) as $value){
			$topids=explode('-',$value['pathkey']);
			$topid=intval(str_replace('_','',$topids[0]));
			$ret[$topid]=$topid;
		}
		return $ret;
	}
	public function chk_memberperm($orgid,$uid = 0){
		global $_G;
        $perm = 0;
        if(!$org=C::t('organization')->fetch($orgid)) return $perm;
        if(!$uid) $uid = $_G['uid'];
		if($_G['adminid'] == 1 && $uid == $_G['uid']) {
            $perm = 2;
            return $perm;
        }

        //判断是否有上级,如果有上级并且当前用户为上级管理员，则给予类似创始人权限
        if($org['forgid']){
            $orgids = C::t('organization')->fetch_parent_by_orgid($orgid);
            $key = array_search($orgid,$orgids);
            unset($orgids[$key]);
            if(DB::result_first("select count(*) from %t where orgid in(%n) and uid = %d",array($this->_table,$orgids,$uid)) > 0){
                $perm = 2;
              return $perm;
            }
        }
		if($admintype = DB::result_first("select admintype from %t where orgid = %d and uid = %d",array($this->_table,$orgid,$uid))){
			$perm = $admintype;
		}
		return $perm;
	}

	public function fetch_adminer_by_orgid($orgid){
	    $admindata = '';
	    foreach(DB::fetch_all("select u.username from %t a left join %t u on a.uid = u.uid where orgid = %d ",array($this->_table,'user',$orgid)) as $v){
            $admindata .= $v['username'].',';
        }
        $admindata = substr($admindata,0,-1);
        return $admindata;
    }
}
