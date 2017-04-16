<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
if (!$_G['uid']) {
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();win.Close();}catch(e){location.href='user.php?mod=logging';}";
	echo "</script>";
	include template('common/footer_reload');
	exit();
}
require './dzz/function/dzz_core.php';
include_once libfile('function/profile');
include_once libfile('function/code');
$uid = intval($_G['uid']);
$verify = C::t('user_verify') -> fetch($_G['uid']);
//error_reporting(E_ALL);
$space = getuserbyuid($uid);
space_merge($space, 'profile1');
$userstatus = C::t('user_status') -> fetch($uid);
$qqlogin = DB::fetch_first("select openid,unbind from %t where uid=%d", array('user_qqconnect', $uid));
if (empty($_G['cache']['profilesetting'])) {
	require_once  libfile('function/cache');
	updatecache('profilesetting');
	loadcache('profilesetting');
}

if (submitcheck('profilesubmit')) {

	$setarr = $verifyarr = $errorarr = array();

	$censor = dzz_censor::instance();
	$vid = intval($_GET['vid']);
	if ($vid) {
		$verifyconfig = $_G['setting']['verify'][$vid];
		if ($verifyconfig['available'] && (empty($verifyconfig['groupid']) || in_array($_G['groupid'], $verifyconfig['groupid']))) {
			$verifyinfo = C::t('user_verify_info') -> fetch_by_uid_verifytype($_G['uid'], $vid);
			if (!empty($verifyinfo)) {
				$verifyinfo['field'] = dunserialize($verifyinfo['field']);
			}
			foreach ($verifyconfig['field'] as $key => $field) {
				if (!isset($verifyinfo['field'][$key])) {
					$verifyinfo['field'][$key] = $key;
				}
			}
		} else {
			$_GET['vid'] = $vid = 0;
			$verifyconfig = array();
		}
	}
	
	$updatearr_user=array();
	foreach ($_POST as $key => $value) {
		
		$field = $_G['cache']['profilesetting'][$key];
		if (in_array($field['formtype'], array('text', 'textarea'))) {
			$value = $censor -> replace($value);
		}
		
		if ($key == 'language') {
			if(!in_array($value,array_keys($_G['config']['output']['language_list']))) $value='';
			$updatearr_user[$key]=$value;
		
			if(empty($value)) $value=checklanguage();
			if($_G['language']!=$value) $language_updated=1;
			dsetcookie('language',$value,60*60*24*365);
			//判断语言是否改变
			
			//C::t('user') -> update($_G['uid'], array('language' => $value));
		}
		
		if ($field && !$field['available']) {
			continue;
		} elseif ($key == 'timeoffset') {
			if ($value >= -12 && $value <= 12 || $value == 9999) {
				$updatearr_user[$key]=intval($value);
				//C::t('user') -> update($_G['uid'], array('timeoffset' => intval($value)));
			}
		} elseif ($key == 'site') {
			if (!in_array(strtolower(substr($value, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $value) && !preg_match('/^data\//', $value)) {
				$value = 'http://' . $value;
			}
		}
		if ($field['formtype'] == 'file') {
			if ((!empty($_FILES[$key]) && $_FILES[$key]['error'] == 0) || (!empty($space[$key]) && empty($_GET['deletefile'][$key]))) {
				$value = '1';
			} else {
				$value = '';
			}
		}
		
		if (empty($field)) {
			continue;
		} elseif (profile_check($key, $value, $space)) {
			$setarr[$key] = dhtmlspecialchars(trim($value));
		} else {
			if ($key == 'birthyear' || $key == 'birthmonth') {
				$key = 'birthday';
			}
			profile_showerror($key);
		}
		if ($field['formtype'] == 'file') {
			unset($setarr[$key]);
		}
		if ($vid && $verifyconfig['available'] && isset($verifyconfig['field'][$key])) {
			if (isset($verifyinfo['field'][$key]) && $setarr[$key] != $verifyinfo['field'][$key]) {
				$verifyarr[$key] = $setarr[$key];
			}
			unset($setarr[$key]);
		}
		if (isset($setarr[$key]) && $_G['cache']['profilesetting'][$key]['needverify']) {
			if ($setarr[$key] != $space[$key]) {
				$verifyarr[$key] = $setarr[$key];
			}
			unset($setarr[$key]);
		}

	}
	if($updatearr_user) C::t('user') -> update($_G['uid'], $updatearr_user);
	if ($_GET['deletefile'] && is_array($_GET['deletefile'])) {
		foreach ($_GET['deletefile'] as $key => $value) {
			if (isset($_G['cache']['profilesetting'][$key]) && $_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
				@unlink(getglobal('setting/attachdir') . $space[$key]);
				@unlink(getglobal('setting/attachdir') . $verifyinfo['field'][$key]);
				$verifyarr[$key] = $setarr[$key] = '';
			}
		}
	}
	if ($_FILES) {
		foreach ($_FILES as $key => $file) {
			if (!isset($_G['cache']['profilesetting'][$key])) {
				continue;
			}
			$field = $_G['cache']['profilesetting'][$key];
			if ((!empty($file) && $file['error'] == 0) || (!empty($space[$key]) && empty($_GET['deletefile'][$key]))) {
				$value = '1';
			} else {
				$value = '';
			}
			if (!profile_check($key, $value, $space)) {
				profile_showerror($key);
			} elseif ($field['size'] && $field['size'] * 1024 < $file['size']) {
				profile_showerror($key, lang('filesize_lessthan') . $field['size'] . 'KB');
			}
			if ($attachment = uploadtolocal($file, 'profile', '')) {
				if (!@getimagesize($_G['setting']['attachdir'] . $attachment)) {//判断是否为图片文件
					@unlink($_G['setting']['attachdir'] . $attachment);
					continue;
				}
				$setarr[$key] = '';
				//$attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
				if ($vid && $verifyconfig['available'] && isset($verifyconfig['field'][$key])) {
					if (isset($verifyinfo['field'][$key])) {
						@unlink(getglobal('setting/attachdir') . $verifyinfo['field'][$key]);
						$verifyarr[$key] = $attachment;
					}
					continue;
				}
				if (isset($setarr[$key]) && $_G['cache']['profilesetting'][$key]['needverify']) {
					@unlink(getglobal('setting/attachdir') . $verifyinfo['field'][$key]);
					$verifyarr[$key] = $attachment;
					continue;
				}
				@unlink(getglobal('setting/attachdir') . $space[$key]);
				$setarr[$key] = $attachment;
			}

		}
	}
	if ($vid && !empty($verifyinfo['field']) && is_array($verifyinfo['field'])) {
		foreach ($verifyinfo['field'] as $key => $fvalue) {
			if (!isset($verifyconfig['field'][$key])) {
				unset($verifyinfo['field'][$key]);
				continue;
			}
			if (empty($verifyarr[$key]) && !isset($verifyarr[$key]) && isset($verifyinfo['field'][$key])) {
				$verifyarr[$key] = !empty($fvalue) && $key != $fvalue ? $fvalue : $space[$key];
			}
		}
	}

	if (isset($_POST['birthmonth']) && ($space['birthmonth'] != $_POST['birthmonth'] || $space['birthday'] != $_POST['birthday'])) {
		$setarr['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
	}
	if (isset($_POST['birthyear']) && $space['birthyear'] != $_POST['birthyear']) {
		$setarr['zodiac'] = get_zodiac($_POST['birthyear']);
	}
	if ($setarr) {
		C::t('user_profile1') -> update($_G['uid'], $setarr);
	}
	if ($verifyarr) {
		$overifyinfo = C::t('user_verify_info') -> fetch_by_uid_verifytype($_G['uid'], $vid);
		if (!empty($overifyinfo)) {
			$overifyinfo['field'] = dunserialize($overifyinfo['field']);
		}
		foreach ($overifyinfo['field'] as $key => $value) {
			if ($_G['cache']['profilesetting'][$key]['needverify'] && !isset($verifyarr[$key])) {
				$verifyarr[$key] = $value;
			}
		}
		C::t('user_verify_info') -> delete_by_uid($_G['uid'], $vid);
		$setverify = array('uid' => $_G['uid'], 'username' => $_G['username'], 'verifytype' => $vid, 'field' => serialize($verifyarr), 'dateline' => $_G['timestamp'], 'orgid' => intval($verifyarr['department']));
		C::t('user_verify_info') -> insert($setverify);
		if (!(C::t('user_verify') -> count_by_uid($_G['uid']))) {
			C::t('user_verify') -> insert(array('uid' => $_G['uid']));
		}
		if ($_G['setting']['verify'][$vid]['available']) {
			//发送通知管理员有资料需要审核
			$appid = C::t('app_market') -> fetch_appid_by_mod('{adminscript}?mod=member', 1);
			foreach (C::t('user')->fetch_all_by_adminid(1) as $value) {
				if ($value['uid'] != $_G['uid']) {
					//发送通知
					$notevars = array('from_id' => $appid, 'from_idtype' => 'app', 'url' => 'admin.php?mod=member&op=verify&vid=' . $vid, 'author' => getglobal('username'), 'authorid' => getglobal('uid'), 'dataline' => dgmdate(TIMESTAMP), 'title' => $vid ? $_G['setting']['verify'][$vid]['title'] : lang('members_verify_profile'), );
					$action = 'profile_moderate';
					$type = 'profile_moderate_' . $vid;

					dzz_notification::notification_add($value['uid'], $type, $action, $notevars);
				}
			}
		}
	}

	if (isset($_POST['privacy'])) {
		foreach ($_POST['privacy'] as $key => $value) {
			if (isset($_G['cache']['profilesetting'][$key])) {

				$space['privacy']['profile'][$key] = intval($value);
			}
		}
		C::t('user_field') -> update($space['uid'], array('privacy' => serialize($space['privacy'])));
	}

	countprofileprogress();
	//检查语言是否更改
	
	$message = $vid ? lang('profile_verify_verifying', array('verify' => $verifyconfig['title'])) :($language_updated?'language updated':'');
	profile_showsuccess($message);

} elseif ($_GET['action'] == 'qq_unbind') {
	C::t('user_qqconnect') -> delete($_GET['openid']);
	showmessage('cancel_qq_bound_succeed', dreferer(), array(), array('alert' => 'right'));
} else {
	space_merge($space, 'field');

	$vid = $_GET['vid'] ? intval($_GET['vid']) : 0;

	$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();
	$_G['setting']['privacy'] = $_G['setting']['privacy'] ? $_G['setting']['privacy'] : array();
	$_G['setting']['privacy'] = is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : dunserialize($_G['setting']['privacy']);
	$_G['setting']['privacy']['profile'] = !empty($_G['setting']['privacy']['profile']) ? $_G['setting']['privacy']['profile'] : array();
	$privacy = array_merge($_G['setting']['privacy']['profile'], $privacy);

	if ($vid) {
		$allowitems = array();
		if (empty($_G['setting']['verify'][$vid]['groupid']) || in_array($_G['groupid'], $_G['setting']['verify'][$vid]['groupid'])) {
			//$actives = array('verify' =>' class="a"');
			//$opactives = array($operation.$vid =>' class="a"');
			$allowitems = $_G['setting']['verify'][$vid]['field'];
		}
	} else {
		$allowitems = array();
		$verifyfieldid = array();
		//在认证里的资料项只在认证页里出现
		foreach ($_G['setting']['verify'] as $key => $value) {
			if ($value['available'] && $value['field']) {
				$verifyfieldid = array_merge($verifyfieldid, $value['field']);
			}
		}
		foreach ($_G['cache']['profilesetting'] as $key => $value) {
			if ($value['available'] > 0 && !in_array($key, $verifyfieldid))
				$allowitems[] = $key;
		}
		$allowitems[] = 'timeoffset';
	}
	$showbtn = ($vid && $verify['verify' . $vid] != 1) || empty($vid);
	/*if(!empty($verify) && is_array($verify)) {
		foreach ($verify as $key => $flag) {
			if (in_array($key, array('verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7')) && $flag == 1) {
				$verifyid = intval(substr($key, -1, 1));
				if ($_G['setting']['verify'][$verifyid]['available']) {
					foreach ($_G['setting']['verify'][$verifyid]['field'] as $field) {
						$_G['cache']['profilesetting'][$field]['unchangeable'] = 1;
					}
				}
			}
		}
	}*/
	if ($vid) {
		if ($value = C::t('user_verify_info') -> fetch_by_uid_verifytype($_G['uid'], $vid)) {
			$field = dunserialize($value['field']);
			foreach ($field as $key => $fvalue) {
				if($key=='department'){
					if($fvalue){
						$space['department_tree']=C::t('organization')->getPathByOrgid(intval($fvalue));
					}else{
						$space['department_tree']=C::t('organization')->lang('please_select_a_organization_or_department');
					}
				}
				$space[$key] = $fvalue;
			}
		}
	}
	$htmls = $settings = array();
	foreach ($allowitems as $fieldid) {
		if (!in_array($fieldid, array('timeoffset'))) {
			if($showbtn){
				$html = profile_setting($fieldid, $space, $vid ? false : true);
			}else{
				if($html = profile_show($fieldid, $space)){
					$html = '<p class="form-control-static">'.$html.'</p>';
				}
			}
			if ($html) {
				$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
				$htmls[$fieldid] = $html;
			}
		}
	}
	$langList = $_G['config']['output']['language_list'];
	include template('profile');
}

function profile_showerror($key, $extrainfo) {
	echo '<script>';
	echo 'parent.show_error("' . $key . '", "' . $extrainfo . '");';
	echo '</script>';
	exit();
}

function profile_showsuccess($message = '') {
	echo '<script type="text/javascript">';
	echo "parent.show_success('$message');";
	echo '</script>';
	exit();
}
?>
