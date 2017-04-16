<?php

define('IN_DZZ', true);
define('DZZ_ROOT', substr(dirname(__FILE__), 0, -10));
define('DZZ_CORE_DEBUG', false);
define('DZZ_TABLE_EXTENDABLE', false);

set_exception_handler(array('core', 'handleException'));

if(DZZ_CORE_DEBUG) {
	set_error_handler(array('core', 'handleError'));
	register_shutdown_function(array('core', 'handleShutdown'));
}

if(function_exists('spl_autoload_register')) {
	spl_autoload_register(array('core', 'autoload'));
} else {
	function __autoload($class) {
		return core::autoload($class);
	}
}

C::creatapp();



class core
{
	private static $_tables;
	private static $_imports;
	private static $_app;
	private static $_memory;

	public static function app() {
		return self::$_app;
	}

	public static function creatapp() {
		if(!is_object(self::$_app)) {
			self::$_app = dzz_app::instance();
		}
		return self::$_app;
	}

	public static function t($name) {
		return self::_make_obj($name, 'table', DZZ_TABLE_EXTENDABLE);
	}
	
	public static function m($name) {
		$args = array();
		if(func_num_args() > 1) {
			$args = func_get_args();
			unset($args[0]);
		}
		return self::_make_obj($name, 'model', true, $args);
	}

	protected static function _make_obj($name, $type, $extendable = true, $p = array()) {
		$folder = null;
		if($name[0] === '#') {
			list(, $folder, $name) = explode('#', $name);
		}
		$cname = $type.'_'.$name;
		if(!isset(self::$_tables[$cname])) {
			if(!class_exists($cname, false)) {
				self::import('class'.'/'.$type.'/'.$name,$folder);
			}
			if($extendable) {
				self::$_tables[$cname] = new dzz_container();
				switch (count($p)) {
					case 0:	self::$_tables[$cname]->obj = new $cname();break;
					case 1:	self::$_tables[$cname]->obj = new $cname($p[1]);break;
					case 2:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2]);break;
					case 3:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2], $p[3]);break;
					case 4:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2], $p[3], $p[4]);break;
					case 5:	self::$_tables[$cname]->obj = new $cname($p[1], $p[2], $p[3], $p[4], $p[5]);break;
					default: $ref = new ReflectionClass($cname);self::$_tables[$cname]->obj = $ref->newInstanceArgs($p);unset($ref);break;
				}
			} else {
				self::$_tables[$cname] = new $cname();
			}
		}
		return self::$_tables[$cname];
	}

	public static function memory() {
		if(!self::$_memory) {
			self::$_memory = new dzz_memory();
			self::$_memory->init(self::app()->config['memory']);
		}
		return self::$_memory;
	}

	public static function import($name, $folder = '', $force = true) {
		
		$key = $folder.$name;
		
		if(!isset(self::$_imports[$key])) {
			if($folder){
				$path = DZZ_ROOT.'./dzz/'.$folder.'/';
			}else{
				 $path = DZZ_ROOT.'./core/';
				 if(defined('CURSCRIPT')) {
					$path1= DZZ_ROOT.'./'.CURSCRIPT.'/';
					if(defined('CURMODULE')) $path2=DZZ_ROOT.'/'.CURSCRIPT.'/'.CURMODULE.'/';
				}
			}
		
			if(strpos($name, '/') !== false) {
				$pre = basename(dirname($name));
				$filename = dirname($name).'/'.$pre.'_'.basename($name).'.php';
			} else {
				$filename = $name.'.php';
			}
			
			if(isset($path2) && is_file($path2.$filename)) {
				self::$_imports[$key] = true;
				$rt = include $path2.$filename;
				return $rt;
			}elseif(isset($path1) && is_file($path1.$filename)) {
				self::$_imports[$key] = true;
				$rt = include $path1.$filename;
				return $rt;
			}elseif(is_file($path.$filename)) {
				self::$_imports[$key] = true;
				$rt = include $path.$filename;
				return $rt;
			} elseif(!$force) {
				return false;
			} else {
				//throw new Exception('Oops! System file lost: '.$filename);
			}
		}
		return true;
	}

	public static function handleException($exception) {
		dzz_error::exception_error($exception);
	}


	public static function handleError($errno, $errstr, $errfile, $errline) {
		if($errno & DZZ_CORE_DEBUG) {
			dzz_error::system_error($errstr, false, true, false);
		}
	}

	public static function handleShutdown() {
		if(($error = error_get_last()) && $error['type'] & DZZ_CORE_DEBUG) {
			dzz_error::system_error($error['message'], false, true, false);
		}
	}

	public static function autoload($class) {
		$module='';
		if($class[0] === '#') {
			list(, $module, $class) = explode('#', $class);
		}
		$class = ($class);
		if(strpos($class, '_') !== false) {
			list($folder) = explode('_', $class);
			$file = 'class/'.$folder.'/'.substr($class, strlen($folder) + 1);
		} else {
			$file = 'class/'.$class;
		}
		try {
			
			self::import($file,$module);
			return true;

		} catch (Exception $exc) {

			$trace = $exc->getTrace();
			foreach ($trace as $log) {
				if(empty($log['class']) && $log['function'] == 'class_exists') {
					return false;
				}
			}
			dzz_error::exception_error($exc);
		}
	}

	public static function analysisStart($name){
		$key = 'other';
		if($name[0] === '#') {
			list(, $key, $name) = explode('#', $name);
		}
		if(!isset($_ENV['analysis'])) {
			$_ENV['analysis'] = array();
		}
		if(!isset($_ENV['analysis'][$key])) {
			$_ENV['analysis'][$key] = array();
			$_ENV['analysis'][$key]['sum'] = 0;
		}
		$_ENV['analysis'][$key][$name]['start'] = microtime(TRUE);
		$_ENV['analysis'][$key][$name]['start_memory_get_usage'] = memory_get_usage();
		$_ENV['analysis'][$key][$name]['start_memory_get_real_usage'] = memory_get_usage(true);
		$_ENV['analysis'][$key][$name]['start_memory_get_peak_usage'] = memory_get_peak_usage();
		$_ENV['analysis'][$key][$name]['start_memory_get_peak_real_usage'] = memory_get_peak_usage(true);
	}

	public static function analysisStop($name) {
		$key = 'other';
		if($name[0] === '#') {
			list(, $key, $name) = explode('#', $name);
		}
		if(isset($_ENV['analysis'][$key][$name]['start'])) {
			$diff = round((microtime(TRUE) - $_ENV['analysis'][$key][$name]['start']) * 1000, 5);
			$_ENV['analysis'][$key][$name]['time'] = $diff;
			$_ENV['analysis'][$key]['sum'] = $_ENV['analysis'][$key]['sum'] + $diff;
			unset($_ENV['analysis'][$key][$name]['start']);
			$_ENV['analysis'][$key][$name]['stop_memory_get_usage'] = memory_get_usage();
			$_ENV['analysis'][$key][$name]['stop_memory_get_real_usage'] = memory_get_usage(true);
			$_ENV['analysis'][$key][$name]['stop_memory_get_peak_usage'] = memory_get_peak_usage();
			$_ENV['analysis'][$key][$name]['stop_memory_get_peak_real_usage'] = memory_get_peak_usage(true);
		}
		return $_ENV['analysis'][$key][$name];
	}
}

class C extends core {}
class DB extends dzz_database {}
class IO extends dzz_io {}
?>