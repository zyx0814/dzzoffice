<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
class helper_config {
	function save($filename, $config, $default=array()) {

	$config = self::setdefault($config, $default);
	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$content = <<<EOT
<?php


\$_config = array();

EOT;
	$content .= self::getvars(array('_config' => $config));
	$content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)."\r\n return \$_config;";
	file_put_contents($filename, $content);
}

	public function setdefault($var, $default) {
		foreach ($default as $k => $v) {
			if(!isset($var[$k])) {
				$var[$k] = $default[$k];
			} elseif(is_array($v)) {
				$var[$k] = setdefault($var[$k], $default[$k]);
			}
		}
		return $var;
	}
	public function getvars($data, $type = 'VAR') {
		$evaluate = '';
		foreach($data as $key => $val) {
			if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
				continue;
			}
			if(is_array($val)) {
				$evaluate .= self::buildarray($val, 0, "\${$key}")."\r\n";
			} else {
				$val = addcslashes($val, '\'\\');
				$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
			}
		}
		return $evaluate;
	}
	public function buildarray($array, $level = 0, $pre = '$_config') {
		static $ks;
		if($level == 0) {
			$ks = array();
			$return = '';
		}

		foreach ($array as $key => $val) {
			if($level == 0) {
				$newline = str_pad('  CONFIG '.strtoupper($key).'  ', 70, '-', STR_PAD_BOTH);
				$return .= "\r\n// $newline //\r\n";
				if($key == 'admincp') {
					$newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
					$return .= "// $newline //\r\n";
				}
			}

			$ks[$level] = $ks[$level - 1]."['$key']";
			if(is_array($val)) {
				$ks[$level] = $ks[$level - 1]."['$key']";
				$return .= self::buildarray($val, $level + 1, $pre);
			} else {
				$val =  is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
				$return .= $pre.$ks[$level - 1]."['$key']"." = $val;\r\n";
			}
		}
		return $return;
	}
}
?>