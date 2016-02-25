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
require_once libfile('function/admin');
$do=trim($_GET['do']);
if($do=='export'){//主题导出
	$id=intval($_GET['id']);
	$thame = C::t('thame')->fetch($id);
	unset($thame['id']);
	unset($thame['default']);
	if(!$thame) {
		showmessage('主题不存在');
	}
	$apparray=array();
	$apparray['thame']=$thame;
	exportdata('Dzz! theme', $apparray['thame']['folder'], $apparray);
	exit();
	
}elseif($do=='install'){//安装主题
	$finish = FALSE;
	$dir = $_GET['dir'];
	$xmlfile = 'dzz_theme_'.$dir.'.xml';
	$importfile = DZZ_ROOT.'./dzz/styles/thame/'.$dir.'/'.$xmlfile;
	if(!file_exists($importfile)) {
		showmessage('主题目录内没有主题配置文件：'.$xmlfile,dreferer());
	}
	$importtxt = @implode('', file($importfile));
	$apparray = getimportdata('Dzz! theme');
	$thame=$apparray['thame'];
	unset($thame['id']);
	unset($thame['default']);
	if($id=DB::result_first("select id from %t where folder=%s",array('thame',$dir))){
		C::t('thame')->update($id,$thame);
	}else{
		$id=C::t('thame')->insert($thame,1);
	}
	//if($id && $thame['default']) DB::query("update %t SET `default`='0' where id!=%d",array('thame',$id));
	showmessage('主题安装成功',dreferer(),array(),array('alert'=>'right'));
	

}
//include template('cp');

?>
