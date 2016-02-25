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

class table_icon extends dzz_table
{
	public function __construct() {

		$this->_table = 'icon';
		$this->_pk    = 'did';
		$this->_pre_cache_key = 'icon_';
		$this->_cache_ttl = 60*60;
		parent::__construct();
	}
	public function delete_by_did($did){
		global $_G;
		$icon=self::fetch($did);
		@unlink($_G['setting']['attachdir'].$icon['pic']);
		return self::delete($did);
	}
	public function update_copys_by_did($did,$ceof=1){
		global $_G;
		if($icon=self::fetch($did)){
			if($icon['check']<2 && ($icon['copys']+$ceof)<1 && $icon['check']<1){
				@unlink($_G['setting']['attachdir'].$icon['pic']);
				C::t('icon')->delete($did);
			}else{
				C::t('icon')->update($did,array('copys'=>$icon['copys']+$ceof));
			}
		}
	}
	public function fetch_by_link($link){//根据连接判断icon
		
		$data = array();
		$parse_url=parse_url($link);
		$host=$parse_url['host'];
		$host=preg_replace("/^www./",'',$host);//strstr('.',$host);
		if($_SERVER['HTTP_HOST']==$host || $_SERVER['HTTP_HOST']=='www.'.$host) $host='localhost';
		foreach(DB::fetch_all("select * from %t where domain=%s order by disp DESC,dateline DESC",array($this->_table,$host)) as $value){
			if($value['reg']){
				if(preg_match("/^\/.+?\/\w+$/i",$value['reg']) && preg_match($value['reg'],$link)){
					return $value;
				}elseif(strpos($link,$value['reg'])!==false){
					return $value;
				}
			}else{
				$data[]=$value;
			}
		}
		return $data?$data[0]:array();
	}
	
	
}

?>
