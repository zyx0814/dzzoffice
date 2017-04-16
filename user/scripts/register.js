/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

var  profileTips =  __lang.js_change_password;

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

function addFormEvent(formid, focus){
	var si = 0;
	
	var formNode = $(formid).getElementsByTagName('input');
	for(i = 0;i < formNode.length;i++) {
		
		if(formNode[i].name == '') {
			formNode[i].name = formNode[i].id;
			stmp[si] = i;
			si++;
		}
		
	}
	if(!si) {
		return;
	}
	formNode[stmp[1]].onblur = function () {
		checkusername(formNode[stmp[1]].id);
	};
	checkPwdComplexity(formNode[stmp[3]], formNode[stmp[4]]);
	try {
		if(!ignoreEmail) {
			addMailEvent(formNode[stmp[0]]);
		}
	} catch(e) {}

	try {
		if(focus) {
			$('invitecode').focus();
		} else {
			formNode[stmp[0]].focus();
		}
	} catch(e) {}
}

function checkPwdComplexity(firstObj, secondObj, modify) {
	modifypwd = modify || false;
	firstObj.onblur = function () {
		if(firstObj.value == '') {
			var pwmsg = !modifypwd ? __lang.register_password_tips : profileTips;
			if(pwlength > 0) {
				pwmsg += '，'+__lang.register_password_length_tips1+pwlength+__lang.register_password_length_tips2;
			}
			if(!modify) errormessage(firstObj.id, pwmsg);
		}else{
			errormessage(firstObj.id, !modifypwd ? 'succeed' : profileTips);
		}
		checkpassword(firstObj.id, secondObj.id);
	};
	firstObj.onkeyup = function () {
		if(pwlength == 0 || $(firstObj.id).value.length >= pwlength) {
			var passlevels = new Array('',__lang.weak,__lang.center,__lang.strong);
			var passlevel = checkstrongpw(firstObj.id);
			
			errormessage(firstObj.id, '<span class="passlevel passlevel'+passlevel+'">'+__lang.intension+':'+passlevels[passlevel]+'</span>','passlevel');
		}
	};
	secondObj.onblur = function () {
		if(secondObj.value == '') {
			if(!modify) errormessage(secondObj.id, !modifypwd ? __lang.register_repassword_tips : profileTips);
		}
		checkpassword(firstObj.id, secondObj.id);
	};
}

function addMailEvent(mailObj) {

	mailObj.onclick = function (event) {
		emailMenu(event, mailObj.id);
	};
	mailObj.onkeyup = function (event) {
		emailMenu(event, mailObj.id);
	};
	mailObj.onkeydown = function (event) {
		emailMenuOp(4, event, mailObj.id);
	};
	mailObj.onblur = function () {
		if(mailObj.value == '') {
			errormessage(mailObj.id, __lang.register_email_tips1);
		}
		emailMenuOp(3, null, mailObj.id);
	};
	stmp['email'] = mailObj.id;
}
function checkstrongpw(id) {
	var passlevel = 0;
	if($(id).value.match(/\d+/g)) {
		passlevel ++;
	}
	if($(id).value.match(/[a-z]+/ig)) {
		passlevel ++;
	}
	if($(id).value.match(/[^a-z0-9]+/ig)) {
		passlevel ++;
	}
	return passlevel;
}


function showbirthday(){
	var el = $('birthday');
	var birthday = el.value;
	el.length=0;
	el.options.add(new Option(__lang.day, ''));
	for(var i=0;i<28;i++){
		el.options.add(new Option(i+1, i+1));
	}
	if($('birthmonth').value!="2"){
		el.options.add(new Option(29, 29));
		el.options.add(new Option(30, 30));
		switch($('birthmonth').value){
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
	} else if($('birthyear').value!="") {
		var nbirthyear=$('birthyear').value;
		if(nbirthyear%400==0 || (nbirthyear%4==0 && nbirthyear%100!=0)) el.options.add(new Option(29, 29));
	}
	el.value = birthday;
}

function trim(str) {
	return str.replace(/^\s*(.*?)[\s\n]*$/g, '$1');
}

var emailMenuST = null, emailMenui = 0, emaildomains = ['qq.com', '163.com', '126.com', 'sina.com', 'sohu.com', 'yahoo.com', 'gmail.com', 'hotmail.com'];
function emailMenuOp(op, e, id) {
	if(op == 3 && BROWSER.ie && BROWSER.ie < 7) {
		checkemail(id);
	}
	if(!$('emailmore_menu')) {
		return;
	}
	if(op == 1) {
		$('emailmore_menu').style.display = 'none';
	} else if(op == 2) {
		showMenu({'ctrlid':'emailmore','pos': '13!'});
	} else if(op == 3) {
		emailMenuST = setTimeout(function () {
			emailMenuOp(1, id);
			checkemail(id);
		}, 500);
	} else if(op == 4) {
	       	e = e ? e : window.event;
                var obj = $(id);
        	if(e.keyCode == 13) {
                        var v = obj.value.indexOf('@') != -1 ? obj.value.substring(0, obj.value.indexOf('@')) : obj.value;
                        obj.value = v + '@' + emaildomains[emailMenui];
                        doane(e);
        	}
	} else if(op == 5) {
                var as = $('emailmore_menu').getElementsByTagName('a');
                for(i = 0;i < as.length;i++){
                        as[i].className = '';
                }
	}
}

function emailMenu(e, id) {
	if(BROWSER.ie && BROWSER.ie < 7) {
		return;
	}
	e = e ? e : window.event;
        var obj = $(id);
	if(obj.value.indexOf('@') != -1) {
		if($('emailmore_menu')) $('emailmore_menu').style.display = 'none';
		return;
	}
	var value = e.keyCode;
	var v = obj.value;
	if(!obj.value.length) {
		emailMenuOp(1);
		return;
	}

        if(value == 40) {
		emailMenui++;
		if(emailMenui >= emaildomains.length) {
			emailMenui = 0;
		}
	} else if(value == 38) {
		emailMenui--;
		if(emailMenui < 0) {
			emailMenui = emaildomains.length - 1;
		}
	} else if(value == 13) {
  		$('emailmore_menu').style.display = 'none';
  		return;
 	}
        if(!$('emailmore_menu')) {
		menu = document.createElement('div');
		menu.id = 'emailmore_menu';
		menu.style.display = 'none';
		menu.className = 'p_pop';
		menu.setAttribute('disautofocus', true);
		$('append_parent').appendChild(menu);
	}
	var s = '<ul class="dropdown-menu" style="display:block">';
	for(var i = 0; i < emaildomains.length; i++) {
		s += '<li><a href="javascript:;" onmouseover="emailMenuOp(5)" ' + (emailMenui == i ? 'class="a" ' : '') + 'onclick="$(stmp[\'email\']).value=this.innerHTML;display(\'emailmore_menu\');checkemail(stmp[\'email\']);">' + v + '@' + emaildomains[i] + '</a></li>';
	}
	s += '</ul>';
	$('emailmore_menu').innerHTML = s;
	
	emailMenuOp(2);
}

function checksubmit() {
	var p_chks = $('registerform').getElementsByTagName('kbd');
	for(i = 0;i < p_chks.length;i++){
		if(p_chks[i].className == 'p_chk'){
			p_chks[i].innerHTML = '';
		}
	}
	ajaxpost('registerform', 'returnmessage4', 'returnmessage4');
	return;
}

function checkusername(id) {
	errormessage(id);
	var username = trim($(id).value);
	if($('chk_' + id).parentNode.className.match(/ p_right/) && (username == '' || username == lastusername)) {
		return;
	} else {
		lastusername = username;
	}
	if(username.match(/<|\"/ig)) {
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
			errormessage(id, s);
		});
	}
}

function checkpassword(id1, id2) {
	if(!$(id1).value && !$(id2).value) {
		return;
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
			errormessage(id1, __lang.password_weak+strongpw_str.join('，'));
			return;
		}
	}
	errormessage(id2);
	if($(id1).value != $(id2).value) {
		errormessage(id2, __lang.profile_passwd_notmatch);
	} else {
		errormessage(id2, !modifypwd ? 'succeed' : profileTips);
	}
}

function checkemail(id) {
	errormessage(id);
	var email = trim($(id).value);
	if($(id).parentNode.className.match(/ p_right/) && (email == '' || email == lastemail)) {
		return;
	} else {
		lastemail = email;
	}
	if(email.match(/<|\"/ig)) {
		errormessage(id, __lang.Email_sensitivity);
		return;
	}
	var x = new Ajax();
	jQuery('#suc_' + id).removeClass('p_right');
	x.get('user.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkemail&email=' + email, function(s) {
		errormessage(id, s);
	});
}

function checkinvite() {
	errormessage('invitecode');
	var invitecode = trim($('invitecode').value);
	if(invitecode == '' || invitecode == lastinvitecode) {
		return;
	} else {
		lastinvitecode = invitecode;
	}
	if(invitecode.match(/<|\"/ig)) {
		errormessage('invitecode', __lang.js_invitation_sensitivity);
		return;
	}
	var x = new Ajax();
	jQuery('#suc_invitecode').removeClass('p_right');
	x.get('user.php?mod=ajax&inajax=yes&infloat=register&handlekey=register&ajaxmenu=1&action=checkinvitecode&invitecode=' + invitecode, function(s) {
		errormessage('invitecode', s);
	});
}
