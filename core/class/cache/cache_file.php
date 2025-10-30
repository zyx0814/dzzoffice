<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class ultrax_cache {

    function __construct($conf) {
        $this->conf = $conf;
    }

    function get_cache($key) {
        if ($this->cache_exists($key)) {
            $data = $this->_get_cache($key);
            return $data['data'];
        }
        return false;
    }

    function set_cache($key, $value, $life) {
		global $_G;
		$data = array($key => array('data' => $value, 'life' => $life));
		require_once libfile('function/cache');
		$cache_file = $this->get_cache_file_path($key);
		dmkdir(dirname($cache_file));
		$cachedata = "\$data = " . arrayeval($data) . ";\n";
		$cachedata_save = "<?php\n//Dzz! cache file, DO NOT modify me!".
		"\n//Created: " . date("M j, Y, G:i") .
		"\n//Identify: " . md5($cache_file . $cachedata . $_G['config']['security']['authkey']) . "\n\nif(!defined('IN_DZZ')) {\n\texit('Access Denied');\n}\n\n$cachedata?>";
		$fp = fopen($cache_file, 'cb');
		if(!($fp && flock($fp, LOCK_EX) && ftruncate($fp, 0) && fwrite($fp, $cachedata_save) && fflush($fp) && flock($fp, LOCK_UN) && fclose($fp))) {
			flock($fp, LOCK_UN);
			fclose($fp);
			unlink($cache_file);
			exit('Can not write to cache files, please check directory ./data/ and ./data/ultraxcache/ .');
		}
		return true;
	}
    function del_cache($key) {
        $cache_file = $this->get_cache_file_path($key);
        if (file_exists($cache_file)) {
            return @unlink($cache_file);
        }
        return true;
    }

    function _get_cache($key) {
        static $data = array();
        if (!isset($data[$key])) {
            include $this->get_cache_file_path($key);
        }
        return $data[$key];
    }

    function cache_exists($key) {
        $cache_file = $this->get_cache_file_path($key);
        if (!file_exists($cache_file)) {
            return false;
        }
        $data = $this->_get_cache($key);
        if ($data['life'] && (filemtime($cache_file) < time() - $data['life'])) {
            return false;
        }
        return true;
    }

    function get_cache_file_path($key) {
        static $cache_path = array();
        if (!isset($cache_path[$key])) {
            $dir = hexdec($key[0] . $key[1] . $key[2]) % 1000;
            $cache_path[$key] = $this->conf['path'] . '/' . $dir . '/' . $key . '.php';
        }
        return $cache_path[$key];
    }

}

?>