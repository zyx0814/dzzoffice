// 1 Bit Audio Player v1.4
// See http://1bit.markwheeler.net for documentation and updates

function OneBit(pluginPath) {
		// Object Vars
	
	// Relative to calling path
	this.pluginPath = pluginPath || '1bit.swf';	
	
	// Style Options
	this.color = false;
	this.background = '#FFFFFF';
	this.playerSize = false;
	this.position = 'after';
	this.analytics = false;
	
	// Semi Internal
	this.wrapperClass = 'onebit_mp3';
	
	// Internal
	this.playerCount = 1;
	this.flashVersion = 9;

		// Methods
	
	// Specify optional settings
	this.specify = function(key, value) {
		if(key == "color") {
			this.color = value;
		}
		if(key == "background") {
			this.background = value;
		}
		if(key == "playerSize") {
			this.playerSize = value;
		}
		if(key == "position") {
			this.position = value;
		}
		if(key == "analytics") {
			this.analytics = value;
		}
	};
	
	// Run through each applicable link and add a player
	this.apply = function(selector) {
		var links = this.getElementsBySelector(selector);
		for(var i = 0; i < links.length; i++) {
			
			// Avoid applying the player twice to the same link
			if (this.hasClass(links[i].parentNode, this.wrapperClass)) {
				continue;
			}
			
			// Avoid non .mp3 links
			if (links[i].href.substr(links[i].href.length - 4) != '.mp3') {
				continue;
			}
			
			this.insertPlayer(links[i]);
		}	
	};
	
	this.insertPlayer = function(elem) {
		if (!this.playerSize) {
		    // Set the playerSize from the elements height
			this.autoPlayerSize = Math.floor(elem.scrollHeight * 0.65);
		}

		if(!this.color) {
			// Set the foreColor from the element's style
			this.autoColor = this.getStyle(elem, 'color');
			// Put in extra 0's if it's a 3 digit hex (which Flash doesn't understand)
			if(this.autoColor.substr(0, 1) == '#' && this.autoColor.length == 4) {
			    this.autoColor = this.autoColor.substr(0, 2) + '0' + this.autoColor.substr(2, 1) + '0' + this.autoColor.substr(3, 1) + '0';
			}
			// Convert to hex if required
			if(this.autoColor.substr(0, 1) != '#') {
			    this.autoColor = this.autoColor.substr(4, this.autoColor.indexOf(')') - 4);
				var rgbSplit = new Array();
				rgbSplit = this.autoColor.split(', ');
				this.autoColor = '#'+this.convertColor(Number(rgbSplit[2]),Number(rgbSplit[1]),Number(rgbSplit[0]));
			}
		}

		// Make a span to encapsulate the link and flash
		var playerWrapper = document.createElement('span');
		this.addClass(playerWrapper, this.wrapperClass);
				
		// Add an empty span to be replaced by the flash by it's ID
		var hook_id = 'oneBitInsert_' + this.playerCount;
		var span = document.createElement('span');
		span.setAttribute('id', hook_id);

		// Move everything where it needs to be
		elem.parentNode.insertBefore(playerWrapper, elem);
		if(this.position == 'before') {
	 		playerWrapper.appendChild(span);
	 		playerWrapper.innerHTML += '&nbsp;';
	 		playerWrapper.appendChild(elem);
		} else {
	 		playerWrapper.appendChild(elem);
	 		playerWrapper.innerHTML += '&nbsp;';
	 		playerWrapper.appendChild(span);
		}

	    // Insert the flash
		if(!this.playerSize) {
		    this.insertPlayerSize = this.autoPlayerSize;
		} else {
		    this.insertPlayerSize = this.playerSize;
		}

		var so = new SWFObject(
			this.pluginPath,
			hook_id,
			this.insertPlayerSize,
			this.insertPlayerSize,
			this.flashVersion,
			this.background
		);

		if(this.background == 'transparent') {
			so.addParam('wmode', 'transparent');
		}
		if(!this.color) {
			so.addVariable('foreColor', this.autoColor);
		} else {
            so.addVariable('foreColor', this.color);
		}
		so.addVariable('analytics', this.analytics);
		so.addVariable('filename', elem.href);

		so.write(hook_id);		
		this.playerCount++;
	};

	// Get CSS styles - based on http://www.quirksmode.org/dom/getstyles.html
	this.getStyle = function(el, styleProp) {
		if (el.currentStyle) {
			var value = el.currentStyle[styleProp];
		} else {
			var value = document.defaultView.getComputedStyle(el, null).getPropertyValue(styleProp);
  		}
		return value;
	};
	
	// Convert RGB color into Hex
	this.convertColor = function(red, green, blue) {
	    var decColor = red + 256 * green + 65536 * blue;
	    return decColor.toString(16);
	};
	
	// Get DOM elements based on the given CSS Selector - V 1.00.A Beta
	// http://www.openjs.com/scripts/dom/css_selector/
	this.getElementsBySelector = function (all_selectors) {
		var selected = new Array();
		if(!document.getElementsByTagName) return selected;
		all_selectors = all_selectors.replace(/\s*([^\w])\s*/g,"$1");//Remove the 'beutification' spaces
		var selectors = all_selectors.split(",");
		// Grab all of the tagName elements within current context	
		var getElements = function(context,tag) {
			if (!tag) tag = '*';
			// Get elements matching tag, filter them for class selector
			var found = new Array;
			for (var a=0,len=context.length; con=context[a],a<len; a++) {
				var eles;
				if (tag == '*') eles = con.all ? con.all : con.getElementsByTagName("*");
				else eles = con.getElementsByTagName(tag);

				for(var b=0,leng=eles.length;b<leng; b++) found.push(eles[b]);
			}
			return found;
		};

		COMMA:
		for(var i=0,len1=selectors.length; selector=selectors[i],i<len1; i++) {
			var context = new Array(document);
			var inheriters = selector.split(" ");

			SPACE:
			for(var j=0,len2=inheriters.length; element=inheriters[j],j<len2;j++) {
				//This part is to make sure that it is not part of a CSS3 Selector
				var left_bracket = element.indexOf("[");
				var right_bracket = element.indexOf("]");
				var pos = element.indexOf("#");//ID
				if(pos+1 && !(pos>left_bracket&&pos<right_bracket)) {
					var parts = element.split("#");
					var tag = parts[0];
					var id = parts[1];
					var ele = document.getElementById(id);
					if(!ele || (tag && ele.nodeName.toLowerCase() != tag)) { //Specified element not found
						continue COMMA;
					}
					context = new Array(ele);
					continue SPACE;
				}

				pos = element.indexOf(".");//Class
				if(pos+1 && !(pos>left_bracket&&pos<right_bracket)) {
					var parts = element.split('.');
					var tag = parts[0];
					var class_name = parts[1];

					var found = getElements(context,tag);
					context = new Array;
	 				for (var l=0,len=found.length; fnd=found[l],l<len; l++) {
	 					if(fnd.className && fnd.className.match(new RegExp('(^|\s)'+class_name+'(\s|$)'))) context.push(fnd);
	 				}
					continue SPACE;
				}

				if(element.indexOf('[')+1) {//If the char '[' appears, that means it needs CSS 3 parsing
					// Code to deal with attribute selectors
					if (element.match(/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?['"]?([^\]'"]*)['"]?\]$/)) {
						var tag = RegExp.$1;
						var attr = RegExp.$2;
						var operator = RegExp.$3;
						var value = RegExp.$4;
					}
					var found = getElements(context,tag);
					context = new Array;
					for (var l=0,len=found.length; fnd=found[l],l<len; l++) {
	 					if(operator=='=' && fnd.getAttribute(attr) != value) continue;
						if(operator=='~' && !fnd.getAttribute(attr).match(new RegExp('(^|\\s)'+value+'(\\s|$)'))) continue;
						if(operator=='|' && !fnd.getAttribute(attr).match(new RegExp('^'+value+'-?'))) continue;
						if(operator=='^' && fnd.getAttribute(attr).indexOf(value)!=0) continue;
						if(operator=='$' && fnd.getAttribute(attr).lastIndexOf(value)!=(fnd.getAttribute(attr).length-value.length)) continue;
						if(operator=='*' && !(fnd.getAttribute(attr).indexOf(value)+1)) continue;
						else if(!fnd.getAttribute(attr)) continue;
						context.push(fnd);
	 				}

					continue SPACE;
				}

				//Tag selectors - no class or id specified.
				var found = getElements(context,element);
				context = found;
			}
			for (var o=0,len=context.length;o<len; o++) selected.push(context[o]);
		}
		return selected;
	};

	
		// Support Methods
	
	this.hasClass = function(elem, cls) {
		return elem.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)'));
	};
	
	this.addClass = function(elem, cls) {
		if (!this.hasClass(elem, cls)) elem.className += " " + cls;
	};
	
	this.removeClass = function(elem, cls) {
		if (hasClass(elem ,cls)) {
			var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
			elem.className=ele.className.replace(reg ,' ')
		}
	};
	
		// Events
		
	this.ready = function(func) {
	  var oldonload = window.onload;
	  if (typeof window.onload != 'function') {
	    window.onload = func;
	  } else {
	    window.onload = function() {
	      if (oldonload) {
	        oldonload();
	      }
	      func();
	    }
	  }
	}
};