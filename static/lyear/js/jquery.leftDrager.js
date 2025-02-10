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
			'leftHide':1024
		}
	  	options=$.extend(opt,options);
		var $this=$(this);
		var $leftContainer=$('.bs-left-container');
		var $mainContainer=$('.bs-main-container');
		var $middleconMenu=$('.middleconMenu');
		var $topContainer=$('.bs-top-container');
		var oleft=((options.cookieid && getcookie(options.cookieid+'_width'))?parseInt(getcookie(options.cookieid+'_width')):$leftContainer.outerWidth(true))|| 0;
		var left=oleft;
		var clientWidth = document.documentElement.clientWidth;
		var setPosition=function(xx,flag){
			xx=xx*1;
			if(flag===true){
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
			if(flag===true){
				oleft=left;
			} 
			$leftContainer.css('width',left);
			$mainContainer.css('paddingLeft',left);
			$middleconMenu.css('left',left+5);
			$topContainer.css('paddingLeft',left);
			var currentRightWidth = mainWidth - xx;
			$mainContainer.trigger('leftDrager_layout.changeWidthValue',[currentRightWidth]);
			$this.css('left',left);
			if(options.cookieid){
				setcookie(options.cookieid+'_width',left,options.cookietime);
			}
		};
		var dragerClick=function(flag,nocookie){
			if(flag==='hide'){
				$mainContainer.css('paddingLeft',0);
				$middleconMenu.css('left',left+5);
				$topContainer.css('paddingLeft',0);
				$this.css({'left':0,'cursor':'default'});
				if(options.cookieid && !nocookie) setcookie(options.cookieid+'_isshow','hide',options.cookietime);
			}else if(flag==='show'){
				$leftContainer.css({width:left,'display':'block'});
				$mainContainer.css('paddingLeft',document.documentElement.clientWidth<opt.leftHide?0:left);
				$middleconMenu.css('left',left+5);
				$topContainer.css('paddingLeft',document.documentElement.clientWidth<opt.leftHide?0:left);
				$this.css({'left':left,'cursor':'w-resize'});
				if(options.cookieid && !nocookie) setcookie(options.cookieid+'_isshow','show',options.cookietime);
			}else{
				if($leftContainer.width()<64 || $leftContainer.is(':hidden')){
					dragerClick('show');
				}else{
					dragerClick('hide');
				}
			}
		};
		var dragging=function(){
			$this.off('mousedown').on('mousedown',function(e){
				 e.preventDefault(); 
				 var x=e.clientX;
				 var ox=x-$this.offset().left;
				 var width=$this.width();
				 $(document).mousemove(function(e){
					  e.preventDefault();
					  var xx=e.clientX;
					  if((xx-ox+width)>clientWidth){
						  xx=clientWidth+ox-width;
					  }
					  if(xx-ox<=0){
						xx=ox;  
					  } 
					  setPosition(xx-ox);
				 });
				 $(document).mouseup(function(e) {
					 $(document).off('mouseup').off('mousemove');
					 var xx=e.clientX;
					  if((xx-ox+width)>clientWidth){
						  xx=clientWidth+ox-width;
					  }
					  if(xx-ox<=0){
						xx=ox;  
					  }
					  setPosition(xx-ox,true);
					  
				 });
			});
		};
		
		var Layout=function(){
			var headerHeight=jQuery('.bs-top-container').outerHeight(true);
			var clientHeight = Math.max(document.documentElement.clientHeight, document.body.clientHeight);
			jQuery('.bs-main-container').css('margin-top',headerHeight?headerHeight:0);
			jQuery('.bs-main-container,.left-drager').css('height',clientHeight-headerHeight-1);
			jQuery('.left-drager').css('top',headerHeight?headerHeight:0);
			if(typeof(resizefunc)==='function'){
			   resizefunc();  
			}
			leftHide();
	   };
		var leftHide=function(){
			if(document.documentElement.clientWidth<opt.leftHide){
				dragerClick('hide',true);
			}else{
				if(options.cookieid && getcookie(options.cookieid+'_isshow')){
					var isshow=getcookie(options.cookieid+'_isshow');
					if(isshow==='hide'){
						dragerClick('hide',true);
					}else{
						dragerClick('show',true);
					}
				}else{
					dragerClick('show',true);
				}
			}
		};
		
		var init=function(){
			Layout();
			$this.find('.left-drager-op').off('click').on('click',function(){
				dragerClick();
			});
			jQuery('.lyear-aside-toggler').off('click').on('click',function(){
				if($leftContainer.width()<64 || $leftContainer.is(':hidden')){
					dragerClick('show');
				}else{
					dragerClick('hide');
				}
				
			});
			
			var isshow='';
			if(options.cookieid && getcookie(options.cookieid+'_isshow')){
				isshow=getcookie(options.cookieid+'_isshow');
				if(isshow==='hide'){
					dragerClick('hide',true);
				}else{
					
					dragerClick('show');
				}
			}else{
				if($leftContainer.width()<64 || $leftContainer.is(':hidden')){
					dragerClick('hide');
				}else{
					dragerClick('show');
				}
			}
			dragging();
			var resizeTimer=null;
			window.onresize=function(){
				if(resizeTimer){
					window.clearTimeout(resizeTimer);
				}
				window.setTimeout(function(){Layout();},100);
			};
			leftHide();	
		};
		init();
	};
})(jQuery);