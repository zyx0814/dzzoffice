/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

_explorer.isPower=function(power,action){//判断有无权限;
	var actionArr={ 'flag' 		: 1,		//标志位为1表示权限设置,否则表示未设置，继承上级；
					  'read1'		: 2,		//读取自己的文件
					  'read2'		: 4,		//读取所有文件
					  'delete1'		: 8,		//删除自己的文件
					  'delete2'		: 16,		//删除所有文件
					  'edit1'		: 32,		//编辑自己的文件
					  'edit2'		: 64,		//编辑所有文件
					  'download1'   : 128,		//下载自己的文件
					  'download2'	: 256,		//下载所有文件
					  'copy1'       : 512,		//拷贝自己的文件
					  'copy2'       : 1024,		//拷贝所有文件
					  'upload'		: 2048,		//上传
					 // 'newtype'		: 4096,		//新建其他类型文件（除文件夹、网址、dzz文档、视频、快捷方式以外）
					  'folder'      : 8192,		//新建文件夹
					 // 'link'    	: 16384,	//新建网址
					 // 'dzzdoc'   	: 32768,	//新建dzz文档  
					 // 'video'		: 65536,	//新建视频
					 // 'shortcut'	: 131072,	//快捷方式
					  'share'   	: 262144,	//分享
	};
	if(parseInt(actionArr[action])<1) return false;
	//权限比较时，进行与操作，得到0的话，表示没有权限
	//console.log([action,actionArr[action],power,(power & parseInt(actionArr[action]))]);  
    if( (power & parseInt(actionArr[action])) >0 ) return true;  
    return false; 
}
_explorer.FolderSPower=function(power,action){//判断有无权限;
	var actionArr={   'delete'  : 1,		
  					  'folder'  : 2,		
					  'link'   	: 4,		
					  'upload'  : 8,      
					  'document': 16,
					  'dzzdoc'	: 32,
					  'app'	  	: 64,
					  'widget'	: 128,	
					  'user'    : 256,
					  'shortcut': 512,
					  'discuss' :1024,
					  'download' :2048	 	 
	};
	if(action=='copy') action='delete';
	if(parseInt(actionArr[action])<1) return true;
	//权限比较时，进行与操作，得到0的话，表示没有权限
    if( (power & parseInt(actionArr[action])) == parseInt(actionArr[action]) ) return false;  
    return true; 
}
_explorer.FileSPower=function(power,action){//判断有无权限;
   
	var actionArr={   'delete'      : 1,		
  					  'edit'  		 : 2,		
					  'rename'   	: 4,		
					  'move'	    : 8,      
					  'download'  	: 16,
					  'share'		: 32,
					  'widget'	  	: 64,
					  'wallpaper'	: 128,
					  'cut'         : 256,
					  'shortcut'    : 512	 
	};
	
	if(action=='copy') action='delete';
	if(parseInt(actionArr[action])<1) return true;
	//权限比较时，进行与操作，得到0的话，表示没有权限  
    if( (power & parseInt(actionArr[action])) == parseInt(actionArr[action]) ) return false;  
    return true; 
}

_explorer.getFidByContainer=function(container){
	if(container.indexOf('icosContainer_body_')!==-1){
		return _explorer.space.typefid['desktop'];
	}else if(container=='taskbar_dock'){
		return _explorer.space.typefid['dock'];
	}else if(container=='_dock'){
		return _explorer.space.typefid['dock'];
	}else if(container.indexOf('icosContainer_folder_')!==-1){
		return container.replace('icosContainer_folder_','');
	}
}
_explorer.getContainerByFid=function(fid){
	var type='';
	for(var i in _explorer.space.typefid){
		if(fid==_explorer.space.typefid[i]) type=i;
	}
	var container='';
	if(type=='dock') container='_dock';
	else if(type=='desktop') container='icosContainer_body_'+_layout.fid;
	else{
		container='icosContainer_folder_'+fid;
	}
	return container;
}

_explorer.Permission_Container=function(action,fid){
	//预处理些权限
	//首先判断超级权限
	if(!_explorer.sourcedata.folder[fid]) return false;
	var perm=_explorer.sourcedata.folder[fid].perm;
	var sperm=_explorer.sourcedata.folder[fid].fsperm;	
	var gid=_explorer.sourcedata.folder[fid].gid;
	//判断超级权限
	if(!_explorer.FolderSPower(sperm,action)) return false;
	if(_explorer.space.uid<1) return false;//游客没有权限；
	/*if(_explorer.space.self>1){
		 return true;//系统管理员有权限
	}*/
	if(gid>0){
		if(action=='admin'){
			if(_explorer.space.self>1 || _explorer.sourcedata.folder[fid].ismoderator>0) return true;
			else return false;
		}else if(action=='rename'){
			action='delete';
		}else if(action=='multiselect'){
			action='copy';
		}else if(jQuery.inArray(action,['link','dzzdoc','newtype'])>-1 ){
			action='upload';
		}
		if(jQuery.inArray(action,['read','delete','edit','download','copy'])>-1){
			if(_explorer.myuid==_explorer.sourcedata.folder[fid].uid) action+='1';
			else action+='2';
		}
		return _explorer.isPower(perm,action);
	}else{
		if(action=='admin' || action=='multiselect'){
			//是自己的目录有管理权限
			if(_explorer.space.uid==_explorer.sourcedata.folder[fid].uid) return true;
			//云端的资源默认都有管理权限；
			if(_explorer.sourcedata.folder[fid].bz) return true;
		}
		if(action=='rename'){
			action='delete';
		}else if(jQuery.inArray(action,['link','dzzdoc','newtype'])>-1 ){
			action='upload';
		}
		
		if(jQuery.inArray(action,['read','delete','edit','download','copy'])>-1){
			if(_explorer.myuid==_explorer.sourcedata.folder[fid].uid) action+='1';
			else action+='2';
		}
		return _explorer.isPower(perm,action);
		
	}
	return false;
}

_explorer.Permission=function(action,data){
	if(_explorer.myuid<1) return false; //游客无权限；
	//预处理些权限
	if(data.isdelete>0) return true; //回收站有权限；
	var fid=data.pfid;
	var sperm=data.sperm;
	if(action=='download'){ //不是附件类型的不能下载
		if(data.type!='document' && data.type!='attach' && data.type!='image' && data.type!='folder') return false;
	}else if(action=='copy'){ //回收站内不能复制
		if(data.flag=='recycle') return false;
		if(data.type=='app' ||  data.type=='storage' || data.type=='pan' || data.type=='ftp') return false;
		
	}else if(action=='paste'){ //没有复制或剪切，没法粘帖
		if(_explorer.cut.icos.length<1) return false;
		action=_explorer.sourcedata.icos[_explorer.cut.icos[0]].type;
	}else if(action=='chmod'){ //修改权限
		if(data.bz && data.bz.split(':')[0]=='ftp') return true;
		else return false;
	}else if(action=='rename'){ //重命名
		if(fid==_explorer.space.typefid['dock']) return false;
		if(data.type=='folder' && data.bz && (data.bz.split(':')[0]=='ALIOSS' || data.bz.split(':')[0]=='qiniu')) return false;
		action='delete';
		
	}else if(action=='multiselect'){
		action='copy';
	}else if(action=='drag'){
		if(data.gid>0) action='copy';
		else action='admin';
	}
	if(!_explorer.FileSPower(sperm,action)) return false;
	if(jQuery.inArray(action,['read','delete','edit','download','copy'])>-1){
		if(_explorer.myuid==data.uid) action+='1';
		else action+='2';
	}
	return _explorer.Permission_Container(action,fid);
	
};

//判断容器是否有写入此类型文件的权限
_explorer.Permission_Container_write=function(fid,type){
	if(!_explorer.sourcedata.folder[fid]) return false;
	var sperm=_explorer.sourcedata.folder[fid].fsperm;	
	var gid=_explorer.sourcedata.folder[fid].gid;
	var action=type;
	if(jQuery.inArray(type,['folder','link','dzzdoc','shortcut','video'])<0) action='newtype';
	//判断超级权限
	if(!_explorer.FolderSPower(sperm,action)) return false;
	if(_explorer.myuid<1) return false;//游客没有权限；
	if(gid>0){
		 //是机构管理员有权权限；
		 if(_explorer.space.self>1 || _explorer.sourcedata.folder[fid].ismoderator>0) return true;
	}else{
			//是自己的目录有管理权限
			if(_explorer.myuid==_explorer.sourcedata.folder[fid].uid) return true;
			//云端的资源默认都有管理权限；
			if(_explorer.sourcedata.folder[fid].bz) return true;
	}
	
	return _explorer.Permission_Container(action,fid);
}
