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
//error_reporting(E_ALL);
include_once libfile('function/profile', '', 'user');
include_once libfile('function/admin');
loadcache('profilesetting');
$vid = intval($_GET['vid']);
$anchor = in_array($_GET['anchor'], array('authstr', 'refusal', 'pass', 'add')) ? $_GET['anchor'] : 'authstr';
$current = array($anchor => 1);
$op=$_GET['op'];
//判断管理权限
if ($vid) {
	if ($vid == 1) {
		if ($_G['member']['grid'] != 4 && $_G['adminid'] != 1)
			showmessage('contact_administrator1');
	} else {
		if ($_G['adminid'] != 1)
			showmessage('contact_administrator2');
	}
} else {
	if ($_G['member']['grid'] != 5 && $_G['adminid'] != 1)
		showmessage('contact_administrator3');
}
if ($anchor != 'pass') {
	$_GET['verifytype'] = $vid;
} else {
	$_GET['verify' . $vid] = 1;
	$_GET['orderby'] = 'uid';
}
if (!submitcheck('verifysubmit', true)) {
	$navtitle = $vid ? $_G['setting']['verify'][$vid]['title'] : lang('members_verify_profile');

	$thurl = ADMINSCRIPT . '?mod=member&op=verify&anchor=' . $anchor . '&vid=' . $vid;
	if ($anchor == 'refusal') {
		$_GET['flag'] = -1;
	} elseif ($anchor == 'authstr') {
		$_GET['flag'] = 0;
	}
	$intkeys = array('uid', 'verifytype', 'flag', 'verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7');
	$strkeys = array();
	$randkeys = array();
	$likekeys = array('username');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 'v.');
	foreach ($likekeys as $k) {
		$_GET[$k] = dhtmlspecialchars($_GET[$k]);
	}
	$thurl .= '&' . implode('&', $results['urls']);
	$wherearr = $results['wherearr'];
	if ($_GET['dateline1']) {
		$wherearr[] = "v.dateline >= '" . strtotime($_GET['dateline1']) . "'";
		$thurl .= '&dateline1=' . $_GET['dateline1'];
	}
	if ($_GET['dateline2']) {
		$wherearr[] = "v.dateline <= '" . strtotime($_GET['dateline2']) . "'";
		$thurl .= '&dateline2=' . $_GET['dateline2'];
	}

	$wheresql = empty($wherearr) ? '1' : implode(' AND ', $wherearr);

	$orders = getorders(array('dateline', 'uid'), 'dateline', 'v.');
	$ordersql = $orders['sql'];
	if ($orders['urls'])
		$thurl .= '&' . implode('&', $orders['urls']);
	$orderby = array($_GET['orderby'] => ' selected');
	$ordersc = array($_GET['ordersc'] => ' selected');

	$orders = in_array($_G['orderby'], array('dateline', 'uid')) ? $_G['orderby'] : 'dateline';
	$ordersc = in_array(strtolower($_GET['ordersc']), array('asc', 'desc')) ? $_GET['ordersc'] : 'desc';

	$perpage = empty($_GET['perpage']) ? 0 : intval($_GET['perpage']);
	if (!in_array($perpage, array(10, 20, 50, 100)))
		$perpage = 10;
	$perpages = array($perpage => ' selected');
	$thurl .= '&perpage=' . $perpage;

	$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
	if ($page < 1)
		$page = 1;
	$start = ($page - 1) * $perpage;

	$multi = '';
	if ($anchor != 'pass') {
		$count = C::t('user_verify_info') -> count_by_search($_GET['uid'], $vid, $_GET['flag'], $_GET['username'], strtotime($_GET['dateline1']), strtotime($_GET['dateline2']));
	} else {
		$wheresql = (!empty($_GET['username']) ? str_replace('v.username', 'm.username', $wheresql) : $wheresql) . ' AND v.uid=m.uid ';
		$count = C::t('user_verify') -> count_by_search($_GET['uid'], $vid, $_GET['username']);
	}
	if ($count) {

		if ($anchor != 'pass') {
			$verifyusers = C::t('user_verify_info') -> fetch_all_search($_GET['uid'], $vid, $_GET['flag'], $_GET['username'], strtotime($_GET['dateline1']), strtotime($_GET['dateline2']), $orders, $start, $perpage, $ordersc);
		} else {
			$verifyusers = C::t('user_verify') -> fetch_all_search($_GET['uid'], $vid, $_GET['username'], 'v.uid', $start, $perpage, $ordersc);
			$verifyuids = array_keys($verifyusers);
			$profiles = C::t('user_profile') -> fetch_all($verifyuids, false, 0);
		}
		$list = array();
		foreach ($verifyusers as $uid => $value) {
			if ($anchor == 'pass') {
				$value = array_merge($value, $profiles[$uid]);
			}
			$value['username'] = '<a href="user.php?&uid=' . $value['uid'] . '" target="_blank"><img src="avatar.php?uid=' . $value['uid'] . '&size=small"><br/><br/>' . $value['username'] . '</a>';
			if ($anchor != 'pass') {
				$fields = $anchor != 'pass' ? dunserialize($value['field']) : $_G['setting']['verify'][$vid]['field'];
				$value['verifytype'] = $value['verifytype'] ? $_G['setting']['verify'][$value['verifytype']]['title'] : lang('members_verify_profile');
				$fieldstr = '<table class="table-sub" width="96%">';
				$i = 0;
				$fieldstr .= '<tr>' . ($anchor == 'authstr' ? '<td width="35">' . lang('refuse') . '</td>' : '') . '<td width="100">' . lang('members_verify_fieldid') . '</td><td>' . lang('members_verify_newvalue') . '</td></tr><tbody id="verifyitem_' . $value['vid'] . '">';
				$i++;

				foreach ($fields as $key => $field) {
					if (in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth'))) {
						continue;
					}
					if ($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
						if ($field) {
							$field = '<a href="' . (getglobal('setting/attachurl') . $field) . '" target="_blank"><img src="' . (getglobal('setting/attachurl') . $field) . '" class="verifyimg" /></a>';
						} else {
							$field = lang('members_verify_pic_removed');
						}
					} elseif (in_array($key, array('gender', 'birthday', 'department'))) {

						$field = profile_show($key, $fields);
					}
					$fieldstr .= '<tr>' . ($anchor == 'authstr' ? '<td><input type="checkbox" name="refusal[' . $value['vid'] . '][' . $key . ']" value="' . $key . '" onclick="document.getElementById(\'refusal' . $value['vid'] . '\').click();" /></td>' : '') . '<td>' . $_G['cache']['profilesetting'][$key]['title'] . ':</td><td>' . $field . '</td></tr>';
					$i++;
				}
				$opstr = "";

				if ($anchor == 'authstr') {
					$opstr .= "<label class=\"radio-inline\"><input type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');showreason($value[vid], 0);\">" . lang('validate') . "</label><label class=\"radio-inline\"><input  type=\"radio\" name=\"verify[$value[vid]]\" value=\"refusal\" id=\"refusal$value[vid]\" onclick=\"mod_setbg($value[vid], 'refusal');showreason($value[vid], 1);\">" . lang('refuse') . "</label>";
				} elseif ($anchor == 'refusal') {
					$opstr .= "<label class=\"radio-inline\"><input type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');\">" . lang('validate') . "</label>";
				}

				$fieldstr .= "</tbody><tr><td colspan=\"5\">$opstr <span id=\"reason_$value[vid]\" style=\"display: none;\" title=\"" . lang('moderate_reasonpm') . "\" ><input type=\"text\" class=\"form-control input-sm\" placeholder=\"" . lang('moderate_reasonpm') . "\" name=\"reason[$value[vid]]\" style=\"margin: 0px;\"></span><input type=\"button\" value=\"" . lang('moderate') . "\" name=\"singleverifysubmit\" class=\"btn btn-default btn-sm ml10\" onclick=\"singleverify($value[vid]);\"></td></tr></table>";
				$value['fieldstr'] = $fieldstr;
				$value['dateline'] = dgmdate($value['dateline'], 'u');
				$list[$uid] = $value;
				// = array($value['username'], $verifytype, dgmdate($value['dateline'], 'dt'), $fieldstr);
				//showtablerow("id=\"mod_$value[vid]_row\" verifyid=\"$value[vid]\"", $cssarr, $valuearr);
			} else {
				$fields = $_G['setting']['verify'][$vid]['field'];
				$value['verifytype'] = $vid ? $_G['setting']['verify'][$vid]['title'] : lang('members_verify_profile');

				$fieldstr = '<table class="table-sub" width="96%">';
				$fieldstr .= '<tr><td width="100">' . lang('members_verify_fieldid') . '</td><td>' . lang('members_verify_newvalue') . '</td></tr>';

				foreach ($fields as $key => $field) {
					if (!in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth'))) {
						if (in_array($key, array('gender', 'birthday', 'department'))) {
							$value[$field] = profile_show($key, $value);
						}
						if ($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
							if ($value[$field]) {
								$value[$field] = '<a href="' . (getglobal('setting/attachurl') . $value[$field]) . '" target="_blank"><img src="' . (getglobal('setting/attachurl') . $value[$field]) . '" class="verifyimg" /></a>';
							} else {
								$value[$field] = lang('members_verify_pic_removed');
							}
						}
						$fieldstr .= '<tr><td width="100">' . $_G['cache']['profilesetting'][$key]['title'] . ':</td><td>' . $value[$field] . '</td></tr>';
					}
				}
				$fieldstr .= "</table>";
				$value['fieldstr'] = $fieldstr;
				$opstr = "<ul class=\"list-unstyled\"><li><label class=\"radio-inline\"><input  type=\"radio\" name=\"verify[$value[uid]]\" value=\"export\" onclick=\"mod_setbg($value[uid], 'export');\">".lang('export')."</label></li><li><label class=\"radio-inline\"><input type=\"radio\" name=\"verify[$value[uid]]\" value=\"refusal\" onclick=\"mod_setbg($value[uid], 'refusal');\">" . lang('refuse') . "</label></li></ul>";
				$value['opstr'] = $opstr;
				$value['dateline'] = dgmdate($value['dateline'], 'u');
				$list[$uid] = $value;
				//showtablerow("id=\"mod_$value[uid]_row\"", $cssarr, $valuearr);
			}
		}
		$multi = multi($count, $perpage, $page, $thurl, 'pull-right');
	}

} else {
	if ($anchor == 'pass') {
		$verifyuids = array();

		foreach ($_GET['verify'] as $uid => $type) {
			if ($type == 'export') {
				$verifyuids['export'][] = $uid;
			} elseif ($type == 'refusal') {
				$verifyuids['refusal'][] = $uid;
				//发送通知
				$notevars = array('from_id' => 0, 'from_idtype' => '', 'author' => $_G['username'], 'authorid' => $_G['uid'], 'url' => 'user.php?mod=profile&vid=' . $vid, 'profile' => $fieldtitle, 'dataline' => dgmdate(TIMESTAMP), 'title' => $vid ? $_G['setting']['verify'][$vid]['title'] : lang('members_verify_profile'), 'reason' => $_GET['reason'][$value['vid']], );

				$action = 'user_profile_pass_refusal';
				$type = 'user_profile_pass_refusal_' . $vid;

				dzz_notification::notification_add($uid, $type, $action, $notevars, 1,'');
			}
		}
		if (is_array($verifyuids['refusal']) && !empty($verifyuids['refusal'])) {
			C::t('user_verify') -> update($verifyuids['refusal'], array("verify$vid" => '0'));
			if ($vid == 1)
				C::t('user') -> update($uid, array('grid' => '0'));
		}
		if (is_array($verifyuids['export']) && !empty($verifyuids['export']) || empty($verifyuids['refusal'])) {
			$uids = array();
			if (is_array($verifyuids['export']) && !empty($verifyuids['export'])) {
				$uids = $verifyuids['export'];
			}
			$fields = $_G['setting']['verify'][$vid]['field'];
			$fields = array_reverse($fields);
			$fields['username'] = 'username';
			$fields = array_reverse($fields);
			$title = $verifylist = '';
			$showtitle = true;
			$verifyusers = C::t('user_verify') -> fetch_all_by_vid($vid, 1, $uids);
			$verifyuids = array_keys($verifyusers);
			$members = C::t('user') -> fetch_all($verifyuids, false, 0);
			$profiles = C::t('user_profile') -> fetch_all($verifyuids, false, 0);
			foreach ($verifyusers as $uid => $value) {
				$value = array_merge($value, $members[$uid], $profiles[$uid]);
				$str = $common = '';
				foreach ($fields as $key => $field) {
					if (in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
						continue;
					}
					if ($showtitle) {
						$title .= $common . ($key == 'username' ? lang('username') : $_G['cache']['profilesetting'][$key]['title']);
					}
					if (in_array($key, array('gender', 'birthday', 'department'))) {
						$value[$field] = profile_show($key, $value);
					}
					$str .= $common . $value[$field];
					$common = "\t";
				}
				$verifylist .= $str . "\n";
				$showtitle = false;
			}
			$verifylist = $title . "\n" . $verifylist;
			$filename = date('Ymd', TIMESTAMP) . '.xls';

			define('FOOTERDISABLED', true);
			ob_end_clean();
			header("Content-type:application/vnd.ms-excel");
			header('Content-Encoding: none');
			header('Content-Disposition: attachment; filename=' . $filename);
			header('Pragma: no-cache');
			header('Expires: 0');
			if ($_G['charset'] != 'gbk') {
				$verifylist = diconv($verifylist, $_G['charset'], 'GBK');
			}
			echo $verifylist;
			exit();
		} else {
			showmessage('members_verify_succeed', ADMINSCRIPT . '?mod=member&op=verify&vid=' . $vid . '&anchor=pass', array(), array('alert' => 'right'));
		}
	} else {
		$vids = array();
		$single = intval($_GET['singleverify']);
		$verifyflag = empty($_GET['verify']) ? false : true;
		if ($verifyflag) {
			if ($single) {
				$_GET['verify'] = array($single => $_GET['verify'][$single]);
			}
			foreach ($_GET['verify'] as $id => $type) {
				$vids[] = $id;
			}

			$verifysetting = $_G['setting']['verify'];
			$verify = $refusal = array();
			foreach (C::t('user_verify_info')->fetch_all($vids) as $value) {
				if (in_array($_GET['verify'][$value['vid']], array('refusal', 'validate'))) {
					$fields = dunserialize($value['field']);
					$verifysetting = $_G['setting']['verify'][$value['verifytype']];

					if ($_GET['verify'][$value['vid']] == 'refusal') {
						$refusalfields = !empty($_GET['refusal'][$value['vid']]) ? $_GET['refusal'][$value['vid']] : $verifysetting['field'];
						$fieldtitle = $common = '';
						$deleteverifyimg = false;
						foreach ($refusalfields as $key => $field) {
							$fieldtitle .= $common . $_G['cache']['profilesetting'][$field]['title'];
							$common = ',';
							if ($_G['cache']['profilesetting'][$field]['formtype'] == 'file') {
								$deleteverifyimg = true;
								@unlink(getglobal('setting/attachdir') . $fields[$key]);
								$fields[$field] = '';
							}
						}
						if ($deleteverifyimg) {
							C::t('user_verify_info') -> update($value['vid'], array('field' => serialize($fields)));
						}
						if ($value['verifytype']) {
							$verify["verify"]['-1'][] = $value['uid'];
						}
						$verify['flag'][] = $value['vid'];
						//发送通知
						$notevars = array('from_id' => 0, 'from_idtype' => '', 'author' => $_G['username'], 'authorid' => $_G['uid'], 'url' => 'user.php?mod=profile&vid=' . $vid, 'profile' => $fieldtitle, 'dataline' => dgmdate(TIMESTAMP), 'title' => $vid ? $_G['setting']['verify'][$vid]['title'] : lang('members_verify_profile'), 'reason' => $_GET['reason'][$value['vid']], );

						$action = 'user_profile_moderate_refusal';
						$type = 'user_profile_moderate_refusal_' . $vid;

					} else {
						C::t('user_profile') -> update(intval($value['uid']), $fields);
						if ($fields['department']) {//含有department时审核通过后，把此用户加入相应的部门
							
							C::t('organization_user') -> insert_by_orgid($fields['department'],array($value['uid']));
						}
						$verify['delete'][] = $value['vid'];
						if ($value['verifytype']) {
							$verify["verify"]['1'][] = $value['uid'];
						}
						//发送通知
						$notevars = array('from_id' => 0, 'from_idtype' => '', 'author' => $_G['username'], 'authorid' => $_G['uid'], 'url' => 'user.php?mod=profile&vid=' . $vid, 'dataline' => dgmdate(TIMESTAMP), 'title' => $vid ? $_G['setting']['verify'][$vid]['title'] : lang('members_verify_profile'), );

						$action = 'user_profile_moderate_pass';
						$type = 'user_profile_moderate_pass_' . $vid;

					}
					dzz_notification::notification_add($value['uid'], $type, $action, $notevars, 1,'');
				}
			}
			if ($vid && !empty($verify["verify"])) {
				foreach ($verify["verify"] as $flag => $uids) {
					$flag = intval($flag);
					C::t('user_verify') -> update($uids, array("verify$vid" => $flag));
					if ($vid == 1)
						C::t('user') -> update($uids, array('grid' => 6));
				}
			}

			if (!empty($verify['delete'])) {
				C::t('user_verify_info') -> delete($verify['delete']);
			}

			if (!empty($verify['flag'])) {
				C::t('user_verify_info') -> update($verify['flag'], array('flag' => '-1'));
			}
		}
		if ($single) {
			echo "<script type=\"text/javascript\">var trObj = parent.document.getElementById('mod_{$single}_row');trObj.parentNode.removeChild(trObj);</script>";
		} else {
			showmessage('members_verify_succeed', ADMINSCRIPT . '?mod=member&op=verify&vid=' . $vid . '&anchor=' . $_GET['anchor'], array(), array('alert' => 'right'));
		}
	}
}

include template('verify');
?>
