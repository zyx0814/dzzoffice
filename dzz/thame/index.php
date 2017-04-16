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
$do=$_GET['do']?$_GET['do']:'systhame';
$navtitle=lang('theme');

if($do=='systhame'){
//获取用户选择的主题信息
	
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$thames=array();
	$perpage = 300;
			//$perpage = mob_perpage($perpage);
	$start = ($page-1)*$perpage;
	$count=DB::result_first("SELECT COUNT(*) FROM ".DB::table('thame')." where 1 ");
	if($count){
		$query=DB::query("SELECT * FROM ".DB::table('thame')."  where 1 ORDER BY dateline DESC  LIMIT $start,$perpage" );
		while($value=DB::fetch($query)){
			if(!$value['backimg']) $value['backimg']='dzz/styles/thame/'.$value['folder'].'/back.jpg';
			$value['modules']=unserialize(stripslashes($value['modules']));
			$thames['thame_'.$value['id']]=$value;
		}
	}
	$thamejson=json_encode($thames);
	$multi = multi($count, $perpage, $page, DZZSCRIPT."?mod=thame");
}elseif($do=='syscolor'){
	
	//获取所有颜色
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$thames=array();
	$perpage =300;
			//$perpage = mob_perpage($perpage);
	$start = ($page-1)*$perpage;
	$count=DB::result_first("SELECT COUNT(*) FROM ".DB::table('wallpaper')." where type='syscolor'");
	if($count){
		$query=DB::query("SELECT * FROM ".DB::table('wallpaper')."  where type='syscolor' ORDER BY dateline DESC LIMIT $start,$perpage" );
		while($value=DB::fetch($query)){
			$thames[]=$value;
		}
	}
	$multi = multi($count, $perpage, $page, DZZSCRIPT."?mod=thame&do=$do");	
}elseif($do=='color'){
	
	//获取所有颜色
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$thames=array();
	$perpage = 300;
			//$perpage = mob_perpage($perpage);
	$start = ($page-1)*$perpage;
	$count=DB::result_first("SELECT COUNT(*) FROM ".DB::table('wallpaper')." where type='color'");
	if($count){
		$query=DB::query("SELECT * FROM ".DB::table('wallpaper')."  where type='color' ORDER BY dateline DESC LIMIT $start,$perpage" );
		while($value=DB::fetch($query)){
			$thames[]=$value;
		}
	}
	$multi = multi($count, $perpage, $page, DZZSCRIPT."?mod=thame&do=$do");
}elseif($do=='repeat' || $do=='scale'){
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$thames=array();
	$classid=intval($_GET['classid']);
	$class=array();
	$query=DB::query("select * from ".DB::table('wallpaper_class')." where type='{$do}' ORDER BY disp DESC");
	while($value=DB::fetch($query)){
		$class[]=$value;
	}
	$perpage =300;
	$wheresql="where type='{$do}'";
	if($classid) $wheresql.=" and  classid='{$classid}'";
			//$perpage = mob_perpage($perpage);
	$start = ($page-1)*$perpage;
	$count=DB::result_first("SELECT COUNT(*) FROM ".DB::table('wallpaper')." $wheresql");
	if($count){
		$query=DB::query("SELECT * FROM ".DB::table('wallpaper')." $wheresql ORDER BY dateline DESC LIMIT $start,$perpage" );
		while($value=DB::fetch($query)){
			if($value['thumb']) $value['thumbpic']=$value['val'].'.thumb.jpg';
			else $value['thumbpic']=$value['val'];
			$thames[]=$value;
		}
	}
	
	$multi = multi($count, $perpage, $page, DZZSCRIPT."?mod=thame&do=$do&classid=$classid");
}elseif($do=='url'){
	$page = empty($_GET['page'])?1:intval($_GET['page']);
	$thames=array();
	$classid=intval($_GET['classid']);
	$class=array();
	$query=DB::query("select * from ".DB::table('wallpaper_class')." where type='{$do}' ORDER BY disp DESC");
	while($value=DB::fetch($query)){
		$class[]=$value;
	}
	$perpage = 300;
	$wheresql="where type='{$do}'";
	if($classid) $wheresql.=" and  classid='{$classid}'";
			//$perpage = mob_perpage($perpage);
	$start = ($page-1)*$perpage;
	$count=DB::result_first("SELECT COUNT(*) FROM ".DB::table('wallpaper')." $wheresql");
	if($count){
		$query=DB::query("SELECT * FROM ".DB::table('wallpaper')." $wheresql ORDER BY dateline DESC LIMIT $start,$perpage" );
		while($value=DB::fetch($query)){
			if($value['thumb']) $value['thumbpic']=$value['img'].'.thumb.jpg';
			else $value['thumbpic']=$value['img'];
			$thames[]=$value;
		}
	}
	$multi = multi($count, $perpage, $page, DZZSCRIPT."?mod=thame&do=$do&classid=$classid");
}
include template('thame_thame');

?>
