<?php
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
class core
{
	private static $_tables;
	private static $_tptables;
	private static $_imports;
	private static $_app;
	private static $_memory;

    // 类名映射
    protected static $map = array();

    //psr4
    private static $prefixLengthsPsr4 = array();
    private static $prefixDirsPsr4    = array();
    private static $fallbackDirsPsr4  = array();

	public static function app($params=array()) {
		if(!is_object(self::$_app)) {
			self::$_app = dzz_app::instance($params);
		}
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
	
	public static function tp_t($name) {
		return self::_tp_make_obj($name, 'table', DZZ_TABLE_EXTENDABLE);
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
	
	protected static function _tp_make_obj($name, $type, $extendable = true, $p = array()) {
		$folder = null;
		if($name[0] === '#') {
			list(, $folder, $name) = explode('#', $name);
		}
		$cname = $type.'_'.$name;
		if(!isset(self::$_tptables[$cname])) {
			/*if(!class_exists($cname, false)) {
				self::import('class'.'/'.$type.'/'.$name,$folder); 
			}*/
			self::$_tptables[$cname] = new dzz_mode($name); 
		}
		return self::$_tptables[$cname];
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
        $namespaceArr = explode('\\',$class);
        $namesapce = $namespaceArr[0].'\\';
        if(array_key_exists($namesapce,self::$prefixDirsPsr4)){
            $file = self::findFile($class);
            if(file_exists($file)){
                include_once $file;
                return true;
            }
        } elseif(strpos($class, '_') !== false) {
			list($folder) = explode('_', $class);
			$file = 'class/'.$folder.BS.substr($class, strlen($folder) + 1);
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

	//查找文件
    private static function findFile($class)
    {
        if (!empty(self::$map[$class])) {
            // 类库映射
            return self::$map[$class];
        }

        // 查找 PSR-4
        $logicalPathPsr4 = strtr($class, '\\', '/') . EXT;
        $first = $class[0];
        if (isset(self::$prefixLengthsPsr4[$first])) {

            foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {

                if (0 === strpos($class, $prefix)) {

                    foreach (self::$prefixDirsPsr4[$prefix] as $dir) {
                        if (is_file($file = $dir  .BS .substr($logicalPathPsr4, $length))) {

                            return $file;
                        }
                    }
                }
            }
        }

        // 查找 PSR-4 fallback dirs
        foreach (self::$fallbackDirsPsr4 as $dir) {
            if (is_file($file = $dir . $logicalPathPsr4)) {
                return $file;
            }
        }
        return self::$map[$class] = false;
    }
    // 注册命名空间
    public static function addNamespace($namespace, $path = '')
    {
        if (is_array($namespace)) {
            foreach ($namespace as $prefix => $paths) {
                self::addPsr4($prefix . '\\', rtrim($paths, '/'), true);
            }
        } else {
            self::addPsr4($namespace . '\\', rtrim($path, '/'), true);
        }
    }

    // 添加Psr4空间
    private static function addPsr4($prefix, $paths, $prepend = false)
    {
        if (!$prefix) {
            // Register directories for the root namespace.
            if ($prepend) {
                self::$fallbackDirsPsr4 = array_merge(
                    (array) $paths,
                    self::$fallbackDirsPsr4
                );
            } else {
                self::$fallbackDirsPsr4 = array_merge(
                    self::$fallbackDirsPsr4,
                    (array) $paths
                );
            }
        } elseif (!isset(self::$prefixDirsPsr4[$prefix])) {
            // Register directories for a new namespace.
            $length = strlen($prefix);
            if ('\\' !== $prefix[$length - 1]) {
                throw dzz_error::system_error("A non-empty PSR-4 prefix must end with a namespace separator.");
            }
            self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            self::$prefixDirsPsr4[$prefix]                = (array) $paths;
        } elseif ($prepend) {
            // Prepend directories for an already registered namespace.
            self::$prefixDirsPsr4[$prefix] = array_merge(
                (array) $paths,
                self::$prefixDirsPsr4[$prefix]
            );
        } else {
            // Append directories for an already registered namespace.
            self::$prefixDirsPsr4[$prefix] = array_merge(
                self::$prefixDirsPsr4[$prefix],
                (array) $paths
            );
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

	public static function loadConfig($file = null){
	    if($file && file_exists($file)){

            return include $file;

        }else{
            return false;
        }

    }

    public static function getConfig($name){

        global $_config;

        return $_config[$name];
    }
    public static function setConfig($name,$value = null){

        global $_config;

        if(is_string($name)){//单个设置

            $name = strtolower($name);

            $_config[$name] = $value;

        }elseif(is_array($name)){//批量设置

            $name = array_change_key_case($name,CASE_LOWER);

            foreach($name as $k=>$v){

                $_config[$k] = $v;
            }
        }else{
            return false;
        }
    }
    public static function getNamespaceDir($namespace = null){
        // 查找 PSR-4
        $logicalPathPsr4 = strtr($namespace, '\\', '/');

        $first = $namespace[0];

        if (isset(self::$prefixLengthsPsr4[$first])) {

            foreach (self::$prefixLengthsPsr4[$first] as $prefix => $length) {

                if (0 === strpos($namespace, $prefix)) {

                    foreach (self::$prefixDirsPsr4[$prefix] as $dir) {

                        if (is_dir($realdir = $dir  .BS .substr($logicalPathPsr4, $length))) {

                            return $realdir;
                        }
                    }
                }
            }
        }

    }
}