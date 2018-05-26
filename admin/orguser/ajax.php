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
function getuserIcon($uids,$datas,&$data){
	$uids = array_unique($uids);
	$avatars = array();
	foreach(DB::fetch_all('select u.avatarstatus,u.uid,s.svalue from %t u left join %t s on u.uid=s.uid and s.skey=%s where u.uid in(%n)',array('user','user_setting','headerColor',$uids)) as $v){
		if($v['avatarstatus'] == 1){
			$avatars[$v['uid']]['avatarstatus'] = 1;
		}else{
			$avatars[$v['uid']]['avatarstatus'] = 0;
			$avatars[$v['uid']]['headerColor'] = $v['svalue'];
		}
	}
	$userarr = array();
	$data1 = array();
	foreach($datas as $v){
		$uid=$v['li_attr']['uid'];
		$avatarstatus = $avatars[$uid]['avatarstatus'];
		if($avatars[$v['li_attr']['uid']]['avatarstatus']){
			$v['icon'] = 'avatar.php?uid='.$v['li_attr']['uid'];
		}elseif($avatars[$uid]['headerColor']){
			$headercolor = $avatars[$uid]['headerColor'];
			$v['icon'] = false;
			$v['text']= '<span class="iconFirstWord" style="background:'.$headercolor.';">'.strtoupper(new_strsubstr($v['text'],1,'')).'</span>'.$v['text'];
	
		}else{
			$v['icon'] = false;
			$v['text']= avatar_block($uid,array(),'iconFirstWord').$v['text'];
		}
		$data[] = $v;
	}
}
$do = trim($_GET['do']);
$orgid = intval($_GET['orgid']);
if ($do == 'upload') {//上传图片文件
    include libfile('class/uploadhandler');
    $options = array('accept_file_types' => '/\.(gif|jpe?g|png)$/i',
        'upload_dir' => $_G['setting']['attachdir'] . 'cache/',
        'upload_url' => $_G['setting']['attachurl'] . 'cache/',
        'thumbnail' => array('max-width' => 40, 'max-height' => 40));
    $upload_handler = new uploadhandler($options);
    exit();
} /*elseif ($do == 'getdefaultpic') {//获取群组默认图片，上传图片保存
    $imgs = C::t('resources_grouppic')->fetch_user_pic();
    if (isset($_GET['aid'])) {
        $aid = intval($_GET['aid']);
        if ($_G['adminid'] == 1) $dafault = 1;
        else $default = 0;
        if (C::t('resources_grouppic')->insert_data($aid, $default)) {
            showTips(array('success' => true), 'json');
        } else {
            showTips(array('error' => true), 'json');
        }
    }
}*/elseif($do == 'getchildren') {
	
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
		foreach (C::t('organization')->fetch_all_by_forgid($id,0,0) as $value) {
			if($value['type']==1) continue;//过滤群
			if($_G['adminid']!=1 && !in_array($value['orgid'],$topids)) continue;
			if (C::t('organization_admin') -> ismoderator_by_uid_orgid($value['orgid'], $_G['uid'])) {
				$orgdisable = false;
				$orgtype = 'organization';
			} else {
				$orgdisable = true;
				$orgtype = 'disabled';
			}
			$arr=array('id' => $value['orgid'], 'text' => $value['orgname'], 'icon' => $icon, 'state' => array('disabled' => $orgdisable), "type" => $orgtype, 'children' => true);
			if(intval($value['aid'])==0){
					$arr['text'] = avatar_group($value['orgid'],array($value['orgid']=>array('aid'=>$value['aid'],'orgname'=>$value['orgname']))).$value['orgname'];
					$arr['icon'] = false;
				}else{
					$arr['text'] = $value['orgname'];
					$arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $value['aid']);
				}
			$data[]=$arr;
			
		}

		$data[] = array('id' => 'other', 'text' => lang('no_institution_users'), 'state' => array('disabled' => $disable), "type" => 'group', 'children' => true);
	} else {
		//获取用户列表

		if (!$id) {
			
			if ($ismoderator) {
				$uids = array();
				$datas = array();
				foreach (C::t('organization_user')->fetch_user_not_in_orgid($limit) as $value) {
					if(!$value['uid']) continue;
					$uids[] = $value['uid'];
					$datas[] = array('id' => 'uid_' . $value['uid'], 'text' => $value['username'] . '<em class="hide">' . $value['email'] . '</em>', 'icon' => 'dzz/system/images/user.png', 'state' => array('disabled' => $disable), "type" => $type, 'li_attr' => array('uid' => $value['uid']));
				}
				getuserIcon($uids,$datas,$data);
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
				$arr=array('id' => $value['orgid'], 'text' => $value['orgname'], 'icon' => $icon, 'state' => array('disabled' => $orgdisable), "type" => $orgtype, 'children' => true);
				if(intval($value['aid'])==0){
						$arr['text'] = avatar_group($value['orgid'],array($value['orgid']=>array('aid'=>$value['aid'],'orgname'=>$value['orgname']))).$value['orgname'];
						$arr['icon'] = false;
					}else{
						$arr['text'] = $value['orgname'];
						$arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $value['aid']);
					}
				$data[]=$arr;
			}
			if ($ismoderator) {
				$uids = array();
				$datas = array();
				foreach (C::t('organization_user')->fetch_user_by_orgid($id,$limit) as $value) {
					if(!$value['uid']) continue;
					$uids[] = $value['uid'];
					$datas[] = array('id' => 'orgid_' . $value['orgid'] . '_uid_' . $value['uid'], 'text' => $value['username'] . '<em class="hide">' . $value['email'] . '</em>', 'icon' => 'dzz/system/images/user.png', 'state' => array('disabled' => $disable), "type" => $type, 'li_attr' => array('uid' => $value['uid']));
				}
				getuserIcon($uids,$datas,$data);
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
		$uporgids = C::t('organization')->fetch_parent_by_orgid($orgid);
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
	/*默认新建机构和部门开始群组manageon群组管理员开启 syatemon系统管理员开启 available 系统管理员开启共享目录,保留diron(群组管理员开启目录)控制是否开启目录显示在前台*/
	$setarr = array('forgid' => intval($_GET['forgid']), 'orgname' => lang('new_department'), 'fid' => 0, 'disp' => intval($_GET['disp']), 'indesk' => 0, 'dateline' => TIMESTAMP, 'available' => 1,'syatemon'=>1,'manageon'=>1);
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
			//检测权限
			if (!$ismoderator = C::t('organization_admin') ->chk_memberperm($forgid, $_G['uid'])) {
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
	$perm = C::t('organization_admin') ->chk_memberperm($orgid, $_G['uid']);
	if ($perm < 2) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	$setarr = array('orgid' => $orgid, 'uid' => intval($_GET['uid']), 'dateline' => TIMESTAMP, 'opuid' => $_G['uid']);
	if ($setarr['id'] = C::t('organization_admin') -> insert(intval($_GET['uid']), $orgid)) {
		$user = getuserbyuid($setarr['uid']);
		$setarr['username'] = $user['username'];
		$setarr['avatar']=avatar_block($setarr['uid']);
		exit(json_encode($setarr));
	} else {
		exit(json_encode(array('error' => lang('add_administrator_unsuccess'))));
	}
} elseif ($do == 'moderator_del') {
	$orgid = intval($_GET['orgid']);
	$org = C::t('organization_admin') -> fetch($orgid);
	//获取当前操作用户权限,系统管理员，上级部门管理员和群组创建人均返回2
	$perm = C::t('organization_admin') ->chk_memberperm($orgid,$_G['uid']);
	if ($perm < 2) {
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
} elseif ($do == 'set_org_orgname') {
	$orgid = intval($_GET['orgid']);
	$orgname=getstr($_GET['orgname'],255);

	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization')->update_by_orgid($orgid, array('orgname'=>$orgname))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('rechristen_error'))));
	}
}elseif($do == 'set_org_logo'){
	$orgid = intval($_GET['orgid']);
	$img=intval(($_GET['aid']));
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization')->update_by_orgid($orgid, array('aid'=>$img))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('rechristen_error'))));
	}

}elseif ($do == 'set_org_desc') {
	$orgid = intval($_GET['orgid']);
	$desc=getstr($_GET['desc']);

	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization')->update_by_orgid($orgid, array('desc'=>$desc))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('rechristen_error'))));
	}
}elseif($do == 'group_on'){
	if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
		exit(json_encode(array('error' => lang('privilege'))));
	}
	if (C::t('organization') -> setgroupByOrgid($orgid, intval($_GET['available']))) {
		exit(json_encode(array('msg' => 'success')));
	} else {
		exit(json_encode(array('error' => lang('unable_set'))));
	}
}elseif($do == 'orginfo'){
	$array = isset($_GET['arr']) ? $_GET['arr']:'';
	if(!empty($array)){
		$orgid = intval($array['orgid']);
		if (!C::t('organization_admin') -> ismoderator_by_uid_orgid($orgid, $_G['uid'])) {
			exit(json_encode(array('error' => lang('privilege'))));
		}
		if(!$org = C::t('organization') -> fetch($orgid)){
			exit(json_encode(array('error' => lang('organization_not_exists'))));
		}
		$setarr = array(
			'desc'=>getstr($array['desc']),
			'groupback'=>isset($array['groupback']) ? intval($array['groupback']):0,
			'aid'=>isset($array['aid']) ? intval($array['aid']):0,
			//'orgname'=>getstr($array['orgname']);
		);
		if(C::t('organization')->update($orgid,$setarr)){
			$addaids = array();
			$delaids = array();
			if(!empty($array['aid']) && $array['aid'] != $org['aid']){
				$addaids[] = $array['aid'];
				$delaids[] = $org['aid'];
			}
			if(!empty($array['groupback']) && $array['groupback'] != $org['groupback']){
				$addaids[] = $array['groupback'];
				$delaids[] = $org['groupback'];
			}
			if(!empty($addaids)){
				C::t('attachment')->addcopy_by_aid($addaids);
			}
			if(!empty($delaids)){
				C::t('attachment')->addcopy_by_aid($delaids,-1);
			}
			exit(json_encode(array('success' =>true)));
		}else{
			exit(json_encode(array('error' => lang('edit_error'))));
		}
	}
}elseif($do=='folder_maxspacesize'){
	$orgid=intval($_GET['orgid']);
	$setspacesize = intval($_GET['maxspacesize']);
	if(!$org=C::t('organization')->fetch($orgid)){
		exit(json_encode(array('error'=>'该机构或群组不存在或被删除')));
	}
	//暂时只允许系统管理员进行空间相关设置
	if($_G['adminid'] != 1){
		exit(json_encode(array('error'=>'没有权限')));
	}
	if($setspacesize != 0){

		//获取允许设置的空间值
		$allowallotspace = C::t('organization')->get_allowallotspacesize_by_orgid($orgid);

		if($allowallotspace < 0) {
			exit(json_encode(array('error' => '可分配空间不足')));
		}

		//获取当前已占用空间大小
		$currentallotspace = C::t('organization')->get_orgallotspace_by_orgid($orgid,0,false);
		//设置值小于当前下级分配总空间值即：当前设置值 < 下级分配总空间
		if($setspacesize > 0 && $setspacesize*1024*1024 < $currentallotspace){

			exit(json_encode(array('error'=>'设置空间值不足,小于已分配空间值！','val'=>$org['maxspacesize'])));

		}
		//上级包含空间限制时，无限制不处理，直接更改设置值
		if($allowallotspace > 0 && ($setspacesize*1024*1024 > $allowallotspace)){

			exit(json_encode(array('error'=>'总空间不足！','val'=>$org['maxspacesize'])));

		}
	}

	//设置新的空间值
	if(C::t('organization')->update($orgid,array('maxspacesize'=>$setspacesize))){

		exit(json_encode(array('msg'=>'success')));

	}else{
		exit(json_encode(array('error'=>'设置不成功或未更改','val'=>$org['maxspacesize'])));
	}

}elseif ($do == 'guide') {
	include template('guide');
}
exit();
?>
