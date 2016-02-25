<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
class db_driver_mysql_slave extends db_driver_mysql
{

	public $slaveid = null;

	public $slavequery = 0;

	public $slaveexcept = false;

	public $excepttables = array();

	public $tablename = '';

	protected $_weighttable = array();

	public $serverid = null;

	function set_config($config) {
		parent::set_config($config);

		if($this->config['common']['slave_except_table']) {
			$this->excepttables = explode(',', str_replace(' ', '', $this->config['common']['slave_except_table']));
		}
	}

	public function table_name($tablename) {
		$this->tablename = $tablename;
		if(!$this->slaveexcept && $this->excepttables) {
			$this->slaveexcept = in_array($tablename, $this->excepttables, true);
		}
		$this->serverid = isset($this->map[$this->tablename]) ? $this->map[$this->tablename] : 1;
		return $this->tablepre.$tablename;
	}

	protected function _slave_connect() {
		if(!empty($this->config[$this->serverid]['slave'])) {
			$this->_choose_slave();
			if($this->slaveid) {
				if(!isset($this->link[$this->slaveid])) {
					$this->connect($this->slaveid);
				}
				$this->slavequery ++;
				$this->curlink = $this->link[$this->slaveid];
			}
			return true;
		} else {
			return false;
		}
	}

	protected function _choose_slave(){
		if(!isset($this->_weighttable[$this->serverid])) {
			foreach ($this->config[$this->serverid]['slave'] as $key => $value) {
				$this->_weighttable[$this->serverid] .= str_repeat($key, 1 + intval($value['weight']));
			}
		}
		$sid = $this->_weighttable[$this->serverid][mt_rand(0, strlen($this->_weighttable[$this->serverid]) -1)];
		$this->slaveid = $this->serverid.'_'.$sid;
		if(!isset($this->config[$this->slaveid])) {
			$this->config[$this->slaveid] = $this->config[$this->serverid]['slave'][$sid];
		}
	}

	protected function _master_connect() {
		if(!$this->link[$this->serverid]) {
			$this->connect($this->serverid);
		}
		$this->curlink = $this->link[$this->serverid];
	}

	public function query($sql, $silent = false, $unbuffered = false) {
		if(!(!$this->slaveexcept && strtoupper(substr($sql, 0 , 6)) === 'SELECT' && $this->_slave_connect())) {
			$this->_master_connect();
		}
		$this->tablename = '';
		$this->slaveexcept = false;
		return parent::query($sql, $silent, $unbuffered);
	}

}
?>