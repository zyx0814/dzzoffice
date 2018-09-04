<?php

class memory_driver_redis
{
	var $enable;
	var $obj;

	function init($config) {
		if(!empty($config['server'])) {
			try {
				$this->obj = new Redis();
				if($config['pconnect']) {
					$connect = @$this->obj->pconnect($config['server'], $config['port']);
				} else {
					$connect = @$this->obj->connect($config['server'], $config['port']);
				}
                if($config['password']){
                    @$this->obj->auth($config['password']);
                }
			} catch (RedisException $e) {
			    echo $e;
			}
			$this->enable = $connect ? true : false;
			if($this->enable) {
				if($config['requirepass']) {
					$this->obj->auth($config['requirepass']);
				}
				@$this->obj->setOption(Redis::OPT_SERIALIZER, $config['serializer']);
			}
		}
	}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new memory_driver_redis();
			$object->init(getglobal('config/memory/redis'));
		}
		return $object;
	}

	function get($key) {
		if(is_array($key)) {
			return $this->getMulti($key);
		}
		return $this->obj->get($key);
	}

	function getMulti($keys) {
		$result = $this->obj->getMultiple($keys);
		$newresult = array();
		$index = 0;
		foreach($keys as $key) {
			if($result[$index] !== false) {
				$newresult[$key] = $result[$index];
			}
			$index++;
		}
		unset($result);
		return $newresult;
	}

	function select($db=0) {
		return $this->obj->select($db);
	}

	function set($key, $value, $ttl = 0) {
		if($ttl) {
			return $this->obj->setex($key, $ttl, $value);
		} else {
			return $this->obj->set($key, $value);
		}
	}

	function rm($key) {
		return $this->obj->delete($key);
	}

	function setMulti($arr, $ttl=0) {
		if(!is_array($arr)) {
			return FALSE;
		}
		foreach($arr as $key => $v) {
			$this->set($key, $v, $ttl);
		}
		return TRUE;
	}

	function inc($key, $step = 1) {
		return $this->obj->incr($key, $step);
	}

	function dec($key, $step = 1) {
		return $this->obj->decr($key, $step);
	}

	function getSet($key, $value) {
		return $this->obj->getSet($key, $value);
	}

	function sADD($key, $value) {
		return $this->obj->sADD($key, $value);
	}

	function sRemove($key, $value) {
		return $this->obj->sRemove($key, $value);
	}

	function sMembers($key) {
		return $this->obj->sMembers($key);
	}

	function sIsMember($key, $member) {
		return $this->obj->sismember($key, $member);
	}

	function keys($key) {
		return $this->obj->keys($key);
	}

	function expire($key, $second){
		return $this->obj->expire($key, $second);
	}

	function sCard($key) {
		return $this->obj->sCard($key);
	}

	function hSet($key, $field, $value) {
		return $this->obj->hSet($key, $field, $value);
	}

	function hDel($key, $field) {
		return $this->obj->hDel($key, $field);
	}

	function hLen($key) {
		return $this->obj->hLen($key);
	}

	function hVals($key) {
		return $this->obj->hVals($key);
	}

	function hIncrBy($key, $field, $incr){
		return $this->obj->hIncrBy($key, $field, $incr);
	}

	function hGetAll($key) {
		return $this->obj->hGetAll($key);
	}

	function sort($key, $opt) {
		return $this->obj->sort($key, $opt);
	}

	function exists($key) {
		return $this->obj->exists($key);
	}

	function clear() {
		return $this->obj->flushAll();
	}
}
?>