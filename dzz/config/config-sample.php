<?php
return array(
    /*是否有访问权限，0=>任何人都可访问,1=>需登录可访问,2=>管理员可访问,3=>创始人可访问;默认值为1*/
    'allow_view'=>1,
    'allow_robot'=>false,//是否允许机器人爬取
    /*此三项配置如果未配置，将读取系统默认配置访问*/
    'default_mod'=>'index',//默认应用CONFIG DEFAULT_MOD
    'default_op' => 'index',//CONFIG DEFAULT_OP
    'dafault_action' => 'index',//CONFIG DAFAULT_ACTION
    /*加载函数文件，有两种格式：
     *1.字符串格式,多个文件之间用','隔开，如：function/example,test,将会加载当前模块下的function下的function_example.php和test.php
     * 2.数组格式array('file_name'=>'test','file_folder'=>'fun','mod_name'=>'test'),将会加载test模块下的fun下的test.php
     * 此配置默认为空
    */
    'libfile'=>'',
);