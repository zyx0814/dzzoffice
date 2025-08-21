<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
	exit('Access Denied');
}

class table_seccheck extends dzz_table
{
	private $_uids = array();
	public function __construct() {

		$this->_table = 'seccheck';
		$this->_pk    = 'ssid';

		$this->_pre_cache_key = 'seccheck_';
		$this->_cache_ttl = 600;

		parent::__construct();
	}

	public function delete_expiration($ssid = 0) {
		if ($this->_allowmem) {
			if ($ssid) {
				$ssid = dintval($ssid);
				memory('rm', $ssid . "_verified", $this->_pre_cache_key);
				memory('rm', $ssid . "_succeed", $this->_pre_cache_key);
				memory('rm', $ssid . "_code", $this->_pre_cache_key);
				memory('rm', $ssid . "_dateline", $this->_pre_cache_key);
			}
			// 其它情况，由cache自己处理过期
		} else {
			if($ssid) {
				$ssid = dintval($ssid);
				DB::delete($this->_table, "ssid='$ssid'");
			}
			DB::delete($this->_table, TIMESTAMP."-dateline>600");
			DB::delete($this->_table, "verified>4");
			DB::delete($this->_table, "succeed>1");
		}
	}

	public function update_verified($ssid) {
		if ($this->_allowmem) {
			memory('inc', $ssid . "_verified", 1, 0, $this->_pre_cache_key);
		} else {
			DB::query("UPDATE %t SET verified=verified+1 WHERE ssid=%d", array($this->_table, $ssid));
		}
	}

	public function update_succeed($ssid) {
		if (!$this->_allowmem) {
			return DB::query("UPDATE %t SET verified=verified+1,succeed=succeed+1 WHERE ssid=%d", array($this->_table, $ssid));
		}
		memory('inc', $ssid . "_verified", 1, 0, $this->_pre_cache_key);
		memory('inc', $ssid . "_succeed", 1, 0, $this->_pre_cache_key);
		return 1; // simulate 1 row changed
	}

	public function truncate() {
		if ($this->_allowmem) {
			// 由Cache自己处理过期
		} else {
			DB::query("TRUNCATE %t", array($this->_table));
		}
	}

	/*
	 * 用一个单独的seccheck_pk生成唯一ID
	 * 所有的值按seccheck_$id_$key的格式记录
	 */
	public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		if (!$this->_allowmem) {
			return parent::insert($data, $return_insert_id, $replace, $silent);
		}

		$ssid = memory("inc", 'pk', 1, 0, $this->_pre_cache_key);
		foreach ($data as $key => $value) {
			memory('set', $ssid . "_" . $key, $value, $this->_cache_ttl, $this->_pre_cache_key);
		}
		if ($return_insert_id) {
			return $ssid;
		}
		return TRUE;
	}

	public function fetch($id, $force_from_db = false) {
		if (!$this->_allowmem) {
			return parent::fetch($id, $force_from_db);
		}

		$data = array();
		$data['ssid'] = $id;
		$data['code'] = memory('get', $id . "_code", $this->_pre_cache_key);
		$data['dateline'] = memory('get', $id . "_dateline", $this->_pre_cache_key);
		$data['succeed'] = memory('get', $id . "_succeed", $this->_pre_cache_key);
		$data['verified'] = memory('get', $id . "_verified", $this->_pre_cache_key);
		return $data;
	}

	public function delete($ssid, $force_from_db = false) {
		if (!$this->_allowmem || $force_from_db) {
			return parent::delete($ssid, $force_from_db);
		}
		$ssid = dintval($ssid);
		memory('rm', $ssid . "_verified", $this->_pre_cache_key);
		memory('rm', $ssid . "_succeed", $this->_pre_cache_key);
		memory('rm', $ssid . "_code", $this->_pre_cache_key);
		memory('rm', $ssid . "_dateline", $this->_pre_cache_key);
		return true;
	}

}

?>