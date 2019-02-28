/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @version     DzzOffice 1.1 20140705
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','edge':'edge','rv':'rv','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser','ie11':'trident'});
if(BROWSER.ie11){
	BROWSER.ie=11;
	BROWSER.rv=11;
}else{
	BROWSER.rv=0;
}
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;
HTMLNODE = document.getElementsByTagName('head')[0].parentNode;
if(BROWSER.ie) {
	BROWSER.iemode = parseInt(typeof document.documentMode != 'undefined' ? document.documentMode : BROWSER.ie);
	HTMLNODE.className = 'ie_all ie' + BROWSER.iemode;
}
var CSSLOADED = [];
var JSLOADED = [];
var JSMENU = [];
JSMENU['active'] = [];
JSMENU['timer'] = [];
JSMENU['drag'] = [];
JSMENU['layer'] = 0;
JSMENU['zIndex'] = {'win':11200,'menu':11300,'dialog':11400,'prompt':11500};
JSMENU['float'] = '';
var CURRENTSTYPE = null;
var creditnotice = isUndefined(creditnotice) ? '' : creditnotice;
var cookiedomain = isUndefined(cookiedomain) ? '' : cookiedomain;
var cookiepath = isUndefined(cookiepath) ? '' : cookiepath;
var EXTRAFUNC = [], EXTRASTR = '';
EXTRAFUNC['showmenu'] = [];


var USERABOUT_BOX = true;
var USERCARDST = null;
var CLIPBOARDSWFDATA = '';
var NOTICETITLE = [];
var NOTICECURTITLE = document.title;

if(BROWSER.firefox && window.HTMLElement) {
	HTMLElement.prototype.__defineGetter__( "innerText", function(){
		var anyString = "";
		var childS = this.childNodes;
		for(var i=0; i <childS.length; i++) {
			if(childS[i].nodeType==1) {
				anyString += childS[i].tagName=="BR" ? '\n' : childS[i].innerText;
			} else if(childS[i].nodeType==3) {
				anyString += childS[i].nodeValue;
			}
		}
		return anyString;
	});
	HTMLElement.prototype.__defineSetter__( "innerText", function(sText){
		this.textContent=sText;
	});
	HTMLElement.prototype.__defineSetter__('outerHTML', function(sHTML) {
			var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var df = r.createContextualFragment(sHTML);
		this.parentNode.replaceChild(df,this);
		return sHTML;
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		var attr;
		var attrs = this.attributes;
		var str = '<' + this.tagName.toLowerCase();
		for(var i = 0;i < attrs.length;i++){
			attr = attrs[i];
			if(attr.specified)
			str += ' ' + attr.name + '="' + attr.value + '"';
		}
		if(!this.canHaveChildren) {
			return str + '>';
		}
		return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
		});

	HTMLElement.prototype.__defineGetter__('canHaveChildren', function() {
		switch(this.tagName.toLowerCase()) {
			case 'area':case 'base':case 'basefont':case 'col':case 'frame':case 'hr':case 'img':case 'br':case 'input':case 'isindex':case 'link':case 'meta':case 'param':
			return false;
			}
		return true;
	});
}

//判断当前窗口是否激活，当窗口激活时不使用桌面通知
var CurrentActive;
if ("onfocusin" in document){//for IE 
	document.onfocusin = function() {
		CurrentActive = true;
		
	};
	document.onfocusout = function() {
		CurrentActive = false;
	};
} else {
	window.onfocus = function() {
		CurrentActive = true;
	}
	window.onblur = function() {
		CurrentActive = false;
	}
}

function $C(classname, ele, tag) {
	var returns = [];
	ele = ele || document;
	tag = tag || '*';
	if(ele.getElementsByClassName) {
		var eles = ele.getElementsByClassName(classname);
		if(tag != '*') {
			for (var i = 0, L = eles.length; i < L; i++) {
				if (eles[i].tagName.toLowerCase() == tag.toLowerCase()) {
						returns.push(eles[i]);
				}
			}
		} else {
			returns = eles;
		}
	}else {
		eles = ele.getElementsByTagName(tag);
		var pattern = new RegExp("(^|\\s)"+classname+"(\\s|$)");
		for (i = 0, L = eles.length; i < L; i++) {
				if (pattern.test(eles[i].className)) {
						returns.push(eles[i]);
				}
		}
	}
	return returns;
}

function _attachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.addEventListener) {
		obj.addEventListener(evt, func, false);
	} else if(eventobj.attachEvent) {
		obj.attachEvent('on' + evt, func);
	}
}

function _detachEvent(obj, evt, func, eventobj) {
	eventobj = !eventobj ? obj : eventobj;
	if(obj.removeEventListener) {
		obj.removeEventListener(evt, func, false);
	} else if(eventobj.detachEvent) {
		obj.detachEvent('on' + evt, func);
	}
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s|:)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function getEvent() {
	if(document.all) return window.event;
	func = getEvent.caller;
	while(func != null) {
		var arg0 = func.arguments[0];
		if (arg0) {
			if((arg0.constructor  == Event || arg0.constructor == MouseEvent) || (typeof(arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
				return arg0;
			}
		}
		func=func.caller;
	}
	return null;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}
function formatSize(bytes){
	var i = -1; 
	do {
		bytes = bytes / 1024;
		i++;  
	} while (bytes > 99);
   
	return Math.max(bytes, 0).toFixed(1) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];          
};

function trim(str) {
	return (str + '').replace(/(\s+)$/g, '').replace(/^\s+/g, '');
}

function strlen(str) {
	return (BROWSER.ie && str.indexOf('\n') != -1) ? str.replace(/\r?\n/g, '_').length : str.length;
}

function mb_strlen(str) {
	var len = 0;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
	}
	return len;
}

function mb_cutstr(str, maxlen, dot) {
	var len = 0;
	var ret = '';
	var dot = (dot != '' && !dot) ? '...' : dot;

	maxlen = maxlen - dot.length;
	for(var i = 0; i < str.length; i++) {
		len += str.charCodeAt(i) < 0 || str.charCodeAt(i) > 255 ? (charset == 'utf-8' ? 3 : 2) : 1;
		if(len > maxlen) {
			ret += dot;
			break;
		}
		ret += str.substr(i, 1);
	}
	return ret;
}
function mb_cutstr_nohtml(str, maxlen,dot) {
	str = strip_tags(str);
	return mb_cutstr(str, maxlen, dot)
};
function strip_tags (input, allowed) {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Luke Godfrey
  // +      input by: Pul
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Onno Marsman
  // +      input by: Alex
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +      input by: Marc Palau
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +      input by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Eric Nagel
  // +      input by: Bobby Drake
  // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Tomasz Wesolowski
  // +      input by: Evertjan Garretsen
  // +    revised by: Rafa? Kukawski (http://blog.kukawski.pl/)
  // *     example 1: strip_tags('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
  // *     returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
  // *     example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
  // *     returns 2: '<p>Kevin van Zonneveld</p>'
  // *     example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
  // *     returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
  // *     example 4: strip_tags('1 < 5 5 > 1');
  // *     returns 4: '1 < 5 5 > 1'
  // *     example 5: strip_tags('1 <br/> 1');
  // *     returns 5: '1  1'
  // *     example 6: strip_tags('1 <br/> 1', '<br>');
  // *     returns 6: '1  1'
  // *     example 7: strip_tags('1 <br/> 1', '<br><br/>');
  // *     returns 7: '1 <br/> 1'
  allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
  var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
    commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
  return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
    return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
  });
}


function preg_replace(search, replace, str, regswitch) {
	var regswitch = !regswitch ? 'ig' : regswitch;
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], regswitch);
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

function htmlspecialchars(str) {
	return preg_replace(['&', '<', '>', '"'], ['&', '<', '>', '"'], str);
}

function display(id) {
	var obj = document.getElementById(id);
	if(obj.style.visibility) {
		obj.style.visibility = obj.style.visibility == 'visible' ? 'hidden' : 'visible';
	} else {
		obj.style.display = obj.style.display == '' ? 'none' : '';
	}
}

function checkall(form, prefix, checkall) {
	var checkall = checkall ? checkall : 'chkall';
	count = 0;
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name && e.name != checkall && !e.disabled && (!prefix || (prefix && e.name.match(prefix)))) {
			e.checked = form.elements[checkall].checked;
			if(e.checked) {
				count++;
			}
		}
	}
	return count;
}

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	if(seconds) {
		var expires = new Date();
		expires.setTime(expires.getTime() + seconds * 1000);
	}
	domain = !domain ? cookiedomain : domain;
	path = !path ? cookiepath : path;
	document.cookie = escape(cookiepre + cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function getcookie(name, nounescape) {
	name = cookiepre + name;
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	if(cookie_start == -1) {
		return '';
	} else {
		var v = document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
		return !nounescape ? unescape(v) : v;
	}
}

function Ajax(recvType, waitId) {

	var aj = new Object();

	aj.loading = __lang.please_wait;
	aj.recvType = recvType ? recvType : 'XML';
	aj.waitId = waitId ? document.getElementById(waitId) : null;

	aj.resultHandle = null;
	aj.sendString = '';
	aj.targetUrl = '';

	aj.setLoading = function(loading) {
		if(typeof loading !== 'undefined' && loading !== null) aj.loading = loading;
	};

	aj.setRecvType = function(recvtype) {
		aj.recvType = recvtype;
	};

	aj.setWaitId = function(waitid) {
		aj.waitId = typeof waitid == 'object' ? waitid : document.getElementById(waitid);
	};

	aj.createXMLHttpRequest = function() {
		var request = false;
		if(window.XMLHttpRequest) {
			request = new XMLHttpRequest();
			if(request.overrideMimeType) {
				request.overrideMimeType('text/xml');
			}
		} else if(window.ActiveXObject) {
			var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request = new ActiveXObject(versions[i]);
					if(request) {
						return request;
					}
				} catch(e) {}
			}
		}
		return request;
	};

	aj.XMLHttpRequest = aj.createXMLHttpRequest();
	aj.showLoading = function() {
		if(aj.waitId && (aj.XMLHttpRequest.readyState != 4 || aj.XMLHttpRequest.status != 200)) {
			aj.waitId.style.display = '';
			aj.waitId.innerHTML = '<span><img src="' + IMGDIR + '/loading.gif" class="vm"> ' + aj.loading + '</span>';
		}
	};

	aj.processHandle = function() {
		if(aj.XMLHttpRequest.readyState == 4 && aj.XMLHttpRequest.status == 200) {
			if(aj.waitId) {
				aj.waitId.style.display = 'none';
			}
			if(aj.recvType == 'HTML') {
				aj.resultHandle(aj.XMLHttpRequest.responseText, aj);
			} else if(aj.recvType == 'XML') {
				if(!aj.XMLHttpRequest.responseXML || !aj.XMLHttpRequest.responseXML.lastChild || aj.XMLHttpRequest.responseXML.lastChild.localName == 'parsererror') {
					aj.resultHandle('<a href="' + aj.targetUrl + '" target="_blank" style="color:red">'+__lang.internal_error_unable_display_content+'</a>' , aj);
				} else {
					aj.resultHandle(aj.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue, aj);
				}
			} else if(aj.recvType == 'JSON') {
				var s = null;
				try {
					s = (new Function("return ("+aj.XMLHttpRequest.responseText+")"))();
				} catch (e) {
					s = null;
				}
				aj.resultHandle(s, aj);
			}
		}
	};

	aj.get = function(targetUrl, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		var attackevasive = isUndefined(attackevasive) ? 0 : attackevasive;
		if(window.XMLHttpRequest) {
			aj.XMLHttpRequest.open('GET', aj.targetUrl);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send(null);
		} else {
			aj.XMLHttpRequest.open("GET", targetUrl, true);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send();
		}
	};
	aj.post = function(targetUrl, sendString, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.sendString = sendString;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		aj.XMLHttpRequest.open('POST', targetUrl);
		aj.XMLHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		aj.XMLHttpRequest.send(aj.sendString);
	};
	aj.getJSON = function(targetUrl, resultHandle) {
		aj.setRecvType('JSON');
		aj.get(targetUrl+'&ajaxdata=json', resultHandle);
	};
	aj.getHTML = function(targetUrl, resultHandle) {
		aj.setRecvType('HTML');
		aj.get(targetUrl+'&ajaxdata=html', resultHandle);
	};
	return aj;
}

function getHost(url) {
	var host = "null";
	if(typeof url == "undefined"|| null == url) {
		url = window.location.href;
	}
	var regex = /^\w+\:\/\/([^\/]*).*/;
	var match = url.match(regex);
	if(typeof match != "undefined" && null != match) {
		host = match[1];
	}
	return host;
}

function hostconvert(url) {
	if(!url.match(/^https?:\/\//)) url = SITEURL + url;
	var url_host = getHost(url);
	var cur_host = getHost().toLowerCase();
	if(url_host && cur_host != url_host) {
		url = url.replace(url_host, cur_host);
	}
	return url;
}

function newfunction(func) {
	var args = [];
	for(var i=1; i<arguments.length; i++) args.push(arguments[i]);
	return function(event) {
		doane(event);
		window[func].apply(window, args);
		return false;
	}
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			if(arr1) appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

var safescripts = {}, evalscripts = [];
function safescript(id, call, seconds, times, timeoutcall, endcall, index) {
	seconds = seconds || 1000;
	times = times || 0;
	var checked = true;
	try {
		if(typeof call == 'function') {
			call();
		} else {
			eval(call);
		}
	} catch(e) {
		checked = false;
	}
	if(!checked) {
		if(!safescripts[id] || !index) {
			safescripts[id] = safescripts[id] || [];
			safescripts[id].push({
				'times':0,
				'si':setInterval(function () {
					safescript(id, call, seconds, times, timeoutcall, endcall, safescripts[id].length);
				}, seconds)
			});
		} else {
			index = (index || 1) - 1;
			safescripts[id][index]['times']++;
			if(safescripts[id][index]['times'] >= times) {
				clearInterval(safescripts[id][index]['si']);
				if(typeof timeoutcall == 'function') {
					timeoutcall();
				} else {
					eval(timeoutcall);
				}
			}
		}
	} else {
		try {
			index = (index || 1) - 1;
			if(safescripts[id][index]['si']) {
				clearInterval(safescripts[id][index]['si']);
			}
			if(typeof endcall == 'function') {
				endcall();
			} else {
				eval(endcall);
			}
		} catch(e) {}
	}
}


function appendscript(src, text, reload, charset) {
	var id = dhash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && document.getElementById(id)) {
		document.getElementById(id).parentNode.removeChild(document.getElementById(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.setAttribute('ajaxappend','1');
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (BROWSER.firefox ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
			scriptNode.onloadDone = false;
			scriptNode.onload = function () {
				scriptNode.onloadDone = true;
				JSLOADED[src] = 1;
			};
			scriptNode.onreadystatechange = function () {
				if((scriptNode.readyState == 'loaded' || scriptNode.readyState == 'complete') && !scriptNode.onloadDone) {
					scriptNode.onloadDone = true;
					JSLOADED[src] = 1;
				}
			};
		} else if(text){
			scriptNode.text = text;
		}
		document.getElementsByTagName('head')[0].appendChild(scriptNode);
	} catch(e) {}
}

function stripscript(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig, '');
}

function ajaxupdateevents(obj, tagName) {
	tagName = tagName ? tagName : 'A';
	var objs = obj.getElementsByTagName(tagName);
	for(k in objs) {
		var o = objs[k];
		ajaxupdateevent(o);
	}
}

function ajaxupdateevent(o) {
	if(typeof o == 'object' && o.getAttribute) {
		if(o.getAttribute('ajaxtarget')) {
			if(!o.id) o.id = Math.random();
			var ajaxevent = o.getAttribute('ajaxevent') ? o.getAttribute('ajaxevent') : 'click';
			var ajaxurl = o.getAttribute('ajaxurl') ? o.getAttribute('ajaxurl') : o.href;
			_attachEvent(o, ajaxevent, newfunction('ajaxget', ajaxurl, o.getAttribute('ajaxtarget'), o.getAttribute('ajaxwaitid'), o.getAttribute('ajaxloading'), o.getAttribute('ajaxdisplay')));
			if(o.getAttribute('ajaxfunc')) {
				o.getAttribute('ajaxfunc').match(/(\w+)\((.+?)\)/);
				_attachEvent(o, ajaxevent, newfunction(RegExp.$1, RegExp.$2));
			}
		}
	}
}

function ajaxget(url, showid, waitid, loading, display, recall) {
	waitid = typeof waitid == 'undefined' || waitid === null ? showid : waitid;
	var x = new Ajax();
	x.setLoading(loading);
	x.setWaitId(waitid);
	x.display = typeof display == 'undefined' || display == null ? '' : display;
	x.showId = document.getElementById(showid);

	if(url.substr(strlen(url) - 1) == '#') {
		url = url.substr(0, strlen(url) - 1);
		x.autogoto = 1;
	}
	var url = url + '&inajax=1&ajaxtarget=' + showid;
	if(url && url.indexOf('?')==-1){
		url=url.replace(/&/i,'?');
	}
	x.get(url, function(s, x) {
		var evaled = false;
		if(s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			if(x.showId) {
				x.showId.style.display = x.display;
				ajaxinnerhtml(x.showId, s);
				ajaxupdateevents(x.showId);
				if(x.autogoto) scroll(0, x.showId.offsetTop);
			}
		}

		ajaxerror = null;
		if(recall && typeof recall == 'function') {
			recall();
		} else if(recall) {
			eval(recall);
		}
		if(!evaled) evalscript(s);
	});
}

function ajaxpost(formid, showid, waitid, showidclass, submitbtn, recall) {
	var waitid = typeof waitid == 'undefined' || waitid === null ? showid : (waitid !== '' ? waitid : '');
	var showidclass = !showidclass ? '' : showidclass;
	var ajaxframeid = 'ajaxframe';
	var ajaxframe = document.getElementById(ajaxframeid);
	var curform = document.getElementById(formid);
	var formtarget = curform.target;
	var handleResult = function() {
		var s = '';
		var evaled = false;

		showloading('none');
		try {
			s = document.getElementById(ajaxframeid).contentWindow.document.XMLDocument.text;
		} catch(e) {
			try {
				s = document.getElementById(ajaxframeid).contentWindow.document.documentElement.firstChild.wholeText;
			} catch(e) {
				try {
					s = document.getElementById(ajaxframeid).contentWindow.document.documentElement.firstChild.nodeValue;
				} catch(e) {
					s = __lang.internal_error_unable_display_content;
				}
			}
		}
		
		if(s != '' && s.indexOf('ajaxerror') != -1) {
			evalscript(s);
			evaled = true;
		}
		if(showidclass) {
			if(showidclass != 'onerror') {
				document.getElementById(showid).className = showidclass;
			} else {
				showError(s);
				ajaxerror = true;
			}
		}
		if(submitbtn) {
			submitbtn.disabled = false;
		}
		if(!evaled && (typeof ajaxerror == 'undefined' || !ajaxerror)) {
			ajaxinnerhtml(document.getElementById(showid), s);
		}
		ajaxerror = null;
		if(curform) curform.target = formtarget;
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
		if(!evaled) evalscript(s);
		ajaxframe.loading = 0;
		if(!BROWSER.firefox || BROWSER.safari) {
			document.getElementById('append_parent').removeChild(ajaxframe.parentNode);
		} else {
			setTimeout(
				function(){
					document.getElementById('append_parent').removeChild(ajaxframe.parentNode);
				},
				100
			);
		}
	};
	if(!ajaxframe) {
		var div = document.createElement('div');
		div.style.display = 'none';
		div.innerHTML = '<iframe name="' + ajaxframeid + '" id="' + ajaxframeid + '" loading="1"></iframe>';
		document.getElementById('append_parent').appendChild(div);
		ajaxframe = document.getElementById(ajaxframeid);
	} else if(ajaxframe.loading) {
		return false;
	}

	_attachEvent(ajaxframe, 'load', handleResult);

	showloading();
	curform.target = ajaxframeid;
	var action = curform.getAttribute('action');
	action = hostconvert(action).replace(/(&|&|\?)inajax\=1/g, '');
	var s=(action.indexOf('?') != -1) ? '&' :'?';
	curform.action = action+s+'inajax=1'; 
	curform.submit();
	if(submitbtn) {
		submitbtn.disabled = true;
	}
	doane();
	return false;
}

function ajaxmenu(ctrlObj, timeout, cache, duration, pos, recall, idclass, contentclass) {
	if(!ctrlObj.getAttribute('mid')) {
		var ctrlid = ctrlObj.id;
		if(!ctrlid) {
			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	} else {
		var ctrlid = ctrlObj.getAttribute('mid');
		if(!ctrlObj.id) {
			ctrlObj.id = 'ajaxid_' + Math.random();
		}
	}
	var menuid = ctrlid + '_menu';
	var menu = document.getElementById(menuid);
	if(isUndefined(timeout)) timeout = 3000;
	if(isUndefined(cache)) cache = 1;
	if(isUndefined(pos)) pos = '43';
	if(isUndefined(duration)) duration = timeout > 0 ? 0 : 3;
	if(isUndefined(idclass)) idclass = 'p_pop';
	if(isUndefined(contentclass)) contentclass = 'p_opt';
	var func = function() {
		showMenu({'ctrlid':ctrlObj.id,'menuid':menuid,'duration':duration,'timeout':timeout,'pos':pos,'cache':cache,'layer':2});
		if(typeof recall == 'function') {
			recall();
		} else {
			eval(recall);
		}
	};

	if(menu) {
		if(menu.style.display == '') {
			hideMenu(menuid);
		} else {
			func();
		}
	} else {
		menu = document.createElement('div');
		menu.id = menuid;
		menu.style.display = 'none';
		menu.className = idclass;
		menu.innerHTML = '<div class="' + contentclass + '" id="' + menuid + '_content"></div>';
		document.getElementById('append_parent').appendChild(menu);
		var url = (!isUndefined(ctrlObj.attributes['shref']) ? ctrlObj.attributes['shref'].value : (!isUndefined(ctrlObj.href) ? ctrlObj.href : ctrlObj.attributes['href'].value));
		url += (url.indexOf('?') != -1 ? '&' :'?') + 'ajaxmenu=1';
		ajaxget(url, menuid + '_content', 'ajaxwaitid', '', '', func);
	}
	doane();
}

function dhash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}


function showloading(display, waiting) {
	var display = display ? display : 'block';
	var waiting = waiting ? waiting : __lang.please_wait;
	document.getElementById('ajaxwaitid').innerHTML = waiting;
	document.getElementById('ajaxwaitid').style.display = display;
}

function ajaxinnerhtml(showid, s) {
	if(showid.tagName != 'TBODY') {
		showid.innerHTML = s;
	} else {
		while(showid.firstChild) {
			showid.firstChild.parentNode.removeChild(showid.firstChild);
		}
		var div1 = document.createElement('DIV');
		div1.id = showid.id+'_div';
		div1.innerHTML = '<table><tbody id="'+showid.id+'_tbody">'+s+'</tbody></table>';
		document.getElementById('append_parent').appendChild(div1);
		var trs = div1.getElementsByTagName('TR');
		var l = trs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(trs[0]);
		}
		var inputs = div1.getElementsByTagName('INPUT');
		var l = inputs.length;
		for(var i=0; i<l; i++) {
			showid.appendChild(inputs[0]);
		}
		div1.parentNode.removeChild(div1);
	}
}

function doane(event, preventDefault, stopPropagation) {
	var preventDefault = isUndefined(preventDefault) ? 1 : preventDefault;
	var stopPropagation = isUndefined(stopPropagation) ? 1 : stopPropagation;
	e = event ? event : window.event;
	
	if(!e) {
		return null;
	}
	if(preventDefault) {
		if(e.preventDefault) {
			e.preventDefault();
		} else {
			e.returnValue = false;
		}
	}
	if(stopPropagation) {
		if(e.stopPropagation) {
			e.stopPropagation();
		} else {
			e.cancelBubble = true;
		}
	}
	return e;
}

function loadcss(cssname,url) {
	if(!CSSLOADED[cssname]) {
		if(!document.getElementById('css_' + cssname)) {
			css = document.createElement('link');
			css.id = 'css_' + cssname,
			css.type = 'text/css';
			css.rel = 'stylesheet';
			css.href = url+'?' + VERHASH;
			var headNode = document.getElementsByTagName("head")[0];
			headNode.appendChild(css);
		} else {
			document.getElementById('css_' + cssname).href =url+'?' + VERHASH;
		}
		CSSLOADED[cssname] = 1;
	}
}

function showMenu(v) {
	var ctrlid = isUndefined(v['ctrlid']) ? v : v['ctrlid'];
	var showid = isUndefined(v['showid']) ? ctrlid : v['showid'];
	var menuid = isUndefined(v['menuid']) ? showid + '_menu' : v['menuid'];
	var ctrlObj = document.getElementById(ctrlid);
	var menuObj = document.getElementById(menuid);
	if(!menuObj) return;
	var mtype = isUndefined(v['mtype']) ? 'menu' : v['mtype'];
	var evt = isUndefined(v['evt']) ? 'mouseover' : v['evt'];
	var pos = isUndefined(v['pos']) ? '43' : v['pos'];
	var layer = isUndefined(v['layer']) ? 1 : v['layer'];
	var duration = isUndefined(v['duration']) ? 2 : v['duration'];
	var timeout = isUndefined(v['timeout']) ? 250 : v['timeout'];
	var maxh = isUndefined(v['maxh']) ? 600 : v['maxh'];
	var cache = isUndefined(v['cache']) ? 1 : v['cache'];
	var drag = isUndefined(v['drag']) ? '' : v['drag'];
	var dragobj = drag && document.getElementById(drag) ? document.getElementById(drag) : menuObj;
	var fade = isUndefined(v['fade']) ? 0 : v['fade'];
	var cover = isUndefined(v['cover']) ? 0 : v['cover'];
	var zindex = isUndefined(v['zindex']) ? JSMENU['zIndex']['menu'] : v['zindex'];
	var ctrlclass = isUndefined(v['ctrlclass']) ? '' : v['ctrlclass'];
	var winhandlekey = isUndefined(v['win']) ? '' : v['win'];
	zindex = cover ? zindex + 500 : zindex;
	if(typeof JSMENU['active'][layer] == 'undefined') {
		JSMENU['active'][layer] = [];
	}

	for(i in EXTRAFUNC['showmenu']) {
		try {
			eval(EXTRAFUNC['showmenu'][i] + '()');
		} catch(e) {}
	}

	if(evt == 'click' && in_array(menuid, JSMENU['active'][layer]) && mtype != 'win') {
		hideMenu(menuid, mtype);
		return;
	}
	if(mtype == 'menu') {
		hideMenu(layer, mtype);
	}

	if(ctrlObj) {
		if(!ctrlObj.getAttribute('initialized')) {
			ctrlObj.setAttribute('initialized', true);
			ctrlObj.unselectable = true;

			ctrlObj.outfunc = typeof ctrlObj.onmouseout == 'function' ? ctrlObj.onmouseout : null;
			ctrlObj.onmouseout = function() {
				if(this.outfunc) this.outfunc();
				if(duration < 3 && !JSMENU['timer'][menuid]) {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype);
					}, timeout);
				}
			};

			ctrlObj.overfunc = typeof ctrlObj.onmouseover == 'function' ? ctrlObj.onmouseover : null;
			ctrlObj.onmouseover = function(e) {
				doane(e);
				if(this.overfunc) this.overfunc();
				if(evt == 'click') {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				} else {
					for(var i in JSMENU['timer']) {
						if(JSMENU['timer'][i]) {
							clearTimeout(JSMENU['timer'][i]);
							JSMENU['timer'][i] = null;
						}
					}
				}
			};
		}
	}

	if(!menuObj.getAttribute('initialized')) {
		menuObj.setAttribute('initialized', true);
		menuObj.ctrlkey = ctrlid;
		menuObj.mtype = mtype;
		menuObj.layer = layer;
		menuObj.cover = cover;
		if(ctrlObj && ctrlObj.getAttribute('fwin')) {menuObj.scrolly = true;}
		menuObj.style.position = 'absolute';
		menuObj.style.zIndex = zindex + layer;
		menuObj.onclick = function(e) {
			return doane(e, 0, 1);
		};
		if(duration < 3) {
			if(duration > 1) {
				menuObj.onmouseover = function() {
					clearTimeout(JSMENU['timer'][menuid]);
					JSMENU['timer'][menuid] = null;
				};
			}
			if(duration != 1) {
				menuObj.onmouseout = function() {
					JSMENU['timer'][menuid] = setTimeout(function () {
						hideMenu(menuid, mtype);
					}, timeout);
				};
			}
		}
		if(cover) {
			var coverObj = document.createElement('div');
			coverObj.id = menuid + '_cover';
			coverObj.style.position = 'absolute';
			coverObj.style.zIndex = menuObj.style.zIndex - 1;
			coverObj.style.left = coverObj.style.top = '0px';
			coverObj.style.width = '100%';
			coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			coverObj.style.backgroundColor = '#000';
			coverObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=50)';
			coverObj.style.opacity = 0.5;
			coverObj.onclick = function () {hideMenu();};
			document.getElementById('append_parent').appendChild(coverObj);
			_attachEvent(window, 'load', function () {
				coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + 'px';
			}, document);
		}
	}
	if(drag) {
		dragobj.style.cursor = 'move';
		dragobj.onmousedown = function(event) {try{dragMenu(menuObj, event, 1);}catch(e){}};
	}

	if(cover) document.getElementById(menuid + '_cover').style.display = '';
	if(fade) {
		var O = 0;
		var fadeIn = function(O) {
			if(O > 100) {
				clearTimeout(fadeInTimer);
				return;
			}
			menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
			menuObj.style.opacity = O / 100;
			O += 20;
			var fadeInTimer = setTimeout(function () {
				fadeIn(O);
			}, 40);
		};
		fadeIn(O);
		menuObj.fade = true;
	} else {
		menuObj.fade = false;
	}
	menuObj.style.display = '';
	if(ctrlObj && ctrlclass) {
		ctrlObj.className += ' ' + ctrlclass;
		menuObj.setAttribute('ctrlid', ctrlid);
		menuObj.setAttribute('ctrlclass', ctrlclass);
	}
	if(pos != '*') {
		setMenuPosition(showid, menuid, pos);
	}
	if(BROWSER.ie && BROWSER.ie < 7 && winhandlekey && document.getElementById('fwin_' + winhandlekey)) {
		document.getElementById(menuid).style.left = (parseInt(document.getElementById(menuid).style.left) - parseInt(document.getElementById('fwin_' + winhandlekey).style.left)) + 'px';
		document.getElementById(menuid).style.top = (parseInt(document.getElementById(menuid).style.top) - parseInt(document.getElementById('fwin_' + winhandlekey).style.top)) + 'px';
	}
	if(maxh && menuObj.scrollHeight > maxh) {
		menuObj.style.height = maxh + 'px';
		if(BROWSER.opera) {
			menuObj.style.overflow = 'auto';
		} else {
			menuObj.style.overflowY = 'auto';
		}
	}

	if(!duration) {
		setTimeout('hideMenu(\'' + menuid + '\', \'' + mtype + '\')', timeout);
	}

	if(!in_array(menuid, JSMENU['active'][layer])) JSMENU['active'][layer].push(menuid);
	menuObj.cache = cache;
	if(layer > JSMENU['layer']) {
		JSMENU['layer'] = layer;
	}
	var hasshow = function(ele) {
		while(ele.parentNode && ((typeof(ele['currentStyle']) === 'undefined') ? window.getComputedStyle(ele,null) : ele['currentStyle'])['display'] !== 'none') {
			ele = ele.parentNode;
		}
		if(ele === document) {
			return true;
		} else {
			return false;
		}
	};
	if(!menuObj.getAttribute('disautofocus')) {
		try{
			var focused = false;
			var tags = ['input', 'select', 'textarea', 'button', 'a'];
			for(var i = 0; i < tags.length; i++) {
				var _all = menuObj.getElementsByTagName(tags[i]);
				if(_all.length) {
					for(j = 0; j < _all.length; j++) {
						if((!_all[j]['type'] || _all[j]['type'] != 'hidden') && hasshow(_all[j])) {
							_all[j].className += ' hidefocus';
							_all[j].focus();
							focused = true;
							var cobj = _all[j];
							_attachEvent(_all[j], 'blur', function (){cobj.className = trim(cobj.className.replace(' hidefocus', ''));});
							break;
						}
					}
				}
				if(focused) {
					break;
				}
			}
		} catch (e) {
		}
	}
}
var delayShowST = null;
function delayShow(ctrlObj, call, time) {
	if(typeof ctrlObj == 'object') {
		var ctrlid = ctrlObj.id;
		call = call || function () {showMenu(ctrlid);};
	}
	var time = isUndefined(time) ? 500 : time;
	delayShowST = setTimeout(function () {
		if(typeof call == 'function') {
			call();
		} else {
			eval(call);
		}
	}, time);
	if(!ctrlObj.delayinit) {
		_attachEvent(ctrlObj, 'mouseout', function() {clearTimeout(delayShowST);});
		ctrlObj.delayinit = 1;
	}
}

var dragMenuDisabled = false;
function dragMenu(menuObj, e, op) {
	e = e ? e : window.event;
	if(op == 1) {
		if(dragMenuDisabled || in_array(e.target ? e.target.tagName : e.srcElement.tagName, ['TEXTAREA', 'INPUT', 'BUTTON', 'SELECT'])) {
			return;
		}
		JSMENU['drag'] = [e.clientX, e.clientY];
		JSMENU['drag'][2] = parseInt(menuObj.style.left);
		JSMENU['drag'][3] = parseInt(menuObj.style.top);
		document.onmousemove = function(e) {try{dragMenu(menuObj, e, 2);}catch(err){}};
		document.onmouseup = function(e) {try{dragMenu(menuObj, e, 3);}catch(err){}};
		doane(e);
	}else if(op == 2 && JSMENU['drag'][0]) {
		var menudragnow = [e.clientX, e.clientY];
		menuObj.style.left = (JSMENU['drag'][2] + menudragnow[0] - JSMENU['drag'][0]) + 'px';
		menuObj.style.top = (JSMENU['drag'][3] + menudragnow[1] - JSMENU['drag'][1]) + 'px';
		menuObj.removeAttribute('top_');menuObj.removeAttribute('left_');
		doane(e);
	}else if(op == 3) {
		JSMENU['drag'] = [];
		document.onmousemove = null;
		document.onmouseup = null;
	}
}
function setMenuPosition(showid, menuid, pos) {
	var showObj = document.getElementById(showid);
	var menuObj = menuid ? document.getElementById(menuid) : document.getElementById(showid + '_menu');
	if(isUndefined(pos) || !pos) pos = '43';
	var basePoint = parseInt(pos.substr(0, 1));
	var direction = parseInt(pos.substr(1, 1));
	var important = pos.indexOf('!') != -1 ? 1 : 0;
	var sxy = 0, sx = 0, sy = 0, sw = 0, sh = 0, ml = 0, mt = 0, mw = 0, mcw = 0, mh = 0, mch = 0, bpl = 0, bpt = 0;

	if(!menuObj || (basePoint > 0 && !showObj)) return;
	if(showObj) {
		sxy = jQuery(showObj).offset();
		sx = sxy['left'];
		sy = sxy['top'];
		sw = jQuery(showObj).outerWidth(true);
		sh =jQuery(showObj).outerHeight(true);
	}
	mw = menuObj.offsetWidth;
	mcw = menuObj.clientWidth;
	mh = menuObj.offsetHeight;
	mch = menuObj.clientHeight;

	switch(basePoint) {
		case 1:
			bpl = sx;
			bpt = sy;
			break;
		case 2:
			bpl = sx + sw;
			bpt = sy;
			break;
		case 3:
			bpl = sx + sw;
			bpt = sy + sh;
			break;
		case 4:
			bpl = sx;
			bpt = sy + sh;
			break;
	}
	switch(direction) {
		case 0:
			menuObj.style.left = (document.body.clientWidth - menuObj.clientWidth) / 2 + 'px';
			mt = (document.documentElement.clientHeight - menuObj.clientHeight) / 2;
			break;
		case 1:
			ml = bpl - mw;
			mt = bpt - mh;
			break;
		case 2:
			ml = bpl;
			mt = bpt - mh;
			break;
		case 3:
			ml = bpl;
			mt = bpt;
			break;
		case 4:
			ml = bpl - mw;
			mt = bpt;
			break;
	}
	var scrollTop =0;// Math.max(document.documentElement.scrollTop, document.body.scrollTop);
	var scrollLeft =0;// Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
	if(!important) {
		if(in_array(direction, [1, 4]) && ml < 0) {
			ml = bpl;
			if(in_array(basePoint, [1, 4])) ml += sw;
		} else if(ml + mw > scrollLeft + document.body.clientWidth && sx >= mw) {
			ml = bpl - mw;
			if(in_array(basePoint, [2, 3])) {
				ml -= sw;
			} else if(basePoint == 4) {
				ml += sw;
			}
		}
		if(in_array(direction, [1, 2]) && mt < 0) {
			mt = bpt;
			if(in_array(basePoint, [1, 2])) mt += sh;
		} else if(mt + mh > scrollTop + document.documentElement.clientHeight && sy >= mh) {
			mt = bpt - mh;
			if(in_array(basePoint, [3, 4])) mt -= sh;
		}
	}
	if(pos.substr(0, 3) == '210') {
		ml += 69 - sw / 2;
		mt -= 5;
		if(showObj.tagName == 'TEXTAREA') {
			ml -= sw / 2;
			mt += sh / 2;
		}
	}
	if(direction == 0 || menuObj.scrolly) {
		if(BROWSER.ie && BROWSER.ie < 7) {
			if(direction == 0) mt += scrollTop;
		} else {
			if(menuObj.scrolly) mt -= scrollTop;
			menuObj.style.position = 'fixed';
		}
	}
	
	if(document.body.clientWidth>0 && ml>0 && (ml+mw>document.body.clientWidth)){
		ml=(document.body.clientWidth-mw-10)>0?(document.body.clientWidth-mw-10):0;
	}
	if(ml) menuObj.style.left = ml + 'px';
	if(mt) menuObj.style.top = mt + 'px';
	if(direction == 0 && BROWSER.ie && !document.documentElement.clientHeight) {
		menuObj.style.position = 'absolute';
		menuObj.style.top = (document.body.clientHeight - menuObj.clientHeight) / 2 + 'px';
	}
	if(menuObj.style.clip && !BROWSER.opera) {
		menuObj.style.clip = 'rect(auto, auto, auto, auto)';
	}
}

function hideMenu(attr, mtype) {
	attr = isUndefined(attr) ? '' : attr;
	mtype = isUndefined(mtype) ? 'menu' : mtype;
	
	
	if(attr == '') {
		for(var i = 1; i <= JSMENU['layer']; i++) {
			hideMenu(i, mtype);
		}
		return;
	} else if(typeof attr == 'number') {
		for(var j in JSMENU['active'][attr]) {
			hideMenu(JSMENU['active'][attr][j], mtype);
		}
		return;
	}else if(typeof attr == 'string') {
		var menuObj = document.getElementById(attr);
		if(!menuObj || (mtype && menuObj.mtype != mtype)) return;
		
		var ctrlObj = '', ctrlclass = '';
		if((ctrlObj = document.getElementById(menuObj.getAttribute('ctrlid'))) && (ctrlclass = menuObj.getAttribute('ctrlclass'))) {
			var reg = new RegExp(' ' + ctrlclass);
			ctrlObj.className = ctrlObj.className.replace(reg, '');
		}
		clearTimeout(JSMENU['timer'][attr]);
		var hide = function() {
			if(menuObj.cache) {
				if(menuObj.style.visibility != 'hidden') {
					menuObj.style.display = 'none';
					if(menuObj.cover) document.getElementById(attr + '_cover').style.display = 'none';
				}
			}else {
				menuObj.parentNode.removeChild(menuObj);
				if(menuObj.cover) document.getElementById(attr + '_cover').parentNode.removeChild(document.getElementById(attr + '_cover'));
			}
			var tmp = [];
			for(var k in JSMENU['active'][menuObj.layer]) {
				if(attr != JSMENU['active'][menuObj.layer][k]) tmp.push(JSMENU['active'][menuObj.layer][k]);
			}
			JSMENU['active'][menuObj.layer] = tmp;
		};
		if(menuObj.fade) {
			var O = 100;
			var fadeOut = function(O) {
				if(O == 0) {
					clearTimeout(fadeOutTimer);
					hide();
					return;
				}
				menuObj.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + O + ')';
				menuObj.style.opacity = O / 100;
				O -= 20;
				var fadeOutTimer = setTimeout(function () {
					fadeOut(O);
				}, 40);
			};
			fadeOut(O);
		} else {
			hide();
		}
	}
	
}

function getCurrentStyle(obj, cssproperty, csspropertyNS) {
	if(obj.style[cssproperty]){
		return obj.style[cssproperty];
	}
	if (obj.currentStyle) {
		return obj.currentStyle[cssproperty];
	} else if (document.defaultView.getComputedStyle(obj, null)) {
		var currentStyle = document.defaultView.getComputedStyle(obj, null);
		var value = currentStyle.getPropertyValue(csspropertyNS);
		if(!value){
			value = currentStyle[cssproperty];
		}
		return value;
	} else if (window.getComputedStyle) {
		var currentStyle = window.getComputedStyle(obj, "");
		return currentStyle.getPropertyValue(csspropertyNS);
	}
}

function fetchOffset(obj, mode) {
	var left_offset = 0, top_offset = 0, mode = !mode ? 0 : mode;

	if(obj.getBoundingClientRect && !mode) {
		var rect = obj.getBoundingClientRect();
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var scrollLeft = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
		if(document.documentElement.dir == 'rtl') {
			scrollLeft = scrollLeft + document.documentElement.clientWidth - document.documentElement.scrollWidth;
		}
		left_offset = rect.left + scrollLeft - document.documentElement.clientLeft;
		top_offset = rect.top + scrollTop - document.documentElement.clientTop;
	}
	if(left_offset <= 0 || top_offset <= 0) {
		left_offset = obj.offsetLeft;
		top_offset = obj.offsetTop;
		while((obj = obj.offsetParent) != null) {
			position = getCurrentStyle(obj, 'position', 'position');
			if(position == 'relative') {
				continue;
			}
			left_offset += obj.offsetLeft;
			top_offset += obj.offsetTop;
		}
	}
	return {'left' : left_offset, 'top' : top_offset};
}







function showError(msg) {
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	msg = msg.replace(p, '');
	if(msg !== '') {
		showDialog(msg, 'alert', __lang.db_error_message, null, true, null, '', '', '', 3);
	}
}

function hideWindow(k, all, clear) {
	all = isUndefined(all) ? 1 : all;
	clear = isUndefined(clear) ? 1 : clear;
	jQuery('#fwin_' + k).modal('hide').remove();
	/*hideMenu('fwin_' + k, 'win');
	if(clear && document.getElementById('fwin_' + k)) {
		document.getElementById('append_parent').removeChild(document.getElementById('fwin_' + k));
	}*/
	if(all) {
		jQuery('.modal.fwinmask').modal('hide').remove();
	}
}

function AC_FL_RunContent() {
	var str = '';
	var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
	if(BROWSER.ie && !BROWSER.opera) {
		str += '<object ';
		for (var i in ret.objAttrs) {
			str += i + '="' + ret.objAttrs[i] + '" ';
		}
		str += '>';
		for (var i in ret.params) {
			str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
		}
		str += '</object>';
	} else {
		str += '<embed ';
		for (var i in ret.embedAttrs) {
			str += i + '="' + ret.embedAttrs[i] + '" ';
		}
		str += '></embed>';
	}
	return str;
}

function AC_GetArgs(args, classid, mimeType) {
	var ret = new Object();
	ret.embedAttrs = new Object();
	ret.params = new Object();
	ret.objAttrs = new Object();
	for (var i = 0; i < args.length; i = i + 2){
		var currArg = args[i].toLowerCase();
		switch (currArg){
			case "classid":break;
			case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
			case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
			case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
			case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
			case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
			case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
			case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
			case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
			case "id":ret.objAttrs[args[i]] = args[i+1];break;
			case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
			case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
			default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
		}
	}
	ret.objAttrs["classid"] = classid;
	if(mimeType) {
		ret.embedAttrs["type"] = mimeType;
	}
	return ret;
}

function simulateSelect(selectId, widthvalue) {
	var selectObj = document.getElementById(selectId);
	if(!selectObj) return;
	if(BROWSER.other) {
		if(selectObj.getAttribute('change')) {
			selectObj.onchange = function () {eval(selectObj.getAttribute('change'));}
		}
		return;
	}
	var widthvalue = widthvalue ? widthvalue : 70;
	var defaultopt = selectObj.options[0] ? selectObj.options[0].innerHTML : '';
	var defaultv = '';
	var menuObj = document.createElement('div');
	var ul = document.createElement('ul');
	var handleKeyDown = function(e) {
		e = BROWSER.ie ? event : e;
		if(e.keyCode == 40 || e.keyCode == 38) doane(e);
	};
	var selectwidth = (selectObj.getAttribute('width', i) ? selectObj.getAttribute('width', i) : widthvalue) + 'px';
	var tabindex = selectObj.getAttribute('tabindex', i) ? selectObj.getAttribute('tabindex', i) : 1;

	for(var i = 0; i < selectObj.options.length; i++) {
		var li = document.createElement('li');
		li.innerHTML = selectObj.options[i].innerHTML;
		li.k_id = i;
		li.k_value = selectObj.options[i].value;
		if(selectObj.options[i].selected) {
			defaultopt = selectObj.options[i].innerHTML;
			defaultv = selectObj.options[i].value;
			li.className = 'current';
			selectObj.setAttribute('selecti', i);
		}
		li.onclick = function() {
			if(document.getElementById(selectId + '_ctrl').innerHTML != this.innerHTML) {
				var lis = menuObj.getElementsByTagName('li');
				lis[document.getElementById(selectId).getAttribute('selecti')].className = '';
				this.className = 'current';
				document.getElementById(selectId + '_ctrl').innerHTML = this.innerHTML;
				document.getElementById(selectId).setAttribute('selecti', this.k_id);
				document.getElementById(selectId).options.length = 0;
				document.getElementById(selectId).options[0] = new Option('', this.k_value);
				eval(selectObj.getAttribute('change'));
			}
			hideMenu(menuObj.id);
			return false;
		};
		ul.appendChild(li);
	}

	selectObj.options.length = 0;
	selectObj.options[0]= new Option('', defaultv);
	selectObj.style.display = 'none';
	selectObj.outerHTML += '<a href="javascript:;" id="' + selectId + '_ctrl" style="width:' + selectwidth + '" tabindex="' + tabindex + '">' + defaultopt + '</a>';

	menuObj.id = selectId + '_ctrl_menu';
	menuObj.className = 'sltm';
	menuObj.style.display = 'none';
	menuObj.style.width = selectwidth;
	menuObj.appendChild(ul);
	document.getElementById('append_parent').appendChild(menuObj);

	document.getElementById(selectId + '_ctrl').onclick = function(e) {
		document.getElementById(selectId + '_ctrl_menu').style.width = selectwidth;
		showMenu({'ctrlid':(selectId == 'loginfield' ? 'account' : selectId + '_ctrl'),'menuid':selectId + '_ctrl_menu','evt':'click','pos':'43'});
		doane(e);
	};
	document.getElementById(selectId + '_ctrl').onfocus = menuObj.onfocus = function() {
		_attachEvent(document.body, 'keydown', handleKeyDown);
	};
	document.getElementById(selectId + '_ctrl').onblur = menuObj.onblur = function() {
		_detachEvent(document.body, 'keydown', handleKeyDown);
	};
	document.getElementById(selectId + '_ctrl').onkeyup = function(e) {
		e = e ? e : window.event;
		value = e.keyCode;
		if(value == 40 || value == 38) {
			if(menuObj.style.display == 'none') {
				document.getElementById(selectId + '_ctrl').onclick();
			} else {
				lis = menuObj.getElementsByTagName('li');
				selecti = selectObj.getAttribute('selecti');
				lis[selecti].className = '';
				if(value == 40) {
					selecti = parseInt(selecti) + 1;
				} else if(value == 38) {
					selecti = parseInt(selecti) - 1;
				}
				if(selecti < 0) {
					selecti = lis.length - 1
				} else if(selecti > lis.length - 1) {
					selecti = 0;
				}
				lis[selecti].className = 'current';
				selectObj.setAttribute('selecti', selecti);
				lis[selecti].parentNode.scrollTop = lis[selecti].offsetTop;
			}
		} else if(value == 13) {
			var lis = menuObj.getElementsByTagName('li');
			lis[selectObj.getAttribute('selecti')].onclick();
		} else if(value == 27) {
			hideMenu(menuObj.id);
		}
	};
}


function ctrlEnter(event, btnId, onlyEnter) {
	if(isUndefined(onlyEnter)) onlyEnter = 0;
	if((event.ctrlKey || onlyEnter) && event.keyCode == 13) {
		document.getElementById(btnId).click();
		return false;
	}
	return true;
}



function updatestring(str1, str2, clear) {
	str2 = '_' + str2 + '_';
	return clear ? str1.replace(str2, '') : (str1.indexOf(str2) == -1 ? str1 + str2 : str1);
}

function getClipboardData() {
	window.document.clipboardswf.SetVariable('str', CLIPBOARDSWFDATA);
}
function setCopy(text, msg){
	if(BROWSER.ie) {
		var r = clipboardData.setData('Text', text);
		if(r) {
			if(msg) {
				showPrompt(null, null, '<span>' + msg + '</span>', 1500);
			}
		} else {
			showDialog('<div class="" style="width: 200px; text-align: center;">'+__lang.copy_unsuccess_allow_access+'</div>', 'alert');
		}
	} else {
		var msg = '<div class="" style="border-bottom:none; width:200px;text-align: center; text-decoration:underline;">'+__lang.click_here_copy_clipboard+'</div>' +
		AC_FL_RunContent('id', 'clipboardswf', 'name', 'clipboardswf', 'devicefont', 'false', 'width', '200', 'height', '40', 'src', STATICURL + 'image/common/clipboard.swf', 'menu', 'false',  'allowScriptAccess', 'sameDomain', 'swLiveConnect', 'true', 'wmode', 'transparent', 'style' , 'margin-top:-20px');
		showDialog(msg, 'message');
		text = text.replace(/[\xA0]/g, ' ');
		CLIPBOARDSWFDATA = text;
	}
}







var secST = new Array();

function strLenCalc(obj, checklen, maxlen) {
	var v = obj.value, charlen = 0, maxlen = !maxlen ? 200 : maxlen, curlen = maxlen, len = strlen(v);
	for(var i = 0; i < v.length; i++) {
		if(v.charCodeAt(i) < 0 || v.charCodeAt(i) > 255) {
			curlen -= charset == 'utf-8' ? 2 : 1;
		}
	}
	if(curlen >= len) {
		document.getElementById(checklen).innerHTML = curlen - len;
	} else {
		obj.value = mb_cutstr(v, maxlen, 0);
	}
}


if(BROWSER.ie && BROWSER.ie<11) {
	try{document.documentElement.addBehavior("#default#userdata");}catch(e){}
}

function updateseccode(idhash, play) {
	if(isUndefined(play)) {
		if(document.getElementById('seccode_' + idhash)) {
			document.getElementById('seccodeverify_' + idhash).value = '';
			if(secST['code_' + idhash]) {
				clearTimeout(secST['code_' + idhash]);
			}
			document.getElementById('checkseccodeverify_' + idhash).innerHTML = '';
			ajaxget('misc.php?mod=seccode&action=update&idhash=' + idhash, 'seccode_' + idhash, null, '', '', function() {
				secST['code_' + idhash] = setTimeout(function() {document.getElementById('seccode_' + idhash).innerHTML = '<span class="btn btn-link" onclick="updateseccode(\''+idhash+'\')">'+__lang.refresh_verification_code+'</span>';}, 180000);
			});
		}
	} else {
		eval('window.document.seccodeplayer_' + idhash + '.SetVariable("isPlay", "1")');
	}
}

function checksec(type, idhash, showmsg, recall) {
	var showmsg = !showmsg ? 0 : showmsg;
	var secverify = document.getElementById('sec' + type + 'verify_' + idhash).value;
	if(!secverify) {
		return;
	}
	var x = new Ajax('XML', 'checksec' + type + 'verify_' + idhash);
	x.loading = '';
	document.getElementById('checksec' + type + 'verify_' + idhash).innerHTML = '<span class="dzz dzz-autorenew dzz-spin"></span>';
	x.get('misc.php?mod=sec' + type + '&action=check&inajax=1&&idhash=' + idhash + '&secverify=' + (BROWSER.ie && document.charset == 'utf-8' ? encodeURIComponent(secverify) : secverify), function(s){
		var obj = document.getElementById('checksec' + type + 'verify_' + idhash);
		if(obj){
			obj.style.display = '';
			if(s.substr(0, 7) == 'succeed') {
				obj.innerHTML = '<span class="dzz dzz-done"></span>';
				jQuery(obj).closest('.seccode-wrapper').find('.help-msg').addClass('chk_right');
				if(showmsg) {
					recall(1);
				}
			} else {
				jQuery(obj).closest('.seccode-wrapper').find('.help-msg').removeClass('chk_right');
				obj.innerHTML = '<span class="dzz dzz-close"></span>';
				if(showmsg) {
					if(type == 'code') {
						showError(__lang.verification_error_reset);
					}
					recall(0);
				}
			}
		}
	});
}


function showdistrict(container, elems, totallevel, changelevel, containertype) {
	var getdid = function(elem) {
		var op = elem.options[elem.selectedIndex];
		return op['did'] || op.getAttribute('did') || '0';
	};
	var pid = changelevel >= 1 && elems[0] && document.getElementById(elems[0]) ? getdid(document.getElementById(elems[0])) : 0;
	var cid = changelevel >= 2 && elems[1] && document.getElementById(elems[1]) ? getdid(document.getElementById(elems[1])) : 0;
	var did = changelevel >= 3 && elems[2] && document.getElementById(elems[2]) ? getdid(document.getElementById(elems[2])) : 0;
	var coid = changelevel >= 4 && elems[3] && document.getElementById(elems[3]) ? getdid(document.getElementById(elems[3])) : 0;
	var url = "user.php?mod=ajax&action=district&container="+container+"&containertype="+containertype
		+"&province="+elems[0]+"&city="+elems[1]+"&district="+elems[2]+"&community="+elems[3]
		+"&pid="+pid + "&cid="+cid+"&did="+did+"&coid="+coid+'&level='+totallevel+'&handlekey='+container+'&inajax=1'+(!changelevel ? '&showdefault=1' : '');
	ajaxget(url, container, '');
}

function showbirthday(){
	var el = document.getElementById('birthday');
	var birthday = el.value;
	el.length=0;
	el.options.add(new Option(__lang.day, ''));
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
	} else if(document.getElementById('birthyear').value!="") {
		var nbirthyear=document.getElementById('birthyear').value;
		if(nbirthyear%400==0 || (nbirthyear%4==0 && nbirthyear%100!=0)) el.options.add(new Option(29, 29));
	}
	el.value = birthday;
}



var tipTimer=[];
function showTip(ctrlobj,pos,msg) {
	if(!ctrlobj.id) {
		ctrlobj.id = 'tip_' + Math.random();
	}
	var tip_classname='';
	switch(pos){
		case '12':
			tip_classname='tip_4';
			break;
		case '21':
			tip_classname='tip_3';
			break;
		case '43':
			tip_classname='tip_1';
			break;
		case '34':
			tip_classname='tip_2';
			break;
		default:
			pos='12';
			tip_classname='tip_4';
	}
	menuid = ctrlobj.id + '_menu';
	if(!document.getElementById(menuid)) {
		var div = document.createElement('div');
		div.id = ctrlobj.id + '_menu';
		div.className = 'tip '+tip_classname;
		div.style.display = 'none';
		div.innerHTML = '<div class="tip_horn"></div><div class="tip_c">' + (msg?msg:ctrlobj.getAttribute('tip')) + '</div>';
		document.getElementById('append_parent').appendChild(div);
		div.onmouseover=function(){
			if(tipTimer[this.id]) window.clearTimeout(tipTimer[this.id]);
		};
		div.onmouseout=function(){
			var self=this;
			tipTimer[this.id]=window.setTimeout(function(){hideMenu(self.id, 'prompt');},200);
		};
	}
	document.getElementById(ctrlobj.id).onmouseout = function () {var self=this; tipTimer[this.id+'_menu']=window.setTimeout(function(){hideMenu(self.id+'_menu', 'prompt');},200)};
	
	showMenu({'mtype':'prompt','ctrlid':ctrlobj.id,'pos':pos+'!','duration':3,'zindex':JSMENU['zIndex']['prompt']});
	
}

function showPrompt(ctrlid, evt, msg, timeout, classname) {
	var menuid = ctrlid ? ctrlid + '_pmenu' : 'ntcwin';
	var duration = timeout ? 0 : 3;
	if(document.getElementById(menuid)) {
		document.getElementById(menuid).parentNode.removeChild(document.getElementById(menuid));
	}
	var div = document.createElement('div');
	div.id = menuid;
	div.className = !classname ? (ctrlid ? 'tip tip_js' : 'ntcwin') : classname;
	div.style.display = 'none';
	document.getElementById('append_parent').appendChild(div);
	if(ctrlid) {
		msg = '<div id="' + ctrlid + '_prompt"><div class="tip_horn"></div><div class="tip_c">' + msg + '</div>';
	} else {
		msg = '<table cellspacing="0" cellpadding="0" class="popupcredit"><tr><td class="pc_l">&nbsp;</td><td class="pc_c"><div class="pc_inner">' + msg +
			'</td><td class="pc_r">&nbsp;</td></tr></table>';
	}
	div.innerHTML = msg;
	if(ctrlid) {
		if(!timeout) {
			evt = 'click';
		}
		if(document.getElementById(ctrlid)) {
			if(document.getElementById(ctrlid).evt !== false) {
				var prompting = function() {
					showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210'});
				};
				if(evt == 'click') {
					document.getElementById(ctrlid).onclick = prompting;
				} else {
					document.getElementById(ctrlid).onmouseover = prompting;
				}
			}
			showMenu({'mtype':'prompt','ctrlid':ctrlid,'evt':evt,'menuid':menuid,'pos':'210','duration':duration,'timeout':timeout,'zindex':JSMENU['zIndex']['prompt']});
			document.getElementById(ctrlid).unselectable = false;
		}
	} else {
		showMenu({'mtype':'prompt','pos':'00','menuid':menuid,'duration':duration,'timeout':timeout,'zindex':JSMENU['zIndex']['prompt']});
		document.getElementById(menuid).style.top = (parseInt(document.getElementById(menuid).style.top) - 100) + 'px';
	}
}
function cardInit() {
	var cardShow = function (obj) {
		if(BROWSER.ie && BROWSER.ie < 7 && obj.href.indexOf('username') != -1) {
			return;
		}
		pos = obj.getAttribute('c') == '1' ? '43' : obj.getAttribute('c');
		USERCARDST = setTimeout(function() {ajaxmenu(obj, 500, 1, 2, pos, null, 'p_pop card');}, 250);
	};
	
	var cardids = {};
	var a = document.body.getElementsByTagName('a');
	for(var i = 0;i < a.length;i++){
		if(a[i].getAttribute('c')) {
			var href = a[i].getAttribute('href', 1);
			if(typeof cardids[href] == 'undefined') {
				cardids[href] = Math.round(Math.random()*10000);
			}
			a[i].setAttribute('mid', 'card_' + cardids[href]);
			a[i].onmouseover = function() {cardShow(this)};
			a[i].onmouseout = function() {clearTimeout(USERCARDST);};
		}
	}
	
}
function mobileplayer()
{
	var platform = navigator.platform;
	var ua = navigator.userAgent;
	var ios = /iPhone|iPad|iPod/.test(platform) && ua.indexOf( "AppleWebKit" ) > -1;
	var andriod = ua.indexOf( "Android" ) > -1;
	if(ios || andriod) {
		return true;
	} else {
		return false;
	}
}


/*if(typeof showusercard != 'undefined' && showusercard == 1) {
	_attachEvent(window, 'load', cardInit, document);
}*/
function showTopMsg(msg,timeout){
//显示
	if(!timeout) time=5000;
	var el=	jQuery('<div class="tips">'+msg+'</div>').appendTo(document.body);
	el.slideDown();
	el.css({"margin-left":-el.width()/2});
	window.setTimeout(function(){el.slideUp(function(){el.remove();});},time);
}

//target='dzz'时在桌面打开
jQuery(document).ready(function(e) {
    jQuery(document).on('click','a',function(){
		var href=this.href,id='',name=jQuery(this).attr('title')?jQuery(this).attr('title'):strip_tags(this.innerHTML);
		
		if(href.indexOf('user.php?uid=')!==-1){
			id='profile';
			try{top.OpenWindow(id, href, __lang.subscriber_data);}catch(e){
				window.open(href,name);
				return false;
			}
			return false;
		}else if(this.target=='_dzz'){
			if(jQuery(this).attr('icon')){
				var taskdata={img:jQuery(this).attr('icon'),name:name};						  
			}else{
				var taskdata={};
			}
			try{
				if(top._config){
					top.OpenWindow('url', href, name,'',taskdata);
					return false;
				}else{
					window.open(href,name);
					return false;
				}
			}catch(e){}
		}
	});
});

var showDialogST = null;
function showDialog(msg, mode, t, func, cover, funccancel, leftmsg, confirmtxt, canceltxt, closetime, locationtime) {
	clearTimeout(showDialogST);
	cover = isUndefined(cover) ? (mode == 'info' || mode == 'icon' ? 0 : 1) : cover;
	leftmsg = isUndefined(leftmsg) ? '' : leftmsg;
	//mode = in_array(mode, ['confirm', 'notice', 'info', 'right']) ? mode : 'alert';
	if(!mode) mode='alert';
	var menuid = 'fwin_dialog';
	var menuObj = document.getElementById(menuid);
	var showconfirm = 1;
	confirmtxtdefault = __lang.confirms;
	closetime = isUndefined(closetime) ? '' : closetime;
	closefunc = function () {
		if(typeof func == 'function') func();
		else eval(func);
		hideMenu(menuid, 'dialog');
	};
	if(closetime) {
		leftmsg = closetime + __lang.message_closetime;
		showDialogST = setTimeout(closefunc, closetime * 1000);
		//showconfirm = 0;
	}
	locationtime = isUndefined(locationtime) ? '' : locationtime;
	if(locationtime) {
		leftmsg = locationtime + __lang.message_locationtime;
		showDialogST = setTimeout(closefunc, locationtime * 1000);
		//showconfirm = 0;
	}
	confirmtxt = confirmtxt ? confirmtxt : confirmtxtdefault;
	canceltxt = canceltxt ? canceltxt : __lang.cancel;

	if(menuObj) hideMenu('fwin_dialog', 'dialog');
	menuObj = document.createElement('div');
	menuObj.style.display = 'none';
	menuObj.className = 'fwinmask';
	menuObj.id = menuid;
	document.getElementById('append_parent').appendChild(menuObj);
	var hidedom = '';
	if(!BROWSER.ie) {
		hidedom = '<style type="text/css">object{visibility:hidden;}</style>';
	}
	var shadow='';//'<div class="LEFT_TOP ROUND" ></div><div class="TOP ROUND"></div><div class="RIGHT_TOP ROUND" ></div><div class="RIGHT ROUND" ></div><div class="RIGHT_BOTTOM ROUND"></div><div class="BOTTOM ROUND" ></div><div class="LEFT_BOTTOM ROUND" ></div><div class="LEFT ROUND"></div>';
	// var s = hidedom + shadow+ '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l"></td><td class="m_c"><h3 class="flb" id="drag_fwin_dialog"><em>';
	// s += t ? t : __lang.board_message;
	// s += '</em><button id="fwin_dialog_close" type="button" class="close" onclick="hideMenu(\'fwin_dialog\', \'dialog\')" >×</button></h3>';
    var s='';
    if(t) {
        s=hidedom + shadow+ '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l"></td><td class="m_c"><h3 class="flb" id="drag_fwin_dialog"><em>';
        s += t;
        s += '</em></h3><button id="fwin_dialog_close" type="button" class="close alert-close" onclick="hideMenu(\'fwin_dialog\', \'dialog\')" >×</button>';
    }else {
        s=hidedom + shadow+ '<table cellpadding="0" cellspacing="0" class="fwin"><tr><td class="t_l"></td><td class="t_c"></td><td class="t_r"></td></tr><tr><td class="m_l"></td><td class="m_c"><h3 class="flb" style="padding: 0;height: 30px;" id="drag_fwin_dialog"><em>';
        s += '</em></h3><button id="fwin_dialog_close" type="button" class="close alert-close" onclick="hideMenu(\'fwin_dialog\', \'dialog\')" >×</button>';
    }
    if(mode.indexOf('alert_icon_' )!== -1){
		var icon=decodeURIComponent(mode.replace('alert_icon_' ,''));
		if(icon){
			s += '<div class="c altw"><div class="alert_icon"><img class="alert_icon_img" src="'+icon+'"><p>' + msg + '</p></div></div>';
		}else{
			s += '<div class="c altw"><div class="' + ('alert_info') + '"><p>' + msg + '</p></div></div>';
		}
		s += '<p class="o pns">' + (leftmsg ? '<span class=" muted pull-left">' + leftmsg + '</span>' : '') + (showconfirm ? '<button id="fwin_dialog_submit" value="true" class="btn btn-primary"><strong>'+confirmtxt+'</strong></button>' : '');
		
		s += '</p>';
	}else if(mode=='message'){
		if(leftmsg){
			s += '<div class="c altw"><div class="alert_info"><p>' + msg + '</p></div></div>';
		}else{
			s += '<div class="c altw">' + msg + '</div>';
		}
		s += '<p class="o pns">' + (leftmsg ? '<span class=" muted pull-left">' + leftmsg + '</span>' : '') + (showconfirm ? '<button id="fwin_dialog_submit" value="true" class="btn btn-primary"><strong>'+confirmtxt+'</strong></button>' : '');
		s += '</p>';
	} else {
		s += '<div class="c altw"><div class="' + (mode == 'alert' ? 'alert_error' : (mode == 'right' ? 'alert_right' : (mode == 'info' ? 'alert_info':''))) + '"><p>' + msg + '</p></div></div>';
		s += '<p class="o pns">' + (leftmsg ? '<span class="muted pull-left">' + leftmsg + '</span>' : '') + (showconfirm ? '<button id="fwin_dialog_submit" value="true" class="btn btn-primary">'+confirmtxt+'</button>   ' : '');
		s += mode == 'confirm' ? '<button id="fwin_dialog_cancel" value="true" class="btn btn-default-outline" onclick="hideMenu(\'fwin_dialog\', \'dialog\')">'+canceltxt+'</button>' : '';
		s += '</p>';
	}
	s += '</td><td class="m_r"></td></tr><tr><td class="b_l"></td><td class="b_c"></td><td class="b_r"></td></tr></table>';
	menuObj.innerHTML = s;
	if(document.getElementById('fwin_dialog_submit')) document.getElementById('fwin_dialog_submit').onclick = function() {
		if(typeof func == 'function') func();
		else eval(func);
		hideMenu(menuid, 'dialog');
	};
	if(document.getElementById('fwin_dialog_cancel')) {
		document.getElementById('fwin_dialog_cancel').onclick = function() {
			if(typeof funccancel == 'function') funccancel();
			else eval(funccancel);
			hideMenu(menuid, 'dialog');
		};
		//document.getElementById('fwin_dialog_close').onclick = document.getElementById('fwin_dialog_cancel').onclick;
	}
	showMenu({'mtype':'dialog','menuid':menuid,'duration':3,'pos':'00','zindex':JSMENU['zIndex']['dialog'],'drag':'drag_'+menuid,'cache':0,'cover':cover});
	// try {
	// 	if(document.getElementById('fwin_dialog_submit')) document.getElementById('fwin_dialog_submit').focus();
	// } catch(e) {}
}
function Alert(msg,timeout,callback,oktext,type)
{
	if(!type) type='alert'
	showDialog(msg, type, __lang.board_message, callback, 1,callback, '', oktext?oktext:__lang.i_see, '', timeout);
};
function Confirm(msg,callback)
{
	showDialog(msg, 'confirm', __lang.confirm_message, callback, 1);
};

function showWindow(k, url, mode, cache, showWindow_callback,disablebacktohide) {

	mode = isUndefined(mode) ? 'get' : mode;
	cache = isUndefined(cache) ? 1 : cache;
	var menuid = 'fwin_' + k;
	var menuObj = document.getElementById(menuid);
	var drag = null;
	var loadingst = null;
	var hidedom = '';

	if(disallowfloat && disallowfloat.indexOf(k) != -1) {
		if(BROWSER.ie) url += (url.indexOf('?') != -1 ?  '&' : '?') + 'referer=' + escape(location.href);
		location.href = url;
		doane();
		return;
	}

	var fetchContent = function() {
		if(mode == 'get') {
			menuObj.url = url;
			url += (url.search(/\?/) > 0 ? '&' : '?') + 'infloat=yes&handlekey=' + k;
			url += cache == -1 ? '&t='+(+ new Date()) : '';
			if(BROWSER.ie &&  url.indexOf('referer=') < 0) {
				url = url + '&referer=' + encodeURIComponent(location);
			}
			ajaxget(url, 'fwin_content_' + k, null, '', '', function() {initMenu();show();});
		} else if(mode == 'post') {
			menuObj.act = document.getElementById(url).action;
			ajaxpost(url, 'fwin_content_' + k, '', '', '', function() {initMenu();show();});
		}
		if(parseInt(BROWSER.ie) != 6) {
			loadingst = setTimeout(function() {showDialog('', 'info', '<img src="' + IMGDIR + '/loading.gif"> '+__lang.please_wait)}, 500);
		}
		if(mode == 'html'){
			document.getElementById('fwin_content_' + k).innerHTML = url;
			initMenu();
			show();
		}
	};
	var initMenu = function() {
		clearTimeout(loadingst);
		/*var objs = menuObj.getElementsByTagName('*');
		var fctrlidinit = false;
		for(var i = 0; i < objs.length; i++) {
			if(objs[i].id) {
				objs[i].setAttribute('fwin', k);
			}
			if(objs[i].className == 'flb' && !fctrlidinit) {
				if(!objs[i].id) objs[i].id = 'fctrl_' + k;
				drag = objs[i].id;
				fctrlidinit = true;
			}
		}*/
	};
	var show = function() {
		hideMenu('fwin_dialog', 'dialog');
		jQuery(menuObj).modal('show');
	};
	if(!menuObj) {
		var html='<div class="modal-dialog modal-center">'
				 +'	<div class="modal-content" >'
				 +'  <div class="modal-content-inner" id="fwin_content_'+k+'">'
				/* +'	  <div class="modal-header">'
				 +'		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>'
				 +'		<h4 class="modal-title" id="fwin_title_'+k+'">加载中,请稍候</h4>'
				 +'	  </div>'*/
				/* +'	  <div class="m_c modal-body" id="fwin_content_' + k + '">'
				 +'		<table   height="100%" width="100%"><tbody><tr><td align="center" valign="middle"><div class="loading_img"><div class="loading_process"></div></div></td></tr></tbody></table>'
				 +'	  </div>'
			   +'	  <div class="modal-footer">'
				 +'		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>'
				 +'		<button type="button" class="btn btn-primary">Save changes</button>'
				 +'	  </div>'*/
				 +'	 </div>'
				 +'	</div>'
				 +'</div>';
				 
		menuObj = document.createElement('div');
		menuObj.id = menuid;
		menuObj.className = ' modal ';
		if(disablebacktohide){
			menuObj.setAttribute('data-backdrop','static');
			menuObj.setAttribute('data-keyboard','false');
			
		} 
		menuObj.style.display = 'none';
		document.body.appendChild(menuObj);
		
		menuObj.innerHTML =  html;
		if(mode == 'html') {
			document.getElementById('fwin_content_' + k).innerHTML = url;
			initMenu();
			show();
		} else {
			fetchContent();
		}
	} else if((mode == 'get' && (url != menuObj.url || cache != 1)) || (mode == 'post' && document.getElementById(url).action != menuObj.act) || (mode == 'html' &&  cache != 1)) {
		fetchContent();
	} else {
		show();
	}

	if(typeof showWindow_callback == 'function') window.showWindow_callback=showWindow_callback;
	doane();
}

var messageTimer=null;
function showmessage(msg,type,timeout,haveclose,position,callback,maxwidth,maxheight,delay){
/* 提示消息框
 * @string  msg       消息
 * @string  type      消息类型，'danger','info','success','warning'
 * @number  timeout   定时关闭，单位毫秒
 * @boolean haveclose 是否显示关闭按钮
 * @string  position  消息框位置，'left-top'、'right-top'、'right-bottom'、'left-bottom','top-center','center'，默认'top-center'
 * @function callback 回调函数，timeout用尽时才会触发；
 */
	if(!maxheight) maxheight=300; //最大高度
	if(!maxwidth) maxwidth=300;  //最大宽度
	if(!delay) delay=300;     //动画时间
	
	if(!position) position='top-center';
	var el;
	if(!document.getElementById('message_tip_box')){
		el=jQuery('<div id="message_tip_box" class="message_tip_box"><div id="message_tip_alert" class="alert"></div></div>').appendTo(document.body);
		var isnew=true;
	}else{
		el=jQuery('#message_tip_box');
	} 
	var el1=jQuery('#message_tip_alert');
	 el.attr('sytle','').attr('class','message_tip_box position-'+position);
	 el.css({'height':'auto','max-height':maxheight,width:maxwidth,margin:'0,auto','overflow':'hidden'});
	
	//设置消息框的类型（不同类型背景颜色不同）；
	if(type=='error') type='danger';
	var types=['danger','info','success','warning'];
	/*if(jQuery.inArray(type,types)<0){
		jQuery('#message_tip_alert').attr('class',' alert alert-'+type);
	}else{
		jQuery('#message_tip_alert').attr('class','alert');
	}*/	
	if(jQuery.inArray(type,types)<0) type='';
	if(type) {
		jQuery('#message_tip_alert').attr('class',' alert alert-'+type);
		if(type == 'info'){
		var spantype ='<span class="dzz dzz-info-outline spantype" style="color:#3d91ea;"></span>'	;
			}else if(type == 'success'){
				var spantype ='<span class="dzz dzz-notification-success spantype" style="color:#48c874;"></span>';	
			}else if(type == 'danger'){
				var spantype ='<span class="dzz dzz-clear spantype" style="color:#f04836;"></span>';
			}else if(type == 'warning'){
				var spantype ='<span class="dzz dzz-error spantype" style="color:#fdc318;"></span>';
			}
	}
	else  {
		jQuery('#message_tip_alert').attr('class','alert alert-warning');}
	
	//处理关闭按钮
	//处理关闭按钮
	if(haveclose){
		msg='<button type="button" class="close">×</button>'+spantype+'<div class="title-con">'+msg+'</div>';
	}
	jQuery('#message_tip_alert').html(msg);
	//处理位置
	var width=el.outerWidth(true);
	var height=el.outerHeight(true);
	if(messageTimer){
		window.clearTimeout(messageTimer);
	}
	switch(position){
		case 'left-top':
			el.css({right:'auto',top:0,left:-width,bottom:'auto'}).animate({left:0},delay);
			if(timeout){
				messageTimer=window.setTimeout(function(){
					el.animate({left:-width},delay,function(){
						el.remove();
					});
					if(typeof(callback)=='function') callback();
				},timeout);
			} 
			//增加关闭事件
			el1.find('button.close').off('click').on('click',function(){
				el.animate({left:-width},delay,function(){
					el.remove();
				});
				if(typeof(callback)=='function') callback();//关闭时触发回调函数；
			});
			break;
		case 'right-top':
			el.css({right:-width,top:0,left:'auto',bottom:'auto'}).animate({right:0},delay);
			if(timeout){
				messageTimer=window.setTimeout(function(){
					el.animate({right:-width},delay,function(){
						el.remove();
					});
					if(typeof(callback)=='function') callback();
				},timeout);
			} 
			//增加关闭事件
			el1.find('button.close').off('click').on('click',function(){
				el.animate({right:-width},delay,function(){
					el.remove();
				});
				if(typeof(callback)=='function') callback();//关闭时触发回调函数；
			});
			break;
		case 'right-bottom':
			el.css({right:-width,top:'auto',left:'auto',bottom:0}).animate({right:0},delay);
			if(timeout){
				messageTimer=window.setTimeout(function(){
					el.animate({right:-width},delay,function(){
						el.remove();
					});
					if(typeof(callback)=='function') callback();
				},timeout);
			} 
			//增加关闭事件
			el1.find('button.close').off('click').on('click',function(){
				el.animate({left:-width},delay,function(){
					el.remove();
				});
				if(typeof(callback)=='function') callback();//关闭时触发回调函数；
			});
			break;
		case 'left-bottom':
			el.css({right:'auto',top:'auto',left:-width,bottom:0}).animate({left:0},delay);
			if(timeout){
				messageTimer=window.setTimeout(function(){
					el.animate({left:-width},delay,function(){
						el.remove();
					});
					if(typeof(callback)=='function') callback();
				},timeout);
			} 
			//增加关闭事件
			el1.find('button.close').off('click').on('click',function(){
				el.animate({left:-width},delay,function(){
					el.remove();
				});
				if(typeof(callback)=='function') callback();//关闭时触发回调函数；
			});
			break;
		case 'center':
			var w2=width/2;
			var h2=height/2
			el.css({left:'50%',top:'50%',right:'auto',bottom:'auto','margin-left':-w2,'margin-top':-h2,'opacity':0}).animate({'opacity':1},delay);
			
			if(timeout){
				messageTimer=window.setTimeout(function(){
					el.animate({'opacity':0},delay,function(){
						el.remove();
					});
					if(typeof(callback)=='function') callback();
				},timeout);
			} 
			//增加关闭事件
			el1.find('button.close').off('click').on('click',function(){
				el.animate({'opacity':0},delay,function(){
					el.remove();
				});
				if(typeof(callback)=='function') callback();//关闭时触发回调函数；
			});
			break;
		default://top-center
			el.css({left:'50%','marginLeft':-width/2,top:-height,right:'auto',bottom:'auto'}).animate({'top':0},delay);;
			if(timeout){
				messageTimer=window.setTimeout(function(){
					el.animate({top:-height},delay,function(){
						el.remove();
					});
					if(typeof(callback)=='function') callback();
				},timeout);
			} 
			//增加关闭事件
			el1.find('button.close').off('click').on('click',function(){
				el.animate({top:-height},delay,function(){
					el.remove();
				});
				if(typeof(callback)=='function') callback();//关闭时触发回调函数；
			});
			break;
	}
	if(!timeout && typeof(callback)=='function'){//没有设置时间时立即触发
		callback();
	}
}


/*js和css加载器*/
 var jcLoader = function(){   
      
	var dc = document;   
  
	function createScript(url,ids,callback){   
		var urls = url.replace(/[,]\s*$/ig,"").split(",");   
		var lids = ids.replace(/[,]\s*$/ig,"").split(",");   
		var scripts = [];   
		var completeNum = 0;   
		for( var i = 0; i < urls.length; i++ ){   
			if(lids[i] && document.getElementById(lids[i])){
				completeNum++;
				completeNum >= urls.length?(typeof callback =='function'?callback():''):"";   
				continue;
			}
			scripts[i] = dc.createElement("script");   
			scripts[i].type = "text/javascript";   
			scripts[i].src = urls[i]; 
			if(lids[i]) scripts[i].id =  lids[i]; 
			dc.getElementsByTagName("head")[0].appendChild(scripts[i]);   
  			if(typeof callback!='function') continue;
			if(scripts[i].readyState){   
				scripts[i].onreadystatechange = function(){   
  
					if( this.readyState == "loaded" || this.readyState == "complete" ){   
						this.onreadystatechange = null;   
						completeNum++;   
						completeNum >= urls.length?(typeof callback =='function'?callback():''):"";   
					}   
				}   
			}   
			else{   
				scripts[i].onload = function(){   
					completeNum++;   
					completeNum >= urls.length?(typeof callback =='function'?callback():''):"";   
				}   
			}   
  
		}   
  
	}   
  
	function createLink(url,ids,callback){   
		var urls = url.replace(/[,]\s*$/ig,"").split(",");   
		var lids = ids.replace(/[,]\s*$/ig,"").split(",");   
		var links = []; 
		var completeNum = 0;    
		for( var i = 0; i < urls.length; i++ ){  
			if(lids[i] && document.getElementById(lids[i])){
				completeNum++;
				completeNum >= urls.length?(typeof callback =='function'?callback():''):"";   
				continue;
			}
			//try{if(lids[i] && dc.getElementById(lids[i])) dc.getElementsByTagName("head")[0].removeChild(dc.getElementById(lids[i]));}catch(e){};
			links[i] = dc.createElement("link"); 
			if(lids[i]) links[i].id=lids[i];  
			links[i].rel = "stylesheet"; 
			links[i].href = urls[i];   
			dc.getElementsByTagName("head")[0].appendChild(links[i]);   
			if(typeof callback!='function') continue;
			if(links[i].readyState){   
				links[i].onreadystatechange = function(){   
  
					if( this.readyState == "loaded" || this.readyState == "complete" ){   
						this.onreadystatechange = null;   
						completeNum++;   
						completeNum >= urls.length?(typeof callback =='function'?callback():''):"";   
					}   
				}   
			}   
			else{   
				links[i].onload = function(){ 
					completeNum++;   
					completeNum >= urls.length?(typeof callback =='function'?callback():''):"";   
				}   
			}   
	   } 
	}   
	return{   
		load:function(option,callback){   
			var _type = "",_url = "",_ids="";   
			var _callback = callback   
			option.type? _type = option.type:"";   
			option.url? _url = option.url:"";  
			option.ids? _ids = option.ids:"";   
			
			switch(_type){   
				case "js":   
				case "javascript": createScript(_url,_ids,_callback); break;   
				case "css": createLink(_url,_ids,_callback); break;   
			}   
  
			return this;   
		}   
	}   
 }  
 
 function checkeURL(URL){
	var str=URL;
	if(str=='about:blank') return true;
	var Expression=/[a-zA-z]+:\/\/[^\s]*/;
	var objExp=new RegExp(Expression);
	if(objExp.test(str)==true){
		return true;
	}else{
		return false;
	}
} ;
function parseURL(url) {
    var a =  document.createElement('a');
    a.href = url;
    return {
        source: url,
        protocol: a.protocol.replace(':',''),
        host: a.hostname,
        port: a.port,
        query: a.search,
        params: (function(){
            var ret = {},
                seg = a.search.replace(/^\?/,'').split('&'),
                len = seg.length, i = 0, s;
            for (;i<len;i++) {
                if (!seg[i]) { continue; }
                s = seg[i].split('=');
                ret[s[0]] = s[1];
            }
            return ret;
        })(),
        file: (a.pathname.match(/\/([^\/?#]+)$/i) || [,''])[1],
        hash: a.hash.replace('#',''),
        path: a.pathname.replace(/^([^\/])/,'/$1'),
        relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [,''])[1],
        segments: a.pathname.replace(/^\//,'').split('/')
    };
}
function getUrlParam(url,name) {
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
	var r = url.match(reg);
	if (r != null) return unescape(r[2]); return false;
}
function toggleFullScreen(videoElement) {
	if (!document.fullscreen && !document.mozFullScreen && !document.webkitFullScreen) {
	  if (videoElement.requestFullScreen) {
			videoElement.requestFullScreen();
	  }else if (videoElement.mozRequestFullScreen) {
			videoElement.mozRequestFullScreen();
	  } else {
			videoElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
	  }
	} else {
		if (document.exitFullscreen) {
			document.exitFullscreen();
		}else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		} else {
			document.webkitCancelFullScreen();
		}
	}
};

var onousermove=onmouseup=onselectstart=null;
var DetachEvent=function(e,el){
	try{
		//document.body.style.cursor="url('dzz/images/cur/aero_arrow.cur'),auto";
		document.onmousemove=onmousemove;
		document.onmouseup=onmouseup;
		document.onselectstart=onselectstart;
		if(el.releaseCapture)el.releaseCapture();
	}catch(e){}
};
var AttachEvent=function(e,el){ 
	try{
		onmousemove=document.onmousemove;
		onmouseup=document.onmouseup;
		onselectstart=document.onselectstart;
		document.onselectstart=function(){return false;}
		if(e.preventDefault) e.preventDefault();
		else{
			if(el.setCapture)el.setCapture();
		}
	}catch(e){};
};
function dfire(e){
	jQuery(document).trigger(e);
}
/*修复url 没有？的时候第一个&改为？*/
function correcturl(url){
	if(url && url.indexOf('?')===-1){
		url=url.replace(/&/i,'?');
	}
	return url;
}




Date.prototype.format = function(format) {
       var date = {
              "M+": this.getMonth() + 1,
              "d+": this.getDate(),
              "h+": this.getHours(),
              "m+": this.getMinutes(),
              "s+": this.getSeconds(),
              "q+": Math.floor((this.getMonth() + 3) / 3),
              "S+": this.getMilliseconds()
       };
       if (/(y+)/i.test(format)) {
              format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
       }
       for (var k in date) {
              if (new RegExp("(" + k + ")").test(format)) {
                     format = format.replace(RegExp.$1, RegExp.$1.length == 1
                            ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
              }
       }
       return format;
};






function dbind(id,ev,recall){
/*
 * 此函数在document上绑定鼠标按下事件，用于按任意键（不再对象元素上）触发元素事件
 × id为绑定的domid
 * ev为触发此元素的某个事件名称
*/
	jQuery(document).on('mousedown.'+id,function(e){
			e=e?e:window.event;
			var obj = e.srcElement ? e.srcElement :e.target;
			if(checkInDom(obj,id)==false){
				jQuery(document).off('.'+id);
				jQuery('#'+id).trigger(ev);
				if(recall && typeof recall == 'function') {
					recall();
				} else if(recall) {
					eval(recall);
				}
			}
	});
}

function checkInDom(obj,id){
	if(!obj) return false;
	if(obj.id==id) return true;
	else if(obj.tagName=='BODY'){
		return false;
	}else{
		return checkInDom(obj.parentNode,id);
	}
};
function contains(parentNode, childNode) { 
	if (parentNode.contains) { return parentNode != childNode && parentNode.contains(childNode); 
	} else { return !!(parentNode.compareDocumentPosition(childNode) & 16); } 
};
function checkHover(e,target){
    if (getEvent(e).type=="mouseover")  {
        return !contains(target,getEvent(e).relatedTarget||getEvent(e).fromElement) && !((getEvent(e).relatedTarget||getEvent(e).fromElement)===target);
    } else {
        return !contains(target,getEvent(e).relatedTarget||getEvent(e).toElement) && !((getEvent(e).relatedTarget||getEvent(e).toElement)===target);
    }
};



function setMouseDownHide(id){
	jQuery(document).bind('mousedown.'+id,function(e){
		e=e?e:window.event;
		var obj = e.srcElement ? e.srcElement :e.target;
		if(checkInDom(obj,id)==false){
			jQuery('#'+id).hide();
			jQuery(document).unbind('mousedown.'+id);
		}
	});
};

function nowTime(ev,type){
	/*
	 * ev:显示时间的元素
	 * type:时间显示模式.若传入12则为12小时制,不传入则为24小时制
	 * 
		//24小时制调用
		nowTime(document.getElementById('time24'));
		//12小时制调用
		nowTime(document.getElementById('time12'),12);
	 */
	//年月日时分秒
	var Y,M,D,W,H,I,S;
	//月日时分秒为单位时前面补零
	function fillZero(v){
		if(v<10){v='0'+v;}
		return v;
	}
	(function(){
		var d=new Date();
		var Week=[__lang.Sunday,__lang.Monday,__lang.Tuesday,__lang.Wednesday,__lang.Thursday,__lang.Friday,__lang.Saturday];
		Y=d.getFullYear();
		M=fillZero(d.getMonth()+1);
		D=fillZero(d.getDate());
		W=Week[d.getDay()];
		H=fillZero(d.getHours());
		I=fillZero(d.getMinutes());
		S=fillZero(d.getSeconds());
		//12小时制显示模式
		if(type && type==12){
			//若要显示更多时间类型诸如中午凌晨可在下面添加判断
			if(H<=12){
				H='上午 '+H;
			}else if(H>12 && H<24){
				H-=12;
				H='下午 '+fillZero(H);
			}else if(H==24){
				H='下午 00';
			}
		}
		ev.innerHTML=Y+__lang.year+M+__lang.month+D+__lang.day + ' '+W+' '+H+':'+I+':'+S;
		//每秒更新时间
		setTimeout(arguments.callee,1000);
	})();
}
function serialize (mixed_value) {
    // Returns a string representation of variable (which can later be unserialized)  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/serialize    // +   original by: Arpad Ray (mailto:arpad@php.net)
    // +   improved by: Dino
    // +   bugfixed by: Andrej Pavlovic
    // +   bugfixed by: Garagoth
    // +      input by: DtTvB (http://dt.in.th/2008-09-16.string-length-in-bytes.html)    // +   bugfixed by: Russell Walker (http://www.nbill.co.uk/)
    // +   bugfixed by: Jamie Beck (http://www.terabit.ca/)
    // +      input by: Martin (http://www.erlenwiese.de/)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
    // +   improved by: Le Torbi (http://www.letorbi.de/)    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net/)
    // +   bugfixed by: Ben (http://benblume.co.uk/)
    // -    depends on: utf8_encode
    // %          note: We feel the main purpose of this function should be to ease the transport of data between php & js
    // %          note: Aiming for PHP-compatibility, we have to translate objects to arrays    // *     example 1: serialize(['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: 'a:3:{i:0;s:5:"Kevin";i:1;s:3:"van";i:2;s:9:"Zonneveld";}'
    // *     example 2: serialize({firstName: 'Kevin', midName: 'van', surName: 'Zonneveld'});
    // *     returns 2: 'a:3:{s:9:"firstName";s:5:"Kevin";s:7:"midName";s:3:"van";s:7:"surName";s:9:"Zonneveld";}'
    var _utf8Size = function (str) {        var size = 0,
            i = 0,
            l = str.length,
            code = '';
        for (i = 0; i < l; i++) {            code = str.charCodeAt(i);
            if (code < 0x0080) {
                size += 1;
            } else if (code < 0x0800) {
                size += 2;            } else {
                size += 3;
            }
        }
        return size;    };
    var _getType = function (inp) {
        var type = typeof inp,
            match;
        var key; 
        if (type === 'object' && !inp) {
            return 'null';
        }
        if (type === "object") {            if (!inp.constructor) {
                return 'object';
            }
            var cons = inp.constructor.toString();
            match = cons.match(/(\w+)\(/);            if (match) {
                cons = match[1].toLowerCase();
            }
            var types = ["boolean", "number", "string", "array"];
            for (key in types) {                if (cons == types[key]) {
                    type = types[key];
                    break;
                }
            }        }
        return type;
    };
    var type = _getType(mixed_value);
    var val, ktype = ''; 
    switch (type) {
    case "function":
        val = "";
        break;    case "boolean":
        val = "b:" + (mixed_value ? "1" : "0");
        break;
    case "number":
        val = (Math.round(mixed_value) == mixed_value ? "i" : "d") + ":" + mixed_value;        break;
    case "string":
        val = "s:" + _utf8Size(mixed_value) + ":\"" + mixed_value + "\"";
        break;
    case "array":    case "object":
        val = "a";
/*
            if (type == "object") {
                var objname = mixed_value.constructor.toString().match(/(\w+)\(\)/);                if (objname == undefined) {
                    return;
                }
                objname[1] = this.serialize(objname[1]);
                val = "O" + objname[1].substring(1, objname[1].length - 1);            }
            */
        var count = 0;
        var vals = "";
        var okey;        var key;
        for (key in mixed_value) {
            if (mixed_value.hasOwnProperty(key)) {
                ktype = _getType(mixed_value[key]);
                if (ktype === "function") {                    continue;
                }
 
                okey = (key.match(/^[0-9]+$/) ? parseInt(key, 10) : key);
                vals += this.serialize(okey) + this.serialize(mixed_value[key]);                count++;
            }
        }
        val += ":" + count + ":{" + vals + "}";
        break;    case "undefined":
        // Fall-through
    default:
        // if the JS object has a property which contains a null value, the string cannot be unserialized by PHP
        val = "N";        break;
    }
    if (type !== "object" && type !== "array") {
        val += ";";
    }    return val;
};

function array_merge () {
    // Merges elements from passed arrays into one array  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/array_merge    // +   original by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Nate
    // +   input by: josh
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: arr1 = {"color": "red", 0: 2, 1: 4}    // *     example 1: arr2 = {0: "a", 1: "b", "color": "green", "shape": "trapezoid", 2: 4}
    // *     example 1: array_merge(arr1, arr2)
    // *     returns 1: {"color": "green", 0: 2, 1: 4, 2: "a", 3: "b", "shape": "trapezoid", 4: 4}
    // *     example 2: arr1 = []
    // *     example 2: arr2 = {1: "data"}    // *     example 2: array_merge(arr1, arr2)
    // *     returns 2: {0: "data"}
    var args = Array.prototype.slice.call(arguments),
        argl = args.length,
        arg,        retObj = {},
        k = '', 
        argil = 0,
        j = 0,
        i = 0,        ct = 0,
        toStr = Object.prototype.toString,
        retArr = true;
 
    for (i = 0; i < argl; i++) {        if (toStr.call(args[i]) !== '[object Array]') {
            retArr = false;
            break;
        }
    } 
    if (retArr) {
        retArr = [];
        for (i = 0; i < argl; i++) {
            retArr = retArr.concat(args[i]);        }
        return retArr;
    }
 
    for (i = 0, ct = 0; i < argl; i++) {        arg = args[i];
        if (toStr.call(arg) === '[object Array]') {
            for (j = 0, argil = arg.length; j < argil; j++) {
                retObj[ct++] = arg[j];
            }        }
        else {
            for (k in arg) {
                if (arg.hasOwnProperty(k)) {
                    if (parseInt(k, 10) + '' === k) {                        retObj[ct++] = arg[k];
                    }
                    else {
                        retObj[k] = arg[k];
                    }                }
            }
        }
    }
    return retObj;
};

function htmlspecialchars_decode (string, quote_style) {
  // http://kevin.vanzonneveld.net
  // + original by: Mirek Slugen
  // + improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // + bugfixed by: Mateusz "loonquawl" Zalega
  // + input by: ReverseSyntax
  // + input by: Slawomir Kaniecki
  // + input by: Scott Cariss
  // + input by: Francois
  // + bugfixed by: Onno Marsman
  // + revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // + bugfixed by: Brett Zamir (http://brett-zamir.me)
  // + input by: Ratheous
  // + input by: Mailfaker (http://www.weedem.fr/)
  // + reimplemented by: Brett Zamir (http://brett-zamir.me)
  // + bugfixed by: Brett Zamir (http://brett-zamir.me)
  // * example 1: htmlspecialchars_decode("<p>this -> "</p>", 'ENT_NOQUOTES');
  // * returns 1: '<p>this -> "</p>'
  // * example 2: htmlspecialchars_decode(""");
  // * returns 2: '"'
  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString().replace(/</g, '<').replace(/>/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/'|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/"/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&/g, '&');

  return string;
};
