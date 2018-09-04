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
if($_G['adminid']!=1) showmessage('orguser_import_user',dreferer());
require_once libfile('function/organization');
$do=trim($_GET['do']);
if($do=='importing'){
	//判断邮箱是否存在
	require_once libfile('function/user','','user');
	$email=trim($_GET['email']);
	$_GET['username']=addslashes(trim(stripslashes(trim($_GET['username']))));
	$_GET['username']=str_replace('...','',getstr($_GET['username'],30));
	$_GET['password']=empty($_GET['password'])?trim($_GET['pswdefault']):trim($_GET['password']);
	
	$_GET['weixinid']=addslashes(trim(stripslashes(trim($_GET['weixinid']))));
	$_GET['mobile']=addslashes(trim(stripslashes(trim($_GET['mobile']))));
	
	if(empty($email) || empty($_GET['username'])) exit(json_encode(array('error'=>lang('name_email_empty'))));
	if(!isemail($email)) exit(json_encode(array('error'=>'email'.lang('format_error'))));
	
	$isappend=intval($_GET['append']);
	$sendmail=intval($_GET['sendmail']);
	/*
	if($sendmail){ //随机密码时重新设置密码为随机数；
		$_GET['password']=random(8);
	}*/
	$exist=0;
	
	//检查用户是否已经存在
	if(($user=C::t('user')->fetch_by_email($email)) || ($user=C::t('user')->fetch_by_username($_GET['username']))){//用户已经存在时
		$uid=$user['uid'];
		$exist=1;
		if($isfounder=C::t('user')->checkfounder($user)) $isappend=1;//创始人不支持覆盖导入
		if($isappend){//增量添加，如果原先没有nickname,增加
			$appendfield=array();
			
			if($_GET['mobile'] && empty($user['phone'])){
				if(!preg_match("/^\d+$/",$_GET['mobile'])){
					exit(json_encode(array('error'=>lang('phone_number_illegal'))));
				}
				if(C::t('user')->fetch_by_phone($_GET['mobile']) ) {
					exit(json_encode(array('error'=>lang('user_phone_exist'))));
				}
				$appendfield['phone']=$_GET['mobile'];
				
			}
			if($_GET['weixinid'] && empty($user['weixinid'])){
				if(!preg_match("/^[a-zA-Z\d_]{5,}$/i",$_GET['weixinid'])){
					exit(json_encode(array('error'=>lang('weixin_illegal'))));
				}
				if(C::t('user')->fetch_by_weixinid($_GET['weixinid']) ) {
					exit(json_encode(array('error'=>lang('weixin_exist'))));
				}
				$appendfield['weixinid']=$_GET['weixinid'];
			}
			if($appendfield) C::t('user')->update($uid,$appendfield);
		}else{ //覆盖导入时，覆盖用户的姓名和密码
			$salt=substr(uniqid(rand()), -6);
			if(!check_username($_GET['username'])) exit(json_encode(array('error'=>lang('user_name_sensitive'))));
			$setarr=array('username'=>$_GET['username'],
						  'password'=>md5(md5($_GET['password']).$salt),
						  'salt'=>$salt
						  );
			
			if($_GET['mobile'] && $_GET['mobile']!=$user['phone']){
				if(!preg_match("/^\d+$/",$_GET['mobile'])){
					exit(json_encode(array('error'=>lang('phone_number_illegal'))));
				}
				if(C::t('user')->fetch_by_phone($_GET['mobile']) ) {
					exit(json_encode(array('error'=>lang('user_phone_exist'))));
				}
				$setarr['phone']=$_GET['mobile'];
				
			}
			if($_GET['weixinid'] && $_GET['weixinid']!=$user['weixinid']){
				if(!preg_match("/^[a-zA-Z\d_]{5,}$/i",$_GET['weixinid'])){
					exit(json_encode(array('error'=>lang('weixin_illegal'))));
				}
				if(C::t('user')->fetch_by_weixinid($_GET['weixinid']) ) {
					exit(json_encode(array('error'=>lang('weixin_exist'))));
				}
				$setarr['weixinid']=$_GET['weixinid'];
			}
			C::t('user')->update($uid,$setarr);
			if($sendmail){ //发送密码到用户邮箱，延时发送
				$email_password_message = lang('email_password_message', array(
						'sitename' => $_G['setting']['sitename'],
						'siteurl' => $_G['siteurl'],
						'email'=>$email,
						'password'=>$_GET['password']
					));
					
					if(!sendmail_cron("$email <$email>", lang('email_password_subject'), $email_password_message)) {
						runlog('sendmail', "$email sendmail failed.");
					}
			}
		}
	}else{ //新添用户
		if(!check_username($_GET['username'])) exit(json_encode(array('error'=>lang('user_name_sensitive'))));
		
		
		$user=uc_add_user($_GET['username'], $_GET['password'], $email);
		
		$uid=$user['uid'];
		if($uid<1)  exit(json_encode(array('error'=>lang('import_failure'))));
		$base = array(
				'uid' => $uid,
				'adminid' => 0,
				'groupid' =>9,
				'regdate' => TIMESTAMP,
				'emailstatus' => 1,
			);
			if($_GET['mobile']){
				if(!preg_match("/^\d+$/",$_GET['mobile'])){
				}elseif(C::t('user')->fetch_by_phone($_GET['mobile']) ) {
				}else{
					$base['phone']=$_GET['mobile'];
				}
			}
			if($_GET['weixinid']){
				if(!preg_match("/^[a-zA-Z\d_]{5,}$/i",$_GET['weixinid'])){
				}elseif(C::t('user')->fetch_by_weixinid($_GET['weixinid'])) {
				}else{
					$base['weixinid']=$_GET['weixinid'];
				}
			}
		C::t('user')->update($uid,$base);
		if($sendmail){ //发送密码到用户邮箱，延时发送
			$email_password_message = lang('email_password_message', array(
					'sitename' => $_G['setting']['sitename'],
					'siteurl' => $_G['siteurl'],
					'email'=>$email,
					'password'=>$_GET['password']
				));
				
				if(!sendmail_cron("$email <$email>", lang('email_password_subject'), $email_password_message)) {
					runlog('sendmail', "$email sendmail failed.");
				}
		}
	}
	//处理用户资料
	$_GET['gender']=trim($_GET['gender']);
	$_GET['birth']=trim($_GET['birth']);
	$_GET['telephone']=trim($_GET['telephone']);
	//$_GET['mobile']=trim($_GET['mobile']);
	
	if($exist && $isappend){ //增量时
		$oldprofile=C::t('user_profile')->fetch($uid);
		$profile=array();
		if(!empty($_GET['birth']) && empty($oldprofile['birthyear'])){
			 $birth=strtotime($_GET['birth']);
			 if($birth<TIMESTAMP && $birth>0){
				 $arr=getdate($birth);
				 $profile['birthyear']=$arr['year'];
				 $profile['birthmonth']=$arr['mon'];
				 $profile['birthday']=$arr['mday'];
			 }
		}
		if(!empty($_GET['gender']) && empty($oldprofile['gender'])){
			if($_GET['gender']==lang('man')) $profile['gender']=1;
			elseif($_GET['gender']==lang('woman')) $profile['gender']=2;
			else $profile['gender']=0;
		}
		
		if(!empty($_GET['telephone']) && empty($oldprofile['telephone'])){
			 $profile['telephone']=$_GET['telephone'];
		}
		foreach($_GET as $key=>$value){
			if(!empty($_GET[$key]) && empty($oldprofile[$key])){
				 if(checkprofile($key,$value))  $profile[$key]=$value;
			}
		}
		
		if($profile){
			$profile['uid']=$uid;
			C::t('user_profile')->insert($profile);
		}
	}else{
		$profile=array();
		if(!empty($_GET['birth'])){
			 $birth=strtotime(trim($_GET['birth']));
			 if($birth<TIMESTAMP && $birth>0){
				 $arr=getdate($birth);
				 $profile['birthyear']=$arr['year'];
				 $profile['birthmonth']=$arr['mon'];
				 $profile['birthday']=$arr['mday'];
			 }
		}
		if(!empty($_GET['gender'])){
			if($_GET['gender']==lang('man')) $profile['gender']=1;
			elseif($_GET['gender']==lang('woman')) $profile['gender']=2;
			else $profile['gender']=0;
		}
		
		if(!empty($_GET['telephone'])){
			 $profile['telephone']=$_GET['telephone'];
		}
		
		foreach($_GET as $key=>$value){
			if(checkprofile($key,$value))  $profile[$key]=$value;
		}
		
		$profile['uid']=$uid;
		
		C::t('user_profile')->insert($profile);
		 
		 //插入用户状态表
		$status = array(
			'uid' => $uid,
			'regip' => '',
			'lastip' => '',
			'lastvisit' => TIMESTAMP,
			'lastactivity' => TIMESTAMP,
			'lastsendmail' => 0
		);
		C::t('user_status')->insert($status, false, true);
	}
	//处理部门和职位
	$orgid=intval($_GET['orgid']);
	
	$_GET['orgname']=!empty($_GET['orgname'])?explode('/',$_GET['orgname']):array();
	$_GET['job']=!empty($_GET['job'])?explode('/',$_GET['job']):array();
	
	//创建机构和部门
	foreach($_GET['orgname'] as $key => $orgname){
		if(empty($orgname)) continue;
		if($porgid=DB::result_first("select orgid from %t where forgid=%d and orgname=%s",array('organization',$orgid,$orgname))){
			$orgid=$porgid;
		}else{
			$setarr=array('forgid'=>$orgid,
						  'orgname'=>$orgname,
						  'fid'=>0,
						  'disp'=>100,
						  'indesk'=>0,
						  'dateline'=>TIMESTAMP,
						);
			if($porgid=C::t('organization')->insert_by_orgid($setarr)){
				$orgid=$porgid;
			}
		}
	}
	
	//用户加入机构
	if($isappend){//增量导入时
		C::t('organization_user')->insert_by_orgid($orgid,$uid);
	}else{
		C::t('organization_user')->delete_by_uid($uid,0);
		C::t('organization_user')->insert_by_orgid($orgid,$uid);
	}
	if($orgid){
		foreach($_GET['job'] as $key =>$jobname){ //处理职位
			$jobid=0;
			if($pjobid=DB::result_first("select jobid from %t where orgid=%d and name=%s",array('organization_job',$orgid,$jobname))){
				$jobid=$pjobid;
			}else{
				$setarr=array('orgid'=>$orgid,
							  'name'=>$_GET['job'][$key],
							  'dateline'=>TIMESTAMP,
							  'opuid'=>$_G['uid']
							  );
				if($pjobid=C::t('organization_job')->insert($setarr,1)){
					$jobid=$pjobid;
				}
			}
			if($jobid){
				if($isappend){//增量导入时
					if(!DB::result_first("select COUNT(*) from %t where uid=%d and orgid=%d and jobid>0 ",array('organization_user',$uid,$orgid))){
						DB::update('organization_user',array('jobid'=>$jobid),"uid='{$uid}' and orgid='{$orgid}'");
					}
				}else{//覆盖导入时
					DB::update('organization_user',array('jobid'=>$jobid),"uid='{$uid}' and orgid='{$orgid}'");
				}
			}
		}
	}
	exit(json_encode(array('msg'=>'success')));
}elseif($do=='list'){
	require_once DZZ_ROOT.'./core/class/class_PHPExcel.php';
	$inputFileName = $_G['setting']['attachdir'].$_GET['file'];
	if(!is_file($inputFileName)){
		showmessage('orguser_import_user_table',ADMINSCRIPT.'?mod=orguser&op=import');
	}
	$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objPHPExcel = $objReader->load($inputFileName);
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
	//获取导入数据的字段
	$h0=array('username'=>lang('compellation'),'email'=>lang('email'),'nickname'=>lang('username'),'birth'=>lang('date_birth'),'gender'=>lang('gender'),'mobile'=>lang('cellphone'),'weixinid'=>lang('weixin'),'orgname'=>lang('category_department'),'job'=>lang('department_position'),'password'=>lang('user_login_password'));
	$h1=getProfileForImport();
	$h0=array_merge($h0,$h1);
	//获取可导入的用户资料
	$h=array();
	foreach($sheetData[1] as $key =>$value){
		$value=trim($value);
		foreach($h0 as $fieldid=>$title){
			if($title==$value){
				$h[$key]=$fieldid;
				break;
			}
		}
	}
	
	if(!in_array('username',$h)){
		showmessage('lack_required_fields_name');
	}elseif(!in_array('email',$h) && !in_array('username',$h)){
		 showmessage('lack_required_fields_name_email');
	} 
	if(!in_array('email',$h)){
		$h=array_merge(array('_'=>'email'),$h);
	}
	$list=array();
	foreach($sheetData as $key=> $value){
		if($key<=1) continue;
		$temp=array();
		foreach($value as $col =>$val){
			if(trim($val)=='') continue;
			if($h[$col]=='orgname'){
				$temp[$h[$col]][]=$val;
			}elseif($h[$col]=='job'){
				$temp[$h[$col]][]=$val;
			}elseif($key1=='birth'){
				$arr=explode('-',$value[$value1]);
				if(count($arr)==3){
					$temp[$key1]=dgmdate(strtotime($arr[2].'-'.$arr[0].'-'.$arr[1]),'Y-m-d');
				}else{
					$temp[$key1]=$val;
				}
			}else{
				if($h[$col]) $temp[$h[$col]]=$val;
			}
		}
		if(empty($temp['email'])) $temp['email']=random(10,true).'@163.com';
		if(isset($list[$temp['email']])){
			foreach($h as $key1 => $value1){
				if(!empty($temp[$key1])){
					$list[$temp['email']][$key1]=$temp[$key1];
				}
			}
		}else{
			if($temp) $list[$temp['email']]=$temp;
		}
	}
	$h=array_unique($h);
	$orgpath=C::t('organization')->getPathByOrgid($orgid);
	if(empty($orgpath)) $orgpath=lang('choose_import_agency_department');

	//默认选中
	$open=array();
	$patharr=getPathByOrgid($orgid);
	$arr=(array_keys($patharr));
	array_pop($arr);
	$count=count($arr);
	if($open[$arr[$count-1]]){
		if(count($open[$arr[$count-1]])>$count) $open[$arr[count($arr)-1]]=$arr;
	}else{
		$open[$arr[$count-1]]=$arr;
	}
	$openarr=json_encode(array('orgid'=>$open));
	include template('import_list');	
}else{
	if(submitcheck('importfilesubmit')){
		if($_FILES['importfile']['tmp_name']){
			$allowext=array('xls','xlsx');
			$ext=strtolower(substr(strrchr($_FILES['importfile']['name'], '.'), 1, 10));
			if(!in_array($ext,$allowext)) showmessage('orguser_import_xls_xlsx',dreferer());
			if($file=uploadtolocal($_FILES['importfile'],'cache','',array('xls','xlsx'))){
				$url=outputurl($_G['siteurl'].MOD_URL.'&op=import&do=list&file='.urlencode($file));
				@header("Location: $url");
				exit();
				showmessage('orguser_import_user_message',outputurl($_G['siteurl'].MOD_URL.'?mod=orguser&op=import&do=list&file='.urlencode($file)));
			}else{
				showmessage('orguser_import_tautology',dreferer());
			}
		}else{
			showmessage('orguser_import_user_message_table',dreferer());
		}
	}else{
		
		include template('import_guide');
	}
}
function checkprofile($fieldid,&$value){
	global $_G;
	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	$field = $_G['cache']['profilesetting'][$fieldid];
	if(empty($field) || in_array($fieldid, array('department','realname','gender','birthyear','birthmonth','birthday','birth','constellation','zodiac','email','nickname','password','orgname','job','username'))) {
		return false;
	}
	
	if($field['choices']) {
		$field['choices'] = explode("\n", $field['choices']);
	}
	if($field['formtype'] == 'text' || $field['formtype'] == 'textarea') {
		$value = getstr($value);
		if($field['size'] && strlen($value) > $field['size']) {
			return false;
		} else {
			$field['validate'] = !empty($field['validate']) ? $field['validate'] : ($_G['profilevalidate'][$fieldid] ? $_G['profilevalidate'][$fieldid] : '');
			if($field['validate'] && !preg_match($field['validate'], $value)) {
				return false;
			}
		}
	} elseif($field['formtype'] == 'checkbox' || $field['formtype'] == 'list') {
		$arr = array();
		$value=explode('\n',$value);
		foreach ($value as $op) {
			if(in_array(trim($op), trim($field['choices']))) {
				$arr[] = trim($op);
			}
		}
		$value = implode("\n", $arr);
		if($field['size'] && count($arr) > $field['size']) {
			return false;
		}
	} elseif($field['formtype'] == 'radio' || $field['formtype'] == 'select') {
		if(!in_array($value, $field['choices'])){
			return false;
		}
	}
	return true;
	
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
