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

class table_organization_user extends dzz_table
{
	public function __construct() {

		$this->_table = 'organization_user';
		$this->_pk    = '';
		parent::__construct();
	}
	public function insert($uid, $orgid,$jobid=0) {
		if(!$uid || !$orgid) return 0;
		parent::insert(array("orgid"=>$orgid,'uid'=>$uid,'jobid'=>$jobid,'dateline'=>TIMESTAMP),1,1);
		wx_updateUser($uid);
		return true;
	}
	public function fetch_by_uid_orgid($uid,$orgid){
		return DB::fetch_first("select * from %t where uid=%d and orgid=%d",array($this->_table,$uid,$orgid));
	}
	public function replace_orgid_by_uid($uid, $orgarr) {
		$orgids=array();
		foreach($orgarr as $key => $value){
			$orgids[]=$key;
		}
		$Oorgids=self::fetch_orgids_by_uid($uid);
		if(!is_array($orgids)) $orgids=array($orgids);
		$insertids=array_diff($orgids,$Oorgids);
		$delids=array_diff($Oorgids,$orgids);
		$updateids=array_diff($orgids,$delids,$insertids);
		if($delids) DB::delete($this->_table,"uid='{$uid}' and orgid IN (".dimplode($delids).")");
		foreach($insertids as $orgid){
			if($orgid>0) parent::insert(array("orgid"=>$orgid,'jobid'=>$orgarr[$orgid],'uid'=>$uid,'dateline'=>TIMESTAMP),0,1);
		}
		foreach($updateids as $orgid){
			if($orgid>0) DB::update($this->_table,array('jobid'=>$orgarr[$orgid]),"orgid='{$orgid}' and uid='{$uid}'");
		}
		
		wx_updateUser($uid);
		return true;
	}
	public function delete_by_uid($uid,$wxupdate=1) {
		if($return=DB::delete($this->_table, "uid='{$uid}'")){
			if($wxupdate) wx_updateUser($uid);
			return $return;
		}else return false;
	}
	public function delete_by_uid_orgid($uids,$orgid,$wxupdate=1) {
		$uids=(array)$uids;
		if($return=DB::delete($this->_table, "uid IN (".dimplode($uids).") and orgid='{$orgid}'")){		
			include_once libfile('function/cache');
			updatecache('organization');
			if($wxupdate) wx_updateUser($uids);
			return $return;
		}else return false;
	}
	public function delete_by_orgid($orgids) {
		if(!$orgids) return;
		$orgids=(array)$orgids;
		$uids=self::fetch_uids_by_orgid($orgids);
		if($return=DB::delete($this->_table, "orgid IN (".dimplode($orgids).")")){
			if($uids) wx_updateUser($uids);
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
		unset($query);
		return $uids;
	}
	public function fetch_user_not_in_orgid($limit=10000){
		$limitsql='';
		if($limit) $limitsql="limit $limit";
		return DB::fetch_all("select u.username,u.uid,u.email from %t u LEFT JOIN %t o ON u.uid=o.uid where isnull(o.orgid) order by username $limitsql ",array('user',$this->_table),'uid');
	}
	public function fetch_user_by_orgid($orgids,$limit=0,$count=false){
		if(!is_array($orgids)) $orgids=array($orgids);
		$limitsql='';
		if($limit) $limitsql="limit $limit";
		if($count) return DB::result_first("select COUNT(*) %t where orgid IN(%n)",array($this->_table,$orgids));
		return DB::fetch_all("select o.* ,u.username,u.email from ".DB::table('organization_user'). " o LEFT JOIN ".DB::table('user')." u ON o.uid=u.uid where o.orgid IN(".dimplode($orgids).") order by dateline DESC $limitsql ");
	}
	public function fetch_orgids_by_uid($uids){
		$uids=(array)$uids;
		$orgids=array();
		$arr=DB::fetch_all("select orgid from %t where uid IN(%n) ",array($this->_table,$uids));
		foreach($arr as $value){
			$orgids[$value['orgid']]=$value['orgid'];
		}
		return $orgids;
	}
	public function fetch_all_by_uid($uids){
		$uids=(array)$uids;
		return DB::fetch_all("select * from %t where uid IN(%n) ",array($this->_table,$uids));
	}
	
	
	public function move_to_forgid_by_orgid($forgid,$orgid){//移动用户到上级部门
		if(!$org=C::t('organization')->fetch($forgid)) return false;
		if(!$org['forgid']){
			return self::delete_by_orgid($orgid);
		}
		foreach(DB::fetch_all("select * from %t where orgid=%d",array($this->_table,$orgid)) as $value){
			if(DB::result_first("select COUNT(*) from %t where orgid=%d and uid=%d",array($this->_table,$org['forgid'],$value['uid']))){
				DB::delete($this->_table,"orgid='{$org[forgid]}' and uid='{$value[uid]}'");
			}else{
				$value['orgid']=$org['forgid'];
				parent::insert($value);
			}
		}
		
		return true;
	}
	public function move_to_by_uid_orgid($uid,$orgid,$torgid,$copy){
		if($orgid==$torgid) return true;
		if($torgid==0){
			return self::delete_by_uid_orgid($uid,$orgid);
		}
		if(!$copy && DB::result_first("select COUNT(*) from %t where orgid=%d and uid=%d",array($this->_table,$torgid,$uid))){
			return self::delete_by_uid_orgid($uid,$orgid,0);
		}else{
			self::insert($uid,$torgid);
			if(!$copy) self::delete_by_uid_orgid($uid,$orgid,0);
			return true;
		}
	}
}

?>
