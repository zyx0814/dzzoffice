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
//获取文件的打开方式
function getOpenUrl($icoarr,$share){
	$ext=$icoarr['ext'];
	$dpath=$icoarr['dpath'];
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
				$url.='&path=' . dzzencode('sid:'.$share['id'].'_' . $icoarr['rid']);
			}
			return array('type'=>'attach','url'=>$url,'canedit'=>$data['canedit']);
		}
		
	}else{//没有可用的打开方式，转入下载；
		$sid=dzzencode($share['id'],'',0,0);
		if($candownload){
			return array('type'=>'download','url'=>'index.php?mod=shares&op=download&operation=download&sid='.$sid.'&filename='.$icoarr['name'].'&path='.$dpath);
		}else{
			return array('type'=>'download','url'=>'index.php?mod=shares&op=download&operation=download&sid='.$sid.'&filename='.$icoarr['name'].'&path='.$dpath);
		}
	}
}