<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_cron
{
	public static function run($cronid = 0) {
		global $_G;
		$cron = $cronid ? C::t('cron')->fetch($cronid) : C::t('cron')->fetch_nextrun(TIMESTAMP);
		$processname ='DZZ_CRON_'.(empty($cron) ? 'CHECKER' : $cron['cronid']);
		if($cronid && !empty($cron)) {
			dzz_process::unlock($processname);
		}
		if(dzz_process::islocked($processname, 600)) {
			return false;
		}
		if($cron) {
			$cron['filename'] = str_replace(array('..', '/', '\\'), '', $cron['filename']);
			$efile = explode(':', $cron['filename']);
			if(count($efile) > 1) {
				$filename = array_pop($efile);
				$cronfile =  DZZ_ROOT. './'.implode("/",$efile).'/cron/'.$filename; 
			} else {
				$cronfile = DZZ_ROOT.'./core/cron/'.$cron['filename'];
			}
			if($cronfile) {
				$cron['minute'] = explode("\t", $cron['minute']);
				self::setnextime($cron);

				@set_time_limit(1000);
				@ignore_user_abort(TRUE);

				if(!@include $cronfile) {
					return false;
				}
			}
		}
		self::nextcron();
		dzz_process::unlock($processname);
		return true;
	}
	private static function nextcron() {
		$cron = C::t('cron')->fetch_nextcron();
		if($cron && isset($cron['nextrun'])) {
			savecache('cronnextrun', $cron['nextrun']);
		} else {
			savecache('cronnextrun', TIMESTAMP + 86400 * 365);
		}
		return true;
	}
	private static function setnextime($cron) {
		if(empty($cron)) return FALSE;
		list($yearnow, $monthnow, $daynow, $weekdaynow, $hournow, $minutenow) = explode('-', gmdate('Y-m-d-w-H-i', TIMESTAMP + getglobal('setting/timeoffset') * 3600));
		if($cron['weekday'] == -1) {
			if($cron['day'] == -1) {
				$firstday = $daynow;
				$secondday = $daynow + 1;
			} else {
				$firstday = $cron['day'];
				$secondday = $cron['day'] + gmdate('t', TIMESTAMP + getglobal('setting/timeoffset') * 3600);
			}
		} else {
			$firstday = $daynow + ($cron['weekday'] - $weekdaynow);
			$secondday = $firstday + 7;
		}
		if($firstday < $daynow) {
			$firstday = $secondday;
		}
		if($firstday == $daynow) {
			$todaytime = self::todaynextrun($cron);
			if($todaytime['hour'] == -1 && $todaytime['minute'] == -1) {
				$cron['day'] = $secondday;
				$nexttime = self::todaynextrun($cron, 0, -1);
				$cron['hour'] = $nexttime['hour'];
				$cron['minute'] = $nexttime['minute'];
			} else {
				$cron['day'] = $firstday;
				$cron['hour'] = $todaytime['hour'];
				$cron['minute'] = $todaytime['minute'];
			}
		} else {
			$cron['day'] = $firstday;
			$nexttime = self::todaynextrun($cron, 0, -1);
			$cron['hour'] = $nexttime['hour'];
			$cron['minute'] = $nexttime['minute'];
		}
		$nextrun = @gmmktime($cron['hour'], $cron['minute'] > 0 ? $cron['minute'] : 0, 0, $monthnow, $cron['day'], $yearnow) - getglobal('setting/timeoffset') * 3600;
		$data = array('lastrun' => TIMESTAMP, 'nextrun' => $nextrun);
		if(!($nextrun > TIMESTAMP)) {
			$data['available'] = '0';
		}
		C::t('cron')->update($cron['cronid'], $data);

		return true;
	}

	private static function todaynextrun($cron, $hour = -2, $minute = -2) {

		$hour = $hour == -2 ? gmdate('H', TIMESTAMP + getglobal('setting/timeoffset') * 3600) : $hour;
		$minute = $minute == -2 ? gmdate('i', TIMESTAMP + getglobal('setting/timeoffset') * 3600) : $minute;

		$nexttime = array();
		if($cron['hour'] == -1 && !$cron['minute']) {
			$nexttime['hour'] = $hour;
			$nexttime['minute'] = $minute + 1;
		} elseif($cron['hour'] == -1 && $cron['minute'] != '') {
			$nexttime['hour'] = $hour;
			if(($nextminute = self::nextminute($cron['minute'], $minute)) === false) {
				++$nexttime['hour'];
				$nextminute = $cron['minute'][0];
			}
			$nexttime['minute'] = $nextminute;
		} elseif($cron['hour'] != -1 && $cron['minute'] == '') {
			if($cron['hour'] < $hour) {
				$nexttime['hour'] = $nexttime['minute'] = -1;
			} elseif($cron['hour'] == $hour) {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $minute + 1;
			} else {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = 0;
			}
		} elseif($cron['hour'] != -1 && $cron['minute'] != '') {
			$nextminute = self::nextminute($cron['minute'], $minute);
			if($cron['hour'] < $hour || ($cron['hour'] == $hour && $nextminute === false)) {
				$nexttime['hour'] = -1;
				$nexttime['minute'] = -1;
			} else {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $nextminute;
			}
		}
		return $nexttime;
	}
	private static function nextminute($nextminutes, $minutenow) {
		foreach($nextminutes as $nextminute) {
			if($nextminute > $minutenow) {
				return $nextminute;
			}
		}
		return false;
	}
}
?>