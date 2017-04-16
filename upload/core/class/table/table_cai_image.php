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

class table_cai_image extends dzz_table
{
	public function __construct() {

		$this->_table = 'cai_image';
		$this->_pk    = 'cid';
		$this->_pre_cache_key = 'cai_image_';
		$this->_cache_ttl = 0;
		parent::__construct();
	}
	public function delete_by_cid($cid){
		$cimage=self::fetch($cid);
		if($cimage['copys']<=1){
			if($cimage['aid']) C::t('attachment')->delete_by_aid($cimage['aid']);
			self::delete($cid);
		}else{
			self::update($cid,array('copys'=>$cimage['copys']-1));
		}
	}
}
?>
