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

class table_stat extends dzz_table
{
	public function __construct() {

		$this->_table = 'stat';
		$this->_pk    = 'daytime';

		parent::__construct();
	}

	public function updatestat($uid, $type, $primary = 0, $num = 1) {
		$nowdaytime = dgmdate(TIMESTAMP, 'Ymd');
		$type = addslashes($type);
		if($primary) {
			$setarr = array(
				'uid' => intval($uid),
				'daytime' => $nowdaytime,
				'type' => $type
			);
			if(C::t('statuser')->check_exists($uid, $nowdaytime, $type)) {
				return false;
			} else {
				C::t('statuser')->insert($setarr);
			}
		}
		$num = abs(intval($num));
		if(DB::result_first('SELECT COUNT(*) FROM '.DB::table($this->_table)." WHERE `daytime` = '$nowdaytime'")){
			DB::query('UPDATE '.DB::table($this->_table)." SET `$type`=`$type`+$num WHERE `daytime` = '$nowdaytime'");
		} else {
			C::t('statuser')->clear_by_daytime($nowdaytime);
			DB::insert($this->_table, array('daytime'=>$nowdaytime, $type=>$num));
		}
	}

	public function fetch_post_avg() {
		return DB::result_first("SELECT AVG(post) FROM ".DB::table($this->_table));
	}

	public function fetch_all($begin, $end, $field = '*') {
		$data = array();
		$query = DB::query('SELECT %i FROM %t WHERE daytime>=%d AND daytime<=%d ORDER BY daytime', array($field, $this->_table, $begin, $end));
		while($value = DB::fetch($query)) {
			$data[$value['daytime']] = $value;
		}
		return $data;
	}
	public function fetch_all_by_daytime($daytime, $start = 0, $limit = 0, $sort = 'ASC') {
		$wheresql = '';
		$parameter = array($this->_table);
		if($daytime) {
			$wheresql = 'WHERE daytime>=%d';
			$parameter[] = $daytime;
		}
		return DB::fetch_all("SELECT * FROM %t $wheresql ORDER BY daytime $sort".DB::limit($start, $limit), $parameter);
	}
}

?>
