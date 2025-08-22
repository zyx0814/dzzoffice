<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
include_once libfile('function/admin');
$op = isset($_GET['op']) ? $_GET['op'] : '';
$do = isset($_GET['do']) ? $_GET['do'] : '';
$navtitle = lang('memory') . ' - ' . lang('appname');
$do_clear_ok = $do == 'clear' ? ' (清理完毕)' : '';

$cache_extension = C::memory()->extension;
$cache_config = C::memory()->config;
$cache_type = C::memory()->type;

$dir = DZZ_ROOT.'./core/class/memory';
$qaadir = dir($dir);
$cachelist = array();
while($entry = $qaadir->read()) {
    if(!in_array($entry, array('.', '..')) && preg_match("/^memory\_driver\_[\w\.]+$/", $entry) && substr($entry, -4) == '.php' && strlen($entry) < 30 && is_file($dir.'/'.$entry)) {
        $cache = str_replace(array('.php', 'memory_driver_'), '', $entry);
        $class_name = 'memory_driver_'.$cache;
        $memory = new $class_name();
        $available = is_array($cache_config[$cache]) ? !empty($cache_config[$cache]['server']) : !empty($cache_config[$cache]);
        $cachelist[] = array($memory->cacheName,
            $memory->env($config) ? '<span class="text-success">'.lang('supportted').'</span>' : '<span class="text-danger">'.lang('unsupportted').'</span>',
            $available ? '<span class="text-primary">'.lang('open').'</span>' : lang('close')
        );
    }
}
$env_str = '';
foreach($cachelist as $cache) {
    $env_str .= "<tr>\n";
    $env_str .= "<td>$cache[0]</td>\n";
    $env_str .= "<td>$cache[1]</td>\n";
    $env_str .= "<td>$cache[2]</td>\n";
    $env_str .= "</tr>\n";
}

if($do == 'clear') {
    C::memory()->clear();
}
include template('memory');
?>
