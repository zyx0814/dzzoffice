// ******************  dzzjs V1.00  ******************
// 作者：dzzjs1.0
// 版本：1.00
// 网站：http://www.dzz.cc
// 邮件：admin@dzz.cc
// 版权：版权归dzz.cc所有,任何人未经允许，不得使用和修改此代码
// **********************************************************
// ----------------------------------------------------------
function OpenWindow(url, name,feature)
{
	var content, title, features;
	content = "[url]" + url;
	title=name;
	
	if(feature) features=feature;
	else features = "pattern=title,width=500,height=370";
	
	_window.Open(content, title, features);
}
function _window(features)
{
	this.id=this.name="_W_test";
	this.string="_window.windows."+this.id;
	this.zIndex=++_window.zIndex;
	this.className='mac';
	this.bodyWidth= parseInt(_window.getFeature(features,"width")) || 800;
	this.bodyHeight= parseInt(_window.getFeature(features,"height")) ||500;
	this.left= _window.getFeature(features,"left");
	this.top= _window.getFeature(features,"top");
	this.right=_window.getFeature(features,"right");
	this.bottom=_window.getFeature(features,"bottom");
	this.move=_window.getFeature(features,"move").toLowerCase()||"move";
	this.maxmine=_window.getFeature(features,"maxmine").toLowerCase()||"max";
	this.minmine=_window.getFeature(features,"minmine").toLowerCase()||"min";
	this.closeable=_window.getFeature(features,"close").toLowerCase()||"close";
	this.isModal=_window.getFeature(features,"ismodal")?true:false;
	this.button=_window.getFeature(features,"button").toUpperCase();
	this.resize=_window.getFeature(features,"resize").toLowerCase()||"resize";
	this.pattern=_window.getFeature(features,"pattern").toLowerCase()||"title";//样式分 noborder  border title三种，默认 title;
	this.buttons={};
	
	_window.windows[this.id]=this;
	
};
_window.windows={};
_window.Max=new Array();
_window.Version="dzzdesktop js 1.0";
_window.Width=400;
_window.Height=-1;
_window.Timer=0;
_window.zIndex=4000;
_window.wIndex=4000;
_window.windows={};
_window.clientWidth=document.documentElement.clientWidth;
_window.clientHeight=document.documentElement.clientHeight;
_window.onmousemove=null;
_window.onmouseup=null;
_window.onselectstart=1;
_window.sum=0;
_window.ctrl=0;
_window.alt=0;
_window.hidetime=500;

_window.getFeature=function(source,name)
{
	var reg=new RegExp("(^|,|\\s)"+ name +"\\s*=\\s*([^,]*)(\\s|,|$)","i");
	if (reg.test(source)) return RegExp.$2;
	return "";
};
_window.getMaxNumber=function()
{
	var num=0;
	for(var i=0;i<arguments.length;i++) {if(arguments[i]>num) num=arguments[i];}
	return num;
};

_window.Open=function(content,title,features)
{  
	var obj=new _window(features);
	obj.Creat(content,title);
	return obj;
};


_window.prototype.Creat=function(content,title)
{
	this.board=document.createElement("div");
	this.board.className=this.className;
	this.board.style.position="absolute";
	this.board.style.zIndex=this.zIndex;
	this.board.style.visibility="hidden";
	document.body.appendChild(this.board);
	if(!_window.clientHeight)
	{
		this.board.style.left = "100%";
		this.board.style.top = "100%";
		_window.clientWidth = this.board.offsetLeft;
		_window.clientHeight = this.board.offsetTop;
		
	}
	var styles=new Array("LEFT_TOP","TOP","RIGHT_TOP","RIGHT","RIGHT_BOTTOM","BOTTOM","LEFT_BOTTOM","LEFT","SHADOW_TOP","SHADOW_RIGHT","SHADOW_BOTTOM","SHADOW_LEFT","CONTENT","TITLE");
	
	this.sides=new Array();
	
	var obj=document.createElement("div");
	var self=this;
	if(this.button)
	{
		styles[4]="RIGHT_BOTTOM_BY_BUTTON";
		styles[5]="BOTTOM_BY_BUTTON";
		styles[6]="LEFT_BOTTOM_BY_BUTTON";
		styles[styles.length]="BUTTON";
	}
	if(this.resize!="no") 	{styles[styles.length]="RESIZE";styles[styles.length]="RESIZE-X";styles[styles.length]="RESIZE-Y";}
	if(this.closeable!="no") 	styles[styles.length]="CLOSE";
	
	for(var i=0;i<styles.length;i++)
	{
		
		var obj=document.createElement("div");
		obj.className=styles[i];
		obj.style.position="absolute";
		this.board.appendChild(obj);
		switch(styles[i])
		{
			case "CONTENT":
			
				this.contentCase=obj;
				obj.style.width=this.bodyWidth+"px";
				if(this.bodyHeight>0) obj.style.height=this.bodyHeight+"px";
				
				obj.style.left=(this.sides[7].width+obj.offsetLeft)+"px";
				obj.style.top=(this.sides[1].height+obj.offsetTop)+"px";
				
				this.SetContent(content);
				this.width=this.bodyWidth+this.sides[3].width+this.sides[7].width;
				this.height=this.bodyHeight+this.sides[1].height+this.sides[5].height;
				this.minWidth=_window.getMaxNumber(this.sides[0].width+this.sides[2].width,this.sides[3].width+this.sides[7].width,this.sides[4].width+this.sides[6].width)+20;
				this.minHeight=_window.getMaxNumber(this.sides[0].height+this.sides[6].height,this.sides[1].height+this.sides[5].height,this.sides[2].height+this.sides[4].height)+2;
			
				this.board.style.height=this.height+"px";
				this.board.style.width=this.width+"px";
				var self=this;
				
				
				
				break;

			case "TITLE":
				
				this.titleCase=obj;
				obj.style.width=this.width+"px";
				this.titleCase.dx=obj.offsetWidth-this.width;
				if(this.minWidth<this.titleCase.dx){ this.minWidth=this.titleCase.dx;this.titlewidth=this.titleCase.dx}
				if(this.width>this.titleCase.dx){ obj.style.width=(this.width-this.titleCase.dx)+"px";this.titlewidth=this.width-this.titleCase.dx}
				this.SetTitle(title);
				jQuery(obj).bind('ondblclick',function(e){self.Max();});
				
				jQuery(obj).bind('mousedown',function(e){self.PreMove(e?e:window.event);});
				//jQuery(obj).bind('mouseup',function(e){self.Moved(e?e:window.event);});
			
				break;

			case "BUTTON":
				this.buttonCase=obj;
				obj.style.width=this.width+"px";
				obj.style.bottom="0px";
				this.buttonCase.dx=obj.offsetWidth-this.width;
				if(this.minWidth<this.buttonCase.dx) this.minWidth=this.buttonCase.dx;
				if(this.width>this.buttonCase.dx) obj.style.width=(this.width-this.buttonCase.dx)+"px";
				var buttons=this.button.split("|");
				for(var j=0;j<buttons.length;j++)
				{
					var ox=document.createElement("button");
					ox.className=buttons[j];
					ox.title=buttons[j];
					obj.appendChild(ox);
					jQuery(ox).bind('click',function(e){eval(self.string+".On"+this.title+"()")});
					this.buttons[buttons[j]]=ox;
				}
				break;

			case "RESIZE":
				obj.style.cursor="url('dzz/images/cur/aero_nwse.cur'),auto";
				jQuery(obj).bind('mousedown',function(e){self.resize='yes';self.PreResize(e?e:window.event);});
				jQuery(obj).bind('mouseup',function(e){self.resize='resize-x';self.Resized(e?e:window.event);});
				break;

			case "CLOSE":
				jQuery(obj).bind('click',function(e){self.Close();});
				
				break;
			
			case "RESIZE-X":
					obj.style.height=this.height+'px';
					this.resizexCase=obj;
					obj.style.cursor="e-resize";
					jQuery(obj).bind('mousedown',function(e){self.resize='resize-x';self.PreResize(e?e:window.event);});
					jQuery(obj).bind('mouseup',function(e){self.resize='resize-x';self.Resized(e?e:window.event);});
				break;
		
			case "RESIZE-Y":
					obj.style.width=this.width+'px';
					this.resizeyCase=obj;
					obj.style.cursor="s-resize";
					jQuery(obj).bind('mousedown',function(e){self.resize='resize-y';self.PreResize(e?e:window.event);});
					jQuery(obj).bind('mouseup',function(e){self.resize='resize-x';self.Resized(e?e:window.event);});
				break;
			default:
					this.sides[i]=obj;
					this.sides[i].width=obj.offsetWidth;
					this.sides[i].height=obj.offsetHeight;
					jQuery(obj).bind('mousedown',function(e){self.PreMove(e?e:window.event);});
					
					jQuery(obj).bind('mouseup',function(e){self.Moved(e?e:window.event);});
				
				break;
		}
	}
		this.sides[1].dx=this.sides[0].width+this.sides[2].width;
		if(this.width>this.sides[1].dx) this.sides[1].style.width=(this.width-this.sides[1].dx)+"px";
		this.sides[3].dy=this.sides[2].height+this.sides[4].height;
		if(this.height>this.sides[3].dy) this.sides[3].style.height=(this.height-this.sides[3].dy)+"px";
		this.sides[5].dx=this.sides[4].width+this.sides[6].width;
		if(this.width>this.sides[5].dx) this.sides[5].style.width=(this.width-this.sides[5].dx)+"px";
		this.sides[7].dy=this.sides[6].height+this.sides[0].height;
		if(this.height>this.sides[7].dy) this.sides[7].style.height=(this.height-this.sides[7].dy)+"px";

		this.sides[0].style.left="0px";
		this.sides[0].style.top="0px";
		this.sides[1].style.left=this.sides[0].width+"px";
		this.sides[1].style.top="0px";
		this.sides[2].style.right="0px";
		this.sides[2].style.top="0px";
		this.sides[3].style.right="0px";
		this.sides[3].style.top=this.sides[2].height+"px";
		this.sides[4].style.right="0px";
		this.sides[4].style.bottom="0px";
		this.sides[5].style.left=this.sides[6].width+"px";
		this.sides[5].style.bottom="0px";
		this.sides[6].style.left="0px";
		this.sides[6].style.bottom="0px";
		this.sides[7].style.left="0px";
		this.sides[7].style.top=this.sides[0].height+"px";
		this.left=this.left?parseInt(this.left):(this.right?_window.clientWidth-this.width-parseInt(this.right):parseInt((_window.clientWidth-this.width)/2));
		this.top=this.top?parseInt(this.top):(this.bottom?_window.clientHeight-this.height-parseInt(this.bottom):parseInt((_window.clientHeight-this.height)/2));
		if(this.left<0) this.left=0;
		if(this.top<0) this.top=0;
		this.left+=document.documentElement.scrollLeft;
		this.top+=document.documentElement.scrollTop;
		this.board.style.left=this.left+"px";
		this.board.style.top=this.top+"px";
	
		
		this.board.style.visibility="visible";
	
	this.status=1;
	var self=this;
	

};


_window.prototype.SetContent=function(content)
{
	var type=content.slice(0,5),tent=content.slice(5);
	//if(this.oldcase)
	//{
	//	this.oldcase.appendChild(this.oldcontent);
	//	this.oldcase = null;
	//}
	if(type=="[url]")
	{
		
			if(this.iframe)
			{
				if(this.contentCase.firstChild!=this.iframe) this.contentCase.replaceChild(this.iframe,this.contentCase.firstChild);
				this.iframe.src=tent;
			}
			else
			{
				var url=tent;
				if(url.substr(url.lastIndexOf('.')).toLowerCase()=='.swf'){
						this.contentCase.innerHTML='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%"  id="FlashID" title="test"><param name="movie" value="'+url+'" /><param name="quality" value="high" /><param name="wmode" value="Opaque" /><param name="swfversion" value="8.0.35.0" /> <param name="expressinstall" value="Scripts/expressInstall.swf" /> <!--[if !IE]>--><object type="application/x-shockwave-flash" data="'+url+'"  width="100%" height="100%"><!--<![endif]--><param name="quality" value="high" /><param name="wmode" value="Opaque" /><param name="swfversion" value="8.0.35.0" /><param name="expressinstall" value="Scripts/expressInstall.swf" /> <div><h4>Need Adobe Flash Player.</h4><p><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash Player" width="112" height="33" /></a></p></div><!--[if !IE]>--></object> <!--<![endif]--></object>';
				}else{
					window.onbeforeunload=function(){
						return 'ddddddddd';
					}
					var xframe=document.createElement('iframe');
					xframe.name = 'ifm0';
					xframe.id = 'ifm0';
					xframe.frameBorder=0;
					xframe.marginHeight=0;
					xframe.marginWidth=0;
					xframe.allowtransparency=true;
					xframe.src=url;
					xframe.style.width='100%';
					xframe.style.height='100%';
					jQuery(xframe).bind('load',function(){window.onbeforeunload=null;});
					this.contentCase.style.overflow='hidden';
					this.contentCase.appendChild(xframe);
				}
				if(this.bodyHeight<0) this.bodyHeight=1;
			}
	}
};
_window.prototype.SetTitle=function(title)
{
	if(this.pattern=='noborder') this.titleCase.style.display='none'; 
	title=title||"dzz.cc";
	if(this.title==title) return false;
	var self=this;
	jQuery(this.titleCase).bind('contextmenu',function(e){self.Windowmenu(e?e:window.event);});
	this.title='<table width="90%" height="100%"  border="0" cellspacing="0"><tr><td align="center" valign="bottom">'+title+'</td></tr></table>';
	
	this.titleCase.innerHTML=this.title;
};

_window.prototype.Duplicate1=function()
{
	if(this.copy1) return false;
		this.copy1=document.createElement("DIV");
		this.contentCase.appendChild(this.copy1);
	
	this.copy1.style.cssText="position:absolute;left:"+(this.oldleft)+"px;top:"+(this.oldtop)+"px;width:0px;height:0px;border:2px dotted #000000;";
	this.copy1.style.zIndex=5000;
	
};
_window.prototype.Focus=function(e)
{
	if(this.zIndex<_window.zIndex) this.board.style.zIndex=this.zIndex=++_window.zIndex;
	this.isHide=0;
	this.status=1;
	jQuery(this.board).show();
	return false;
};

_window.prototype.Close=function()
{
	jQuery(this.board).remove();
	delete _window.windows[this.id];
	for(var key in this) delete this[key];

	
};

_window.prototype.Showhide=function()
{
	this.Focus();
	this.board.style.display='block';
	this.isHide=0;
	this.status=1;
};
_window.prototype.Windowmenu=function()
{

};

_window.prototype.Hidden=function()
{
	this.board.style.zIndex=-99999;
	
};


_window.prototype.Duplicate=function()
{
	if(typeof(this.copy)=='undefined' || this.copy==null) {
	this.copy=document.createElement("DIV");
	document.body.appendChild(this.copy);
	}
	this.copy.style.cssText="position:absolute;background:url(dzz/images/b.gif);left:"+(this.left-2)+"px;top:"+(this.top-2)+"px;width:"+this.width+"px;height:"+this.height+"px;border:2px dotted #000000;";
	this.copy.style.zIndex=this.zIndex+2;
	
	document.getElementById('_blank').style.display='block';

};

_window.prototype.DetachEvent=function(e)
{
	document.onmousemove=_window.onmousemove;
	document.onmousemove=_window.onmousemove;
	document.onmouseup=_window.onmouseup;
	document.onselectstart=_window.onselectstart;
	if(this.board.releaseCapture) this.board.releaseCapture();
	//_window.tach=0;
	document.getElementById('_blank').style.display='none';

	
};
_window.prototype.AttachEvent=function(e)
{
	
	_window.onmousemove=document.onmousemove;
	_window.onmouseup=document.onmouseup;
	_window.onselectstart=document.onselectstart;
	if(e.preventDefault) e.preventDefault();
	else
	{
		document.onselectstart=function(){return false;}
		if(this.board.setCapture) this.board.setCapture();
	}

	document.getElementById('_blank').style.display='block';	
};

_window.prototype.PreResize=function(e)
{
	if(this.move=='no') return;
	if (typeof(this.ResizeTimer)!="undefined") clearTimeout(this.ResizeTimer);
	this.Focus();
	//this.Duplicate();
	this.resizeX=e.clientX-this.width-4;
	this.resizeY=e.clientY-this.height-4;
	//document.body.style.cursor="url('dzz/images/cur/aero_nwse.cur'),auto";;
	this.AttachEvent(e);
	var self=this;

	eval("document.onmousemove=function(e){"+this.string+".Resize(e?e:window.event);};");
	eval("document.onmouseup=function(e){"+this.string+".Resized(e?e:window.event);};");
	
};
_window.prototype.Resize=function(e)
{
	var dx=0;
	var dy=0;
	if(this.resize!="resize-y")
	{
		var w=e.clientX-this.resizeX-4;
		w=(w>this.minWidth)?w:this.minWidth;
		if((w+this.left)>document.documentElement.clientWidth) w=document.documentElement.clientWidth-this.left;
		dx=w-this.width;
		this.width+=dx;
		this.resizeyCase.style.width=this.width+'px';
		this.board.style.width=this.width+"px";
		this.sides[1].style.width=(this.width-this.sides[1].dx)+"px";
		this.sides[5].style.width=(this.width-this.sides[5].dx)+"px";
		this.titleCase.style.width=(this.width-this.titleCase.dx)+"px";
		if(this.buttonCase) this.buttonCase.style.width=(this.width-this.buttonCase.dx)+"px";
		this.bodyWidth+=dx;
		this.contentCase.style.width=this.bodyWidth+"px";
	
	}
	if(this.resize!="resize-x")
	{
		var h=e.clientY-this.resizeY;
		var h=(h>this.minHeight)?h:this.minHeight;
		if((h+this.top)>_window.clientHeight) h=_window.clientHeight-this.top;
		dy=h-this.height;
		this.height+=dy;
		this.resizexCase.style.height=this.height+'px';
		this.board.style.height=this.height+"px";
		this.sides[3].style.height=(this.height-this.sides[3].dy)+"px";
		this.sides[7].style.height=(this.height-this.sides[7].dy)+"px";
		this.bodyHeight+=dy;
		
		this.contentCase.style.height=this.bodyHeight+"px";
	
	}
};
_window.prototype.Resized=function(e)
{
	this.DetachEvent(e);
		//记录窗口的大小
	document.getElementById('width').value=this.bodyWidth;
	document.getElementById('height').value=this.bodyHeight;
};

_window.prototype.ResizeBy=function(bodyWidth,bodyHeight)
{
	var dx=bodyWidth-this.bodyWidth;
	var dy=bodyHeight-this.bodyHeight;
	if(dx)
	{
		this.width+=dx;
		this.board.style.width=this.width+"px";
		this.blank.style.width=this.width+'px';
		this.sides[1].style.width=(this.width-this.sides[1].dx)+"px";
		this.sides[5].style.width=(this.width-this.sides[5].dx)+"px";
		this.titleCase.style.width=(this.width-this.titleCase.dx)+"px";
		if(this.buttonCase) this.buttonCase.style.width=(this.width-this.buttonCase.dx)+"px";
		this.bodyWidth+=dx;
		this.contentCase.style.width=this.bodyWidth+"px";
		if(this.folder && this.folder!='sys') this.blankcontent.style.width=this.bodyWidth+"px";
		if(typeof(this.topbarCase)!='undefined'){
			this.topbarCase.style.width=this.bodyWidth+this.sides['leftbar'].width+this.sides['rightbar'].width+"px";
		}
		if(typeof(this.bottombarCase)!='undefined'){
			this.bottombarCase.style.width=this.bodyWidth+this.sides['leftbar'].width+this.sides['rightbar'].width+"px";
		}
	}
	if(dy){
		this.height+=dy;
		this.board.style.height=this.height+"px";
		this.blank.style.height=this.height+"px";
		this.sides[3].style.height=(this.height-this.sides[3].dy)+"px";
		this.sides[7].style.height=(this.height-this.sides[7].dy)+"px";
		this.bodyHeight+=dy;
		this.contentCase.style.height=this.bodyHeight+"px";
		if(this.folder && this.folder!='sys') this.blankcontent.style.height=this.bodyHeight+"px";
		if(typeof(this.bottombarCase)!='undefined'){
			this.bottombarCase.style.top=(this.sides[1].height+this.sides['topbar'].height+this.bodyHeight)+"px";
		}
		if(typeof(this.leftbarCase)!='undefined'){
			this.leftbarCase.style.height=this.bodyHeight+"px";
		}
		if(typeof(this.rightbarCase)!='undefined'){
			this.rightbarCase.style.left=(this.width-this.sides[7].width-this.sides['rightbar'].width)+"px";
			this.rightbarCase.style.height=this.bodyHeight+"px";
		}	
			
	}

		//if(this.folder) this.SetFolderContent(this.folder,this.id);
};

_window.prototype.Mousedown=function(e)
{
	if(jQuery.browser.msie){
		if(e.button>1) return;
	}else{
		if(e.button>0) return;
	}
	
	
	this.Focus();
	
	this.mousedowndoing=false;
	var self=this;
	var XX=e.clientX;
	var YY=e.clientY;
	_window.even=e;
	if(e.preventDefault) e.preventDefault();
	_window.onselectstart=document.onselectstart;
	document.onselectstart=function(){return false;};
	this.PreMove(_window.even,XX,YY);
	//eval("document.onmouseup=function(e){"+this.string+".Moved(e?e:window.event);};");
	
};

_window.prototype.Mouseup=function(e)
{
//alert('movedup');
	document.onselectstart=_window.onselectstart;
		clearTimeout(this.mousedownTimer);
		 this.DetachEvent(e);
};
_window.prototype.PreMove=function(e)
{
	this.Focus();
	if (this.move=="no") return;
	
	var XX=e.clientX;
	var YY=e.clientY;
	//this.Duplicate();
	jQuery('#_blank').show();
	this.moveX=XX-this.left;
	this.moveY=YY-this.top;
	document.body.style.cursor="url('dzz/images/cur/aero_arrow.cur'),auto";
	var self=this;
	this.AttachEvent(e);
	eval("document.onmousemove=function(e){"+this.string+".Move(e?e:window.event);};");
	eval("document.onmouseup=function(e){"+this.string+".Moved(e?e:window.event);};");
	
};
_window.prototype.Move=function(e)
{
	var XX=e.clientX;
	var YY=e.clientY;
	if(XX<0) XX=0;
	if(YY<0) YY=0;
	if(XX>_window.clientWidth) XX=_window.clientWidth;
	if(YY>_window.clientHeight) YY=_window.clientHeight;
	if (XX-this.moveX+this.width>_window.clientWidth) XX=_window.clientWidth+this.moveX-this.width;
	if(XX-this.moveX<=0) XX=this.moveX;
	if (YY-this.moveY+this.height>_window.clientHeight) YY=_window.clientHeight+this.moveY-this.height;
	if(YY-this.moveY<=0) YY=this.moveY;
	if(this.move!="move-y") {this.board.style.left=(XX-this.moveX)+"px";this.left=XX-this.moveX;}
	if(this.move!="move-x") {this.board.style.top=(YY-this.moveY)+"px";this.top=YY-this.moveY;}
};
_window.prototype.Moved=function(e)
{	

	this.DetachEvent(e);
	jQuery('#_blank').hide();
	
	var XX=e.clientX;
	var YY=e.clientY;
	if(XX<0) XX=0;
	if(YY<0) YY=0;
	if(XX>_window.clientWidth) XX=_window.clientWidth;
	if(YY>_window.clientHeight) YY=_window.clientHeight;
	if (XX-this.moveX+this.width>_window.clientWidth) XX=_window.clientWidth+this.moveX-this.width;
	if(XX-this.moveX<=0) XX=this.moveX;
	if (YY-this.moveY+this.height>_window.clientHeight) YY=_window.clientHeight+this.moveY-this.height;
	if(YY-this.moveY<=0) YY=this.moveY;
	var tx=(this.move=="move-y")?null:(XX-this.moveX);
	var ty=(this.move=="move-x")?null:(YY-this.moveY);
	
	this.board.style.left=tx+"px";
	this.board.style.top=ty+"px";

};


_window.prototype.DisableButton=function(name,style)
{
	name=name.toUpperCase();
	this.buttons[name].disabled=true;
	this.buttons[name].className=(style?style:"DISABLED")+" "+name;
};
_window.prototype.EnableButton=function(name)
{
	name=name.toUpperCase();
	this.buttons[name].disabled=false;
	this.buttons[name].className=name;
};

_window.prototype.OnBACK=function()
{
	if(!this.step) this.step=0;
	if(this.OnBACKS){ if(this.step>0) this.OnBACKS[--this.step]();}
};
_window.prototype.OnNEXT=function()
{
	if(!this.step) this.step=0;
	if(this.OnNEXTS){ if(this.step<this.OnNEXTS.length) this.OnNEXTS[this.step++]();}
};
_window.prototype.OnOK=function()
{
	this.Close();
};
_window.prototype.OnCANCEL=function()
{
	this.Close();
};