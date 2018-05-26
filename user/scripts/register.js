/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

var  profileTips = __lang.js_change_password;

function errormessage(el, msg,passlevel) {
	if(el.length) {
		msg = !msg ? '' : msg;
		if(msg){
			el.parent().find('.help-msg').removeClass('chk_right');
		}else{
			el.parent().find('.help-msg').addClass('chk_right');
		}
		el.parent().find('.help-msg').html(msg);
		if(msg && !passlevel) el.parent().addClass('has-error');
		else el.closest('.has-error').removeClass('has-error');
	}
}

function addFormEvent(formid, focus){
	
	jQuery('#username').on('blur keyup', function () {
		if(this.value == '') {
			errormessage(jQuery(this), __lang.username_character);
		}else{
			checkusername(jQuery(this));
		}
	});
	jQuery('#email').on('blur keyup', function () {
		if(this.value == '') {
			errormessage(jQuery(this), __lang.register_email_tips1);
		}else{
			checkemail(jQuery(this));
		}
	});
	

	checkPwdComplexity(document.getElementById('password'), document.getElementById('password2'));
	
	if(focus){
		jQuery('#'+formid+' .form-control').first().focus();
	}
	
}

function checkPwdComplexity(Obj1, Obj2, modify) {
	modifypwd = modify || false;
	var firstObj=jQuery(Obj1);
	var secondObj=jQuery(Obj2);
	
	firstObj.on('blur',function () {
		if(firstObj.val() == '') {
			var pwmsg = !modifypwd ? __lang.profile_passwd_illegal : profileTips;
			if(pwlength > 0) {
				pwmsg += ', '+__lang.register_password_length_tips1+pwlength+' '+__lang.register_password_length_tips2;
			}
			if(!modify) errormessage(firstObj, pwmsg);
		}else{
			errormessage(firstObj, !modifypwd ? '' : profileTips);
		}
		checkpassword(firstObj, secondObj);
	});
	firstObj.on('keyup',function () {
		if(pwlength == 0 || firstObj.val().length >= pwlength) {
			var passlevels = new Array('',__lang.weak,__lang.center,__lang.strong);
			var passlevel = checkstrongpw(firstObj);
			
			errormessage(firstObj, '<span class="passlevel passlevel'+passlevel+'">'+__lang.intension+':'+passlevels[passlevel]+'</span>','passlevel');
		}
	});
	secondObj.on('blur keyup', function () {
		if(secondObj.val() == '') {
			if(!modify){
				errormessage(secondObj, !modifypwd ? __lang.register_repassword_tips : profileTips);
			} 
		}
		checkpassword(firstObj, secondObj);
	});
}


function checkstrongpw(el) {
	var passlevel = 0;
	var val=el.val();
	if(val && val.match(/\d+/g)) {
		passlevel ++;
	}
	if(val && val.match(/[a-z]+/ig)) {
		passlevel ++;
	}
	if(val && val.match(/[^a-z0-9]+/ig)) {
		passlevel ++;
	}
	return passlevel;
}


function showbirthday(){
	var el = document.getElementById('birthday');
	var birthday = el.value;
	el.length=0;
	el.options.add(new Option('日', ''));
	for(var i=0;i<28;i++){
		el.options.add(new Option(i+1, i+1));
	}
	if(document.getElementById('birthmonth').value!="2"){
		el.options.add(new Option(29, 29));
		el.options.add(new Option(30, 30));
		switch(document.getElementById('birthmonth').value){
			case "1":
			case "3":
			case "5":
			case "7":
			case "8":
			case "10":
			case "12":{
				el.options.add(new Option(31, 31));
			}
		}
	} else if(document.getElementById('birthyear').value!='') {
		var nbirthyear=document.getElementById('birthyear').value;
		if(nbirthyear%400==0 || (nbirthyear%4==0 && nbirthyear%100!=0)) el.options.add(new Option(29, 29));
	}
	el.value = birthday;
}

function trim(str) {
	return str.replace(/^\s*(.*?)[\s\n]*$/g, '$1');
}



function checksubmit(form) {
	var p_chks = jQuery('#registerform').find('.has-error');
	if(p_chks.length>0){
		p_chks.first().find('input').focus();
		return false;
	} 
	ajaxpost('registerform', 'returnmessage4', 'returnmessage4');
	return;
}

function check_submit(form){
	//检测所有，没有.p_right的不允许提交；
	var error=0;
	jQuery(form).find('.help-msg').each(function(){
		if(!jQuery(this).hasClass('chk_right')){
			jQuery(this).parent().find('input').trigger('blur').focus();
			error=1;
			return false;
		}
	});
	if(error) return false;
	
	var url = jQuery(form).attr('action');

	url = (url)? url:'user.php?mod=register';

	var type = 'json';

	jQuery.post(url+'&returnType='+type,jQuery(form).serialize(),function(json){

		if(json.success){
			location.href=json['success']['url_forward'];
			
			/*jQuery('#succeedmessage_href').href = json['success']['url_forward'];
			jQuery('#register_form').hide();
			jQuery('#main_succeed').show();
			jQuery('#succeedlocation').html(json['success']['message']);
			setTimeout("window.location.href ='"+json['success']['url_forward']+"';", 3000);*/

		}else{
			jQuery('#returnmessage4').html(json['error']);
		}
	},'json');
}

function checkusername(el) {
	var username = trim(el.val());
	if(username == '' || username == lastusername) {
		errormessage(el);
		return;
	} 
	if(username.match(/<|\"/ig)) {
		errormessage(el, __lang.profile_nickname_illegal);
		return;
	}
	if(username){
		var unlen = username.replace(/[^\x00-\xff]/g, "**").length;
		if(unlen < 3 || unlen > 30) {
			errormessage(el,  __lang.username_character );
			return;
		}
		
		
		jQuery.getJSON('user.php?mod=ajax&action=checkusername&username=' + encodeURI(username), function(json) {
			errormessage(el, json.error||'');
		});
	}
}

function checkpassword(el1, el2) {
	if(!el1.val() && !el2.val()) {
		return;
	}
	if(pwlength > 0) {
		if(el1.val().length < pwlength) {
			errormessage(el1, __lang.password_too_short+' '+pwlength+' '+__lang.register_password_length_tips2);
			return;
		}
	}
	if(strongpw) {
		var strongpw_error = false, j = 0;
		var strongpw_str = new Array();
		for(var i in strongpw) {
			if(strongpw[i] === 1 && !el1.val().match(/\d+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_1;
				j++;
			}
			if(strongpw[i] === 2 && !el1.val().match(/[a-z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_2;
				j++;
			}
			if(strongpw[i] === 3 && !el1.val().match(/[A-Z]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_3;
				j++;
			}
			if(strongpw[i] === 4 && !el1.val().match(/[^A-Za-z0-9]+/g)) {
				strongpw_error = true;
				strongpw_str[j] = __lang.strongpw_4;
				j++;
			}
		}
		if(strongpw_error) {
			errormessage(el1, __lang.password_weak+' '+strongpw_str.join('，'));
			return;
		}
	}
	errormessage(el2);
	if(el1.val() != el2.val()) {
		errormessage(el2, __lang.profile_passwd_notmatch);
	} else {
		errormessage(el2, !modifypwd ? '' : profileTips);
	}
}

function checkemail(el) {
	
	var email = trim(el.val());
	if(email == '' || email == lastemail) {
		errormessage(el);
		return;
	} 
	if(email.match(/<|\"/ig)) {
		errormessage(el, __lang.Email_sensitivity);
		return;
	}
	var isEmail = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;  
	if(!isEmail.test(email)) {
		errormessage(el, __lang.email_illegal);
		return;
	}
	jQuery.getJSON('user.php?mod=ajax&action=checkemail&email=' + email, function(json) {	
			errormessage(el, json.error||'');	
	});
}


