<?php
/* @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
include libfile('class/uploadhandler');
$options = array('accept_file_types' => '/\.(gif|jpe?g|png)$/i', 'upload_dir' => $_G['setting']['attachdir'] . 'cache/', 'upload_url' => $_G['setting']['attachurl'] . 'cache/', 'max_file_size' => 2 * 1024 * 1024, 'thumbnail' => array('max-width' => 256, 'max-height' => 256));
$upload_handler = new uploadhandler($options);
exit();	