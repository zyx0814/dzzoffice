<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
$operation = $_GET['operation'] ? $_GET['operation'] : '';
$template = isset($_GET['template']) ? $_GET['template'] : '';
if ($operation == 'app') {
    $applist = C::t('app_market')->fetch_all_by_default($_G['uid']);
    $applist_1 = array();

    foreach ($applist as $key => $value) {
        if ($value['isshow'] < 1) continue;
        if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
            $value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
        }
        $value['url'] = replace_canshu($value['appurl']);
        $applist_1[] = $value;
    }
    //对应用根据disp 排序
    if ($applist_1) {
        $sort = array(
            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field' => 'disp', //排序字段
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
    if ($template == '1') {
        include template('lyear_app_ajax', 'lyear');
    } else {
        include template('app_ajax');
    }
    exit();
}



