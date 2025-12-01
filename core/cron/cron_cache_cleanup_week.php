<?php
/*
 * 计划任务脚本 定期清理 缓存数据
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}


//清空临时缓存区
removedirectory($_G['setting']['attachdir'] . 'temp/', true);

//清空临时缓存区
$time = 60 * 60 * 24 * 1; //1天 1天没有修改的将被删除；
removedirectory($_G['setting']['attachdir'] . 'cache/', true, $time);

//清理上传未成功的文件
$like = '%dzz_upload_%';
$like1 = '%FTP_upload_%';
foreach (DB::fetch_all("select * from %t where (cachekey like %s or cachekey like %s) and dateline<%d", array('cache', $like, $like1, TIMESTAMP - 24 * 60 * 60)) as $value) {
    @unlink($_G['setting']['attachdir'] . $value['cachevalue']);
    C::t('cache')->delete($value['cachekey']);
}

?>
