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
include_once libfile('function/cache');
$operation = empty($_GET['operation']) ? 'basic' : trim($_GET['operation']);
$setting = C::t('setting')->fetch_all(null);
if ($setting['thumbsize']) {
    $setting['thumbsize'] = unserialize($setting['thumbsize']);
    foreach ($setting['thumbsize'] as $key => $value) {
        $value['width'] = intval($value['width']);
        if (!$value['width']) {
            $value['width'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 800 : 1440));
        }
        $value['height'] = intval($value['height']);
        if (!$value['height']) {
            $value['height'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 600 : 900));
        }
        $setting['thumbsize'][$key] = $value;
    }
}
if (!submitcheck('settingsubmit')) {
    if ($operation == 'basic') {
        $navtitle = lang('members_verify_base') . ' - ' . lang('appname');
        $spacesize = DB::result_first("select maxspacesize from " . DB::table('usergroup_field') . " where groupid='9'");
        if ($setting['defaultdepartment']) {
            $patharr = C::t('organization')->getPathByOrgid($setting['defaultdepartment'], false);
            $defaultdepartment = implode(' - ', ($patharr));

        }
        if (empty($defaultdepartment)) {
            $defaultdepartment = lang('no_join_agency_department');
            $setting['defaultdepartment'] = 'other';
        }
        $setting['sitebeian'] = dhtmlspecialchars($setting['sitebeian']);
        $applist = DB::fetch_all("select appname,identifier from %t where isshow>0 and `available`>0 and app_path='dzz' ORDER BY disp", array('app_market'));
    } elseif ($operation == 'desktop') {
        if ($setting['desktop_default'] && !is_array($setting['desktop_default'])) {
            $setting['desktop_default'] = unserialize($setting['desktop_default']);
        }
        if (!$setting['desktop_default']) {
            $setting['desktop_default'] = array('iconview' => 2);
        }
        if ($_G['setting']['dzz_iconview']) {
            $iconview = $_G['setting']['iconview'];
        } else {
            $iconview = C::t('iconview')->fetch_all();
        }
    } elseif ($operation == 'upload') {
        $setting['maxChunkSize'] = round($setting['maxChunkSize'] / (1024 * 1024), 2);
        $navtitle = lang('upload_download_set') . ' - ' . lang('appname');
        $setting['unRunExts'] = implode(',', dunserialize($setting['unRunExts']));
    } elseif ($operation == 'notification') {
        $navtitle = lang('notification_set') . ' - ' . lang('appname');
    } elseif ($operation == 'at') {
        $navtitle = '@' . lang('sector_set') . ' - ' . lang('appname');
        $setting['at_range'] = dunserialize($setting['at_range']);
        $usergroups = DB::fetch_all("select f.*,g.grouptitle from %t f LEFT JOIN %t g ON g.groupid=f.groupid where f.groupid NOT IN ('2','3','4','5','6','7','8') order by groupid ASC", array('usergroup_field', 'usergroup'));
    } elseif ($operation == 'access') {
        $navtitle = lang('loginSet') . ' - ' . lang('appname');
        $welcomemsg = array();
		if($setting['welcomemsg'] == 1) {
			$welcomemsg[] = '1';
		} elseif($setting['welcomemsg'] == 2) {
			$welcomemsg[] = '2';
		} elseif($setting['welcomemsg'] == 3) {
			$welcomemsg[] = '1';
			$welcomemsg[] = '2';
		} else {
			$welcomemsg[] = '0';
		}
        $setting['strongpw'] = dunserialize($setting['strongpw']);
    } elseif ($operation == 'denlu') {
        $navtitle = lang('loginSet') . ' - ' . lang('appname');
    } elseif ($operation == 'space') {//获取空间设置结果
        $navtitle = lang('spaceSet') . ' - ' . lang('appname');
    } elseif ($operation == 'permgroup') {
        $perms = get_permsarray();//获取所有权限;
        $permgroups = C::t('resources_permgroup')->fetch_all();
        $navtitle = lang('permGroupSet') . ' - ' . lang('appname');
    } elseif ($operation == 'usergroup') {
        $usergroups = DB::fetch_all("select f.*,g.grouptitle,g.type from %t f LEFT JOIN %t g ON g.groupid=f.groupid where f.groupid NOT IN ('3','4','5','6','8') order by groupid ASC", array('usergroup_field', 'usergroup'));
        $navtitle = lang('usergroup_perm') . ' - ' . lang('appname');
    } elseif ($operation == 'datetime') {
        $navtitle = lang('time_or_date') . ' - ' . lang('appname');
        $checktimeformat = array($setting['timeformat'] == 'H:i' ? 24 : 12 => 'checked');
        $setting['userdateformat'] = dateformat($setting['userdateformat']);
        $setting['dateformat'] = dateformat($setting['dateformat']);
        $timezones = lang('setting_timezone');
    } elseif ($operation == 'sec') {
        $navtitle = lang('verification_code_set') . ' - ' . lang('appname');
        $seccodecheck = /*$secreturn =*/
            1;
        $sectpl = '<br /><sec>: <sec><sec>';
        $checksc = array();
        $setting['seccodedata'] = dunserialize($setting['seccodedata']);
        $setting['reginput'] = dunserialize($setting['reginput']);
        $seccodestatus[1] = $setting['seccodestatus'] & 1;
        $seccodestatus[2] = $setting['seccodestatus'] & 2;
        $seccodestatus[3] = $setting['seccodestatus'] & 4;
    } elseif ($operation == 'desktop') {
        $navtitle = lang('desktop_set') . ' - ' . lang('appname');
    } elseif ($operation == 'loginset') {
        $navtitle = lang('login_page_set') . ' - ' . lang('appname');
        if ($setting['loginset'] && !is_array($setting['loginset'])) {
            $setting['loginset'] = unserialize($setting['loginset']);
                if ($setting['loginset']['orgid']) {
                $patharr = C::t('organization')->getPathByOrgid($setting['loginset']['orgid'], false);
                $orgid = implode(' - ', ($patharr));
            }
            if (empty($orgid)) {
                $orgid = '不显示任何机构';
                $setting['loginset']['orgid'] = 'other';
            }
        }
    } elseif ($operation == 'smileyset') {
        $navtitle = lang('expression_set') . ' - ' . lang('appname');
    } elseif ($operation == 'mail') {
        $navtitle = lang('mail') . ' - ' . lang('appname');
        $setting['mail'] = dunserialize($setting['mail']);
        $passwordmask = $setting['mail']['auth_password'] ? $setting['mail']['auth_password'][0] . '********' . substr($setting['mail']['auth_password'], -2) : '';
        $smtps = array();
        foreach ($setting['mail']['smtp'] as $id => $smtp) {
            $smtp['authcheck'] = $smtp['auth'] ? 'checked' : '';
            $smtp['auth_password'] = $smtp['auth_password'] ? $smtp['auth_password'][0] . '********' . substr($smtp['auth_password'], -2) : '';
            $smtps[$id] = $smtp;
        }
    } elseif ($operation == 'censor') {
        $navtitle = lang('words_set') . ' - ' . lang('appname');
        loadcache('censor');
        $badwords = $_G['cache']['censor']['words'];
        $replace = empty($_G['cache']['censor']['replace']) ? '*' : $_G['cache']['censor']['replace'];
    }
} else {
    $settingnew = $_GET['settingnew'];
    if ($operation == 'basic') {
        $settingnew['sitename'] = dhtmlspecialchars($settingnew['sitename']);
        $settingnew['bbname'] = $settingnew['sitename'];
        foreach ($settingnew['thumbsize'] as $key => $value) {
            $value['width'] = intval($value['width']);
            if (!$value['width']) {
                $value['width'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 800 : 1440));
            }
            $value['height'] = intval($value['height']);
            if (!$value['height']) {
                $value['height'] = ($key == 'samll' ? 256 : ($key == 'middle' ? 600 : 900));
            }
            $settingnew['thumbsize'][$key] = $value;
        }

        //设置默认应用
        if ($settingnew["default_mod"] && $settingnew["default_mod"] != $_GET["old_default_mod"]) {
            $configfile = DZZ_ROOT . 'data/cache/default_mod.php';
            $configarr = array();
            $configarr['default_mod'] = $settingnew["default_mod"];
            @file_put_contents($configfile, "<?php \t\n return " . var_export($configarr, true) . ";");
        }
    } elseif ($operation == 'upload') {
        if ($settingnew['unRunExts'])
            $settingnew['unRunExts'] = explode(',', trim($settingnew['unRunExts'], ','));
        else
            $settingnew['unRunExts'] = array();
        if (!in_array('php', $settingnew['unRunExts']))
            $settingnew['unRunExts'][] = 'php';
        if (empty($settingnew['maxChunkSize']) || $settingnew['maxChunkSize'] < 0) {
            $settingnew['maxChunkSize'] = 1;
        }
        $settingnew['maxChunkSize'] = intval($settingnew['maxChunkSize'] * 1024 * 1024);
        $group = $_GET['group'];
        foreach ($group as $key => $value) {
            C::t('usergroup_field')->update(intval($key), array('maxspacesize' => intval($value['maxspacesize']), 'maxattachsize' => intval($value['maxattachsize']), 'attachextensions' => trim($value['attachextensions'])));
        }
        include_once libfile('function/cache');
        updatecache('usergroups');
    } elseif ($operation == 'notification') {
        $settingnew['notificationrefresh'] = intval($settingnew['notificationrefresh']);
        if ($settingnew['notificationrefresh'] <= 0) {
            $settingnew['notificationrefresh'] = 60;
        }
    } elseif ($operation == 'mail') {
        $setting['mail'] = dunserialize($setting['mail']);
        $oldsmtp = $settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp'] : $settingnew['mail']['esmtp'];
        $deletesmtp = $settingnew['mail']['mailsend'] != 1 ? ($settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp']['delete'] : $settingnew['mail']['esmtp']['delete']) : array();
        $settingnew['mail']['smtp'] = array();
        foreach ($oldsmtp as $id => $value) {
            if ((empty($deletesmtp) || !in_array($id, $deletesmtp)) && !empty($value['server']) && !empty($value['port'])) {
                $passwordmask = $setting['mail']['smtp'][$id]['auth_password'] ? $setting['mail']['smtp'][$id]['auth_password'][0] . '********' . substr($setting['mail']['smtp'][$id]['auth_password'], -2) : '';
                $value['auth_password'] = $value['auth_password'] == $passwordmask ? $setting['mail']['smtp'][$id]['auth_password'] : $value['auth_password'];
                $settingnew['mail']['smtp'][] = $value;
            }
        }

        if (!empty($_GET['newsmtp'])) {
            foreach ($_GET['newsmtp']['server'] as $id => $server) {
                if (!empty($server) && !empty($_GET['newsmtp']['port'][$id])) {
                    $settingnew['mail']['smtp'][] = array('server' => $server, 'port' => $_GET['newsmtp']['port'][$id] ? intval($_GET['newsmtp']['port'][$id]) : 25, 'auth' => $_GET['newsmtp']['auth'][$id] ? 1 : 0, 'from' => $_GET['newsmtp']['from'][$id], 'auth_username' => $_GET['newsmtp']['auth_username'][$id], 'auth_password' => $_GET['newsmtp']['auth_password'][$id]);
                }

            }
        }
    } elseif ($operation == 'denlu') {
        $settingnew['numberoflogins'] = intval($settingnew['numberoflogins']);
        if ($settingnew['numberoflogins'] <= 0) {
            $settingnew['numberoflogins'] = 1;
        }

        $settingnew['forbiddentime'] = intval($settingnew['forbiddentime']);
        if ($settingnew['forbiddentime'] <= 0) {
            $settingnew['forbiddentime'] = 1;
        }
        $settingnew['oltimespan'] = intval($settingnew['oltimespan']);
    } elseif ($operation == 'access') {
        isset($settingnew['reglinkname']) && empty($settingnew['reglinkname']) && $settingnew['reglinkname'] = lang('register_immediately');
        $settingnew['reglinkname'] = dhtmlspecialchars($settingnew['reglinkname']);
        $settingnew['pwlength'] = intval($settingnew['pwlength']);
        $settingnew['regstatus'] = intval($settingnew['regstatus']);
        $settingnew['regctrl'] = intval($settingnew['regctrl']);
        /*if(in_array('open', $settingnew['regstatus']) && in_array('invite', $settingnew['regstatus'])) {
         $settingnew['regstatus'] = 3;
         } elseif(in_array('open', $settingnew['regstatus'])) {
         $settingnew['regstatus'] = 1;
         } elseif(in_array('invite', $settingnew['regstatus'])) {
         $settingnew['regstatus'] = 2;
         } else {
         $settingnew['regstatus'] = 0;
         }*/
        $settingnew['welcomemsg'] = (array)$settingnew['welcomemsg'];
         if(in_array('1', $settingnew['welcomemsg']) && in_array('2', $settingnew['welcomemsg'])) {
         $settingnew['welcomemsg'] = 3;
         } elseif(in_array('1', $settingnew['welcomemsg'])) {
         $settingnew['welcomemsg'] = 1;
         } elseif(in_array('2', $settingnew['welcomemsg'])) {
         $settingnew['welcomemsg'] = 2;
         } else {
         $settingnew['welcomemsg'] = 0;
         }

        if (empty($settingnew['strongpw'])) {
            $settingnew['strongpw'] = array();
        }
        if(isset($settingnew['welcomemsgtitle'])) {
            $settingnew['welcomemsgtitle'] = cutstr(trim(dhtmlspecialchars($settingnew['welcomemsgtitle'])), 75);
        }
    } elseif ($operation == 'space') {//空间设置
        $settingnew['memorySpace'] = intval($settingnew['memorySpace']);
        $settingnew['orgmemorySpace'] = isset($settingnew['orgmemorySpace']) ? intval($settingnew['orgmemorySpace']) : 0;
        $settingnew['groupmerorySpace'] = isset($settingnew['groupmerorySpace']) ? intval($setting['groupmerorySpace']) : 0;
        $settingnew['systemSpace'] = isset($settingnew['systemSpace']) ? intval($settingnew['systemSpace']) : 0;
        /*$setarr =array(//接收设置数据处理
            //'usermemoryOn' => isset($setting['usermemoryOn'])?$setting['usermemoryOn']:0,
            //'mermoryusersetting' => $setting['mermoryusersetting'],
            //'memoryorgusers' => $setting['memoryorgusers'],
            'memorySpace' => intval($setting['memorySpace']),
            //'organizationOn' => isset($setting['organizationOn'])?$setting['organizationOn']:0,
            'orgmemorySpace' => isset($setting['orgmemorySpace'])?intval($setting['orgmemorySpace']):0,
            //'groupOn' =>  isset($setting['groupOn'])?$setting['groupOn']:'',
            'groupmerorySpace'=>isset($setting['groupmerorySpace'])?intval($setting['groupmerorySpace']):0,
            'systemSpace'=>isset($setting['systemSpace'])?intval($setting['systemSpace']):0,
        );
        if(C::t('setting')->update_batch($setarr)){
            //更新缓存
            updatecache('setting');
            //更新机构最大空间值,部门不做处理
            if($setarr['orgmemorySpace']){
                DB::update('organization',array('maxspacesize'=>$setarr['orgmemorySpace']),array('`type`'=>0,'forgid'=>0));
            }
            //更新群组最大空间值
            if($setarr['groupmerorySpace']){
                DB::update('organization',array('maxspacesize'=>$setarr['groupmerorySpace']),array('`type`'=>1));
            }

        }*/
    } elseif ($operation == 'datetime') {
        if (isset($settingnew['timeformat'])) {
            $settingnew['timeformat'] = $settingnew['timeformat'] == '24' ? 'H:i' : 'h:i A';
        }
        if (isset($settingnew['dateformat'])) {
            $settingnew['dateformat'] = dateformat($settingnew['dateformat'], 'format');
        }
    } elseif ($operation == 'sec') {
        $settingnew['seccodestatus'] = bindec(intval($settingnew['seccodestatus'][3]) . intval($settingnew['seccodestatus'][2]) . intval($settingnew['seccodestatus'][1]));

    } elseif ($operation == 'censor') {
        $data = array('replace' => trim($_GET['replace']), 'words' => $_GET['badwords']);
        savecache('censor', $data);
        showmessage('do_success', dreferer());
    } elseif ($operation == 'loginset') {
        $settingnew['loginset']['title'] = dhtmlspecialchars($settingnew['loginset']['title']);
        $settingnew['loginset']['subtitle'] = dhtmlspecialchars($settingnew['loginset']['subtitle']);
        if ($back = trim($settingnew['loginset']['background'])) {
            if (strpos($back, '#') === 0) {
                $settingnew['loginset']['bcolor'] = $back;
            } else {
                $arr = explode('.', $back);
                $ext = array_pop($arr);
                if ($ext && in_array(strtolower($ext), array('jpg', 'jpeg', 'gif', 'png', 'webp'))) {
                    $settingnew['loginset']['img'] = $back;
                    $settingnew['loginset']['bcolor'] = '';
                } else {
                    $settingnew['loginset']['url'] = $back;
                    $settingnew['loginset']['bcolor'] = '';
                }
            }
        } else {
            $settingnew['loginset']['bcolor'] = '';
        }
    }
    $updatecache = FALSE;
    if ($settingnew) {
        $settings = array();
        foreach ($settingnew as $key => $val) {
            if ($setting[$key] != $val) {
                $updatecache = TRUE;
                if (in_array($key, array('timeoffset', 'regstatus', 'oltimespan', 'seccodestatus'))) {
                    $val = (float)$val;
                }

                $settings[$key] = $val;
            }
        }
        if ($settings) {
            C::t('setting')->update_batch($settings);
        }
    }
    if ($operation == 'basic') {
        if ($settingnew['sitelogo'] && $settingnew['sitelogo'] != $setting['sitelogo']) {
            if ($setting['sitelogo']) C::t('attachment')->delete_by_aid($setting['sitelogo']);
            C::t('attachment')->addcopy_by_aid($settingnew['sitelogo'], 1);
        }
    }
    if ($updatecache) {
        updatecache('setting');
    }
    if ($operation == 'upload') {
        dfsockopen($_G['siteurl'] . 'misc.php?mod=setunrun', 0, '', '', FALSE, '', 1);
    }
    showmessage('do_success', dreferer());
}
function dateformat($string, $operation = 'formalise') {
    $string = dhtmlspecialchars(trim($string));
    $replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
    return str_replace($replace[0], $replace[1], $string);
}

include template('main');
?>
