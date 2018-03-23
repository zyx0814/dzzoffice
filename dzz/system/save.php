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
$do = empty($_GET['do'])?'':trim($_GET['do']);
if(empty($_G['uid'])) {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		dsetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		dsetcookie('_refer', rawurlencode(DZZSCRIPT.'?mod=system&op=dzzcp='.$de));
	}
	exit('needlogin');
}

$uid =$_G['uid'];
$space = dzzgetspace($_G['uid']);
$space['self']=intval($space['self']);
$refer=dreferer();	
if($do=='folder'){
	$data = array();
	$fid=intval($_GET['fid']);
	if(isset($_GET['iconview'])) $data['iconview']=intval($_GET['iconview']);
	if(isset($_GET['disp'])) $data['disp']=intval($_GET['disp']);
	if($data && perm_check::checkperm_Container($fid,'admin')){
			C::t('folder')->update($fid,$data);
	}

	exit('success');
}elseif($do == 'catsearch'){
	$cid=intval($_GET['catid']);
	$data = array();
	if(isset($_GET['iconview'])) $data['iconview']=intval($_GET['iconview']);
	if(isset($_GET['disp'])) $data['disp']=intval($_GET['disp']);
	if($data){
		$update = C::t('resources_cat')->update($cid,$data);
	}
	exit('success');
}elseif($do == 'search'){
	$uid = getglobal('uid');
	$data = array();
	if(isset($_GET['iconview'])) $data['iconview']=intval($_GET['iconview']);
	if(isset($_GET['disp'])) $data['disp']=intval($_GET['disp']);
	foreach($data as $k=>$v){
		if($settinginfo = C::t('user_setting')->fetch_by_skey($k)){
			C::t('user_setting')->update_by_skey($k,$v);
		}else{
			C::t('user_setting')->insert_by_skey($k,$v);
		}
	}
	exit('success');
}elseif($do == 'recycle'){
    $uid = getglobal('uid');
    $data = array();
    if(isset($_GET['iconview'])) $data['recycleiconview']=intval($_GET['iconview']);
    if(isset($_GET['disp'])) $data['recycledisp']=intval($_GET['disp']);
    foreach($data as $k=>$v){
        if($settinginfo = C::t('user_setting')->fetch_by_skey($k)){
            C::t('user_setting')->update_by_skey($k,$v);
        }else{
            C::t('user_setting')->insert_by_skey($k,$v);
        }
    }
    exit('success');
}elseif($do == 'infopanelopen'){
	$infopanelopen = intval($_GET['infopanelopen']);
	//$infoPanelOpened=($infopanelopen == 1) ? 'on':'off';
	$settinginfo = C::t('user_setting')->fetch_by_skey('infoPanelOpened');
	if(isset($settinginfo)){
		C::t('user_setting')->update_by_skey('infoPanelOpened',$infopanelopen);
	}else{
		C::t('user_setting')->insert_by_skey('infoPanelOpened',$infopanelopen);
	}
	exit('success');


}

?>
