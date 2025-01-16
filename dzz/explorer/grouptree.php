<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$id = isset($_GET['id']) ? $_GET['id']:'';
$do=$_GET['do']?$_GET['do']:'get_children';
$data = array();
$colors=array('#6b69d6','#a966ef','#e9308d','#e74856','#f35b42','#00cc6a','#0078d7','#5290f3','#00b7c3','#0099bc','#018574','#c77c52','#ff8c00','#68768a','#7083cb','#26a255');
if($do=='get_children'){
	if($id == 'group'){
		$groupinfo = C::t('organization')->fetch_group_by_uid($uid, true);
		foreach($groupinfo as $v){
            $children = (C::t('resources')->fetch_folder_num_by_pfid($v['fid']) > 0) ? true:false;
			$arr= array(
				'id'=>'g_'.$v['orgid'],
				'type'=>'group',
				'children'=>$children,
				'li_attr'=>array('href'=>DZZSCRIPT.'?mod='.MOD_NAME.'&op=group','hashs'=>'group&gid='.$v['orgid'])
			);
            if(intval($v['aid'])==0){
               $arr['text'] = avatar_group($v['orgid'],array($v['orgid']=>array('aid'=>$v['aid'],'orgname'=>$v['orgname']))).$v['orgname'];
                $arr['icon'] = false;
            }else{
                $arr['text'] = $v['orgname'];
                $arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $v['aid']);
            }

			$data[] =$arr;
		}
	}elseif(preg_match('/g_\d+/',$id)){
		$gid = intval(str_replace('g_','',$id));
		$groupinfo = C::t('organization')->fetch($gid);

		if($groupinfo && ($groupinfo['diron'] == 1 || C::t('organization_admin')->chk_memberperm($gid,$uid))) {
			foreach (C::t('folder')->fetch_folder_by_pfid($groupinfo['fid'],array('fname','fid')) as $val) {
                $children = (C::t('resources')->fetch_folder_num_by_pfid($val['fid']) > 0) ? true:false;
				$data[] = array(
					'id' => 'f_' . $val['fid'],
					'text' => $val['fname'],
					'type' => 'folder',
					'children' => $children,
					'li_attr' => array(
						'href' => DZZSCRIPT . '?mod=' . MOD_NAME . '&op=group',
						'hashs' => 'group&do=file&gid=' . $groupinfo['orgid'] . '&fid=' . $val['fid'])
				);
			}
		}
	}elseif(preg_match('/gid_\d+/',$id)){
		$gid = intval(str_replace('gid_','',$id));
		$orginfo = C::t('organization')->fetch($gid);
		if($orginfo && ($orginfo['diron'] == 1 || C::t('organization_admin')->chk_memberperm($gid,$uid))) {
			foreach (C::t('folder')->fetch_folder_by_pfid($orginfo['fid'],array('fname','fid')) as $val) {
                $children = (C::t('resources')->fetch_folder_num_by_pfid($val['fid']) > 0) ? true:false;
				$arr = array(
					'id' => 'f_' . $val['fid'],
					'text' => $val['fname'],
					'type' => 'folder',
					'children' => $children,
					'li_attr' => array(
						'href' => DZZSCRIPT . '?mod=' . MOD_NAME . '&op=group',
						'hashs' => 'group&do=file&gid=' . $orginfo['orgid'] . '&fid=' . $val['fid']
						)
				);
                if($val['flag'] == 'app'){
                    $appid = C::t("folder_attr")->fetch_by_skey_fid($val['fid'],'appid');
                    if($imgs = C::t('app_market')->fetch_appico_by_appid($appid)){
                        $arr['icon'] = 'data/attachment/'.$imgs;
                    }

                }
                $data[] = $arr;
			}
		}
		
		$groupinfo = C::t('organization')->fetch_org_by_uidorgid($uid,$gid);
		
		if($groupinfo) {
			foreach ($groupinfo as $val) {
                if(count(C::t('organization')->fetch_org_by_uidorgid($uid,$val['orgid'])) > 0 || C::t('resources')->fetch_folder_num_by_pfid($val['fid']) > 0){
                    $children = true;
                }else{
                    $children = false;
                }
				$arr= array(
					'id' =>'gid_'.$val['orgid'],
					'type' => 'department',
					'children' => $children,
					'li_attr' => array('hashs' => 'group&gid=' . $val['orgid'], 'args' => 'gid_' . $val['orgid'])
				);
				if(intval($val['aid'])==0){
					$arr['text'] = avatar_group($val['orgid'],array($val['orgid']=>array('aid'=>$val['aid'],'orgname'=>$val['orgname']))).$val['orgname'];
					$arr['icon'] = false;
				}else{
					$arr['text'] = $val['orgname'];
					$arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $val['aid']);
				}
				$data[]=$arr;
			}
		}
	}elseif(preg_match('/f_\d+/',$id)){
		$fid = intval(str_replace('f_','',$id));
		foreach (C::t('folder')->fetch_folder_by_pfid($fid,array('fname','fid','gid')) as $val) {
            $children = (C::t('resources')->fetch_folder_num_by_pfid($val['fid']) > 0) ? true:false;
			$data[] = array(
				'id' => 'f_' . $val['fid'],
				'text' => $val['fname'],
				'type' => 'folder',
				'children' => $children,
				'li_attr' => array(
					'href' => DZZSCRIPT . '?mod=' . MOD_NAME . '&op=group',
					'hashs' => 'group&do=file&gid=' . $val['gid'] . '&fid=' . $val['fid'])
			);
		}
	
	}elseif(preg_match('/u_\d+/',$id)){
		$fid = intval(str_replace('u_','',$id));
		foreach(C::t('resources')->fetch_folder_by_pfid($fid,array('fname','fid')) as $v){
			$children = (C::t('resources')->fetch_folder_num_by_pfid($v['oid']) > 0) ? true:false;
			$data[] = array(
				'id'=>'u_'.$v['oid'],
				'text'=>$v['name'],
				'type'=>'folder',
				'children'=>$children,
				'li_attr'=>array(
					'href'=>DZZSCRIPT.'?mod='.MOD_NAME.'&op=home',
					'hashs'=>'home&do=file&fid='.$v['oid']
				)
			);
		}
	}else {
        //获取配置设置值
        $explorer_setting = get_resources_some_setting();
        if ($explorer_setting['useronperm']) {
            $folders = C::t('folder')->fetch_home_by_uid();
            $fid = $folders['fid'];
            $children = (C::t('resources')->fetch_folder_num_by_pfid($fid) > 0) ? true:false;
            $data[] = array(
                'id' => 'u_' . $fid,
                'text' => lang('explorer_user_root_dirname'),
                'type' => 'home',
                'children' => $children,
                'li_attr' => array('hashs' => "home&fid=" . $fid)
            );
        }
        if ($explorer_setting['orgonperm']) {
            $orgs = C::t('organization')->fetch_all_orggroup($uid,false);
            foreach ($orgs['org'] as $v) {
                if(count(C::t('organization')->fetch_org_by_uidorgid($uid,$v['orgid'])) > 0 || C::t('resources')->fetch_folder_num_by_pfid($v['fid']) > 0){
                    $children = true;
                }else{
                    $children = false;
                }
                if (!empty($v)) {
                    $arr = array(
                        'id' => 'gid_' . $v['orgid'],
                        'type' => ($v['pfid'] > 0 ? 'department' : 'organization'),
                        'children' => $children,
                        'li_attr' => array('hashs' => 'group&gid=' . $v['orgid'], 'args' => 'gid_' . $v['orgid'])
                    );
                    if(intval($v['aid'])==0){
						$arr['text'] = avatar_group($v['orgid'],array($v['orgid']=>array('aid'=>$v['aid'],'orgname'=>$v['orgname']))).$v['orgname'];
						$arr['icon'] = false;
					}else{
						$arr['text'] = $v['orgname'];
						$arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $v['aid']);
					}
                    $data[] = $arr;
                }
            }
        }
        if ($explorer_setting['grouponperm']) {
            $groups = C::t('organization')->fetch_group_by_uid($uid,true);
            $children = (count($groups) > 0) ? true:false;
            $data[] = array(
                'id' => 'group',
                'text' => '群组',
                'type' => 'group',
                'children' => $children,
                'li_attr' => array('hashs' => 'mygroup')
            );
        }
    }

}elseif($do=='filemanage'){

}elseif($do == 'getParentsArr'){//获取
	$fid=intval($_GET['fid']);
	$gid=intval($_GET['gid']);
	$ret=array();
	if($fid){
		$subfix='';
		$org=array();
		foreach(C::t('folder')->fetch_all_parent_by_fid($fid) as $value){
			if(empty($subfix)){
				if($value['gid']){//是部门或者群组
					$org=C::t('organization')->fetch($value['gid']);
					if($org['type']==0){
						$subfix='gid_';
					}elseif($org['type']==1){
						$subfix='g_';
					}
				}else{
					$subfix='u_';
				}
			}
			if($value['gid']<1){
				$arr[]='u_'.$value['fid'];
			}elseif($value['flag']=='organization'){
				$arr[]=$subfix.$value['gid'];
			}else{
				$arr[]='f_'.$value['fid'];
			}
		}
		if($subfix=='g_'){//群组的话，需要增加顶级"群组"
			array_push($arr,'group');
		}
		 $arr=array_reverse($arr);
	}elseif($gid){
		$subfix='';
		foreach(C::t('organization')->fetch_parent_by_orgid($gid) as $orgid){
			if(empty($subfix)){
					$org=C::t('organization')->fetch($orgid);
					if($org['type']==0){
						$subfix='gid_';
					}elseif($org['type']==1){
						$subfix='g_';
					}
			}
			$arr[]=$subfix.$orgid;
			
		}
		if($subfix=='g_') array_unshift($arr,'group');
	}
	$arr=array_unique($arr);
    exit(json_encode($arr));
}elseif($do == 'create_group'){
    $data = array();
    if($_G['adminid'] != 1) exit(json_encode($data));
    $id = isset($_GET['id']) ? $_GET['id']:'';
    $gid = intval(str_replace('g_','',$id));
    //获取配置设置值
    $explorer_setting = get_resources_some_setting();
    if($gid && $explorer_setting['grouponperm']){
        $groupinfo = C::t('organization')->fetch($gid);
        $children = (C::t('resources')->fetch_folder_num_by_pfid($groupinfo['fid']) > 0) ? true:false;
        $arr= array(
            'id'=>'g_'.$groupinfo['orgid'],
            'type'=>'group',
            'children'=>$children,
            'li_attr'=>array('href'=>DZZSCRIPT.'?mod='.MOD_NAME.'&op=group','hashs'=>'group&gid='.$groupinfo['orgid'])
        );
        if(intval($groupinfo['aid'])==0){
            $arr['text'] = avatar_group($groupinfo['orgid'],array($groupinfo['orgid']=>array('aid'=>$groupinfo['aid'],'orgname'=>$groupinfo['orgname']))).$groupinfo['orgname'];
            $arr['icon'] = false;
        }else{
            $arr['text'] = $groupinfo['orgname'];
            $arr['icon']='index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $groupinfo['aid']);
        }
        $data['group'] =$arr;

    }

}
exit(json_encode($data));