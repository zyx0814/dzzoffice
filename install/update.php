<?php
/*
 * @copyright   Leyun Internet Technology(Shanghai) Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
define('CURSCRIPT', 'misc');
require __DIR__ . '/../core/coreBase.php';
@set_time_limit(0);
error_reporting(0);
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
if(empty($_GET['step'])) $_GET['step'] = 'start';
$lockfile = DZZ_ROOT.'./data/update.lock';
if(file_exists($lockfile) && !$_GET['from']) {
	show_msg('请您先手工删除 ./data/update.lock 文件，再次运行本文件进行升级。');
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

if($_GET['step'] == 'start') {
	if(!C::t('setting')->fetch('bbclosed')) {
		C::t('setting')->update('bbclosed', 1);
		require_once libfile('function/cache');
		updatecache('setting');
		show_msg('您的站点未关闭，正在关闭，请稍后...', $theurl.'?step=start', 5000);
	}
	$phpversion=PHP_VERSION;
	$msg = 'php版本不支持，仅支持php7+到php8以下，建议使用php7.4<br>当前版本：'.$phpversion.'<br><br><a href="'.$theurl.'?step=prepare'.($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '').'">已更换PHP版本，开始升级</a>';
	if(strcmp($phpversion, '7+') < 0) {
		show_msg($msg);
	}
	if(strcmp($phpversion, '8.0') >=0) {
		show_msg($msg);
	}
	show_msg('<h2>说明：</h1>本升级程序会参照最新的SQL文件，对数据库进行同步升级。<br>
			请确保网站根目录下 ./install/data/install.sql 文件为最新版本。<br>请在升级之前做好站点全量数据（含数据库、文件）的备份操作，并小心操作。<br><br>
			<a href="'.$theurl.'?step=prepare'.($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '').'">准备完毕，开始升级</a>');
	
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
		preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*=\s*(\w+)/s", $newsqls[$i], $maths);

		$maths[3] = strtoupper($maths[3]);
		if($maths[3] == 'MEMORY' || $maths[3] == 'HEAP') {
			$type = " ENGINE=MEMORY".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" );
		} else {
			$type =" ENGINE=MYISAM".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" );
		}
		$usql = $maths[1].$type;

		$usql = str_replace("CREATE TABLE IF NOT EXISTS dzz_", 'CREATE TABLE IF NOT EXISTS '.$config['tablepre'], $usql);
		$usql = str_replace("CREATE TABLE IF NOT EXISTS `dzz_", 'CREATE TABLE IF NOT EXISTS `' . $config['tablepre'], $usql);
		$usql = str_replace("CREATE TABLE dzz_", 'CREATE TABLE '.$config['tablepre'], $usql);
		$usql = str_replace("CREATE TABLE `dzz_", 'CREATE TABLE `' . $config['tablepre'], $usql);
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
							show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"font-size:11px;background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
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
				show_msg('升级表 '.DB::table($newtable).' 出错,请手工执行以下升级语句后,再重新运行本升级程序:<br><br><b>升级SQL语句</b>:<div style=\"background:#EBEBEB;padding:0.5em;\">'.dhtmlspecialchars($usql)."</div><br><b>Error</b>: ".DB::error()."<br><b>Errno.</b>: ".DB::errno());
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
		//新增两个配置项
		 C::t('setting')->update('fileVersion', '1');
		 C::t('setting')->update('fileVersionNumber', '50');
		
		//添加云设置和管理应用
		if(!DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{adminscript}?mod=cloud'))){
			
		 C::t('app_market')->insert(array('appname'=>'云设置和管理',
		 								  'appico'=>'appico/201712/21/171106u1dk40digrrr79ed.png',
		 								  'appurl'=>'{adminscript}?mod=cloud',
										  'appdesc'=>'设置和管理第三方云盘、云存储等',
										  'dateline'=>TIMESTAMP,
										  'disp'=>5,
										  'vendor'=>'乐云网络',
										  'group'=>3,
										  'system'=>2,
										  'notdelete'=>1,
										  'position'=>0,
										  'app_path'=>'admin',
										  'identifier'=>'cloud',
										  'version'=>'2.0',
										  'available'=>1),0,1);
		}
		//添加用户资料管理应用
		if(!DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{adminscript}?mod=member'))){
			
		 C::t('app_market')->insert(array('appname'=>'用户资料管理',
		 								  'appico'=>'appico/201712/21/103805dczcm89b0gi8i9gc.png',
		 								  'appurl'=>'{adminscript}?mod=member',
										  'appdesc'=>'用户资料项配置，资料审核，认证等',
										  'dateline'=>TIMESTAMP,
										  'disp'=>10,
										  'vendor'=>'乐云网络',
										  'group'=>3,
										  'system'=>2,
										  'notdelete'=>1,
										  'position'=>0,
										  'app_path'=>'admin',
										  'identifier'=>'member',
										  'version'=>'2.0',
										  'available'=>1),0,1);
		}
		//添加网盘应用
		if(!DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{dzzscript}?mod=explorer'))){
			C::t('app_market')->insert(array('appname'=>'网盘',
											  'appico'=>'appico/202411/02/170040bgapsjg4pt4nuee4.png',
											  'appurl'=>'{dzzscript}?mod=explorer',
											 'appdesc'=>'企业、团队文件集中管理。主要体现的功能是支持企业部门的组织架构建立共享目录，也支持组的方式灵活建立共享目录。支持文件标签，多版本，评论，详细的目录权限等协作功能',
											 'dateline'=>TIMESTAMP,
											 'disp'=>14,
											 'vendor'=>'乐云网络',
											 'group'=>1,
											 'system'=>0,
											 'notdelete'=>1,
											 'position'=>1,
											 'mid'=>'27',
											 'app_path'=>'dzz',
											 'identifier'=>'explorer',
											 'version'=>'2.05',
											 'available'=>1),0,1);
		   }
		//添加图片预览应用
		if(!DB::result_first("select COUNT(*) from %t where identifier=%s",array('app_market','OpenPicWin'))){
			C::t('app_market')->insert(array('mid' => '25','appname' => '图片预览','appico' => 'appico/202411/02/184008xbuvo0sh8y1xey8f.png','appdesc' => '简易的图片浏览器','appurl' => "dzzjs:OpenPicWin('{icoid}')",'appadminurl' => '','noticeurl' => '','dateline' => '0','disp' => '101','vendor' => '乐云网络','haveflash' => '0','isshow' => '0','havetask' => '1','hideInMarket' => '0','feature' => '','fileext' => 'image','group' => '0','orgid' => '0','position' => '1','system' => '0','notdelete' => '1','open' => '0','nodup' => '0','identifier' => 'OpenPicWin','app_path' => 'dzz/link','available' => '1','version' => '2.1'));
			$OpenPicWin=C::t('app_market')->fetch_by_identifier('OpenPicWin','dzz/link');
			if($OpenPicWin['appid']){
				C::t('app_open')->insert_by_exts($OpenPicWin['appid'], 'image');
			}
		}
		//添加DPlayer应用
		if(!DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{dzzscript}?mod=DPlayer'))){
			C::t('app_market')->insert(array('mid' => '41','appname' => 'DPlayer','appico' => 'appico/202411/02/184037v0by6dzb1wwobdy3.png','appdesc' => 'DPlayer，支持MP3,mp4,flv,wav等格式','appurl' => '{dzzscript}?mod=DPlayer','appadminurl' => '','noticeurl' => '','dateline' => '0','disp' => '0','vendor' => '小胡（gitee.com/xiaohu2024)','haveflash' => '0','isshow' => '0','havetask' => '1','hideInMarket' => '0','feature' => '','fileext' => 'mp3,mp4,m4v,flv,mov,webm,ogv,ogg,wav,m3u8,f4v,webmv,mkv,magne','group' => '0','orgid' => '0','position' => '1','system' => '0','notdelete' => '1','open' => '1','nodup' => '0','identifier' => 'DPlayer','app_path' => 'dzz','available' => '1','version' => '1.2'),1,1);
			$DPlayer=C::t('app_market')->fetch_by_identifier('DPlayer');
			if($DPlayer['appid']){
				C::t('app_open')->insert_by_exts($DPlayer['appid'], 'mp3,mp4,m4v,flv,mov,webm,ogv,ogg,wav,m3u8,f4v,webmv,mkv,magne');
			}
		}
		//添加PDF阅读器应用
		if(!DB::result_first("select COUNT(*) from %t where appurl=%s",array('app_market','{dzzscript}?mod=pdf'))){
			C::t('app_market')->insert(array('mid' => '13','appname' => 'PDF阅读器','appico' => 'appico/202411/02/170328nz056he0mixeezpo.png','appdesc' => '通过HTML5的方式来实现pdf在线预览','appurl' => 'index.php?mod=pdf','appadminurl' => '','noticeurl' => '','dateline' => '0','disp' => '110','vendor' => 'PDS.JS','haveflash' => '0','isshow' => '0','havetask' => '1','hideInMarket' => '0','feature' => '','fileext' => 'pdf,ai','group' => '0','orgid' => '0','position' => '1','system' => '0','notdelete' => '1','open' => '0','nodup' => '0','identifier' => 'pdf','app_path' => 'dzz','available' => '1','version' => '2.1'),1,1);
			$pdf=C::t('app_market')->fetch_by_identifier('pdf');
			if($pdf['appid']){
				C::t('app_open')->insert_by_exts($pdf['appid'], 'pdf,ai');
			}
		}
		//修改应用
		$appurl = "{adminscript}?mod=filemanage";
		$filemanageappid = DB::result_first("SELECT appid FROM %t WHERE appurl=%s", array('app_market', $appurl));
		if ($filemanageappid) {
			C::t('app_market')->update($filemanageappid, array('appurl' => "{dzzscript}?mod=filemanage", 'group' => 1,'open'=>1,'app_path'=>'dzz','position'=>1));
		}
		$appurl = "{adminscript}?mod=orguser";
		$orguserappid = DB::result_first("SELECT appid FROM %t WHERE appurl=%s", array('app_market', $appurl));
		if ($orguserappid) {
			C::t('app_market')->update($orguserappid, array('appurl' => "{dzzscript}?mod=orguser", 'group' => 1,'open'=>1,'app_path'=>'dzz','position'=>1));
		}
		$appurl = "{adminscript}?mod=share";
		$shareappid = DB::result_first("SELECT appid FROM %t WHERE appurl=%s", array('app_market', $appurl));
		if ($shareappid) {
			C::t('app_market')->update($shareappid, array('appurl' => "{dzzscript}?mod=share", 'group' => 1,'open'=>1,'app_path'=>'dzz','position'=>1));
		}
		$appurl = "{dzzscript}?mod=comment";
		$commentappid = DB::result_first("SELECT appid FROM %t WHERE appurl=%s", array('app_market', $appurl));
		if ($commentappid) {
			C::t('app_market')->update($commentappid, array('group' => 1,'open'=>1,'position'=>1));
		}
		//处理更新之后群组开关问题
		DB::update('organization',array('manageon'=>1,'available'=>1,'syatemon'=>1),"1");
		show_msg("基本设置修改完成", "$theurl?step=data&dp=1");
	
	}elseif($_GET['dp']==1){ //转换机构和部门数据
	
		$i = empty($_GET['i'])?0:intval($_GET['i']);
		$count_i = DB::result_first("select COUNT(*) from %t where 1 ",array('organization'));
		if($i>=$count_i) {
			show_msg('部门数据升级完成，进入下一步操作', $theurl.'?step=data&dp=2');
		}
		if($orgid=DB::result_first("select orgid from %t where 1 order by orgid limit $i,1 ",array('organization'))){
			C::t('organization')->setPathkeyByOrgid($orgid,1);
		}
		$i++;
		$msg='部门数据转换完成';
		$next=$theurl.'?step=data&dp=1&i='.$i;
		show_msg("[ $i / $count_i ] ".$msg, $next);
	
	}elseif($_GET['dp']==2){ //修复目录gid
		$i = empty($_GET['i'])?0:intval($_GET['i']);
		
		$count_i = DB::result_first("select COUNT(*) from %t where gid>0 ",array('folder'));
		if($i>=$count_i) {
			show_msg('开始修复继承权限...', $theurl.'?step=data&dp=3');
		}
		$arr=DB::fetch_first("select fid,pfid,gid,fname from %t where gid>0  order by fid limit $i,1",array('folder'));
		$gid=C::t('folder')->fetch_gid_by_fid($arr['fid']);
		
		if($gid!=$arr['gid']){
			C::t('folder')->update($arr['fid'],array('gid'=>$gid));
			DB::query("update %t set gid=%d where pfid=%d ",array('resources',$gid,$arr['fid']));
		}
		$i++;
		$msg='部门文件夹修复完成';
		$next=$theurl.'?step=data&dp=2&i='.$i;
		show_msg("[ $i / $count_i ] ".$msg, $next);
	}elseif($_GET['dp']==3){ //更新继承权限和路径
		$i = empty($_GET['i'])?0:intval($_GET['i']);
		
		$count_i = DB::result_first("select COUNT(*) from %t",array('folder'));
		if($i>=$count_i) {
			show_msg('开始修复回收站...', $theurl.'?step=data&dp=4');
		}
		$arr=DB::fetch_first("select fid from %t order by fid limit $i,1",array('folder'));
		$pdata = C::t('folder')->create_pathinfo_by_fid($arr['fid']);
		if($pdata){
			if(!DB::result_first("select count(*) from %t where fid = %d",array('resources_path',$arr['fid']))){
				$pdata['fid'] = $arr['fid'];
				DB::insert('resources_path',$pdata);
			}else{
				DB::update('resources_path',$pdata,array('fid'=>$arr['fid']));
			}
		}
		$perm_inherit=perm_check::getPerm1($arr['fid']);
		DB::update('folder',array('perm_inherit'=>$perm_inherit),"fid='{$arr['fid']}'");
		$i++;
		$msg='继承权限修复';
		$next=$theurl.'?step=data&dp=3&i='.$i;
		show_msg("[ $i / $count_i ] ".$msg, $next);
	}elseif($_GET['dp']==4){ //修改回收站相关
		//回收站数据处理
		$rids = $delfids = $delrids = array();
		foreach(DB::fetch_all("select rid from %t where isdelete>0",array('resources')) as $v){
			 $delrids[] = $v['rid'];
			 if($v['type'] == 'folder' && $v['oid']){
				$delfids[] = $v['oid'];
			}
		}
		//更改resources表数据
		if(count($delrids) > 0) DB::update("resources",array('pfid'=>-1),'rid in('.dimplode($delrids).')');
		//更改folder表数据
		if(count($delfids) > 0) DB::update("folder",array('pfid'=>-1),'fid in('.dimplode($delfids).')');
		//清除回收站中的无用数据
		DB::delete('resources_recyle','rid not in('.dimplode($delrids).')');
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
						$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$coltype][$index]\" value=\"1\"></td><td>表 {$config['tablepre']}$tablename</td><td>索引($coltype) $index $indexvalue</td></tr>";
					}
				} else {
					$delcolumnhtml .= "<tr><td><input type=\"checkbox\" name=\"delcols[$tablename][$col]\" value=\"1\"></td><td>表 {$config['tablepre']}$tablename</td><td>字段 $col</td></tr>";
				}
			}
		}
		$delcolumnhtml .= '</table>';

		echo "<p>以下 <strong>字段</strong> 与标准数据库相比是多余的:<br>您可以根据需要自行决定是否删除(建议删除)</p>$delcolumnhtml";
	}

	if(empty($deltables) && empty($delcolumns)) {
		echo "<p>与标准数据库相比，没有需要删除的数据表和字段</p><a href=\"$theurl?step=cache".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">请点击进入下一步</a></p>";
	} else {
		echo "<p><input type=\"submit\" name=\"delsubmit\" value=\"提交删除\"></p><p>您也可以忽略多余的表和字段</p><a href=\"$theurl?step=cache".($_GET['from'] ? '&from='.rawurlencode($_GET['from']).'&frommd5='.rawurlencode($_GET['frommd5']) : '')."\">直接进入下一步</a>";
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
	$configfile = DZZ_ROOT.'data/cache/default_mod.php';  
	$configarr = array();
	if($_G['setting']['default_mod']) {
		$configarr['default_mod']=$_G['setting']['default_mod'];
	} else{
		$configarr['default_mod']='explorer';
	}
	@file_put_contents($configfile,"<?php \t\n return ".var_export($configarr,true).";");
	C::t('setting')->update('bbclosed', 0);
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
	$version = CORE_VERSION;
	$nowarr = array($_GET['step'] => ' class="current"');
	if(in_array($_GET['step'], array('waitingdb','prepare'))) {
		$nowarr = array('sql' => ' class="current"');
	}
	print<<<END
	<!DOCTYPE html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$config[charset]" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="DzzOffice" />
	<title>DzzOffice 升级程序</title>
	<style type="text/css">
	body { margin: 0; padding: 0; background: #f4f5fa;font-size: 14px; }
	.bodydiv {margin: 40px auto 40px auto; max-width:720px; border: 1px solid #007bff; border-width: 5px 1px 1px; background: #FFF; border-radius: 12px;box-shadow: 0 5px 10px rgba(0, 0, 0, .15) !important;}
	h1 { font-size: 18px; margin: 0; padding: 10px; color: #495057; padding-left: 10px; border-bottom: 1px solid #ededee;}
	#menu {width: 100%; margin: 0 0 10px 0; text-align: center; }
	#menu td { padding: 10px;color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #007bff !important; border-bottom-color: #007bff !important; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	#footer {text-align: center;color: #6c757d; padding: 10px;border-top: 1px solid rgba(77, 82, 89, 0.1); }
	a {font-size: 16px;color: #007bff;padding:5px 10px;border-radius:5px;border:1px solid #007bff;background-color:transparent;text-decoration:none;transition:color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;font-weight:400;line-height:1.5;text-align:center;}
	a:hover {color: #fff;background-color: #007bff;}
	table {width: 100%;}
	input:hover,
	button:hover {
		color: #212529;
		border-color: #007bff;
		box-shadow: 0 0 0 1px rgba(13, 110, 253, .25);
		outline: 0;
		-webkit-transition: all .25s linear;
		-moz-transition: all .25s linear;
		-ms-transition: all .25s linear;
		-o-transition: all .25s linear;
		transition: all .25s linear;
	}
	</style>
	</head>
	<body>
	<div class="bodydiv">
	<h1>DzzOffice V$version 升级程序</h1>
	<div style="padding: 10px;">
	<table id="menu">
	<tr>
	<td{$nowarr['start']}>升级准备</td>
	<td{$nowarr['sql']}>数据库结构添加与更新</td>
	<td{$nowarr['data']}>系统数据更新</td>
	<td{$nowarr['delete']}>数据库结构删除</td>
	<td{$nowarr['cache']}>升级完成</td>
	</tr>
	</table>
END;
}

function show_footer() {
	$date = date("Y");
	print<<<END
	</div>
	<div id="footer">Copyright © 2012-$date DzzOffice.com All Rights Reserved.</div>
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