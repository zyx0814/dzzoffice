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
$op=$_GET['op'];
$profilevalidate = array(
	'telephone' => '/^((\\(?\\d{3,4}\\)?)|(\\d{3,4}-)?)\\d{7,8}$/',
	'mobile' => '/^(\+)?(86)?0?1\\d{10}$/',
	'zipcode' => '/^\\d{5,6}$/',
	'revenue' => '/^\\d+$/',
	'height' => '/^\\d{1,3}$/',
	'weight' => '/^\\d{1,3}$/',
	'qq' => '/^[1-9]*[1-9][0-9]*$/'
);
$fieldid = $_GET['fieldid'] ? $_GET['fieldid'] : '';
$do=$_GET['do'] ? $_GET['do'] : '';
if($do=='delete'){
	C::t('user_profile_setting')->delete_by_fieldid($fieldid);
	require_once libfile('function/cache');
	updatecache(array('profilesetting', 'fields_required', 'fields_optional', 'fields_register', 'setting'));
	showmessage('data_del_success',dreferer(),array(),array('alert'=>'right'));
	
}elseif($fieldid) {
		$_G['setting']['privacy'] = !empty($_G['setting']['privacy']) ? $_G['setting']['privacy'] : array();
		$_G['setting']['privacy'] = is_array($_G['setting']['privacy']) ? $_G['setting']['privacy'] : dunserialize($_G['setting']['privacy']);
		$field = C::t('user_profile_setting')->fetch($fieldid);
		$fixedfields1 = array('uid', 'constellation', 'zodiac');
		$fixedfields2 = array('gender', 'birthday','department');
		$field['isfixed1'] = in_array($fieldid, $fixedfields1);
		$field['isfixed2'] = $field['isfixed1'] || in_array($fieldid, $fixedfields2);
		//$field['customable'] = preg_match('/^field[1-8]$/i', $fieldid);
		$field['validate'] = $field['validate'] ? $field['validate'] : ($profilevalidate[$fieldid]?$profilevalidate[$fieldid]:'');
		if(!submitcheck('editsubmit')) {	
			
			$checkLanguage = checkLanguage();
			include template('profileset_edit');
			exit();
		} else {

			$setarr = array(
				'invisible' => intval($_POST['invisible']),
				'showincard' => intval($_POST['showincard']),
				'showinregister' => intval($_POST['showinregister']),
				'allowsearch' => intval($_POST['allowsearch']),
				'displayorder' => intval($_POST['displayorder'])
			);
			if(!$field['isfixed2']) {
				$_POST['title'] = dhtmlspecialchars(trim($_POST['title']));
				if(empty($_POST['title'])) {
					showmessage('data_name_empty', ADMINSCRIPT.'?mod=member&op=profileset&fieldid='.$fieldid, array(),array('alert'=>'error'));
				}
				$setarr['title'] = $_POST['title'];
				$setarr['description'] = dhtmlspecialchars(trim($_POST['description']));
			}
			if(!$field['isfixed1']) {
				$setarr['required'] = intval($_POST['required']);
				$setarr['available'] = intval($_POST['available']);
				$setarr['unchangeable'] = intval($_POST['unchangeable']);
				$setarr['needverify'] = intval($_POST['needverify']);
			}
			if(!$field['isfixed2']) {
				$setarr['formtype'] = $fieldid == 'realname' ? 'text' : strtolower(trim($_POST['formtype']));
				$setarr['size'] = intval($_POST['size']);
				if($_POST['choices']) {
					$_POST['choices'] = trim($_POST['choices']);
					$ops = explode("\n", $_POST['choices']);
					$parts = array();
					foreach ($ops as $op) {
						$parts[] = dhtmlspecialchars(trim($op));
					}
					$_POST['choices'] = implode("\n", $parts);
				}
				$setarr['choices'] = $_POST['choices'];
				if($_POST['validate'] && $_POST['validate'] != $profilevalidate[$fieldid]) {
					$setarr['validate'] = $_POST['validate'];
				} elseif(empty($_POST['validate'])) {
					$setarr['validate'] = '';
				}
			}
			//print_r($setarr);exit($fieldid);
			C::t('user_profile_setting')->update($fieldid, $setarr);
			if($_GET['fieldid'] == 'birthday') {
				C::t('user_profile_setting')->update('birthmonth', $setarr);
				C::t('user_profile_setting')->update('birthyear', $setarr);
			}

			
			require_once libfile('function/cache');
			if(!isset($_G['setting']['privacy']['profile']) || $_G['setting']['privacy']['profile'][$fieldid] != $_POST['privacy']) {
				$_G['setting']['privacy']['profile'][$fieldid] = intval($_POST['privacy']);
				C::t('setting')->update('privacy', $_G['setting']['privacy']);
			}
			updatecache(array('profilesetting','fields_required', 'fields_optional', 'fields_register', 'setting'));
			
			showmessage('subscriber_data_edit_success', ADMINSCRIPT.'?mod=member&op=profileset', array(),array('alert'=>'right'));
		}
}else {
		
	if(!submitcheck('ordersubmit')) {
		$list = array();
		foreach(C::t('user_profile_setting')->range() as $fieldid => $value) {
			$list[$fieldid] = array(
				'title'=>$value['title'],
				'displayorder'=>$value['displayorder'],
				'available'=>$value['available'],
				'invisible'=>$value['invisible'],
				'showincard'=>$value['showincard'],
				'showinregister'=>$value['showinregister'],
				'customable'=>$value['customable']);
		}

		unset($list['birthyear']);
		unset($list['birthmonth']);
		
			$fieldid='';
			
	} else {
			foreach($_GET['displayorder'] as $fieldid => $value) {
				$setarr = array(
					'displayorder' => intval($value),
					'invisible' => intval($_GET['invisible'][$fieldid]) ? 0 : 1,
					'available' => intval($_GET['available'][$fieldid]),
					'showincard' => intval($_GET['showincard'][$fieldid]),
					'showinregister' => intval($_GET['showinregister'][$fieldid]),
				);
				C::t('user_profile_setting')->update($fieldid, $setarr);

				if($fieldid == 'birthday') {
					C::t('user_profile_setting')->update('birthmonth', $setarr);
					C::t('user_profile_setting')->update('birthyear', $setarr);
				
				} 
			}
			foreach($_GET['add']['displayorder'] as $key => $value) {
				$setarr = array(
					'displayorder' => intval($value),
					'invisible' => intval($_GET['add']['invisible'][$key]) ? 0 : 1,
					'available' => intval($_GET['add']['available'][$key]),
					'showincard' => intval($_GET['add']['showincard'][$key]),
					'showinregister' => intval($_GET['add']['showinregister'][$key]),
					'title'=>dhtmlspecialchars($_GET['add']['title'][$key]),
					'fieldid'=>dhtmlspecialchars($_GET['add']['fieldid'][$key])
				);
				if(empty($setarr['title']) || empty($setarr['fieldid'])) continue;
				if(DB::result_first("select COUNT(*) from %t where fieldid=%s",array('user_profile_setting',$settarr['fieldid']))){
					continue;
				}
				C::t('user_profile_setting')->insert($setarr);

			}
			require_once libfile('function/cache');
			updatecache(array('profilesetting', 'fields_required', 'fields_optional', 'fields_register', 'setting'));
			showmessage('subscriber_data_item_edit_success',dreferer(),array(),array('alert'=>'right'));
	}
}


include template('profileset');

?>
