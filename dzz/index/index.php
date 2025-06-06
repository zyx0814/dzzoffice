<?php
if ($_GET['do'] == 'saveIndex') {
    $appids = implode(',', $_GET['appids']);
    C::t('user_setting')->update_by_skey('index_simple_appids', $appids);
    $ret = C::t('user_setting')->insert(array('index_simple_appids' => $appids));
    exit(json_encode(array('success' => $ret)));
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
            if ($newappids) C::t('app_user')->insert_by_uid($_G['uid'], $newappids);
            C::t('user_field')->update($_G['uid'], array('applist' => implode(',', $applist)));
        }
    }
    $userstatus = C::t('user_status')->fetch($_G['uid']);
    //最近使用文件
    $explorer_setting = get_resources_some_setting();
    $data = $recents = $files = $folders = $folderdata = $filedata = array();
    $limit = 5;
    $param = array('resources_statis', $_G['uid']);
    $orderby = ' order by opendateline desc, editdateline desc, edits desc, views desc';
    $limitsql = ' limit ' . $limit;
    $files = DB::fetch_all("select * from %t where uid = %d and fid = 0 and rid != '' $orderby $limitsql", $param);
    $folders = DB::fetch_all("select * from %t where uid = %d  and fid != 0 and rid != '' $orderby $limitsql", $param);
    $results = array();
    foreach ($folders as $v) {
        $results[] = $v;
    }
    foreach ($files as $v) {
        $results[] = $v;
    }
    foreach ($results as $v) {
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
                if ($val['type'] == 'folder') {
                    $folderdata[] = $val;
                } else {
                    $filedata[] = $val;
                }
            }
        }
    }
    $space = C::t('user_profile')->get_user_info_by_uid($_G['uid']);
    $space['fusesize'] = formatsize($space['usesize']);
    if (!$_G['cache']['usergroups']) loadcache('usergroups');
    $usergroup = $_G['cache']['usergroups'][$space['groupid']];
    if ($usergroup['maxspacesize'] == 0) {
        $space['maxspacesize'] = 0;
    } elseif ($usergroup['maxspacesize'] < 0) {
        if (($space['addsize'] + $space['buysize']) > 0) {
            $space['maxspacesize'] = ($space['addsize'] + $space['buysize']) * 1024 * 1024;
        } else {
            $space['maxspacesize'] = -1;
        }
    } else {
        $space['maxspacesize'] = ($usergroup['maxspacesize'] + $space['addsize'] + $space['buysize']) * 1024 * 1024;
    }
    if ($space['maxspacesize'] > 0) {
        $space['fmaxspacesize'] = formatsize($space['maxspacesize']);
    } elseif ($space['maxspacesize'] == 0) {
        $space['fmaxspacesize'] = lang('no_limit');
    } else {
        $space['fmaxspacesize'] = lang('unallocated_space');
    }
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
    include template('main');
}