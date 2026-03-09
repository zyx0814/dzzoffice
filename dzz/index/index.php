<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$do = isset($_GET['do']) ? $_GET['do'] : '';
if ($do == 'saveIndex') {
    if (!$_G['uid']) exit(json_encode(['error' => 'notlogin']));
    $appids = implode(',', $_GET['appids']);
    $ret = C::t('user_setting')->update_by_skey('index_simple_appids', $appids);
    exit(json_encode(['success' => $ret]));
} else {
    if ($_G['uid']) {
        $userstatus = C::t('user_status')->fetch($_G['uid']);
        $space = dzzgetspace($_G['uid']);
    }
    //获取已安装应用
    $applist = C::t('app_market')->fetch_all_by_default($_G['uid']);
    $applist_1 = [];

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
        $temp = [];
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
    } elseif ($applist_1) {//对应用根据disp 排序
        $sort = [
            'direction' => 'SORT_ASC',
            'field' => 'disp',
        ];
        $arrSort = [];
        foreach ($applist_1 as $uniqid => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $applist_1);
        }
    }
    $timestamp = time();
    $servertime = $timestamp * 1000;
    $regdatedays = human_time(intval($_G['member']['regdate']));
    $hour = date('H', $timestamp);

    // 计算问候语
    if ($hour < 6) {
        $greeting = '凌晨好！';
    } elseif ($hour < 9) {
        $greeting = '早上好！';
    } elseif ($hour < 12) {
        $greeting = '上午好！';
    } elseif ($hour < 14) {
        $greeting = '中午好！';
    } elseif ($hour < 18) {
        $greeting = '下午好！';
    } elseif ($hour < 22) {
        $greeting = '晚上好！';
    } else {
        $greeting = '夜深了！';
    }

    // 格式化日期和时间
    $currentDate = date('Y年n月j日', $timestamp);
    $weekdayMap = ['日', '一', '二', '三', '四', '五', '六'];
    $weekday = '星期' . $weekdayMap[date('w', $timestamp)];
    $currentTime = date('H:i:s', $timestamp);
    include template('main');
}
/**
 * 精准人性化加入时长（自动识别闰年、大小月）
 * @param int $time 加入时间戳
 * @return string
 */
function human_time($time)
{
    try {
        // 时间戳转 DateTime（支持 int 时间戳）
        $join = new DateTime();
        $join->setTimestamp($time);
        
        $now = new DateTime();
        $diff = $join->diff($now);

        $result = '';
        if ($diff->y > 0) $result .= $diff->y . '年';
        if ($diff->m > 0) $result .= $diff->m . '个月';
        if ($diff->d > 0) $result .= $diff->d . '天';

        return $result ?: '今天加入';
    } catch (Exception $e) {
        return '时间异常';
    }
}