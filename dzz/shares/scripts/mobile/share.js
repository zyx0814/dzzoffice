//开始长按
/*
 $(document).bind('contextmenu',function(){
 var e=event;
 e.preventDefault();
 })*/
var selectors = [];
//打开文件
$(document).off('tap.openfile').on('tap.openfile', '.filelist', function () {
    var obj = $(this), dpath = obj.data('dpath');
    if (selectors.length > 0) {
        select_file(obj);
        return false;
    } else {
        if (is_wxwork) {
            var href = obj.data('href');
            window.location.href = href;
            return false;
        } else {
            var dpath = obj.data('dpath');
            var preurl = 'share.php?a=view&s=' + dpath;
            window.open(preurl);
        }
    }


})
//打开文件夹
$(document).off('tap.openfolder').on('tap.openfolder', '.folderlist', function () {
    var obj = $(this), dhpath = obj.data('dhpath'), dpath = obj.data('dpath');
    if (selectors.length > 0) {
        select_file(obj);
        return false;
    } else {
        $.post(MOD_URL + '&op=ajax', {path: dhpath, currentfolder: 0}, function (data) {
            $('#dataContainer').html(data);
            $('#filelist').html($('#dataContainer').find('.module-list').html());
            $($('#dataContainer').find('.breadcrumb-data:first').html()).insertAfter($('.breadcrumb li').last());
            $('.breadcrumb li:not(:last)').each(function () {
                $(this).removeClass('active');
                $(this).html('<a href="javascript:;">' + $(this).html() + '</a>');
            });
            $('#weui_address_scroll').navbarscroll();
            $('#dataContainer').empty();
        })
    }
})
//选择文件
function select_file(obj) {
    var dpath = obj.data('dpath'), index = $.inArray(dpath, selectors);
    if (obj.find('.weui-cells_checkbox .weui-check').prop('checked')) {
        obj.find('.weui-cells_checkbox').addClass('hide');
        obj.find('.weui-cell__ft').removeClass('hide');
        obj.find('.weui-cells_checkbox .weui-check').prop('checked', false)
        if (index != -1) {
            selectors.splice(index, 1);
        }
    } else {
        obj.find('.weui-cells_checkbox').removeClass('hide');
        obj.find('.weui-cell__ft').addClass('hide');
        obj.find('.weui-cells_checkbox .weui-check').prop('checked', true)
        if (index == -1) {
            selectors.push(dpath);
        }
    }
    chk_select();
}
//选择文件
$(document).off('longTap.select').on('longTap.select', '.weui-cell_longpress', function () {
    var obj = $(this);
    select_file(obj);
})
function chk_select() {
    if (selectors.length > 0) {
        $('.weui-cells_checkbox').removeClass('hide');
        $('.weui-cell__ft').addClass('hide');
    } else {
        $('.weui-cells_checkbox').addClass('hide');
        $('.weui-cell__ft').removeClass('hide');
    }
    return false;
}
$(document).off('tap.route').on('tap.route', '.route', function () {
    var obj = $(this);
    var href = obj.data('href');
    $.post(href, {currentfolder: 0}, function (data) {
        $('#dataContainer').html(data);
        $('#filelist').html($('#dataContainer').find('.module-list').html());
        obj.nextAll('li').remove();
        $('.breadcrumb li:not(:last)').each(function () {
            $(this).removeClass('active');
        });
        $('#weui_address_scroll').navbarscroll();
        $('#dataContainer').empty();
    })
})
//加载更多
//单页滚动加载
var loading = false;  //状态标记
$(document.body).infinite().on("infinite", function () {
    if (loading) return;
    loading = true;
	var nextpage=$('#nextpage');
    if (nextpage.length) {
        $.post(DZZSCRIPT + '?mod=shares&op=ajax', {'morepath': nextpage.data('morepath'), 'page': nextpage.data('nextpage')}, function (data) {
			loading = false;
            $('#dataContainer').html(data);
            $('#filelist #nextpage').replaceWith($('#dataContainer').find('.module-list').html());
            $('#dataContainer').empty();
        });
    } else {
		loading = false;
    }
});
function nextPageLoad(){
	if (loading) return;
    loading = true;
	var nextpage=$('#nextpage');
    if (nextpage.length) {
        $.post(DZZSCRIPT + '?mod=shares&op=ajax', {'morepath': nextpage.data('morepath'), 'page': nextpage.data('nextpage')}, function (data) {
			loading = false;
            $('#dataContainer').html(data);
            $('#filelist #nextpage').replaceWith($('#dataContainer').find('.module-list').html());
            $('#dataContainer').empty();
        });
    } else {
		loading = false;
    }
}
//保存文件
$(document).off('tap.savefile').on('tap.savefile', '.savefiles', function () {
    var action = 'index.php?mod=system&op=mobilefileselection&type=2&handlekey=seldir&allowcreate=1',
        rids = [], callback_url = encodeURI(MOD_URL + '&op=save'), token = {};
    if (selectors.length > 0) {
        rids = selectors.join(',');
    } else {
        $('.weui-cell_longpress').each(function () {
            var rid = $(this).data('dpath');
            rids.push(rid);
        })
        rids = rids.join(',');
    }
    token = {"paths": rids};
    if ($('#submitForm').length < 1) {
        var form = $('<form id="submitForm"></form>');
        $(document.body).append(form);
    } else {
        form = $('#submitForm');
    }
    if ($('#tokendata').length < 1) {
        var tokendata = $('<input type="hidden" name="token" id="tokendata" />');
        form.append(tokendata);
    } else {
        var tokendata = $('#tokendata');
    }

    tokendata.val(JSON.stringify(token));
    if ($('#callbackdata').length < 1) {
        var callbackdata = $('<input type="hidden" name="callback_url" id="callbackdata" />');
        form.append(callbackdata);
    } else {
        var callbackdata = $('#callbackdata');
    }
    callbackdata.val(callback_url);
    form.attr('action', action);
    form.attr('method', 'post');
    form.submit();
    return false;

})
//下载文件
$(document).off('tap.down').on('tap.down', '.downfile', function () {
    var href = DZZSCRIPT + '?mod=io&op=download&checkperm=false',
        rids = [];
    if (selectors.length > 0) {
        rids = selectors.join(',');
    } else {
        $('.weui-cell_longpress').each(function () {
            var rid = $(this).data('dpath');
            rids.push(rid);
        })
        rids = rids.join(',');
    }
    href = href + '&path=' + rids;
    downfile(href);
})

