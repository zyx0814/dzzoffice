<?php
require __DIR__.'/coreBase.php';
// error_reporting(E_ALL);
$dzz = C::app();
$dzz->init();

$admincp = new dzz_admincp();
$admincp->core  =  $dzz;
$admincp->init();

Hook::listen('dzz_initafter');//初始化后钩子
$files = Hook::listen('dzz_route',$_GET);//路由钩子，返回文件路径
foreach($files as $v){
    require $v;//包含文件
}

