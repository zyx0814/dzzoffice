<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
	
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
$code=rawurldecode($_GET['code']);
$redirecturl=dzzdecode(rawurldecode($_GET['url']));
if(empty($redirecturl)) $redirecturl=dzzdecode(rawurldecode($_GET['url']),'',4);
$weObj=new qyWechat(array('token'=>getglobal('setting/token_0'),'appid'=>getglobal('setting/CorpID'),'appsecret'=>getglobal('setting/CorpSecret'),'agentid'=>0,'encodingaeskey'=>getglobal('setting/encodingaeskey_0'),'debug'=>true));
$userid=$weObj->getUserId($code,0);

//生成登录cookie
if($user=C::t('user')->fetch(str_replace('dzz-','',$userid))){
	dsetcookie('auth', authcode("{$user['password']}\t{$user['uid']}", 'ENCODE'), 365*24*60*60, 1, true);
}
@header("Location: $redirecturl");
exit();
?>
