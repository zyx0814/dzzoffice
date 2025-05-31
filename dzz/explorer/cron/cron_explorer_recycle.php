<?php
/**
 * //cronname:回收站自动删除任务
 * //week:
 * //day:
 * //hour:0
 * //minute:
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$time = date("Y-m-d", TIMESTAMP);
$time = strtotime($time);//今天0点
$day = C::t('setting')->fetch('explorer_finallydelete');
$executetime = 0;
if ($day == 0) {
    $executetime = $time + 86400;
} elseif ($day > 0) {
    $executetime = $time - 86400 * $day;
}
if ($executetime > 0) {
    $rids = C::t('resources_recyle')->fetch_rid_bydate($executetime);
    if (count($rids) > 0) {
        $i = 0;
        foreach ($rids as $v) {
            if ($i <= 100) {
                IO::Delete($v, true);
            }
            $i++;
        }
    }
}