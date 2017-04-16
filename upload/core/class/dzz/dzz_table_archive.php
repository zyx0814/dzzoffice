<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_table_archive extends dzz_table
{
	public function __construct($para = array()) {
		parent::__construct($para);
	}

	public function fetch($id, $force_from_db = false, $fetch_archive = 0){
		$data = array();
		if(!empty($id)) {
		   if($fetch_archive<2){
				$data = parent::fetch($id, $force_from_db);
				if( $fetch_archive && empty($data)) {
					$data = C::t($this->_table.'_archive')->fetch($id);
				}
		   }else{
			   $data = C::t($this->_table.'_archive')->fetch($id);
		   }
		}
		return $data;
	}


	public function fetch_all($ids, $force_from_db = false, $fetch_archive = 1) {
		$data = array();
		if(!empty($ids)) {
			 if($fetch_archive<2){
				$data = parent::fetch_all($ids, $force_from_db);
				if( $fetch_archive && count($data) != count($ids)) {
					$data = $data + C::t($this->_table.'_archive')->fetch_all(array_diff($ids, array_keys($data)));
				}
			 }else{
					$data = C::t($this->_table.'_archive')->fetch_all($ids); 
			 }
		}
		return $data;
	}

	public function delete($val, $unbuffered = false, $fetch_archive = 0) {
		$ret = false;
		if($val) {
			if($fetch_archive<2){
				$ret = parent::delete($val, $unbuffered);
				if( $fetch_archive) {
					$_ret = C::t($this->_table.'_archive')->delete($val, $unbuffered);
					if(!$unbuffered) {
						$ret = $ret + $_ret;
					}
				}
			}else{
				$ret=C::t($this->_table.'_archive')->delete($val, $unbuffered);
			}
		}
		return $ret;
	}
}

?>