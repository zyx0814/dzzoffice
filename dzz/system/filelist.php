<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
if(!C::t('folder')->check_home_by_uid($uid)){
    C::t('folder')->fetch_home_by_uid($uid);
}
$id = isset($_GET['id']) ? $_GET['id'] : '';
$do = $_GET['do'] ? $_GET['do'] : '';
$ctrlid = isset($_GET['ctrlid']) ? trim($_GET['ctrlid']):'selposition';
$callback=isset($_GET['callback']) ? trim($_GET['callback']):'callback_selectposition';
$inwindow = isset($_GET['inwindow']) ? intval($_GET['inwindow']):0;
$allowcreate = isset($_GET['allowcreate']) ? $_GET['allowcreate']:0;
$selhome = isset($_GET['selhome']) ? $_GET['selhome']:0;//展示网盘0不展示
$selorg = isset($_GET['selorg']) ? $_GET['selorg']:0;//展示机构0不展示
$selgroup = isset($_GET['selgroup']) ? $_GET['$selgroup']:0;//展示群组0不展示
$range = isset($_GET['range']) ? $_GET['range']:0;//是否限制展示0不限定
$ismobile=helper_browser::ismobile();
$data = array();
$powerarr = perm_binPerm::getPowerArr();
if ($do == 'get_children') {
    if ($id == 'group') {
        //$orgids = C::t('organization_user')->fetch_org_by_uid($uid,1);
        $groupinfo = C::t('organization')->fetch_group_by_uid($uid, true);
        foreach ($groupinfo as $v) {
            $children = (C::t('resources')->fetch_folder_num_by_pfid($v['fid']) > 0) ? true : false;
            $arr = array(
                'id' => 'g_' . $v['orgid'],
                'type' => 'group',
                'children' => $children,
                'li_attr' => array('fid'=>$v['fid'],'gid'=>$v['orgid'])
            );
            if (intval($v['aid']) == 0) {
                $arr['text'] = '<span class="iconFirstWord" style="background:' . $v['aid'] . ';">' . strtoupper(new_strsubstr($v['orgname'], 1, '')) . '</span>' . $v['orgname'];
                $arr['icon'] = false;
            } else {
                $arr['text'] = $v['orgname'];
                $arr['icon'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $v['aid']);
            }

            $data[] = $arr;
        }
        exit(json_encode($data));
    } elseif (preg_match('/g_\d+/', $id)) {
        $gid = intval(str_replace('g_', '', $id));
        $groupinfo = C::t('organization')->fetch($gid);
        if ($groupinfo && $groupinfo['available'] == 1 && $groupinfo['diron'] == 1) {
            foreach (C::t('folder')->fetch_folder_by_pfid($groupinfo['fid']) as $val) {
                $children = (C::t('resources')->fetch_folder_num_by_pfid($val['fid']) > 0) ? true : false;
                $data[] = array(
                    'id' => 'f_' . $val['fid'],
                    'text' => $val['fname'],
                    'type' => 'folder',
                    'children' => $children,
                    'li_attr' =>array('fid'=>$val['fid'],'gid'=>$val['orgid'])
                );
            }
        }
        exit(json_encode($data));
    } elseif (preg_match('/gid_\d+/', $id)) {
        $gid = intval(str_replace('gid_', '', $id));
        $orginfo = C::t('organization')->fetch($gid);
        if ($orginfo && $orginfo['available'] == 1 && $orginfo['diron'] == 1) {
            foreach (C::t('folder')->fetch_folder_by_pfid($orginfo['fid']) as $val) {
                $children = (C::t('resources')->fetch_folder_num_by_pfid($val['fid']) > 0) ? true : false;

                $arr = array(
                    'id' => 'f_' . $val['fid'],
                    'text' => $val['fname'],
                    'type' => 'folder',
                    'children' => $children,
                    'li_attr' =>array('fid'=>$val['fid'],'gid'=>$val['orgid'])
                );
                if ($val['flag'] == 'app') {
                    $appid = C::t("folder_attr")->fetch_by_skey_fid($val['fid'], 'appid');
                    if ($imgs = C::t('app_market')->fetch_appico_by_appid($appid)) {
                        $arr['icon'] = 'data/attachment/' . $imgs;
                    }

                }
                $data[] = $arr;
            }
        }

        $groupinfo = C::t('organization')->fetch_org_by_uidorgid($uid, $gid);

        if ($groupinfo) {
            foreach ($groupinfo as $val) {
                $children = (DB::result_first("select count(*) from %t where forgid = %d", array('organization', $val['orgid'])) > 0) ? true : false;
                $arr = array(
                    'id' => 'gid_' . $val['orgid'],
                    'type' => 'department',
                    'children' => $children,
                    'li_attr' => array('fid'=>$val['fid'],'gid'=>$val['orgid'])
                );
                if (intval($val['aid']) == 0) {
                    $arr['text'] = '<span class="iconFirstWord" style="background:' . $val['aid'] . ';">' . strtoupper(new_strsubstr($val['orgname'], 1, '')) . '</span>' . $val['orgname'];
                    $arr['icon'] = false;
                } else {
                    $arr['text'] = $val['orgname'];
                    $arr['icon'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $val['aid']);
                }
                $data[] = $arr;
            }
        }
        exit(json_encode($data));
    } elseif (preg_match('/f_\d+/', $id)) {
        $fid = intval(str_replace('f_', '', $id));
        $params = array('folder',$fid,$powerarr['upload']);
        //foreach (DB::fetch_all("select fid,fname from %t where pfid = %d and perm_inherit & %d",$params) as $val){
        foreach (C::t('folder')->fetch_folder_by_pfid($fid) as $val) {
            $children = (C::t('resources')->fetch_folder_num_by_pfid($val['fid']) > 0) ? true : false;
            $data[] = array(
                'id' => 'f_' . $val['fid'],
                'text' => $val['fname'],
                'type' => 'folder',
                'children' => $children,
                'li_attr' => array('fid'=>$val['fid'])
            );
        }
        exit(json_encode($data));
    } elseif (preg_match('/u_\d+/', $id)) {
        $fid = intval(str_replace('u_', '', $id));
        foreach (C::t('resources')->fetch_folder_by_pfid($fid) as $v) {
            $children = (C::t('resources')->fetch_folder_num_by_pfid($v['oid']) > 0) ? true : false;
            $data[] = array(
                'id' => 'u_' . $v['oid'],
                'text' => $v['name'],
                'type' => 'folder',
                'children' => $children,
                'li_attr' => array('fid'=>$v['oid'])
            );
        }
    } else {
        //获取配置设置值
        $explorer_setting = get_resources_some_setting();
        if ($explorer_setting['useronperm'] && (!$range || ($range && $selhome))) {
            $fid = C::t('folder')->fetch_fid_by_flag('home');
            $children = (C::t('resources')->fetch_folder_num_by_pfid($fid) > 0) ? true : false;
            $data[] = array(
                'id' => 'u_' . $fid,
                'text' => lang('explorer_user_root_dirname'),
                'type' => 'home',
                'children' => $children,
                'li_attr' => array('fid'=>$fid)
            );
        }
        if ($explorer_setting['orgonperm'] && (!$range || ($range && $selorg))) {
            $orgs = C::t('organization')->fetch_all_orggroup($uid);
            foreach ($orgs['org'] as $v) {
                if (DB::result_first("select count(*) from %t where forgid = %d", array('organization', $v['orgid'])) > 0 || C::t('resources')->fetch_folder_num_by_pfid($v['fid']) > 0) {
                    $children = true;
                } else {
                    $children = false;
                }
                if (!empty($v)) {
                    $arr = array(
                        'id' => 'gid_' . $v['orgid'],
                        'type' => ($v['pfid'] > 0 ? 'department' : 'organization'),
                        'children' => $children,
                        'li_attr' => array('fid'=>$v['fid'],'gid'=>$v['gid'])
                    );
                    if (intval($v['aid']) == 0) {
                        $arr['text'] = '<span class="iconFirstWord" style="background:' . $v['aid'] . ';">' . strtoupper(new_strsubstr($v['orgname'], 1, '')) . '</span>' . $v['orgname'];
                        $arr['icon'] = false;
                    } else {
                        $arr['text'] = $v['orgname'];
                        $arr['icon'] = 'index.php?mod=io&op=thumbnail&width=24&height=24&path=' . dzzencode('attach::' . $v['aid']);
                    }
                    $data[] = $arr;
                }
            }
        }
        if ($explorer_setting['grouponperm'] && (!$range || ($range && $selgroup))) {
            $groups = C::t('organization')->fetch_group_by_uid($uid);
            $children = (count($groups) > 0) ? true : false;
            $data[] = array(
                'id' => 'group',
                'text' => '群组',
                'type' => 'group',
                'children' => $children,
                'li_attr' => array('hashs' => 'mygroup')
            );
        }
    }
    exit(json_encode($data));
} elseif ($do == 'filemanage') {

} elseif ($do == 'getParentsArr') {//获取
    $fid = intval($_GET['fid']);
    $gid = intval($_GET['gid']);
    $ret = array();
    if ($fid) {
        $subfix = '';
        $org = array();
        foreach (C::t('folder')->fetch_all_parent_by_fid($fid) as $value) {
            if (empty($subfix)) {
                if ($value['gid']) {//是部门或者群组
                    $org = C::t('organization')->fetch($value['gid']);
                    if ($org['type'] == 0) {
                        $subfix = 'gid_';
                    } elseif ($org['type'] == 1) {
                        $subfix = 'g_';
                    }
                } else {
                    $subfix = 'u_';
                }
            }
            if ($value['gid'] < 1) {
                $arr[] = 'u_' . $value['fid'];
            } elseif ($value['flag'] == 'organization') {
                $arr[] = $subfix . $value['gid'];
            } else {
                $arr[] = 'f_' . $value['fid'];
            }
        }
        if ($subfix == 'g_') {//群组的话，需要增加顶级"群组"
            array_push($arr, 'group');
        }
        $arr = array_reverse($arr);
    } elseif ($gid) {
        $subfix = '';
        foreach (C::t('organization')->fetch_parent_by_orgid($gid) as $orgid) {
            if (empty($subfix)) {
                $org = C::t('organization')->fetch($orgid);
                if ($org['type'] == 0) {
                    $subfix = 'gid_';
                } elseif ($org['type'] == 1) {
                    $subfix = 'g_';
                }
            }
            $arr[] = $subfix . $orgid;

        }
        if ($subfix == 'g_') array_unshift($arr, 'group');
    }
    $arr = array_unique($arr);
    exit(json_encode($arr));
}elseif($do == 'creatnewfolder'){
    $fid = isset($_GET['fid']) ? intval($_GET['fid']):'';
    $fname = isset($_GET['foldername']) ? trim($_GET['foldername']) : lang('newfolder');
    if ($arr = IO::CreateFolder($fid, $fname, $perm)) {
        if ($arr['error']) {
        } else {
            $arr = array_merge($arr['icoarr'], $arr['folderarr']);
            $arr['msg'] = 'success';

        }
    } else {
        $arr = array();
        $arr['error'] = lang('failure_newfolder');
    }
    exit(json_encode($arr));
}elseif($do == 'rename'){
    $rid = isset($_GET['rid']) ? trim($_GET['rid']):'';
    $text=str_replace('...','',getstr(io_dzz::name_filter($_GET['fname']),80));
    $ret=IO::rename($rid,$text);
    exit(json_encode($ret));
}elseif ($do == 'getfoldername'){
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    if(perm_check::checkperm_Container($fid,'folder')){
        $fname = isset($_GET['foldername']) ? trim($_GET['foldername']) : lang('newfolder');
        $newname = IO::getFolderName($fname,$fid);
        exit(json_encode(array('success'=>true,'fname'=>$newname)));
    }else{
        exit(json_encode(array('error'=>lang('no_privilege'))));
    }
}elseif($do == 'checkupload'){
    $fid = isset($_GET['fid']) ? trim($_GET['fid']):'';
    if(perm_check::checkperm_Container($fid,'upload')){
        exit(json_encode(array('perm'=>true)));
    }else{
        exit(json_encode(array('perm'=>false)));
    }
}elseif($do == 'geffolderinfo'){
    $fid = isset($_GET['fid']) ? intval($_GET['fid']):'';
    if(!perm_check::checkperm_Container($fid,'read')){
        exit(json_encode(array('error'=>lang('no_privilege'))));
    }
    $data = C::t('folder')->fetch_by_fid($fid);
    exit(json_encode($data));
}elseif($do == 'savefile'){

}

include template('filelist');
exit();