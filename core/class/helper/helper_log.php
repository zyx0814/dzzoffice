<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class helper_log {

    public static function runlog($file, $message, $halt = 0) {
        $loginfo = ["mark" => $file, "content" => $message];
        Hook::listen('systemlog', $loginfo);
        if ($halt) {
            exit();
        }
    }

    public static function writelog($file, $log) {
        $loginfo = ["mark" => $file, "content" => $log];
        Hook::listen('systemlog', $loginfo);
    }
}

