<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class memory_driver_memcached {
    public $cacheName = 'MemCached';
    public $enable;
    public $obj;

    public function env() {
		return extension_loaded('memcached');
	}
    public function init($config) {
        if (!$this->env()) {
			$this->enable = false;
			return;
		}
        if (!empty($config['server'])) {
            $this->obj = new Memcached;
            $this->obj->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
			$this->obj->setOption(Memcached::OPT_TCP_NODELAY, true);
			$this->obj->addServer($config['server'], $config['port']);
            $connect=$this->obj->set('connect', '1');
            $this->enable = $connect ? true : false;
        }
    }

    public function get($key) {
        return $this->obj->get($key);
    }

    public function getMulti($keys) {
        return $this->obj->getMulti($keys);
    }

    public function set($key, $value, $ttl = 0) {
        return $this->obj->set($key, $value, $ttl);
    }

    public function add($key, $value, $ttl = 0) {
		return $this->obj->add($key, $value, $ttl);
	}

    public function rm($key) {
        return $this->obj->delete($key);
    }

    public function clear() {
        return $this->obj->flush();
    }

    public function inc($key, $step = 1) {
		return $this->obj->increment($key, $step, $step);
	}

	public function incex($key, $step = 1) {
		return $this->obj->increment($key, $step);
	}

	public function dec($key, $step = 1) {
		return $this->obj->decrement($key, $step);
	}

	public function exists($key) {
		$this->obj->get($key);
		return \Memcached::RES_NOTFOUND !== $this->obj->getResultCode();
	}

}

?>