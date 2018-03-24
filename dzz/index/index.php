<?php
/*默认页跳转*/

$app=DB::fetch_first("select * from %t where (`group`<3 OR identifier='appmanagement') and isshow>0 and `available`>0 ORDER BY disp",array('app_market'));

$url=replace_canshu($app['appurl']);
header("Location: ".$url);
exit();
