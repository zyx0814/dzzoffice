
dzzattach={};
dzzattach.indzz=top._config?true:false; //是否在桌面内打开；
dzzattach.downurl=SITEURL+(DZZSCRIPT?DZZSCRIPT:'index.php')+'?mod=attach&op=down';
dzzattach.previewurl=SITEURL+(DZZSCRIPT?DZZSCRIPT:'index.php')+'?mod=attach&op=preview';
dzzattach.savetourl=SITEURL+(DZZSCRIPT?DZZSCRIPT:'index.php')+'?mod=attach&op=saveto';
dzzattach.init=function(root){
	
	dzzattach.root=root;
	//所有链接默认新窗口打开
	jQuery(root).find('a').each(function(){
		if(!jQuery(this).attr('target') || jQuery(this).attr('target')!='_self') jQuery(this).attr('target','_blank');
	});
	jQuery.getJSON(dzzattach.previewurl,function(data){
		dzzattach.exts=data;	
		jQuery(root).find('.dzz-image').css({'max-width':'100%','width':'auto','height':'auto'})
			/*.on('mouseover',function(){
				var el=jQuery(this);
				if(!this.id) this.id='tip_' + Math.random();
				var html='';
				html+='<h5>'+this.alt+' <small>('+el.attr('dsize')+')</small></h5>';
				html+='<div class="tip-op">';
				html+='<a class="preview" href="javascript:;" onclick="return dzzattach.preview(\''+this.id+'\',\'image\')" >预览</a><a class="download" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.download(this)" >下载</a><a class="saveto" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.saveto(this)" >保存到我的文档</a>'
				html+='</div>';
				//hideMenu('','prompt');
				showTip(this,'12',html);
			})*/
			.on('click',function(){
				if(!this.id) this.id='tip_' + Math.random();
				dzzattach.preview(this.id,'image');
				return false;
			});
		jQuery(root).find('.dzz-attach .dzz-attach-title')
			.attr('target','_blank')
			.on('mouseover',function(){
				var el=jQuery(this);
				if(!this.id) this.id='tip_' + Math.random();
				var html='';
				html+='<h5>'+el.attr('title')+' <small>('+el.attr('dsize')+')</small></h5>';
				html+='<div class="tip-op">';
				html+='<a class="preview" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.preview(\''+this.id+'\',\'attach\')" ><i class="dzz dzz-visibility dzzattach-i"></i>'+__lang.preview+'</a><a class="download" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.download(this);" ><i class="dzz dzz-download dzzattach-i"></i>'+__lang.download+'</a>';
					// '<a class="saveto" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.saveto(this,\'attach\');" >'+__lang.js_saved_my_documents+'</a>';
				html+='</div>';
				
				hideMenu('','prompt');
				showTip(this,'12',html);
			})
			.on('click',function(){
				return dzzattach.preview(this.id,'attach');
			});
		//链接方式
		jQuery(root).find('.dzz-link .dzz-link-title')
			.attr('target','_blank')
			.on('mouseover',function(){
				var el=jQuery(this);
				if(!this.id) this.id='tip_' + Math.random();
				var html='';
				html+='<h5>'+el.attr('title')+'</h5>';
				html+='<div class="tip-op">';
				html+='<a class="preview" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.preview(\''+this.id+'\',\'link\')" >'+__lang.preview+'</a><a class="saveto" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.saveto(this,\'link\');" >'+__lang.js_saved_my_documents+'</a>';
				html+='</div>';
				
				hideMenu('','prompt');
				showTip(this,'12',html);
			})
			.on('click',function(){
				return dzzattach.preview(this.id,'link');
			});
		//dzzdoc文档
		jQuery(root).find('.dzz-dzzdoc .dzz-dzzdoc-title')
			.attr('target','_blank')
			.on('mouseover',function(){
				var el=jQuery(this);
				if(!this.id) this.id='tip_' + Math.random();
				var html='';
				html+='<h5>'+el.attr('title')+'</h5>';
				html+='<div class="tip-op">';
				html+='<a class="preview" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.preview(\''+this.id+'\',\'dzzdoc\')" >'+__lang.preview+'</a><a class="saveto" href="javascript:;" data-id="'+this.id+'" onclick="return dzzattach.saveto(this,\'dzzdoc\');" >'+__lang.js_saved_my_documents+'</a>';
				html+='</div>';
				
				hideMenu('','prompt');
				showTip(this,'12',html);
			})
			.on('click',function(){
				return dzzattach.preview(this.id,'dzzdoc');
			});
	});
};

dzzattach.preview=function(id,type){
	var ele=document.getElementById(id);
	if(!ele) return false;
	var el=jQuery(ele);  
	var ext=el.attr('ext');
	switch(type){
		case 'attach':
		  var url=SITEURL+'share.php?a=view&s='+el.attr('apath');
		  if(dzzattach.indzz){
				try{
					top.OpenWindow('preview_'+el.attr('aid')
							  ,url
							  ,data.title
							  ,data.feature || ''
							  ,{name:data.title,img:data.icon}
						);
				}catch(e){
					window.open(url);
				}
			}else{
				window.open(url);
			}
		   break;
			/* if(dzzattach.exts[ext]){//有打开方式 
			 	var data=dzzattach.exts[ext];
			 	var url=data.url;
				data.title=el.attr('title')?el.attr('title'):el.attr('alt');
				data.icon=el.parent().find('.dzz-attach-icon').attr('src');
			 	if(url.indexOf('dzzjs:')!==-1){//dzzjs形式时
					if(url.indexOf('window.open')!==-1){//新窗口打开;
							window.open(el.attr('href'));
						}else{//内部窗口打开
							if(dzzattach.indzz){//在桌面内
								top.OpenWindow('preview_'+encodeURIComponent(el.attr('href')).replace(/\./g,'_').replace(/%/g,'_')
										  ,el.attr('href')
										  ,data.title
										  ,data.feature || ''
										  ,{name:data.title,img:data.icon}
									);
							}else{
								window.open(el.attr('href'));
							}
						}
				}else{
						//替换参数
						url=url.replace(/{(\w+)}/g, function($1){
								key=$1.replace(/[{}]/g,'');
								if(key=='url'){
									return encodeURIComponent(el.attr('href'));
								}else if(key=='path'){
									return el.attr('apath');
								}else if(key=='icoid'){
									return 'preview_'+el.attr('aid');
								}else return '';
							});	
						//添加path参数；
						if(url.indexOf('dzzjs:')===-1 && url.indexOf('?')!==-1){
							if(url.indexOf('path=')===-1){
								url=url+'&path='+el.attr('apath');
							}
							url=url+'&n='+encodeURIComponent(el.html());
						}
						
						if(dzzattach.indzz){
							try{
								top.OpenWindow('preview_'+el.attr('aid')
										  ,url
										  ,data.title
										  ,data.feature || ''
										  ,{name:data.title,img:data.icon}
									);
							}catch(e){
								window.open(url);
							}
						}else{
							
							window.open(url);
						}
				}
			 }else{//没有找到打开方式
				 alert('此文件不支持预览');
			 }*/
			
			break
	    case 'link':
		     if(!ext) ext='link';
			 if(ext && dzzattach.exts[ext]){//有打开方式
			    var data=dzzattach.exts[ext];
				data.title=el.attr('title');
				data.icon=el.parent().find('.dzz-link-icon').attr('src');
				var url=data.url;
				if(url.indexOf('dzzjs:')!==-1){//dzzjs形式时
						if(url.indexOf('window.open')!==-1){//新窗口打开;
							window.open(el.attr('href'));
						}else{//内部窗口打开
							if(dzzattach.indzz){//在桌面内
								top.OpenWindow('preview_'+encodeURIComponent(el.attr('href')).replace(/\./g,'_').replace(/%/g,'_')
										  ,el.attr('href')
										  ,data.title
										  ,''
										  ,{name:data.title,img:data.icon}
									);
							}else{
								window.open(el.attr('href'));
							}
						}
				}else{
					if(dzzattach.indzz){//在桌面内
					   top.OpenWindow('preview_'+encodeURIComponent(el.attr('href')).replace(/\./g,'_').replace(/%/g,'_')
									  ,el.attr('href')
									  ,data.title
									  ,''
									  ,{name:data.title,img:data.icon}
									 );
					}else{
						window.open(el.attr('href'));
					}
				}
			 }else{
				if(dzzattach.indzz){//在桌面内
					top.OpenWindow('preview_'+encodeURIComponent(el.attr('href')).replace(/\./g,'_').replace(/%/g,'_')
								   ,el.attr('href')
								   ,data.title
								   ,''
								   ,{name:data.title,img:data.icon}
								  );
					}else{
						window.open(el.attr('href'));
					}
			 }
			break;
		case 'dzzdoc':
			var data=dzzattach.exts[ext] ||{};
			data.title=el.attr('title');
			data.icon=el.parent().find('.dzz-dzzdoc-icon').attr('src');
			var url=data.url;
		
			if(dzzattach.indzz){//在桌面内
				top.OpenWindow('preview_'+encodeURIComponent(el.attr('href')).replace(/\./g,'_').replace(/%/g,'_')
						  ,el.attr('href')
						  ,data.title
						  ,''
						  ,{name:data.title,img:data.icon}
					);
			}else{
				window.open(el.attr('href'));
			}
			break;
		 case 'image':
			dzzattach.thumb(id);
			break;
		default:
			if(dzzattach.indzz){//在桌面内
				top.OpenWindow('preview_'+encodeURIComponent(el.attr('href')).replace(/\./g,'_').replace(/%/g,'_')
						  ,url
						  ,el.attr('title')
						  ,''
						  ,{name:el.attr('title')}
					);
			}else{
				window.open(el.attr('href'));
			}
			
	}
	
	return false;
}
dzzattach.download=function(obj,type){
	if(type=='image') var el=jQuery(obj);
	else{
		var ele=document.getElementById(jQuery(obj).data('id'));
		if(!ele) return false;
		var el=jQuery(ele);
	}
	var url=dzzattach.downurl+'&path='+el.attr('apath')+'&filename='+encodeURI(el.attr('title')?el.attr('title'):el.attr('alt'))
	if(!document.getElementById('hideframe')){
		jQuery('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
	}
	jQuery('#hideframe').attr('src',url);
}
dzzattach.saveto=function(obj,type){
	if(type=='image') var el=jQuery(obj);
	else{
		var ele=document.getElementById(jQuery(obj).data('id'));
		if(!ele) return false;
		var el=jQuery(ele);
	}
	if(type=='link'){
		var url=dzzattach.savetourl+'&type=link&link='+encodeURIComponent(el.attr('href'))+'&filename='+encodeURI(el.attr('title')?el.attr('title'):el.attr('alt'));
	}else if(type=='dzzdoc'){
		var url=dzzattach.savetourl+'&type=dzzdoc&aid='+el.attr('aid')+'&filename='+encodeURI(el.attr('title'));
	}else if(type=='image'){
		var url=dzzattach.savetourl+'&type=image&aid='+el.attr('aid')+'&filename='+encodeURI(el.attr('alt'));
	}else{
		var url=dzzattach.savetourl+'&type=attach&aid='+el.attr('aid')+'&filename='+encodeURI(el.attr('title'));
	}
	if(!document.getElementById('hideframe')){
		jQuery('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
	}
	jQuery('#hideframe').attr('src',url);
}
dzzattach.thumb={};
dzzattach.thumb=function(id){
	dzzattach.thumb.datas=[];
	dzzattach.thumb.current=0;
	jQuery(dzzattach.root).find('.dzz-image').each(function(index){
		if(document.getElementById(id).src==this.src) dzzattach.thumb.current=index;
		
		dzzattach.thumb.datas.push({ele:this,src:this.src,title:this.alt});
	});
	
	 var preview_setupDom=function(){
	
		var html='';
		html+='<div id="preview_Container" class="modal"  style="position:fixed;width:100%;height:100%;top:0px;left:0px;bottom:0px;right:0px;display:none;z-index:90000">';
		html+='<div id="preview-box" class="preview-box">';
		html+='	<div class="preview-handle" style="z-index: 118;"><b data_title="ESC'+__lang.logout+'" btn="close" class="pr-close" onclick="dzzattach.thumb.btnClick(\'close\');">ESC'+__lang.logout+'</b></div>';
		html+='	<div id="btn_hand" class="preview-panel" style="z-index: 117;">';
		html+='		<ul id="contents-panel" style="right:55px;" class="contents-panel">';
		html+='			<li btn="rotate"  onclick="dzzattach.thumb.btnClick(\'rotate\');"><i class="pr-rotate"></i><b>'+__lang.rotation+'</b></li>';
		//html+='			<li class="hidden-xs"  btn="collect" onclick="dzzattach.thumb.btnClick(\'collect\');"><i class="pr-save"></i><b>'+__lang.js_saved_my_documents+'</b></li>';
		html+='			<li class="hidden-xs"  btn="download" onclick="dzzattach.thumb.btnClick(\'download\');"><i class="pr-download"></i><b>'+__lang.download+'</b></li>';
		html+='			<li btn="newwindow"   onclick="dzzattach.thumb.btnClick(\'newwindow\');"><i class="pr-newwindow"></i><b>'+__lang.look_artwork+'</b></li>';
		html+='		</ul>';
		html+='		<div id="file_name" class="previewer-filename hidden-xs"></div>';
		html+='	</div>';
		html+='	<div id="con" class="preview-contents">';
		html+='		<div class="pr-btn-switch">';
		html+='			<b data_title="'+__lang.keyboard+'“←”'+__lang.key_on+'" btn="prev" class="pr-btn-prev" style="z-index: 116;" onclick="dzzattach.thumb.btnClick(\'prev\');">'+__lang.on_a+'</b>';
		html+='			<b data_title="'+__lang.keyboard+'“→”'+__lang.key_under+'" btn="next" class="pr-btn-next" style="z-index: 116;" onclick="dzzattach.thumb.btnClick(\'next\');">'+__lang.under_a+'</b>';
		html+='		</div>';
		html+='		<div id="pre_loading" style="display: none;" class="previewer-loading">'+__lang.loading_in+'</div>';
		html+='		<div id="previewer-photo" class="previewer-photo" style="overflow: visible; z-index: 114; display: none; left: 0px; top: 40px;" onclick="dzzattach.thumb.btnClick(\'close\');"></div>';
		html+='	</div>';
		html+='</div>';
		html+='<div id="prev-tips" class="prev-tips" >'+__lang.keyboard+'“←”'+__lang.key_on+'</div>';
		html+='<div id="next-tips" class="next-tips">'+__lang.keyboard+'“→”'+__lang.key_under+'</div>';
		html+='<div id="close-tips" class="esc-tips">ESC'+__lang.logout+'</div>';
		html+='<div id="popup-hint" style="z-index: 999999999; top: 50%; left:50%;margin-left:-86px; display:none;" class="popup-hint">';
		html+='	<i rel="type" class="hint-icon hint-inf-m"></i>';
		html+='	<em class="sl"><b></b></em>';
		html+='	<span rel="con">'+__lang.has_last_picture1+'</span>';
		html+='	<em class="sr"><b></b></em>';
		html+='</div>';
		html+='</div>';
		jQuery(html).appendTo(document.body);
		jQuery('body').addClass('dzzthumb_body');
		jQuery('#preview_Container').css({height:'100%',width:'100%'}).show();
		jQuery('#preview-box b').on('mouseenter',function(){
			var btn=jQuery(this).attr('btn');
			jQuery('#'+btn+'-tips').show();
		});
		jQuery('#preview-box b').on('mouseleave',function(){
			var btn=jQuery(this).attr('btn');
			jQuery('#'+btn+'-tips').hide();
		});
		jQuery(document).on('keyup.preview',function(event){
			var e;
			if (event.which !="") { e = event.which; }
			else if (event.charCode != "") { e = event.charCode; }
			else if (event.keyCode != "") { e = event.keyCode; }
			switch(e){
				case 27://Ctrl + Alt + ←
					dzzattach.thumb.btnClick('close');
					break;
				case 37://Ctrl + Alt + ←
					dzzattach.thumb.btnClick('prev');
					break;
				case 39://Ctrl + Alt + →
					dzzattach.thumb.btnClick('next');
					break;
			}
		});

	 }
	
	preview_setupDom();
	dzzattach.showContent();	
} 
dzzattach.showContent=function(){
		var data=dzzattach.thumb.datas[dzzattach.thumb.current];
		if(!data) return ;
		
		jQuery('#file_name').html(data.title);
		jQuery('#popup-hint').hide();
		jQuery('#previewer-photo').empty().hide();
		
		jQuery('#pre_loading').show();
		//jQuery('#contents-panel li').hide();
		
			//jQuery('#contents-panel li[btn=rotate],#contents-panel li[btn=collect],#contents-panel li[btn=download],#contents-panel li[btn=newwindow]').show();
			var el=jQuery('#previewer-photo');
			var screenWidth=Math.max(document.documentElement.clientWidth,document.body.offsetWidth);
			var screenHeight= Math.max(document.documentElement.clientHeight,document.body.offsetHeight);
			dzzattach.imgReady(data.src,function(){
				var width=0;
				var height=0;
				var imgw = this.width*1;
				var imgh =this.height*1;
				var bodyWidth=screenWidth-6;
				var bodyHeight=screenHeight-jQuery('#btn_hand').height()-6;
				var ratio=bodyWidth/bodyHeight;
				var ratio1=imgw/imgh;
				if(ratio>ratio1){
					if(bodyHeight<imgh){
						height=bodyHeight;
						width=imgw/imgh*bodyHeight;
					}else{
						width = imgw;
						height = imgh;
					}
				}else{
					if(bodyWidth<imgw){
						width=bodyWidth;
						height=imgh/imgw*bodyWidth;
					} else {
						width = imgw;
						height = imgh;
					}
				}
				var left=(screenWidth-width)/2;
				var top=(bodyHeight-height)/2;
				var el1=jQuery('<img height="'+height+'" width="'+width+'" style="cursor: move; top: '+top+'px; transform: rotate(0deg); left: '+left+'px;" src="'+data.src+'" ws_property="1" onload="jQuery(\'#pre_loading\').fadeOut();jQuery(\'#previewer-photo\').show();" >').appendTo(el);
				el1.get(0).onmousedown = function(event) {try{dragMenu(el1.get(0), event, 1);}catch(e){}};
				el1.on('click',function(){return false});
				jQuery.getScript('static/js/jquery.mousewheel.js',function(data){
					el1.on('mousewheel',function(e,delta, deltaX, deltaY){
						var dy=delta*100;
						var dx=dy*ratio1;
						dzzattach.thumb.pic_resize(dx,dy);
						return false;
					});
				});
			});
			
			
			
	};	
dzzattach.thumb.angle=0;
dzzattach.thumb.pic_resize=function(dx,dy){
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
dzzattach.thumb.btnClick=function(btn){
	switch(btn){
			case "close":
				jQuery(document).off('.preview');
				jQuery('body').removeClass('dzzthumb_body');
				jQuery('#preview_Container').remove();
				jQuery('#previewr-photo').empty();
				break;	
			case "prev":
				if(dzzattach.thumb.current==0){
					jQuery('#popup-hint').find('span').html(__lang.has_last_picture);
					jQuery('#popup-hint').show();
					window.setTimeout(function(){jQuery('#popup-hint').hide();},3000);
				}else{
					dzzattach.thumb.current=dzzattach.thumb.current-1;
					dzzattach.showContent();
				}
				break;
			case "next":
				if(dzzattach.thumb.current==dzzattach.thumb.datas.length-1){
					jQuery('#popup-hint').find('span').html(__lang.has_last_picture1);
					jQuery('#popup-hint').show();
					window.setTimeout(function(){jQuery('#popup-hint').hide();},3000);
				}else{
					dzzattach.thumb.current=dzzattach.thumb.current+1;
					dzzattach.showContent();
				}
				break;
			case "download":
				var data=dzzattach.thumb.datas[dzzattach.thumb.current];
				dzzattach.download(data.ele,'image');
				break;
			case "newwindow":
				var data=dzzattach.thumb.datas[dzzattach.thumb.current];
				if(data.src) window.open(data.src);
				break;
			
			case "rotate":
				var el=jQuery('#previewer-photo img');
				 dzzattach.thumb.angle+=90;
				 var rotation=((dzzattach.thumb.angle%360)/90);
				el.css({'transform':'rotate('+(dzzattach.thumb.angle)+'deg)','-webkit-transform':'rotate('+(dzzattach.thumb.angle)+'deg)','-moz-transform':'rotate('+(dzzattach.thumb.angle)+'deg)','-o-transform':'rotate('+(dzzattach.thumb.angle)+'deg)','-ms-transform':'rotate('+(dzzattach.thumb.angle)+'deg)'});
				if(BROWSER.ie && BROWSER.ie<9){
					el.css('filter','progid:DXImageTransform.Microsoft.BasicImage(Rotation='+(rotation)+'))');
				}
				break;
			case "collect":
			var data=dzzattach.thumb.datas[dzzattach.thumb.current];
				dzzattach.saveto(data.ele,'image');
				break;
		}
};
dzzattach.imgReady = (function () {
	var list = [], intervalId = null,

	// 用来执行队列
	tick = function () {
		var i = 0;
		for (; i < list.length; i++) {
			list[i].end ? list.splice(i--, 1) : list[i]();
		};
		!list.length && stop();
	},

	// 停止所有定时器队列
	stop = function () {
		clearInterval(intervalId);
		intervalId = null;
	};

	return function (url, ready, load, error) {
		var onready, width, height, newWidth, newHeight,
			img = new Image();
		
		img.src = url;

		// 如果图片被缓存，则直接返回缓存数据
		if (img.complete) {
			ready.call(img);
			load && load.call(img);
			return;
		};
		
		width = img.width;
		height = img.height;
		
		// 加载错误后的事件
		img.onerror = function () {
			error && error.call(img);
			onready.end = true;
			img = img.onload = img.onerror = null;
		};
		
		// 图片尺寸就绪
		onready = function () {
			newWidth = img.width;
			newHeight = img.height;
			if (newWidth !== width || newHeight !== height ||
				// 如果图片已经在其他地方加载可使用面积检测
				newWidth * newHeight > 1024
			) {
				ready.call(img);
				onready.end = true;
			};
		};
		onready();
		
		// 完全加载完毕的事件
		img.onload = function () {
			// onload在定时器时间差范围内可能比onready快
			// 这里进行检查并保证onready优先执行
			!onready.end && onready();
		
			load && load.call(img);
			
			// IE gif动画会循环执行onload，置空onload即可
			img = img.onload = img.onerror = null;
		};

		// 加入队列中定期执行
		if (!onready.end) {
			list.push(onready);
			// 无论何时只允许出现一个定时器，减少浏览器性能损耗
			if (intervalId === null) intervalId = setInterval(tick, 40);
		};
	};
})();