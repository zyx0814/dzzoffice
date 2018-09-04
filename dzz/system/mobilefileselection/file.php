<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
global $_G;
$uid = $_G['uid'];
$operation = isset($_GET['operation']) ? trim($_GET['operation']) : '';
$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;
$gid = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
$perm = 0;
if ($gid) {
    //获取网盘系统设置值
    $explorer_setting = get_resources_some_setting();
    if ($group['type'] == 1 && !$explorer_setting['grouponperm']) {
        showmessage(lang('no_privilege'), dreferer());
    }
    if ($group['type'] == 0 && !$explorer_setting['orgonperm']) {
        showmessage(lang('no_privilege'), dreferer());
    }
    //获取群组基本信息
    if (!$group = C::t('organization')->fetch($gid)) {
        showmessage(lang('no_group'), dreferer());
    }
    //获取成员权限
    $perm = C::t('organization_admin')->chk_memberperm($gid, $uid);
    //判断群组是否开启，如果未开启(共享目录)并且不是管理员不能访问
    if (!$group['diron'] && !$perm) {
        showmessage(lang('no_privilege'), dreferer());
    }
    //判断是否有权限访问群组，如果不是管理员权限(主要针对系统管理员和上级管理员),并且非成员,不能访问
    if (!$perm && !C::t('organization')->ismember($gid, $uid, false)) {
        showmessage(lang('no_privilege'), dreferer());
    }

    if (!$group['syatemon']) {
        showmessage(lang('no_group_by_system'), dreferer());
    }
    if (!$group['manageon'] && $perm < 1) {
        showmessage(lang('no_privilege'), dreferer());
    }
    if (!$fid) $fid = $group['fid'];
}
$perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 10;//默认每页条数
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
$start = ($page - 1) * $perpage;//开始条数
$datastart = isset($_GET['datatotal']) ? intval($_GET['datatotal']) : 0;
$total = 0;//总条数
//是否有更多群组
$gropunext = isset($_GET['gropunext']) ? intval($_GET['gropunext']) : true;
//默认按时间顺序查询
$disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;
$bz = empty($_GET['bz']) ? '' : urldecode($_GET['bz']);
$marker = empty($_GET['marker']) ? '' : trim($_GET['marker']);
$data = array();
$keyword = isset($_GET['keyword']) ? urldecode($_GET['keyword']) : '';
$exts = isset($_GET['exts']) ? trim($_GET['exts']) : '';
$conditions = array();
if ($keyword) {
    $conditions['name'] = array($keyword, 'like', 'and');
}

//类型筛选
if ($exts) {
    if ($exts == 'folder') {
        $conditions['type'] = array('folder', '=', 'and');
    } else {
        $extarr = explode(',', $exts);
        $conditions['ext'] = array($extarr, 'in', 'and');
    }
}
$asc = isset($_GET['asc']) ? intval($_GET['asc']) : 0;

$order = $asc > 0 ? 'ASC' : "DESC";

switch ($disp) {
    case 0:
        $orderby = 'name';
        $groupby = 'o.orgname';
        break;
    case 1:
        $orderby = 'size';
        $groupby = 'o.dateline';
        break;
    case 2:
        $orderby = array('type', 'ext');
        $groupby = 'o.dateline';
        break;
    case 3:
        $orderby = 'dateline';
        $groupby = 'o.dateline';
        break;

}
$folder = C::t('folder')->fetch_folderinfo_by_fid($fid);
$folder['gid'] = ($gid) ? $gid : 0;
$folder['ismoderator'] = $perm;
$folderjson = json_encode(array($fid => $folder));
$folderpath = array_filter(explode('/', preg_replace('/dzz:(.+?):/', '', $folder['path'])));
$navtitle = $folderpath[0];
$pathkeyarr = explode('-', str_replace('_', '', $folder['pathkey']));
$folderpatharr = array();
foreach (DB::fetch_all("select fid,gid,fname from %t where fid in(%n)", array('folder', $pathkeyarr)) as $v) {
    $folderpatharr[] = array('fid' => $v['fid'], 'gid' => $v['gid'], 'name' => $v['fname']);
}
$groups = array();
$newperpage = 10;
//如果是机构获或部门取下级
if ($gid > 0 && $group['type'] == 0 && $gropunext) {
    if (C::t('organization_admin')->chk_memberperm($gid, $uid) || C::t('organization')->ismember($gid, $uid, true)) {
        foreach (DB::fetch_all("select o.*,f.fid from %t  o left join %t f on o.fid=f.fid where o.forgid = %d  order by $groupby $order limit $start,$perpage", array('organization', 'folder', $gid)) as $v) {
            if (((C::t('organization_admin')->chk_memberperm($v['orgid'], $uid) > 0) || ($v['manageon'] && $v['diron'])) && $v['syatemon']) {
                $resultarr[] = $v;
                if (intval($v['aid'])) {
                    //群组图
                    $v['img'] = 'index.php?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $v['aid']);
                }
                $contaions = C::t('resources')->get_contains_by_fid($v['fid']);
                $v['filenum'] = $contaions['contain'][0];
                $v['foldernum'] = $contaions['contain'][1];
                $v['orgname'] = addslashes($v['orgname']);
                $groups[] = $v;
            } else {
                continue;
            }
        }
    }
    $groupnum = count($groups);
    if ($groupnum >= $perpage) {
        $gropunext = $page + 1;
    } else {
        $gropunext = false;
    }
    //如果有机构部门结果，则减去机构部门结果数量
    $newperpage = $perpage - $groupnum;
} else {
    $gropunext = false;
}

//获取文件数据
if ($newperpage) {
    //查询结果处理
    foreach (C::t('resources')->fetch_all_by_pfid($fid, $conditions, $newperpage, $orderby, $order, $datastart) as $val) {
        if ($val['type'] == 'folder') {
            $val['filenum'] = $val['contaions']['contain'][0];
            $val['foldernum'] = $val['contaions']['contain'][1];
        } else {
            $val['monthdate'] = dgmdate($val['dateline'], 'm-d');
            $val['hourdate'] = dgmdate($val['dateline'], 'H:i');
        }
        if ($val['type'] == 'image') {
            $val['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $val['aid']);
            $val['imgpath'] = DZZSCRIPT . '?mod=io&op=thumbnail&path=' . dzzencode('attach::' . $val['aid']);
        }
        $val['name'] = addslashes($val['name']);
        $data[$val['rid']] = $val;
    }
}
$next = false;
if (count($data) + count($groups) >= $perpage) {
    $next = $page + 1;
}
$createFolderPerm = false;
if($gid){
    if ($folder['ismoderator']) {
        $createFolderPerm = true;
    } else {
        $$createFolderPerm = perm_binPerm::havePower('folder', $folder['perm_inherit']) ? true:false;
    }
}else{
    $createFolderPerm = true;
}

//返回数据
$return = array('fid' => $fid, 'data' => $data ? $data : array(), 'param' => array(
    'perpage' => $perpage,
    'bz' => $bz,
    'asc' => $asc,
    'disp' => $disp,
    'page' => $next,
    'ext' => $exts,
    'fid' => $fid,
    'gid' => $gid,
    'datatotal' => (count($data) + count($groups) + $datastart),
    'groupnext' => $gropunext,
    'localsearch' => $bz ? 1 : 0,
    'createFolderPerm'=>$createFolderPerm
)
);
$params = json_encode($return['param']);
require template('mobilefileselection/filelist');
exit();



