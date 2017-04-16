(function($) {
	$.fn.ResizeTextarea=function () {

		var target = this.get(0);
	   // 保存初始高度，之后需要重新设置一下初始高度，避免只能增高不能减低。
		var dh = $(target).attr('defaultHeight') || 30;
		if (!dh) {
			dh = target.clientHeight;
			$(target).attr('defaultHeight', dh);
		}

		target.style.height = dh +'px';
		var clientHeight = target.clientHeight;
		var scrollHeight = target.scrollHeight;
		if (clientHeight !== scrollHeight) { target.style.height = scrollHeight + 10 + "px";
		}
			return true;
	};
	// jQuery plugin definition
	$.fn.TextAreaExpander = function(minHeight, maxHeight) {
		this.on('focus',function(){
			$(this).trigger('input');
		});
		this.off("input propertychange").on("input propertychange", function (e) {
			
		   var target = e.target;
		   // 保存初始高度，之后需要重新设置一下初始高度，避免只能增高不能减低。
			var dh = $(target).attr('defaultHeight') || minHeight;
			if(minHeight && dh<minHeight) dh=minHeight;
			if (!dh) {
				dh = target.clientHeight;
				if(minHeight && dh>minHeight) dh=minHeight;
				$(target).attr('defaultHeight', dh);
			}
			
			target.style.height = dh +'px';
			var clientHeight = target.clientHeight;
			var scrollHeight = target.scrollHeight || maxHeight;
			if(scrollHeight>maxHeight) scrollHeight=maxHeight;
			
			if (clientHeight !== scrollHeight) { target.style.height = scrollHeight + 10 + "px";}
		});
		return this;
	};

	

})(jQuery);