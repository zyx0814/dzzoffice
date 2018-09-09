<?php
/*
 * 计划任务脚本
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
 
//定时检查未成功迁移的文件，迁移到默认的云端位置；
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$limit=10;//考虑到计划任务占用的系统资源，一次最大迁移100个;
$delay=TIMESTAMP-60*60*1;//仅迁移1天以前的附件；
$remoteid=C::t('local_storage')->getRemoteId();
if($remoteid>1){
	foreach(DB::fetch_all("select * from %t where dateline<%d and remote<2 and filesize>0 ORDER BY dateline DESC limit $limit",array('attachment',$delay,$limit)) as $value){
		io_remote::Migrate($value,$remoteid);
	}
}