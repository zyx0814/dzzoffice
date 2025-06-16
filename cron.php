<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
//计划任务触发程序
//如果这个程序运行，可以修改config文件($_config['remote']['cron'] = 1)，正常的访问不运行计划任务；	
// echo '* * * * php cron.php >>/dev/null 2>$1' > /etc/crontab
// crontab cron.txt
define('APPTYPEID', 200);
define('CURSCRIPT', 'cron');
define('DZZSCRIPT', 'index.php');
require __DIR__.'/core/coreBase.php';
$dzz = C::app();
$dzz->init_user=true;
$dzz->init_setting=true;
$dzz->init_session=false;
$dzz->init_cron=false;
$dzz->init_misc=true;
$dzz->init();
if($_GET['cronid']){
    dzz_cron::run(intval($_GET['cronid']));
}else{
    dzz_cron::runcron();
}
exit('success');