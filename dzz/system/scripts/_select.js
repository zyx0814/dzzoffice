/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
function _select(container)
{
	this.id=this.name=container;
	this.string="_select.icos."+this.id;
	this.board=document.getElementById(container);
	_select.icos[this.id]=this;
};
_select.delay=500;
_select.width=120;
_select.height=120;
_select.icos={};

_select.onmousemove=null;
_select.onmouseup=null;
_select.tach=null;
_select.onselectstart=1;
_select.init=function(container){
	var obj= new _select(container);
	jQuery(obj.board).on('mousedown',function(e){
		
		e=e?e:window.event;
		var tag = e.srcElement ? e.srcElement :e.target;
		
		if(/input|textarea/i.test(tag.tagName)){
			return true;
		}	
		
		if(e.button==2) return true;
		dfire('mousedown');//dfire('touchstart');
		obj.Mousedown(e?e:window.event);
		return true;
	});

        jQuery(obj.board).on('mouseup',function(e){
            e=e?e:window.event;
            var tag = e.srcElement ? e.srcElement :e.target;
            if(/input|textarea/i.test(tag.tagName)){
                return true;
            }
            dfire('mouseup');
            //dfire('touchend');
            obj.Mouseup(e?e:window.event);

            return true;
        });


	return obj;
};
_select.prototype.DetachEvent=function(e)
{
	if(!_select.tach) return;
	document.onmousemove=_select.onmousemove;
	document.onmouseup=_select.onmouseup;
	document.onselectstart=_select.onselectstart;
	try{
		if(this.board.releaseCapture) this.board.releaseCapture();
	}catch(e){};
	_select.tach=0;
	_select.finishblank=0;
	
};
_select.prototype.AttachEvent=function(e)
{ 
	if(_select.tach) return
	_select.onmousemove=document.onmousemove;
	_select.onmouseup=document.onmouseup;
	_select.onselectstart=document.onselectstart;
	try{
		document.onselectstart=function(){return false;}
		if(e.preventDefault) e.preventDefault();
		else{
			if(this.board.setCapture) this.board.setCapture();
		}
	}catch(e){};
	_select.tach=1;
};
_select.prototype.Duplicate=function()
{
	this.copy=document.createElement('div');
	
	document.body.appendChild(this.copy);
	this.copy.style.cssText="position:absolute;left:0px;top:0px;width:0px;height:0px;filter:Alpha(opacity=50);opacity:0.5;z-index:10002;overflow:hidden;background:#000;border:1px solid #000;";
	//jQuery(this.copy).find('#text'+this.id).html(' ');
};
_select.prototype.Mousedown=function(e)
{
//	console.log(_explorer.type);
	this.mousedowndoing=false;
	if(e.type=='touchstart'){
		var XX=e.touches[0].clientX;
		var YY=e.touches[0].clientY;
	}else{
		var XX=e.clientX;
		var YY=e.clientY;
	}
	
	_select.oldxx=XX;
	_select.oldyy=YY;
	this.tl=XX;
	this.tt=YY;
	this.oldx=XX;
	this.oldy=YY;
	//alert('down');
	var self=this;
	if(!_select.tach) this.AttachEvent(e);
	
	if(e.type=='touchstart'){
		jQuery(this.board).on('touchmove',function(e){self.Move(e);return false});
	}else{
		document.onmousemove=function(e){self.Move(e?e:window.event);return false};
	}
	
	//this.mousedownTimer=setTimeout(function(){self.PreMove(XX,YY);},200);
};

_select.prototype.Mouseup=function(e)
{
	if(_select.tach) this.DetachEvent(e);
	if(!this.mousedowndoing) {
	}else this.Moved(e);
};
_select.prototype.PreMove=function(e)
{
	jQuery('#_blank').empty().show();
	if (this.move=="no") return;
	this.Duplicate();
	
	var self=this;
	this.mousedowndoing=true;
	var p=jQuery(this.board).offset();
	
	
	this.copy.style.left=this.tl+'px';
	this.copy.style.top=this.tt+'px';
	
	//清空数据
	if(_hotkey.ctrl>0 && _selectfile.selectall.container==this.id){
		
	}else{
		if(_selectfile.selectall.container) jQuery('#'+_selectfile.selectall.container).find('.Icoblock').removeClass('Icoselected');
		_selectfile.selectall.container=this.id;
		_selectfile.selectall.icos=[];
		_selectfile.selectall.position={};
	}
		//计算此容器内的所有ico的绝对位置，并且存入_selectfile.selectall.position中；
		jQuery(this.board).find('.Icoblock').each(function(){
			var el=jQuery(this);
			var p=el.offset();
			var icoid=el.attr('rid');
			if(icoid){
				_selectfile.selectall.position[icoid]={icoid:icoid,left:p.left,top:p.top,width:el.width(),height:el.height()};
			}
		});
	if(e.type=='touchmove'){
		jQuery(this.board).on('touchend',function(e){self.Moved(e);return true});
	}else{
		document.onmouseup=function(e){self.Moved(e?e:window.event);return false;};
	}
};
_select.prototype.Move=function(e)
{
	if(e.type==='touchmove'){
		var XX=e.touches[0].clientX;
		var YY=e.touches[0].clientY;
	}else{
		var XX=e.clientX;
		var YY=e.clientY;
	}
	if(!this.mousedowndoing && (Math.abs(this.oldx-XX)>5 || Math.abs(this.oldy-YY)>5)){
		this.PreMove(e);
	}
	if(!this.mousedowndoing) return;
	var flag=0;
	if(XX-this.oldx>0){
		this.copy.style.width=(XX-this.oldx)+"px";
	}else{
		this.copy.style.width=Math.abs(XX-this.oldx)+"px";
		this.copy.style.left=this.tl+(XX-this.oldx)+"px";
	}
	if(YY-this.oldy>0){
		this.copy.style.height=(YY-this.oldy)+"px";
	}else{
		this.copy.style.height=Math.abs(YY-this.oldy)+"px";
		this.copy.style.top=this.tt+(YY-this.oldy)+"px";
	}
	if(!BROWSER.ie){
		//if(Math.abs(_select.oldxx-XX)>20 || Math.abs(_select.oldyy-YY)>20){
			if(XX>this.oldx && YY > this.oldy){
				if(Math.abs(XX-_select.oldxx)>20 || Math.abs(YY-_select.oldyy)>20){
					 _select.oldxx=XX;
					 _select.oldyy=YY;
					 this.setSelected(true);
				}
			}else{
				if(Math.abs(XX-_select.oldxx)>20 || Math.abs(YY-_select.oldyy)>20){
					 _select.oldxx=XX;
					 _select.oldyy=YY;
					 this.setSelected();
				}
			}
	}
};
_select.prototype.Moved=function(e)
{
	var self=this;
	jQuery('#_blank').hide();
	if(_select.tach)	this.DetachEvent(e);
	if(e.type=='touchend'){
		var XX=e.changedTouches[0].clientX;
		var YY=e.changedTouches[0].clientY;
	}else{
		var XX=e.clientX;
		var YY=e.clientY;
	}
	if(BROWSER.ie){
		if(XX>this.oldx && YY > this.oldy){
			this.setSelected(true);
		}else{
			this.setSelected();
		}
	}
	jQuery(this.copy).remove();
	
};
_select.prototype.setSelected=function(flag){
	_select.sum++;
	var p=jQuery(this.copy).offset();
	var icos=[];
	var copydata={left:p.left,top:p.top,width:jQuery(this.copy).width(),height:jQuery(this.copy).height()};
	for(var icoid in _selectfile.selectall.position){
		var data=_selectfile.selectall.position[icoid];
		if(_select.checkInArea(copydata,data,flag)){
			_select.SelectedStyle(this.id,icoid,true,true);
		}else if(_hotkey.ctrl<1){
			_select.SelectedStyle(this.id,icoid,false,true);
		}
	}
};
_select.checkInArea=function(copydata,data,flag){
	var rect={minx:0,miny:0,maxx:0,maxy:0}
	rect.minx=Math.max(data.left,copydata.left);
	rect.miny =Math.max(data.top,copydata.top) ;
	rect.maxx =Math.min(data.left+data.width,copydata.left+copydata.width) ;
	rect.maxy =Math.min(data.top+data.height,copydata.top+copydata.height) ;
	if(!flag){
		if(rect.minx>rect.maxx || rect.miny>rect.maxy){
			return false;
		}else{
			return true
		}
	}else{
		if(rect.minx>rect.maxx || rect.miny>rect.maxy){
			return false;
		}else{
			return true;
			var area=(rect.maxx-rect.minx)*(rect.maxy-rect.miny);
			var dataarea=data.width*data.height;
			if(dataarea==area) return true;
			else return false;
		}
	}
};
_select.SelectedStyle=function(container,rid,flag,multi){
	var icos=_selectfile.selectall.icos||[];
	var fidarr=container.split('-');
	var fid=parseInt(fidarr[2]);
	var filemanageid=container.replace('filemanage-','');//'f-'+fidarr[2];
	if(flag){

	}
	var el=jQuery('#'+container).find('.Icoblock[rid='+rid+']');
	if(flag){
		if(_selectfile.selectall.container=='') _selectfile.selectall.container=container;
		if(multi  && _selectfile.selectall.container==container){
			if(jQuery.inArray(rid,_selectfile.selectall.icos)<0){
			 	_selectfile.selectall.icos.push(rid);
			}
		}else{
			jQuery('#'+_selectfile.selectall.container).find('.Icoblock').removeClass('Icoselected');
			_selectfile.selectall.container=container;
			_selectfile.selectall.icos=[rid];
			_selectfile.selectall.position={};
		}
		el.addClass('Icoselected');
		
	}else{
		var arr=[];
		if(_selectfile.selectall.container==container){
			for(var i in icos){
				if(icos[i]!=rid) arr.push(icos[i]);
			}
		}
		_selectfile.selectall.icos=arr;
		el.removeClass('Icoselected');	
	}

	if(_selectfile.cons[filemanageid]) _selectfile.cons[filemanageid].selectInfo();
	if(_explorer.type){
		_selectfile.changefileName(rid);
	}
};