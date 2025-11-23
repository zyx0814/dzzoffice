<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$do = isset($_GET['do']) ? $_GET['do'] : '';
if ($do == 'saveIndex') {
    if (!$_G['uid']) exit(json_encode(array('error' => 'notlogin')));
    $appids = implode(',', $_GET['appids']);
    $ret = C::t('user_setting')->update_by_skey('index_simple_appids', $appids);
    exit(json_encode(array('success' => $ret)));
} else {
    if ($_G['uid']) {
        $userstatus = C::t('user_status')->fetch($_G['uid']);
        $space = dzzgetspace($_G['uid']);
    }
    //获取已安装应用
    $applist = C::t('app_market')->fetch_all_by_default($_G['uid']);
    $applist_1 = array();

    foreach ($applist as $key => $value) {
        if ($value['isshow'] < 1) continue;
        if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
            $value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
        }
        $value['url'] = replace_canshu($value['appurl']);
        $applist_1[$value['appid']] = $value;
    }

    if ($_G['uid'] && $sortids = C::t('user_setting')->fetch_by_skey('index_simple_appids')) {
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
    $regdatedays = floor((time() - $_G['member']['regdate']) / (60 * 60 * 24));
    include template('main');
}