var _selectfile = {};
_selectfile = function (id, data, param) {
    var page = isNaN(parseInt(param.page)) ? 1 : parseInt(param.page);
    var total = isNaN(parseInt(param.total)) ? 1 : parseInt(param.total);
    this.string = "_selectfile.cons." + this.id;
    this.container = param.container;
    this.total = total;
    //alert('filemangeid='+id);
    this.bz = param.bz || ''; //标志是那个api的数据

    this.perpage = param.perpage;
    this.totalpage = Math.ceil(this.total / this.perpage);
    this.totalpage = this.totalpage < 1 ? 1 : this.totalpage;
    this.id = id;
    this.string = "_selectfile.cons." + this.id;
    //alert(this.id);
    var sidarr = id.split('-');
    if (sidarr[0] == 'f') this.fid = sidarr[1];
    else this.fid = 0;
    this.subfix = sidarr[0]; //记录当前的sid前缀 f、cat等
    this.winid = id;
    this.keyword = param.keyword;
    this.localsearch = param.localsearch;

    this.view = isNaN(parseInt(param.view)) ? _selectfile.view : parseInt(param.view);
    this.disp = isNaN(parseInt(param.disp)) ? _selectfile.disp : parseInt(param.disp);

    this.asc = param.asc; //_selectfile.asc;
    this.detailper = _selectfile.detailper;
    if (!this.data) this.data = {};
    this.data = data;
    this.currentpage = page;
    this.container = param.container;
    this.odata = [];
    this.sum = 0;
    _selectfile.cons[this.id] = this;
    _selectfile.fid = this.fid;
    _selectfile.subfix = this.subfix;
    _selectfile.winid = this.id;
    this.pageloadding = true;
    this.exts = param.exts || '';
    this.tags = param.tags || '';
    this.before = param.before || '';
    this.after = param.after || '';
    this.fids = param.fids || '';
    this.gid = param.gid || '';
}
_selectfile.selectall = {
    position: {},
    container: '',
    icos: []
};
_selectfile.saveurl = 'index.php?mod=system&op=save';
_selectfile.speed = 5;
_selectfile.perpage = 100; //每页最多个数；
_selectfile.cons = {};
_selectfile.view = 4;
_selectfile.disp = 0;
_selectfile.asc = 1;
_selectfile.detailper = [47, 10, 20, 15, 8]; //依此对应：名称  大小  类型  修改时间；
_selectfile.onmousemove = null;
_selectfile.onmouseup = null;
_selectfile.onselectstart = 1;
_selectfile.stack_data = {};
_selectfile.showicosTimer = {};
_selectfile.apicacheTimer = {};
_selectfile.infoPanelUrl = '';
_selectfile.viewstyle = ['bigicon', 'middleicon', 'middlelist', 'smalllist', 'detaillist'];
//获取中间高度
_selectfile.select_Scrolly = function (els) {
    var clientHeight = document.documentElement.clientHeight;
    if (!els) {
        els = $('.selectScroll-y');
    }
    els.each(function () {
        var els = $(this);
        var pos = els.offset();
      	var height = clientHeight - pos.top - 100;
        if (_explorer.type > 0) {
            var height = clientHeight - pos.top - 140;
        }
        els.css({
            'overflow': 'auto',
            'height': height,
        });
        new PerfectScrollbar(this);
    })

}

//iocn图标切换

_selectfile.icon = function () {
//修改初始化时的排列方式指示
    jQuery('.icons-thumbnail').attr('iconview', obj.view).find('.dzz').removeClass('dzz-view-module').removeClass('dzz-view-list').addClass(obj.view === 2 ? 'dzz-view-list' : 'dzz-view-module');
//jQuery('.icons-thumbnail').attr('iconview', obj.view).find('.dzz').attr('data-original-title',obj.view === 2 ? __lang.deltail_lsit : __lang.medium_icons);
    jQuery('.icons-thumbnail').attr('folderid', obj.id);
}
//替换列表图和缩略图
_selectfile.get_template = function (sid, whole, disp, asc) {
    var obj = _selectfile.cons[sid];
    var str = '';
    if (whole) {
        switch (obj.view) {
            case 0:
            case 1:
            case 2:
            case 3:
                str = jQuery('#template_middleicon').html();

                break;
            case 4:
                str = jQuery('#template_detaillist').html();
                //替换
                break;
        }
        //替换参数
        str = str.replace(/\{asc_\d\}/g, obj.asc);
        var regx = new RegExp('\{show_' + obj.disp + '\}', 'ig');
        str = str.replace(regx, 'inline-block');
        str = str.replace(/\{show_\d}/ig, 'none');
    } else {
        switch (obj.view) {
            case 0:
            case 1:
            case 2:
            case 3:
                str = jQuery('#template_middleicon .js-file-item-tpl').html();
                break;
            case 4:
                str = jQuery('#template_detaillist .js-file-item-tpl').html();
                break;
        }
    }
    return str;

};
_selectfile.prototype.CreateIcos = function (data, flag) {
    var self = this;
    var containerid = 'filemanage-' + this.winid;
    if (!flag && this.data[data.rid]) { //如果已经存在
        var el1 = jQuery('#' + containerid + ' .Icoblock[rid=' + data.rid + ']');
        _selectfile.glow(el1);
        return;
    }
    this.data[data.rid] = data;
    var template = _selectfile.get_template(this.id);
    //创建图标列表
    if (data.flag) {
        if (!data.img) {
            data.img = 'dzz/styles/thame/' + _explorer.thame.system.folder + '/system/' + data.flag + '.png';
        }
        data.error = 'dzz/images/default/system/' + data.flag + '.png';
    } else if (data.type === 'folder') {
        if (data.gid > 0) {
            data.icon = data.img ? data.img : data.icon;
            data.error = data.icon || 'dzz/images/default/system/folder-read.png';
            data.img = data.icon ? data.icon.replace('dzz/images/default', 'dzz/styles/thame/' + _explorer.thame.system.folder) : 'dzz/styles/thame/' + _explorer.thame.system.folder + '/system/folder-read.png';
        } else {
            data.icon = data.img ? data.img : data.icon;
            data.error = data.icon || 'dzz/images/default/system/folder.png';
            data.img = data.icon ? ((data.icon).replace('dzz/images/default', 'dzz/styles/thame/' + _explorer.thame.system.folder)) : 'dzz/styles/thame/' + _explorer.thame.system.folder + '/system/folder.png';
        }
    } else if (data.type === 'shortcut' && data.ttype === 'folder') {
        if (data.tdata.gid > 0) {
            data.error = data.tdata.img || 'dzz/images/default/system/folder-read.png';
            data.img = (data.tdata.img + '').replace('dzz/images/default', 'dzz/styles/thame/' + _explorer.thame.system.folder);
        } else {
            data.error = data.tdata.img || 'dzz/images/default/system/folder.png';
            data.img = data.tdata.img ? ((data.tdata.img + '').replace('dzz/images/default', 'dzz/styles/thame/' + _explorer.thame.system.folder)) : 'dzz/styles/thame/' + _explorer.thame.system.folder + '/system/folder.png';
        }
    } else {
        data.error = 'dzz/images/default/icodefault.png';
    }
    var html = template.replace(/\{name\}/g, data.name);
    html = html.replace(/\{rid\}/g, data.rid);
    html = html.replace(/tsrc=\"\{img\}\"/g, 'src="{img}"');
    html = html.replace(/\{img\}/g, data.img);

    html = html.replace(/\{username\}/g, data.username);
    html = html.replace(/\{replynum\}/g, data.replynum ? data.replynum : '0');


    html = html.replace(/\{zIndex\}/g, 10);
    html = html.replace(/\{error\}/g, data.error);
    html = html.replace(/\{size\}/g, ((data.type === 'folder' || data.type === 'app' || data.type === 'shortcut') ? '' : data.fsize));
    html = html.replace(/\{fsize\}/g, data.fsize);
    html = html.replace(/\{type\}/g, data.type);
    html = html.replace(/\{ftype\}/g, data.ftype);
    html = html.replace(/\{dateline\}/g, data.dateline);
    html = html.replace(/\{fdateline\}/g, data.fdateline);
    html = html.replace(/\{flag\}/g, data.flag);
    html = html.replace(/\{position\}/g, data.relpath);
    html = html.replace(/\{dpath\}/g, data.dpath);
    html = html.replace(/\{from\}/g, data.from);
    html = html.replace(/\{delusername\}/g, data.username);
    html = html.replace(/\{deldateline\}/g, data.deldateline);
    html = html.replace(/\{finallydate\}/g, data.finallydate);
    html = html.replace(/\{views\}/g, data.views);
    html = html.replace(/\{times\}/g, data.times);
    html = html.replace(/\{downs\}/g, data.downs);
    html = html.replace(/\{expireday\}/g, data.expireday);
    html = html.replace(/\{sharelink\}/g, data.sharelink);
    html = html.replace(/dsrc=\"\{qrcode\}\"/g, 'src="{qrcode}"');
    html = html.replace(/dsrc='\{qrcode\}'/g, "src='{qrcode}'");
    html = html.replace(/\{qrcode\}/g, data.qrcode);
    html = html.replace(/\{password\}/g, data.password);
    html = html.replace(/\{count\}/g, data.count);
    if (data.status < 0) {
        var sharestatus = '<span  style="color: red;">(' + data.fstatus + ')</span>';
    } else {
        sharestatus = '';
    }
    //收藏
    if (data.collect) {
        var collectstatus = '<a href="javascript:;" class="dzz-colllection-item" ><i class="dzz dzz-star" title=""></i></a>';
    } else {
        var collectstatus = '<a href="javascript:;" class="dzz-colllection-item hide"><i class="dzz dzz-star" title=""></i></a>';
    }
    html = html.replace(/\{collectstatus\}/g, collectstatus);
    html = html.replace(/\{sharestatus\}/g, sharestatus);
    if (data.type !== 'image') {
        html = html.replace(/data-start=\"image\".+?data-end=\"image\"/ig, '');
    }
    var position_hash = '';
    if (data.gid > 0) {
        position_hash = data.pfid > 0 ? '#group&do=file&gid=' + data.gid + '&fid=' + data.pfid : '#group&gid=' + data.gid;
    } else {
        position_hash = '#home&do=file&fid=' + data.pfid;
    }
    html = html.replace(/\{position_hash\}/g, position_hash);
    //处理操作按钮
    //html=this.filterOPIcon(data,html);
    var el = null;
    if (flag && jQuery('.Icoblock[rid=' + data.rid + ']').length > 0) {
        jQuery('.Icoblock[rid=' + data.rid + ']').replaceWith(html);
        el = jQuery('.Icoblock[rid=' + data.rid + ']')
    } else {
//        jQuery(html).appendTo('#' + containerid + ' .js-file-item-tpl');
		  jQuery('#' + containerid + ' .js-file-item-tpl').prepend(html);
        el = jQuery('.Icoblock[rid=' + data.rid + ']');
        jQuery('#shareinfo_' + data.rid).on('click', function (e) {
            return false;
        });

    }

  /*  //检查下载和分享菜单
    //判断下载权限
    if (!_explorer.Permission('download', data)) {
        el.find('.download').remove();
    }

    //判断分享权限
    if (!_explorer.Permission('share', data)) {
        el.find('.share').remove();
    }
*/
    if (this.view < 4) {

        el.on('mouseenter', function () {
            jQuery(this).addClass('hover');

        });
        el.on('mouseleave', function () {
            jQuery(this).removeClass('hover');

        });

        //处理多选框
        //if(!_selectfile.fid || _explorer.Permission_Container('multiselect',this.fid)){
        el.find('.icoblank_rightbottom').on('click', function () {
            var flag = true;
            var ell = jQuery(this).parent();
            var rid = ell.attr('rid');
            if (ell.hasClass('Icoselected')) {
                flag = false;
            }
            _select.SelectedStyle('filemanage-' + self.id, rid, flag, true);

            return false;
        });
        //处理操作按钮
        el.on('click', function (e) {
            var tag = e.srcElement ? e.srcElement : e.target;
            if (/input|textarea/i.test(tag.tagName)) {
                return true;
            }
            var Item = jQuery(this).closest('.Icoblock');
            var rid = Item.attr('rid');
            if( _explorer.sourcedata.icos[rid].type == 'folder'){
                _selectfile.Open(rid);
                return false;
            }
            var flag = true;
            if ((_hotkey.ctrl && Item.hasClass('Icoselected')) || (Item.hasClass('Icoselected') && _selectfile.selectall.icos.length === 1 && _selectfile.selectall.icos[0] === rid)) {
                flag = false;
            }
            var multi = (_hotkey.ctrl && _explorer.mulitype) ? true : false;
            _select.SelectedStyle('filemanage-' + self.id, jQuery(this).attr('rid'), flag, multi);
            //self.createBottom();
            return false;
        });
        if (this.total == 0 && jQuery('#' + containerid).find('.emptyPage').length == 0) {
            jQuery(jQuery('#template_nofile_notice').html()).appendTo(jQuery('#' + containerid));
        } else {
            jQuery('#' + containerid).find('.emptyPage').remove();
        }

    } else { //详细列表时


        el.bind('mouseenter', function () {
            jQuery(this).addClass('hover');
            //return false;
        });
        el.bind('mouseleave', function () {
            jQuery(this).removeClass('hover');
            //return false;
        });

        //点击图片和名称直接打开

        el.on('click', function (e) {
            e = e ? e : window.event;
            var tag = e.srcElement ? e.srcElement : e.target;
            if (/input|textarea/i.test(tag.tagName)) {
                return true;
            }
            var Item = jQuery(this).closest('.Icoblock');
            var rid = Item.attr('rid');
            if(_explorer.sourcedata.icos[rid].type == 'folder'){
                _selectfile.Open(rid);
                return false;
            }
            var flag = true;
            if ((_hotkey.ctrl && Item.hasClass('Icoselected')) || (Item.hasClass('Icoselected') && _selectfile.selectall.icos.length === 1 && _selectfile.selectall.icos[0] === rid)) {
                flag = false;
            }
            var multi = (_hotkey.ctrl && _explorer.mulitype) ? true : false;
            _select.SelectedStyle('filemanage-' + self.id, Item.attr('rid'), flag, multi);
            return false;
        });

    }
    el.on('dblclick', function (e) {
        if (!_selectfile.fid && _selectfile.winid == 'recycle-list') return true;
        var tag = e.srcElement ? e.srcElement : e.target;
        if (/input|textarea/i.test(tag.tagName)) {
            return true;
        }
        _selectfile.Open(el.attr('rid'));
        dfire('click');
        return false;
    });
    //设置邮件菜单
    el.on('contextmenu', function (e) {
        e = e ? e : window.event;
        var tag = e.srcElement ? e.srcElement : e.target;
        if (/input|textarea/i.test(tag.tagName)) {
            return true;
        }
        _contextmenu.right_ico(e, jQuery(this).attr('rid'));
        return false;
    });
    //检测已选中
    if (jQuery.inArray(data.rid, _selectfile.selectall.icos) > -1) {
        el.addClass('Icoselected');
    }
    //处理按钮

    if (!flag) {
        _selectfile.glow(el);
        this.sum++;
        this.total++;
        jQuery('#' + containerid + ' .scroll-y').scrollTop(9999999);
        this.currentdata['icos_' + data.rid] = data;
    }
    if (this.total == 0 && jQuery('#' + containerid).find('.emptyPage').length == 0) {
        jQuery(jQuery('#template_nofile_notice').html()).appendTo(jQuery('#' + containerid));
    } else {
        jQuery('#' + containerid).find('.emptyPage').remove();
    }
};
_selectfile.changefileName = function(rid){
    var filename = '';
    if(!rid){
        filename = _explorer.defaultfilename;
    }else{
        var ico = _explorer.sourcedata.icos[rid];
        filename = ico.name;
    }
    $('#savenewname').val(filename);
}
_selectfile.getData = function (url, callback) {
    jQuery.getJSON(url, function (json) {
        if (json.error) {
            alert(json.error);
            return false;
        } else {
            for (var id in json.data) {
                _explorer.sourcedata.icos[id] = json.data[id];
            }
            for (var fid in json.folderdata) {
                _explorer.sourcedata.folder[fid] = json.folderdata[fid];
            }
            _explorer.createMenuSwidth(_selectfile.fid);
            var obj = null;
            if (json.param.page > 1) {
                obj = _selectfile.cons[json.sid];
                obj.appendIcos(json.data);
                obj.total = parseInt(json.total);
                obj.totalpage = Math.ceil(obj.total / obj.perpage);
            } else {
                obj = new _selectfile(json.sid, json.data, json.param);
                if (_selectfile.selectall.container !== '_selectfile-' + json.sid) {
                    _selectfile.selectall = {
                        position: {},
                        container: '',
                        icos: []
                    };
                }
                obj.showIcos();
            }
            obj.url = url;
            //修改初始化时的排列方式指示
            jQuery('.icons-thumbnail').attr('iconview', obj.view).find('.dzz').removeClass('dzz-view-module').removeClass('dzz-view-list').addClass(obj.view === 2 ? 'dzz-view-list' : 'dzz-view-module');
            jQuery('.icons-thumbnail').attr('iconview', obj.view).find('.dzz').attr('data-original-title', obj.view === 2 ? __lang.deltail_lsit : __lang.medium_icons);
            jQuery('.icons-thumbnail').attr('folderid', obj.id);
            if (typeof (callback) === 'function') {
                callback(obj);
            }
        }
    });
};
_selectfile.prototype.showIcos = function (ext) {
    //排序数据
    var self = this;
    if (_selectfile.showicosTimer[this.winid]) {
        window.clearTimeout(_selectfile.showicosTimer[this.winid]);
    }
    var containerid = 'filemanage-' + this.winid;

    jQuery('#' + containerid).empty();
    this.createIcosContainer();
    var data_sorted = null;
    if (this.keyword) {
        data_sorted = _selectfile.Sort(_selectfile.Search(this.data, this.keyword), this.disp, this.asc);
        jQuery('#searchInput_' + this.id).val(this.keyword);
    } else {
        data_sorted = _selectfile.Sort(this.data, this.disp, this.asc);
    }
    if (ext) {
        data_sorted = _file.Searchext(data_sorted, ext);
    }
    this.currentdata = data_sorted;
    _selectfile.stack_data[self.id] = Array();
    for (var i in data_sorted) {
        _selectfile.stack_data[self.id].push({
            data: data_sorted[i],
            "obj": self
        });
    }
    window.setTimeout(function () {
        _selectfile.stack_run(self.id);
    }, 1);
    //增加底部信息
    this.pageloadding = false;
};
_selectfile.prototype.Resize = function () {
    _selectfile.select_Scrolly(jQuery('.scroll-y'));
    _selectfile.searchall();
};
//监听iframe父级窗口变化大小
$(parent).resize(function(){	
 _selectfile.select_Scrolly();
 })
 /*jQuery(document).ready(function(){
 _selectfile.select_Scrolly();
 })*/
//文件没有可以打开的应用
_selectfile.Open = function (rid, extid, title) {
    var data = _explorer.sourcedata.icos[rid];
    var name = data.name;
    // var ext =data.ext;
    // var type=data.type;

    var obj = {};
    obj.type = data.type;
    obj.ext = data.ext;
    obj.id = rid;
    obj.text = name;
    obj.dpath = data.dpath;
    //判断打开的url中是否含有dzzjs:等特殊协议；为了安全，只有应用才可以
    if (obj.type === 'link') {
        window.open(data.url);
        return;
    } else if (obj.type === 'dzzdoc') {
        obj.url = "index.php?mod=document&icoid=" + obj.id;
        window.open(obj.url);
        return;
    } else if (obj.type === 'folder') {
        var hash = '';
        var fid = data.oid;
        if (data.gid > 0) {
            hash = '#group&do=file&gid=' + data.gid + (fid > 0 ? '&fid=' + fid : '');
        } else {
            hash = '#home&do=file&fid=' + fid;
        }
        window.location.hash = hash;
        return false;
    }

    if (!extid) {
        extid = getExtOpen(data, true);
    }
    if (extid) {
        if (_explorer.extopen.all[extid].appid > 0 && _explorer.sourcedata.app[_explorer.extopen.all[extid].appid]['available'] < 1) {
            Alert(__lang.regret_app + _explorer.sourcedata.app[_explorer.extopen.all[extid].appid]['appname'] + __lang.already_close, 5, null, null, 'info');
            return;
        }
        var extdata_url = extopen_replace(data, extid);
        //var app=_explorer.sourcedata.app[_explorer.extopen.all[extid].appid];
        if (extdata_url) {
            extdata_url = extdata_url.replace(/{\w+}/g, '');
            if (extdata_url.indexOf('dzzjs:OpenPicWin') === 0) {
                jQuery('img[data-original]:visible').dzzthumb();
                jQuery('.Icoblock[rid=' + rid + '] img[data-original]').trigger('click');
                return;
            } else if (extdata_url.indexOf('dzzjs:') === 0) {
                window.open(data.url);
                return;
            } else {
                window.open(extdata_url);
            }
        }
    } else {
        top.showDialog('文件没有可以打开的应用');
    }
};

//获取打开方式

function getExtOpen(data, isdefault) {

    if (data.type === 'folder' || data.type === 'user' || data.type === 'app' || data.type === 'pan' || data.type === 'storage' || data.type === 'disk') {
        return true;
    }
    var openarr = [];

    //判断特殊区域后缀
    var bz = 'dzz';
    if (data.bz === '' || typeof data.bz === 'undefined') {
        if (data.rbz) {
            var bzarr = data.rbz.split(':');
            bz = bzarr[0];
        } else {
            bz = 'dzz';
        }
    } else {
        var bzarr = data.bz.split(':');
        bz = bzarr[0];
    }
    var ext = bz + ':' + data.ext;
    var i = 0;
    if (ext && _explorer.extopen.ext[ext]) {
        if (isdefault && _explorer.extopen.all[_explorer.extopen.user[ext]]) {
            return _explorer.extopen.user[ext];
        }
        for (i = 0; i < _explorer.extopen.ext[ext].length; i++) {
            if (_explorer.extopen.all[_explorer.extopen.ext[ext][i]]) {
                if (isdefault && _explorer.extopen.all[_explorer.extopen.ext[ext][i]].isdefault > 0) {
                    return _explorer.extopen.all[_explorer.extopen.ext[ext][i]].extid;
                }
                openarr.push(_explorer.extopen.all[_explorer.extopen.ext[ext][i]]);
            }
        }
    }
    if (data.ext && _explorer.extopen.ext[data.ext]) {
        if (isdefault && _explorer.extopen.all[_explorer.extopen.user[data.ext]]) {
            return _explorer.extopen.user[data.ext];
        }
        for (i = 0; i < _explorer.extopen.ext[data.ext].length; i++) {
            if (_explorer.extopen.all[_explorer.extopen.ext[data.ext][i]]) {
                if (isdefault && _explorer.extopen.all[_explorer.extopen.ext[data.ext][i]].isdefault > 0) {
                    return _explorer.extopen.all[_explorer.extopen.ext[data.ext][i]].extid;
                }
                openarr.push(_explorer.extopen.all[_explorer.extopen.ext[data.ext][i]]);
            }
        }
    }


    //判断type
    if (data.type !== data.ext && _explorer.extopen.ext[data.type]) {
        if (isdefault && _explorer.extopen.all[_explorer.extopen.user[data.type]]) {
            return _explorer.extopen.user[data.type];
        }
        for (i = 0; i < _explorer.extopen.ext[data.type].length; i++) {
            if (_explorer.extopen.all[_explorer.extopen.ext[data.type][i]]) {
                if (isdefault && _explorer.extopen.all[_explorer.extopen.ext[data.type][i]].isdefault > 0) {
                    return _explorer.extopen.all[_explorer.extopen.ext[data.type][i]].extid;
                }
                openarr.push(_explorer.extopen.all[_explorer.extopen.ext[data.type][i]]);
            }
        }
    }
    if (isdefault) {
        if (openarr.length > 0) {
            return openarr[0].extid;
        } else {
            return false;
        }
    } else {
        var appids = [];
        for (i in openarr) {
            if ($.inArray(openarr[i].appid, appids) > -1) {
                openarr.splice(i, 1);
            } else {
                appids.push(openarr[i].appid);
            }
        }
        if (openarr.length > 0) {
            return openarr;
        } else {
            return false;
        }
    }
}
//文件路径
function extopen_replace(ico, extid) {
    ico.icoid = ico.rid;
    var extdata = _explorer.extopen.all[extid];
    var extdata_url = '';
    if (!ico || !extdata) {
        return false;
    }
    for (var key in ico) {
        extdata_url = extdata.url.replace(/{(\w+)}/g, function ($1) {
            key = $1.replace(/[{}]/g, '');
            if (key === 'url') {
                return encodeURIComponent(ico[key]);
            } else if (key === 'icoid') {
                return ico.rid;
            } else if (key === 'path') {
                return ico.dpath;
            } else {
                return ico[key];
            }
        });
    }
    if (extdata_url.indexOf('dzzjs:') === -1 && extdata_url.indexOf('?') !== -1 && extdata_url.indexOf('path=') === -1) {
        extdata_url = extdata_url + '&path=' + ico.dpath;
    }
    return extdata_url;
}
_selectfile.prototype._selectInfo = function () {
    //设置全选框信息
    //设置全选按钮的文字
    var sum = _selectfile.selectall.icos.length;
    var total = jQuery('#filemanage-' + this.id).find('.Icoblock').length;
    var html = jQuery('#template_file').html();
    var hash = location.hash;
    if (sum > 0) { //有选中
        jQuery('.navtopheader').css('display', 'block');
        jQuery('.navtopheader').html(html);
        jQuery('.selectall-box').addClass('Icoselected');
        jQuery('.selectall-box .select-info').html('已选中<span class="num">' + sum + '</span>个文件');
        jQuery('.docunment-allfile').hide();
        if (sum >= total) { //全部选中
            jQuery('.selectall-box').addClass('Icoselected');
        }
    } else { //没有选中
        jQuery('.navtopheader').css('display', 'none');
        jQuery('.navtopheader').html('');
        jQuery('.selectall-box').removeClass('Icoselected');
        jQuery('.selectall-box .select-info').html(this.view < 4 ? '全选' : '');
        jQuery('.docunment-allfile').show();
        if (hash.indexOf('recycle') != -1) {
            jQuery('.recycle-option-icon').hide();
        }
    }
    //this.setToolButton(); //设置头部工具菜单；
    return false;
};
_selectfile.prototype.selectInfo = function () {
    var self = this;
    if (this.selectinfoTimer) {
        window.clearTimeout(this.selectinfoTimer);
    }
    this.selectinfoTimer = window.setTimeout(function () {
        self._selectInfo();
    }, 200);
};
_selectfile.prototype.appendIcos = function (data) {
    var self = this;
    if (_selectfile.showicosTimer[this.winid]) {
        window.clearTimeout(_selectfile.showicosTimer[this.winid]);
    }
    _selectfile.stack_data[self.winid] = Array();
    for (var i in data) {
        //this.data[i]=data[i];
        _selectfile.stack_data[self.winid].push({
            data: data[i],
            "obj": self
        });
    }
    window.setTimeout(function () {
        _selectfile.stack_run(self.winid);
    }, 1);
    this.pageloadding = false;
};

_selectfile.prototype.createIcosContainer = function () {
    var self = this;
    var containerid = 'filemanage-' + this.id;
    var div = document.getElementById(containerid);
    if (!div) {
        return;
    }
    div.className = "icosContainer";
    div.setAttribute('unselectable', "on");
    div.setAttribute('onselectstart', "return event.srcElement.type== 'text';");
    var htmlContent = '';
    div.innerHTML = _selectfile.get_template(this.id, true);
    _selectfile.select_Scrolly();
    var el = jQuery(div);
    el.find('.js-file-item-tpl').empty();
    jQuery('.middlecenter')
        .on('contextmenu', function (e) {
            e = e ? e : window.event;
            var tag = e.srcElement ? e.srcElement : e.target;
            if (/input|textarea/i.test(tag.tagName)) {
                return true;
            }
            _contextmenu.right_body(e, self.fid);
            return false;
        })
        .on('click', function (e) {
            //清空数据
            e = e ? e : window.event;
            var tag = e.srcElement ? e.srcElement : e.target;
            if (/input|textarea/i.test(tag.tagName)) {
                return true;
            }
            if (containerid === _selectfile.selectall.container) {
                _selectfile.selectall.container = containerid;
                _selectfile.selectall.icos = [];
                _selectfile.selectall.position = {};
                el.find('.Icoblock').removeClass('Icoselected');
                el.find('.selectall-box').removeClass('Icoselected');
                if(_explorer.type){
                    _selectfile.changefileName('');
                }
                self.selectInfo();
            }
        })
        .end().find('.selectall-box').on('click', function () {
        var el = jQuery(this);
        var selectall = true;
        if (el.hasClass('Icoselected')) {
            el.removeClass('Icoselected');
            selectall = false;
            _selectfile.selectall.icos = [];
        } else {
            el.addClass('Icoselected');
            selectall = true;
            _selectfile.selectall.icos = [];
        }
        _selectfile.selectall.container = containerid;
        jQuery('#' + containerid).find('.Icoblock').each(function () {
            if (selectall) {
                jQuery(this).addClass('Icoselected');
                _selectfile.selectall.icos.push(jQuery(this).attr('rid'));
            } else {
                jQuery(this).removeClass('Icoselected');
            }
        });
        self.selectInfo();
        return false;
    });
    if(_explorer.mulitype){
        _select.init(containerid);
    }
    if (this.view < 4) {
    } else {
        jQuery('#' + containerid).find('.detail_header:not(.detail_header_select)').on('click', function () {
            var disp = parseInt(jQuery(this).attr('disp'));
            if (disp * 1 === self.disp * 1) {
                if (self.asc > 0) {
                    self.asc = 0;
                } else {
                    self.asc = 1;
                }
            } else {
                _selectfile.Disp(this, self.id, disp);
                self.asc = 1;
            }
            self.disp = disp;
            if (self.fid) {
                _explorer.sourcedata.folder[self.fid].disp = disp;
            }
            if (self.bz.indexOf('ALIOSS') === 0 || self.bz.indexOf('JSS') === 0) {
                self.showIcos();
            } else {
                self.pageClick(1);
            }
        });
    }
    el.closest('.scroll-srcollbars').scroll(function () {
        var el = jQuery(this);
        if (el.height() + el.scrollTop() >= el.children().first().height()) {
            if (self.currentpage >= self.totalpage || self.pageloadding) {
                return;
            }
            self.pageloadding = true;
            self.currentpage++;
            self.pageClick(self.currentpage);
        }
    });
    if (this.fid) {
        $.getScript(MOD_PATH + '/scripts/uplodfile.js', function () {
            jQuery('.wangpan-upload-file').each(function () {
                fileupload(jQuery(this), self.fid);
            });
        });
    }
    if (this.total < 1 && jQuery('#' + containerid).find('.emptyPage').length == 0) {
        jQuery(jQuery('#template_nofile_notice').html()).appendTo(div);
    } else {
        jQuery('#' + containerid).find('.emptyPage').remove();
    }

}
_selectfile.prototype.pageClick = function (page) {
    var self = this;
    this.pageloadding = true;
    if (!page) {
        page = 1;
    }
    var keyword = (this.keyword) ? this.keyword : jQuery('#searchval').val();
    if (!keyword || keyword === __lang.search) {
        keyword = '';
    }
    if(!this.exts && _explorer.defaultexttype){
        this.exts = _explorer.defaultexttype;
    }
    var url = self.url
        .replace(/&disp\=\d/ig, '')
        .replace(/&asc\=\d/ig, '')
        .replace(/&iconview\=\d/ig, '')
        .replace(/&page\=\d+/ig, '')
        .replace(/&exts\=[\w,]*(&|$)/ig, '&')
        .replace(/&tags\=[\w,]*(&|$)/ig, '&')
        .replace(/&keyword\=\w*(&|$)/, '&')
        .replace(/&fid\=\w*(&|$)/, '&')
        .replace(/&gid\=\w*(&|$)/, '&')
        .replace(/&before\=\w*(&|$)/, '&')
        .replace(/&after\=\w*(&|$)/, '&')
        .replace(/&marker\=\w*(&|$)/, '&')
        .replace(/&t\=\d+/, '');
    url = url.replace(/&+$/ig, '');
    _selectfile.getData(url + '&exts=' + this.exts + '&tags=' + this.tags + '&disp=' + this.disp + '&fids=' + this.fids + '&gid=' + this.gid + '&before=' + this.before + '&after=' + this.after + '&asc=' + this.asc + '&iconview=' + this.view + '&keyword=' + encodeURI(keyword) + '&page=' + page + '&marker=' + (this.fid ? _explorer.sourcedata.folder[this.fid].nextMarker : '') + '&t=' + new Date().getTime(), function () {
        //self.PageInfo();
    });
};

_selectfile.stack_run = function (winid) {
    //if(_selectfile.showicosTimer[winid]) window.clearTimeout(_selectfile.showicosTimer[winid]);
    if (_selectfile.stack_data[winid].length > 0) {
        var obj = _selectfile.stack_data[winid][0].obj;
        for (var i = 0; i < _selectfile.speed; i++) {
            if (_selectfile.stack_data[winid].length > 0) {
                _selectfile.stack_data[winid][0].obj.CreateIcos(_selectfile.stack_data[winid][0]['data'], 1);
                _selectfile.stack_data[winid].splice(0, 1);
            } else break;
        }
        _selectfile.showicosTimer[winid] = window.setTimeout(function () {
            _selectfile.stack_run(winid);
        }, 1);
    } else {
        jQuery(document).trigger('showIcos_done');
    }
};
_selectfile.prototype.tddrager_start = function (e) {
    this.XX = e.clientX;
    document.getElementById('_blank').style.cursor = 'e-resize';
    jQuery('#_blank').show();
    this.AttachEvent(e);
    eval("document.onmousemove=function(e){" + this.string + ".tddraging(e?e:window.event);};");
    eval("document.onmouseup=function(e){" + this.string + ".tddraged(e?e:window.event);};");
};
_selectfile.prototype.tddraging = function () {
    document.body.style.cursor = 'e-resize';

};
_selectfile.prototype.tddraged = function (e) {
    this.DetachEvent(e);
    jQuery('#_blank').hide();
    var xx = e.clientX - this.XX;
    //计算新的各个td的百分比
    var right_width = _window.windows[this.winid].bodyWidth - jQuery('#jstree_area').width();
    var current_width = right_width * this.detailper[this.tddrager_disp] / 100;
    var width = xx + current_width;
    if (width < 50) {
        width = 50;
    }
    var all_width = [];
    var other_width = 0;
    for (var i = 0; i < 4; i++) {
        all_width[i] = right_width * this.detailper[i] / 100;
    }
    var dx = width - current_width;
    if (xx > 0) {
        if (all_width[this.tddrager_disp + 1] - dx > 50) {
            all_width[this.tddrager_disp + 1] -= dx;
        } else {
            var dx1 = dx + (all_width[this.tddrager_disp + 1] - 50);
            all_width[this.tddrager_disp + 1] = 50;
            if ((this.tddrager_disp + 1 + 1) < 4) {
                if (all_width[this.tddrager_disp + 1 + 1] - dx1 > 50) {
                    all_width[this.tddrager_disp + 1 + 1] -= dx;
                } else {
                    var dx2 = dx1 + (all_width[this.tddrager_disp + 1 + 1] - 50);
                    all_width[this.tddrager_disp + 1 + 1] = 50;
                    if ((this.tddrager_disp + 1 + 1 + 1) < 4) {
                        if (all_width[this.tddrager_disp + 1 + 1 + 1] - dx2 > 50) {
                            all_width[this.tddrager_disp + 1 + 1 + 1] -= dx;
                        } else {
                            all_width[this.tddrager_disp + 1 + 1 + 1] = 50;
                        }
                    }
                }
            }

        }
        other_width = 0;
        for (i = 0; i < 4; i++) {
            if (i !== this.tddrager_disp) {
                other_width += all_width[i];
            }
        }
        all_width[this.tddrager_disp] = right_width - other_width;
    } else {
        all_width[this.tddrager_disp] = width;
        all_width[this.tddrager_disp + 1] -= dx;
    }
    other_width = 0;
    for (i = 0; i < 4; i++) {
        if (i != this.tddrager_disp) {
            other_width += all_width[i];
        }
    }
    all_width[this.tddrager_disp] = right_width - other_width;
    for (i = 0; i < 4; i++) {
        this.detailper[i] = Math.floor((all_width[i] / right_width) * 100);
    }
    this.showIcos(this.winid);
    //alert(document.getElementById('tabs_cover').offsetLeft+'========='+document.getElementById('tabs_cover').offsetWidth);
};
_selectfile.prototype.DetachEvent = function () {
    document.onmousemove = _selectfile.onmousemove;
    document.onmouseup = _selectfile.onmouseup;
    document.onselectstart = _selectfile.onselectstart;


};
_selectfile.prototype.AttachEvent = function (e) {
    _selectfile.onmousemove = document.onmousemove;
    _selectfile.onmouseup = document.onmouseup;
    _selectfile.onselectstart = document.onselectstart;
    try {
        document.onselectstart = function () {
            return false;
        };
        if (e.preventDefault) {
            e.preventDefault();
        } else {
            if (this.board.setCapture) {
                this.board.setCapture();
            }
        }
    } catch (event) {

    }
};


_selectfile.Search = function (data, keyword) {
    var data1 = {};
    for (var i in data) {
        if (data[i].name.toLowerCase().indexOf(keyword.toLowerCase()) !== -1) {
            data1[i] = data[i];
        }
    }
    return data1;
};
_selectfile.Sort = function (data, disp, asc) {
    var sarr = [];
    if (!data) {
        return [];
    }
    for (var i in data) {

        switch (parseInt(disp)) {
            case 0:

                if (data[i].type === 'folder') {
                    sarr[sarr.length] = ' ' + data[i].name.replace(/_/g, '') + ' ___' + i;
                } else {
                    sarr[sarr.length] = data[i].name.replace(/_/g, '') + '___' + i;
                }
                break;
            case 1:
                sarr[sarr.length] = data[i].size + '___' + i;
                break;
            case 2:
                if (data[i].type === 'folder') {
                    sarr[sarr.length] = ' ' + '___' + i;
                } else {
                    sarr[sarr.length] = data[i].ext + data[i].type + '___' + i;
                }
                break;
            case 3:
                //asc=0;
                sarr[sarr.length] = (data[i].dateline) + '___' + i;
                break;
        }
    }
    if (parseInt(disp) === 1) {
        sarr = sarr.sort(function (a, b) {
            return (parseInt(a) - parseInt(b));
        });

    } else {
        sarr = sarr.sort();
    }
    var temp = {};
    var temp1 = '';
    if (asc > 0) {
        for (i = 0; i < sarr.length; i++) {
            temp1 = sarr[i].split('___');
            temp['icos_' + temp1[1]] = data[temp1[1]];
        }
    } else {
        for (i = sarr.length - 1; i >= 0; i--) {
            temp1 = sarr[i].split('___');
            temp['icos_' + temp1[temp1.length - 1]] = data[temp1[temp1.length - 1]];
        }
    }
    return temp;
};
_selectfile.get_template = function (sid, whole, disp, asc) {
    var obj = _selectfile.cons[sid];
    var str = '';
    if (whole) {
        switch (obj.view) {
            case 0:
            case 1:
            case 2:
            case 3:
                str = jQuery('#template_middleicon').html();

                break;
            case 4:
                str = jQuery('#template_detaillist').html();
                //替换
                break;
        }
        //替换参数
        str = str.replace(/\{asc_\d\}/g, obj.asc);
        var regx = new RegExp('\{show_' + obj.disp + '\}', 'ig');
        str = str.replace(regx, 'inline-block');
        str = str.replace(/\{show_\d}/ig, 'none');
    } else {
        switch (obj.view) {
            case 0:
            case 1:
            case 2:
            case 3:
                str = jQuery('#template_middleicon .js-file-item-tpl').html();
                break;
            case 4:
                str = jQuery('#template_detaillist .js-file-item-tpl').html();
                break;
        }
    }
    return str;

};
_selectfile.rename = function (id) {
    var ico = _explorer.sourcedata.icos[id];
    if (!ico) {
        return;
    }
    var filemanage = _selectfile.cons[_selectfile.winid];

    var el = jQuery('#file_text_' + id);
    el.css('overflow', 'visible');
    el.closest('td').addClass('renaming');
    jQuery('#Icoblock_middleicon_' + id).find('.IcoText_div').css('overflow', 'visible');
    filemanage.oldtext = el.html();
    var html = '';
    if (filemanage.view > 3) {
        html = "<input type='text' class='' name='text'  id='input_" + id + "' style=\"width:" + (el.closest('td').width() - 110) + "px;height:30px;padding:2px; \" value=\"" + filemanage.oldtext + "\">";
    } else {
        html = "<textarea type='textarea' class='textarea' name='text'  id='input_" + id + "' style=\"width:100%;height:30px;padding:2px;overflow:hidden;margin-top:3px;color:#666666 \">" + filemanage.oldtext + "</textarea>";
    }

    el.html(html);
    //jQuery('#content_'+filemanage.winid+' .icoblank[icoid="'+id+'"]').css('z-index',-1);
    var ele = jQuery('#input_' + id);
    ele.select();
    ele.on('keyup', function (e) {
        e = e ? e : event;
        if (e.keyCode === 13) {
            jQuery(document).trigger('mousedown.file_text_' + id);
        }
    });
    jQuery(document).on('mousedown.file_text_' + id, function (e) {
        e = e ? e : window.event;
        var obj = e.srcElement ? e.srcElement : e.target;
        if (jQuery(obj).closest('#file_text_' + id).length < 1) {
            jQuery(document).off('.file_text_' + id);
            var text = ele.val() || "";
            var emptymatch = /^\s*$/;
            if (emptymatch.test(text)) {
                top.showDialog(__lang.name_is_must, 'error', '', function () {
                    el.html(filemanage.oldtext);
                    el.css('overflow', 'hidden');
                    el.closest('td').removeClass('renaming');
                    jQuery('#Icoblock_middleicon_' + id).find('.IcoText_div').css('overflow', 'hidden');
                });
                return false;
            }
            text = text.replace("\n", '');
            if (filemanage.oldtext !== text) {
                _selectfile.Rename(id, text);
            } else {
                el.html(filemanage.oldtext);
                el.css('overflow', 'hidden');
                el.closest('td').removeClass('renaming');
                jQuery('#Icoblock_middleicon_' + id).find('.IcoText_div').css('overflow', 'hidden');
            }
            //jQuery('#content_'+filemanage.winid+' .icoblank[icoid="'+id+'"]').css('z-index',10);
        }
    });

};

_selectfile.Rename = function (rid, text) {
    var ico = _explorer.sourcedata.icos[rid];
    var filemanage = _selectfile.cons[_selectfile.winid];
    jQuery.ajax({
        type: 'post',
        url: _explorer.appUrl + '&do=dzzcp&operation=rename',
        data: {
            "text": text,
            "path": ico.dpath,
            "t": (new Date().getTime())
        },
        dataType: "json",
        success: function (json) {
            if (json.rid) {
                _explorer.sourcedata.icos[json.rid].name = json.name;
                filemanage.data[json.rid].name = json.name;
                filemanage.CreateIcos(_explorer.sourcedata.icos[json.rid], true);
            } else {
                jQuery('#file_text_' + rid).html(filemanage.oldtext);
                if (json.error) {
                    top.showmessage(json.error, 'danger', 3000, 1);
                }
            }
        },
        error: function () {
            jQuery('#file_text_' + rid).html(filemanage.oldtext);
            if (json.error) {
                top.showmessage(json.error, 'danger', 3000, 1);
            }
            top.showmessage(__lang.js_network_error, 'danger', 3000, 1);
        }
    });
};
_selectfile.downAttach = function (id) {
    if (!id) {
        id = _selectfile.selectall.icos[0];
    }
    var data = _explorer.sourcedata.icos[id];
    if (!data) {
        return false;
    }
    var url = DZZSCRIPT + '?mod=io&op=download&path=' + encodeURIComponent(data.dpath) + '&t=' + new Date().getTime();
    if (BROWSER.ie) {
        window.open(url);
    } else {
        window.frames.hideframe.location = url;
    }
    //}
    return false;
};
_selectfile.downThumb = function (id) {
    var data = _explorer.sourcedata.icos[id];
    var url = data.url + '&filename=' + encodeURIComponent(data.name) + '&a=down&t=' + new Date().getTime();
    if (BROWSER.ie) {
        window.open(url);
    } else {
        window.frames.hideframe.location = url;
    }
    //}
    return false;
};
_selectfile.property = function (rid, isfolder) {
    var path = '';
    if (isfolder) {
        var folder = _explorer.sourcedata.folder[rid];
        path = encodeURIComponent('fid_' + folder.path);
    } else {
        var dpaths = [];
        var ico = null;
        if (_selectfile.selectall.icos.length > 0 && jQuery.inArray(rid, _selectfile.selectall.icos) > -1) {
            for (var i = 0; i < _selectfile.selectall.icos.length; i++) {
                ico = _explorer.sourcedata.icos[_selectfile.selectall.icos[i]];
                dpaths.push(ico.dpath);
            }
        } else {
            ico = _explorer.sourcedata.icos[rid];
            dpaths = [ico.dpath];
        }
        path = encodeURIComponent(dpaths.join(','));
    }
    showWindow('property', _explorer.appUrl + '&do=ajax&operation=property&paths=' + path);
};
_selectfile.NewIco = function (type, fid) {
    if (!fid && !_selectfile.fid) {
        return;
    }
    if (!fid) {
        fid = _selectfile.fid;
    }
    if (type === 'newFolder') {
//        showWindow('newFolder', _explorer.appUrl + '&do=ajax&operation=' + type + '&fid=' + fid);
		  $.post(_explorer.appUrl + '&do=ajax&operation=newFolder',{'fid':fid},function(data){
		  	if(data.msg === 'success'){
		  		_explorer.sourcedata.icos[data.rid] = data;
                _selectfile.cons['f-' + fid].CreateIcos(data);
                _selectfile.rename(data.rid);
		  	}else{
		  		top.showDialog(data.error);
		  	}
		  },'json');
    } else if (type === 'newLink') {
        showWindow('newLink', _explorer.appUrl + '&do=ajax&operation=' + type + '&fid=' + fid);
    } else {
        $.post(_explorer.appUrl + '&do=ajax&operation=newIco&type=' + type, {
            'fid': fid
        }, function (data) {
            if (data.msg === 'success') {
                _explorer.sourcedata.icos[data.rid] = data;
                _selectfile.cons['f-' + fid].CreateIcos(data);
                _selectfile.rename(data.rid);
            } else {
                top.showDialog(data.error);
            }
        }, 'json');
    }
};
_selectfile.glow = function (el) {
    var delay = 200;
    for (var i = 0; i < 4; i++) {
        window.setTimeout(function () {
            el.find('.toggleGlow').toggleClass('glow');
        }, delay * i);
    }
};
_selectfile.Arrange = function (obj, id, view) {
    var el = jQuery(obj);
    if (!id) {
        id = _selectfile.winid;
    }
    var filemanage = _selectfile.cons[id];

    if (!view) {
        view = (parseInt(el.attr('iconview')) < 4 ? 4 : 2);
    } else {
        view = view * 1;
    }
    jQuery('.icons-thumbnail').attr('iconview', view).find('.dzz').removeClass('dzz-view-module').removeClass('dzz-view-list').addClass(view === 2 ? 'dzz-view-list' : 'dzz-view-module');
    jQuery('.icons-thumbnail').attr('iconview', view).find('.dzz').attr('data-original-title', view === 2 ? __lang.deltail_lsit : __lang.medium_icons);
    var fid = _selectfile.fid;
    if (fid > 0 && _explorer.Permission_Container('admin', fid)) {
        jQuery.post(_selectfile.saveurl + '&do=folder', {
            fid: fid,
            iconview: view
        });
        _explorer.sourcedata.folder[fid]['iconview'] = view;

    }
    filemanage.view = view;
    filemanage.showIcos();
    jQuery('#right_contextmenu .menu-icon-iconview').each(function () {
        if (jQuery(this).attr('view') * 1 === view * 1) {
            jQuery(this).removeClass('dzz-check-box-outline-blank').addClass('dzz-check-box');
        } else {
            jQuery(this).addClass('dzz-check-box-outline-blank').removeClass('dzz-check-box');
        }
    });
};
_selectfile.Disp = function (obj, id, disp) {
    var filemanage = _selectfile.cons[id];
    if (filemanage.subfix === 'f') {
        var fid = filemanage.fid;
        if (fid > 0 && _explorer.Permission_Container('admin', fid)) {
            jQuery.post(_selectfile.saveurl + '&do=folder', {
                fid: fid,
                disp: parseInt(disp)
            });
        }
        _explorer.sourcedata.folder[fid]['disp'] = parseInt(disp);
    } else if (filemanage.subfix === 'cat') {
        jQuery.post(_selectfile.saveurl + '&do=catsearch', {
            catid: id.replace('cat-', ''),
            disp: parseInt(disp)
        });
    }
    if (disp * 1 === filemanage.disp * 1) {
        filemanage.asc = filemanage.asc > 0 ? 0 : 1;
    }
    filemanage.disp = parseInt(disp);
    if (filemanage.bz.indexOf('ALIOSS') === 0 || filemanage.bz.indexOf('JSS') === 0) {
        filemanage.showIcos();
    } else {
        filemanage.pageClick(1);
    }
    jQuery('#right_contextmenu .menu-icon-disp').each(function () {
        if (jQuery(this).attr('disp') * 1 === disp * 1) {
            jQuery(this).removeClass('dzz-check-box-outline-blank').addClass('dzz-check-box');
            jQuery(this).next().find('.caret').removeClass('asc').removeClass('desc').addClass(filemanage.asc > 0 ? 'asc' : 'desc');
        } else {
            jQuery(this).addClass('dzz-check-box-outline-blank').removeClass('dzz-check-box');
            jQuery(this).next().find('.caret').removeClass('asc').removeClass('desc');
        }
    });
};
//文件复制
_selectfile.copy = function (rid) {
    if (!rid) {
        rid = _selectfile.selectall.icos[0];
    }
    var icosdata = _explorer.sourcedata.icos[rid];
    var path = [];
    var data = {};
    if (_selectfile.selectall.icos.length > 0 && jQuery.inArray(rid, _selectfile.selectall.icos) > -1) {
        if (icosdata.bz && icosdata.bz) {
            for (var i in _selectfile.selectall.icos) {
                path.push(_explorer.sourcedata.icos[_selectfile.selectall.icos[i]].dpath);
            }
            data = {
                rids: path,
                'bz': icosdata.bz
            };
        } else {
            for (var i in _selectfile.selectall.icos) {
                path.push(_explorer.sourcedata.icos[_selectfile.selectall.icos[i]].dpath);
            }
            data = {
                rids: path
            };
        }
    } else {
        if (icosdata.bz && icosdata.bz) {
            data = {
                rids: [icosdata.dpath],
                'bz': icosdata.bz
            };
        } else {
            data = {
                rids: [icosdata.dpath]
            };
        }
    }
    //复制类型值为1，剪切类型值为2
    data.copytype = 1;
    var url = _explorer.appUrl + '&do=dzzcp&operation=copyfile&t=' + new Date().getTime();
    jQuery.post(url, data, function (json) {
        if (json.msg === 'success') {
            var filenames = '';
            _explorer.cut.iscut = 0;
            _explorer.cut.icos = json.rid;
            for (var o in json['rid']) {
                jQuery('.Icoblock[rid=' + json.rid[o] + ']').removeClass('iscut');
                filenames += _explorer.sourcedata.icos[json.rid[o]].name + ',';
            }
            filenames = filenames.substr(0, filenames.length - 1);
            top.showmessage(filenames + __lang.copy_success, 'success', 1000, 1, 'right-bottom');
        } else {
            top.showmessage(json.msg, 'error', 3000, 1, 'right-bottom');
        }


    }, 'json');
};
//文件剪切
_selectfile.cut = function (rid) {
    var filemanage = _selectfile.cons[_selectfile.winid];
    var containid = 'filemanage-' + _selectfile.winid;
    var total = filemanage.total;
    if (!rid) {
        rid = _selectfile.selectall.icos[0];
    }
    var icosdata = _explorer.sourcedata.icos[rid];
    var path = [];
    var data = {};
    if (_selectfile.selectall.icos.length > 0 && jQuery.inArray(rid, _selectfile.selectall.icos) > -1) {
        if (icosdata.bz && icosdata.bz) {
            for (var i in _selectfile.selectall.icos) {
                path.push(_explorer.sourcedata.icos[_selectfile.selectall.icos[i]].dpath);
            }
            data = {
                rids: path,
                'bz': icosdata.bz
            };
        } else {
            for (var i in _selectfile.selectall.icos) {
                path.push(_explorer.sourcedata.icos[_selectfile.selectall.icos[i]].dpath);
            }
            data = {
                rids: path
            };
        }
    } else {
        if (icosdata.bz && icosdata.bz) {
            data = {
                rids: [icosdata.dpath],
                'bz': icosdata.bz
            };
        } else {
            data = {
                rids: [icosdata.dpath]
            };
        }
    }
    //复制类型值为1，剪切类型值为2
    data.copytype = 2;
    var url = _explorer.appUrl + '&do=dzzcp&operation=copyfile';
    jQuery.post(url, data, function (json) {
        if (json.msg === 'success') {
            var filenames = '';
            _explorer.cut.iscut = 1;
            _explorer.cut.icos = json.rid;
            jQuery('.Icoblock').removeClass('iscut');
            for (var o in json.rid) {
                jQuery('.Icoblock[rid=' + json.rid[o] + ']').addClass('iscut');
                filenames += _explorer.sourcedata.icos[json.rid[o]].name + ',';
                total--;
            }
            // _selectfile.showTemplatenoFile(containid, total);
            filenames = filenames.substr(0, filenames.length - 1);
            top.showmessage(filenames + __lang.cut_success, 'success', 1000, 1, 'right-bottom');
        } else {
            top.showmessage(json.msg, 'error', 3000, 1, 'right-bottom');
        }

    }, 'json');
};
//粘贴
_selectfile.paste = function (fid) {
    var folder = _explorer.sourcedata.folder[fid];
    if (!folder) {
        return false;
    }
    var data = {
        'tpath': folder.fid,
        'tbz': folder.bz
    };
    var url = _explorer.appUrl + '&do=dzzcp&operation=paste';
    var i = 0;
    var node = null;
    jQuery.post(url, data, function (json) {
        if (fid === _selectfile.fid) {
            if (json.folderarr) {
                for (i = 0; i < json.folderarr.length; i++) {
                    _explorer.sourcedata.folder[json.folderarr[i].fid] = json.folderarr[i];
                }
                node = jQuery('#position').jstree(true).get_node(folder.gid > 0 ? (folder.type > 0 ? '#g_' + folder.gid : '#gid_' + folder.gid) : '#f-' + folder.pfid);
                jQuery('#position').jstree('refresh', node);
                jQuery('#position').jstree('correct_state', node);
            }
            if (json.icoarr) {
                var filemanage = _selectfile.cons['f-' + fid];
                for (i = 0; i < json.icoarr.length; i++) {
                    if (json.icoarr[i].pfid === filemanage.fid) {
                        _explorer.sourcedata.icos[json.icoarr[i].rid] = json.icoarr[i];
                        filemanage.CreateIcos(json.icoarr[i]);
                    }
                }
            }
        } else {
            top.showmessage('粘贴成功', 'success', 3000, 1);
        }
    }, 'json');

};
_selectfile.delIco = function (rid, noconfirm) {
    var filemanage = _selectfile.cons[_selectfile.winid];
    var containid = 'filemanage-' + _selectfile.winid;
    var total = filemanage.total;
    if (!rid) {
        rid = _selectfile.selectall.icos[0];
    }
    var icosdata = _explorer.sourcedata.icos[rid];
    if (!noconfirm) {
        //var finallydelete = (_explorer.deletefinally == 1) ? true:false;
        var finallydelete = false;
        if (_selectfile.selectall.icos.length > 0 && jQuery.inArray(rid, _selectfile.selectall.icos) > -1) {
            if (_explorer.sourcedata.icos[_selectfile.selectall.icos[0]].isdelete > 0 || (_explorer.sourcedata.icos[_selectfile.selectall.icos[0]].bz && _explorer.sourcedata.icos[_selectfile.selectall.icos[0]].bz)) {
                top.showDialog((finallydelete) ? __lang.js_finallydelete_selectall : __lang.js_delete_selectall,'confirm','' ,function () {
                    _selectfile.delIco(rid, 1);
                });
            } else {
                top.showDialog((finallydelete) ? __lang.js_finallydelete_selectall_recycle : __lang.js_delete_selectall_recycle,'confirm','', function () {
                    _selectfile.delIco(rid, 1);
                });
            }
            return;
        } else if (_explorer.sourcedata.icos[rid].type === 'folder' && _explorer.sourcedata.folder[_explorer.sourcedata.icos[rid].oid] && _explorer.sourcedata.folder[_explorer.sourcedata.icos[rid].oid].iconum) {
            if (_explorer.sourcedata.icos[rid].isdelete > 0 || (_explorer.sourcedata.icos[rid].bz && _explorer.sourcedata.icos[rid].bz)) {
                top.showDialog((finallydelete) ? __lang.js_finallydelete_folder.replace('{name}', _explorer.sourcedata.icos[rid].name) : __lang.js_delete_folder.replace('{name}', _explorer.sourcedata.icos[rid].name),'confirm','', function () {
                    _selectfile.delIco(rid, 1);
                });
            } else {
                top.showDialog((finallydelete) ? __lang.js_finallydelete_folder_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name) : __lang.js_delete_folder_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name),'confirm','', function () {
                    _selectfile.delIco(rid, 1);
                });
            }
            return;
        } else {
            if (_explorer.sourcedata.icos[rid].isdelete > 0 || (_explorer.sourcedata.icos[rid].bz && _explorer.sourcedata.icos[rid].bz)) {
                top.showDialog((finallydelete) ? __lang.js_finallydelete_confirm.replace('{name}', _explorer.sourcedata.icos[rid].name) : __lang.js_delete_confirm.replace('{name}', _explorer.sourcedata.icos[rid].name),'confirm','' ,function () {
                    _selectfile.delIco(rid, 1);
                });
            } else {
                top.showDialog((finallydelete) ? __lang.js_finallydelete_confirm_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name) : __lang.js_delete_confirm_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name),'confirm','', function () {
                    _selectfile.delIco(rid, 1);
                });
            }
            return;
        }
    }
    var path = [];
    var data = {};
    if (_selectfile.selectall.icos.length > 0 && jQuery.inArray(rid, _selectfile.selectall.icos) > -1) {
        if (icosdata.bz && icosdata.bz) {

            for (var i in _selectfile.selectall.icos) {
                path.push(_explorer.sourcedata.icos[_selectfile.selectall.icos[i]].dpath);
            }
            data = {
                rids: path,
                'bz': icosdata.bz
            };
        } else {

            for (var i in _selectfile.selectall.icos) {
                path.push(_explorer.sourcedata.icos[_selectfile.selectall.icos[i]].dpath);
            }
            data = {
                rids: path
            };
        }
    } else {
        if (icosdata.bz && icosdata.bz) {
            data = {
                rids: [icosdata.dpath],
                'bz': icosdata.bz
            };
        } else {
            data = {
                rids: [icosdata.dpath]
            };
        }
    }
    var url = _explorer.appUrl + '&do=dzzcp&operation=deleteIco&t=' + new Date().getTime();
    var progress = '<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>';
    top.showmessage('<p>' + __lang.deleting_not_please_close + '</p>' + progress, 'success', 0, 1, 'right-bottom');
    jQuery.post(url, data, function (json) {
        var rids = [];
        for (var i in json.msg) {
            if (json.msg[i] === 'success') {
                top.showmessage(_explorer.sourcedata.icos[i].name + __lang.delete_success, 'success', 1000, 1, 'right-bottom');
                //_selectfile.removerid(i);
                rids.push(i);
                total--;
                //_selectfile.showTemplatenoFile(containid, total);

            } else {
                top.showmessage(json.msg[i], 'error', 3000, 1, 'right-bottom');
            }
        }
        _selectfile.removeridmore(rids);

    }, 'json');
};
_selectfile.removerid = function (rid) {
    var data = _explorer.sourcedata.icos[rid];
    var containerid = 'filemanage-' + _selectfile.winid;
    var el = jQuery('#' + containerid + ' .Icoblock[rid=' + rid + ']');
    el.remove();
    if (data.type === 'folder') {
        var node = jQuery('#position').jstree(true).get_node(data.gid > 0 ? (data.type > 0 ? '#g_' + data.gid : '#gid_' + data.gid) : '#f-' + data.oid);
        jQuery('#position').jstree('refresh', node);
        jQuery('#position').jstree('correct_state', node);
    }
    var filemanage = _selectfile.cons[_selectfile.winid];
    //删除选中列表
    var i = jQuery.inArray(rid, _selectfile.selectall.icos);
    if (i > -1) {
        _selectfile.selectall.icos.splice(i, 1);
    }
    delete filemanage.data[rid];
    delete filemanage.currentdata['icos_' + rid];
    filemanage.sum--;
    filemanage.total--;
    filemanage.selectInfo();
    _selectfile.stack_run(filemanage.winid); //删除时如果有未显示的接着显示


};
_selectfile.removeridmore = function (rids) {
    if (rids.length > 1) {
        var rid = rids[0];
        var data = _explorer.sourcedata.icos[rid];
        var containerid = 'filemanage-' + _selectfile.winid;
        var types = [];
        for (var o in rids) {
            var currentrid = rids[o];
            jQuery('#' + containerid + ' .Icoblock[rid=' + currentrid + ']').remove();
            var currentdata = _explorer.sourcedata.icos[currentrid];
            types.push(currentdata.type);
        }
        if ($.inArray('folder', types) != -1) {
            var node = jQuery('#position').jstree(true).get_node(data.gid > 0 ? (data.type > 0 ? '#g_' + data.gid : '#gid_' + data.gid) : '#f-' + data.oid);
            jQuery('#position').jstree('refresh', node);
            jQuery('#position').jstree('correct_state', node);
        }
        var filemanage = _selectfile.cons[_selectfile.winid];
        for (var o in rids) {
            var currentrid = rids[o];
            //删除选中列表
            var i = jQuery.inArray(currentrid, _selectfile.selectall.icos);
            if (i > -1) {
                _selectfile.selectall.icos.splice(i, 1);
            }
            delete filemanage.data[currentrid];
            delete filemanage.currentdata['icos_' + currentrid];
            filemanage.sum--;
            filemanage.total--;
            filemanage.selectInfo();
            _selectfile.stack_run(filemanage.winid); //删除时如果有未显示的接着显示
        }

    } else {
        _selectfile.removerid(rids[0]);
    }

}
