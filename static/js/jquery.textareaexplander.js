(function($) {
 
	// jQuery plugin definition
	$.fn.TextAreaExpander = function(minHeight, maxHeight) {
 
		var hCheck = !(BROWSER.ie || BROWSER.opera);
 
		// resize a textarea
		function ResizeTextarea(e) {
 
			// event or initialize element?S
			e = e.target || e;
 
			// find content length and box width
			var vlen = e.value.length, ewidth = e.offsetWidth;
			if (vlen != e.valLength || ewidth != e.boxWidth) {
 
				//if (hCheck && (vlen < e.valLength || ewidth != e.boxWidth)) e.style.height = ewidth+"px";
				var h = Math.max(e.expandMin, Math.min(e.scrollHeight, e.expandMax));
 				
				e.style.overflow = (e.scrollHeight > h ? "auto" : "hidden");
				e.style.height = h + "px";
				e.valLength = vlen;
				e.boxWidth = ewidth;
			}
 
			return true;
		};
 
		// initialize
		this.each(function() {
 
			// is a textarea?
			if (this.nodeName.toLowerCase() != "textarea") return;
			// set height restrictions
			var p = this.className.match(/expand(\d+)\-*(\d+)*/i);
			this.expandMin = minHeight || (p ? parseInt('0'+p[1], 10) : 0);
			this.expandMax = maxHeight || (p ? parseInt('0'+p[2], 10) : 99999);
 
			// initial resize
			ResizeTextarea(this);
 
			// zero vertical padding and add events
			if (!this.Initialized) {
				this.Initialized = true;
				//$(this).css("padding-top", 0).css("padding-bottom", 0);
				$(this).bind("keyup", ResizeTextarea);
			}
		});
 
		return this;
	};
 
})(jQuery);