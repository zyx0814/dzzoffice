<?php
/*
 模板文件内调用方法
<div class="demo">{lang title}</div>
<div class="demo1">{lang dev_desc}</div>
*/
$lang = array (
    'appname'=>'测试' ,//应用名称 统一用 appname
    'menu_setting'=>'设置' ,//菜单类 的语言 统一 以  menu_ 开头
    
    'field_subtitle'=>'副标题' ,//字段类 的语言 统一 以  field_ 开头
    
    'field_subtitle_tip'=>'副标题说明' ,//字段说明类 的语言 统一 以  在字段的基础上以_info 结束
	
    'info_test_desc'	=>'这里编写自己的模板代码<br><br>默认已经引入了：<b>jQuery 1.10</b>、<b>bootstrap V3 css</b> 和 <b>dzz.api.js</b>', //说明类 的语言 统一 以  info_开头
	'info_title1'=>'应用开发示例',
    
    'button_setting'=>'保存',//按钮类 的语言 统一 以 button_ 开头
    'info_test_hook_one'=>'我是一个钩子调用程序,哪儿需要，哪儿调用-1',//按钮类 的语言 统一 以 button_ 开头
    'info_test_hook_two'=>'我是一个钩子调用程序,哪儿需要，哪儿调用-2'//按钮类 的语言 统一 以 button_ 开头
); 
?>