<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class memory_driver_xcache
{

	public function init($config) {

	}

	public function get($key) {
		return xcache_get($key);
	}

	public function set($key, $value, $ttl = 0) {
		return xcache_set($key, $value, $ttl);
	}

	public function rm($key) {
		return xcache_unset($key);
	}

	public function clear() {
		return xcache_clear_cache(XC_TYPE_VAR, 0);
	}

	public function inc($key, $step = 1) {
		return xcache_inc($key, $step);
	}

	public function dec($key, $step = 1) {
		return xcache_dec($key, $step);
	}

}
?>