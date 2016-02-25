<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if(!defined('IN_DZZ') || !defined('IN_ADMIN')) {
	exit('Access Denied');
}
$do=trim($_GET['do']);

$title='添加';

if(submitcheck('iconsubmit')){
	$did=intval($_GET['did']);
	$icon=C::t('icon')->fetch($did);
	$setarr=array('domain'=>$_GET['domain'],
				  'reg'=>trim($_GET['reg']),
				  'ext'=>trim($_GET['ext']),
				  'check'=>1,
				  'disp'=>intval($_GET['disp'])
				  );
	//处理图标
		$iconnew='';
		$target='';
		if($icon){
			$target=$icon['pic'];
		}
		if($_FILES['iconnew']) {
			//print_r($_FILES['iconnew']);
			//cpmsg('dfdsfd');
			if($_FILES['iconnew']['tmp_name']) {
				if($pic=upload_to_icon($_FILES['iconnew'],$target,$setarr['domain'])){
					$setarr['pic']=$pic;
				}
			}
		} else {
			//if(!$_GET['iconnew']) $_GET['iconnew']='dzz/images/default/icodefault.png';
			if($_GET['iconnew'] && $_GET['iconnew']!=$_G['setting']['attachurl'].$target){
				if($pic=image_to_icon($_GET['iconnew'],$target,$setarr['domain'])){
					$setarr['pic']=$pic;
				}
			}
		}
	if($did){
		C::t('icon')->update($did,$setarr);
			$setarr['did']=$did;
			$setarr['msg']='success';
			$setarr['pic']=$_G['setting']['attachurl'].($setarr['pic']?$setarr['pic']:$icon['pic']).'?t='.TIMESTAMP;
			showmessage('do_success',dreferer(),array('data'=>rawurlencode(json_encode($setarr))),array('showmsg'=>false));
	}else{
		$setarr['dateline']=TIMESTAMP;
		if($setarr['did']=C::t('icon')->insert($setarr,1)){
			$setarr['msg']='success';
			$setarr['pic']=$_G['setting']['attachurl'].($setarr['pic']?$setarr['pic']:$icon['pic']).'?t='.TIMESTAMP;
			showmessage('do_success',dreferer(),array('data'=>rawurlencode(json_encode($setarr))),array('showmsg'=>false));
		}else{
			showmessage('添加失败，请稍后重试');
		}
	}

}elseif($do=='delete'){
	$dids=$_GET['dids'];
	foreach($dids as $did){
		C::t('icon')->delete_by_did($did);
	}
	exit(json_encode(array('msg'=>'success')));
}else{
	$did=intval($_GET['did']);
	if($did){
		$icon=C::t('icon')->fetch($did);
		$title='编辑';
	}
}
include template('editicon');
?>
