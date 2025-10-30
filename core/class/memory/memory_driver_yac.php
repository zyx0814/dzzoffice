<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class memory_driver_yac {

	public $cacheName = 'Yac';
	private $object = null;
	public $enable;

	public function env() {
		return extension_loaded('Yac');
	}

	public function init($config) {
		$this->enable = $this->env();
		if ($this->enable) {
			$this->object = new yac();
		}
	}

	public function get($key) {
		return $this->object->get($key);
	}

	public function getMulti($keys) {
		$result = $this->object->get($keys);
		foreach ($result as $key => $value) {
			if ($value === false) {
				unset($result[$key]);
			}
		}
		return $result;
	}

	public function set($key, $value, $ttl = 0) {
		return $this->object->set($key, $value, $ttl);
	}

	public function rm($key) {
		return $this->object->delete($key);
	}

	public function clear() {
		return $this->object->flush();
	}

	public function inc($key, $step = 1) {
		$old = $this->get($key);
		if (!$old) {
			return false;
		}
		return $this->set($key, $old + $step);
	}

	public function dec($key, $step = 1) {
		$old = $this->get($key);
		if (!$old) {
			return false;
		}
		return $this->set($key, $old - $step);
	}

}