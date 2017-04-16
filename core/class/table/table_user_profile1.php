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

class table_user_profile1 extends dzz_table
{
	//private $_fields;

	public function __construct() {

		$this->_table = 'user_profile1';
		$this->_pk    = '';
		
		parent::__construct();
	}
	public function fetch($uid){
		$data=array('uid'=>$uid);
		foreach(DB::fetch_all("select * from %t where uid =%d",array($this->_table,$uid)) as $value) {
			$data[$value['fieldid']]=$value['value'];
		}
		return $data;
	}
	public function fetch_by_field($uid,$field){ //获取用户某项资料的值
		return DB::result_first("select value from %t where uid=%d and fieldid=%s",array($this->_table,$uid,$field));
	}
    public function update($uid,$fieldarr){//插入用户资料
		foreach($fieldarr as $key=>$value){
			$setarr=array('uid'=>$uid,
						  'fieldid'=>$key,
						  'value'=>$value
						  );
			DB::insert($this->_table,$setarr,0,1);
		}
		return true;
	}
	 public function insert($fieldarr){//插入用户资料
	    $uid=$fieldarr['uid'];
		unset($fieldarr['uid']);
		foreach($fieldarr as $key=>$value){
			$setarr=array('uid'=>$uid,
						  'fieldid'=>$key,
						  'value'=>$value
						  );
			DB::insert($this->_table,$setarr,0,1);
		}
		return true;
	}
	public function delete($uid){
		$uid=(array)$uid;
		return DB::delete($this->_table,"uid IN (".dimplode($uid).")");
	}
	public function delete_by_field($fieldids){ //删除用户资料项
	    $fieldids=(array)$fieldids;
		return DB::delete($this->_table,"fieldid IN (".dimplode($fieldids).")");
	}
	public function delete_by_uid($uids){ //删除用户资料
	    $uids=(array)$uids;
		return DB::delete($this->_table,"uid IN (".dimplode($uids).")");
	}
	public function fetch_all($uids) {
		$data = array();
		$uids=(array)$uids;
		if(!empty($uids)) {
			foreach(DB::fetch_all("select * from %t where uid IN (%n)",array($this->_table,$uids)) as $value) {
				$data[$value['uid']][$value['fieldid']]=$value['value'];
				$data[$value['uid']]['uid']=$value['uid'];
			}
		}
		return $data;
	}

	public function count_by_field($field, $val) {
		
		return DB::result_first('SELECT COUNT(*) as cnt FROM '.DB::table($this->_table).' WHERE '.DB::field($field, $val)); 
	}

	public function fetch_all_field_value($field) {
		return DB::fetch_all('SELECT DISTINCT(`'.$field.'`) FROM '.DB::table($this->_table), null, $field);
	}

	public function fetch_all_will_birthday_by_uid($uids) {
		$birthlist = array();
		if(!empty($uids)) {
			$uids = explode(',', (string)$uids);
			$uids = dimplode(dintval($uids, true));
			list($s_month, $s_day) = explode('-', dgmdate(TIMESTAMP-3600*24*3, 'n-j'));
			list($n_month, $n_day) = explode('-', dgmdate(TIMESTAMP, 'n-j'));
			list($e_month, $e_day) = explode('-', dgmdate(TIMESTAMP+3600*24*7, 'n-j'));
			if($e_month == $s_month) {
				$wheresql = "sf.birthmonth='$s_month' AND sf.birthday>='$s_day' AND sf.birthday<='$e_day'";
			} else {
				$wheresql = "(sf.birthmonth='$s_month' AND sf.birthday>='$s_day') OR (sf.birthmonth='$e_month' AND sf.birthday<='$e_day' AND sf.birthday>'0')";
			}
			$data=array();
			foreach(DB::fetch_all("select sf.*,u.username,u.email from %t sf LEFT JOIN %t u USING(uid) ON sf.uid=u.uid where sf.uid IN (%n) and $wheresql",array($this->_table,'user',$uids)) as $value){
				$data[$value['uid']][$value['fileid']]=$value['value'];
				$data[$value['uid']]['username']=$value['username'];
			}
			foreach($data as $value){
				$value['istoday'] = 0;
				if($value['birthmonth'] == $n_month && $value['birthday'] == $n_day) {
					$value['istoday'] = 1;
				}
				$key = sprintf("%02d", $value['birthmonth']).sprintf("%02d", $value['birthday']);
				$birthlist[$key][] = $value;
				ksort($birthlist);
			}
			
		}
		return $birthlist;
	}
}

?>
