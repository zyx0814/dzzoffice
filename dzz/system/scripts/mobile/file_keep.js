var _filemanage = {};
_filemanage = function (json) {
    _filemanage.formhash = json.formhash || ''; //FORMHASH
    _filemanage.type = json.type || 0;//文件选择类型，0为选择文件,1为保存文件,2为选择位置
    _filemanage.mulitype = json.mulitype || 0;//是否允许多选，默认不允许
    _filemanage.callback_url = json.callback_url;
    _filemanage.token = json.token;
    _filemanage.defaultfilename = json.filename;
    if (_filemanage.type == 2) {
        _filemanage.allowselecttype = {'folder': ['文件夹', ['folder'], 'selected']}
    } else {
        _filemanage.allowselecttype = json.exttype || '';//允许筛选文件类型
    }
    //默认筛选文件类型
    if (_filemanage.allowselecttype) {
        for (var o in _filemanage.allowselecttype) {
            if (_filemanage.allowselecttype[o][2] == 'selected') {
                _filemanage.defaultexttype = _filemanage.allowselecttype[o][1].join(',').toLowerCase();
            }
        }
    } else {
        _filemanage.defaultexttype = '';
    }
    _filemanage.defaultfilename = json.defaultfilename || '';
};
_filemanage.hash = '';
_filemanage.getConfig = function (json, callback) {
    new _filemanage(json);
    _filemanage.hashHandler();
    _filemanage.initEvents();
    if (typeof callback === "function") {
        callback(json);
    }
    //});
};
_filemanage.selector = [];//选择文件rid
_filemanage.dataparam = {};//请求文件条件参数
_filemanage.initEvents = function () { //初始化页面事件
    _filemanage.height();
    //hashchange事件
    $(window).on('hashchange', function () {
        _filemanage.hashHandler();
    });
    $(document).off('click.document-data').on('click.document-data', '.document-data', function () {
        location.hash = jQuery(this).data('hash');
    });
    //右侧加载完成事件
    $(document).off('ajaxLoad.middleContent').on('ajaxLoad.middleContent', function () {
        var hash = location.hash.replace(/^#/i, '');
        var op = hash.replace(/&(.+?)$/ig, '');
        if (hash.indexOf('search') == 0) {
            $('#footermenu').html('');
        } else if (_filemanage.fid) {
            if(_filemanage.dataparam.createFolderPerm){
                $('#footer_menu').find('.filelistmenu .new-folder').removeClass('hide');
            }else{
                $('#footer_menu').find('.filelistmenu .new-folder').addClass('hide');
            }
            $('#footermenu').html($('#footer_menu').find('.filelistmenu').html());
        } else {
            $('#footermenu').html($('#footer_menu').find('.formatmenu').html());
        }
        //设置类型
        if (_filemanage.allowselecttype) {
            var typejson = _filemanage.allowselecttype, typehtml = '', selecttype = '', selectval = '';
            for (var o in typejson) {
                var exts = typejson[o][1].join(',').toLowerCase();
                if (typejson[o][2] == 'selected') {
                    selecttype = typejson[o][0];
                    selectval = exts;
                    typehtml += '<label class="weui-cell weui-check__label"><div class="weui-cell__bd"> <p class="weui-type-word">' + typejson[o][0] + '</p> </div> ' +
                        '<div class="weui-cell__ft"> <input type="radio" class="weui-check" name="radio1" checked="checked" value="' + exts + '">' +
                        ' <span class="weui-icon-checked"></span> </div> </label>';
                } else {
                    typehtml += '<label class="weui-cell weui-check__label"><div class="weui-cell__bd"> <p class="weui-type-word">' + typejson[o][0] + '</p> </div> ' +
                        '<div class="weui-cell__ft"> <input type="radio" class="weui-check" name="radio1"  value="' + exts + '">' +
                        ' <span class="weui-icon-checked"></span> </div> </label>';
                }


            }
            $('#footermenu .typeext').html(typehtml);
        }
        //加载更多
        if (_filemanage.dataparam.page) {

            //单页滚动加载
            var loading = false;  //状态标记
            $(document.body).infinite().on("infinite", function () {
                if (loading) return;
                loading = true;
                if (_filemanage.dataparam.page) {
                    $.post(_filemanage.appUrl + '&do=' + _filemanage.hash, _filemanage.dataparam, function (data) {
                        $('#containsdata').html(data);
                        $('#middleconMenu .filelist').append($('#containsdata').find('.weui-cells__margin_footer').html());
                        $('#containsdata').empty();
                        if (!_filemanage.dataparam.page) {
                            loading = false;
                        } else {
                            loading = true;
                        }
                    })

                } else {
                    jQuery(document.body).destroyInfinite();
                }
            });
        }
    });

};
_filemanage.height = function () {
    var h = $(document).outerHeight(true);
    var h1 = $('.weui-file-keep').outerHeight(true);
    var h2 = $('.weui-file-footer').outerHeight(true);
    $('#middleconMenu').css('height', h - h1 - h2);
}
_filemanage.hashHandler = function () { //处理页面hash变化
    var hash = location.hash;
    hash = hash.replace(/^#/i, '');
    if (!hash) {
        hash = 'home';
    }
    if (hash === _filemanage.hash) {
        return false;
    }
    if (hash !== _filemanage.hash) {
        _filemanage.getContent(hash, $('#middleconMenu'));
        _filemanage.hash = hash;
    } else {
        _filemanage.hash = hash;
    }
    return false;
};
_filemanage.getContent = function (hash, container) { //处理页面加载
    var url = _filemanage.appUrl + '&do=' + hash;
    if(url.indexOf('?') == -1){
        url = url.replace('&','?');
    }
    _filemanage.dataparam = {};
    if (_filemanage.defaultexttype) {
        _filemanage.dataparam.exts = _filemanage.defaultexttype;
    }
    $.post(url, _filemanage.dataparam, function (data) {
        $('#containsdata').html(data);
        $('#middleconMenu').html($('#containsdata').find('.datacontent').html());
        if ($('#containsdata').find('.addresscontent').length) {
            $('#addressdata').html($('#containsdata').find('.addresscontent').html());
            $('#addressdata').removeClass('hide');
        }else if( _filemanage.hash.indexOf('home') == 0){
            $('#addressdata').html('');
          	$('#addressdata').removeClass('hide');
        }
        if(_filemanage.hash.indexOf('search') == 0){
        	$('.weui-file-keep').addClass('hide');
        	$('#addressdata').addClass('hide');
        }else{
        	$('.weui-file-keep').removeClass('hide');
        }
        $('#containsdata').empty();
        $(document).trigger('ajaxLoad.middleContent', [hash]);
    })

};
//打开文件夹
$(document).off('tap.openhref').on('tap.openhref', '.document-data', function () {
    var hash = $(this).data('hash');
    location.hash = hash;
})
//排序
$(document).off('tap.sort').on('tap.sort', '.sort_menu .sort', function () {
    var sort = $(this).data('sort');
    if (_filemanage.dataparam.disp == sort) _filemanage.dataparam.asc = (_filemanage.dataparam.asc > 0) ? 0 : 1;
    else _filemanage.dataparam.disp = sort;
    _filemanage.dataparam.page = 1;
    _filemanage.dataparam.datatotal = 0;
    if (_filemanage.defaultexttype) {
        _filemanage.dataparam.exts = _filemanage.defaultexttype;
    }
    $.post(_filemanage.appUrl + '&do=' + _filemanage.hash, _filemanage.dataparam, function (data) {
        $('#containsdata').html(data);
        $('#middleconMenu').html($('#containsdata').find('.datacontent').html());
        if ($('#containsdata').find('.addresscontent').length) {
            $('#addressdata').html($('#containsdata').find('.addresscontent').html());
            $('#addressdata').removeClass('hide');
        }
        $('#containsdata').empty();
        $('.sort_menu').addClass('hide');
    })
})
//类型筛选
$(document).off('tap.exts').on('tap.exts', '.weui-check__label', function () {
    var obj = $(this);
    var exts = obj.find('.weui-cell__ft input').val();
    _filemanage.dataparam.exts = exts;
    _filemanage.dataparam.page = 1;
    _filemanage.dataparam.datatotal = 0;
    $.post(_filemanage.appUrl + '&do=' + _filemanage.hash, _filemanage.dataparam, function (data) {
        $('#containsdata').html(data);
        $('#middleconMenu').html($('#containsdata').find('.datacontent').html());
        if ($('#containsdata').find('.addresscontent').length) {
            $('#addressdata').html($('#containsdata').find('.addresscontent').html());
            $('#addressdata').removeClass('hide');
        }
        $('#containsdata').empty();
        obj.closest('.typeext').addClass('hide');
    })

})
//搜索文件
$(document).off('tap.search').on('tap.search', '.search', function () {
    var hash = 'search', oldhash = _filemanage.hash;
    oldhash = oldhash.replace(/&/g, '-');
    if (_filemanage.fid) {
        hash += '&fid=' + _filemanage.fid + '&oldhash=' + oldhash;
    }
    location.hash = hash;
})

//文件选择
$(document).off('tap.selectFile').on('tap.selectFile', '.document-filelist', function () {
    var obj = $(this), rid = obj.data('rid'), index = $.inArray(rid, _filemanage.selector);
    if (_filemanage.mulitype) {
        if (obj.find('.weui-cells_checkbox .weui-check').prop('checked')) {
            obj.find('.weui-cells_checkbox').addClass('hide');
            obj.find('.weui-cells_checkbox .weui-check').prop('checked', false);
            if (index != -1) {
                _filemanage.selector.splice(index, 1);
            }
        } else {
            obj.find('.weui-cells_checkbox').removeClass('hide');
            obj.find('.weui-cells_checkbox .weui-check').prop('checked', true);
            if (index == -1) {
                _filemanage.selector.push(rid);
            }
        }
    } else {
        if (obj.find('.weui-cells_checkbox .weui-check').prop('checked')) {
            $('.weui-cells_checkbox').each(function () {
                $(this).find('.weui-check').prop('checked', false);
                $(this).addClass('hide');
            })
            if (index != -1) {
                _filemanage.selector.splice(index, 1);
            }
        } else {
            $('.weui-cells_checkbox').each(function () {
                $(this).find('.weui-check').prop('checked', false);
                $(this).addClass('hide');
            })
            obj.find('.weui-cells_checkbox').removeClass('hide');
            obj.find('.weui-cells_checkbox .weui-check').prop('checked', true);
            if (index == -1) {
                _filemanage.selector.push(rid);
            }
        }

    }


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
            if (emojpatt.test(foldername)) {
                $.toast('文件名不合法!', "cancel");
                $('#weui-prompt-input').val('');
                return false;
            }
            $.post(_filemanage.appUrl + '&do=ajax&operation=createFolder', {
                'foldername': foldername,
                'fid': fid,
            }, function (data) {
                if (data['error']) {
                    $.toast(data['error'], 1000);
                } else {
                    var html = '<div class="weui-cell weui-cell_access weui-cell_longpress document-data" data-hash="#file&fid=' + data.fid + '"> ' +
                        '<div class="weui-cell__hd"> ' +
                        '<img src="' + data.img + '" class="weui-cell__recentimg"> ' +
                        '</div> <div class="weui-cell__bd"> <h4>' + data.name + '</h4><p> ' +
                        '<span class="file">文件:</span><i class="file-number">0,</i> ' +
                        '<span class="folder">文件夹:</span><i class="folder-number">0</i> </p> ' +
                        '</div> <div class="weui-cell__ft"></div> </div>';
                    $('.filelist').prepend(html);
                    $.toast("操作成功");
                }
            }, 'json');

        },
        onCancel: function () {
            $('#weui-prompt-input').val('');
            obj.closest('div.weui-dropup').addClass('hide');
            obj.closest('div.weui-dropup').siblings('.background-none').hide();
            obj.closest('div.weui-dropup').siblings('.weui-footer-new-folder').find('p').css('color', '#666');
        }
    });
});
//弹出框点击其他地方消失
jQuery(document).off('tap.confirm').on('tap.confirm', '.background-none', function () {
    $(this).prev('.weui-dropup').addClass('hide');
    $(this).prevAll('.weui-footer-none').find('p').css({'color': '#666666'});
    $(this).hide();
})
//排序菜单
jQuery(document).off('tap.array').on('tap.array', '.weui-footer-sort', function () {
    var dropup = $(this).next('.weui-dropup');
    if (dropup.hasClass('hide')) {
        dropup.removeClass('hide');
        dropup.next('.background-none').show();
        $(this).find('p').css({'color': '#3779ff'});
    }
})