/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @version     DzzOffice 1.1 20140705
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
var BROWSER = {},
USERAGENT = navigator.userAgent.toLowerCase();
BROWSER.ie11 ? (BROWSER.ie = 11, BROWSER.rv = 11) : BROWSER.rv = 0,
BROWSER.safari && (BROWSER.firefox = !0),
BROWSER.opera = BROWSER.opera ? opera.version() : 0,
HTMLNODE = document.getElementsByTagName("head")[0].parentNode,
BROWSER.ie && (BROWSER.iemode = parseInt(void 0 !== document.documentMode ? document.documentMode: BROWSER.ie), HTMLNODE.className = "ie_all ie" + BROWSER.iemode);
var CSSLOADED = [],
JSLOADED = [],
JSMENU = [];
JSMENU.active = [],
JSMENU.timer = [],
JSMENU.drag = [],
JSMENU.layer = 0,
JSMENU.zIndex = {
	win: 11200,
	menu: 11300,
	dialog: 11400,
	prompt: 11500
},
JSMENU.float = "";
var CURRENTSTYPE = null,
creditnotice = isUndefined(creditnotice) ? "": creditnotice,
cookiedomain = isUndefined(cookiedomain) ? "": cookiedomain,
cookiepath = isUndefined(cookiepath) ? "": cookiepath,
EXTRAFUNC = [],
EXTRASTR = "";
EXTRAFUNC.showmenu = [];
var NOTICECURTITLE = document.title,
CurrentActive;
function $C(e, t, n) {
	var a = [];
	if (n = n || "*", (t = t || document).getElementsByClassName) {
		var i = t.getElementsByClassName(e);
		if ("*" != n) for (var o = 0,
		r = i.length; o < r; o++) i[o].tagName.toLowerCase() == n.toLowerCase() && a.push(i[o]);
		else a = i
	} else {
		i = t.getElementsByTagName(n);
		var c = new RegExp("(^|\\s)" + e + "(\\s|$)");
		for (o = 0, r = i.length; o < r; o++) c.test(i[o].className) && a.push(i[o])
	}
	return a
}
function _attachEvent(e, t, n, a) {
	a = a || e,
	e.addEventListener ? e.addEventListener(t, n, !1) : a.attachEvent && e.attachEvent("on" + t, n)
}
function getEvent() {
	if (document.all) return window.event;
	for (func = getEvent.caller; null != func;) {
		var e = func.arguments[0];
		if (e && (e.constructor == Event || e.constructor == MouseEvent || "object" == typeof e && e.preventDefault && e.stopPropagation)) return e;
		func = func.caller
	}
	return null
}
function isUndefined(e) {
	return void 0 === e
}
function in_array(e, t) {
	if ("string" == typeof e || "number" == typeof e) for (var n in t) if (t[n] == e) return ! 0;
	return ! 1
}
function formatSize(e) {
	var t = -1;
	do {
		e /= 1024, t++
	} while ( e > 99 );
	return Math.max(e, 0).toFixed(1) + ["kB", "MB", "GB", "TB", "PB", "EB"][t]
}
function trim(e) {
	return (e + "").replace(/(\s+)$/g, "").replace(/^\s+/g, "")
}
function strlen(e) {
	return BROWSER.ie && -1 != e.indexOf("\n") ? e.replace(/\r?\n/g, "_").length: e.length
}
function mb_strlen(e) {
	for (var t = 0,
	n = 0; n < e.length; n++) t += e.charCodeAt(n) < 0 || e.charCodeAt(n) > 255 ? "utf-8" == charset ? 3 : 2 : 1;
	return t
}
function mb_cutstr(e, t, n) {
	var a = 0,
	i = "";
	t -= (n = "" == n || n ? n: "...").length;
	for (var o = 0; o < e.length; o++) {
		if ((a += e.charCodeAt(o) < 0 || e.charCodeAt(o) > 255 ? "utf-8" == charset ? 3 : 2 : 1) > t) {
			i += n;
			break
		}
		i += e.substr(o, 1)
	}
	return i
}
function strip_tags(e, t) {
	return t = (((t || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(""),
	e.replace(/<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi, "").replace(/<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
	function(e, n) {
		return t.indexOf("<" + n.toLowerCase() + ">") > -1 ? e: ""
	})
}
function preg_replace(e, t, n, a) {
	a = a || "ig";
	for (var i = e.length,
	o = 0; o < i; o++) re = new RegExp(e[o], a),
	n = n.replace(re, "string" == typeof t ? t: t[o] ? t[o] : t[0]);
	return n
}
function htmlspecialchars(e) {
	return preg_replace(["&", "<", ">", '"'], ["&amp;", "&lt;", "&gt;", "&quot;"], e)
}
function display(e) {
	var t = document.getElementById(e);
	t.style.visibility ? t.style.visibility = "visible" == t.style.visibility ? "hidden": "visible": t.style.display = "" == t.style.display ? "none": ""
}
function checkall(e, t, n) {
	n = n || "chkall",
	count = 0;
	for (var a = 0; a < e.elements.length; a++) {
		var i = e.elements[a];
		i.name && i.name != n && !i.disabled && (!t || t && i.name.match(t)) && (i.checked = e.elements[n].checked, i.checked && count++)
	}
	return count
}
function setcookie(e, t, n, a, i, o) {
	if (("" == t || n < 0) && (t = "", n = -2592e3), n) {
		var r = new Date;
		r.setTime(r.getTime() + 1e3 * n)
	}
	i = i || cookiedomain,
	a = a || cookiepath,
	document.cookie = escape(cookiepre + e) + "=" + escape(t) + (r ? "; expires=" + r.toGMTString() : "") + (a ? "; path=" + a: "/") + (i ? "; domain=" + i: "") + (o ? "; secure": "")
}
function getcookie(e, t) {
	e = cookiepre + e;
	var n = document.cookie.indexOf(e),
	a = document.cookie.indexOf(";", n);
	if ( - 1 == n) return "";
	var i = document.cookie.substring(n + e.length + 1, a > n ? a: document.cookie.length);
	return t ? i: unescape(i)
}
function Ajax(e, t) {
	var n = new Object;
	return n.loading = __lang.please_wait,
	n.recvType = e || "XML",
	n.waitId = t ? document.getElementById(t) : null,
	n.resultHandle = null,
	n.sendString = "",
	n.targetUrl = "",
	n.setLoading = function(e) {
		null != e && (n.loading = e)
	},
	n.setRecvType = function(e) {
		n.recvType = e
	},
	n.setWaitId = function(e) {
		n.waitId = "object" == typeof e ? e: document.getElementById(e)
	},
	n.createXMLHttpRequest = function() {
		var e = !1;
		if (window.XMLHttpRequest)(e = new XMLHttpRequest).overrideMimeType && e.overrideMimeType("text/xml");
		else if (window.ActiveXObject) for (var t = ["Microsoft.XMLHTTP", "MSXML.XMLHTTP", "Microsoft.XMLHTTP", "Msxml2.XMLHTTP.7.0", "Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.5.0", "Msxml2.XMLHTTP.4.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP"], n = 0; n < t.length; n++) try {
			if (e = new ActiveXObject(t[n])) return e
		} catch(e) {}
		return e
	},
	n.XMLHttpRequest = n.createXMLHttpRequest(),
	n.showLoading = function() { ! n.waitId || 4 == n.XMLHttpRequest.readyState && 200 == n.XMLHttpRequest.status || (n.waitId.style.display = "", n.waitId.innerHTML = '<span><img src="' + IMGDIR + '/loading.gif" class="vm"> ' + n.loading + "</span>")
	},
	n.processHandle = function() {
		if (4 == n.XMLHttpRequest.readyState && 200 == n.XMLHttpRequest.status) if (n.waitId && (n.waitId.style.display = "none"), "HTML" == n.recvType) n.resultHandle(n.XMLHttpRequest.responseText, n);
		else if ("XML" == n.recvType) n.XMLHttpRequest.responseXML && n.XMLHttpRequest.responseXML.lastChild && "parsererror" != n.XMLHttpRequest.responseXML.lastChild.localName ? n.resultHandle(n.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue, n) : n.resultHandle('<a href="' + n.targetUrl + '" target="_blank" style="color:red">' + __lang.internal_error_unable_display_content + "</a>", n);
		else if ("JSON" == n.recvType) {
			var e = null;
			try {
				e = new Function("return (" + n.XMLHttpRequest.responseText + ")")()
			} catch(t) {
				e = null
			}
			n.resultHandle(e, n)
		}
	},
	n.get = function(e, t) {
		e = hostconvert(e),
		setTimeout(function() {
			n.showLoading()
		},
		250),
		n.targetUrl = e,
		n.XMLHttpRequest.onreadystatechange = n.processHandle,
		n.resultHandle = t;
		var a = isUndefined(a) ? 0 : a;
		window.XMLHttpRequest ? (n.XMLHttpRequest.open("GET", n.targetUrl), n.XMLHttpRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest"), n.XMLHttpRequest.send(null)) : (n.XMLHttpRequest.open("GET", e, !0), n.XMLHttpRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest"), n.XMLHttpRequest.send())
	},
	n.post = function(e, t, a) {
		e = hostconvert(e),
		setTimeout(function() {
			n.showLoading()
		},
		250),
		n.targetUrl = e,
		n.sendString = t,
		n.XMLHttpRequest.onreadystatechange = n.processHandle,
		n.resultHandle = a,
		n.XMLHttpRequest.open("POST", e),
		n.XMLHttpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded"),
		n.XMLHttpRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest"),
		n.XMLHttpRequest.send(n.sendString)
	},
	n.getJSON = function(e, t) {
		n.setRecvType("JSON"),
		n.get(e + "&ajaxdata=json", t)
	},
	n.getHTML = function(e, t) {
		n.setRecvType("HTML"),
		n.get(e + "&ajaxdata=html", t)
	},
	n
}
function getHost(e) {
	var t = "null";
	void 0 !== e && null != e || (e = window.location.href);
	var n = e.match(/^\w+\:\/\/([^\/]*).*/);
	return void 0 !== n && null != n && (t = n[1]),
	t
}
function hostconvert(e) {
	e.match(/^https?:\/\//) || (e = SITEURL + e);
	var t = getHost(e),
	n = getHost().toLowerCase();
	return t && n != t && (e = e.replace(t, n)),
	e
}
function newfunction(e) {
	for (var t = [], n = 1; n < arguments.length; n++) t.push(arguments[n]);
	return function(n) {
		return doane(n),
		window[e].apply(window, t),
		!1
	}
}
function evalscript(e) {
	if ( - 1 == e.indexOf("<script")) return e;
	for (var t = /<script[^\>]*?>([^\x00]*?)<\/script>/gi,
	n = []; n = t.exec(e);) {
		var a = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i,
		i = []; (i = a.exec(n[0])) ? appendscript(i[1], "", i[2], i[3]) : appendscript("", (i = (a = /<script(.*?)>([^\x00]+?)<\/script>/i).exec(n[0]))[2], -1 != i[1].indexOf("reload="))
	}
	return e
}
BROWSER.firefox && window.HTMLElement && (HTMLElement.prototype.__defineGetter__("innerText",
function() {
	for (var e = "",
	t = this.childNodes,
	n = 0; n < t.length; n++) 1 == t[n].nodeType ? e += "BR" == t[n].tagName ? "\n": t[n].innerText: 3 == t[n].nodeType && (e += t[n].nodeValue);
	return e
}), HTMLElement.prototype.__defineSetter__("innerText",
function(e) {
	this.textContent = e
}), HTMLElement.prototype.__defineSetter__("outerHTML",
function(e) {
	var t = this.ownerDocument.createRange();
	t.setStartBefore(this);
	var n = t.createContextualFragment(e);
	return this.parentNode.replaceChild(n, this),
	e
}), HTMLElement.prototype.__defineGetter__("outerHTML",
function() {
	for (var e, t = this.attributes,
	n = "<" + this.tagName.toLowerCase(), a = 0; a < t.length; a++)(e = t[a]).specified && (n += " " + e.name + '="' + e.value + '"');
	return this.canHaveChildren ? n + ">" + this.innerHTML + "</" + this.tagName.toLowerCase() + ">": n + ">"
}), HTMLElement.prototype.__defineGetter__("canHaveChildren",
function() {
	switch (this.tagName.toLowerCase()) {
	case "area":
	case "base":
	case "basefont":
	case "col":
	case "frame":
	case "hr":
	case "img":
	case "br":
	case "input":
	case "isindex":
	case "link":
	case "meta":
	case "param":
		return ! 1
	}
	return ! 0
})),
"onfocusin" in document ? (document.onfocusin = function() {
	CurrentActive = !0
},
document.onfocusout = function() {
	CurrentActive = !1
}) : (window.onfocus = function() {
	CurrentActive = !0
},
window.onblur = function() {
	CurrentActive = !1
});
var safescripts = {},
evalscripts = [];
function safescript(id, call, seconds, times, timeoutcall, endcall, index) {
	seconds = seconds || 1e3,
	times = times || 0;
	var checked = !0;
	try {
		"function" == typeof call ? call() : eval(call)
	} catch(e) {
		checked = !1
	}
	if (checked) try {
		index = (index || 1) - 1,
		safescripts[id][index].si && clearInterval(safescripts[id][index].si),
		"function" == typeof endcall ? endcall() : eval(endcall)
	} catch(e) {} else safescripts[id] && index ? (index = (index || 1) - 1, safescripts[id][index].times++, safescripts[id][index].times >= times && (clearInterval(safescripts[id][index].si), "function" == typeof timeoutcall ? timeoutcall() : eval(timeoutcall))) : (safescripts[id] = safescripts[id] || [], safescripts[id].push({
		times: 0,
		si: setInterval(function() {
			safescript(id, call, seconds, times, timeoutcall, endcall, safescripts[id].length)
		},
		seconds)
	}))
}
function appendscript(e, t, n, a) {
	var i = dhash(e + t);
	if (n || !in_array(i, evalscripts)) {
		n && document.getElementById(i) && document.getElementById(i).parentNode.removeChild(document.getElementById(i)),
		evalscripts.push(i);
		var o = document.createElement("script");
		o.type = "text/javascript",
		o.setAttribute("ajaxappend", "1"),
		o.id = i,
		o.charset = a || (BROWSER.firefox ? document.characterSet: document.charset);
		try {
			e ? (o.src = e, o.onloadDone = !1, o.onload = function() {
				o.onloadDone = !0,
				JSLOADED[e] = 1
			},
			o.onreadystatechange = function() {
				"loaded" != o.readyState && "complete" != o.readyState || o.onloadDone || (o.onloadDone = !0, JSLOADED[e] = 1)
			}) : t && (o.text = t),
			document.getElementsByTagName("head")[0].appendChild(o)
		} catch(e) {}
	}
}
function stripscript(e) {
	return e.replace(/<script.*?>.*?<\/script>/gi, "")
}
function ajaxupdateevents(e, t) {
	t = t || "A";
	var n = e.getElementsByTagName(t);
	for (k in n) ajaxupdateevent(n[k])
}
function ajaxupdateevent(e) {
	if ("object" == typeof e && e.getAttribute && e.getAttribute("ajaxtarget")) {
		e.id || (e.id = Math.random());
		var t = e.getAttribute("ajaxevent") ? e.getAttribute("ajaxevent") : "click";
		_attachEvent(e, t, newfunction("ajaxget", e.getAttribute("ajaxurl") ? e.getAttribute("ajaxurl") : e.href, e.getAttribute("ajaxtarget"), e.getAttribute("ajaxwaitid"), e.getAttribute("ajaxloading"), e.getAttribute("ajaxdisplay"))),
		e.getAttribute("ajaxfunc") && (e.getAttribute("ajaxfunc").match(/(\w+)\((.+?)\)/), _attachEvent(e, t, newfunction(RegExp.$1, RegExp.$2)))
	}
}
function ajaxget(url, showid, waitid, loading, display, recall) {
	waitid = null == waitid ? showid: waitid;
	var x = new Ajax;
	x.setLoading(loading),
	x.setWaitId(waitid),
	x.display = void 0 === display || null == display ? "": display,
	x.showId = document.getElementById(showid),
	"#" == url.substr(strlen(url) - 1) && (url = url.substr(0, strlen(url) - 1), x.autogoto = 1);
	var url = url + "&inajax=1&ajaxtarget=" + showid;
	url && -1 == url.indexOf("?") && (url = url.replace(/&/i, "?")),
	x.get(url,
	function(s, x) {
		var evaled = !1; - 1 != s.indexOf("ajaxerror") && (evalscript(s), evaled = !0),
		evaled || "undefined" != typeof ajaxerror && ajaxerror || x.showId && (x.showId.style.display = x.display, ajaxinnerhtml(x.showId, s), ajaxupdateevents(x.showId), x.autogoto && scroll(0, x.showId.offsetTop)),
		ajaxerror = null,
		recall && "function" == typeof recall ? recall() : recall && eval(recall),
		evaled || evalscript(s)
	})
}
function ajaxpost(formid, showid, waitid, showidclass, submitbtn, recall) {
	var waitid = null == waitid ? showid: "" !== waitid ? waitid: "",
	showidclass = showidclass || "",
	ajaxframeid = "ajaxframe",
	ajaxframe = document.getElementById(ajaxframeid),
	curform = document.getElementById(formid),
	formtarget = curform.target,
	handleResult = function() {
		var s = "",
		evaled = !1;
		showloading("none");
		try {
			s = document.getElementById(ajaxframeid).contentWindow.document.XMLDocument.text
		} catch(e) {
			try {
				s = document.getElementById(ajaxframeid).contentWindow.document.documentElement.firstChild.wholeText
			} catch(e) {
				try {
					s = document.getElementById(ajaxframeid).contentWindow.document.documentElement.firstChild.nodeValue
				} catch(e) {
					s = __lang.internal_error_unable_display_content
				}
			}
		}
		"" != s && -1 != s.indexOf("ajaxerror") && (evalscript(s), evaled = !0),
		showidclass && ("onerror" != showidclass ? document.getElementById(showid).className = showidclass: (showError(s), ajaxerror = !0)),
		submitbtn && (submitbtn.disabled = !1),
		evaled || "undefined" != typeof ajaxerror && ajaxerror || ajaxinnerhtml(document.getElementById(showid), s),
		ajaxerror = null,
		curform && (curform.target = formtarget),
		"function" == typeof recall ? recall() : eval(recall),
		evaled || evalscript(s),
		ajaxframe.loading = 0,
		!BROWSER.firefox || BROWSER.safari ? document.getElementById("append_parent").removeChild(ajaxframe.parentNode) : setTimeout(function() {
			document.getElementById("append_parent").removeChild(ajaxframe.parentNode)
		},
		100)
	};
	if (ajaxframe) {
		if (ajaxframe.loading) return ! 1
	} else {
		var div = document.createElement("div");
		div.style.display = "none",
		div.innerHTML = '<iframe name="' + ajaxframeid + '" id="' + ajaxframeid + '" loading="1"></iframe>',
		document.getElementById("append_parent").appendChild(div),
		ajaxframe = document.getElementById(ajaxframeid)
	}
	_attachEvent(ajaxframe, "load", handleResult);
	curform.target = ajaxframeid;
	var action = curform.getAttribute("action");
	action = hostconvert(action).replace(/(&|&|\?)inajax\=1/g, "");
	var s = -1 != action.indexOf("?") ? "&": "?";
	return curform.action = action + s + "inajax=1",
	curform.submit(),
	submitbtn && (submitbtn.disabled = !0),
	doane(),
	!1
}
function ajaxmenu(ctrlObj, timeout, cache, duration, pos, recall, idclass, contentclass) {
	if (ctrlObj.getAttribute("mid")) {
		var ctrlid = ctrlObj.getAttribute("mid");
		ctrlObj.id || (ctrlObj.id = "ajaxid_" + Math.random())
	} else {
		var ctrlid = ctrlObj.id;
		ctrlid || (ctrlObj.id = "ajaxid_" + Math.random())
	}
	var menuid = ctrlid + "_menu",
	menu = document.getElementById(menuid);
	isUndefined(timeout) && (timeout = 3e3),
	isUndefined(cache) && (cache = 1),
	isUndefined(pos) && (pos = "43"),
	isUndefined(duration) && (duration = timeout > 0 ? 0 : 3),
	isUndefined(idclass) && (idclass = "p_pop"),
	isUndefined(contentclass) && (contentclass = "p_opt");
	var func = function() {
		showMenu({
			ctrlid: ctrlObj.id,
			menuid: menuid,
			duration: duration,
			timeout: timeout,
			pos: pos,
			cache: cache,
			layer: 2
		}),
		"function" == typeof recall ? recall() : eval(recall)
	};
	if (menu)"" == menu.style.display ? hideMenu(menuid) : func();
	else {
		menu = document.createElement("div"),
		menu.id = menuid,
		menu.style.display = "none",
		menu.className = idclass,
		menu.innerHTML = '<div class="' + contentclass + '" id="' + menuid + '_content"></div>',
		document.getElementById("append_parent").appendChild(menu);
		var url = isUndefined(ctrlObj.attributes.shref) ? isUndefined(ctrlObj.href) ? ctrlObj.attributes.href.value: ctrlObj.href: ctrlObj.attributes.shref.value;
		url += ( - 1 != url.indexOf("?") ? "&": "?") + "ajaxmenu=1",
		ajaxget(url, menuid + "_content", "ajaxwaitid", "", "", func)
	}
	doane()
}
function dhash(e, t) {
	t = t || 32;
	var n = 0,
	a = 0,
	i = "";
	for (filllen = t - e.length % t, a = 0; a < filllen; a++) e += "0";
	for (; n < e.length;) i = stringxor(i, e.substr(n, t)),
	n += t;
	return i
}
function stringxor(e, t) {
	for (var n = "",
	a = Math.max(e.length, t.length), i = 0; i < a; i++) {
		var o = e.charCodeAt(i) ^ t.charCodeAt(i);
		n += "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(o % 52)
	}
	return n
}
function showloading(e, t) {
	e = e || "block",
	t = t || __lang.please_wait,
	document.getElementById("ajaxwaitid").innerHTML = t,
	document.getElementById("ajaxwaitid").style.display = e
}
function ajaxinnerhtml(e, t) {
	if ("TBODY" != e.tagName) e.innerHTML = t;
	else {
		for (; e.firstChild;) e.firstChild.parentNode.removeChild(e.firstChild);
		var n = document.createElement("DIV");
		n.id = e.id + "_div",
		n.innerHTML = '<table><tbody id="' + e.id + '_tbody">' + t + "</tbody></table>",
		document.getElementById("append_parent").appendChild(n);
		for (var a = n.getElementsByTagName("TR"), i = a.length, o = 0; o < i; o++) e.appendChild(a[0]);
		var r = n.getElementsByTagName("INPUT");
		for (i = r.length, o = 0; o < i; o++) e.appendChild(r[0]);
		n.parentNode.removeChild(n)
	}
}
function doane(t, n, a) {
	return n = isUndefined(n) ? 1 : n,
	a = isUndefined(a) ? 1 : a,
	e = t || window.event,
	e ? (n && (e.preventDefault ? e.preventDefault() : e.returnValue = !1), a && (e.stopPropagation ? e.stopPropagation() : e.cancelBubble = !0), e) : null
}
function loadcss(e, t) {
	CSSLOADED[e] || (document.getElementById("css_" + e) ? document.getElementById("css_" + e).href = t + "?" + VERHASH: (css = document.createElement("link"), css.id = "css_" + e, css.type = "text/css", css.rel = "stylesheet", css.href = t + "?" + VERHASH, document.getElementsByTagName("head")[0].appendChild(css)), CSSLOADED[e] = 1)
}
function showMenu(v) {
	var ctrlid = isUndefined(v.ctrlid) ? v: v.ctrlid,
	showid = isUndefined(v.showid) ? ctrlid: v.showid,
	menuid = isUndefined(v.menuid) ? showid + "_menu": v.menuid,
	ctrlObj = document.getElementById(ctrlid),
	menuObj = document.getElementById(menuid);
	if (menuObj) {
		var mtype = isUndefined(v.mtype) ? "menu": v.mtype,
		evt = isUndefined(v.evt) ? "mouseover": v.evt,
		pos = isUndefined(v.pos) ? "43": v.pos,
		layer = isUndefined(v.layer) ? 1 : v.layer,
		duration = isUndefined(v.duration) ? 2 : v.duration,
		timeout = isUndefined(v.timeout) ? 250 : v.timeout,
		maxh = isUndefined(v.maxh) ? 600 : v.maxh,
		cache = isUndefined(v.cache) ? 1 : v.cache,
		drag = isUndefined(v.drag) ? "": v.drag,
		dragobj = drag && document.getElementById(drag) ? document.getElementById(drag) : menuObj,
		fade = isUndefined(v.fade) ? 0 : v.fade,
		cover = isUndefined(v.cover) ? 0 : v.cover,
		zindex = isUndefined(v.zindex) ? JSMENU.zIndex.menu: v.zindex,
		ctrlclass = isUndefined(v.ctrlclass) ? "": v.ctrlclass,
		winhandlekey = isUndefined(v.win) ? "": v.win;
		for (i in zindex = cover ? zindex + 500 : zindex, void 0 === JSMENU.active[layer] && (JSMENU.active[layer] = []), EXTRAFUNC.showmenu) try {
			eval(EXTRAFUNC.showmenu[i] + "()")
		} catch(e) {}
		if ("click" == evt && in_array(menuid, JSMENU.active[layer]) && "win" != mtype) hideMenu(menuid, mtype);
		else {
			if ("menu" == mtype && hideMenu(layer, mtype), ctrlObj && (ctrlObj.getAttribute("initialized") || (ctrlObj.setAttribute("initialized", !0), ctrlObj.unselectable = !0, ctrlObj.outfunc = "function" == typeof ctrlObj.onmouseout ? ctrlObj.onmouseout: null, ctrlObj.onmouseout = function() {
				this.outfunc && this.outfunc(),
				duration < 3 && !JSMENU.timer[menuid] && (JSMENU.timer[menuid] = setTimeout(function() {
					hideMenu(menuid, mtype)
				},
				timeout))
			},
			ctrlObj.overfunc = "function" == typeof ctrlObj.onmouseover ? ctrlObj.onmouseover: null, ctrlObj.onmouseover = function(e) {
				if (doane(e), this.overfunc && this.overfunc(), "click" == evt) clearTimeout(JSMENU.timer[menuid]),
				JSMENU.timer[menuid] = null;
				else for (var t in JSMENU.timer) JSMENU.timer[t] && (clearTimeout(JSMENU.timer[t]), JSMENU.timer[t] = null)
			})), !menuObj.getAttribute("initialized") && (menuObj.setAttribute("initialized", !0), menuObj.ctrlkey = ctrlid, menuObj.mtype = mtype, menuObj.layer = layer, menuObj.cover = cover, ctrlObj && ctrlObj.getAttribute("fwin") && (menuObj.scrolly = !0), menuObj.style.position = "absolute", menuObj.style.zIndex = zindex + layer, menuObj.onclick = function(e) {
				return doane(e, 0, 1)
			},
			duration < 3 && (duration > 1 && (menuObj.onmouseover = function() {
				clearTimeout(JSMENU.timer[menuid]),
				JSMENU.timer[menuid] = null
			}), 1 != duration && (menuObj.onmouseout = function() {
				JSMENU.timer[menuid] = setTimeout(function() {
					hideMenu(menuid, mtype)
				},
				timeout)
			})), cover)) {
				var coverObj = document.createElement("div");
				coverObj.id = menuid + "_cover",
				coverObj.style.position = "absolute",
				coverObj.style.zIndex = menuObj.style.zIndex - 1,
				coverObj.style.left = coverObj.style.top = "0px",
				coverObj.style.width = "100%",
				coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + "px",
				coverObj.style.backgroundColor = "#000",
				coverObj.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=50)",
				coverObj.style.opacity = .5,
				coverObj.onclick = function() {
					hideMenu()
				},
				document.getElementById("append_parent").appendChild(coverObj),
				_attachEvent(window, "load",
				function() {
					coverObj.style.height = Math.max(document.documentElement.clientHeight, document.body.offsetHeight) + "px"
				},
				document)
			}
			if (drag && (dragobj.style.cursor = "move", dragobj.onmousedown = function(e) {
				try {
					dragMenu(menuObj, e, 1)
				} catch(e) {}
			}), cover && (document.getElementById(menuid + "_cover").style.display = ""), fade) {
				var O = 0,
				fadeIn = function(e) {
					if (e > 100) clearTimeout(t);
					else {
						menuObj.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + e + ")",
						menuObj.style.opacity = e / 100,
						e += 20;
						var t = setTimeout(function() {
							fadeIn(e)
						},
						40)
					}
				};
				fadeIn(O),
				menuObj.fade = !0
			} else menuObj.fade = !1;
			menuObj.style.display = "",
			ctrlObj && ctrlclass && (ctrlObj.className += " " + ctrlclass, menuObj.setAttribute("ctrlid", ctrlid), menuObj.setAttribute("ctrlclass", ctrlclass)),
			"*" != pos && setMenuPosition(showid, menuid, pos),
			BROWSER.ie && BROWSER.ie < 7 && winhandlekey && document.getElementById("fwin_" + winhandlekey) && (document.getElementById(menuid).style.left = parseInt(document.getElementById(menuid).style.left) - parseInt(document.getElementById("fwin_" + winhandlekey).style.left) + "px", document.getElementById(menuid).style.top = parseInt(document.getElementById(menuid).style.top) - parseInt(document.getElementById("fwin_" + winhandlekey).style.top) + "px"),
			maxh && menuObj.scrollHeight > maxh && (menuObj.style.height = maxh + "px", BROWSER.opera ? menuObj.style.overflow = "auto": menuObj.style.overflowY = "auto"),
			duration || setTimeout("hideMenu('" + menuid + "', '" + mtype + "')", timeout),
			in_array(menuid, JSMENU.active[layer]) || JSMENU.active[layer].push(menuid),
			menuObj.cache = cache,
			layer > JSMENU.layer && (JSMENU.layer = layer);
			var hasshow = function(e) {
				for (; e.parentNode && "none" !== (void 0 === e.currentStyle ? window.getComputedStyle(e, null) : e.currentStyle).display;) e = e.parentNode;
				return e === document
			};
			if (!menuObj.getAttribute("disautofocus")) try {
				for (var focused = !1,
				tags = ["input", "select", "textarea", "button", "a"], i = 0; i < tags.length; i++) {
					var _all = menuObj.getElementsByTagName(tags[i]);
					if (_all.length) for (j = 0; j < _all.length; j++) if ((!_all[j].type || "hidden" != _all[j].type) && hasshow(_all[j])) { - 1 == _all[j].className.indexOf("hidefocus") && (_all[j].className += " hidefocus"),
						_all[j].focus(),
						focused = !0;
						var cobj = _all[j];
						_attachEvent(_all[j], "blur",
						function() {
							cobj.className = trim(cobj.className.replace(" hidefocus", ""))
						});
						break
					}
					if (focused) break
				}
			} catch(e) {}
		}
	}
}
var delayShowST = null;
function delayShow(ctrlObj, call, time) {
	if ("object" == typeof ctrlObj) {
		var ctrlid = ctrlObj.id;
		call = call ||
		function() {
			showMenu(ctrlid)
		}
	}
	var time = isUndefined(time) ? 500 : time;
	delayShowST = setTimeout(function() {
		"function" == typeof call ? call() : eval(call)
	},
	time),
	ctrlObj.delayinit || (_attachEvent(ctrlObj, "mouseout",
	function() {
		clearTimeout(delayShowST)
	}), ctrlObj.delayinit = 1)
}
var dragMenuDisabled = !1;
function dragMenu(e, t, n) {
	if (t = t || window.event, 1 == n) {
		if (dragMenuDisabled || in_array(t.target ? t.target.tagName: t.srcElement.tagName, ["TEXTAREA", "INPUT", "BUTTON", "SELECT"])) return;
		JSMENU.drag = [t.clientX, t.clientY],
		JSMENU.drag[2] = parseInt(e.style.left),
		JSMENU.drag[3] = parseInt(e.style.top),
		document.onmousemove = function(t) {
			try {
				dragMenu(e, t, 2)
			} catch(e) {}
		},
		document.onmouseup = function(t) {
			try {
				dragMenu(e, t, 3)
			} catch(e) {}
		},
		doane(t)
	} else if (2 == n && JSMENU.drag[0]) {
		var a = [t.clientX, t.clientY];
		e.style.left = JSMENU.drag[2] + a[0] - JSMENU.drag[0] + "px",
		e.style.top = JSMENU.drag[3] + a[1] - JSMENU.drag[1] + "px",
		e.removeAttribute("top_"),
		e.removeAttribute("left_"),
		doane(t)
	} else 3 == n && (JSMENU.drag = [], document.onmousemove = null, document.onmouseup = null)
}
function setMenuPosition(e, t, n) {
	var a = document.getElementById(e),
	i = t ? document.getElementById(t) : document.getElementById(e + "_menu"); ! isUndefined(n) && n || (n = "43");
	var o, r, c = parseInt(n.substr(0, 1)),
	s = parseInt(n.substr(1, 1)),
	d = -1 != n.indexOf("!") ? 1 : 0,
	l = 0,
	u = 0,
	m = 0,
	f = 0,
	p = 0,
	h = 0,
	g = 0,
	v = 0,
	y = 0;
	if (! (!i || c > 0 && !a)) {
		switch (a && (u = (l = jQuery(a).offset()).left, m = l.top, f = jQuery(a).outerWidth(!0), p = jQuery(a).outerHeight(!0)), o = i.offsetWidth, i.clientWidth, r = i.offsetHeight, i.clientHeight, c) {
		case 1:
			v = u,
			y = m;
			break;
		case 2:
			v = u + f,
			y = m;
			break;
		case 3:
			v = u + f,
			y = m + p;
			break;
		case 4:
			v = u,
			y = m + p
		}
		switch (s) {
		case 0:
			i.style.left = (document.body.clientWidth - i.clientWidth) / 2 + "px",
			g = (document.documentElement.clientHeight - i.clientHeight) / 2;
			break;
		case 1:
			h = v - o,
			g = y - r;
			break;
		case 2:
			h = v,
			g = y - r;
			break;
		case 3:
			h = v,
			g = y;
			break;
		case 4:
			h = v - o,
			g = y
		}
		d || (in_array(s, [1, 4]) && h < 0 ? (h = v, in_array(c, [1, 4]) && (h += f)) : h + o > 0 + document.body.clientWidth && u >= o && (h = v - o, in_array(c, [2, 3]) ? h -= f: 4 == c && (h += f)), in_array(s, [1, 2]) && g < 0 ? (g = y, in_array(c, [1, 2]) && (g += p)) : g + r > 0 + document.documentElement.clientHeight && m >= r && (g = y - r, in_array(c, [3, 4]) && (g -= p))),
		"210" == n.substr(0, 3) && (h += 69 - f / 2, g -= 5, "TEXTAREA" == a.tagName && (h -= f / 2, g += p / 2)),
		(0 == s || i.scrolly) && (BROWSER.ie && BROWSER.ie < 7 ? 0 == s && (g += 0) : (i.scrolly && (g -= 0), i.style.position = "fixed")),
		document.body.clientWidth > 0 && h > 0 && h + o > document.body.clientWidth && (h = document.body.clientWidth - o - 10 > 0 ? document.body.clientWidth - o - 10 : 0),
		h && (i.style.left = h + "px"),
		g && (i.style.top = g + "px"),
		0 == s && BROWSER.ie && !document.documentElement.clientHeight && (i.style.position = "absolute", i.style.top = (document.body.clientHeight - i.clientHeight) / 2 + "px"),
		i.style.clip && !BROWSER.opera && (i.style.clip = "rect(auto, auto, auto, auto)")
	}
}
function hideMenu(e, t) {
	if (e = isUndefined(e) ? "": e, t = isUndefined(t) ? "menu": t, "" != e) if ("number" != typeof e) {
		if ("string" == typeof e) {
			var n = document.getElementById(e);
			if (!n || t && n.mtype != t) return;
			var a = "",
			i = "";
			if ((a = document.getElementById(n.getAttribute("ctrlid"))) && (i = n.getAttribute("ctrlclass"))) {
				var o = new RegExp(" " + i);
				a.className = a.className.replace(o, "")
			}
			clearTimeout(JSMENU.timer[e]);
			var r = function() {
				n.cache ? "hidden" != n.style.visibility && (n.style.display = "none", n.cover && (document.getElementById(e + "_cover").style.display = "none")) : (n.parentNode.removeChild(n), n.cover && document.getElementById(e + "_cover").parentNode.removeChild(document.getElementById(e + "_cover")));
				var t = [];
				for (var a in JSMENU.active[n.layer]) e != JSMENU.active[n.layer][a] && t.push(JSMENU.active[n.layer][a]);
				JSMENU.active[n.layer] = t
			};
			if (n.fade) {
				var c = function(e) {
					if (0 == e) return clearTimeout(t),
					void r();
					n.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + e + ")",
					n.style.opacity = e / 100,
					e -= 20;
					var t = setTimeout(function() {
						c(e)
					},
					40)
				};
				c(100)
			} else r()
		}
	} else for (var s in JSMENU.active[e]) hideMenu(JSMENU.active[e][s], t);
	else for (var d = 1; d <= JSMENU.layer; d++) hideMenu(d, t)
}
function getCurrentStyle(e, t, n) {
	if (e.style[t]) return e.style[t];
	if (e.currentStyle) return e.currentStyle[t];
	if (document.defaultView.getComputedStyle(e, null)) {
		var a = (i = document.defaultView.getComputedStyle(e, null)).getPropertyValue(n);
		return a || (a = i[t]),
		a
	}
	var i;
	return window.getComputedStyle ? (i = window.getComputedStyle(e, "")).getPropertyValue(n) : void 0
}
function fetchOffset(e, t) {
	var n = 0,
	a = 0;
	if (t = t || 0, e.getBoundingClientRect && !t) {
		var i = e.getBoundingClientRect(),
		o = Math.max(document.documentElement.scrollTop, document.body.scrollTop),
		r = Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
		"rtl" == document.documentElement.dir && (r = r + document.documentElement.clientWidth - document.documentElement.scrollWidth),
		n = i.left + r - document.documentElement.clientLeft,
		a = i.top + o - document.documentElement.clientTop
	}
	if (n <= 0 || a <= 0) for (n = e.offsetLeft, a = e.offsetTop; null != (e = e.offsetParent);) position = getCurrentStyle(e, "position", "position"),
	"relative" != position && (n += e.offsetLeft, a += e.offsetTop);
	return {
		left: n,
		top: a
	}
}
function showError(e) {
	"" !== (e = e.replace(/<script[^\>]*?>([^\x00]*?)<\/script>/gi, "")) && showDialog(e, "alert", __lang.db_error_message, null, !0, null, "", "", "", 3)
}
function hideWindow(k, all, clear) {
    all = isUndefined(all) ? 1 : all;
	clear = isUndefined(clear) ? 1 : clear;
    var modal = jQuery('#fwin_' + k);
    if (modal.length) {
        modal.modal('hide');
        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    }
    
    if (all) {
		jQuery('.modal.fwinmask').modal('hide').remove();
    }
}
function AC_FL_RunContent() {
	var e = "",
	t = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
	if (BROWSER.ie && !BROWSER.opera) {
		for (var n in e += "<object ",
		t.objAttrs) e += n + '="' + t.objAttrs[n] + '" ';
		for (var n in e += ">",
		t.params) e += '<param name="' + n + '" value="' + t.params[n] + '" /> ';
		e += "</object>"
	} else {
		for (var n in e += "<embed ",
		t.embedAttrs) e += n + '="' + t.embedAttrs[n] + '" ';
		e += "></embed>"
	}
	return e
}
function AC_GetArgs(e, t, n) {
	var a = new Object;
	a.embedAttrs = new Object,
	a.params = new Object,
	a.objAttrs = new Object;
	for (var i = 0; i < e.length; i += 2) switch (e[i].toLowerCase()) {
	case "classid":
		break;
	case "pluginspage":
		a.embedAttrs[e[i]] = "http://www.macromedia.com/go/getflashplayer";
		break;
	case "src":
		a.embedAttrs[e[i]] = e[i + 1],
		a.params.movie = e[i + 1];
		break;
	case "codebase":
		a.objAttrs[e[i]] = "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0";
		break;
	case "onafterupdate":
	case "onbeforeupdate":
	case "onblur":
	case "oncellchange":
	case "onclick":
	case "ondblclick":
	case "ondrag":
	case "ondragend":
	case "ondragenter":
	case "ondragleave":
	case "ondragover":
	case "ondrop":
	case "onfinish":
	case "onfocus":
	case "onhelp":
	case "onmousedown":
	case "onmouseup":
	case "onmouseover":
	case "onmousemove":
	case "onmouseout":
	case "onkeypress":
	case "onkeydown":
	case "onkeyup":
	case "onload":
	case "onlosecapture":
	case "onpropertychange":
	case "onreadystatechange":
	case "onrowsdelete":
	case "onrowenter":
	case "onrowexit":
	case "onrowsinserted":
	case "onstart":
	case "onscroll":
	case "onbeforeeditfocus":
	case "onactivate":
	case "onbeforedeactivate":
	case "ondeactivate":
	case "type":
	case "id":
		a.objAttrs[e[i]] = e[i + 1];
		break;
	case "width":
	case "height":
	case "align":
	case "vspace":
	case "hspace":
	case "class":
	case "title":
	case "accesskey":
	case "name":
	case "tabindex":
		a.embedAttrs[e[i]] = a.objAttrs[e[i]] = e[i + 1];
		break;
	default:
		a.embedAttrs[e[i]] = a.params[e[i]] = e[i + 1]
	}
	return a.objAttrs.classid = t,
	n && (a.embedAttrs.type = n),
	a
}
var secST = new Array;
function strLenCalc(e, t, n) {
	for (var a = e.value,
	i = n = n || 200,
	o = strlen(a), r = 0; r < a.length; r++)(a.charCodeAt(r) < 0 || a.charCodeAt(r) > 255) && (i -= "utf-8" == charset ? 2 : 1);
	i >= o ? document.getElementById(t).innerHTML = i - o: e.value = mb_cutstr(a, n, 0)
}
if (BROWSER.ie && BROWSER.ie < 11) try {
	document.documentElement.addBehavior("#default#userdata")
} catch(e) {}
function updateseccode(idhash, play) {
	isUndefined(play) ? document.getElementById("seccode_" + idhash) && (document.getElementById("seccodeverify_" + idhash).value = "", secST["code_" + idhash] && clearTimeout(secST["code_" + idhash]), ajaxget("misc.php?mod=seccode&action=update&idhash=" + idhash, "seccode_" + idhash, null, "", "",
	function() {
		secST["code_" + idhash] = setTimeout(function() {
			document.getElementById("seccode_" + idhash).innerHTML = '<span class="btn btn-primary" onclick="updateseccode(\'' + idhash + "')\">" + __lang.refresh_verification_code + "</span>"
		},
		18e4)
	})) : eval("window.document.seccodeplayer_" + idhash + '.SetVariable("isPlay", "1")')
}
function showdistrict(e, t, n, a, i) {
	var o = function(e) {
		var t = e.options[e.selectedIndex];
		return t.did || t.getAttribute("did") || "0"
	},
	r = a >= 1 && t[0] && document.getElementById(t[0]) ? o(document.getElementById(t[0])) : 0,
	c = a >= 2 && t[1] && document.getElementById(t[1]) ? o(document.getElementById(t[1])) : 0,
	s = a >= 3 && t[2] && document.getElementById(t[2]) ? o(document.getElementById(t[2])) : 0,
	d = a >= 4 && t[3] && document.getElementById(t[3]) ? o(document.getElementById(t[3])) : 0;
	ajaxget("user.php?mod=ajax&action=district&container=" + e + "&containertype=" + i + "&province=" + t[0] + "&city=" + t[1] + "&district=" + t[2] + "&community=" + t[3] + "&pid=" + r + "&cid=" + c + "&did=" + s + "&coid=" + d + "&level=" + n + "&handlekey=" + e + "&inajax=1" + (a ? "": "&showdefault=1"), e, "")
}
function showbirthday() {
	var e = document.getElementById("birthday"),
	t = e.value;
	e.length = 0,
	e.options.add(new Option(__lang.day, ""));
	for (var n = 0; n < 28; n++) e.options.add(new Option(n + 1, n + 1));
	if ("2" != document.getElementById("birthmonth").value) switch (e.options.add(new Option(29, 29)), e.options.add(new Option(30, 30)), document.getElementById("birthmonth").value) {
	case "1":
	case "3":
	case "5":
	case "7":
	case "8":
	case "10":
	case "12":
		e.options.add(new Option(31, 31))
	} else if ("" != document.getElementById("birthyear").value) {
		var a = document.getElementById("birthyear").value; (a % 400 == 0 || a % 4 == 0 && a % 100 != 0) && e.options.add(new Option(29, 29))
	}
	e.value = t
}
var tipTimer = [];
function showTip(e, t, n) {
	e.id || (e.id = "tip_" + Math.random());
	var a = "";
	switch (t) {
	case "12":
		a = "tip_4 bs-popover-top";
		break;
	case "21":
		a = "tip_3 bs-popover-bottom";
		break;
	case "43":
		a = "tip_1 bs-popover-bottom";
		break;
	case "34":
		a = "tip_2 bs-popover-left";
		break;
	default:
		t = "12",
		a = "tip_4 bs-popover-top"
	}
	if (menuid = e.id + "_menu", !document.getElementById(menuid)) {
		var i = document.createElement("div");
		i.id = e.id + "_menu",
		i.className = "popover tip " + a,
		i.style.display = "none",
		i.innerHTML = '<div class="popover-arrow"></div><div class="popover-header">' + (n || e.getAttribute("tip")) + "</div>",
		document.getElementById("append_parent").appendChild(i),
		i.onmouseover = function() {
			tipTimer[this.id] && window.clearTimeout(tipTimer[this.id])
		},
		i.onmouseout = function() {
			var e = this;
			tipTimer[this.id] = window.setTimeout(function() {
				hideMenu(e.id, "prompt")
			},
			200)
		}
	}
	document.getElementById(e.id).onmouseout = function() {
		var e = this;
		tipTimer[this.id + "_menu"] = window.setTimeout(function() {
			hideMenu(e.id + "_menu", "prompt")
		},
		200)
	},
	showMenu({
		mtype: "prompt",
		ctrlid: e.id,
		pos: t + "!",
		duration: 3,
		zindex: JSMENU.zIndex.prompt
	})
}
var showDialogST = null;
function showDialog(msg, mode, t, func, cover, funccancel, leftmsg, confirmtxt, canceltxt, closetime, locationtime) {
	clearTimeout(showDialogST),
	cover = isUndefined(cover) ? "info" == mode || "icon" == mode ? 0 : 1 : cover,
	leftmsg = isUndefined(leftmsg) ? "": leftmsg,
	mode || (mode = "alert");
	var menuid = "fwin_dialog",
	menuObj = document.getElementById(menuid),
	showconfirm = 1;
	confirmtxtdefault = __lang.confirms,
	closetime = isUndefined(closetime) ? "": closetime,
	closefunc = function() {
		"function" == typeof func ? func() : eval(func),
		hideMenu(menuid, "dialog")
	},
	closetime && (leftmsg = closetime + __lang.message_closetime, showDialogST = setTimeout(closefunc, 1e3 * closetime)),
	locationtime = isUndefined(locationtime) ? "": locationtime,
	locationtime && (leftmsg = locationtime + __lang.message_locationtime, showDialogST = setTimeout(closefunc, 1e3 * locationtime)),
	confirmtxt = confirmtxt || confirmtxtdefault,
	canceltxt = canceltxt || __lang.cancel,
	menuObj && hideMenu("fwin_dialog", "dialog"),
	menuObj = document.createElement("div"),
	menuObj.style.display = "none",
	menuObj.className = "fwinmask",
	menuObj.id = menuid,
	document.getElementById("append_parent").appendChild(menuObj);
	var hidedom = "";
	BROWSER.ie || (hidedom = '<style type="text/css">object{visibility:hidden;}</style>');
	var shadow = "",
	s = "";
	if (t ? (s = hidedom + shadow + '<div class="modal-header"><h4 class="modal-title text-truncate">', s += t, s += '</h4><button type="button" class="btn-close" data-dismiss="modal" aria-label="Close" onclick="hideMenu(\'fwin_dialog\', \'dialog\')" ></button></div>') : (s = hidedom + shadow + '<div class="modal-header"><h4 class="modal-title text-truncate">', s += __lang.board_message, s += '</h4><button id="fwin_dialog_close" type="button" class="btn-close" data-dismiss="modal" aria-label="Close" onclick="hideMenu(\'fwin_dialog\', \'dialog\')" ></button></div>'), -1 !== mode.indexOf("alert_icon_")) {
		var icon = decodeURIComponent(mode.replace("alert_icon_", ""));
		s += icon ? '<div class="modal-body"><div class="alert_icon"><img class="alert_icon_img" src="' + icon + '"><p>' + msg + "</p></div></div>": '<div class="modal-body"><div class="alert_info"><p>' + msg + "</p></div></div>",
		s += '<div class="modal-footer">' + (leftmsg ? '<span class=" muted pull-left">' + leftmsg + "</span>": "") + (showconfirm ? '<button id="fwin_dialog_submit" value="true" class="btn btn-primary"><strong>' + confirmtxt + "</strong></button>": ""),
		s += "</div>"
	} else "message" == mode ? (s += leftmsg ? '<div class="modal-body"><div class="alert_info"><p>' + msg + "</p></div></div>": '<div class="modal-body">' + msg + "</div>", s += '<div class="modal-footer">' + (leftmsg ? '<span class=" muted pull-left">' + leftmsg + "</span>": "") + (showconfirm ? '<button id="fwin_dialog_submit" value="true" class="btn btn-primary"><strong>' + confirmtxt + "</strong></button>": ""), s += "</div>") : (s += '<div class="modal-body"><div class="' + ("alert" == mode ? "alert_error": "right" == mode ? "alert_right": "info" == mode ? "alert_info": "") + '">' + msg + "</div></div>", s += '<div class="modal-footer">' + (leftmsg ? '<span class="muted pull-left">' + leftmsg + "</span>": "") + (showconfirm ? '<button id="fwin_dialog_submit" value="true" class="btn btn-primary">' + confirmtxt + "</button>": ""), s += "confirm" == mode ? '<button id="fwin_dialog_cancel" value="true" class="btn btn-secondary" onclick="hideMenu(\'fwin_dialog\', \'dialog\')">' + canceltxt + "</button>": "", s += "</div>");
	menuObj.innerHTML = s,
	document.getElementById("fwin_dialog_submit") && (document.getElementById("fwin_dialog_submit").onclick = function() {
		"function" == typeof func ? func() : eval(func),
		hideMenu(menuid, "dialog")
	}),
	document.getElementById("fwin_dialog_cancel") && (document.getElementById("fwin_dialog_cancel").onclick = function() {
		"function" == typeof funccancel ? funccancel() : eval(funccancel),
		hideMenu(menuid, "dialog")
	}),
	showMenu({
		mtype: "dialog",
		menuid: menuid,
		duration: 3,
		pos: "00",
		zindex: JSMENU.zIndex.dialog,
		drag: "drag_" + menuid,
		cache: 0,
		cover: cover
	})
}
function Alert(e, t, n, a, i) {
	i || (i = "alert"),
	showDialog(e, i, __lang.board_message, n, 1, n, "", a || __lang.i_see, "", t)
}
function Confirm(e, t) {
	showDialog(e, "confirm", __lang.confirm_message, t, 1)
}
function showWindow(k, url, mode, cache, showWindow_callback,disablebacktohide) {
	mode = isUndefined(mode) ? 'get' : mode;
	cache = isUndefined(cache) ? 1 : cache;
	var menuid = 'fwin_' + k;
	var menuObj = document.getElementById(menuid);
	var loadingst = null;
	// 不允许浮动窗口时直接跳转
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
			loadingst = setTimeout(function() {showDialog('正在为您加载中...', 'info', '<img src="' + IMGDIR + '/loading.gif"> '+__lang.please_wait)}, 500);
		}
		if(mode == 'html'){
			document.getElementById('fwin_content_' + k).innerHTML = url;
			initMenu();
			show();
		}
	};
	var initMenu = function() {
		clearTimeout(loadingst);
	};
	var show = function() {
		hideMenu('fwin_dialog', 'dialog');
		jQuery(menuObj).modal('show');
	};
	if(!menuObj) {
		var html='<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">'
				 +'	<div class="modal-content" id="fwin_content_'+k+'">'
				 +'	</div>'
				 +'</div>';
				 
		menuObj = document.createElement('div');
		menuObj.id = menuid;
		menuObj.className = '  modal fade ';
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
jcLoader = function() {
	var e = document;
	return {
		load: function(t, n) {
			var a = "",
			i = "",
			o = "",
			r = n;
			switch (t.type && (a = t.type), t.url && (i = t.url), t.ids && (o = t.ids), a) {
			case "js":
			case "javascript":
				!
				function(t, n, a) {
					for (var i = t.replace(/[,]\s*$/gi, "").split(","), o = n.replace(/[,]\s*$/gi, "").split(","), r = [], c = 0, s = 0; s < i.length; s++) o[s] && document.getElementById(o[s]) ? ++c >= i.length && "function" == typeof a && a() : (r[s] = e.createElement("script"), r[s].type = "text/javascript", r[s].src = i[s], o[s] && (r[s].id = o[s]), e.getElementsByTagName("head")[0].appendChild(r[s]), "function" == typeof a && (r[s].readyState ? r[s].onreadystatechange = function() {
						"loaded" != this.readyState && "complete" != this.readyState || (this.onreadystatechange = null, ++c >= i.length && "function" == typeof a && a())
					}: r[s].onload = function() {++c >= i.length && "function" == typeof a && a()
					}))
				} (i, o, r);
				break;
			case "css":
				!
				function(t, n, a) {
					for (var i = t.replace(/[,]\s*$/gi, "").split(","), o = n.replace(/[,]\s*$/gi, "").split(","), r = [], c = 0, s = 0; s < i.length; s++) o[s] && document.getElementById(o[s]) ? ++c >= i.length && "function" == typeof a && a() : (r[s] = e.createElement("link"), o[s] && (r[s].id = o[s]), r[s].rel = "stylesheet", r[s].href = i[s], e.getElementsByTagName("head")[0].appendChild(r[s]), "function" == typeof a && (r[s].readyState ? r[s].onreadystatechange = function() {
						"loaded" != this.readyState && "complete" != this.readyState || (this.onreadystatechange = null, ++c >= i.length && "function" == typeof a && a())
					}: r[s].onload = function() {++c >= i.length && "function" == typeof a && a()
					}))
				} (i, o, r)
			}
			return this
		}
	}
};
function parseURL(e) {
	var t = document.createElement("a");
	return t.href = e,
	{
		source: e,
		protocol: t.protocol.replace(":", ""),
		host: t.hostname,
		port: t.port,
		query: t.search,
		params: function() {
			for (var e, n = {},
			a = t.search.replace(/^\?/, "").split("&"), i = a.length, o = 0; o < i; o++) a[o] && (n[(e = a[o].split("="))[0]] = e[1]);
			return n
		} (),
		file: (t.pathname.match(/\/([^\/?#]+)$/i) || [, ""])[1],
		hash: t.hash.replace("#", ""),
		path: t.pathname.replace(/^([^\/])/, "/$1"),
		relative: (t.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ""])[1],
		segments: t.pathname.replace(/^\//, "").split("/")
	}
}
function getUrlParam(e, t) {
	var n = new RegExp("(^|&)" + t + "=([^&]*)(&|$)"),
	a = e.match(n);
	return null != a && unescape(a[2])
}
var onousermove = onmouseup = onselectstart = null,
DetachEvent = function(e, t) {
	try {
		document.onmousemove = onmousemove,
		document.onmouseup = onmouseup,
		document.onselectstart = onselectstart,
		t.releaseCapture && t.releaseCapture()
	} catch(e) {}
},
AttachEvent = function(e, t) {
	try {
		onmousemove = document.onmousemove,
		onmouseup = document.onmouseup,
		onselectstart = document.onselectstart,
		document.onselectstart = function() {
			return ! 1
		},
		e.preventDefault ? e.preventDefault() : t.setCapture && t.setCapture()
	} catch(e) {}
};
function dfire(e) {
	jQuery(document).trigger(e)
}
function correcturl(e) {
	return e && -1 === e.indexOf("?") && (e = e.replace(/&/i, "?")),
	e
}
function checkInDom(e, t) {
	return !! e && (e.id == t || "BODY" != e.tagName && checkInDom(e.parentNode, t))
}
function contains(e, t) {
	return e.contains ? e != t && e.contains(t) : !!(16 & e.compareDocumentPosition(t))
}
function serialize(e) {
	var t, n = function(e) {
		var t, n, a = typeof e;
		if ("object" === a && !e) return "null";
		if ("object" === a) {
			if (!e.constructor) return "object";
			var i = e.constructor.toString(); (t = i.match(/(\w+)\(/)) && (i = t[1].toLowerCase());
			var o = ["boolean", "number", "string", "array"];
			for (n in o) if (i == o[n]) {
				a = o[n];
				break
			}
		}
		return a
	},
	a = n(e);
	switch (a) {
	case "function":
		t = "";
		break;
	case "boolean":
		t = "b:" + (e ? "1": "0");
		break;
	case "number":
		t = (Math.round(e) == e ? "i": "d") + ":" + e;
		break;
	case "string":
		t = "s:" +
		function(e) {
			var t = 0,
			n = 0,
			a = e.length,
			i = "";
			for (n = 0; n < a; n++) t += (i = e.charCodeAt(n)) < 128 ? 1 : i < 2048 ? 2 : 3;
			return t
		} (e) + ':"' + e + '"';
		break;
	case "array":
	case "object":
		t = "a";
		var i, o, r = 0,
		c = "";
		for (o in e) if (e.hasOwnProperty(o)) {
			if ("function" === n(e[o])) continue;
			i = o.match(/^[0-9]+$/) ? parseInt(o, 10) : o,
			c += this.serialize(i) + this.serialize(e[o]),
			r++
		}
		t += ":" + r + ":{" + c + "}";
		break;
	case "undefined":
	default:
		t = "N"
	}
	return "object" !== a && "array" !== a && (t += ";"),
	t
}
function array_merge() {
	var e, t = Array.prototype.slice.call(arguments),
	n = t.length,
	a = {},
	i = "",
	o = 0,
	r = 0,
	c = 0,
	s = 0,
	d = Object.prototype.toString,
	l = !0;
	for (c = 0; c < n; c++) if ("[object Array]" !== d.call(t[c])) {
		l = !1;
		break
	}
	if (l) {
		for (l = [], c = 0; c < n; c++) l = l.concat(t[c]);
		return l
	}
	for (c = 0, s = 0; c < n; c++) if (e = t[c], "[object Array]" === d.call(e)) for (r = 0, o = e.length; r < o; r++) a[s++] = e[r];
	else for (i in e) e.hasOwnProperty(i) && (parseInt(i, 10) + "" === i ? a[s++] = e[i] : a[i] = e[i]);
	return a
}
function dzzNotification() {
	var e = new Object;
	return e.issupport = function() {
		return "Notification" in window
	},
	e.shownotification = function(e, t, n, a, i) {
		function o() {
			new Notification(a, {
				tag: e,
				icon: n,
				body: i
			}).onclick = function(e) {
				e.preventDefault(),
				window.open(t, "_blank")
			}
		}
		"granted" === Notification.permission ? o() : "denied" !== Notification.permission && Notification.requestPermission().then(function(e) {
			"granted" === e && o()
		})
	},
	e
}
Date.prototype.format = function(e) {
	var t = {
		"M+": this.getMonth() + 1,
		"d+": this.getDate(),
		"h+": this.getHours(),
		"m+": this.getMinutes(),
		"s+": this.getSeconds(),
		"q+": Math.floor((this.getMonth() + 3) / 3),
		"S+": this.getMilliseconds()
	};
	for (var n in /(y+)/i.test(e) && (e = e.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length))), t) new RegExp("(" + n + ")").test(e) && (e = e.replace(RegExp.$1, 1 == RegExp.$1.length ? t[n] : ("00" + t[n]).substr(("" + t[n]).length)));
	return e
};

function generateRandomCode(length) {
	var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	var result = '';
	for (var i = length; i > 0; --i) {
		result += chars[Math.floor(Math.random() * chars.length)];
	}
	return result;
}
var messageTimer = null;
function showmessage(msg, type, timeout, haveclose, position, callback, maxwidth, maxheight, delay) {
	if (!maxheight) maxheight = 300;
	if (!maxwidth) maxwidth = 350;
	if (!delay) delay = 300;
	if (!position) position = 'top-center';
	var existing = document.getElementById('message_tip_box');
	if (existing) {
		jQuery(existing).remove();
	}
	var el = jQuery('<div id="message_tip_box" class="notify"></div>').appendTo(document.body);
	el.css({
		'height': 'auto',
		'z-index': '99999999',
		'position': 'fixed',
		'max-height': maxheight,
		width: maxwidth,
		margin: '0,auto',
		'overflow': 'hidden'
	});

	//设置消息框的类型（不同类型背景颜色不同）；
	if (type == 'error') type = 'danger';
	var types = ['danger', 'info', 'success', 'warning'];
	if (jQuery.inArray(type, types) < 0) type = 'info';
	var spantype = '';
	if (type == 'info') {
		spantype = '<span class="me-2 lead mdi mdi-alert-circle-outline text-info"></span>';
	} else if (type == 'success') {
		spantype = '<span class="me-2 lead mdi mdi-check-circle-outline text-success"></span>';
	} else if (type == 'danger') {
		spantype = '<span class="me-2 lead mdi mdi-close-circle-outline text-danger"></span>';
	} else if (type == 'warning') {
		spantype = '<span class="me-2 lead mdi mdi-alert-octagram text-warning"></span>';
	}
	var messageContent = '<div class="d-flex p-2">' + spantype + '<span id="message_tip_alert" class="notify-body text-break">' + msg + '</span>';
	if (haveclose) {
		messageContent += '<a class="me-2 lead close dcolor" href="javascript:;"><i class="mdi mdi-close"></i></a>';
	}
	messageContent += '</div>';
	el.html(messageContent);
	//处理位置
	var width = el.outerWidth(true);
	var height = el.outerHeight(true);
	if (messageTimer) {
		window.clearTimeout(messageTimer);
	}
	switch (position) {
	case 'left-top':
		el.css({
			right:
			'auto',
			top: 0,
			left: -width,
			bottom: 'auto'
		}).animate({
			left: 0
		},
		delay);
		break;
	case 'right-top':
		el.css({
			right:
			-width,
			top: 0,
			left: 'auto',
			bottom: 'auto'
		}).animate({
			right: 0
		},
		delay);
		break;
	case 'right-bottom':
		el.css({
			right:
			-width,
			top: 'auto',
			left: 'auto',
			bottom: 0
		}).animate({
			right: 0
		},
		delay);
		break;
	case 'left-bottom':
		el.css({
			right:
			'auto',
			top: 'auto',
			left: -width,
			bottom: 0
		}).animate({
			left: 0
		},
		delay);
		break;
	case 'center':
		var w2 = width / 2;
		var h2 = height / 2;
        el.css({
			left: '50%',
			top: '50%',
			right: 'auto',
			bottom: 'auto',
			'margin-left': -w2,
			'margin-top': -h2,
			'opacity': 0
		}).animate({
			'opacity': 1
		},
		delay);
		break;
	default:
		el.css({
			left:
			'50%',
			'marginLeft': -width / 2,
			top: -height,
			right: 'auto'
		}).animate({
			'top': 0
		},
		delay);;
		break;
	}
	if (timeout) {
		window.messageTimer = window.setTimeout(function() {
			closeMessage(position, delay, callback);
		},
		timeout);
	}
	// 绑定关闭事件
	el.find('a.close').on('click',
	function() {
		closeMessage(position, delay, callback);
	});
	if (!timeout && typeof(callback) == 'function') { //没有设置时间时立即触发
		callback();
	}
	// 统一的关闭函数
	function closeMessage(pos, animDelay, cb) {
		switch (pos) {
		case 'left-top':
			el.animate({
				left:
				-width
			},
			animDelay, removeElement);
			break;
		case 'right-top':
			el.animate({
				right:
				-width
			},
			animDelay, removeElement);
			break;
		case 'right-bottom':
			el.animate({
				right:
				-width
			},
			animDelay, removeElement);
			break;
		case 'left-bottom':
			el.animate({
				left:
				-width
			},
			animDelay, removeElement);
			break;
		case 'center':
			el.animate({
				opacity:
				0
			},
			animDelay, removeElement);
			break;
		default:
			el.animate({
				top:
				-height
			},
			animDelay, removeElement);
		}

		function removeElement() {
			el.remove();
			if (typeof cb === 'function') cb();
		}
	}
}