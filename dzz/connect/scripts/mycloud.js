/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

function Open(icoid) {
	parent.OpenFolderWin(icoid);
}

function Delete(id, bz) {
	jQuery.getJSON(ajaxurl + '&do=delete&bz=' + bz + '&id=' + id, function(json) {
		if(json.msg == 'success') {
			jQuery('#item_' + bz + '_' + id).remove();
			for(var i in json.icoids) {
				parent._ico.removeIcoid(json.icoids[i]);
			}
		} else showmessage(json.error, 'danger', 3000, 1);
	});
}

function Todesktop(id, bz) {
	parent._ico.ShortCut('icoid_' + id);
	return
}

function Rename(id, bz) {
	var el = jQuery('#item_' + bz + '_' + id);
	var pos = el.offset();
	jQuery('#renameContainer').css({ left: pos.left + 2, top: pos.top + 107 });
	jQuery('#rename').attr('bz', bz).attr('cid', id).val(el.find('h5 a').html()).focus();

}
jQuery(document).ready(function(e) {
	jQuery('#rename').blur(function(e) {
		var cid = jQuery(this).attr('cid');
		var bz = jQuery(this).attr('bz');
		var val = jQuery(this).val();
		if(val != '' && val != jQuery('#item_' + bz + '_' + cid + ' h5 a').html()) {
			jQuery.getJSON(ajaxurl + '&do=rename', { name: val, bz: bz, id: cid }, function(json) {
				if(json.error) {
					showmessage(json.error, 'danger', 3000, 1);
					jQuery('#renameContainer').css({ left: '-500px', top: '-500px' });
				} else {
					jQuery('#item_' + bz + '_' + cid + ' h5 a').html(val);
					jQuery('#renameContainer').css({ left: '-500px', top: '-500px' });
				}
			});
		} else {
			jQuery('#renameContainer').css({ left: '-500px', top: '-500px' });
		}
	});
	jQuery('.thumbnails li').mouseenter(function() {
		jQuery(this).addClass('hover');
	}).mouseleave(function() {
		jQuery(this).removeClass('hover');
	}).click(function() {
		jQuery('.thumbnails li').not(this).removeClass('Selected');
		jQuery(this).toggleClass('Selected');
		var cid = jQuery(this).attr('cid');
		var icoid = jQuery(this).attr('icoid');
		var bz = jQuery(this).attr('bz');
		var html = '';
		if(jQuery(this).hasClass('Selected')) {
			html += '<li><a href="javascript:;" onclick="Open(\'' + icoid + '\');return false">'+__lang.open+'</a></li>';
			if(bz != 'dzz') html += '<li><a href="javascript:;" onclick="Delete(\'' + cid + '\',\'' + bz + '\');return false">'+__lang.delete+'</a></li>';
			html += '<li><a href="javascript:;" onclick="Rename(\'' + cid + '\',\'' + bz + '\');return false">'+__lang.rechristen+'</a></li>';
			html += '<li><a href="javascript:;" onclick="Todesktop(\'' + icoid + '\',\'' + bz + '\');return false">'+__lang.apptodesktop+'</a></li>';
		}
		jQuery('#menu').html(html);
		return false;
	}).dblclick(function() {
		var icoid = jQuery(this).attr('icoid');
		parent.OpenFolderWin(icoid);
	}).find('.thumbnail img,h5 a').click(function() {
		var icoid = jQuery(this).attr('icoid');
		parent.OpenFolderWin(icoid);
		return false
	});
	jQuery(document).click(function(e) {
		e = e ? e : window.event;
		var tag = e.srcElement ? e.srcElement : e.target;
		if(tag.tagName == "A" || tag.type == "text" || tag.type == "textarea") {
			return true;
		}
		jQuery('.thumbnails li').removeClass('Selected');
		jQuery('#menu').html('');
	});
	jQuery('.thumbnails li').on('contextmenu', function(e, cid, bz, icoid) {
		e = e ? e : window.event;
		var x = e.clientX;
		var y = e.clientY;
		var cid = jQuery(this).attr('cid');
		var icoid = jQuery(this).attr('icoid');
		var bz = jQuery(this).attr('bz');
		//设置当前容器的相关菜单选项的图标
		var html = document.getElementById('right_ico').innerHTML;
		html = html.replace(/XX/g, x);
		html = html.replace(/YY/g, y);
		html = html.replace(/-cid-/g, cid);
		html = html.replace(/-icoid-/g, icoid);
		html = html.replace(/-bz-/g, bz);
		if(!document.getElementById('right_contextmenu')) {
			var el = jQuery('<div id="right_contextmenu" class="menu"></div>').appendTo(document.body);
		} else {
			var el = jQuery(document.getElementById('right_contextmenu'));
		}

		el.html(html);
		if(bz == 'dzz') jQuery('#right_contextmenu').find('.delete').remove()

		el.css({ 'z-index': 201 });
		el.show();

		var Max_x = document.documentElement.clientWidth;
		var Max_y = document.documentElement.clientHeight;
		el.find('>div').each(function() {
			var item = jQuery(this);

			item.on('mouseover', function() {
				jQuery(this).addClass('menu-active');
				return false;
			});
			item.on('mouseout', function() {
				jQuery(this).removeClass('menu-active');
			});

		});

		if(x + el.width() > Max_x) x = x - el.width();
		if(y + el.height() > Max_y) y = y - el.height();
		el.css({ left: x, top: y });
		jQuery('#shadow').css({ display: "block", zIndex: 200, left: x, top: y, width: el.outerWidth(), height: el.outerHeight() });

		jQuery(document).bind('mousedown.right_contextmenu', function(e) {
			//var obj = event.srcElement ? event.srcElement : event.target;
			e = e ? e : window.event;
			var obj = e.srcElement ? e.srcElement : e.target;
			if(checkInDom(obj, 'right_contextmenu') == false) {
				el.hide();
				jQuery('#shadow').hide();
				jQuery(document).off('.right_contextmenu');

			}
		});
		return false;
	});
});
/*document.oncontextmenu=function(e){
	e=e?e:window.event;
	var tag = e.srcElement ? e.srcElement :e.target;
	if(tag.type=="text"||tag.type=="textarea"){
		return true;
	}else{
		return false;
	}
}*/