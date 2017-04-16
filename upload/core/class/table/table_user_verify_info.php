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

class table_user_verify_info extends dzz_table
{
	public function __construct() {

		$this->_table = 'user_verify_info';
		$this->_pk    = 'vid';

		parent::__construct();
	}
	public function fetch_by_uid_verifytype($uid, $verifytype) {
		return DB::fetch_first('SELECT * FROM %t WHERE uid=%d AND verifytype=%d', array($this->_table, $uid, $verifytype));
	}
	public function fetch_all_search($uid, $vid, $flag = null, $username = '', $starttime = 0, $endtime = 0, $order = 'dateline', $start = 0, $limit = 0, $sort = 'DESC',$orgids=array()) {
		$condition = $this->search_condition($uid, $vid, $flag, $username, $starttime, $endtime,$orgids);
		
		$ordersql = !empty($order) ? ' ORDER BY v.'.$order : '';
		
		return DB::fetch_all("SELECT * FROM %t v $condition[0] $ordersql ".DB::limit($start, $limit), $condition[1], $this->_pk);
	}
	public function group_by_verifytype_count() {
		return DB::fetch_all('SELECT verifytype, COUNT(*) AS num FROM %t WHERE flag=0 GROUP BY verifytype', array($this->_table));
	}

	public function delete_by_uid($uid, $verifytype = null) {
		if($uid) {
			$addsql = '';
			if($verifytype !== null) {
				$verifytype = dintval($verifytype, is_array($verifytype) ? true : false);
				$addsql = ' AND '.DB::field('verifytype', $verifytype);
			}
			return DB::query('DELETE FROM %t WHERE '.(is_array($uid) ? 'uid IN(%n)' : 'uid=%d').$addsql, array($this->_table, $uid));
		}
		return false;
	}

	public function count_by_search($uid, $vid, $flag = null, $username = '', $starttime = 0, $endtime = 0,$orgids=array()) {
		$condition = $this->search_condition($uid, $vid, $flag, $username, $starttime, $endtime,$orgids);
		return DB::result_first('SELECT COUNT(*) FROM %t v'.$condition[0], $condition[1]);
	}

	public function search_condition($uid, $vid, $flag, $username, $starttime, $endtime,$orgids) {
		$parameter = array($this->_table);
		$wheresql='';
		$wherearr = array();
		if($orgids){
			if($vid==1) {
				if(is_array($orgids)){
					$parameter[] = $orgids;
					$wherearr[] = 'v.orgid IN (%n)';
				}else{
					$parameter[] = $orgids;
					$wherearr[] = 'v.orgid=%d';
				}
			}else{
				$parameter[] = 'organization_user';
				$parameter[] = $orgids;
				$wheresql=" LEFT JOIN %t o ON o.uid=v.uid and o.orgid IN(%n)";
				$wherearr[] = '!isnull(o.dateline)';
			}
		}
		if($uid) {
			if(is_array($uid)){
				$parameter[] = $uid;
				$wherearr[] = 'v.uid IN (%n)';
			}else{
				$parameter[] = $uid;
				$wherearr[] = 'v.uid=%d';
			}
		}
		
		if($vid >= 0 && $vid < 8) {
			$parameter[] = $vid;
			$wherearr[] = 'v.verifytype=%d';
		}
		if($flag !== null) {
			$parameter[] = $flag;
			$wherearr[] = 'v.flag=%d';
		}
		if($starttime){
			$parameter[] = $starttime;
			$wherearr[] = 'v.dateline>=%d';
		}
		if($endtime){
			$parameter[] = $endtime;
			$wherearr[] = 'v.dateline<=%d';
		}
		if(!empty($username)) {
			$parameter[] = '%'.$username.'%';
			$wherearr[] = "v.username LIKE %s";
		}
		$wheresql .= !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);

	}


}

?>