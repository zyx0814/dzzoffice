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

class table_user_archive extends table_user
{
	public function __construct() {

		parent::__construct();
		$this->_table = 'user_archive';
		$this->_pk    = 'uid';
	}

	public function fetch($id){
		$data = array();
		if(($id = dintval($id)) && ($data = DB::fetch_first('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $id)))) {
			$data['_inarchive'] = true;
		}
		return $data;
	}

	public function fetch_by_username($username) {
		$user = array();
		if($username && ($user = DB::fetch_first('SELECT * FROM %t WHERE username=%s', array($this->_table, $username)))) {
			$user['_inarchive'] = true;
		}
		return $user;
	}
	public function fetch_by_nickname($username) {
		$user = array();
		if($username && ($user = DB::fetch_first('SELECT * FROM %t WHERE nickname=%s', array($this->_table, $username)))) {
			$user['_inarchive'] = true;
		}
		return $user;
	}

	public function fetch_uid_by_username($username) {
		$uid = 0;
		if($username) {
			$uid = DB::result_first('SELECT uid FROM %t WHERE username=%s', array($this->_table, $username));
		}
		return $uid;
	}

	public function count() {
		return isset($this->membersplit) ? DB::result_first('SELECT COUNT(*) FROM %t', array($this->_table)) : 0;
	}

	public function fetch_by_email($email) {
		$user = array();
		if($email && ($user = DB::fetch_first('SELECT * FROM %t WHERE email=%s', array($this->_table, $email)))) {
			$user['_inarchive'] = true;
		}
		return $user;
	}
	
	public function fetch_by_uid($uid) {
		$user = array();
		if($uid && ($user = DB::fetch_first('SELECT * FROM %t WHERE uid=%d', array($this->_table, $uid)))) {
			$user['_inarchive'] = true;
		}
		return $user;
	}


	public function count_by_email($email) {
		$count = 0;
		if($email) {
			$count = DB::result_first('SELECT COUNT(*) FROM %t WHERE email=%s', array($this->_table, $email));
		}
		return $count;
	}

	public function fetch_all($ids) {
		$data = array();
		if(($ids = dintval($ids, true))) {
			$query = DB::query('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $ids));
			while($value = DB::fetch($query)) {
				$value['_inarchive'] = true;
				$data[$value[$this->_pk]] = $value;
			}
		}
		return $data;
	}

	public function move_to_master($uid){
		if(($uid = intval($uid)) && ($member = $this->fetch($uid))) {
			unset($member['_inarchive']);
			DB::insert('user',$member);
			C::t('user_status')->insert(C::t('user_status_archive')->fetch($uid));
			C::t('user_profile')->insert(C::t('user_profile_archive')->fetch($uid));
			$this->delete($uid);
			C::t('user_status_archive')->delete($uid);
			C::t('user_profile_archive')->delete($uid);
			
		}
	}

	public function delete($val, $unbuffered = false) {
		return ($val = dintval($val, true)) && DB::delete($this->_table, DB::field($this->_pk, $val), null, $unbuffered);
	}

	public function check_table() {
		if(DB::fetch_first("SHOW TABLES LIKE '".DB::table('user_archive')."'")){
			return false;
		} else {
			$mastertables = array('user',  'user_status', 'user_profile');
			foreach($mastertables as $tablename) {
				$createtable = DB::fetch_first('SHOW CREATE TABLE '.DB::table($tablename));
				DB::query(str_replace(DB::table($tablename), DB::table("{$tablename}_archive"), $createtable['Create Table']));
			}
			return true;
		}
	}

	public function rebuild_table($step) {
		$mastertables = array('user',  'user_status', 'user_profile');

		if(!isset($mastertables[$step])) {
			return false;
		}
		$updates = array();
		$mastertable = DB::table($mastertables[$step]);
		$archivetable = DB::table($mastertables[$step].'_archive');

		$mastercols = DB::fetch_all('SHOW COLUMNS FROM '.$mastertable, null, 'Field');
		$archivecols = DB::fetch_all('SHOW COLUMNS FROM '.$archivetable, null, 'Field');
		foreach(array_diff(array_keys($archivecols), array_keys($mastercols)) as $field) {
			$updates[] = "DROP `$field`";
		}

		$createtable = DB::fetch_first('SHOW CREATE TABLE '.$mastertable);
		$mastercols = $this->getcolumn($createtable['Create Table']);

		$archivecreatetable = DB::fetch_first('SHOW CREATE TABLE '.$archivetable);
		$oldcols = $this->getcolumn($archivecreatetable['Create Table']);

		$indexmastercols =array_keys($mastercols);
		foreach ($mastercols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) {
						$usql = "RENAME TABLE ".$archivetable." TO ".$archivetable.'_bak';
						if(!DB::query($usql, 'SILENT')) {
							return $mastertable;
						}
					}
					$updates[] = "ADD PRIMARY KEY $value";
				}
			} elseif ($key == 'KEY') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['KEY'][$subkey])) {
						if($subvalue != $oldcols['KEY'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					} else {
						$updates[] = "ADD INDEX `$subkey` $subvalue";
					}
				}
			} elseif ($key == 'UNIQUE') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['UNIQUE'][$subkey])) {
						if($subvalue != $oldcols['UNIQUE'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
						}
					} else {
						$usql = "ALTER TABLE  ".$archivetable." DROP INDEX `$subkey`";
						DB::query($usql, 'SILENT');
						$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
					}
				}
			} else {
				if(!empty($oldcols[$key])) {
					if(strtolower($value) != strtolower($oldcols[$key])) {
						$updates[] = "CHANGE `$key` `$key` $value";
					}
				} else {
					$i = array_search($key, $indexmastercols);
					$fieldposition = $i > 0 ? 'AFTER '.$indexmastercols[$i-1] : 'FIRST';
					$updates[] = "ADD `$key` $value $fieldposition";
				}
			}
		}

		$ret = true;
		if(!empty($updates)) {
			if(!DB::query("ALTER TABLE ".$archivetable." ".implode(', ', $updates), 'SILENT')) {
				$ret = $mastertable;
			} else {
			}
		}
		return $ret;
	}

	private function getcolumn($creatsql) {

		$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
		$matchs = array();
		preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

		$cols = explode("\n", $matchs[1]);
		$newcols = array();
		foreach ($cols as $value) {
			$value = trim($value);
			if(empty($value)) continue;
			$value = $this->remakesql($value);
			if(substr($value, -1) == ',') $value = substr($value, 0, -1);

			$vs = explode(' ', $value);
			$cname = $vs[0];

			if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

				$name_length = strlen($cname);
				if($cname == 'UNIQUE') $name_length = $name_length + 4;

				$subvalue = trim(substr($value, $name_length));
				$subvs = explode(' ', $subvalue);
				$subcname = $subvs[0];
				$newcols[$cname][$subcname] = trim(substr($value, ($name_length+2+strlen($subcname))));

			}  elseif($cname == 'PRIMARY') {

				$newcols[$cname] = trim(substr($value, 11));

			}  else {

				$newcols[$cname] = trim(substr($value, strlen($cname)));
			}
		}
		return $newcols;
	}

	private function remakesql($value) {
		$value = trim(preg_replace("/\s+/", ' ', $value));
		$value = str_replace(array('`',', ', ' ,', '( ' ,' )', 'mediumtext'), array('', ',', ',','(',')','text'), $value);
		return $value;
	}
}

?>
