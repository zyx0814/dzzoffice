/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */


function delDepart(obj){
	jQuery(obj).parent().parent().remove();
}

var tpml_index=0;
function addorgsel(){
	jQuery('#selorg_container').append(' <ul class="nav nav-pills">'+(orgsel_html.replace(/orgid_tpml/ig,'orgid_tpml_'+tpml_index))+'</ul>');
	tpml_index++;
}

function selJob(obj){
	var jobid=jQuery(obj).attr('_jobid');
	var li=jQuery(obj).parent().parent().parent();
	var html=obj.innerHTML;
	li.find('.dropdown-toggle').attr('_jobid',jobid).find('span').html(html);
	li.find('input').val(jobid);
}
function selDepart(obj){
	var orgid=jQuery(obj).val();
	var li=jQuery(obj).parent();
	li.parent().find('.job .dropdown-menu').load('admin.php?mod=member&op=ajax&do=getjobs&orgid='+orgid,function(html){
			
			if(li.parent().find('.job .dropdown-menu li').length>1) li.parent().find('.job .dropdown-toggle').trigger('click');
		});
	li.parent().find('.job .dropdown-toggle').attr('_jobid',0).find('span').html(__lang.none);
	li.parent().find('.job input').val('0');
}
function errormessage(id, msg,passlevel) {
	if($(id)) {
		msg = !msg ? '' : msg;
		if(msg == 'succeed') {
			msg = '';
			jQuery('#suc_' + id).addClass('p_right');
		} else if(msg !== '') {
			jQuery('#suc_' + id).removeClass('p_right');
		}
		jQuery('#chk_' + id).find('kbd').html(msg);
		if(msg && !passlevel) jQuery('#'+id).parent().parent().addClass('has-warning');
		else jQuery('#'+id).parent().parent().removeClass('has-warning');
	}
}

function checkemail(id) {
	errormessage(id);
	var email = trim($(id).value);
	if($(id).parentNode.className.match(/ p_right/) && (email == '' || email == lastemail ) || email == lastemail) {
		return;
	} 
	if(email.match(/<|"/ig)) {
		errormessage(id, __lang.Email_sensitivity);
		return;
	}
	
	var x = new Ajax();
	jQuery('#suc_' + id).removeClass('p_right');
	x.get('user.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkemail&email=' + email, function(s) {
		s=s.replace(/<a(.+?)<\/a>/i,'');
		errormessage(id, s);
	});
}
function checknick(id) {
	errormessage(id);
	var username = trim($(id).value);
	if($('chk_' + id).parentNode.className.match(/ p_right/) && (username == '' || username == lastusername) || username == lastusername) {
		return;
	} 
	if(username.match(/<|"/ig)) {
		errormessage(id, __lang.profile_nickname_illegal);
		return;
	}
	if(username){
		var unlen = username.replace(/[^\x00-\xff]/g, "**").length;
		if(unlen < 3 || unlen > 30) {
			errormessage(id, unlen < 3 ? __lang.username_character : __lang.username_character);
			return;
		}
		var x = new Ajax();
		jQuery('#suc_' + id).removeClass('p_right');
		x.get('user.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkusername&username=' + encodeURI(username), function(s) {
			s=s.replace(/<a(.+?)<\/a>/i,'');
			errormessage(id, s);
		});
	}
}
function checkPwdComplexity(firstObj, secondObj, modify) {
	modifypwd = modify || false;
	firstObj.onblur = function () {
		if(firstObj.value == '') {
			var pwmsg = !modifypwd ? __lang.register_password_tips : __lang.js_change_password;
			if(pwlength > 0) {
				pwmsg += '，'+__lang.register_password_length_tips1+pwlength+__lang.register_password_length_tips2;
			}
			if(!modifypwd) errormessage(firstObj.id, pwmsg);
		}else{
			errormessage(firstObj.id, !modifypwd ? 'succeed' :  __lang.js_change_password);
		}
		checkpassword(firstObj.id, secondObj.id);
	};
	firstObj.onkeyup = function () {
		if(pwlength == 0 || $(firstObj.id).value.length >= pwlength) {
			var passlevels = new Array('',__lang.weak,__lang.center,_lang.strong);
			var passlevel = checkstrongpw(firstObj.id);
			
			errormessage(firstObj.id, '<span class="passlevel passlevel'+passlevel+'">'+__lang.intension+':'+passlevels[passlevel]+'</span>','passlevel');
		}
	};
	secondObj.onblur = function () {
		if(secondObj.value == '') {
			if(!modifypwd) errormessage(secondObj.id, !modifypwd ?'succeed' :__lang.register_repassword_tips);
		}
		checkpassword(firstObj.id, secondObj.id);
	};
}

function checkpassword(id1, id2) {
	if(!$(id1).value && !$(id2).value) {
		//return;
	}
	if(pwlength > 0) {
		if($(id1).value.length < pwlength) {
			errormessage(id1, __lang.password_too_short+pwlength+__lang.register_password_length_tips2);
			return;
		}
	}
	if(strongpw) {
		var strongpw_error = false, j = 0;
		var strongpw_str = new Array();
		for(var i in strongpw) {
			if(strongpw[i] === 1 && !$(id1).value.match(/\d+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_1;
				j++;
			}
			if(strongpw[i] === 2 && !$(id1).value.match(/[a-z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_2;
				j++;
			}
			if(strongpw[i] === 3 && !$(id1).value.match(/[A-Z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_3;
				j++;
			}
			if(strongpw[i] === 4 && !$(id1).value.match(/[^A-Za-z0-9]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_4;
				j++;
			}
		}
		if(strongpw_error) {
			errormessage(id1, __lang.password_weak +strongpw_str.join('，'));
			return;
		}
	}
	errormessage(id2);
	if($(id1).value != $(id2).value) {
		errormessage(id2, __lang.profile_passwd_notmatch);
	} else {
		if(modifypwd) errormessage(id1,  'succeed' );
		errormessage(id2,  'succeed' );
		
	}
}

