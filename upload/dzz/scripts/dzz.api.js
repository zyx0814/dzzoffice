/*
 * #通过调用此类，子框架可以获取桌面包含此窗口的窗体句柄，通过window.name传递过来的参数：用户id 用户名  等等
 * @depend  jQuery.1.10.2.min.js
 * @depend  jquery.json-2.3.js
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
function _api(win,wname){
	this.win=win;
	this.wname=wname;
	this.icoid=win.icoid;
	this.sourcedata=parent._config.sourcedata.icos[win.icoid];
};
_api.init=function(){
	var wname = window.name;
	if (wname == "") return false;
	wname=jQuery.parseJSON(decodeURIComponent(wname));
	var winid=wname.winid;
	if(parent._window.windows[winid]){
		win=parent._window.windows[winid];
		return new _api(win,wname);
	}
	return false;
};
//设置窗体标题
_api.prototype.setTitle=function(title){
	this.win.setTitleText(title);
	//jQuery(win.titleCase).find('.titleText').html(title);
};
//设置接收的文件类型；
_api.prototype.setFileExt=function(exts){
	 this.win.fileext=exts||[];
};
//设置窗体大小
_api.prototype.setWinSize=function(width,height){
	this.win.ResizeTo(width,height);
};

//窗体显示加载loading
_api.prototype.showLoading=function(t){
	this.win.Loading(t);
}
//窗体最大化
_api.prototype.Max=function(){
	this.win.Max();
};

//窗体最小化
_api.prototype.Min=function(){
	this.win.Min();
};

//窗体还原
_api.prototype.Restore=function(){
	this.win.Restore();
};

//窗体关闭
_api.prototype.Close=function(){
	this.win.Close();
};

//窗体全屏
_api.prototype.FullScreen=function(){
	this.win.FullScreen();
};
function acceptdata(data){
		if(data.params.multiple){
		  var datas=data.icodata; 
	   }else{
		   var datas=[data.icodata];
	   }
	   
	   var me=UE.getEditor('editor')
	   for(var i in datas){
			var arr=datas[i];
			if(arr['bz'] && arr['bz']!='dzz'){
				 try{top.showmessage(__lang.Insert_nonenterprise_files_not_supported,'',3000,true);}catch(e){alert(__lang.Insert_nonenterprise_files_not_supported);}
				  continue;
			}
			switch(arr['type']){
				case 'image':
					var url=(DZZSCRIPT?DZZSCRIPT:'index.php')+'?mod=io&op=thumbnail&original=1&path='+(arr.apath?arr.apath:arr.dpath);
					 me.execCommand('insertimage',{
						src: url,
						_src:url,
						alt: arr.name,
						
						"class":'dzz-image',
						path:'attach::'+arr.aid,
						apath:arr.apath,
						aid:arr.aid,
						ext:arr.ext,
						dsize:arr.fsize
					});
					break;
				case 'attach':case 'document':
				     var url=(DZZSCRIPT?DZZSCRIPT:'index.php')+'?mod=io&op=getStream&original=1&path='+(arr.apath?arr.apath:arr.dpath);
					 var html='';
					html += '<span class="dzz-attach">' +
								'<img class="dzz-attach-icon" src="'+ arr.img + '" _src="' + arr.img + '" />' +
								'<a class="dzz-attach-title" href="' + url +'" title="' + arr.name + '"  dsize="'+arr.fsize+'" ext="'+arr.ext+'"  aid="'+arr.aid+'"  path="attach::'+arr.aid+'" apath="'+arr.apath+'"  >' + arr.name + '</a>' +
								'</span>';
					me.execCommand('insertHtml', html);
					break;
				case 'link':
					var html='';
					html += '<span class="dzz-link">' +
								'<img class="dzz-link-icon" src="'+ SITEURL+arr.img + '" _src="' +SITEURL+ arr.img + '" />' +
								'<a class="dzz-link-title" href="' + arr.url +'" title="' + arr.name + '" '+(arr.ext?'ext="'+arr.ext+'"':'')+' >' + arr.name + '</a>' +
								'</span>';
					me.execCommand('insertHtml', html);
					me.fireEvent("catchRemoteImage");
					break;
				case 'dzzdoc':
					var html='';
					html += '<span class="dzz-dzzdoc">' +
								'<img class="dzz-dzzdoc-icon" src="'+ arr.img + '" _src="' + arr.img + '" />' +
								'<a class="dzz-dzzdoc-title" href="' + arr.url +'" title="' + arr.name + '" aid="'+arr.aid+'" path="attach::'+arr.aid+'" apath="'+arr.apath+'" >' + arr.name + '</a>' +
								'</span>';
					me.execCommand('insertHtml', html);
					break;
				
				case 'video':
					me.execCommand('insertvideo', {
									url: arr.url,
									width: 420,
									height: 280
								});
					break;
			}
			
		}
	}