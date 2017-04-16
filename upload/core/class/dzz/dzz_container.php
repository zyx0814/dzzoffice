<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_container extends dzz_base
{
	protected $_obj;
	protected $_objs = array();
	public function __construct($obj = null) {
		if(isset($obj)) {
			if(is_object($obj)) {
				$this->_obj = $obj;
			} else if(is_string($obj)) {
				try {
					if(func_num_args()) {
						$p = func_get_args();
						unset($p[0]);
						$ref = new ReflectionClass($obj);
						$this->_obj = $ref->newInstanceArgs($p);
						unset($ref);
					} else {
						$this->_obj = new $obj;
					}
				} catch (Exception $e) {
					throw new Exception('Class "'.$obj.'" does not exists.');
				}
			}
		}
		parent::__construct();
	}
	public function getobj() {
		return $this->_obj;
	}
	public function setobj($value) {
		$this->_obj = $value;
	}
	public function __call($name, $p) {
		if(method_exists($this->_obj, $name)) {
			if(isset($this->_obj->methods[$name][0])) {
				$this->_call($name, $p, 0);
			}
			switch (count($p)) {
				case 0:	$this->_obj->data = $this->_obj->{$name}();break;
				case 1:	$this->_obj->data = $this->_obj->{$name}($p[0]);break;
				case 2:	$this->_obj->data = $this->_obj->{$name}($p[0], $p[1]);break;
				case 3:	$this->_obj->data = $this->_obj->{$name}($p[0], $p[1], $p[2]);break;
				case 4:	$this->_obj->data = $this->_obj->{$name}($p[0], $p[1], $p[2], $p[3]);break;
				case 5:	$this->_obj->data = $this->_obj->{$name}($p[0], $p[1], $p[2], $p[3], $p[4]);break;
				default: $this->_obj->data = call_user_func_array(array($this->_obj, $name), $p);break;
			}
			if(isset($this->_obj->methods[$name][1])) {
				$this->_call($name, $p, 1);
			}

			return $this->_obj->data;
		} else {
			throw new Exception('Class "'.get_class($this->_obj).'" does not have a method named "'.$name.'".');
		}
	}
	protected function _call($name, $p, $type) {
		$ret = null;
		if(isset($this->_obj->methods[$name][$type])) {
			foreach($this->_obj->methods[$name][$type] as $extend) {
				if(is_array($extend) && isset($extend['class'])) {
					$obj = $this->_getobj($extend['class'], $this->_obj);
					switch (count($p)) {
						case 0:	$ret = $obj->{$extend['method']}();break;
						case 1:	$ret = $obj->{$extend['method']}($p[0]);break;
						case 2:	$ret = $obj->{$extend['method']}($p[0], $p[1]);break;
						case 3:	$ret = $obj->{$extend['method']}($p[0], $p[1], $p[2]);break;
						case 4:	$ret = $obj->{$extend['method']}($p[0], $p[1], $p[2], $p[3]);break;
						case 5:	$ret = $obj->{$extend['method']}($p[0], $p[1], $p[2], $p[3], $p[4]);break;
						default: $ret = call_user_func_array(array($obj, $extend['method']), $p);break;
					}
				} elseif(is_callable($extend, true)) {
					if(is_array($extend)) {
						list($obj, $method) = $extend;
						if(method_exists($obj, $method)) {
							if(is_object($obj)) {
								$obj->obj = $this->_obj;
								switch (count($p)) {
									case 0:	$ret = $obj->{$method}();break;
									case 1:	$ret = $obj->{$method}($p[0]);break;
									case 2:	$ret = $obj->{$method}($p[0], $p[1]);break;
									case 3:	$ret = $obj->{$method}($p[0], $p[1], $p[2]);break;
									case 4:	$ret = $obj->{$method}($p[0], $p[1], $p[2], $p[3]);break;
									case 5:	$ret = $obj->{$method}($p[0], $p[1], $p[2], $p[3], $p[4]);break;
									default: $ret = call_user_func_array(array($obj, $method), $p);break;
								}
							} else {
								$p[] = $this;
								$ret = call_user_func_array($extend, $p);
							}
						}/* else {
							throw new Exception('Class "'.get_class($extend[0]).'" does not have a method named "'.$extend[1].'".');
						}*/
					} else {
						$p[] = $this->_obj;
						$ret = call_user_func_array($extend, $p);
					}
				}
			}
		}
		return $ret;
	}
	protected function _getobj($class, $obj) {
		if(!isset($this->_objs[$class])) {
			$this->_objs[$class] = new $class($obj);
			if(method_exists($this->_objs[$class], 'init_base_var')) {
				$this->_objs[$class]->init_base_var();
			}
		}
		return $this->_objs[$class];
	}
	public function __get($name) {
		if(isset($this->_obj) && property_exists($this->_obj, $name) === true) {
			return $this->_obj->$name;
		} else {
			return parent::__get($name);
		}
	}
	public function __set($name, $value) {
		if(isset($this->_obj) && property_exists($this->_obj, $name) === true) {
			return $this->_obj->$name = $value;
		} else {
			return parent::__set($name, $value);
		}
	}
	public function __isset($name) {
		return isset($this->_obj->$name);
	}
}
?>