<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}

$page=max(1,intval($_GET['page']));
$lpp = empty($_GET['lpp']) ? 20 : $_GET['lpp'];
$checklpp = array();
$checklpp[$lpp] = 'selected="selected"';
$extrainput = '';

$operation = in_array($_GET['operation'], array('illegal', 'cp','error',  'sendmail')) ? $_GET['operation'] : 'illegal';
$navtitle=lang('nav_logs_'.$operation).' - '.lang('admin_navtitle');

$logdir = DZZ_ROOT.'./data/log/';
$logfiles = get_log_files($logdir, $operation.($operation == 'sendmail' ? '' : 'log'));
$logs = array();
$lastkey = count($logfiles) - 1;
$lastlog = $logfiles[$lastkey];
krsort($logfiles);
if($logfiles) {
	if(!isset($_GET['day']) || strexists($_GET['day'], '_')) {
		list($_GET['day'], $_GET['num']) = explode('_', $_GET['day']);
		$logs = file(($_GET['day'] ? $logdir.$_GET['day'].'_'.$operation.($operation == 'sendmail' ? '' : 'log').($_GET['num'] ? '_'.$_GET['num'] : '').'.php' : $logdir.$lastlog));
	} else {
		$logs = file($logdir.$_GET['day'].'_'.$operation.($operation == 'sendmail' ? '' : 'log').'.php');
	}
}

$start = ($page - 1) * $lpp;
$logs = array_reverse($logs);

if(empty($_GET['keyword']) && empty($_GET['filteract'])) {
	$num = count($logs);
	$multipage = multi($num, $lpp, $page, ADMINSCRIPT."?mod=system&op=log&operation=$operation&lpp=$lpp".(!empty($_GET['day']) ? '&day='.$_GET['day'] : ''), 0, 3);
	$logs = array_slice($logs, $start, $lpp);
} else {
	foreach($logs as $key => $value) {
		if(!empty($_GET['filteract'])) {
			$log = explode("\t", $value);
			preg_match("/operation=(.[^;]*)/i", $log[6], $operationInfo);
			$logExplain = $operationInfo[1] ? rtrim($log[5]).'_'.$operationInfo[1] : rtrim($log[5]) ;
			$logPostion = strpos($logExplain, $_GET['filteract']);
			if($logPostion === false || $logPostion != 0) {
				unset($logs[$key]);
			}
		}
		if(!empty($_GET['keyword']) && strpos($value, $_GET['keyword']) === FALSE) {
			unset($logs[$key]);
		}
	}
	$multipage = '';
}


$usergroup = array();

if(in_array($operation, array( 'cp'))) {
	foreach(C::t('usergroup')->range() as $group) {
		$usergroup[$group['groupid']] = $group['grouptitle'];
	}
}
if($logfiles) {
	$sel = '<select class="form-control input-sm"  onchange="location.href=\''.BASESCRIPT.'?mod=system&op=log&operation='.$operation.'&keyword='.$_GET['keyword'].'&day=\'+this.value">';
	foreach($logfiles as $logfile) {
		list($date, $logtype, $num) = explode('_', $logfile);
		if(is_numeric($date)) {
			$num = intval($num);
			$sel .= '<option value="'.$date.'_'.$num.'"'.($date.'_'.$num == $_GET['day'].'_'.intval($_GET['num']) ? ' selected="selected"' : '').'>'.($num ? '&nbsp;&nbsp;'.$date.''.$num : $date).'</option>';
		} else {
			list($logtype) = explode('.', $logtype);
			$sel .= '<option value="'.$logtype.'"'.($logtype == $_GET['day'] ? ' selected="selected"' : '').'>'.$logtype.'</option>';
		}
	}
	$sel .= '</select>';
} else {
	$sel = '';
}

$filters = '';
$list=array();
if($operation == 'illegal') {
	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		if(strtolower($log[2]) == strtolower($_G['member']['username'])) {
			$log[2] = "<b>$log[2]</b>";
		}
		$log[5] = $log[5];
		$list[]=$log;
	}
} elseif($operation == 'sendmail') {


	$logarr = $logemail = array();
	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[5] = trim(str_replace('sendmail failed.', '', $log[5]));
		if(!$log[5]) {
			continue;
		}
		$logemail[] = $log[5];
		$logarr[] = $log;
	}

	$members = C::t('user')->fetch_all_by_email($logemail);

	foreach($logarr as $log) {
		$log[6] = $members[$log[5]]['username'];
		if(strtolower($log[6]) == strtolower($_G['member']['username'])) {
			$log[6] = "<b>$log[6]</b>";
		}
		$list[]=$log;
		
	}

} elseif($operation == 'cp') {
	
	foreach($logs as $k => $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			//continue;
		}
		$log[1] = dgmdate($log[1], 'y-n-j H:i');
		$log[2] = $log[2];
		$log[2] = ($log[2] != $_G['member']['username'] ? "<b>$log[2]</b>" : $log[2]);
		$log[3] = $usergroup[$log[3]];
 		
		$list[$k]=$log;
	}
} elseif($operation == 'error') {

	foreach($logs as $logrow) {
		$log = explode("\t", $logrow);
		if(empty($log[1])) {
			continue;
		}
		$log[1]=dgmdate($log[1], 'Y-m-d H:i:s');
		$log[2]=$log[2].'<br>'.$log[4].'<br>'.$log[5];
		$list[]=$log;
	}

} 
function getactionarray() {
	$isfounder = true;
	//require './source/admincp/admincp_menu.php';
	//require './source/admincp/admincp_perm.php';
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


include template('log');

?>
