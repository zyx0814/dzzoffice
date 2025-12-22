<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

error_reporting(E_ERROR);
@set_time_limit(1000);

define('IN_DZZ', TRUE);
define('IN_LEYUN', TRUE);
define('ROOT_PATH', dirname(__DIR__).'/');

if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    exit('您的 PHP 版本过低 (' . PHP_VERSION . ')，请升级到 PHP 7.0 或更高版本。');
}

require ROOT_PATH . './core/core_version.php';
require ROOT_PATH . './install/include/install_var.php';
require ROOT_PATH . './install/include/install_mysqli.php';
require ROOT_PATH . './install/include/install_function.php';
require ROOT_PATH . './install/language/zh-cn/lang.php';

$view_off = getgpc('view_off');
define('VIEW_OFF', $view_off ? TRUE : FALSE);

$allow_method = array('show_license', 'env_check', 'db_init', 'ext_info', 'install_check', 'tablepre_check', 'phpinfo');
$step = intval(getgpc('step', 'R')) ? intval(getgpc('step', 'R')) : 0;
$method = getgpc('method');

if (empty($method) || !in_array($method, $allow_method)) {
    $method = isset($allow_method[$step]) ? $allow_method[$step] : '';
}

if (empty($method)) {
    show_msg('method_undefined', $method, 0);
}

if (file_exists($lockfile) && $method != 'ext_info') {
    show_msg('install_locked', '', 0);
} elseif (!class_exists('dbstuff')) {
    show_msg('database_nonexistence', '', 0);
}

// 设置时区
if (function_exists('date_default_timezone_set')) {
    @date_default_timezone_set('Etc/GMT-8');
}

if ($method == 'show_license') {
    show_license();
} elseif ($method == 'phpinfo') {
    exit(phpinfo());
} elseif ($method == 'env_check') {
    VIEW_OFF && function_check($func_items);
    env_check($env_items);
    dirfile_check($dirfile_items);
    show_env_result($env_items, $dirfile_items, $func_items, $filesock_items);
} elseif ($method == 'db_init') {
    $submit = true;
    $default_config = $_config = array();
    $default_configfile = './config/config_default.php';

    if (!file_exists(ROOT_PATH . $default_configfile)) {
        exit('config_default.php 丢失，请重新上传。');
    } else {
        include ROOT_PATH . $default_configfile;
        $default_config = $_config;
    }
    $_config = $default_config;

    $company = SOFT_NAME;
    // 支持环境变量注入 (Docker友好)
    $dbhost = getenv('MYSQL_HOST') ?: $_config['db'][1]['dbhost'];
    $dbname = getenv('MYSQL_DATABASE') ?: $_config['db'][1]['dbname'];
    $dbpw = getenv('MYSQL_PASSWORD') ?: $_config['db'][1]['dbpw'];
    $dbuser = getenv('MYSQL_USER') ?: $_config['db'][1]['dbuser'];
    $tablepre = $_config['db'][1]['tablepre'];
    $adminemail = 'admin@dzzoffice.com';

    $error_msg = array();
    if (isset($form_db_init_items) && is_array($form_db_init_items)) {
        foreach ($form_db_init_items as $key => $items) {
            $$key = getgpc($key, 'p');
            if (!isset($$key) || !is_array($$key)) {
                $submit = false;
                break;
            }
            foreach ($items as $k => $v) {
                $tmp = $$key;
                $$k = $tmp[$k];
                if (empty($$k) || !preg_match($v['reg'], $$k)) {
                    if (empty($$k) && !$v['required']) continue;
                    $submit = false;
                    VIEW_OFF or $error_msg[$key][$k] = 1;
                }
            }
        }
    } else {
        $submit = false;
    }
    if ($submit && !VIEW_OFF && $_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($password != $password2) {
            $error_msg['admininfo']['password2'] = 1;
            $submit = false;
        }
        $forceinstall = isset($_POST['dbinfo']['forceinstall']) ? $_POST['dbinfo']['forceinstall'] : '';
        if (!empty($dbhost) && empty($forceinstall)) {
            if (!check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre)) {
                $form_db_init_items['dbinfo']['forceinstall'] = array('type' => 'checkbox', 'required' => 0, 'reg' => '/^.*+/');
                $error_msg['dbinfo']['forceinstall'] = 1;
                $submit = false;
            }
        }
    }

    if ($submit) {
        $step = $step + 1;
        if (empty($dbname)) {
            show_msg('dbname_invalid', $dbname, 0);
        } else {
            $unix_socket = null;
            //兼容支持域名直接带有端口的情况
            if (strpos($dbhost, '.sock') !== false) {//地址直接是socket地址
                $unix_socket = $dbhost;
                $dbhost = 'localhost';
            }
            $link = new mysqli($dbhost, $dbuser, $dbpw, '', null, $unix_socket);
            if($link->connect_errno) {
                $errno = $link->connect_errno;
                $error = $link->connect_error;
                if ($errno) {
                    if ($errno == 1045) {
                        show_msg('database_errno_1045', $error, 0);
                    } elseif ($errno == 2003 || $errno == 2002) {
                        show_msg('database_errno_2003', $error, 0);
                    } else {
                        show_msg('database_connect_error', $error, 0);
                    }
                }
            }
            $link->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET " . DBCHARSET);
            if ($link->errno) {
                show_msg('database_errno_1044', $link->error, 0);
            }
            $link->close();
        }

        if (strpos($tablepre, '.') !== false || intval($tablepre[0])) {
            show_msg('tablepre_invalid', $tablepre, 0);
        }
        if ($username && $email && $password) {
            if (strlen($username) > 30 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^Guest/is", $username)) {
                show_msg('admin_username_invalid', $username, 0);
            } elseif (!strstr($email, '@') || $email != stripslashes($email) || $email != dhtmlspecialchars($email)) {
                show_msg('admin_email_invalid', $email, 0);
            }
        } else {
            show_msg('admininfo_invalid', '', 0);
        }
        $uid = 1;
        $authkey = md5((isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '') . $_SERVER['HTTP_USER_AGENT'] . $dbhost . $dbuser . $dbpw . $dbname .$username.$password . substr(time(), 0, 6)) . random(10);
        $_config['db']['driver'] = 'mysqli';
        $_config['db'][1]['dbhost'] = $dbhost;
        $_config['db'][1]['dbname'] = $dbname;
        $_config['db'][1]['dbpw'] = $dbpw;
        $_config['db'][1]['dbuser'] = $dbuser;
        $_config['db'][1]['tablepre'] = $tablepre;
        $_config['admincp']['founder'] = (string)$uid;
        $_config['security']['authkey'] = $authkey;
        $_config['cookie']['cookiepre'] = random(4) . '_';
        $_config['memory']['prefix'] = random(6) . '_';
        $_config['memory']['redis']['server'] = getenv('REDIS_HOST') ?: '';
        $_config['memory']['redis']['port'] = getenv('REDIS_PORT') ?: 6379;
        $_config['memory']['redis']['requirepass'] = getenv('REDIS_PASSWORD') ?: '';

        save_config_file(ROOT_PATH . CONFIG, $_config, $default_config);
        $runqueryerror = 0;
        if (!VIEW_OFF) {
            show_header();
            show_install();
        }

        @set_time_limit(0);
        @ignore_user_abort(TRUE);
        ini_set('max_execution_time', 0);
        ini_set('mysql.connect_timeout', 0);

        $db = new dbstuff;

        $db->connect($dbhost, $dbuser, $dbpw, $dbname, DBCHARSET);
        showjsmessage(lang('begin_establish_data_tables'));
        $sql = file_get_contents($sqlfile);
        $sql = str_replace("\r\n", "\n", $sql);
        if (!runquery($sql)) {
            exit();
        }
        showjsmessage(lang('table_clear_success'));
        showjsmessage(lang('start_importing_initialized_data'));
        $sql = file_get_contents(ROOT_PATH . './install/data/install_data.sql');
        $sql = str_replace("\r\n", "\n", $sql);
        if (!runquery($sql)) {
            exit();
        }
        showjsmessage(lang('start_importing_initialized_data1'));
        showjsmessage(lang('set_system'));
        $onlineip = $_SERVER['REMOTE_ADDR'];
        $timestamp = time();

        $backupdir = substr(md5((isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '') . $_SERVER['HTTP_USER_AGENT'] . substr($timestamp, 0, 4)), 8, 6);
	
        $ret = false;
        if (is_dir(ROOT_PATH . 'data/backup')) {
            $ret = @rename(ROOT_PATH . 'data/backup', ROOT_PATH . 'data/backup_' . $backupdir);
        }
        if (!$ret) {
            @mkdir(ROOT_PATH . 'data/backup_' . $backupdir, 0777);
        }
        if (is_dir(ROOT_PATH . 'data/backup_' . $backupdir)) {
            $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('backupdir', '$backupdir')");
        }
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $siteuniqueid = 'DZZOFFICE' . $chars[date('y') % 60] . $chars[date('n')] . $chars[date('j')] . $chars[date('G')] . $chars[date('i')] . $chars[date('s')] . substr(md5($onlineip . $timestamp), 0, 4) . random(4);
        $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('authkey', '$authkey')");
        $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('siteuniqueid', '$siteuniqueid')");
        $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('adminemail', '$adminemail')");
        $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('backupdir', '" . $backupdir . "')");
        $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('verhash', '" . random(3) . "')");
        //创建默认机构
        if ($company) {
            $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('sitename', '" . $company . "')");
            $db->query("REPLACE INTO {$tablepre}setting (skey, svalue) VALUES ('bbname', '" . $company . "')");
            //插入默认机构
            $db->query("INSERT INTO {$tablepre}organization (`orgid`,`orgname`, `forgid`, `fid`, `disp`, `dateline`, `usesize`, `maxspacesize`, `indesk`,`available`,`pathkey`,`syatemon`,`manageon`,`diron`) VALUES( 1, '$company', 0, 1, 0, '$timestamp', 0, 0, 0,1,'_1_',1,1,1)");
            //插入默认机构文件夹
            $db->query("INSERT INTO {$tablepre}folder (`fid`,`pfid`, `uid`, `username`, `innav`, `fname`, `perm`, `perm_inherit`, `fsperm`,`disp`,`iconview`,`display`,`dateline`,`gid`,`flag`,`default`,`isdelete`,`deldateline`) VALUES( 1, 0, 0, '', 1, '$company',7,7,0,0,4,0,'$timestamp', 1, 'organization','',0,0)");
            //插入默认机构path路径
            $db->query("INSERT INTO {$tablepre}resources_path (`fid`,`path`, `pathkey`) VALUES( 1, 'dzz:gid_1:$company/','_1_')");
            //将管理员加入默认机构
            $db->query("INSERT INTO {$tablepre}organization_user (`orgid`, `uid`,`jobid`, `dateline`) VALUES(1, 1, 0, '$timestamp')");

        }
        $db->query("UPDATE {$tablepre}cron SET lastrun='0', nextrun='" . ($timestamp + 3600) . "'");
        $salt = random(6);
        $password = md5(md5($password) . $salt);
        $db->query("REPLACE INTO {$tablepre}user (uid, username,nickname, password, adminid, groupid, email, regdate,salt,authstr) VALUES ('$uid', '$username', '','$password', '1', '1', '$email', '" . time() . "','$salt','');");
        $db->query("update {$tablepre}folder set `uid`=$uid,`username`='$username' where `fid` = 1");
        $db->query("REPLACE INTO {$tablepre}user_status (uid, regip,lastip, lastvisit, lastactivity, lastsendmail, invisible, profileprogress) VALUES ('$uid', '', '','$timestamp', '$timestamp', '0', '0', '0');");
        $query = $db->query("SELECT COUNT(*) FROM {$tablepre}user");
        $totalmembers = $db->result($query, 0);
        $userstats = array('totalmembers' => $totalmembers, 'newsetuser' => $username);
        $ctype = 1;
        $data = addslashes(serialize($userstats));
        $db->query("REPLACE INTO {$tablepre}syscache (cname, ctype, dateline, data) VALUES ('userstats', '$ctype', '" . time() . "', '$data')");
        showjsmessage(lang('set_system1'));
        showjsmessage(lang('import_division_data'));
        install_districtdata();
        showjsmessage(lang('import_division_data1'));

        $yearmonth = date('Ym_', time());
        loginit($yearmonth . 'loginlog');
        loginit($yearmonth . 'cplog');
        loginit($yearmonth . 'errorlog');

        dir_clear(ROOT_PATH . './data/template');
        dir_clear(ROOT_PATH . './data/cache');

        if ($runqueryerror) {
            showjsmessage('<span class="red">' . lang('error_quit_msg') . '</span>');
            show_footer();
            exit();
        }

        showjsmessage(lang('system_data_installation_successful'));
        show_footer();
    }
    show_form($form_db_init_items, $error_msg);
} elseif ($method == 'ext_info') {
    $version = CORE_VERSION;
    $sitename = SOFT_NAME;
    $enter_desktop = lang('enter_desktop');
    $handwork_del = lang('handwork_del');

    @touch($lockfile);
    @unlink(ROOT_PATH . './install/index.php');
    @unlink(ROOT_PATH . './install/update.php');
    show_header();
    echo '<iframe src="../misc.php?mod=syscache" style="display:none;"></iframe>';
    echo <<<EOT
<div class="finish-card">
    <div class="success-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    </div>
    
    <h2 class="finish-title">恭喜，安装完成！</h2>
    <p class="finish-subtitle">$sitename V$version 已成功部署在您的服务器上</p>
    
    <div class="finish-actions">
        <a href="/index.php?mod=appmanagement" class="btn btn-secondary">进入管理后台</a>
        <a href="/" class="btn btn-primary">$enter_desktop</a>
    </div>

    <div class="security-tip">
    <p style="text-align: left;">请使用管理员账号登录管理后台、并且按照下面的步骤依次配置系统！</p><ol style="text-align: left;font-size: 14px;"><li>系统默认仅预装了少量的应用，更多应用需要到 应用市场 内选择安装；</li><li>进入 系统设置 设置默认首页，平台名称、logo等系统基本设置；</li><li>如果系统只是在内网环境使用，可以在 系统设置 中关闭升级提醒，以免影响用户体验和页面性能。</li><li class="red">$handwork_del/install/index.php"</li></ol>
    </div>
</div>
EOT;
    show_footer();
} elseif ($method == 'install_check') {
    if (file_exists($lockfile)) {
        show_msg('installstate_succ');
    } else {
        show_msg('lock_file_not_touch', $lockfile, 0);
    }
} elseif ($method == 'tablepre_check') {
    $dbinfo = getgpc('dbinfo');
    extract($dbinfo);
    if (check_db($dbhost, $dbuser, $dbpw, $dbname, $tablepre)) {
        show_msg('tablepre_not_exists', 0);
    } else {
        show_msg('tablepre_exists', $tablepre, 0);
    }
}