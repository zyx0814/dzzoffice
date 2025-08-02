<?php

namespace admin\systemlog\classes;
class Systemlog {
    //$arr 必须传入 level,content
    public function run($arr = array()) {
        global $_G;
        if ($_G['setting']['systemlog_open'] > 0) {
            $systemlog_setting = unserialize($_G['setting']['systemlog_setting']);
            //判断是否开启该类型日志
            if (!isset($arr["mark"]) || (isset($systemlog_setting[$arr["mark"]]) && $systemlog_setting[$arr["mark"]]["is_open"] != 1)) {
                return;
            }
            $file = $arr["mark"];//$arr["level"];//级别
            $log = $arr["content"];
            $yearmonth = "dzz";//dgmdate(TIMESTAMP, 'Ym', $_G['setting']['timeoffset']);
            $logdir = DZZ_ROOT . './data/log/';
            $logfile = $logdir . $yearmonth . '_' . $file . '.php';

            //检查并返回当前log文件行数
            $i = 0;
            if (file_exists($logfile)) {
                $fp = fopen($logfile, "r");
                while (!feof($fp)) {
                    //每次最多读取1M
                    if ($data = fread($fp, 1024 * 1024 * 1)) {
                        //计算读取到的行数
                        $num = substr_count($data, "\n");
                        $i += $num;
                    }
                }
                fclose($fp);
            }
            //每4000行重新生成日志文件
            if ($i >= 4000) {
                $dir = opendir($logdir);
                $length = strlen($file);
                $maxid = $id = 0;
                while ($entry = readdir($dir)) {
                    if (strpos($entry, $yearmonth . '_' . $file) !== false) {
                        $id = intval(substr($entry, $length + 8, -4));
                        $id > $maxid && $maxid = $id;
                    }
                }
                closedir($dir);
                $logfilebak = $logdir . $yearmonth . '_' . $file . '_' . ($maxid + 1) . '.php';
                @rename($logfile, $logfilebak);
            }
            if ($fp = @fopen($logfile, 'a')) {
                @flock($fp, 2);
                if (!is_array($log)) {
                    $log = array($log);
                }
                $cur_url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
                $from_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                if (!$_G['uid']) {
                    $username = '游客';
                } else {
                    $username = $_G['username'];
                }
                $source_type = 'unknown';
                if (php_sapi_name() === 'cli') {
                    $source_type = 'CLI'; // 命令行执行（如计划任务）
                } elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    $source_type = 'AJAX'; // AJAX异步请求
                } elseif (!empty($_G['inajax'])) {
                    $source_type = 'DZZ_AJAX'; // 系统内AJAX
                } else {
                    $source_type = 'Web'; // 普通Web请求
                }
                foreach ($log as $tmp) {
                    $tmp = implode("\t", clearlogstring(array($_G['timestamp'], $username, $_G['groupid'], $_G['clientip'], $tmp, $cur_url, $from_url, $_SERVER['HTTP_USER_AGENT'] ?? 'none', "uid=" . $_G['uid'], $source_type)));
                    fwrite($fp, "<?PHP exit;?>\t" . str_replace(array('<?', '?>'), '', $tmp) . "\n");
                }
                fclose($fp);
            }
        }
    }
}