<?php
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
global $_G;
if(is_string($this->config['security']['attackevasive'])) {
	$attackevasive_tmp = explode('|', $this->config['security']['attackevasive']);
	$attackevasive = 0;
	foreach($attackevasive_tmp AS $key => $value) {
		$attackevasive += intval($value);
	}
	unset($attackevasive_tmp);
} else {
	$attackevasive = $this->config['security']['attackevasive'];
}

$lastrequest = isset($_G['cookie']['lastrequest']) ? authcode($_G['cookie']['lastrequest'], 'DECODE') : '';

if($attackevasive & 1 || $attackevasive & 4) {
	dsetcookie('lastrequest', authcode(TIMESTAMP, 'ENCODE'), TIMESTAMP + 816400, 1, true);
}

if($attackevasive & 1) {
	if(TIMESTAMP - $lastrequest < 1) {
		securitymessage('attackevasive_1_subject', 'attackevasive_1_message');
	}
}

if(($attackevasive & 2) && ($_SERVER['HTTP_X_FORWARDED_FOR'] ||
	$_SERVER['HTTP_VIA'] || $_SERVER['HTTP_PROXY_CONNECTION'] ||
	$_SERVER['HTTP_USER_AGENT_VIA'] || $_SERVER['HTTP_CACHE_INFO'] ||
	$_SERVER['HTTP_PROXY_CONNECTION'])) {
		securitymessage('attackevasive_2_subject', 'attackevasive_2_message', FALSE);
}

if($attackevasive & 4) {
	if(empty($lastrequest) || TIMESTAMP - $lastrequest > 300) {
		securitymessage('attackevasive_4_subject', 'attackevasive_4_message');
	}
}

if($attackevasive & 8) {
	list($visitcode, $visitcheck, $visittime) = explode('|', authcode($_G['cookie']['visitcode'], 'DECODE'));
	if(!$visitcode || !$visitcheck || !$visittime || TIMESTAMP - $visittime > 60 * 60 * 4 ) {
		if(empty($_POST['secqsubmit']) || ($visitcode != md5($_POST['answer']))) {
			$answer = 0;
			$question = '';
			for ($i = 0; $i< rand(2, 5); $i ++) {
				$r = rand(1, 20);
				$question .= $question ? ' + '.$r : $r;
				$answer += $r;
			}
			$question .= ' = ?';
			dsetcookie('visitcode', authcode(md5($answer).'|0|'.TIMESTAMP, 'ENCODE'), TIMESTAMP + 816400, 1, true);
			securitymessage($question, '<input type="text" name="answer" size="8" maxlength="150" /><input type="submit" name="secqsubmit" class="button" value=" Submit " />', FALSE, TRUE);
		} else {
			dsetcookie('visitcode', authcode($visitcode.'|1|'.TIMESTAMP, 'ENCODE'), TIMESTAMP + 816400, 1, true);
		}
	}

}

function securitymessage($subject, $message, $reload = TRUE, $form = FALSE) {
	global $_G;
	$scuritylang = array(
		'attackevasive_1_subject' => '&#x9891;&#x7e41;&#x5237;&#x65b0;&#x9650;&#x5236;',
		'attackevasive_1_message' => '&#x60a8;&#x8bbf;&#x95ee;&#x672c;&#x7ad9;&#x901f;&#x5ea6;&#x8fc7;&#x5feb;&#x6216;&#x8005;&#x5237;&#x65b0;&#x95f4;&#x9694;&#x65f6;&#x95f4;&#x5c0f;&#x4e8e;&#x4e24;&#x79d2;&#xff01;&#x8bf7;&#x7b49;&#x5f85;&#x9875;&#x9762;&#x81ea;&#x52a8;&#x8df3;&#x8f6c;&#x20;&#x2e;&#x2e;&#x2e;',
		'attackevasive_2_subject' => '&#x4ee3;&#x7406;&#x670d;&#x52a1;&#x5668;&#x8bbf;&#x95ee;&#x9650;&#x5236;',
		'attackevasive_2_message' => '&#x672c;&#x7ad9;&#x73b0;&#x5728;&#x9650;&#x5236;&#x4f7f;&#x7528;&#x4ee3;&#x7406;&#x670d;&#x52a1;&#x5668;&#x8bbf;&#x95ee;&#xff0c;&#x8bf7;&#x53bb;&#x9664;&#x60a8;&#x7684;&#x4ee3;&#x7406;&#x8bbe;&#x7f6e;&#xff0c;&#x76f4;&#x63a5;&#x8bbf;&#x95ee;&#x672c;&#x7ad9;&#x3002;',
		'attackevasive_4_subject' => '&#x9875;&#x9762;&#x91cd;&#x8f7d;&#x5f00;&#x542f;',
		'attackevasive_4_message' => '&#x6b22;&#x8fce;&#x5149;&#x4e34;&#x672c;&#x7ad9;&#xff0c;&#x9875;&#x9762;&#x6b63;&#x5728;&#x91cd;&#x65b0;&#x8f7d;&#x5165;&#xff0c;&#x8bf7;&#x7a0d;&#x5019;&#x20;&#x2e;&#x2e;&#x2e;'
	);

	$subject = $scuritylang[$subject] ? $scuritylang[$subject] : $subject;
	$message = $scuritylang[$message] ? $scuritylang[$message] : $message;
	if($_GET['inajax']) {
		security_ajaxshowheader();
		echo '<div id="attackevasive_1" class="popupmenu_option"><b style="font-size: 16px">'.$subject.'</b><br /><br />'.$message.'</div>';
		security_ajaxshowfooter();
	} else {
		echo '<html>';
		echo '<head>';
		echo '<title>'.$subject.'</title>';
		echo '</head>';
		echo '<body bgcolor="#FFFFFF">';
		if($reload) {
			echo '<script language="JavaScript">';
			echo 'function reload() {';
			echo '	document.location.reload();';
			echo '}';
			echo 'setTimeout("reload()", 1001);';
			echo '</script>';
		}
		if($form) {
			echo '<form action="'.$G['PHP_SELF'].'" method="post" autocomplete="off">';
		}
		echo '<table cellpadding="0" cellspacing="0" border="0" width="700" align="center" height="85%">';
		echo '  <tr align="center" valign="middle">';
		echo '    <td>';
		echo '    <table cellpadding="10" cellspacing="0" border="0" width="80%" align="center" style="font-family: Verdana, Tahoma; color: #666666; font-size: 11px">';
		echo '    <tr>';
		echo '      <td valign="middle" align="center" bgcolor="#EBEBEB">';
		echo '     	<br /><br /> <b style="font-size: 16px">'.$subject.'</b> <br /><br />';
		echo $message;
		echo '        <br /><br />';
		echo '      </td>';
		echo '    </tr>';
		echo '    </table>';
		echo '    </td>';
		echo '  </tr>';
		echo '</table>';
		if($form) {
			echo '</form>';
		}
		echo '</body>';
		echo '</html>';
	}
	exit();
}


function security_ajaxshowheader() {
	$charset = getglobal('config/output/charset');
	ob_end_clean();
	@header("Expires: -1");
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header("Pragma: no-cache");
	header("Content-type: application/xml");
	echo "<?xml version=\"1.0\" encoding=\"".$charset."\"?>\n<root><![CDATA[";
}

function security_ajaxshowfooter() {
	echo ']]></root>';
	exit();
}

?>