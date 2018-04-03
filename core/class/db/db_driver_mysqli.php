<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}


class db_driver_mysqli
{
	var $tablepre;
	var $version = '';
	var $drivertype = 'mysqli';
	var $querynum = 0;
	var $slaveid = 0;
	var $curlink;
	var $link = array();
	var $config = array();
	var $sqldebug = array();
	var $map = array();

	function db_mysql($config = array()) {
		if(!empty($config)) {
			$this->set_config($config);
		}
	}

	function set_config($config) {
		$this->config = &$config;
		$this->tablepre = $config['1']['tablepre'];
		if(!empty($this->config['map'])) {
			$this->map = $this->config['map'];
			for($i = 1; $i <= 100; $i++) {
				if(isset($this->map['attachment']) && $i <= 10) {
					$this->map['attachment_'.($i-1)] = $this->map['attachment'];
				}
				if(isset($this->map['user'])) {
					$this->map['user_status']  =
					$this->map['user_profile']  =
					$this->map['user_field']  =
					$this->map['user'];
				}
			}
		}
	}
	
	function linknum(){
		return $this->curlink;
	}

	function connect($serverid = 1) {

		if(empty($this->config) || empty($this->config[$serverid])) {
			$this->halt('config_db_not_found');
		}
		//兼容支持域名直接带有端口的情况
		if(strpos($this->config[$serverid]['dbhost'],':')!==false){
			list($dbhost,$port)=explode(':',$this->config[$serverid]['dbhost']);
			if($port && empty($this->config[$serverid]['port'])) $this->config[$serverid]['port']=$port;
			if($dbhost) $this->config[$serverid]['dbhost']=$dbhost;
		}elseif(strpos($this->config[$serverid]['dbhost'],'.sock')!==false){//地址直接是socket地址
			$this->config[$serverid]['unix_socket']=$this->config[$serverid]['dbhost'];
			$this->config[$serverid]['dbhost']='localhost';
		}
		if(empty($this->config[$serverid]['port'])) $this->config[$serverid]['port']='3306';
		
		$this->link[$serverid] = $this->_dbconnect(
			$this->config[$serverid]['dbhost'],
			$this->config[$serverid]['dbuser'],
			$this->config[$serverid]['dbpw'],
			$this->config[$serverid]['dbcharset'],
			$this->config[$serverid]['dbname'],
			$this->config[$serverid]['pconnect'],
			$this->config[$serverid]['port'],
			$this->config[$serverid]['unix_socket']
		);
		$this->curlink = $this->link[$serverid];
	}

	function _dbconnect($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect,$port='3306',$unix_socket='', $halt = true) {
		$link = new mysqli();
		if(!$link->real_connect($dbhost, $dbuser, $dbpw, $dbname, $port, $unix_socket)) {
			$halt && $this->halt('notconnect', $this->errno());
		} else {
			$this->curlink = $link;
			if($this->version() > '4.1') {
				$link->set_charset($dbcharset ? $dbcharset : $this->config[1]['dbcharset']);
				$serverset = $this->version() > '5.0.1' ? 'sql_mode=\'\'' : '';
				$serverset && $link->query("SET $serverset");
			}
		}
		return $link;
	}

	function table_name($tablename) {
		if(!empty($this->map) && !empty($this->map[$tablename])) {
			$id = $this->map[$tablename];
			if(!$this->link[$id]) {
				$this->connect($id);
			}
			$this->curlink = $this->link[$id];
		} else {
			$this->curlink = $this->link[1];
		}
		return $this->tablepre.$tablename;
	}

	function select_db($dbname) {
		return $this->curlink->select_db($dbname);
	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		if($result_type == 'MYSQL_ASSOC') $result_type = MYSQLI_ASSOC;
		return $query ? $query->fetch_array($result_type) : null;
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	public function query($sql, $silent = false, $unbuffered = false) {
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

		$resultmode = $unbuffered ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;

		if(!($query = $this->curlink->query($sql, $resultmode))) {
			if(in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
				$this->connect();
				return $this->curlink->query($sql, 'RETRY'.$silent);
			}
			if(!$silent) {
				$this->halt($this->error(), $this->errno(), $sql);
			}
		}

		if(defined('DZZ_DEBUG') && DZZ_DEBUG) {
			$this->sqldebug[] = array($sql, number_format((microtime(true) - $starttime), 6), debug_backtrace(), $this->curlink);
		}

		$this->querynum++;
		return $query;
	}

	function affected_rows() {
		return $this->curlink->affected_rows;
	}

	function error() {
		return (($this->curlink) ? $this->curlink->error : mysqli_error());
	}

	function errno() {
		return intval(($this->curlink) ? $this->curlink->errno : mysqli_errno());
	}

	function result($query, $row = 0) {
		if(!$query || $query->num_rows == 0) {
			return null;
		}
		$query->data_seek($row);
		$assocs = $query->fetch_row();
		return $assocs[0];
	}

	function num_rows($query) {
		$query = $query ? $query->num_rows : 0;
		return $query;
	}

	function num_fields($query) {
		return $query ? $query->field_count : null;
	}

	function free_result($query) {
		return $query ? $query->free() : false;
	}

	function insert_id() {
		return ($id = $this->curlink->insert_id) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = $query ? $query->fetch_row() : null;
		return $query;
	}

	function fetch_fields($query) {
		return $query ? $query->fetch_field() : null;
	}

	function version() {
		if(empty($this->version)) {
			$this->version = $this->curlink->server_info;
		}
		return $this->version;
	}

	function escape_string($str) {
		return $this->curlink->escape_string($str);
	}

	function close() {
		return $this->curlink->close();
	}

	function halt($message = '', $code = 0, $sql = '') {
		throw new DbException($message, $code, $sql);
	}

}

?>