var _permcheck = {};
_permcheck.isPower = function (power, action) {//判断有无权限;
    var actionArr = {
        'flag': 1,		//标志位为1表示权限设置,否则表示未设置，继承上级；
        'read1': 2,		//读取自己的文件
        'read2': 4,		//读取所有文件
        'delete1': 8,		//删除自己的文件
        'delete2': 16,		//删除所有文件
        'edit1': 32,		//编辑自己的文件
        'edit2': 64,		//编辑所有文件
        'download1': 128,		//下载自己的文件
        'download2': 256,		//下载所有文件
        'copy1': 512,		//拷贝自己的文件
        'copy2': 1024,		//拷贝所有文件
        'upload': 2048,		//上传
        // 'newtype'		: 4096,		//新建其他类型文件（除文件夹、网址、dzz文档、视频、快捷方式以外）
        'folder': 8192,		//新建文件夹
        // 'link'    	: 16384,	//新建网址
        // 'dzzdoc'   	: 32768,	//新建dzz文档
        // 'video'		: 65536,	//新建视频
        // 'shortcut'	: 131072,	//快捷方式
        'share': 262144,	//分享
    };
    if (parseInt(actionArr[action]) < 1) return false;
    //权限比较时，进行与操作，得到0的话，表示没有权限
    if ((power & parseInt(actionArr[action])) > 0) return true;
    return false;
}
//文件夹超级权限
_permcheck.FolderSPower = function (power, action) {//判断有无权限;
    var actionArr = {
        'delete': 1,
        'folder': 2,
        'link': 4,
        'upload': 8,
        'document': 16,
        'dzzdoc': 32,
        'app': 64,
        'widget': 128,
        'user': 256,
        'shortcut': 512,
        'discuss': 1024,
        'download': 2048
    };
    if (action == 'copy') action = 'delete';
    if (parseInt(actionArr[action]) < 1) return true;
    //权限比较时，进行与操作，得到0的话，表示没有权限
    if ((power & parseInt(actionArr[action])) == parseInt(actionArr[action])) return false;
    return true;
}
//超级权限
_permcheck.FileSPower = function (power, action) {//判断有无权限;

    var actionArr = {
        'delete': 1,
        'edit': 2,
        'rename': 4,
        'move': 8,
        'download': 16,
        'share': 32,
        'widget': 64,
        'wallpaper': 128,
        'cut': 256,
        'shortcut': 512
    };

    if (action == 'copy') action = 'delete';
    if (parseInt(actionArr[action]) < 1) return true;
    //权限比较时，进行与操作，得到0的话，表示没有权限
    if ((power & parseInt(actionArr[action])) == parseInt(actionArr[action])) return false;
    return true;
}
_permcheck.Permission = function (action, data) {
    if (_filemanage.myuid < 1) return false; //游客无权限；
    var fid = data.pfid;
    var sperm = data.sperm;
    if (action == 'download') { //不是附件类型的不能下载
        if (data.type != 'document' && data.type != 'attach' && data.type != 'image' && data.type != 'folder') return false;
    } else if (action == 'copy') {
        if (data.type == 'app' || data.type == 'storage' || data.type == 'pan' || data.type == 'ftp') return false;
    } else if (action == 'paste') { //没有复制或剪切，无法粘帖
        if (_filemanage.copyfile < 1) return false;
        action = _filemanage.sourcedata.icos[_filemanage.cut.icos[0]].type;
    } else if (action == 'rename') { //如果是阿里云，七牛不可重命名,重命名时判断删除权限
        if (data.type == 'folder' && data.bz && (data.bz.split(':')[0] == 'ALIOSS' || data.bz.split(':')[0] == 'qiniu')) return false;
        action = 'delete';

    } else if (action == 'multiselect') {
        action = 'copy';
    } else if (action == 'drag') {
        if (data.gid > 0) action = 'copy';
        else action = 'admin';
    }
    if (!_permcheck.FileSPower(sperm, action)) return false;
    if (jQuery.inArray(action, ['read', 'delete', 'edit', 'download', 'copy']) > -1) {
        if (_filemanage.myuid == data.uid) action += '1';
        else action += '2';
    }
    return _permcheck.Permission_Container(action, fid);

};
_permcheck.Permission_Container = function (action, fid) {
    //首先判断超级权限
    if (!_filemanage.folderdata[fid]) return false;
    var perm = _filemanage.folderdata[fid].perm;
    var sperm = _filemanage.folderdata[fid].fsperm;
    var gid = _filemanage.folderdata[fid].gid;
    //判断超级权限
    if (!_permcheck.FolderSPower(sperm, action)) return false;
    if (_filemanage.space.uid < 1) return false;//游客没有权限；
    if (gid > 0) {
        if(_filemanage.folderdata[fid].ismoderator > 0) return true;
        if (action == 'admin') {
            if (_filemanage.space.self > 1 || _filemanage.folderdata[fid].ismoderator > 0) return true;
            else return false;
        } else if (action == 'rename') {//重命名判断删除权限
            action = 'delete';
        } else if (action == 'multiselect') {
            action = 'copy';
        } else if (jQuery.inArray(action, ['link', 'dzzdoc', 'newtype']) > -1) {
            action = 'upload';
        }
        if (jQuery.inArray(action, ['read', 'delete', 'edit', 'download', 'copy']) > -1) {
            if (_filemanage.myuid == _filemanage.folderdata[fid].uid) action += '1';
            else action += '2';
        }
        return _permcheck.isPower(perm, action);
    } else {
        if (_filemanage.space.uid == _filemanage.folderdata[fid].uid) return true;
        if (action == 'admin' || action == 'multiselect') {
            //是自己的目录有管理权限
            if (_filemanage.space.uid == _filemanage.folderdata[fid].uid) return true;
            //云端的资源默认都有管理权限；
            if (_filemanage.folderdata[fid].bz) return true;
        }
        if (action == 'rename') {
            action = 'delete';
        } else if (jQuery.inArray(action, ['link', 'dzzdoc', 'newtype']) > -1) {
            action = 'upload';
        }

        if (jQuery.inArray(action, ['read', 'delete', 'edit', 'download', 'copy']) > -1) {
            if (_filemanage.myuid == _filemanage.folderdata[fid].uid) action += '1';
            else action += '2';
        }

        return _permcheck.isPower(perm, action);

    }
    return false;
}
