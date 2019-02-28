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

class table_syscache extends dzz_table
{
	private $_isfilecache;

	public function __construct() {

		$this->_table = 'syscache';
		$this->_pk    = 'cname';
		$this->_pre_cache_key = '';
		$this->_isfilecache = getglobal('config/cache/type') == 'file';
		$this->_allowmem = memory('check');

		parent::__construct();
	}

	public function fetch($cachename) {
		$data = $this->fetch_all(array($cachename));
		return isset($data[$cachename]) ? $data[$cachename] : false;
	}
	public function fetch_all($cachenames) {

		$data = array();
		$cachenames = is_array($cachenames) ? $cachenames : array($cachenames);
		if($this->_allowmem) {
			$data = memory('get', $cachenames);
			$newarray = $data !== false ? array_diff($cachenames, array_keys($data)) : $cachenames;
			if(empty($newarray)) {
				return $data;
			} else {
				$cachenames = $newarray;
			}
		}

		if($this->_isfilecache) {
			$lostcaches = array();
			foreach($cachenames as $cachename) {
				if(!@include_once(DZZ_ROOT.'./data/cache/cache_'.$cachename.'.php')) {
					$lostcaches[] = $cachename;
				} elseif($this->_allowmem) {
					memory('set', $cachename, $data[$cachename]);
				}
			}
			if(!$lostcaches) {
				return $data;
			}
			$cachenames = $lostcaches;
			unset($lostcaches);
		}

		$query = DB::query('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field('cname', $cachenames));
		while($syscache = DB::fetch($query)) {
			$data[$syscache['cname']] = $syscache['ctype'] ? unserialize($syscache['data']) : $syscache['data'];
			$this->_allowmem && (memory('set', $syscache['cname'], $data[$syscache['cname']]));
			if($this->_isfilecache) {
				$cachedata = '$data[\''.$syscache['cname'].'\'] = '.var_export($data[$syscache['cname']], true).";\n\n";
				if(($fp = @fopen(DZZ_ROOT.'./data/cache/cache_'.$syscache['cname'].'.php', 'wb'))) {
					fwrite($fp, "<?php\n//Dzz! cache file, DO NOT modify me!\n//Identify: ".md5($syscache['cname'].$cachedata.getglobal('config/security/authkey'))."\n\n$cachedata?>");
					fclose($fp);
				}
			}
		}
		
		foreach($cachenames as $name) {
			if($data[$name] === null) {
				$data[$name] = null;
				$this->_allowmem && (memory('set', $name, array()));
			}
		}

		return $data;
	}

	public function insert($cachename, $data) {
		parent::insert(array(
			'cname' => $cachename,
			'ctype' => is_array($data) ? 1 : 0,
			'dateline' => TIMESTAMP,
			'data' => is_array($data) ? serialize($data) : $data,
		), false, true);

		if($this->_allowmem && memory('get', $cachename) !== false) {
			memory('set', $cachename, $data);
		}
		$this->_isfilecache && @unlink(DZZ_ROOT.'./data/cache/cache_'.$cachename.'.php');
	}

	public function update($cachename, $data,$unbuffered = false, $low_priority = false) {
		$this->insert($cachename, $data);
	}

	public function delete($cachenames,$unbuffered = false) {
		parent::delete($cachenames);
		if($this->_allowmem || $this->_isfilecache) {
			foreach((array)$cachenames as $cachename) {
				$this->_allowmem && memory('rm', $cachename);
				$this->_isfilecache && @unlink(DZZ_ROOT.'./data/cache/cache_'.$cachename.'.php');
			}
		}
	}
}

?>
