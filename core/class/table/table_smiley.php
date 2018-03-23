<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class table_smiley extends dzz_table
{
	private $allowtype = array('smiley','stamp','stamplist');
	public function __construct() {

		$this->_table = 'smiley';
		$this->_pk    = 'id';

		parent::__construct();
	}
	public function fetch_all_by_type($type) {
		$type = $this->checktype($type);
		if(empty($type)) {
			return array();
		}
		$typesql = is_array($type) ? 'type IN(%n)' : 'type=%s';
		return DB::fetch_all("SELECT * FROM %t WHERE $typesql ORDER BY displayorder", array($this->_table, $type), $this->_pk);
	}

	public function fetch_all_by_typeid_type($typeid, $type, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE typeid=%d AND type=%s ORDER BY displayorder '.DB::limit($start, $limit), array($this->_table, $typeid, $type), $this->_pk);
	}
	public function fetch_all_by_type_code_typeid($type, $typeid) {
		return DB::fetch_all("SELECT * FROM %t WHERE type=%s AND code<>'' AND typeid=%d ORDER BY displayorder ", array($this->_table, $type, $typeid), $this->_pk);
	}
	public function fetch_all_cache() {
		return DB::fetch_all("SELECT s.id, s.code, s.url, t.typeid FROM %t s INNER JOIN %t t ON t.typeid=s.typeid WHERE s.type='smiley' AND s.code<>'' AND t.available='1' ORDER BY LENGTH(s.code) DESC", array($this->_table, 'imagetype'));

	}
	public function fetch_by_id_type($id, $type) {
		return DB::fetch_first('SELECT * FROM %t WHERE id=%d AND type=%s', array($this->_table, $id, $type), $this->_pk);
	}
	public function update_by_type($type, $data) {
		if(!empty($data) && is_array($data) && in_array($type, $this->allowtype)) {
			return DB::update($this->_table, $data, DB::field('type', $type));
		}
		return 0;
	}
	public function update_by_id_type($id, $type, $data) {
		$id = dintval($id, true);
		if(!empty($data) && is_array($data) && $id && in_array($type, $this->allowtype)) {
			return DB::update($this->_table, $data, DB::field('id', $id).' AND '.DB::field('type', $type));
		}
		return 0;
	}
	public function update_code_by_typeid($typeid) {
		$typeid = dintval($typeid, true);
		if(empty($typeid)) {
			return 0;
		}
		$typeidsql = is_array($typeid) ? 'typeid IN(%n)' : 'typeid=%d';
		return DB::query("UPDATE %t SET code=CONCAT('{:', typeid, '_', id, ':}') WHERE $typeidsql", array($this->_table, $typeid));
	}
	public function update_code_by_id($ids) {
		$ids = dintval($ids, true);
		if(empty($ids)) {
			return 0;
		}
		$idssql = is_array($ids) ? 'id IN(%n)' : 'id=%d';
		return DB::query("UPDATE %t SET code=CONCAT('{:', typeid, '_', id, ':}') WHERE $idssql", array($this->_table, $ids));
	}
	public function count_by_type($type) {
		$type = $this->checktype($type);
		if(empty($type)) {
			return 0;
		}
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE type IN(%n)', array($this->_table, $type));
	}
	public function count_by_typeid($typeid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE typeid=%d', array($this->_table, $typeid));
	}
	public function count_by_type_typeid($type, $typeid) {
		$typeid = dintval($typeid, true);
		if(!empty($typeid) && in_array($type, $this->allowtype)) {
			return DB::result_first('SELECT COUNT(*) FROM %t WHERE type=%s AND typeid IN(%n)', array($this->_table, $type, $typeid));
		}
		return 0;
	}
	public function count_by_type_code_typeid($type, $typeid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE type=%s AND code<>'' AND typeid=%d", array($this->_table, $type, $typeid));
	}

	private function checktype($type) {
		if(is_array($type)) {
			foreach($type as $key => $val) {
				if(!in_array($val, $this->allowtype)) {
					unset($type[$key]);
				}
			}
		} else {
			$type = in_array($type, $this->allowtype) ? $type : '';
		}
		return $type;
	}

}

?>