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
if (!function_exists('ajaxshowheader')) {
    function ajaxshowheader() {
        global $_G;
        ob_end_clean();
        @header("Expires: -1");
        @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");
        header("Content-type: application/xml");
        echo "<?xml version=\"1.0\" encoding=\"" . CHARSET . "\"?>\n<root><![CDATA[";
    }

}

if (!function_exists('ajaxshowfooter')) {
    function ajaxshowfooter() {
        echo ']]></root>';
        exit();
    }

}
if ($this->core->var['inajax']) {
    ajaxshowheader();
    ajaxshowfooter();
}
$lang = lang();
if($this->cpaccess == -2 || $this->cpaccess == -3) {
    html_login_header(false, $lang);
} else {
    html_login_header(true, $lang);
}
if($this->cpaccess == -2 || $this->cpaccess == -3) {
    echo '<div class="alert alert-danger" role="alert">' . lang('login_cp_noaccess') . '</div>';

} elseif ($this->cpaccess == -1) {
    $ltime = $this->sessionlife - (TIMESTAMP - $this->adminsession['dateline']);
    echo '<div class="alert alert-danger" role="alert">' . lang('login_cplock', array('ltime' => $ltime)) . '</div>';

} elseif ($this->cpaccess == -4) {
    $ltime = $this->sessionlife - (TIMESTAMP - $this->adminsession['dateline']);
    echo '<div class="alert alert-danger" role="alert">' . lang('login_user_lock') . '</div>';

} else {

    html_login_form($lang);
}

html_login_footer();

function html_login_header($form = true, $lang = array()) {
    global $_G;
    $charset = CHARSET;
    $title = $lang['login_title'];

    echo <<<EOT
<!DOCTYPE>
<html>
<head>
<title>$title</title>
<base href="{$_G['siteurl']}">
<meta http-equiv="Content-Type" content="text/html;charset=$charset" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<link rel="stylesheet" href="static/lyear/css/bootstrap.min.css" type="text/css" media="all" />
<link rel="stylesheet" href="static/lyear/css/style.min.css" type="text/css" media="all" />
<script type="text/javascript" src="static/js/md5.js"></script> 
<script type="text/javascript" src="static/lyear/js/jquery.min.js"></script>
<meta content="DzzOffice.com" name="Copyright" />
</head>
<body class="center-vh overflow-y-auto">
EOT;
    if ($form) {
        $loginset_img = $_G['setting']['loginset']['img'] ? $_G['setting']['loginset']['img'] : 'user/login/images/login.jpg';
        $loginset_bcolor = $_G['setting']['loginset']['bcolor'] ? $_G['setting']['loginset']['bcolor'] : '#76838f';
        echo <<<EOT
<div id="wrapper_div" style="width: 100%;height:100%;  position: absolute; top: 0px; left: 0px; margin: 0px; padding: 0px; overflow: hidden;z-index:0;  font-size: 0px; background:$loginset_bcolor;"> 
	<img src="$loginset_img" name="imgbg" id="imgbg" style="right: 0px; bottom: 0px; top: 0px; left: 0px; z-index:1;margin:0;padding:0;overflow:hidden; position: absolute;width:100%;height:100%" height="100%" width="100%">
</div>
EOT;
    }
}

function html_login_footer($halt = true) {
    echo <<<EOT
</body>
</html>

EOT;
    $halt && exit();
}

function html_login_form($lang = array()) {
    global $_G;
    $year = dgmdate(TIMESTAMP, 'Y');
    $maintitle = $lang['title_admincp'];
    $loginuser = !$_G['uid'] ? '<div class="mb-3"><input class="form-control" name="admin_email" type="text" title="" autofocus placeholder="' . lang('login_email_username') . '"  required/></div>' : '<h5 class="text-center">' . $_G['member']['username'] . '</h5><p class="text-center text-muted">' . $_G['member']['email'] . '</p>';
    $sid = $_G['sid'];
    $avastar = '';
    if (!$_G['uid']) {
        if ($_G['setting']['bbclosed']) {
            $sitelogo = 'static/image/common/logo.png';
        } else {
            $sitelogo = $_G['setting']['sitelogo'] ? 'index.php?mod=io&op=thumbnail&size=small&path=' . dzzencode('attach::' . $_G['setting']['sitelogo']) : 'static/image/common/logo.png';
        }
        $avastar = '<img src="' . $sitelogo . '">';
    } else {
        $avastar = avatar_block($_G['uid']);
    }
    $extra = BASESCRIPT . '?' . $_SERVER['QUERY_STRING'];
    echo <<<EOT
<div class="card card-shadowed p-5 mb-0 mr-2 ml-2" style="width: 380px;">
<form method="post" name="login" id="loginform" class="signin-form loginForm" action="$extra" onsubmit="pwmd5('admin_password')">
	<input type="hidden" name="sid" value="$sid">
		<div class="card-body text-center">
			<div class="text-center mb-3 img-avatar-128 w-100">$avastar</div>
			<h2 class="main-title">$maintitle</h2>
		</div>
		$loginuser
		<div class="mb-3">
			<input type="password" class="form-control" id="admin_password" autofocus placeholder="$lang[password]" name="admin_password" value="" required>
		</div>
		<div class="mb-3 d-grid">
		<input name="submit" value="$lang[login]" type="submit" class="btn btn-primary bodyloading"  />
		</div>
</form>
<p class="text-center text-muted mb-0"><span>Powered By <a href="http://www.dzzoffice.com" target="_blank" class="dcolor">DzzOffice</a>&nbsp;&copy; 2012-$year</span></p>
</div>
EOT;
}

?>