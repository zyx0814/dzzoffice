<?php

class memory_driver_redis {
    public $cacheName = 'Redis';
    var $enable;
    var $obj;
    public function env() {
		return extension_loaded('redis');
	}

    function init($config) {
        if(!$this->env()) {
			$this->enable = false;
			return;
		}
        if (!empty($config['server'])) {
            try {
                $this->obj = new Redis();
                if ($config['pconnect']) {
                    $connect = @$this->obj->pconnect($config['server'], $config['port']);
                } else {
                    $connect = @$this->obj->connect($config['server'], $config['port']);
                }
            } catch (RedisException $e) {
            }
            $this->enable = $connect ? true : false;
            if ($this->enable) {
                if ($config['requirepass']) {
                    $this->obj->auth($config['requirepass']);
                }
                @$this->obj->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
				$this->select(isset($config['db']) ? $config['db'] : 0);
            }
        }
    }

    function feature($feature) {
		switch ($feature) {
			case 'set':
			case 'hash':
			case 'sortedset':
			case 'pipeline':
				return true;
			case 'eval':
				$ret = $this->obj->eval("return 1");
				return ($ret === 1);
			case 'cluster':
				$ret = $this->obj->info("cluster");
				return ($ret['cluster_enabled'] === 1);
			default:
				return false;
		}
	}

    public static function &instance() {
        static $object;
        if (empty($object)) {
            $object = new memory_driver_redis();
            $object->init(getglobal('config/memory/redis'));
        }
        return $object;
    }

    function get($key) {
        if (is_array($key)) {
            return $this->getMulti($key);
        }
        return $this->_try_deserialize($this->obj->get($key));
    }

    function getMulti($keys) {
        $result = $this->obj->mGet($keys);
        $newresult = array();
        $index = 0;
        foreach ($keys as $key) {
            if ($result[$index] !== false) {
                $newresult[$key] = $this->_try_deserialize($result[$index]);
            }
            $index++;
        }
        unset($result);
        return $newresult;
    }

    function select($db = 0) {
        return $this->obj->select($db);
    }

    function set($key, $value, $ttl = 0) {
        if (is_array($value)) {
			$value = serialize($value);
		}
        if ($ttl) {
            return $this->obj->setex($key, $ttl, $value);
        } else {
            return $this->obj->set($key, $value);
        }
    }

    function add($key, $value, $ttl = 0) {
		if ($ttl > 0) return $this->obj->set($key, $value, array('nx', 'ex' => $ttl));
		return $this->obj->setnx($key, $value);
	}

    function rm($key) {
        return $this->obj->del($key);
    }

    function setMulti($arr, $ttl = 0) {
        if (!is_array($arr)) {
            return FALSE;
        }
        foreach ($arr as $key => $v) {
            $this->set($key, $v, $ttl);
        }
        return TRUE;
    }

    function inc($key, $step = 1) {
        return $this->obj->incr($key, $step);
    }

    function incex($key, $step = 1) {
		$script = "if redis.call('exists', ARGV[1]) == 1 then return redis.call('incrby', ARGV[1], ARGV[2]) end";
		return $this->evalscript($script, array($key, $step));
	}

    function dec($key, $step = 1) {
        return $this->obj->decr($key, $step);
    }

    function getSet($key, $value) {
        return $this->obj->getSet($key, $value);
    }

    function sadd($key, $value) {
		return $this->obj->sAdd($key, $value);
	}

	function srem($key, $value) {
		return $this->obj->sRem($key, $value);
	}

    function smembers($key) {
		return $this->obj->sMembers($key);
	}

	function sismember($key, $member) {
		return $this->obj->sIsMember($key, $member);
	}

    function keys($key) {
        return $this->obj->keys($key);
    }

    function expire($key, $second) {
        return $this->obj->expire($key, $second);
    }

    function sCard($key) {
        return $this->obj->sCard($key);
    }

    function hSet($key, $field, $value) {
        return $this->obj->hSet($key, $field, $value);
    }

    function hmset($key, $value) {
		return $this->obj->hMSet($key, $value);
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

    function hIncrBy($key, $field, $incr) {
        return $this->obj->hIncrBy($key, $field, $incr);
    }

    function hgetall($key) {
		return $this->obj->hGetAll($key);
	}

    function hget($key, $field) {
		return $this->obj->hGet($key, $field);
	}

	function hexists($key, $field) {
		return $this->obj->hExists($key, $field);
	}

	function evalscript($script, $argv) {
		return $this->obj->eval($script, $argv);
	}

	function evalsha($sha, $argv) {
		return $this->obj->evalSha($sha, $argv);
	}

	function loadscript($script) {
		return $this->obj->script('load', $script);
	}

	function scriptexists($sha) {
		$r =  $this->obj->script('exists', $sha);
		return $r[0];
	}

	function zadd($key, $member, $score) {
		return $this->obj->zAdd($key, $score, $member);
	}

	function zrem($key, $member) {
		return $this->obj->zRem($key, $member);
	}

	function zscore($key, $member) {
		return $this->obj->zScore($key, $member);
	}

	function zcard($key) {
		return $this->obj->zCard($key);
	}

	function zrevrange($key, $start, $end, $withscore = false) {
		return $this->obj->zRevRange($key, $start, $end, $withscore);
	}

	function zincrby($key, $member, $value) {
		return $this->obj->zIncrBy($key, $value, $member);
	}
    function sort($key, $opt) {
        return $this->obj->sort($key, $opt);
    }

    function exists($key) {
        return $this->obj->exists($key);
    }

    function clear() {
		return $this->obj->flushDb();
	}

	function pipeline() {
		return $this->obj->multi(Redis::PIPELINE);
	}

	function commit() {
		return $this->obj->exec();
	}

	function discard() {
		return $this->obj->discard();
	}

    private function _try_deserialize($data) {
		try {
			$ret = dunserialize($data);
			if ($ret === FALSE) {
				return $data;
			}
			return $ret;
		} catch (Exception $e) {
		}
		return $data;
	}
}

?>