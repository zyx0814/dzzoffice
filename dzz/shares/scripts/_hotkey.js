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
		/*case 67: //Alt+C
			if(_hotkey.alt) _window.currentWindow('Close');
			//_hotkey.alt=0;
			break;
		case 77: //Alt+M
			if(_hotkey.alt) _window.currentWindow('Max');
			_hotkey.alt=0;
			break;
		case 78://Alt+N
			if(_hotkey.alt) _window.currentWindow('Min');
			_hotkey.alt=0;
			break;*/
		/*case 81://Alt+shift+Q
			if(_hotkey.alt && _hotkey.shift) _window.CloseAppwinAll();
			_hotkey.alt=0;
			_hotkey.shift=0
			break;*/
		/*case 75: //Alt+K
			if(_hotkey.alt) _login.showHotkey();
			//_hotkey.alt=0;
			break;*/
		/*case 83://Alt+S
			if(_hotkey.alt) jQuery('#taskbar_start').trigger('mousedown');;
			//_hotkey.alt=0;
			break;*/
		/*case 37://Ctrl + Alt + ←
			if(_hotkey.ctrl && _hotkey.alt) {_layout.setPagePrev();}
			break;
		case 39://Ctrl + Alt + →
			if(_hotkey.ctrl && _hotkey.alt) _layout.setPageNext();
			break;
		
		case 68:
			//Ctrl + Alt + D
			if(_hotkey.alt && _hotkey.ctrl) _window.showDesktop();
			
			break;
		case 145:
			//Ctrl + Alt + ScrollLock
			if(_hotkey.alt && _hotkey.ctrl) _login.showBackground();
			break;*/
		/*case 35:
			//Ctrl + Alt + End
			if(_hotkey.alt && _hotkey.ctrl) _login.LockDesktop();
			break;*/
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
