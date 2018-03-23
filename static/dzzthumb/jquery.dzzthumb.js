/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 <img src="" data-original="" data-download="">
 */
(function($) {
	$.fn.dzzthumb = function(options) {
		var defaults = {
				root: $('body'),
				selector: 'img[data-original]',
				container: $('body'),
				allowdownload:1  //是否允许下载按钮
			},
			opts = $.extend(defaults, options),
			angle = 0,
			current = 0;

			$imgs = $(this);
		$imgs.off('click.dzzthumb').on('click.dzzthumb', function() {
			var self = this;
			$imgs.each(function(index) {
				if(this.src == self.src) current = index;
			});
			setupDom();
			showContent();
			return false;
		});
		var setupDom = function() {
			jQuery('html,body').addClass('dzzthumb_body');
			if(!document.getElementById('MsgContainer')) {
				jQuery('<div id="MsgContainer" style=" background: url(dzz/images/b.gif); z-index:99999;width:100%;height:100%;margin:0;padding:0; right: 0px; bottom: 0px;position: fixed; top:0px; left: 0px;"></div>').appendTo(opts['container']);
			} else {
				jQuery('#MsgContainer').empty().show();
			}

			var html = '';
			html += '<div id="preview_Container" style="position:absolute;width:100%;height:100%;top:0px;left:0px;bottom:0px;right:0px;display:none;z-index:10000">';
			html += '<div id="preview-box" class="preview-box">';
			html += '	<div class="preview-handle" style="z-index: 118;"><b data_title="ESC'+__lang.logout+'" btn="close" class="pr-close">ESC'+__lang.logout+'</b></div>';
			html += '	<div id="btn_hand" class="preview-panel" style="z-index: 117;">';
			html += '		<ul id="contents-panel" style="right:55px;" class="contents-panel">';
			html += '			<li btn="rotate"  ><i class="pr-rotate"></i><b>'+__lang.rotation+'</b></li>';
			//html+='			<li class="hidden-xs"  btn="collect" ><i class="pr-save"></i><b>保存到我的文档</b></li>';
			if(opts.allowdownload){
			html += '			<li class="hidden-xs"  btn="download" ><i class="pr-download"></i><b>'+__lang.download+'</b></li>';
			}
			html += '			<li btn="newwindow" ><i class="pr-newwindow"></i><b>'+__lang.look_artwork+'</b></li>';
			html += '		</ul>';
			html += '		<div id="file_name" class="previewer-filename hidden-xs"></div>';
			html += '	</div>';
			html += '	<div id="con" class="preview-contents">';
			html += '		<div class="pr-btn-switch">';
			html += '			<b data_title="'+__lang.keyboard+'“←”'+__lang.key_on+'" btn="prev" class="pr-btn-prev" style="z-index: 116;" >'+__lang.on_a+'</b>';
			html += '			<b data_title="'+__lang.keyboard+'“→”'+__lang.key_under+'" btn="next" class="pr-btn-next" style="z-index: 116;">'+__lang.under_a+'</b>';
			html += '		</div>';
			html += '		<div id="pre_loading" style="display: none;" class="previewer-loading">'+__lang.loading_in+'</div>';
			html += '		<div id="previewer-photo" class="previewer-photo" style="overflow: visible; z-index: 114; display: none; left: 0px; top: 40px;"></div>';
			html += '	</div>';
			html += '</div>';
			html += '<div id="prev-tips" class="prev-tips" >'+__lang.keyboard+'“←”'+__lang.key_on+'</div>';
			html += '<div id="next-tips" class="next-tips">'+__lang.keyboard+'“→”'+__lang.key_under+'</div>';
			html += '<div id="close-tips" class="esc-tips">ESC'+__lang.logout+'</div>';
			html += '<div id="popup-hint" style="z-index: 999999999; top: 50%; left:50%;margin-left:-86px; display:none;" class="popup-hint">';
			html += '	<i rel="type" class="hint-icon hint-inf-m"></i>';
			html += '	<em class="sl"><b></b></em>';
			html += '	<span rel="con">'+__lang.has_last_picture1+'</span>';
			html += '	<em class="sr"><b></b></em>';
			html += '</div>';
			html += '</div>';
			jQuery(html).appendTo('#MsgContainer');

			jQuery('#preview_Container').css({ height: '100%', width: '100%' }).show();
			jQuery('#preview-box b').on('mouseenter', function() {
				var btn = jQuery(this).attr('btn');
				jQuery('#' + btn + '-tips').show();
			});
			jQuery('#preview-box b').on('mouseleave', function() {
				var btn = jQuery(this).attr('btn');
				jQuery('#' + btn + '-tips').hide();
			});
			jQuery(document).on('keyup.dzzthumb', function(event) {
				var e;
				if(event.which != "") { e = event.which; } else if(event.charCode != "") { e = event.charCode; } else if(event.keyCode != "") { e = event.keyCode; }
				switch(e) {
					case 27: //Ctrl + Alt + ←
						jQuery(document).off('.dzzthumb');
						jQuery('#MsgContainer').empty().hide();
						break;
					case 37: //Ctrl + Alt + ←
						if(current == 0) {
							jQuery('#popup-hint').find('span').html(__lang.has_last_picture);
							jQuery('#popup-hint').show();
							window.setTimeout(function() { jQuery('#popup-hint').hide(); }, 3000);
						} else {
							current = current - 1;
							showContent();
						}
						break;
					case 39: //Ctrl + Alt + →
						if(current == $imgs.length - 1) {
							jQuery('#popup-hint').find('span').html(__lang.has_last_picture1);
							jQuery('#popup-hint').show();
							window.setTimeout(function() { jQuery('#popup-hint').hide(); }, 3000);
						} else {
							current = current + 1;
							showContent();
						}
						break;
				}
			});
			jQuery('#previewer-photo').on('click.dzzthumb', function() {
				jQuery(document).off('.dzzthumb');
				jQuery('#MsgContainer').empty().hide();
				jQuery('html,body').removeClass('dzzthumb_body');
			});
			jQuery('#MsgContainer [btn],#previewer-photo').on('click.dzzthumb', function() {
				var btn = jQuery(this).attr('btn');
				switch(btn) {
					case "close":
						jQuery(document).off('.dzzthumb');
						jQuery('#MsgContainer').empty().hide();
						jQuery('html,body').removeClass('dzzthumb_body');
						break;
					case "prev":
						if(current == 0) {
							jQuery('#popup-hint').find('span').html(__lang.has_last_picture);
							jQuery('#popup-hint').show();
							window.setTimeout(function() { jQuery('#popup-hint').hide(); }, 3000);
						} else {
							current = current - 1;
							showContent();
						}
						break;
					case "next":
						if(current == $imgs.length - 1) {
							jQuery('#popup-hint').find('span').html(__lang.has_last_picture1);
							jQuery('#popup-hint').show();
							window.setTimeout(function() { jQuery('#popup-hint').hide(); }, 3000);
						} else {
							current = current + 1;
							showContent();
						}
						break;
					case "download":
						var img = $imgs.get(current);
						//var dpath = $(img).data('dpath');
						var url =  $(img).data('download');//downurl + '&path=' + dpath;
						if(!document.getElementById('hideframe')) {
							jQuery('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
						}
						jQuery('#hideframe').attr('src', url);
						break;
					case "newwindow":
						var $img = $($imgs.get(current));
						if($img.data('original')){
							var original_img=$img.data('original');
							original_img=original_img.replace(/&original=0/i,'')+'&original=1';
							window.open(original_img);
						}
						break;

					case "rotate":
						var el = jQuery('#previewer-photo img');
						angle += 90;
						var rotation = ((angle % 360) / 90);
						el.css({ 'transform': 'rotate(' + (angle) + 'deg)', '-webkit-transform': 'rotate(' + (angle) + 'deg)', '-moz-transform': 'rotate(' + (angle) + 'deg)', '-o-transform': 'rotate(' + (angle) + 'deg)', '-ms-transform': 'rotate(' + (angle) + 'deg)' });
						if(BROWSER.ie && BROWSER.ie < 9) {
							el.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(Rotation=' + (rotation) + '))');
						}
						break;

				}
				return false;
			});
		}
		var showContent = function() {
			var img = $imgs.get(current);

			jQuery('#file_name').html(img.title);
			jQuery('#popup-hint').hide();
			jQuery('#previewer-photo').empty().hide();

			jQuery('#pre_loading').show();
			var el = jQuery('#previewer-photo');
			var screenWidth = opts['root'].width();
			var screenHeight = opts['root'].height();
			imgReady($(img).data('original'), function() {
				var width = 0;
				var height = 0;
				var imgw = this.width * 1;
				var imgh = this.height * 1;
				var bodyWidth = screenWidth - 6;
				var bodyHeight = screenHeight - jQuery('#btn_hand').height() - 6;
				var ratio = bodyWidth / bodyHeight;
				var ratio1 = imgw / imgh;
				if(ratio > ratio1) {
					if(bodyHeight < imgh) {
						height = bodyHeight;
						width = imgw / imgh * bodyHeight;
					} else {
						width = imgw;
						height = imgh;
					}
				} else {
					if(bodyWidth < imgw) {
						width = bodyWidth;
						height = imgh / imgw * bodyWidth;
					} else {
						width = imgw;
						height = imgh;
					}
				}
				var left = (screenWidth - width) / 2;
				var top = (bodyHeight - height) / 2;
				var el1 = jQuery('<img height="' + height + '" width="' + width + '" style="cursor: move; top: ' + top + 'px; transform: rotate(0deg); left: ' + left + 'px;" src="' + $(img).data('original') + '" ws_property="1" onload="jQuery(\'#pre_loading\').fadeOut();jQuery(\'#previewer-photo\').show();" >').appendTo(el);
				el1.get(0).onmousedown = function(event) { try { dragMenu(el1.get(0), event, 1); } catch(e) {} };
				el1.on('click', function() { return false });
				jQuery.getScript('static/js/jquery.mousewheel.min.js',function(data){
					el1.on('mousewheel',function(e,delta, deltaX, deltaY){
						var dy=delta*100;
						var dx=dy*ratio1;
						pic_resize(dx,dy);
						return false;
					});
				});
			});
		};
		var pic_resize=function(dx,dy){
			var el=jQuery('#previewer-photo>img');
			var pos=el.position();
			var imgleft=pos.left;
			var imgtop=pos.top;
			var imgwidth=el.width();
			var imgheight=el.height();
			imgleft-=dx/2;
			imgtop-=dy/2;
			imgwidth+=dx;
			imgheight+=dy;
			el.css({left:imgleft,top:imgtop,width:imgwidth,height:imgheight,'max-width':'none'});
		}
		var imgReady = (function() {
			var list = [],
				intervalId = null,

				// 用来执行队列
				tick = function() {
					var i = 0;
					for(; i < list.length; i++) {
						list[i].end ? list.splice(i--, 1) : list[i]();
					};
					!list.length && stop();
				},

				// 停止所有定时器队列
				stop = function() {
					clearInterval(intervalId);
					intervalId = null;
				};

			return function(url, ready, load, error) {
				var onready, width, height, newWidth, newHeight,
					img = new Image();

				img.src = url;

				// 如果图片被缓存，则直接返回缓存数据
				if(img.complete) {
					ready.call(img);
					load && load.call(img);
					return;
				};

				width = img.width;
				height = img.height;

				// 加载错误后的事件
				img.onerror = function() {
					error && error.call(img);
					onready.end = true;
					img = img.onload = img.onerror = null;
				};

				// 图片尺寸就绪
				onready = function() {
					newWidth = img.width;
					newHeight = img.height;
					if(newWidth !== width || newHeight !== height ||
						// 如果图片已经在其他地方加载可使用面积检测
						newWidth * newHeight > 1024
					) {
						ready.call(img);
						onready.end = true;
					};
				};
				onready();

				// 完全加载完毕的事件
				img.onload = function() {
					// onload在定时器时间差范围内可能比onready快
					// 这里进行检查并保证onready优先执行
					!onready.end && onready();

					load && load.call(img);

					// IE gif动画会循环执行onload，置空onload即可
					img = img.onload = img.onerror = null;
				};

				// 加入队列中定期执行
				if(!onready.end) {
					list.push(onready);
					// 无论何时只允许出现一个定时器，减少浏览器性能损耗
					if(intervalId === null) intervalId = setInterval(tick, 40);
				};
			};
		})();
		//var btnClick=

	}
})(jQuery);