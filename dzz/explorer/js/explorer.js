"use strict";
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
var _explorer = {};
_explorer = function (json) {
	_explorer.space = json.space; //用户信息
    _explorer.myuid = json.myuid;
	_explorer.formhash = json.formhash; //FORMHASH
	_explorer.extopen = json.extopen || {}; //打开方式信息
	_explorer.sourcedata = json.sourcedata || []; //所有文件信息
	_explorer.infoPanelOpened = json.infoPanelOpened || 0;
	_explorer.infoRequest = 0;
	_explorer.deletefinally = json.deletefinally || 0;
	_explorer.cut = json.cut || {
		iscut: 0,
		icos: []
	};
};

_explorer.infoPanel_hide = 0; //标识右侧面板不能开启
_explorer.appUrl = MOD_URL;
_explorer.hash = '';
_explorer.bz = '';
_explorer.loadhtml = '<div class="nothing_message"><div class="nothing_allimg"><i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop" style="font-size:30px;"></i></div>';
_explorer.getConfig = function (url, callback) {
	$.getJSON(url + '&t=' + new Date().getTime(), function (json) {
		new _explorer(json);
		_explorer.hashHandler();
		_explorer.initEvents();
		_explorer.infoPanel();
		if (typeof callback === "function") {
			callback(json);
		}
	});
};
_explorer.initEvents = function () { //初始化页面事件
	//hashchange事件
	$(window).on('hashchange', function () {
		_explorer.hashHandler();
	});
	//左侧列表页事件
	$(document).off('click.document-data').on('click.document-data', '.document-data', function () {
		//var el=$(this);
		location.hash = jQuery(this).data('hash');
	});

	//右侧加载完成事件
	$(document).off('ajaxLoad.middleContent').on('ajaxLoad.middleContent', function () {
		_explorer.Scroll($('.scroll-y'));
		var hash = location.hash.replace(/^#/i, '');
		var op = hash.replace(/&(.+?)$/ig, ''); //(hash,'op');

		_explorer.topNav_init();
		_explorer.address_resize();
		_explorer.setHeight($('.height-100'));
		if ($('.scroll-100').length) {
			_explorer.scroll_100 = new PerfectScrollbar('.scroll-100');
		}
	});

};
_explorer.infoPanel = function () {
	_explorer.toggleRight();
	$(document).off('click.togglle').on('click.togglle', '.toggRight', function () {
		_explorer.infoPanelOpened = _explorer.infoPanelOpened ? 0 : 1;
		_explorer.toggleRight();
		if (_explorer.infoPanelOpened) {
			_filemanage && _filemanage.setInfoPanel();
		}
		$.post(_filemanage.saveurl + '&do=infopanelopen', {
			'infopanelopen': _explorer.infoPanelOpened
		});
		/*window.setTimeout(function () {
			_filemanage && _filemanage.SetMoreButton();
		}, 500);*/
	});
};
_explorer.toggleRight = function () {
	if (!_explorer.infoPanelOpened || _explorer.infoPanel_hide) {
		$('.middleconMenu').removeClass('m-right-300');
		$('.rightMenu').removeClass('is-block');
		$('.toggRight').parent('li').removeClass('background-toggle').find('.mdi').attr("data-original-title", "开启右侧信息");
	} else {
		$('.middleconMenu').addClass('m-right-300');
		$('.rightMenu').addClass('is-block');
		$('.toggRight').parent('li').addClass('background-toggle').find('.mdi').attr("data-original-title", "关闭右侧信息");

	}
};

_explorer.address_resize = function (dir) {
	var container = jQuery('.address-container');
	var address = jQuery('.address');
	var cwidth = container.width();
	var speed = cwidth;
	var awidth = 0;
	address.find('li').each(function () {
		awidth += jQuery(this).outerWidth(true);
	});
	
	var left = isNaN(parseInt(address.css('left'))) ? (cwidth - awidth) : parseInt(address.css('left'));
	if (dir === 'left') {
		left += speed;
		if (left >= 0) {
			left = 0;
			container.removeClass('arrow-left');
		}
		if (left > (cwidth - awidth)) {
			container.addClass('arrow-right');
		} else {
			container.removeClass('arrow-right');
		}
		if (left < 0) {
			container.addClass('arrow-left');
		} else {
			container.removeClass('arrow-left');
		}

		address.animate({
			'left': left,
			'right': 'auto'
		}, 500);

	} else if (dir === 'right') {
		left -= speed;

		if (left <= (cwidth - awidth)) {
			left = (cwidth - awidth);
			container.removeClass('arrow-right');
		}
		if (left > (cwidth - awidth)) {
			container.addClass('arrow-right');
		} else {
			container.removeClass('arrow-right');
		}
		if (left < 0) {
			container.addClass('arrow-left');
		} else {
			container.removeClass('arrow-left');
		}
		address.animate({
			'left': left,
			'right': 'auto'
		}, 500);
	} else {

		if (awidth > cwidth + 21) {
			container.removeClass('arrow-right').addClass('arrow-left');
		} else {
			container.removeClass('arrow-right').removeClass('arrow-left');
		}
		address.css({
			'left': 'auto',
			'right': 0
		});
	}
};
_explorer.topNav_init = function () {

	/*页面地址栏相关事件*/
	$('.address-left-arrow').off('click.address-left-arrow').on('click.address-left-arrow', function () {
		_explorer.address_resize('left');
		return false;
	});
	$(document).off('click.address-right-arrow').on('click.address-right-arrow', '.address-right-arrow', function () {
		_explorer.address_resize('right');
		return false;
	});

	$(document).off('click.address-container').on('click.address-container', '.address-container', function () {
		$(this).removeClass('borderHover');
		$('.bordeInput').show().select();
		$(this).find('.address').hide();
		$(document).on('mousedown.bordeInput', function (e) {
			e = e ? e : window.event;
			var obj = e.srcElement ? e.srcElement : e.target;
			if (/input|textarea/i.test(obj.tagName)) {
				return true;
			}
			$('.bordeInput').trigger('blur');
			$(document).off('mousedown.bordeInput');
		});
	});
	$(document).off('blur.bordeInput').on('blur.bordeInput', '.bordeInput', function () {
		$('.bordeInput').hide();
		$('.address').show();

	});
	//点击路径切栏切换位置
	$(document).off('click.routes').on('click.routes', '.address-container  .routes', function () {
		var path = '';
		var text = $(this).text();
		var textprefix = /[:：]/;
		var prefix = '';
		var textarr = [];
		if (textprefix.test(text)) {
			textarr = text.split(/[:：]/);
			prefix = textarr[0];
			text = textarr[1];
		}
		var hash = location.hash;
		if (hash.indexOf('cloud') != -1) {
			var bz = _explorer.getUrlParam(hash, 'bz');
			var li = $(this).closest('li');
			var siblings = li.prevAll();
			if (siblings.length === 0) {
				hash = '#cloud&bz='+bz;
			} else {
				siblings.not(':last').find('a').each(function () {
					path += $(this).text() + '/';
				});
				path += text;
				if (path.charAt(path.length - 1) !== '/') {
					path = path + '/';
				}
				path = path.substring(0, path.length - 1);
				hash = '#cloud&bz='+bz+'&path=' +bz+path;
			}
			location.hash = hash;
			return false;
		}

		$(this).closest('li').prevAll().find('a').each(function () {
			path += $(this).text() + '/';
		});
		path += text;
		//path = path.replace(/>/g,'/');
		if (path.charAt(path.length - 1) !== '/') {
			path = path + '/';
		}
		_explorer.routerule(path, prefix);
		return false;
	});
	//输入地址栏实现切换
	$(document).off('keyup.referer_path').on('keyup.referer_path', '.address-container .referer_path', function (e) {
		if (e.keyCode === 13) {
			var path = $(this).val();
			path = path.replace(/\\/g, '/', path);
			var hash = false;
			switch (path) {
				case '我的网盘':
					_explorer.routerule(path);
					break;
				case '动态':
					hash = 'dynamic';
					break;
				case '回收站':
					hash = 'recycle';
					break;
				case '分享':
					hash = 'share';
					break;
				case '收藏':
					hash = 'collection';
					break;
				case '最近使用':
					hash = 'recent';
					break;
				case '文件夹权限':
					hash = 'perm';
					break;
				case '功能管理':
					hash = 'app';
					break;
				case '图片':
				case '文档':
					path = '类型:' + path;
					break;
			}
			var textprefix = /[:：]/;
			var patharr = [];
			var prefix = '';
			if (textprefix.test(path)) {
				patharr = path.split(/[:：]/);
				prefix = patharr[0];
				path = patharr[1];
				if (path.charAt(path.length - 1) !== '/') {
					path = path + '/';
				}
				_explorer.routerule(path, prefix);
				return false;
			}

			if (hash) {
				location.hash = hash;
				return false;
			}

			if (path.charAt(path.length - 1) !== '/') {
				path = path + '/';
			}
			_explorer.routerule(path);
			return false;
		}

	});
};
_explorer.routerule = function (path, prefix) {
	if (location.hash.indexOf('cloud') != -1) {
		showmessage('网络挂载不支持手动输入地址进行切换','error',3000,1);
		return false;
	}
	var queryobj = {
		'name': path
	};
	//获取前缀
	if (prefix) {
		switch (prefix) {
			case '群组':
				queryobj.prefix = 'g';
				break;
			case '机构':
				queryobj.prefix = 'o';
				break;
			case '类型':
				queryobj.prefix = 'c';
				break;
		}
	}

	$.post(_explorer.appUrl + '&op=ajax&do=getfid', queryobj, function (data) {
		if (data.success) {
			var hash = '';
			if (!isNaN(parseInt(data.success['gid']))) {
				hash = 'group&do=file&gid=' + data.success['gid'] + (data.success['fid'] ? '&fid=' + data.success['fid'] : '');
			} else if (!isNaN(parseInt(data.success['cid']))) {
				hash = 'catsearch&do=searchfile&id=' + data.success['cid'];
			} else {
				hash = 'home&do=file&fid=' + data.success['fid'];
			}
			location.hash = hash;
		} else if (data.error) {
			showmessage('没有找到该路径', 'info', 3000, 1);
		} else {
			showmessage(__lang.do_failed, 'error', 3000, 1);
		}
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		showmessage(__lang.do_failed, 'error', 3000, 1);
	});
	return false;
};
_explorer.hashHandler = function () { //处理页面hash变化
	var hash = location.hash;
	hash = hash.replace(/^#/i, '');
	_explorer.jstree_select(hash);
	if (!hash) {
		hash = 'recent';
	}
	if (hash === _explorer.hash) {
		return false;
	}
	if (hash !== _explorer.hash) {
		_explorer.getRightContent(hash, $('#middleconMenu'));
		_explorer.hash = hash;
	} else {
		_explorer.hash = hash;
	}
	_explorer.topMenu(hash);
	return false;
};

_explorer.getRightContent = function (hash, container) { //处理右侧页面加载
	var searchpreg = /#searchFile/;
	if (!searchpreg.test(hash)) {
		try {
			resetting_condition();
		} catch (e) {}
	}
	$('#middleconMenu').html(_explorer.loadhtml);
	if(window.innerWidth < 1024) {
		_explorer.infoPanelOpened = 0;
	}
	$('.document-data').removeClass('active');
	$('[data-hash="' + hash + '"]').addClass('active');
	var url = _explorer.appUrl + '&op=' + hash;
	jQuery('#middleconMenu').load(url, function (response, status, xhr) {
		if (status === "error") {
			console.error("加载失败：", xhr.status, xhr.statusText);
			$('#middleconMenu').html('<div class="emptyPage" id="noticeinfo"><img src="static/image/common/no_list.png"><div class="emptyPage-text text-danger">加载内容失败，请刷新重试。</div></div>');
		} else {
			$(document).trigger('ajaxLoad.middleContent', [hash]);
		}
	});
};
_explorer.topMenu = function (hash, fid) {
	var shownewbuild = false;
	if (hash) {
		//根据hash值判断是否显示在头部
		if (hash == 'groupmanage' || hash == 'app' || hash == 'dynamic' || hash == 'mygroup' || hash.indexOf('share') == 0) {
			jQuery('.navtopheader').css('display', 'none');
			jQuery('.rightswitch').hide();
			if(hash.indexOf('recycle') == 0 || hash.indexOf('cloud') == 0){
				jQuery('.listchange').show();
			}else{
				jQuery('.listchange').hide();
			}
			_explorer.infoPanel_hide = 1; //标识右侧面板有没有
		} else {
			jQuery('.listchange').show();
			jQuery('.rightswitch').show();
			if (hash == 'cloud') {
				jQuery('.listchange').hide();
				jQuery('.rightswitch').hide();
				_explorer.infoPanel_hide = 1;
			} else {
				_explorer.infoPanel_hide = 0;
			}
		}
		_explorer.toggleRight();
		if (hash.indexOf('home') == 0 || (hash.indexOf('group') == 0 && hash.indexOf('groupmanage') == -1)) { //判断hash隐藏或显示新建上传
			jQuery('.new-buildMenu').show();
            shownewbuild = true;
		} else {
			jQuery('.new-buildMenu').hide();
            shownewbuild = false;
		}
	}
	if (fid && shownewbuild) {
	
		//判断新建和上传图标显示
		var folderperm = false;
		var uploadperm = false;
		if (!_explorer.Permission_Container('upload', fid)) { //文件夹权限(判断是否有文件夹权限如果没有隐藏文件夹相关新建上传)
			jQuery('.new-buildMenu').find('li').not('.folderPermMust').hide();
		} else {
			jQuery('.new-buildMenu').find('li').not('.folderPermMust').show();
			folderperm = true;
		}
		if (!_explorer.Permission_Container('folder', fid)) { //其它类型新建权限，若果无权限，隐藏文件相关权限
			jQuery('.new-buildMenu').find('li.folderPermMust').hide();
		} else {
			jQuery('.new-buildMenu').find('li.folderPermMust').show();
			uploadperm = true;
		}
		if (folderperm || uploadperm) { //如果没有文件夹权限和文件权限，隐藏新建上传菜单
			jQuery('.new-buildMenu').show();
		}else{
			jQuery('.new-buildMenu').hide();
		}
	} else {
		jQuery('.new-buildMenu').hide();
	}
};

//通过hash值来设置左侧树的选择指示
_explorer.jstree_select = function (hash) {
	if (!hash) {
		hash = location.hash.replace('#', '');
	}
	var op = hash.replace(/&(.+?)$/ig, ''); //(hash,'op');
	var fid = _explorer.getUrlParam(hash, 'fid');
	if (op === 'group') {
		var gid = _explorer.getUrlParam(hash, 'gid');
		_explorer.open_node_by_id(fid, gid);
	} else if (op === 'home') {
		_explorer.open_node_by_id(fid);
	} else if (op === 'cloud') {
		var inst = $('#position').jstree(true);
		if (!inst || typeof inst.select_node !== 'function') {
			console.warn('jstree instance is not available');
			return;
		}
		var bz = _explorer.getUrlParam(hash, 'bz');
		if (bz) {
			bz = bz.replace(/:/g, '_');
			var node = inst.get_node('#bz_' + bz);
			if (node) {
				inst.select_node('#bz_' + bz);
			} else {
				node = inst.get_node('#mycloud');
				inst.open_node(node, function (node) {
					inst.select_node('#bz_' + bz);
				});
			}
		} else {
			inst.select_node('#mycloud');
		}
	} else if (op === 'mygroup') {
		$('#position').jstree(true).select_node('#group');
	} else {
		if ($('#position').length > 0) {
			$('#position').jstree(true).deselect_all();
		}
	}
};
_explorer.open_node_by_id = function (fid, gid,bz) {
	var inst = $('#position').jstree(true);
	var node = null;
	if (fid) {
		node = inst.get_node('#f_' + fid) || inst.get_node('#u_' + fid);
	} else if (gid) {
		node = inst.get_node('#g_' + gid) || inst.get_node('#gid_' + gid);
	} else if (bz) {
		node = inst.get_node('#bz_' + bz);
	} else {
		inst.deselect_all();
		return;
	}
	if (node) {
		inst.deselect_all();
		var selects = inst.get_selected();
		for (var i = 0; i < selects.length; i++) {
			if (selects[i] === node.id) {
				continue;
			}
			inst.deselect_node('#' + selects[i]);
		}
		inst.select_node(node);
	} else {
		$.post(_explorer.appUrl + '&op=grouptree&do=getParentsArr', {
			'fid': fid,
			'gid': gid,
			'bz': bz
		}, function (data) {
			var node = inst.get_node('#' + data[0]);
			_explorer.open_node_bg(inst, node, data);
		}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage(__lang.do_failed, 'error', 3000, 1);
        });
	}
};
_explorer.open_node_bg = function (inst, node, arr) {
	inst.open_node(node, function (node) {
		var i = jQuery.inArray(node.id, arr);
		if (i < arr.length && i > -1 && document.getElementById(arr[i + 1])) {
			_explorer.open_node_bg(inst, document.getElementById(arr[i + 1]), arr);
		} else {
			var gid = arr[i + 1];
			if(node.id == 'group' && gid && !inst.get_node(gid)){
				var parentnode = inst.get_node('#group');
				$.post('index.php?mod=explorer&op=grouptree&do=create_group', {'id': gid}, function (data) {
					if (data) {
						if(data['group']){
							var newnode = {
								'text': data['group']['text'],
								'icon': data['group']['icon'],
								'id': data['group']['id'],
								'type':'folder',
								'li_attr':data['group']['li_attr'],
								'children':data['group']['children']
							};
							inst.create_node(parentnode, newnode, 'last', function () {
								inst.open_node(parentnode);
								inst.set_type(newnode,'group');
								inst.deselect_all();
								inst.select_node(newnode);
							});
						}

					}
				},'json').fail(function (jqXHR, textStatus, errorThrown) {
					showmessage(__lang.do_failed, 'error', 3000, 1);
				});
				return false;
			} else{
				inst.deselect_all();
				inst.select_node(node);
			}
		}
	});
};

_explorer.getUrlParam = function (url, name) {
	if (!name) {
		return url;
	}
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
	if (!url) {
		return '';
	}
	var r = url.match(reg);

	if (r !== null) {
		return decodeURIComponent(r[2]);
	}
};
//设定层高度
_explorer.setHeight = function (els) {
	var clientHeight = document.documentElement.clientHeight;
	els.each(function () {
		var el = $(this);
		var pos = el.offset();
		var height = clientHeight - pos.top;
		el.css('height', height);
	});
};
//带有.scroll-y的层设置滚动条
_explorer.Scroll = function (els) {
	var clientHeight = document.documentElement.clientHeight;
	if (!els) {
		els = $('.scroll-y');
	}
	els.each(function () {
		var el = $(this);
		var pos = el.offset();
		var height = clientHeight - pos.top;
		if (el.data('subtractor')) {
			height = height - el.data('subtractor');
		}
		el.css({
			'overflow': 'auto',
			'height': height,
			'position': 'relative'
		});
		new PerfectScrollbar(this);
	});
};



_explorer.image_resize = function (img, width, height) {
	width = !width ? jQuery(img).parent().width() : width;
	height = !height ? jQuery(img).parents('.icoimgContainer').parent().height() : height;
	imgReady(img.src, function () {
		var w = this.width;
		var h = this.height;
		var realw = 0,
			realh = 0;
		if (w > 0 && h > 0) {
			if ((w / h) > 1) {
				realw = (w > width) ? parseInt(width) : w;
				realh = (w > width) ? parseInt(height) : (realw * h / w);
			} else {
				realh = (h > height) ? parseInt(height) : h;
				realw = (h > height) ? parseInt(width) : (realh * w / h);
			}
			if (realw < 32 && realh < 32) {
				jQuery(img).addClass('image_tosmall').css({
					padding: ((height - realh) / 2 - 1) + 'px ' + ((width - realw) / 2 - 1) + 'px'
				});
			}
		}
		jQuery(img).show();
	});
};
_explorer.icoimgError = function (img, width, height) {
	width = !width ? jQuery(img).parent().width() : width;
	height = !height ? jQuery(img).parent().height() : height;
	if (jQuery(img).attr('error')) {
		imgReady(jQuery(img).attr('error'), function () {
				var w = this.width;
				var h = this.height;
				var realw = 0,
					realh = 0;
				if (w > 0 && h > 0) {
					if ((w / h) > 1) {
						realw = (w > width) ? parseInt(width) : w;
						realh = realw * h / w;
					} else {
						realh = (h > height) ? parseInt(height) : h;
						realw = realh * w / h;
					}
					if (realw < 32 && realh < 32) {
						jQuery(img).addClass('image_tosmall').css({
							padding: ((height - realh) / 2 - 1) + 'px ' + ((width - realw) / 2 - 1) + 'px'
						});
					}
					try {
						img.style.width = realw + 'px';
						img.style.height = realh + 'px';

					} catch (e) {

					}
					img.src = jQuery(img).attr('error');
					jQuery(img).show();
				}
			},
			function () {},
			function () {
				img.onerror = null;
				img.src = 'dzz/images/default/icodefault.png';
				jQuery(img).show();
			}
		);
	}
};
jQuery(window).resize(function () {
	_explorer.Scroll();
});


function checkAll(type, form, value, checkall, changestyle) {
	checkall = checkall ? checkall : 'chkall';
	for (var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if (type === 'option' && e.type === 'radio' && e.value === value && e.disabled !== true) {
			e.checked = true;
		} else if (type === 'value' && e.type === 'checkbox' && e.getAttribute('chkvalue') === value) {
			e.checked = form.elements[checkall].checked;

		} else if (type === 'prefix' && e.name && e.name !== checkall && (!value || (value && e.name.match(value)))) {
			e.checked = form.elements[checkall].checked;
			if (changestyle) {
				if (e.parentNode && e.parentNode.tagName.toLowerCase() === 'li') {
					e.parentNode.className = e.checked ? 'checked' : '';
				}
				if (e.parentNode.parentNode && e.parentNode.parentNode.tagName.toLowerCase() === 'div') {
					e.parentNode.parentNode.className = e.checked ? 'item checked' : 'item';
				}
			}
		}
	}
}
function dfire(e) {
	jQuery(document).trigger(e);
}
//增加统计数
function addstatis(rid) {
	if (_explorer.hash.indexOf('cloud') != -1) return;
	var remsg = false;
	$.ajax({
		type: 'post',
		url: MOD_URL + '&op=filestatis&do=addopenrecord',
		data: {
			'rid': rid
		},
		async: false,
		dataType: 'json',
		success: function (data) {
			if (data.msg === 'success') {
				remsg = true;
			}
		},
		error: function(xhr, status, error) {
			showmessage('Request failed:' + status +' ' +error, 'error', 3000, 1);
		}
	});
	return remsg;
}
function riddesc(rid,desc) {
	if (!rid) return;
	var data = {
		'desc':desc,
		'rid':rid
	};
	jQuery.post(_explorer.appUrl + '&op=dzzcp&do=riddesc', data, function (json) {
		if(json.success){
			_filemanage.prototype._selectInfo();
		} else if(json.error){
			showmessage(json.error, 'danger', 3000, 1);
		} else {
			showmessage(__lang.do_failed, 'danger', 3000, 1);
		}
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		showmessage(__lang.do_failed, 'error', 3000, 1);
	});
}

function infoversion(obj) {
	var rid = $(obj).data('rid');
	var vid = $(obj).data('vid');
	var querystr = '';
	if (rid) {
		querystr += '&rid=' + rid;
	}
	if (vid) {
		querystr += '&vid=' + vid;
	}
	showWindow('property', MOD_URL + '&op=ajax&do=infoversion' + querystr, 'get', 0);
	return false;
};
function deletethisversion(obj) {
	var rid = $(obj).data('rid');
	var vid = $(obj).data('vid');
	if (!vid) {
		return false;
	}
	layer.confirm('删除后将无法找回，确认要进行该操作吗?', {skin:'lyear-skin-danger',title:'删除该版本'}, function(index){
		layer.msg(__lang.deleting_not_please_close, {offset:'10px',time:0});
		$.post(MOD_URL+'&op=ajax&do=deletethisversion', {'rid':rid,'vid': vid}, function (json) {
			if(json.msg=='success'){
				layer.msg(__lang.delete_success, {offset:'10px'});
				$('.version_' + vid).remove();
			}else{
				layer.alert(json.error, {skin:'lyear-skin-danger'});
			}
		},'json').fail(function (jqXHR, textStatus, errorThrown) {
			layer.msg(__lang.do_failed, {offset:'10px'});
		});
	});
	return false;
};

function editVersionName(obj) {
	var rid = $(obj).data('rid');
	var vid = $(obj).data('vid');
	var querystr = '';
	if (rid) {
		querystr += '&rid=' + rid;
	}
	if (vid) {
		querystr += '&vid=' + vid;
	}
	showWindow('property', MOD_URL + '&op=ajax&do=editFileVersionInfo' + querystr, 'get', 0);
	return false;
};

function viewversion(obj) {
	var path = $(obj).data('dpath');
	var viewhref = 'share.php?a=view&s=' + path;
	var vid = $(obj).data('vid');
	if (vid) {
		viewhref += '&vid=' + vid;
	}
	window.open(viewhref);
}

function primaryVersion(obj) {
	var el = $(obj);
	var vid = el.data('vid');
	var primaryVersion = el.data('primary');
	//如果当前版本已经是主版本或者只有当前版本
	if (typeof vid == 'undefined' || !vid || primaryVersion == 'yes') {
		showmessage('当前版本已经是主版本了','info',3000,1);
		return false;
	}
	$.post(MOD_URL+'&op=dzzcp&do=setpramiryversion', {'vid': vid}, function (data) {
		if (data['success']) {
			var datas = data['data'];
			$('.version_menu').each(function () {
				$(this).find('.badge-outline-primary').remove().end().find('.dropdown-menu-version .primary_versions').data('primary', 'no');
			})
			$('.version_' + vid).find('div.versioninfos').append('<span class="badge badge-outline-primary">'+__lang.principal_edition+'</span>').end().find('.dropdown-menu-version .pramiry_version').data('primary', 'yes');
			;
			_explorer.sourcedata.icos[datas.rid] = datas;
			datas.vid = 0;
			_filemanage.addIndex(datas);
			_filemanage.cons['f-' + datas.pfid].CreateIcos(datas, true);
			_filemanage.prototype._selectInfo();
		} else {
			layer.alert(data['msg'], {skin:'lyear-skin-danger'});
		}
	}, 'json').fail(function (jqXHR, textStatus, errorThrown) {
		showmessage(__lang.do_failed, 'error', 3000, 1);
	});
};

function commentdelete(id) {
    var commentid = id;
    if(typeof commentid == 'undefined' || commentid < 0){
        layer.alert('{lang delete_error}', {skin:'lyear-skin-danger'});
    }
    layer.confirm(__lang.delete_filenorecover_confirm, {title:__lang.delete_comment_confirm,skin:'lyear-skin-danger'}, function(index){
		layer.msg(__lang.recovering_not_please_close, {offset:'10px',time:0});
		$.post(MOD_URL+'&op=dynamic&do=deletecomment',{'id':commentid},function(data){
        if(data['success']){
			$('.comment_' + id).remove();
            layer.msg('删除成功', {offset:'10px'});
        }else if(data['error']){
            layer.alert(data['error'], {skin:'lyear-skin-danger'});
        }
    },'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage('{lang do_failed}', 'error', 3000, 1);
        });
	});
}
function comment_file(form) {
    var form = $(form);
    var commentArea = form.closest('.comment-area');
    var textarea = commentArea.find('.leave_message');
    
    if (textarea.val().length < 1) {
        textarea.focus();
        return false;
    }
    
    $.post(form.attr('action'), form.serialize(), function (data) {
        if (data['success']) {
			if ($('.comment-tab').hasClass('active')) {
				jQuery('.comment-tab').click();
			}
			if ($('.property-comment-tab').hasClass('active')) {
				var rid = $('.property-comment-tab').data('rid');
				var fid = $('.property-comment-tab').data('fid');
				ajaxget(MOD_URL+'&op=ajax&do=comment&property=1&rid='+rid+'&fid='+fid, 'fwin_content_property');
			}
            showmessage('评论成功','success',3000,1);
        } else if(data['error']) {
            showmessage(data['error'],'error',3000,1);
        } else {
            showmessage('评论失败','error',3000,1);
        }
        textarea.val('').trigger('input');
    }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
        showmessage(__lang.do_failed, 'error', 3000, 1);
    });
    
    return false;
}

function rightinfo(obj) {
	if (!_explorer.infoPanelOpened || _explorer.infoPanel_hide) {
		return;
	}
	var el = $(obj);
	var rid = el.data('rid');
	var fid = el.data('fid');
	var item = el.data('item');
	jQuery('.tab-content').scrollTop(0);
	jQuery('.right-tab-'+item).html('<div class="emptyPage"><i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop" style="font-size:30px;"></i></div>');
	switch(item){
		case 'perm':
			ajaxget(MOD_URL+'&op=ajax&do=perm&rid='+rid+'&fid='+fid,'perm-tab','perm-tab')
		break;
		case 'comment':
		    ajaxget(MOD_URL+'&op=ajax&do=comment&rid='+rid+'&fid='+fid,'comment-tab','comment-tab')
		break;
		case 'dynamic':
			ajaxget(MOD_URL+'&op=ajax&do=dynamic&rid='+rid+'&fid='+fid,'dynamic-tab','dynamic-tab')
		break;
		case 'version':
			ajaxget(MOD_URL+'&op=ajax&do=version&rid='+rid,'version-tab','version-tab')
		break;
	}
}

function editgroupperm(rid,fid){
	showWindow('property', MOD_URL+'&op=ajax&do=selectperm&fid='+fid+'&rid='+rid+'&new=0','get','0');
}

function historyupload(obj,rid) {
	var el = $(obj);
	el.off();
	el.fileupload({
        url: MOD_URL + '&op=ajax&do=uploadfiles',
        dataType: 'json',
        autoUpload: true,
        maxChunkSize: parseInt(_explorer.space.maxChunkSize), //2M
        dropZone: '.historyMenu',
        pasteZone: '.historyMenu',
        maxFileSize: parseInt(_explorer.space.maxattachsize) > 0 ? parseInt(_explorer.space.maxattachsize) : null, // 5 MB
        acceptFileTypes: new RegExp(attachextensions, 'i'),
        sequentialUploads: true
    }).on('fileuploadadd', function (e, data) {
        layerupload();
        data.context = $('<li class="dialog-file-list"></li>').appendTo($('.dialog-filelist-ul'));
        $.each(data.files, function (index, file) {
            $(getItemTpl(file)).appendTo(data.context);
           _upload.total++;
            $('#upload_header_status').html(__lang.upload_processing);
            $('#upload_header_number_container').show();
            $('#upload_header_total').html(_upload.total);
        });
    }).on('fileuploadsubmit', function (e, data) {
        data.context.find('.upload-cancel').off('click').on('click', function () {
            data.abort();
            data.files = '';
            uploaddone();
            $(this).parents('.dialog-info').find('.upload-cancel').hide();
            $(this).parents('.dialog-info').find('.upload-file-status').html('<span class="cancel show_uploading_status"><em></em><i>' + __lang.already_cancel + '</i></span>');
        });
        $.each(data.files, function (index, file) {
            file.relativePath = (file.relativePath) ? file.relativePath + file.name : '';
            var relativePath = (file.webkitRelativePath ? file.webkitRelativePath : file.relativePath);
            data.formData = {relativePath: relativePath};
            return;
        });
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index];
        if (file.error) {
            uploaddone('error');
            var err = file.error ? file.error  : __lang.upload_failure;
            data.context.find('.upload-file-status').html('<span class="text-danger" title="' + err + '">' + err + '</span>');
        }
    }).on('fileuploadprogress', function (e, data) {
        var index = data.index;
        _upload.bitrate = formatSize(data.bitrate / 8);
        var progre = parseInt(data.loaded / data.total * 100, 10);
        data.context.find('.upload-file-status .speed').html(_upload.bitrate + '/s');
        data.context.find('.upload-file-status .precent').html(progre + '%');
    }).on('fileuploadprogressall', function (e, data) {
        _upload.bitrate = formatSize(data.bitrate / 8);
        var progre = parseInt(data.loaded / data.total * 100, 10);
        uploadprogress(_upload.bitrate + '/s', progre + '%');
        _upload.el.find('.panel-heading .upload-progress-mask').css('width', progre + '%');
    }).on('fileuploaddone', function (e, data) {
        uploaddone();
        data.context.find('.upload-progress-mask').css('width', '0%');
        data.context.find('.upload-cancel').hide();
        $.each(data.result.files, function (index, file) {
            if (file.error) {
                var err = file.error ? file.error  : __lang.upload_failure;
                data.context.find('.dialog-info .upload-file-status').html('<span class="text-danger" title="' + err + '">' + err + '</span>');
            } else {
                var filedata = file.data;
                //_upload.tips.find('.dialog-body-text').html(_upload.completed + '/' + _upload.total);
                data.context.find('.upload-file-status .precent').html(__lang.update_finish);
                data.context.addClass('success').find('.upload-file-status .speed').html('');
                data.context.find('.upload-file-operate').html('');
				data.context.find('.process').css('display', 'none');
                $.post(MOD_URL+'&op=dzzcp&do=uploadnewVersion', {
                    'rid': rid,
                    'aid': filedata.aid,
                    'name': filedata.filename,
                    'ext': filedata.filetype,
                    'size': filedata.filesize,
                }, function (data) {
                    if (data['success']) {
                        var resourcesdata = data['filedata'];
                        $('.detailsimage').attr('src', SITEURL + resourcesdata['img']);
                        $('.right-imgname').html(resourcesdata['name']).attr('title', resourcesdata['name']);
                        _explorer.sourcedata.icos[data['filedata'].rid] = data['filedata'];
                        _filemanage.cons['f-' + data['filedata'].pfid].CreateIcos(data['filedata'], true);
                        resourcesdata.vid = 0;
                        _filemanage.addIndex(resourcesdata);
						if ($('.version-tab').hasClass('active')) {
							jQuery('.version-tab').click();
						}
						if ($('.index-tab').hasClass('active')) {
							_filemanage.prototype._selectInfo();
						}
						if ($('.property-version-tab').hasClass('active')) {
							var rid = $('.property-version-tab').data('rid');
							ajaxget(MOD_URL+'&op=ajax&do=version&property=1&rid='+rid, 'fwin_content_property');
						}
                    } else if (data['error']) {
                        layer.alert(data['error'], {skin:'lyear-skin-danger'});
                    }
                }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
                    showmessage('{lang do_failed}', 'error', 3000, 1);
                });
            }
        });

    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index, file) {
            uploaddone();
            data.context.find('.upload-item.percent').html('<span class="text-danger" title="' + file.error + '">' + file.error + '</span>');
        });

    });
}
function getItemTpl(file) {
	var relativePath = (file.webkitRelativePath ? file.webkitRelativePath : (file.relativePath ? file.relativePath : file.name));
	var filearr = file.name.split('.');
	var ext = filearr.pop();
	var imgicon = '<img src="dzz/images/extimg/' + ext + '.png" onerror="replace_img(this)" style="width:24px;height:24px;position:absolute;left:0;"/>';
	var html =
		'<div class="process" style="position:absolute;z-index:-1;height:100%;background-color:#e8f5e9;-webkit-transition:width 0.6s ease;-o-transition:width 0.6s ease;transition:width 0.6s ease;width:0%;"></div> <div class="dialog-info"> <div class="upload-file-name">' +
		'<div class="dialog-file-icon" align="center">' + imgicon + '</div> <span class="name-text">' + file.name + '</span> ' +
		'</div> <div class="upload-file-size">' + (file.size ? formatSize(file.size) : '') + '</div> <div class="upload-file-path">' +
		'<a title="" class="" href="javascript:;">' + relativePath + '</a> </div> <div class="upload-file-status"> <span class="uploading"><em class="precent"></em><em class="speed">排队中</em>' +
		'</span> <span class="success"><em></em><i></i></span> </div> <div class="upload-file-operate"> ' +
		'<em class="operate-pause"></em> <em class="operate-continue"></em> <em class="operate-retry"></em> <em class="operate-remove"></em> ' +
		'<a class="error-link upload-cancel" href="javascript:void(0);">取消</a> </div> </div>';
	return html;
}
function uploaddone(flag) {
	_upload.completed++;
	_upload.completed++;
	if(flag == 'error') _upload.errored++;
	else _upload.succeed++;
	if (_upload.completed >= _upload.total) {
		$('#upload_header_status').html(__lang.upload_finish);
		$('#upload_header_completed').html(_upload.succeed);
		$('#upload_header_total').html(_upload.total);
		$('#upload_header_progress').css('width', 0);
		if (_upload.speedTimer) window.clearTimeout(_upload.speedTimer);
		_upload.speedTimer = window.setTimeout(function () {
			$('#upload_header_speed').hide();
			//_upload.el.find('li.success').remove();
		}, 3000);
	} else {
		$('#upload_header_completed').html(_upload.succeed);
	}
	var li=$('.dialog-filelist-ul').find('li.success');
	if(_upload.maxli && li.length>=_upload.maxli){
		//li.remove();
	}
}

function uploadprogress(speed, per) {
	_upload.el.find('.upload-speed').show().find('.upload-speed-value').html(speed);
	if (_upload.speedTimer) window.clearTimeout(_upload.speedTimer);
	_upload.speedTimer = window.setTimeout(function () {
		_upload.el.find('.upload-speed').hide();
	}, 2000);
}
function checkStatusBtn(id) {
	var checkStatus = layuiModules.table.checkStatus(id);
	var isChecked = checkStatus.data.length > 0;
	var buttonIds = ['getCheckDataBtn', 'deletebtn'];
	buttonIds.forEach(function(btnId) {
		var btn = document.getElementById(btnId);
		if (btn) {
			if (isChecked) {
				btn.removeAttribute('disabled');
				btn.classList.remove('layui-btn-disabled');
			} else {
				btn.setAttribute('disabled', 'disabled');
				btn.classList.add('layui-btn-disabled');
			}
		}
	});
}