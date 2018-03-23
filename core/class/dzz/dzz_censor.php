<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
class dzz_censor {
	var $censor_words = array();
	var $censor_replace='*';
	var $highlight;

	public function __construct() {
		global $_G;
		if(empty($_G['cache']['censor'])) loadcache('censor');
		$this->censor_words = !empty($_G['cache']['censor']['words']) ? explode(',',$_G['cache']['censor']['words']) : array();
		$this->censor_replace = !empty($_G['cache']['censor']['replace']) ? $_G['cache']['censor']['replace'] : '*';
	}

	public static function & instance() {
		static $instance;
		if(!$instance) {
			$instance = new self();
		}
		return $instance;
	}
	public function replace($message){
		if($badwords = array_combine($this->censor_words,array_fill(0,count($this->censor_words),$this->censor_replace))){
			return strtr($message,$badwords);
		}else{
			return $message;
		}
	}
	
}
?>