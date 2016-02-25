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
include_once libfile('function/cache');
include_once libfile('function/organization');
$operation=empty($_GET['operation'])?'basic':trim($_GET['operation']);
$setting = C::t('setting')->fetch_all(null);

if(!submitcheck('settingsubmit')) {
	if($operation == 'basic') {
		$navtitle='基本设置';
		$spacesize=DB::result_first("select maxspacesize from ".DB::table('usergroup_field')." where groupid='9'");
		include_once libfile('function/organization');
		
		if($setting['defaultdepartment']){
			 $patharr=getPathByOrgid($setting['defaultdepartment']);
			 $defaultdepartment=implode(' - ',array_reverse($patharr));
			
		}
		if(empty($defaultdepartment)){
			 $defaultdepartment='不加入机构或部门';
			 $setting['defaultdepartment']='other';
		}
		//$orgtree=getDepartmentOption(0);
	} elseif($operation == 'qywechat') {	
		if($setting['synorgid']){
			 $patharr=getPathByOrgid($setting['synorgid']);
			 $syndepartment=implode(' - ',array_reverse($patharr));
			
		}
		if(empty($syndepartment)){
			 $syndepartment='全部用户';
			 $setting['syndepartment']='0';
		}
	} elseif($operation == 'desktop') {	
	    if($setting['desktop_default'] && !is_array($setting['desktop_default'])){
			$setting['desktop_default']=unserialize($setting['desktop_default']);
		}
		if(!$setting['desktop_default']){
			$setting['desktop_default']=array('iconview'=>2,
			                                  'taskbar'=>'bottom',
											  'iconposition'=>0,
											  'direction'=>0,
											  );
		}
		if($_G['setting']['dzz_iconview']){
			$iconview=$_G['setting']['iconview'];
		}else{
			$iconview=C::t('iconview')->fetch_all();
		}
	} elseif($operation == 'upload') {
		$setting['maxChunkSize']=round($setting['maxChunkSize']/(1024*1024),2);
		$navtitle='上传设置';
		$setting['unRunExts']=implode(',',dunserialize($setting['unRunExts']));
		$usergroups=DB::fetch_all("select f.*,g.grouptitle from %t f LEFT JOIN %t g ON g.groupid=f.groupid where f.groupid IN ('1','2','9') order by groupid DESC",array('usergroup_field','usergroup'));
	} elseif($operation == 'at') {
		$navtitle='@部门设置';
		$setting['at_range']=dunserialize($setting['at_range']);
		$usergroups=DB::fetch_all("select f.*,g.grouptitle from %t f LEFT JOIN %t g ON g.groupid=f.groupid where f.groupid IN ('1','2','9') order by groupid DESC",array('usergroup_field','usergroup'));
	} elseif($operation == 'access') {
		$navtitle='注册和访问';
		/*if($setting['welcomemsg'] == 1) {
			$welcomemsg[] = '1';
		} elseif($setting['welcomemsg'] == 2) {
			$welcomemsg[] = '2';
		} elseif($setting['welcomemsg'] == 3) {
			$welcomemsg[] = '1';
			$welcomemsg[] = '2';
		} else {
			$welcomemsg[] = '0';
		}*/
		$setting['strongpw'] = dunserialize($setting['strongpw']);
	} elseif($operation == 'qqlogin') {
		$navtitle='QQ登陆设置';
	} elseif($operation == 'datetime'){
		$navtitle='时间和日期';
		$checktimeformat = array($setting['timeformat'] == 'H:i' ? 24 : 12 => 'checked');
		$setting['userdateformat'] = dateformat($setting['userdateformat']);
		$setting['dateformat'] = dateformat($setting['dateformat']);
		$timezones= array(
			'' => '选择公共时区',
			'-12' => '(GMT -12:00) 埃尼威托克岛, 夸贾林环礁',
			'-11' => '(GMT -11:00) 中途岛, 萨摩亚群岛',
			'-10' => '(GMT -10:00) 夏威夷',
			'-9' => '(GMT -09:00) 阿拉斯加',
			'-8' => '(GMT -08:00) 太平洋时间(美国和加拿大), 提华纳',
			'-7' => '(GMT -07:00) 山区时间(美国和加拿大), 亚利桑那',
			'-6' => '(GMT -06:00) 中部时间(美国和加拿大), 墨西哥城',
			'-5' => '(GMT -05:00) 东部时间(美国和加拿大), 波哥大, 利马, 基多',
			'-4' => '(GMT -04:00) 大西洋时间(加拿大), 加拉加斯, 拉巴斯',
			'-3.5' => '(GMT -03:30) 纽芬兰',
			'-3' => '(GMT -03:00) 巴西利亚, 布宜诺斯艾利斯, 乔治敦, 福克兰群岛',
			'-2' => '(GMT -02:00) 中大西洋, 阿森松群岛, 圣赫勒拿岛',
			'-1' => '(GMT -01:00) 亚速群岛, 佛得角群岛 [格林尼治标准时间] 都柏林, 伦敦, 里斯本, 卡萨布兰卡',
			'0' => '(GMT) 卡萨布兰卡, 都柏林, 爱丁堡, 伦敦, 里斯本, 蒙罗维亚',
			'1' => '(GMT +01:00) 柏林, 布鲁塞尔, 哥本哈根, 马德里, 巴黎, 罗马',
			'2' => '(GMT +02:00) 赫尔辛基, 加里宁格勒, 南非, 华沙',
			'3' => '(GMT +03:00) 巴格达, 利雅得, 莫斯科, 奈洛比',
			'3.5' => '(GMT +03:30) 德黑兰',
			'4' => '(GMT +04:00) 阿布扎比, 巴库, 马斯喀特, 特比利斯',
			'4.5' => '(GMT +04:30) 坎布尔',
			'5' => '(GMT +05:00) 叶卡特琳堡, 伊斯兰堡, 卡拉奇, 塔什干',
			'5.5' => '(GMT +05:30) 孟买, 加尔各答, 马德拉斯, 新德里',
			'5.75' => '(GMT +05:45) 加德满都',
			'6' => '(GMT +06:00) 阿拉木图, 科伦坡, 达卡, 新西伯利亚',
			'6.5' => '(GMT +06:30) 仰光',
			'7' => '(GMT +07:00) 曼谷, 河内, 雅加达',
			'8' => '(GMT +08:00) 北京, 香港, 帕斯, 新加坡, 台北',
			'9' => '(GMT +09:00) 大阪, 札幌, 首尔, 东京, 雅库茨克',
			'9.5' => '(GMT +09:30) 阿德莱德, 达尔文',
			'10' => '(GMT +10:00) 堪培拉, 关岛, 墨尔本, 悉尼, 海参崴',
			'11' => '(GMT +11:00) 马加丹, 新喀里多尼亚, 所罗门群岛',
			'12' => '(GMT +12:00) 奥克兰, 惠灵顿, 斐济, 马绍尔群岛');
	}elseif($operation=='sec'){
		$navtitle='验证码设置';
		$seccodecheck = /*$secreturn =*/ 1;
		$sectpl = '<br /><sec>: <sec><sec>';
		$checksc = array();
		$setting['seccodedata'] = dunserialize($setting['seccodedata']);
		$setting['reginput'] = dunserialize($setting['reginput']);
		$seccodestatus[1]=$setting['seccodestatus'] & 1;
		$seccodestatus[2]=$setting['seccodestatus'] & 2;
		$seccodestatus[3]=$setting['seccodestatus'] & 4;
	} elseif($operation == 'desktop') {
		$navtitle='桌面设置';
	} elseif($operation == 'loginset') {
		$navtitle='登录页设置';
		if($setting['loginset'] && !is_array($setting['loginset'])){
			$setting['loginset']=unserialize($setting['loginset']);
		}
	} elseif($operation == 'smiley') {
		$navtitle='表情设置';
	} elseif($operation == 'mail') {
		$navtitle='邮件设置';
		$setting['mail'] = dunserialize($setting['mail']);
		$passwordmask = $setting['mail']['auth_password'] ? $setting['mail']['auth_password']{0}.'********'.substr($setting['mail']['auth_password'], -2) : '';
		$smtps=array();
		foreach($setting['mail']['smtp'] as $id => $smtp) {
			$smtp['authcheck'] = $smtp['auth'] ? 'checked' : '';
			$smtp['auth_password'] = $smtp['auth_password'] ? $smtp['auth_password']{0}.'********'.substr($smtp['auth_password'], -2) : '';
			$smtps[$id]=$smtp;
		}
	} elseif($operation == 'censor') {
		$navtitle='敏感词设置';
		loadcache('censor');
		$badwords=$_G['cache']['censor']['words'];
		$replace=empty($_G['cache']['censor']['replace'])?'*':$_G['cache']['censor']['replace'];
	}
}else{
	
	$settingnew=$_GET['settingnew'];
	if($operation == 'basic') {
		$settingnew['bbname']=$settingnew['sitename'];
	}elseif($operation == 'upload') {
		if($settingnew['unRunExts']) $settingnew['unRunExts']=explode(',',trim($settingnew['unRunExts'],','));
		else $settingnew['unRunExts']=array();
		if(!in_array('php',$settingnew['unRunExts'])) $settingnew['unRunExts'][]='php';
		$settingnew['maxChunkSize']=intval($settingnew['maxChunkSize']*1024*1024);
		$group=$_GET['group'];
		foreach($group as $key=> $value){
			C::t('usergroup_field')->update(intval($key),array('maxspacesize'=>intval($value['maxspacesize']),'maxattachsize'=>intval($value['maxattachsize']),'attachextensions'=>trim($value['attachextensions'])));
		}
		include_once libfile('function/cache');
		updatecache('usergroups');
	}elseif($operation=='mail'){
		$setting['mail'] = dunserialize($setting['mail']);
		$oldsmtp = $settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp'] : $settingnew['mail']['esmtp'];
		$deletesmtp = $settingnew['mail']['mailsend'] != 1 ? ($settingnew['mail']['mailsend'] == 3 ? $settingnew['mail']['smtp']['delete'] : $settingnew['mail']['esmtp']['delete']) : array();
		
		$settingnew['mail']['smtp'] = array();
		foreach($oldsmtp as $id => $value) {
			if((empty($deletesmtp) || !in_array($id, $deletesmtp)) && !empty($value['server']) && !empty($value['port'])) {
				$passwordmask = $setting['mail']['smtp'][$id]['auth_password'] ? $setting['mail']['smtp'][$id]['auth_password']{0}.'********'.substr($setting['mail']['smtp'][$id]['auth_password'], -2) : '';
				$value['auth_password'] = $value['auth_password'] == $passwordmask ? $setting['mail']['smtp'][$id]['auth_password'] : $value['auth_password'];
				$settingnew['mail']['smtp'][] = $value;
			}
		}
	
		if(!empty($_GET['newsmtp'])) {
			foreach($_GET['newsmtp']['server'] as $id => $server) {
				if(!empty($server) && !empty($_GET['newsmtp']['port'][$id])) {
					$settingnew['mail']['smtp'][] = array(
							'server' => $server,
							'port' => $_GET['newsmtp']['port'][$id] ? intval($_GET['newsmtp']['port'][$id]) : 25,
							'auth' => $_GET['newsmtp']['auth'][$id] ? 1 : 0,
							'from' => $_GET['newsmtp']['from'][$id],
							'auth_username' => $_GET['newsmtp']['auth_username'][$id],
							'auth_password' => $_GET['newsmtp']['auth_password'][$id]
						);
				}

			}
		}
	}elseif($operation=='access'){
		isset($settingnew['reglinkname']) && empty($settingnew['reglinkname']) && $settingnew['reglinkname'] = '立即注册';
		$settingnew['pwlength'] = intval($settingnew['pwlength']);
		$settingnew['regstatus'] = intval($settingnew['regstatus']);
		
		/*if(in_array('open', $settingnew['regstatus']) && in_array('invite', $settingnew['regstatus'])) {
			$settingnew['regstatus'] = 3;
		} elseif(in_array('open', $settingnew['regstatus'])) {
			$settingnew['regstatus'] = 1;
		} elseif(in_array('invite', $settingnew['regstatus'])) {
			$settingnew['regstatus'] = 2;
		} else {
			$settingnew['regstatus'] = 0;
		}*/
		/*$settingnew['welcomemsg'] = (array)$settingnew['welcomemsg'];
		if(in_array('1', $settingnew['welcomemsg']) && in_array('2', $settingnew['welcomemsg'])) {
			$settingnew['welcomemsg'] = 3;
		} elseif(in_array('1', $settingnew['welcomemsg'])) {
			$settingnew['welcomemsg'] = 1;
		} elseif(in_array('2', $settingnew['welcomemsg'])) {
			$settingnew['welcomemsg'] = 2;
		} else {
			$settingnew['welcomemsg'] = 0;
		}*/

		if(empty($settingnew['strongpw'])) {
			$settingnew['strongpw'] = array();
		}
	}elseif($operation=='datetime'){
		if(isset($settingnew['timeformat'])) {
			$settingnew['timeformat'] = $settingnew['timeformat'] == '24' ? 'H:i' : 'h:i A';
		}
		if(isset($settingnew['dateformat'])) {
			$settingnew['dateformat'] = dateformat($settingnew['dateformat'], 'format');
		}
	}elseif($operation=='sec'){	
		$settingnew['seccodestatus'] = bindec(intval($settingnew['seccodestatus'][3]).intval($settingnew['seccodestatus'][2]).intval($settingnew['seccodestatus'][1]));
		
	}elseif($operation=='qqlogin'){
		if(empty($settingnew['qq_appid']) ||  empty($settingnew['qq_appkey'])){
			$settingnew['qq_login']=0;
		}
		
	}elseif($operation=='censor'){	
		$data=array('replace'=>trim($_GET['replace']),
					'words'=>$_GET['badwords']);
		savecache('censor',$data);
		showmessage('do_success',dreferer());
	} elseif($operation == 'loginset') {
		if($back=trim($settingnew['loginset']['background'])){
			if(strpos($back,'#')===0){
				$settingnew['loginset']['bcolor']=$back;
			}else{
				$arr=explode('.',$back);
				$ext=array_pop($arr);
				if($ext && in_array(strtolower($ext),array('jpg','jpeg','gif','png'))){
					$settingnew['loginset']['img']=$back;
					$settingnew['loginset']['bcolor']='rgb(58, 110, 165)';
				}else{
					$settingnew['loginset']['url']=$back;
					$settingnew['loginset']['bcolor']='rgb(58, 110, 165)';
				}
			}
		}else{
			$settingnew['loginset']['bcolor']='rgb(58, 110, 165)';
		}
	
	}elseif($operation == 'qywechat') {
		switch($_GET['fbind']){
			case 'bind':
				$wechat=new qyWechat(array('appid'=>$settingnew['CorpID'],'appsecret'=>$settingnew['CorpSecret']));
				if(!$wechat->checkAuth()){
					showmessage('验证不成功,errCode：'.$wechat->errCode.'; errMsg:'.$wechat->errMsg,dreferer());
				}
				if(empty($setting['token_0'])) $settingnew['token_0']=random(8);
				if(empty($setting['encodingaeskey_0'])) $settingnew['encodingaeskey_0']=random(43);
				break;
			case 'unbind':
				$settingnew['CorpID']='';
				$settingnew['CorpSecret']='';
				break;
		}
	}
	
	$updatecache = FALSE;
	$settings = array();
	foreach($settingnew as $key => $val) {
		if($setting[$key] != $val) {
			$updatecache = TRUE;
			if(in_array($key, array('timeoffset','regstatus',   'oltimespan', 'seccodestatus'))) {
				$val = (float)$val;
			}

			$settings[$key] = $val;
		}
	}

	if($settings) {
		C::t('setting')->update_batch($settings);
	}
	if($updatecache) {
		updatecache('setting');
	}
	if($operation=='upload'){
		dfsockopen($_G['siteurl'].'misc.php?mod=setunrun',0, '', '', FALSE, '',1);
	}
	showmessage('do_success',dreferer());
}
function dateformat($string, $operation = 'formalise') {
	$string = dhtmlspecialchars(trim($string));
	$replace = $operation == 'formalise' ? array(array('n', 'j', 'y', 'Y'), array('mm', 'dd', 'yy', 'yyyy')) : array(array('mm', 'dd', 'yyyy', 'yy'), array('n', 'j', 'Y', 'y'));
	return str_replace($replace[0], $replace[1], $string);
}
include template('main');

?>
