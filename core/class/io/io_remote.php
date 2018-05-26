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

class io_remote
{
	public function getBzByRemoteid($remoteid){ //通过remoteid获取bz,默认返回dzz
		return C::t('local_storage')->getBzByRemoteid($remoteid);
	}
	public function getRemoteid($attach){
		if($remoteid=C::t('local_router')->getRemoteId($attach)){
			return $remoteid;
		}
		if($remoteid=C::t('local_storage')->getRemoteId()) return $remoteid;
		return 0; //默认本地磁盘
	}
	public function DeleteFromSpace($attach){
		global $_G;
		$bz=self::getBzByRemoteid($attach['remote']);
		if($bz=='dzz'){
			@unlink($_G['setting']['attachdir'].$attach['attachment']);
		}else{
			$path=$bz.'/'.$attach['attachment'];
			IO::Delete($path);
		}
		//更新存储位置统计
		C::t('local_storage')->update_usesize_by_remoteid($attach['remote'],-$attach['filesize']);
		return true;
	}
	public function MoveToSpace($attach,$remoteid=0){ //注意：判断时使用===false;
	   global $_G;
		if(!$remoteid) $remoteid=self::getRemoteid($attach); //未指定时根据路由获取；
		$bz=self::getBzByRemoteid($remoteid);
		$obz=self::getBzByRemoteid($attach['remote']);
		if($bz==$obz) return false; //同一区域不需要移动
		if($bz=='dzz'){
			$path='attach::'.$attach['aid'];
		}else{
			$path=$bz.'/'.$attach['attachment'];
		}
		if($re=IO::MoveToSpace($path,$attach)){
			if($re['error']){
				return $re;
			}else{
				return $remoteid;
			}
		}else{ 
			return false;
		}
	}
	//迁移文件
	public function Migrate($attach,$remoteid){
		global $_G;
		if(is_numeric($re=self::MoveToSpace($attach,$remoteid))){
			$remoteid=$re;
			if(C::t('attachment')->update($attach['aid'],array('remote'=>$re))){
				//删除原文件
				$obz=io_remote::getBzByRemoteid($attach['remote']);
				if($obz=='dzz'){
					@unlink($_G['setting']['attachdir'].$attach['attachment']);
				}else{
					$opath=$obz.'/'.$attach['attachment'];
					IO::Delete($opath,true);
				}
			}
			C::t('local_storage')->update_usesize_by_remoteid($remoteid,$attach['filesize']);
			C::t('local_storage')->update_usesize_by_remoteid($attach['remote'],-$attach['filesize']);
			$attach['remote']=$remoteid;
			return $attach;
		}else{
			if($re['error']){
				$attach['error']=$re['error'];
				return $attach;
			}else{
				return false;
			}
		}
	}
}
?>
