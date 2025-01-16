<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2018/3/29
 * Time: 15:08
 */

//修改resources表和folder表pfid
$sql1 = 'ALTER TABLE '.DB::table('resources').' MODIFY pfid int(11) NOT NULL DEFAULT 0';
$sql2 = 'ALTER TABLE '.DB::table('folder').' MODIFY pfid int(11) NOT NULL DEFAULT 0';
DB::query($sql1);
DB::query($sql2);

//处理更新之后群组开关问题
DB::update('organization',array('manageon'=>1,'available'=>1,'syatemon'=>1),"1");
//修复继承权限
//判断是否已经存在pathkey字段,不存在则添加
if(!DB::result_first("DESCRIBE %t `perm_inherit`",array('folder'))){
    $sql = "alter table ".DB::table('folder') ." add column `perm_inherit` int(10) NOT NULL DEFAULT '0' after `perm` ";
    DB::query($sql);
}
if(!DB::result_first("DESCRIBE %t `pathkey`",array('resources_path'))){
    $sql = "alter table ".DB::table('resources_path') ." add column `pathkey` varchar(255) NOT NULL DEFAULT '' after `path` ";
    DB::query($sql);
}
function create_pathinfo_by_fid($fid, $appid = 0)
{
    $patharr = array();
    if (!$pathdata = C::t('folder')->get_folder_pathinfo_by_fid($fid)) return $patharr;
    $pathprefix = ($appid) ? "dzz:app_" . $appid . ":" : '';
    $path = '';
    $pathkey = '';
    foreach ($pathdata as $v) {
        $path .= $v['fname'] . '/';
        $pathkey .= '_' . $v['fid'] . '_-';
    }
    if (!$pathprefix) {
        $pathprefix = ($v['gid']) ? "dzz:gid_" . $v['gid'] . ":" : "dzz:uid_" . $v['uid'] . ":";
    }
    $patharr['path'] = $pathprefix.$path;
    $patharr['pathkey'] = substr($pathkey, 0, -1);
    return $patharr;
}
//修复resource_path表数据
foreach(DB::fetch_all("select f.fid,fa.svalue from %t f left join %t fa on f.fid = fa.fid and fa.skey = %s",array('folder','folder_attr','appid')) as $value){
    $_appid = false;
    if($value['svalue']){
        $_appid = $value['svalue'];
    }
	$pdata = create_pathinfo_by_fid($value['fid'],$_appid);
	if($pdata){
		if(!DB::result_first("select count(*) from %t where fid = %d",array('resources_path',$value['fid']))){
			$pdata['fid'] = $value['fid'];
			DB::insert('resources_path',$pdata);
		}else{
			DB::update('resources_path',$pdata,array('fid'=>$value['fid']));
		}
	}
	$perm_inherit=perm_check::getPerm1($value['fid']);
	DB::update('folder',array('perm_inherit'=>$perm_inherit),"fid='{$value[fid]}'");
}
//回收站数据处理
$rids = array();
foreach(DB::fetch_all("select rid from %t",array('resources')) as $v){
    $rids[] = $v['rid'];
}
$delfids = array();
$delrids = array();
foreach(DB::fetch_all("select rid,type,oid from %t where rid in(%n) and isdelete > 0",array('resources',$rids)) as $v){
    if($v['type'] == 'folder' && $v['oid']){
        $delfids[] = $v['oid'];
    }
    $delrids[] = $v['rid'];
}
$nodelrids = array_diff($rids,$delrids);

//更改resources表数据
if(count($delrids) > 0) DB::update("resources",array('pfid'=>-1),'rid in('.dimplode($delrids).')');

//更改folder表数据
if(count($delfids) > 0) DB::update("folder",array('pfid'=>-1),'fid in('.dimplode($delfids).')');

//清除回收站中的无用数据

if(count($nodelrids) > 0) DB::delete('resources_recyle','rid in('.dimplode($nodelrids).')');

//修复机构部门及群组管理员非成员数据问题
$orgadminer = array();
foreach(DB::fetch_all("select  uid,orgid from %t where 1", array('organization_admin')) as $v){
    if(DB::result_first("select count(*) from %t where orgid = %d and uid = %d",array('orgnazination_user',$v['orgid'],$v['uid']))){
        C::t('organization_user')->insert_by_orgid($v['orgid'],$v['uid'],0);
    }
}


DB::update("user".array('groupid'=>9),array('groupid'=>2));
$finish=true;