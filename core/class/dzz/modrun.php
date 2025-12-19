<?php

namespace core\dzz;

use \core as C;

class Modrun {

    //初始运行方法
    public static function run() {

        global $_config, $_G;

        self::check_perm($_config['allow_view']);

        self::check_robot($_config['allow_robot']);

        self::loadFile($_config['libfile']);
    }

    //检测权限
    private static function check_perm($perm = 1) {
        global $_G;
        switch ($perm) {
            case 0: // 不检查权限
                break;
            case 1:// 检查是否为登录用户
                if (!$_G['uid']) Hook::listen('check_login');
                break;
            case 2: // 检查是否为管理员
                Hook::listen('adminlogin');
                break;
            case 3: // 检查是否为创始人
                if (!$_G['uid']) Hook::listen('check_login');
                if (!C::t('user')->checkfounder($_G['member'])) exit('Access Denied');
                break;
            default:
                exit('arg error');
        }
    }

    //检测机器人访问权限
    private static function check_robot($robotAllow = false) {
        if (!$robotAllow) {
            $dzz = C::app();
            $dzz->reject_robot(); //阻止机器人访问
        }
    }

    //加载文件
    private static function loadFile($files = null) {
        if (!$files) return;
        global $_config, $_G;
        if (is_array($files)) {
            foreach ($files as $v) {
                require_once libfile((isset($v['file_name']) ? $v['file_name'] : ''), (isset($v['file_folder']) ? $v['file_folder'] : ''), (isset($v['mod_name']) ? $v['mod_name'] : ''));
            }

        } elseif (is_string($files)) {
            $files = explode(',', $files);
            foreach ($files as $v) {
                if ($v) {
                    require_once libfile($v);
                }
            }
        }
    }
}