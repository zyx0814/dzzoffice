<?php

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

function dshowmessage($message, $url_forward = '', $values = array(), $extraparam = array(), $custom = 0) {
    global $_G, $show_message;

    if ($extraparam['break']) {
        return;
    }
    $_G['inshowmessage'] = true;

    $param = array(
        'header' => false,       // 是否发送 HTTP 头
        'timeout' => null,       // 是否自动跳转
        'refreshtime' => 3,      // 跳转延迟（默认3秒）
        'closetime' => null,     // 弹窗关闭延迟
        'locationtime' => null,  // 定位跳转延迟
        'alert' => null,         // 消息类型（error/right/info）
        'return' => false,       // 是否显示返回按钮
        'redirectmsg' => 0,      // 重定向消息标识
        'msgtype' => 1,          // 消息展示类型（1-页面/2-模态框/3-Ajax）
        'showmsg' => true,       // 是否显示消息内容
        'showdialog' => false,   // 是否显示对话框
        'login' => false,        // 是否强制登录
        'handle' => false,       // 是否触发回调函数
        'extrajs' => '',         // 额外 JS 代码
        'striptags' => false,    // 是否过滤 HTML 标签
        'showid' => ''           // 指定消息渲染的 DOM ID
    );

    $navtitle = lang('board_message');

    if ($custom) {
        $alerttype = 'alert_info';
        $show_message = $message;
        include template('common/showmessage');
        dexit();
    }

    $handlekey = '';
    $_GET['handlekey'] = !empty($_GET['handlekey']) && preg_match('/^\w+$/', $_GET['handlekey']) ? $_GET['handlekey'] : '';
    if (!empty($_G['inajax'])) {
        $handlekey = $_GET['handlekey'] = !empty($_GET['handlekey']) ? dhtmlspecialchars($_GET['handlekey']) : '';
        $param['handle'] = true;
        $param['msgtype'] = empty($_GET['ajaxmenu']) && (empty($_POST) || !empty($_GET['nopost'])) ? 2 : 3;
    }
    if ($url_forward) {
        $param['timeout'] = true;
        if ($param['handle'] && !empty($_G['inajax'])) {
            $param['showmsg'] = false;
        }
    }

    foreach ($extraparam as $k => $v) {
        $param[$k] = $v;
    }
    if (array_key_exists('set', $extraparam)) {
        $setdata = array('1' => array('msgtype' => 3));
        if ($setdata[$extraparam['set']]) {
            foreach ($setdata[$extraparam['set']] as $k => $v) {
                $param[$k] = $v;
            }
        }
    }

    $timedefault = intval($param['refreshtime'] === null ? $_G['setting']['refreshtime'] : $param['refreshtime']);
    if ($param['timeout'] !== null) {
        $refreshsecond = !empty($timedefault) ? $timedefault : 3;
        $refreshtime = $refreshsecond * 1000;
    } else {
        $refreshtime = $refreshsecond = 0;
    }

    if ($param['login'] && $_G['uid'] || $url_forward) {
        $param['login'] = false;
    }

    $param['header'] = $url_forward && $param['header'] ? true : false;

    if ($_GET['ajaxdata'] === 'json') {
        $param['header'] = '';
    }

    if ($param['header']) {
        header("HTTP/1.1 301 Moved Permanently");
        dheader("location: " . str_replace('&amp;', '&', $url_forward));
    }
    $url_forward_js = addslashes(str_replace('\\', '%27', $url_forward));
    if(!empty($param['location']) && !empty($_G['inajax'])) {
        include template('common/header_ajax');
        echo '<script type="text/javascript" reload="1">window.location.href=\'' . $url_forward_js . '\';</script>';
        include template('common/footer_ajax');
        dexit();
    }
    $vars = explode(':', $message);
    if (count($vars) == 2) {
        $show_message = lang($vars[1], $values, null, $vars[0]);
    } else {
        $show_message = lang($message, $values);
    }

    if (isset($_GET['ajaxdata'])) {
        if ($_GET['ajaxdata'] === 'json') {
            helper_output::json(array('code' => 1, 'msg' => $show_message, 'message' => $show_message, 'data' => $values));
        } else if ($_GET['ajaxdata'] === 'html') {
            helper_output::html($show_message);
        }
    }

    if ($param['msgtype'] == 2 && $param['login']) {
        dheader('location: user.php?mod=login');
    }

    $show_jsmessage = str_replace("'", "\\'", $param['striptags'] ? strip_tags($show_message) : $show_message);

    if((!$param['showmsg'] || !empty($param['showid'])) && !defined('IN_MOBILE') ) {
        $show_message = '';
    }

    $allowreturn = !$param['timeout'] && !$url_forward && !$param['login'] || $param['return'] ? true : false;
    if ($param['alert'] === null) {
        $alerttype = $url_forward ? (preg_match('/\_(succeed|success|成功)$/', $message) ? 'alert_right' : 'alert_info') : ($allowreturn ? 'alert_error' : 'alert_info');
    } else {
        $alerttype = 'alert_' . $param['alert'];
    }

    $extra = '';
    if(!empty($param['showid'])) {
        $extra .= 'if($(\'' . $param['showid'] . '\')) {$(\'' . $param['showid'] . '\').innerHTML = \'' . $show_jsmessage . '\';}';
    }
    if ($param['handle']) {
        $valuesjs = $comma = $subjs = '';
        foreach ($values as $k => $v) {
            $v = daddslashes($v);
            if (is_array($v)) {
                $subcomma = '';
                foreach ($v as $subk => $subv) {
                    $subjs .= $subcomma . '\'' . $subk . '\':\'' . $subv . '\'';
                    $subcomma = ',';
                }
                $valuesjs .= $comma . '\'' . $k . '\':{' . $subjs . '}';
            } else {
                $valuesjs .= $comma . '\'' . $k . '\':\'' . $v . '\'';
            }
            $comma = ',';
        }
        $valuesjs = '{' . $valuesjs . '}';
        if ($url_forward) {
            $extra .= 'if(typeof succeedhandle_' . $handlekey . '==\'function\') {succeedhandle_' . $handlekey . '(\'' . $url_forward_js . '\', \'' . $show_jsmessage . '\', ' . $valuesjs . ');}';
        } else {
            $extra .= 'if(typeof errorhandle_' . $handlekey . '==\'function\') {errorhandle_' . $handlekey . '(\'' . $show_jsmessage . '\', ' . $valuesjs . ');}';
        }
    }
    if ($param['closetime'] !== null) {
        $param['closetime'] = $param['closetime'] === true ? $timedefault : $param['closetime'];
    }
    if ($param['locationtime'] !== null) {
        $param['locationtime'] = $param['locationtime'] === true ? $timedefault : $param['locationtime'];
    }
    if ($handlekey) {
        if ($param['showdialog']) {
            $modes = array('alert_error' => 'alert', 'alert_right' => 'right', 'alert_info' => 'notice');
            $extra .= 'hideWindow(\'' . $handlekey . '\');showDialog(\'' . $show_jsmessage . '\', \'' . $modes[$alerttype] . '\', null, ' . ($param['locationtime'] !== null ? 'function () { window.location.href =\'' . $url_forward_js . '\'; }' : 'null') . ', 0, null, null, null, null, ' . ($param['closetime'] ? $param['closetime'] : 'null') . ', ' . ($param['locationtime'] ? $param['locationtime'] : 'null') . ');';
            $param['closetime'] = null;
            $st = '';
            if ($param['showmsg']) {
                $show_message = '';
            }
        }
        if ($param['closetime'] !== null) {
            $extra .= 'setTimeout("hideWindow(\'' . $handlekey . '\')", ' . ($param['closetime'] * 1000) . ');';
        }
    } else {
        $st = $param['locationtime'] !== null ? 'setTimeout("window.location.href =\'' . $url_forward_js . '\';", ' . ($param['locationtime'] * 1000) . ');' : '';
    }
    if (!$extra && $param['timeout']) {
        $extra .= 'setTimeout("window.location.href =\'' . $url_forward_js . '\';", ' . $refreshtime . ');';
    }
    $show_message .= $extra ? '<script type="text/javascript" reload="1">' . $extra . $st . '</script>' : '';
    $show_message .= $param['extrajs'] ? $param['extrajs'] : '';
    if ((defined('template') && template == '1') || $_G['config']['template'] == '1') {
        include template('common/showmessage', 'lyear');
    } else {
        include template('common/showmessage');
    }
    exit();
}
?>