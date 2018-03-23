/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
(function($){
  $.fn.kpdragsort = function(options,recall) {
	var opt={
		'scrollContainer':$('.bs-main-container'), //滚动层
		'contentContainer':$('.main-content') //内容层
	}
	options=$.extend(opt,options);
	var container = this;
	var dx,dy,_this,wid,mousedownTimer,scrollHeight,scrollTop,clientHeight,_blank,bs,marginLeft,marginTop,marginRight,marginBottom;
 
	var _blank=jQuery('<div class="nokpdrager"  unselectable="on" onselectstart="return event.srcElement.type== \'text\';" style="display:none; url(dzz/images/b.gif); z-index:10000;width:100%;height:100%;margin:0;padding:0; right: 0px; bottom: 0px;position: absolute; top:0px; left: 0px;"></div>').appendTo(options.contentContainer);
	$(container).children().addTouch();
	$(container).children().off("mousedown").mousedown(function(e) {
		if($(this).hasClass('nokpdrager') || e.which != 1 || $(e.target).is("input, textarea") || window.kp_only) return; // 排除非左击和表单元素
		var self=this;
		try{
				if(e.preventDefault) e.preventDefault();
				else{
					e.returnvalue=false
				}
			}catch(e){};
		 mousedownTimer=setTimeout(function(){PreMove(e.clientX,e.clientY,self,e);},200);
	});
	
	$(container).children().off("mouseup").mouseup(function(e) {
		 if(!window.kp_only) {
			clearTimeout(mousedownTimer);
		}
	});
	var PreMove=function(xx,yy,item,e) {
		_this = $(item);
		marginLeft=isNaN(parseInt(_this.css('margin-left')))?0:parseInt(_this.css('margin-left'));
		marginTop=isNaN(parseInt(_this.css('margin-top')))?0:parseInt(_this.css('margin-top'));
		marginRight=isNaN(parseInt(_this.css('margin-right')))?0:parseInt(_this.css('margin-right'));
		marginBottom=isNaN(parseInt(_this.css('margin-bottom')))?0:parseInt(_this.css('margin-bottom'));
		//e.preventDefault(); // 阻止选中文本
		clientHeight =options.scrollContainer.height();// Math.max(document.documentElement.clientHeight, document.body.offsetHeight);
		scrollTop = options.scrollContainer.scrollTop();//Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		scrollHeight = options.contentContainer.outerHeight(true);//Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
		try{
			if(e.preventDefault) e.preventDefault();
			else{
				e.returnvalue=false
			}
		}catch(e){};
		bs=options.scrollContainer.offset();
 		
		var x = xx;
		var y = yy;
		var w = _this.outerWidth();
		var h = _this.outerHeight();
		var w2 = w/2;
		var h2 = h/2;
		var p = _this.offset();
		var p1=_this.position();
		var left = p1.left;
		var top = p1.top;
		dx=x-p.left;
		dy=y-p.top;
		window.kp_only = true;
		_blank.show();
		// 添加虚线框
		_this.before('<div id="kp_widget_holder"></div>');
		var wid = $("#kp_widget_holder");
		
		wid.css({"border":"2px dashed #ccc","float":"left",'marginLeft':marginLeft,'marginTop':marginTop,'marginRight':marginRight,'marginBottom':marginBottom, "height":_this.outerHeight(true)-marginTop-marginBottom, "width":_this.outerWidth(true)-marginLeft-marginRight});

		// 保持原来的宽高
		_this.css({"width":w, "height":h, "position":"absolute", opacity: 0.8, "z-index": 999, "left":left, "top":top});
		//创建空白遮盖层
		
		// 绑定mousemove事件
		$(document).mousemove(function(e) {
			e=e?e:window.event;
			try{
				if(e.preventDefault) e.preventDefault();
				else{
					e.returnValue=false;
				}
			}catch(e){};
			
			var xx=e.clientX;
			var yy=e.clientY;
			if(yy-dy<bs.top && scrollTop>0){
				scrollTop=scrollTop+yy-dy-bs.top;
				if(scrollTop<0) scrollTop=0;
				options.scrollContainer.scrollTop(scrollTop);
			}else if((yy+(_this.height()-dy))>=clientHeight+bs.top ){
				
				scrollTop=scrollTop+yy+(_this.height()-dy)-clientHeight-bs.top;
				if(scrollTop>scrollHeight-clientHeight) scrollTop=scrollHeight-clientHeight;
				options.scrollContainer.scrollTop(scrollTop);
			}
			var t = yy-dy-bs.top+scrollTop;
			var l = xx-dx-bs.left;
			// 移动选中块
			//var l = left + e.clientX - x;
			//var t = top + e.clientY - y;
			_this.css({"left":l, "top":t});
			
			// 选中块的中心坐标
			var ml = l+w2;
			var mt = t+h2;
			// 遍历所有块的坐标
			$(container).children().not(_this).not(wid).not('.nokpdrager').each(function(i) {
				var obj = $(this);
				var p = obj.position();
				var a1 = p.left;
				var a2 = p.left + obj.width();
				var a3 = p.top;
				var a4 = p.top + obj.height();

				// 移动虚线框
				if(a1 < ml && ml < a2 && a3 < mt && mt < a4) {
					if(!obj.next("#kp_widget_holder").length) {
						wid.insertAfter(this);
					}else{
						wid.insertBefore(this);
					}
					return;
				}
			});
		});

		// 绑定mouseup事件
		$(document).mouseup(function(e) {
			$(document).off('mouseup').off('mousemove');

			// 检查容器为空的情况
			$(container).each(function() {
				var obj = $(this).children();
				var len = obj.length;
				if(len == 1 && obj.is(_this)) {
					$("<div></div>").appendTo(this).attr("class", "kp_widget_block").css({"height":100});
				}else if(len == 2 && obj.is(".kp_widget_block")){
					$(this).children(".kp_widget_block").remove();
				}
			});

			// 拖拽回位，并删除虚线框
			var p = wid.position();
			
			_this.animate({"left":p.left, "top":p.top}, 200, function() {
				_this.removeAttr("style");
				wid.replaceWith(_this);
				if(typeof recall=='function'){
					recall();
				}
				window.kp_only = null;
				_blank.hide();
			});
		});
	};
}
})(jQuery);


