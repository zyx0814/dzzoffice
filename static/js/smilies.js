
function smilies_show(id, smcols, seditorkey) {
	if(seditorkey && !document.getElementById(seditorkey + 'sml_menu')) {
		var div = document.createElement("div");
		div.id = seditorkey + 'sml_menu';
		div.style.display = 'none';
		div.className = 'sllt';
		document.getElementById('append_parent').appendChild(div);
		var div = document.createElement("div");
		div.id = id;
		div.style.overflow = 'hidden';
		document.getElementById(seditorkey + 'sml_menu').appendChild(div);
		
	}
	if(typeof smilies_type == 'undefined') {
		var scriptNode = document.createElement("script");
		scriptNode.type = "text/javascript";
		//scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
		scriptNode.src = 'data/cache/smilies_var.js?';
		document.getElementById('append_parent').appendChild(scriptNode);
		if(BROWSER.ie) {
			scriptNode.onreadystatechange = function() {
				smilies_onload(id, smcols, seditorkey);
			};
		} else {
			scriptNode.onload = function() {
				smilies_onload(id, smcols, seditorkey);
			};
		}
	} else {
		smilies_onload(id, smcols, seditorkey);
	}
}

function smilies_onload(id, smcols, seditorkey) {
	seditorkey = !seditorkey ? '' : seditorkey;
	smile = getcookie('smile').split('D');
	if(typeof smilies_type == 'object') {
		if(smile[0] && smilies_array[smile[0]]) {
			CURRENTSTYPE = smile[0];
		} else {
			for(i in smilies_array) {
				CURRENTSTYPE = i;break;
			}
		}
		var k=0;
		smiliestype = '<div id="'+id+'_tb" class="tb tb_s cl"><ul class="nav nav-pills nav-pills-bottomguide" style="margin:0;border-bottom:1px solid #DDD;">';
		for(i in smilies_type) {
			key = i.substring(1);
			if(smilies_type[i][0]) {
				k++;
				smiliestype += '<li ' + (CURRENTSTYPE == key ? 'class="active"' : '') + ' id="'+seditorkey+'stype_'+key+'" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', '+key+', 1, \'' + seditorkey + '\');if(CURRENTSTYPE) {document.getElementById(\''+seditorkey+'stype_\'+CURRENTSTYPE).className=\'\';}this.className=\'active\';CURRENTSTYPE='+key+';doane(event);" ><a href="javascript:;" hidefocus="true">'+smilies_type[i][0]+'</a></li>';
			}
		}
		smiliestype += '</ul></div>';
		if(k<2) smiliestype='';//只有一组表情时不现实分类栏
		document.getElementById(id).innerHTML = smiliestype + '<div id="' + id + '_data" class="sllt-wrap"></div><div class="sllt_p" id="' + id + '_page"></div>';
		smilies_switch(id, smcols, CURRENTSTYPE, smile[1], seditorkey);
		smilies_fastdata = '';
		if(seditorkey == 'fastpost' && document.getElementById('fastsmilies') && smilies_fast) {
			var j = 0;
			for(i = 0;i < smilies_fast.length; i++) {
				if(j == 0) {
					smilies_fastdata += '<tr>';
				}
				j = ++j > 3 ? 0 : j;
				s = smilies_array[smilies_fast[i][0]][smilies_fast[i][1]][smilies_fast[i][2]];
				smilieimg = STATICURL + 'image/smiley/' + smilies_type['_' + smilies_fast[i][0]][1] + '/' + s[2];
				img[k] = new Image();
				img[k].src = smilieimg;
				smilies_fastdata += s ? '<td onmouseover="smilies_preview(\'' + seditorkey + '\', \'fastsmiliesdiv\', this, ' + s[5] + ')" onmouseout="document.getElementById(\'smilies_preview\').style.display = \'none\'" onclick="' + (typeof wysiwyg != 'undefined' ? 'insertSmiley(' + s[0] + ')': 'seditor_insertunit(\'' + seditorkey + '\', \'' + s[1].replace(/'/, '\\\'') + '\')') +
					'" id="' + seditorkey + 'smilie_' + s[0] + '_td"><img id="smilie_' + s[0] + '" width="' + s[3] +'" height="' + s[4] +'" src="' + smilieimg + '" alt="' + s[1] + '" />' : '<td>';
			}
			document.getElementById('fastsmilies').innerHTML = '<table cellspacing="0" cellpadding="0"><tr>' + smilies_fastdata + '</tr></table>';
		}
	}
}
function seditor_insertunit(key, text, textend, moveend, selappend) {
	if(document.getElementById(key)) {
		//document.getElementById(key).focus();
	}
	textend = isUndefined(textend) ? '' : textend;
	moveend = isUndefined(textend) ? 0 : moveend;
	selappend = isUndefined(selappend) ? 1 : selappend;
	startlen = strlen(text);
	endlen = strlen(textend);
	if(!isUndefined(document.getElementById(key).selectionStart)) {
		if(selappend) {
			var opn = document.getElementById(key).selectionStart + 0;
			if(textend != '') {
				text = text + document.getElementById(key).value.substring(document.getElementById(key).selectionStart, document.getElementById(key).selectionEnd) + textend;
			}
			document.getElementById(key).value = document.getElementById(key).value.substr(0, document.getElementById(key).selectionStart) + text + document.getElementById(key).value.substr(document.getElementById(key).selectionEnd);
			if(!moveend) {
				document.getElementById(key).selectionStart = opn + strlen(text) - endlen;
				document.getElementById(key).selectionEnd = opn + strlen(text) - endlen;
			}
		} else {
			text = text + textend;
			document.getElementById(key).value = document.getElementById(key).value.substr(0, document.getElementById(key).selectionStart) + text + document.getElementById(key).value.substr(document.getElementById(key).selectionEnd);
		}
	} else if(document.selection && document.selection.createRange) {
		var sel = document.selection.createRange();
		if(!sel.text.length && document.getElementById(key).sel) {
			sel = document.getElementById(key).sel;
			document.getElementById(key).sel = null;
		}
		if(selappend) {
			if(textend != '') {
				text = text + sel.text + textend;
			}
			sel.text = text.replace(/\r?\n/g, '\r\n');
			if(!moveend) {
				sel.moveStart('character', -endlen);
				sel.moveEnd('character', -endlen);
			}
			sel.select();
		} else {
			sel.text = text + textend;
		}
	} else {
		document.getElementById(key).value += text;
	}
//	console.log(document.getElementById(key));
	document.getElementById(key).focus();
	hideMenu();
	if(BROWSER.ie) {
		doane();
	}
}
function smilies_switch(id, smcols, type, page, seditorkey) {
	page = page ? page : 1;
	if(!smilies_array[type] || !smilies_array[type][page]) return;
	setcookie('smile', type + 'D' + page, 31536000);
	smiliesdata = '<table id="' + id + '_table" cellpadding="0" cellspacing="0"><tr>';
	j = k = 0;
	img = [];
	for(var i = 0; i < smilies_array[type][page].length; i++) {
		if(j >= smcols) {
			smiliesdata += '<tr>';
			j = 0;
		}
		s = smilies_array[type][page][i];
		smilieimg = STATICURL + 'image/smiley/' + smilies_type['_' + type][1] + '/' + s[2];
		img[k] = new Image();
		img[k].src = smilieimg;
		smiliesdata += s && s[0] ? '<td onmouseover="smilies_preview(\'' + seditorkey + '\', \'' + id + '\', this, ' + s[5] + ')" onclick="' + (typeof wysiwyg != 'undefined' ? 'insertSmiley(' + s[0] + ')': 'seditor_insertunit(\'' + seditorkey + '\', \'' + s[1].replace(/'/, '\\\'') + '\')') +
			'" id="' + seditorkey + 'smilie_' + s[0] + '_td"><img title="'+s[6]+'" alt="'+s[6]+'" id="smilie_' + s[0] + '" width="' + s[3] +'" height="' + s[4] +'" src="' + smilieimg + '" alt="' + s[1] + '" />' : '<td>';
		j++;k++;
	}
	smiliesdata += '</table>';
	smiliespage = '';
	if(smilies_array[type].length > 2) {
		prevpage = ((prevpage = parseInt(page) - 1) < 1) ? smilies_array[type].length - 1 : prevpage;
		nextpage = ((nextpage = parseInt(page) + 1) == smilies_array[type].length) ? 1 : nextpage;
		smiliespage = '<div class="pull-left"><a href="javascript:;" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + type + ', ' + prevpage + ', \'' + seditorkey + '\');doane(event);">上页</a>' +
			'<a href="javascript:;" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + type + ', ' + nextpage + ', \'' + seditorkey + '\');doane(event);">下页</a></div>' +
			page + '/' + (smilies_array[type].length - 1);
	}
	document.getElementById(id + '_data').innerHTML = smiliesdata;
	document.getElementById(id + '_page').innerHTML = smiliespage;
	
	//if(document.getElementById(id + '_tb')) document.getElementById(id + '_tb').style.width = smcols*(16+parseInt(s[3])) + 'px';
}

function smilies_preview(seditorkey, id, obj, w) {
	var menu = document.getElementById('smilies_preview');
	if(!menu) {
		menu = document.createElement('div');
		menu.id = 'smilies_preview';
		menu.className = 'sl_pv';
		menu.style.display = 'none';
		document.getElementById('append_parent').appendChild(menu);
	}
	menu.innerHTML = '<img width="' + w + '" src="' + obj.childNodes[0].src + '" />';
	mpos = fetchOffset(document.getElementById(id + '_data'));
	spos = fetchOffset(obj);
	pos = spos['left'] >= mpos['left'] + document.getElementById(id + '_data').offsetWidth / 2 ? '13' : '24';
	showMenu({'ctrlid':obj.id,'showid':id + '_data','menuid':menu.id,'pos':pos,'layer':3});
}