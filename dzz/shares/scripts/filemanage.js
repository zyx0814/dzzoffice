/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
//添加目录树的右键
var shareinfo = '';
function _filemanage(id, data, param) {
	var page = isNaN(parseInt(param.page)) ? 1 : parseInt(param.page);
	var total = isNaN(parseInt(param.total)) ? 1 : parseInt(param.total);
	this.total = total;
	//alert('filemangeid='+id);
	this.bz = param.bz || ''; //标志是那个api的数据

	this.perpage = param.perpage || _filemanage.perpage;
	this.totalpage = Math.ceil(this.total / this.perpage);
	this.totalpage = this.totalpage < 1 ? 1 : this.totalpage;
	this.id = id;
	this.string = "_filemanage.cons." + this.id;
	//alert(this.id);
	var fidarr = id.split('-');
	if (fidarr[0] == 'f') this.fid = fidarr[1];
	else this.fid = 0;
	this.subfix = fidarr[0]; //记录当前的fid前缀 f、cat等
	this.winid = id;

	this.view = isNaN(parseInt(param.view)) ? _filemanage.view : parseInt(param.view);
	this.disp = isNaN(parseInt(param.disp)) ? _filemanage.disp : parseInt(param.disp);

	this.asc = param.asc; //_filemanage.asc;
	this.detailper = _filemanage.detailper;
	if (!this.data) this.data = {};
	this.data = data;
	this.currentpage = page;
	this.container = param.container;
	this.odata = [];
	this.sum = 0;
	_filemanage.cons[this.id] = this;
	_filemanage.fid = this.fid;
	_filemanage.subfix = this.subfix;
	_filemanage.winid = this.id;
	_filemanage.param = param;
	this.pageloadding = true;

}
_filemanage.selectall = {
	position: {},
	container: '',
	icos: []
};
_filemanage.saveurl = 'index.php?mod=system&op=save';
_filemanage.speed = 5;
_filemanage.perpage = 100; //每页最多个数；
_filemanage.cons = {};
_filemanage.view = 4;
_filemanage.disp = 0;
_filemanage.asc = 1;
_filemanage.detailper = [47, 10, 20, 15, 8]; //依此对应：名称  大小  类型  修改时间；
_filemanage.onmousemove = null;
_filemanage.onmouseup = null;
_filemanage.onselectstart = 1;
_filemanage.stack_data = {};
_filemanage.showicosTimer = {};
_filemanage.infoPanelUrl = '';
_filemanage.viewstyle = ['middleicon','detaillist'];
_filemanage.getData = function (url, callback) {
	var l = $('#middleconMenu').lyearloading({
        opacity           : 0,
		spinnerSize       : 'lg',
		textColorClass    : 'text-info',
		spinnerColorClass : 'text-info',
		spinnerText       : '加载中...',
    });
	_filemanage.selectall.icos = [];
	jQuery.getJSON(url, function (json) {
		l.destroy();
		jQuery('.navtopheader').css('display', 'none');
		jQuery('.tooltip').html('');
		if (json.error == 'no_login') {
			jQuery('#filemanage-f-1').html('<div class="emptyPage" id="noticeinfo"><img src="static/image/common/noFilePage-fail.png"><p class="emptyPage-text">该分享文件需要登录才能查看，请先进行登录</p><a class="btn btn-primary" href="user.php?mod=login"><i class="mdi mdi-login p-2"></i><span>登录</span></a></div>');
			jQuery('.allsave,.downAll,.new-buildMenu,.icons-thumbnail').hide();
			return false;
		} else if (json.error){
			jQuery('#filemanage-f-1').html('<div class="emptyPage" id="noticeinfo"><img src="static/image/common/no_list.png"><p class="emptyPage-text">'+json.error+'</p></div>');
			showmessage(json.error, 'danger', 3000, 1);
			jQuery('.allsave,.downAll,.new-buildMenu,.icons-thumbnail').hide();
			return false;
		} else if (json.password){
			jQuery('#filemanage-f-1').html('<div class="container"><div class="card shadow m-3"><div class="card-header">'+json.password.avatar+' <strong>'+json.password.username+'</strong> <span class="ml10">给您加密分享了文件</span></div><div class="card-body"><div class="row mb-3"><label class="col-sm-2" for="passwords">请输入提取密码</label><div class="col-sm-10"><input type="password" class="form-control passwords" id="passwords" name="password" placeholder="请填写提取密码" value=""></div></div></div><div class="card-footer d-grid"><button type="button" class="btn btn-primary btn-round bodyloading" onclick="passwordsubmit()" value="true">提取文件</button></div></div></div>');
			jQuery('.allsave,.downAll,.new-buildMenu,.icons-thumbnail').hide();
			return false;
		} else {
			if (json.error){
				showmessage(json.error, 'danger', 3000, 1);
			}
			for (var id in json.data) {
				_explorer.sourcedata.icos[id] = json.data[id];
			}
			for (var fid in json.folderdata) {
				_explorer.sourcedata.folder[fid] = json.folderdata[fid];
			}
			if (json.foldername) {
				function updateBreadcrumb(isHome) {
					var dataContainer = '<div class="hide breadcrumb-data"><li class="' + (isHome ? 'home ' : '') + 'active" data-fid="'+json.folderid+'">'+json.foldername+'<span>></span></li></div>';
					_explorer.fids = json.folderid;
					jQuery('#dataContainer').html(dataContainer);
					jQuery(jQuery('#dataContainer').find('.breadcrumb-data:first').html()).insertAfter(jQuery('.breadcrumb li').last());
					jQuery('.breadcrumb li:not(:last)').each(function () {
						jQuery(this).removeClass('active');
						jQuery(this).html('<a href="javascript:;">' + jQuery(this).html() + '</a>');
					});
					jQuery('#dataContainer').empty();
				}
				if(_explorer.fid) {
					if(json.folderid !== _explorer.fids){
						updateBreadcrumb(false);
					}
				} else {
					updateBreadcrumb(true);
				}
			}
			jQuery('.listchange').show();
			var obj = null;
			//判断新建和上传图标显示
			var createperm = json.param.create;
			if (createperm) { //如果没有文件夹权限和文件权限，隐藏新建上传菜单
				jQuery('.new-buildMenu').show();
			}else{
				jQuery('.new-buildMenu').hide();
			}
			if (!shareinfo && json.share) {
				shareinfo = json.share;
				jQuery('#expireday').html(shareinfo.expireday);
				jQuery('.share-title').html(shareinfo.title);
				jQuery('#sharefdateline').html(shareinfo.fdateline);
				jQuery('.shareusername').html(shareinfo.username+'的分享');
				jQuery('#shareviews').html(shareinfo.views);
				jQuery('#sharedowns').html(shareinfo.downs);
				jQuery('.file-icon').html('<img src="'+shareinfo.img+'">');
			}
			if (json.param.page > 1) {
				obj = _filemanage.cons[json.fid];
				obj.appendIcos(json.data);
				obj.total = parseInt(json.total);
				obj.totalpage = Math.ceil(obj.total / obj.perpage);
			} else {
				obj = new _filemanage(json.fid, json.data, json.param);
				if (_filemanage.selectall.container !== 'filemanage-' + json.fid) {
					_filemanage.selectall = {
						position: {},
						container: '',
						icos: []
					};
					obj.selectInfo();
				}
				obj.showIcos();
			}
			jQuery('#sharepage').html('共' + obj.totalpage + '页(' + obj.total + '条记录),' + obj.perpage + '条/页');
			obj.url = url;
			//修改初始化时的排列方式指示
			jQuery('.sizeMenu .icons-thumbnail').attr('iconview', obj.view).find('.mdi').removeClass('mdi-view-module').removeClass('mdi-view-list').addClass(obj.view === 2 ? 'mdi-view-list':'mdi-view-module');
            jQuery('.sizeMenu .icons-thumbnail').attr('iconview', obj.view).find('.mdi').attr('data-original-title',obj.view === 2 ? __lang.deltail_lsit : __lang.medium_icons);
			jQuery('.sizeMenu .icons-thumbnail').attr('folderid', obj.id);
			if (typeof (callback) === 'function') {
				callback(obj);
			}
		}
	}).fail(function(jqxhr, textStatus, error) {
		l.destroy();
		jQuery('#filemanage-f-1').html(jqxhr.responseText);
		jQuery('.allsave,.downAll,.new-buildMenu,.icons-thumbnail').hide();
		return false;
	});
};
_filemanage.glow = function (el) {
	var delay = 200;
	for (var i = 0; i < 4; i++) {
		window.setTimeout(function () {
			el.find('.toggleGlow').toggleClass('glow');
		}, delay * i);
	}
};
_filemanage.Arrange = function (obj, id, view) {
	var el = jQuery(obj);
	if (!id) {
		id = el.attr('folderid');
	}
	var filemanage = _filemanage.cons[id];

	if (!view) {
		view = (parseInt(el.attr('iconview')) < 4 ? 4 : 2);
	} else {
		view = view * 1;
	}
	jQuery('.sizeMenu .icons-thumbnail').attr('iconview', view).find('.mdi').removeClass('mdi-view-module').removeClass('mdi-view-list').addClass(view === 2 ?  'mdi-view-list':'mdi-view-module');
    jQuery('.sizeMenu .icons-thumbnail').attr('iconview', view).find('.mdi').attr('data-original-title',view === 2 ? __lang.deltail_lsit : __lang.medium_icons);
	if (filemanage.subfix === 'f') {
		var fid = _filemanage.fid;
		_explorer.sourcedata.folder[fid]['iconview'] = view;
	}
	filemanage.view = view;
	filemanage.showIcos();
	jQuery('span .menu-icon-iconview').each(function () {
		if (jQuery(this).attr('view') * 1 === view * 1) {
			jQuery(this).removeClass('mdi-checkbox-blank-outline').addClass('mdi-checkbox-marked');
		} else {
			jQuery(this).addClass('mdi-checkbox-blank-outline').removeClass('mdi-checkbox-marked');
		}
	});
};
_filemanage.Disp = function (obj, id, disp) {
	var filemanage = _filemanage.cons[id];
	if (filemanage.subfix === 'f') {
		var fid = filemanage.fid;
		_explorer.sourcedata.folder[fid]['disp'] = parseInt(disp);
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
	jQuery('span .menu-icon-disp').each(function () {
		if (jQuery(this).attr('disp') * 1 === disp * 1) {
			jQuery(this).removeClass('mdi-checkbox-blank-outline').addClass('mdi-checkbox-marked');
			jQuery(this).nextAll('.caret').first().removeClass('asc').removeClass('desc').addClass(filemanage.asc > 0 ? 'asc' : 'desc');
		} else {
			jQuery(this).addClass('mdi-checkbox-blank-outline').removeClass('mdi-checkbox-marked');
			jQuery(this).nextAll('.caret').first().removeClass('asc').removeClass('desc');
		}
	});
};

/*
view: 图标排列方式：0:大图标，1：中图标，2：中图标列表，3小图标列表,4:详细
disp：图标排列顺序：0：原始顺序:按名称；1：按大小；2：按类型；3：按时间
asc :升序或降序：0：升序；1：降序
*/

_filemanage.prototype.CreateIcos = function (data, flag) {
	var self = this;
	var containerid = 'filemanage-' + this.winid;
	if (!flag && this.data[data.rid]) { //如果已经存在
		var el1 = jQuery('#' + containerid + ' .Icoblock[rid=' + data.rid + ']');
		_filemanage.glow(el1);
		return;
	}
	this.data[data.rid] = data;
	var template = _filemanage.get_template(this.id);
	//创建图标列表
	if (data.flag) {
        if (!data.img) {
            data.img = 'dzz/images/default/system/' + data.flag + '.png';
        }
        data.error = 'dzz/images/default/system/' + data.flag + '.png';
    } else if (data.type === 'folder') {
        data.icon = data.img ? data.img : data.icon;
        data.error = data.icon || 'dzz/images/default/system/folder.png';
        data.img = data.icon ? data.icon : 'dzz/images/default/system/folder.png';
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
	html = html.replace(/\{fsize\}/g,((data.type === 'folder' || data.type === 'app' || data.type === 'shortcut') ? '': data.fsize));
	html = html.replace(/\{type\}/g, data.type);
	html = html.replace(/\{ftype\}/g, data.ftype);
	html = html.replace(/\{dateline\}/g, data.dateline);
	html = html.replace(/\{fdateline\}/g, data.fdateline);
	html = html.replace(/\{flag\}/g, data.flag);
	html = html.replace(/\{dpath\}/g, data.dpath);
	html = html.replace(/\{from\}/g, data.from);
	html = html.replace(/\{delusername\}/g, data.username);
	html = html.replace(/\{deldateline\}/g, data.deldateline);
	html = html.replace(/\{finallydate\}/g, data.finallydate);
	html = html.replace(/\{views\}/g, data.views);
	html = html.replace(/\{times\}/g, data.times);
	html = html.replace(/\{downs\}/g, data.downs);
	html = html.replace(/\{expireday\}/g, data.expireday);
	html = html.replace(/dsrc=\"\{qrcode\}\"/g, 'src="{qrcode}"');
	html = html.replace(/dsrc='\{qrcode\}'/g, "src='{qrcode}'");
	html = html.replace(/\{qrcode\}/g, data.qrcode);
	html = html.replace(/\{password\}/g, data.password);
	html = html.replace(/\{count\}/g, data.count);
	if (data.type !== 'image') {
		html = html.replace(/data-start=\"image\".+?data-end=\"image\"/ig, '');
	}
	var position_hash = '#home&do=filelist&fid=' + data.pfid;
	html = html.replace(/\{position_hash\}/g, position_hash);
	//处理操作按钮
	//html=this.filterOPIcon(data,html);
	var el = null;
	if (flag && jQuery('.Icoblock[rid=' + data.rid + ']').length > 0) {
		jQuery('.Icoblock[rid=' + data.rid + ']').replaceWith(html);
		el = jQuery('.Icoblock[rid=' + data.rid + ']')

	} else {
		jQuery(html).appendTo('#' + containerid + ' .js-file-item-tpl');
		el = jQuery('.Icoblock[rid=' + data.rid + ']');
		jQuery('#shareinfo_' + data.rid).on('click', function (e) {
			return false;
		});

	}

	//检查下载和分享菜单
	//判断下载权限
	if (!_filemanage.param.download) {
		el.find('.download').remove();
		jQuery('.downAll').hide();
		jQuery('.allsave').hide();
	}else {
		jQuery('.allsave').show();
        jQuery('.downAll').show();
    }

	if (this.view < 4) {

		el.on('mouseenter', function () {
			jQuery(this).addClass('hover');

		});
		el.on('mouseleave', function () {
			jQuery(this).removeClass('hover');

		});
		el.find('.icoblank_righttop').on('click', function () {
			var flag = true;
			var ell = jQuery(this).parent();
			var rid = el.attr('rid');
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
			var flag = true;
			if ((_hotkey.ctrl && Item.hasClass('Icoselected')) || (Item.hasClass('Icoselected') && _filemanage.selectall.icos.length === 1 && _filemanage.selectall.icos[0] === rid)) {
				flag = false;
			}
			var multi = _hotkey.ctrl ? true : false;
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
			var flag = true;
			if ((_hotkey.ctrl && Item.hasClass('Icoselected')) || (Item.hasClass('Icoselected') && _filemanage.selectall.icos.length === 1 && _filemanage.selectall.icos[0] === rid)) {
				flag = false;
			}
			var multi = _hotkey.ctrl ? true : false;
			_select.SelectedStyle('filemanage-' + self.id, Item.attr('rid'), flag, multi);
			//self.createBottom();
			return false;
		});
		el.find('.selectbox').on('click', function () {
			var flag = true;
			var ell = jQuery(this).closest('.Icoblock');
			var rid = ell.attr('rid');
			if (ell.hasClass('Icoselected')) {
				flag = false;
			}
			_select.SelectedStyle('filemanage-' + self.id, rid, flag, true);
			return false;
		});

	}
	el.on('dblclick', function (e) {
		if(!_filemanage.fid && (_filemanage.winid == 'recycle-list')) return true;
		var tag = e.srcElement ? e.srcElement : e.target;
		if (/input|textarea/i.test(tag.tagName)) {
			return true;
		}
		_filemanage.Open(el.attr('rid'));
		dfire('click');
		return false;
	});
	//设置右键菜单
	el.on('contextmenu', function (e) {
		e = e ? e : window.event;
		var tag = e.srcElement ? e.srcElement : e.target;
		if (/input|textarea/i.test(tag.tagName)) {
			return true;
		}
		var options = {
			content: contextmenuico(jQuery(this).attr('rid')),
			show: true,
		}
		layuiModules.dropdown.reloadData('right_ico',options);
		layuiModules.dropdown.open('right_ico');
		return false;
	});
	//检测已选中
	if (jQuery.inArray(data.rid, _filemanage.selectall.icos) > -1) {
		el.addClass('Icoselected');
	}
	//处理按钮

	if (!flag) {
		_filemanage.glow(el);
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

_filemanage.prototype.setToolButton = function () { //设置工具栏
	var rids = _filemanage.selectall.icos;
	var data = _explorer.sourcedata.icos[rids[0]];
	var el = jQuery('.navtopheader .shareMenu').empty();
	if (!rids.length) {
		return;
	}
	var html = jQuery('#template_toolButton').html();
	//替换rid
	html = html.replace(/\{rid\}/ig, rids[rids.length - 1]);
	if (rids.length === 1 && data.type === 'folder') { //单选中目录时，粘贴到此目录内部
		html = html.replace(/\{fid\}/g, data.fid);
	} else {
		html = html.replace(/\{fid\}/g, data.pfid);
	}
	el.html(html);

	//过滤单选和多选的情况
	if (rids.length > 1) { //多选
		el.find('.single').remove();
	} else if (rids.length === 1) { //单选
		el.find('.multi').remove();
	}
	//判断下载权限
	var downloadprem = 0; // 默认值
    if (_filemanage && _filemanage.param && _filemanage.param.download) {
        downloadprem = _filemanage.param.download;
    }
    //下载权限
    if (!downloadprem) {
		el.find('.download,.downpackage,.downAll,.allsave').remove();
		jQuery('.downAll').hide();
		jQuery('.allsave').hide();
	}else {
		jQuery('.allsave').show();
        jQuery('.downAll').show();
    }

	//打开方式
	if (rids.length === 1) {
		data = _explorer.sourcedata.icos[rids[0]];
		var info = '';
		//判断打开方式
		var subdata = getExtOpen(data.type === 'shortcut' ? data.tdata : data);
		if (subdata === true) {
			el.find('.openwith').remove();
		} else if (subdata === false) {
			el.find('.openwith').remove();
			el.find('.open').remove();
		} else if (subdata.length === 1) {
			el.find('.openwith').remove();
		} else if (subdata.length > 1) {
			for (var i = 0; i < subdata.length; i++) {
				info += '<li><a class="dropdown-item" onClick="_filemanage.Open(\'' + data.rid + '\',\'' + subdata[i].extid + '\')" href="javascript:;"><img class="filee-icon" src="' + subdata[i].icon + '"><span class="file-text">' + subdata[i].name + '</span></a></li>';
			}
			el.find('.openwith').find('ul.dropdown-menu').html(info);
		}
	}
	_filemanage.SetMoreButton();
};

_filemanage.SetMoreButton = function () {
	var el = $('.navtopheader .toolButtons');
	if (!el.length) {
		return;
	}
	var yunfileButton = el.find('.yunfile-btnMenu');
	yunfileButton.find('button').hide();
};

_filemanage.prototype.showIcos = function (ext) {
	//排序数据
	var self = this;
	if (_filemanage.showicosTimer[this.winid]) {
		window.clearTimeout(_filemanage.showicosTimer[this.winid]);
	}
	//_window.windows[this.winid].filemanageid=this.id;
	var containerid = 'filemanage-' + this.winid;

	jQuery('#' + containerid).empty();
	this.createIcosContainer();
	//var container=jQuery('#'+containerid+' .js-file-item-tpl');
	var data_sorted = null;
		data_sorted = _filemanage.Sort(this.data, this.disp, this.asc);
	if (ext) {
		data_sorted = _file.Searchext(data_sorted, ext);
	}
	this.currentdata = data_sorted;
	_filemanage.stack_data[self.id] = Array();
	for (var i in data_sorted) {
		_filemanage.stack_data[self.id].push({
			data: data_sorted[i],
			"obj": self
		});
	}
	window.setTimeout(function () {
		_filemanage.stack_run(self.id);
	}, 1);
	//增加底部信息
	this.pageloadding = false;
};
_filemanage.prototype.appendIcos = function (data) {
	var self = this;
	if (_filemanage.showicosTimer[this.winid]) {
		window.clearTimeout(_filemanage.showicosTimer[this.winid]);
	}
	_filemanage.stack_data[self.winid] = Array();
	for (var i in data) {
		//this.data[i]=data[i];
		_filemanage.stack_data[self.winid].push({
			data: data[i],
			"obj": self
		});
	}
	window.setTimeout(function () {
		_filemanage.stack_run(self.winid);
	}, 1);
	this.pageloadding = false;
};
function contextmenuico(rid) {
	if (!rid) {
		return '';
	}
	var obj = _explorer.sourcedata.icos[rid];
    var html = document.getElementById('right_ico').innerHTML;
	if(!html) {
		return '';
	}
    html = html.replace(/\{rid\}/g, rid);
    if (_filemanage.selectall.icos.length == 1 && obj.type == 'folder') {
        html = html.replace(/\{fid\}/g, obj.fid);
    } else {
        html = html.replace(/\{fid\}/g, obj.pfid);
    }
	var el = $(html);
	if (!el.length) return '';
	var obj = _explorer.sourcedata.icos[rid];
    if (obj.type == 'shortcut' || obj.type == 'storage' || obj.type == 'pan' || _explorer.myuid < 1) {
        el.find('.shortcut').remove();
    }
    var downloadprem = 0; // 默认值
    if (_filemanage && _filemanage.param && _filemanage.param.download) {
        downloadprem = _filemanage.param.download;
    }
    //下载权限
    if (!downloadprem) {
        el.find('.download').remove();
        el.find('.allsave').remove();
        jQuery('.downAll').hide();
        jQuery('.allsave').hide();
        el.find('.downpackage').remove();
    } else {
        jQuery('.allsave').show();
        jQuery('.downAll').show();
    }

    //多选时的情况
    if (_filemanage.selectall.icos.length > 1 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		el.find('.menu-item:not(.allsave,.downpackage,.property)').remove();
        var pd = 1;
        if (!downloadprem) {
            pd = 0;
        }
        if (!pd) {
            jQuery('.allsave').hide();
            el.find('.allsave').remove();
            el.find('.downpackage').remove();
        }
        el.find('.download').remove();
    } else {
        el.find('.downpackage').remove();
    }
    if (!el.find('.menu-item').length) {
        el.hide();
        return;
    }
    //判断打开方式
    var subdata = getExtOpen(obj.type == 'shortcut' ? obj.tdata : obj);
    if (subdata === true) {
        el.find('.openwith').remove();
    } else if (subdata === false) {
        el.find('.openwith').remove();
        el.find('.open').remove();
    } else if (subdata.length == 1) {
        el.find('.openwith').remove();
    } else if (subdata.length > 1) {
        var html = '';
		for (var i = 0; i < subdata.length; i++) {
			html += '<li class="menu-item" onClick="_filemanage.Open(\'' + rid + '\',\'' + subdata[i].extid + '\');jQuery(\'#right_contextmenu\').hide();jQuery(\'#shadow\').hide();return false;" title="' + subdata[i].name + '"><div class="layui-menu-body-title dropdown-item">';
			if (subdata[i].icon) {
				html += '<span class="pe-2"><img width="24px" height="24px" src=' + subdata[i].icon + '></span>';
			}
			html += subdata[i].name;
			html += '</div></li>';
		}
		el.find('.openwithdata').html(html);
    } else {
        el.find('.openwith').remove();
    }

    //去除多余的分割线
    el.find('.layui-menu-item-divider').each(function () {
		if (!jQuery(this).next().first().hasClass('menu-item') || !jQuery(this).prev().first().hasClass('menu-item')) jQuery(this).remove();
	});
	return el[0] ? el[0].outerHTML : '';
}
function contextmenubody(fid) {
	var html = document.getElementById('right_body').innerHTML;
	if(!html) {
		return '';
	}
	html = html.replace(/\{fid\}/g, fid);
    html = html.replace(/\{filemanageid\}/g, _filemanage.winid);
	var filemanage = _filemanage.cons[_filemanage.winid];
	var el = $(html);
	if (!el.length) return '';
    //设置当前容器的相关菜单选项的图标
    el.find('span.menu-icon-iconview[view=' + filemanage.view + ']').removeClass('mdi-checkbox-blank-outline').addClass('mdi-checkbox-marked');
    //设置排序
   	el.find('.menu-icon-disp').each(function () {
		if (jQuery(this).attr('disp') == filemanage.disp) {
			jQuery(this).removeClass('mdi-checkbox-blank-outline').addClass('mdi-checkbox-marked');
			jQuery(this).nextAll('.caret').first().removeClass('mdi-menu-up').removeClass('mdi-menu-down').addClass(filemanage.asc > 0 ? 'mdi-menu-up' : 'mdi-menu-down');
		} else {
			jQuery(this).addClass('mdi-checkbox-blank-outline').removeClass('mdi-checkbox-marked');
			jQuery(this).nextAll('.caret').first().removeClass('mdi-menu-up').removeClass('mdi-menu-down');
		}
	});
    if (!fid) {
        el.find('.property').remove();
    }
    var create = 0; // 默认值
    if (_filemanage && _filemanage.param && _filemanage.param.create) {
        create = _filemanage.param.create;
    }
    if (!create) {
        el.find('.create').remove();
		el.find('.upload').remove();
        el.find('.uploadfolder').remove();
    }
    //设置默认桌面

    //检测新建和上传是否都没有
    if (el.find('.create .menu-item').length < 1) {
		el.find('.create').remove();
	}
	if (_explorer.sourcedata.folder[1].bz) {
		el.find('.newlink').remove();
	}
    if (el.find('.menu-item').length < 1) {
        el.hide();
        return;
    }
	el.find('.layui-menu-item-divider').each(function () {
		if (!jQuery(this).next().first().hasClass('menu-item') || !jQuery(this).prev().first().hasClass('menu-item')) jQuery(this).remove();
	});
	return el[0] ? el[0].outerHTML : '';
}
_filemanage.prototype.createIcosContainer = function () {
	var self = this;
	var containerid = 'filemanage-' + this.id;
	var div = document.getElementById(containerid);
	if (!div) {
		return;
	}
	div.className = "icosContainer";
	div.setAttribute('unselectable', "on");
	div.setAttribute('onselectstart', "return event.srcElement.type== 'text';");
	div.innerHTML = _filemanage.get_template(this.id, true);
	_explorer.Scroll($('.scroll-y'));
	var el = jQuery(div);
	el.find('.js-file-item-tpl').empty();
	jQuery('.middlecenter')
		.on('contextmenu', function (e) {
			e = e ? e : window.event;
			var tag = e.srcElement ? e.srcElement : e.target;
			if (/input|textarea/i.test(tag.tagName)) {
				return true;
			}
			var options = {
				content: contextmenubody(self.fid),
				show: true
			}
			layuiModules.dropdown.reloadData('right_ico',options);
			layuiModules.dropdown.open('right_ico');
			return false;
		})
		.on('click', function (e) {
			//清空数据
			//if(_hotkey.ctrl<1) return true;
			e = e ? e : window.event;
			var tag = e.srcElement ? e.srcElement : e.target;
			if (/input|textarea/i.test(tag.tagName)) {
				return true;
			}
			if (containerid === _filemanage.selectall.container) {
				_filemanage.selectall.container = containerid;
				_filemanage.selectall.icos = [];
				_filemanage.selectall.position = {};
				el.find('.Icoblock').removeClass('Icoselected');
				el.find('.select-all').removeClass('Icoselected');
				self.selectInfo();
			}
		});
		jQuery(document).off('click', '.select-all').on('click', '.select-all', function() {
			var el = jQuery(this);
			var selectall = true;
			if (el.hasClass('Icoselected')) {
				el.removeClass('Icoselected');
				selectall = false;
				_filemanage.selectall.icos = [];
			} else {
				el.addClass('Icoselected mdi-checkbox-blank-outline');
				selectall = true;
				_filemanage.selectall.icos = [];
			}
			_filemanage.selectall.container = containerid;
			jQuery('#' + containerid).find('.Icoblock').each(function () {
				if (selectall) {
					jQuery(this).addClass('Icoselected');
					_filemanage.selectall.icos.push(jQuery(this).attr('rid'));
				} else {
					jQuery(this).removeClass('Icoselected');
				}
			});
			self.selectInfo();
		});
	jQuery(document).off('click.cselect').on('click.cselect', '.mdi-close', function () {
		jQuery('.navtopheader').css('display', 'none');
		el.find('.Icoblock').removeClass('Icoselected');
		_filemanage.selectall.icos = [];
	});
	_select.init(containerid);
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
				_filemanage.Disp(this, self.id, disp);
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

};

_filemanage.prototype.createBottom = function () {
	//创建right_bottom
	var right_bottom = document.createElement('div');
	right_bottom.className = "filemanage-bottom";
	right_bottom.id = 'bottom_content_' + this.winid + '_' + this.id;
	document.getElementById('content_' + this.winid).appendChild(right_bottom);
};
_filemanage.prototype.selectInfo = function () {
	var self = this;
	if (this.selectinfoTimer) {
		window.clearTimeout(this.selectinfoTimer);
	}
	this.selectinfoTimer = window.setTimeout(function () {
		self._selectInfo();
	}, 200);
};
_filemanage.prototype._selectInfo = function () {

	//设置全选框信息
	//设置全选按钮的文字
	var sum = _filemanage.selectall.icos.length;
	var total = jQuery('#filemanage-' + this.id).find('.Icoblock').length;
	var html = jQuery('#template_file').html();
	if (sum > 0) { //有选中
		jQuery('.navtopheader').css('display', 'block');
		jQuery('.navtopheader').html(html);
		jQuery('.selectall-box .select-info').html('<span class="num">' + sum + '</span>个选中');
		jQuery('.docunment-allfile').hide();
		if (sum >= total) { //全部选中
			jQuery('.select-all').addClass('Icoselected');
			jQuery('.select-all').removeClass('mdi-checkbox-intermediate').addClass('mdi-checkbox-marked');
			jQuery('.select-all').attr('title', '取消全选');
		} else { //部分选中
			jQuery('.select-all').removeClass('mdi-checkbox-marked').addClass('mdi-checkbox-intermediate');
			jQuery('.select-all').removeClass('Icoselected');
			jQuery('.select-all').attr('title', '全选');
		}
	} else { //没有选中
		jQuery('.navtopheader').css('display', 'none');
		jQuery('.navtopheader').html('');
		jQuery('.selectall-box').removeClass('Icoselected');
		jQuery('.selectall-box .select-info').html(this.view < 4 ? '全选' : '');
		jQuery('.docunment-allfile').show();
	}
	this.setToolButton(); //设置头部工具菜单；
	return false;
};
_filemanage.prototype.PageInfo = function () {
	return;
};

_filemanage.prototype.pageClick = function (page) {
	var self = this;
	this.pageloadding = true;
	if (!page) {
		page = 1;
	}
	this.currentpage = page;
	var url = self.url
		.replace(/&disp\=\d/ig, '')
		.replace(/&asc\=\d/ig, '')
		.replace(/&iconview\=\d/ig, '')
		.replace(/&page\=\d+/ig, '')
		.replace(/&fid\=\w*(&|$)/, '&')
		.replace(/&t\=\d+/, '');
	url = url.replace(/&+$/ig, '');
	_filemanage.getData(url + '&disp=' + this.disp + '&asc=' + this.asc + '&iconview=' + this.view + '&page=' + page + '&t=' + new Date().getTime(), function () {
		self.PageInfo();
	});
};

_filemanage.stack_run = function (winid) {
	//if(_filemanage.showicosTimer[winid]) window.clearTimeout(_filemanage.showicosTimer[winid]);
	if (_filemanage.stack_data[winid].length > 0) {
		var obj = _filemanage.stack_data[winid][0].obj;
		for (var i = 0; i < _filemanage.speed; i++) {
			if (_filemanage.stack_data[winid].length > 0) {
				_filemanage.stack_data[winid][0].obj.CreateIcos(_filemanage.stack_data[winid][0]['data'], 1);
				_filemanage.stack_data[winid].splice(0, 1);
			} else break;
		}
		_filemanage.showicosTimer[winid] = window.setTimeout(function () {
			_filemanage.stack_run(winid);
		}, 1);
	} else {
		jQuery(document).trigger('showIcos_done');
	}
};

_filemanage.Sort = function (data, disp, asc) {
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
_filemanage.get_template = function (fid, whole, disp, asc) {
	var obj = _filemanage.cons[fid];
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
//文件没有可以打开的应用
_filemanage.Open = function (rid, extid, title) {
	var openprem = _filemanage.param.open;
	var data = _explorer.sourcedata.icos[rid];
	var name = data.name;
	// var ext =data.ext;
	// var type=data.type;
	var atdingding=0;
	try{
	if(DingTalkPC && typeof(DingTalkPC)!="undefined" && DingTalkPC.ua.isDesktop && DingTalkPC.ua.isInDingTalk){
		atdingding=1;
	}
	}catch(e){}
	var obj = {};
	obj.type = data.type;
	obj.ext = data.ext;
	obj.id = rid;
	obj.text = name;
	obj.dpath = data.dpath;
	if (obj.type !== 'folder' && !openprem) {
		showmessage('分享者禁用了在线预览', 'info', 0, 1);
		return false;
	}
	if (obj.type === 'link') {
		window.open(data.url);
		return;
	} else if (obj.type === 'dzzdoc') {
		obj.url = "index.php?mod=document&icoid=" + obj.id;
		if(atdingding){ 
			window.open( encodeURI(SITEURL+"index.php?mod=dingtalk&op=loginfromding&redirecturl="+encodeURIComponent(obj.url)) );
		}else{
			window.open(obj.url);
		} 
		return;
	} else if (obj.type === 'folder') {
		_explorer.getRightContent(data.oid, 'filelist');
		return false;
	}

	if (!extid) {
		extid = getExtOpen(data, true);
	}
	if (extid) {
		var extdata_url = extopen_replace(data, extid);
		if (extdata_url) {
			extdata_url = extdata_url.replace(/{\w+}/g, '');
			if (extdata_url.indexOf('dzzjs:OpenPicWin') === 0) {
				jQuery('img[data-original]:visible').dzzthumb();
				jQuery('.Icoblock[rid=' + rid + '] img[data-original]').trigger('click');
				return;
			} else if (extdata_url.indexOf('dzzjs:') === 0) {
				
				eval((extdata_url.replace('dzzjs:','')));
				return;
			} else {
				if(atdingding){
					var extdata_url=encodeURI(SITEURL+"index.php?mod=dingtalk&op=loginfromding&redirecturl="+encodeURIComponent(extdata_url));
				}
				window.open(extdata_url);
			}
		}
	} else {
		showDialog('文件没有可以打开的应用');
	}
};
//获取打开方式
function getExtOpen(data, isdefault) {
	var openprem = 0; // 默认值
    if (_filemanage && _filemanage.param && _filemanage.param.open) {
        openprem = _filemanage.param.open;
    }
	if (!openprem) {
		return false;
	}

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

_filemanage.property = function (rid, isfolder) {
	var path = '';
	var bz = '';
	if (_explorer.sourcedata.folder[1].bz) {
		bz = '1';
	}
	if (isfolder) {
		var folder = _explorer.sourcedata.folder[rid];
		path = encodeURIComponent('fid_' + folder.path);
	} else {
		var dpaths = [];
		var ico = null;
		if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
			for (var i = 0; i < _filemanage.selectall.icos.length; i++) {
				ico = _explorer.sourcedata.icos[_filemanage.selectall.icos[i]];
				dpaths.push(ico.dpath);
			}
		} else {
			ico = _explorer.sourcedata.icos[rid];
			dpaths = [ico.dpath];
		}
		path = encodeURIComponent(dpaths.join(','));
	}
	showWindow('property', _explorer.appUrl + '&op=ajax&do=property&bz='+bz+'&sid='+sid+'&paths=' + path,'get','0');
};

_filemanage.downAttach = function (id) {
	if (!id) {
		id = _filemanage.selectall.icos[0];
	}
	var data = _explorer.sourcedata.icos[id];
	if (!data) {
		return false;
	}
	
	$.post(MOD_URL+'&op=ajax&do=adddowns', {'sid':sid},function (json) {
		if (json['success']) {
			var url = DZZSCRIPT + '?mod=io&op=download&path=' + encodeURIComponent(data.dpath) + '&t=' + new Date().getTime();
			if (BROWSER.ie) {
				window.open(url);
			} else {
				window.frames.hideframe.location = url;
			}
		} else {
			showmessage(json['error'], 'danger', 5000, 1);
			return false;
		}
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		showmessage(__lang.do_failed, 'error', 3000, 1);
	});
	return false;
};
_filemanage.downAll = function () {
	showmessage('处理中，请等待浏览器响应', 'info', 5000, 1);
	var rids = [];
	for (var key in _explorer.sourcedata.icos) {
		if (_explorer.sourcedata.icos.hasOwnProperty(key)) {
			rids.push(_explorer.sourcedata.icos[key].dpath);
		}
	}
	$.post(MOD_URL+'&op=ajax&do=adddowns', {'sid':sid},function (json) {
		if (json['success']) {
			var url = DZZSCRIPT + '?mod=io&op=download&path=' + rids;
			if (BROWSER.ie) {
				window.open(url);
			} else {
				window.frames.hideframe.location = url;
			}
		} else {
			showmessage(json['error'], 'danger', 5000, 1);
			return false;
		}
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		showmessage(__lang.do_failed, 'error', 3000, 1);
	});
	return false;
};

_filemanage.downpackage = function () {
	//检查是否有不能下载的文件类型
	var errors = '';
	var dpaths = [];
	for (var i = 0; i < _filemanage.selectall.icos.length; i++) {
		var ico = _explorer.sourcedata.icos[_filemanage.selectall.icos[i]];
		if (ico.type === 'folder' || ico.type === 'document' || ico.type === 'image' || ico.type === 'attach') {
			dpaths.push(ico.dpath);
		} else {
			errors += '<li>' + ico.name + '</li>';
		}
	}
	if (errors) {
		layer.msg('<p>' + __lang.error_file_not_download + '</p><ul>' + errors + '</ul>', {offset:'10px'});
		return false;
	}
	var path = encodeURIComponent(dpaths.join(','));
	if (path.length > 2048) {
		showmessage(__lang.choose_file_many, 'danger', 3000, 1);
		return false;
	}
	$.post(MOD_URL+'&op=ajax&do=adddowns', {'sid':sid},function (json) {
		if (json['success']) {
			var url = DZZSCRIPT + '?mod=io&op=download&path=' + path + '&t=' + new Date().getTime();
			if (BROWSER.ie) {
				window.open(url);
			} else {
				window.frames.hideframe.location = url;
			}
		} else {
			showmessage(json['error'], 'danger', 5000, 1);
			return false;
		}
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		showmessage(__lang.do_failed, 'error', 3000, 1);
	});
	return false;
};

_filemanage.NewIco = function (type, fid) {
	if (!fid) {
		fid = _explorer.sourcedata.folder[1].fid;
	}
	if (!fid && !_filemanage.fid) {
		return;
	}
	var bz = '';
	if(_explorer.sourcedata.folder[1].bz) {
		bz = _explorer.sourcedata.folder[1].path;
	}
	if (type === 'newFolder') {
		showWindow('newFolder', _explorer.appUrl + '&op=ajax&sid='+sid+'&do=' + type + '&fid=' + fid+'&bz='+bz,'get','0');
	} else if (type === 'newLink') {
		showWindow('newLink', _explorer.appUrl + '&op=ajax&sid='+sid+'&do=' + type + '&fid=' + fid,'get','0');
	} else {
		$.post(_explorer.appUrl + '&op=ajax&do=newIco&type=' + type, {
			'fid': fid,
			'sid':sid,
			'bz': bz
		}, function (data) {
			if (data.msg === 'success') {
				_explorer.sourcedata.icos[data.rid] = data;
				_filemanage.cons['f-1'].CreateIcos(data);
                _filemanage.addIndex(data);
				showmessage('已创建：'+data.name, 'success', 3000, 1);
            } else {
				showmessage(data.error, 'danger', 5000, 1);
			}
		}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage(__lang.do_failed, 'error', 3000, 1);
        });
	}
};
//增加索引
_filemanage.addIndex = function(data){
	if(data.bz) return;
	if(data.filetype != 'folder' && data.filetype != 'link'){
        $.post(MOD_URL+'&op=ajax&sid='+sid+'&do=addIndex',{
            'aid':data.aid,
            'rid':data.rid,
            'username':data.username,
            'filetype':data.filetype,
            'filename':data.name,
            'md5':data.md5,
            'vid':data.vid,
			'pfid':data.pfid,
			'gid':data.gid,
			'uid':data.uid,
        },function(json){
            if(json['success']){

            }else{
                alert(json.error);
            }
        },'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage(__lang.do_failed, 'error', 3000, 1);
        });
	}
}
_filemanage.showTemplatenoFile = function (containid, total) {
	if (total < 1 && jQuery('#' + containid).find('.emptyPage').length == 0) {
		jQuery(jQuery('#template_nofile_notice').html()).appendTo(jQuery('#' + containid));
	} else {
		jQuery('#' + containid).find('.emptyPage').remove();
	}
}