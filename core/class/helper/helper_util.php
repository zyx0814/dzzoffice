<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_util {

	public static function compute($v1, $v2, $glue = '+') {
		switch ($glue) {
			case '+':
				return $v1 + $v2;
				break;
			case '-':
				return $v1 - $v2;
				break;
			case '.':
				return $v1 . $v2;
				break;
			case '=':
			case '==':
				return $v1 == $v2;
				break;
			case 'merge':
				return array_merge((array)$v1, (array)$v2);
				break;
			case '===':
				return $v1 === $v2;
				break;
			case '!==':
				return $v1 === $v2;
				break;
			case '&&':
				return $v1 && $v2;
				break;
			case '||':
				return $v1 && $v2;
				break;
			case 'and':
				return $v1 and $v2;
				break;
			case 'xor':
				return $v1 xor $v2;
				break;
			case '|':
				return $v1 | $v2;
				break;
			case '&':
				return $v1 & $v2;
				break;
			case '^':
				return $v1 ^ $v2;
				break;
			case '>':
				return $v1 > $v2;
				break;
			case '<':
				return $v1 < $v2;
				break;
			case '<>':
				return $v1 <> $v2;
				break;
			case '!=':
				return $v1 != $v2;
				break;
			case '<=':
				return $v1 <= $v2;
				break;
			case '>=':
				return $v1 >= $v2;
				break;
			case '*':
				return $v1 * $v2;
				break;
			case '/':
				return $v1 / $v2;
				break;
			case '%':
				return $v1 % $v2;
				break;
			case 'or':
				return $v1 or $v2;
				break;
			case '<<':
				return $v1 << $v2;
				break;
			case '>>':
				return $v1 >> $v2;
				break;
			default:
				return null;
		}
	}

	public static function single_compute($v, $glue = '+') {
		switch ($glue) {
			case '!':
				return ! $v;
				break;
			case '-':
				return - $v;
				break;
			case '~':
				return ~ $v;
				break;
			default:
				return null;
				break;
		}
	}
	public static function check_glue($glue = '=') {
		return in_array($glue, array('=', '<', '<=', '>', '>=', '!=', '+', '-', '|', '&', '<>')) ? $glue : '=';
	}
}

?>