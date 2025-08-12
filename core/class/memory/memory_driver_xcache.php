<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class memory_driver_xcache {
    public $cacheName = 'XCache';
	public $enable;
    public function env() {
		return function_exists('xcache_get');
	}

    public function init($config) {
        $this->enable = $this->env();
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