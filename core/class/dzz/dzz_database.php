<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class dzz_database {

    public static $db;

    public static $driver;
    protected static $logsql = 0;
    protected static $ispdo = false;

    public static function init($driver, $config, $logsql = 0) {
        self::$driver = $driver;
        self::$db = new $driver;
        self::$logsql = $logsql;
        self::$ispdo = $driver == 'db_driver_pdo';
        self::$db->set_config($config);
        self::$db->connect();
    }

    public static function linknum() {
        return self::$db->linknum();
    }

    public static function object() {
        return self::$db;
    }

    public static function table($table) {
        return self::$db->table_name($table);
    }

    public static function delete($table, $condition, $limit = 0, $unbuffered = true) {
        $arg = [];
        if (empty($condition)) {
            return false;
        } elseif (is_array($condition)) {
            if(count($condition) == 2 && isset($condition['where']) && isset($condition['arg'])) {
                $where = self::$ispdo ? self::format_prepared($condition['where'], $condition['arg']) : self::format($condition['where'], $condition['arg']);
			} else {
                $where = self::$ispdo ? self::implode_prepared($condition, $arg, ' AND ') : self::implode($condition, ' AND ');
			}
        } else {
            $where = $condition;
        }
        $limit = dintval($limit);
        $sql = 'DELETE FROM ' . self::table($table) . " WHERE $where " . ($limit > 0 ? "LIMIT $limit" : '');
        return self::query($sql, $arg, false, $unbuffered);
    }

    public static function insert($table, $data, $return_insert_id = false, $replace = false, $silent = false) {
		if(!self::$ispdo) {
			$sql = 'SET '.self::implode($data);
			$arg = null;
		} else {
			$arg = [];
			$sql = self::implode_prepared_insert($data, $arg);
		}

		$cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';

        $sql = "$cmd " . self::table($table) . " $sql";
		$silent = $silent ? 'SILENT' : '';

		return self::query($sql, $arg, $silent, !$return_insert_id);
	}

    public static function update($table, $data, $condition = '', $unbuffered = false, $low_priority = false) {
        if(self::$ispdo) {
            $arg = [];
			$sql = self::implode_prepared($data, $arg);
		} else {
            $arg = null;
			$sql = self::implode($data);
		}

        if (empty($sql)) {
            return false;
        }
        $cmd = 'UPDATE ' . ($low_priority ? 'LOW_PRIORITY' : '');
        $where = '';
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = self::$ispdo ? self::implode_prepared($condition, $arg, ' AND ') : self::implode($condition, ' AND ');
        } else {
            $where = $condition;
        }
        $sql = "$cmd " . self::table($table) . " SET $sql WHERE $where";
        $res = self::query($sql, $arg, false, $unbuffered);
        return $res;
    }

    public static function insert_id() {
        return self::$db->insert_id();
    }

    public static function fetch($resourceid, $type = null) {
        if (!isset($type)) {
			$type = constant('MYSQLI_ASSOC');
		}
        return self::$db->fetch_array($resourceid, $type);
    }

    public static function fetch_first($sql, $arg = [], $silent = false) {
        $res = self::query($sql, $arg, $silent, false);
        if ($res === 0) {
            return [];
        }
        $ret = self::$db->fetch_array($res);
        self::$db->free_result($res);
        return $ret ? $ret : [];
    }

    public static function fetch_all($sql, $arg = [], $keyfield = '', $silent = false) {
        $data = [];
        $query = self::query($sql, $arg, $silent, false);
        while ($row = self::$db->fetch_array($query)) {
            if ($keyfield && isset($row[$keyfield])) {
                $data[$row[$keyfield]] = $row;
            } else {
                $data[] = $row;
            }
        }
        self::$db->free_result($query);
        return $data;
    }

    public static function result($resourceid, $row = 0) {
        return self::$db->result($resourceid, $row);
    }

    public static function result_first($sql, $arg = [], $silent = false) {
        $res = self::query($sql, $arg, $silent, false);
        $ret = self::$db->result($res, 0);
        self::$db->free_result($res);
        return $ret;
    }

    public static function query($sql, $arg = [], $silent = false, $unbuffered = false) {
        if (!empty($arg)) {
            if (is_array($arg)) {
                $sql = self::$ispdo ? self::format_prepared($sql, $arg) : self::format($sql, $arg);
            } elseif ($arg === 'SILENT') {
                $silent = true;
                $arg = [];
            } elseif ($arg === 'UNBUFFERED') {
                $unbuffered = true;
                $arg = [];
            }
        }
        self::checkquery($sql);

        $ret = self::$db->query(self::$ispdo ? [$sql, $arg] : $sql, $silent, $unbuffered);
        if (!$unbuffered && $ret) {
            $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
            switch ($cmd) {
                case 'SELECT':
                    break;
                case 'UPDATE':
                case 'DELETE':
                    $ret = self::$db->affected_rows();
                    break;
                case 'INSERT':
                    $ret = self::$db->insert_id();
                    break;
            }

            if(self::$logsql) {
                if ($cmd === 'UPDATE' || $cmd === 'INSERT') {
                    self::logsql($sql, 'updatelog');
                } elseif ($cmd === 'DELETE') {
                    self::logsql($sql, 'deletelog');
                }
            }
        }
        return $ret;
    }

    public static function num_rows($resourceid) {
        return self::$db->num_rows($resourceid);
    }

    public static function affected_rows() {
        return self::$db->affected_rows();
    }

    public static function free_result($query) {
        return self::$db->free_result($query);
    }

    public static function error() {
        return self::$db->error();
    }

    public static function errno() {
        return self::$db->errno();
    }

    public static function checkquery($sql) {
        return dzz_database_safecheck::checkquery($sql);
    }

    public static function quote($str, $noarray = false) {
        if (is_string($str))
            return '\'' . self::$db->escape_string($str) . '\'';

        if (is_int($str) or is_float($str))
            return '\'' . $str . '\'';

        if (is_array($str)) {
            if ($noarray === false) {
                foreach ($str as &$v) {
                    $v = self::quote($v, true);
                }
                return $str;
            } else {
                return '\'\'';
            }
        }

        if (is_bool($str))
            return $str ? '1' : '0';

        return '\'\'';
    }

    public static function quote_field($field) {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $field[$k] = self::quote_field($v);
            }
        } else {
            if (strpos($field, '`') !== false)
                $field = str_replace('`', '', $field);
            $field = '`' . $field . '`';
        }
        return $field;
    }

    public static function limit($start, $limit = 0) {
        $limit = intval($limit > 0 ? $limit : 0);
        $start = intval($start > 0 ? $start : 0);
        if ($start > 0 && $limit > 0) {
            return " LIMIT $start, $limit";
        } elseif ($limit) {
            return " LIMIT $limit";
        } elseif ($start) {
            return " LIMIT $start";
        } else {
            return '';
        }
    }

    public static function order($field, $order = 'ASC') {
        if (empty($field)) {
            return '';
        }
        $order = strtoupper($order) == 'ASC' || empty($order) ? 'ASC' : 'DESC';
        return self::quote_field($field) . ' ' . $order;
    }

    public static function field($field, $val, $glue = '=') {

        $field = self::quote_field($field);

        if (is_array($val)) {
            $glue = $glue == 'notin' ? 'notin' : 'in';
        } elseif ($glue == 'in') {
            $glue = '=';
        }

        switch ($glue) {
            case '>=':
            case '=':
                return $field . $glue . self::quote($val);
                break;
            case '-':
            case '+':
                return $field . '=' . $field . $glue . self::quote((string)$val);
                break;
            case '|':
            case '&':
            case '^':
            case '&~':
                return $field . '=' . $field . $glue . self::quote($val);
                break;
            case '>':
            case '<':
            case '<>':
            case '<=':
                return $field . $glue . self::quote($val);
                break;

            case 'like':
                return $field . ' LIKE(' . self::quote($val) . ')';
                break;

            case 'in':
            case 'notin':
                $val = $val ? implode(',', self::quote($val)) : '\'\'';
                return $field . ($glue == 'notin' ? ' NOT' : '') . ' IN(' . $val . ')';
                break;

            default:
                throw new DbException('Not allow this glue between field and value: "' . $glue . '"');
        }
    }

    public static function implode($array, $glue = ',') {
        $sql = $comma = '';
        $glue = ' ' . trim($glue) . ' ';
        foreach ($array as $k => $v) {
            $sql .= $comma . self::quote_field($k) . '=' . self::quote($v);
            $comma = $glue;
        }
        return $sql;
    }

    public static function implode_prepared_insert($array, &$arg, $glue = ',') {
		$sql1 = $sql2 = $comma1 = $comma2 = '';
		$glue = ' '.trim($glue).' ';
		foreach($array as $k => $v) {
			$sql1 .= $comma1.self::quote_field($k);
			$sql2 .= $comma2.'?';
			$arg[] = !is_object($v) ? $v : '';
			$comma1 = $glue;
			$comma2 = $glue;
		}
		return '('.$sql1.') VALUES ('.$sql2.')';
	}

	public static function implode_prepared($array, &$arg, $glue = ',') {
		$sql = $comma = '';
		$glue = ' '.trim($glue).' ';
		foreach($array as $k => $v) {
			$sql .= $comma.self::quote_field($k).'=?';
			$arg[] = !is_object($v) ? $v : '';
			$comma = $glue;
		}
		return $sql;
	}

    public static function format($sql, $arg) {
        $count = substr_count($sql, '%');
        if (!$count) {
            return $sql;
        } elseif ($count > count($arg)) {
            throw new DbException('SQL string format error! This SQL need "' . $count . '" vars to replace into.', 0, $sql);
        }

        $len = strlen($sql);
        $i = $find = 0;
        $ret = '';
        while ($i <= $len && $find < $count) {
            if ($sql[$i] == '%') {
                $next = $sql[$i + 1];
                if ($next == 't') {
                    $ret .= self::table($arg[$find]);
                } elseif ($next == 's') {
                    $ret .= self::quote(is_array($arg[$find]) ? serialize($arg[$find]) : (string)$arg[$find]);
                } elseif ($next == 'f') {
                    $ret .= sprintf('%F', $arg[$find]);
                } elseif ($next == 'd') {
                    $ret .= dintval($arg[$find]);
                } elseif ($next == 'i') {
                    $ret .= $arg[$find];
                } elseif ($next == 'n') {
                    if (!empty($arg[$find])) {
                        $ret .= is_array($arg[$find]) ? implode(',', self::quote($arg[$find])) : self::quote($arg[$find]);
                    } else {
                        $ret .= '0';
                    }
                } else {
                    $ret .= self::quote($arg[$find]);
                }
                $i++;
                $find++;
            } else {
                $ret .= $sql[$i];
            }
            $i++;
        }
        if ($i < $len) {
            $ret .= substr($sql, $i);
        }
        return $ret;
    }

    public static function format_prepared($sql, &$arg) {
		$params = [];
		$count = substr_count($sql, '%');
		if(!$count) {
			return $sql;
		} elseif($count > count($arg)) {
			throw new DbException('SQL string format error! This SQL need "'.$count.'" vars to replace into.', 0, $sql);
		}

		$len = strlen($sql);
		$i = $find = 0;
		$ret = '';
		while($i <= $len && $find < $count) {
			if($sql[$i] == '%') {
				$next = $sql[$i + 1];
				$v = $arg[$find];
				if($next == 't') {
					$ret .= self::table($v);
				} elseif($next == 's') {
					$v = is_array($v) ? serialize($v) : (string)$v;
					$ret .= '?';
					$params[] = $v;
				} elseif($next == 'f') {
					$ret .= sprintf('%F', $v);
				} elseif($next == 'd') {
					$ret .= dintval($v);
				} elseif($next == 'i') {
					$ret .= $v;
				} elseif($next == 'n') {
					if(!empty($v)) {
						if(is_array($v)) {
							$_ret = [];
							foreach($v as $_v) {
								$_ret[] = '?';
								$params[] = $_v;
							}
							$ret .= implode(',', $_ret);
						} else {
							$ret .= '?';
							$params[] = $v;
						}
					} else {
						$ret .= '0';
					}
				} else {
					$ret .= '?';
					$params[] = $v;
				}
				$i++;
				$find++;
			} else {
				$ret .= $sql[$i];
			}
			$i++;
		}
		if($i < $len) {
			$ret .= substr($sql, $i);
		}
		$arg = $params;
		return $ret;
	}

    public static function begin_transaction() {
		return self::$db->begin_transaction();
	}

    public static function commit() {
		return self::$db->commit();
	}

	public static function rollback() {
		return self::$db->rollback();
	}

    protected static function logsql($sql, $type) {
        // 检查是否启用日志
        if (empty(self::$logsql)) {
            return;
        }

        if (self::$logsql == 2) {
            $backtrace = debug_backtrace();
            krsort($backtrace);
            $call_chain = array_map(function($error) {
                $file = str_replace(DZZ_ROOT, '', $error['file']);
                $func = ($error['class'] ?? '') . ($error['type'] ?? '') . ($error['function'] ?? '');
                return "[file: {$file}] {$func}:" . sprintf('%04d', $error['line']);
            }, $backtrace);

            $sql .= ' 调用链：';
            $sql .= implode(' -> ', $call_chain);
        }
        writelog($type, $sql);
        return;
    }

}

class dzz_database_safecheck {

    protected static $checkcmd = array('SEL' => 1, 'UPD' => 1, 'INS' => 1, 'REP' => 1, 'DEL' => 1);
    protected static $config;

    public static function checkquery($sql) {
        if(is_array($sql)) {
			$sql = $sql[0];
		}
        if (self::$config === null) {
            self::$config = getglobal('config/security/querysafe');
        }
        if (self::$config['status']) {
            $check = 1;
            $cmd = strtoupper(substr(trim($sql), 0, 3));
            if (isset(self::$checkcmd[$cmd])) {
                $check = self::_do_query_safe($sql);
            } elseif (substr($cmd, 0, 2) === '/*') {
                $check = -1;
            }

            if ($check < 1) {
                throw new DbException('It is not safe to do this query', 0, $sql);
            }
        }
        return true;
    }

    private static function _do_query_safe($sql) {
        $sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
        $mark = $clean = '';
        if (strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false && strpos($sql, '@') === false && strpos($sql, '`') === false) {
            $clean = preg_replace("/'(.+?)'/s", '', $sql);
        } else {
            $len = strlen($sql);
            $mark = $clean = '';
            for ($i = 0; $i < $len; $i++) {
                $str = $sql[$i];
                switch ($str) {
                    case '`':
                        if (!$mark) {
                            $mark = '`';
                            $clean .= $str;
                        } elseif ($mark == '`') {
                            $mark = '';
                        }
                        break;
                    case '\'':
                        if (!$mark) {
                            $mark = '\'';
                            $clean .= $str;
                        } elseif ($mark == '\'') {
                            $mark = '';
                        }
                        break;
                    case '/':
                        if (empty($mark) && $sql[$i + 1] == '*') {
                            $mark = '/*';
                            $clean .= $mark;
                            $i++;
                        } elseif ($mark == '/*' && $sql[$i - 1] == '*') {
                            $mark = '';
                            $clean .= '*';
                        }
                        break;
                    case '#':
                        if (empty($mark)) {
                            $mark = $str;
                            $clean .= $str;
                        }
                        break;
                    case "\n":
                        if ($mark == '#' || $mark == '--') {
                            $mark = '';
                        }
                        break;
                    case '-':
                        if (empty($mark) && substr($sql, $i, 3) == '-- ') {
                            $mark = '-- ';
                            $clean .= $mark;
                        }
                        break;

                    default:

                        break;
                }
                $clean .= $mark ? '' : $str;
            }
        }

        if (strpos($clean, '@') !== false) {
            return '-3';
        }

        $clean = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($clean));

        if (self::$config['afullnote']) {
            $clean = str_replace('/**/', '', $clean);
        }

        if (is_array(self::$config['dfunction'])) {
            foreach (self::$config['dfunction'] as $fun) {
                if (strpos($clean, $fun . '(') !== false)
                    return '-1';
            }
        }

        if (is_array(self::$config['daction'])) {
            foreach (self::$config['daction'] as $action) {
                if (strpos($clean, $action) !== false)
                    return '-3';
            }
        }

        if (self::$config['dlikehex'] && strpos($clean, 'like0x')) {
            return '-2';
        }

        if (is_array(self::$config['dnote'])) {
            foreach (self::$config['dnote'] as $note) {
                if (strpos($clean, $note) !== false)
                    return '-4';
            }
        }

        return 1;
    }

    public static function setconfigstatus($data) {
        self::$config['status'] = $data ? 1 : 0;
    }

}

?>