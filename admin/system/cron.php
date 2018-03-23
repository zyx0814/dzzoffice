<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
include_once libfile('function/cache');

//error_reporting(E_ALL);
$op = $_GET['op'];
$navtitle = lang('cron') . ' - ' . lang('admin_navtitle');
if (empty($_GET['edit']) && empty($_GET['run'])) {

	if (!submitcheck('cronssubmit')) {
		$crons = array();
		$query = DB::query("SELECT * FROM " . DB::table('cron') . " ORDER BY type DESC");
		while ($cron = DB::fetch($query)) {
			$disabled = $cron['weekday'] == -1 && $cron['day'] == -1 && $cron['hour'] == -1 && $cron['minute'] == '' ? 'disabled' : '';

			if ($cron['day'] > 0 && $cron['day'] < 32) {
				$cron['time'] = lang('monthly') . $cron['day'] . lang('day');
			} elseif ($cron['weekday'] >= 0 && $cron['weekday'] < 7) {
				$cron['time'] = lang('weekly') . lang('misc_cron_week_day_' . $cron['weekday']);
			} elseif ($cron['hour'] >= 0 && $cron['hour'] < 24) {
				$cron['time'] = lang('everyday');
			} else {
				$cron['time'] = lang('per_hour');
			}
			$cron['time'] .= $cron['hour'] >= 0 && $cron['hour'] < 24 ? sprintf('%02d', $cron[hour]) . lang('timeliness') : '';
			if (!in_array($cron['minute'], array(-1, ''))) {
				foreach ($cron['minute'] = explode("\t", $cron['minute']) as $k => $v) {
					$cron['minute'][$k] = sprintf('%02d', $v);
				}
				$cron['minute'] = implode(',', $cron['minute']);
				$cron['time'] .= $cron['minute'] . lang('point');
			} else {
				$cron['time'] .= '00' . lang('point');
			}

			$cron['lastrun'] = $cron['lastrun'] ? dgmdate($cron['lastrun'], $_G['setting']['dateformat'] . "<\b\\r />" . $_G['setting']['timeformat']) : '<b>N/A</b>';
			$cron['nextcolor'] = $cron['nextrun'] && $cron['nextrun'] + $_G['setting']['timeoffset'] * 3600 < TIMESTAMP ? 'style="color: #ff0000"' : '';
			$cron['nextrun'] = $cron['nextrun'] ? dgmdate($cron['nextrun'], $_G['setting']['dateformat'] . "<\b\\r />" . $_G['setting']['timeformat']) : '<b>N/A</b>';
			$cron['run'] = $cron['available'];

			$crons[] = $cron;
		}
	} else {

		if ($ids = dimplode($_GET['delete'])) {
			DB::delete('cron', "cronid IN ($ids) AND type!='system'");
		}

		if (is_array($_GET['namenew'])) {
			foreach ($_GET['namenew'] as $id => $name) {
				$newcron = array('name' => dhtmlspecialchars($_GET['namenew'][$id]), 'available' => $_GET['availablenew'][$id]);
				if (empty($_GET['availablenew'][$id])) {
					$newcron['nextrun'] = '0';
				}
				DB::update('cron', $newcron, "cronid='{$id}'");
			}
		}

		if ($newname = trim($_GET['newname'])) {
			DB::insert('cron', array('name' => dhtmlspecialchars($newname), 'type' => 'user', 'available' => '0', 'weekday' => '-1', 'day' => '-1', 'hour' => '-1', 'minute' => '', 'nextrun' => $_G['timestamp'], ));
		}

		$query = DB::query("SELECT cronid, filename FROM " . DB::table('cron'));
		while ($cron = DB::fetch($query)) {
			$efile = explode(':', $cron['filename']);
			if (count($efile) > 1) {
				$filename = array_pop($efile);
				$cronfile =  DZZ_ROOT. ''.implode("/",$efile).'/cron/'.$filename; 
			} else {
				$cronfile = DZZ_ROOT . './core/cron/' . $cron['filename'];
			}
			if (!file_exists($cronfile)) {
				DB::update('cron', array('available' => '0', 'nextrun' => '0', ), "cronid='$cron[cronid]'");
			}
		}
		updatecache('setting');
		$msg = lang('crons_succeed');
		$redirecturl = BASESCRIPT . '?mod=system&op=cron';
		$msg_type = 'text-success';
	}

} else {

	$cronid = empty($_GET['run']) ? $_GET['edit'] : $_GET['run'];
	$cron = DB::fetch_first("SELECT * FROM " . DB::table('cron') . " WHERE cronid='$cronid'");
	if (!$cron) {
		$msg = lang('cron_not_found');
		$redirecturl = BASESCRIPT . '?mod=system&op=cron';
		$msg_type = 'text-error';
		include template('cron');
		exit();
	}
	$cron['filename'] = str_replace(array('..', '/', '\\'), array('', '', ''), $cron['filename']);
	$cronminute = str_replace("\t", ',', $cron['minute']);
	$cron['minute'] = explode("\t", $cron['minute']);

	if (!empty($_GET['edit'])) {

		if (!submitcheck('editsubmit')) {

			$navtitle = lang('misc_cron_edit') . ' - ' . lang('admin_navtitle');

			$weekdayselect = $dayselect = $hourselect = '';

			for ($i = 0; $i <= 6; $i++) {
				$weekdayselect .= "<option value=\"$i\" " . ($cron['weekday'] == $i ? 'selected' : '') . ">" . lang('misc_cron_week_day_' . $i) . "</option>";
			}

			for ($i = 1; $i <= 31; $i++) {
				$dayselect .= "<option value=\"$i\" " . ($cron['day'] == $i ? 'selected' : '') . ">$i " . lang('day') . "</option>";
			}

			for ($i = 0; $i <= 23; $i++) {
				$hourselect .= "<option value=\"$i\" " . ($cron['hour'] == $i ? 'selected' : '') . ">$i " . lang('timeliness') . "</option>";
			}

		} else {

			$daynew = $_GET['weekdaynew'] != -1 ? -1 : $_GET['daynew'];
			if (strpos($_GET['minutenew'], ',') !== FALSE) {
				$minutenew = explode(',', $_GET['minutenew']);
				foreach ($minutenew as $key => $val) {
					$minutenew[$key] = $val = intval($val);
					if ($val < 0 || $var > 59) {
						unset($minutenew[$key]);
					}
				}
				$minutenew = array_slice(array_unique($minutenew), 0, 12);
				$minutenew = implode("\t", $minutenew);
			} else {
				$minutenew = intval($_GET['minutenew']);
				$minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
			}

			$msg = '';
			$_GET['filenamenew'] = str_replace(array('..', '/', '\\'), '', $_GET['filenamenew']);
			$efile = explode(':', $_GET['filenamenew']);
			if (count($efile) > 1) {
				$filename = array_pop($efile);
				$cronfile =  DZZ_ROOT. ''.implode("/",$efile).'/cron/'.$filename; 
			} else {
				$cronfile = DZZ_ROOT . './core/cron/' . $cron['filename'];
			}
			if (preg_match("/[\\\\\/\*\?\"\<\>\|]+/", $_GET['filenamenew'])) {
				$msg = lang('crons_filename_illegal');
			} elseif (!is_readable($cronfile)) {
				$msg = lang('crons_filename_invalid', array('cronfile' => $cronfile));
			} elseif ($_GET['weekdaynew'] == -1 && $daynew == -1 && $_GET['hournew'] == -1 && $minutenew === '') {
				$msg = lang('crons_time_invalid');
			}
			if (!empty($msg)) {
				$msg_type = 'text-error';
				$redirecturl = dreferer();
				include template('cron');
				exit();
			}

			DB::update('cron', array('weekday' => $_GET['weekdaynew'], 'day' => $daynew, 'hour' => $_GET['hournew'], 'minute' => $minutenew, 'filename' => trim($_GET['filenamenew']), ), "cronid='$cronid'");

			dzz_cron::run($cronid);

			$msg = lang('crons_succeed');
			$msg_type = 'text-success';
			$redirecturl = BASESCRIPT . '?mod=system&op=cron';
		}

	} else {

		$cron['filename'] = str_replace(array('..', '/', '\\'), '', $cron['filename']);
		$efile = explode(':', $cron['filename']);
		if (count($efile) > 1) {
			$filename = array_pop($efile);
			$cronfile =  DZZ_ROOT. ''.implode("/",$efile).'/cron/'.$filename; 
		} else {
			$cronfile = DZZ_ROOT . './core/cron/' . $cron['filename'];
		}

		if (!file_exists($cronfile)) {
			$msg = lang('crons_run_invalid', array('cronfile' => $cronfile));
			$msg_type = 'text-error';

		} else {
			dzz_cron::run($cron['cronid']);
			$msg = lang('crons_run_succeed');
			$redirecturl = BASESCRIPT . '?mod=system&op=cron';
			$msg_type = 'text-success';
		}
	}
}
include template('cron');
?>
