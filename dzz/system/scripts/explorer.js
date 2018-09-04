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
    _explorer.type = json.fileselectiontype || 0;//文件选择类型，0为选择文件,1为保存文件，2选择位置
    _explorer.defaultselect = json.defaultselect;
    _explorer.allowcreate = json.allowcreate;
    _explorer.mulitype = json.mulitype || 0;//是否允许多选，默认不允许
    _explorer.permfilter = json.permfilter || '';//文件检索写入权限过滤
    //如果是保存文件并且未带入权限过滤参数，强制过滤写入权限
    if(_explorer.type == 1){
        _explorer.permfilter = (json.permfilter) ? json.permfilter:'write';
    }
    //如果是选择位置
    if (_explorer.type == 2) {
        _explorer.allowselecttype = {'folder': ['文件夹', ['folder'], 'selected']};
    } else {
        _explorer.allowselecttype = json.allowselecttype || '';//允许筛选文件类型
    }
    //默认筛选文件类型
    if (json.allowselecttype) {
        for (var o in json.allowselecttype) {
            if (json.allowselecttype[o][2] == 'selected') {
                _explorer.defaultexttype = json.allowselecttype[o][1].join(',').toLowerCase();
            }
        }
    } else {
        _explorer.defaultexttype = '';
    }
    _explorer.defaultfilename = json.defaultfilename || '';
    _explorer.thame = json.thame || {};
    _explorer.infoRequest = 0;
    _explorer.deletefinally = json.deletefinally || 0;
    _explorer.cut = json.cut || {
            iscut: 0,
            icos: []
        };
};
_explorer.appUrl = 'index.php?mod=system&op=fileselection';
_explorer.hash = '';
_explorer.getConfig = function (url, callback) {
    $.getJSON(url + '&t=' + new Date().getTime(), function (json) {
        new _explorer(json);
        _explorer.hashHandler();
        _explorer.initEvents();
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
_explorer.createMenuSwidth = function (fid) {
    //判断新建和上传图标显示
    var folderperm = false;//文件夹权限
    var uploadperm = false;//上传权限
    if(_explorer.allowcreate){
        //文件夹权限(判断是否有文件夹权限如果没有隐藏文件夹相关新建上传),如果是选择位置隐藏新建文件
        if (!_explorer.Permission_Container('upload', fid) || _explorer.type == 2) {
            $('#createmenu').find('li').not('.folderPermMust').addClass('hide');
        } else {
            $('#createmenu').find('li').not('.folderPermMust').removeClass('hide');
            folderperm = true;
        }
        if (!_explorer.Permission_Container('folder', fid)) { //其它类型新建权限，如果无权限，隐藏文件相关权限
            jQuery('#createmenu').find('li.folderPermMust').addClass('hide');
        } else {
            jQuery('#createmenu').find('li.folderPermMust').removeClass('hide');
            uploadperm = true;
        }
        if (folderperm || uploadperm) { //如果没有文件夹权限和文件权限，隐藏新建上传菜单
            $('#createmenu').removeClass('hide');
            $('#exampleColorDropdown2').removeClass('hide');
        } else {
            $('#createmenu').addClass('hide');
            $('#exampleColorDropdown2').addClass('hide');
        }
        //去掉多余的分割线
        $('.divider').each(function(){
            if($(this).prev('li:visible').length < 1){
                $(this).remove();
            }
        })
    }
}
_explorer.set_address = function (path) {
    var pathstr = path;
    $('.select-address input.inputaddress').val(pathstr);
    var patharr = pathstr.split('\\');
    var address_html = '';
    for (var o in patharr) {
        address_html += ' <li class="routes"> <a href="javascript:;">' + patharr[o] + '</a> <span class="dzz dzz-chevron-right"></span></li>';
    }
    $('.select-address div.address-field').html(address_html);
}
_explorer.address_resize = function (dir) {
    var container = jQuery('.address-container');
    var address = jQuery('.address-field');
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
    $(document).off('click.address-left-arrow').on('click.address-left-arrow', '.address-left-arrow', function () {
        _explorer.address_resize('left');
        return false;
    });
    $(document).off('click.address-right-arrow').on('click.address-right-arrow', '.address-right-arrow', function () {
        _explorer.address_resize('right');
        return false;
    });

    //点击路径切栏切换位置
    $(document).off('click.routes').on('click.routes', '.address-container  .routes', function () {
        var path = '';
        var text = $(this).text().replace(/(^\s*)|(\s*$)/g, '');
        var textprefix = /[:：]/;
        var prefix = '';
        var textarr = [];
        if (textprefix.test(text)) {
            textarr = text.split(/[:：]/);
            prefix = textarr[0];
            text = textarr[1];
        }
        $(this).closest('li').prevAll().find('a').each(function () {

            path += $(this).text().replace(/(^\s*)|(\s*$)/g, '') + '/';
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

    $.post(_explorer.appUrl + '&do=ajax&operation=getfid', queryobj, function (data) {
        if (data.success) {
            var hash = '';
            if (!isNaN(parseInt(data.success['gid']))) {
                hash = 'group&gid=' + data.success['gid'] + (data.success['fid'] ? '&fid=' + data.success['fid'] : '');
            } else {
                hash = 'home&fid=' + data.success['fid'];
            }
            location.hash = hash;
        }
    }, 'json');
    return false;
};
_explorer.hashHandler = function () { //处理页面hash变化
    var hash = location.hash;
    hash = hash.replace(/^#/i, '');
    if (!hash) {
        hash = _explorer.defaultselect;
        _explorer.jstree_select(hash);
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
    //_explorer.topMenu(hash);
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
    _explorer.loading(container);
    _explorer.rightLoading = 1;
    $('.document-data').removeClass('actives');
    $('[data-hash="' + hash + '"]').addClass('actives');
    var url = _explorer.appUrl + '&do=file&' + hash;
    jQuery('#middleconMenu').load(url, function () {
        $(document).trigger('ajaxLoad.middleContent', [hash]);
    });

};
//通过hash值来设置左侧树的选择指示
_explorer.jstree_select = function (hash) {
    if (!hash) {
        hash = location.hash.replace('#', '');
    }
    if (!hash) {
        hash = $('#position').find("li[flag='home']").attr('hashs');
    }
    var op = hash.replace(/&(.+?)$/ig, ''); //(hash,'op');
    var fid = _explorer.getUrlParam(hash, 'fid');
    if (op === 'group') {
        var gid = _explorer.getUrlParam(hash, 'gid');
        _explorer.open_node_by_id(fid, gid);
    } else if (op === 'home') {
        _explorer.open_node_by_id(fid);
    } /*else if (op === 'mygroup') {
     $('#position').jstree(true).select_node('#group');
     }*/ else {
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
        $.post(_explorer.appUrl + '&op=filelist&do=getParentsArr', {
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
            inst.deselect_all();
            inst.select_node(node);
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
            function () {
            },
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
