/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

var attachexts = new Array();
var attachwh = new Array();

var insertType = 1;
var thumbwidth = parseInt(60);
var thumbheight = parseInt(60);
var extensions = 'jpg,jpeg,gif,png';
var forms;
var nowUid = 0;
var uploadStat = 0;
var picid = 0;
var nowid = 0;
var mainForm;
var successState = false;
function getExt(path) {
	return path.lastIndexOf('.') == -1 ? '' : path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
}
function delete_pic(obj,picid){
	document.getElementById('pic_container').removeChild(obj.parentNode.parentNode);
	var input=document.createElement('input');
	input.type='hidden';
	input.name='delete_pics[]';
	input.value=picid;
	document.getElementById('pic_delete').appendChild(input);
}
function delAttach(id) {
	document.getElementById('attachbody').removeChild(document.getElementById('attach_' + id).parentNode.parentNode.parentNode);
	if(document.getElementById('attachbody').innerHTML == '') {
		addAttach();
	}
	document.getElementById('localimgpreview_' + id + '_menu') ? document.body.removeChild(document.getElementById('localimgpreview_' + id + '_menu')) : null;
}

function addAttach() {
	newnode = document.getElementById('attachbodyhidden').rows[0].cloneNode(true);
	var id = nowid;
	var tags;
	tags = newnode.getElementsByTagName('form');
	for(var i=0;i<tags.length;i++) {
		
		if(tags[i] && tags[i].id == 'upload') {
			tags[i].id = 'upload_' + id;
		}
	}
	tags = newnode.getElementsByTagName('input');
	for(var i=0;i<tags.length;i++) {
		if(tags[i].name == 'attach') {
			tags[i].id = 'attach_' + id;
			tags[i].name = 'attach';
			tags[i].onchange = function() {insertAttach(id)};
			tags[i].unselectable = 'on';
		}
		
	}
	tags = newnode.getElementsByTagName('span');
	for(var i=0;i<tags.length;i++) {
		if(tags[i].id == 'localfile') {
			tags[i].id = 'localfile_' + id;
		}
	}
	nowid++;

	document.getElementById('attachbody').appendChild(newnode);
}

addAttach();

function insertAttach(id) {
	var localimgpreview = '';
	var path = document.getElementById('attach_' + id).value;
	var ext = getExt(path);
	var re = new RegExp("(^|\\s|,)" + ext + "($|\\s|,)", "ig");
	var localfile = document.getElementById('attach_' + id).value.substr(document.getElementById('attach_' + id).value.replace(/\\/g, '/').lastIndexOf('/') + 1);

	if(path == '') {
		return;
	}
	if(extensions != '' && (re.exec(extensions) == null || ext == '')) {
		alert(__lang.support_upload_pictures_extensions);
		return;
	}
	attachexts[id] = inArray(ext, ['gif', 'jpg', 'jpeg', 'png']) ? 2 : 1;

	var inhtml = '<table cellspacing="0" cellpadding="0" class="up_row"><tr>';
	
	inhtml += '<td><strong>' + localfile +'</strong>';
	inhtml += '</td><td class="o"><span id="showmsg' + id + '"><a href="javascript:;" onclick="delAttach(' + id + ');return false;" class="xi2">['+__lang.delete+']</a></span>';
	inhtml += '</td></tr></table>';

	document.getElementById('localfile_' + id).innerHTML = inhtml;
	document.getElementById('attach_' + id).style.display = 'none';

	addAttach();
}

function getPath(obj){
	if (obj) {
		if (BROWSER.ie && BROWSER.ie < 7) {
			obj.select();
			return document.selection.createRange().text;

		} else if(BROWSER.firefox) {
			if (obj.files) {
				return obj.files.item(0).getAsDataURL();
			}
			return obj.value;
		} else {
			return '';
		}
		return obj.value;
	}
}
function inArray(needle, haystack) {
	if(typeof needle == 'string') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function insertAttachimgTag(id) {
	edit_insert('[imgid=' + id + ']');
}

function uploadSubmit(obj) {
	obj.disabled = true;
	mainForm = obj.form;
	forms = document.getElementById('attachbody').getElementsByTagName("FORM");
	upload();
}

function upload() {
	if(typeof(forms[nowUid]) == 'undefined') return false;
	var nid = forms[nowUid].id.split('_');
	nid = nid[1];
	if(nowUid>0) {
		var upobj = document.getElementById('showmsg'+nowid);
		if(uploadStat==1) {
			upobj.innerHTML = __lang.upload_success;
			successState = true;
			var InputNode;
			try {
				var InputNode = document.createElement("<input type=\"hidden\" id=\"picid_" + picid + "\" value=\""+ picid +"\" name=\"picids[]\">");
			} catch(e) {
				var InputNode = document.createElement("input");
				InputNode.setAttribute("name", "picids[]");
				InputNode.setAttribute("type", "hidden");
				InputNode.setAttribute("id", "picid_" + picid);
				InputNode.setAttribute("value",picid);
			}
			mainForm.appendChild(InputNode);

		} else {
			upobj.style.color = "#f00";
			upobj.innerHTML = __lang.upload_failed+uploadStat;
		}
	}
	if(document.getElementById('showmsg'+nid) != null) {
		document.getElementById('showmsg'+nid).innerHTML = __lang.upload_await+'(<a href="javascript:;" onclick="forms[nowUid].submit();">'+__lang.founder_upgrade_reset+'</a>)';
		forms[nowUid].submit();
	} else if(nowUid+1 == forms.length) {
		window.onbeforeunload = null;
		mainForm.submit();
	}
	nowid = nid;
	nowUid++;
	uploadStat = 0;
}
