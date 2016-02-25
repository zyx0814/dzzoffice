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

if($_GET['do']=='imageUpload'){
	
	include_once libfile('class/uploadhandler');
	$options=array( 'generate_response'=>false,
					'param_name'=>'upfile',
					'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
					'upload_dir' =>$_G['setting']['attachdir'].'cache/',
					'upload_url' => $_G['setting']['attachurl'].'cache/',
					);
	$upload_handler = new uploadhandler($options);
	
	$type = $_GET['type'];
    $editorId=$_GET['editorid'];
    if(!$info = $upload_handler->getFileInfo()){
		exit();
	}
    /**
     * 返回数据，调用父页面的ue_callback回调
     */
    if($type == "ajax"){
		echo DZZSCRIPT.'?mod=io&op=thumbnail&width=700&height=500&path='.dzzencode('attach::'.$info[0]['aid'] ).'&original=1&attach='.rawurlencode('attach::'.$info[0]['aid']);
	}elseif($type=='attach'){
		$info[0]['url']=DZZSCRIPT.'?mod=io&op=getStream&path='.dzzencode('attach::'.$info[0]['aid']).'&attach='.rawurlencode('attach::'.$info[0]['aid']);
		echo json_encode($info[0]);
    }else{
		$str='';
		foreach($info as $value){
			$value['state']='SUCCESS';
			if(in_array($value['filetype'],array('jpg','jpeg','png','gif','bmp'))){
			   $value['url']= DZZSCRIPT.'?mod=io&op=thumbnail&width=700&height=500&path='.dzzencode('attach::'.$value['aid'] ).'&original=1';
			   $str.= '<img class="attach-item image" path="'.rawurlencode('attach::'.$value['aid']).'" src="'.$value['url'].'" _src="'.$value['url'].'" />';
		   }else{
			   $value['url']= DZZSCRIPT.'?mod=io&op=download&path='.dzzencode('attach::'.$value['aid']).'&filename='.urlencode($value['filename']);
			   $value['img']=geticonfromext($value['filetype'],'');
			   $str.= '<p><span class="attach-item attach" path="'.rawurlencode('attach::'.$value['aid']).'"><img src="'.$value['img'].'" _src="'.$value['img'].'"  /><a href="'.$value['url'].'" _href="'.$value['url'].'" title="'.$value['filename'].'" target="_blank">'.$value['filename'].'</a></span></p>';
		   }
		}
        echo "<script>parent.UM.getEditor('". $editorId ."').execCommand('insertHtml','".$str."',true )</script>";
			
    }
	exit();
    
}
?>
