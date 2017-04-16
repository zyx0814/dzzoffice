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

define('NOROBOT', TRUE);

if (is_array($_COOKIE) && (empty($_G['uid']) || ($_G['uid'] && $formhash == formhash()))) {
	foreach ($_G['cookie'] as $key => $val) {
		dsetcookie($key, '', -1, 0);
	}
	foreach ($_COOKIE as $key => $val) {
		setcookie($key, '', -1, $_G['config']['cookie']['cookiepath'], '');
	}
}

showmessage('login_clearcookie', dreferer(), array(), $_G['inajax'] ? array('msgtype' => 3, 'showmsg' => true) : array());
?>
