/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
_hotkey={};
_hotkey.ctrl=0;
_hotkey.alt=0;
_hotkey.shift=0;
_hotkey.init=function(){
	_hotkey.ctrl=0;
	_hotkey.alt=0;
	_hotkey.shift=0;
}
jQuery(document).on('keydown',function(event){
	event=event?event:window.event;
	var tag = event.srcElement ? event.srcElement :event.target;
	if(/input|textarea/i.test(tag.tagName)){
		return true;
	}
	var e;
	if (event.which !="") { e = event.which; }
	else if (event.charCode != "") { e = event.charCode; }
	else if (event.keyCode != "") { e = event.keyCode; }
	switch(e){
		case 17:
			_hotkey.ctrl=1;
			break;
		case 18:
			_hotkey.alt=1;
			break;
		case 16:
			_hotkey.shift=1;
			break;
	}
 });
jQuery(document).on('keyup',function(event){
	event=event?event:window.event;
	var tag = event.srcElement ? event.srcElement :event.target;
	if(/input|textarea/i.test(tag.tagName)){
		return true;
	}
	var e;
	if (event.which !="") { e = event.which; }
	else if (event.charCode != "") { e = event.charCode; }
	else if (event.keyCode != "") { e = event.keyCode; }
	switch(e){
				
		case 17:
			_hotkey.ctrl=0;
			break;
		case 18:
			_hotkey.alt=0;
			break;
		case 16:
			_hotkey.shift=0;
			break;
		case 46:case 110: //delete
			try{
				if(_explorer.selectall.icos.length>0){
					_filemanage.delIco(_config.selectall.icos[0]);
				}
			}catch(e){}
			break;
		
		case 69://Ctrl + Alt + E
			try{
				if(_hotkey.alt && _hotkey.ctrl) _header.loging_close();
			}catch(e){}
			break;
	}

 });
