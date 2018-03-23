<?php 
if(!defined('IN_DZZ')) {
	exit('Access Denied');
}

class dzz_admincp
{
	var $core = null;
	var $script = null;

	var $userlogin = false;
	var $adminsession = array();
	var $adminuser = array();
	var $perms = null;

	var $panel = 1;

	var $isfounder = false;

	var $cpsetting = array();

	var $cpaccess = 0;

	var $sessionlife = 1800;
	var $sessionlimit = 0;

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new dzz_admincp();
		}
		return $object;
	}

	function __construct() {
	}

	function init() {
		if(empty($this->core) || !is_object($this->core)) {
			exit('No Dzz core found');
		}

		$this->cpsetting = $this->core->config['admincp'];
		$this->adminuser = & $this->core->var['member'];

		$this->isfounder = $this->checkfounder($this->adminuser);

		$this->sessionlimit = TIMESTAMP - $this->sessionlife;

		$this->check_cpaccess(); 
		if(  $_GET['mod']!="systemlog"){ 
			$this->writecplog();
		}
		
	}

	function writecplog() {
		global $_G;
		$extralog = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('formhash', 'submit', 'addsubmit', 'admin_password', 'sid', 'action'));
		writelog('cplog', $extralog);
	}

	function check_cpaccess() {
		global $_G;
		$session = array();

		if(!$this->adminuser['uid']) {
			$this->cpaccess = 0;
		} else {
			if(!$this->isfounder) {
				$session = C::t('user')->fetch($this->adminuser['uid']);
				if($session && ($session['groupid']==1) ) {
					$session = array_merge($session, (array)C::t('admincp_session')->fetch($this->adminuser['uid'], $session['groupid']));
				}else $session=array();
			} else {
				$session = C::t('admincp_session')->fetch($this->adminuser['uid'], $this->panel);
			}
			if(empty($session)) {
				$this->cpaccess = $this->isfounder ? 1 : -2;

			} elseif(isset($_G['setting']['adminipaccess']) && $_G['setting']['adminipaccess'] && !ipaccess($_G['clientip'], $_G['setting']['adminipaccess'])) {
				$this->do_user_login();

			} elseif ($session && empty($session['uid'])) {
				$this->cpaccess = 1;

			} elseif ($session['dateline'] < $this->sessionlimit) {
				$this->cpaccess = 1;

			} elseif ($this->cpsetting['checkip'] && ($session['ip'] != $this->core->var['clientip'])) {
				$this->cpaccess = 1;

			} elseif ($session['errorcount'] >= 0 && $session['errorcount'] <= 3) {
				$this->cpaccess = 2;

			} elseif ($session['errorcount'] == -1) {
				$this->cpaccess = 3;

			} else {
				$this->cpaccess = -1;
			}
		}
		if($this->cpaccess == 2 || $this->cpaccess == 3) {
			if(!empty($session['customperm'])) {
				$session['customperm'] = dunserialize($session['customperm']);
			}
		}

		$this->adminsession = $session;

		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_password'])) {
			if($this->cpaccess == 2) {
				$this->check_admin_login();
			} elseif($this->cpaccess == 0) {
				$this->check_user_login();
			}
		}

		if($this->cpaccess == 1) {
			C::t('admincp_session')->delete($this->adminuser['uid'],$this->adminuser['groupid'], $this->sessionlife);
			C::t('admincp_session')->insert(array(
				'uid' => $this->adminuser['uid'],
				'adminid' => $this->adminuser['adminid'],
				'panel' => $this->adminuser['groupid'],
				'ip' => $this->core->var['clientip'],
				'dateline' => TIMESTAMP,
				'errorcount' => 0,
			));
		} elseif ($this->cpaccess == 3) {
			//$this->load_admin_perms();
			C::t('admincp_session')->update($this->adminuser['uid'], $this->adminuser['groupid'], array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => -1));
		}

		if($this->cpaccess != 3) {
			$this->do_user_login();
		}

	}

	function check_admin_login() {
		global $_G;
		if((empty($_POST['admin_questionid']) || empty($_POST['admin_answer'])) && ($_G['config']['admincp']['forcesecques'] || $_G['group']['forcesecques'])) {
			$this->do_user_login();
		}
		require_once DZZ_ROOT.'/user/function/function_user.php';
		$ucresult = uc_user_login($this->adminuser['uid'], $_POST['admin_password'], 1, 1, $_POST['admin_questionid'], $_POST['admin_answer'], $this->core->var['clientip']);
		if($ucresult[0] > 0) {
			C::t('admincp_session')->update($this->adminuser['uid'], $this->adminuser['groupid'], array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => -1));
			dheader('Location: '.BASESCRIPT.'?'.cpurl('url', array('sid')));
		} else {
			$errorcount = $this->adminsession['errorcount'] + 1;
			C::t('admincp_session')->update($this->adminuser['uid'], $this->adminuser['groupid'], array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => $errorcount));
		}
	}

	function check_user_login() {
		global $_G;
		$admin_email = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
		if($admin_email != '') {
			require_once DZZ_ROOT.'/user/function/function_user.php';
			if(logincheck($_POST['admin_email'])) {
				if((empty($_POST['admin_questionid']) || empty($_POST['admin_answer'])) && ($_G['config']['admincp']['forcesecques'] || $_G['group']['forcesecques'])) {
					$this->do_user_login();
				}
				$result = userlogin($_POST['admin_email'], $_POST['admin_password'], $_POST['admin_questionid'], $_POST['admin_answer'], 'auto', $this->core->var['clientip']);
				if($result['status'] == 1) {
				
					if($this->checkfounder($result['member']) || $result['member']['groupid']==1) {
						C::t('admincp_session')->insert(array(
							'uid' =>$result['member']['uid'],
							'adminid' =>$result['member']['adminid'],
							'panel' =>$result['member']['groupid'],
							'dateline' => TIMESTAMP,
							'ip' => $this->core->var['clientip'],
							'errorcount' => -1), false, true);

						setloginstatus($result['member'], 0);
						dheader('Location: '.$_SERVER['HTTP_REFERER'] );
						//dheader('Location: '.ADMINSCRIPT.'?'.cpurl('url', array('sid')));
					
					} else {
						$this->cpaccess = -2;
					}
				} else {
					loginfailed($_POST['admin_email']);
				}
			} else {
				$this->cpaccess = -4;
			}
		}
	}


	function checkfounder($user) {

		$founders = str_replace(' ', '', $this->cpsetting['founder']);
		if(!$user['uid'] || $user['groupid'] != 1 || $user['adminid'] != 1) {
			return false;
		} elseif(empty($founders)) {
			return true;
		} elseif(strexists(",$founders,", ",$user[uid],")) {
			return true;
		} elseif(!is_numeric($user['nickname']) && strexists(",$founders,", ",$user[nickname],")) {
			return true;
		} else {
			return FALSE;
		}
	}

	function do_user_login() {
		require $this->admincpfile('login');
	}

	function do_admin_logout() {
		C::t('admincp_session')->delete($this->adminuser['uid'], $this->adminuser['groupid'], $this->sessionlife);
	}

	function admincpfile($action) {
		return './admin/login/login.php';
	}
}
?>