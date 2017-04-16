

function formatSize(bytes){
	var i = -1; 
	do {
		bytes = bytes / 1024;
		i++;  
	} while (bytes > 99);
   
	return Math.max(bytes, 0).toFixed(1) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];          
};

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

function addFavorite(url, title) {
	try {
		window.external.addFavorite(url, title);
	} catch (e){
		try {
			window.sidebar.addPanel(title, url, '');
        	} catch (e) {
			alert(__lang.js_prompt1_ctrlD);
		}
	}
}

function setHomepage(sURL) {
	if(BROWSER.ie){
		document.body.style.behavior = 'url(#default#homepage)';
		document.body.setHomePage(sURL);
	} else {
		alert(__lang.js_prompt1_sethome, 'notice');
		
	}
};
function copyToClipboard(copyText) {  
       if (window.clipboardData){
            window.clipboardData.setData("Text", copyText)
        }else{
            var flashcopier = 'flashcopier';
            if(!document.getElementById(flashcopier)) 
            {
              var divholder = document.createElement('div');
              divholder.id = flashcopier;
              document.body.appendChild(divholder);
            }
            document.getElementById(flashcopier).innerHTML = '';
            var divinfo = '<embed width="0" height="0" allownetworking="internal" allowscriptaccess="never" src="./dzz/scripts/clipboard/_clipboard.swf" flashvars="clipboard='+encodeURIComponent(copyText)+'" quality="high" wmode="transparent" allowfullscreen="false" type="application/x-shockwave-flash">'
            document.getElementById(flashcopier).innerHTML = divinfo;
        }
      alert(copyText+'====='+__lang.success_copied_clipboard);
} ;

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
				H='上午&nbsp;'+H;
			}else if(H>12 && H<24){
				H-=12;
				H='下午&nbsp;'+fillZero(H);
			}else if(H==24){
				H='下午&nbsp;00';
			}
		}
		ev.innerHTML=Y+__lang.year+M+__lang.month+D+__lang.day + ' '+W+'&nbsp;'+H+':'+I+':'+S;
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
  // * example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
  // * returns 1: '<p>this -> &quot;</p>'
  // * example 2: htmlspecialchars_decode("&amp;quot;");
  // * returns 2: '&quot;'
  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
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
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  return string;
};
