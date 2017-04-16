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

class table_user_qqconnect extends dzz_table
{
	public function __construct() {
		$this->_table = 'user_qqconnect';
		$this->_pk    = 'openid';
		parent::__construct();
	}
	public function fetch_by_openid($openid){
		return DB::fetch_first("select qq.openid,u.* from %t qq LEFT JOIN %t u ON qq.uid=u.uid where qq.openid=%d",array($this->_table,'user',$openid));
	}
	public function insert_by_openid($openid,$uid,$uinfo,$unbind=0){
		if(!$openid) return false;
		if(!$user=C::t('user')->fetch($uid)) return false;
		if(!DB::insert($this->_table,array('openid'=>$openid,'uid'=>$uid,'unbind'=>$unbind,'dateline'=>TIMESTAMP),1,true)){
			return false;
		}
		//使用qq头像
		/*if($uinfo['figureurl_2'] && !$user['avatarstatus']){
			avatar_by_image($uinfo['figureurl_2'],$uid);
		}*/
	}
}
?>
