<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/3/1
 * Time: 16:26
 */
if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('mod_run');//执行配置
Hook::listen('mod_start',$_GET);//模块路由
include $_GET['route_file'];
