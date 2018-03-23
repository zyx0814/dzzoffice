<?php
//error_reporting(E_ALL);
if(!defined('IN_DZZ')) {
	exit('Access Denied');
} 
define('NOROBOT', TRUE);
$returntype =  isset($_GET['returnType']) ?  $_GET['returnType']: 'json';//返回值方式
$type=$_GET['type'];
if(!in_array($type, array('list'))) {
	$type='list';
} 

$checkLanguage = $_G['language']; 
if(file_exists (DZZ_ROOT.'./admin/language/'.$checkLanguage.'/'.'lang.php')){							
	include DZZ_ROOT.'./admin/language/'.$checkLanguage.'/'.'lang.php';	
	$_G['lang']['template']=array_merge($_G['lang']['template'],$lang); 
}
 
if($type=="list"){
	//Hook::listen('adminlogin'); 
	!isset($_GET['page']) && $_GET['page']=1;
	$page=max(1,intval($_GET['page']));
	$lpp = empty($_GET['lpp']) ? 15 : $_GET['lpp'];
	$checklpp = array();
	$checklpp[$lpp] = 'selected="selected"';
	$extrainput = '';
	$systemlog_setting = unserialize($_G["setting"]["systemlog_setting"]); 
	$operationarr = array_keys($systemlog_setting);  
	$operation = in_array($_GET['operation'], $operationarr) ? $_GET['operation'] : "cplog"; 
	$navtitle=$systemlog_setting[$operation]["title"].' - '.lang('appname');//lang('nav_logs_'.$operation).' - '.lang('admin_navtitle');
	
	$logdir = DZZ_ROOT.'./data/log/';
	$logfiles = get_log_files($logdir, $operation);
	 
	if($logfiles) $logfiles=array_reverse($logfiles);
	//error_reporting(E_ALL);
	$firstlogs = file( $logdir.$logfiles[0] ) ; 
	$firstlogsnum = count($firstlogs);
	$countlogfile=count($logfiles);
	$count = ($countlogfile-1)*4000+$firstlogsnum;
	$multipage = multi($count, $lpp, $page, MOD_URL."&type=list&operation=$operation&lpp=$lpp",'pull-right' ); 
	 
	$logs = array();
	$jishu=4000;//每个日志文件最多行数
	$start = ($page - 1) * $lpp;
	$lastlog=$last_secondlog="";
	
	$newdata=array();
	foreach($logfiles as $k=>$v){
		$nowfilemaxnum=($jishu*($k+1))-($jishu-$firstlogsnum);
		$startnum=($nowfilemaxnum-$jishu)<=0?0:($nowfilemaxnum-$jishu+1); 
		$newdata[]=array("file"=>$v,"start"=>$startnum,"end"=>$nowfilemaxnum); 
	}
	//print_R($newdata);
	//查询当前分页数据位于哪个日志文件
	$lastlog=$last_secondlog="";
	foreach($newdata as $k=>$v){
		if( $start<=$v["end"]){
			$lastlog=$v;
			if( ($start+$lpp)<$v["end"]){
				 
			}else{
				if( isset($newdata[$k+1])){
					$last_secondlog=$newdata[$k+1];
				}
			}
			break;
		}
	}
	 
	$j=0; 
	for($i=$lastlog["start"];$i<$lastlog["end"];$i++){
		if(  $start<=($lastlog["start"]+$j) ){ 
			break;
		}
		$j++;
	}
	//获取数据开始
	$logs = file( $logdir.$lastlog["file"] );
	$logs = array_reverse($logs);
	if( $lastlog["file"]!=$logfiles[0] ){
		$j++;
	}
	$logs = array_slice($logs, $j, $lpp);
	$onecountget = count($logs);
	 
	$jj=0;
	if( $last_secondlog ){
		for($i=$last_secondlog["start"];$i<$last_secondlog["end"];$i++){
			if( ($jj)>= ($lpp-$onecountget)  ){//$last_secondlog["start"] ){ 
				break;
			}
			$jj++;
		} 
	}
	 
	if($last_secondlog){
		$logs2 = file( $logdir.$last_secondlog["file"] );
		$logs2 = array_reverse($logs2);
		$end=$lpp-count($logs); 
		$logs2 = array_slice( $logs2, 0, $jj);
		$logs=array_merge($logs,$logs2);
	} 
	//获取数据结束
	
	$usergroup = array(); 
	foreach(C::t('usergroup')->range() as $group) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
	  
	$list=array();
	foreach($logs as $k => $logrow) {
		$log = explode("\t", $logrow); 
		if(empty($log[1])) {
			continue;
		}
		 
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = $log[2];
		$log[2] = ($log[2] != $_G['member']['username'] ? "<b>$log[2]</b>" : $log[2]);
		$log[3] = $usergroup[$log[3]];
		
		$list[$k]=$log;
	}
	
	include template('list');
}


function getactionarray() {
	$isfounder = true;
	unset($topmenu['index'], $menu['index']);
	$actioncat = $actionarray = array();
	$actioncat[] = 'setting';
	$actioncat = array_merge($actioncat, array_keys($topmenu));
	foreach($menu as $tkey => $items) {
		foreach($items as $item) {
			$actionarray[$tkey][] = $item;
		}
	}
	return array('actions' => $actionarray, 'cats' => $actioncat);
}
function get_log_files($logdir = '', $action = 'action') {
	$dir = opendir($logdir);
	$files = array();
	while($entry = readdir($dir)) {
		$files[] = $entry;
	}
	closedir($dir);

	if($files) {
		sort($files);
		$logfile = $action;
		$logfiles = array();
		$ym = '';
		foreach($files as $file) {
			if(strpos($file, $logfile) !== FALSE) {
				if(substr($file, 0, 6) != $ym) {
					$ym = substr($file, 0, 6);
				}
				$logfiles[$ym][] = $file;
			}
		}
		if($logfiles) {
			$lfs = array();
			foreach($logfiles as $ym => $lf) {
				$lastlogfile = $lf[0];
				unset($lf[0]);
				$lf[] = $lastlogfile;
				$lfs = array_merge($lfs, $lf);
			}
			return $lfs;
		}
		return array();
	}
	return array();
}