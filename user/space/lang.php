<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
    exit('Access Denied');
}
$lang = isset($_GET['lang']) ? trim($_GET['lang']) : 'zh-cn';
$langList = $_G['config']['output']['language_list'];
if(!$_G['uid']) {
    exit(json_encode(array('msg' => 'error')));
}
if (isset($langList[$lang])) {
    C::t('user')->update($_G['uid'], array('language' => ($lang)));
    exit(json_encode(array('msg' => 'success')));
} else {
    exit(json_encode(array('msg' => 'error')));
}