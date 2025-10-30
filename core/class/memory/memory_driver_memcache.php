<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class memory_driver_memcache {
    public $cacheName = 'MemCache';
    public $enable;
    public $obj;
    public function env() {
		return extension_loaded('memcache');
	}

    public function init($config) {
        if (!$this->env()) {
			$this->enable = false;
			return;
		}
        if (!empty($config['server'])) {

            $this->obj = new Memcache;
            if ($config['pconnect']) {
                $connect = @$this->obj->pconnect($config['server'], $config['port']);
            } else {
                $connect = @$this->obj->connect($config['server'], $config['port']);
            }

            $this->enable = $connect ? true : false;
        }
    }

    public function get($key) {
        return $this->obj->get($key);
    }

    public function getMulti($keys) {
        return $this->obj->get($keys);
    }

    public function set($key, $value, $ttl = 0) {
		return $this->obj->set($key, $value, 0, $ttl); // 不再使用MEMCACHE_COMPRESSED，因为不能increment
	}

	public function add($key, $value, $ttl = 0) {
		return $this->obj->add($key, $value, 0, $ttl);
	}

    public function rm($key) {
        return $this->obj->delete($key);
    }

    public function clear() {
        return $this->obj->flush();
    }

    public function inc($key, $step = 1) {
		if (!$this->obj->increment($key, $step)) {
			$this->set($key, $step);
		}
	}

	public function incex($key, $step = 1) {
		return $this->obj->increment($key, $step);
	}

	public function dec($key, $step = 1) {
		return $this->obj->decrement($key, $step);
	}

	public function exists($key) {
	    return $this->obj->get($key) !== FALSE;
    }

}

?>