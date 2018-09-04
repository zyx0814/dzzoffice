/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
_contextmenu = {}//定义一个空对象
_contextmenu.zIndex = 9999999;//设置堆叠顺序
_contextmenu.right_ico = function (e, rid) {
    e = e ? e : window.event;
    var obj = e.srcElement ? e.srcElement : e.target;
    if (/input|textarea/i.test(obj.tagName)) {
        return true;
    }
    var x = e.clientX;
    var y = e.clientY;
    var obj = _explorer.sourcedata.icos[rid];
    if (!document.getElementById('right_contextmenu')) {
        var el = jQuery('<div id="right_contextmenu" class="menu"></div>').appendTo(document.body);
    } else {
        var el = jQuery(document.getElementById('right_contextmenu'));
    }
    //如果是系统桌面，且用户不是管理员则不出现右键菜单
    rid = rid + '';
    var html = document.getElementById('right_ico').innerHTML;
    html = html.replace(/\{XX\}/g, x);
    html = html.replace(/\{YY\}/g, y);
    html = html.replace(/\{rid\}/g, rid);
    if (_selectfile.selectall.icos.length == 1 && obj.type == 'folder') {//单选中目录时，粘贴到此目录内部
        html = html.replace(/\{fid\}/g, obj.fid);
    } else {
        html = html.replace(/\{fid\}/g, obj.pfid);
    }
    el.html(html);
    if (obj.type == 'shortcut' || obj.type == 'storage' || obj.type == 'pan' || _explorer.myuid < 1) {
        el.find('.shortcut').remove();
    }
    //如果是选择位置则只保留新建文件夹菜单
    if(_explorer.type == 2){
        el.find('.download,.cut,.copy,.delete').remove();
    }
    //判断copy
    if (!_explorer.Permission('copy', obj)) {
        el.find('.copy').remove();
    }
    //判断粘贴
    if (!_explorer.Permission('upload', obj) || _explorer.cut.icos.length < 1 || _selectfile.fid < 1) {
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
    if (_selectfile.selectall.icos.length > 1 && jQuery.inArray(rid, _selectfile.selectall.icos) > -1) {
        if(obj.isdelete == 1){
            el.find('.menu-item:not(.recover,.finallydelete)').remove();
        }else{
            el.find('.menu-item:not(.delete,.cut,.copy,.restore,.downpackage,.property,.collect,.paste,.share,.cancleshare)').remove();
        }
        var pd = 1;
        for (var i = 0; i < _selectfile.selectall.icos.length; i++) {
            var ico = _explorer.sourcedata.icos[_selectfile.selectall.icos[i]];
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
        if (collects == _selectfile.selectall.icos.length) {//区别是已收藏时，菜单显示取消收藏
            el.find('.collect .menu-text').html('取消收藏');
        }
    } else {
        if (obj.collect) el.find('.collect .menu-text').html('取消收藏');
        el.find('.downpackage').remove();
    }


    if (obj.isdelete == 1) {
        el.find('.menu-item:not(.recover,.finallydelete)').remove();
    } else {
        el.find('.finallydelete').remove();
        el.find('.recover').remove();
    }
    if(_selectfile.winid.indexOf('collect') != -1){
        el.find('.cut').remove();
        el.find('.copy').remove();
        el.find('.paste').remove();
    }
    //分享处理
    if(_selectfile.winid.indexOf('share') != -1){
        el.find('.menu-item:not(.cancleshare,.editshare)').remove();
    }else{
        el.find('.cancleshare,.editshare').remove();
    }
    //如果在收藏,搜索和最近使用页面去掉删去和剪切和重命名
    if(_selectfile.winid.indexOf('collect') != -1 || _selectfile.winid.indexOf('recent') != -1 || _selectfile.winid.indexOf('search') != -1){
        el.find('.cut,.delete,.rename').remove();
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
        var html = '<span class="menu-icon icon-openwith" ></span><span class="menu-text">' + __lang.method_open + '</span><span class="menu-rightarrow"></span>';

        html += '<div class=" menu " style="display:none">';
        for (var i = 0; i < subdata.length; i++) {
            html += '<div class="menu-item" onClick="_selectfile.Open(\'' + rid + '\',\'' + subdata[i].extid + '\');jQuery(\'#right_contextmenu\').hide();jQuery(\'#shadow\').hide();return false;">';
            if (subdata[i].icon) {
                html += '<span class="menu-icon" style="background:none"><img width="100%" height="100%" src=' + subdata[i].icon + '></span>';
            }
            html += '<span class="menu-text">' + subdata[i].name + '</span>';
            html += '</div>';
        }
        html += '</div>';
        el.find('.openwith').html(html);
    } else {
        el.find('.openwith').remove();
    }


    //去除多余的分割线
    el.find('.menu-sep').each(function () {
        if (!jQuery(this).next().first().hasClass('menu-item') || !jQuery(this).prev().first().hasClass('menu-item')) jQuery(this).remove();
    });

    var Max_x = document.documentElement.clientWidth;
    var Max_y = document.documentElement.clientHeight;
    el.css({'z-index': _contextmenu.zIndex + 1});
    el.show();

    el.find('>div').each(function () {
        var item = jQuery(this);
        var subitem = item.find('.menu');
        if (subitem.length) {
            var shadow = item.find('.menu-shadow');
            item.on('mouseover', function () {
                if (_contextmenu.ppp) _contextmenu.ppp.hide();
                if (_contextmenu.kkk) _contextmenu.kkk.hide();
                if (_contextmenu.last) _contextmenu.last.removeClass('menu-active');
                _contextmenu.kkk = shadow;
                _contextmenu.last = item;
                _contextmenu.ppp = subitem;
                item.addClass('menu-active');
                var temp = item.find('.menu');
                var subx = el.width() - 1;
                suby = 0;
                if (x + el.width() * 2 > Max_x) subx = subx - temp.width() - el.width() - 6;
                if (y + item.position().top + temp.height() > Max_y) suby = suby - temp.height() + item.height() - 5;
                temp.css({left: subx, top: suby, 'z-index': _contextmenu.zIndex + 2, display: 'block'});
                shadow.css({
                    display: "block",
                    zIndex: _contextmenu.zIndex + 1,
                    left: subx,
                    top: suby,
                    width: temp.outerWidth(),
                    height: temp.outerHeight()
                });
                subitem.find('.menu-item').on('mouseover', function () {
                    jQuery(this).addClass('menu-active');
                });
                subitem.find('.menu-item').on('mouseout', function () {
                    jQuery(this).removeClass('menu-active');
                    return false;
                });

                return false;
            });
            item.on('mouseout', function () {
                item.removeClass('menu-active');
                shadow.hide();
                subitem.hide();//alert('dddddd');
                return false;
            });

        } else {
            item.on('mouseover', function () {
                if (_contextmenu.last) _contextmenu.last.removeClass('menu-active');
                if (_contextmenu.ppp) _contextmenu.ppp.hide();
                if (_contextmenu.kkk) _contextmenu.kkk.hide();
                jQuery(this).addClass('menu-active');
                return false;
            });
            item.on('mouseout', function () {
                jQuery(this).removeClass('menu-active');
            });
        }
    });
    //alert(el.width()+'===='+el.height());
    if (x + el.width() > Max_x) x = x - el.width();
    if (y + el.height() > Max_y) y = y - el.height();
    el.css({left: x, top: y});

    jQuery(document).on('mousedown.right_contextmenu', function (e) {
        e = e ? e : window.event;
        var obj = e.srcElement ? e.srcElement : e.target;
        if (jQuery(obj).closest('#right_contextmenu').length < 1) {
            el.hide();
            el.empty();
            jQuery(document).off('.right_contextmenu');
            _contextmenu.kkk = null;
            _contextmenu.ppp = null;
            _contextmenu.last = null;
        }
    });
};
_contextmenu.right_body = function (e, fid) {
    e = e ? e : window.event;
    var obj = e.srcElement ? e.srcElement : e.target;
    if (/input|textarea/i.test(obj.tagName)) {
        return true;
    }
    var x = e.clientX;
    var y = e.clientY;
    var html = document.getElementById('right_body').innerHTML;
    html = html.replace(/\{fid\}/g, fid);
    html = html.replace(/\{filemanageid\}/g, _selectfile.winid);
    if (!document.getElementById('right_contextmenu')) {
        var el = jQuery('<div id="right_contextmenu" class="menu"></div>').appendTo(document.body);
    } else {
        var el = jQuery(document.getElementById('right_contextmenu'));
    }
    el.html(html);
    var filemanage = _selectfile.cons[_selectfile.winid];
    //设置当前容器的相关菜单选项的图标
    el.find('span.menu-icon-iconview[view=' + filemanage.view + ']').removeClass('dzz-check-box-outline-blank').addClass('dzz-check-box');
    //el.find('span.menu-icon-disp[disp='+filemanage.disp+']').removeClass('dzz-check-box-outline-blank').addClass('dzz-check-box');
    //设置排序
    el.find('.menu-icon-disp').each(function () {
        if (jQuery(this).attr('disp') == filemanage.disp) {
            jQuery(this).removeClass('dzz-check-box-outline-blank').addClass('dzz-check-box');
            jQuery(this).next().find('.caret').removeClass('asc').removeClass('desc').addClass(filemanage.asc > 0 ? 'asc' : 'desc');
        } else {
            jQuery(this).addClass('dzz-check-box-outline-blank').removeClass('dzz-check-box');
            jQuery(this).next().find('.caret').removeClass('asc').removeClass('desc');
        }
    });
    if (!fid) {
        el.find('.property').remove();
        el.find('.paste').remove();
       if(_selectfile.winid != 'recycle-list'){
           el.find('.recoverall').remove();
           el.find('.deleteall').remove();
       }else{
           el.find('.sort .disp2').remove();
           el.find('.sort .disp3').remove();
       }
    }else{
        el.find('.recoverall').remove();
        el.find('.deleteall').remove();
    }
    if (!_explorer.Permission_Container('folder', fid)) {
        el.find('.newfolder').remove();
    }
    if (!_explorer.Permission_Container('link', fid)) {
        el.find('.newlink').remove();
    }
    if (!_explorer.Permission_Container('dzzdoc', fid)) {
        el.find('.newdzzdoc').remove();
    }
    if (!_explorer.Permission_Container('upload', fid)) {
        el.find('.newdzzdoc').remove();
    }
    if (!_explorer.Permission_Container('newtype', fid)) {
        el.find('.newtext').remove();
        el.find('.newdoc').remove();
        el.find('.newexcel').remove();
        el.find('.newpowerpoint').remove();
    }
    if (el.find('.create .menu-item').length < 1) {
        el.find('.create').remove();
    }
    if (_explorer.cut.icos.length < 1) el.find('.paste').remove();

    if (_explorer.Permission_Container('upload', fid)) {

        if (BROWSER.ie) {
            jQuery('<input id="right_uploadfile_' + fid + '" name="files[]" tabIndex="-1" style="position: absolute;outline:none; filter: alpha(opacity=0); PADDING-BOTTOM: 0px; margin: 0px; padding-left: 0px; padding-right: 0px; font-family: Arial; font-size: 180px;height:30px;width:200px;top: 0px; cursor: pointer; right: 0px; padding-top: 0px; opacity: 0;direction:ltr;background-image:none" type="file" multiple="multiple" >').appendTo(el.find('.upload'));
            fileupload(jQuery('#right_uploadfile_' + fid));

            jQuery('<input id="right_uploadfolder_' + fid + '" name="files[]" tabIndex="-1" style="position: absolute;outline:none; filter: alpha(opacity=0); PADDING-BOTTOM: 0px; margin: 0px; padding-left: 0px; padding-right: 0px; font-family: Arial; font-size: 180px;height:30px;width:200px;top: 0px; cursor: pointer; right: 0px; padding-top: 0px; opacity: 0;direction:ltr;background-image:none" type="file" multiple="multiple" >').appendTo(el.find('.uploadfolder'));
            fileupload(jQuery('#right_uploadfolder_' + fid));

        } else {
            console.log(el.find('.upload'));
            el.find('.upload').get(0).onclick = function () {
                jQuery('.js-upload-file input').trigger('click');
                console.log(jQuery('.js-upload-file input'));
                console.log('aaaa');
                el.hide();
            }
            el.find('.uploadfolder').get(0).onclick = function () {
                jQuery('.js-upload-folder input').trigger('click');
                el.hide();
            }
        }
    } else {
        el.find('.upload').remove();
        el.find('.uploadfolder').remove();
    }
    //设置默认桌面

    //检测新建和上传是否都没有
    if (el.find('.create .menu>.menu-item').length < 1) {
        el.find('.create').remove();
    }
    if(_selectfile.winid == 'share-list'){
        el.find('.menu-item').remove();
    }
    if (el.find('.menu-item').length < 1) {
        el.hide();
        return;
    }
    el.find('.menu-sep').each(function () {
        if (!jQuery(this).next().first().hasClass('menu-item') || !jQuery(this).prev().first().hasClass('menu-item')) jQuery(this).remove();
    });

    var Max_x = document.documentElement.clientWidth;
    var Max_y = document.documentElement.clientHeight;
    el.css({'z-index': _contextmenu.zIndex + 1});
    el.show();

    el.find('>div').each(function () {
        var item = jQuery(this);
        var subitem = item.find('.menu');
        if (subitem.length) {
            var shadow = item.find('.menu-shadow');
            item.on('mouseover', function () {
                if (_contextmenu.ppp) _contextmenu.ppp.hide();
                if (_contextmenu.kkk) _contextmenu.kkk.hide();
                if (_contextmenu.last) _contextmenu.last.removeClass('menu-active');
                _contextmenu.kkk = shadow;
                _contextmenu.last = item;
                _contextmenu.ppp = subitem;
                item.addClass('menu-active');
                var temp = item.find('.menu');
                var subx = el.width() - 1;
                suby = -5;
                if (x + el.width() * 2 > Max_x) subx = subx - temp.width() - el.width() - 6;
                if (y + item.position().top + temp.height() > Max_y) suby = suby - temp.height() + item.height();
                console.log(temp);
                temp.css({left: subx, top: suby, 'z-index': _contextmenu.zIndex + 2, display: 'block'});
                shadow.css({
                    display: "block",
                    zIndex: _contextmenu.zIndex + 1,
                    left: subx,
                    top: suby,
                    width: temp.outerWidth(),
                    height: temp.outerHeight()
                });
                subitem.find('.menu-item').on('mouseover', function () {
                    jQuery(this).addClass('menu-active');

                });
                subitem.find('.menu-item').on('mouseout', function () {
                    jQuery(this).removeClass('menu-active');
                    return false;

                });

                return false;
            });
            item.on('mouseout', function () {
                item.removeClass('menu-active');
                shadow.hide();
                subitem.hide();//alert('dddddd');
                return false;
            });

        } else {
            item.on('mouseover', function () {
                if (_contextmenu.last) _contextmenu.last.removeClass('menu-active');
                if (_contextmenu.ppp) _contextmenu.ppp.hide();
                if (_contextmenu.kkk) _contextmenu.kkk.hide();
                jQuery(this).addClass('menu-active');
                return false;
            });
            item.on('mouseout', function () {
                jQuery(this).removeClass('menu-active');
            });
        }
    });
    if (x + el.width() > Max_x) x = x - el.width();
    if (y + el.height() > Max_y) y = y - el.height();
    if (y < 0) y = 0;
    el.css({left: x, top: y});

    jQuery('#shadow').css({
        display: "block",
        zIndex: _contextmenu.zIndex,
        left: x,
        top: y,
        width: el.outerWidth(),
        height: el.outerHeight()
    });

    jQuery(document).on('mousedown.right_contextmenu', function (e) {
        //var obj = event.srcElement ? event.srcElement : event.target;
        e = e ? e : window.event;
        var obj = e.srcElement ? e.srcElement : e.target;
        if (jQuery(obj).closest('#right_contextmenu').length < 1) {
            el.hide();
            jQuery('#shadow').hide();
            jQuery(document).off('.right_contextmenu');
            _contextmenu.kkk = null;
            _contextmenu.ppp = null;
            _contextmenu.last = null;
        }
    });
};

