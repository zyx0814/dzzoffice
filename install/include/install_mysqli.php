<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_LEYUN')) {
    exit('Access Denied');
}

class dbstuff {
    var $querynum = 0;
    var $link;
    var $histories;
    var $time;
    var $tablepre;

    function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset, $pconnect = 0, $tablepre = '', $time = 0) {
        $this->time = $time;
        $this->tablepre = $tablepre;
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->link = new mysqli();
        if (strpos($dbhost, '.sock') !== false) {//地址直接是socket地址
            $unix_socket = $dbhost;
            $dbhost = 'localhost';
        }

        if (!$this->link->real_connect($dbhost, $dbuser, $dbpw, $dbname, null, $unix_socket, MYSQLI_CLIENT_COMPRESS)) {
            $this->halt('Can not connect to MySQL server');
        }
        if (version_compare($this->version(), '5.5.3', '<')) {
			$this->halt('MySQL version must be 5.5.3 or greater');
		}

        if ($dbcharset) {
            $this->link->set_charset($dbcharset);
        }

        $this->link->query("SET sql_mode=''");
        $this->link->query("SET character_set_client=binary");

    }

    function fetch_array($query, $result_type = MYSQLI_ASSOC) {
        return $query ? $query->fetch_array($result_type) : null;
    }

    function result_first($sql, &$data) {
        $query = $this->query($sql);
        $data = $this->result($query, 0);
    }

    function fetch_first($sql, &$arr) {
        $query = $this->query($sql);
        $arr = $this->fetch_array($query);
    }

    function fetch_all($sql, &$arr) {
        $query = $this->query($sql);
        while ($data = $this->fetch_array($query)) {
            $arr[] = $data;
        }
    }

    function cache_gc() {
        $this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
    }

    function query($sql, $type = '', $cachetime = FALSE) {
        $resultmode = $type == 'UNBUFFERED' ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;
        if (!($query = $this->link->query($sql, $resultmode)) && $type != 'SILENT') {
            $this->halt('SQL:', $sql);
        }
        $this->querynum++;
        $this->histories[] = $sql;
        return $query;
    }

    function affected_rows() {
        return $this->link->affected_rows;
    }

    function error() {
		return $this->link->error;
	}

	function errno() {
		return $this->link->errno;
	}

    function result($query, $row) {
        if (!$query || $query->num_rows == 0) {
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
        return $query ? $query->field_count : 0;
    }

    function free_result($query) {
        return $query ? $query->free() : false;
    }

    function insert_id() {
        return ($id = $this->link->insert_id) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    function fetch_row($query) {
        $query = $query ? $query->fetch_row() : null;
        return $query;
    }

    function fetch_fields($query) {
        return $query ? $query->fetch_field() : null;
    }

    function version() {
        return $this->link->server_info;
    }

    function escape_string($str) {
        return $this->link->escape_string($str);
    }

    function close() {
        return $this->link->close();
    }

    function halt($message = '', $sql = '') {
        show_error('run_sql_error', $message . $sql . '<br /> Error:' . $this->error() . '<br />Errno:' . $this->errno(), 0);
    }
}

?>