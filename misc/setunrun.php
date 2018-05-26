<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
ignore_user_abort(true);
@set_time_limit(0);
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$unrunexts=$_G['setting']['unRunExts'];
//获取所有需要设置为禁止运行的项目

//设置所有本地的需要修改的项目
foreach(DB::fetch_all("select attachment,filetype,aid,unrun,remote from %t where filetype!='' and filetype IN(%n)",array('attachment',$unrunexts)) as $value){
	if($value['unrun']<1){
		if($value['remote']<2){
			$earr=explode('.',$value['attachment']);
			foreach($earr as $key=> $ext){
				if(in_array(strtolower($ext),array($value['filetype'],'dzz'))) unset($earr[$key]);
			}
			$eattachment=implode('.',$earr);
			$tattachment=implode('.',$earr).'.dzz';
			
			if(@is_file(getglobal('setting/attachdir').'./'.$value['attachment']) && @rename(getglobal('setting/attachdir').'./'.$value['attachment'],getglobal('setting/attachdir').'./'.$tattachment)){
				 C::t('attachment')->update($value['aid'],array('unrun'=>1,'attachment'=>$tattachment));
			}
			
		}else{
			C::t('attachment')->update($value['aid'],array('unrun'=>1));
		}
	}
}
foreach(DB::fetch_all("select attachment,filetype,aid,unrun,remote from %t where filetype!='' and filetype NOT IN(%n) and unrun='1'",array('attachment',$unrunexts)) as $value){
		if($value['remote']<2){
			
			$earr=explode('.',$value['attachment']);
			foreach($earr as $key=> $ext){
				if(in_array(strtolower($ext),array($value['filetype'],'dzz'))) unset($earr[$key]);
			}
			$tattachment=implode('.',$earr).'.'.$value['filetype'];
			
			if(@is_file(getglobal('setting/attachdir').'./'.$value['attachment']) && @rename(getglobal('setting/attachdir').'./'.$value['attachment'],getglobal('setting/attachdir').'./'.$tattachment)){
				 C::t('attachment')->update($value['aid'],array('unrun'=>0,'attachment'=>$tattachment));
			}
		}else{
			C::t('attachment')->update($value['aid'],array('unrun'=>0));
		}
}
exit('success');
?>
