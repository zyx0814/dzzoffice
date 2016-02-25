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

include_once libfile('function/cache');
$dzz->reject_robot(); //阻止机器人访问
if($_G['adminid']!=1){
	showmessage('没有权限',dreferer());
}
$operation=trim($_GET['operation']);

$appid=C::t('app_market')->fetch_appid_by_mod('{dzzscript}?mod=feed',1);
$baseurl_info=DZZSCRIPT.'?mod=feed&op=wxsetting';
$baseurl_menu=DZZSCRIPT.'?mod=feed&op=wxsetting&operation=menu';
$baseurl_ajax=DZZSCRIPT.'?mod=feed&op=wxsetting&operation=ajax';
$setting=unserialize($_G['setting']['feed_wxsetting']);
if(empty($operation)){
	if(submitcheck('settingsubmit')){
		$setting['agentid']=intval($_GET['agentid']);
		$setting['appstatus']=intval($_GET['appstatus']);
		if($appid) C::t('wx_app')->update($appid,array('agentid'=>$setting['agentid'],'status'=>$setting['appstatus']));
		C::t('setting')->update('feed_wxsetting',$setting);
		updatecache('setting');
		showmessage('do_success',dreferer(),array(),array('alert'=>'right'));
	}else{
		$navtitle='微信设置';
		$navlast='微信设置';
		$settingnew=array();
		if(empty($setting['token'])) $settingnew['token']=$setting['token']=random(8);
		if(empty($setting['encodingaeskey'])) $settingnew['encodingaeskey']=$setting['encodingaeskey']=random(43);
		if($settingnew){
			C::t('setting')->update('feed_wxsetting',$setting);
			updatecache('setting');
		}
		$wxapp=array('appid'=>$appid,
					 'name'=>'消息中心',
					 'desc'=>'消息中心应用，通过它可以让员工分享工作信息、和同事交流。',
					 'icon'=>'dzz/feed/images/0.jpg',
					 'agentid'=> $setting['agentid'],
					 'token'=>$setting['token'],
					 'encodingaeskey'=>$setting['encodingaeskey'],
					 'host'=>$_SERVER['HTTP_HOST'],
					 'callback'=>$_G['siteurl'].'index.php?mod=feed&op=wxreply',
					 'otherpic'=>'dzz/feed/images/c.png',
					 'status'=>$setting['appstatus'],	//应用状态
					 'report_msg'=>1,                	//用户消息上报
					 'notify'=>0,                   	 //用户状态变更通知
					 'report_location'=>0,           	//上报用户地理位置
				);
		C::t('wx_app')->insert($wxapp,1,1);
	}
}elseif($operation=='menu'){
	$navtitle='菜单设置';
	$menu=$setting['menu']?($setting['menu']):'';
}elseif($operation=='ajax'){	
	if($_GET['action']=='setEventkey'){
		//支持的菜单事件
		$menu_select=array('click'=>array(),
							'link'=>array(
									$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=aboutme'=>'与我相关',
									$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=fromme'=>'我发布的',
									$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=atme'=>'@我的',
									$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=collect'=>'我收藏的',
									$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=replyme'=>'回复我的',
									$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=all'=>'全部动态'
							)
					);
		
		
		$json_menu_select=json_encode($menu_select);
		$type=trim($_GET['type']);
		$typetitle=array('click'=>'设置菜单KEY值','link'=>'设置菜单跳转链接');
		
	}elseif($_GET['action']=='menu_save'){ //菜单保存
			$setting['menu']=array('button'=>$_GET['menu']);
			C::t('setting')->update('feed_wxsetting',$setting);
			if($appid) C::t('wx_app')->update($appid,array('menu'=>serialize(array('button'=>$_GET['menu']))));
			updatecache('setting');
			exit(json_encode(array('msg'=>'success')));
	}elseif($_GET['action']=='menu_publish'){//发布到微信
			$data=$setting['menu']=array('button'=>$_GET['menu']);
			C::t('setting')->update('feed_wxsetting',$setting);
			if($appid) C::t('wx_app')->update($appid,array('menu'=>serialize($data)));
			updatecache('setting');
			//发布菜单到微信
			
			if(getglobal('setting/CorpID') && getglobal('setting/CorpSecret') && $setting['agentid']){
				$wx=new qyWechat(array('appid'=>getglobal('setting/CorpID'),'appsecret'=>getglobal('setting/CorpSecret')));
				//处理菜单数据，所有本站链接添加oauth2地址
				foreach($data['button'] as $key=>$value){
					if($value['url'] && strpos($value['url'],$_G['siteurl'])===0){
						$data['button'][$key]['url']=$wx->getOauthRedirect(getglobal('siteurl').'index.php?mod=system&op=wxredirect&url='.dzzencode($value['url']));
					}elseif($value['sub_button']){
						foreach($value['sub_button'] as $key1=>$value1){
							if($value1['url'] && strpos($value1['url'],$_G['siteurl'])===0){
								$data['button'][$key]['sub_button'][$key1]['url']=$wx->getOauthRedirect(getglobal('siteurl').'index.php?mod=system&op=wxredirect&url='.dzzencode($value1['url']));
							}
						}
					}
				}
				if($wx->createMenu($data,$setting['agentid'])){
					exit(json_encode(array('msg'=>'success')));
				}else{
					exit(json_encode(array('error'=>'发布失败,errCode:'.$wx->errCode.',errMsg:'.$wx->errMsg)));
				}
			}else{
				exit(json_encode(array('error'=>'发布失败,应用还没有创建微信agentid')));
			}
			
	}elseif($_GET['action']=='menu_default'){//恢复默认
		
		$setting['menu']=array('button'=>array(
											array(
												'type'=>'view',	
												'name'=>'全部动态',
												'url'=>$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=all'
											),
											array(
												'type'=>'view',	
												'name'=>'与我相关',
												'url'=>$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=aboutme'
											),
											array(
												'name'=>'我的动态',
												'sub_button'=>array(
													array(
														'type'=>'view',	
														'name'=>'我发布的',
														'url'=>$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=fromme'
													),
													array(
														'type'=>'view',	
														'name'=>'@我的',
														'url'=>$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=atme'
													),
													array(
														'type'=>'view',	
														'name'=>'回复我的',
														'url'=>$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=replyme'
													),
													array(
														'type'=>'view',	
														'name'=>'我收藏的',
														'url'=>$_G['siteurl'].DZZSCRIPT.'?mod=feed&feedType=collect'
													)
												
												)
											)
							)
					  );
		C::t('setting')->update('feed_wxsetting',$setting);
		updatecache('setting');
		exit('success');
	}
	include template('common/wx_ajax');
	exit();
}
include template('wxsetting');
?>
