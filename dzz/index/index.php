<?php
/*默认页跳转*/
Hook::listen('check_login');//检查是否登录，未登录跳转到登录界面
include template('main');
exit();