<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class helper_util {

    public static function compute($v1, $v2, $glue = '+') {
        switch ($glue) {
            case '+':
                return $v1 + $v2;
            case '-':
                return $v1 - $v2;
            case '.':
                return $v1 . $v2;
            case '=':
            case '==':
                return $v1 == $v2;
            case 'merge':
                return array_merge((array)$v1, (array)$v2);
            case '===':
                return $v1 === $v2;
            case '!==':
                return $v1 === $v2;
            case '&&':
                return $v1 && $v2;
            case '||':
                return $v1 && $v2;
            case 'and':
                return $v1 and $v2;
            case 'xor':
                return $v1 xor $v2;
            case '|':
                return $v1 | $v2;
            case '&':
                return $v1 & $v2;
            case '^':
                return $v1 ^ $v2;
            case '>':
                return $v1 > $v2;
            case '<':
                return $v1 < $v2;
            case '<>':
                return $v1 <> $v2;
            case '!=':
                return $v1 != $v2;
            case '<=':
                return $v1 <= $v2;
            case '>=':
                return $v1 >= $v2;
            case '*':
                return $v1 * $v2;
            case '/':
                return $v1 / $v2;
            case '%':
                return $v1 % $v2;
            case 'or':
                return $v1 or $v2;
            case '<<':
                return $v1 << $v2;
            case '>>':
                return $v1 >> $v2;
            default:
                return null;
        }
    }

    public static function single_compute($v, $glue = '+') {
        switch ($glue) {
            case '!':
                return !$v;
            case '-':
                return -$v;
            case '~':
                return ~$v;
            default:
                return null;
        }
    }

    public static function check_glue($glue = '=') {
        return in_array($glue, ['=', '<', '<=', '>', '>=', '!=', '+', '-', '|', '&', '<>']) ? $glue : '=';
    }
}

