<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class table_user_field extends dzz_table
{
	public function __construct() {

		$this->_table = 'user_field';
		$this->_pk    = 'uid';
		$this->_pre_cache_key = 'user_field_';
		$this->_cache_ttl = 60*60;
		
		parent::__construct();
	}
}
