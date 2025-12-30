<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
global $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$operation = empty($_GET['operation']) ? '' : trim($_GET['operation']);

if ($operation == 'deleteIco') {//删除文件到回收站
    $arr = array();
    $names = array();
    $i = 0;
    $icoids = $_GET['rids'];
    $ridarr = array();
    $bz = isset($_GET['bz']) ? trim($_GET['bz']) : '';
    foreach ($icoids as $icoid) {
        $icoid = dzzdecode($icoid);
        if (empty($icoid)) {
            continue;
        }
        if (strpos($icoid, '../') !== false) {
            $arr['msg'][$return['rid']] = lang('illegal_calls');
        } else {
            $return = IO::Delete($icoid);
            if (!$return['error']) {
                //处理数据
                $arr['sucessicoids'][$return['rid']] = $return['rid'];
                $arr['msg'][$return['rid']] = 'success';
                $ridarr[] = $return['rid'];
                $i++;
            } else {
                $arr['msg'][$return['rid']] = $return['error'];
            }
        }
    }
    //更新剪切板数据
    if (!empty($ridarr)) {
        C::t('resources_clipboard')->update_data_by_delrid($ridarr);
    }
    echo json_encode($arr);
    exit();
} elseif ($operation == 'rename') {
    if (!$path = dzzdecode($_GET['path'])) {
        exit(json_encode(array('error' => lang('parameter_error'))));
    }
    $text = str_replace('...', '', getstr(IO::name_filter($_GET['text']), 80));
    $ret = IO::rename($path, $text);
    exit(json_encode($ret));

}