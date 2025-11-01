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
        return array($show, $log);
    }

    public static function db_error($message, $sql) {
        global $_G;

        list($showtrace, $logtrace) = dzz_error::debug_backtrace();
        $title = lang('db_' . $message);
        $title_msg = lang('db_error_message');
        $title_sql = lang('db_query_sql');
        $title_backtrace = lang('backtrace');
        $title_help = lang('db_help_link');

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

        $trace[] = array('file' => $exception->getFile(), 'line' => $exception->getLine(), 'function' => 'break');
        $logmsg = '';
        $phpmsg = array();
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
            $phpmsg[] = array(
                'file' => str_replace(array(DZZ_ROOT, '\\'), array('', '/'), $error['file']),
                'line' => $error['line'],
                'function' => $error['function'],
            );
            $file = str_replace(array(DZZ_ROOT, '\\'), array('', '/'), $error['file']);
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
        $gzip = $_G['gzipcompress'];
        ob_start($gzip ? 'ob_gzhandler' : null);
        header("HTTP/1.1 503 Service Temporarily Unavailable");
        header("Status: 503 Service Temporarily Unavailable");
        header("Retry-After: 3600");
        $host = $_SERVER['HTTP_HOST'];
        $title = $type == 'db' ? 'Database' : 'System';
        echo <<<EOT
<!DOCTYPE html>
<html>
<head>
    <title>$host - $title Error</title>
    <meta charset="{$_G['config']['output']['charset']}" />
    <meta name="renderer" content="webkit" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
    <!--
    body { background-color: white; color: black; font: 9pt/11pt verdana, arial, sans-serif;}
    #container { max-width: 1024px; margin: auto; }
    #message   { max-width: 1024px; color: black; }

    .red  {color: red;}
    a:link     { font: 9pt/11pt verdana, arial, sans-serif; color: red; }
    a:visited  { font: 9pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
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
        font: 12px verdana, arial, sans-serif;
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
    -->
    </style>
</head>
<body>
<div id="container">
<h1>Dzz! $title Error</h1>

EOT;
        if (defined('CORE_VERSION')) {
            $VERSION = CORE_VERSION;
        } else {
            $VERSION = 'Unknown';
        }
        echo '<p>Time: ' . date('Y-m-d H:i:s O') . ' IP: ' . $_G['clientip'] . ' version: ' . $VERSION . ' BackTraceID: ' . $backtraceid . '</p>';
        if (!empty($errormsg)) {
            echo '<div class="info">' . $errormsg . '</div>';
        }
        if (!empty($phpmsg)) {
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

        $endmsg = lang('error_end_message', array('host' => $host));
        echo <<<EOT
<div class="help">$endmsg</div>
</div>
</body>
</html>
EOT;
    }

    public static function clear($message) {
        return str_replace(array("\t", "\r", "\n"), " ", $message);
    }

    public static function sql_clear($message) {
        $message = self::clear($message);
        $message = str_replace(DB::object()->tablepre, '', $message);
        return dhtmlspecialchars($message);
    }

    public static function write_error_log($message) {
        $loginfo = array("mark" => "errorlog", "content" => $message);
        Hook::listen('systemlog', $loginfo);
        return;
    }

}