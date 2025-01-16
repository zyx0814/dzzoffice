<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
if (!defined('IN_DZZ')) {
	exit('Access Denied');
}
global $_G;
if($_GET['sid']){
    $sid=dzzdecode($_GET['sid']);
    $share=C::t('shares')->fetch($sid);
    if (!$share) {
        showmessage('share_file_iscancled');
    }
    $canview=1;
    $candownload = 1;
    if ($share['perm']) {
        $perms = array_flip(explode(',', $share['perm'])); // 将权限字符串转换为数组
        if (isset($perms[3]) && !$_G['uid']) { // 3 表示仅登录访问
            Hook::listen('check_login');
        } elseif (isset($perms[2])) { // 2 表示禁用预览权限
            $canview = 0;
        }
        if (isset($perms[1])) {
            $candownload = 0; // 下载权限被禁用
        }
    }
} else{
    showmessage('share_file_iscancled');
}
$open = false;
if(isset($_GET['path'])){//打开文件夹
	$morepath = $_GET['path'];
	$rids = array();
    if(preg_match('/^\d+$/',$morepath)){
    	foreach (DB::fetch_all('select rid from %t where pfid = %d and isdelete < 1',array('resources',$morepath)) as $v){
    		$rids[] = $v['rid'];
		}
	}else{
        $dpath = explode(',',$_GET['path']);
        foreach($dpath as $v){
            $rids[] = dzzdecode($v);
        }
	}
	$open = true;
}elseif(isset($_GET['morepath'])){//加载更多
	$morepath = $_GET['morepath'];
    if(preg_match('/^\d+$/',$morepath)){
        foreach (DB::fetch_all('select rid from %t where pfid = %d and isdelete < 1',array('resources',$morepath)) as $v){
            $rids[] = $v['rid'];
        }
    } else{
		$filepaths = $share['filepath'];
		$rids  = explode(',',$filepaths);
	}
}
if(isset($_GET['currentfolder']) && $_GET['currentfolder']){
    $currentfolder = true;
}else{
    $currentfolder = false;
}
$page = (isset($_GET['page'])) ? intval($_GET['page']):1;
$perpage = 20;
$start = ($page - 1) * $perpage;
$gets = array('mod' => MOD_NAME, 'sid' => $sid);
$theurl = BASESCRIPT . "?" . url_implode($gets);
$ordersql = '';
$asc = (isset($_GET['asc'])) ? intval($_GET['asc']):1;
$disp = (isset($_GET['disp'])) ? intval($_GET['disp']):0;
$order = ($asc > 0) ? 'ASC':'DESC';
switch ($disp) {
	case 0:
		$orderby = 'name';
		break;
	case 1:
		$orderby = 'size';
		break;
	case 2:
		$orderby = array('type', 'ext');
		break;
	case 3:
		$orderby = 'dateline';
		break;
}
if(is_array($orderby)){
	foreach($orderby as $key=>$value){
		$orderby[$key]=$value.' '.$order;
	}
	$ordersql=' ORDER BY '.implode(',',$orderby);
}elseif($orderby){
	$ordersql=' ORDER BY '.$orderby.' '.$order;
}
$limitsql = 'limit '.$start .','. ($perpage);
$params = array('resources',$rids);
$wheresql = " where rid in(%n)  and isdelete < 1";
$list = array();
$foldername = '';
$allrids = '';
if(!empty($rids)){
    if($currentfolder){
        $fileinfo = DB::fetch_first("select * from %t where rid = %s",array('resources',$rids[0]));
        $foldername = $fileinfo['name'];
        $allrids = dzzencode($fileinfo['rid']);
        $list = array();
    }else{
        //获取分享数据
        foreach(DB::fetch_all("select rid from %t $wheresql $ordersql $limitsql",$params) as $v){
            $fileinfo = getfileinfo($v['rid']);
            if($open && !$foldername){
                $foldername = DB::result_first("select fname from %t where fid = %d",array('folder',$fileinfo['pfid']));
            }
            if($fileinfo['type'] == 'folder' && $fileinfo['oid']) {
                $oid = $fileinfo['oid'];
                $fileinfo['dhpath'] = $oid;
				$fileinfo['contaions']= C::t('resources')->get_contains_by_fid($fileinfo['oid']);
                $fileinfo['filenum'] = $fileinfo['contaions']['contain'][0];
                $fileinfo['foldernum'] = $fileinfo['contaions']['contain'][1];
            } else {
                if ($_G['ismobile']) {
                    $opendata=getOpenUrl($fileinfo,$share);
                    $fileinfo['type']=$opendata['type'];
                    $fileinfo['url']=$opendata['url'];
                }
            }
            if ($fileinfo['type'] == 'image') {
                $fileinfo['img'] = DZZSCRIPT . '?mod=io&op=thumbnail&width=45&height=45&path=' . dzzencode('attach::' . $fileinfo['aid']);
            }
            $list[] = $fileinfo;
            $allrids .= dzzencode($val['rid']).',';
        }
        $allrids = substr($allrids,0,-1);
    }
}else{
    if($open && !$foldername){
        $foldername = DB::result_first("select fname from %t where fid = %d",array('folder',$morepath));
    }
}

if (count($list) >= $perpage) {
	$nextpage = $page + 1;
} else {
	$naxtpage = 0;
}
if($_G['ismobile']){
    include template('mobile/list_item');
}else{
    include template('list_item');
}
dexit();
?>
