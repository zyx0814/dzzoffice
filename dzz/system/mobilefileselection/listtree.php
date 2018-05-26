<?php
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
echo 'aaaa';
die;
global  $_G;
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
$uid = $_G['uid'];
