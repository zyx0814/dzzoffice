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
require_once libfile('function/organization');
require_once DZZ_ROOT.'./core/class/class_PHPExcel.php';
if($_G['adminid']!=1) showmessage('没有权限，只有系统管理员才能导出用户',dreferer());
	$h0=array('username'=>'姓名','email'=>'邮箱','nickname'=>'用户名','birth'=>'出生日期','gender'=>'性别','mobile'=>'手机','weixinid'=>'微信号','orgname'=>'所属部门','job'=>'部门职位');
	$h1=getProfileForImport();
	$h0=array_merge($h0,$h1);
$orgid=intval($_GET['orgid']);
if(!submitcheck('exportsubmit')){
	$orgpath=C::t('organization')->getPathByOrgid($orgid);
	if(empty($orgpath)) $orgpath='请选择导出范围';
	
	//默认选中
	$open=array();
	$patharr=getPathByOrgid($orgid);
	$arr=array_reverse(array_keys($patharr));
	array_pop($arr);
	$count=count($arr);
	if($open[$arr[$count-1]]){
		if(count($open[$arr[$count-1]])>$count) $open[$arr[count($arr)-1]]=$arr;
	}else{
		$open[$arr[$count-1]]=$arr;
	}
	$openarr=json_encode(array('orgid'=>$open));
	
	include template('export');
	exit();
}else{
	if(!is_array($_GET['item'])) showmessage("请选择导出项目",dreferer());
	foreach($h0 as $key=>$value){
		if(!in_array($key,$_GET['item'])) unset($h0[$key]);
	}
	$title='';
	if($org=C::t('organization')->fetch($orgid)){
		$orgids=getOrgidTree($org['orgid']);
		if($org['forgid']>0){
			$toporgid=C::t('organization')->getTopOrgid($orgid);
			$toporg=C::t('organization')->fetch($toporgid);
			$title=$_G['setting']['sitename'].'-'.$toporg['orgname'].'-'.$org['orgname'];
		}else{
			$title=$_G['setting']['sitename'].'-'.$org['orgname'];
		}
	}else{
		$title=$_G['setting']['sitename'];
	}
	
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator($_G['username'])
								 ->setTitle($title.' - 人员信息表 - DzzOffice')
								 ->setSubject($title.' - 人员信息表')
								 ->setDescription($title.' - 人员信息表 Export By DzzOffice  '.date('Y-m-d H:i:s'))
								 ->setKeywords($title.' - 人员信息表')
								 ->setCategory("人员信息表");
	$list=array();
	// Create a first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	$j=0;
	foreach($h0 as $key =>$value){
		$index=getColIndex($j).'1';
		$objPHPExcel->getActiveSheet()->setCellValue($index,$value);
		$list[1][$index]=$value;
		$j++;
	}
	$i=2;
	$wheresql=1;
	if($orgid){
		$uids=C::t('organization_user')->fetch_uids_by_orgid($orgids);
		$wheresql=" where  uid IN (".dimplode($uids).")";
	}else{
		$wheresql=" where 1 ";
	}
	
	foreach(DB::fetch_all("select * from %t $wheresql",array('user')) as $user){
		
		$profile=C::t('user_profile1')->fetch_all($user['uid']);
		if($profile) $value=array_merge($user,$profile[$user['uid']]);
		else $value=$user;
		if($value['birthyear'] && $value['birsthmonth'] && $value['birthday']) $value['birth']=$value['birthyear'] .'-'. $value['birsthmonth'] .'-'. $value['birthday'];
		if($value['gender']){
			if($value['gender']==2) $value['gender']='女';
			elseif($value['gender']==1) $value['gender']='男';
			else $value['gender']='';
		}
		//获取用户的部门和职位
		if($orgids=C::t('organization_user')->fetch_orgids_by_uid($value['uid'])){
			$k=0;
			foreach($orgids as $key=> $gid){
				$orgpath=C::t('organization')->getPathByOrgid($gid);
				$value['orgname']=str_replace('-','/',$orgpath);
				if(empty($value['orgname'])) continue;
				if($job=DB::fetch_first("select j.name from %t u LEFT JOIN %t j ON u.jobid=j.jobid  where u.orgid=%d and u.uid=%d",array('organization_user','organization_job',$gid,$user['uid']))) $value['job']=$job['name'];
				$j=0;
				foreach($h0 as $key1 =>$fieldid){
					$index=getColIndex($j).intval($i+$k);
					$objPHPExcel->getActiveSheet()->setCellValue($index,$value[$key1]);
					$j++;
					$list[$i+$k][$index]=$value[$key1];
				}
				$k++;								   
			}	
			$i+=$k-1;
		}else{
			$j=0;
			foreach($h0 as $key1 =>$fieldid){
				$index=getColIndex($j).($i);
				$objPHPExcel->getActiveSheet()->setCellValue($index,$value[$key1]);
				$j++;
				$list[$i][$index]=$value[$key1];
			}
		}
		$i++;	
	}
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$filename=$_G['setting']['attachdir'].'./cache/'.random(5).'.xlsx';
	$objWriter->save($filename);
	
	
	$name=$title.' - 人员信息表.xlsx';
	$name = '"'.(strtolower(CHARSET) == 'utf-8' && (strexists($_SERVER['HTTP_USER_AGENT'], 'MSIE') || strexists($_SERVER['HTTP_USER_AGENT'], 'rv:11')) ? urlencode($name) : $name).'"';
	
	$filesize=filesize($filename);
	$chunk = 10 * 1024 * 1024; 
	if(!$fp = @fopen($filename, 'rb')) {
		exit('导出失败！');
	}
	dheader('Date: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', TIMESTAMP).' GMT');
	dheader('Content-Encoding: none');
	dheader('Content-Disposition: attachment; filename='.$name);
	dheader('Content-Type: application/octet-stream');
	dheader('Content-Length: '.$filesize);
	@ob_end_clean();if(getglobal('gzipcompress')) @ob_start('ob_gzhandler');
	while (!feof($fp)) { 
		echo fread($fp, $chunk);
		@ob_flush();  // flush output
		@flush();
	}
	@unlink($filename);
	exit();
}
function getColIndex($index){
	$string="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$ret='';
	if($index>255) return '';
	for($i=0;$i<floor($index/strlen($string));$i++){
		$ret=$string[$i];
	}
	$ret.=$string[($index%(strlen($string)))];
	return $ret;
}
function getProfileForImport(){
	global $_G;
	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	$profilesetting=$_G['cache']['profilesetting'];
	$ret=array();
	foreach($profilesetting as $key=> $value){
		if(in_array($key,array('department','realname','gender','birthyear','birthmonth','birthday','constellation','zodiac'))) continue;
		elseif($value['formtype']=='file') continue;
		elseif($value['formtype']=='select' || $value['formtype']=='radio'){
			$ret[$key]=$value['title']/*.($value['choices']?'('.preg_replace("/[\r\n]/i",'|',$value['choices']).')':'')*/;
		}elseif( $value['formtype']=='checkbox'){
			$ret[$key]=$value['title']/*.($value['choices']?'('.preg_replace("/[\r\n]/i",'-',$value['choices']).')':'')*/;
		}else{	
			$ret[$key]=$value['title'];
		}
	}
	return $ret;
}
?>
