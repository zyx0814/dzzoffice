<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(1000);
if(function_exists('set_magic_quotes_runtime')) {
	@set_magic_quotes_runtime(0);
}
define('IN_DZZ', TRUE);
define('ROOT_PATH', dirname(__FILE__).'/../');

@include_once ROOT_PATH.'./core/config/config.php';
define('CHARSET', $_config['output']['charset']);
define('DBCHARSET', $_config['db']['1']['dbcharset']);

$lock_file = ROOT_PATH.'./data/restore.lock';
if(file_exists($lock_file)) {
	show_msg('restored_error');
}

require_once ROOT_PATH.'./core/core_version.php';


$operation = trim(getgpc('operation', 'G'));
$operation = $operation ? $operation : 'import';
$phpself = htmlspecialchars($_SERVER['SCRIPT_NAME'] ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF']);
$siteurl = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api)?\/*$/i", '', substr($phpself, 0, strrpos($phpself, '/'))).'/');

$db = function_exists("mysql_connect") ? new dbstuff : new dbstuffi;
if(!@$db->connect($_config['db']['1']['dbhost'], $_config['db']['1']['dbuser'], $_config['db']['1']['dbpw'], $_config['db']['1']['dbname'], $_config['db']['1']['dbcharset'])) {
	show_msg('connect_error');
}

if($operation == 'import') {

	if(!submitcheck('importsubmit', 1)) {
		$exportlog = $exportsize = $exportziplog = array();
		check_exportfile($exportlog, $exportziplog, $exportsize);
		if(empty($exportlog) && empty($exportziplog)) {
			show_msg('backup_file_unexist');
		}

		show_importfile_list($exportlog, $exportziplog, $exportsize);

	} else {

		$readerror = 0;
		$datafile_vol1 = trim(getgpc('datafile_vol1'));
		if($datafile_vol1) {
			$datafile = $datafile_vol1;
		} else {
			$datafile = getgpc('datafile_server', 'G');
		}
		$datafile = urldecode($datafile);
		if(@$fp = fopen($datafile, 'rb')) {
			$confirm = trim(getgpc('confirm', 'G'));
			$confirm = $confirm ? 1 : 0;
			$sqldump = fgets($fp, 256);
			$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", $sqldump)));
			$dumpinfo = array('method' => $identify[3], 'volume' => intval($identify[4]), 'tablepre' => $identify[5], 'dbcharset' => $identify[6]);
			if(!$confirm) {
				$showmsg = '';
				if($dumpinfo['tablepre'] != $_config['db']['1']['tablepre'] && !getgpc('ignore_tablepre', 'G')) {
					$showmsg .= lang('tableprediff');
				}
				if($dumpinfo['dbcharset'] != $_config['db']['1']['dbcharset']) {
					$showmsg .= lang('dbcharsetdiff');
				}
				if($showmsg) {
					show_msg(lang('different_dbcharset_tablepre', TRUE, array('diff' => $showmsg)), $siteurl.'restore.php?operation=import&datafile_server='.$datafile.'&autoimport=yes&importsubmit=yes&confirm=yes', 'confirm');
				}
			}

			if($dumpinfo['method'] == 'multivol') {
				$sqldump .= fread($fp, filesize($datafile));
			}
			fclose($fp);
		} else {
			if(getgpc('autoimport', 'G')) {
				touch($lock_file);
				show_msg('database_import_multivol_succeed', '', 'message', 1);
			} else {
				show_msg('database_import_file_illegal');
			}
		}

		if($dumpinfo['method'] == 'multivol') {
			$sqlquery = splitsql($sqldump);
			unset($sqldump);

			foreach($sqlquery as $sql) {

				$sql = syntablestruct(trim($sql), $db->version() > '4.1', DBCHARSET);

				if($sql != '') {
					$db->query($sql, 'SILENT');
					if(($sqlerror = $db->error()) && $db->errno() != 1062) {
						$db->halt('MySQL Query Error', $sql);
					}
				}
			}

			$delunzip = getgpc('delunzip', 'G');
			if($delunzip) {
				@unlink($datafile);
			}

			$datafile_next = preg_replace("/-($dumpinfo[volume])(\..+)$/", "-".($dumpinfo['volume'] + 1)."\\2", $datafile);
			$datafile_next = urlencode($datafile_next);
			if($dumpinfo['volume'] == 1) {
				show_msg(lang('database_import_multivol_redirect', TRUE, array('volume' => $dumpinfo['volume'])),
					$siteurl."restore.php?operation=import&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes&confirm=yes".(!empty($delunzip) ? '&delunzip=yes' : ''),
					'redirect');
			} elseif(getgpc('autoimport', 'G')) {
				show_msg(lang('database_import_multivol_redirect', TRUE, array('volume' => $dumpinfo['volume'])), $siteurl."restore.php?operation=import&datafile_server=$datafile_next&autoimport=yes&importsubmit=yes&confirm=yes".(!empty($delunzip) ? '&delunzip=yes' : ''), 'redirect');
			} else {
				show_msg('database_import_succeed', '', 'message', 1);
			}
		} elseif($dumpinfo['method'] == 'shell') {

			list($dbhost, $dbport) = explode(':', $dbhost);
			$query = $db->query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = $db->fetch($query, $db->drivertype == 'mysqli' ? MYSQLI_NUM : MYSQL_NUM);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes($mysql_base).'bin/';
			shell_exec($mysqlbin.'mysql -h"'.$dbhost.($dbport ? (is_numeric($dbport) ? ' -P'.$dbport : ' -S"'.$dbport.'"') : '').
				'" -u"'.$dbuser.'" -p"'.$dbpw.'" "'.$dbname.'" < '.getgpc('datafile'));

			show_msg('database_import_succeed', '', 'message', 1);
		} else {
			show_msg('database_import_format_illegal');
		}
	}

} elseif($operation == 'importzip') {

	if(!getgpc('datafile_server')) {
		show_msg('database_import_file_illegal');
	} else {
		$datafile_server = getgpc('datafile_server');
		if(!@file_exists($datafile_server)) {
			show_msg('database_import_file_illegal');
		}
	}
	$datafile_vol1 = trim(getgpc('datafile_vol1', 'G'));
	$multivol = intval(getgpc('multivol', 'G'));

	require_once ROOT_PATH.'./core/class/class_zip.php';
	$unzip = new SimpleUnzip();
	$backupdir = substr($datafile_server, 8, 13);
	$unzip->ReadFile($datafile_server);

	if($unzip->Count() == 0 || $unzip->GetError(0) != 0 || !preg_match("/\.sql$/i", $importfile = $unzip->GetName(0))) {
		show_msg('database_import_file_illegal');
	}

	$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", substr($unzip->GetData(0), 0, 256))));
	$confirm = getgpc('confirm', 'G');
	$confirm = !empty($confirm) ? 1 : 0;
	if(!$confirm && $identify[1] != CORE_VERSION) {
		show_msg('database_import_confirm', $siteurl.'restore.php?operation=importzip&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes', 'confirm');
	}

	$sqlfilecount = 0;
	foreach($unzip->Entries as $entry) {
		if(preg_match("/\.sql$/i", $entry->Name)) {
			$fp = fopen('../data/'.$backupdir.'/'.$entry->Name, 'w');
			fwrite($fp, $entry->Data);
			fclose($fp);
			$sqlfilecount++;
		}
	}

	if(!$sqlfilecount) {
		show_msg('database_import_file_illegal');
	}

	$info = '<b>'.basename($datafile_server).'</b><br />'.lang('version').': '.$identify[1].'<br />'.lang('type').': '.lang('db_export_'.$identify[2]).'<br />'.lang('db_method').': '.($identify[3] == 'multivol' ? lang('db_multivol') : lang('db_shell')).'<br />';

	if($multivol) {
		$multivol++;
		$datafile_server = preg_replace("/-(\d+)(\..+)$/", "-$multivol\\2", $datafile_server);
		if(file_exists($datafile_server)) {
			show_msg(lang('database_import_multivol_unzip_redirect', TRUE, array('multivol' => $multivol)), $siteurl.'restore.php?operation=importzip&multivol='.$multivol.'&datafile_vol1='.$datafile_vol1.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes', 'redirect');
		} else {
			show_msg('database_import_multivol_confirm', $siteurl.'restore.php?operation=import&datafile_server='.$datafile_vol1.'&importsubmit=yes&delunzip=yes', 'confirm');
		}
	}

	if($identify[3] == 'multivol' && $identify[4] == 1 && preg_match("/-1(\..+)$/", $datafile_server)) {
		$datafile_vol1 = $datafile_server;
		$datafile_server = preg_replace("/-1(\..+)$/", "-2\\1", $datafile_server);

		if(file_exists($datafile_server)) {
			show_msg(lang('database_import_multivol_unzip_redirect', TRUE, array('multivol' => 1)), $siteurl.'restore.php?operation=importzip&multivol=1&datafile_vol1=../data/'.$backupdir.'/'.$importfile.'&datafile_server='.$datafile_server.'&importsubmit=yes&confirm=yes', 'redirect');
		}
	}

	show_msg(lang('database_import_unzip', TRUE, array('info' => $info)), $siteurl.'restore.php?operation=import'.($datafile_vol1 ? '&datafile_vol1='.$datafile_vol1 : '').'&datafile_server=../data/'.$backupdir.'/'.$importfile.'&importsubmit=yes&delunzip=yes', 'confirm');
}

function get_backup_dir() {

	$backupdirs = array();
	$dir = dir(ROOT_PATH.'./data');
	while(($file = $dir->read()) !== FALSE) {
		if(filetype(ROOT_PATH.'./data/'.$file) == 'dir' && preg_match('/^backup_\w+/', $file)) {
			$backupdirs[] = $file;
		}
	}
	$dir->close();
	return $backupdirs;
}

function check_exportfile(&$exportlog, &$exportziplog, &$exportsize) {

	$backupdirs = get_backup_dir();
	if(empty($backupdirs)) {
		return;
	}

	foreach($backupdirs as $backupdir) {
		$dir = dir(ROOT_PATH.'./data/'.$backupdir);
		while($entry = $dir->read()) {
			$entry = '../data/'.$backupdir.'/'.$entry;
			if(is_file($entry)) {
				if(preg_match("/\.sql$/i", $entry)) {
					$filesize = filesize($entry);
					$fp = fopen($entry, 'rb');
					$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
					fclose($fp);
					$key = preg_replace('/^(.+?)(\-\d+)\.sql$/i', '\\1', basename($entry));
					$exportlog[$key][$identify[4]] = array(
						'version' => $identify[1],
						'type' => $identify[2],
						'method' => $identify[3],
						'volume' => $identify[4],
						'tablepre' => $identify[5],
						'dbcharset' => $identify[6],
						'filename' => $entry,
						'dateline' => filemtime($entry),
						'size' => $filesize
					);
					$exportsize[$key] += $filesize;
				} elseif(preg_match("/\.zip$/i", $entry)) {
					$key = preg_replace('/^(.+?)(\-\d+)\.zip$/i', '\\1', basename($entry));
					$filesize = filesize($entry);
					$exportziplog[$key][] = array(
						'type' => 'zip',
						'filename' => $entry,
						'size' => filesize($entry),
						'dateline' => filemtime($entry)
					);
				}
			}
		}
		$dir->close();
	}
}

function show_importfile_list($exportlog = array(), $exportziplog = array(), $exportsize = array()) {

	show_header();
	show_tips('db_import_tips');

	$title = array('filename', 'director', 'version', 'time', 'type', 'size', 'db_method', 'db_volume', '');
	echo "\n<table width='100%'>\n<tr>";
	foreach($title as $col) {
		echo "<th>".lang($col)."</th>";
	}
	echo "</tr>\n";

	foreach($exportlog as $key => $val) {
		$info = $val[1];
		$info['dateline'] = is_int($info['dateline']) ? gmdate('Y-m-d H:i:s', $info['dateline'] + 3600 * 8) : lang('unknown');
		$info['size'] = sizecount($exportsize[$key]);
		$info['volume'] = count($val);
		$info['method'] = $info['type'] != 'zip' ? ($info['method'] == 'multivol' ? lang('db_multivol') : lang('db_shell')) : '';
		echo "<tr>";
		echo
			"<td><a href=\"javascript:;\" onclick=\"display('exportlog_$key')\">".basename($info['filename'])."</a></td>",
			'<td width="150">'.dirname($info['filename']).'</td>',
			'<td width="60">'.$info['version'].'</td>',
			'<td width="140">'.$info['dateline'].'</td>',
			'<td width="170">'.lang('db_export_'.$info['type']).'</td>',
			'<td width="80">'.$info['size'].'</td>',
			'<td width="30">'.$info['method'].'</td>',
			'<td width="30">'.$info['volume'].'</td>',
			'<td width="40">'.($info['type'] == 'zip' ? "<a href=\"{$siteurl}restore.php?operation=importzip&datafile_server=$info[filename]&importsubmit=yes\" onclick=\"return confirm('".lang('database_import_confirm_zip')."');\">".lang('db_import_unzip')."</a>" : "<a href=\"{$siteurl}restore.php?operation=import&datafile_server=$info[filename]&importsubmit=yes\" ".(($info['version'] != CORE_VERSION) ? "onclick=\"return confirm('".lang('database_import_confirm')."');\"" : "onclick=\"return confirm('".lang('database_import_confirm_sql')."');\"").">".lang('import')."</a>")."</td>"
		;
		echo "</tr>\n";
		echo '<tbody id="exportlog_'.$key.'" style="display:none">';
		foreach($val as $info) {
			$info['dateline'] = is_int($info['dateline']) ? gmdate('Y-m-d H:i:s', $info['dateline'] + 3600 * 8) : lang('unknown');
			$info['size'] = sizecount($info['size']);
			echo "<tr>";
			echo
				"<td colspan='2' class='subtb'><a href=\"$info[filename]\">".$info['filename']."</a></td>",
				"<td>".$info['version']."</td>",
				"<td>".$info['dateline']."</td>",
				'<td></td>',
				"<td>".$info['size']."</td>",
				'<td></td>',
				"<td>".$info['volume']."</td>",
				'<td></td>'
			;
			echo "</tr>\n";
		}
		echo '</tbody>';
	}

	foreach($exportziplog as $key => $val) {

		$info = $val[0];
		$info['dateline'] = is_int($info['dateline']) ? gmdate('Y-m-d H:i:s', $info['dateline'] + 3600 * 8) : lang('unknown');
		$info['size'] = sizecount($info['size']);
		$info['method'] = $info['method'] == 'multivol' ? lang('db_multivol') : lang('db_zip');
		echo "<tr>";
		echo
			"<td><a href=\"javascript:;\" onclick=\"display('exportlog_$key')\">".basename($info['filename'])."</a></td>",
			"<td colspan='2'>".dirname($info['filename'])."</td>",
			"<td width='140'>".$info['dateline']."</td>",
			"<td width='170'>".lang('db_export_'.$info['type'])."</td>",
			"<td width='80'>".$info['size']."</td>",
			"<td colspan='2'>".$info['method']."</td>",
			"<td width='40'><a href=\"{$siteurl}restore.php?operation=importzip&datafile_server=$info[filename]&importsubmit=yes\" onclick=\"return confirm('".lang('database_import_confirm_zip')."');\">".lang('db_import_unzip')."</a></td>"
		;
		echo "</tr>\n";
		echo '<tbody id="exportlog_'.$key.'" style="display:none">';
		foreach($val as $info) {
			$info['dateline'] = is_int($info['dateline']) ? gmdate('Y-m-d H:i:s', $info['dateline'] + 3600 * 8) : lang('unknown');
			$info['size'] = sizecount($info['size']);
			$info['method'] = $info['method'] == 'multivol' ? lang('db_multivol') : lang('db_zip');
			echo "<tr>";
			echo
				"<td colspan='3' class='subtb'><a href=\"$info[filename]\">".$info['filename']."</a></td>",
				"<td>".$info['dateline']."</td>",
				"<td>".lang('db_export_'.$info['type'])."</td>",
				"<td>".$info['size']."</td>",
				"<td colspan='3'>".$info['method']."</td>"
			;
			echo "</tr>\n";
		}
		echo '</tbody>';
	}

	echo "</table>\n";
	show_footer();

}

function submitcheck($var, $allowget = 0, $seccodecheck = 0, $secqaacheck = 0) {
	if(!getgpc($var)) {
		return FALSE;
	} else {
		if($allowget || ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
		preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
			return TRUE;
		} else {
			echo 'submit_invalid';exit;
		}
	}
}

function getgpc($k, $type='GP') {
	$type = strtoupper($type);
	switch($type) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		default:
			if(isset($_GET[$k])) {
				$var = &$_GET;
			} else {
				$var = &$_POST;
			}
			break;
	}

	return isset($var[$k]) ? $var[$k] : NULL;

}

function sizecount($size) {
	if($size >= 1073741824) {
		$size = round($size / 1073741824 * 100) / 100 . ' GB';
	} elseif($size >= 1048576) {
		$size = round($size / 1048576 * 100) / 100 . ' MB';
	} elseif($size >= 1024) {
		$size = round($size / 1024 * 100) / 100 . ' KB';
	} else {
		$size = $size . ' Bytes';
	}
	return $size;
}

function show_header() {
	ob_start();
	print <<< EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DzzOffice 数据恢复工具</title>
<style type="text/css">
* { word-break: break-all; }
body { text-align:center; margin:0; padding:0; background: #F5FBFF; font-size:12px; font-family:Verdana, Arial, Helvetica, 'SimSun', sans-serif; line-height: 1.8; }
.bodydiv { margin:40px auto 0; width:960px; text-align:left; border:solid #86B9D6; border-width:5px 1px 1px; background:#FFF; }
h1 { font-size:18px; margin:1px 0 0; line-height:50px; height:50px; background:#E8F7FC; color:#5086A5; padding-left:10px; }
	h1 span { font-weight:400; font-size:12px; }
.main { margin:0 auto; padding:20px; min-height:100px; }
#footer { font-size:10px; line-height:40px; background:#E8F7FC; text-align:center; height:38px; overflow:hidden; color:#5086A5; }
table { border-collapse:collapse; margin-bottom:20px; }
	th { text-align:left; color:#5086A5; }
	th, td { padding:5px 2px; border-bottom:1px dashed #DDD; }
	.subtb { padding-left:20px; }
.desc { margin-bottom:20px; }
.red{ color:red; }
</style>
</head>

<body>
<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}

	function showmessage(message) {
		document.getElementById('notice').innerHTML += message + '<br />';
	}

	function display(id) {
		var obj = $(id);
		if(obj.style.visibility) {
			obj.style.visibility = obj.style.visibility == 'visible' ? 'hidden' : 'visible';
		} else {
			obj.style.display = obj.style.display == '' ? 'none' : '';
		}
	}
	function redirect(url) {
		window.location = url;
		if($('confirmbtn')) {
			$('confirmbtn').disabled = !($('confirmbtn').disabled   &&   true);
		}
		if($('cancelbtn')) {
			$('cancelbtn').disabled = !($('cancelbtn').disabled   &&   true);
		}
	}
</script>

<div class="bodydiv">
	<h1>
		DzzOffice 数据恢复工具 2.0
		<span> &nbsp; 恢复当中有任何问题请访问技术支持站点 <a href="http://dev.dzzoffice.com" target="_blank">dev.dzzoffice.com</a></span>
	</h1>
	<div class="main">
EOT;
}

function show_footer($quit = true) {

	echo <<< EOT
	</div>
	<div id="footer">Copyright © 2012-2017 DzzOffice.com All Rights Reserved.</div>
</div>
</body>
</html>
EOT;
	ob_flush();
	exit();
}

function show_msg($message, $url_forward = '', $type = 'message', $success = 0) {
	global $siteurl;

	show_header();

	$message = lang($message);

	if($type == 'message') {
		echo '<span'.($success ? '' : ' class="red"').'>'.$message.'</span>';
	} elseif($type == 'redirect') {
		echo "$message ...";
		echo "<br /><br /><br /><a href=\"$url_forward\">浏览器会自动跳转页面，无需人工干预。除非当您的浏览器长时间没有自动跳转时，请点击这里</a>";
		echo "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script>";
	} elseif($type == 'confirm') {
		echo "$message";
		echo "<br /><br /><br /><button id=\"confirmbtn\" onclick=\"redirect('$url_forward')\">确定</button><button id=\"cancelbtn\" onclick=\"redirect('{$siteurl}restore.php')\">取消</button>";
	}

	show_footer();
}

function show_error($type, $errors = '', $quit = false) {

	$title = lang($type);
	$comment = lang($type.'_comment', false);
	$errormsg = '';
	if($errors) {
		if(!empty($errors)) {
			foreach ((array)$errors as $k => $v) {
				if(is_numeric($k)) {
					$comment .= "<li><em class=\"red\">".lang($v)."</em></li>";
				}
			}
		}
	}

	if($step > 0) {
		echo "<div class=\"desc\"><b>$title</b><ul>$comment</ul>";
	} else {
		echo "</div><div class=\"main\" style=\"margin-top: -123px;\"><b>$title</b><ul style=\"line-height: 200%; margin-left: 30px;\">$comment</ul>";
	}

	if($quit) {
		echo '<br /><span class="red">'.lang('error_quit_msg').'</span><br /><br /><br /><br /><br /><br />';
	}

	echo '</div>';

	$quit && show_footer();
}


function show_tips($tip, $title = '', $comment = '', $style = 1) {
	$title = empty($title) ? lang($tip) : $title;
	$comment = empty($comment) ? lang($tip.'_comment', FALSE) : $comment;
	if($style) {
		echo "<div class=\"desc\">$title";
	} else {
		echo "</div><div class=\"main\" style=\"margin-top: -123px;\">$title<div class=\"desc1 marginbot\"><ul>";
	}
	$comment && print('<br>'.$comment);
	echo "</div>";
}

function lang($lang_key, $force = true, $replace = array()) {
	$lang = array('filename' => '文件名称',
				'director' => '所在目录',
				'version' => '版本',
				'time' => '备份时间',
				'type' => '类型',
				'size' => '尺寸',
				'db_method' => '方式',
				'db_volume' => '卷数',
				'import' => '导入',
				'different_dbcharset_tablepre' => '检测到导入的备份数据与配置文件的{diff} 不同，您还要继续运行此程序吗？',
				'db_import_tips' => '本功能在恢复备份数据的同时，将全部覆盖原有数据，请确定恢复前已将平台关闭，恢复全部完成后可以将平台重新开放。<br />您可以通过数据备份管理功能查看站点的备份文件的详细信息，删除过期的备份,并导入需要的备份。<br /><span class="red">恢复数据的整个过程会在一个新页面完成，您成功恢复数据后请务必及时删除restore.php文件。</span><br />',
				'db_export_dzz' => 'Dzz! 数据',
				'db_export_dzz_uc' => 'Dzz! ',
				'db_multivol' => '多卷',
				'db_import_unzip' => '解压缩',
				'db_export_zip' => '压缩备份',
				'db_zip' => 'ZIP',
				'db_shell' => 'Shell',
				'unknown' => '未知',
				'backup_file_unexist' => '备份文件不存在',
				'connect_error' => '连接数据库失败，请您查看数据库配置文件core/config/config.php是否存在以及配置是否正确',
				'dbcharsetdiff' => ' 数据库字符集($_config[\'db\'][\'1\'][\'dbcharset\'])',
				'tableprediff' => ' 表前缀($_config[\'db\'][\'1\'][\'tablepre\'])',
				'database_import_multivol_succeed' => '分卷数据成功导入站点数据库<br />请在后台更新缓存<br /><span class="red">请尽快删除restore.php文件，以免对数据造成影响</span>',
				'database_import_file_illegal' => '数据文件不存在：可能服务器不允许上传文件或文件大小超过限制',
				'database_import_multivol_prompt' => '分卷数据第一卷成功导入数据库，您需要自动导入本次备份的其他分卷吗？',
				'database_import_succeed' => '数据已成功导入站点数据库<br />请在后台更新缓存<br /><span class="red">请尽快删除restore.php文件，以免对数据造成影响</span>',
				'database_import_format_illegal' => '数据文件非 Dzz! 格式，无法导入',
				'database_import_unzip' => '{info}<br />备份文件解压缩完毕，您需要自动导入备份吗？导入后解压缩的文件将会被删除',
				'database_import_multivol_unzip' => '{info}<br />备份文件解压缩完毕，您需要自动解压缩其他的分卷文件吗？',
				'database_import_multivol_unzip_redirect' => '数据文件 #{multivol} 解压缩成功，程序将自动继续',
				'database_import_confirm' => '导入和当前 Dzz! 版本不一致的数据极有可能产生无法解决的故障，您确定继续吗？',
				'database_import_confirm_sql' => '您确定导入该备份吗？',
				'database_import_confirm_zip' => '您确定解压该备份吗？',
				'database_import_multivol_confirm' => '所有分卷文件解压缩完毕，您需要自动导入备份吗？导入后解压缩的文件将会被删除',
				'database_import_multivol_redirect' => '数据文件 #{volume} 成功导入，程序将自动继续',
				'error_quit_msg' => '必须解决以上问题，才能继续恢复数据',
				'restored_error' => '恢复数据功能锁定，已经恢复过了，如果您确定要恢复数据，请到服务器上删除./data/restore.lock',
			);
	$return = isset($lang[$lang_key]) ? $lang[$lang_key] : ($force ? $lang_key : '');
	if($replace && is_array($replace)) {
		$searchs = $replaces = array();
		foreach($replace as $k => $v) {
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
		$return = str_replace($searchs, $replaces, $return);
	}
	return $return;
}

function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return($ret);
}

function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

class dbstuff {
	var $querynum = 0;
	var $drivertype = 'mysql';
	var $link;
	var $histories;
	var $time;
	var $tablepre;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset, $pconnect = 0, $tablepre='', $time = 0) {
		$this->time = $time;
		$this->tablepre = $tablepre;
		if($pconnect) {
			if(!$this->link = mysql_pconnect($dbhost, $dbuser, $dbpw)) {
				return FALSE;
			}
		} else {
			if(!$this->link = mysql_connect($dbhost, $dbuser, $dbpw, 1)) {
				return FALSE;
			}
		}

		if($this->version() > '4.1') {
			if($dbcharset) {
				mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary", $this->link);
			}

			if($this->version() > '5.0.1') {
				mysql_query("SET sql_mode=''", $this->link);
			}
		}

		if($dbname) {
			mysql_select_db($dbname, $this->link);
		}
		return TRUE;
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function result_first($sql, &$data) {
		$query = $this->query($sql);
		$data = $this->result($query, 0);
	}

	function fetch_first($sql, &$arr) {
		$query = $this->query($sql);
		$arr = $this->fetch_array($query);
	}

	function fetch_all($sql, &$arr) {
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$arr[] = $data;
		}
	}

	function cache_gc() {
		$this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
	}

	function query($sql, $type = '', $cachetime = FALSE) {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->link)) && $type != 'SILENT') {
			$this->halt('SQL:', $sql);
		}
		$this->querynum++;
		$this->histories[] = $sql;
		return $query;
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function error() {
		return (($this->link) ? mysql_error($this->link) : mysql_error());
	}

	function errno() {
		return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
	}

	function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function num_fields($query) {
		return mysql_num_fields($query);
	}

	function free_result($query) {
		return mysql_free_result($query);
	}

	function insert_id() {
		return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	function version() {
		return mysql_get_server_info($this->link);
	}

	function close() {
		return mysql_close($this->link);
	}

	function halt($message = '', $sql = '') {
		show_error('run_sql_error', $message.$sql.'<br /> Error:'.$this->error().'<br />Errno:'.$this->errno(), 0);
	}
}

class dbstuffi {
	var $querynum = 0;
	var $drivertype = 'mysqli';
	var $link;
	var $histories;
	var $time;
	var $tablepre;

	function connect($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset, $pconnect = 0, $tablepre='', $time = 0) {
		$this->time = $time;
		$this->tablepre = $tablepre;
		$this->link = new mysqli();
		if(!$this->link->real_connect($dbhost, $dbuser, $dbpw, $dbname)) {
			return FALSE;
		}

		if($this->version() > '4.1') {
			if($dbcharset) {
				$this->link->set_charset($dbcharset);
			}

			if($this->version() > '5.0.1') {
				$this->query("SET sql_mode=''");
			}
		}

		return TRUE;
	}

	function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		return $query ? $query->fetch_array($result_type) : null;
	}

	function result_first($sql, &$data) {
		$query = $this->query($sql);
		$data = $this->result($query, 0);
	}

	function fetch_first($sql, &$arr) {
		$query = $this->query($sql);
		$arr = $this->fetch_array($query);
	}

	function fetch_all($sql, &$arr) {
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$arr[] = $data;
		}
	}

	function cache_gc() {
		$this->query("DELETE FROM {$this->tablepre}sqlcaches WHERE expiry<$this->time");
	}

	function query($sql, $type = '', $cachetime = FALSE) {
		$resultmode = $type == 'UNBUFFERED' ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;
		if(!($query = $this->link->query($sql, $resultmode)) && $type != 'SILENT') {
			$this->halt('SQL:', $sql);
		}
		$this->querynum++;
		$this->histories[] = $sql;
		return $query;
	}

	function affected_rows() {
		return $this->link->affected_rows;
	}

	function error() {
		return (($this->link) ? $this->link->error : mysqli_error());
	}

	function errno() {
		return intval(($this->link) ? $this->link->errno : mysqli_errno());
	}

	function result($query, $row) {
		if(!$query || $query->num_rows == 0) {
			return null;
		}
		$query->data_seek($row);
		$assocs = $query->fetch_row();
		return $assocs[0];
	}

	function num_rows($query) {
		$query = $query ? $query->num_rows : 0;
		return $query;
	}

	function num_fields($query) {
		return $query ? $query->field_count : 0;
	}

	function free_result($query) {
		return $query ? $query->free() : false;
	}

	function insert_id() {
		return ($id = $this->link->insert_id) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_row($query) {
		$query = $query ? $query->fetch_row() : null;
		return $query;
	}

	function fetch_fields($query) {
		return $query ? $query->fetch_field() : null;
	}

	function version() {
		return $this->link->server_info;
	}

	function close() {
		return $this->link->close();
	}

	function halt($message = '', $sql = '') {
		show_error('run_sql_error', $message.$sql.'<br /> Error:'.$this->error().'<br />Errno:'.$this->errno(), 0);
	}
}
?>