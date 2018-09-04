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
include_once DZZ_ROOT . './data/extdata/exts.php';
require_once libfile('function/user', '', 'user');
$grouptitle = array('0' => lang('all'), '-1' => lang('visitors_visible'), '1' => lang('members_available'), '2' => lang('section_administrators_available'), '3' => lang('system_administrators_available'));

$do = trim($_GET['do']);
$appid = intval($_GET['appid']);
$refer = dreferer();
$op = $_GET['op'];
if (submitcheck('appsubmit')) {
	$appurl = addslashes(trim($_GET['appurl']));
	$appadminurl = addslashes(trim($_GET['appadminurl']));
	$identifier = getstr($_GET['identifier']);
	$app_path = getstr($_GET['app_path']);
	$_GET['appdesc'] = getstr($_GET['appdesc']);
	$_GET['feature'] = getstr($_GET['feature']);
	$_GET['taginput'] = str_replace(array(',', "'", '，'), '', trim($_GET['taginput']));
	$_GET['fileextinput'] = trim($_GET['fileextinput']);
	if (!empty($_GET['fileextinput']))
		$_GET['fileext'][] = $_GET['fileextinput'];
	if (!empty($_GET['taginput']))
		$_GET['tag'][] = $_GET['taginput'];
	$setarr = array(
					'appname' => getstr($_GET['appname'], 80, 0, 0, 0, -1),
					'appurl' => trim($_GET['appurl']),
					'appadminurl' => $appadminurl,
					'identifier' => $identifier,
					'app_path' => $app_path, 
					'noticeurl' => trim($_GET['noticeurl']),
					'haveflash' => intval($_GET['haveflash']),
					'vendor' => trim($_GET['vendor']),
					'hideInMarket' => intval($_GET['hideInMarket']),
					'appdesc' => ($_GET['appdesc']),
					'isshow' => intval($_GET['isshow']),
					'havetask' => intval($_GET['havetask']),
					'feature' => $_GET['feature'],
					'fileext' => $_GET['fileext'],
					'group' => intval($_GET['group']),
					'open' => intval($_GET['open']),
					'nodup' => intval($_GET['nodup'])
				);

	//判断依序的参数是否有值
	$msg = '';
	if (!$setarr['appname']) $msg .= lang('application_appname').lang('not_empty');
	if (!$setarr['appurl']) $msg .= lang('application_site').lang('not_empty');
	if (!$setarr['identifier']) $msg .= lang('application_identifier').lang('not_empty');
	if (!$setarr['app_path']) $msg .= lang('application_app_path').lang('not_empty');

	if (($oappid = DB::result_first("select appid from %t where appurl=%s", array('app_market', $setarr['appurl']))) && $oappid != $appid) {
		$msg .= lang('application_site') .lang('already_exist');
	}
	
	if (($oappid = DB::result_first("select appid from %t where identifier=%s", array('app_market', $setarr['identifier']))) && $oappid != $appid) {
		$msg .= lang('application_identifier') .lang('already_exist');
	}

	if ($msg) showmessage($msg);

	//处理应用图标
	$iconnew = '';
	$target = '';
	if ($appid) {
		$target = DB::result_first("select appico from " . DB::table('app_market') . " where appid='{$appid}'");
	}
	if ($_FILES['iconnew']) {
		if ($_FILES['iconnew']['tmp_name']) {
			if ($appico = uploadtolocal($_FILES['iconnew'], 'appico', $target)) {
				$setarr['appico'] = $appico;
			}
		}
	} else {
		if (!$_GET['iconnew'])
			$_GET['iconnew'] = 'dzz/images/default/icodefault.png';
		if ($_GET['iconnew'] && $_GET['iconnew'] != $_G['setting']['attachurl'] . $target) {
			if ($appico = imagetolocal($_GET['iconnew'], 'appico', $target)) {
				$setarr['appico'] = $appico;
			}
		}
	}
	$picids = $_GET['picids'];
	//删除已有图片
	$delete_picids = $_GET['delete_pics'];
	if ($delete_picids)
		app_pic_delete($delete_picids);

	if ($appid) {
		C::t('app_market') -> update($appid, $setarr);
	} else {
		$setarr['dateline'] = $_G['timestamp'];
		if (!$setarr['appico'])
			$setarr['appico'] = 'dzz/images/default/icodefault.png';
			$setarr["version"]="1.0";//默认版本1.0开始
		$appid = C::t('app_market') -> insert($setarr, 1);
	}
	//处理标签
	C::t('app_tag') -> addtags($_GET['tag'], $appid);
	//更新上传图片的id
	if ($picids)
		C::t('app_pic') -> update($picids, array('appid' => $appid));
	C::t('app_open') -> insert_by_exts($appid, $_GET['fileext']);

	//处理组织机构
	if ($setarr['group'] != 1)
		$orgids = array();
	//只有用户可用时才设置部门
	else
		$orgids = $_GET['orgids'] ? explode(',', $_GET['orgids']) : array();
	C::t('app_organization') -> replace_orgids_by_appid($appid, $orgids);

	showmessage('do_success', $_GET['refer']);

} elseif ($do == 'upload') {
	$picid = 0;
	$uploadfiles = dzz_app_pic_save($_FILES['attach']);
	if ($uploadfiles && is_array($uploadfiles)) {
		$picid = $uploadfiles['picid'];
		$uploadStat = 1;
	} else {
		$uploadStat = $uploadfiles;
	}
	echo "<script>";
	echo "parent.uploadStat = '$uploadStat';";
	echo "parent.picid = $picid;";
	echo "parent.upload();";
	echo "</script>";
	exit();
} else {
	include_once libfile('function/organization');
	$sexts = array();
	foreach ($exts as $ext) {
		$sexts[] = array('name' => $ext);
	}
	$fileext_source = htmlspecialchars(json_encode($sexts));
	$orglist = C::t('organization') -> fetch_all_by_forgid(0);
	$tags = DB::fetch_all("SELECT tagname FROM %t WHERE hot>0 ORDER BY HOT DESC limit 50", array('app_tag'));
	$tag_source = array();
	foreach ($tags as $value) {
		$tag_source[] = array('name' => $value['tagname']);
	}
	$tag_source = htmlspecialchars(json_encode($tag_source));
	$app = array();
	if ($app = dstripslashes(C::t('app_market') -> fetch($appid))) {
		if ($app['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $app['appico'])) {
			$app['appico'] = $_G['setting']['attachurl'] . $app['appico'];
		}
		$apptags = array();
		foreach (C::t('app_relative')->fetch_all_by_appid($app['appid']) as $value) {
			$apptags[] = $value['tagname'];
		}
		if ($apptags)
			$app['tags'] = implode(',', $apptags);
		else
			$app['tags'] = '';
		//$app['fileext']=$app['fileext']?explode(',',$app['fileext']):array();

		$open = $sel = array();
		$orgids = C::t('app_organization') -> fetch_orgids_by_appid($app['appid']);
		if ($orgids) {
			$sel_org = C::t('organization') -> fetch_all($orgids);
			foreach ($sel_org as $key => $value) {
				$orgpath = getPathByOrgid($value['orgid']);
				$sel_org[$key]['orgname'] = implode('-', ($orgpath));
				$sel[] = $value['orgid'];
				$arr = (array_keys($orgpath));
				array_pop($arr);
				$count = count($arr);
				if ($open[$arr[$count - 1]]) {
					if (count($open[$arr[$count - 1]]) > $count)
						$open[$arr[count($arr) - 1]] = $arr;
				} else {
					$open[$arr[$count - 1]] = $arr;
				}
			}
		}
		$sel = implode(',', $sel);
		$openarr = json_encode(array('orgids' => $open));
		$piclist = array();
		$list = C::t('app_pic') -> fetch_all_by_appid($appid, false, true);
		foreach ($list as $value) {
			$value['pic'] = getAttachUrl($value);
			$value['dateline'] = dgmdate($value['dateline'], 'd');
			$piclist[] = $value;
		}
	} else {
		$app = array();
		$app['hideInMarket'] = 0;
		$app['isshow'] = 1;
		$app['havetask'] = 1;
		$app['haveflash'] = 0;
		$app['group'] = 1;
	}
	include template('edit');
}
function app_pic_delete($picids) {
	if (!is_array($picids))
		$picids = array($picids);
	foreach ($picids as $picid) {
		C::t('app_pic') -> delete_by_picid($picid);
	}
}
?>
