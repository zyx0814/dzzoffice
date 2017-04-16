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
if(empty($_G['uid'])){
	include template('common/header_reload');
	echo "<script type=\"text/javascript\">";
	echo "try{top._login.logging();win.Close();}catch(e){location.href='user.php?mod=logging'}";
	echo "</script>";	
	include template('common/footer_reload');
	exit();
}

$mycloud=array();
$icosdata=$folderdata=array();
$query=DB::query("select * from ".DB::table('connect')." where available>0");
while($value=DB::fetch($query)){
	if($value['type']=='local'){
		$value['cloudname']=$value['name'];
		$value['img']='dzz/images/default/system/home.png';
		$value['dateline']=$_G['timestamp'];
		$value['icoid']='home';
		$mycloud[]=$value;
		$icosdata['home']=array(
				'icoid'=>'home',
				'name'=>$value['cloudname'],
				'img'=>'dzz/images/default/system/home.png',
				'bz'=>'',
				'path'=>'home',
				'type'=>'folder',
				'pfid'=>0,
				
				'oid'=>DB::result_first("select fid from ".DB::table('folder')." where flag='home' and uid='{$_G[uid]}'")
				
			);
	
	}elseif($value['type']=='pan'){
		foreach(DB::fetch_all("select cloudname,cuid,cusername,id,dateline from ".DB::table($value['dname'])." where uid='{$_G[uid]}'") as $value1){
			if(!$value1['cloudname']) $value1['cloudname']=$value['name'].':'.($value1['cusername']?$value1['cusername']:$value1['cuid']);
			$value1['bz']=$value['bz'];
			$value1['icoid']=md5($value['bz'].':'.$value1['id'].':'.$value['root']);
			$value1['img']='dzz/images/default/system/'.$value['bz'].'.png';
			$mycloud[]=$value1;
			$icosdata[md5($value['bz'].':'.$value1['id'].':'.$value['root'])]=array(
				'icoid'=>md5($value['bz'].':'.$value1['id'].':'.$value['root']),
				'name'=>$value1['cloudname'],
				'img'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':'.$value['root'],
				'type'=>'pan',
				'oid'=>md5($value['bz'].':'.$value1['id'].':'.$value['root']),
				
			);
			$folderdata[md5($value['bz'].':'.$value1['id'].':'.$value['root'])]=array(
				'fid'=>md5($value['bz'].':'.$value1['id'].':'.$value['root']),
				'pfid'=>0,
				'fname'=>$value1['cloudname'],
				'icon'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':'.$value['root'],
				'type'=>'pan',
				'fsperm'=>perm_FolderSPerm::flagPower($value['bz']),
				'perm'=>perm_binPerm::getGroupPower('all'),
				);
		}
	}elseif($value['type']=='storage'){
		foreach(DB::fetch_all("select id,access_id,bz,cloudname,dateline,bucket from ".DB::table($value['dname'])." where bz='{$value[bz]}' and uid='{$_G[uid]}'") as $value1){
			$value1['access_id']=authcode($value1['access_id'],'DECODE',$value1['bz'])?authcode($value1['access_id'],'DECODE',$value1['bz']):$value1['access_id'];
			if(!$value1['cloudname']) $value1['cloudname']=$value['name'].':'.($value1['bucket']?$value1['bucket']:cutstr($value1['access_id'], 4, $dot = ''));
			$value1['bz']=$value['bz'];
			if($value1['bucket']) $value1['bucket'].='/';
			$value1['icoid']=md5($value['bz'].':'.$value1['id'].':'.$value1['bucket']);
			$value1['img']='dzz/images/default/system/'.$value['bz'].'.png';
			$mycloud[]=$value1;
			
			$icosdata[md5($value['bz'].':'.$value1['id'].':'.$value1['bucket'])]=array(
				'icoid'=>md5($value['bz'].':'.$value1['id'].':'.$value1['bucket']),
				'name'=>$value1['cloudname'],
				'img'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':'.$value1['bucket'],
				'type'=>'storage',
				'oid'=>md5($value['bz'].':'.$value1['id'].':'.$value1['bucket']),
				
			);
			$folderdata[md5($value['bz'].':'.$value1['id'].':'.$value1['bucket'])]=array(
				'fid'=>md5($value['bz'].':'.$value1['id'].':'.$value1['bucket']),
				'pfid'=>0,
				'fname'=>$value1['cloudname'],
				'icon'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':'.$value1['bucket'],
				'type'=>'storage',
				'fsperm'=>($value1['bucket'])?'0':perm_FolderSPerm::flagPower($value['bz'].'_root'),
				'perm'=>perm_binPerm::getGroupPower('all'),
				);
		}
	
	}elseif($value['type']=='ftp'){
		
		foreach(DB::fetch_all("select id,bz,cloudname,dateline from ".DB::table($value['dname'])." where bz='{$value[bz]}' and uid='{$_G[uid]}'") as $value1){
			$value1['bz']=$value['bz'];
			$value1['icoid']=md5($value['bz'].':'.$value1['id'].':');
			$value1['img']='dzz/images/default/system/'.$value['bz'].'.png';
			$mycloud[]=$value1;
			
			$icosdata[md5($value['bz'].':'.$value1['id'].':')]=array(
				'icoid'=>md5($value['bz'].':'.$value1['id'].':'),
				'name'=>$value1['cloudname'],
				'img'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':',
				'type'=>'ftp',
				'oid'=>md5($value['bz'].':'.$value1['id'].':'),
				
			);
			$folderdata[md5($value['bz'].':'.$value1['id'].':')]=array(
				'fid'=>md5($value['bz'].':'.$value1['id'].':'),
				'pfid'=>0,
				'fname'=>$value1['cloudname'],
				'icon'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':',
				'type'=>'ftp',
				'fsperm'=>perm_FolderSPerm::flagPower($value['bz']),
				'perm'=>perm_binPerm::getGroupPower('all'),
				);
		}
	}elseif($value['type']=='disk'){
		
		foreach(DB::fetch_all("select id,bz,cloudname,dateline from ".DB::table($value['dname'])." where bz='{$value[bz]}' and uid='{$_G[uid]}'") as $value1){
			$value1['bz']=$value['bz'];
			$value1['icoid']=md5($value['bz'].':'.$value1['id'].':');
			$value1['img']='dzz/images/default/system/'.$value['bz'].'.png';
			$mycloud[]=$value1;
			
			$icosdata[md5($value['bz'].':'.$value1['id'].':')]=array(
				'icoid'=>md5($value['bz'].':'.$value1['id'].':'),
				'name'=>$value1['cloudname'],
				'img'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':',
				'type'=>'disk',
				'oid'=>md5($value['bz'].':'.$value1['id'].':'),
				
			);
			$folderdata[md5($value['bz'].':'.$value1['id'].':')]=array(
				'fid'=>md5($value['bz'].':'.$value1['id'].':'),
				'pfid'=>0,
				'fname'=>$value1['cloudname'],
				'icon'=>'dzz/images/default/system/'.$value['bz'].'.png',
				'bz'=>$value['bz'].':'.$value1['id'].':',
				'path'=>$value['bz'].':'.$value1['id'].':',
				'type'=>'disk',
				'fsperm'=>perm_FolderSPerm::flagPower($value['bz']),
				'perm'=>perm_binPerm::getGroupPower('all'),
				);
		}
	
	}
}
//按创建时间排序时间排序
function sortcloud($a,$b){
	if ($a['dateline'] == $b['dateline']) {
        return 0;
    }
    return ($a['dateline'] > $b['dateline']) ? -1 : 1;

}
usort($mycloud,"sortcloud");

$icosdata_json=json_encode($icosdata);
$folderdata_json=json_encode($folderdata);
include template("connect_index");

?>
