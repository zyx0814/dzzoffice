<?php
require __DIR__.'/coreBase.php';
  // error_reporting(E_ALL);
$dzz = C::app();
Hook::listen('dzz_initbefore');//初始化前钩子
$dzz->init();
Hook::listen('dzz_initafter');//初始化后钩子
$files = Hook::listen('dzz_route',$_GET);//路由钩子，返回文件路径
foreach($files as $v){
    require $v;//包含文件
}