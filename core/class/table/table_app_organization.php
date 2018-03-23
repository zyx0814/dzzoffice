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

class table_app_organization extends dzz_table
{
	public function __construct() {

		$this->_table = 'app_organization';
		$this->_pk    = '';
		parent::__construct();
	}
	public function insert($appid, $orgid) {
		return DB::insert($this->_table, array("orgid"=>$orgid,'appid'=>$appid,'dateline'=>TIMESTAMP),1,1);
	}
	public function replace_orgids_by_appid($appid,$orgids){
		$Oorgids=self::fetch_orgids_by_appid($appid);
		if(!is_array($orgids)) $orgids=array($orgids);
		$insertids=array_diff($orgids,$Oorgids);
		$delids=array_diff($Oorgids,$orgids);
		if($delids) DB::delete($this->_table,"appid='{$appid}' and orgid IN (".dimplode($delids).")");
		foreach($insertids as $orgid){
			if($orgid>0) self::insert($appid,$orgid);
		}
		return true;
	}
	public function delete_by_appid($appid) {
		return DB::delete($this->_table, "appid='{$appid}'");
	}
	public function delete_by_orgid($orgids) {
		if(!$orgids) return;
		if(!is_array($orgids)) $orgids=array($orgids);
		return DB::delete($this->_table, "orgid IN (".dimplode($orgids).")");
	}
	public function fetch_appids_by_orgid($orgids,$sub=false){
		$appids=array();
		$orgids=(array)$orgids;
		if($sub){
			
			foreach(DB::fetch_all("select * from %t where 1",array($this->_table)) as $value){
				if(($porgids= C::t('organization')->fetch_parent_by_orgid($value['orgid'],true)) && array_intersect($porgids,$orgids)){
					$appids[]=$value['appid'];
				}
			}
		}else{
			$query=DB::query("select appid from %t where orgid IN(%n)",array($this->_table,$orgids));
			while($value=DB::fetch($query)){
				$appids[]=$value['appid'];
			}
		}
		return $appids;
	}
	public function fetch_notin_appids_by_uid($uid){
		$paichu_appids=$orgids=array();
		foreach(C::t('organization_user')->fetch_orgids_by_uid($uid) as $orgid){
			if($parentids=C::t('organization')->fetch_parent_by_orgid($orgid)){
				$orgids=array_merge($orgids,$parentids);
			}
		}
		if($orgids){
			$appids=C::t('app_organization')->fetch_appids_by_orgid($orgids);
		}else{
			$appids=array();
		}
		foreach(DB::fetch_all("select appid from %t where appid NOT IN(%n) ",array($this->_table,$appids)) as $value){
			$paichu_appids[]=$value['appid'];
		}
		
		return $paichu_appids;	
	}
	public function fetch_orgids_by_appid($appid){
		$orgids=array();
		$arr=DB::fetch_all("select orgid from %t where appid = %d ",array($this->_table,$appid));
		foreach($arr as $value){
			$orgids[]=$value['orgid'];
		}
		return $orgids;
	}
	
	
}
?>
