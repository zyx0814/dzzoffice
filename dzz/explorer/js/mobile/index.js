var _filemanage = {};
_filemanage = function (json) {
    _filemanage.space = json.space; //用户信息
    _filemanage.myuid = json.myuid;
    _filemanage.formhash = json.formhash; //FORMHASH
    _filemanage.extopen = json.extopen || {}; //打开方式信息
    _filemanage.app = json.app || []; //所有文件信息
    _filemanage.copyfile = json.clipboarddata || {
            status: 0,
            type: 0
        };
    _filemanage.is_wxwork = (json.is_wxwork) ? 1 : 0;
};
_filemanage.folderdata = {};//文件夹数据
_filemanage.datajson = {};//文件列表数据
_filemanage.dataurl = {};//请求文件链接
_filemanage.dataparam = {};//请求文件条件参数
_filemanage.contains = '';//当前页面文件列表区域
_filemanage.selector = [];//选择文件rid
_filemanage.selectorTime = null;//选择执行定时器
_filemanage.collect = 1;//收藏
var deviceAgent = navigator.userAgent;
_filemanage.ios = deviceAgent.toLowerCase().match(/(iphone|ipod|ipad)/);
$.toast.prototype.defaults.duration=1000;
_filemanage.getConfig = function (url, callback) {
    $.getJSON(url + '&t=' + new Date().getTime(), function (json) {
        new _filemanage(json);
        if (typeof callback === "function") {
            callback(json);
        }
    });
}
//获取列表数据
_filemanage.getData = function (callback) {
    if (!_filemanage.datajson.param) _filemanage.datajson.param = {k: Math.random()};
    if (_filemanage.fid) {
        _filemanage.datajson.param.fid = _filemanage.fid;
        _filemanage.datajson.param.gid = _filemanage.folderdata[_filemanage.fid].gid;
    }
    $.post(_filemanage.dataurl, _filemanage.datajson.param, function (data) {
        if (typeof (callback) === 'function') {
            callback(data);
        } else {
            $('#' + _filemanage.contains).html(data);
            $('.weui-cells__margin_footer').css('margin-bottom', '70px');
            _filemanage.menuSwitch();
            if (!_filemanage.datajson.param.datatotal) {
                var con = $('.weui-cell-search-normal').html();
                $('.weui-cells__margin_footer').css('margin-bottom', 0);
                $('#' + _filemanage.contains).html(con);
                return false;
            }
            _filemanage.loadMore();
        }
    })
}
//列表加载更多
var scroll_flag = 1//开启状态
_filemanage.loadMore = function () {
    if (_filemanage.datajson.param.page) {
        _filemanage.getData(function (data) {
            if (scroll_flag == 1) {
                $(window).off();
                scroll_flag = 0;
                if (data) {
                    scroll_flag = 1;
                    $(window).scroll(function () {
                        var scrollTop = $(this).scrollTop();
                        var scrollHeight = $(document).height();
                        var clientHeight = $(this).height();
                        if (scrollTop + clientHeight >= scrollHeight) {
                            if (_filemanage.datajson.param.page > 0) {
                                $('#' + _filemanage.contains).append(data);
                                _filemanage.menuSwitch();
                                _filemanage.loadMore();
                            }
                        }
                    });
                } else {
                    scroll_flag = 0;
                }
            }
        });
    }

}
function is_not_allowdown(){
	return false;
    var u = navigator.userAgent;
    if(!!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/) || u.indexOf('MicroMessenger') > -1){
        return true;
    }
    return false;
}

//底部菜单切换
_filemanage.menuSwitch = function () {
//当有复制或剪切文件时，底部菜单显示(即复制状态)
    if (_filemanage.copyfile.status > 0) {
        $('.weui-cells_checkbox').addClass('hide');
        $('.weui-cell__ft').show();
        $('#copypaste_menu').removeClass('hide').siblings().addClass('hide');
        _filemanage.checkMenuCopy();
        _filemanage.selector = [];
        return false;
    } else if (_filemanage.selector.length > 0) {//有选择时菜单显示
        $('#select-menu').removeClass('hide').siblings().addClass('hide');
        $('.weui-cell__ft').not('.nochecked').hide();//隐藏打开指示箭头
        $('.weui-cells_checkbox').removeClass('hide');//显示多选框
        _filemanage.checkMenuSelect();
        return false;
    } else {//无选择时菜单显示
        _filemanage.checkMenuinit()
        $('.weui-cells_checkbox').addClass('hide');
        $('.weui-cell__ft').show();
        $('.weui-cell_longpress').removeAttr('times');
        $('#nomal_menu').removeClass('hide').siblings('.model_menus').addClass('hide');
    }
}
//初始化页面菜单权限判断(判断新建权限:包含文件夹和文件)正常状态下
_filemanage.checkMenuinit = function () {
    var fid = _filemanage.fid;
    var cid = _filemanage.contains;
    var catpreg = /^fileList-cat-\d+$/;
    if (catpreg.test(_filemanage.contains)) {
        $('#nomal_menu').find('.singlepropetymenu').removeClass('hide');
    } else {
        $('#nomal_menu').find('.singlepropetymenu').addClass('hide');
    }
    if (fid) {
        $('#nomal_menu').find('.moremenus').removeClass('hide').show();
        var gid = _filemanage.folderdata[_filemanage.fid].gid;
        //成员
        if (gid) {
            $('#nomal_menu').find('.membermenu').removeClass('hide');
        } else {
            $('#nomal_menu').find('.membermenu').addClass('hide');
        }
        //上传
        if (!_permcheck.Permission_Container('upload', fid) && !_permcheck.Permission_Container('folder', fid)) {
            $('#nomal_menu').find('.uploadnewfile,.newfilemenu').addClass('hide');
        } else {
            $('#nomal_menu').find('.uploadnewfile,.newfilemenu').removeClass('hide');
        }
        //新建文件夹
        if (!_permcheck.Permission_Container('folder', fid)) {
            $('#nomal_menu').find('.newfoldermenu').addClass('hide');
        } else {
            $('#nomal_menu').find('.newfoldermenu').removeClass('hide');
        }
        //评论
        if (!_permcheck.Permission_Container('read', fid)) {
            $('#nomal_menu').find('.commentmenu').addClass('hide');
        } else {
            $('#nomal_menu').find('.commentmenu').removeClass('hide');
        }
        $.getScript(MOD_PATH + '/js/mobile/upload.js', function () {
            jQuery('.explorer_upload_files').each(function () {
                var obj = $(this), typeallow = '';
                if (obj.data('onlyimg')) {
                    typeallow = '(\.|\/)([gif|png|jpe?g])$';
                }
                fileupload(obj, fid, typeallow);
            });
        });
    } else {
        $('.weui-cell-footer:visible').find('.moremenus').addClass('hide');
    }

}
//选中文件菜单权限判断
_filemanage.checkMenuSelect = function () {
    //多选文件时不允许评论和重命名
    if (_filemanage.selector.length > 1) {
        $('#select-menu').find('.commentmenu,.renamemenu').addClass('hide');
    } else {
        $('#select-menu').find('.commentmenu,.renamemenu').removeClass('hide');
    }
    //非群组或不在群组目录下不允许查看成员
    if (!_filemanage.fid || !_filemanage.folderdata[_filemanage.fid].gid) {
        $('#select-menu').find('.membermenu').addClass('hide');
    } else {
        $('#select-menu').find('.membermenu').removeClass('hide');
    }
    var currentIndex = _filemanage.selector.length - 1, data = _filemanage.datajson.data[_filemanage.selector[currentIndex]];
    var id = _filemanage.contains;
    //收藏中，仅允许取消收藏
    if (id == 'fileList-collect') {
        $('#select-menu').find('.collectmenu').addClass('hide');
        $('#select-menu').find('.cancel-collectmenu').removeClass('hide');
    } else {
        $('#select-menu').find('.collectmenu').removeClass('hide');
        $('#select-menu').find('.cancel-collectmenu').addClass('hide');
    }
    //判断复制权限
    if (!_permcheck.Permission('copy', data)) {
        $('#select-menu').find('.copymenu').addClass('hide');
    } else {
        $('#select-menu').find('.copymenu').removeClass('hide');
    }
    //不在文件目录下或没有删除权限的，不允许执行剪切重命名和删除
    if (!_filemanage.fid || !_permcheck.Permission('delete', data)) {
        $('#select-menu').find('.cutmenu,.deletemenu,.renamemenu').addClass('hide');
    } else {
        $('#select-menu').find('.cutmenu,.deletemenu,.renamemenu').removeClass('hide');
    }
    //判断下载权限
    if (!_permcheck.Permission('download', data) || is_not_allowdown()) {
        $('#select-menu').find('.downloadmenu').addClass('hide');
    } else {
        $('#select-menu').find('.downloadmenu').removeClass('hide');
    }
    //判断分享权限
    if (!_permcheck.Permission('share', data)) {
        $('#select-menu').find('.sharemenu').addClass('hide');
    } else {
        $('#select-menu').find('.sharemenu').removeClass('hide');
    }
    var pop = _filemanage.selector.pop();
    _filemanage.selector.push(pop);
    var index = _filemanage.datajson.data[pop].collect;
    if (index) {//收藏成功
        for (var o in _filemanage.selector) {
            if (!_filemanage.datajson.data[_filemanage.selector[o]].collect) {
                _filemanage.collect = 0; //未收藏
            } else {
                _filemanage.collect = 1;
            }
        }
        if (_filemanage.collect == 0) {
            $('#select-menu').find('.collectmenu').removeClass('hide');
            $('#select-menu').find('.cancel-collectmenu').addClass('hide');
        } else {
            $('#select-menu').find('.collectmenu').addClass('hide');
            $('#select-menu').find('.cancel-collectmenu').removeClass('hide');
        }

    } else {
        _filemanage.collect = 0;
        $('#select-menu').find('.collectmenu').removeClass('hide');
        $('#select-menu').find('.cancel-collectmenu').addClass('hide');

    }

}
//复制模式下(即有复制或剪切文件的情形)的菜单处理
_filemanage.checkMenuCopy = function () {
    var fid = _filemanage.fid;
    var fileperm = _permcheck.Permission_Container('upload', fid);
    var folderperm = _permcheck.Permission_Container('folder', fid);
    if (!fileperm && !folderperm) {
        $('.weui-cell-footer:visible').find('.uploadnewfile,.pastemenu').addClass('hide');
    } else if (!fileperm) {
        $('.weui-cell-footer:visible').find('.uploadnewfile').addClass('hide');
        if (_filemanage.copyfile.type == 2) $('.weui-cell-footer:visible').find('.pastemenu').addClass('hide');
    } else if (!folderperm) {
        $('.weui-cell-footer:visible').find('.newfoldermenu').addClass('hide');
        if (_filemanage.copyfile.type == 1) $('.weui-cell-footer:visible').find('.pastemenu').addClass('hide');
    } else {
        $('.weui-cell-footer:visible').find('.pastemenu').removeClass('hide');
    }
}
//开始长按
$(document).bind('contextmenu',function(){
	var e=event;
	e.preventDefault();
})
function gtouchstart(obj) {
//非复制状态
    if (_filemanage.copyfile.status == 0 && _filemanage.selector.length < 1) {
        _filemanage.selectorTime = setTimeout(function () {
            _filemanage.selectFile(obj);
        }, 500);
    }
    return false;
}
//选择或取消选择
_filemanage.selectFile = function (obj) {
    $('.weui-cell_longpress').attr('times', '1');//设置当前项不可跳转
    var rid = $(obj).data('rid'), index = $.inArray(rid, _filemanage.selector);
    var check = $(obj).find('.weui-cells_checkbox input').prop("checked");
//如果当前项是选中，则取消选择
    if (check) {
        $(obj).find('.weui-cells_checkbox input').prop("checked", false);
        if (index > -1) {
            _filemanage.selector.splice(index, 1);
        }
    } else {
        $(obj).find('.weui-cells_checkbox input').prop("checked", true);
        if (index == -1) {
            _filemanage.selector.push(rid);
        }
    }
    _filemanage.menuSwitch();

}
//取消页面全部选择
_filemanage.cancel = function () {
    _filemanage.selector = [];
    $('.weui-cells_checkbox input').prop("checked", false);
    _filemanage.menuSwitch();
}

//点击处理
$(document).off('tap.click').on('tap.click', '.weui-cell_longpress', function (e) {
    var obj = $(this);
    //如果是选中模式下，执行选中或取消选择
    if (_filemanage.selector.length > 0 && _filemanage.copyfile.status == 0) {
        _filemanage.selectFile(obj);
        return false;
    } else {
        if ($(obj).data('open') == 'href') {
            var href = $(obj).attr('href');
            window.location.href=href;
        } else {
            _filemanage.Open($(obj).data('rid'));
        }
    }
    return false;
})
//长按操作
$(document).off('longTap.longclick').on('longTap.longclick', '.select-files', function (e) {
    var obj = $(this);
    if (_filemanage.copyfile.status == 0 && _filemanage.selector.length < 1) {
        _filemanage.selectFile(obj);
    }
    e.preventDefault();
    return false;
})

//新建文件夹
jQuery(document).off('tap.create').on('tap.create', '.weui-footer-new-folder', function () {
    var dropup = $(this).next('.weui-dropup');
    if (dropup.hasClass('hide')) {
        dropup.removeClass('hide');
        dropup.next('.background-none').show();
        $(this).find('p').css({'color': '#3779ff'});
    }
})
//我的网盘弹出框点击其他地方消失
jQuery(document).off('tap.confirm').on('tap.confirm', '.background-none', function () {
    $(this).prev('.weui-dropup').addClass('hide');
    $(this).prevAll('.weui-footer-none').find('p').css({'color': '#666666'});
    $(this).hide();
})
//新建文件夹
jQuery(document).off('tap.docreate').on('tap.docreate', '.new-folder', function (placeholder) {
    var obj = $(this);
    $.prompt({
        title: '新建文件夹',
        placeholder: '新建文件夹',
        empty: false, // 是否允许为空
        onOK: function (input) {
            var foldername = $('#weui-prompt-input').val(),
                fid = _filemanage.fid, emojpatt = /[\ud800-\udbff][\udc00-\udfff]/gi;
            if(emojpatt.test(foldername)){
                $.toast('文件名不合法!',"cancel");
                $('#weui-prompt-input').val('');
                return false;
            }
            $.post('index.php?mod=explorer&op=mobile&do=ajax&operation=createFolder', {
                'foldername': foldername,
                'fid': fid,
            }, function (data) {
                if (data['error']) {
                    $.toast(data['error'],1000);
                } else {
                    $('#' + _filemanage.contains).prepend(_filemanage.getNewIcos(data));
                    _filemanage.datajson.data[data.rid] = data;
                    obj.closest('div.weui-dropup').addClass('hide');
                    obj.closest('div.weui-dropup').siblings('.background-none').hide();
                    obj.closest('div.weui-dropup').siblings('.weui-footer-new-folder').find('p').css('color','#666');
                    $.toast("操作成功");
                }
            }, 'json');

        },
        onCancel: function () {
            $('#weui-prompt-input').val('');
            obj.closest('div.weui-dropup').addClass('hide');
            obj.closest('div.weui-dropup').siblings('.background-none').hide();
            obj.closest('div.weui-dropup').siblings('.weui-footer-new-folder').find('p').css('color','#666');
        }
    });
});
_filemanage.getNewIcos = function (data) {
    var html = '';
    if (data['type'] == 'folder') {
        html = '<div class="weui-cell weui-cell_access weui-cell_longpress select-files" data-open="href" ' +
            'href="' + MOD_URL + '&op=mobile&do=file&fid=' + data.oid + '" data-rid="' + data.rid + '" rid="' + data.rid + '"  data-collect="0" data-dpath="' + data.dpath + '" data-url="' + data.url + '"> ' +
            '<div class="weui-cell__hd"><img src="' + data.img + '" class="weui-cell__recentimg"> </div> <div class="weui-cell__bd"> <h4 rid="' + data.rid + '">' + data.name + '</h4> ' +
            '<p> <span class="file">文件:</span><i class="file-number">0,</i> <span class="folder">文件夹:</span>' +
            '<i class="folder-number">0</i> </p> </div> <div class="weui-cell__ft"></div> <div class="weui-cells_checkbox hide"> ' +
            '<input type="checkbox" class="weui-check" name="checkbox1"> <i class="weui-icon-checked"></i> </div> </div>';
    } else {
        html = '<div class="weui-cell weui-cell_access weui-cell_longpress select-files" href="javascript:;"' +
            ' data-original="index.php?mod=io&op=thumbnail&original=1&path=' + data.dpath + '" data-rid="' + data.rid + '" rid="' + data.rid + '" data-collect="0" data-dpath="' + data.dpath + '" data-url="' + data.url + '"> ' +
            '<div class="weui-cell__hd"><img src="' + data.img + '" class="weui-cell__recentimg"></div> ' +
            '<div class="weui-cell__bd"> <h4 rid="' + data.rid + '">' + data.name + '</h4> <p> <span class="date">' + data.monthdate + '</span><i class="date-time">' + data.hourdate + ',</i> ' +
            '<span class="size">' + data.fsize + '</span> </p> </div> <div class="weui-cells_checkbox hide"> <input type="checkbox" class="weui-check" name="checkbox1"> ' +
            '<i class="weui-icon-checked"></i> </div> </div>';
    }
    return html;
}
//排序菜单
jQuery(document).off('tap.array').on('tap.array', '.weui-footer-sort', function () {
    var dropup = $(this).next('.weui-dropup');
    if (dropup.hasClass('hide')) {
        dropup.removeClass('hide');
        dropup.next('.background-none').show();
        $(this).find('p').css({'color': '#3779ff'});
    }
})
//执行排序
$(document).off('tap.doarrag').on('tap.doarrag', '.sortfile', function () {
    var sort = $(this).data('sort');
    if (_filemanage.datajson.param.disp == sort) _filemanage.datajson.param.asc = (_filemanage.datajson.param.asc > 0) ? 0 : 1;
    _filemanage.datajson.param.disp = sort;
    _filemanage.datajson.param.page = 1;
    _filemanage.datajson.param.datatotal = 0;
    _filemanage.getData();
    $(this).closest('.weui-dropup').addClass('hide');
    $(this).closest('.weui-dropup').siblings('.weui-footer-sort').find('p').css({'color': '#666'});
    $(this).closest('.weui-dropup').next('.background-none').hide();
})

//更多菜单
jQuery(document).on('tap', '.weui-footer-more', function () {
    var dropup = $(this).next('.weui-dropup');
    if (dropup.hasClass('hide')) {
        dropup.removeClass('hide');
        dropup.next('.background-none').show();
        $(this).find('p').css({'color': '#3779ff'});
    }
})
//复制
jQuery(document).on('tap', '.copyorcut', function () {
    var copytype = $(this).data('copytype'), path = [], data = {};
    if (_filemanage.selector.length > 0) {
        var icosdata = _filemanage.datajson.data[_filemanage.selector[0]], bz = icosdata.bz;
        for (var i in _filemanage.selector) {
            path.push(_filemanage.datajson.data[_filemanage.selector[i]].dpath);
        }
        if (path.length > 0) data = {'rids': path, 'bz': bz, 'copytype': copytype};
        else return false;
    } else {
        return false;
    }
    var url = MOD_URL + '&op=dzzcp&do=copyfile&t=' + new Date().getTime();
    jQuery.post(url, data, function (json) {
        if (json.msg === 'success') {
            var filenames = '';
            for (var o in json['rid']) {
                if (copytype == 2) jQuery('.weui-cell_longpress[rid=' + json.rid[o] + ']').addClass('iscut');
            }
            _filemanage.copyfile.status = 1;
            _filemanage.copyfile.type = json['type'];
            _filemanage.cancel();
            if (copytype == 2) {
                $.toast(__lang.crop_files_success);
            } else {
                $.toast(__lang.file_copy_success);
            }
        } else {
            $.toast(json.msg);
            _filemanage.cancel();
        }
    }, 'json');

})
//取消复制
$(document).on('tap', '.canclepastemenu', function () {
    var url = MOD_URL + '&op=dzzcp&do=deletecopy&t=' + new Date().getTime();
    jQuery.post(url, {k: Math.random()}, function (json) {
        if (json['success']) {
            $.toast('取消成功');
            _filemanage.copyfile.status = 0;
            _filemanage.menuSwitch();
        } else {
            $.toast('取消失败');
        }
    }, 'json')
})
//粘贴
$(document).on('tap', '.pastemenu', function () {
    var tpath = _filemanage.fid;
    var url = MOD_URL + '&op=dzzcp&do=paste';
    var i = 0;
    var node = null;
    jQuery.post(url, {'tpath': _filemanage.fid, k: Math.random()}, function (json) {
        if (json.icoarr) {
            for (i = 0; i < json.icoarr.length; i++) {
                if (json.icoarr[i].pfid === _filemanage.fid) {
                    _filemanage.datajson.data[json.icoarr[i].rid] = json.icoarr[i];
                    $('#' + _filemanage.contains).prepend(_filemanage.getNewIcos(json.icoarr[i]))
                }
            }
        }


        _filemanage.copyfile.status = 0;
        _filemanage.cancel();
        _filemanage.menuSwitch();
        $.toast("粘贴成功");
    }, 'json');
})
//删除文件
$(document).on('click', '.deletemenu', function () {
	$.confirm({
		  title: '确认删除',
		  text: '你确定删除？',
		  onOK: function () {
		  	var obj = $(this), path = [];
		    if (_filemanage.selector.length > 0) {
		        var icosdata = _filemanage.datajson.data[_filemanage.selector[0]], bz = icosdata.bz;
		        for (var i in _filemanage.selector) {
		            path.push(_filemanage.datajson.data[_filemanage.selector[i]].dpath);
		        }
		        if (path.length > 0){
		        	data = {'rids': path, 'bz': bz};	
		        } else{
		        	return false;	
		        } 
		    } else {
		        return false;
		    }
		    var url = MOD_URL + '&op=dzzcp&do=deleteIco&t=' + new Date().getTime();  
		    jQuery.post(url, data, function (json) {
		        var rids = [];
		        for (var i in json.msg) {
		            if (json.msg[i] === 'success') {
		                $('#' + _filemanage.contains).find('.weui-cell_access[rid=' + i + ']').remove();
		            } else {
		                $.toast(json.msg[i]);
		            }
		            $.toast('删除成功！');
		        }
		        obj.closest('.moredo').addClass('hide');
		        obj.closest('.moredo').siblings('.background-none').hide();
		        obj.closest('.moredo').siblings('.weui-footer-sort').find('p').css('color','#666666');
		        _filemanage.cancel();
		    }, 'json');
		},
		onCanel:function(){
			obj.closest('.moredo').addClass('hide');
	        obj.closest('.moredo').siblings('.background-none').hide();
	        obj.closest('.moredo').siblings('.weui-footer-sort').find('p').css('color','#666666');
	        _filemanage.cancel();
		}
	});
})
//复制模式下的取消
jQuery(document).on('tap', '.weui-footer-item-cancel', function () {
    jQuery('.weui-cell-footer-copy').addClass('hide');
    jQuery('.weui-cell-default-footer').removeClass('hide');
})
//取消全部选择
jQuery(document).on('tap', '.weui-footer-cancel-checked', function (e) {
    var obj = jQuery('.weui-cell_longpress');
    jQuery('.weui-cell_longpress').find('.weui-cells_checkbox input').prop("checked", false);
    _filemanage.cancel();
    return false;
});
//全选
jQuery(document).on('tap', '.weui-footer-all-checked', function () {
    $.each($('.weui-cell_longpress'), function () {
        var rid = $(this).data('rid'), index = $.inArray(rid, _filemanage.selector);
        var check = $(this).find('.weui-cells_checkbox input').prop("checked");
        if (!check) {
            $(this).find('.weui-cells_checkbox input').prop("checked", true);
            if (index == -1) {
                _filemanage.selector.push(rid);
            }
        }
        _filemanage.menuSwitch();
    })
})

//动态菜单点击
$(document).off('tap.dynamisc').on('tap.dynamisc', '.dynamiscmenu', function () {
    if ($('#submitForm').length < 1) {
        var form = $('<form id="submitForm"></form>');
        $(document.body).append(form);
    } else {
        form = $('#submitForm');
    }
    if ($('#fidinput').length < 1) {
        var finput = $('<input type="hidden" name="fid" id="fidinput" />');
        form.append(rinput);
    } else {
        var finput = $('#fidinput');
    }
    finput.val(_filemanage.fid);

    var action = MOD_URL + '&op=mobile&do=dynamic';
    if (_filemanage.selector.length > 0) {
        var rids = _filemanage.selector.join(',')
        if ($('#ridinput').length < 1) {
            var rinput = $('<input type="hidden" name="rid" id="ridinput" />');
            form.append(rinput);
        } else {
            var finput = $('#ridinput');
        }
        rinput.val(rids);
    } else if (_filemanage.fid) {
        if ($('#fidinput').length < 1) {
            var finput = $('<input type="hidden" name="fid" id="fidinput" />');
            form.append(finput);
        } else {
            var finput = $('#fidinput');
        }
        finput.val(_filemanage.fid);
    }
    form.attr('action', action);
    form.attr('method', 'post');
    form.submit();

})
//搜索跳转
$(document).off('tap.searchFile').on('tap.searchFile', '.searchFile', function () {
    var href =MOD_URL+'&op=mobile&do=search', catpreg = /^fileList-cat-\d+$/, collectpreg = /^fileList-collect$/;
    if (catpreg.test(_filemanage.contains)) {
        var cid = parseInt(_filemanage.contains.replace('fileList-cat-', ''));
        href = href + '&cid=' + cid;
    }
    if (_filemanage.fid) {//如果有fid
        href = href + '&fid=' + _filemanage.fid;
    }
    if (collectpreg.test(_filemanage.contains)) {
        href = href + '&collect=1';
    }
    window.location.href=href;

})

$(document).off('tap.propetymenu').on('tap.propetymenu', '.propetymenu,.singlepropetymenu', function () {
    var action = MOD_URL+'&op=mobile&do=property', catpreg = /^fileList-cat-\d+$/;
    if ($('#submitForm').length < 1) {
        var form = $('<form id="submitForm"></form>');
        $(document.body).append(form);
    } else {
        form = $('#submitForm');
    }

    if (_filemanage.selector.length > 0) {
        var rids = _filemanage.selector.join(',');
        if ($('#ridinput').length < 1) {
            var rinput = $('<input type="hidden" name="rid" id="ridinput"/>');
            form.append(rinput);
        } else {
            var rinput = $('#ridinput');
        }
        rinput.val(rids);
    } else if (_filemanage.fid) {
        if ($('#fidinput').length < 1) {
            var finput = $('<input type="hidden" name="fid" id="fidinput" />');
            form.append(finput);
        } else {
            var finput = $('#fidinput');
        }
        finput.val(_filemanage.fid);

    } else if (catpreg.test(_filemanage.contains)) {
        var cid = parseInt(_filemanage.contains.replace('fileList-cat-', ''));
        window.location.href=action+'&cid='+cid;
        return false;
    }
    form.attr('action', action);
    form.attr('method', 'post');
    form.submit();
})
$(document).off('tap.membermenu').on('tap.membermenu', '.membermenu', function () {
    var gid = 0, action=MOD_URL+'&op=mobile&do=member';
    if (_filemanage.selector.length > 0) {
        var data = _filemanage.datajson.data[_filemanage.selector[0]];
        gid = data.gid;
    } else {
        if (_filemanage.fid) {
            gid = _filemanage.folderdata[_filemanage.fid].gid;
        }
    }
    if (gid) {
        window.location.href=action+'&gid='+ gid;
    } else {
        return false;
    }
})
//收藏操作
$(document).off('tap.collectmenu').on('tap.collectmenu', '.collectmenu', function () {
    var path = [], collect = 1, obj = $(this);
    if (_filemanage.selector.length > 0) {
        for (var o in _filemanage.selector) {
            path.push(_filemanage.datajson.data[_filemanage.selector[o]].dpath);
        }
    }
    $.post(MOD_URL + '&op=mobile&do=ajax&operation=collect', {'paths': path, 'collect': collect}, function (data) {
        for (var o in data.msg) {
            if (data.msg[o] == 'success') {
                _filemanage.datajson.data[o].collect = 1;
            }
        }
        obj.closest('div.moredo').addClass('hide');
        obj.closest('div.moredo').next('div.background-none').hide();
        obj.closest('div.moredo').prevAll('.weui-footer-none').find('p').css({'color': '#666666'});
        _filemanage.cancel();
        $.toast('收藏成功');
    }, 'json')
})
//取消收藏
$(document).off('tap.cancel-collectmenu').on('tap.cancel-collectmenu', '.cancel-collectmenu', function () {
    var path = [], collect = 0, obj = $(this);
    if (_filemanage.selector.length > 0) {
        for (var o in _filemanage.selector) {
            path.push(_filemanage.datajson.data[_filemanage.selector[o]].dpath);
        }
    }
    $.post(MOD_URL + '&op=mobile&do=ajax&operation=collect', {'paths': path, 'collect': collect}, function (data) {
        for (var o in data.msg) {
            if (data.msg[o] == 'success') {
                _filemanage.datajson.data[o].collect = 0;
            }
        }
        obj.closest('div.moredo').addClass('hide');
        obj.closest('div.moredo').next('div.background-none').hide();
        obj.closest('div.moredo').prevAll('.weui-footer-none').find('p').css({'color': '#666666'});
        _filemanage.cancel();
        $.toast('取消收藏成功');
    }, 'json')
})
//分享
$(document).off('tap.sharemenu').on('tap.sharemenu', '.sharemenu', function () {
    if ($('#submitForm').length < 1) {
        var form = $('<form id="submitForm"></form>');
        $(document.body).append(form);
    } else {
        form = $('#submitForm');
    }
    var action = MOD_URL + '&op=mobile&do=ajax&operation=share';
    if (_filemanage.selector.length > 0) {
        var rids = _filemanage.selector.join(',');
        if ($('#ridinput').length < 1) {
            var rinput = $('<input type="hidden" name="rid" id="ridinput"/>');
            form.append(rinput);
        } else {
            var rinput = $('#ridinput');
        }
        rinput.val(rids);
    } else {
        return false;
    }
    form.attr('action', action);
    form.attr('method', 'post');
    form.submit();
})
//评论
$(document).off('tap.commentmenu').on('tap.commentmenu', '.commentmenu', function () {
    if ($('#submitForm').length < 1) {
        var form = $('<form id="submitForm"></form>');
        $(document.body).append(form);
    } else {
        form = $('#submitForm');
    }
    var action = MOD_URL + '&op=mobile&do=comment';
    if (_filemanage.fid) {
        if ($('#fidinput').length < 1) {
            var finput = $('<input type="hidden" name="fid" id="fidinput" />');
            form.append(finput);
        } else {
            var finput = $('#fidinput');
        }
        finput.val(_filemanage.fid);

    }
    if (_filemanage.selector.length > 0) {
        var rid = _filemanage.selector[0];
        if ($('#ridinput').length < 1) {
            var rinput = $('<input type="hidden" name="rid" id="ridinput"/>');
            form.append(rinput);
        } else {
            var rinput = $('#ridinput');
        }
        rinput.val(rid);
    }
    form.attr('action', action);
    form.attr('method', 'post');
    form.submit();
})
//打开
_filemanage.Open = function (rid, extid, title) {
    var data = _filemanage.datajson.data[rid];
    var name = data.name;
    var obj = {};
    obj.type = data.type;
    obj.ext = data.ext;
    obj.id = rid;
    obj.text = name;
    obj.dpath = data.dpath;
    if (obj.type === 'link') {
        //window.open(data.url);
		
		if(_filemanage.ios){
			 window.location.href=data.url;
		 }else{
			 window.open(data.url);
		 }
        return;
    } else if (obj.type === 'dzzdoc') {
        obj.url = "index.php?mod=document&icoid=" + obj.id;
		if(_filemanage.ios){
			window.location.href=obj.url;
		}else{
			 window.open(obj.url);
		}
        
        return;
    }
    if (_filemanage.is_wxwork && obj.type != 'image') {
        window.location.href=DZZSCRIPT+'?mod=io&op=download&path=' + data.dpath;
        return false;
    }
    if (obj.type == 'image') {
        var currentimg = data.imgpath;
        $.getScript('static/jquery_weui/js/swiper.min.js', function () {
            var imglists = [];
            for (var o in _filemanage.datajson.data) {
                if (_filemanage.datajson.data[o].type == 'image') {
                    var imgurl =
                        imglists.push(_filemanage.datajson.data[o]['imgpath']);
                }
            }
            var index = $.inArray(currentimg, imglists);
            var pb = jQuery.photoBrowser({
                items: imglists,
                initIndex: [index],
            });
            pb.open(index);
        });
        return;
    }
    if (!extid) {
        extid = getExtOpen(data, true);
    }
    if (extid) {
        if (_filemanage.extopen.all[extid].appid > 0 && _filemanage.app[_filemanage.extopen.all[extid].appid]['available'] < 1) {
            Alert(__lang.regret_app + _filemanage.app[_filemanage.extopen.all[extid].appid]['appname'] + __lang.already_close, 5, null, null, 'info');
            return;
        }
        var extdata_url = extopen_replace(data, extid);
        if (extdata_url) {
            extdata_url = extdata_url.replace(/{\w+}/g, '');
            if (extdata_url.indexOf('dzzjs:OpenPicWin') === 0) {
                var currentimg = data.imgpath;
                $.getScript('static/jquery_weui/js/swiper.min.js', function () {
                    var imglists = [];
                    for (var o in _filemanage.datajson.data) {
                        if (_filemanage.datajson.data[o].type == 'image') {
                            var imgurl =
                                imglists.push(_filemanage.datajson.data[o]['imgpath']);
                        }
                    }
                    var index = $.inArray(currentimg, imglists);
                    var pb = jQuery.photoBrowser({
                        items: imglists,
                        initIndex: [index],
                    });
                    pb.open(index);
                });
                return;
            } else if (extdata_url.indexOf('dzzjs:') === 0) {

                eval((extdata_url.replace('dzzjs:', '')));
                return;
            } else {
               if(_filemanage.ios){
					 window.location.href=extdata_url;
				 }else{
					 window.open(extdata_url);
				 }
            }
        }
    } else {
        $.toast('文件没有可以打开的应用');
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
    if (ext && _filemanage.extopen.ext[ext]) {
        if (isdefault && _filemanage.extopen.all[_filemanage.extopen.user[ext]]) {
            return _filemanage.extopen.user[ext];
        }
        for (i = 0; i < _filemanage.extopen.ext[ext].length; i++) {
            if (_filemanage.extopen.all[_filemanage.extopen.ext[ext][i]]) {
                if (isdefault && _filemanage.extopen.all[_filemanage.extopen.ext[ext][i]].isdefault > 0) {
                    return _filemanage.extopen.all[_filemanage.extopen.ext[ext][i]].extid;
                }
                openarr.push(_filemanage.extopen.all[_filemanage.extopen.ext[ext][i]]);
            }
        }
    }
    if (data.ext && _filemanage.extopen.ext[data.ext]) {
        if (isdefault && _filemanage.extopen.all[_filemanage.extopen.user[data.ext]]) {
            return _filemanage.extopen.user[data.ext];
        }
        for (i = 0; i < _filemanage.extopen.ext[data.ext].length; i++) {
            if (_filemanage.extopen.all[_filemanage.extopen.ext[data.ext][i]]) {
                if (isdefault && _filemanage.extopen.all[_filemanage.extopen.ext[data.ext][i]].isdefault > 0) {
                    return _filemanage.extopen.all[_filemanage.extopen.ext[data.ext][i]].extid;
                }
                openarr.push(_filemanage.extopen.all[_filemanage.extopen.ext[data.ext][i]]);
            }
        }
    }
//判断type
    if (data.type !== data.ext && _filemanage.extopen.ext[data.type]) {
        if (isdefault && _filemanage.extopen.all[_filemanage.extopen.user[data.type]]) {
            return _filemanage.extopen.user[data.type];
        }
        for (i = 0; i < _filemanage.extopen.ext[data.type].length; i++) {
            if (_filemanage.extopen.all[_filemanage.extopen.ext[data.type][i]]) {
                if (isdefault && _filemanage.extopen.all[_filemanage.extopen.ext[data.type][i]].isdefault > 0) {
                    return _filemanage.extopen.all[_filemanage.extopen.ext[data.type][i]].extid;
                }
                openarr.push(_filemanage.extopen.all[_filemanage.extopen.ext[data.type][i]]);
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
    var extdata = _filemanage.extopen.all[extid];
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

//下载
$(document).on('tap.download').on('tap.download', '.downloadmenu', function () {
    if (_filemanage.selector.length == 1) {
        var data = _filemanage.datajson.data[_filemanage.selector[0]];
        if (!data) {
            $.toast('没有可下载文件!');
            return false;
        }
        var url = DZZSCRIPT + '?mod=io&op=download&path=' + encodeURIComponent(data.dpath) + '&t=' + new Date().getTime();

    } else if (_filemanage.selector.length > 1) {
        var dpaths = [];
        for (var i = 0; i < _filemanage.selector.length; i++) {
            var ico = _filemanage.datajson.data[_filemanage.selector[i]];
            if (ico.type === 'folder' || ico.type === 'document' || ico.type === 'image' || ico.type === 'attach') {
                dpaths.push(ico.dpath);
            }
        }
        if (dpaths.length > 0) {
            var path = encodeURIComponent(dpaths.join(','));
            var url =  DZZSCRIPT + '?mod=io&op=download&path=' + path + '&t=' + new Date().getTime();
        } else {
            $.toast('没有可下载文件!');
            return false;
        }
    }
    window.location.href=url;
})
//重命名
jQuery(document).off('tap.rename').on('tap.rename', '.renamemenu', function () {
    var obj = $(this), ico = _filemanage.datajson.data[_filemanage.selector[0]], oldtext = ico.name;
    $.prompt({
        title: '重命名',
        input: oldtext,
        empty: false, // 是否允许为空
        onOK: function (input) {
            var text = $('#weui-prompt-input').val(), emptypreg = /^\s*$/,emojpatt = /[\ud800-\udbff][\udc00-\udfff]/gi;;
            //不允许为空
            if(emojpatt.test(text)){
                $.toast('文件名不合法!','cancel');
                $('#weui-prompt-input').val('');
                return false;
            }
            if (emptypreg.test(text)) {
                $.toast('文件名不合法!','cancel');
                $('#weui-prompt-input').val('');
                return false;
            }
            if(text == oldtext){
            	$('#weui-prompt-input').val('');
            	obj.closest('div.moredo').addClass('hide');
                obj.closest('div.moredo').siblings('.background-none').hide();
                obj.closest('div.moredo').siblings('.weui-footer-sort').find('p').css('color','#666');
                _filemanage.cancel();
            	 return false;
            }
            $.post(MOD_URL + '&op=dzzcp&do=rename', {
                "text": text,
                "path": ico.dpath,
                "t": (new Date().getTime())
            }, function (data) {
                if (data['error']) {
                    $.toast(data['error']);
                } else {
                    $('#' + _filemanage.contains).find('h4[rid=' + _filemanage.selector[0] + ']').text(data.name);
                    _filemanage.datajson.data[_filemanage.selector[0]].name = data.name;
                    obj.closest('div.moredo').addClass('hide');
                    obj.closest('div.moredo').siblings('.background-none').hide();
                    obj.closest('div.moredo').siblings('.weui-footer-sort').find('p').css('color','#666');
                    _filemanage.cancel();
                    $.toast("操作成功");
                }
            }, 'json');
        },
        onCancel: function () {
            obj.closest('div.moredo').addClass('hide');
            obj.closest('div.moredo').siblings('.background-none').hide();
            obj.closest('div.moredo').siblings('.weui-footer-sort').find('p').css('color','#666');
            _filemanage.cancel();
        }
    });

})
