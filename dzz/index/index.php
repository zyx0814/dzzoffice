<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$do = isset($_GET['do']) ? $_GET['do'] : '';
if ($do == 'saveIndex') {
    $appids = implode(',', $_GET['appids']);
    $ret = C::t('user_setting')->update_by_skey('index_simple_appids', $appids);
    exit(json_encode(array('success' => $ret)));
} elseif ($do == 'statis') {
    $filedata = get_statis();
    include template('statis');
    exit();
} else {
    $config = array();
    $config = C::t('user_field')->fetch($_G['uid']);
    if (!$config) {
        $config = dzz_userconfig_init();
        if ($config['applist']) {
            $applist = explode(',', $config['applist']);
        } else {
            $applist = array();
        }
    } else {//检测不允许删除的应用,重新添加进去
        if ($config['applist']) {
            $applist = explode(',', $config['applist']);
        } else {
            $applist = array();
        }
        if ($applist_n = array_keys(C::t('app_market')->fetch_all_by_notdelete($_G['uid']))) {
            $newappids = array();
            foreach ($applist_n as $appid) {
                if (!in_array($appid, $applist)) {
                    $applist[] = $appid;
                    $newappids[] = $appid;
                }
            }
            if ($newappids) {
                C::t('app_user')->insert_by_uid($_G['uid'], $newappids);
                C::t('user_field')->update($_G['uid'], array('applist' => implode(',', $applist)));
            }
        }
    }
    $userstatus = C::t('user_status')->fetch($_G['uid']);
    $space = dzzgetspace($_G['uid']);
    if (!$_G['cache']['usergroups']) loadcache('usergroups');
    $usergroup = $_G['cache']['usergroups'][$space['groupid']];
    //获取已安装应用
    $app = C::t('app_market')->fetch_all_by_appid($applist);
    $applist_1 = array();
    foreach ($app as $key => $value) {
        if ($value['isshow'] < 1) continue;
        if ($value['available'] < 1) continue;
        if ($value['position'] < 1) continue;//位置为无的忽略
        //判断管理员应用
        if ($_G['adminid'] != 1 && $value['group'] == 3) {
            continue;
        }
        $applist_1[$value['appid']] = $value;
    }

    if ($sortids = C::t('user_setting')->fetch_by_skey('index_simple_appids')) {
        $appids = explode(',', $sortids);
        $temp = array();
        foreach ($appids as $appid) {
            if ($applist_1[$appid]) {
                $temp[$appid] = $applist_1[$appid];
                unset($applist_1[$appid]);
            }
        }

        foreach ($applist_1 as $appid => $value) {
            $temp[$appid] = $value;
        }
        $applist_1 = $temp;
    } else {
        //对应用根据disp 排序
        if ($applist_1) {
            $sort = array(
                'direction' => 'SORT_ASC',
                'field' => 'disp',
            );
            $arrSort = array();
            foreach ($applist_1 as $uniqid => $row) {
                foreach ($row as $key => $value) {
                    $arrSort[$key][$uniqid] = $value;
                }
            }
            if ($sort['direction']) {
                array_multisort($arrSort[$sort['field']], constant($sort['direction']), $applist_1);
            }
        }
    }
    $servertime = time() * 1000;
    $filedata = get_statis();
    include template('main');
}
function get_statis() {
    global $_G;
    $recents = $filedata = array();
    $explorer_setting = get_resources_some_setting();
    $param = array('resources_statis', $_G['uid']);
    $wheresql = " where uid = %d and fid = 0 and rid != '' ";
    $orderby = ' order by opendateline desc, editdateline desc';
    $limitsql = ' limit ' . 5;
    $recents = DB::fetch_all("select * from %t $wheresql $orderby $limitsql", $param);
    foreach ($recents as $v) {
        if ($val = C::t('resources')->fetch_info_by_rid($v['rid'])) {
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
                $val['opendateline'] = dgmdate($v['opendateline'], 'u');
                $val['img'] = geticonfromext($val['ext'], $val['type']);
                if ($val['gid']) {
                    $val['url'] = '#group&gid='.$val['gid'].'&fid='.$val['oid'];
                } else {
                    if ($val['oid']) {
                        $val['url'] = '#home&fid='.$val['oid'];
                    } else {
                        $val['url'] = '#home&fid='.$val['pfid'];
                    }
                }
                $filedata[] = $val;
            }
        }
    }
    return $filedata;
}