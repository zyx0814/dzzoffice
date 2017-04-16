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

include_once libfile('function/organization');
$do = trim($_GET['do']);
$orgid = intval($_GET['orgid']);
if ($do == 'getchildren') {
	$id = intval($_GET['id']);
	$list = array();
	$limit = 0;
	$html = '';

	//判断用户有没有操作权限
	$ismoderator = C::t('organization_admin') -> ismoderator_by_uid_orgid($id, $_G['uid']);
	if ($ismoderator) {
		$disable = '';
		$type = 'user';
	} else {
		$disable = '"disabled":true,';
		$type = "disabled";
	}
	if ($id) {
		$icon = 'dzz/system/images/department.png';
	} else {
		$icon = 'dzz/system/images/organization.png';
	}
	$data = array();
	if ($_GET['id'] == '#') {
		if($_G['adminid']!=1) $topids=C::t('organization_admin')->fetch_toporgids_by_uid($_G['uid']);
		foreach (C::t('organization')->fetch_all_by_forgid($id) as $value) {
			if($_G['adminid']!=1 && !in_array($value['orgid'],$topids)) continue;
			if (C::t('organization_admin') -> ismoderator_by_uid_orgid($value['orgid'], $_G['uid'])) {
				$orgdisable = false;
				$orgtype = 'organization';
			} else {
				$orgdisable = true;
				$orgtype = 'disabled';
			}
			$data[] = array('id' => $value['orgid'], 'text' => $value['orgname'], 'icon' => $icon, 'state' => array('disabled' => $orgdisable), "type" => $orgtype, 'children' => true);
		}

		$data[] = array('id' => 'other', 'text' => lang('no_institution_users'), 'icon' => 'dzz/system/images/department.png', 'state' => array('disabled' => $disable), "type" => 'default', 'children' => true);

	} else {
		//获取用户列表

		if (!$id) {
			if ($ismoderator) {
				foreach (C::t('organization_user')->fetch_user_not_in_orgid($limit) as $value) {
					$data[] = array('id' => 'uid_' . $value['uid'], 'text' => $value['username'] . '<em class="hide">' . $value['email'] . '</em>', 'icon' => 'dzz/system/images/user.png', 'state' => array('disabled' => $disable), "type" => $type, 'li_attr' => array('uid' => $value['uid']));
				}
			}
		} else {
			foreach (C::t('organization')->fetch_all_by_forgid($id) as $value) {
				if (C::t('organization_admin') -> ismoderator_by_uid_orgid($value['orgid'], $_G['uid'])) {
					$orgdisable = '';
					$orgtype = 'organization';
				} else {
					$orgdisable = '"disabled":true,';
					$orgtype = 'disabled';
				}
				$data[] = array('id' => $value['orgid'], 'text' => $value['orgname'], 'icon' => $icon, 'state' => array('disabled' => $orgdisable), "type" => $orgtype, 'children' => true);
			}
			if ($ismoderator) {
				foreach (C::t('organization_user')->fetch_user_by_orgid($id,$limit) as $value) {
					$data[] = array('id' => 'orgid_' . $value['orgid'] . '_uid_' . $value['uid'], 'text' => $value['username'] . '<em class="hide">' . $value['email'] . '</em>', 'icon' => 'dzz/system/images/user.png', 'state' => array('disabled' => $disable), "type" => $type, 'li_attr' => array('uid' => $value['uid']));
				}
			}
		}

	}
	exit(json_encode($data));
} elseif ($do == 'search') {//jstree搜索接口
	$str = trim($_GET['str']);
	$str = '%' . $str . '%';
	$sql = "username LIKE %s";
	//搜索用户
	$data = array('other');
	$uids = array();
	foreach (DB::fetch_all("select * from %t where $sql ",array('user',$str)) as $value) {
		$uids[] = $value['uid'];
		$data['uid_' . $value['uid']] = 'uid_' . $value['uid'];
	}
	$orgids = array();
	foreach ($orgusers=C::t('organization_user')->fetch_all_by_uid($uids) as $value) {
		$data['uid_' . $value['uid']] = 'orgid_' . $value['orgid'] . '_uid_' . $value['uid'];
		$orgids[] = $value['orgid'];
	}

	foreach ($orgids as $orgid) {
		$uporgids = getUpOrgidTree($orgid, true);
		foreach ($uporgids as $value) {
			$data[$value] = $value;
		}
	}
	$temp = array();
	foreach ($data as $value) {
		$temp[] = $value;
	}
	exit(json_encode($temp));

} elseif ($do == 'getjobs') {
	$orgid = intval($_GET['orgid']);
	$jobs = C::t('organization_job') -> fetch_all_by_orgid($orgid);
	$html = '<li role="presentation"><a href="javascript:;" tabindex="-1" role="menuitem" _jobid="0" onclick="selJob(this)">'.lang('none').'</a></li>';
	foreach ($jobs as $job) {
		$html .= '<li role="presentation"><a href="javascript:;" tabindex="-1" role="menuitem" _jobid="' . $job['jobid'] . '" onclick="selJob(this)">' . $job['name'] . '</a></li>';
	}
	exit($html);
} elseif ($do == 'create') {
	$forgid = intval($_GET['forgid']);
	$borgid = intval($_GET['orgid']);
	//放在此部门后面
	if (!$ismoderator = C::t('organization_admin') -> ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	$setarr = array('forgid' => intval($_GET['forgid']), 'orgname' => lang('new_department'), 'fid' => 0, 'disp' => intval($_GET['disp']), 'indesk' => 0, 'dateline' => TIMESTAMP, 'available' => 0);

	if ($setarr = C::t('organization') -> insert_by_forgid($setarr, $borgid)) {
		include_once  libfile('function/cache');
		updatecache('organization');
	} else {
		$setarr['error'] = 'create organization failure';
	}

	exit(json_encode($setarr));
} elseif ($do == 'rename') {
	$orgid = intval($_GET['orgid']);
	if (!$ismoderator = C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization') -> update_by_orgid($orgid, array('orgname' => getstr($_GET['text'])))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('msg' => lang('rechristen_error'))));
	}
} elseif ($do == 'delete') {

	if ($_GET['type'] == 'user') {//删除用户
		$forgid = intval($_GET['forgid']);
		$uids = $_GET['uids'];
		$realdelete = intval($_GET['realdelete']);
		if ($realdelete) {
			if ($_G['adminid'] != 1)
				exit(json_encode(array('error' => lang('privilege'))));
			//判断用户是否在部门中，在部门中的用户不彻底删除
			if (C::t('organization_user') -> fetch_orgids_by_uid($uids)) {
				exit(json_encode(array('error' => lang('orguser_ajax_delete'))));
			}
			foreach ($uids as $uid) {
				//删除用户
				C::t('user') -> delete_by_uid($uid);
			}
			exit(json_encode(array('msg' => 'success')));
		} else {
			if (!$ismoderator = C::t('organization_admin') -> ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
				exit(json_encode(array('error' => lang('privilege'))));
			}
			if (C::t('organization_user') -> delete_by_uid_orgid($uids, $forgid)) {
				exit(json_encode(array('msg' => 'success')));
			} else {
				exit(json_encode(array('msg' => lang('delete_error'))));
			}
		}

	} else {
		$orgid = ($_GET['orgid']);
		$forgid = intval($_GET['forgid']);
		if (!$ismoderator = C::t('organization_admin') -> ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
			exit(json_encode(array('error' => loang('privilege'))));
		}
		if ($return = C::t('organization') -> delete_by_orgid($orgid)) {//删除部门，部门的用户移动到上级部门去;
			if ($return['error']) {
				exit(json_encode($return));
			}
			exit(json_encode(array('msg' => 'success')));
		} else {
			exit(json_encode(array('msg' => lang('delete_error'))));
		}
	}
} elseif ($do == 'move') {

	if ($_GET['type'] == 'user') {//移动用户
		$orgid = intval($_GET['orgid']);
		$forgid = intval($_GET['forgid']);
		if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
			exit(json_encode(array('error' => lang('privilege'))));
		}
		if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
			exit(json_encode(array('error' => lang('privilege'))));
		}
		$copy = intval($_GET['copy']);

		$uid = intval($_GET['uid']);
		if (C::t('organization_user') -> move_to_by_uid_orgid($uid, $forgid, $orgid, $copy)) {
			exit(json_encode(array('msg' => 'success')));
		} else {
			exit(json_encode(array('error' => lang('movement_error'))));
		}
	} else {
		$orgid = intval($_GET['orgid']);
		$disp = intval($_GET['position']);
		$forgid = intval($_GET['forgid']);
		if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($forgid, $_G['uid'])) {
			exit(json_encode(array('error' => lang('privilege'))));
		}
		if (C::t('organization') -> setDispByOrgid($orgid, $disp, $forgid)) {//移动部门;
			exit(json_encode(array('msg' => 'success')));
		} else {
			exit(json_encode(array('msg' => lang('delete_error'))));
		}
	}
} elseif ($do == 'jobedit') {
	$jobid = intval($_GET['jobid']);
	$orgid = intval($_GET['orgid']);
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	$name = str_replace('...', '', getstr($_GET['name'], 30));
	if (C::t('organization_job') -> update($jobid, array('name' => $name))) {
		exit(json_encode(array('jobid' => $jobid, 'name' => $name)));
	} else {
		exit(json_encode(array('error' => lang('edit_error'))));
	}
} elseif ($do == 'jobdel') {
	$jobid = intval($_GET['jobid']);
	$orgid = intval($_GET['orgid']);
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization_job') -> delete($jobid)) {
		exit(json_encode(array('jobid' => $jobid)));
	} else {
		exit(json_encode(array('error' => lang('delete_unsuccess'))));
	}
} elseif ($do == 'jobadd') {
	$orgid = intval($_GET['orgid']);
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	$setarr = array('orgid' => $orgid, 'name' => str_replace('...', '', getstr($_GET['name'], 30)), 'dateline' => TIMESTAMP, 'opuid' => $_G['uid']);
	if ($setarr['jobid'] = C::t('organization_job') -> insert($setarr)) {
		exit(json_encode($setarr));
	} else {
		exit(json_encode(array('error' => lang('add_unsuccess'))));
	}
} elseif ($do == 'moderator_add') {
	$orgid = intval($_GET['orgid']);
	$org = C::t('organization') -> fetch($orgid);
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($org['forgid'], $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	$setarr = array('orgid' => $orgid, 'uid' => intval($_GET['uid']), 'dateline' => TIMESTAMP, 'opuid' => $_G['uid']);
	if ($setarr['id'] = C::t('organization_admin') -> insert(intval($_GET['uid']), $orgid)) {
		$user = getuserbyuid($setarr['uid']);
		$setarr['username'] = $user['username'];
		exit(json_encode($setarr));
	} else {
		exit(json_encode(array('error' => lang('add_administrator_unsuccess'))));
	}
} elseif ($do == 'moderator_del') {
	$orgid = intval($_GET['orgid']);
	$org = C::t('organization_admin') -> fetch($orgid);
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($org['forgid'], $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization_admin') -> delete_by_id(intval($_GET['id']))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('add_administrator_unsuccess'))));
	}
} elseif ($do == 'folder_available') {
	$orgid = intval($_GET['orgid']);

	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization') -> setFolderAvailableByOrgid($orgid, intval($_GET['available']))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('unable_set'))));
	}
} elseif ($do == 'folder_indesk') {
	$orgid = intval($_GET['orgid']);

	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization') -> setIndeskByOrgid($orgid, intval($_GET['indesk']))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('no_open_Shared_directory'))));
	}

} elseif ($do == 'guide') {
	include template('guide');
}
exit();
?>
