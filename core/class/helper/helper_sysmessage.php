<?php

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class helper_sysmessage {

	public static function show($message, $title = '', $msgvar = array()) {
		if(function_exists('lang')) {
			$message = lang($message, $msgvar);
			$title = $title ? lang($title) : lang('System_Message');
		} else {
			$title = $title ? $title : 'System Message';
		}
		$charset = CHARSET;
		echo <<<EOT
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$title</title>
<meta name="keywords" content="" />
<meta name="description" content="System Message - DzzOffice" />
<meta name="generator" content="Dzz! " />
<meta name="author" content="Dzzfox Dzz Inc." />
<meta name="copyright" content="2010-2018 DzzOffice Inc." />
<meta name="MSSmartTagsPreventParsing" content="True" />
<meta http-equiv="MSThemeCompatible" content="Yes" />
</head>
<body bgcolor="#FFFFFF">
<table cellpadding="0" cellspacing="0" border="0" width="850" align="center" height="85%">
<tr align="center" valign="middle">
	<td>
	<table cellpadding="20" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 12px">
	<tr>
	<td valign="middle" align="center" bgcolor="#EBEBEB">
		<b style="font-size: 16px">$title</b>
		<br /><br /><p style="text-align:left;">$message</p>
		<br /><br />
	</td>
	</tr>
	</table>
	</td>
</tr>
</table>
</body>
</html>
EOT;
		die();
	}

}

?>