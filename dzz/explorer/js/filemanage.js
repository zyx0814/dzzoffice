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

	this.perpage = param.perpage;
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
_filemanage.showicosTimer = {};
_filemanage.apicacheTimer = {};
_filemanage.infoPanelUrl = '';
_filemanage.viewstyle = ['bigicon', 'middleicon', 'middlelist', 'smalllist', 'detaillist'];
_filemanage.getData = function (url, callback) {
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
			jQuery('.sizeMenu .icons-thumbnail').attr('iconview', obj.view).find('.dzz').removeClass('dzz-view-module').removeClass('dzz-view-list').addClass(obj.view === 2 ? 'dzz-view-list':'dzz-view-module');
            jQuery('.sizeMenu .icons-thumbnail').attr('iconview', obj.view).find('.dzz').attr('data-original-title',obj.view === 2 ? __lang.deltail_lsit : __lang.medium_icons);
			jQuery('.sizeMenu .icons-thumbnail').attr('folderid', obj.id);
			if (typeof (callback) === 'function') {
				callback(obj);
			}
		}
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
	jQuery('.sizeMenu .icons-thumbnail').attr('iconview', view).find('.dzz').removeClass('dzz-view-module').removeClass('dzz-view-list').addClass(view === 2 ?  'dzz-view-list':'dzz-view-module');
    jQuery('.sizeMenu .icons-thumbnail').attr('iconview', view).find('.dzz').attr('data-original-title',view === 2 ? __lang.deltail_lsit : __lang.medium_icons);
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
	//var classend=jQuery('#filemanage_view'+winid).attr('class').replace(/filemanage_view\d+_/i,'');
	//jQuery('#filemanage_view'+winid).attr('class','filemanage_view'+view+'_'+classend);
	jQuery('#right_contextmenu .menu-icon-iconview').each(function () {
		if (jQuery(this).attr('view') * 1 === view * 1) {
			jQuery(this).removeClass('dzz-check-box-outline-blank').addClass('dzz-check-box');
		} else {
			jQuery(this).addClass('dzz-check-box-outline-blank').removeClass('dzz-check-box');
		}
	});
};
_filemanage.Disp = function (obj, id, disp) {
	var filemanage = _filemanage.cons[id];
	if (filemanage.subfix === 'f') {
		var fid = filemanage.fid;
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
		_filemanage.infoPanelUrl='';
	} 
	if (!_explorer.infoPanelOpened || _explorer.infoPanel_hide) {
		return; //右侧面板没有打开的话，不加载文件详细信息
	}
	if (rids.length < 1) {
		var fid = _filemanage.fid || $('#fidinput').val();
		if (!fid) {
			var data = '<div class="nothing_message">'
			+'<div class="nothing_allimg">'
			+'<img src="dzz/explorer/img/noFilePage-FileChoice.png">'
			+'<p>'+__lang.choose_file_examine_information+'</p>'
			+'</div>'
			+'</div>';
			$('#rightMenu').html(data);
			_filemanage.infoPanelUrl = '';

			return false;
		}
		if (_filemanage.infoPanelUrl !== fid) {
			_explorer.infoRequest = $.post(MOD_URL + '&op=dynamic&do=getfolderdynamic', {
				'fid': fid
			}, function (data) {
				$('#rightMenu').html(data);
				_filemanage.infoPanelUrl = fid;
			});
		}
	} else if (rids.length === 1) {
		if (_filemanage.infoPanelUrl !== rids[0]) {
			_explorer.infoRequest = $.post(MOD_URL + '&op=dynamic&do=getfiledynamic', {
				'rid': rids
			}, function (data) {
				$('#rightMenu').html(data);
				_filemanage.infoPanelUrl = rids[0];
			});
		}
	} else {
		var ridsstr = rids.join(',');
		if (_filemanage.infoPanelUrl !== ridsstr) {
			_explorer.infoRequest = $.post(MOD_URL + '&op=dynamic&do=getfiledynamic', {
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
	html = html.replace(/\{fsize\}/g,((data.type === 'folder' || data.type === 'app' || data.type === 'shortcut') ? '': data.fsize));
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
	//__lang.some_day_after.replace('{day}', data.finallydate)
	html = html.replace(/\{finallydate\}/g, (data.finallydate > 0) ? __lang.some_day_after.replace('{day}', data.finallydate):__lang.within_a_day);
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
	if(data.status < 0){
     var sharestatus = '<span  style="color: red;">('+data.fstatus+')</span>';
	}else{
        sharestatus = '';
	}
	//收藏
	if(data.collect){
		var collectstatus = '<a href="javascript:;" class="dzz-colllection-item" ><i class="dzz dzz-star" title=""></i></a>';
	}else{
		var collectstatus = '<a href="javascript:;" class="dzz-colllection-item hide"><i class="dzz dzz-star" title=""></i></a>';
	}
	html = html.replace(/\{collectstatus\}/g,collectstatus);
    html = html.replace(/\{sharestatus\}/g,sharestatus);
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
		jQuery(html).appendTo('#' + containerid + ' .js-file-item-tpl');
		el = jQuery('.Icoblock[rid=' + data.rid + ']');
		jQuery('#shareinfo_' + data.rid).on('click', function (e) {
			return false;
		});

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
			var flag = true;
			if ((_hotkey.ctrl && Item.hasClass('Icoselected')) || (Item.hasClass('Icoselected') && _filemanage.selectall.icos.length === 1 && _filemanage.selectall.icos[0] === rid)) {
				flag = false;
			}
			var multi = _hotkey.ctrl ? true : false;
			_select.SelectedStyle('filemanage-' + self.id, jQuery(this).attr('rid'), flag, multi);
			//self.createBottom();
			return false;
		});
		/*}else{
			//el.find('.icoblank_rightbottom').remove();
		}*/

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
		//if(_filemanage.fid<1 || _explorer.Permission_Container('multiselect',this.fid)){

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
		/*}else{
			//el.find('.detail_item_td_select').parent().remove();
		}*/

	}
	el.on('dblclick', function (e) {
		if(!_filemanage.fid && (_filemanage.winid == 'recycle-list' || _filemanage.winid == 'share-list')) return true;
		var tag = e.srcElement ? e.srcElement : e.target;
		if (/input|textarea/i.test(tag.tagName)) {
			return true;
		}
		_filemanage.Open(el.attr('rid'));
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
		el.find('.collect a').html('<i class="glyphicon glyphicon-heart"></i> 取消收藏');
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
			for (i = 0; i < subdata.length; i++) {
				info += '			<li><a onClick="_filemanage.Open(\'' + data.rid + '\',\'' + subdata[i].extid + '\')" href="javascript:;"><img class="filee-icon" src="' + subdata[i].icon + '">' + subdata[i].name + '</a></li>';
			}
			el.find('.openwith').find('ul.dropdown-menu').html(info);
		}
	}
	//如果在收藏，搜索和最近使用页面去掉删去和剪切和重命名
	if (_filemanage.winid.indexOf('collect') != -1 || _filemanage.winid.indexOf('recent') != -1 || _filemanage.winid.indexOf('search') != -1) {
		el.find('.cut,.delete,.rename').remove();
	}
	_filemanage.SetMoreButton();
};

_filemanage.SetMoreButton = function () {
	var el = $('.navtopheader .toolButtons');
	if (!el.length) {
		return;
	}
	var width = el.width() - el.find('.yunfile-moreMenu').outerWidth(true);
	if (width <= 0) {
		return;
	}
	var yunfileButton = el.find('.yunfile-btnMenu');
	yunfileButton.find('button').hide();
	el.find('.yunfile-moreMenu>.dropdown-menu>li').show();
	//已经显示的在更多内不在显示
	yunfileButton.children().each(function () {
		var el1 = jQuery(this);
		jQuery(this).show();
		//		if(yunfileButton.width()>(width-15)){
		//			el1.hide();
		//			return;
		//		}
	});

	yunfileButton.children().each(function () {
		var el1 = jQuery(this);
		if (el1.hasClass('open')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu li.open').show();
			} else {
				el.find('.yunfile-moreMenu li.open').hide();
			}
		} else if (el1.hasClass('copy')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .copy').show();
			} else {
				el.find('.yunfile-moreMenu .copy').hide();
			}
		} else if (el1.hasClass('rename')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .rename').show();
			} else {
				el.find('.yunfile-moreMenu .rename').hide();
			}
		} else if (el1.hasClass('paste')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .paste').show();
			} else {
				el.find('.yunfile-moreMenu .paste').hide();
			}
		} else if (el1.hasClass('cut')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .cut').show();
			} else {
				el.find('.yunfile-moreMenu .cut').hide();
			}
		} else if (el1.hasClass('download')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .download').show();
			} else {
				el.find('.yunfile-moreMenu .download').hide();
			}
		} else if (el1.hasClass('downpackage')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .downpackage').show();
			} else {
				el.find('.yunfile-moreMenu .downpackage').hide();
			}
		} else if (el1.hasClass('delete')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .delete').show();
			} else {
				el.find('.yunfile-moreMenu .delete').hide();
			}
		} else if (el1.hasClass('share')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .share').show();
			} else {
				el.find('.yunfile-moreMenu .share').hide();
			}
		} else if (el1.hasClass('collect')) {
			if (el1.is(':hidden')) {
				el.find('.yunfile-moreMenu .collect').show();
			} else {
				el.find('.yunfile-moreMenu .collect').hide();
			}
		}
		/*else if(el1.hasClass('paste')){
					if(el1.is(':hidden')){
						el.find('.yunfile-moreMenu .paste').show();
					}else{
						el.find('.yunfile-moreMenu .paste').hide();
					}
				}*/
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
	jQuery('.middlecenter,.middle-recycle,.share-content')
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
				el.find('.selectall-box').removeClass('Icoselected');
				self.selectInfo();
			}
		})
		.end().find('.selectall-box').on('click', function () {
			var el = jQuery(this);
			var selectall = true;
			if (el.hasClass('Icoselected')) {
				el.removeClass('Icoselected');
				selectall = false;
				_filemanage.selectall.icos = [];
			} else {
				el.addClass('Icoselected');
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
			return false;
		});
	jQuery(document).off('click.cselect').on('click.cselect', '.dzz-backing-out', function () {
		var hash = location.hash;
		if (hash.indexOf('share') != -1) {
			jQuery('.deatisinfo').each(function () {
				jQuery(this).addClass('hide');
			});
		}
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
					//						 if(self.asc=0){
					//						 	jQuery(this).find('span').addClass('dzz-expand-more').removeClass('dzz-expand-less');
					//						 }
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
	}, 200);
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
		jQuery('.selectall-box').addClass('Icoselected');
		jQuery('.selectall-box .select-info').html('已选中<span class="num">' + sum + '</span>个文件');
		jQuery('.docunment-allfile').hide();
		if (!_explorer.infoPanelOpened || _explorer.infoPanel_hide) {
		$('.bs-main-container').css({
			'margin-right': '0px'
		});
		$('.rightMenu').css('right', '-320px');
		$('.toggRight').parent('li').removeClass('background-toggle').find('.dzz').attr("data-original-title", "开启右侧信息");
	} else {
		$('.bs-main-container').css({
			'margin-right': '300px'
		});
		$('.rightMenu').css('right', '0');
		$('.toggRight').parent('li').addClass('background-toggle').find('.dzz').attr("data-original-title", "关闭右侧信息");
	}
		if (sum >= total) { //全部选中
			jQuery('.selectall-box').addClass('Icoselected');
		}
		if (hash.indexOf('recycle') != -1 || hash.indexOf('isdelete') != -1) {
			jQuery('.recycle-option-icon').show();
			// jQuery('.select-toperate-right .toggRight').hide();
		}
		if (hash.indexOf('share') != -1) {
			jQuery('.share-option-icon').show();
			jQuery('.select-toperate-right').hide();
			jQuery('.select-toperate-right').show();
			if (sum == 1) {
				var shareinfo = _filemanage.selectall.icos[0];
				jQuery('.deatisinfo').each(function () {
					jQuery(this).addClass('hide');
				});
				var shareobj = jQuery('#shareinfo_' + shareinfo);
				shareobj.removeClass('hide');
				var passwordval = shareobj.find('span.sharepasswordval');
				var sharetimesval = shareobj.find('span.sharetimes');
				var passtext = (passwordval.data('password')) ? passwordval.data('passwordval1') : passwordval.data('passwordval2');
				var timetext = (sharetimesval.data('times')) ? sharetimesval.data('timesval1') : sharetimesval.data('timesval2');
				if (!shareobj.find('.sharetextinfo').text()) {
					shareobj.find('.sharetextinfo').text(passtext + ' ' + timetext);
				}

			} else {
				jQuery('.deatisinfo').each(function () {
					jQuery(this).addClass('hide');
				});
				jQuery('.share-option-icon').find('.btnedit').hide();
			}
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
		if (hash.indexOf('share') != -1) {
			jQuery('.share-option-icon').hide();
			jQuery('.deatisinfo').each(function () {
				jQuery(this).addClass('hide');
			});
		}
	}
	this.setToolButton(); //设置头部工具菜单；
	return false;
};
_filemanage.prototype.PageInfo = function () {
	return;
	/*var Sum=0;
	var Size=0;
	for(var i in this.data){
		Sum++;
		Size+=(this.data[i].size)*1;
	}
	var leftinfo=__lang.bottom_leftinfo.replace('{n}',Sum).replace('{size}',formatSize(Size)).replace('{total}',this.total);
	jQuery('#bottom_content_'+this.winid+'_'+this.id).html('<div style="line-height:35px;">'+leftinfo+'</div>');
	//jQuery('#'+this.winid+' .BOTTOM .info_right').html();
	jQuery('#bottom_content_'+this.winid+'_'+this.id).show();
	if(this.bottomShowTimer) window.clearTimeout(this.bottomShowTimer);
	var self=this;
	this.bottomShowTimer=window.setTimeout(function(){
		jQuery('#bottom_content_'+self.winid+'_'+self.id).hide();
	},1000);*/
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
	_filemanage.getData(url + '&exts=' + this.exts + '&tags=' + this.tags + '&disp=' + this.disp + '&fids=' + this.fids + '&gid=' + this.gid + '&before=' + this.before + '&after=' + this.after + '&asc=' + this.asc + '&iconview=' + this.view + '&keyword=' + encodeURI(keyword) + '&page=' + page + '&marker=' + (this.fid ? _explorer.sourcedata.folder[this.fid].nextMarker : '') + '&t=' + new Date().getTime(), function () {
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
	       if(_explorer.sourcedata.app[obj.oid] && _explorer.sourcedata.app[obj.oid]['available']<1){
	           Alert(__lang.regret_app+_explorer.sourcedata.app[obj.oid]['appname']+__lang.already_close,5,null,null,'info');
	           return ;
	       }
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
		} else {
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
		showDialog('文件没有可以打开的应用');
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

_filemanage.collect = function (rid) {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	var dpaths = [];
	var collects = 0;
	var collect = 1;
	var ico = null;
	var i = 0;
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
//	console.log(dpaths.length);
	if (dpaths.length) {
		$.post(_explorer.appUrl + '&op=ajax&operation=collect', {
			"paths": dpaths,
			'collect': collect
		}, function (json) {
			if (json.error) {
				showDialog(json.error, 'notice');
			} else {
				var msg = '';
				if (collect === 0) {					
					if (_filemanage.subfix === 'collect') {//收藏页面中
						for (var key in json.msg) {
							if (json.msg[key] === 'success') {
								msg += '<p class="text-success">' + _explorer.sourcedata.icos[key].name + __lang.cancle_collect_success + '</p>';
								_filemanage.removerid(key);
								
								total--;
							} else {
								msg += '<p class="text-danger">' + _explorer.sourcedata.icos[key].name + json.msg[key].error + '</p>';

							}
						}
						_filemanage.showTemplatenoFile(containid, total);

					} else {
						for (var i in json.msg) {
							if (json.msg[i] === 'success') {
								_explorer.sourcedata.icos[rid].collect = 0;
								msg += '<p class="text-success">' + _explorer.sourcedata.icos[i].name + __lang.cancle_collect_success + '</p>';
								jQuery('#' + containid + ' .Icoblock[rid=' + i + ']').find('.dzz-colllection-item').addClass('hide');
							} else {
								msg += '<p class="text-danger">' + _explorer.sourcedata.icos[i].name + json.msg[i].error + '</p>';
							}
						}
					}
				} else {
					
					for (var i in json.msg) {
						if (json.msg[i] === 'success') {							
							msg += '<p class="text-success">' + _explorer.sourcedata.icos[i].name + __lang.collect_success + '</p>';
							_explorer.sourcedata.icos[rid].collect = 1;
							jQuery('#' + containid + ' .Icoblock[rid=' + i + ']').find('.dzz-colllection-item').removeClass('hide');
						} else {
							msg += '<p class="text-danger">' + _explorer.sourcedata.icos[i].name + json.msg[i].error + '</p>';
						}
					}
				}
				showmessage(msg, 'success', 3000, 1, 'right-bottom');
				//console.log('收藏成功时处理');
			}
		}, 'json');
	}
	return;
};
_filemanage.property = function (rid, isfolder) {
	var path = '';
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
	showWindow('property', _explorer.appUrl + '&op=ajax&operation=property&paths=' + path);
};
_filemanage.share = function (rid, rids) {
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
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
	if (dpaths.length > 0) {
		showWindow('share', _explorer.appUrl + '&op=ajax&operation=share&paths=' + path, 'get', 0);
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
		showmessage('<p>' + __lang.error_file_not_download + '</p><ul>' + errors + '</ul>', 'danger', 5000, 1);
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

	if (type === 'newFolder') {
		showWindow('newFolder', _explorer.appUrl + '&op=ajax&operation=' + type + '&fid=' + fid);
	} else if (type === 'newLink') {
		showWindow('newLink', _explorer.appUrl + '&op=ajax&operation=' + type + '&fid=' + fid);
	} else {
		$.post(_explorer.appUrl + '&op=ajax&operation=newIco&type=' + type, {
			'fid': fid
		}, function (data) {
			if (data.msg === 'success') {
				_explorer.sourcedata.icos[data.rid] = data;
				_filemanage.cons['f-' + fid].CreateIcos(data);
                _filemanage.addIndex(data);
				_filemanage.rename(data.rid);
            } else {
				showDialog(data.error);
			}
		}, 'json');
	}
};
//增加索引
_filemanage.addIndex = function(data){
	console.log(data);
	if(data.filetype != 'folder' && data.filetype != 'link'){
        $.post(MOD_URL+'&op=ajax&operation=addIndex',{
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
        },'json')
	}
}
_filemanage.updateIndex = function(data){
    if(data.type != 'folder' && data.type != 'link'){
        $.post(MOD_URL+'&op=ajax&operation=updateIndex',data,function(json){
            if(json['success']){

            }else{
                alert(json.error);
            }
        },'json')
    }
}
_filemanage.rename = function (id) {
	var ico = _explorer.sourcedata.icos[id];
	if (!ico) {
		return;
	}
	var filemanage = _filemanage.cons[_filemanage.winid];

	var el = jQuery('#file_text_' + id);
	el.css('overflow', 'visible');
	el.closest('td').addClass('renaming');
	jQuery('#Icoblock_middleicon_' + id).find('.IcoText_div').css('overflow', 'visible');
	filemanage.oldtext = el.html();
	var html = '';
	if (filemanage.view > 3) {
		html = "<input type='text' class='form-control' name='text'  id='input_" + id + "' style=\"width:" + (el.closest('td').width() - 110) + "px;height:30px;padding:2px; \" value=\"" + filemanage.oldtext + "\">";
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
		//var obj = event.srcElement ? event.srcElement : event.target;
		e = e ? e : window.event;
		var obj = e.srcElement ? e.srcElement : e.target;
		if (jQuery(obj).closest('#file_text_' + id).length < 1) {
			jQuery(document).off('.file_text_' + id);
			var text = ele.val() || "";
            var emptymatch = /^\s*$/;
            if(emptymatch.test(text)){
                showDialog(__lang.name_is_must,'error','',function(){
                    el.html(filemanage.oldtext);
                    el.css('overflow', 'hidden');
                    el.closest('td').removeClass('renaming');
                    jQuery('#Icoblock_middleicon_' + id).find('.IcoText_div').css('overflow', 'hidden');
				});
                return false;
            }
			text = text.replace("\n", '');
			if (filemanage.oldtext !== text) {
				_filemanage.Rename(id, text);
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

_filemanage.Rename = function (rid, text) {
	var ico = _explorer.sourcedata.icos[rid];
	var filemanage = _filemanage.cons[_filemanage.winid];
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
			if (json.rid) {
				_explorer.sourcedata.icos[json.rid].name = json.name;
				filemanage.data[json.rid].name = json.name;
				filemanage.CreateIcos(_explorer.sourcedata.icos[json.rid], true);
				var updatedatas = {'arr[rid]':json.rid,'arr[name]':json.name,'arr[vid]':json.vid,'type':json.type};
                _filemanage.updateIndex(updatedatas);
			} else {
				jQuery('#file_text_' + rid).html(filemanage.oldtext);
				if (json.error) {
					showmessage(json.error, 'danger', 3000, 1);
				}
			}
		},
		error: function () {
			jQuery('#file_text_' + rid).html(filemanage.oldtext);
			if (json.error) {
				showmessage(json.error, 'danger', 3000, 1);
			}
			showmessage(__lang.js_network_error, 'danger', 3000, 1);
		}
	});
};
_filemanage.deleteIndex=function(rids){
	$.post(MOD_URL+'&op=ajax&operation=deleteIndex',{
       'rids':rids
    },function(json){
        if(json['success']){

        }else{
            alert(json.error);
        }
    },'json')
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
	var msg = '<span class="delect_text_red">' + __lang.finally_delete_file_confirm + '</span>';
	showDialog(msg, 'confirm', '<span class="delect-title-normal">确定要删除文件？<span>', function () {
		var progress = '<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>';
		showmessage('<p>' + __lang.deleting_not_please_close + '</p>' + progress, 'success', 0, 1, 'right-bottom');
		jQuery.post(url, data, function (json) {
			var rids = [];
			for (var i in json.msg) {
				if (json.msg[i] === 'success') {
					showmessage(_explorer.sourcedata.icos[i].name + __lang.delete_success, 'success', 1000, 1, 'right-bottom');
					//_filemanage.removerid(i);
					rids.push(i);
					total--;
					_filemanage.showTemplatenoFile(containid, total);

				} else {
					showmessage(json.msg[i], 'error', 3000, 1, 'right-bottom');
				}
			}
            _filemanage.deleteIndex(rids);
            _filemanage.removeridmore(rids);
		}, 'json');
	})


};
//清空回收站
_filemanage.deleteAll = function () {
		var filemanage = _filemanage.cons[_filemanage.winid];
		var containid = 'filemanage-' + _filemanage.winid;
		var total = filemanage.total;
		var url = _explorer.appUrl + '&op=dzzcp&do=emptyallrecycle&k=' + new Date().getTime();
		var msg = '<span class="delect_text_red">' + __lang.finally_delete_file_confirm + '</span>';
		showDialog(msg, 'confirm', '<i class="dzz dzz-info dzz-whole-sigh"></i><span class="delect-title-normal">您确定删除回收站所有文件吗？删除之后不可恢复<span>', function () {
			var progress = '<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>';
			showmessage('<p>' + __lang.deleting_not_please_close + '</p>' + progress, 'success', 0, 1, 'right-bottom');
			$.getJSON(url, function (data) {
				if(data.error){
                    showmessage(data.error, 'error', 3000, 1, 'right-bottom');
                    return false;
				}
				var rids = [];
				for (var i in data.msg) {
					if (data.msg[i] == 'success') {
						showmessage(data.name[i] + __lang.delete_success, 'success', 1000, 1, 'right-bottom');
						//_filemanage.removerid(i);
						rids.push(i);
						total--;

					} else {
						showmessage(data.msg[i], 'error', 3000, 1, 'right-bottom');
					}
				}
				_filemanage.showTemplatenoFile(containid, total);
                _filemanage.deleteIndex(rids);
                _filemanage.removeridmore(rids);


			});
		})
	}
	//还原所有文件
_filemanage.recoverAll = function () {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	var url = _explorer.appUrl + '&op=dzzcp&do=recoverAll&k=' + new Date().getTime();
	var msg = '<span class="delect_text_red">' + __lang.recover_file_confirm + '</span>';
	showDialog(msg, 'confirm', '<i class="dzz dzz-info dzz-whole-sigh"></i><span class="delect-title-normal">您确定恢复所有文件到原位置吗？<span>', function () {
		var progress = '<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>';
		showmessage('<p>' + __lang.recovering_not_please_close + '</p>' + progress, 'success', 0, 1, 'right-bottom');
		$.getJSON(url, function (data) {
            if(data.error){
                showmessage(data.error, 'error', 3000, 1, 'right-bottom');
                return false;
            }
			var rids = [];
			for (var i in data.msg) {
				if (data.msg[i] == 'success') {
					showmessage(data.name[i] + __lang.recover_success, 'success', 1000, 1, 'right-bottom');
					//_filemanage.removerid(i);
					rids.push(i);
					total--;
					_filemanage.showTemplatenoFile(containid, total);

				} else {
					showmessage(data.msg[i], 'error', 3000, 1, 'right-bottom');
				}
			}
            _filemanage.removeridmore(rids);
		});
	})
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

	var progress = '<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>';
	showmessage('<p>' + __lang.recovering_not_please_close + '</p>' + progress, 'success', 0, 1, 'right-bottom');
	jQuery.post(url, data, function (json) {
		var rids = [];
		for (var i in json.msg) {
			if (json.msg[i] === 'success') {
				showmessage(_explorer.sourcedata.icos[i].name + __lang.recover_success, 'success', 1000, 1, 'right-bottom');
				//_filemanage.removerid(i);
				rids.push(i);
				_filemanage.showTemplatenoFile(containid, total);

			} else {
				showmessage(json.msg[i], 'error', 3000, 1, 'right-bottom');
			}
		}
        _filemanage.removeridmore(rids);

	}, 'json');
};

_filemanage.showTemplatenoFile = function (containid, total) {
		if (total < 1 && jQuery('#' + containid).find('.emptyPage').length == 0) {
			jQuery(jQuery('#template_nofile_notice').html()).appendTo(jQuery('#' + containid));
		} else {
			jQuery('#' + containid).find('.emptyPage').remove();
		}
	}
	//取消分享
_filemanage.cancleshares = function (rid, noconfirm) {
	var filemanage = _filemanage.cons[_filemanage.winid];
	var containid = 'filemanage-' + _filemanage.winid;
	var total = filemanage.total;
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var msg = '<span class="delect_text_red">' + __lang.delete_share_sure + '</span>';
	var title = '<span class="delect-title-normal">' + '确定要删除分享？' + '</span>';
	if (_filemanage.selectall.icos.length > 1 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
		var questryshareid = _filemanage.selectall.icos.join(',');
	} else {
		var questryshareid = rid;
	}
	showDialog(msg, 'confirm', title, function () {
		jQuery.getJSON(MOD_URL + '&op=share&do=delshare&shareid=' + questryshareid, function (data) {
			var error_name = '';
			if (data['msg']) {
				var datareturn = data['msg'];
				for (var o in datareturn) {
					if (datareturn[o]['success']) {
						var shareid = parseInt(o);
						jQuery('#shareinfo_' + shareid).remove();
						_filemanage.removerid(o);
						total--;

					} else {
						error_name += $('#' + shareid + '_title').text() + ',';
					}
				}
				_filemanage.showTemplatenoFile(containid, total);
				if (error_name != '') {
					error_name = error_name.substr(0, error_name.length - 1);
					showDetail(error_name + '{lang fail_cancel_share}');
				}
			}
		});
	})
	return false;
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
		//var finallydelete = (_explorer.deletefinally == 1) ? true:false;
        var finallydelete = false;
		if (_filemanage.selectall.icos.length > 0 && jQuery.inArray(rid, _filemanage.selectall.icos) > -1) {
			if (_explorer.sourcedata.icos[_filemanage.selectall.icos[0]].isdelete > 0 || (_explorer.sourcedata.icos[_filemanage.selectall.icos[0]].bz && _explorer.sourcedata.icos[_filemanage.selectall.icos[0]].bz)) {
				Confirm((finallydelete) ?__lang.js_finallydelete_selectall:__lang.js_delete_selectall, function () {
					_filemanage.delIco(rid, 1);
				});
			} else {
				Confirm((finallydelete) ? __lang.js_finallydelete_selectall_recycle : __lang.js_delete_selectall_recycle, function () {
					_filemanage.delIco(rid, 1);
				});
			}
			return;
		} else if (_explorer.sourcedata.icos[rid].type === 'folder' && _explorer.sourcedata.folder[_explorer.sourcedata.icos[rid].oid] && _explorer.sourcedata.folder[_explorer.sourcedata.icos[rid].oid].iconum) {
			if (_explorer.sourcedata.icos[rid].isdelete > 0 || (_explorer.sourcedata.icos[rid].bz && _explorer.sourcedata.icos[rid].bz)) {
				Confirm((finallydelete) ? __lang.js_finallydelete_folder.replace('{name}', _explorer.sourcedata.icos[rid].name):__lang.js_delete_folder.replace('{name}', _explorer.sourcedata.icos[rid].name), function () {
					_filemanage.delIco(rid, 1);
				});
			} else {
				Confirm((finallydelete) ? __lang.js_finallydelete_folder_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name):__lang.js_delete_folder_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name), function () {
					_filemanage.delIco(rid, 1);
				});
			}
			return;
		} else {
			if (_explorer.sourcedata.icos[rid].isdelete > 0 || (_explorer.sourcedata.icos[rid].bz && _explorer.sourcedata.icos[rid].bz)) {
				Confirm((finallydelete) ? __lang.js_finallydelete_confirm.replace('{name}', _explorer.sourcedata.icos[rid].name) : __lang.js_delete_confirm.replace('{name}', _explorer.sourcedata.icos[rid].name), function () {
					_filemanage.delIco(rid, 1);
				});
			} else {
				Confirm((finallydelete) ? __lang.js_finallydelete_confirm_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name): __lang.js_delete_confirm_recycle.replace('{name}', _explorer.sourcedata.icos[rid].name), function () {
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
	var progress = '<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>';
	showmessage('<p>' + __lang.deleting_not_please_close + '</p>' + progress, 'success', 0, 1, 'right-bottom');
	jQuery.post(url, data, function (json) {
		var rids = [];
		for (var i in json.msg) {
			if (json.msg[i] === 'success') {
				showmessage(_explorer.sourcedata.icos[i].name + __lang.delete_success, 'success', 1000, 1, 'right-bottom');
				//_filemanage.removerid(i);
				rids.push(i);
				total--;
				_filemanage.showTemplatenoFile(containid, total);

			} else {
				showmessage(json.msg[i], 'error', 3000, 1, 'right-bottom');
			}
		}
        _filemanage.removeridmore(rids);

	}, 'json');
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
	//var self=this;
	var data = _explorer.sourcedata.icos[rid];
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
_filemanage.copy = function (rid) {
	if (!rid) {
		rid = _filemanage.selectall.icos[0];
	}
	var icosdata = _explorer.sourcedata.icos[rid];
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
	//复制类型值为1，剪切类型值为2
	data.copytype = 1;
	var url = _explorer.appUrl + '&op=dzzcp&do=copyfile&t=' + new Date().getTime();
	//var progress='<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>';
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
			showmessage(filenames + __lang.copy_success, 'success', 1000, 1, 'right-bottom');
		} else {
			showmessage(json.msg, 'error', 3000, 1, 'right-bottom');
		}


	}, 'json');
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
	//复制类型值为1，剪切类型值为2
	data.copytype = 2;
	var url = _explorer.appUrl + '&op=dzzcp&do=copyfile';
	// var progress='<div class="progress progress-striped active" style="margin:0"><div class="progress-bar" style="width:100%;"></div></div>'
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
			showmessage(filenames + __lang.cut_success, 'success', 1000, 1, 'right-bottom');
		} else {
			showmessage(json.msg, 'error', 3000, 1, 'right-bottom');
		}

	}, 'json');
};
//粘贴
_filemanage.paste = function (fid) {
	var folder = _explorer.sourcedata.folder[fid];
	if (!folder) {
		return false;
	}
	var data = {
		'tpath': folder.fid,
		'tbz': folder.bz
	};
	var url = _explorer.appUrl + '&op=dzzcp&do=paste';
	var i = 0;
	var node = null;
	jQuery.post(url, data, function (json) {
		if(json.error){
			showmessage(json.error,'danger',3000,1);
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
						if (json.icoarr[i].pfid === filemanage.fid) {
							_explorer.sourcedata.icos[json.icoarr[i].rid] = json.icoarr[i];
							filemanage.CreateIcos(json.icoarr[i]);
							if(addIndex){
								_filemanage.addIndex(json.icoarr[i]);
							}
						}
					}
				}
			} else {
				showmessage('粘贴成功', 'success', 3000, 1);
			}
		}
		
	}, 'json');

};
