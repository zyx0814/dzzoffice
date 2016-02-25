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

class table_cai_music extends dzz_table
{
	public function __construct() {

		$this->_table = 'cai_music';
		$this->_pk    = 'cid';
		//$this->_pre_cache_key = 'cai_music_';
		//$this->_cache_ttl = 0;
		parent::__construct();
	}
}

?>
