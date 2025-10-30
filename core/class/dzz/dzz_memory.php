<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class dzz_memory extends dzz_base {
    private $config;
    private $extension = array();
    private $memory;
    private $prefix;
    private $userprefix;
    public $type;
    public $enable = false;
    public $debug = array();

    public $gotset = false; // 是否支持Set数据类型
	public $gothash = false; // 是否支持Hash数据类型
	public $goteval = false; // 是否支持lua脚本eval
	public $gotsortedset = false; // 是否支持SortedSet
	public $gotcluster = false; // 是否是集群环境
	public $gotpipeline = false; // 是否支持pipeline

    public function __construct() {
    }

    public function init($config) {
        $this->config = $config;
		$this->prefix = empty($config['prefix']) ? substr(md5($_SERVER['HTTP_HOST']), 0, 6).'_' : $config['prefix'];
		unset($this->config['prefix']);

		foreach($this->config as $cache => $config) {
			$available = is_array($config) ? !empty($config['server']) : !empty($config);
			if($available && !is_object($this->memory)) {
				$class_name = 'memory_driver_'.$cache;
				$this->memory = new $class_name();
				$this->memory->init($config);
				if(!$this->memory->enable) {
					$this->memory = null;
				} else {
					$this->type = $this->memory->cacheName;
					$this->enable = true;
                    $this->gotset = method_exists($this->memory, 'feature') && $this->memory->feature('set');
					$this->gothash = method_exists($this->memory, 'feature') && $this->memory->feature('hash');
					$this->goteval = method_exists($this->memory, 'feature') && $this->memory->feature('eval');
					$this->gotsortedset = method_exists($this->memory, 'feature') && $this->memory->feature('sortedset');;
					$this->gotcluster = method_exists($this->memory, 'feature') && $this->memory->feature('cluster');
					$this->gotpipeline = method_exists($this->memory, 'feature') && $this->memory->feature('pipeline');
					break;
				}
			}
		}

    }

    public function get($key, $prefix = '') {
        static $getmulti = null;
        $ret = false;
        if ($this->enable) {
            if (!isset($getmulti)) $getmulti = method_exists($this->memory, 'getMulti');
            $this->userprefix = $prefix;
            if (is_array($key)) {
                if ($getmulti) {
                    $ret = $this->memory->getMulti($this->_key($key));
                    if ($ret !== false && !empty($ret)) {
                        $_ret = array();
                        foreach ((array)$ret as $_key => $value) {
                            $_ret[$this->_trim_key($_key)] = $value;
                        }
                        $ret = $_ret;
                    }
                } else {
                    $ret = array();
                    $_ret = false;
                    foreach ($key as $id) {
                        if (($_ret = $this->memory->get($this->_key($id))) !== false && isset($_ret)) {
                            $ret[$id] = $_ret;
                        }
                    }
                }
                if (empty($ret)) $ret = false;
            } else {
                $ret = $this->memory->get($this->_key($key));
                if (!isset($ret)) $ret = false;
            }
        }
        return $ret;
    }

    public function set($key, $value, $ttl = 0, $prefix = '') {

        $ret = false;
        if ($value === false) $value = '';
        if ($this->enable) {
            $this->userprefix = $prefix;
            $ret = $this->memory->set($this->_key($key), $value, $ttl);
        }
        return $ret;
    }

    public function add($key, $value, $ttl = 0, $prefix = '') {
		$ret = false;
		if($value === false) $value = '';
		if($this->enable) {
			$this->userprefix = $prefix;
			$ret = $this->memory->add($this->_key($key), $value, $ttl);
		}
		return $ret;
	}

	public function exists($key, $prefix = '') {
		$ret = false;
		if ($this->enable && method_exists($this->memory, 'exists')) {
			$this->userprefix = $prefix;
			$ret = $this->memory->exists($this->_key($key));
		}
		return $ret;
	}

    public function rm($key, $prefix = '') {
        $ret = false;
        if ($this->enable) {
            $this->userprefix = $prefix;
            $key = $this->_key($key);
            foreach ((array)$key as $id) {
                $ret = $this->memory->rm($id);
            }
        }
        return $ret;
    }

    public function clear() {
        $ret = false;
        if ($this->enable && method_exists($this->memory, 'clear')) {
            $ret = $this->memory->clear();
        }
        return $ret;
    }

    public function inc($key, $step = 1, $prefix = '') {
        static $hasinc = null;
        $ret = false;
        if ($this->enable) {
            $this->userprefix = $prefix;
            if (!isset($hasinc)) $hasinc = method_exists($this->memory, 'inc');
            if ($hasinc) {
                $ret = $this->memory->inc($this->_key($key), $step);
            } else {
                if (($data = $this->memory->get($key)) !== false) {
                    $ret = ($this->memory->set($key, $data + ($step)) !== false ? $this->memory->get($key) : false);
                }
            }
        }
        return $ret;
    }

    public function incex($key, $value, $prefix = '') {
		if (!$this->enable || !$this->gotset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->incex($this->_key($key), $value);
	}


	public function dec($key, $step = 1, $prefix = '') {
		static $hasdec = null;
		$ret = false;
		if($this->enable) {
			$this->userprefix = $prefix;
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

    public function sadd($key, $value, $prefix = '') {
		if (!$this->enable || !$this->gotset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->sadd($this->_key($key), $value);
	}

	public function srem($key, $value, $prefix = '') {
		if (!$this->enable || !$this->gotset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->srem($this->_key($key), $value);
	}

	public function sismember($key, $value, $prefix = '') {
		if (!$this->enable || !$this->gotset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->sismember($this->_key($key), $value);
	}

	public function scard($key, $prefix = '') {
		if (!$this->enable || !$this->gotset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->scard($this->_key($key));
	}

	public function smembers($key, $prefix = '') {
		if (!$this->enable || !$this->gotset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->smembers($this->_key($key));
	}

	public function hmset($key, $value, $prefix = '') {
		if (!$this->enable || !$this->gothash) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->hmset($this->_key($key), $value);
	}

	public function hgetall($key, $prefix = '') {
		if (!$this->enable || !$this->gothash) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->hgetall($this->_key($key));
	}

	public function hexists($key, $field, $prefix = '') {
		if (!$this->enable || !$this->gothash) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->hexists($this->_key($key), $field);
	}

	public function hget($key, $field, $prefix = '') {
		if (!$this->enable || !$this->gothash) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->hget($this->_key($key), $field);
	}

	/*
	 * 如果设置了sha_key，将脚本load，然后将sha保存在$prefix_$sha_key中
	 * 如果sha_key中有sha，则执行evalSha
	 * 如果没有sha_key，则eval脚本
	 */
	public function evalscript($script, $argv, $sha_key, $prefix = '') {
		if (!$this->enable || !$this->goteval) {
			return false;
		}
		if (!is_array($argv)) {
			$argv = array();
		}
		$this->userprefix = $prefix;
		if ($sha_key) {
			$sha_key = $sha_key . '_eval_sha';
			$sha = $this->memory->get($this->_key($sha_key));
			$should_load = false;
			if (!$sha) {
				if (!$script) return false;
				$should_load = true;
			} else {
				if (!$this->memory->scriptexists($sha)) { // 重启redis后，有可能sha-key存在，但script已经不存在了
					$should_load = true;
				}
			}
			if ($should_load) {
				$sha = $this->memory->loadscript($script);
				$this->memory->set($this->_key($sha_key), $sha);
			}
			return $this->memory->evalSha($sha, array_merge(array($this->_key('')), $argv));
		} else {
			return $this->memory->evalscript($script, array_merge(array($this->_key('')), $argv));
		}
	}

	public function zadd($key, $value, $score, $prefix = '') {
		if (!$this->enable || !$this->gotsortedset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->zadd($this->_key($key), $value, $score);
	}

	public function zrem($key, $value, $prefix = '') {
		if (!$this->enable || !$this->gotsortedset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->zrem($this->_key($key), $value);
	}

	public function zscore($key, $member, $prefix = '') {
		if (!$this->enable || !$this->gotsortedset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->zscore($this->_key($key), $member);
	}

	public function zcard($key, $prefix = '') {
		if (!$this->enable || !$this->gotsortedset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->zcard($this->_key($key));
	}

	public function zrevrange($key, $start, $end, $prefix = '', $withscore = false) {
		if (!$this->enable || !$this->gotsortedset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->zrevrange($this->_key($key), $start, $end, $withscore);
	}

	public function zincrby($key, $member, $value, $prefix = '') {
		if (!$this->enable || !$this->gotsortedset) {
			return false;
		}
		$this->userprefix = $prefix;
		return $this->memory->zincrby($this->_key($key), $member, $value);
	}

	public function pipeline() {
		if (!$this->enable || !$this->gotpipeline) {
			return false;
		}
		return $this->memory->pipeline();
	}

	public function commit() {
		if (!$this->enable || !$this->gotpipeline) {
			return false;
		}
		return $this->memory->commit();
	}

	public function discard() {
		if (!$this->enable || !$this->gotpipeline) {
			return false;
		}
		return $this->memory->discard();
	}

    private function _key($str) {
        $perfix = $this->prefix . $this->userprefix;
        if (is_array($str)) {
            foreach ($str as &$val) {
                $val = $perfix . $val;
            }
        } else {
            $str = $perfix . $str;
        }
        return $str;
    }

    private function _trim_key($str) {
        return substr($str, strlen($this->prefix . $this->userprefix));
    }

    public function getextension() {
        return $this->extension;
    }

    public function getconfig() {
        return $this->config;
    }
}

?>