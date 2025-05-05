/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */
_hotkey={};
_hotkey.ctrl=0;
_hotkey.alt=0;
_hotkey.shift=0;
_hotkey.init=function(){
	_hotkey.ctrl=0;
	_hotkey.alt=0;
	_hotkey.shift=0;
}
jQuery(document).on('keydown',function(event){
	event=event?event:window.event;
	var tag = event.srcElement ? event.srcElement :event.target;
	if(/input|textarea/i.test(tag.tagName)){
		return true;
	}
	var e;
	if (event.which !="") { e = event.which; }
	else if (event.charCode != "") { e = event.charCode; }
	else if (event.keyCode != "") { e = event.keyCode; }
	switch(e){
		case 17:
			_hotkey.ctrl=1;
			break;
		case 18:
			_hotkey.alt=1;
			break;
		case 16:
			_hotkey.shift=1;
			break;
	}
 });
jQuery(document).on('keyup',function(event){
	event=event?event:window.event;
	var tag = event.srcElement ? event.srcElement :event.target;
	if(/input|textarea/i.test(tag.tagName)){
		return true;
	}
	var e;
	if (event.which !="") { e = event.which; }
	else if (event.charCode != "") { e = event.charCode; }
	else if (event.keyCode != "") { e = event.keyCode; }
	switch(e){
				
		case 17:
			_hotkey.ctrl=0;
			break;
		case 18:
			_hotkey.alt=0;
			break;
		case 16:
			_hotkey.shift=0;
			break;
		case 46:case 110: //delete
			try{
				if(_explorer.selectall.icos.length>0){
					_filemanage.delIco(_config.selectall.icos[0]);
				}
			}catch(e){}
			break;
		
		case 69://Ctrl + Alt + E
			try{
				if(_hotkey.alt && _hotkey.ctrl) _header.loging_close();
			}catch(e){}
			break;
	}

 });
var _explorer = {};
_explorer = function (json) {
	_explorer.space = json.space; //用户信息
	_explorer.formhash = json.formhash; //FORMHASH
	_explorer.extopen = json.extopen || {}; //打开方式信息
	_explorer.sourcedata = json.sourcedata || []; //所有文件信息
	_explorer.thame = json.thame || {};
};
_explorer.appUrl = MOD_URL;
_explorer.fid = '';
_explorer.fids = '';
_explorer.getConfig = function (url, callback) {
	$.getJSON(url + '&t=' + new Date().getTime(), function (json) {
		new _explorer(json);
		_explorer.initEvents();
		if (typeof callback === "function") {
			callback(json);
		}
	});
};
_explorer.initEvents = function () { //初始化页面事件
	_explorer.getRightContent('','');
	//右侧加载完成事件
	$(document).off('ajaxLoad.middleContent').on('ajaxLoad.middleContent', function () {
		_explorer.Scroll($('.scroll-y'));
		_explorer.setHeight($('.height-100'));
		if ($('.scroll-100').length) {
			_explorer.scroll_100 = new PerfectScrollbar('.scroll-100');
		}
	});
};

_explorer.loading = function (container, flag) { //右侧加载效果
	if (flag === 'hide') {
		container.find('.rightLoading').remove();
	} else {
		container.append('<div class="rightLoading"></div>');
	}
};
_explorer.getRightContent = function (fid,dos) { //处理右侧页面加载
	if(fid && _explorer.fid == fid) {
		return false;
	}
	_explorer.fid = fid;
	var container = $('#middleconMenu');
	_explorer.loading(container);
	_explorer.rightLoading = 1;
	var view = $('.icons-thumbnail').attr('iconview') || '2';
	_filemanage.getData(_explorer.appUrl+'&op=file&do='+dos+'&view='+view+'&sid='+sid+'&fid=f-'+fid);
	jQuery('.listchange').show();
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
function dfire(e) {
	jQuery(document).trigger(e);
}
function passwordsubmit() {
	var passwords = jQuery('.passwords').val();
	$.post(MOD_URL+'&op=file', {'sid':sid,'passwordsubmit':true,'password':passwords},function (json) {
		if (json['success']) {
			window.location.reload();
		} else if (json['error']) {
			showmessage(json['error'], 'danger', 5000, 1);
			return false;
		} else {
			showmessage('系统异常', 'danger', 5000, 1);
			return false;
		}
	}, 'json');
}
function allsave() {
	dzzApi.fileselect('',function(fid) {
		var rids = [];
		if (_filemanage.selectall.icos.length > 0) {
			for (var i = 0; i < _filemanage.selectall.icos.length; i++) {
				var ico = _explorer.sourcedata.icos[_filemanage.selectall.icos[i]];
				rids.push(ico.dpath);
			}
		} else {
			for (var key in _explorer.sourcedata.icos) {
				if (_explorer.sourcedata.icos.hasOwnProperty(key)) {
					rids.push(_explorer.sourcedata.icos[key].dpath);
				}
			}
		}
		var rids = rids.join(',');
		$.post(MOD_URL+'&op=save', {'fid': fid,'sid':sid, 'dzzrids': rids}, function (data) {
			if (data.error) {
				showmessage(data.error, 'danger', 5000, 1);
				return false;
			}
			if (data.success) {
				showmessage(data.success, 'success', 3000, 1);
			}
		}, 'json');
	})
}
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
        var el = jQuery('<div id="right_contextmenu" class="menu dropdown-menu"></div>').appendTo(document.body);
    } else {
        var el = jQuery(document.getElementById('right_contextmenu'));
    }
    //如果是系统桌面，且用户不是管理员则不出现右键菜单
    rid = rid + '';
    var html = document.getElementById('right_ico').innerHTML;
    html = html.replace(/\{XX\}/g, x);
    html = html.replace(/\{YY\}/g, y);
    html = html.replace(/\{rid\}/g, rid);
    if (_filemanage.selectall.icos.length == 1 && obj.type == 'folder') {//单选中目录时，粘贴到此目录内部
        html = html.replace(/\{fid\}/g, obj.fid);
    } else {
        html = html.replace(/\{fid\}/g, obj.pfid);
    }
    el.html(html);
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
        if(obj.isdelete == 1){
            el.find('.menu-item:not(.recover,.finallydelete)').remove();
        }else{
            el.find('.menu-item:not(.cut,.copy,.restore,.allsave,.downpackage,.property)').remove();
        }
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


    el.find('.recover').remove();
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
        var html = '<span class="menu-icon" ></span><span class="menu-text">' + __lang.method_open + '</span><span class="menu-rightarrow"></span>';

        html += '<div class=" menu dropdown-menu " style="display:none">';
        for (var i = 0; i < subdata.length; i++) {
            html += '<div class="menu-item dropdown-item" onClick="_filemanage.Open(\'' + rid + '\',\'' + subdata[i].extid + '\');jQuery(\'#right_contextmenu\').hide();jQuery(\'#shadow\').hide();return false;">';
            if (subdata[i].icon) {
                html += '<span class="menu-icon"><img width="100%" height="100%" src=' + subdata[i].icon + '></span>';
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

    //el.addClass(_window.className);
	var Max_x = document.documentElement.clientWidth;
    var Max_y = document.documentElement.clientHeight;
    el.css({'z-index': _contextmenu.zIndex + 1,'max-height':Max_y});
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
                if (x + el.width() * 2 > Max_x) subx = subx - temp.width() - el.width() - 3;
                if (y + item.position().top + temp.height() > Max_y) suby = suby - temp.height() + item.height() - 5;
                temp.css({left: subx-0.01, top: suby, 'z-index': _contextmenu.zIndex + 2, display: 'block'});
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
	if (y < 0) y = 0;
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
    html = html.replace(/\{filemanageid\}/g, _filemanage.winid);
    if (!document.getElementById('right_contextmenu')) {
        var el = jQuery('<div id="right_contextmenu" class="menu dropdown-menu"></div>').appendTo(document.body);
    } else {
        var el = jQuery(document.getElementById('right_contextmenu'));
    }
    el.html(html);
    var filemanage = _filemanage.cons[_filemanage.winid];
    //设置当前容器的相关菜单选项的图标
    el.find('span.menu-icon-iconview[view=' + filemanage.view + ']').removeClass('mdi-checkbox-blank-outline').addClass('mdi-checkbox-marked');
    //el.find('span.menu-icon-disp[disp='+filemanage.disp+']').removeClass('mdi-checkbox-blank-outline').addClass('mdi-checkbox-marked');
    //设置排序
    el.find('.menu-icon-disp').each(function () {
        if (jQuery(this).attr('disp') == filemanage.disp) {
            jQuery(this).removeClass('mdi-checkbox-blank-outline').addClass('mdi-checkbox-marked');
            jQuery(this).next().find('.caret').removeClass('asc').removeClass('desc').addClass(filemanage.asc > 0 ? 'asc' : 'desc');
        } else {
            jQuery(this).addClass('mdi-checkbox-blank-outline').removeClass('mdi-checkbox-marked');
            jQuery(this).next().find('.caret').removeClass('asc').removeClass('desc');
        }
    });
    if (!fid) {
        el.find('.property').remove();
        el.find('.sort .disp2').remove();
        el.find('.sort .disp3').remove();
    }
    var create = 0; // 默认值
    if (_filemanage && _filemanage.param && _filemanage.param.create) {
        create = _filemanage.param.create;
    }
    if (!create) {
        el.find('.create').remove();
    }
     
    if (create) {
        if (BROWSER.ie) {
            jQuery('<input id="right_uploadfile_' + fid + '" name="files[]" tabIndex="-1" style="position: absolute;outline:none; filter: alpha(opacity=0); PADDING-BOTTOM: 0px; margin: 0px; padding-left: 0px; padding-right: 0px; font-family: Arial; font-size: 180px;height:30px;width:200px;top: 0px; cursor: pointer; right: 0px; padding-top: 0px; opacity: 0;direction:ltr;background-image:none" type="file" multiple="multiple" >').appendTo(el.find('.upload'));
            fileupload(jQuery('#right_uploadfile_' + fid),fid);

            jQuery('<input id="right_uploadfolder_' + fid + '" name="files[]" tabIndex="-1" style="position: absolute;outline:none; filter: alpha(opacity=0); PADDING-BOTTOM: 0px; margin: 0px; padding-left: 0px; padding-right: 0px; font-family: Arial; font-size: 180px;height:30px;width:200px;top: 0px; cursor: pointer; right: 0px; padding-top: 0px; opacity: 0;direction:ltr;background-image:none" type="file" multiple="multiple" >').appendTo(el.find('.uploadfolder'));
            fileupload(jQuery('#right_uploadfolder_' + fid),fid);

        } else {
			if( el.find('.upload').length>0){
				el.find('.upload').get(0).onclick = function () {
					jQuery('.js-upload-file input').trigger('click');
					el.hide();
				}
			}
			if( el.find('.uploadfolder').length>0){
				el.find('.uploadfolder').get(0).onclick = function () {
					jQuery('.js-upload-folder input').trigger('click');
					el.hide();
				}
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
    if (el.find('.menu-item').length < 1) {
        el.hide();
        return;
    }
    el.find('.menu-sep').each(function () {
        if (!jQuery(this).next().first().hasClass('menu-item') || !jQuery(this).prev().first().hasClass('menu-item')) jQuery(this).remove();
    });

 	var Max_x = document.documentElement.clientWidth;
    var Max_y = document.documentElement.clientHeight;
    el.css({'z-index': _contextmenu.zIndex + 1,'max-height':Max_y});
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
                if (x + el.width() * 2 > Max_x) subx = subx - temp.width() - el.width() - 3;
                if (y + item.position().top + temp.height() > Max_y) suby = suby - temp.height() + item.height();
                temp.css({left: subx-0.01, top: suby, 'z-index': _contextmenu.zIndex + 2, display: 'block'});
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
function _select(container)
{
	this.id=this.name=container;
	this.string="_select.icos."+this.id;
	this.board=document.getElementById(container);
	_select.icos[this.id]=this;
};
_select.delay=500;
_select.width=120;
_select.height=120;
_select.icos={};

_select.onmousemove=null;
_select.onmouseup=null;
_select.tach=null;
_select.onselectstart=1;
_select.init=function(container){
	var obj= new _select(container);
	jQuery(obj.board).on('mousedown',function(e){
		
		e=e?e:window.event;
		var tag = e.srcElement ? e.srcElement :e.target;
		
		if(/input|textarea/i.test(tag.tagName)){
			return true;
		}	
		
		if(e.button==2) return true;
		dfire('mousedown');//dfire('touchstart');
		obj.Mousedown(e?e:window.event);
		return true;
	});
	jQuery(obj.board).on('mouseup',function(e){
		e=e?e:window.event;
		var tag = e.srcElement ? e.srcElement :e.target;
		if(/input|textarea/i.test(tag.tagName)){
			return true;
		}
		dfire('mouseup');	
		obj.Mouseup(e?e:window.event);
		
		return true;
	});
	return obj;
};
_select.prototype.DetachEvent=function(e)
{
	if(!_select.tach) return;
	document.onmousemove=_select.onmousemove;
	document.onmouseup=_select.onmouseup;
	document.onselectstart=_select.onselectstart;
	try{
		if(this.board.releaseCapture) this.board.releaseCapture();
	}catch(e){};
	_select.tach=0;
	_select.finishblank=0;
	
};
_select.prototype.AttachEvent=function(e)
{ 
	if(_select.tach) return
	_select.onmousemove=document.onmousemove;
	_select.onmouseup=document.onmouseup;
	_select.onselectstart=document.onselectstart;
	try{
		document.onselectstart=function(){return false;}
		if(e.preventDefault) e.preventDefault();
		else{
			if(this.board.setCapture) this.board.setCapture();
		}
	}catch(e){};
	_select.tach=1;
};
_select.prototype.Duplicate=function()
{
	this.copy=document.createElement('div');
	
	document.body.appendChild(this.copy);
	this.copy.style.cssText="position:absolute;left:0px;top:0px;width:0px;height:0px;filter:Alpha(opacity=50);opacity:0.5;z-index:10002;overflow:hidden;background:#000;border:1px solid #000;";
	//jQuery(this.copy).find('#text'+this.id).html(' ');
};
_select.prototype.Mousedown=function(e)
{
	this.mousedowndoing=false;
	if(e.type=='touchstart'){
		var XX=e.touches[0].clientX;
		var YY=e.touches[0].clientY;
	}else{
		var XX=e.clientX;
		var YY=e.clientY;
	}
	
	_select.oldxx=XX;
	_select.oldyy=YY;
	this.tl=XX;
	this.tt=YY;
	this.oldx=XX;
	this.oldy=YY;
	//alert('down');
	var self=this;
	if(!_select.tach) this.AttachEvent(e);
	
	if(e.type=='touchstart'){
		jQuery(this.board).on('touchmove',function(e){self.Move(e);return false});
	}else{
		document.onmousemove=function(e){self.Move(e?e:window.event);return false};
	}
	
	//this.mousedownTimer=setTimeout(function(){self.PreMove(XX,YY);},200);
};

_select.prototype.Mouseup=function(e)
{
	if(_select.tach) this.DetachEvent(e);
	if(!this.mousedowndoing) {
	}else this.Moved(e);
};
_select.prototype.PreMove=function(e)
{
	jQuery('#_blank').empty().show();
	if (this.move=="no") return;
	this.Duplicate();
	
	var self=this;
	this.mousedowndoing=true;
	var p=jQuery(this.board).offset();
	
	
	this.copy.style.left=this.tl+'px';
	this.copy.style.top=this.tt+'px';
	
	//清空数据
	if(_hotkey.ctrl>0 && _filemanage.selectall.container==this.id){
		
	}else{
		if(_filemanage.selectall.container) jQuery('#'+_filemanage.selectall.container).find('.Icoblock').removeClass('Icoselected');
		_filemanage.selectall.container=this.id;
		_filemanage.selectall.icos=[];
		_filemanage.selectall.position={};
	}
		//计算此容器内的所有ico的绝对位置，并且存入_filemanage.selectall.position中；
		jQuery(this.board).find('.Icoblock').each(function(){
			var el=jQuery(this);
			var p=el.offset();
			var icoid=el.attr('rid');
			if(icoid){
				_filemanage.selectall.position[icoid]={icoid:icoid,left:p.left,top:p.top,width:el.width(),height:el.height()};
			}
		});
	if(e.type=='touchmove'){
		jQuery(this.board).on('touchend',function(e){self.Moved(e);return true});
	}else{
		document.onmouseup=function(e){self.Moved(e?e:window.event);return false;};
	}
};
_select.prototype.Move=function(e)
{
	if(e.type==='touchmove'){
		var XX=e.touches[0].clientX;
		var YY=e.touches[0].clientY;
	}else{
		var XX=e.clientX;
		var YY=e.clientY;
	}
	if(!this.mousedowndoing && (Math.abs(this.oldx-XX)>5 || Math.abs(this.oldy-YY)>5)){
		this.PreMove(e);
	}
	if(!this.mousedowndoing) return;
	var flag=0;
	if(XX-this.oldx>0){
		this.copy.style.width=(XX-this.oldx)+"px";
	}else{
		this.copy.style.width=Math.abs(XX-this.oldx)+"px";
		this.copy.style.left=this.tl+(XX-this.oldx)+"px";
	}
	if(YY-this.oldy>0){
		this.copy.style.height=(YY-this.oldy)+"px";
	}else{
		this.copy.style.height=Math.abs(YY-this.oldy)+"px";
		this.copy.style.top=this.tt+(YY-this.oldy)+"px";
	}
	if(!BROWSER.ie){
		//if(Math.abs(_select.oldxx-XX)>20 || Math.abs(_select.oldyy-YY)>20){
			if(XX>this.oldx && YY > this.oldy){
				if(Math.abs(XX-_select.oldxx)>20 || Math.abs(YY-_select.oldyy)>20){
					 _select.oldxx=XX;
					 _select.oldyy=YY;
					 this.setSelected(true);
				}
			}else{
				if(Math.abs(XX-_select.oldxx)>20 || Math.abs(YY-_select.oldyy)>20){
					 _select.oldxx=XX;
					 _select.oldyy=YY;
					 this.setSelected();
				}
			}
	/*	}else{
			_select.oldxx=XX;
			_select.oldyy=YY;
		}*/
	}
};
_select.prototype.Moved=function(e)
{
	var self=this;
	jQuery('#_blank').hide();
	if(_select.tach)	this.DetachEvent(e);
	if(e.type=='touchend'){
		var XX=e.changedTouches[0].clientX;
		var YY=e.changedTouches[0].clientY;
	}else{
		var XX=e.clientX;
		var YY=e.clientY;
	}
	if(BROWSER.ie){
		if(XX>this.oldx && YY > this.oldy){
			this.setSelected(true);
		}else{
			this.setSelected();
		}
	}
	jQuery(this.copy).remove();
	
};
_select.prototype.setSelected=function(flag){
	_select.sum++;
	var p=jQuery(this.copy).offset();
	var icos=[];
	var copydata={left:p.left,top:p.top,width:jQuery(this.copy).width(),height:jQuery(this.copy).height()};
	for(var icoid in _filemanage.selectall.position){
		var data=_filemanage.selectall.position[icoid];
		if(_select.checkInArea(copydata,data,flag)){
			_select.SelectedStyle(this.id,icoid,true,true);
		}else if(_hotkey.ctrl<1){
			_select.SelectedStyle(this.id,icoid,false,true);
		}
	}
};
_select.checkInArea=function(copydata,data,flag){
	var rect={minx:0,miny:0,maxx:0,maxy:0}
	rect.minx=Math.max(data.left,copydata.left);
	rect.miny =Math.max(data.top,copydata.top) ;
	rect.maxx =Math.min(data.left+data.width,copydata.left+copydata.width) ;
	rect.maxy =Math.min(data.top+data.height,copydata.top+copydata.height) ;
	if(!flag){
		if(rect.minx>rect.maxx || rect.miny>rect.maxy){
			return false;
		}else{
			return true
		}
	}else{
		if(rect.minx>rect.maxx || rect.miny>rect.maxy){
			return false;
		}else{
			return true;
			var area=(rect.maxx-rect.minx)*(rect.maxy-rect.miny);
			var dataarea=data.width*data.height;
			if(dataarea==area) return true;
			else return false;
		}
	}
};
_select.SelectedStyle=function(container,rid,flag,multi){
	var icos=_filemanage.selectall.icos||[];
	var filemanageid=container.replace('filemanage-','');
	var el=jQuery('#'+container).find('.Icoblock[rid='+rid+']');
	if(flag){
		if(_filemanage.selectall.container=='') _filemanage.selectall.container=container;
		if(multi && _filemanage.selectall.container==container){
			if(jQuery.inArray(rid,_filemanage.selectall.icos)<0){
			 	_filemanage.selectall.icos.push(rid);
			}
		}else{
			jQuery('#'+_filemanage.selectall.container).find('.Icoblock').removeClass('Icoselected');
			_filemanage.selectall.container=container;
			_filemanage.selectall.icos=[rid];
			_filemanage.selectall.position={};
		}
		el.addClass('Icoselected');
		
	}else{
			/*el.each(function(){
				if(jQuery(this).hasClass('file-line')){
					var el1=jQuery(this);
					el1.off('.drag');
				}
			});*/
		var arr=[];
		if(_filemanage.selectall.container==container){
			for(var i in icos){
				if(icos[i]!=rid) arr.push(icos[i]);
			}
		}
		_filemanage.selectall.icos=arr;
		el.removeClass('Icoselected');	
	}

	if(_filemanage.cons[filemanageid]) _filemanage.cons[filemanageid].selectInfo();
};
_select.remove=function(icos){//移除原来的icoid
	for(var i=0;i<icos.length;i++){
		_ico.removeIcoid(icos[i]);
	}
}