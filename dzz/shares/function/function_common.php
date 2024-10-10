<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if ( !defined( 'IN_DZZ' ) ) { //所有的php文件必须加上此句，防止被外部调用
	exit( 'Access Denied' );
}
function checkShare($share){
	if (!$share) {
		exit(json_encode(array('msg'=>lang('share_file_iscancled'))));
	}
	if ($share['status'] == -4) return array('msg'=>lang('shared_links_screened_administrator'));
	if ($share['status'] == -5) return array('msg'=>lang('sharefile_isdeleted_or_positionchange'));
	//判断是否过期
	if ($share['endtime'] && ($share['endtime']+60*60*24) < TIMESTAMP) {
		return array('msg'=>lang('share_link_expired'));
	}
	if ($share['times'] && $share['times'] <= $share['count']) {
		return array('msg'=>lang('link_already_reached_max_number'));
	}

	if ($share['status'] == -3) {
		return array('msg'=>lang('share_file_deleted'));
	}
	if ($share['password'] && (dzzdecode($share['password'],'',0,0) != authcode($_G['cookie']['pass_' . $sid]))) {
		return array('msg'=>lang('password_share_error'));
	}
	return array('msg'=>'success');
}
//获取文件的打开方式
function getOpenUrl($icoarr,$share){
	$ext=$icoarr['ext'];
	$dpath=$icoarr['dpath'];//(array('path'=>$icoarr['rid'],'perm'=>$share['perm']));
	$canedit=0;
	if($share['perm'] & 64){
		$canedit=1;
	}
	$candownload=0;
	if($share['perm'] & 256){
		$candownload=1;
	}
	$extall=C::t('app_open')->fetch_all_ext();
    $exts=array();
    $bzarr=explode(':',$icoarr['rbz']?$icoarr['rbz']:$icoarr['bz']);
    $bz=($bzarr[0]) ? $bzarr[0]:'dzz';
    foreach($extall as $value){
        if(!isset($exts[$value['ext']]) || $value['isdefault']) $exts[$value['ext']]=$value;
    }

	if(isset($exts[$bz.':'.$ext])){
		$data=$exts[$bz.':'.$ext];
	}elseif($exts[$ext]){
		$data=$exts[$ext];
	}elseif($exts[$icoarr['type']]){
		$data=$exts[$icoarr['type']];
	}else $data=array();
	if($data){
		$url=$data['url'];
		if($icoarr['type']=='image' || strpos($url,'dzzjs:OpenPicWin')!==false){//dzzjs形式时
			return array('type'=>'image','url'=>$icoarr['url']);
		}else{
			//替换参数
			//$url=preg_replace("/{(\w+)}/i",'', $url);
			//替换参数
			$url=preg_replace_callback("/{(\w+)}/i", function($matches) use($ext,$dpath){
				$key=$matches[1];
				if($key=='path'){
					return $dpath;
				}elseif($key=='ext'){
					return $ext;
				}else{
					return '';
				}
			}, $url);
			//添加path参数；
			if(strpos($url,'?')!==false  && strpos($url,'path=')===false){
				$url.='&path=' . dzzencode('preview_' . $icoarr['rid']);
			}
			//$url = $_G['siteurl'].$url;
			return array('type'=>'attach','url'=>$url,'canedit'=>$data['canedit']);
		}
		
	}else{//没有可用的打开方式，转入下载；
		$sid=dzzencode($share['id'],'',0,0);
		if($candownload){
			return array('type'=>'download','url'=>'index.php?mod=shares&op=download&operation=download&sid='.$sid.'&filename='.$icoarr['name'].'&path='.$dpath);
		}else{
			return array('type'=>'download','url'=>'index.php?mod=shares&op=download&operation=download&sid='.$sid.'&filename='.$icoarr['name'].'&path='.$dpath);
		}
		//IO::download($path,$_GET['filename']);
	}
}