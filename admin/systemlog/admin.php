<?php
//error_reporting(E_ALL);
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
define('NOROBOT', TRUE);
if (submitcheck('settingsubmit')) {
    if ($_G['adminid'] != 1) {
        showmessage('no_privilege');
    }
    $settingnew = $_GET["settingnew"];
    $data = array();
    foreach ($settingnew["mark"] as $k => $v) {
        if (isset($data[$v])) {
            showmessage("日志标记 " . $v . " 重复");
        }
        $data[$v] = array(
            "title" => $settingnew["title"][$k],
            "is_open" => intval($settingnew["is_open"][$k]),
            "issystem" => $settingnew["issystem"][$k]
        );
    }

    $settingnew = serialize($data);
    $update = array(
        "systemlog_open" => $_GET["systemlog_open"],
        "systemlog_setting" => $settingnew,
    );
    $result = C::t('setting')->update_batch($update);
    if ($result) {
        include_once libfile('function/cache');
        updatecache('setting');
    }
    writelog('otherlog', "更新日志设置");
    showmessage('do_success', dreferer());
} else {
    $systemlog_setting = unserialize($_G["setting"]["systemlog_setting"]);
    $navtitle = lang('systemlog_setting') . ' - ' . lang('appname');
    include template("admin");
} 