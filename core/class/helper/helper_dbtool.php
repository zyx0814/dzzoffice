<?php

if (!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_dbtool {

	public static function dbversion() {
		return DB::result_first("SELECT VERSION()");
	}

	public static function dbsize() {
		$dbsize = 0;
		$query = DB::query("SHOW TABLE STATUS LIKE '".getglobal('config/db/1/tablepre')."%'", 'SILENT');
		while($table = DB::fetch($query)) {
			$dbsize += $table['Data_length'] + $table['Index_length'];
		}
		return $dbsize;
	}

	public static function gettablestatus($tablename, $formatsize = true) {
		$status = DB::fetch_first("SHOW TABLE STATUS LIKE '".str_replace('_', '\_', $tablename)."'");

		if($formatsize) {
			$status['Data_length'] = sizecount($status['Data_length']);
			$status['Index_length'] = sizecount($status['Index_length']);
		}

		return $status;
	}

	public static function showtablecloumn($tablename) {
		$data = array();
		$db = &DB::object();
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM ".DB::table($tablename), 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM ".DB::table($tablename), 'SILENT');
		}
		while($field = @DB::fetch($query)) {
			$data[$field['Field']] = $field;
		}
		return $data;
	}

	public static function isexisttable($tablename) {
		$tablearr = array();
		$query = DB::query('SHOW TABLES', 'SILENT');
		while($table = DB::fetch($query)) {
			foreach($table as $value) {
				$tablearr[] = $value;
			}
		}
		if(in_array(DB::table($tablename), $tablearr)) {
			return true;
		} else {
			return false;
		}
	}
}

?>