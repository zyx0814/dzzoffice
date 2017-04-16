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

class table_shou extends dzz_table
{
	public function __construct() {

		$this->_table = 'shou';
		$this->_pk    = 'sid';
		$this->_pre_cache_key = 'shou_';
		$this->_cache_ttl =0;
		parent::__construct();
	}
	public function getSid() {
		 $sid=random(10);
		 if(DB::result_first("select COUNT(*) from %t where sid=%s",array($this->_table,$sid))){
			return self::getSid(); 
		 }
		 if(parent::insert(array('sid'=>$sid,'dateline'=>TIMESTAMP,'guize'=>6))){
			 return $sid;
		 }
	}
	public function fetch_by_sid($sid){
		if($data=self::fetch($sid)){
			$data['flogo']=$data['logo']?'index.php?mod=io&op=thumbnail&width=100&height=100&path='.dzzencode('attach::'.$data['logo']):'shou/images/logo.png';
			$data['fendtime']=$data['endtime']?dgmdate($data['endtime'],'Y-m-d'):'';
			$data['token']=C::t('connect_pan')->fetch($data['token']);
			$data['qrcode']=self::getQRcodeBySid($sid);
			if(REWRITE===true){
				$data['url']=getglobal('siteurl').'/'.$sid;
			}else{
				$data['url']=getglobal('siteurl').'shou.php?t='.$sid;
			}
			
			return $data;
		}else{
			return false;
		}
	}
	public function update_by_sid($sid,$arr){
		$data=parent::fetch($sid);
		$oaids=$data['aids']?explode(',',$data['aids']):array();
		$aids=array();
		if($arr['desc']){
			$aids=self::getAidsByMessage($arr['desc']);
			$arr['aids']=implode(',',$aids);
		}
		if(!$data['dateline']) $arr['dateline']=TIMESTAMP;
		if($ret=parent::update($sid,$arr)){
			$inserts=array_diff($aids,$oaids);
			$dels=array_diff($oaids,$aids);
			if($inserts) C::t('attachment')->addcopy_by_aid($inserts);
			if($dels) C::t('attachment')->addcopy_by_aid($dels,-1);
		}
		return $ret;
	}
	
	public function increase($sid, $fieldarr) {
		$sql = array();
		$num = 0;
		$allowkey = array('views', 'files', 'lastpost');
		foreach($fieldarr as $key => $value) {
			if(in_array($key, $allowkey)) {
				if(is_array($value)) {
					$sql[] = DB::field($key, $value[0]);
				} else {
					$value = dintval($value);
					$sql[] = "`$key`=`$key`+'$value'";
				}
			} else {
				unset($fieldarr[$key]);
			}
		}
		if(!empty($sql)){
			$cmd = "UPDATE ";
			$num = DB::query($cmd.DB::table($this->_table)." SET ".implode(',', $sql)." WHERE sid='{$sid}'", 'UNBUFFERED');
			$this->increase_cache($sid, $fieldarr);
		}
		return $num;
	}
	public function delete_by_sid($sids){	
	    $sids=(array)$sids;
		foreach(DB::fetch_all("select aids,logo from %t where sid IN(%n)",array($this->_table,$sids)) as $value){
			if($value['aids']){
				if($aids) C::t('attachment')->addcopy_by_aid(explode(',',$value['aids']));
			}
			if($value['logo']) C::t('attachment')->addcopy_by_aid($value['logo']);
		}
		return parent::delete($sids);
	}
	public function getAidsByMessage($message){
		$aids=array();
		if(preg_match_all("/path=\"attach::(\d+)\"/i",$message,$matches)){
			$aids=$matches[1];
		}
		if(preg_match_all("/path=\"".rawurlencode('attach::')."(\d+)\"/i",$message,$matches1)){
			$aids=array_merge($aids,$matches1[1]);
		}
		return array_unique($aids);
	}
	public function getQRcodeBySid($sid){
		$target='./qrcode/'.$sid[0].$sid[1].'/'.$sid.'.png';
		$targetpath = dirname(getglobal('setting/attachdir').$target);
		dmkdir($targetpath);
		if(@getimagesize(getglobal('setting/attachdir').$target)){
			return getglobal('setting/attachurl').$target;
		}else{//生成二维码
			if(REWRITE===true){
				$url=getglobal('siteurl').'/'.$sid;
			}else{
				$url=getglobal('siteurl').'shou.php?t='.$sid;
			}
			QRcode::png($url,getglobal('setting/attachdir').$target,'M',5,2);
			return getglobal('setting/attachurl').$target;
		}
	}
	
}
?>
