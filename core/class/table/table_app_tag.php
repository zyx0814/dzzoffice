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

class table_app_tag extends dzz_table
{
	public function __construct() {

		$this->_table = 'app_tag';
		$this->_pk    = 'tagid';

		parent::__construct();
	}

	
	public function addtags($tags,$appid) {
		if(!$tags){
			C::t('app_relative')->delete_by_appid($appid);
			return;
		}
		if(!is_array($tags)){
			if(strpos($tags,"','")!==false){
				$tagnames=explode("','",trim($tags,"'"));
			}elseif(strpos($tags,',')!==false){
				$tagnames=explode(',',trim($tags));
			}else{
				$tagnames=array(trim($tags,"'"));
			}
		}else{
			$tagnames=$tags;
		}
		

		$tagarr=DB::fetch_all('SELECT tagid,tagname FROM '.DB::table($this->_table)."  WHERE  tagname IN( ".dimplode($tagnames).")");
		$have_tagnames=array();
		$have_tagids=array();

		foreach($tagarr as $tagid => $value){
			$have_tagnames[]=$value['tagname'];
			$have_tagids[]=$value['tagid'];
		}
		//已经存在的增加hot +1;
		DB::query("UPDATE ".DB::table($this->_table)." SET hot=hot+1 WHERE tagid IN(%n)",array($have_tagids));
		$insert_names=array_diff($tagnames,$have_tagnames);
		foreach($insert_names as $name){
			$have_tagids[]=self::insert(array('tagname'=>$name, 'dateline'=>TIMESTAMP, 'hot'=>1),1);
		}
		//插入关系表
		C::t('app_relative')->update_by_appid($appid,$have_tagids);
	}
	public function delete_by_tagid($tagids){
		DB::query("UPDATE ".DB::table($this->_table)." SET hot=hot-1 WHERE tagid IN(".dimplode($tagids).")");
		DB::delete($this->_table, array('hot'=>0));
	}
	public function deletetags($tags) {
		if(!$tags) return;
		if(!is_array($tags)){
			if(strpos($tags,"','")!==false){
				$tagnames=explode("','",trim($tags,"'"));
			}elseif(strpos($tags,',')!==false){
				$tagnames=explode(',',trim($tags));
			}else{
				$tagnames=array($tags);
			}
		}else{
			$tagnames=$tags;
		}
		$tagarr=DB::fetch_all('SELECT tagid,tagname,hot FROM '.DB::table($this->_table)."  WHERE tagname IN (".dimplode($tagnames).")");
		$have_tagids=array();
		$delete_tagids=array();
		foreach($tagarr as $tagid => $value){
			if($value['hot']>1)	$have_tagids[]=$value['tagname'];
			elseif($value['hot']<1){
				$delete_tagids[]=$value['tagid'];
			}
		}
		//已经存在的且hot>1的-1;
		DB::query("UPDATE ".DB::table($this->_table)." SET hot=hot-1 WHERE tagid IN(".dimplode($have_tagids).")");
		//已经存在的且hot<=1的删除;
		DB::query("DELETE FROM ".DB::table($this->_table)."  WHERE tagid IN(".dimplode($delete_tagids).")");
		
	}
	
}

?>
