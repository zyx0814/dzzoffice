/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
(function($)
{
	//左右分栏时，调用此，可以实现点击隐藏左侧分栏，拖动改变左侧分栏的宽度
	$.fn.leftDrager_layout = function(resizefunc,options)
	{
		var opt={	
					'cookieid':null, //记忆左侧大小和关闭状态的cookie标志符
					'cookietime':60*60*24*30,
					'leftHide':700
			}
	  	options=$.extend(opt,options);
		var self=this;
		var $this=$(this);
		var timer=null;
		var dragerWidth=$this.width();
		var $leftContainer=$('.bs-left-container');
		var $mainContainer=$('.bs-main-container');
		var isTopFixed=jQuery('.bs-top-container').hasClass('.navbar-fixed-top');
		var oleft=((options.cookieid && getcookie(options.cookieid+'_width'))?parseInt(getcookie(options.cookieid+'_width')):$leftContainer.outerWidth(true))|| 0;
		var left=oleft;
	    var headerHeight=jQuery('.bs-top-container').outerHeight(true);
		var clientWidth = document.documentElement.clientWidth;
		var clientHeight = document.documentElement.clientHeight;
		var setPosition=function(xx,flag){
			xx=xx*1;
			if(flag==true){
				if(xx<10){
					left=oleft;
					dragerClick('hide');
					return;
				}else{
					dragerClick('show');
				}
			}
			/*判断最小宽度*/
			var mainWidth=$mainContainer.outerWidth(true);
			var minWidth=parseInt($mainContainer.css('minWidth')) ||0;
			if(mainWidth-xx<minWidth){
				xx=mainWidth-minWidth;
			}
			
			left=xx;
			if(flag==true) oleft=left;
			$leftContainer.css('width',left);
			$mainContainer.css('marginLeft',left);
			var currentRightWidth = mainWidth - xx;
			$mainContainer.trigger('leftDrager_layout.changeWidthValue',[currentRightWidth]);
			$this.css('left',left);
	
			if(options.cookieid) setcookie(options.cookieid+'_width',left,options.cookietime);
		}
		var dragerClick=function(flag){
			if(flag=='hide'){
				$leftContainer.css('display','none');
				$mainContainer.css('marginLeft',0);
				$this.css('left',0).css('cursor','default');
				if(options.cookieid) setcookie(options.cookieid+'_isshow','hide',options.cookietime);
				jQuery('.left-drager-op').addClass('left-drager-op2');
			}else if(flag=='show'){
				$leftContainer.css('display','block');
				$mainContainer.css('marginLeft',document.documentElement.clientWidth<opt.leftHide?0:left);
				$this.css('left',left).css('cursor','w-resize');
				if(options.cookieid) setcookie(options.cookieid+'_isshow','show',options.cookietime);
				jQuery('.left-drager-op').removeClass('left-drager-op2');
				
			}else{
				
				if($leftContainer.width()<10){
					dragerClick('show');
				}else{
					dragerClick('hide');
				}
			}
		}
		var dragging=function(){
			$this.off('mousedown').on('mousedown',function(e){
				 e.preventDefault(); 
				 var x=e.clientX;
				 var ox=x-$this.offset().left
				 var width=$this.width();
				 $(document).mousemove(function(e){
					  e.preventDefault();
					  var xx=e.clientX;
					  if((xx-ox+width)>clientWidth) xx=clientWidth+ox-width;
					  if(xx-ox<=0) xx=ox;
					  setPosition(xx-ox);
				 });
				 $(document).mouseup(function(e) {
					 $(document).off('mouseup').off('mousemove');
					 var xx=e.clientX;
					  if((xx-ox+width)>clientWidth) xx=clientWidth+ox-width;
					  if(xx-ox<=0) xx=ox;
					  setPosition(xx-ox,true);
					  
				 });
			});
		}
		
		var Layout=function(){
			 var headerHeight=jQuery('.bs-top-container').outerHeight(true);
			 var isTopFixed=jQuery('.bs-top-container').hasClass('navbar-fixed-top');
			 var clientHeight = Math.max(document.documentElement.clientHeight, document.body.clientHeight);
			 jQuery('.bs-container').css('padding-top',isTopFixed?headerHeight:0);
			 jQuery('.bs-left-container,.bs-main-container,.left-drager').css('height',clientHeight-headerHeight);
			 jQuery('.left-drager,.bs-left-container').css('top',isTopFixed?headerHeight:0);
			 if(typeof(resizefunc)=='function') resizefunc(); 
			 leftHide();
		}
		var leftHide=function(){
			 if(document.documentElement.clientWidth<opt.leftHide){
				dragerClick('hide');
			}else{
				dragerClick('show');
			}
		}
		var Icon_location = function(){
			var nav = jQuery('nav');
			var nav_height =  nav.outerHeight();			
			if($this.length){
				 $this.find('.left-drager-sub').html('<span class="glyphicon glyphicon-arrow-left"></span>');	
				var sub_height = $this.find('.left-drager-sub').outerHeight();
				var width = $this.find('.left-drager-op').outerWidth();
//				nav.css('padding-left',width);
				$this.find('.left-drager-op').css({'height':nav_height,'padding-top':(nav_height-sub_height)/2});
							
			}

		}
		var Cleate_Result = function(e){
				//		创建水波div
		        var _this = $this;
		        var px = e.clientX;
		        var py = e.clientY;
		        var id=parseInt(Math.random()*1000);		                
		        _this.find('.left-drager-op').append('<div class="left-drager-click" style="top:'+py+'px;left:'+px+'px;background:rgba(255,255,255,.5);" id="wb_'+id+'"></div>');
		        setTimeout(function(){
		            _this.find('#wb_'+id).remove()
		        },500)				
		}
		var Icon_trigger = function(){
				if($this.find('.left-drager-op').hasClass('left-drager-op1')){	
					$this.find('.left-drager-sub').removeClass('left-setTimeout');	
					$this.find('.left-drager-sub').html('<span class="glyphicon glyphicon-arrow-left"></span>');
			        $this.find('.left-drager-sub').addClass('left-setTimeout1');						
				
				}else{
					$this.find('.left-drager-sub').removeClass('left-setTimeout1');	
					$this.find('.left-drager-sub').html('<span class="glyphicon glyphicon-th-list"></span>');
			        $this.find('.left-drager-sub').addClass('left-setTimeout');						
				}
	
		}
		var init=function(){
			var clientWidth = document.documentElement.clientWidth;
			//Icon_location();
			Layout();				
			$this.find('.left-drager-op').off('click').on('click',function(e){
				//Cleate_Result(e);
				dragerClick();
				//Icon_trigger();										
				
			});
			var isshow='';
			if(options.cookieid && getcookie(options.cookieid+'_isshow')){
				var isshow=getcookie(options.cookieid+'_isshow');
				if(isshow=='hide'){
					dragerClick('hide');
				}else{
					setPosition(left);
				}
			}else{
				if($leftContainer.width()<10){
					dragerClick('hide');
				}else{
					setPosition(left);
				}
			}
			 
			
			
			dragging();
			var resizeTimer=null;
			window.onresize=function(){
				if(resizeTimer) window.clearTimeout(resizeTimer);
				window.setTimeout(function(){Layout();},100);
			}
			leftHide();	
		}	
		init();
	}
})(jQuery);