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

class table_user_profile extends dzz_table
{
	//private $_fields;

	public function __construct() {

		$this->_table = 'user_profile';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'user_profile_';
		/*$this->_fields = array('uid', 'realname', 'gender', 'birthyear', 'birthmonth', 'birthday', 'constellation',
				'zodiac', 'telephone', 'mobile', 'idcardtype', 'idcard', 'address', 'zipcode', 'nationality', 'birthprovince', 'birthcity', 'birthdist',
				'birthcommunity', 'resideprovince', 'residecity', 'residedist', 'residecommunity', 'residesuite', 'graduateschool', 'education', 'company',
				'occupation', 'position', 'revenue', 'affectivestatus', 'lookingfor', 'bloodtype', 'height', 'weight', 'alipay', 'icq', 'qq',
				'yahoo',  'taobao', 'site', 'bio', 'interest', 'field1', 'field2', 'field3', 'field4', 'field5', 'field6', 'field7', 'field8');*/

		parent::__construct();
	}
    
	public function fetch_all($uids, $force_from_db = false, $fetch_archive = 0) {
		$data = array();
		if(!empty($uids)) {
			if($force_from_db || ($data = $this->fetch_cache($uids)) === false || count($uids) != count($data)) {
				if(is_array($data) && !empty($data)) {
					$uids = array_diff($uids, array_keys($data));
				}
				if($data === false) $data =array();
				if(!empty($uids)) {
					$query = DB::query('SELECT '.implode(',', $this->_fields).' FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $uids));
					while($value = DB::fetch($query)) {
						$data[$value[$this->_pk]] = $value;
						$this->store_cache($value[$this->_pk], $value);
					}
				}
			}
			/*if($fetch_archive && count($data) != count($uids)) {
				$data = $data + C::t($this->_table.'_archive')->fetch_all(array_diff($uids, array_keys($data)), null, 0);
			}*/

		}
		return $data;
	}

	public function count_by_field($field, $val) {
		/*$count = 0;
		if(in_array($field, $this->_fields, true)) {
			$count = DB::result_first('SELECT COUNT(*) as cnt FROM '.DB::table('user_profile').' WHERE '.DB::field($field, $val));
		}*/
		return DB::result_first('SELECT COUNT(*) as cnt FROM '.DB::table('user_profile').' WHERE '.DB::field($field, $val)); 
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

			$query = DB::query("SELECT sf.uid,sf.birthyear,sf.birthmonth,sf.birthday,s.username
				FROM ".DB::table('user_profile')." sf
				LEFT JOIN ".DB::table('user')." s USING(uid)
				WHERE (sf.uid IN ($uids)) AND ($wheresql)");
			while ($value = DB::fetch($query)) {
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
