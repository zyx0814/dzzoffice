<?php
namespace core\dzz;

use \core as C;

class Modrun{

    //初始运行方法
   public static function run(){

        global $_config,$_G;

        self::check_perm($_config['allow_view']);

        self::check_robot($_config['allow_robot']);

        self::loadFile($_config['libfile']);
   }

   //检测权限
   private static function check_perm($perm = 1){
       global $_G;
       switch ($perm){
           case 0:
               break;
           case 1:if(!$_G['uid']) Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面//exit('Access Denied');
               break;
           case 2:  if(!$_G['uid']) Hook::listen('check_login');
                    if($_G['adminid']!=1) exit('Access Denied');
               break;
           case 3: if(!$_G['uid']) Hook::listen('check_login');
               if(!C::t('user')->checkfounder($_G['member'])) exit('Access Denied');
               break;
           default: exit('arg error');

       }
   }

   //检测机器人访问权限
   private static function check_robot($robotAllow = false){
        if(!$robotAllow){

            $dzz = C::app();

            $dzz->reject_robot(); //阻止机器人访问
        }
   }

   //加载文件
   private static function loadFile($files = null){

       if(is_array($files)){

           foreach($files as $v){

               require_once libfile((isset($v['file_name']) ? $v['file_name']:''),(isset($v['file_folder']) ? $v['file_folder']:''),(isset($v['mod_name']) ? $v['mod_name']:''));

           }

       }elseif(is_string($files)){

           $files = explode(',',$files);

           foreach ($files as $v){
                if($v){
                    require_once libfile($v);
                }

           }
       }
   }
}