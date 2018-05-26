<?php
/*
 * 计划任务脚本 每周获取检查百度网盘的token，过期时间小于1周的全部刷新
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_DZZ')) {
	exit('Access Denied');
}
set_time_limit(0);
$week=7*24*60*60;
require_once(DZZ_ROOT.'./core/api/BaiduPCS/BaiduOAuth2.php');
if(!$cloud=DB::fetch_first("select `key` , `secret` from ".DB::table('connect')." where bz='baiduPCS'")){
	exit();
}
if(!isset($cloud['key']) || !isset($cloud['secret'])) {
	exit();
}

$auth=new BaiduOAuth2($cloud['key'],$cloud['secret']);
$list=DB::fetch_all("select * from ".DB::table('connect_pan')." where bz='baiduPCS'");
foreach($list as $value){
	if(($value['refreshtime']+$value['expires_in']-TIMESTAMP)<$week){
		if($token=$auth->getAccessTokenByRefreshToken($value['refresh_token'],$value['scope']) ){
			$token['refreshtime']=TIMESTAMP;
			if($token['access_token']) C::t('connect_pan')->update($value['id'],$token);
		}
	}
}
?>
