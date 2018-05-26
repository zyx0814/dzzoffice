var _upload = {};
_upload.total = 0;
_upload.completed = 0;
_upload.ismin = 1;
_upload.tips = $('#upload_file_tips');
_upload.el = $('#uploading_file_list');
_upload.filelist = $('.fileList');
_upload.fid = null;
var attachextensions = (_explorer.space.attachextensions.indexOf('|') != -1) ? _explorer.space.attachextensions.join('|') : _explorer.space.attachextensions;
if (attachextensions == '') attachextensions = "\.*$";
else attachextensions = "(\.|\/)(" + (attachextensions) + ")$";
function fileupload(el,fid) {
	el.off();
    el.fileupload({
        url: _explorer.appUrl+'&do=ajax&operation=uploads&container=' + fid,
        dataType: 'json',
        autoUpload: true,
        maxChunkSize: parseInt(_explorer.space.maxChunkSize), //2M
        dropZone: el.attr('id')=='wangpan-upload-folder'?null:$('#middleconMenu'),
        pasteZone: el.attr('id')=='wangpan-upload-folder'?null:$('#middleconMenu'),
        maxFileSize: parseInt(_explorer.space.maxattachsize) > 0 ? parseInt(_explorer.space.maxattachsize) : null, // 5 MB
        acceptFileTypes: new RegExp(attachextensions, 'i'),
        sequentialUploads: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<li class="dialog-file-list"></li>').appendTo(_upload.el);
        $.each(data.files, function (index, file) {
            $(getItemTpl(file)).appendTo(data.context);
            uploadadd();
            _uploadheight();
        });
    }).on('fileuploadsubmit', function (e, data) {
        data.context.find('.upload-cancel').off('click').on('click', function () {
            data.abort();
            data.files = '';
            uploaddone();
            $(this).parents('.dialog-info').find('.upload-cancel').hide();
            $(this).parents('.dialog-info').find('.upload-file-status').html('<span class="cancel show_uploading_status"><em></em><i>' + __lang.already_cancel + '</i></span>');
        });

        uploadsubmit();
        $.each(data.files, function (index, file) {
            file.relativePath = (file.relativePath)?file.relativePath+file.name:'';
            var relativePath = (file.webkitRelativePath ? file.webkitRelativePath :file.relativePath);
            data.formData = {relativePath: relativePath};
            return;
        });

    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index];
        if (file.error) {
            uploaddone();
            data.context.find('.upload-item.percent').html('<span class="danger" title="' + file.error + '">' + file.error + '</span>');
        }
    }).on('fileuploadprogress', function (e, data) {
        var index = data.index;
        _upload.bitrate = formatSize(data.bitrate / 8);
        var progre = parseInt(data.loaded / data.total * 100, 10);
        data.context.find('.process').css('width',progre + '%');
        data.context.find('.upload-file-status .speed').html(_upload.bitrate + '/s');
        data.context.find('.upload-file-status .precent').html(progre + '%');
    }).on('fileuploadprogressall', function (e, data) {
        _upload.bitrate = formatSize(data.bitrate / 8);
        var progre = parseInt(data.loaded / data.total * 100, 10);
        uploadprogress(_upload.bitrate + '/s', progre + '%');
        _upload.el.find('.panel-heading .upload-progress-mask').css('width', progre + '%');
    }).on('fileuploaddone', function (e, data) {
        uploaddone();
 //       data.context.find('.upload-progress-mask').css('width',progre + '%');
 //     data.context.find('.upload-cancel').hide();
        data.context.find('.upload-progress-mask').css('width', '0%');
      data.context.find('.upload-cancel').hide();
     var process_bar=data.context.find('.process').css('width', '100%');
 		if(process_bar){
 			data.context.find('.process').css('background-color','#fff');
 		}
        $.each(data.result.files, function (index, file) {
            if (file.error) {
                var relativePath = (file.relativePath ? file.relativePath : '');
                data.context.find('.dialog-info .upload-file-status').html('<span class="danger" title="' + file.error + '">' + file.error + '</span>');
            } else {
                
                _upload.tips.find('.dialog-body-text').html(_upload.completed + '/' + _upload.total);
                data.context.find('.upload-file-status .speed').html('');
                data.context.find('.upload-file-operate').html('完成');
					if(file.data.folderarr){
						for(var i=0;i<file.data.folderarr.length;i++){
							_explorer.sourcedata.folder[file.data.folderarr[i].fid]=file.data.folderarr[i];
						}
						var inst=jQuery('#position').jstree(true);
						inst.refresh_node(inst.get_selected());
					}
					if(file.data.icoarr){
						for(var i=0;i<file.data.icoarr.length;i++){
							if(file.data.icoarr[i].pfid==_selectfile.cons['f-'+fid].fid){
								 _explorer.sourcedata.icos[file.data.icoarr[i].rid]=file.data.icoarr[i];
								 _selectfile.cons['f-'+fid].CreateIcos(file.data.icoarr[i]);
							}
						}
					}
            }
        });

    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index, file) {
            uploaddone();
            data.context.find('.upload-item.percent').html('<span class="danger" title="' + file.error + '">' + file.error + '</span>');
        });

    }).on('fileuploaddrop', function (e, data) {
        var url = e.dataTransfer.getData("text/plain");
        if (url) {
            e.preventDefault();
            if (_explorer.Permission_Container('link', fid)) {
                $.getJSON(_explorer.appUrl + '&do=dzzcp&do=newlink&path=' + parseInt(fid) + '&handlekey=handle_add_newlink&link=' + encodeURIComponent(url), function (json) {
                    if (!json) Alert(__lang.js_network_error, 1);
                    else if (json.error) {
                        Alert(json.error, 3);
                    } else {
						_explorer.sourcedata.icos[json.rid]=json;
                        _selectfile.cons['f-'+fid].CreateIcos(json);
                    }
                });
                return false;
            }
        }
    }).on('fileuploaddragover', function (e) {
        e.dataTransfer.dropEffect = 'copy';
        e.preventDefault();
    });
}

function uploadadd() {
    _upload.total++;
    _upload.tips.show();
    _upload.tips.find('.dialog-body-text').html(_upload.completed + '/' + _upload.total);
}

function getItemTpl(file) {
    var relativePath = (file.webkitRelativePath ? file.webkitRelativePath : (file.relativePath ? file.relativePath:file.name));
    var filearr = file.name.split('.');
    var ext = filearr.pop();
    var imgicon = '<img src="dzz/images/extimg/'+ext+'.png" onerror="replace_img(this)" style="width:24px;height:24px;position:absolute;left:0;"/>';
    var html =
        '<div class="process" style="position:absolute;z-index:-1;height:100%;background-color:#e8f5e9;-webkit-transition:width 0.6s ease;-o-transition:width 0.6s ease;transition:width 0.6s ease;width:0%;"></div> <div class="dialog-info"> <div class="upload-file-name">' +
        '<div class="dialog-file-icon" align="center">'+imgicon+'</div> <span class="name-text">' + file.name + '</span> ' +
        '</div> <div class="upload-file-size">' + (file.size ? formatSize(file.size) : '') + '</div> <div class="upload-file-path">' +
        '<a title="" class="" href="javascript:;">' + relativePath + '</a> </div> <div class="upload-file-status"> <span class="uploading"><em class="precent"></em><em class="speed">排队中</em>' +
        '</span> <span class="success"><em></em><i></i></span> </div> <div class="upload-file-operate"> ' +
        '<em class="operate-pause"></em> <em class="operate-continue"></em> <em class="operate-retry"></em> <em class="operate-remove"></em> ' +
        '<a class="error-link upload-cancel" href="javascript:void(0);">取消</a> </div> </div>';
    return html;
}
function replace_img(obj){
    jQuery(obj).attr('src','dzz/images/default/icodefault.png');
}

function formatSize(bytes) {
    var i = -1;
    do {
        bytes = bytes / 1024;
        i++;
    } while (bytes > 99);

    return Math.max(bytes, 0).toFixed(1) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
};

function uploadsubmit() {
    _upload.el.find('.upload-sum-title').show().html(_upload.completed + '/' + _upload.total);
}
function uploaddone() {
    _upload.completed++;
    if (_upload.completed > _upload.total) {
        _upload.el.find('.upload-sum-title').html(__lang.upload_finish + ':');
        _upload.el.find('.panel').addClass('ismin');
        _upload.ismin = 1;
    } else {
        _upload.el.find('.upload-sum-completed').show().html(_upload.completed + '/' + _upload.total);
    }
}

function uploadprogress(speed, per) {
    _upload.el.find('.upload-speed').show().find('.upload-speed-value').html(speed);
    if (_upload.speedTimer) window.clearTimeout(_upload.speedTimer);
    _upload.speedTimer = window.setTimeout(function () {
        _upload.el.find('.upload-speed').hide();
    }, 2000);
}
//文件上传成功
$(document).on('click', '.dialog-close', function () {//事件委托
    $(this).parent('.dialog-tips').hide();
});
$(document).on('click', '.dialog-header-close', function () {
    $(this).parents('.docunment-dialog').hide();
    $('#uploading_file_list').html('');
});
function _uploadheight() {
    var winHeight = $('#uploading_file_list').height();
    if (winHeight > 441) {
        _upload.el.animate({scrollTop: winHeight});//滚动条跟着滚动
        _upload.el.css({'overflow-y': 'auto', 'overflow-x': 'hidden', 'max-height': '460px'});
    }
};
$(document).off('click.icon').on('click.icon', '.dialog-header-narrow', function () {
    if ($(this).hasClass('dzz-dzz-min')) {
        $(this).removeClass('dzz-dzz-min').addClass('dzz-dzz-max');
        $(this).parents('.docunment-dialog').css({'max-height': '146px', 'animation': '15s'});
        return false;
    } else {
        $(this).removeClass('dzz-dzz-max').addClass('dzz-dzz-min');
        $(this).parents('.docunment-dialog').css({'max-height': '600px', 'animation': '15s'});
    }
});
