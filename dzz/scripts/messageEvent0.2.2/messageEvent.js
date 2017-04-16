/*!
 * messageEvent
 * Date: 2012-01-18 20:58
 * http://code.google.com/p/message-event/
 * (c) 2011 - 2012 TangBin, http://www.planeArt.cn
 *
 * This is licensed under the GNU LGPL, version 2.1 or later.
 * For details, see: http://creativecommons.org/licenses/LGPL/2.1/
 */
 
 
var messageEvent = {

	version: '0.2.2',
	
	/**
	 * 跨域向窗口中发送数据
	 * @param	{DOMWindow}	目标窗口
	 * @param	{String}	消息
	 * @param	{String}	(可选) 限定URL接受消息, 默认 '*'
	 */
	postMessage: function (otherWindow, message, targetOrigin) {
	
		var that = this;
		var proxy = arguments[3];
		
		targetOrigin = targetOrigin || '*';
		
		
		// 修复 IE8、IE9 原生 postMessage 方法以及
		// IE6、7 window.name 方式跨域不能传递 Object 类型消息的问题，
		// 这里统一把 Object 转化成 String 用来传递
		message = this._toString(message);
		
		
		if (typeof message !== 'string') {
			throw new Error('messageEvent.js: Error message type!');
		};
		
		
		// IE8+ 浏览器与其他现代浏览器均原生支持 postMessage 方法
		if (otherWindow.postMessage) {
			// 对 messageEvent.js 触发的 message 事件进行特定的标记，
			// 让监听程序不会因为第三方程序干扰
			message = '_MESSAGEEVENT_' + message;
			
			otherWindow.postMessage(message, targetOrigin);
			return;
		};
		
		
		// 以下代码针对 IE6、7 ----------
		
		
		// 找出 iframe 元素
		var targetIframe = this._getIframe(otherWindow);
		
		
		// 若目标 iframe 的 id 或者 name 属性为空则无法正确获取框架名
		if (targetIframe) {
			targetIframe.id = targetIframe.id || '_UUID_' + this._uuid ++;
		};
		
		
		// 找出目标窗口在 window.frames 里的成员名
		var targetName = this._getFramesName(otherWindow);
		
		
		if (!targetName || /\(|\)/.test(targetName)) {
			throw new Error('messageEvent.js: Iframe "name" property error!');
		};
		
		
		var event = {
			timeStamp: + new Date,
			windowName: window.name, /** @inner */
			targetOrigin: targetOrigin /** @inner */
		};
		
		
		try {
		
			// 同域无需代理
			var ONMESSAGE = window.frames[targetName]['_MESSAGEEVENT_'];
			
			if (ONMESSAGE) {
				event.data = message;
				event.source = window;
				event.origin = this._getHome(location.href);
				ONMESSAGE(event);
				return;
			};
			
		} catch (e) {};
		
		
		// 获取远程跨域代理页面路径
		proxy = proxy || this._getProxy(targetName, targetIframe);
		
		if (!proxy) {
			throw new Error('messageEvent.js: The wrong proxy address!');
		};
		
		
		// 创建跨域 iframe 代理
		var iframeProxy = document.createElement('iframe');
		
		iframeProxy.name = message;
		iframeProxy.style.display = 'none';
		
		// iframe 使用完毕及时清理，释放内存占用
		var iframeLoad = function () {
			that._removeEvent(iframeProxy, 'load', iframeLoad);
			iframeProxy.src = 'about:blank';
			iframeProxy.parentNode.removeChild(iframeProxy);
		};
		
		this._addEvent(iframeProxy, 'load', iframeLoad);
		
		
		var hash = [
			'#version=' + this.version, /** @inner */
			'&targetName=' + targetName /** @inner */
		];
			
			
		for (var i in event) {
			if (event.hasOwnProperty(i)) {
				hash.push('&', i, '=', event[i]);
			};
		};
		
		
		iframeProxy.src = proxy + hash.join('');
		document.getElementsByTagName('head')[0].appendChild(iframeProxy);
		
		
		// 以下代码必须在 iframe appendChild 之后
		// 让IE6,7 动态创建的 iframe 内部可读取 window.name 属性
		iframeProxy.contentWindow.name = iframeProxy.name;
	},
	
	
	/**
	 * 添加消息事件
	 * @param	{Function}	回调函数
	 */
	add: function (callback) {
	
		this._listeners.push(callback);
	},
	
	
	/**
	 * 卸载消息事件
	 * @param	{Function}	(可选)待卸载的函数，为空则卸载全部
	 */
	remove: function (callback) {
	
		var listeners = this._listeners;
		
		if (callback) {
		
			for (i = 0; i < listeners.length; i ++) {
			
				if (callback === listeners[i]) {
					listeners.splice(i--, 1);
				};
				
			};
			
		} else {
			this._listeners = [];
		};
	},
	
	
	// 主动触发事件
	_dispatch: function (event) {
	
		var listeners = this._listeners,
			callback;
			
		for (var i = 0; callback = listeners[i++]; ) {
		
			callback.call(window, event);
		};
	},
	
	
	// 其他事件添加
	_addEvent: function (elem, type, callback) {
	
		var el = 'addEventListener',
			dom = elem[el],
			add = dom ? el : 'attachEvent',
			type = dom ? type : 'on' + type;
		
		elem[add](type, callback, false);
		
	},
	
	
	// 其他事件卸载
	_removeEvent: function (elem, type, callback) {
	
		var el = 'removeEventListener',
			dom = elem[el],
			remove = dom ? el : 'detachEvent',
			type = dom ? type : 'on' + type;
		
		elem[remove](type, callback, false);
		
	},
	
	
	// 获取 window 在 frames 里的成员名
	_getFramesName: function (win) {
		
		var name, frames = window.frames;
		
		// 因为目标 window 一般会跨域，所以需要 try
		try {
			for (var i in frames) {
				if (frames[i] == win) {
					name = i;
				};
			};
			
		} catch (e) {};

		return name;
		
	},
	
	
	// 获取 contentWindow 所属的 iframe 元素
	_getIframe: function (contentWindow) {
	
		var iframes = document.getElementsByTagName('iframe'),
			ileng = iframes.length;
			
		for (var i = 0; i < ileng; i ++) {
		
			if (iframes[i].contentWindow == contentWindow) {
				return iframes[i];
			};
			
		};
		
	},
	
	
	// 获取 messageEvent-proxy.html 路径
	_getProxy: function (framesName, iframe) {
	
		var proxy, file = '/messageEvent-proxy.html';
		
		if (this._cache[framesName]) {
		
			proxy = this._cache[framesName];
			
		} else
		if (iframe) {
			
			proxy = this._getHome(iframe.src);
			this._cache[framesName] = proxy;
		};
		
		return proxy && proxy + file;
		
	},
	
	
	// 把对象转换成 JSON 字符串
	_toString: function (object) {
	
		if (typeof object !== 'string') {
		
			return window.JSON && JSON.stringify
			? '_ISOBJECT_' + JSON.stringify(object)
			: null;
			
		};
		
		return object;
		
	},
	
	
	// 尝试恢复 Object 类型消息
	_toObject: function (string) {
	
		var source = string.split('_ISOBJECT_')[1];
		
		if (source) {
			try {
				string = JSON.parse(source);
			} catch (e) {};
		};
		
		return string;
		
	},
	
	
	// 消息事件的所有回调函数
	_listeners: [],
	
	_getHome: function (url) {
		return url.match(/[A-Za-z]+:\/{0,3}([^\/]+)/)[0];
	},
	
	_cache: {},
	
	_uuid: 0,
	
	
	_init: function () {
	
		var that = this;
		
		if (window.postMessage) {

			that._addEvent(window, 'message', function (event) {
			
				var data = event.data,
					src = event;
					
				if (typeof data !== 'string') {
					return;
				};
				
				data = data.split('_MESSAGEEVENT_')[1];
					
				if (!data) {
					return;
				};
				
				event = {};
				
				for (var i in src) {
					event[i] = src[i];
				};
				
				event.data = that._toObject(data);
				that._dispatch(event);
			});
			
		} else {

			window['_MESSAGEEVENT_'] = function (event) {
			
				var origin = event.origin,
					windowName = event.windowName,
					targetOrigin = event.targetOrigin;
				
				
				if (windowName) {
					that._cache[windowName] = origin;
					
					if (windowName === 'parent') {
						window.name = '_PARENTHOST_' + origin;
					};
					
					if (event.data === '_NULL_') {
						return;
					};
				};
				
				
				if (targetOrigin === '*' || targetOrigin.indexOf(document.URL) === 0) {
					event.type = 'message';
					event.target = window;
					event.data = that._toObject(event.data);
					that._dispatch(event);
				};
				
			};
			
			
			that._cache.parent = function () {
					
				var home, namespaces = '_PARENTHOST_';
				
				if (name.indexOf(namespaces) === 0) {
				
					home = name.split(namespaces)[1];
				} else {
				
					var referrer = document.referrer;
					home = referrer && that._getHome(referrer);
				};
				
				if (home) {
					window.name = namespaces + home;
				};
				
				return home;
				
			}();
			
			
			// 页面初始化的时候尝试向父窗口传递一个”空“消息，
			// 这个消息包含了当前页面主机信息，
			// 这样 iframe 内容页面哪怕跳转到其他域名，
			// 仍然能让父窗口保持对当前页面的联络
			if (window.parent != window) {
				that.postMessage(parent, '_NULL_');
			};
			
		};
	}
	
};
messageEvent._init();



/*
	给 messageEvent 提供 Object 类型消息的支持
	http://www.JSON.org/json2.js
	@see http://www.JSON.org/js.html
*/
var JSON;JSON||(JSON={}),function(){function f(a){return a<10?"0"+a:a}function quote(a){return escapable.lastIndex=0,escapable.test(a)?'"'+a.replace(escapable,function(a){var b=meta[a];return typeof b=="string"?b:"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+a+'"'}function str(a,b){var c,d,e,f,g=gap,h,i=b[a];i&&typeof i=="object"&&typeof i.toJSON=="function"&&(i=i.toJSON(a)),typeof rep=="function"&&(i=rep.call(b,a,i));switch(typeof i){case"string":return quote(i);case"number":return isFinite(i)?String(i):"null";case"boolean":case"null":return String(i);case"object":if(!i)return"null";gap+=indent,h=[];if(Object.prototype.toString.apply(i)==="[object Array]"){f=i.length;for(c=0;c<f;c+=1)h[c]=str(c,i)||"null";return e=h.length===0?"[]":gap?"[\n"+gap+h.join(",\n"+gap)+"\n"+g+"]":"["+h.join(",")+"]",gap=g,e}if(rep&&typeof rep=="object"){f=rep.length;for(c=0;c<f;c+=1)typeof rep[c]=="string"&&(d=rep[c],e=str(d,i),e&&h.push(quote(d)+(gap?": ":":")+e))}else for(d in i)Object.prototype.hasOwnProperty.call(i,d)&&(e=str(d,i),e&&h.push(quote(d)+(gap?": ":":")+e));return e=h.length===0?"{}":gap?"{\n"+gap+h.join(",\n"+gap)+"\n"+g+"}":"{"+h.join(",")+"}",gap=g,e}}"use strict",typeof Date.prototype.toJSON!="function"&&(Date.prototype.toJSON=function(a){return isFinite(this.valueOf())?this.getUTCFullYear()+"-"+f(this.getUTCMonth()+1)+"-"+f(this.getUTCDate())+"T"+f(this.getUTCHours())+":"+f(this.getUTCMinutes())+":"+f(this.getUTCSeconds())+"Z":null},String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(a){return this.valueOf()});var cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,gap,indent,meta={"\b":"\\b","\t":"\\t","\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"},rep;typeof JSON.stringify!="function"&&(JSON.stringify=function(a,b,c){var d;gap="",indent="";if(typeof c=="number")for(d=0;d<c;d+=1)indent+=" ";else typeof c=="string"&&(indent=c);rep=b;if(!b||typeof b=="function"||typeof b=="object"&&typeof b.length=="number")return str("",{"":a});throw new Error("JSON.stringify")}),typeof JSON.parse!="function"&&(JSON.parse=function(text,reviver){function walk(a,b){var c,d,e=a[b];if(e&&typeof e=="object")for(c in e)Object.prototype.hasOwnProperty.call(e,c)&&(d=walk(e,c),d!==undefined?e[c]=d:delete e[c]);return reviver.call(a,b,e)}var j;text=String(text),cx.lastIndex=0,cx.test(text)&&(text=text.replace(cx,function(a){return"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)}));if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,"")))return j=eval("("+text+")"),typeof reviver=="function"?walk({"":j},""):j;throw new SyntaxError("JSON.parse")})}();



/*
	给 jQuery 库提供跨浏览器绑定 message 事件的能力
	jQuery(window).bind('message', function (event) {
		alert(event.data)
	})
	以及数据发送方法
	jQuery.postMessage(otherWindow, 'hello world', '*')
*/
(function ($, messageEvent) {

	if (!$ || !messageEvent) {
		return;
	};
	
	/**
	 * 跨域向窗口中发送数据
	 * @param	{DOMWindow}	窗口
	 * @param	{String}	消息
	 * @param	{String}	(可选) 限定URL接受消息, 默认 '*'
	 */
	$.postMessage = function (otherWindow, message, targetOrigin) {

		messageEvent.postMessage.apply(messageEvent, arguments);
	};

	
	/**
	 * 添加消息事件处理函数
	 * @param	{Function}	函数
	 * @return	{Object jQuery}
	 */
	$.fn.message = function (callback) {
	
		return this.bind('message', callback);
	};
	
	
	/*
		让 jQuery.event 增加 bind 方法绑定 message 事件：
		jQuery(window).bind('message', function (event) {
			alert(event.data)
		})
	 */
	 
	var poll;
	$.event.special.message = {
	
		setup: function () {
		
			var that = this;
			
			if (this != window) {
				return false;
			};
			
			poll = function (e) {
				var event = new $.Event('message', e);
				event.data = e.data;

				setEventData(that, e.data);
				
				$.event.trigger(event, null, that);
			};
			
			
			messageEvent.add(poll);
		},
		
		teardown: function () {
		
			if (this != window) {
				return false;
			};
			
			messageEvent.remove(poll);
		}
		
	};
	
	
	/*
		由于 jQuery 把 event.data 用来保存 bind 方法传入的附加数据，
		而未考虑到会与 html5 的 message 事件的 event.data 属性冲突，
		jQuery 这个不合理的设计导致需要操作其底层缓存才能解决问题
	*/
	var setEventData = function (elem, data) {
	
		var eventsCache = $.data(elem, 'events');
		var messageCache = eventsCache && eventsCache.message;
		
		if (messageCache) {
		
			$.each(messageCache, function (name, val) {
				val.data = data;
			});
		};
		
	};
	
})(this.jQuery, this.messageEvent);
