<?php
/*
 * 此应用的通知接口
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
include libfile('function/code');

$do = trim($_GET['do']);
$guests = array('getcomment', 'getThread', 'getNewThreads', 'getReply', 'getReplys', 'getUserToJson');
if (empty($_G['uid']) && !in_array($do, $guests)) {
	include  template('common/header_ajax');
	/*echo "<script type=\"text/javascript\">";
	 echo "try{top._login.logging();}catch(e){}";
	 echo "</script>";	*/
	echo '&nbsp;&nbsp;&nbsp;<a href="user.php?mod=login" class="btn btn-primary">'.lang('login').'</a>';
	if( $_G['setting']['regstatus']>0){
		echo '&nbsp;&nbsp;&nbsp;<a href="user.php?mod=register" class="btn btn-success">'.lang('register').'</a>';
	}
	include  template('common/footer_ajax');
	exit();
}

if (submitcheck('replysubmit')) {

	$message = censor($_GET['message']);

	if (empty($message)) {
		showmessage('please_enter_comment', DZZSCRIPT . '?mod=comment', array());
	}
	//处理@
	$at_users = array();
	$message = preg_replace_callback("/@\[(.+?):(.+?)\]/i", "atreplacement", $message);
	$setarr = array('author' => $_G['username'], 'authorid' => $_G['uid'], 'pcid' => intval($_GET['pcid']), 'rcid' => intval($_GET['rcid']), 'id' => getstr($_GET['id'], 60), 'idtype' => trim($_GET['idtype']), 'module' => trim($_GET['module']), 'ip' => $_G['clientip'], 'dateline' => TIMESTAMP, 'message' => $message, );
	if (!$setarr['cid'] = C::t('comment') -> insert_by_cid($setarr, $at_users, $_GET['attach'])) {
		showmessage('internal_server_error', DZZSCRIPT . '?mod=comment', array('message' => $message));
	}
	$setarr['attachs'] = C::t('comment_attach') -> fetch_all_by_cid($setarr['cid']);
	$setarr['dateline'] = dgmdate($setarr['dateline'], 'u');
	$setarr['message'] = dzzcode($message);
	$setarr['allowattach'] = intval($_GET['allowattach']);
	$setarr['allowat'] = intval($_GET['allowat']);
	$setarr['allowsmiley'] = intval($_GET['allowsmiley']);
	$setarr['avatar']=avatar_block($setarr['authorid']);
	if ($_G['adminid'] == 1 || $_G['uid'] == $setarr['authorid'])
		$setarr['haveperm'] = 1;
	showmessage('do_success', DZZSCRIPT . '?mod=comment', array('data' => rawurlencode(json_encode($setarr))));
} elseif ($do == 'edit') {
	$cid = intval($_GET['cid']);
	if ($data = C::t('comment') -> fetch($cid)) {
		$data['message'] = dzzcode($data['message'], 0, 0, 0, 0, 1);
		if (!$_G['adminid'] == 1 && $_G['uid'] != $data['authorid'])
			showmessage('privilege');
	} else {
		showmessage('discuss_nonentity_del');
	}
	if (!submitcheck('editsubmit')) {
		$data['attachs'] = C::t('comment_attach') -> fetch_all_by_cid($cid);
		if ($data['rcid'])
			$data['rpost'] = C::t('comment') -> fetch($data['rcid']);
		$space = dzzgetspace($_G['uid']);
		$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();
		$space['maxattachsize'] = intval($space['maxattachsize']);
	} else {
		C::t('comment') -> update_by_cid($cid, censor($_GET['message']), intval($_GET['rcid']), $_GET['attach']);
		$value = array();
		if ($value = C::t('comment') -> fetch($cid)) {
			$value['message'] = dzzcode($value['message']);
			$value['dateline'] = dgmdate($value['dateline'], 'u');
			$value['attachs'] = C::t('comment_attach') -> fetch_all_by_cid($value['cid']);
			if ($value['rcid']) {
				$value['rpost'] = C::t('comment') -> fetch($value['rcid']);
			}
			$value['allowattach'] = intval($_GET['allowattach']);
			$value['allowat'] = intval($_GET['allowat']);
			$value['allowsmiley'] = intval($_GET['allowsmiley']);
			$value['avatar']=avatar_block($value['authorid']);
		}
		showmessage('do_success', DZZSCRIPT . '?mod=comment', array('data' => rawurlencode(json_encode($value))));
	}

} elseif ($do == 'getcomment') {

	$id = getstr($_GET['id'], 60);
	$idtype = trim($_GET['idtype']);
	$page = empty($_GET['page']) ? 1 : intval($_GET['page']); 
	$perpage = 10;
	$start = ($page - 1) * $perpage;
	$limit = $start . "-" . $perpage;
	$gets = array('mod' => 'comment', 'op' => 'ajax', 'do' => 'getcomment', 'id' => $id, 'idtype' => $idtype, );
	$theurl = BASESCRIPT . "?" . url_implode($gets);
	$count = C::t('comment') -> fetch_all_by_idtype($id, $idtype, $limit, true);
	$list = array();
	if ($count) {
		$list = C::t('comment') -> fetch_all_by_idtype($id, $idtype, $limit);
	}
	$multi = multi($count, $perpage, $page, $theurl, 'pull-right');
} elseif ($do == 'getcommentbycid') {
	$cid = intval($_GET['cid']);

	if ($value = C::t('comment') -> fetch($cid)) {
		$value['message'] = dzzcode($value['message']);
		$value['dateline'] = dgmdate($value['dateline'], 'u');
		$value['attachs'] = C::t('comment_attach') -> fetch_all_by_cid($value['cid']);

		if ($value['rcid']) {
			$value['rpost'] = C::t('comment') -> fetch($value['rcid']);
		}
		$value['replies'] = DB::result_first("select COUNT(*) from  %t where pcid=%d", array('comment', $value['cid']));
		$value['replys'] = C::t('comment') -> fetch_all_by_pcid($value['cid'], 5);

	}
} elseif ($do == 'getreplys') {
	$cid = intval($_GET['cid']);
	$limit = empty($_GET['limit']) ? 0 : intval($_GET['limit']);
	$count = C::t('comment') -> fetch_all_by_pcid($cid, $limit, true);
	if ($count) {
		$list = C::t('comment') -> fetch_all_by_pcid($cid, $limit);
	}
} elseif ($do == 'delete') {
	$cid = intval($_GET['cid']);
	$data = C::t('comment') -> fetch($cid);
	if ($_G['adminid'] != 1 && $_G['uid'] != $data['authorid'])
		exit(json_encode(array('msg' => lang('privilege'))));
	if (C::t('comment') -> delete_by_cid($cid)) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('delete_error'))));
	}
} elseif ($do == 'upload') {
	include_once  libfile('class/uploadhandler');
	$space = dzzgetspace($_G['uid']);
	$allowedExtensions = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();

	// max file size in bytes
	$sizeLimit = intval($space['maxattachsize']);

	$options = array('accept_file_types' => $allowedExtensions ? ("/(\.|\/)(" . implode('|', $allowedExtensions) . ")$/i") : "/.+$/i", 'max_file_size' => $sizeLimit ? $sizeLimit : null, 'upload_dir' => $_G['setting']['attachdir'] . 'cache/', 'upload_url' => $_G['setting']['attachurl'] . 'cache/', );
	$upload_handler = new uploadhandler($options);
	exit();

} elseif ($do == 'getPublishForm') {
	$id = getstr($_GET['id'], 60);
	$idtype = trim($_GET['idtype']);
	$module = trim($_GET['module']);
	$space = dzzgetspace($_G['uid']);
	$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();
	$space['maxattachsize'] = intval($space['maxattachsize']);
	$pcid = 0;
} elseif ($do == 'getReplyForm') {
	$id = getstr($_GET['id'], 60);
	$idtype = trim($_GET['idtype']);
	$module = trim($_GET['module']);
	$cid = intval($_GET['cid']);
	if ($cid) {
		$data = C::t('comment') -> fetch($cid);
		$id = $data['id'];
		$idtype = $data['idtype'];
		$module = $data['module'];
	}
	$space = dzzgetspace($_G['uid']);
	$space['attachextensions'] = $space['attachextensions'] ? explode(',', $space['attachextensions']) : array();
	$space['maxattachsize'] = intval($space['maxattachsize']);
}
function atreplacement($matches) {
	global $at_users, $_G;

	include_once  libfile('function/organization');
	if (strpos($matches[2], 'g') !== false) {
		$gid = str_replace('g', '', $matches[2]);
		if (($org = C::t('organization') -> fetch($gid)) && checkAtPerm($gid)) {//判定用户有没有权限@此部门
			$uids = getUserByOrgid($gid, true, array(), true);
			foreach ($uids as $uid) {
				if ($uid != $_G['uid'])
					$at_users[] = $uid;
			}
			return '[org=' . $gid . '] @' . $org['orgname'] . '[/org]';
		} else {
			return '';
		}
	} else {
		$uid = str_replace('u', '', $matches[2]);
		if (($user = C::t('user') -> fetch($uid)) && $user['uid'] != $_G['uid']) {
			$at_users[] = $user['uid'];
			return '[uid=' . $user['uid'] . ']@' . $user['username'] . '[/uid]';
		} else {
			return $matches[0];
		}
	}
}

include template('ajax');
?>
