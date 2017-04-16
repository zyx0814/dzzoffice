<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */


!defined('IN_DZZ') && exit('Access Denied');

define('PMINBALCKLIST_ERROR', -6);
define('PMSENDSELF_ERROR', -8);
define('PMSENDNONE_ERROR', -9);
define('PMSENDCHATNUM_ERROR', -10);
define('PMTHREADNONE_ERROR', -11);
define('PMPRIVILEGENONE_ERROR', -12);
define('PMCHATTYPE_ERROR', -13);
define('PMUIDTYPE_ERROR', -14);
define('PMDATA_ERROR', -15);

class dzz_pm {

	function pmintval($pmid) {
		return @is_numeric($pmid) ? $pmid : 0;
	}

	function getpmbypmid($uid, $pmid) {
		if(!$pmid) {
			return array();
		}
		$arr = array();
		$pm = DB::fetch_first("SELECT * FROM ".DB::table('pm_indexes')." i LEFT JOIN ".DB::table('pm_lists')." t ON t.plid=i.plid WHERE i.pmid='$pmid'");
		if($this->isprivilege($pm['plid'], $uid)) {
			$pms = DB::fetch_all("SELECT t.*, p.*, t.authorid as founderuid, t.dateline as founddateline FROM ".DB::table($this->getposttablename($pm['plid']))." p LEFT JOIN ".DB::table('pm_lists')." t ON t.plid=p.plid WHERE p.pmid='$pm[pmid]'");
			$arr = $this->getpostlist($pms);
		}
		return $arr;
	}

	function isprivilege($plid, $uid) {
		if(!$plid || !$uid) {
			return true;
		}
		$query = DB::query("SELECT * FROM ".DB::table('pm_members')." WHERE plid='$plid' AND uid='$uid'");
		if(DB::fetch_array($query)) {
			return true;
		} else {
			return false;
		}
	}

	function getpmbyplid($uid, $plid, $starttime, $endtime, $start, $ppp, $type = 0) {
		if(!$type) {
			$pm = $this->getprivatepmbyplid($uid, $plid, $starttime, $endtime, $start, $ppp);
		} else {
			$pm = $this->getchatpmbyplid($uid, $plid, $starttime, $endtime, $start, $ppp);
		}
		return $this->getpostlist($pm);
	}

	function getpostlist($list) {
		if(empty($list)) {
			return array();
		}
		$authoridarr = $authorarr = array();
		foreach($list as $key => $value) {
			$authoridarr[$value['authorid']] = $value['authorid'];
		}
		if($authoridarr) {
			$authorarr = self::id2name($authoridarr);
		}
		foreach($list as $key => $value) {
			if($value['pmtype'] == 1) {
				$users = explode('_', $value['min_max']);
				if($value['authorid'] == $users[0]) {
					$value['touid'] = $users[1];
				} else {
					$value['touid'] = $users[0];
				}
			} else {
				$value['touid'] = 0;
			}
			$value['author'] = $authorarr[$value['authorid']];

			$value['msgfromid'] = $value['authorid'];
			$value['msgfrom'] = $value['author'];
			$value['msgtoid'] = $value['touid'];

			unset($value['min_max']);
			unset($value['delstatus']);
			unset($value['lastmessage']);
			$list[$key] = $value;
		}
		return $list;
	}

	function setpmstatus($uid, $touids, $plids, $status = 0) {
		if(!$uid) {
			return false;
		}
		if(!$status) {
			$oldstatus = 1;
			$newstatus = 0;
		} else {
			$oldstatus = 0;
			$newstatus = 1;
		}
		if($touids) {
			foreach($touids as $key => $value) {
				if($uid == $value || !$value) {
					return false;
				}
				$relastionship[] = $this->relationship($uid, $value);
			}
			$plid = $plidpostarr = array();
			$query = DB::query("SELECT plid FROM ".DB::table('pm_lists')." WHERE min_max IN (".dimplode($relationship).")");
			while($thread = DB::fetch_array($query)) {
				$plidarr[] = $thread['plid'];
			}
			if($plidarr) {
				DB::query("UPDATE ".DB::table('pm_members')." SET isnew='$newstatus' WHERE plid IN (".dimplode($plidarr).") AND uid='$uid' AND isnew='$oldstatus'");
			}
		}
		if($plids) {
			DB::query("UPDATE ".DB::table('pm_members')." SET isnew='$newstatus' WHERE plid IN (".dimplode($plids).") AND uid='$uid' AND isnew='$oldstatus'");
		}
		return true;
	}

	function set_ignore($uid) {
		return DB::query("DELETE FROM ".DB::table('newpm')." WHERE uid='$uid'");
	}

	function isnewpm($uid) {
		return DB::result_first("SELECT COUNT(*) FROM ".DB::table('newpm')." WHERE uid='$uid'");
	}

	function lastpm($uid) {
		$lastpm = DB::fetch_first("SELECT * FROM ".DB::table('pm_members')." m LEFT JOIN ".DB::table('pm_lists')." t ON m.plid=t.plid WHERE m.uid='$uid' ORDER BY m.lastdateline DESC LIMIT 1");
		$lastmessage = unserialize($lastpm['lastmessage']);
		if($lastmessage['lastauthorid']) {
			$lastpm['lastauthorid'] = $lastmessage['lastauthorid'];
			$lastpm['lastauthor'] = $lastmessage['lastauthor'];
			$lastpm['lastsummary'] = $lastmessage['lastsummary'];
		} else {
			$lastpm['lastauthorid'] = $lastmessage['firstauthorid'];
			$lastpm['lastauthor'] = $lastmessage['firstauthor'];
			$lastpm['lastsummary'] = $lastmessage['firstsummary'];
		}
		return $lastpm;
	}

	function getpmnum($uid, $type = 0, $isnew = 0) {
		$newsql = '';
		$newnum = 0;

		if($isnew) {
			$newsql = 'AND m.isnew=1';
		}
		if(!$type) {
			$newnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pm_members')." m WHERE m.uid='$uid' $newsql");
		} else {
			$newnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pm_members')." m LEFT JOIN ".DB::table('pm_lists')." t ON t.plid=m.plid WHERE m.uid='$uid' $newsql AND t.pmtype='$type'");
		}
		return $newnum;
	}

	function getpmnumbyplid($uid, $plid) {
		return DB::result_first("SELECT pmnum FROM ".DB::table('pm_members')." WHERE plid='$plid' AND uid='$uid'");
	}

	function sendpm($fromuid, $fromusername, $touids, $subject, $message, $type = 0) {
		if(!$fromuid || !$fromusername || !$touids || !$message) {
			return 0;
		}
		$touids = array_unique($touids);
		$relationship = $existplid = $pm_member_insertsql = array();
		//$this->base->load('user');
		$tmptouidarr = $touids;
		//$blackls = $this->get_blackls($fromuid, $touids);

		foreach($tmptouidarr as $key => $value) {
			if($fromuid == $value || !$value) {
				return PMSENDSELF_ERROR;
			}

			/*if(in_array('{ALL}', $blackls[$value])) {
				unset($touids[$key]);
				continue;
			}
			$blackls[$value] = $_ENV['user']->name2id($blackls[$value]);
			if(!(isset($blackls[$value]) && !in_array($fromuid, $blackls[$value]))) {
				unset($touids[$key]);
			} else {
				$relationship[$value] = $this->relationship($fromuid, $value);
			}*/
		}
		if(empty($touids)) {
			return PMSENDNONE_ERROR;
		}
		if($type == 1 && count($touids) < 2) {
			return PMSENDCHATNUM_ERROR;
		}

		if($_G['setting']['badwords']['findpattern']) {
			$subject = @preg_replace($_G['setting']['badwords']['findpattern'], $_G['setting']['badwords']['replace'], $subject);
			$message = @preg_replace($_G['setting']['badwords']['findpattern'], $_G['setting']['badwords']['replace'], $message);
		}
		if(!$subject) {
			$subject = $this->removecode(trim($message), 80);
		} else {
			$subject = htmlspecialchars($subject);
		}
		$lastsummary = $this->removecode(trim($message), 150);

		if(!$type) {
			$query = DB::query("SELECT plid, min_max FROM ".DB::table('pm_lists')." WHERE min_max IN (".dimplode($relationship).")");
			while($thread = DB::fetch_array($query)) {
				$existplid[$thread['min_max']] = $thread['plid'];
			}
			$lastmessage = array('lastauthorid' => $fromuid, 'lastauthor' => $fromusername, 'lastsummary' => $lastsummary);
			$lastmessage = addslashes(serialize($lastmessage));
			foreach($relationship as $key => $value) {
				if(!isset($existplid[$value])) {
					DB::query("INSERT INTO ".DB::table('pm_lists')."(authorid, pmtype, subject, members, min_max, dateline, lastmessage) VALUES('$fromuid', '1', '$subject', 2, '$value', '".TIMPSTAMP."', '$lastmessage')");
					$plid = DB::insert_id();
					DB::query("INSERT INTO ".DB::table('pm_indexes')."(plid) VALUES('$plid')");
					$pmid = DB::insert_id();
					DB::query("INSERT INTO ".DB::table($this->getposttablename($plid))."(pmid, plid, authorid, message, dateline, delstatus) VALUES('$pmid', '$plid', '$fromuid', '$message', '".TIMPSTAMP."', 0)");
					DB::query("INSERT INTO ".DB::table('pm_members')."(plid, uid, isnew, pmnum, lastupdate, lastdateline) VALUES('$plid', '$key', '1', '1', '0', '".TIMPSTAMP."')");
					DB::query("INSERT INTO ".DB::table('pm_members')."(plid, uid, isnew, pmnum, lastupdate, lastdateline) VALUES('$plid', '$fromuid', '0', '1', '".TIMPSTAMP."', '".TIMPSTAMP."')");
				} else {
					$plid = $existplid[$value];
					DB::query("INSERT INTO ".DB::table('pm_indexes')."(plid) VALUES('$plid')");
					$pmid = DB::insert_id();
					DB::query("INSERT INTO ".DB::table($this->getposttablename($plid))."(pmid, plid, authorid, message, dateline, delstatus) VALUES('$pmid', '$plid', '$fromuid', '$message', '".TIMPSTAMP."', 0)");
					$result = DB::query("INSERT INTO ".DB::table('pm_members')."(plid, uid, isnew, pmnum, lastupdate, lastdateline) VALUES('$plid', '$key', '1', '1', '0', '".TIMPSTAMP."')", 'SILENT');
					if(!$result) {
						DB::query("UPDATE ".DB::table('pm_members')." SET isnew=1, pmnum=pmnum+1, lastdateline='".TIMPSTAMP."' WHERE plid='$plid' AND uid='$key'");
					}
					$result = DB::query("INSERT INTO ".DB::table('pm_members')."(plid, uid, isnew, pmnum, lastupdate, lastdateline) VALUES('$plid', '$fromuid', '0', '1', '".TIMPSTAMP."', '".TIMPSTAMP."')", 'SILENT');
					if(!$result) {
						DB::query("UPDATE ".DB::table('pm_members')." SET isnew=0, pmnum=pmnum+1, lastupdate='".TIMPSTAMP."', lastdateline='".TIMPSTAMP."' WHERE plid='$plid' AND uid='$fromuid'");
					}
					DB::query("UPDATE ".DB::table('pm_lists')." SET lastmessage='$lastmessage' WHERE plid='$plid'");
				}
			}
		} else {
			$lastmessage = array('firstauthorid' => $fromuid, 'firstauthor' => $fromusername, 'firstsummary' => $lastsummary);
			$lastmessage = addslashes(serialize($lastmessage));
			DB::query("INSERT INTO ".DB::table('pm_lists')."(authorid, pmtype, subject, members, min_max, dateline, lastmessage) VALUES('$fromuid', '2', '$subject', '".(count($touids)+1)."', '', '".TIMPSTAMP."', '$lastmessage')");
			$plid = DB::insert_id();
			DB::query("INSERT INTO ".DB::table('pm_indexes')."(plid) VALUES('$plid')");
			$pmid = DB::insert_id();
			DB::query("INSERT INTO ".DB::table($this->getposttablename($plid))."(pmid, plid, authorid, message, dateline, delstatus) VALUES('$pmid', '$plid', '$fromuid', '$message', '".TIMPSTAMP."', 0)");
			$pm_member_insertsql[] = "('$plid', '$fromuid', '0', '1', '".TIMPSTAMP."', '".TIMPSTAMP."')";
			foreach($touids as $key => $value) {
				$pm_member_insertsql[] = "('$plid', '$value', '1', '1', '0', '".TIMPSTAMP."')";
			}
			DB::query("INSERT INTO ".DB::table('pm_members')."(plid, uid, isnew, pmnum, lastupdate, lastdateline) VALUES ".implode(',', $pm_member_insertsql));
		}

		$newpm = array();
		foreach($touids as $key => $value) {
			$newpm[] = "('$value')";
		}
		DB::query("REPLACE INTO ".DB::table('newpm')."(uid) VALUES ".implode(',', $newpm));
		return $pmid;
	}

	function replypm($plid, $fromuid, $fromusername, $message) {
		if(!$plid || !$fromuid || !$fromusername || !$message) {
			return 0;
		}

		$threadpm = DB::fetch_first("SELECT * FROM ".DB::table('pm_lists')." WHERE plid='$plid'");
		if(empty($threadpm)) {
			return PMTHREADNONE_ERROR;
		}

		if($threadpm['pmtype'] == 1) {
			$users = explode('_', $threadpm['min_max']);
			if($users[0] == $fromuid) {
				$touid = $users[1];
			} elseif($users[1] == $fromuid) {
				$touid = $users[0];
			} else {
				return PMPRIVILEGENONE_ERROR;
			}

			
		}

		$memberuid = array();
		$query = DB::query("SELECT * FROM ".DB::table('pm_members')." WHERE plid='$plid'");
		while($member = DB::fetch_array($query)) {
			$memberuid[$member['uid']] = "('$member[uid]')";
		}
		if(!isset($memberuid[$fromuid])) {
			return PMPRIVILEGENONE_ERROR;
		}

		if($_G['setting']['badwords']['findpattern']) {
			$message = @preg_replace($_G['setting']['badwords']['findpattern'], $_G['setting']['badwords']['replace'], $message);
		}
		$lastsummary = $this->removecode(trim($message), 150);

		DB::query("INSERT INTO ".DB::table('pm_indexes')."(plid) VALUES('$plid')");
		$pmid = DB::insert_id();
		DB::query("INSERT INTO ".DB::table($this->getposttablename($plid))."(pmid, plid, authorid, message, dateline, delstatus) VALUES('$pmid', '$plid', '$fromuid', '$message', '".TIMPSTAMP."', 0)");
		if($threadpm['pmtype'] == 1) {
			$lastmessage = array('lastauthorid' => $fromuid, 'lastauthor' => $fromusername, 'lastsummary' => $lastsummary);
			$lastmessage = addslashes(serialize($lastmessage));
			$result = DB::query("INSERT INTO ".DB::table('pm_members')."(plid, uid, isnew, pmnum, lastupdate, lastdateline) VALUES('$plid', '$touid', '1', '1', '0', '".TIMPSTAMP."')", 'SILENT');
			if(!$result) {
				DB::query("UPDATE ".DB::table('pm_members')." SET isnew=1, pmnum=pmnum+1, lastdateline='".TIMPSTAMP."' WHERE plid='$plid' AND uid='$touid'");
			}
			DB::query("UPDATE ".DB::table('pm_members')." SET isnew=0, pmnum=pmnum+1, lastupdate='".TIMPSTAMP."', lastdateline='".TIMPSTAMP."' WHERE plid='$plid' AND uid='$fromuid'");
		} else {
			$lastmessage = unserialize($threadpm['lastmessage']);
			$lastmessage = array('firstauthorid' => $lastmessage['firstauthorid'], 'firstauthor' => $lastmessage['firstauthor'], 'firstsummary' => $lastmessage['firstsummary'], 'lastauthorid' => $fromuid, 'lastauthor' => $fromusername, 'lastsummary' => $lastsummary);
			$lastmessage = addslashes(serialize($lastmessage));
			DB::query("UPDATE ".DB::table('pm_members')." SET isnew=1, pmnum=pmnum+1, lastdateline='".TIMPSTAMP."' WHERE plid='$plid'");
			DB::query("UPDATE ".DB::table('pm_members')." SET isnew=0, lastupdate='".TIMPSTAMP."' WHERE plid='$plid' AND uid='$fromuid'");
		}
		DB::query("UPDATE ".DB::table('pm_lists')." SET lastmessage='$lastmessage' WHERE plid='$plid'");

		DB::query("REPLACE INTO ".DB::table('newpm')."(uid) VALUES ".implode(',', $memberuid)."");

		return $pmid;
	}

	function appendchatpm($plid, $uid, $touid) {
		if(!$plid || !$uid || !$touid) {
			return 0;
		}
		$threadpm = DB::fetch_first("SELECT * FROM ".DB::table('pm_lists')." WHERE plid='$plid'");
		if(empty($threadpm)) {
			return PMTHREADNONE_ERROR;
		}
		if($threadpm['pmtype'] != 2) {
			return PMCHATTYPE_ERROR;
		}
		if($threadpm['authorid'] != $uid) {
			return PMPRIVILEGENONE_ERROR;
		}

		

		$pmnum = DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->getposttablename($plid))." WHERE plid='$plid'");
		DB::query("INSERT INTO ".DB::table('pm_members')."(plid, uid, isnew, pmnum, lastupdate, lastdateline) VALUES('$plid', '$touid', '1', '$pmnum', '0', '0')", 'SILENT');
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pm_members')." WHERE plid='$plid'");
		DB::query("UPDATE ".DB::table('pm_lists')." SET members='$num' WHERE plid='$plid'");

		return 1;
	}

	function kickchatpm($plid, $uid, $touid) {
		if(!$uid || !$touid || !$plid || $uid == $touid) {
			return 0;
		}
		$threadpm = DB::fetch_first("SELECT * FROM ".DB::table('pm_lists')." WHERE plid='$plid'");
		if($threadpm['pmtype'] != 2) {
			return PMCHATTYPE_ERROR;
		}
		if($threadpm['authorid'] != $uid) {
			return PMPRIVILEGENONE_ERROR;
		}
		DB::query("DELETE FROM ".DB::table('pm_members')." WHERE plid='$plid' AND uid='$touid'");
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pm_members')." WHERE plid='$plid'");
		DB::query("UPDATE ".DB::table('pm_lists')." SET members='$num' WHERE plid='$plid'");
		return 1;
	}

	function quitchatpm($uid, $plids) {
		if(!$uid || !$plids) {
			return 0;
		}
		$list = array();
		$query = DB::query("SELECT * FROM ".DB::table('pm_members')." m LEFT JOIN ".DB::table('pm_lists')." t ON m.plid=t.plid WHERE m.plid IN (".dimplode($plids).") AND m.uid='$uid'");
		while($threadpm = DB::fetch_array($query)) {
			if($threadpm['pmtype'] != 2) {
				return PMCHATTYPE_ERROR;
			}
			if($threadpm['authorid'] == $uid) {
				return PMPRIVILEGENONE_ERROR;
			}
			$list[] = $threadpm['plid'];
		}

		if($list) {
			DB::query("DELETE FROM ".DB::table('pm_members')." WHERE plid IN (".dimplode($list).") AND uid='$uid'");
			DB::query("UPDATE ".DB::table('pm_lists')." SET members=members-1 WHERE plid IN (".dimplode($list).")");
		}

		return 1;
	}

	function deletepmbypmid($uid, $pmid) {
		if(!$uid || !$pmid) {
			return 0;
		}
		$index = DB::fetch_first("SELECT * FROM ".DB::table('pm_indexes')." i LEFT JOIN ".DB::table('pm_lists')." t ON i.plid=t.plid WHERE i.pmid='$pmid'");
		if($index['pmtype'] != 1) {
			return PMUIDTYPE_ERROR;
		}
		$users = explode('_', $index['min_max']);
		if(!in_array($uid, $users)) {
			return PMPRIVILEGENONE_ERROR;
		}
		if($index['authorid'] != $uid) {
			DB::query("UPDATE ".DB::table($this->getposttablename($index['plid']))." SET delstatus=2 WHERE pmid='$pmid' AND delstatus=0");
			$updatenum = DB::affected_rows();
			DB::query("DELETE FROM ".DB::table($this->getposttablename($index['plid']))." WHERE pmid='$pmid' AND delstatus=1");
			$deletenum = DB::affected_rows();
		} else {
			DB::query("UPDATE ".DB::table($this->getposttablename($index['plid']))." SET delstatus=1 WHERE pmid='$pmid' AND delstatus=0");
			$updatenum = DB::affected_rows();
			DB::query("DELETE FROM ".DB::table($this->getposttablename($index['plid']))." WHERE pmid='$pmid' AND delstatus=2");
			$deletenum = DB::affected_rows();
		}

		if(!DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->getposttablename($index['plid']))." WHERE plid='$index[plid]'")) {
			DB::query("DELETE FROM ".DB::table('pm_lists')." WHERE plid='$index[plid]'");
			DB::query("DELETE FROM ".DB::table('pm_members')." WHERE plid='$index[plid]'");
			DB::query("DELETE FROM ".DB::table('pm_indexes')." WHERE plid='$index[plid]'");
		} else {
			DB::query("UPDATE ".DB::table('pm_members')." SET pmnum=pmnum-".($updatenum + $deletenum)." WHERE plid='".$index['plid']."' AND uid='$uid'");
		}
		return 1;
	}

	function deletepmbypmids($uid, $pmids) {
		if($pmids) {
			foreach($pmids as $key => $pmid) {
				$this->deletepmbypmid($uid, $pmid);
			}
		}
		return 1;
	}



	function deletepmbyplid($uid, $plid, $isuser = 0) {
		if(!$uid || !$plid) {
			return 0;
		}

		if($isuser) {
			$relationship = $this->relationship($uid, $plid);
			$sql = "SELECT * FROM ".DB::table('pm_lists')." WHERE min_max='$relationship'";
		} else {
			$sql = "SELECT * FROM ".DB::table('pm_lists')." WHERE plid='$plid'";
		}

		$query = DB::query($sql);
		if($list = DB::fetch_array($query)) {
			if($list['pmtype'] == 1) {
				$user = explode('_', $list['min_max']);
				if(!in_array($uid, $user)) {
					return PMPRIVILEGENONE_ERROR;
				}
			} else {
				if($uid != $list['authorid']) {
					return PMPRIVILEGENONE_ERROR;
				}
			}
		} else {
			return PMTHREADNONE_ERROR;
		}

		if($list['pmtype'] == 1) {
			if($uid == $list['authorid']) {
				DB::query("DELETE FROM ".DB::table($this->getposttablename($list['plid']))." WHERE plid='$list[plid]' AND delstatus=2");
				DB::query("UPDATE ".DB::table($this->getposttablename($list['plid']))." SET delstatus=1 WHERE plid='$list[plid]' AND delstatus=0");
			} else {
				DB::query("DELETE FROM ".DB::table($this->getposttablename($list['plid']))." WHERE plid='$list[plid]' AND delstatus=1");
				DB::query("UPDATE ".DB::table($this->getposttablename($list['plid']))." SET delstatus=2 WHERE plid='$list[plid]' AND delstatus=0");
			}
			$count = DB::result_first("SELECT COUNT(*) FROM ".DB::table($this->getposttablename($list['plid']))." WHERE plid='$list[plid]'");
			if(!$count) {
				DB::query("DELETE FROM ".DB::table('pm_lists')." WHERE plid='$list[plid]'");
				DB::query("DELETE FROM ".DB::table('pm_members')." WHERE plid='$list[plid]'");
				DB::query("DELETE FROM ".DB::table('pm_indexes')." WHERE plid='$list[plid]'");
			} else {
				DB::query("DELETE FROM ".DB::table('pm_members')." WHERE plid='$list[plid]' AND uid='$uid'");
			}
		} else {
			DB::query("DELETE FROM ".DB::table($this->getposttablename($list['plid']))." WHERE plid='$list[plid]'");
			DB::query("DELETE FROM ".DB::table('pm_lists')." WHERE plid='$list[plid]'");
			DB::query("DELETE FROM ".DB::table('pm_members')." WHERE plid='$list[plid]'");
			DB::query("DELETE FROM ".DB::table('pm_indexes')." WHERE plid='$list[plid]'");
		}
		return 1;
	}

	function deletepmbyplids($uid, $plids, $isuser = 0) {
		if($plids) {
			foreach($plids as $key => $plid) {
				$this->deletepmbyplid($uid, $plid, $isuser);
			}
		}
		return 1;
	}



	function getprivatepmbyplid($uid, $plid, $starttime = 0, $endtime = 0, $start = 0, $ppp = 0) {
		if(!$uid || !$plid) {
			return 0;
		}
		if(!$this->isprivilege($plid, $uid)) {
			return 0;
		}
		$thread = DB::fetch_first("SELECT * FROM ".DB::table('pm_lists')." WHERE plid='$plid'");
		if($thread['pmtype'] != 1) {
			return 0;
		}
		$pms = $addsql = array();
		$addsql[] = "p.plid='$plid'";
		if($thread['authorid'] == $uid) {
			$addsql[] = 'p.delstatus IN (0,2)';
		} else {
			$addsql[] = 'p.delstatus IN (0,1)';
		}
		if($starttime) {
			$addsql[]= "p.dateline>'$starttime'";
		}
		if($endtime) {
			$addsql[] = "p.dateline<'$endtime'";
		}
		if($addsql) {
			$addsql = implode(' AND ', $addsql);
		} else {
			$addsql = '';
		}
		if($ppp) {
			$limitsql = 'LIMIT '.intval($start).', '.intval($ppp);
		} else {
			$limitsql = '';
		}
		$pms = DB::fetch_all("SELECT t.*, p.*, t.authorid as founderuid, t.dateline as founddateline FROM ".DB::table($this->getposttablename($plid))." p LEFT JOIN ".DB::table('pm_lists')." t ON p.plid=t.plid WHERE $addsql ORDER BY p.dateline DESC $limitsql");
		DB::query("UPDATE ".DB::table('pm_members')." SET isnew=0 WHERE plid='$plid' AND uid='$uid' AND isnew=1");
		return array_reverse($pms);
	}

	function getchatpmbyplid($uid, $plid, $starttime = 0, $endtime = 0, $start = 0, $ppp = 0) {
		if(!$uid || !$plid) {
			return 0;
		}
		if(!$this->isprivilege($plid, $uid)) {
			return 0;
		}
		$pms = $addsql = array();
		$addsql[] = "p.plid='$plid'";
		if($starttime) {
			$addsql[]= "p.dateline>'$starttime'";
		}
		if($endtime) {
			$addsql[] = "p.dateline<'$endtime'";
		}
		if($addsql) {
			$addsql = implode(' AND ', $addsql);
		} else {
			$addsql = '';
		}
		if($ppp) {
			$limitsql = 'LIMIT '.intval($start).', '.intval($ppp);
		} else {
			$limitsql = '';
		}
		$query = DB::query("SELECT t.*, p.*, t.authorid as founderuid, t.dateline as founddateline FROM ".DB::table($this->getposttablename($plid))." p LEFT JOIN ".DB::table('pm_lists')." t ON p.plid=t.plid WHERE $addsql ORDER BY p.dateline DESC $limitsql");
		while($pm = DB::fetch_array($query)) {
			if($pm['pmtype'] != 2) {
				return 0;
			}
			$pms[] = $pm;
		}
		DB::query("UPDATE ".DB::table('pm_members')." SET isnew=0 WHERE plid='$plid' AND uid='$uid' AND isnew=1");
		return array_reverse($pms);
	}

	function getpmlist($uid, $filter, $start, $ppp = 10) {
		if(!$uid) {
			return 0;
		}
		$members = $touidarr = $tousernamearr = array();

		if($filter == 'newpm') {
			$addsql = 'm.isnew=1 AND ';
		
		} else {
			$addsql = '';
		}
		$query = DB::query("SELECT * FROM ".DB::table('pm_members')." m LEFT JOIN ".DB::table('pm_lists')." t ON t.plid=m.plid WHERE $addsql m.uid='$uid' ORDER BY m.lastdateline DESC LIMIT $start, $ppp");
		while($member = DB::fetch_array($query)) {
			if($member['pmtype'] == 1) {
				$users = explode('_', $member['min_max']);
				$member['touid'] = $users[0] == $uid ? $users[1] : $users[0];
			} else {
				$member['touid'] = 0;
			}
			$touidarr[$member['touid']] = $member['touid'];
			$members[] = $member;
		}

		DB::query("DELETE FROM ".DB::table('newpm')." WHERE uid='$uid'");

		$array = array();
		if($members) {
			$today = TIMPSTAMP - TIMPSTAMP % 86400;
			
			$tousernamearr = $this->id2name($touidarr);
			foreach($members as $key => $data) {

				$daterange = 5;
				$data['founddateline'] = $data['dateline'];
				$data['dateline'] = $data['lastdateline'];
				$data['pmid'] = $data['plid'];
				$lastmessage = unserialize($data['lastmessage']);
				if($lastmessage['firstauthorid']) {
					$data['firstauthorid'] = $lastmessage['firstauthorid'];
					$data['firstauthor'] = $lastmessage['firstauthor'];
					$data['firstsummary'] = $lastmessage['firstsummary'];
				}
				if($lastmessage['lastauthorid']) {
					$data['lastauthorid'] = $lastmessage['lastauthorid'];
					$data['lastauthor'] = $lastmessage['lastauthor'];
					$data['lastsummary'] = $lastmessage['lastsummary'];
				}
				$data['msgfromid'] = $lastmessage['lastauthorid'];
				$data['msgfrom'] = $lastmessage['lastauthor'];
				$data['message'] = $lastmessage['lastsummary'];

				$data['new'] = $data['isnew'];

				$data['msgtoid'] = $data['touid'];
				if($data['lastdateline'] >= $today) {
					$daterange = 1;
				} elseif($data['lastdateline'] >= $today - 86400) {
					$daterange = 2;
				} elseif($data['lastdateline'] >= $today - 172800) {
					$daterange = 3;
				} elseif($data['lastdateline'] >= $today - 604800) {
					$daterange = 4;
				}
				$data['daterange'] = $daterange;

				$data['tousername'] = $tousernamearr[$data['touid']];
				unset($data['min_max']);
				$array[] = $data;
			}
		}
		return $array;
	}

	function getplidbypmid($pmid) {
		if(!$pmid) {
			return false;
		}
		return DB::result_first("SELECT plid FROM ".DB::table('pm_indexes')." WHERE pmid='$pmid'");
	}

	function getplidbytouid($uid, $touid) {
		if(!$uid || !$touid) {
			return 0;
		}
		return DB::result_first("SELECT plid FROM ".DB::table('pm_lists')." WHERE min_max='".$this->relationship($uid, $touid)."'");
	}

	function getuidbyplid($plid) {
		if(!$plid) {
			return array();
		}
		$uidarr = array();
		$query = DB::query("SELECT uid FROM ".DB::table('pm_members')." WHERE plid='$plid'");
		while($uid = DB::fetch_array($query)) {
			$uidarr[$uid['uid']] = $uid['uid'];
		}
		return $uidarr;
	}

	function chatpmmemberlist($uid, $plid) {
		if(!$uid || !$plid) {
			return 0;
		}
		$uidarr = $this->getuidbyplid($plid);
		if(empty($uidarr)) {
			return 0;
		}
		if(!isset($uidarr[$uid])) {
			return 0;
		}
		$authorid = DB::result_first("SELECT authorid FROM ".DB::table('pm_lists')." WHERE plid='$plid'");
		return array('author' => $authorid, 'member' => $uidarr);
	}

	function relationship($fromuid, $touid) {
		if($fromuid < $touid) {
			return $fromuid.'_'.$touid;
		} elseif($fromuid > $touid) {
			return $touid.'_'.$fromuid;
		} else {
			return '';
		}
	}

	function getposttablename($plid) {
		$id = substr((string)$plid, -1, 1);
		return 'pm_messages_'.$id;
	}

	/*function get_blackls($uid, $uids = array()) {
		if(!$uids) {
			$blackls = DB::result_first("SELECT blacklist FROM ".DB::table('memberfields')." WHERE uid='$uid'");
		} else {
			$uids = dimplode($uids);
			$blackls = array();
			$query = DB::query("SELECT uid, blacklist FROM ".DB::table('memberfields')." WHERE uid IN ($uids)");
			while($data = DB::fetch_array($query)) {
				$blackls[$data['uid']] = explode(',', $data['blacklist']);
			}
		}
		return $blackls;
	}

	function set_blackls($uid, $blackls) {
		DB::query("UPDATE ".DB::table('memberfields')." SET blacklist='$blackls' WHERE uid='$uid'");
		return DB::affected_rows();
	}

	function update_blackls($uid, $username, $action = 1) {
		$username = !is_array($username) ? array($username) : $username;
		if($action == 1) {
			if(!in_array('{ALL}', $username)) {
				$usernames = dimplode($username);
				$query = DB::query("SELECT username FROM ".DB::table('members')." WHERE username IN ($usernames)");
				$usernames = array();
				while($data = DB::fetch_array($query)) {
					$usernames[addslashes($data['username'])] = addslashes($data['username']);
				}
				if(!$usernames) {
					return 0;
				}
				$blackls = addslashes(DB::result_first("SELECT blacklist FROM ".DB::table('memberfields')." WHERE uid='$uid'"));
				if($blackls) {
					$list = explode(',', $blackls);
					foreach($list as $k => $v) {
						if(in_array($v, $usernames)) {
							unset($usernames[$v]);
						}
					}
				}
				if(!$usernames) {
					return 1;
				}
				$listnew = implode(',', $usernames);
				$blackls .= $blackls !== '' ? ','.$listnew : $listnew;
			} else {
				$blackls = addslashes(DB::result_first("SELECT blacklist FROM ".DB::table('memberfields')." WHERE uid='$uid'"));
				$blackls .= ',{ALL}';
			}
		} else {
			$blackls = addslashes(DB::result_first("SELECT blacklist FROM ".DB::table('memberfields')." WHERE uid='$uid'"));
			$list = $blackls = explode(',', $blackls);
			foreach($list as $k => $v) {
				if(in_array($v, $username)) {
					unset($blackls[$k]);
				}
			}
			$blackls = implode(',', $blackls);
		}
		DB::query("UPDATE ".DB::table('memberfields')." SET blacklist='$blackls' WHERE uid='$uid'");
		return 1;
	}
*/
	function removecode($str, $length) {
		return getstr(strip_tags($str),$length,0,0,0,-1);
	}

	function ispminterval($uid, $interval = 0) {
		if(!$uid) {
			return 0;
		}
		$interval = intval($interval);
		if(!$interval) {
			return 1;
		}
		$lastupdate = DB::result_first("SELECT lastupdate FROM ".DB::table('pm_members')." WHERE uid='$uid' ORDER BY lastupdate DESC LIMIT 1");
		if((TIMPSTAMP - $lastupdate) > $interval) {
			return 1;
		} else {
			return 0;
		}
	}

	function isprivatepmthreadlimit($uid, $maxnum = 0) {
		if(!$uid) {
			return 0;
		}
		$maxnum = intval($maxnum);
		if(!$maxnum) {
			return 1;
		}
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pm_members')." m LEFT JOIN ".DB::table('pm_lists')." t ON m.plid=t.plid WHERE uid='$uid' AND lastupdate>'".(TIMPSTAMP-86400)."' AND t.pmtype=1");
		if($maxnum - $num < 0) {
			return 0;
		} else {
			return 1;
		}
	}

	function ischatpmthreadlimit($uid, $maxnum = 0) {
		if(!$uid) {
			return 0;
		}
		$maxnum = intval($maxnum);
		if(!$maxnum) {
			return 1;
		}
		$num = DB::result_first("SELECT COUNT(*) FROM ".DB::table('pm_lists')." WHERE authorid='$uid' AND dateline>'".(TIMPSTAMP-86400)."'");
		if($maxnum - $num < 0) {
			return 0;
		} else {
			return 1;
		}
	}
	

	function id2name($uidarr) {
		$arr = array();
		$query = DB::query("SELECT uid, username FROM dzz_members WHERE uid IN (".dimplode($uidarr).")");
		while($user = DB::fetch($query)) {
			$arr[$user['uid']] = $user['username'];
		}
		return $arr;
	}
}
?>
