<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$returntype = isset($_GET['returnType']) ? $_GET['returnType'] : 'json';//返回值方式
$navtitle = lang('appname');
$do = isset($_GET['do']) ? $_GET['do'] : '';
$operation = isset($_GET['operation']) ? $_GET['operation'] : 'cplog';
$systemlog_setting = unserialize($_G["setting"]["systemlog_setting"]);
if ($do == 'getinfo') {
    $operationarr = array_keys($systemlog_setting);
    $operation = in_array($operation, $operationarr) ? $operation : "cplog";
    $limit = empty($_GET['limit']) ? 20 : $_GET['limit'];
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $page = (isset($_GET['page'])) ? intval($_GET['page']) : 1;
    $start = ($page - 1) * $limit;
    $logdir = DZZ_ROOT . './data/log/';
    $logfiles = get_log_files($logdir, $operation);
    if ($logfiles) {
        $logfiles = array_reverse($logfiles);
        $firstlogs = file($logdir . $logfiles[0]);
        $firstlogsnum = count($firstlogs);
        $countlogfile = count($logfiles);
        $count = ($countlogfile - 1) * 4000 + $firstlogsnum;
        $logs = array();
        $jishu = 4000;//每个日志文件最多行数
        $start = ($page - 1) * $limit;
        $lastlog = $last_secondlog = "";

        $newdata = array();
        foreach ($logfiles as $k => $v) {
            $nowfilemaxnum = ($jishu * ($k + 1)) - ($jishu - $firstlogsnum);
            $startnum = ($nowfilemaxnum - $jishu) <= 0 ? 0 : ($nowfilemaxnum - $jishu + 1);
            $newdata[] = array("file" => $v, "start" => $startnum, "end" => $nowfilemaxnum);
        }
        //print_R($newdata);
        //查询当前分页数据位于哪个日志文件
        $lastlog = $last_secondlog = "";
        foreach ($newdata as $k => $v) {
            if ($start <= $v["end"]) {
                $lastlog = $v;
                if (($start + $limit) < $v["end"]) {

                } else {
                    if (isset($newdata[$k + 1])) {
                        $last_secondlog = $newdata[$k + 1];
                    }
                }
                break;
            }
        }

        $j = 0;
        for ($i = $lastlog["start"]; $i < $lastlog["end"]; $i++) {
            if ($start <= ($lastlog["start"] + $j)) {
                break;
            }
            $j++;
        }
        //获取数据开始
        $logs = file($logdir . $lastlog["file"]);
        $logs = array_reverse($logs);
        if ($keyword) {
            foreach ($logs as $key => $value) {
                if (!empty($_GET['keyword']) && strpos($value, $_GET['keyword']) === FALSE) {
                    unset($logs[$key]);
                }
            }
            $count = count($logs);
        }
        if ($lastlog["file"] != $logfiles[0]) {
            $j++;
        }
        $logs = array_slice($logs, $j, $limit);
        $onecountget = count($logs);

        $jj = 0;
        if ($last_secondlog) {
            for ($i = $last_secondlog["start"]; $i < $last_secondlog["end"]; $i++) {
                if (($jj) >= ($limit - $onecountget)) {
                    break;
                }
                $jj++;
            }
        }

        if ($last_secondlog) {
            $logs2 = file($logdir . $last_secondlog["file"]);
            $logs2 = array_reverse($logs2);
            $end = $limit - count($logs);
            $logs2 = array_slice($logs2, 0, $jj);
            $logs = array_merge($logs, $logs2);
        }
        if ($logs) {
            if (!function_exists('mb_check_encoding') || !function_exists('mb_convert_encoding')) {
                $errorResponse = [
                    "code" => 1,
                    "msg" => "mb_check_encoding或mb_convert_encoding函数不存在，请检查PHP配置或升级PHP版本。",
                    "count" => 0,
                    "data" => [],
                ];
                exit(json_encode($errorResponse));
            }
            $usergroup = array();
            foreach (C::t('usergroup')->range() as $group) {
                $usergroup[$group['groupid']] = $group['grouptitle'];
            }
            $list = array();
            foreach ($logs as $logrow) {
                $log = explode("\t", $logrow);
                if (empty($log[1])) {
                    continue;
                }
                $log[1] = dgmdate($log[1], 'Y-m-d H:i:s');
                $log[3] = $usergroup[$log[3]];
                if (mb_check_encoding($log[5], 'UTF-8')) {
                    $loginfo = $log[5];
                } else {
                    $loginfo = mb_convert_encoding($log[5], 'UTF-8', 'auto');
                }
                $list[] = [
                    "operator" => $log[2] ? $log[2] : "",
                    "usergroup" => $log[3] ? $log[3] : "",
                    "ip" => $log[4] ? $log[4] : "",
                    "time" => $log[1] ? $log[1] : "",
                    "loginfo" => $loginfo,
                    "visit" => $log[6] ? $log[6] : "",
                    "from" => $log[7] ? $log[7] : "",
                    "info" => $log[8] ? $log[8] : "",
                ];
            }
        }
    }
    header('Content-Type: application/json');
    $return = [
        "code" => 0,
        "msg" => "",
        "count" => $count ? $count : 0,
        "data" => $list ? $list : [],
    ];
    $jsonReturn = json_encode($return);
    if ($jsonReturn === false) {
        $errorMessage = json_last_error_msg();
        $errorResponse = [
            "code" => 1,
            "msg" => "JSON 编码失败，请刷新重试: " . $errorMessage,
            "count" => 0,
            "data" => [],
        ];
        exit(json_encode($errorResponse));
    }
    exit($jsonReturn);
}
include template('list');
function getactionarray() {
    $isfounder = true;
    unset($topmenu['index'], $menu['index']);
    $actioncat = $actionarray = array();
    $actioncat[] = 'setting';
    $actioncat = array_merge($actioncat, array_keys($topmenu));
    foreach ($menu as $tkey => $items) {
        foreach ($items as $item) {
            $actionarray[$tkey][] = $item;
        }
    }
    return array('actions' => $actionarray, 'cats' => $actioncat);
}

function get_log_files($logdir = '', $action = 'action') {
    $dir = opendir($logdir);
    $files = array();
    while ($entry = readdir($dir)) {
        $files[] = $entry;
    }
    closedir($dir);

    if ($files) {
        sort($files);
        $logfile = $action;
        $logfiles = array();
        $ym = '';
        foreach ($files as $file) {
            if (strpos($file, $logfile) !== FALSE) {
                if (substr($file, 0, 6) != $ym) {
                    $ym = substr($file, 0, 6);
                }
                $logfiles[$ym][] = $file;
            }
        }
        if ($logfiles) {
            $lfs = array();
            foreach ($logfiles as $ym => $lf) {
                $lastlogfile = $lf[0];
                unset($lf[0]);
                $lf[] = $lastlogfile;
                $lfs = array_merge($lfs, $lf);
            }
            return $lfs;
        }
        return array();
    }
    return array();
}