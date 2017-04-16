<?php
/*
 * @copyright   Leyun Internet Technology(Shanghai) Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
include_once('../core/class/class_core.php');
include_once('../core/core_version.php');
include_once('../dzz/function/dzz_core.php');

@set_time_limit(0);

$cachelist = array();
$dzz = C::app();

$dzz->cachelist = $cachelist;
$dzz->init_cron = false;
$dzz->init_setting = true;
$dzz->init_user = false;
$dzz->init_session = false;
$dzz->init_misc = false;
$dzz->init();
$config = array(
	'dbcharset' => $_G['config']['db']['1']['dbcharset'],
	'charset' => $_G['config']['output']['charset'],
	'tablepre' => $_G['config']['db']['1']['tablepre']
);
$theurl = 'update.php';

$_G['siteurl'] = preg_replace('/\/install\/$/i', '/', $_G['siteurl']);

if($_GET['from']) {
	if(md5($_GET['from'].$_G['config']['security']['authkey']) != $_GET['frommd5']) {
		$refererarr = parse_url(dreferer());
		list($dbreturnurl, $dbreturnurlmd5) = explode("\t", authcode($_GET['from']));
		if(md5($dbreturnurl) == $dbreturnurlmd5) {
			$dbreturnurlarr = parse_url($dbreturnurl);

		} else {
			$dbreturnurlarr = parse_url($_GET['from']);
		}
		parse_str($dbreturnurlarr['query'], $dbreturnurlparamarr);
		$operation = $dbreturnurlparamarr['operation'];
		$version = $dbreturnurlparamarr['version'];
		$release = $dbreturnurlparamarr['release'];
		if(!$operation || !$version || !$release) {
			show_msg('请求的参数不正确');
		}
		$time = $_G['timestamp'];
		dheader('Location: '.$_G['siteurl'].basename($refererarr['path']).'?action=upgrade&operation='.$operation.'&version='.$version.'&release='.$release.'&ungetfrom='.$time.'&ungetfrommd5='.md5($time.$_G['config']['security']['authkey']));
	}
}

$lockfile = DZZ_ROOT.'./data/update.lock';
if(file_exists($lockfile) && !$_GET['from']) {
	show_msg('请您先登录服务器ftp，手工删除 ./data/update.lock 文件，再次运行本文件进行升级。');
}

$sqlfile = DZZ_ROOT.'./install/data/install.sql';

if(!file_exists($sqlfile)) {
	show_msg('SQL文件 '.$sqlfile.' 不存在');
}

if($_POST['delsubmit']) {
	if(!empty($_POST['deltables'])) {
		foreach ($_POST['deltables'] as $tname => $value) {
			DB::query("DROP TABLE `".DB::table($tname)."`");
		}
	}
	if(!empty($_POST['delcols'])) {
		foreach ($_POST['delcols'] as $tname => $cols) {
			foreach ($cols as $col => $indexs) {
				if($col == 'PRIMARY') {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP PRIMARY KEY", 'SILENT');
				} elseif($col == 'KEY' || $col == 'UNIQUE') {
					foreach ($indexs as $index => $value) {
						DB::query("ALTER TABLE ".DB::table($tname)." DROP INDEX `$index`", 'SILENT');
					}
				} else {
					DB::query("ALTER TABLE ".DB::table($tname)." DROP `$col`");
				}
			}
		}
	}
	show_msg('删除表和字段操作完成了', $theurl.'?step=cache');
}

function waitingdb($curstep, $sqlarray) {
	global $theurl;
	foreach($sqlarray as $key => $sql) {
		$sqlurl .= '&sql[]='.md5($sql);
		$sendsql .= '<img width="1" height="1" src="'.$theurl.'?step='.$curstep.'&waitingdb=1&sqlid='.$key.'">';
	}
	show_msg("优化数据表", $theurl.'?step=waitingdb&nextstep='.$curstep.$sqlurl.'&sendsql='.base64_encode($sendsql), 5000, 1);
}
if(empty($_GET['step'])) $_GET['step'] = 'start';

if($_GET['step'] == 'start') {
	if(!C::t('setting')->fetch('bbclosed')) {
		C::t('setting')->update('bbclosed', 1);
		require_once libfile('function/cache');
		updatecache('setting');
		show_msg('您的站点未关闭，正在关闭，请稍后...', $theurl.'?step=start', 5000);
	}
		show_msg('说明：<br>本升级程序会参照最新的SQL文件，对数据库进行同步升级。<br>
			请确保当前目录下 ./data/install.sql 文件为最新版本。<br><br>
			<a href="'.$theurl.'?step=prepare'.($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '').'">准备完毕，升级开始</a>');
	
} elseif ($_GET['step'] == 'waitingdb') {
	$query = DB::fetch_all("SHOW FULL PROCESSLIST");
	foreach($query as $row) {
		if(in_array(md5($row['Info']), $_GET['sql'])) {
			$list .= '[时长]:'.$row['Time'].'秒 [状态]:<b>'.$row['State'].'</b>[信息]:'.$row['Info'].'<br><br>';
		}
	}
	if(empty($list) && empty($_GET['sendsql'])) {
		$msg = '准备进入下一步操作，请稍后...';
		$notice = '';
		$url = "?step=$_GET[nextstep]";
		$time = 5;
	} else {
		$msg = '正在升级数据，请稍后...';
		$notice = '<br><br><b>以下是正在执行的数据库升级语句:</b><br>'.$list.base64_decode($_GET['sendsql']);
		$sqlurl = implode('&sql[]=', $_GET['sql']);
		$url = "?step=waitingdb&nextstep=$_GET[nextstep]&sql[]=".$sqlurl;
		$time = 20;
	}
	show_msg($msg, $theurl.$url, $time*1000, 0, $notice);
} elseif ($_GET['step'] == 'prepare') {
	$repeat=array();
	//检查数据库表 app_market 中有无appurl重复的情况；
	foreach(DB::fetch_all("select appid,appurl from ".DB::table('app_market')." where 1") as $value){
		if(in_array($value['appurl'],$repeat)){
			C::t('app_market')->update($value['appid'],array('appurl'=>$value['appurl'].'&appid='.$value['appid']));
		}
		$repeat[]=$value['appurl'];
	}
	
	show_msg('准备完毕，进入下一步数据库结构升级', $theurl.'?step=sql');
} elseif ($_GET['step'] == 'sql') {
	$sql = implode('', file($sqlfile));
	preg_match_all("/CREATE\s+TABLE.+?dzz\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		show_msg('SQL文件内容为空，请确认');
	}

	$i = empty($_GET['i'])?0:intval($_GET['i']);
	$count_i = count($newtables);
	if($i>=$count_i) {
		show_msg('数据库结构升级完毕，进入下一步数据升级操作', $theurl.'?step=data');
	}
	$newtable = $newtables[$i];

	$specid = intval($_GET['specid']);
	

	$newcols = getcolumn($newsqls[$i]);

	if(!$query = DB::query("SHOW CREATE TABLE ".DB::table($newtable), 'SILENT')) {
		preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $newsqls[$i], $maths);

		$maths[3] = strtoupper($maths[3]);
		if($maths[3] == 'MEMORY' || $maths[3] == 'HEAP') {
			$type = " ENGINE=MEMORY".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" );
		} else {
			$type =" ENGINE=MYISAM".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" );
		}
		$usql = $maths[1].$type;

		$usql = str_replace("CREATE TABLE IF NOT EXISTS dzz_", 'CREATE TABLE IF NOT EXISTS '.$config['tablepre'], $usql);
		$usql = str_replace("CREATE TABLE dzz_", 'CREATE TABLE '.$config['tablepre'], $usql);

		if(!DB::query($usql, 'SILENT')) {
			show_msg('添加表 '.DB::table($newtable).' 出错,请手工执行以下SQL语句后,再重新运行本升级程序:<br><br>'.dhtmlspecialchars($usql));
		} else {
			$msg = '添加表 '.DB::table($newtable).' 完成';
		}
	} else {
		$value = DB::fetch($query);
		$oldcols = getcolumn($value['Create Table']);

		$updates = array();
		$allfileds =array_keys($newcols);
		foreach ($newcols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) {
						$usql = "RENAME TABLE ".DB::table($newtable)." TO ".DB::table($newtable.'_bak');
						if(!DB::query($usql, 'SILENT')) {
							show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
						} else {
							$msg = '表改名 '.DB::table($newtable).' 完成！';
							show_msg($msg, $theurl.'?step=sql&i='.$_GET['i']);
						}
					}
					$updates[] = "ADD PRIMARY KEY $value";
				}
			} elseif ($key == 'KEY') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['KEY'][$subkey])) {
						if($subvalue != $oldcols['KEY'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					} else {
						$updates[] = "ADD INDEX `$subkey` $subvalue";
					}
				}
			} elseif ($key == 'UNIQUE') {
				foreach ($value as $subkey => $subvalue) {
					if(!empty($oldcols['UNIQUE'][$subkey])) {
						if($subvalue != $oldcols['UNIQUE'][$subkey]) {
							$updates[] = "DROP INDEX `$subkey`";
							$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
						}
					} else {
						$usql = "ALTER TABLE  ".DB::table($newtable)." DROP INDEX `$subkey`";
						DB::query($usql, 'SILENT');
						$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
					}
				}
			} else {
				if(!empty($oldcols[$key])) {
					if(strtolower($value) != strtolower($oldcols[$key])) {
						$updates[] = "CHANGE `$key` `$key` $value";
					}
				} else {
					$i = array_search($key, $allfileds);
					$fieldposition = $i > 0 ? 'AFTER `'.$allfileds[$i-1].'`' : 'FIRST';
					$updates[] = "ADD `$key` $value $fieldposition";
				}
			}
		}

		if(!empty($updates)) {
			$usql = "ALTER TABLE ".DB::table($newtable)." ".implode(', ', $updates);
			if(!DB::query($usql, 'SILENT')) {
				show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
			} else {
				$msg = '升级表 '.DB::table($newtable).' 完成！';
			}
		} else {
			$msg = '检查表 '.DB::table($newtable).' 完成，不需升级，跳过';
		}
	}

	if($specid) {
		$newtable = $spectable;
	}

	if(get_special_table_by_num($newtable, $specid+1)) {
		$next = $theurl . '?step=sql&i='.($_GET['i']).'&specid='.($specid + 1);
	} else {
		$next = $theurl.'?step=sql&i='.($_GET['i']+1);
	}
	show_msg("[ $i / $count_i ] ".$msg, $next);

} elseif ($_GET['step'] == 'data') {
	if(!$_GET['dp']){
		//修改config.php 设置语言
		$config_content=file_get_contents(DZZ_ROOT.'./core/config/config.php');
		$config_content=preg_replace("/_config\['output'\]\['language'\]\s*=\s*'.+?'\s*;/i",'_config[\'output\'][\'language\'] = \'zh-cn\';'."\r\n".'$_config[\'output\'][\'language_list\'][\'zh-cn\']=\'简体中文\';',$config_content);
		file_put_contents(DZZ_ROOT.'./core/config/config.php',$config_content);
		
		
		
		//设置安全后缀名
		if(!DB::result_first("select COUNT(*) from %t where skey='unRunExts'",array('setting'))) C::t('setting')->update('unRunExts',explode(',','exe,bat,sh,dll,php,php4,php5,php3,jsp,asp,aspx,vs,js'));
		//设置分块上传的大小
		if(!DB::result_first("select COUNT(*) from %t where skey='maxChunkSize'",array('setting'))) C::t('setting')->update('maxChunkSize',2000000);
		//设置版权
		if(!DB::result_first("select COUNT(*) from %t where skey='sitecopyright'",array('setting'))) C::t('setting')->update('sitecopyright','<img alt="dzzoffice" src="dzz/images/logo.png" width="263" height="82">
<div style="font-size: 16px;font-weight:bold;text-align:center;padding: 20px 0 10px 0;text-shadow: 1px 1px 1px #FFF;">'.$_G['setting']['sitename'].'</div>
<div style="font-size: 16px;font-weight:bold;text-align:center;padding: 0 0 25px 0;text-shadow:1px 1px 1px #fff">协同办公平台</div><div style="font-size: 12px;text-align:center;padding: 0 0 10px 0;text-shadow:1px 1px 1px #fff">@2012-2017 DzzOffice</div><div style="font-size: 12px;text-align:center;text-shadow:1px 1px 1px #fff">备案信息</div>');
		//添加ftp方式
		if(!DB::result_first("select COUNT(*) from %t where bz='ftp'",array('connect'))){
			C::t('connect')->insert(array('name'=>'FTP',
										  'type'=>'ftp',
										  'bz'=>'ftp',
										  'available'=>1,
										  'dname'=>'connect_ftp'),0,1);
		}
		//添加七牛云存储
		if(!DB::result_first("select COUNT(*) from %t where bz='qiniu'",array('connect'))){
			C::t('connect')->insert(array('name'=>'七牛云存储',
										  'type'=>'storage',
										  'bz'=>'qiniu',
										  'available'=>1,
										  'dname'=>'connect_storage'),0,1);
			//添加后缀名方式
			if($appid=DB::result_first("select appid from ".DB::table('app_market')." where appurl='{dzzscript}?mod=document&op=pdfviewer'")){
				C::t('app_open')->insert_by_exts($appid,'pdf,qiniu:doc,qiniu:docx,qiniu:ppt,qiniu:pptx,qiniu:xls,qiniu:xlsx');
				C::t('app_market')->update($appid,array('fileext'=>'pdf,qiniu:doc,qiniu:docx,qiniu:ppt,qiniu:pptx,qiniu:xls,qiniu:xlsx'));
			}
		
		}
		//添加OneDrive云存储
		if(!DB::result_first("select COUNT(*) from %t where bz='OneDrive'",array('connect'))){
			C::t('connect')->insert(array('name'=>'OneDrive',
										  'type'=>'pan',
										  'bz'=>'OneDrive',
										  'available'=>1,
										  'dname'=>'connect_onedrive'),0,1);
									  
		}
		
		//添加本地磁盘
		if(!DB::result_first("select COUNT(*) from %t where bz='disk'",array('connect'))){
			C::t('connect')->insert(array('name'=>'本地磁盘',
										  'type'=>'disk',
										  'bz'=>'disk',
										  'available'=>1,
										  'disp'=>'-1',
										  'dname'=>'connect_disk'),0,1);
			
									  
		}
		//添加后缀名方式
		if($appid=DB::result_first("select appid from ".DB::table('app_market')." where appurl='dzzjs:OpenAppWin(\'{icoid}\')'")){
			C::t('app_open')->insert_by_exts($appid,array('link','task','corpus','discuss'));
			C::t('app_market')->update($appid,array('fileext'=>'link,task,corpus,discuss'));
		}
		//插入icon
		$setarr=array();
		$setarr[]=array('domain'=>'localhost',
					  'reg'=>'/mod=corpus&op=list&cid=\d+&fid=\d+/i',
					  'ext'=>'corpus',
					  'pic'=>'icon/201405/30/localhost_g21939b3.png',
					  'check'=>1,
					  'copys'=>1,
					  'disp'=>100,
					  'dateline'=>TIMESTAMP
					  );
		$setarr[]=array('domain'=>'localhost',
					  'reg'=>'mod=corpus&op=list&cid=',
					  'ext'=>'corpus',
					  'pic'=>'icon/201405/30/localhost_kae23q2i.png',
					  'check'=>1,
					  'copys'=>1,
					  'disp'=>0,
					  'dateline'=>TIMESTAMP
					  );
	$setarr[]=array('domain'=>'localhost',
					  'reg'=>'/mod=taskboard&op=list&tbid=\d+&taskid=\d+/i',
					  'ext'=>'task',
					  'pic'=>'icon/201405/30/localhost_ovu7buw7.png',
					  'check'=>1,
					  'copys'=>1,
					  'disp'=>100,
					  'dateline'=>TIMESTAMP
					  );
	 $setarr[]=array('domain'=>'localhost',
					  'reg'=>'?mod=taskboard&op=list',
					  'ext'=>'task',
					  'pic'=>'icon/201405/29/localhost_qgiob2bi.png',
					  'check'=>1,
					  'copys'=>1,
					  'disp'=>0,
					  'dateline'=>TIMESTAMP
					  );
	 $setarr[]=array('domain'=>'localhost',
					  'reg'=>'mod=discuss&op=viewthread',
					  'ext'=>'discuss',
					  'pic'=>'icon/201405/30/localhost_g4449m8c.png',
					  'check'=>1,
					  'copys'=>1,
					  'disp'=>50,
					  'dateline'=>TIMESTAMP
					  );
	  $setarr[]=array('domain'=>'localhost',
					  'reg'=>'mod=discuss',
					  'ext'=>'discuss',
					  'pic'=>'icon/201405/30/localhost_kzf18fqm.png',
					  'check'=>1,
					  'copys'=>1,
					  'disp'=>0,
					  'dateline'=>TIMESTAMP
					  );
		foreach($setarr as $value){
			if(!DB::result_first("select COUNT(*) from ".DB::table('icon')." where domain=%s and reg=%s",array($value['domain'],$value['reg']))){
				C::t('icon')->insert($value);
			}
		}
		
		
		//添加图标管理应用
		if(DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{adminscript}?mod=icon'))){
		 C::t('app_market')->insert(array('appname'=>'图标管理',
		 								  'appico'=>'appimg/201406/03/141854wifuhjlmvi3whw63.png',
		 								  'appurl'=>'{adminscript}?mod=icon',
										  'dateline'=>TIMESTAMP,
										  'disp'=>26,
										  'vendor'=>'乐云网络',
										  'group'=>3,
										  'notdelete'=>1,
										  'position'=>1,
										  'available'=>1),0,1);
		}
		//添加文件管理应用
		if(DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{adminscript}?mod=filemanage'))){
		 C::t('app_market')->insert(array('appname'=>'文件管理',
		 								  'appico'=>'appico/201406/24/163715vxgl5p8nv4ix8lki.png',
		 								  'appurl'=>'{adminscript}?mod=filemanage',
										  'dateline'=>TIMESTAMP,
										  'disp'=>25,
										  'vendor'=>'乐云网络',
										  'group'=>3,
										  'notdelete'=>1,
										  'position'=>1,
										  'available'=>1),0,1);
		}
	    //添加应用市场应用
		if(DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','http://www.dzzoffice.com/market/index.htm'))){
		 C::t('app_market')->insert(array('appname'=>'Dzz应用市场',
		 								  'appico'=>'appico/201406/29/161332ptwvhztpiexibqhp.png',
		 								  'appurl'=>'http://www.dzzoffice.com/market/index.htm',
										  'dateline'=>TIMESTAMP,
										  'disp'=>28,
										  'vendor'=>'乐云网络',
										  'group'=>3,
										  'notdelete'=>1,
										  'position'=>1,
										  'available'=>1),0,1);
		}
		//添加用户管理应用
		if(DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{adminscript}?mod=member'))){
		 C::t('app_market')->insert(array('appname'=>'用户管理',
										  'appico'=>'appico/201412/09/210346b2um2d66mem7lmsd.png',
										  'appurl'=>'{adminscript}?mod=member',
										  'dateline'=>TIMESTAMP,
										  'disp'=>25,
										  'vendor'=>'乐云网络',
										  'group'=>3,
										  'notdelete'=>1,
										  'position'=>1,
										  'available'=>1),0,1);
		}
		//添加分享管理应用
		if(DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{adminscript}?mod=share'))){
		 C::t('app_market')->insert(array('appname'=>'分享管理',
										  'appico'=>'appico/201501/28/222419yltwlnlljaztu8kn.png',
										  'appurl'=>'{adminscript}?mod=share',
										  'dateline'=>TIMESTAMP,
										  'disp'=>28,
										  'vendor'=>'乐云网络',
										  'group'=>3,
										  'notdelete'=>1,
										  'position'=>1,
										  'available'=>1),0,1);
		}
		//添加我的分享应用
		if(DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{dzzscript}?mod=share'))){
		 C::t('app_market')->insert(array('appname'=>'我的分享',
										  'appico'=>'appico/201501/25/201401vk51kfeh2e41ujve.png',
										  'appurl'=>'{dzzscript}?mod=share',
										  'dateline'=>TIMESTAMP,
										  'disp'=>8,
										  'vendor'=>'乐云网络',
										  'group'=>1,
										  'notdelete'=>1,
										  'position'=>1,
										  'available'=>1),0,1);
		}
		//添加新计划任务
		if(DB::result_first("select COUNT(*) from %t where filename=%s",array('cron','cron_imgcache_cleanup_week.php'))){
		C::t('cron')->insert(array(
									'available' => 0,
									'type' => 'system',
									'name' => '每周清理缓存缩略图文件',
									'filename' => 'cron_imgcache_cleanup_week.php',
									'weekday' => 2,
									'day' => -1,
									'hour' => -1,
									'minute' => 0
									), 0,1);
		}
		//添加新计划任务
		if(DB::result_first("select COUNT(*) from %t where filename=%s",array('cron','cron_database_backup.php'))){
			C::t('cron')->insert(array(
										'available' => 0,
										'type' => 'system',
										'name' => '定时备份数据库',
										'filename' => 'cron_database_backup.php',
										'weekday' => 0,
										'day' => -1,
										'hour' => -1,
										'minute' => 0,
										'lastrun'=>1460946655
									    ), 0,1);
		}
		//设置消息中心
		if(!C::t('setting')->fetch('feed_at_depart_title')) C::t('setting')->update('feed_at_depart_title','部门');
		if(!C::t('setting')->fetch('feed_at_user_title')) C::t('setting')->update('feed_at_user_title','同事');
		if(!C::t('setting')->fetch('feed_at_range')) C::t('setting')->update('feed_at_range', 'a:3:{i:9;s:1:"1";i:2;s:1:"2";i:1;s:1:"3";}');
		
		//独立登录页设置
		if(!DB::result_first("select COUNT(*) from %t where skey='loginset'",array('setting')))	C::t('setting')->update('loginset','a:5:{s:9:"available";s:1:"0";s:5:"title";s:9:"DzzOffice";s:8:"subtitle";s:18:"协同办公平台";s:10:"background";s:0:"";s:6:"bcolor";s:17:"rgb(58, 110, 165)";}');
		
		//修改用户组的权限
		if(!DB::result_first("select perm from %t where groupid='9'",array('usergroup_field'))){
			$perm_all=perm_binPerm::getMyPower();
			$perm_read=perm_binPerm::getGroupPower('read');
			DB::query('update '.DB::table('usergroup_field')." SET perm='{$perm_all}' where groupid='1' or groupid='2' or groupid='3' or groupid='9'");
			DB::query('update '.DB::table('usergroup_field')." SET perm='{$perm_read}' where groupid='4' or groupid='7' or groupid='8'");
			DB::query('update '.DB::table('usergroup_field')." SET perm='1' where groupid='5' or groupid='6'");
		}
		//修改机构和部门文件夹的权限
		DB::query('update '.DB::table('folder')." SET perm='{$perm_read}' where gid>0 and flag='organization'");
		
		//修改共享文件夹权限；
		$perm_read_write2=perm_binPerm::getGroupPower('read-write2');
		DB::query('update '.DB::table('folder')." SET perm='{$perm_read}' where  perm=3 and flag='folder'");
		DB::query('update '.DB::table('folder')." SET perm='{$perm_read_write2}' where perm=5 and flag='folder'");
		
		//修改几个应用的地址
		
		//1.简易mp3播放器
		DB::query('update '.DB::table('app_market')." SET appurl='{dzzscript}?mod=sound' where appurl='dzz/sound/play.html?url={url}&autoplay=yes'");
		//2.flash播放器
		DB::query('update '.DB::table('app_market')." SET appurl='{dzzscript}?mod=player:swf&url={url}' where appurl='dzz/player/swf/index.html'");
		DB::query('update '.DB::table('app_market')." SET appurl='{dzzscript}?mod=player:swf&url={url}' where appurl='{dzzscript}?mod=player:swf'");
		
		//3.flowplayer(flash)
		DB::query('update '.DB::table('app_market')." SET appurl='{dzzscript}?mod=player:mp4:flowplayer' where appurl='dzz/player/mp4/flowplayer/flowplayer.php?src={url}'");
		
		//4.flowplayer(html5)
		DB::query('update '.DB::table('app_market')." SET appurl='{dzzscript}?mod=player:mp4:flowplayer5&ext={ext}' where appurl='dzz/player/mp4/flowplayer5/flowplayer.php?src={url}&ext={ext}'");
		//5.baidu播放器
		DB::query('update '.DB::table('app_market')." SET appurl='{dzzscript}?mod=player:t5player&path={path}' where appurl='{dzzscript}?mod=player:t5player&src={url}'");
		//设置@部门设置
		if(!DB::result_first("select COUNT(*) from %t where skey='at_range'",array('setting'))) C::t('setting')->update('at_range', 'a:3:{i:9;s:1:"1";i:2;s:1:"2";i:1;s:1:"3";}');
		//设置verhash
		if(!DB::result_first("select COUNT(*) from %t where skey='verhash'",array('setting'))) C::t('setting')->update('verhash', random(3));
		//设置系统计划任务
		$syscron=array('cron_clean_notification_month.php','cron_getAtoken_by_Rtoken_week.php','cron_cache_cleanup_week.php','cron_cache_imgcleanup_week.php','cron_clean_copys0_attachment_by_month.php');
		DB::update("cron",array('type'=>'system'),"filename IN (".dimplode($syscron).")"); 
		//设置资料隐私
		if(!DB::result_first("select COUNT(*) from %t where skey='privacy'",array('setting'))) C::t('setting')->update('privacy', 'a:1:{s:7:"profile";a:17:{s:9:"education";i:1;s:8:"realname";i:-1;s:7:"address";i:0;s:9:"telephone";i:0;s:15:"affectivestatus";i:0;s:10:"department";i:0;s:8:"birthday";i:0;s:13:"constellation";i:0;s:9:"bloodtype";i:0;s:6:"gender";i:0;s:6:"mobile";i:0;s:2:"qq";i:0;s:7:"zipcode";i:0;s:11:"nationality";i:0;s:14:"graduateschool";i:0;s:8:"interest";i:0;s:3:"bio";i:0;}}');
		//设置认证
		if(!DB::result_first("select COUNT(*) from %t where skey='verify'",array('setting'))) C::t('setting')->update('verify', 'a:8:{i:1;a:9:{s:4:"desc";s:0:"";s:9:"available";s:1:"1";s:8:"showicon";s:1:"1";s:5:"field";a:1:{s:8:"realname";s:8:"realname";}s:8:"readonly";i:1;s:5:"title";s:12:"实名认证";s:4:"icon";s:31:"common/verify/1/verify_icon.jpg";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:2;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";s:1:"0";s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:3;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";s:1:"0";s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:4;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:5;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:6;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:7;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}s:7:"enabled";b:1;}');
		show_msg("基本设置修改完成", "$theurl?step=data&dp=1");
	}elseif($_GET['dp']==1){//升级文档中的链接
		$i = empty($_GET['i'])?0:intval($_GET['i']);
		
		$count_i = DB::result_first("select COUNT(*) from %t where unrun>0",array('attachment'));
		if($i>=$count_i) {
			show_msg('附件升级完成，进入下一步数据升级操作', "$theurl?step=data&dp=2");
		}
		$msg='';
		if($value=DB::fetch_first("select * from %t where unrun>0  limit %d,1",array('attachment',$i))){
			$i++;
			$msg='附件转换完成';
			$next=$theurl.'?step=data&dp=1&i='.$i;
			if($value['remote']<2){
				$earr=explode('.',$value['attachment']);
				foreach($earr as $key=> $ext){
					if(in_array(strtolower($ext),array($value['filetype'],'dzz'))) unset($earr[$key]);
				}
				$tattachment=implode('.',$earr).'.dzz';
				if(is_file(getglobal('setting/attachdir').'./'.$value['attachment'])){
					$oattachment=$value['attachment'];
				}elseif(is_file(getglobal('setting/attachdir').'./'.$value['attachment'].'.dzz')){
					$oattachment=$value['attachment'].'.dzz';
				}
				
				if($oattachment && $tattachment!=$oattachment && @rename(getglobal('setting/attachdir').'./'.$oattachment,getglobal('setting/attachdir').'./'.$tattachment)){
					C::t('attachment')->update($value['aid'],array('unrun'=>1,'attachment'=>$tattachment));
				}
			}
			show_msg("[ $i / $count_i ] ".$msg, $next);
		}
	
	}elseif($_GET['dp']==2){ //转换原有资料表到新表user_profile1
		if(DB::result_first("select COUNT(*) from %t where 1",array('user_profile1'))){
			show_msg('用户资料升级完成，进入下一步数据升级操作', $theurl.'?step=data&dp=3');
		}
		$i = empty($_GET['i'])?0:intval($_GET['i']);
		
		$count_i = DB::result_first("select COUNT(*) from %t where 1 ",array('user_profile'));
		if($i>=$count_i) {
			show_msg('用户资料升级完成，进入下一步数据升级操作', $theurl.'?step=data&dp=3');
		}
		$fields=C::t('user_profile_setting')->fetch_all_fields_by_available(0);
		if($value=DB::fetch_first("select * from %t where 1 order by uid  limit $i,1",array('user_profile',$i))){
			foreach($value as $key=>$value1){
				if($key=='uid' || !$value1 || !in_array($key,$fields)) continue;
				$setarr=array('uid'=>$value['uid'],
							  'fieldid'=>$key,
							  'value'=>$value1
							  );
				
				C::t('user_profile1')->insert($setarr,0,1);
			}
		}
		$i++;
		$msg='资料转换完成';
		$next=$theurl.'?step=data&dp=2&i='.$i;
		show_msg("[ $i / $count_i ] ".$msg, $next);
	}elseif($_GET['dp']==3){ //转换机构和部门数据
	
		$i = empty($_GET['i'])?0:intval($_GET['i']);
		$count_i = DB::result_first("select COUNT(*) from %t where 1 ",array('organization'));
		if($i>=$count_i) {
			show_msg('部门数据升级完成，进入下一步操作', $theurl.'?step=data&dp=4');
		}
		if($orgid=DB::result_first("select orgid from %t where 1 order by orgid limit $i,1 ",array('organization',$i))){
			C::t('organization')->setPathkeyByOrgid($orgid,1);
		}
		$i++;
		$msg='部门数据转换完成';
		$next=$theurl.'?step=data&dp=3&i='.$i;
		show_msg("[ $i / $count_i ] ".$msg, $next);
	
	}elseif($_GET['dp']==4){ //修复gid
		$i = empty($_GET['i'])?0:intval($_GET['i']);
		
		$count_i = DB::result_first("select COUNT(*) from %t where gid>0 ",array('folder'));
		if($i>=$count_i) {
			show_msg('部门文件夹修复完成，进入下一步操作', $theurl.'?step=data&dp=5');
		}
		$arr=DB::fetch_first("select fid,pfid,gid,fname from %t where gid>0  order by fid limit $i,1",array('folder',$i));
		$gid=C::t('folder')->fetch_gid_by_fid($arr['fid']);
		
		if($gid!=$arr['gid']){
			C::t('folder')->update($arr['fid'],array('gid'=>$gid));
			DB::query("update %t set gid=%d where pfid=%d ",array('icos',$gid,$arr['fid']));
		}
		$i++;
		$msg='部门文件夹修复完成';
		$next=$theurl.'?step=data&dp=4&i='.$i;
		show_msg("[ $i / $count_i ] ".$msg, $next);
	}elseif($_GET['dp']==5){ //修复gid
		
		$sql="bz LIKE %s OR path LIKE %s";
		$param=array('source_shortcut','org_%' ,'fid_%');
		$cutids_del=array();
		foreach(DB::fetch_all("select cutid,data from %t where $sql",$param) as $value){
			$tdata=unserialize($value['data']);
			if($tdata['flag']=='organization' && $tdata['type']=='folder')	$cutids_del[]=$value['cutid'];
		};
		if($cutids_del){
			foreach(DB::fetch_all("select icoid from %t where type='shortcut' and oid IN(%n)",array('icos',$cutids_del)) as $value){
				C::t('icos')->delete_by_icoid($value['icoid'],true);
			}
		}
		show_msg('部门快捷方式修复，进入下一步操作', $theurl.'?step=delete');
	}
	show_msg("数据升级结束", "$theurl?step=delete");
}elseif ($_GET['step'] == 'delete') {
	$oldtables = array();
	$query = DB::query("SHOW TABLES LIKE '$config[tablepre]%'");
	while ($value = DB::fetch($query)) {
		$values = array_values($value);
		$oldtables[] = $values[0];
	}

	$sql = implode('', file($sqlfile));
	preg_match_all("/CREATE\s+TABLE.+?dzz\_(.+?)\s+\((.+?)\)\s*(ENGINE|TYPE)\s*\=/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	$deltables = array();
	$delcolumns = array();

	foreach ($oldtables as $tname) {
		$tname = substr($tname, strlen($config['tablepre']));
		if(in_array($tname, $newtables)) {
			$query = DB::query("SHOW CREATE TABLE ".DB::table($tname));
			$cvalue = DB::fetch($query);
			$oldcolumns = getcolumn($cvalue['Create Table']);
			$i = array_search($tname, $newtables);
			$newcolumns = getcolumn($newsqls[$i]);

			foreach ($oldcolumns as $colname => $colstruct) {
				if($colname == 'UNIQUE' || $colname == 'KEY') {
					foreach ($colstruct as $key_index => $key_value) {
						if(empty($newcolumns[$colname][$key_index])) {
							$delcolumns[$tname][$colname][$key_index] = $key_value;
						}
					}
				} else {
					if(empty($newcolumns[$colname])) {
						$delcolumns[$tname][] = $colname;
					}
				}
			}
		} else {
			
		}
	}

	show_header();
	echo '<form method="post" autocomplete="off" action="'.$theurl.'?step=delete'.($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '').'">';

	$deltablehtml = '';
	if($deltables) {
		$deltablehtml .= '<table>';
		foreach ($deltables as $tablename) {
			$deltablehtml .= "<tr><td><input type=\"checkbox\" name=\"deltables[$tablename]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td></tr>";
		}
		$deltablehtml .= '</table>';
		echo "<p>以下 <strong>数据表</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除</p>$deltablehtml";
	}

	$delcolumnhtml = '';
	if($delcolumns) {
		$delcolumnhtml .= '<table>';
		foreach ($delcolumns as $tablename => $cols) {
			foreach ($cols as $coltype => $col) {
				if (is_array($col)) {
					foreach ($col as $index => $indexvalue) {
						$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$coltype][$index]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>索引($coltype) $index $indexvalue</td></tr>";
					}
				} else {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>{$config['tablepre']}$tablename</td><td>字段 $col</td></tr>";
				}
			}
		}
		$delcolumnhtml .= '</table>';

		echo "<p>以下 <strong>字段</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除(建议删除)</p>$delcolumnhtml";
	}

	if(empty($deltables) && empty($delcolumns)) {
		echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"$theurl?step=cache".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">请点击进入下一步</a></p>";
	} else {
		echo "<p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段<br><a href=\"$theurl?step=cache".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">直接进入下一步</a></p>";
	}
	echo '</form>';

	show_footer();
	exit();


} elseif ($_GET['step'] == 'cache') {
	
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, ' ');
		fclose($fp);
	}
	//删除数据库恢复文件，防止一些安全问题；
	@unlink(DZZ_ROOT.'./data/restore.php');
	dir_clear(DZZ_ROOT.'./data/template');
	dir_clear(DZZ_ROOT.'./data/cache');
	savecache('setting', '');
	
	
	if($_GET['from']) {
		show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=syscache" style="display:none;" onload="parent.window.location.href=\''.$_GET['from'].'\'"></iframe><iframe src="../misc.php?mod=setunrun" style="display:none;"></iframe>');
	} else {
		show_msg('<span id="finalmsg">缓存更新中，请稍候 ...</span><iframe src="../misc.php?mod=syscache" style="display:none;" onload="document.getElementById(\'finalmsg\').innerHTML = \'恭喜，数据库结构升级完成！为了数据安全，请删除本文件。'.$opensoso.'\'"></iframe><iframe src="../misc.php?mod=setunrun" style="display:none;"></iframe>');
	}

}

function has_another_special_table($tablename, $key) {
	if(!$key) {
		return $tablename;
	}

	$tables_array = get_special_tables_array($tablename);

	if($key > count($tables_array)) {
		return FALSE;
	} else {
		return TRUE;
	}
}
function converttodzzcode($aid){
	return 'path='.dzzencode('attach::'.$aid);
}
function get_special_tables_array($tablename) {
	$tablename = DB::table($tablename);
	$tablename = str_replace('_', '\_', $tablename);
	$query = DB::query("SHOW TABLES LIKE '{$tablename}\_%'");
	$dbo = DB::object();
	$tables_array = array();
	while($row = $dbo->fetch_array($query, $dbo->drivertype == 'mysqli' ? MYSQLI_NUM : MYSQL_NUM)) {
		if(preg_match("/^{$tablename}_(\\d+)$/i", $row[0])) {
			$prefix_len = strlen($dbo->tablepre);
			$row[0] = substr($row[0], $prefix_len);
			$tables_array[] = $row[0];
		}
	}
	return $tables_array;
}

function get_special_table_by_num($tablename, $num) {
	$tables_array = get_special_tables_array($tablename);

	$num --;
	return isset($tables_array[$num]) ? $tables_array[$num] : FALSE;
}

function getcolumn($creatsql) {

	$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
	preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = remakesql($value);
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

			$name_length = strlen($cname);
			if($cname == 'UNIQUE') $name_length = $name_length + 4;

			$subvalue = trim(substr($value, $name_length));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols[$cname][$subcname] = trim(substr($value, ($name_length+2+strlen($subcname))));

		}  elseif($cname == 'PRIMARY') {

			$newcols[$cname] = trim(substr($value, 11));

		}  else {

			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

function remakesql($value) {
	$value = trim(preg_replace("/\s+/", ' ', $value));
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )', 'mediumtext'), array('', ',', ',','(',')','text'), $value);
	return $value;
}

function show_msg($message, $url_forward='', $time = 1, $noexit = 0, $notice = '') {

	if($url_forward) {
		$url_forward = $_GET['from'] ? $url_forward.'&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : $url_forward;
		$message = "<a href=\"$url_forward\">$message (跳转中...)</a><br>$notice<script>setTimeout(\"window.location.href ='$url_forward';\", $time);</script>";
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	</table>
END;
	show_footer();
	!$noexit && exit();
}


function show_header() {
	global $config;

	$nowarr = array($_GET['step'] => ' class="current"');
	if(in_array($_GET['step'], array('waitingdb','prepare'))) {
		$nowarr = array('sql' => ' class="current"');
	}
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<title> 数据库升级程序 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>DzzOffice 数据库升级工具</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[start]}>升级开始</td>
	<td{$nowarr[sql]}>数据库结构添加与更新</td>
	<td{$nowarr[data]}>数据更新</td>
	<td{$nowarr[delete]}>数据库结构删除</td>
	<td{$nowarr[cache]}>升级完成</td>
	</tr>
	</table>
	<br>
END;
}

function show_footer() {
	print<<<END
	</div>
	<div id="footer">Copyright © 2012-2017 DzzOffice.com All Rights Reserved.</div>
	</div>
	<br>
	</body>
	</html>
END;
}

function runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' dzz_', ' `dzz_'), array(' '.$tablepre,  ' '.$tablepre, ' `'.$tablepre), $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(create_table($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}


function save_config_file($filename, $config, $default, $deletevar) {
	$config = setdefault($config, $default, $deletevar);
	$date = gmdate("Y-m-d H:i:s", time() + 3600 * 8);
	$content = <<<EOT
<?php


\$_config = array();

EOT;
	$content .= getvars(array('_config' => $config));
	$content .= "\r\n// ".str_pad('  THE END  ', 50, '-', STR_PAD_BOTH)." //\r\n\r\n?>";
	if(!is_writable($filename) || !($len = file_put_contents($filename, $content))) {
		file_put_contents(DZZ_ROOT.'./data/config.php', $content);
		return 0;
	}
	return 1;
}

function setdefault($var, $default, $deletevar) {
	foreach ($default as $k => $v) {
		if(!isset($var[$k])) {
			$var[$k] = $default[$k];
		} elseif(is_array($v)) {
			$var[$k] = setdefault($var[$k], $default[$k]);
		}
	}
	foreach ($deletevar as $k) {
		unset($var[$k]);
	}
	return $var;
}

function getvars($data, $type = 'VAR') {
	$evaluate = '';
	foreach($data as $key => $val) {
		if(!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
			continue;
		}
		if(is_array($val)) {
			$evaluate .= buildarray($val, 0, "\${$key}")."\r\n";
		} else {
			$val = addcslashes($val, '\'\\');
			$evaluate .= $type == 'VAR' ? "\$$key = '$val';\n" : "define('".strtoupper($key)."', '$val');\n";
		}
	}
	return $evaluate;
}

function buildarray($array, $level = 0, $pre = '$_config') {
	static $ks;
	if($level == 0) {
		$ks = array();
		$return = '';
	}

	foreach ($array as $key => $val) {
		if($level == 0) {
			$newline = str_pad('  CONFIG '.strtoupper($key).'  ', 70, '-', STR_PAD_BOTH);
			$return .= "\r\n// $newline //\r\n";
			if($key == 'admincp') {
				$newline = str_pad(' Founders: $_config[\'admincp\'][\'founder\'] = \'1,2,3\'; ', 70, '-', STR_PAD_BOTH);
				$return .= "// $newline //\r\n";
			}
		}

		$ks[$level] = $ks[$level - 1]."['$key']";
		if(is_array($val)) {
			$ks[$level] = $ks[$level - 1]."['$key']";
			$return .= buildarray($val, $level + 1, $pre);
		} else {
			$val =  is_string($val) || strlen($val) > 12 || !preg_match("/^\-?[1-9]\d*$/", $val) ? '\''.addcslashes($val, '\'\\').'\'' : $val;
			$return .= $pre.$ks[$level - 1]."['$key']"." = $val;\r\n";
		}
	}
	return $return;
}

function dir_clear($dir) {
	global $lang;
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			}
		}
		$directory->close();
		@touch($dir.'/index.htm');
	}
}
function create_table($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(" ENGINE=$type DEFAULT CHARSET=".$dbcharset);
}

?>
