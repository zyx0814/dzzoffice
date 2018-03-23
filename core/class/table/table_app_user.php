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

class table_app_user extends dzz_table
{
	public function __construct() {
		$this->_table = 'app_user';
		$this->_pk    = 'id';
		parent::__construct();
	}
	public function delete_by_uid_appid($uid,$appid){
		if(!$appid) return false;
		return DB::delete($this->_table," appid='{$appid}' and uid='{$uid}'");
	}
	public function delete_by_appid($appid){
		if(!$appid) return false;
		return DB::delete($this->_table," appid='{$appid}'");
	}
	public function update_lasttime($uid,$appid,$lasttime){
		if(!$uid) return false;
		if(DB::query("update ".DB::table($this->_table)." set lasttime=".intval($lasttime).", num=num+1 where appid='{$appid}' and uid='{$uid}'")){
			
		}else{
			parent::insert(array('uid'=>$uid,'appid'=>$appid,'lasttime'=>$lasttime,'dateline'=>TIMESTAMP,'num'=>1),false,true);
		}
	}
	
	public function insert_by_uid($uid,$appids,$isall=0){
		if(!$appids) return false;
		if(!is_array($appids)) $appids=array($appids);
		//删除原来的
		$oids=array();
		$delids=array();
		$insertids=array();
		$oarr=DB::fetch_all("select * from ".DB::table('app_user')." where uid='{$uid}'");
		foreach($oarr as $value){
			$oids[]=$value['appid'];
			if( !in_array($value['appid'],$appids)) $delids[]=$value['id'];
		}
		if($isall && $delids) {
			self::delete($delids);
		}
		foreach($appids as $appid){
			if(!in_array($appid,$oids))	DB::insert('app_user',array('uid'=>$uid,'appid'=>$appid,'dateline'=>TIMESTAMP,'num'=>1));
		}
		return true;
	}
	public function fetch_all_appids_by_uid($uid){
		$data=array();
		foreach(DB::fetch_all("select appid from %t where uid=%d",array($this->_table,$uid)) as $value){
			$data[]=$value['appid'];
		}
		return $data;
	}
}
?>
