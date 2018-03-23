<?php
/* rewrite规则
	<IfModule mod_rewrite.c>
	  Options +FollowSymlinks
	  RewriteEngine On
	  RewriteCond %{REQUEST_FILENAME} !-d
	  RewriteCond %{REQUEST_FILENAME} !-f
	  RewriteRule ^wopi\/files\/(\w+)\/contents(.*)$ core/api/wopi/index.php?action=contents&path=$1&$2 [QSA,PT,L]
	   RewriteRule ^wopi\/files\/(\w+)\/(lock|unlock)(.*)$ core/api/wopi/index.php?action=$2&path=$1&$3 [QSA,PT,L]
	  RewriteRule ^wopi\/files\/(\w+)(.*)$ core/api/wopi/index.php?path=$1&$2 [QSA,PT,L]
	  RewriteRule ^wopi\/files\/(.*)$ core/api/wopi/index.php?$1 [QSA,PT,L]
	</IfModule>
	
	$_SERVER['HTTP_USER_AGENT']  LOOLWSD WOPI Agent 3.0.0(collabora);MSWAC(office online server);
	
	//自定义lock和unlock请求格式
	http://{hostname}/wopi/files/{fileID}/lock|unlock&&access_token={access_token}
	{fileID}:文件ID；
	{access_token} : 格式为dzzencode(uid|{lock});{LOCK}  ：锁内容，加锁时会直接把此内容写入锁文件，解锁时会比较锁文件内容是否等于{LOCK};调用者可以根据不同需要通过不同参数来组织此内容
 */
define('APPTYPEID', 119);
define('CURSCRIPT', 'wopi');
require __DIR__.'./../../coreBase.php';
$dzz = C::app();
$dzz->init();
require_once('wopi.php');
$path=dzzdecode($_GET['path']);

if ($_GET['access_token']) {
	require_once DZZ_ROOT.'./user/function/function_user.php';
	$access_token=dzzdecode($_GET['access_token']);
	list($uid,$alock)=explode('|',$access_token);
	$user=getuserbyuid($uid);
	setloginstatus($user,0);
	//Lock支持
	
	if($lock=$_SERVER['HTTP_X_WOPI_LOCK']){
		$oldlock=$_SERVER['HTTP_X_WOPI_OLDLOCK'];
	}else{
		$lock=$alock?$alock:$_GET['lock'];
	}
	if($Override=$_SERVER['HTTP_X_WOPI_OVERRIDE']){
		if($Override=='PUT'){
			Wopi::PutFile($path,$lock);
		}elseif($Override=='LOCK'){
			Wopi::Lock($path,$lock,$oldlock);
		}elseif($Override=='UNLOCK'){
			Wopi::unLock($path,$lock);
		}elseif($Override=='GET_LOCK'){
			Wopi::getLock($path);
		}elseif($Override=='REFRESH_LOCK'){	
			Wopi::Lock($path,$lock);
		}
	}elseif($_GET['action']=='contents'){
		if($_SERVER['REQUEST_METHOD']=='POST'){
			Wopi::PutFile($path);
		}else{
			Wopi::GetFile($path);	
		}
	}elseif($_GET['action']=='lock'){
		Wopi::Lock($path,$lock);
	}elseif($_GET['action']=='unlock'){
		Wopi::unLock($path,$lock);
	}else{
		Wopi::CheckFileInfo($path,$lock);
	}
} else {
	//print_r(Wopi::CheckFileInfo($path));
    print_r(Wopi::GenerateFileLink($path,'http://oos.dzz.com/','')); //TEST FUNCTION
}

