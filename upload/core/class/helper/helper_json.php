<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_json {

	public static function encode($data) {
		switch ($type = gettype($data)) {
			case 'NULL':
				return 'null';
			case 'boolean':
				return ($data ? 'true' : 'false');
			case 'integer':
			case 'double':
			case 'float':
				return $data;
			case 'string':
				return '"' . addcslashes($data, "\r\n\t\"") . '"';
			case 'object':
				$data = get_object_vars($data);
			case 'array':
				$count = 0;
				$indexed = array();
				$associative = array();
				foreach ($data as $key => $value) {
					if($count !== NULL && (gettype($key) !== 'integer' || $count++ !== $key)) {
						$count = NULL;
					}
					$one = self::encode($value);
					$indexed[] = $one;
					$associative[] = self::encode($key) . ':' . $one;
				}
				if ($count !== NULL) {
					return '[' . implode(',', $indexed) . ']';
				} else {
					return '{' . implode(',', $associative) . '}';
				}
			default:
			return ''; // Not supported
		}
	}
}
?>