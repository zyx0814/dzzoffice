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

function profile_setting($fieldid, $space=array(), $showstatus=false, $ignoreunchangable = false, $ignoreshowerror = false) {
	global $_G;
	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	$field = $_G['cache']['profilesetting'][$fieldid];
	if(empty($field) || !$field['available'] || in_array($fieldid, array('uid', 'constellation', 'zodiac', 'birthmonth', 'birthyear'))) {
		return '';
	}
	if($showstatus) {
		$uid = intval($space['uid']);
		if($uid && !isset($_G['profile_verifys'][$uid])) {
			$_G['profile_verifys'][$uid] = array();
			if($value = C::t('user_verify_info')->fetch_by_uid_verifytype($uid, 0)) {
				$fields = dunserialize($value['field']);
				foreach($fields as $key => $fvalue) {
					if($_G['cache']['profilesetting'][$key]['needverify']) {
						$_G['profile_verifys'][$uid][$key] = $fvalue;
					}
				}
			}
		}
		$verifyvalue = NULL;
		if(isset($_G['profile_verifys'][$uid][$fieldid])) {
			if($fieldid=='gender') {
				$verifyvalue = lang('gender_'.intval($_G['profile_verifys'][$uid][$fieldid]));
			} elseif($fieldid=='birthday') {
				$verifyvalue = $_G['profile_verifys'][$uid]['birthyear'].'-'.$_G['profile_verifys'][$uid]['birthmonth'].'-'.$_G['profile_verifys'][$uid]['birthday'];
				
			}elseif($fieldid=='department'){
				if($_G['profile_verifys'][$uid][$fieldid]){
					include_once libfile('function/organization');
					$orgtree=getPathByOrgid($_G['profile_verifys'][$uid][$fieldid]);
					$verifyvalue = implode('-',($orgtree));
				}else{
					$verifyvalue = '';
				}
				
			}else {
				$verifyvalue = $_G['profile_verifys'][$uid][$fieldid];
			}
		}
	}

	
	$html = '';
	$field['unchangeable'] = !$ignoreunchangable && $field['unchangeable'] ? 1 : 0;
	if($fieldid == 'birthday') {
		if($field['unchangeable'] && !empty($space[$fieldid])) {
			return '<p class="form-control-static profile profile-'.$fieldid.'">'.$space['birthyear'].'-'.$space['birthmonth'].'-'.$space['birthday'].'</p><input type="hidden" name="birthyear" value="'.$space['birthyear'].'" />			<input type="hidden" name="birthmonth" value="'.$space['birthmonth'].'" /><input type="hidden" name="birthday" value="'.$space['birthday'].'" />';
		}
		$birthyeayhtml = '';
		$nowy = dgmdate($_G['timestamp'], 'Y');
		for ($i=0; $i<100; $i++) {
			$they = $nowy - $i;
			$selectstr = ($they == $space['birthyear']) ?' selected':'';
			$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
		}
		$birthmonthhtml = '';
		for ($i=1; $i<13; $i++) {
			$selectstr = (($i == $space['birthmonth'])?' selected':'');
			$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
		}
		$birthdayhtml = '';
		if(empty($space['birthmonth']) || in_array($space['birthmonth'], array(1, 3, 5, 7, 8, 10, 12))) {
			$days = 31;
		} elseif(in_array($space['birthmonth'], array(4, 6, 9, 11))) {
			$days = 30;
		} elseif($space['birthyear'] && (($space['birthyear'] % 400 == 0) || ($space['birthyear'] % 4 == 0 && $space['birthyear'] % 400 != 0))) {
			$days = 29;
		} else {
			$days = 28;
		}
		for ($i=1; $i<=$days; $i++) {
			$selectstr = ($i == $space['birthday'])?' selected':'';
			$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
		}
		$html = '<div class="profile-group-birthday">'
					.'<select name="birthyear" id="birthyear"  onchange="showbirthday();" class="form-control input-sm pull-left profile profile-birthyear"  style="width:80px;margin-right:5px;">'
					.'<option value="">'.lang('year').'</option>'
					.$birthyeayhtml
					.'</select>'
					.'<select name="birthmonth" id="birthmonth"  onchange="showbirthday();"  class="form-control input-sm pull-left profile profile-birthmonth"    style="width:60px;margin-right:5px;">'
					.'<option value="">'.lang('month').'</option>'
					.$birthmonthhtml
					.'</select>'
					.'<select name="birthday" id="birthday" class="form-control input-sm pull-left profile profile-birthday"    style="width:60px">'
					.'<option value="">'.lang('day').'</option>'
					.$birthdayhtml
					.'</select>'
				.'</div>';
				
	} elseif($fieldid=='gender') {
		$space[$fieldid] = isset($space[$fieldid]) ? $space[$fieldid]:'';
		if($field['unchangeable']  && $space[$fieldid] > 0) {
			return '<p class="form-control-static profile profile-'.$fieldid.'">'.lang('gender_'.intval($space[$fieldid])).'</span><input type="hidden" name="'.$fieldid.'" value="'.$space[$fieldid].'" />';
		}
		$selected = array($space[$fieldid]=>' selected="selected"');
		$html = '<select name="gender" id="gender" class="form-control input-sm  profile profile-'.$fieldid.'" >';
		if($field['unchangeable']) {
			$html .= '<option value="">'.lang('gender').'</option>';
		} else {
			$html .= '<option value="0"'.($space[$fieldid]=='0' ? ' selected="selected"' : '').'>'.lang('gender_0').'</option>';
		}
		$html .= '<option value="1"'.($space[$fieldid]=='1' ? ' selected="selected"' : '').'>'.lang('gender_1').'</option>'
			.'<option value="2"'.($space[$fieldid]=='2' ? ' selected="selected"' : '').'>'.lang('gender_2').'</option>'
			.'</select>';

	} elseif($fieldid=='department') {
		$space[$fieldid] = !empty($space[$fieldid]) ? $space[$fieldid]:'';
		if($field['unchangeable']  && $space[$fieldid] > 0) {
			return '<p class="form-control-static profile profile-'.$fieldid.'">'.$space['department_tree'].'</span><input type="hidden" name="'.$fieldid.'" value="'.$space[$fieldid].'" />';
		}
			$html=' <script type="text/javascript">'
				  .'	var selorg={};'
				  .'	selorg.add=function(ctrlid,vals){'
				  ."		if(vals[0].orgid=='other') vals[0].path=".lang('not_choose_agencies_departments1').";"
				  ."		jQuery('#'+ctrlid+'_Menu').html(vals[0].path+' <span class=\"caret\"></span>');"
				  ."		jQuery('#sel_'+ctrlid).val(vals[0].orgid);"
				  .'	}'
				  .'  </script>'
				  .'<div class="dropdown profile-group-department"  >'
				  .'	  <input id="sel_'.$fieldid.'"  type="hidden" name="'.$fieldid.'"  value="'.(!empty($space[$fieldid]) ? $space[$fieldid]:'').'" />'
				  .'	  <button type="button" id="'.$fieldid.'_Menu" class="btn btn-default dropdown-toggle" data-toggle="dropdown">'
				  .'	'.($space['department_tree']?$space['department_tree']:lang('please_select_a_organization_or_department')).' <span class="caret"></span>'
				  .'  </button>'
				  .'  <div id="'.$fieldid.'_dropdown_menu" class="dropdown-menu org-sel-box" role="menu" aria-labelledby="'.$fieldid.'_Menu">'
				  .'	   <iframe name="'.$fieldid.'_iframe" class="org-sel-box-iframe" src="index.php?mod=system&op=orgtree&ctrlid='.$fieldid.'&nouser=1" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%" allowtransparency="true" ></iframe>'
				 .'	  </div>'
				 .'</div>';
	} else {
		if($field['unchangeable'] && $space[$fieldid]!='') {
			if($field['formtype']=='file') {
				$imgurl = getglobal('setting/attachurl').$space[$fieldid];
				return '<p class="form-control-static profile profile-'.$fieldid.'"><a href="'.$imgurl.'" target="_blank"><img src="'.$imgurl.'" /></a></span><input type="hidden" name="'.$fieldid.'" value="'.$space[$fieldid].'" />';
			} else {
				return '<p class="form-control-static profile profile-'.$fieldid.'">'.nl2br($space[$fieldid]).'</span><input type="hidden" name="'.$fieldid.'" value="'.$space[$fieldid].'" />';
			}
		}
		if($field['formtype']=='textarea') {
			$html = "<textarea name=\"$fieldid\"  id=\"$fieldid\" class=\"form-control input-sm profile profile-$fieldid\" rows=\"3\"   >$space[$fieldid]</textarea>";
		} elseif($field['formtype']=='select') {
			$field['choices'] = explode("\n", $field['choices']);
			$html = "<select name=\"$fieldid\" id=\"$fieldid\" class=\"form-control input-sm profile profile-$fieldid\" >";
			foreach($field['choices'] as $op) {
				$html .= "<option value=\"$op\"".($op==$space[$fieldid] ? 'selected="selected"' : '').">$op</option>";
			}
			$html .= '</select>';
		} elseif($field['formtype']=='list') {
			$field['choices'] = explode("\n", $field['choices']);
			$html = "<select name=\"{$fieldid}[]\" id=\"$fieldid\"  class=\"form-control input-sm profile profile-$fieldid\"  multiple=\"multiplue\" >";
			$space[$fieldid] = explode("\n", $space[$fieldid]);
			foreach($field['choices'] as $op) {
				$html .= "<option value=\"$op\"".(in_array($op, $space[$fieldid]) ? 'selected="selected"' : '').">$op</option>";
			}
			$html .= '</select>';
		} elseif($field['formtype']=='checkbox') {
			$field['choices'] = explode("\n", $field['choices']);
			$space[$fieldid] = explode("\n", $space[$fieldid]);
			$html.='<div class="class="profile profile-'.$fieldid.'" >';
			foreach($field['choices'] as $op) {
				$html .= ''
					."<label class=\"checkbox-inline\"><input type=\"checkbox\" name=\"{$fieldid}[]\" id=\"$fieldid\"  value=\"$op\" ".(in_array($op, $space[$fieldid]) ? ' checked="checked"' : '')." />"
					."$op</label>";
			}
			$html.='</div>';
		} elseif($field['formtype']=='radio') {
			$field['choices'] = explode("\n", $field['choices']);
			$html.='<div class="profile profile-'.$fieldid.'" >';
			foreach($field['choices'] as $op) {
				$html .= ''
						."<label class=\"radio-inline\"><input type=\"radio\" name=\"{$fieldid}\"  value=\"$op\" ".($op == $space[$fieldid] ? ' checked="checked"' : '')." />"
						."$op</label>";
			}
			$html.='</div>';
		} elseif($field['formtype']=='file') {
			$html = "<input type=\"file\" value=\"\" name=\"$fieldid\" id=\"$fieldid\" class=\"form-control input-sm profile profile-$fieldid\" /><input type=\"hidden\" name=\"$fieldid\" value=\"$space[$fieldid]\"  />";
			if(!empty($space[$fieldid])) {
				$url = getglobal('setting/attachurl').$space[$fieldid];
				$html .= "&nbsp;<label class=\"checkbox-inline\"><input type=\"checkbox\" name=\"deletefile[$fieldid]\" id=\"$fieldid\" value=\"yes\" />".lang('delete')."</label><br /><a href=\"$url\" target=\"_blank\"><img src=\"$url\" width=\"200\" class=\"mtm\" /></a>";
			}
		
		} else {
			$html = "<input type=\"text\" name=\"$fieldid\" id=\"$fieldid\"  value=\"$space[$fieldid]\"  class=\"form-control input-sm profile profile-$fieldid\" />";
		}
	}
	$showerror = !$ignoreshowerror ? "id=\"showerror_$fieldid\"" : '';
	if($showstatus) {
		$html.= "<span $showerror class=\"help-inline\">";
		if($verifyvalue !== null) {
			if($field['formtype'] == 'file') {
				$imgurl = getglobal('setting/attachurl').$verifyvalue;
				$verifyvalue = "<img src='$imgurl' alt='$imgurl' style='max-width: 500px;'/>";
			}
			$html.= lang('profile_is_verifying')." (<a href=\"javascript:;\" onclick=\"display('newvalue_$fieldid');return false;\">".lang('profile_mypost')."</a>)"
				."<p id=\"newvalue_$fieldid\" style=\"display:none\">".$verifyvalue."</p>";
		} elseif($field['needverify']) {
			$html .= lang( 'profile_need_verifying');
		}elseif(isset($space[$fieldid]) && $space[$fieldid]=='' && $field['unchangeable']) {
			$html .= lang('profile_unchangeable');
		}
		$html .= '</span>';
	}

	return $html;
}

function profile_check($fieldid, &$value, $space=array()) {
	global $_G;

	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	

	$field = $_G['cache']['profilesetting'][$fieldid];
	if(empty($field) || !$field['available']) {
		return false;
	}

	if($value=='') {
		if($field['required']) {
			
			return false;
		} else {
			return true;
		}
	}
	if($field['unchangeable'] && !empty($space[$fieldid])) {
		return false;
	}

	if(in_array($fieldid, array('birthday', 'birthmonth', 'birthyear', 'gender'))) {
		$value = intval($value);
		return true;
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
		foreach ($value as $op) {
			if(in_array($op, $field['choices'])) {
				$arr[] = $op;
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

function profile_show($fieldid, $space=array(), $getalone = false) {
	global $_G;
	if(empty($_G['cache']['profilesetting'])) {
		loadcache('profilesetting');
	}
	$field = $_G['cache']['profilesetting'][$fieldid];
	if(empty($field) || !$field['available'] || (!$getalone && in_array($fieldid, array('uid', 'birthmonth', 'birthyear')))) {
		return false;
	}

	if($fieldid=='gender') {
		return lang('gender_'.intval($space['gender']));
	} elseif($fieldid=='birthday' && !$getalone) {
		$return = $space['birthyear'] ? $space['birthyear'].' '.lang('year').' ' : '';
		if($space['birthmonth'] && $space['birthday']) {
			$return .= $space['birthmonth'].' '.lang('month').' '.$space['birthday'].' '.lang('day');
		}
		return $return;
	}elseif($fieldid=='department'){
		
		if($space['department']){
			include_once libfile('function/organization');
			$orgtree=getPathByOrgid($space['department']);
			return implode('-',($orgtree));
		}else{
			return lang('not_choose_agencies_departments');
		}
	}  else {
		return nl2br($space[$fieldid]);
	}
}


function showdistrict($values, $elems=array(), $container='districtbox', $showlevel=null, $containertype = 'birth') {
	$html = '';
	if(!preg_match("/^[A-Za-z0-9_]+$/", $container)) {
		return $html;
	}
	$showlevel = !empty($showlevel) ? intval($showlevel) : count($values);
	$showlevel = $showlevel <= 4 ? $showlevel : 4;
	$upids = array(0);
	for($i=0;$i<$showlevel;$i++) {
		if(!empty($values[$i])) {
			$upids[] = intval($values[$i]);
		} else {
			for($j=$i; $j<$showlevel; $j++) {
				$values[$j] = '';
			}
			break;
		}
	}
	$options = array(1=>array(), 2=>array(), 3=>array(), 4=>array());
	if($upids && is_array($upids)) {
		foreach(C::t('district')->fetch_all_by_upid($upids, 'displayorder', 'ASC') as $value) {
			if($value['level'] == 1 && ($value['id'] != $values[0] && ($value['usetype'] == 0 || !(($containertype == 'birth' && in_array($value['usetype'], array(1, 3))) || ($containertype != 'birth' && in_array($value['usetype'], array(2, 3))))))) {
				continue;
			}
			$options[$value['level']][] = array($value['id'], $value['name']);
		}
	}
	$names = array('province', 'city', 'district', 'community');
	for($i=0; $i<4;$i++) {
		if(!empty($elems[$i])) {
			$elems[$i] = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\]]/", '', $elems[$i]));
		} else {
			$elems[$i] = ($containertype == 'birth' ? 'birth' : 'reside').$names[$i];
		}
	}

	for($i=0;$i<$showlevel;$i++) {
		$level = $i+1;
		if(!empty($options[$level])) {
			$jscall = "showdistrict('$container', ['$elems[0]', '$elems[1]', '$elems[2]', '$elems[3]'], $showlevel, $level, '$containertype')";
			$html .= '<select name="'.$elems[$i].'" id="'.$elems[$i].'" style="width:100px;" onchange="'.$jscall.'" >';
			$html .= '<option value="">'.lang('district_level_'.$level).'</option>';
			foreach($options[$level] as $option) {
				$selected = $option[0] == $values[$i] ? ' selected="selected"' : '';
				$html .= '<option did="'.$option[0].'" value="'.$option[1].'"'.$selected.'>'.$option[1].'</option>';
			}
			$html .= '</select>';
			$html .= '&nbsp;&nbsp;';
		}
	}
	return $html;
}

function countprofileprogress($uid = 0) {
	global $_G;

	$uid = intval(!$uid ? $_G['uid'] : $uid);
	$fields=array();
	if(empty($_G['cache']['profilesetting'])) {
		require_once libfile('function/cache');
		updatecache('profilesetting');
		loadcache('profilesetting');
	}
	foreach($_G['cache']['profilesetting'] as $key => $value){
		if($value['available']>0) $fields[]=$key;
	}
	
	$complete = 0;
	$profile = C::t('user_profile')->fetch($uid);
	foreach($fields as $key) {
		if($profile[$key] != '') {
			$complete++;
		}
	}
	
	$progress = floor($complete / count($fields) * 100);
	if(DB::result_first("select COUNT(*) from %t where uid=%d",array('user_status',$uid))){
		C::t('user_status')->update($uid, array('profileprogress' => $progress > 100 ? 100 : $progress), 'UNBUFFERED');
	}else{
		C::t('user_status')->insert(array('uid'=>$uid,'regip'=>$_G['clientip'],'lastip'=>$_G['clientip'],'lastvisit'=>TIMESTAMP,'lastactivity'=>TIMESTAMP,'profileprogress' => $progress > 100 ? 100 : $progress,));
	}
	
	return $progress;
}

function get_constellation($birthmonth,$birthday) {
	$birthmonth = intval($birthmonth);
	$birthday = intval($birthday);
	$idx = $birthmonth;
	if ($birthday <= 22) {
		if (1 == $birthmonth) {
			$idx = 12;
		} else {
			$idx = $birthmonth - 1;
		}
	}
	return $idx > 0 && $idx <= 12 ? lang('constellation_'.$idx) : '';
}

function get_zodiac($birthyear) {
	$birthyear = intval($birthyear);
	$idx = (($birthyear - 1900) % 12) + 1;
	return $idx > 0 && $idx <= 12 ? lang('zodiac_'. $idx) : '';
}
function profile_privacy_check($uid,$privacy){
	global $_G;
	$privacy=intval($privacy);
	if(!$_G['uid']) return false; //游客不允许查看
	if($_G['uid']==$uid) $_G[$var]=true;//自己允许查看
	$var='privacy_'.$uid.'_'.$_G['uid'].'_'.$privacy;
	if(isset($_G[$var])) return $_G[$var];
	
	switch($privacy){
		case '-1': //隐私
			$_G[$var]=false;
			break;
		case '0': //公开
			$_G[$var]=true;
			break;
		case '1': //本部门,不包括下级部门
				include_once libfile('function/organization');
				$orgids=$vorgids=array();
				//查看资料用户所在的部门
				$vorgids=C::t('organization_user')->fetch_orgids_by_uid($_G['uid']);
				//资料用户所在的部门
				$orgids=C::t('organization_user')->fetch_orgids_by_uid($uid);
				
				if((!$vorgids && !$orgids) || array_intersect($orgids,$vorgids)) return ($_G[$var]=true);
				
				$_G[$var]=false;
				
				break;
		 case 2: //本机构
		 	    include_once libfile('function/organization');
				$orgids=$vorgids=array();
				//查看资料用户所在的部门
				$vorgids=C::t('organization_user')->fetch_orgids_by_uid($_G['uid']);
				//资料用户所在的部门
				$orgids=C::t('organization_user')->fetch_orgids_by_uid($uid);
			 	if(!$vorgids && !$orgids) return ($_G[$var]=true); //都未加入部门，视为同机构
				//获取查看资料用户所属的机构数组
				$vtops=array();
				foreach($vorgids as $orgid){
					$vtops[]=C::t('organization')->getTopOrgid($orgid);
				}
				//获取资料用户所属的机构数组
				$tops=array();
				foreach($orgids as $orgid){
					$tops[]=C::t('organization')->getTopOrgid($orgid);
				}
				if(array_intersect($vtops,$tops)) $_G[$var]=true;
			break;
			
		
	}
	return $_G[$var];
}
