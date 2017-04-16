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
	public function insert($uid, $orgid) {
		if(!$uid || !$orgid) return 0;
		
		$id=parent::insert(array("orgid"=>$orgid,'uid'=>$uid,'opuid'=>getglobal('uid'),'dateline'=>TIMESTAMP),1,1);
		self::update_groupid_by_uid($uid);
		
		return DB::result_first('select id from %t where uid=%d and orgid=%d',array($this->_table,$uid,$orgid));
	}
	public function delete_by_id($id){
		$data=self::fetch($id);
		if($return=parent::delete($id)){
			self::update_groupid_by_uid($data['uid']);
		}
		return $return;
	}
	public function update_groupid_by_uid($uid){
		$user=getuserbyuid($uid);
		if($user['groupid']==1) return ;
		if(DB::result_first("select COUNT(*) from %t where uid=%d",array($this->_table,$uid))){
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
		return DB::fetch_all("select o.* ,u.username,u.email from ".DB::table($this->_table). " o LEFT JOIN ".DB::table('user')." u ON o.uid=u.uid where o.orgid IN(".dimplode($orgids).") order by o.dateline DESC");
	}
	public function fetch_orgids_by_uid($uid,$getsub=0){
		$orgids=array();
		$arr=DB::fetch_all("select orgid from %t where uid = %d ",array($this->_table,$uid));
		foreach($arr as $value){
			
			if($getsub>0){
				$subs=getOrgidTree($value['orgid']);
				$orgids=array_merge($orgids,$subs);
			}else{
				$orgids[]=$value['orgid'];
			}
		}
		return array_unique($orgids);
	}
	
	public function ismoderator_by_uid_orgid($orgid,$uid,$up=1){
		global $_G;
		include_once libfile('function/organization');
		if($_G['adminid']==1) return true;
		if($up) $orgids=getUpOrgidTree($orgid,true);
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
	
}

?>
