<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_memory extends dzz_base
{
	private $config;
	private $extension = array();
	private $memory;
	private $prefix;
	private $userprefix;
	public $type;
	public $enable = false;
	public $debug = array();

	public function __construct() {
		$this->extension['redis'] = extension_loaded('redis');
		$this->extension['memcache'] = extension_loaded('memcache');
		$this->extension['memcached'] = extension_loaded('memcached');
		$this->extension['apc'] = function_exists('apc_cache_info') && @apc_cache_info();
		$this->extension['xcache'] = function_exists('xcache_get');
		$this->extension['eaccelerator'] = function_exists('eaccelerator_get');
		$this->extension['wincache'] = function_exists('wincache_ucache_meminfo') && wincache_ucache_meminfo();
	}

	public function init($config) {
		$this->config = $config;
		$this->prefix = empty($config['prefix']) ? substr(md5($_SERVER['HTTP_HOST']), 0, 6).'_' : $config['prefix'];


		if($this->extension['redis'] && !empty($config['redis']['server'])) {
			$this->memory = new memory_driver_redis();
			$this->memory->init($this->config['redis']);
			if(!$this->memory->enable) {
				$this->memory = null;
			}
		}
		if(!$this->memory->enable && $this->extension['memcached'] && !empty($config['memcached']['server'])) {
			$this->memory = new memory_driver_memcached();
			$this->memory->init($this->config['memcached']);
			if(!$this->memory->enable) {
				$this->memory = null;
			}
		}
		if(!$this->memory->enable && $this->extension['memcache'] && !empty($config['memcache']['server'])) {
			$this->memory = new memory_driver_memcache();
			$this->memory->init($this->config['memcache']);
			if(!$this->memory->enable) {
				$this->memory = null;
			}
		}

		foreach(array('apc', 'eaccelerator', 'xcache', 'wincache') as $cache) {
			if(!is_object($this->memory) && $this->extension[$cache] && $this->config[$cache]) {
				$class_name = 'memory_driver_'.$cache;
				$this->memory = new $class_name();
				$this->memory->init(null);
			}
		}

		if(is_object($this->memory)) {
			$this->enable = true;
			$this->type = str_replace('memory_driver_', '', get_class($this->memory));
		}

	}

	public function get($key, $prefix = '') {
		static $getmulti = null;
		$ret = false;
		if($this->enable) {
			if(!isset($getmulti)) $getmulti = method_exists($this->memory, 'getMulti');
			$this->userprefix = $prefix;
			if(is_array($key)) {
				if($getmulti) {
					$ret = $this->memory->getMulti($this->_key($key));
					if($ret !== false && !empty($ret)) {
						$_ret = array();
						foreach((array)$ret as $_key => $value) {
							$_ret[$this->_trim_key($_key)] = $value;
						}
						$ret = $_ret;
					}
				} else {
					$ret = array();
					$_ret = false;
					foreach($key as $id) {
						if(($_ret = $this->memory->get($this->_key($id))) !== false && isset($_ret)) {
							$ret[$id] = $_ret;
						}
					}
				}
				if(empty($ret)) $ret = false;
			} else {
				$ret = $this->memory->get($this->_key($key));
				if(!isset($ret)) $ret = false;
			}
		}
		return $ret;
	}

	public function set($key, $value, $ttl = 0, $prefix = '') {

		$ret = false;
		if($value === false) $value = '';
		if($this->enable) {
			$this->userprefix = $prefix;
			$ret = $this->memory->set($this->_key($key), $value, $ttl);
		}
		return $ret;
	}

	public function rm($key, $prefix = '') {
		$ret = false;
		if($this->enable) {
			$this->userprefix = $prefix;
			$key = $this->_key($key);
			foreach((array)$key as $id) {
				$ret = $this->memory->rm($id);
			}
		}
		return $ret;
	}

	public function clear() {
		$ret = false;
		if($this->enable && method_exists($this->memory, 'clear')) {
			$ret = $this->memory->clear();
		}
		return $ret;
	}

	public function inc($key, $step = 1) {
		static $hasinc = null;
		$ret = false;
		if($this->enable) {
			if(!isset($hasinc)) $hasinc = method_exists($this->memory, 'inc');
			if($hasinc) {
				$ret = $this->memory->inc($this->_key($key), $step);
			} else {
				if(($data = $this->memory->get($key)) !== false) {
					$ret = ($this->memory->set($key, $data + ($step)) !== false ? $this->memory->get($key) : false);
				}
			}
		}
		return $ret;
	}

	public function dec($key, $step = 1) {
		static $hasdec = null;
		$ret = false;
		if($this->enable) {
			if(!isset($hasdec)) $hasdec = method_exists($this->memory, 'dec');
			if($hasdec) {
				$ret = $this->memory->dec($this->_key($key), $step);
			} else {
				if(($data = $this->memory->get($key)) !== false) {
					$ret = ($this->memory->set($key, $data - ($step)) !== false ? $this->memory->get($key) : false);
				}
			}
		}
		return $ret;
	}

	private function _key($str) {
		$perfix = $this->prefix.$this->userprefix;
		if(is_array($str)) {
			foreach($str as &$val) {
				$val = $perfix.$val;
			}
		} else {
			$str = $perfix.$str;
		}
		return $str;
	}

	private function _trim_key($str) {
		return substr($str, strlen($this->prefix.$this->userprefix));
	}

	public function getextension() {
		return $this->extension;
	}

	public function getconfig() {
		return $this->config;
	}
}
?>