<?php

if(!defined('IN_DZZ')) {
    exit('Access Denied');
}
Hook::listen('mod_run');//执行配置
@include Hook::listen('mod_start',$_GET,null,true);//模块路由
dexit();