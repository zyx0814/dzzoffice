<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
$do = isset($_GET['do']) ? trim($_GET['do']) : '';
if ($do == 'filelist') {
    $sid = htmlspecialchars($_GET['sid']);
    $perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 25;//默认每页条数
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);//页码数
    $disp = isset($_GET['disp']) ? intval($_GET['disp']) : 3;
    $asc = isset($_GET['asc']) ? intval($_GET['asc']) : 0;
    //最近使用文件
    $explorer_setting = get_resources_some_setting();
    $param = array('resources_statis', $_G['uid']);
    $limitsql = ' limit ' . $perpage;
    $recents = $data = array();
    $recents = DB::fetch_all("select * from %t where uid = %d and rid != '' order by opendateline desc, editdateline desc $limitsql", $param);
    foreach ($recents as $v) {
        if ($val = C::t('resources')->fetch_by_rid($v['rid'])) {
            if (!$explorer_setting['useronperm'] && $val['gid'] == 0) {
                continue;
            }
            if (!$explorer_setting['grouponperm'] && $val['gid'] > 0) {
                if (DB::result_first("select `type` from %t where orgid = %d", array('organization', $val['gid'])) == 1) {
                    continue;
                }
            }
            if (!$explorer_setting['orgonperm'] && $val['gid'] > 0) {
                if (DB::result_first("select `type` from %t where orgid = %d", array('organization', $val['gid'])) == 0) {
                    continue;
                }
            }
            if ($val['isdelete'] == 0) {
                $openTime = (int)$v['opendateline'];
                $editTime = (int)$v['editdateline'];
                $createTime = (int)$val['dateline'];
                
                // 确定最近的操作时间
                if ($openTime > 0 && $editTime > 0) {
                    // 两者都有值时，比较大小显示最近的操作
                    if ($openTime >= $editTime) {
                        $val['ffdateline'] = '打开于 '.dgmdate($openTime, 'u');
                        $val['fdateline'] = dgmdate($openTime);
                        $val['dateline'] = $v['opendateline'];
                    } else {
                        $val['ffdateline'] = '编辑于 '.dgmdate($editTime, 'u');
                        $val['fdateline'] = dgmdate($editTime);
                        $val['dateline'] = $v['editdateline'];
                    }
                } elseif ($openTime > 0) {
                    $val['ffdateline'] = '打开于 '.dgmdate($openTime, 'u');
                    $val['fdateline'] = dgmdate($openTime);
                    $val['dateline'] = $v['opendateline'];
                } elseif ($editTime > 0) {
                    $val['ffdateline'] = '编辑于 '.dgmdate($editTime, 'u');
                    $val['fdateline'] = dgmdate($editTime);
                    $val['dateline'] = $v['editdateline'];
                } else {
                    $val['ffdateline'] = '创建于 '.dgmdate($createTime, 'u');
                }
                $data[$val['rid']] = $val;
            }
        }
    }

    $iconview = isset($_GET['iconview']) ? intval($_GET['iconview']) : 4;//排列方式
    if ($data === null) {
        $data = array();
    }
    $total = count($data);
    if (!$json_data = json_encode($data)) $data = array();
    //返回数据
    $return = array(
        'sid' => $sid,
        'total' => $total,
        'data' => $data ? $data : array(),
        'folderdata' => array(),
        'param' => array(
            'disp' => $disp,
            'view' => $iconview,
            'page' => $page,
            'perpage' => $perpage,
            'bz' => $bz,
            'total' => $total,
            'asc' => $asc,
            'keyword' => '',
            'tags' => '',
            'exts' => '',
            'localsearch' => $bz ? 1 : 0
        )
    );
    exit(json_encode($return));
} else {
    $displayTime = 1;
    include template('recent_content');
}