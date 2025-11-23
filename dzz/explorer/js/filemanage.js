/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
//添加目录树的右键
"use strict";

function _filemanage(id, data, param) {
	var page = isNaN(parseInt(param.page)) ? 1 : parseInt(param.page);
	var total = isNaN(parseInt(param.total)) ? 1 : parseInt(param.total);
	this.total = total;
	//alert('filemangeid='+id);
	this.bz = param.bz || ''; //标志是那个api的数据

	this.perpage = param.perpage || _filemanage.perpage; // 使用全局默认值
	this.totalpage = Math.ceil(this.total / this.perpage);
	this.totalpage = this.totalpage < 1 ? 1 : this.totalpage;
	this.id = id;
	this.string = "_filemanage.cons." + this.id;
	//alert(this.id);
	var sidarr = id.split('-');
	if (sidarr[0] == 'f') this.fid = sidarr[1];
	else this.fid = 0;
	this.subfix = sidarr[0]; //记录当前的sid前缀 f、cat等
	this.winid = id;
	this.keyword = param.keyword;
	this.localsearch = param.localsearch;

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
	this.pageloadding = true;
	this.exts = param.exts || '';
	this.tags = param.tags || '';
	this.before = param.before || '';
	this.after = param.after || '';
	this.fids = param.fids || '';
	this.gid = param.gid || '';

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
_filemanage.rid = '';
_filemanage.showicosTimer = {};
_filemanage.apicacheTimer = {};
_filemanage.infoPanelUrl = '';
_filemanage.viewstyle = ['bigicon', 'middleicon', 'middlelist', 'smalllist', 'detaillist'];
_filemanage.getData = function (url, callback) {
	jQuery('.loadingmiddlecon').html(_explorer.loadhtml);
	jQuery.getJSON(url, function (json) {
		if (json.error) {
			jQuery('#middleconMenu').html('<div class="emptyPage" id="noticeinfo"><img src="static/image/common/no_list.png"><p class="emptyPage-text">'+json.error+'</p></div>');
			layer.alert(json.error, {skin:'lyear-skin-danger'});
			return false;
		} else {
			for (var id in json.data) {
				_explorer.sourcedata.icos[id] = json.data[id];
			}
			for (var fid in json.folderdata) {
				_filemanage.rid = fid;
				_explorer.sourcedata.folder[fid] = json.folderdata[fid];
			}
			_explorer.topMenu(location.hash.replace('#',''),_filemanage.fid);
			var obj = null;
			
			if (json.param.page > 1) {
				obj = _filemanage.cons[json.sid];
				obj.appendIcos(json.data);
				obj.total = parseInt(json.total);
				obj.totalpage = Math.ceil(obj.total / obj.perpage);
			} else {
				obj = new _filemanage(json.sid, json.data, json.param);
				if (_filemanage.selectall.container !== 'filemanage-' + json.sid) {
					_filemanage.selectall = {
						position: {},
						container: '',
						icos: []
					};
					obj.selectInfo();
				}
				obj.showIcos();
			}
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
		jQuery('#middleconMenu').html(jqxhr.responseText);
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
		if (fid > 0 && _explorer.Permission_Container('admin', fid)) {
			jQuery.post(_filemanage.saveurl + '&do=folder', {
				fid: fid,
				iconview: view
			});
			_explorer.sourcedata.folder[fid]['iconview'] = view;

		}
	} else if (filemanage.subfix === 'cat') {
		jQuery.post(_filemanage.saveurl + '&do=catsearch', {
			catid: id.replace('cat-', ''),
			iconview: view
		});
	} else if (filemanage.subfix === 'search') {
		jQuery.post(_filemanage.saveurl + '&do=search', {
			iconview: view
		});
	} else if (filemanage.subfix === 'recycle') {
		jQuery.post(_filemanage.saveurl + '&do=recycle', {
			iconview: view
		});
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
		if (_explorer.hash.indexOf('cloud') != -1) {
			var fid = _filemanage.rid;
		} else {
			var fid = filemanage.fid;
		}
		
		if (fid > 0 && _explorer.Permission_Container('admin', fid)) {
			jQuery.post(_filemanage.saveurl + '&do=folder', {
				fid: fid,
				disp: parseInt(disp)
			});
		}
		_explorer.sourcedata.folder[fid]['disp'] = parseInt(disp);
	} else if (filemanage.subfix === 'cat') {
		jQuery.post(_filemanage.saveurl + '&do=catsearch', {
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
_filemanage.searchsubmit = function (sid) {
	var keyword = document.getElementById('searchInput_' + sid).value;
	keyword = (keyword === __lang.search) ? keyword : '';
	var obj = _filemanage.cons[sid];
	if (!obj) {
		return;
	}
	if (obj.localsearch) {
		obj.keyword = keyword;
		obj.showIcos();
	} else {
		obj.pageClick(1);
	}
};

/*
view: 图标排列方式：0:大图标，1：中图标，2：中图标列表，3小图标列表,4:详细
disp：图标排列顺序：0：原始顺序:按名称；1：按大小；2：按类型；3：按时间
asc :升序或降序：0：升序；1：降序
*/

_filemanage.setInfoPanel = function () {
	var rids = _filemanage.selectall.icos;
	if (_explorer.infoRequest){
		_explorer.infoRequest.abort();
	} 
	if (!_explorer.infoPanelOpened || _explorer.infoPanel_hide) {
		return; //右侧面板没有打开的话，不加载文件详细信息
	}
	var bz = _explorer.getUrlParam(location.hash, 'bz');
	if (bz && _explorer.hash.indexOf('cloud') != -1) {
		if(_explorer.bz == bz) {
			return;
		} else {
			_explorer.bz = bz;
		}
	} else {
		_explorer.bz = '';
	}
	if (rids.length < 1) {
		var fid = _filemanage.fid || $('#fidinput').val();
		if (!fid) {
			if(_explorer.hash.indexOf('recent') != -1) {
				var data = '<div class="briefMenu modal-header dtheme border-bottom clearfix"><div class="modal-title"><i class="mdi mdi-clock-time-four text-info right-topicon"></i><span class="text-truncate fs-6 ps-1">'+__lang.recently_used+'</span><button type="button" class="toggRight btn-close"></button></div></div></div><div class="p-2 border-bottom"><div class="row"><label class="col-sm-3 text-muted">说明</label><div class="col-sm-9 text-break">最近修改、打开的文件,只显示前25条记录。</div></div></div>';
			} else if (_explorer.hash.indexOf('collection') != -1) {
				var data = '<div class="briefMenu modal-header dtheme border-bottom clearfix"><div class="modal-title"><i class="mdi mdi-star right-topicon text-yellow"></i><span class="text-truncate fs-6 ps-1">'+__lang.collect+'</span><button type="button" class="toggRight btn-close"></button></div></div></div><div class="p-2 border-bottom"><div class="row"><label class="col-sm-3 text-muted">说明</label><div class="col-sm-9 text-break">文件添加收藏后可以实现快速访问。</div></div></div>';
			} else if (_explorer.hash.indexOf('catsearch') != -1) {
				var data = '<div class="briefMenu modal-header dtheme border-bottom clearfix"><div class="modal-title"><i class="mdi mdi-file-document-outline right-topicon text-primary"></i><span class="text-truncate fs-6 ps-1">'+__lang.type+'</span><button type="button" class="toggRight btn-close"></button></div></div></div><div class="p-2 border-bottom"><div class="row"><label class="col-sm-3 text-muted">说明</label><div class="col-sm-9 text-break">按类型对文件进行分类。</div></div></div>';
			} else if (_explorer.hash.indexOf('recycle') != -1) {
				var data = '<div class="briefMenu modal-header dtheme border-bottom clearfix"><div class="modal-title"><i class="mdi mdi-delete right-topicon text-danger"></i><span class="text-truncate fs-6 ps-1">'+__lang.explorer_recycle_name+'</span><button type="button" class="toggRight btn-close"></button></div></div></div><div class="p-2 border-bottom"><div class="row"><label class="col-sm-3 text-muted">说明</label><div class="col-sm-9 text-break">在这里管理您删除的文件（夹）。</div></div></div>';
			} else {
				var data = '<div class="briefMenu modal-header clearfix"><div class="modal-title"><button type="button" class="toggRight btn-close"></button></div></div>';
			}
			data += '<div class="nothing_message">'
				+'<div class="nothing_allimg">'
				+'<img src="'+MOD_PATH+'/images/noFilePage-FileChoice.png">'
				+'<p>'+__lang.choose_file_examine_information+'</p>'
				+'</div>'
				+'</div>';
			$('#rightMenu').html(data);
			_filemanage.infoPanelUrl = '';
		} else if (_filemanage.infoPanelUrl !== fid) {
			var rid = $('#ridinput').val();
			_explorer.infoRequest = $.post(MOD_URL + '&op=dynamic&do=getfiledynamic&ajaxdata=html', {
				'fid': rid ? 0 : fid,
				'rid': rid,
				'bz': bz
			}, function (data) {
				$('#rightMenu').html(data);
				var folderInfo = document.getElementById('folderinfo');
				if (folderInfo) {
					if (_filemanage && _filemanage.cons && _filemanage.winid) {
						var filemanage = _filemanage.cons[_filemanage.winid];
						if (filemanage) {
							var infoText = '<div class="row mb-1">' +
									'<label class="col-sm-3 text-muted">分页信息</label>' +
									'<div class="col-sm-9 text-break">' +
										'共' + filemanage.totalpage + '页(' + filemanage.total + '条记录),' + filemanage.perpage + '条/页' +
									'</div>' +
									'</div>';
							folderInfo.innerHTML += infoText;
						}
					}
				}
				_filemanage.infoPanelUrl = fid;
			});
		}
	} else if (rids.length === 1) {
		if (_filemanage.infoPanelUrl !== rids[0]) {
			_explorer.infoRequest = $.post(MOD_URL + '&op=dynamic&do=getfiledynamic&ajaxdata=html', {
				'rid': rids
			}, function (data) {
				$('#rightMenu').html(data);
				_filemanage.infoPanelUrl = rids[0];
			});
		}
	} else {
		var ridsstr = rids.join(',');
		if (_filemanage.infoPanelUrl !== ridsstr) {
			_explorer.infoRequest = $.post(MOD_URL + '&op=dynamic&do=getfiledynamic&ajaxdata=html', {
				'rid': rids
			}, function (data) {
				$('#rightMenu').html(data);
				_filemanage.infoPanelUrl = ridsstr;
			});
		}
	}
};

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
	if(!template) template = '';
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
	html = html.replace(/\{fdateline\}/g, data.fdateline?data.fdateline:'');
	html = html.replace(/\{ffdateline\}/g, data.ffdateline?data.ffdateline:'');
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
	//分享状态
	if(data.shareid > 0) {
		var sharestatus = '<span class="share-item" ><i class="mdi mdi-share-circle text-success" title="'+__lang.shared+'"></i></span>';
	} else {
		var sharestatus = '<span class="share-item hide"><i class="mdi mdi-share-circle text-success" title="'+__lang.shared+'"></i></span>';
	}
	//收藏
	if(data.collect){
		var collectstatus = '<span class="colllection-item" ><i class="mdi mdi-star-circle text-yellow" title="'+__lang.already_collected+'"></i></span>';
	}else{
		var collectstatus = '<span class="colllection-item hide"><i class="mdi mdi-star-circle text-yellow" title="'+__lang.already_collected+'"></i></span>';
	}
	html = html.replace(/\{collectstatus\}/g,collectstatus);
    html = html.replace(/\{sharestatus\}/g,sharestatus);
	if (data.type !== 'image') {
		html = html.replace(/data-start=\"image\".+?data-end=\"image\"/ig, '');
	}
	var position_hash = '';
	if (data.gid > 0) {
		position_hash = data.pfid > 0 ? '#group&do=file&gid=' + data.gid + '&fid=' + data.pfid : '#group&gid=' + data.gid;
	}else if (data.bz && data.bz !== 'dzz') {
		position_hash = '#cloud&bz=' + data.bz + '&path=' + data.path;
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
		jQuery(html).appendTo('#' + containerid + ' .js-file-item-tpl');
		el = jQuery('.Icoblock[rid=' + data.rid + ']');

	}

	//检查下载和分享菜单
	//判断下载权限
	if (!_explorer.Permission('download', data)) {
		el.find('.download').remove();
	}

	//判断分享权限
	if (!_explorer.Permission('share', data)) {
		el.find('.share').remove();
	}

	if (this.view < 4) {

		el.on('mouseenter', function () {
			jQuery(this).addClass('hover');

		});
		el.on('mouseleave', function () {
			jQuery(this).removeClass('hover');

		});
		//处理多选框
		//if(!_filemanage.fid || _explorer.Permission_Container('multiselect',this.fid)){
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
	el.on('contextmenu', function (e) {
		e = e ? e : window.event;
		var tag = e.srcElement ? e.srcElement : e.target;
		if (/input|textarea/i.test(tag.tagName)) {
			return true;
		}
		var options = {
			content: contextmenuico(jQuery(this).attr('rid'))
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
		//this.checkPageChange();
		//this.PageInfo();
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
	var hash = location.hash;
	if (!rids.length) {
		return;
	}
	var html = jQuery('#template_toolButton').html();
	if (hash.indexOf('recycle') != -1 || hash.indexOf('share') != -1 || hash.indexOf('isdelete') != -1) {
		return false;
	}
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

	//判断权限
	var collects = 0;
	for (var i = 0; i < rids.length; i++) {
		data = _explorer.sourcedata.icos[rids[i]];
		/*if(!data){
			continue;
		}*/
		//判断复制权限
        if (!_explorer.Permission('copy', data)) {
            el.find('.copy').remove();
        }
		//判断剪切/删除权限
		if (!_explorer.Permission('delete', data)) {
			el.find('.delete,.cut,.rename').remove();
		}
		//判断下载权限
		if (!_explorer.Permission('download', data)) {
			el.find('.download,.downpackage').remove();
		}
		//判断分享权限
		if (!_explorer.Permission('share', data)) {
			el.find('.share').remove();
		}
		//判断粘贴权限及是否有粘贴项
		if (!_explorer.Permission('upload', data) || _explorer.cut.icos.length < 1 || _filemanage.fid < 1) {
			el.find('.paste').remove();
		}
		if (data.collect) {
			collects += 1;
		}
	}
	if (collects === rids.length) { //区别是已收藏时，菜单显示取消收藏
		el.find('.collect a').html('<i class="mdi mdi-star-minus"></i><span class="file-text">取消收藏</span>');
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
			el.find('.open').remove();
		} else if (subdata.length > 0) {
			for (i = 0; i < subdata.length; i++) {
				info += '<li><a class="dropdown-item" onClick="_filemanage.Open(\'' + data.rid + '\',\'' + subdata[i].extid + '\')" href="javascript:;"><img class="filee-icon" src="' + subdata[i].icon + '"><span class="file-text">' + subdata[i].name + '</span></a></li>';
			}
			//增加选择默认
			info +='<li><hr class="dropdown-divider"></li>';
			info +='<li><a class="dropdown-item" onClick="_filemanage.setOpenDefault(\''+data.rid+'\');" href="javascript:;"><i class="mdi mdi-cog pe-2"></i>'+__lang.set_default_open+'</a>';
			info +='</li>';
		}
		info +='<li class="browserOpen"><a class="dropdown-item" onClick="_filemanage.browserOpen(\''+data.rid+'\');" href="javascript:;"><i class="mdi mdi-web pe-2"></i>'+__lang.open_browser+'</a></li>';
		el.find('.openwith').find('ul.dropdown-menu').html(info);
	}
	if (!_explorer.Permission('download', data)) {
		el.find('.browserOpen').remove();
		if(subdata === false) el.find('.openwith').remove();
	}
	//如果在收藏页面去掉删去和剪切和重命名
	if (_filemanage.winid.indexOf('collect') != -1) {
		el.find('.cut,.delete,.rename').remove();
	}
	if (_explorer.hash.indexOf('cloud') != -1) {
		el.find('.collect').remove();
		el.find('.clone').remove();
		el.find('.history').remove();
		el.find('.dynamic').remove();
	}
	_filemanage.SetMoreButton();
};

_filemanage.SetMoreButton = function () {
	var el = $('.navtopheader .select-toperate');
	if (!el.length) return;
	var width = el.width() - el.find('.yunfile-moreMenu').outerWidth(true);
	if (width <= 0) return;
	var yunfileButton = el.find('.yunfile-btnMenu');
	yunfileButton.children().hide();
	var totalWidth = 40;
	yunfileButton.children().each(function() {
		var el1 = $(this);
		el1.show();
		var btnWidth = el1.outerWidth(true);
		if (totalWidth + btnWidth > (width - 40)) {
			el1.hide();
		} else {
			totalWidth += btnWidth;
		}
	});

	var classNames = [
		'open', 'copy', 'rename', 'cut', 'download', 
		'downpackage', 'delete', 'share', 'collect', 
		'paste', 'openwith', 'history', 'dynamic'
	];
	
	yunfileButton.children().each(function () {
		var el1 = jQuery(this);
		for (var i = 0; i < classNames.length; i++) {
			var className = classNames[i];
			if (el1.hasClass(className)) {
				var target = el.find('.yunfile-moreMenu .' + className);
				if (el1.is(':hidden')) {
					target.show();
				} else {
					target.hide();
				}
			}
		}
	});
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
	if (this.keyword) {
		data_sorted = _filemanage.Sort(_filemanage.Search(this.data, this.keyword), this.disp, this.asc);
		jQuery('#searchInput_' + this.id).val(this.keyword);
	} else {
		data_sorted = _filemanage.Sort(this.data, this.disp, this.asc);
	}
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
	//判断copy
	if (!_explorer.Permission('copy', obj)) {
		el.find('.copy').remove();
	}
	//判断粘贴
	if (!_explorer.Permission('upload', obj) || _explorer.cut.icos.length < 1 || _filemanage.fid < 1) {
		el.find('.paste').remove();
	}

	//分享权限
	if (!_explorer.Permission('share', obj)) {
		el.find('.share').remove();
	}

	//重命名权限
	if (!_explorer.Permission('rename', obj)) {
		el.find('.rename').remove();
	} 

	//下载权限
	if (!_explorer.Permission('download', obj)) {
		el.find('.download').remove();
		el.find('.downpackage').remove();
	}
	//ftp的chmod权限
	if (!_explorer.Permission('chmod', obj)) {
		el.find('.chmod').remove();
	}
	//删除权限
	if (!_explorer.Permission('delete', obj)) {
		el.find('.cut').remove();
		el.find('.delete').remove();
	}
	//不允许删除的情况
	if (obj.notdelete > 0 && obj.type == 'app') {
		el.find('.delete').remove();
		el.find('.cut').remove();
		el.find('.copy').remove();
	}
	//多选时的情况
	var collects = 0;
	if (_filemanage.selectall.icos.length > 1 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		if(obj.isdelete == 1){
			el.find('.menu-item:not(.recover,.finallydelete,.downpackage)').remove();
		}else{
			el.find('.menu-item:not(.delete,.cut,.copy,.restore,.downpackage,.property,.collect,.paste,.share,.more-action)').remove();
		}
		var pd = 1;
		for (var i = 0; i < _filemanage.selectall.icos.length; i++) {
			var ico = _explorer.sourcedata.icos[_filemanage.selectall.icos[i]];
			if (ico.collect) collects += 1;
			if (!_explorer.Permission('download', ico)) {
				pd = 0;
				break;
			}
		}
		if (!pd) {
			el.find('.downpackage').remove();
		}
		el.find('.download').remove();
		if (collects == _filemanage.selectall.icos.length) {//区别是已收藏时，菜单显示取消收藏
			el.find('.collect .layui-menu-item-text').html(__lang.cancel_collection);
			el.find('.collect i').removeClass('mdi-star').addClass('mdi-star-minus');
		}
	} else {
		if (obj.collect) {
			el.find('.collect .layui-menu-item-text').html(__lang.cancel_collection);
			el.find('.collect i').removeClass('mdi-star').addClass('mdi-star-minus');
		}
		el.find('.downpackage').remove();
	}

	if (obj.isdelete == 1) {
		el.find('.menu-item:not(.recover,.finallydelete,.property,.download,.downpackage)').remove();
	} else {
		el.find('.finallydelete').remove();
		el.find('.recover').remove();
	}
	if(_filemanage.winid.indexOf('collect') != -1){
		el.find('.cut').remove();
		el.find('.copy').remove();
		el.find('.paste').remove();
	}
	//如果在收藏页面去掉删去和剪切和重命名
	if(_filemanage.winid.indexOf('collect') != -1){
		el.find('.cut,.delete,.rename').remove();
	}
	if (_explorer.hash.indexOf('cloud') != -1) {
		el.find('.collect').remove();
		el.find('.clone').remove();
		el.find('.history').remove();
		el.find('.dynamic').remove();
	}
	if (!el.find('.menu-item').length) {
		el.hide();
		return;
	}
	//判断打开方式
	var html = '';
	var subdata = getExtOpen(obj.type == 'shortcut' ? obj.tdata : obj);
	if (subdata === true) {
		el.find('.openwith').remove();
	} else if (subdata === false) {
		el.find('.open').remove();
	} else if (subdata.length > 0) {
		for (var i = 0; i < subdata.length; i++) {
			html += '<li class="menu-item" onClick="_filemanage.Open(\'' + rid + '\',\'' + subdata[i].extid + '\');" title="' + subdata[i].name + '"><div class="layui-menu-body-title dropdown-item">';
			if (subdata[i].icon) {
				html += '<span class="pe-2"><img width="24px" height="24px" src=' + subdata[i].icon + '></span>';
			}
			html += subdata[i].name;
			html += '</div></li>';
		}
		//增加选择默认
		html +='<li class="layui-menu-item-divider"><div class="layui-menu-body-title dropdown-item"></div></li>';
		html +='<li class="layui-menu-item-parent menu-item" onClick="_filemanage.setOpenDefault(\''+rid+'\');">';
		html +='<div class="layui-menu-body-title dropdown-item"><i class="mdi mdi-cog pe-2"></i>'+__lang.set_default_open+'</div>';
		html +='</li>';
	}
	
	html +='<li class="layui-menu-item-parent menu-item browserOpen" onClick="_filemanage.browserOpen(\''+rid+'\');">';
	html +='<div class="layui-menu-body-title dropdown-item"><i class="mdi mdi-web pe-2"></i>'+__lang.open_browser+'</div>';
	html +='</li>';
	el.find('.openwithdata').html(html);
	if (!_explorer.Permission('download', obj)) {
		el.find('.browserOpen').remove();
		if(subdata === false) el.find('.openwith').remove();
	}
	el.find('.layui-menu-item-divider').each(function () {
		if (!jQuery(this).next().first().hasClass('menu-item') || !jQuery(this).prev().first().hasClass('menu-item')) jQuery(this).remove();
	});
	el.find('.more-action').each(function () {
		if (jQuery(this).find('.dropdown-menu > ul').find('.menu-item').length === 0) {
			jQuery(this).remove();
		}
	});
	el.find('.layui-menu-item-divider').each(function () {
        if (jQuery(this).next().hasClass('layui-menu-item-divider')) {
            jQuery(this).remove();
        }
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
		el.find('.paste').remove();
		if(_filemanage.winid != 'recycle-list'){
			el.find('.recoverall').remove();
			el.find('.deleteall').remove();
			el.find('.sort .disp4').remove();
		}else{
			el.find('.sort .disp2').remove();
			el.find('.sort .disp3').remove();
		}
	}else{
		el.find('.sort .disp4').remove();
		el.find('.recoverall').remove();
		el.find('.deleteall').remove();
	}
	if (!_explorer.Permission_Container('folder', fid)) {
		el.find('.newfolder').remove();
		el.find('.uploadfolder').remove();
	}
	if (!_explorer.Permission_Container('link', fid)) {
		el.find('.newlink').remove();
	}
	if (!_explorer.Permission_Container('dzzdoc', fid)) {
		el.find('.newdzzdoc').remove();
	}
	if (!_explorer.Permission_Container('upload', fid)) {
		el.find('.upload').remove();
		el.find('.paste').remove();
	}
	if (!_explorer.Permission_Container('newtype', fid)) {
		el.find('.newtext').remove();
		el.find('.newdoc').remove();
		el.find('.newexcel').remove();
		el.find('.newpowerpoint').remove();
		el.find('.newpdf').remove();
	}
	if (_explorer.cut.icos.length < 1) el.find('.paste').remove();
	
	if (!_explorer.Permission_Container('upload', fid)) {
		el.find('.upload').remove();
		el.find('.uploadfolder').remove();
	}
	//设置默认桌面

	//检测新建和上传是否都没有
	if (el.find('.create .menu-item').length < 1) {
		el.find('.create').remove();
	}
	if (el.find('.menu-item').length < 1) {
		el.hide();
		return;
	}
	if (_explorer.hash.indexOf('cloud') != -1) {
		el.find('.newlink').remove();
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
	var htmlContent = '';
	div.innerHTML = _filemanage.get_template(this.id, true);
	_explorer.Scroll($('.scroll-y'));
	var el = jQuery(div);
	el.find('.js-file-item-tpl').empty();
	jQuery('.middlecenter,.middle-recycle')
		.on('contextmenu', function (e) {
			e = e ? e : window.event;
			var tag = e.srcElement ? e.srcElement : e.target;
			if (/input|textarea/i.test(tag.tagName)) {
				return true;
			}
			var options = {
				content: contextmenubody(self.fid)
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
	jQuery(document).off('click.select-toperate').on('click.select-toperate', '.mdi-close', function () {
		var hash = location.hash;
		jQuery('.navtopheader').css('display', 'none');
		el.find('.Icoblock').removeClass('Icoselected');
		_filemanage.selectall.icos = [];
		_filemanage.setInfoPanel();
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
				if (_explorer.hash.indexOf('cloud') != -1) {
					self.fid = _filemanage.rid;
				}
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
		$.getScript(MOD_PATH + '/js/uplodfile.js', function () {
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
	//this.PageInfo();
};
_filemanage.prototype.selectInfo = function () {
	var self = this;
	if (this.selectinfoTimer) {
		window.clearTimeout(this.selectinfoTimer);
	}
	this.selectinfoTimer = window.setTimeout(function () {
		self._selectInfo();
	}, 20);
};
_filemanage.prototype._selectInfo = function () {
	_filemanage.setInfoPanel(); //文件详细信息

	//设置全选框信息
	//设置全选按钮的文字
	var sum = _filemanage.selectall.icos.length;
	var total = jQuery('#filemanage-' + this.id).find('.Icoblock').length;
	var html = jQuery('#template_file').html();
	var hash = location.hash;
	if (sum > 0) { //有选中
		jQuery('.navtopheader').css('display', 'block');
		jQuery('.navtopheader').html(html);
		jQuery('.selectall-box .select-info').html('<span class="num">' + sum + '</span>个选中');
		jQuery('.docunment-allfile').hide();
		_explorer.toggleRight();
		if (sum >= total) { //全部选中
			jQuery('.select-all').addClass('Icoselected');
			jQuery('.select-all').removeClass('mdi-checkbox-intermediate').addClass('mdi-checkbox-marked');
			jQuery('.select-all').attr('title', '取消全选');
		} else { //部分选中
			jQuery('.select-all').removeClass('mdi-checkbox-marked').addClass('mdi-checkbox-intermediate');
			jQuery('.select-all').removeClass('Icoselected');
			jQuery('.select-all').attr('title', '全选');
		}
		if (hash.indexOf('recycle') != -1 || hash.indexOf('isdelete') != -1) {
			jQuery('.recycle-option-icon').show();
		}
	} else { //没有选中
		jQuery('.navtopheader').css('display', 'none');
		jQuery('.navtopheader').html('');
		jQuery('.select-all').removeClass('Icoselected');
		jQuery('.selectall-box .select-info').html('');
		jQuery('.docunment-allfile').show();
		if (hash.indexOf('recycle') != -1) {
			jQuery('.recycle-option-icon').hide();
		}
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
	var keyword = jQuery('#searchInput_' + this.id).value;
	if (!keyword || keyword === __lang.search) {
		keyword = '';
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
	if (_explorer.hash.indexOf('cloud') != -1) {
		var marker = '';
	} else {
		var marker = this.fid ? _explorer.sourcedata.folder[this.fid].nextMarker : '';
	}
	_filemanage.getData(url + '&exts=' + this.exts + '&tags=' + this.tags + '&disp=' + this.disp + '&fids=' + this.fids + '&gid=' + this.gid + '&before=' + this.before + '&after=' + this.after + '&asc=' + this.asc + '&iconview=' + this.view + '&keyword=' + encodeURI(keyword) + '&page=' + page + '&marker=' + marker + '&t=' + new Date().getTime(), function () {
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
	}
};
_filemanage.prototype.tddrager_start = function (e) {
	this.XX = e.clientX;
	document.getElementById('_blank').style.cursor = 'e-resize';
	jQuery('#_blank').show();
	//var self=this;
	this.AttachEvent(e);
	//document.onmousemove=function(e){self.tddraging(e?e:window.event);return false;};
	//document.onmouseup=function(e){self.tddraged(e?e:window.event);return false;};
	eval("document.onmousemove=function(e){" + this.string + ".tddraging(e?e:window.event);};");
	eval("document.onmouseup=function(e){" + this.string + ".tddraged(e?e:window.event);};");
};
_filemanage.prototype.tddraging = function () {
	document.body.style.cursor = 'e-resize';

};
_filemanage.prototype.tddraged = function (e) {
	this.DetachEvent(e);
	jQuery('#_blank').hide();
	//document.getElementById('_blank').style.cursor="url('dzz/images/cur/aero_arrow.cur'),auto";
	//document.body.style.cursor="url('dzz/images/cur/aero_arrow.cur'),auto";
	var xx = e.clientX - this.XX;
	//计算新的各个td的百分比
	var right_width = _window.windows[this.winid].bodyWidth - jQuery('#jstree_area').width();
	var current_width = right_width * this.detailper[this.tddrager_disp] / 100;
	var width = xx + current_width;
	//if(width>right_width-150) width=right_width-200;
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
_filemanage.prototype.DetachEvent = function () {

	//document.body.style.cursor="url('dzz/images/cur/aero_arrow.cur'),auto";
	document.onmousemove = _filemanage.onmousemove;
	document.onmouseup = _filemanage.onmouseup;
	document.onselectstart = _filemanage.onselectstart;
};
_filemanage.prototype.AttachEvent = function (e) {
	_filemanage.onmousemove = document.onmousemove;
	_filemanage.onmouseup = document.onmouseup;
	_filemanage.onselectstart = document.onselectstart;
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
_filemanage.prototype.Resize = function () {
	_explorer.Scroll(jQuery('.scroll-y'));
};

_filemanage.Search = function (data, keyword) {
	var data1 = {};
	for (var i in data) {
		if (data[i].name.toLowerCase().indexOf(keyword.toLowerCase()) !== -1) {
			data1[i] = data[i];
		}
	}
	return data1;
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
			case 4:
				sarr[sarr.length] = (data[i].deldateline) + '___' + i;
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
_filemanage.get_template = function (sid, whole, disp, asc) {
	var obj = _filemanage.cons[sid];
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
	//判断打开的url中是否含有dzzjs:等特殊协议；为了安全，只有应用才可以
  /* if(obj.type=='app'){
	       if(obj.url.indexOf('dzzjs:')===0){
	           eval((obj.url.replace('dzzjs:','')));
	           return;
	       }else{
	           window.open(obj.url);
	       }
    }else*/ if (obj.type === 'link') {
        addstatis(rid);
		window.open(data.url);
		return;
	} else if (obj.type === 'dzzdoc') {
		obj.url = "index.php?mod=document&icoid=" + obj.id;
		if(atdingding){ 
			window.open( encodeURI(SITEURL+"index.php?mod=dingtalk&op=loginfromding&redirecturl="+encodeURIComponent(obj.url)) );
		}else{
			window.open(obj.url);
		} 
		addstatis(obj.id);
		return;
	} else if (obj.type === 'folder') {
		var hash = '';
		var fid = data.oid;
		if (data.gid > 0) {
			hash = '#group&do=file&gid=' + data.gid + (fid > 0 ? '&fid=' + fid : '');
		}else if (data.bz && data.bz !== 'dzz') {
			hash = '#cloud&bz=' + data.bz + '&path=' + data.path;
		}  else {
			hash = '#home&do=file&fid=' + fid;
		}
        addstatis(rid);
		window.location.hash = hash;
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
                addstatis(rid);
				jQuery('.Icoblock[rid=' + rid + '] img[data-original]').trigger('click');
				return;
			} else if (extdata_url.indexOf('dzzjs:') === 0) {
				eval((extdata_url.replace('dzzjs:','')));
				addstatis(rid);
				return;
			} else {
				if(atdingding){
					var extdata_url=encodeURI(SITEURL+"index.php?mod=dingtalk&op=loginfromding&redirecturl="+encodeURIComponent(extdata_url));
				}
				window.open(extdata_url);
				addstatis(rid);
			}
		}
	} else {
		layer.alert('文件没有可以打开的应用');
	}
};

//获取打开方式

function getExtOpen(data, isdefault) {

	if (data.type === 'folder' || data.type === 'user' || data.type === 'app' || data.type === 'pan' || data.type === 'storage' || data.type === 'disk' || data.type === 'link') {
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

_filemanage.collect = function (rid) {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	var dpaths = [];
	var collects = 0;
	var collect = 1;
	var ico = null;
	var i = 0;
	var isalert = false;
	//	console.log(_filemanage.selectall.icos.length);
	if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		for (i = 0; i < _filemanage.selectall.icos.length; i++) {
			ico = _explorer.sourcedata.icos[_filemanage.selectall.icos[i]];
			if (ico.collect) {
				collects += 1;
			}
			dpaths.push(ico.dpath);
		}
		if (collects === _filemanage.selectall.icos.length) {
			collect = 0;
		}
	} else {
		ico = _explorer.sourcedata.icos[rid];
		if (ico.collect) {
			collect = 0;
		}
		dpaths = [ico.dpath];
	}
	//var path=encodeURIComponent(dpaths.join(','));
	if (dpaths.length) {
		$.post(_explorer.appUrl + '&op=ajax&do=collect', {
			"paths": dpaths,
			'collect': collect
		}, function (json) {
			if (json.error) {
				layer.alert(json.error, {skin:'lyear-skin-danger'});
			} else {
				var msg = '';
				if (collect === 0) {					
					if (_filemanage.subfix === 'collect') {//收藏页面中
						for (var key in json.msg) {
							if (json.msg[key] === 'success') {
								msg += '' + _explorer.sourcedata.icos[key].name + __lang.cancle_collect_success + '</p>';
								_filemanage.removerid(key);
								
								total--;
							} else {
								isalert = true;
								msg += '<p class="text-danger">' + _explorer.sourcedata.icos[key].name + json.msg[key].error + '</p>';
							}
						}
						_filemanage.showTemplatenoFile(containid, total);
					} else {
						for (var i in json.msg) {
							if (json.msg[i] === 'success') {
								_explorer.sourcedata.icos[rid].collect = 0;
								msg += '<p>' + _explorer.sourcedata.icos[i].name + __lang.cancle_collect_success + '</p>';
								jQuery('#' + containid + ' .Icoblock[rid=' + i + ']').find('.colllection-item').addClass('hide');
							} else {
								isalert = true;
								msg += '<p class="text-danger">' + _explorer.sourcedata.icos[i].name + json.msg[i].error + '</p>';
							}
						}
					}
				} else {
					for (var i in json.msg) {
						if (json.msg[i] === 'success') {							
							msg += '<p>' + _explorer.sourcedata.icos[i].name + __lang.collect_success + '</p>';
							_explorer.sourcedata.icos[rid].collect = 1;
							jQuery('#' + containid + ' .Icoblock[rid=' + i + ']').find('.colllection-item').removeClass('hide');
						} else {
							isalert = true;
							msg += '<p class="text-danger">' + _explorer.sourcedata.icos[i].name + json.msg[i].error + '</p>';
						}
					}
				}
				if (isalert) {
					layer.alert(msg);
				} else {
					layer.msg(msg, {offset:'10px'});
				}
			}
		}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
			layer.msg(__lang.do_failed, {offset:'10px'});
        });
	}
	return;
};
_filemanage.property = function (id, isfolder) {
	var path = '';
	var bz = '';
	var fid = '';
	var rid = '';
	if (_explorer.hash.indexOf('cloud') != -1) {
		bz = '1';
	}
	if (isfolder) {
		if(bz) {
			var folder = _explorer.sourcedata.folder[id];
			fid = encodeURIComponent(folder.path);
		} else {
			var ridinput = $('#ridinput').val();
			if(ridinput) {
				rid = ridinput;
			} else {
				fid = id;
			}
		}
	} else if (!bz && _filemanage.selectall.icos.length == 1) {
		rid = id;
	} else {
		var dpaths = [];
		var ico = null;
		if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(id, _filemanage.selectall.icos) > -1) {
			for (var i = 0; i < _filemanage.selectall.icos.length; i++) {
				ico = _explorer.sourcedata.icos[_filemanage.selectall.icos[i]];
				dpaths.push(ico.dpath);
			}
		} else {
			ico = _explorer.sourcedata.icos[id];
			dpaths = [ico.dpath];
		}
		path = encodeURIComponent(dpaths.join(','));
	}
	showWindow('property', _explorer.appUrl + '&op=ajax&do=property&bz='+bz+'&paths=' + path+'&fid=' +fid + '&rid=' + rid, 'get', 0);
};
_filemanage.share = function (rid, rids) {
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var bz = '';
	if (_explorer.hash.indexOf('cloud') != -1) {
		bz = '&bz='+1;
	}
	var dpaths = [];
	var path = '';
	var numperg = /^\d+$/;
	if (numperg.test(rid)) {
		dpaths.push(rid);
		path = rid;
	} else {
		var ico = null;
		if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1 && !rids) {
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
	if (_explorer.hash.indexOf('cloud') != -1) {
		if (dpaths.length > 1){
			layer.alert('网络挂载文件不支持分享多个。', {skin:'lyear-skin-danger'});
			return;
		}
	}
	if (dpaths.length > 0) {
		showWindow('share', _explorer.appUrl + '&op=ajax&do=share&paths=' + path+bz, 'get', 0);
	}
};
_filemanage.downAttach = function (id) {
	//if(_explorer.Permission('download','',id)) {
	if (!id) {
		id = _filemanage.selectall.icos[0];
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
_filemanage.downThumb = function (id) {
	//if(_explorer.Permission('download','',id)) {

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

_filemanage.downpackage = function () {
	//if(_explorer.Permission('download','',id)) {
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
	var url = DZZSCRIPT + '?mod=io&op=download&path=' + path + '&t=' + new Date().getTime();
	if (BROWSER.ie) {
		window.open(url);
	} else {
		window.frames.hideframe.location = url;
	}
	//}
	return false;
};

_filemanage.NewIco = function (type, fid) {
	if (!fid && !_filemanage.fid) {
		return;
	}
	if (!fid) {
		fid = _filemanage.fid;
	}
	var bz ='';
	if (_explorer.hash.indexOf('cloud') != -1) {
		bz = _explorer.sourcedata.folder[fid];
		bz = bz.path;
	}

	if (type === 'newFolder') {
		showWindow('newFolder', _explorer.appUrl + '&op=ajax&do=' + type + '&fid=' + fid+'&bz='+bz, 'get', 0);
	} else if (type === 'newLink') {
		showWindow('newLink', _explorer.appUrl + '&op=ajax&do=' + type + '&fid=' + fid, 'get', 0);
	} else {
		$.post(_explorer.appUrl + '&op=ajax&do=newIco&type=' + type, {
			'fid': fid,
			'bz': bz
		}, function (data) {
			if (data.msg === 'success') {
				_explorer.sourcedata.icos[data.rid] = data;
				_filemanage.cons['f-' + fid].CreateIcos(data);
                _filemanage.addIndex(data);
				_filemanage.rename(data.rid);
				layer.msg('已创建：'+data.name, {offset:'10px'});
            } else {
				layer.alert(data.error, {skin:'lyear-skin-danger'});
			}
		}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
			layer.msg(__lang.do_failed, {offset:'10px'});
        });
	}
};
//增加索引
_filemanage.addIndex = function(data){
	if(data.bz) return;
	if(data.filetype != 'folder' && data.filetype != 'link'){
        $.post(MOD_URL+'&op=ajax&do=addIndex',{
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
_filemanage.updateIndex = function(data){
    if(data.type != 'folder' && data.type != 'link'){
        $.post(MOD_URL+'&op=ajax&do=updateIndex',data,function(json){
            if(json['success']){

            }else{
                alert(json.error);
            }
        },'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage(__lang.do_failed, 'error', 3000, 1);
        });
    }
}
_filemanage.rename = function (id) {
	var ico = _explorer.sourcedata.icos[id];
	if (!ico) {
		return;
	}
	var filemanage = _filemanage.cons[_filemanage.winid];

	var el = jQuery('#file_text_' + id);
	el.closest('td').addClass('renaming');
	var filename = el.html();
	var html = '';
	if (filemanage.view > 3) {
		html = "<input type='text' class='form-control' name='text' id='input_" + id + "' style=\"width:" + (el.closest('td').width() - 110) + "px;padding:2px; \" value=\"" + filename + "\">";
	} else {
		html = "<input type='textarea' class='form-control' name='text' id='input_" + id + "' value=\"" + filename + "\">";
	}
	el.html(html);
	var ele = jQuery('#input_' + id);
	ele.select();
	ele.on('keyup', function (e) {
		e = e ? e : event;
		if (e.keyCode === 13) {
			jQuery(document).trigger('mousedown.file_text_' + id);
		}
	});
	jQuery(document).on('mousedown.file_text_' + id, function (e) {
		//var obj = event.srcElement ? event.srcElement : event.target;
		e = e ? e : window.event;
		var obj = e.srcElement ? e.srcElement : e.target;
		if (jQuery(obj).closest('#file_text_' + id).length < 1) {
			jQuery(document).off('.file_text_' + id);
			var text = ele.val() || "";
            var emptymatch = /^\s*$/;
            if(emptymatch.test(text)){
				el.html(filename);
				el.css('overflow', 'hidden');
				el.closest('td').removeClass('renaming');
				return false;
            }
			text = text.replace("\n", '');
			if (filename !== text) {
				_filemanage.Rename(id, text);
			} else {
				el.html(filename);
				el.css('overflow', 'hidden');
				el.closest('td').removeClass('renaming');
			}
			//jQuery('#content_'+filemanage.winid+' .icoblank[icoid="'+id+'"]').css('z-index',10);
		}
	});

};

_filemanage.Rename = function (rid, text) {
	var ico = _explorer.sourcedata.icos[rid];
	var filemanage = _filemanage.cons[_filemanage.winid];
	layer.msg('正在操作中，请不要关闭浏览器或刷新页面', {offset:'10px',time:0});
	jQuery.ajax({
		type: 'post',
		url: _explorer.appUrl + '&op=dzzcp&do=rename',
		data: {
			"text": text,
			"path": ico.dpath,
			"t": (new Date().getTime())
		},
		dataType: "json",
		success: function (json) {
			if(json.bz){
				json.rid = rid;
			}
			if (json.rid) {
				_explorer.sourcedata.icos[json.rid].name = json.name;
				filemanage.data[json.rid].name = json.name;
				filemanage.CreateIcos(_explorer.sourcedata.icos[json.rid], true);
				var updatedatas = {'arr[rid]':json.rid,'arr[name]':json.name,'arr[vid]':json.vid,'type':json.type};
                _filemanage.updateIndex(updatedatas);
				_filemanage.prototype._selectInfo();
				layer.msg('已重命名为：'+json.name, {offset:'10px'});
			} else {
				jQuery('#file_text_' + rid).html(ico.name);
				if (json.error) {
					layer.msg(json.error, {offset:'10px'});
				}
			}
		},
		error: function () {
			jQuery('#file_text_' + rid).html(ico.name);
			layer.msg(__lang.js_network_error, {offset:'10px'});
		}
	});
};
_filemanage.deleteIndex=function(rids){
	$.post(MOD_URL+'&op=ajax&do=deleteIndex',{
       'rids':rids
    },function(json){
        if(json['success']){

        }else{
            alert(json.error);
        }
    },'json').fail(function (jqXHR, textStatus, errorThrown) {
		showmessage(__lang.do_failed, 'error', 3000, 1);
	});
}
//回收站删除时弹出框
_filemanage.finallyDelete = function (rid, noconfirm, title) {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var icosdata = _explorer.sourcedata.icos[rid];
	var path = [];
	var data = {};
	var isalert = false;
	if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		/*if(icosdata.bz && icosdata.bz){

		    for(var i in _filemanage.selectall.icos){
		        path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
		    }
		    data={rids:path,'bz':icosdata.bz};
		}else{*/
		for (var i in _filemanage.selectall.icos) {
			path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
		}
		data = {
			rids: path
		};
		// }
	} else {
		/* if(icosdata.bz && icosdata.bz){
		     data={rids:[icosdata.dpath],'bz':icosdata.bz};
		 }else{*/
		data = {
			rids: [icosdata.dpath]
		};
		//}
	}
	var url = _explorer.appUrl + '&op=dzzcp&do=finallydelete&t=' + new Date().getTime();
	layer.confirm(__lang.finally_delete_file_confirm, {title:'确定要删除文件？',skin:'lyear-skin-danger'}, function(index){
		layer.msg(__lang.deleting_not_please_close, {offset:'10px',time:0});
		jQuery.post(url, data, function (json) {
			var rids = [];
			var msg = '';
			for (var i in json.msg) {
				if (json.msg[i] === 'success') {
					msg += '<p>' + _explorer.sourcedata.icos[i].name + __lang.delete_success + '</p>';
					//_filemanage.removerid(i);
					rids.push(i);
					total--;
					_filemanage.showTemplatenoFile(containid, total);
				} else {
					isalert = true;
					msg += '<p class="text-danger">' + json.msg[i] + '</p>';
				}
			}
			if (isalert) {
				layer.alert(msg);
			} else {
				layer.msg(msg, {offset:'10px'});
			}
            _filemanage.deleteIndex(rids);
            _filemanage.removeridmore(rids);
		}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
			layer.msg(__lang.do_failed, {offset:'10px'});
        });
	});
};
//清空回收站
_filemanage.deleteAll = function () {
		var filemanage = _filemanage.cons[_filemanage.winid];
		var containid = 'filemanage-' + _filemanage.winid;
		var total = filemanage.total;
		var isalert = false;
		var url = _explorer.appUrl + '&op=dzzcp&do=emptyallrecycle&k=' + new Date().getTime();
		layer.confirm(__lang.finally_delete_file_confirm, {title:'您确定删除回收站所有文件吗？删除之后不可恢复',skin:'lyear-skin-danger'}, function(index){
			layer.msg(__lang.deleting_not_please_close, {offset:'10px',time:0});
			$.getJSON(url, function (data) {
				if(data.error){
					layer.alert(data.error, {skin:'lyear-skin-danger'});
					return false;
				}
				var rids = [];
				var msg = '';
				for (var i in data.msg) {
					if (data.msg[i] == 'success') {
						msg += '<p>' + data.name[i] + __lang.delete_success + '</p>';
						//_filemanage.removerid(i);
						rids.push(i);
						total--;
					} else {
						isalert = true;
						msg += '<p class="text-danger">' + data.msg[i] + '</p>';
					}
				}
				if (isalert) {
					layer.alert(msg);
				} else {
					layer.msg(msg, {offset:'10px'});
				}
				_filemanage.showTemplatenoFile(containid, total);
				_filemanage.deleteIndex(rids);
				_filemanage.removeridmore(rids);
			});
		});
	}
	//还原所有文件
_filemanage.recoverAll = function () {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	var isalert = false;
	var url = _explorer.appUrl + '&op=dzzcp&do=recoverAll&k=' + new Date().getTime();
	layer.confirm(__lang.recover_file_confirm, {title:'您确定恢复所有文件到原位置吗？',skin:'lyear-skin-warning'}, function(index){
		layer.msg(__lang.recovering_not_please_close, {offset:'10px',time:0});
		$.getJSON(url, function (data) {
            if(data.error){
				layer.alert(data.error, {skin:'lyear-skin-danger'});
                return false;
            }
			var rids = [];
			var msg = '';
			for (var i in data.msg) {
				if (data.msg[i] == 'success') {
					msg += '<p>' + data.name[i] + __lang.recover_success + '</p>';
					//_filemanage.removerid(i);
					rids.push(i);
					total--;
					_filemanage.showTemplatenoFile(containid, total);
				} else {
					isalert = true;
					msg += '<p class="text-danger">' + data.msg[i] + '</p>';
				}
			}
			if (isalert) {
				layer.alert(msg);
			} else {
				layer.msg(msg, {offset:'10px'});
			}
            _filemanage.removeridmore(rids);
		});
	});
}

_filemanage.RecoverFile = function (rid, noconfirm) {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var icosdata = _explorer.sourcedata.icos[rid];
	var path = [];
	var data = {};
	var isalert = false;
	if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		/*if(icosdata.bz && icosdata.bz){

		 for(var i in _filemanage.selectall.icos){
		 path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
		 }
		 data={rids:path,'bz':icosdata.bz};
		 }else{*/
		for (var i in _filemanage.selectall.icos) {
			path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
		}
		data = {
			rids: path
		};
		// }
	} else {
		/* if(icosdata.bz && icosdata.bz){
		 data={rids:[icosdata.dpath],'bz':icosdata.bz};
		 }else{*/
		data = {
			rids: [icosdata.dpath]
		};
		//}
	}
	var url = _explorer.appUrl + '&op=dzzcp&do=recoverFile&t=' + new Date().getTime();
	layer.msg(__lang.recovering_not_please_close, {offset:'10px',time:0});
	jQuery.post(url, data, function (json) {
		var rids = [];
		var msg = '';
		for (var i in json.msg) {
			if (json.msg[i] === 'success') {
				msg += '<p>' + _explorer.sourcedata.icos[i].name + __lang.recover_success + '</p>';
				//_filemanage.removerid(i);
				rids.push(i);
				_filemanage.showTemplatenoFile(containid, total);

			} else {
				isalert = true;
				msg += '<p class="text-danger">' + json.msg[i] + '</p>';
			}
		}
		if (isalert) {
			layer.alert(msg);
		} else {
			layer.msg(msg, {offset:'10px'});
		}
        _filemanage.removeridmore(rids);

	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		layer.msg(__lang.do_failed, {offset:'10px'});
	});
};

_filemanage.showTemplatenoFile = function (containid, total) {
	if (total < 1 && jQuery('#' + containid).find('.emptyPage').length == 0) {
		jQuery(jQuery('#template_nofile_notice').html()).appendTo(jQuery('#' + containid));
	} else {
		jQuery('#' + containid).find('.emptyPage').remove();
	}
}
_filemanage.delIco = function (rid, noconfirm) {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var icosdata = _explorer.sourcedata.icos[rid];
	if (!noconfirm) {
        var finallydelete = (_explorer.deletefinally == 1) ? true:false;
        //var finallydelete = false;
		if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
			if (_explorer.sourcedata.icos[_filemanage.selectall.icos[0]].isdelete > 0 || (_explorer.sourcedata.icos[_filemanage.selectall.icos[0]].bz && _explorer.sourcedata.icos[_filemanage.selectall.icos[0]].bz)) {
				layer.confirm((finallydelete) ?__lang.js_finallydelete_selectall:__lang.js_delete_selectall, {title:__lang.confirm_message,skin:'lyear-skin-danger'}, function(index){
					_filemanage.delIco(rid, 1);
				});
			} else {
				layer.confirm((finallydelete) ? __lang.js_finallydelete_selectall_recycle : __lang.js_delete_selectall_recycle, {title:__lang.confirm_message,skin:'lyear-skin-danger'}, function(index){
					_filemanage.delIco(rid, 1);
				});
			}
			return;
		} else if (_explorer.sourcedata.icos[rid].type === 'folder' && _explorer.sourcedata.folder[_explorer.sourcedata.icos[rid].oid] && _explorer.sourcedata.folder[_explorer.sourcedata.icos[rid].oid].iconum) {
			if (_explorer.sourcedata.icos[rid].isdelete > 0 || (_explorer.sourcedata.icos[rid].bz && _explorer.sourcedata.icos[rid].bz)) {
				layer.confirm((finallydelete) ? __lang.js_finallydelete_folder.replace('{name}', _explorer.sourcedata.icos[rid].name):__lang.js_delete_folder.replace('{name}', _explorer.sourcedata.icos[rid].name), {title:__lang.confirm_message,skin:'lyear-skin-danger'}, function(index){
					_filemanage.delIco(rid, 1);
				});
			} else {
				layer.confirm((finallydelete) ? __lang.js_finallydelete_folder_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name):__lang.js_delete_folder_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name), {title:__lang.confirm_message,skin:'lyear-skin-danger'}, function(index){
					_filemanage.delIco(rid, 1);
				});
			}
			return;
		} else {
			if (_explorer.sourcedata.icos[rid].isdelete > 0 || (_explorer.sourcedata.icos[rid].bz && _explorer.sourcedata.icos[rid].bz)) {
				layer.confirm((finallydelete) ? __lang.js_finallydelete_confirm.replace('{name}', _explorer.sourcedata.icos[rid].name) : __lang.js_delete_confirm.replace('{name}', _explorer.sourcedata.icos[rid].name), {title:__lang.confirm_message,skin:'lyear-skin-danger'}, function(index){
					_filemanage.delIco(rid, 1);
				});
			} else {
				layer.confirm((finallydelete) ? __lang.js_finallydelete_confirm_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name): __lang.js_delete_confirm_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name), {title:__lang.confirm_message,skin:'lyear-skin-danger'}, function(index){
					_filemanage.delIco(rid, 1);
				});
			}
			return;
		}
	}
	var path = [];
	var data = {};
	if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		if (icosdata.bz && icosdata.bz) {
			for (var i in _filemanage.selectall.icos) {
				path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
			}
			data = {
				rids: path,
				'bz': icosdata.bz
			};
		} else {
			for (var i in _filemanage.selectall.icos) {
				path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
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
	var url = _explorer.appUrl + '&op=dzzcp&do=deleteIco&t=' + new Date().getTime();
	layer.msg(__lang.deleting_not_please_close, {offset:'10px',time:0});
	jQuery.post(url, data, function (json) {
		var rids = [];
		var msg = '';
		var isalert = false;
		for (var i in json.msg) {
			if (json.msg[i] === 'success') {
				msg += '<p>' + _explorer.sourcedata.icos[i].name + __lang.delete_success + '</p>';
				//_filemanage.removerid(i);
				rids.push(i);
				total--;
				_filemanage.showTemplatenoFile(containid, total);
			} else {
				isalert = true;
				msg += '<p class="text-danger">' + json.msg[i] + '</p>';
			}
		}
		if (isalert) {
			layer.alert(msg);
		} else {
			layer.msg(msg, {offset:'10px'});
		}
        _filemanage.removeridmore(rids);
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		layer.msg(__lang.do_failed, {offset:'10px'});
	});
};
_filemanage.removeridmore = function(rids){
	if(rids.length > 1){
		var rid = rids[0];
        var data = _explorer.sourcedata.icos[rid];
        var containerid = 'filemanage-' + _filemanage.winid;
        var types = [];
        for(var o in rids){
        	var currentrid = rids[o];
            jQuery('#' + containerid + ' .Icoblock[rid=' + currentrid + ']').remove();
            var currentdata = _explorer.sourcedata.icos[currentrid];
            types.push(currentdata.type);
		}
		if($.inArray('folder',types) != -1){
            var node = jQuery('#position').jstree(true).get_node(data.gid > 0 ? (data.type > 0 ? '#g_' + data.gid : '#gid_' + data.gid) : '#f-' + data.oid);
            jQuery('#position').jstree('refresh', node);
            jQuery('#position').jstree('correct_state', node);
		}
        var filemanage = _filemanage.cons[_filemanage.winid];
		for(var o in rids){
            var currentrid = rids[o];
            //删除选中列表
            var i = jQuery.inArray(currentrid, _filemanage.selectall.icos);
            if (i > -1) {
                _filemanage.selectall.icos.splice(i, 1);
            }
            delete filemanage.data[currentrid];
            delete filemanage.currentdata['icos_' + currentrid];
            filemanage.sum--;
            filemanage.total--;
            filemanage.selectInfo();
            _filemanage.stack_run(filemanage.winid); //删除时如果有未显示的接着显示
		}

	}else{
        _filemanage.removerid(rids[0]);
	}

}
_filemanage.removerid = function (rid) {
	if (!rid) return;
	//var self=this;
	var data = _explorer.sourcedata.icos[rid];
	if (!data) return;
	//this.asc= this.asc?1:0;
	var containerid = 'filemanage-' + _filemanage.winid;
	var el = jQuery('#' + containerid + ' .Icoblock[rid=' + rid + ']');
	el.remove();
	if (data.type === 'folder') {
		var node = jQuery('#position').jstree(true).get_node(data.gid > 0 ? (data.type > 0 ? '#g_' + data.gid : '#gid_' + data.gid) : '#f-' + data.oid);
		jQuery('#position').jstree('refresh', node);
		jQuery('#position').jstree('correct_state', node);
	}
	var filemanage = _filemanage.cons[_filemanage.winid];
	//删除选中列表
	var i = jQuery.inArray(rid, _filemanage.selectall.icos);
	if (i > -1) {
		_filemanage.selectall.icos.splice(i, 1);
	}
	delete filemanage.data[rid];
	delete filemanage.currentdata['icos_' + rid];
	filemanage.sum--;
	filemanage.total--;
	filemanage.selectInfo();
	_filemanage.stack_run(filemanage.winid); //删除时如果有未显示的接着显示


};
//文件复制
_filemanage.copy = function (rid,fid) {
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var icosdata = _explorer.sourcedata.icos[rid];
	var path = [];
	var bzrid = [];
	var data = {};
	if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		if (icosdata.bz) {
			for (var i in _filemanage.selectall.icos) {
				path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
				bzrid.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].rid);
			}
			data = {
				rids: path,
				rid: bzrid,
				'bz': icosdata.bz
			};
		} else {
			for (var i in _filemanage.selectall.icos) {
				path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
			}
			data = {
				rids: path
			};
		}
	} else {
		if (icosdata.bz) {
			data = {
				rids: [icosdata.dpath],
				rid: [rid],
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
	var url = _explorer.appUrl + '&op=dzzcp&do=copyfile&t=' + new Date().getTime();
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
			layer.msg(filenames + __lang.copy_success, {offset:'10px'});
			if(fid) {_filemanage.paste(fid);}
		} else if(json.msg) {
			layer.alert(json.msg, {skin:'lyear-skin-danger'});
		} else {
			layer.msg(__lang.do_failed, {offset:'10px'});
		}
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		layer.msg(__lang.do_failed, {offset:'10px'});
	});
};
//文件剪切
_filemanage.cut = function (rid) {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var icosdata = _explorer.sourcedata.icos[rid];
	var path = [];
	var bzrid = [];
	var data = {};
	if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		if (icosdata.bz && icosdata.bz) {
			for (var i in _filemanage.selectall.icos) {
				path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
				bzrid.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].rid);
			}
			data = {
				rids: path,
				rid: bzrid,
				'bz': icosdata.bz
			};
		} else {
			for (var i in _filemanage.selectall.icos) {
				path.push(_explorer.sourcedata.icos[_filemanage.selectall.icos[i]].dpath);
			}
			data = {
				rids: path
			};
		}
	} else {
		if (icosdata.bz && icosdata.bz) {
			data = {
				rids: [icosdata.dpath],
				rid: [rid],
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
	var url = _explorer.appUrl + '&op=dzzcp&do=copyfile';
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
			_filemanage.showTemplatenoFile(containid, total);
			filenames = filenames.substr(0, filenames.length - 1);
			layer.msg(filenames + __lang.cut_success, {offset:'10px'});
		} else if(json.msg) {
			layer.alert(json.msg, {skin:'lyear-skin-danger'});
		} else {
			layer.msg(__lang.do_failed, {offset:'10px'});
		}

	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		layer.msg(__lang.do_failed, {offset:'10px'});
	});
};
//粘贴
_filemanage.paste = function (fid) {
	var folder = _explorer.sourcedata.folder[fid];
	if (!folder) {
		showmessage(__lang.folder_not_exist,'danger',3000,1,'center');
		return false;
	}
	if(folder.bz) {
		folder.fid = folder.path;
	}
	var data = {
		'tpath': folder.fid,
		'tbz': folder.bz
	};
	var url = _explorer.appUrl + '&op=dzzcp&do=paste';
	var i = 0;
	var node = null;
	layer.msg('正在粘贴，请不要关闭浏览器或刷新页面，也不要进行其他操作。', {offset:'10px',time:0});
	jQuery.post(url, data, function (json) {
		if(!json){
			layer.alert(__lang.paste_failure, {skin:'lyear-skin-danger'});
		}else if(json.error){
			layer.alert(json.error, {skin:'lyear-skin-danger'});
		}else{
			if (fid === _filemanage.fid) {
				if (json.folderarr) {
					for (i = 0; i < json.folderarr.length; i++) {
						_explorer.sourcedata.folder[json.folderarr[i].fid] = json.folderarr[i];
					}
					node = jQuery('#position').jstree(true).get_node(folder.gid > 0 ? (folder.type > 0 ? '#g_' + folder.gid : '#gid_' + folder.gid) : '#f-' + folder.pfid);
					jQuery('#position').jstree('refresh', node);
					jQuery('#position').jstree('correct_state', node);
				}
				if (json.icoarr) {
					var filemanage = _filemanage.cons['f-' + fid];
					var addIndex = (json['copytype']) ? true:false;
					for (i = 0; i < json.icoarr.length; i++) {
						if (json.icoarr[i].pfid == filemanage.fid) {
							_explorer.sourcedata.icos[json.icoarr[i].rid] = json.icoarr[i];
							filemanage.CreateIcos(json.icoarr[i]);
							if(addIndex){
								_filemanage.addIndex(json.icoarr[i]);
							}
						}
					}
					layer.msg('粘贴成功', {offset:'10px'});
				} else {
					layer.msg('粘贴成功', {offset:'10px'});
				}
				_explorer.cut.icos=[];
			}
			layer.msg('粘贴成功', {offset:'10px'});
		}
	}, 'json').fail(function(jqXHR,statusText){
		layer.alert(__lang.paste_failure, {skin:'lyear-skin-danger'});
	});
};
_filemanage.setOpenDefault = function(rid) {
    var icosdata = _explorer.sourcedata.icos[rid],t;
    var subdata = getExtOpen(icosdata),e = getExtOpen(icosdata, !0),
    html = '<ul  class="extopen nav-drawer nav-stacked nav-pills">',
    extdata = _explorer.extopen.all[e];
	for (html += '<li class="nav-item"><a class="extopen-item nav-link active" href="javascript:;" icoid="' + rid + '" extid="' + extdata.extid + '" onclick="jQuery(this).addClass(\'active\').closest(\'.nav-item\').parent().find(\'.nav-link\').not(this).removeClass(\'active\');"><img class="icon_32_32" src="' + extdata.icon + '">' + extdata.name + "</a></li>", t = 0; t < subdata.length; t++) {
		if (e == subdata[t].extid) continue;
		html += '<li class="nav-item"><a class="extopen-item nav-link" href="javascript:;" icoid="' + rid + '" extid="' + subdata[t].extid + '" onclick="jQuery(this).addClass(\'active\').closest(\'.nav-item\').parent().find(\'.nav-link\').not(this).removeClass(\'active\');"><img class="icon_32_32" src="' + subdata[t].icon + '">' + subdata[t].name + "</a></li>"
	}
    html += "</ul>",
    html += '<label class="col-sm-12"><input type="checkbox" class="form-check-input" id="extopen_setDefault" checked="checked" value="1">  ' + __lang.always_choose_open_app + "</label>",
    showDialog(html, "message", __lang.choose_open_file_app, _filemanage.setOpenDefaultOK, 1)
};
_filemanage.setOpenDefaultOK = function() {
    var t = jQuery(".extopen-item.active"),n = t.attr("extid"),i = t.attr("icoid");
    jQuery("#extopen_setDefault").prop("checked") > 0 && jQuery.post(_explorer.appUrl + "&op=ajax&do=setExtopenDefault", {
        extid: n
    },
    function() {
        _explorer.extopen.user[_explorer.extopen.all[n].ext] = n
    }),
    _filemanage.Open(i, n)
};
_filemanage.browserOpen = function(rid) {
	var icosdata = _explorer.sourcedata.icos[rid];
	window.open(SITEURL + 'index.php?mod=io&op=fileOut&path=' + icosdata.dpath + '&filename=' + icosdata.name);
};
_filemanage.history = function(rid) {
	showWindow('property', _explorer.appUrl + '&op=ajax&do=version&property=1&rid=' + rid, 'get', 0);
};
_filemanage.dynamic = function(rid) {
	showWindow('property', _explorer.appUrl + '&op=ajax&do=dynamic&property=1&rid=' + rid, 'get', 0);
};