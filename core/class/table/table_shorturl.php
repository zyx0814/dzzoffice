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

class table_shorturl extends dzz_table
{
	public function __construct() {

		$this->_table = 'shorturl';
		$this->_pk    = 'sid';
		$this->_pre_cache_key = 'shorturl_';
		$this->_cache_ttl =0;
		parent::__construct();
	}
	
	private function code62($x) {
		$show = '';
		while($x > 0) {
		  $s = $x % 62;
		  if ($s > 35) {
			$s = chr($s+61);
		  } elseif ($s > 9 && $s <=35) {
			$s = chr($s + 55);
		  }
		  $show .= $s;
		  $x = floor($x/62);
		}
		return $show;
	}
	public function getSid($url) {
		   $url = crc32($url);
		   $result = sprintf("%u", $url);
		   return self::code62($result);
	}
	
	public function getShortUrl($url){
		$sid=self::getSid($url);
		if(DB::result_first("select COUNT(*) from %t where sid=%s",array($this->_table,$sid))){
			return getglobal('siteurl').'short.php?sid='.$sid;
		}
		$setarr=array('sid'=>$sid,
					  'url'=>$url,
					  );
		if(parent::insert($setarr)){
			return getglobal('siteurl').'short.php?sid='.$sid;
		}
		return '';
	}
	public function addview($sid){
		return DB::query("update %t set count=count+1 where sid=%s",array($this->_table,$sid));
	}
	public function delete_by_url($url){
        $sid=self::getSid($url);
		return parent::delete($sid);
	}
}
?>
