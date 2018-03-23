(function($) {
	"use strict";
	
	// jQuery plugin definition
	$.fn.TextAreaExpander = function(minHeight, maxHeight) {
		var resize=function(target,minHeight,maxHeight){
			// 保存初始高度，之后需要重新设置一下初始高度，避免只能增高不能减低。
			var dh = $(target).attr('defaultHeight') || minHeight;
			if(minHeight && dh<minHeight){
				dh=minHeight;
			}
			if (!dh) {
				dh = target.clientHeight || minHeight;
				if(minHeight && dh>minHeight){
					dh=minHeight;
				}
				$(target).attr('defaultHeight', dh);
			}
			target.style.height = dh +'px';
			var clientHeight = target.clientHeight || minHeight;
			var scrollHeight = target.scrollHeight || maxHeight;
			if(scrollHeight>maxHeight){
				scrollHeight=maxHeight;
			}else if($(target).is(':hidden')){
				scrollHeight=minHeight;
			}
			if (clientHeight !== scrollHeight) { 
				target.style.height = scrollHeight + 2 + "px";
			}
		};
		this.off(".TextAreaExpander").on("input.TextAreaExpander propertychange.TextAreaExpander", function () {
		   resize(this,minHeight,maxHeight);
		}).on('focus',function(){
			resize(this,minHeight,maxHeight);
		});
		this.each(function(){
		  	resize(this,minHeight,maxHeight);
		});
		
		return this;
	};
})(jQuery);