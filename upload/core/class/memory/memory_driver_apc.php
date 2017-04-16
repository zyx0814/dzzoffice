<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class memory_driver_apc
{

	public function init($config) {

	}

	public function get($key) {
		return apc_fetch($key);
	}

	public function getMulti($keys) {
		return apc_fetch($keys);
	}

	public function set($key, $value, $ttl = 0) {
		return apc_store($key, $value, $ttl);
	}

	public function rm($key) {
		return apc_delete($key);
	}

	public function clear() {
		return apc_clear_cache('user');
	}

	public function inc($key, $step = 1) {
		return apc_inc($key, $step) !== false ? apc_fetch($key) : false;
	}

	public function dec($key, $step = 1) {
		return apc_dec($key, $step) !== false ? apc_fetch($key) : false;
	}

}
?>