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

class table_user_wechat extends dzz_table
{
	public function __construct() {

		$this->_table = 'user_wechat';
		$this->_pk    = 'uid';

		parent::__construct();
	}
	function fetch_by_openid($openid,$appid){
		return DB::fetch_first("select * from %t where openid=%s and appid=%s",array($this->_table,$openid,$appid));
	}
}
?>
