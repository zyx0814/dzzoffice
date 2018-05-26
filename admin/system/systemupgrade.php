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
$navtitle = lang('upgrade') . ' - ' . lang('admin_navtitle');
@set_time_limit(0);
include_once DZZ_ROOT . './core/core_version.php';
include_once libfile('function/admin');
include_once libfile('function/cache');
$dzz_upgrade = new dzz_upgrade();
$step = intval($_GET['step']);
$op = $_GET['op'];
$step = $step ? $step : 1;
$operation = $_GET['operation'] ? trim($_GET['operation']) : 'check';

$steplang = array('', lang('founder_upgrade_updatelist'), lang('founder_upgrade_download'), lang('founder_upgrade_compare'), lang('founder_upgrade_upgrading'), lang('founder_upgrade_complete'), 'dbupdate' => lang('founder_upgrade_dbupdate'));

if ($operation == 'patch' || $operation == 'cross') {

    if (!$_G['setting']['bbclosed']) {
        $msg = '<p style="margin:10px 0;color:red">' . lang('upgrade_close_site') . '</p>';
        $msg .= '<p style="margin:10px 0"><input type="button" class="btn btn-primary" onclick="window.location.reload();" value="' . lang('founder_upgrade_reset') . '" /></p>';

        $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
        $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" >" . lang('message_return') . "</a>');";
        $msg .= "</script></p>";
        if (!$_GET['iframe']) {
            include template('upgrade');
            exit();
        } else {
            include template('upgrade_iframe');
            exit();
        }
    }

    $msg = '';
    $version = trim($_GET['version']);
    //$release = trim($_GET['release']);
    $locale = trim($_GET['locale']);
    $charset = trim($_GET['charset']);
    $upgradeinfo = $upgrade_step = array();

    if ($_GET['ungetfrom']) {
        if (md5($_GET['ungetfrom'] . $_G['config']['security']['authkey']) == $_GET['ungetfrommd5']) {
            $dbreturnurl = $_G['siteurl'] . ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=' . $operation . '&version=' . $version . '&step=5'; 
            $url = outputurl(  $_G['siteurl'] . 'install/update.php?step=prepare&from=' . rawurlencode($dbreturnurl) . '&frommd5=' . rawurlencode(md5($dbreturnurl . $_G['config']['security']['authkey'])) );
            dheader('Location: ' . $url);
        } else {
            showmessage('upgrade_param_error');
        }
    }

    $upgrade_step = C::t('cache') -> fetch('upgrade_step');
    $upgrade_step = dunserialize($upgrade_step['cachevalue']);
    $upgrade_step['step'] = $step;
    $upgrade_step['operation'] = $operation;
    $upgrade_step['version'] = $version;
    //$upgrade_step['release'] = $release;
    $upgrade_step['charset'] = $charset;
    $upgrade_step['locale'] = $locale;
    C::t('cache') -> insert(array('cachekey' => 'upgrade_step', 'cachevalue' => serialize($upgrade_step), 'dateline' => $_G['timestamp'], ), false, true);

    $upgrade_run = C::t('cache') -> fetch('upgrade_run');
    if (!$upgrade_run) {
        C::t('cache') -> insert(array('cachekey' => 'upgrade_run', 'cachevalue' => serialize($_G['setting']['upgrade']), 'dateline' => $_G['timestamp'], ), false, true);
        $upgrade_run = $_G['setting']['upgrade'];
    } else {
        $upgrade_run = dunserialize($upgrade_run['cachevalue']);
    }

    if ($step != 5) {

        foreach ($upgrade_run as $type => $list) {
            if ($type == $operation && $version == $list['latestversion']) {
                $dzz_upgrade -> locale = $locale;
                $dzz_upgrade -> charset = $charset;
                $upgradeinfo = $list;
                break;
            }
        }
        if (!$upgradeinfo) {
            $msg = '<p style="margin:10px 0;color:red">' . lang('upgrade_none', array('upgradeurl' => upgradeinformation(-1))) . '</p>';
            $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
            $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" >" . lang('message_return') . "</a>');";
            $msg .= "</script></p>";
            if (!$_GET['iframe']) {
                include template('upgrade');
                exit();
            } else {
                include template('upgrade_iframe');
                exit();
            }
        }

        $updatefilelist = $dzz_upgrade -> fetch_updatefile_list($upgradeinfo);
        $updatemd5filelist = $updatefilelist['md5'];
        $updatefilelist = $updatefilelist['file'];
        $theurl = $_G['siteurl'].ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=' . $operation . '&version=' . $version . '&locale=' . $locale . '&charset=' . $charset;

        if (empty($updatefilelist)) {
            $msg = '<p style="margin:10px 0;color:red">' . lang('upgrade_download_upgradelist_error', array('upgradeurl' => upgradeinformation(-2))) . '</p>';
            $msg .= '<script type="text/JavaScript">setTimeout("location.href=\'' . ($thurl) . '\';", 1000);</script>';
            $msg .= ' <p style="margin:10px 0"><a href="' . $thurl . '">' . lang('message_redirect') . '</p>';
            if (!$_GET['iframe']) {
                include template('upgrade');
                exit();
            } else {
                include template('upgrade_iframe');
                exit();
            }
        }

    }

    if ($step == 1) {
        $linkurl = $theurl . '&step=2';
        include template('upgrade');
        exit();
    } elseif ($step == 2) {
        $fileseq = intval($_GET['fileseq']);
        $fileseq = $fileseq ? $fileseq : 1;
        if ($fileseq > count($updatefilelist)) {
            if ($upgradeinfo['isupdatedb']) {
                $dzz_upgrade -> download_file($upgradeinfo, 'install/data/install.sql');
                $dzz_upgrade -> download_file($upgradeinfo, 'install/data/install_data.sql');
                $dzz_upgrade -> download_file($upgradeinfo, 'update.php', 'update');
            }
            $linkurl = $theurl . '&step=3';
            $downloadstatus = 3;
            $msg = lang('upgrade_download_complete_to_compare', array('upgradeurl' => upgradeinformation(0)));
            if (!$_GET['iframe']) {
                $msg .= '<script type="text/JavaScript">setTimeout("location.href=\'' . $linkurl . '\';", 1000);</script>';
                $msg .= ' <p><a href="' . $linkurl . '">' . lang('message_redirect') . '</a></p>';
            } else {
                $msg .= '<script type="text/JavaScript">setTimeout("parent.location.href=\'' . $linkurl . '\';", 1000);</script>';
                $msg .= ' <p><a href="javascript:;" onclick="parent.location.href=\'' . $linkurl . '\';return false;">' . lang('message_redirect') . '</a></p>';
                include template('upgrade_iframe');
                exit();
            }
        } else {
            if (!$_GET['iframe']) {
                $linkurl = $theurl . '&step=2&fileseq=' . $fileseq . '&iframe=1';
                $msg = '<iframe id="downiframe" marginheight="0" marginwidth="0" allowtransparency="true" frameborder="0"  src="' . $linkurl . '" style="width:100%;height:100%;"></iframe>';
            } else {
                $downloadstatus = $dzz_upgrade -> download_file($upgradeinfo, $updatefilelist[$fileseq - 1], 'upload', $updatemd5filelist[$fileseq - 1]);
                if ($downloadstatus == 1) {
                    $linkurl = $theurl . '&step=2&fileseq=' . $fileseq . '&iframe=1';
                    $msg = lang('upgrade_downloading_file', array('file' => $updatefilelist[$fileseq - 1], 'percent' => sprintf("%2d", 100 * $fileseq / count($updatefilelist)) . '%', 'upgradeurl' => upgradeinformation(1))) . '<script type="text/JavaScript">setTimeout("location.href=\'' . $linkurl . '\';", 50);</script>';
                    $msg .= ' <p><a href="' . $linkurl . '">' . lang('message_redirect') . '</a></p>';

                } elseif ($downloadstatus == 2) {
                    $linkurl = $theurl . '&step=2&fileseq=' . ($fileseq + 1) . '&iframe=1';
                    $msg = '<p style="margin:10px 0">' . lang('upgrade_downloading_file', array('file' => $updatefilelist[$fileseq - 1], 'percent' => sprintf("%2d", 100 * $fileseq / count($updatefilelist)) . '%', 'upgradeurl' => upgradeinformation(1))) . '<script type="text/JavaScript">setTimeout("location.href=\'' . $linkurl . '\';", 50);</script></p>';
                    $msg .= ' <p><a href="' . $linkurl . '">' . lang('message_redirect') . '</a></p>';
                } else {
                    $linkurl = $theurl . '&step=2&fileseq=' . ($fileseq) . '&iframe=1';
                    $msg = '<p style="margin:10px 0">' . lang('upgrade_redownload', array('file' => $updatefilelist[$fileseq - 1], 'upgradeurl' => upgradeinformation(-3))) . '</p>';
                    $msg .= '<p style="margin:10px 0;"><input type="button" class="btn btn-success"  value="'.lang('founder_upgrade_reset').'" onclick="location.href=\'' . $linkurl . '\'" />';
                }
                include template('upgrade_iframe');
                exit();
            }
        }
    } elseif ($step == 3) { 
        list($modifylist, $showlist, $ignorelist,$newlist) = $dzz_upgrade -> compare_basefile($upgradeinfo, $updatefilelist,$updatemd5filelist);
        if (empty($modifylist) && empty($showlist) && empty($ignorelist) && empty($newlist)) {
            $msg = lang('filecheck_nofound_md5file', array('upgradeurl' => upgradeinformation(-4)));
        }
        $linkurl = $theurl . '&step=4';
    } elseif ($step == 4) {

        $confirm = $_GET['confirm'];
        if (!$confirm) {
            if ($_GET['siteftpsetting']) {
                $action = $theurl . '&step=4&confirm=ftp' . ($_GET['startupgrade'] ? '&startupgrade=1' : '');

                include template('upgrade');
                exit();
            }

            if ($upgradeinfo['isupdatedb']) {
                $checkupdatefilelist = array('install/update.php', 'install/data/install.sql', 'install/data/install_data.sql');
                $checkupdatefilelist = array_merge($checkupdatefilelist, $updatefilelist);
            } else {
                $checkupdatefilelist = $updatefilelist;
            }
            if ($dzz_upgrade -> check_folder_perm($checkupdatefilelist)) {
                $confirm = 'file';
            } else {
                $linkurl = $theurl . '&step=4';
                $ftplinkurl = $linkurl . '&siteftpsetting=1';
                $msg = '<p style="margin:10px 0">' . lang('upgrade_cannot_access_file') . '</p>';
                $msg .= '<p style="margin:10px 0"><input type="button" class="btn btn-primary" onclick="window.location.href=\'' . $ftplinkurl . '\'" value="' . lang('founder_upgrade_set_ftp') . '" />';
                $msg .= ' &nbsp; <input type="button" class="btn btn-default" onclick="window.location.href=\'' . $linkurl . '\'" value="' . lang('founder_upgrade_reset') . '" /></p>';
                $msg .= "<script type=\"text/javascript\">";
                $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" class=\"lightlink\">" . lang('message_return') . "</a>');";
                $msg .= "</script>";
                include template('upgrade');
                exit();
            }
        }

        $paraftp = '';
        if ($_GET['siteftp']) {
            foreach ($_GET['siteftp'] as $k => $v) {
                $paraftp .= '&siteftp[' . $k . ']=' . $v;
            }
        }
        if (!$_GET['startupgrade']) {
            if (!$_GET['backfile']) {
                $linkurl = $theurl . '&step=4&backfile=1&confirm=' . $confirm . $paraftp;
                $msg = '<p style="margin:10px 0">' . lang('upgrade_backuping', array('upgradeurl' => upgradeinformation(2))) . '</p>';
                $msg .= '<script type="text/JavaScript">setTimeout("location.href=\'' . ($linkurl) . '\';", 1000);</script>';
                $msg .= ' <p style="margin:10px 0"><a href="' . $linkurl . '">' . lang('message_redirect') . '</p>';
                include template('upgrade');
                exit();
            }
            foreach ($updatefilelist as $updatefile) {
                $destfile = DZZ_ROOT . $updatefile;
                $backfile = DZZ_ROOT . './data/back/dzzoffice' . CORE_VERSION . '/' . $updatefile;
                if (is_file($destfile)) {
                    if (!$dzz_upgrade -> copy_file($destfile, $backfile, 'file')) {
                        $msg = '<p style="margin:10px 0">' . lang('upgrade_backup_error', array('upgradeurl' => upgradeinformation(-5))) . '</p>';
                        $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
                        $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" >" . lang('message_return') . "</a>');";
                        $msg .= "</script></p>";
                        include template('upgrade');
                        exit();
                    }
                }
            }
            $msg = '<p style="margin:10px 0">' . lang('upgrade_backup_complete', array('upgradeurl' => upgradeinformation(3))) . '</p>';
            $msg .= '<script type="text/JavaScript">setTimeout("location.href=\'' . ($theurl . '&step=4&startupgrade=1&confirm=' . $confirm . $paraftp) . '\';", 1000);</script>';
            $msg .= ' <p><a href="' . ($theurl . '&step=4&startupgrade=1&confirm=' . $confirm . $paraftp) . '">' . lang('message_redirect') . '</p>';
            include template('upgrade');
            exit();
        }

        $linkurl = $theurl . '&step=4&startupgrade=1&confirm=' . $confirm . $paraftp;
        $ftplinkurl = $theurl . '&step=4&startupgrade=1&siteftpsetting=1';
        foreach ($updatefilelist as $updatefile) {
            $srcfile = DZZ_ROOT . './data/update/dzzoffice' . $version . '/' . $updatefile;
            if ($confirm == 'ftp') {
                $destfile = $updatefile;
            } else {
                $destfile = DZZ_ROOT . $updatefile;
            }
            if (!$dzz_upgrade -> copy_file($srcfile, $destfile, $confirm)) {
                if ($confirm == 'ftp') {
                    $msg = '<p style="margin:10px 0">' . lang('upgrade_ftp_upload_error', array('file' => $updatefile, 'upgradeurl' => upgradeinformation(-6))) . '</p>';
                    $msg .= '<p style="margin:10px 0"><input type="button" class="btn btn-primary" onclick="window.location.href=\'' . $linkurl . '\'" value="' . lang('founder_upgrade_reupload') . '" />';
                    $msg .= '&nbsp;<input type="button" class="btn btn-default" onclick="window.location.href=\'' . $ftplinkurl . '\'" value="' . lang('founder_upgrade_reset_ftp') . '" /></p>';
                    $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
                    $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" class=\"lightlink\">" . lang('message_return') . "</a>');";
                    $msg .= "</script></p>";
                    include template('upgrade');
                    exit();

                } else {
                    $msg = '<p style="margin:10px 0">' . lang('upgrade_copy_error', array('file' => $updatefile, 'upgradeurl' => upgradeinformation(-7))) . '</p>';
                    $msg .= '<p style="margin:10px 0"><input type="button" class="btn btn-primary" onclick="window.location.href=\'' . $linkurl . '\'" value="' . lang('founder_upgrade_recopy') . '" />';
                    $msg .= '&nbsp;<input type="button" class="btn btn-default" onclick="window.location.href=\'' . $ftplinkurl . '\'" value="' . lang('founder_upgrade_set_ftp') . '" /></p>';
                    $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
                    $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" class=\"lightlink\">" . lang('message_return') . "</a>');";
                    $msg .= "</script></p>";
                    include template('upgrade');
                    exit();

                }
            }
        }
        if ($upgradeinfo['isupdatedb']) {
            $dbupdatefilearr = array('update.php', 'install/data/install.sql', 'install/data/install_data.sql');
            foreach ($dbupdatefilearr as $dbupdatefile) {
                $srcfile = DZZ_ROOT . './data/update/dzzoffice' . $version . '/' . $dbupdatefile;
                $dbupdatefile = $dbupdatefile == 'update.php' ? 'install/update.php' : $dbupdatefile;
                if ($confirm == 'ftp') {
                    $destfile = $dbupdatefile;
                } else {
                    $destfile = DZZ_ROOT . $dbupdatefile;
                }
                if (!$dzz_upgrade -> copy_file($srcfile, $destfile, $confirm)) {
                    if ($confirm == 'ftp') {
                        $msg = '<p style="margin:10px 0">' . lang('upgrade_ftp_upload_error', array('file' => $updatefile, 'upgradeurl' => upgradeinformation(-6))) . '</p>';
                        $msg .= '<p style="margin:10px 0"><input type="button" class="btn btn-primary" onclick="window.location.href=\'' . $linkurl . '\'" value="' . lang('founder_upgrade_reupload') . '" />';
                        $msg .= '&nbsp;<input type="button" class="btn btn-default" onclick="window.location.href=\'' . $ftplinkurl . '\'" value="' . lang('founder_upgrade_reset_ftp') . '" /></p>';
                        $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
                        $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" class=\"lightlink\">" . lang('message_return') . "</a>');";
                        $msg .= "</script></p>";
                        include template('upgrade');
                        exit();

                    } else {
                        $msg = '<p style="margin:10px 0">' . lang('upgrade_copy_error', array('file' => $updatefile, 'upgradeurl' => upgradeinformation(-7))) . '</p>';
                        $msg .= '<p style="margin:10px 0"><input type="button" class="btn btn-primary" onclick="window.location.href=\'' . $linkurl . '\'" value="' . lang('founder_upgrade_recopy') . '" />';
                        $msg .= '&nbsp;<input type="button" class="btn btn-default" onclick="window.location.href=\'' . $ftplinkurl . '\'" value="' . lang('founder_upgrade_set_ftp') . '" /></p>';
                        $msg .= "<p style=\"margin:10px 0\"><script type=\"text/javascript\">";
                        $msg .= "if(history.length > (BROWSER.ie ? 0 : 1)) document.write('<a href=\"javascript:history.go(-1);\" class=\"lightlink\">" . lang('message_return') . "</a>');";
                        $msg .= "</script></p>";
                        include template('upgrade');
                        exit();

                    }
                }
            }
            $upgrade_step['step'] = 'dbupdate';
            C::t('cache') -> insert(array('cachekey' => 'upgrade_step', 'cachevalue' => serialize($upgrade_step), 'dateline' => $_G['timestamp'], ), false, true);
            $dbreturnurl = $_G['siteurl'] . ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=' . $operation . '&version=' . $version . '&step=5';
            $linkurl = $_G['siteurl'] . 'install/update.php?step=prepare&from=' . rawurlencode($dbreturnurl) . '&frommd5=' . rawurlencode(md5($dbreturnurl . $_G['config']['security']['authkey']));
            $msg = '<p style="margin:10px 0">' . lang('upgrade_file_successful', array('upgradeurl' => upgradeinformation(4))) . '</p>';
            $msg .= '<script type="text/JavaScript">setTimeout(function(){createIframe(\'' . $linkurl . '\');}, 1000);</script>';
            $msg .= ' <p><a href="javascript:;" onclick="createIframe(\'' . $linkurl . '\');return false">' . lang('message_redirect') . '</p>';
            include template('upgrade');
            exit();

        }
        
        $url = outputurl( $_G['siteurl'].MOD_URL.'&op=systemupgrade&operation=' . $operation . '&version=' . $version . '&step=5' );
        dheader('Location: ' . $url);

    } elseif ($step == 5) {
        $file = DZZ_ROOT . './data/update/dzzoffice' . $version . '/updatelist.tmp';
        @unlink($file);
        @unlink(DZZ_ROOT . './install/update.php');
        C::t('cache') -> delete('upgrade_step');
        C::t('cache') -> delete('upgrade_run');
        C::t('setting') -> update('upgrade', '');
        updatecache('setting');
        $old_update_dir = './data/update/';
        $new_update_dir = './data/update' . md5('update' . $_G['config']['security']['authkey']) . '/';
        $old_back_dir = './data/back/';
        $new_back_dir = './data/back' . md5('back' . $_G['config']['security']['authkey']) . '/';
        $dzz_upgrade -> copy_dir(DZZ_ROOT . $old_update_dir, DZZ_ROOT . $new_update_dir);
        $dzz_upgrade -> copy_dir(DZZ_ROOT . $old_back_dir, DZZ_ROOT . $new_back_dir);
        $dzz_upgrade -> rmdirs(DZZ_ROOT . $old_update_dir);
        $dzz_upgrade -> rmdirs(DZZ_ROOT . $old_back_dir);

        $msg = lang('upgrade_successful', array('version' => $version, 'save_update_dir' => $new_update_dir, 'save_back_dir' => $new_back_dir, 'upgradeurl' => upgradeinformation(0)));

    }

}
elseif ($operation == 'check') {
    $msg = '';
    if (!intval($_GET['rechecking'])) {
        $upgrade_step = C::t('cache') -> fetch('upgrade_step');
        if (!empty($upgrade_step['cachevalue'])) {
            $upgrade_step['cachevalue'] = dunserialize($upgrade_step['cachevalue']);
            if (!empty($upgrade_step['cachevalue']['step'])) {
                $theurl = ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=' . $upgrade_step['cachevalue']['operation'] . '&version=' . $upgrade_step['cachevalue']['version'] . '&locale=' . $upgrade_step['cachevalue']['locale'] . '&charset=' . $upgrade_step['cachevalue']['charset'];

                $recheckurl = ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=recheck';
                if ($upgrade_step['cachevalue']['step'] == 'dbupdate') {
                    $dbreturnurl = $_G['siteurl'] . $theurl . '&step=5';
                    $stepurl = $_G['siteurl'] . 'install/update.php?step=prepare&from=' . rawurlencode($dbreturnurl) . '&frommd5=' . rawurlencode(md5($dbreturnurl . $_G['config']['security']['authkey']));
                    $msg = '<p style="margin:10px 0;">' . lang('upgrade_continue_db', array('steplang' => $steplang['dbupdate'], 'stepurl' => $stepurl, 'recheckurl' => $recheckurl)) . '</p>';

                } else {
                    $stepurl = $theurl . '&step=' . $upgrade_step['cachevalue']['step'];
                    $msg = '<p style="margin:10px 0;">' . lang('upgrade_continue', array('steplang' => $steplang[$upgrade_step['cachevalue']['step']], 'stepurl' => $stepurl, 'recheckurl' => $recheckurl)) . '</p>';

                }
            }
        }
    } else {
        C::t('cache') -> delete('upgrade_step');
    }

    if (!intval($_GET['checking']) || $msg) {


    } else {
        $dzz_upgrade -> check_upgrade();
        $url = outputurl( $_G['siteurl'].MOD_URL.'&op=systemupgrade&operation=showupgrade' );
        dheader('Location: ' . $url);
    }

}
elseif ($operation == 'showupgrade') {

    if ($_G['setting']['upgrade']) {

        C::t('cache') -> insert(array('cachekey' => 'upgrade_step', 'cachevalue' => serialize(array('curversion' => $dzz_upgrade -> versionpath())), 'dateline' => $_G['timestamp'], ), false, true);

        $upgraderow = $patchrow = array();
        $charset = str_replace('-', '', strtoupper($_G['config']['output']['charset']));
        $dbversion = helper_dbtool::dbversion();
        $locale = '';

        if ($charset == 'BIG5') {
            $locale = 'TC';
        } elseif ($charset == 'GBK') {
            $locale = 'SC';
        } elseif ($charset == 'UTF8') {
            if ($_G['config']['output']['language'] == 'zh-cn' || $_G['config']['output']['language'] == 'zh_cn') {
                $locale = 'SC';
            } elseif ($_G['config']['output']['language'] == 'zh-tw' || $_G['config']['output']['language'] == 'zh_tw') {
                $locale = 'TC';
            }else{
                $locale = 'SC';
            }
        }

        if (!is_array($_G['setting']['upgrade']))
            $_G['setting']['upgrade'] = unserialize($_G['setting']['upgrade']);
        $list = array();
        foreach ($_G['setting']['upgrade'] as $type => $upgrade) {
            $unupgrade = 0;
            if (version_compare($upgrade['phpversion'], PHP_VERSION) > 0 || version_compare($upgrade['mysqlversion'], $dbversion) > 0) {
                $unupgrade = 1;
            }
            $list[$type]['linkurl'] = $linkurl = ADMINSCRIPT . '?mod=system&op=systemupgrade&operation=' . $type . '&version=' . $upgrade['latestversion'] . '&locale=' . $locale . '&charset=' . $charset;
            if ($unupgrade) {
                $list[$type]['title'] = 'DzzOffice' . $upgrade['latestversion'] . '_' . $locale . '_' . $charset;
                $list[$type]['btn1'] = lang('founder_upgrade_require_config') . ' php v' . PHP_VERSION . 'MYSQL v' . $dbversion;
            } else {
                $list[$type]['title'] = 'DzzOffice' . $upgrade['latestversion'] . '_' . $locale . '_' . $charset;
                $list[$type]['btn1'] = '<input type="button" class="btn btn-success" onclick="confirm(\'' . lang('founder_upgrade_backup_remind') . '\') ? window.location.href=\'' . $linkurl . '\' : \'\';" value="' . lang('founder_upgrade_automatically') . '">';
                $list[$type]['official'] = '<a class="btn btn-link" href="' . $upgrade['official'] . '" target="_blank">' . lang('founder_upgrade_manually') . '</a>';
            }
        }
    } else {

        $msg = lang('upgrade_latest_version');
    }

}
elseif ($operation == 'recheck') {
    $upgrade_step = C::t('cache') -> fetch('upgrade_step');
    $upgrade_step = dunserialize($upgrade_step['cachevalue']);
    $file = DZZ_ROOT . './data/update/DzzOffice' . $upgrade_step['version'] . '/updatelist.tmp';
    @unlink($file);
    @unlink(DZZ_ROOT . './install/update.php');
    C::t('cache') -> delete('upgrade_step');
    C::t('cache') -> delete('upgrade_run');
    C::t('setting') -> update('upgrade', '');
    updatecache('setting');
    $old_update_dir = './data/update/';
    $dzz_upgrade -> rmdirs(DZZ_ROOT . $old_update_dir);
    
    $url = outputurl($_G['siteurl'].MOD_URL.'&op=systemupgrade' );
    dheader('Location: ' . $url);
}
include template('upgrade');
?>