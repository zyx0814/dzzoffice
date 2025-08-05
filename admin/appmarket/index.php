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
$navtitle = lang('installed') . ' - ' . lang('appname');
include libfile('function/organization');
$op = isset($_GET['op']) ? $_GET['op'] : '';
$do = isset($_GET['do']) ? $_GET['do'] : '';
if (submitcheck('appsubmit')) {
    $dels = $_GET['del'];
    $allids = array();
    foreach ($_GET['disp'] as $key => $value) {
        if (!in_array($key, $dels))
            C::t('app_market')->update($key, array('disp' => $value));
    }
    //删除应用
    if ($dels) {
        C::t('app_market')->delete_by_appid($dels);
    }
    showmessage('do_success', dreferer());
}
$grouptitle = array('0' => lang('all'), '-1' => lang('visitors_visible'), '1' => lang('members_available'), '2' => lang('section_administrators_available'), '3' => lang('system_administrators_available'));
if ($do == 'notinstall') {
    $identifiers = array();
    $sql = "identifier!=''";
    foreach (DB::fetch_all("select appid,identifier from %t where %i ", array('app_market', $sql)) as $value) {
        $identifiers[$value['appid']] = $value['identifier'];
    }
    $appdir = DZZ_ROOT . '/dzz';
    $list = [];
    try {
        $appsIterator = new DirectoryIterator($appdir);
        foreach ($appsIterator as $entry) {
            // 跳过无效条目和已安装的应用
            if ($entry->isDot() || !$entry->isDir() || in_array($entry->getFilename(), $identifiers)) {
                continue;
            }
            
            $entryName = $entry->getFilename();
            $entryPath = $entry->getPathname();
            $xmlFilePath = "{$entryPath}/dzz_app_{$entryName}.xml";
            
            // 检查主应用XML文件
            if (file_exists($xmlFilePath)) {
                $importtxt = file_get_contents($xmlFilePath);
                if ($importtxt !== false) {
                    processAppXml($importtxt, $entryName, $list, $identifiers);
                }
            } else {
                // 检查子目录
                try {
                    $subdirIterator = new DirectoryIterator($entryPath);
                    foreach ($subdirIterator as $subEntry) {
                        if ($subEntry->isDot() || !$subEntry->isDir()) {
                            continue;
                        }
                        
                        $subEntryName = $subEntry->getFilename();
                        $subEntryIdentifier = "{$entryName}:{$subEntryName}";
                        
                        // 跳过已安装的子应用
                        if (in_array($subEntryIdentifier, $identifiers)) {
                            continue;
                        }
                        
                        $subEntryPath = $subEntry->getPathname();
                        $subXmlFilePath = "{$subEntryPath}/dzz_app_{$entryName}_{$subEntryName}.xml";
                        
                        if (file_exists($subXmlFilePath)) {
                            $importtxt = file_get_contents($subXmlFilePath);
                            if ($importtxt !== false) {
                                processAppXml($importtxt, $subEntryIdentifier, $list, $identifiers);
                            }
                        }
                    }
                } catch (Exception $e) {
                    // 记录子目录遍历错误
                    error_log("Error reading subdirectory {$entryPath}: " . $e->getMessage());
                }
            }
        }
    } catch (Exception $e) {
        showmessage('Error reading directory '.$appdir, dreferer());
    }
    include template('notinstall');
    exit;
} else {
    //获取所有标签top50；
    $tags = DB::fetch_all("SELECT * FROM %t WHERE hot>0 ORDER BY HOT DESC limit 50", array('app_tag'), 'tagid');
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
    $tagid = intval($_GET['tagid']);
    $group = intval($_GET['group']);
    $page = empty($_GET['page']) ? 1 : intval($_GET['page']);
    $perpage = 20;
    $gets = array('mod' => 'appmarket', 'keyword' => $keyword, 'tagid' => $tagid, 'group' => $group,'do' => $do);
    $theurl = BASESCRIPT . "?" . url_implode($gets);
    $refer = urlencode($theurl . '&page=' . $page);

    $order = ' ORDER BY disp';
    $start = ($page - 1) * $perpage;
    $apps = array();
    $string = " 1 ";
    $param = array('app_market');
    if ($keyword) {
        $string .= " and appname like %s or vendor like %s or identifier like %s";
        $param[] = '%' . $keyword . '%';
        $param[] = '%' . $keyword . '%';
        $param[] = '%' . $keyword . '%';
    }
    if ($do == 'available') {
        $string .= " and available<1";
    } elseif ($do == 'enable') {
        $string .= " and available>0";
    }
    if ($tagid) {
        $appids = C::t('app_relative')->fetch_appids_by_tagid($tagid);
        $string .= " and appid IN (" . dimplode($appids) . ")";
    }
    if ($group) {
        $sql = " and `group` = '{$group}'";
        $string .= " and `group` = '{$group}'";
    }
    if ($count = DB::result_first("SELECT COUNT(*) FROM %t WHERE " . $string, $param)) {
        $apps = DB::fetch_all("SELECT * FROM %t WHERE " . $string . " $order limit $start,$perpage", $param);
        $multi = multi($count, $perpage, $page, $theurl, 'pull-right');
    }

    $list = array();
    foreach ($apps as $value) {
        $value['tags'] = C::t('app_relative')->fetch_all_by_appid($value['appid']);
        if ($value['appico'] != 'dzz/images/default/icodefault.png' && !preg_match("/^(http|ftp|https|mms)\:\/\/(.+?)/i", $value['appico'])) {
            $value['appico'] = $_G['setting']['attachurl'] . $value['appico'];
        }
        $value['appurl'] = replace_canshu($value['appurl']);
        $value['appadminurl'] = replace_canshu($value['appadminurl']);
        $value['grouptitle'] = $grouptitle[$value['group']];
        $value['department'] = getDepartmentByAppid($value['appid']);
        $list[] = $value;
    }
    include template('index');
}
function processAppXml($xmlContent, $identifier, &$list, $identifiers) {
    global $grouptitle;
    
    // 避免处理已安装的应用
    if (in_array($identifier, $identifiers)) {
        return;
    }
    
    // 使用更健壮的XML解析
    try {
        $apparray = getimportdata('Dzz! app', 0, 1, $xmlContent);
        $value = $apparray['app'] ?? [];
        
        if (!empty($value['appname'])) {
            $value['appurl'] = replace_canshu($value['appurl']);
            $value['appadminurl'] = replace_canshu($value['appadminurl']);
            $value['appname'] = dhtmlspecialchars($value['appname']);
            $value['identifier'] = dhtmlspecialchars($identifier);
            $value['version'] = dhtmlspecialchars($value['version']);
            $value['vendor'] = dhtmlspecialchars($value['vendor']);
            $value['grouptitle'] = $grouptitle[$value['group']];
            $list[$identifier] = $value;
        }
    } catch (Exception $e) {
        showmessage('Error processing XML for '.$identifier, dreferer());
    }
}