/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
(function($){
  $.fn.dragsort = function(options,recall) {
	var opt={
		'scrollContainer':$('.bs-main-container'), //滚动层
		'contentContainer':$('.main-content'),
		'hoder_div_css':'position:relative;background:#f7f7f7;border:1px solid #e1e1e1',
		'width_correct':0,
		'height_correct':0,
	}
	options=$.extend(opt,options);
	//console.log(options.scrollContainer);
	var container = this;
	var dx,dy,_this,wid,mousedownTimer,scrollHeight,scrollTop=0,scrollLeft=0,clientHeight;
	var top,left,oldx,oldy,w,h,w2,h2,_this;
	var bs=options.scrollContainer.offset();
	var p0,p;
	var clientWidth =Math.max(document.documentElement.clientWidth, document.body.offsetWidth);
	if(jQuery('#_blank').length){
		var _blank=jQuery('#_blank');
	}else{
		var _blank=jQuery('<div id="_blank" class="nokpdrager"  unselectable="on" onselectstart="return event.srcElement.type== \'text\';" style="display:none; url(dzz/images/b.gif); z-index:10000;width:100%;height:100%;margin:0;padding:0; right: 0px; bottom: 0px;position: absolute; top:0px; left: 0px;"></div>').appendTo(options.contentContainer);
	}
  
	//$(container).children().addTouch();
	$(container).children().off("mousedown.subdrager").on('mousedown.subdrager',function(e) {
		if($(this).hasClass('nodrager') || e.which != 1 || $(e.target).is("input, textarea") || $(e.target).closest('.nodrager').length || window.kp_only) return; // 排除非左击和表单元素
		jQuery('input').trigger('blur');
		var self=this;
		
		try{	if(e.preventDefault) e.preventDefault();
				else{
					e.returnvalue=false
				}
					}catch(e){};
		_this = $(this);
		
		 oldx=e.clientX;
		 oldy=e.clientY;
		p0=_this.offset();
		p = _this.position();
		left = p.left;
		top = p.top-options.scrollContainer.scrollTop();
		dx=e.clientX-p0.left;
		dy=e.clientY-(p0.top);
		
         // 绑定mousemove事件
		$(document).on('mousemove.subdrager',function(e) {
			e=e?e:window.event;
			try{
				if(e.preventDefault) e.preventDefault();
				else{
					e.returnValue=false;
				}
			}catch(e){};
			
			var xx=e.clientX;
			var yy=e.clientY;
			if(!window.kp_only && (oldx!=xx || oldy!=yy)){//不再原位置，表示拖动开始;
				PreMove();
			}
			if(!window.kp_only) return;
			
				
				if(yy-dy<=bs.top){
					scrollTop=scrollTop+((yy-dy-bs.top)>-50?-50:(yy-dy-bs.top));
					if(scrollTop<0) scrollTop=0;
					options.scrollContainer.scrollTop(scrollTop);
					
				}else if((yy+(_this.height()-dy))>=clientHeight+bs.top ){
					scrollTop=scrollTop+((yy+(_this.height()-dy)-clientHeight-bs.top)<50?50:(yy+(_this.height()-dy)-clientHeight-bs.top));
					if(scrollTop>scrollHeight-clientHeight) scrollTop=scrollHeight-clientHeight;
					options.scrollContainer.scrollTop(scrollTop);
					
				}
			var t = yy -dy-(p0.top-p.top)+scrollTop;
			
			// 移动选中块
			//var l = left + e.clientX - x;
			//var t = top + e.clientY - y;
			_this.css({"top":t});
			
			// 选中块的中心坐标
			var mt = yy -dy+h2;
			
			// 遍历所有块的坐标
			$(wid).parent().children().not(_this).not(wid).not('.nodrager').each(function(i) {
				var obj = $(this);
				var p = obj.offset();
				var a3 = p.top;
				var a4 = p.top + obj.height();
				var h2= p.top + obj.height()/2;
					if(a3 < mt && mt < a4 ) {
						if(mt>h2) {
							wid.insertAfter(this);
						}else{
							wid.insertBefore(this);
						}
						//options.scrollContainer.mCustomScrollbar('update');
						return;
					}
				
			});
			
		});
		return false;
	});
	
	$(container).children().off("mouseup").mouseup(function(e) {
		 if(!window.kp_only) {
			$(document).off('mousemove.subdrager');
		}
	});
	var PreMove=function() {
		//e.preventDefault(); // 阻止选中文本
	    window.kp_only=true;
		_blank.show();
		 w = _this.outerWidth(true);
		 h = _this.outerHeight(true);
		 w2 = w/2;
		 h2 = h/2;
		clientHeight =options.scrollContainer.height();
		clientWidth =Math.max(document.documentElement.clientWidth, document.body.offsetWidth);
		scrollTop=options.scrollContainer.scrollTop();scrollLeft=0;
		scrollHeight = options.contentContainer.outerHeight(true);//Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
		scrollWidth =options.contentContainer.outerWidth(true);
		try{
			if(e.preventDefault) e.preventDefault();
			else{
				e.returnvalue=false
			}
		}catch(e){};
	
		
		// 添加虚线框
		_this.before('<div id="kp_widget_holder" style="'+options['hoder_div_css']+'"></div>');
		wid = $("#kp_widget_holder");
		wid.css({"height":_this.outerHeight(true)-options['height_correct'], "width":_this.outerWidth(true)-options['width_correct']});

		// 保持原来的宽高
		_this.css({"width": w-options['width_correct'], "height":h-options['height_correct'], "position":"absolute", opacity: 0.9, "z-index": 999, "left":left-options['width_correct'], "top":top-options['height_correct'],'border':'1px solid #e1e1e1','background':'#fbfbfb','box-shadow':'1px 1px 1px RGBA(0,0,0,0.7)'});
		//,"transform":"rotate(2deg)"
		// 绑定mouseup事件
		
		$(document).on('mouseup.subdrager',function(e) {
			$(document).off('mouseup.subdrager').off('mousemove.subdrager');
			
			// 拖拽回位，并删除虚线框
			var p = wid.position();
			p.top+=scrollTop;
			var data={}
			data.subid=_this.attr('subid');
			data.taskid=_this.attr('taskid');
			data.prevsubid=wid.prev()?wid.prev('.todo-item').attr('subid'):0;
			_this.animate({"left":p.left-parseInt(wid.css('margin-left')), "top":p.top-parseInt(wid.css('margin-top'))}, 300, function() {
				_this.removeAttr("style");
				wid.replaceWith(_this);
				
				if(typeof recall=='function'){
					recall(data);
				}
				_blank.hide();
				window.kp_only = null;
				
			});
			
		});
	};
}
})(jQuery);


