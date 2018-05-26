<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
//error_reporting(E_ALL);
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$op=$_GET['op'];
if ($_G['adminid'] != 1)
	showmessage('no_privilege');
include_once  libfile('function/cache');
$do = $_GET['do'] ? $_GET['do'] : '';

if ($do == 'edit') {
	$vid = $_GET['vid'] < 8 ? intval($_GET['vid']) : 0;
	$verifyarr = $_G['setting']['verify'][$vid];
	if (!submitcheck('verifysubmit')) {

		$verificonhtml = '';
		if ($verifyarr['icon']) {
			$icon_url = parse_url($verifyarr['icon']);
			$prefix = !$icon_url['host'] && strpos($verifyarr['icon'], $_G['setting']['attachurl']) === false ? $_G['setting']['attachurl'] : '';

			$verificonhtml = '<label class="radio-inline"><input type="checkbox" class="checkbox" name="deleteicon[' . $vid . ']" value="yes" />'.lang('del1').'</label>&nbsp;<img src="' . $prefix . $verifyarr['icon'] . '?t=' . TIMESTAMP . '"  />';
		}
		$unverifyiconhtml = '';
		if ($verifyarr['unverifyicon']) {
			$unverifyiconurl = parse_url($verifyarr['unverifyicon']);

			$prefix = !$unverifyiconurl['host'] && strpos($verifyarr['unverifyicon'], $_G['setting']['attachurl']) === false ? $_G['setting']['attachurl'] : '';
			$unverifyiconhtml = '<label class="radio-inline"><input type="checkbox" class="checkbox" name="delunverifyicon[' . $vid . ']" value="yes" />'.lang('del1').'</label>&nbsp;<img src="' . $prefix . $verifyarr['unverifyicon'] . '?t=' . TIMESTAMP . '" />';
		}

		$fieldarr = C::t('user_profile_setting') -> fetch_all_by_available(1);
		unset($fieldarr['birthyear']);
		unset($fieldarr['birthmonth']);
		unset($fieldarr['zodiac']);
		unset($fieldarr['constellation']);
		$groupselect = array();
		$usergroups = C::t('usergroup') -> fetch_all_not(array(6, 7));
	} else {
		foreach ($_G['setting']['verify'] as $key => $value) {
			$_G['setting']['verify'][$key]['icon'] = str_replace($_G['setting']['attachurl'], '', $value['icon']);
			$_G['setting']['verify'][$key]['unverifyicon'] = str_replace($_G['setting']['attachurl'], '', $value['unverifyicon']);
		}
		$verifynew = getgpc('verify');
		$verifynew['readonly'] = $_G['setting']['verify'][$vid]['readonly'];
		if ($vid == 1) {
			$verifynew['title'] = $_G['setting']['verify'][$vid]['title'];
		}
		if ($verifynew['available'] == 1 && !trim($verifynew['title'])) {
			showmessage('members_verify_title_empty', dreferer());
		}
		if ($icon = getverifyicon('iconnew', 'common/verify/' . $vid . '/verify_icon.jpg'))
			$verifynew['icon'] = $icon;
		else
			$verifynew['icon'] = $_G['setting']['verify'][$vid]['icon'];
		if ($uicon = getverifyicon('unverifyiconnew', 'common/verify/' . $vid . '/unverify_icon.jpg'))
			$verifynew['unverifyicon'] = $uicon;
		else
			$verifynew['unverifyicon'] = $_G['setting']['verify'][$vid]['unverifyicon'];
		if ($_GET['deleteicon']) {
			$verifynew['icon'] = delverifyicon($verifyarr['icon']);
		}
		if ($_GET['delunverifyicon']) {
			$verifynew['unverifyicon'] = delverifyicon($verifyarr['unverifyicon']);
		}

		if (!empty($verifynew['field']['birthday'])) {
			$verifynew['field']['birthyear'] = 'birthyear';
			$verifynew['field']['birthmonth'] = 'birthmonth';
		}

		$verifynew['groupid'] = !empty($verifynew['groupid']) && is_array($verifynew['groupid']) ? $verifynew['groupid'] : array();
		$_G['setting']['verify'][$vid] = $verifynew;
		$_G['setting']['verify']['enabled'] = false;
		for ($i = 1; $i < 8; $i++) {
			if ($_G['setting']['verify'][$i]['available'] && !$_G['setting']['verify']['enabled']) {
				$_G['setting']['verify']['enabled'] = true;
			}
			if ($_G['setting']['verify'][$i]['icon']) {
				$icon_url = parse_url($_G['setting']['verify'][$i]['icon']);
			}
			$_G['setting']['verify'][$i]['icon'] = !$icon_url['host'] ? str_replace($_G['setting']['attachurl'], '', $_G['setting']['verify'][$i]['icon']) : $_G['setting']['verify'][$i]['icon'];
		}
		C::t('setting') -> update('verify', $_G['setting']['verify']);

		updatecache(array('setting'));
		showmessage('members_verify_save_success', ADMINSCRIPT . '?mod=member&op=verifyset', array(), array('alert' => 'right'));
	}
	include template('verifyset_edit');
} else {
	if (!submitcheck('verifysubmit')) {
		for ($i = 1; $i < 8; $i++) {
			$url = parse_url($_G['setting']['verify'][$i]['icon']);
			if (!$url['host'] && $_G['setting']['verify'][$i]['icon'] && strpos($_G['setting']['verify'][$i]['icon'], $_G['setting']['attachurl']) === false) {
				$_G['setting']['verify'][$i]['icon'] = $_G['setting']['attachurl'] . $_G['setting']['verify'][$i]['icon'];
			}

		}

	} else {
		$settingnew = getgpc('settingnew');
		$enabled = false;
		foreach ($settingnew['verify'] as $key => $value) {
			if ($value['available'] && !$value['title']) {
				showmessage('members_verify_title_empty', dreferer());
			}
			if ($value['available']) {
				$enabled = true;
			}
			$_G['setting']['verify'][$key]['available'] = intval($value['available']);
			$_G['setting']['verify'][$key]['title'] = $value['title'];
		}
		$_G['setting']['verify']['enabled'] = $enabled;
		C::t('setting') -> update('verify', $_G['setting']['verify']);
		updatecache(array('setting'));

		showmessage('members_verify_success', dreferer(), array(), array('alert' => 'right'));
	}
	include template('verifyset');
}
function getverifyicon($iconkey = 'iconnew', $target) {
	global $_G, $_GET, $_FILES;

	if ($_FILES[$iconkey]) {
		$iconnew = uploadtolocal($_FILES[$iconkey], 'common', $target);
	} elseif ($_GET['' . $iconkey]) {
		$icon_url = parse_url($_GET['' . $iconkey]);
		if ($icon_url['host'])
			$iconnew = imagetolocal($_GET['' . $iconkey], 'common', $target);
		else
			$iconnew = $_GET['' . $iconkey];
	} else {
		$iconnew = '';
	}
	return $iconnew;
}

function delverifyicon($icon) {
	global $_G;

	$valueparse = parse_url($icon);
	if (!isset($valueparse['host']) && preg_match('/^' . preg_quote($_G['setting']['attachurl'], '/') . '/', $icon)) {
		@unlink($icon);
	}
	return '';
}
?>
