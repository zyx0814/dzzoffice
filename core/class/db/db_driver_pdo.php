<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class db_driver_pdo extends db_driver_mysqli {
	var $tablepre;
	var $version = '';
	var $drivertype = 'pdo';
	var $querynum = 0;
	var $slaveid = 0;
	var $curlink;
	var $link = [];
	var $config = [];
	var $sqldebug = [];
	var $map = [];

	function db_mysql($config = []) {
		if(!empty($config)) {
			$this->set_config($config);
		}
	}

	function connect($serverid = 1) {

		if(empty($this->config) || empty($this->config[$serverid])) {
			$this->halt('config_db_not_found');
		}

		if(!empty($this->config[$serverid]['dsn'])) {
			$this->link[$serverid] = $this->_dbconnectWithDSN(
				$this->config[$serverid]['dsn'],
				$this->config[$serverid]['dbuser'],
				$this->config[$serverid]['dbpw'],
				$this->config[$serverid]['pconnect']
			);
		} else {
			$this->link[$serverid] = $this->_dbconnect(
				$this->config[$serverid]['dbhost'],
				$this->config[$serverid]['dbuser'],
				$this->config[$serverid]['dbpw'],
				$this->config[$serverid]['dbcharset'],
				$this->config[$serverid]['dbname'],
				$this->config[$serverid]['pconnect'],
				$this->config[$serverid]['unix_socket']
			);
		}
		$this->curlink = $this->link[$serverid];

	}

	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect, $unix_socket = '', $halt = true) {
		$option = [];
		if(intval($pconnect) === 1) {
			$option = [PDO::ATTR_PERSISTENT => true];
		}
		if ($unix_socket) {
			$dsn = 'mysql:unix_socket='.$unix_socket.';dbname='.$dbname.';charset='.$dbcharset;
		} else {
			$dsn = 'mysql:host='.$dbhost.';dbname='.$dbname.';charset='.$dbcharset;
		}
		$link = new PDO($dsn, $dbuser, $dbpw, $option);

		if(!$link) {
			$halt && $this->halt('notconnect', $this->errno());
		} else {
			$this->curlink = $link;
			$link->query('SET sql_mode=\'\',character_set_client=binary');
		}
		return $link;
	}

	function _dbconnectWithDSN($dsn, $dbuser, $dbpw, $pconnect, $halt = true) {
		$option = [];
		if(intval($pconnect) === 1) {
			$option = [PDO::ATTR_PERSISTENT => true];
		}
		$link = new PDO($dsn, $dbuser, $dbpw, $option);

		if(!$link) {
			$halt && $this->halt('notconnect', $this->errno());
		} else {
			$this->curlink = $link;
			$link->query('SET sql_mode=\'\',character_set_client=binary');
		}
		return $link;
	}

	function select_db($dbname) {
		return false;
	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		switch ($result_type) {
			case 'MYSQL_ASSOC':
			case MYSQLI_ASSOC:
			case 1:
				$result_type = PDO::FETCH_ASSOC;
				break;
			case 'MYSQL_NUM':
			case MYSQLI_NUM:
			case 2:
				$result_type = PDO::FETCH_NUM;
				break;
			default:
				$result_type = PDO::FETCH_BOTH;
		}
		return $query ? $query->fetch($result_type) : null;
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	public function query($sql, $silent = false, $unbuffered = false) {
		$arg = [];
		if(is_array($sql)) {
			$arg = !empty($sql[1]) ? (array)$sql[1] : [];
			$sql = $sql[0];
		}
		if(defined('DZZ_DEBUG') && DZZ_DEBUG) {
			$starttime = microtime(true);
		}

		if('UNBUFFERED' === $silent) {
			$silent = false;
			$unbuffered = true;
		} elseif('SILENT' === $silent) {
			$silent = true;
			$unbuffered = false;
		}

		if(!$unbuffered) {
			$this->curlink->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
		}

		$query = $this->curlink->prepare($sql);
		try {
			$query->execute($arg);
		} catch (Exception $e) {
			if(in_array($this->errno(), ['01002', '08003', '08S01', '08007']) && (strpos($silent, 'RETRY') !== 0)) {
				$this->connect();
				return $this->query([$sql, $arg], 'RETRY'.$silent);
			}

			if(!$silent) {
				$this->halt($this->error() ?? $e->getMessage(), $this->errno(), $sql);
			}
			return false;
		}

		if(defined('DZZ_DEBUG') && DZZ_DEBUG) {
			$this->sqldebug[] = [$sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink, $arg];
		}

		$this->querynum++;

		$cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
		if($cmd === 'UPDATE' || $cmd === 'DELETE' || $cmd === 'INSERT') {
			$this->rowCount = $query->rowCount();
		}

		return $query;
	}

	function affected_rows() {
		return $this->rowCount;
	}

	function error() {
		return (($this->curlink) ? $this->curlink->errorInfo()[2] : 'pdo_error');
	}

	function errno() {
		return intval(($this->curlink) ? $this->curlink->errorCode() : 99999);
	}

	function result($query, $row = 0) {
		if(!$query || $query->rowCount() == 0) {
			return null;
		}
		return $query->fetchColumn($row);
	}

	function num_rows($query) {
		return $query ? $query->rowCount() : 0;
	}

	function num_fields($query) {
		return $query ? $query->columnCount() : null;
	}

	function free_result($query) {
		return true;
	}

	function insert_id() {
		return ($id = $this->curlink->lastInsertId()) >= 0 ? $id : $this->result($this->query('SELECT last_insert_id()'), 0);
	}

	function fetch_row($query) {
		return $query ? $query->fetch_row() : null;
	}

	function fetch_fields($query) {
		return $query ? $query->fetch_field() : null;
	}

	function version() {
		if(empty($this->version)) {
			$this->version = $this->curlink->getAttribute(PDO::ATTR_SERVER_VERSION);
		}
		return $this->version;
	}

	function escape_string($str) {
		return substr($this->curlink->quote($str), 1, -1);
	}

	function close() {
		return true;
	}

	function halt($message = '', $code = 0, $sql = '') {
		throw new DbException(var_export($message, true), $code, $sql);
	}

	function begin_transaction() {
		if($this->curlink->beginTransaction()) {
			return true;
		} else {
			return false;
		}
	}

	function commit() {
		if($this->curlink->commit()) {
			return true;
		} else {
			return false;
		}
	}

	function rollback() {
		if($this->curlink->rollBack()) {
			return true;
		} else {
			return false;
		}
	}

}

