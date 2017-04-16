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

class table_share extends dzz_table
{
	public function __construct() {

		$this->_table = 'share';
		$this->_pk    = 'sid';
		$this->_pre_cache_key = 'share_';
		//$this->_cache_ttl =0;
		parent::__construct();
	}
	public function delete($sids){
		if($ret=parent::delete($sids)){
			$sids=(array)$sids;
			foreach($sids as $sid){
				$target='./qrcode/'.$sid[0].'/'.$sid.'.png';
				@unlink(getglobal('setting/attachdir').$target);
			}
		}
		return $ret;
	}
	
	public function insert_by_sid($arr){
		$arr['uid']=getglobal('uid');
		$arr['username']=getglobal('username');
		
		if(empty($arr['path'])) return false;
		$sid=self::getSid($arr['path'].'&uid='.getglobal('uid'));
		if($data=DB::fetch_first("select * from %t where sid=%s",array($this->_table,$sid))){
			if($data['status']>=-2){
				 if($arr['endtime'] && $arr['endtime']<TIMESTAMP) $arr['status']=-1;
				 elseif($arr['times'] && $arr['times']<=$data['count']) $arr['status']=-2;
				 else $arr['status']=0;
			}
			if(!isset($arr['status'])) $arr['status']=$data['status'];
			parent::update($sid,$arr);
			if(!is_file(getglobal('setting/attachurl').'qrcode/'.$sid[0].'/'.$sid.'.png')) self::getQRcodeBySid($sid);
		    return array('sid'=>$sid,'shareurl'=>getglobal('siteurl').'s.php?sid='.$sid,'qrcode'=>getglobal('setting/attachurl').'qrcode/'.$sid[0].'/'.$sid.'.png','status'=>$arr['status']);
		}else{
			$arr['status']=0;
			$arr['sid']=$sid;
			$arr['dateline']=TIMESTAMP;
			if(parent::insert($arr,1,1)){
				self::getQRcodeBySid($sid);
				return array('sid'=>$sid,'shareurl'=>getglobal('siteurl').'s.php?sid='.$sid,'qrcode'=>getglobal('setting/attachurl').'qrcode/'.$sid[0].'/'.$sid.'.png','status'=>$arr['status']);
			}
		}
		return false;
	}
	public function fetch_by_path($path){
		$sid=self::getSid($path);
		return parent::fetch($sid);
	}
	
	public function addview($sid){
		return DB::query("update %t set count=count+1 where sid=%s",array($this->_table,$sid));
	}
	public function getQRcodeBySid($sid){
		$target='./qrcode/'.$sid[0].'/'.$sid.'.png';
		$targetpath = dirname(getglobal('setting/attachdir').$target);
		dmkdir($targetpath);
		if(@getimagesize(getglobal('setting/attachdir').$target)){
			return getglobal('setting/attachurl').$target;
		}else{//生成二维码
			QRcode::png(getglobal('siteurl').'s.php?sid='.$sid,getglobal('setting/attachdir').$target,'M',4,2);
			return getglobal('setting/attachurl').$target;
		}
	}
	public function getSid($url) {
		   $url = crc32($url);
		   $result = sprintf("%u", $url);
		   return self::code62($result);
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
	
}
?>
