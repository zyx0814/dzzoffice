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
class logging_ctl {

	function logging_ctl() {
	}

	function logging_more($questionexist) {
		global $_G;
		if(empty($_GET['lssubmit'])) {
			return;
		}
		$auth = authcode($_GET['email']."\t".$_GET['password']."\t".($questionexist ? 1 : 0), 'ENCODE');
		$js = '<script type="text/javascript">showWindow(\'login\', \'user.php?mod=logging&action=login&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()).(!empty($_GET['cookietime']) ? '&cookietime=1' : '').'\')</script>';
		showmessage('location_login', '', array('type' => 1), array('extrajs' => $js));
	}

	function on_login() {
		global $_G,$_GET;
		if($_G['uid']) {
			if($_G['setting']['bbclosed']>0 && $_G['adminid']!=1){
				
			}else{
				$referer = dreferer();
				$referer=$referer ? $referer : './';
				$referer=str_replace('login','',$referer);
				$param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']);
				if(!$_GET['inajax']){
					showmessage('login_succeed', $referer ? $referer : './', $param);
				}else{
					$msg='';
					$msg.='		<div class="modal-body">';
					$msg.='		  <div class="alert_right">';
					$msg.='			<p id="succeedmessage"></p>';
					$msg.='			<p id="succeedlocation" class="alert_btnleft">'.lang('login_succeed', $param).'</p>';
					$msg.='			<p class="alert_btnleft"><a href="'.$referer.'" id="succeedmessage_href">'.lang('message_forward').'</a></p>';
					$msg.='		  </div>';
					$msg.='		</div>';
					$msg.='	  </div><script type="text/javascript">setTimeout("window.location.href =\''.$referer.'\';", 3000);</script></div>';
					exit($msg);
				}
			}
		}

		$from_connect = $this->setting['connect']['allow'] && !empty($_GET['from']) ? 1 : 0;
		$seccodecheck = $from_connect ? false : $this->setting['seccodestatus'] & 2;
		$seccodestatus = !empty($_GET['lssubmit']) ? false : $seccodecheck;
		$invite = getinvite();

		if(!submitcheck('loginsubmit', 1, $seccodestatus)) {

			$auth = '';
			$username = !empty($_G['cookie']['loginuser']) ? dhtmlspecialchars($_G['cookie']['loginuser']) : '';

			if(!empty($_GET['auth'])) {
				list($email, $password, $questionexist) = explode("\t", authcode($_GET['auth'], 'DECODE'));
				$email = dhtmlspecialchars($username);
				$auth = dhtmlspecialchars($_GET['auth']);
			}

			$cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_GET['cookietime']) ? 'checked="checked"' : '';

			if($seccodecheck) {
				$seccode = random(6, 1) + $seccode{0} * 1000000;
			}

			if($this->extrafile && file_exists($this->extrafile)) {
				require_once $this->extrafile;
			}

			$navtitle = lang('login');
			include template($this->template);

		} else {
			
			if(!empty($_GET['auth'])) {
				list($_GET['email'], $_GET['password']) = daddslashes(explode("\t", authcode($_GET['auth'], 'DECODE')));
			}

			if(!($_G['member_loginperm'] = logincheck($_GET['username']))) {
				showmessage('login_strike');
			}
			if($_GET['fastloginfield']) {
				$_GET['loginfield'] = $_GET['fastloginfield'];
			}
			$_G['uid'] = $_G['member']['uid'] = 0;
			$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
			if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
				showmessage('profile_passwd_illegal');
			}
			$result = userlogin($_GET['email'], $_GET['password'], $_GET['questionid'], $_GET['answer'],'auto', $_G['clientip']);
			$uid = $result['ucresult']['uid'];
			
			if(!empty($_GET['lssubmit']) && ($result['ucresult']['uid'] == -3 || $seccodecheck)) {
				$_GET['username'] = $result['ucresult']['username'];
				$this->logging_more($result['ucresult']['uid'] == -3);
			}

			if($result['status'] == -1) { //不可能发生；
				if(!$this->setting['fastactivation']) {
					$auth = authcode($result['ucresult']['username']."\t".FORMHASH, 'ENCODE');
					showmessage('location_activation', 'user.php?mod='.$this->setting['regname'].'&action=activation&auth='.rawurlencode($auth).'&referer='.rawurlencode(dreferer()), array(), array('location' => true));
				} else {
					$init_arr = explode(',', $this->setting['initcredits']);
					$groupid = $this->setting['regverify'] ? 8 : $this->setting['newusergroupid'];

					C::t('user')->insert($uid, $result['ucresult']['username'], md5(random(10)), $result['ucresult']['email'], $_G['clientip'], $groupid, $init_arr);
					$result['member'] = getuserbyuid($uid);
					$result['status'] = 1;
				}
			}elseif($result['status']==- 2){
				
				showmessage('user_stopped_please_admin');
			}elseif($_G['setting']['bbclosed']>0 && $result['member']['adminid']!=1){
				showmessage('site_closed_please_admin');
			}
		
			if($result['status'] > 0) {

				if($this->extrafile && file_exists($this->extrafile)) {
					require_once $this->extrafile;
				}

				setloginstatus($result['member'], $_GET['cookietime'] ? 2592000 : 0);
	
				if($_G['member']['lastip'] && $_G['member']['lastvisit']) {
					dsetcookie('lip', $_G['member']['lastip'].','.$_G['member']['lastvisit']);
				}
				C::t('user_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'lastvisit' =>TIMESTAMP, 'lastactivity' => TIMESTAMP));

				if($invite['id']) {
					$result = C::t('invite')->count_by_uid_fuid($invite['uid'], $uid);
					if(!$result) {
						C::t('invite')->update($invite['id'], array('fuid'=>$uid, 'fusername'=>$_G['username']));
					
					} else {
						$invite = array();
					}
				}

				$param = array(
					'username' => $result['ucresult']['username'],
					'usergroup' => $_G['group']['grouptitle'],
					'uid' => $_G['member']['uid'],
					'groupid' => $_G['groupid'],
					'syn' =>  0
				);

				$extra = array(
					'showdialog' => true,
					'locationtime' => true,
					'extrajs' => ''
				);

				$loginmessage = $_G['groupid'] == 8 ? 'login_succeed_inactive_member' : 'login_succeed';

				$location = $_G['groupid'] == 8 ? 'index.php?open=password' : dreferer();
				if(empty($_GET['handlekey']) || !empty($_GET['lssubmit'])) {
					/*if(defined('IN_MOBILE')) {
						showmessage('location_login_succeed_mobile', $location, array('username' => $result['ucresult']['username']), array('location' => true));
					} else {*/
						if(!empty($_GET['lssubmit'])) {
							
							showmessage($loginmessage, $location, $param, $extra);
						} else {
							
							$href = str_replace("'", "\'", $location);
							$href = str_replace("login", "", $location);
							
							showmessage('location_login_succeed', $location, array(),
								array(
									'showid' => 'main_message',
									'extrajs' => '<script type="text/javascript">'.
										'setTimeout("window.location.href =\''.$href.'\';", 3000);'.
										'$(\'succeedmessage_href\').href = \''.$href.'\';'.
										'$(\'main_message\').style.display = \'none\';'.
										'$(\'main_succeed\').style.display = \'\';'.
										'$(\'succeedlocation\').innerHTML = \''.lang( $loginmessage, $param).'\';</script>',
									'striptags' => false,
									'showdialog' => false
								)
							);
						}
					//}
				} else {
					showmessage($loginmessage, $location, $param, $extra);
				}
			} else {
				$password = preg_replace("/^(.{".round(strlen($_GET['password']) / 4)."})(.+?)(.{".round(strlen($_GET['password']) / 6)."})$/s", "\\1***\\3", $_GET['password']);
				$errorlog = dhtmlspecialchars(
					TIMESTAMP."\t".
					($result['ucresult']['email'] ? $result['ucresult']['email'] : $_GET['email'])."\t".
					$password."\t".
					"Ques #".intval($_GET['questionid'])."\t".
					$_G['clientip']);
				writelog('illegallog', $errorlog);
				loginfailed($_GET['username']);
				
				$fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_GET['questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
				if($_G['member_loginperm'] > 1) {
					showmessage($fmsg, '', array('loginperm' => $_G['member_loginperm'] - 1));
				} elseif($_G['member_loginperm'] == -1) {
					showmessage('login_password_invalid');
				} else {
					showmessage('login_strike');
				}
			}

		}

	}

	function on_logout() {
		global $_G;


		if($_GET['formhash'] != $_G['formhash']) {
			showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH));
		}

		clearcookies();
		
		$_G['groupid'] = $_G['member']['groupid'] = 7;
		$_G['uid'] = $_G['member']['uid'] = 0;
		$_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
		if(defined('IN_MOBILE')) {
			showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH));
		} else {
			showmessage('logout_succeed', dreferer(), array('formhash' => FORMHASH));
		}
	}
	

}

class register_ctl {

	var $showregisterform = 1;

	function register_ctl() {
		global $_G;
	
		if($_G['setting']['bbclosed']) {
			showmessage('register_disable', NULL, array(), array('login' => 1));
		}
	}

	function on_register() {
		global $_G;
		$_GET['username'] = $_GET['username'];
		$_GET['nickname'] = $_GET['nickname'];
		$_GET['password'] = $_GET['password'];
		$_GET['password2'] = $_GET['password2'];
		$_GET['email'] = $_GET['email'];

		if($_G['uid']) {
			
			$url_forward = dreferer();
			if(strpos($url_forward, 'reg') !== false) {
				$url_forward = 'index.php';
			}
			showmessage('login_succeed', $url_forward ? $url_forward : './', array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']), array());
		} elseif(!$this->setting['regclosed']) {
			if($_GET['action'] == 'activation' || $_GET['activationauth']) {
				if(!$this->setting['ucactivation'] && !$this->setting['closedallowactivation']) {
					showmessage('register_disable_activation');
				}
			} elseif(!$this->setting['regstatus']) {
				showmessage(!$this->setting['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $this->setting['regclosemessage']));
			}
		}

		$bbrules = & $this->setting['bbrules'];
		$bbrulesforce = & $this->setting['bbrulesforce'];
		$bbrulestxt = & $this->setting['bbrulestxt'];
		$welcomemsg = & $this->setting['welcomemsg'];
		$welcomemsgtitle = & $this->setting['welcomemsgtitle'];
		$welcomemsgtxt = & $this->setting['welcomemsgtxt'];
		$regname = $this->setting['regname'];
		$username = isset($_GET['username']) ? $_GET['username'] : '';
		

		$invitestatus = false;
		$seccodecheck = $this->setting['seccodestatus'] & 1;
		$secqaacheck = 0;
		
		
		$bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';
		$auth = $_GET['auth'];

		if(!$invitestatus) {
			$invite = getinvite();
		}
		
		if(!submitcheck('regsubmit', 0, $seccodecheck/*, $secqaacheck*/)) {

				if($seccodecheck) {
					$seccode = random(6, 1);
				}

				$username = dhtmlspecialchars($username);

				$htmls = $settings = array();
				
				foreach($_G['cache']['fields_register'] as $field) {
					$fieldid = $field['fieldid'];
					$html = profile_setting($fieldid, array(), false, false, true);
					if($html) {
						$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
						$htmls[$fieldid] = $html;
					}
				}

				$navtitle = $this->setting['reglinkname'];

				if($this->extrafile && file_exists($this->extrafile)) {
					require_once $this->extrafile;
				}
			
			$bbrulestxt = nl2br("\n$bbrulestxt\n\n");
			$dreferer = dreferer();
			
			//QQ登陆相关
			@session_start();
			$qqopenid = $_SESSION['openid'] ? $_SESSION['openid'] :'';
			$qquinfo = $_SESSION['uinfo'] ? $_SESSION['uinfo'] :'';
			include template($this->template);

		} else {
					
			$emailstatus = 0;
			if($this->setting['regstatus'] == 2 && empty($invite) && !$invitestatus) {
				showmessage('not_open_registration_invite');
			}
			//验证同意协议
			if($bbrules && $bbrulehash != $_POST['agreebbrule']) {
				showmessage('register_rules_agree');
			}
			//验证用户姓名
			$usernamelen = dstrlen($username);
			if($usernamelen < 3) {
				showmessage('profile_username_tooshort');
			}
			if($usernamelen > 30) {
				showmessage('profile_username_toolong');
			}
			
			//验证用户名
			if($nickname = (trim($_GET['nickname']))){
				$nicknamelen = dstrlen($nickname);
				if($nicknamelen < 3) {
					showmessage('profile_nickname_tooshort');
				}
				if($nicknamelen > 30) {
					showmessage('profile_nickname_toolong');
				}
			}else{
				$nickname='';
			}
			
			//验证邮箱
			$email = strtolower(trim($_GET['email']));
			checkemail($email);
			
			//验证密码长度
			if($this->setting['pwlength']) {
				if(strlen($_GET['password']) < $this->setting['pwlength']) {
					showmessage('profile_password_tooshort', '', array('pwlength' => $this->setting['pwlength']));
				}
			}
			//验证密码强度
			if($this->setting['strongpw']) {
				$strongpw_str = array();
				if(in_array(1, $this->setting['strongpw']) && !preg_match("/\d+/", $_GET['password'])) {
					$strongpw_str[] = lang('strongpw_1');
				}
				if(in_array(2, $this->setting['strongpw']) && !preg_match("/[a-z]+/", $_GET['password'])) {
					$strongpw_str[] = lang('strongpw_2');
				}
				if(in_array(3, $this->setting['strongpw']) && !preg_match("/[A-Z]+/", $_GET['password'])) {
					$strongpw_str[] = lang('strongpw_3');
				}
				if(in_array(4, $this->setting['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['password'])) {
					$strongpw_str[] = lang('strongpw_4');
				}
				if($strongpw_str) {
					showmessage(lang('password_weak').implode(',', $strongpw_str));
				}
			}
			//验证两次密码一致性
			if($_GET['password'] !== $_GET['password2']) {
				showmessage('admininfo_password2_invalid');
			}

			if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
				showmessage('profile_passwd_illegal');
			}
			$password = $_GET['password'];
			
			
			$ctrlip = $_G['clientip'];
			$setregip = null;
			

			$profile = $verifyarr = array();
			foreach($_G['cache']['fields_register'] as $field) {
				/*if(defined('IN_MOBILE')) {
					break;
				}*/
				$field_key = $field['fieldid'];
				$field_val = $_GET[''.$field_key];
				if($field['formtype'] == 'file' && !empty($_FILES[$field_key]) && $_FILES[$field_key]['error'] == 0) {
					$field_val = true;
				}

				if(!profile_check($field_key, $field_val)) {
					$showid = !in_array($field['fieldid'], array('birthyear', 'birthmonth')) ? $field['fieldid'] : 'birthday';
					showmessage($field['title'].lang('profile_illegal'), '', array(), array(
						'showid' => 'chk_'.$showid,
						'extrajs' => $field['title'].lang('profile_illegal').($field['formtype'] == 'text' ? '<script type="text/javascript">'.
							'$(\'registerform\').'.$field['fieldid'].'.parentNode.parentNode.className = \'form-group warning\';'.
							'$(\'registerform\').'.$field['fieldid'].'.onblur = function () { if(this.value != \'\') {this.parentNode.parentNode.className = \'form-group\';$(\'chk_'.$showid.'\').innerHTML = \'\';}}'.
							'</script>' : '')
					));
				}
				if($field['needverify']) {
					$verifyarr[$field_key] = $field_val;
				} else {
					$profile[$field_key] = $field_val;
				}
			}

				$groupinfo = array();
				$addorg=0;
				if($this->setting['regverify']) {
					$groupinfo['groupid'] = 8;
				} else {
					$groupinfo['groupid'] = $this->setting['newusergroupid'];
					$addorg=1;
				}
				$result = uc_user_register(addslashes($username), $password, $email,addslashes($nickname), $questionid, $answer, $_G['clientip'],$addorg);
				if(is_array($result)){
					$uid=$result['uid'];
					$password=$result['password'];
				}else{
					$uid=$result;
				}
				if($uid <= 0) {
					if($uid == -1) {
						showmessage('profile_nickname_illegal');
					} elseif($uid == -2) {
						showmessage('profile_nickname_protect');
					} elseif($uid == -3) {
						showmessage('profile_nickname_duplicate');
					} elseif($uid == -4) {
						showmessage('profile_email_illegal');
					} elseif($uid == -5) {
						showmessage('profile_email_domain_illegal');
					} elseif($uid == -6) {
						showmessage('profile_email_duplicate');
					} elseif($uid == -7) {
						showmessage('profile_username_illegal');
					} else {
						showmessage('undefined_action');
					}
				}
			
			$_G['username'] = $username;
			

			if(isset($_POST['birthmonth']) && isset($_POST['birthday'])) {
				$profile['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
			}
			if(isset($_POST['birthyear'])) {
				$profile['zodiac'] = get_zodiac($_POST['birthyear']);
			}

			if($_FILES) {
				

				foreach($_FILES as $key => $file) {
					$field_key = 'field_'.$key;
					if(!empty($_G['cache']['fields_register'][$field_key]) && $_G['cache']['fields_register'][$field_key]['formtype'] == 'file') {

						if($attachment = uploadtolocal($file,'profile','')){
							if(@getimagesize($_G['setting']['attachdir'].$attachment)) {//判断是否为图片文件
								@unlink($_G['setting']['attachdir'].$attachment);
								continue;
							}
							if($_G['cache']['fields_register'][$field_key]['needverify']) {
								$verifyarr[$key] = $attachment;
							} else {
								$profile[$key] = $attachment;
							}
						}
					}
				}
			}
		
			$init_arr = array('profile'=>$profile, 'emailstatus' => $emailstatus);

			C::t('user')->insert($uid, $_G['clientip'], $groupinfo['groupid'], $init_arr);
			if($verifyarr) {
				$setverify = array(
					'uid' => $uid,
					'username' => $username,
					'verifytype' => '0',
					'field' => serialize($verifyarr),
					'dateline' => TIMESTAMP,
				);
				C::t('user_verify_info')->insert($setverify);
				C::t('user_verify')->insert(array('uid' => $uid));
			}
			//QQ登陆相关
			@session_start();
			if($_SESSION['openid']){//绑定qq登陆的openid
				C::t('user_qqconnect')->insert_by_openid($_SESSION['openid'],$uid,$_SESSION['uinfo']);
				@session_unset();
			}

			require_once libfile('cache/userstats', 'function');
			build_cache_userstats();

			if($this->extrafile && file_exists($this->extrafile)) {
				require_once $this->extrafile;
			}

			setloginstatus(array(
				'uid' => $uid,
				'username' => $_G['username'],
				'password' => $password,
				'groupid' => $groupinfo['groupid'],
			), 0);
			include_once libfile('function/stat');
			
			
			if($welcomemsg && !empty($welcomemsgtxt)) {
				$welcomemsgtitle = replacesitevar($welcomemsgtitle);
				$welcomemsgtxt = replacesitevar($welcomemsgtxt);
				if($welcomemsg == 1) {
					$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
				} elseif($welcomemsg == 2) {
					sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
				} elseif($welcomemsg == 3) {
					sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
					$welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
				}
			}

			dsetcookie('loginuser', '');
			dsetcookie('invite_auth', '');

			$url_forward = dreferer();
			$refreshtime = 3000;
			switch($this->setting['regverify']) {
				case 1:
					$idstring = random(6);
					$authstr = $this->setting['regverify'] == 1 ? "$_G[timestamp]\t2\t$idstring" : '';
					C::t('user')->update($_G['uid'], array('authstr' => $authstr));
					$verifyurl = "{$_G[siteurl]}user.php?mod=activate&amp;uid={$_G[uid]}&amp;id=$idstring";
					$email_verify_message = lang('email_verify_message', array(
						'username' => $_G['member']['username'],
						'sitename' => $this->setting['sitename'],
						'siteurl' => $_G['siteurl'],
						'url' => $verifyurl
					));
					if(!sendmail("$username <$email>", lang('email_verify_subject'), $email_verify_message)) {
						runlog('sendmail', "$email sendmail failed.");
					}
					$message = 'register_email_verify';
					$locationmessage = 'register_email_verify_location';
					$refreshtime = 10000;
					break;
				case 2:
					$message = 'register_manual_verify';
					$locationmessage = 'register_manual_verify_location';
					break;
				default:
					$message = 'register_succeed';
					$locationmessage = 'register_succeed_location';
					break;
			}
			$param = daddslashes(array('sitename' => $this->setting['sitename'], 'username' => $_G['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']));
			if(strpos($url_forward, $this->setting['regname']) !== false || strpos($url_forward, 'buyinvitecode') !== false) {
				$url_forward = 'index.php';
			}
			  
			$extra = array(
					'showdialog' => true,
					'locationtime' => false,
					'extrajs' => ''
				);
			showmessage('', $url_forward, array(),
								array(
									'showid' => 'succeedmessage',
									'extrajs' => '<script type="text/javascript">'.
										'setTimeout("window.location.href =\''.$url_forward.'\';", 3000);'.
										'$(\'succeedmessage_href\').href = \''.$url_forward.'\';'.
										'$(\'register_form\').style.display = \'none\';'.
										'$(\'main_succeed\').style.display = \'\';'.
										'$(\'succeedlocation\').innerHTML = \''.lang( $message, $param).'\';</script>',
									'striptags' => false,
									'showdialog' => false
								)
							);
			//showmessage($message, $url_forward, $param, $extra);
		}
	}
}
?>
