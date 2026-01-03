<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class dzz_error {

    public static function system_error($message, $show = true, $save = true, $halt = true) {
        if (!empty($message)) {
            $message = lang($message);
        } else {
            $message = lang('error_unknow');
        }

        list($showtrace, $logtrace) = dzz_error::debug_backtrace();
        $messagesave = '<b>' . $message . '</b><br><b>PHP:</b>' . $logtrace;
        $BackTraceID = md5(dzz_error::clear($messagesave));
        if ($save) {
            dzz_error::write_error_log($messagesave . ' BackTraceID:' . $BackTraceID);
        }

        if ($show) {
            dzz_error::show_error('system', "<li>$message</li>", $showtrace, '', $BackTraceID);
        }

        if ($halt) {
            header('HTTP/1.1 503 Service Unavailable');
            exit();
        } else {
            return $message;
        }
    }

    public static function template_error($message, $tplname) {
        $message = lang($message);
        $tplname = str_replace(DZZ_ROOT, '', $tplname);
        $message = $message . ': ' . $tplname;
        dzz_error::system_error($message);
    }

    public static function debug_backtrace() {
        $skipfunc[] = 'error->debug_backtrace';
        $skipfunc[] = 'error->db_error';
        $skipfunc[] = 'error->template_error';
        $skipfunc[] = 'error->system_error';
        $skipfunc[] = 'db_mysql->halt';
        $skipfunc[] = 'db_mysql->query';
        $skipfunc[] = 'DB::_execute';

        $show = $log = '';
        $debug_backtrace = debug_backtrace();
        krsort($debug_backtrace);
        foreach ($debug_backtrace as $k => $error) {
            $file = str_replace(DZZ_ROOT, '', $error['file']);
            $func = isset($error['class']) ? $error['class'] : '';
            $func .= isset($error['type']) ? $error['type'] : '';
            $func .= isset($error['function']) ? $error['function'] : '';
            if (in_array($func, $skipfunc)) {
                break;
            }
            $error['line'] = sprintf('%04d', $error['line']);

            $show .= "<li>[Line: {$error['line']}]" . $file . "($func)</li>";
            $log .= (!empty($log) ? ' -> ' : '') . $file . '#' . $func . ':' . $error['line'];
        }
        return [$show, $log];
    }

    public static function db_error($message, $sql) {
        global $_G;

        list($showtrace, $logtrace) = dzz_error::debug_backtrace();
        $title = lang('db_' . $message);

        $db = &DB::object();
        $dberrno = $db->errno();
        $dberror = str_replace($db->tablepre, '', $db->error());
        $sql = dhtmlspecialchars(str_replace($db->tablepre, '', $sql));

        $msg = '<li>[Type] ' . $title . '</li>';
        $msg .= $dberrno ? '<li>[' . $dberrno . '] ' . $dberror . '</li>' : '';
        $msg .= $sql ? '<li>[Query] ' . $sql . '</li>' : '';

        $errormsg = '<b>' . $title . '</b>';
        $errormsg .= "[$dberrno]<br /><b>ERR:</b> $dberror<br />";
        if ($sql) {
            $errormsg .= '<b>SQL:</b> ' . $sql;
        }
        $errormsg .= "<br />";
        $errormsg .= '<b>PHP:</b> ' . $logtrace;
        $BackTraceID = md5(dzz_error::clear($errormsg));
        dzz_error::write_error_log($errormsg . ' BackTraceID:' . $BackTraceID);
        dzz_error::show_error('db', $msg, $showtrace, '', $BackTraceID);
        exit();

    }

    public static function exception_error($exception) {

        if ($exception instanceof DbException) {
            $type = 'db';
        } else {
            $type = 'system';
        }

        if ($type == 'db') {
            $errormsg = '(' . $exception->getCode() . ') ';
            $errormsg .= self::sql_clear($exception->getMessage());
            if ($exception->getSql()) {
                $errormsg .= '<div class="sql">';
                $errormsg .= self::sql_clear($exception->getSql());
                $errormsg .= '</div>';
            }
        } else {
            $errormsg = $exception->getMessage();
        }

        $trace = $exception->getTrace();
        krsort($trace);

        $trace[] = ['file' => $exception->getFile(), 'line' => $exception->getLine(), 'function' => 'break'];
        $logmsg = '';
        $phpmsg = [];
        foreach ($trace as $error) {
            if (!empty($error['function'])) {
                $fun = '';
                if (!empty($error['class'])) {
                    $fun .= $error['class'] . $error['type'];
                }
                $fun .= $error['function'] . '(';
                if (!empty($error['args'])) {
                    $mark = '';
                    foreach ($error['args'] as $arg) {
                        $fun .= $mark;
                        if (is_array($arg)) {
                            $fun .= 'Array';
                        } elseif (is_bool($arg)) {
                            $fun .= $arg ? 'true' : 'false';
                        } elseif (is_int($arg)) {
                            $fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? $arg : '%d';
                        } elseif (is_float($arg)) {
                            $fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? $arg : '%f';
                        } elseif (is_resource($arg)) {
                            $fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? 'Resource' : '%f';
                        } elseif (is_object($arg)) {
                            $fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? 'Object' : '%f';
                        } else {
                            $arg = (string)$arg;
                            $fun .= (defined('DZZ_DEBUG') && DZZ_DEBUG) ? '\'' . dhtmlspecialchars(substr(self::clear($arg), 0, 10)) . (strlen($arg) > 10 ? ' ...' : '') . '\'' : '%s';
                        }
                        $mark = ', ';
                    }
                }

                $fun .= ')';
                $error['function'] = $fun;
            }
            $phpmsg[] = [
                'file' => str_replace([DZZ_ROOT, '\\'], ['', '/'], $error['file']),
                'line' => $error['line'],
                'function' => $error['function'],
            ];
            $file = str_replace([DZZ_ROOT, '\\'], ['', '/'], $error['file']);
            $func = isset($error['class']) ? $error['class'] : '';
            $func .= isset($error['type']) ? $error['type'] : '';
            $func .= isset($error['function']) ? $error['function'] : '';
            $line = sprintf('%04d', $error['line']);
            $logmsg .= (!empty($logmsg) ? ' -> ' : '') . $file . '#' . $func . ':' . $line;
        }
        $messagesave = '<b>' . $errormsg . '</b><br><b>PHP:</b>' . $logmsg;
        $BackTraceID = md5(dzz_error::clear($messagesave));
        self::write_error_log($messagesave . ' BackTraceID:' . $BackTraceID);
        self::show_error($type, $errormsg, $phpmsg, '', $BackTraceID);
        exit();

    }

    public static function show_error($type, $errormsg, $phpmsg = '', $typemsg = '', $backtraceid = '') {
        global $_G;
        ob_end_clean();
        $gzip = isset($_G['gzipcompress']) ? $_G['gzipcompress'] : false;
        ob_start($gzip ? 'ob_gzhandler' : null);
        header("HTTP/1.1 503 Service Temporarily Unavailable");
        header("Status: 503 Service Temporarily Unavailable");
        header("Retry-After: 3600");
        $showError = isset($_G['config']['security']['error']['showerror']) ? $_G['config']['security']['error']['showerror'] : 2;
        $title = ($showError !=0) ? ($type == 'db' ? 'Database' : 'System') : 'General';
        $charset = isset($_G['config']['output']['charset']) ? $_G['config']['output']['charset'] : 'UTF-8';
        $clientIp = isset($_G['clientip']) ? $_G['clientip'] : 'Unknown';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'Unknown Host';
        echo <<<EOT
<!DOCTYPE html>
<html>
<head>
    <title>$host - $title Error</title>
    <meta charset="{$charset}" />
    <meta name="renderer" content="webkit" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
    body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
    #container { max-width: 1024px; margin: auto; }
    #message   { max-width: 1024px; color: black; }

    .red  {color: red;}
    a     {color: red; }
    h1 { color: #FF0000; font: 18pt "Verdana"; margin-bottom: 0.5em;}
    .bg1{ background-color: #FFFFCC;}
    .bg2{ background-color: #EEEEEE;}
    .table {background: #AAAAAA; font: 11pt Menlo,Consolas,"Lucida Console"}
    .table tbody{word-break: break-all;}
    .info {
        background: none repeat scroll 0 0 #F3F3F3;
        border: 0px solid #aaaaaa;
        border-radius: 10px 10px 10px 10px;
        color: #000000;
        font-size: 11pt;
        line-height: 160%;
        margin-bottom: 1em;
        padding: 1em;
    }

    .help {
        background: #F3F3F3;
        border-radius: 10px 10px 10px 10px;
        font: 14px verdana, arial, sans-serif;
        text-align: center;
        line-height: 160%;
        padding: 1em;
        margin: 1em 0;
    }

    .sql {
        background: none repeat scroll 0 0 #FFFFCC;
        border: 1px solid #aaaaaa;
        color: #000000;
        font: arial, sans-serif;
        font-size: 9pt;
        line-height: 160%;
        margin-top: 1em;
        padding: 4px;
    }
    </style>
</head>
<body>
<div id="container">
<h1>Dzz! $title Error</h1>

EOT;
        echo '<p>Time: ' . date('Y-m-d H:i:s O') . ' IP: ' . $clientIp . ' BackTraceID: ' . $backtraceid . '</p>';
        if(!empty($errormsg) && $showError != 0) {
            echo '<div class="info">' . $errormsg . '</div>';
        }
        if($showError == 0) {
			echo '<div class="info"><p>您好，系统暂时发生异常，无法完成您当前的操作</p></div>';
		}
        if(!empty($phpmsg) && $showError == 1) {
            echo '<div class="info">';
            echo '<p><strong>PHP Debug</strong></p>';
            echo '<table cellpadding="5" cellspacing="1" width="100%" class="table">';
            if (is_array($phpmsg)) {
                echo '<tr class="bg2"><td>No.</td><td>File</td><td>Line</td><td>Code</td></tr>';
                foreach ($phpmsg as $k => $msg) {
                    $k++;
                    echo '<tr class="bg1">';
                    echo '<td>' . $k . '</td>';
                    echo '<td>' . $msg['file'] . '</td>';
                    echo '<td>' . $msg['line'] . '</td>';
                    echo '<td>' . $msg['function'] . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td><ul>' . $phpmsg . '</ul></td></tr>';
            }
            echo '</table></div>';
        }
        echo '<div class="help">' . lang('suggestion_user') . '</div>';
        echo '<div class="help">' . lang('suggestion') . '</div>';
        echo '<div class="help"><a href="http://' . $host . '">' . $host . '</a> 已经将此出错信息详细记录在<a href="admin.php?mod=systemlog&operation=errorlog&keyword=' . $backtraceid . '" target="_blank">系统日志-系统错误</a>中, 由此给您带来的访问不便我们深感歉意</div>';
        echo '</div></body></html>';
    }

    public static function clear($message) {
        return str_replace(["\t", "\r", "\n"], " ", $message);
    }

    public static function sql_clear($message) {
        $message = self::clear($message);
        $message = str_replace(DB::object()->tablepre, '', $message);
        return dhtmlspecialchars($message);
    }

    public static function write_error_log($message) {
        $loginfo = ["mark" => "errorlog", "content" => $message];
        Hook::listen('systemlog', $loginfo);
    }

}