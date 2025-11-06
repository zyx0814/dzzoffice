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
$template = $_GET['template'] ?? '';
$about = array();
$identify = filter_var($_GET['modname'], FILTER_SANITIZE_STRING);
$appConfig = DZZ_ROOT . './dzz/' . $identify . '/config/config.php';
if ($_G['setting']['bbclosed']) {
    $sitelogo = 'static/image/common/logo.png';
} else {
    $sitelogo = $_G['setting']['sitelogo'] ? 'index.php?mod=io&op=thumbnail&size=small&path=' . dzzencode('attach::' . $_G['setting']['sitelogo']) : 'static/image/common/logo.png';
}
if ($identify && file_exists($appConfig)) {
    $config = include($appConfig);
    if (isset($config['about'])) {
        $about = $config['about'];
        $appinfo = C::t('app_market')->fetch_by_allidentifier($identify);
        if (empty($about['logo'])) {
            if ($appinfo['appico']) {
                $about['logo'] = $_G['setting']['attachurl'] . $appinfo['appico'];
            }
        }
        if (empty($about['version'])) $about['version'] = $appinfo['version'];
    }
}
if ($_G['language'] === 'zh-en' && isset($about['name_zh'])) {
    $about['name'] = $about['name_zh'];
} elseif ($_G['language'] === 'en-us' && isset($about['name_en'])) {
    $about['name'] = $about['name_en'];
} else {
    if (isset($about['name_zh'])) {
        $about['name'] = $about['name_zh'];
    } elseif (isset($about['name_en'])) {
        $about['name'] = $about['name_en'];
    } else {
        $about['name'] = $_G['setting']['sitename'] ? $_G['setting']['sitename'] : 'DzzOffice';
    }
}
$version = 'V' . CORE_VERSION;
if ($_G['ismobile'] && !$_GET['inajax']) {
    include template('mobile_about');
} else {
    if ($template == '1') {
        include template('lyear_about', 'lyear');
    } else {
        include template('about');
    }
}
exit();