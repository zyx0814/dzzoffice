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
	//_explorer.applist=json.applist || [];
	_explorer.thame = json.thame || {};
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

	$.post(_explorer.appUrl + '&op=ajax&operation=getfid', queryobj, function (data) {
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
		}
	}, 'json');
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

_explorer.loading = function (container, flag) { //右侧加载效果
	if (flag === 'hide') {
		container.find('.rightLoading').remove();
	} else {
		container.append('<div class="rightLoading"></div>');
	}
};
_explorer.getRightContent = function (hash, container) { //处理右侧页面加载
	var searchpreg = /#searchFile/;
	if (!searchpreg.test(hash)) {
		try {
			resetting_condition();
		} catch (e) {}
	}
	_explorer.loading(container);
	_explorer.rightLoading = 1;
	$('.document-data').removeClass('actives');
	$('[data-hash="' + hash + '"]').addClass('actives');
	var url = _explorer.appUrl + '&op=' + hash;
	jQuery('#middleconMenu').load(url, function () {
		$(document).trigger('ajaxLoad.middleContent', [hash]);
	});

};
_explorer.topMenu = function (hash, fid) {
	var shownewbuild = false;
	if (hash) {
		//根据hash值判断是否显示在头部
		if (hash == 'groupmanage' || hash == 'app' || hash == 'dynamic' || hash == 'mygroup' || hash.indexOf('share') == 0 || hash.indexOf('recycle') == 0) {
			jQuery('.rightswitch').hide();
			if(hash.indexOf('recycle') == 0){
				jQuery('.listchange').show();
			}else{
				jQuery('.listchange').hide();
			}
			_explorer.infoPanel_hide = 1; //标识右侧面板有没有
		} else {
			jQuery('.listchange').show();
			jQuery('.rightswitch').show();
			_explorer.infoPanel_hide = 0;
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
		if (folderperm  || uploadperm) { //如果没有文件夹权限和文件权限，隐藏新建上传菜单
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
	//if(!_explorer.jstree) return;
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
	} else if (op === 'mygroup') {
		$('#position').jstree(true).select_node('#group');
	} else {
		if ($('#position').length > 0) {
			$('#position').jstree(true).deselect_all();
		}
	}
};
_explorer.open_node_by_id = function (fid, gid) {
	var inst = $('#position').jstree(true);
	var node = null;
	if (fid) {
		node = inst.get_node('#f_' + fid) || inst.get_node('#u_' + fid);
	} else if (gid) {
		node = inst.get_node('#g_' + gid) || inst.get_node('#gid_' + gid);
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
			'gid': gid
		}, function (data) {
			var node = inst.get_node('#' + data[0]);
			_explorer.open_node_bg(inst, node, data);
		}, 'json');
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
				},'json')
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
				img.style.width = realw + 'px';
				img.style.height = realh + 'px';
			} else {
				realh = (h > height) ? parseInt(height) : h;
				realw = (h > height) ? parseInt(width) : (realh * w / h);
				img.style.width = realw + 'px';
				img.style.height = 'auto';
			}
			if (realw < 32 && realh < 32) {
				jQuery(img).addClass('image_tosmall').css({
					padding: ((height - realh) / 2 - 1) + 'px ' + ((width - realw) / 2 - 1) + 'px'
				});
			}
			try {
				/*img.style.width=realw+'px';
				img.style.height='auto'  */
			} catch (e) {

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
		}
	});
	return remsg;
}
