<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class memory_driver_memcached
{
	public $enable;
	public $obj;

	public function init($config) {
		if(!empty($config['server'])) {
			$this->obj = new Memcached();
			$connect = $this->connectd($config['server'], $config['port']);
			$this->enable = $connect ? true : false;
		}
	}
	 public function connectd($host , $port){ 
		$servers = $this->obj->getServerList(); 
		if(is_array($servers)) { 
			foreach ($servers as $server) {
				if($server['host'] == $host and $server['port'] == $port) return true; 
			}
		} 
		return $this->obj->addServer($host , $port); 
	} 
	public function get($key) {
		return $this->obj->get($key);
	}

	public function getMulti($keys) {
		return $this->obj->get($keys);
	}
	public function set($key, $value, $ttl = 0) {
		return $this->obj->set($key, $value, $ttl);
	}

	public function rm($key) {
		return $this->obj->delete($key);
	}

	public function clear() {
		return $this->obj->flush();
	}

	public function inc($key, $step = 1) {
		return $this->obj->increment($key, $step);
	}

	public function dec($key, $step = 1) {
		return $this->obj->decrement($key, $step);
	}

}

?>