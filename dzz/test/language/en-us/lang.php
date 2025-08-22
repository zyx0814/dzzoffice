<?php
/*
 模板文件内调用方法
<div class="demo">{lang title}</div>
<div class="demo1">{lang dev_desc}</div>
*/
$lang = array (
    'appname'=>'Test' ,//应用名称 统一用 appname
    'menu_setting'=>'Setting' ,//菜单类 的语言 统一 以  menu_ 开头
    
    'field_subtitle'=>'Subtitle' ,//字段类 的语言 统一 以  field_ 开头
    
    'field_subtitle_tip'=>'Subtitle Explain' ,//字段说明类 的语言 统一 以  在字段的基础上以_info 结束
	
    'info_test_desc'	=>'Write your own template code here,<br><br>For example, the introduction of <b>jQuery 1.10</b>、<b>bootstrap V3 css</b> 和 <b>dzz.api.js</b>', //说明类 的语言 统一 以  info_开头
	'info_title1'=>'Example of application development',
    
    'button_setting'=>'Save',//按钮类 的语言 统一 以 button_ 开头
    'info_test_hook_one'=>'I am a hook call procedure, where need, where to call -1',//按钮类 的语言 统一 以 button_ 开头
    'info_test_hook_two'=>'I am a hook call procedure, where need, where to call -2'//按钮类 的语言 统一 以 button_ 开头
); 
?>