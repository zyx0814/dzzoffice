;(function(){
    $.fn.lyearloading = function(options) {
    
	    // DOM容器对象
        var $this = $(this);
    
        var defaults = {
            opacity           : 0.1,                // 遮罩层的透明度，==0时没有遮罩层
            backgroundColor   : '#000000',          // 遮罩层的颜色
            imgUrl            : '',                 // 加载动画使用图片
            textColorClass    : '',                 // 定义文字的颜色，当不使用图片时
            spinnerColorClass : '',                 // 定义加载动画的颜色，当不使用图片时
            spinnerSize       : 'normal',           // 定义加载动画的大小，当不使用图片时
            spinnerText       : '',                 // 显示的文字
            zindex            : 9999,               // 遮罩层的z-index值
        };
    
	    // 融合配置项
        var opts = $.extend({}, defaults, options);
      
        // 默认样式
        var maskStyle  = {
            'position'         : 'absolute',
            'width'            : '100%',
            'height'           : '100%',
            'top'              : 0,
            'left'             : 0,
            'background-color' : opts.backgroundColor,
            'opacity'          : opts.opacity,
            'z-index'          : opts.zindex,
            
        }, textStyle  = {
            'position'       : 'absolute',
            'line-height'    : '120%',
            'text-align'     : 'center',
            'vertical-align' : 'middle',
            'z-index'        : opts.zindex + 1,
        }, spinnerStyle = {
            'position' : 'absolute',
            'z-index'  : opts.zindex + 1,
        };
        
        var defaultClass = 'lyear-loading';
    
	    // 初始化方法
	    this.init = function(){
            if ($this.children('.' + defaultClass).length > 0) {
                $this.children('.' + defaultClass).fadeIn(250)
            } else {
                var $maskHtml    = $('<div />').addClass(defaultClass),
                    $textHtml    = $('<span />').html($.trim(opts.spinnerText)).addClass(defaultClass).addClass(opts.textColorClass),
                    $spinnerHtml = opts.imgUrl ? $('<img />').attr('src', opts.imgUrl).addClass(defaultClass) : $('<div />').addClass('spinner-border').addClass(defaultClass).addClass(opts.spinnerColorClass).css(this.getSpinnerSize());
                
                var toolMethods = {
                    resizeStyle: function() {
                        var $parent        = $this.find('.' + defaultClass).parent(),
                            parentPosition = ('fixed,relative').indexOf($parent.css('position')),
                            isFixed        = parentPosition > -1 || $parent[0] === $('.' + defaultClass)[0].offsetParent,
                            offsetP        = isFixed ? { top: 0, left: 0 } : { top: $parent[0].offsetTop, left: $parent[0].offsetLeft },
		                    parentW        = $this.outerWidth(),
		                    parentH        = $this.outerHeight();
                        
                        //if ($this.selector == 'body') { // jquery 2.*版本
                        if ($this[0].localName == 'body') {
                            maskStyle.position     = 'fixed';
                            spinnerStyle.position  = 'fixed';                    
                            textStyle.position     = 'fixed';
                            
                            spinnerStyle.top  = $(window).height() / 2 - $spinnerHtml.outerHeight() / 2 + (opts.spinnerText ? (- 4 - $textHtml.height() / 2) : 0);
                            spinnerStyle.left = $(window).width() / 2 - $spinnerHtml.outerWidth() / 2;
                            
                            textStyle.top  = $(window).height() / 2 + $spinnerHtml.outerHeight() / 2 - 4;
                            textStyle.left = $(window).width() / 2 - $textHtml.outerWidth() / 2;
                        } else {
                            maskStyle.width  = parentW;
                            maskStyle.height = parentH;
                            maskStyle.top    = offsetP.top;
                            maskStyle.left   = offsetP.left;
                            
                            spinnerStyle.top  = parentH / 2 - $spinnerHtml.outerHeight() / 2 + (opts.spinnerText ? (- 4 - $textHtml.height() / 2) : 0) + offsetP.top;
                            spinnerStyle.left = parentW / 2 - $spinnerHtml.outerWidth() / 2 + offsetP.left;
            
                            textStyle.top  = parentH / 2 + $spinnerHtml.outerHeight() / 2 - 4 + offsetP.top;
                            textStyle.left = parentW / 2 - $textHtml.width() / 2 + offsetP.left;
                        }
                        
                        $maskHtml.css(maskStyle);
                        $spinnerHtml.css(spinnerStyle);
                        $textHtml.css(textStyle);
                    }
                };
                
                // 遮罩层继承父元素的边框效果
                maskStyle['border-top-left-radius']     = $this.css('border-top-left-radius');
				maskStyle['border-top-right-radius']    = $this.css('border-top-right-radius');
				maskStyle['border-bottom-left-radius']  = $this.css('border-bottom-left-radius');
				maskStyle['border-bottom-right-radius'] = $this.css('border-bottom-right-radius');
                
                opts.opacity && $maskHtml.css(maskStyle).appendTo($this);
                $.trim(opts.spinnerText) && $textHtml.css(textStyle).appendTo($this);
                $spinnerHtml.css(spinnerStyle).appendTo($this);
                
                this.loadImage(opts.imgUrl, function (imgObj) {
                    toolMethods.resizeStyle();
                }, function(e){
                    throw new Error(e);
                });
                
                $(window).off('resize.' + defaultClass).on('resize.' + defaultClass, function () {
                    toolMethods.resizeStyle();
                });
            }
	    }
	    
	    this.hide = function(){
            $this.children('.' + defaultClass).fadeOut(250);
	    }
        
        this.show = function(){
            $this.children('.' + defaultClass).fadeIn(250);
        }
        
        this.destroy = function() {
            $this.children('.' + defaultClass).fadeOut(250, function() {
                $(window).off('resize.' + defaultClass);
                $(this).remove();
            });
        }
        
        this.loadImage = function (url, callback, error) {
			if (!url) {
				return callback();
			}
			
			var imgObj;

			imgObj     = new Image();
			imgObj.src = url;

			if (imgObj.complete && callback) {
				return callback();
			}

			imgObj.onload = function () {
				imgObj.onload = null;
				callback && callback();
			};

			imgObj.onerror = function (e) {
				imgObj.onerror = null;
				error && error(e);
			};

			return imgObj;
		}
        
        // 对loading设置大小的处理返回
        this.getSpinnerSize = function() {
            var sizeCss;
            switch (options.spinnerSize) {
                case 'sm' :
                    sizeCss = {'width': '12px', 'height' : '12px'};
                    break;
                case 'nm' : 
                    sizeCss = {'width': '24px', 'height' : '24px'};
                    break;
                case 'md' : 
                    sizeCss = {'width': '36px', 'height' : '36px'};
                    break;
                case 'lg' : 
                    sizeCss = {'width': '48px', 'height' : '48px'};
                    break;
                default : 
                    sizeCss = {'width': options.spinnerSize, 'height': options.spinnerSize};
            }
            
            return sizeCss;
        };
        
	    // 自动执行初始化函数
	    this.init();
	    
	    // 返回函数对象
	    return this;
    }
})(jQuery);