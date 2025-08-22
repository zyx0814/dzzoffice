<?php
return array(
    'allow_view'=>1,//是否有访问权限，0=>任何人都可访问,1=>需登录可访问,2=>管理员可访问,3=>创始人可访问;默认值为1
    'allow_robot'=>false,//是否允许机器人爬取
    /*此三项配置如果未配置，将读取系统默认配置访问*/
    'about'=>array(//关于信息，默认不显示关于信息
        'name_zh'=>'',//中文名称，留空不显示
        'name_en'=>'test',//英文名称，留空不显示
    'version'=>'X1.0'//版本信息，留空不显示
    ),
    'libfile'=>'',
    /*加载函数文件，有两种格式：
    *1.字符串格式,多个文件之间用','隔开，如：function/example,test,将会加载当前模块下的function下的function_example.php和test.php
    * 2.数组格式array('file_name'=>'test','file_folder'=>'fun','mod_name'=>'test'),将会加载test模块下的fun下的test.php
    * 此配置默认为空
    */
);