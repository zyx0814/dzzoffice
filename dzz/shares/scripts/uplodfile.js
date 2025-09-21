var _upload = {};

_upload.total = 0;
_upload.completed = 0;
_upload.succeed = 0;//成功数量
_upload.errored = 0;//错误数量
_upload.el = $('#uploading_file_list');
_upload.filelist = $('.fileList');
_upload.fid = null;
_upload.maxli=10;//设置为0时，不缓存添加数据功能
_upload.datas=[];
var attachextensions = '';
var maxfileSize = null;
if (_explorer.space && _explorer.space.attachextensions) {
    attachextensions = (_explorer.space.attachextensions.indexOf('|') != -1) ? _explorer.space.attachextensions.join('|') : _explorer.space.attachextensions;
    attachextensions = "(\.|\/)(" + attachextensions + ")$";
} else {
    attachextensions = "\.*$";
}
if (_explorer.space && _explorer.space.maxattachsize) {
    maxfileSize =  parseInt(_explorer.space.maxattachsize) > 0 ? parseInt(_explorer.space.maxattachsize) : null;
}
function fileupload(el, fid) {
    if (_explorer.sourcedata.folder[1].bz) {
		fid = _explorer.sourcedata.folder[1].path;
	} else {
		fid = _explorer.sourcedata.folder[1].fid;
	}
    el.off();
    el.fileupload({
        url: MOD_URL + '&op=ajax&do=uploads&container=' + fid+'&sid='+sid,
        dataType: 'json',
        autoUpload: true,
        maxChunkSize: parseInt(_explorer.space.maxChunkSize), //2M
        dropZone: el.attr('id') == 'wangpan-upload-folder' ? null : $('#middleconMenu'),
        pasteZone: el.attr('id') == 'wangpan-upload-folder' ? null : $('#middleconMenu'),
        maxFileSize: maxfileSize, // 5 MB
        acceptFileTypes: new RegExp(attachextensions, 'i'),
        sequentialUploads: true
	}).on('fileuploadadd', function (e, data) {
		layerupload();
		if(_upload.maxli && _upload.datas.length>=_upload.maxli){
			_upload.datas.push(data);
			_upload.uploadadd();
		}else{
			data.context = $('<li class="dialog-file-list"></li>').appendTo($('.dialog-filelist-ul'));
			
			$.each(data.files, function (index, file) {
				$(_upload.getItemTpl(file)).appendTo(data.context);
				_upload.uploadadd();
			});
		}
    }).on('fileuploadsubmit', function (e, data) {
        data.context.find('.upload-cancel').off('click').on('click', function () {
            data.abort();
            data.files = '';
            _upload.uploaddone();
            $(this).parents('.dialog-info').find('.upload-cancel').hide();
            data.context.find('.process').css('display', 'none');
            $(this).parents('.dialog-info').find('.upload-file-status').html('<span class="cancel text-danger show_uploading_status"><em></em>'+__lang.already_cancel+'</span>');
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
            _upload.uploaddone('error');
            var err = file.error ? file.error  : __lang.upload_failure;
                data.context.find('.upload-file-status').html('<span class="text-danger" title="' + err + '">' + err + '</span>');
        }
    }).on('fileuploadprogress', function (e, data) {
        var index = data.index;
        _upload.bitrate = formatSize(data.bitrate / 8);
        var progre = parseInt(data.loaded / data.total * 100, 10);
        data.context.find('.process').css('width', progre + '%');
        data.context.find('.upload-file-status .speed').html(_upload.bitrate + '/s');
        data.context.find('.upload-file-status .precent').html(progre + '%');
    }).on('fileuploadprogressall', function (e, data) {
        _upload.bitrate = formatSize(data.bitrate / 8);
        var progre = parseInt(data.loaded / data.total * 100, 10);
        _upload.uploadprogress(_upload.bitrate + '/s', progre + '%');
		
    }).on('fileuploaddone', function (e, data) {
        data.context.find('.upload-progress-mask').css('width', '0%');
        data.context.find('.upload-cancel').hide();
        var process_bar = data.context.find('.process').css('width', '100%');
        if (process_bar) {
            data.context.find('.process').css('background-color', '');
        }
        $.each(data.result.files, function (index, file) {
            if (file.error) {
                var err = file.error ? file.error  : __lang.upload_failure;
                data.context.find('.dialog-info .upload-file-status').html('<span class="text-danger" title="' + err + '">' + err + '</span>');
				 _upload.uploaddone('error');
            } else {
				_upload.uploaddone();
                data.context.find('.upload-file-status .precent').html(__lang.update_finish);
                data.context.addClass('success').find('.upload-file-status .speed').html('');
                data.context.find('.upload-file-operate').html('');
                data.context.find('.process').css('display', 'none');
				
                if (file.data.folderarr) {
                    for (var i = 0; i < file.data.folderarr.length; i++) {
                        _explorer.sourcedata.folder[1] = file.data.folderarr[i];
                    }
                }
                if (file.data.icoarr) {
                    for (var i = 0; i < file.data.icoarr.length; i++) {
                        _explorer.sourcedata.icos[file.data.icoarr[i].rid] = file.data.icoarr[i];
                        _filemanage.cons['f-1'].CreateIcos(file.data.icoarr[i]);
                        if(file.data.icoarr[i].type != 'folder'){
                        }
                    }
                }
				if(_upload.maxli){
					/*window.setTimeout(function(){
						data.context.remove();
						
					},50);*/
					var d=_upload.datas.pop();
					if(d){
						d.context = $('<li class="dialog-file-list"></li>').appendTo($('.dialog-filelist-ul'));
						$.each(d.files, function (index, file) {
							$(_upload.getItemTpl(file)).appendTo(d.context);
							
						});
					}
				 }
            }


        });

    }).on('fileuploadfail', function (e, data) {
        var errorMsg = '上传失败';
        if (data.jqXHR && data.jqXHR.responseText) {
            try {
                var response = JSON.parse(data.jqXHR.responseText);
                if (response.files && response.files[0] && response.files[0].error) {
                    errorMsg = response.files[0].error;
                } else if (data.jqXHR.responseText) {
                    errorMsg = data.jqXHR.responseText;
                }
            } catch(e) {
                errorMsg = data.jqXHR.responseText || '上传失败';
            }
        }
        $.each(data.files, function (index, file) {
            if (file.error) {
                errorMsg = file.error;
            }
            data.context.find('.upload-file-status').html(
                '<span class="text-danger" title="' + errorMsg + '">' + errorMsg + '</span>'
            );
            _upload.uploaddone('error');
        });

    }).on('fileuploaddragover', function (e) {
        e.dataTransfer.dropEffect = 'copy';
        e.preventDefault();
    });
}

 _upload.uploadadd=function() {
    _upload.total++;
    
   $('#upload_header_status').html(__lang.upload_processing);
   $('#upload_header_number_container').show();
   $('#upload_header_total').html(_upload.total);
   // _upload.tips.find('.dialog-body-text').html(_upload.completed + '/' + _upload.total);
}

 _upload.getItemTpl=function(file) {
    var relativePath = (file.webkitRelativePath ? file.webkitRelativePath : (file.relativePath ? file.relativePath : file.name));
    var filearr = file.name.split('.');
    var ext = filearr.pop();
    var imgicon = '<img src="dzz/images/extimg/' + ext + '.png" onerror="replace_img(this)" style="width:24px;height:24px;position:absolute;left:0;"/>';
    var typerule = new RegExp(attachextensions, 'i');
	var uploadtips = (typerule.test(file.name)) ? '排队' : __lang.allow_file_type;
    if(maxfileSize && (maxfileSize < file.size)){
        uploadtips = '文件太大了！';
    }
    var html =
        '<div class="process" style="position:absolute;z-index:-1;background-color:#e8f5e9;height:100%;-webkit-transition:width 0.6s ease;-o-transition:width 0.6s ease;transition:width 0.6s ease;width:0%;"></div> <div class="dialog-info"> <div class="upload-file-name">' +
        '<div class="dialog-file-icon" align="center">' + imgicon + '</div> <span class="name-text text-truncate">' + file.name + '</span> ' +
        '</div> <div class="upload-file-size text-truncate">' + (file.size ? formatSize(file.size) : '0B') + '</div> <div class="upload-file-path text-truncate">' +
        '<a title="' + relativePath + '" class="" href="javascript:;">' + relativePath + '</a> </div> <div class="upload-file-status"> <span class="uploading text-truncate"><em class="precent"></em><em class="speed">'+uploadtips+'</em>' +
        '</span> <span class="success text-truncate"><em></em><i></i></span> </div> <div class="upload-file-operate"> ' +
        '<em class="operate-pause"></em> <em class="operate-continue"></em> <em class="operate-retry"></em> <em class="operate-remove"></em> ' +
        '<a class="btn btn-danger btn-sm upload-cancel" href="javascript:void(0);">'+__lang.cancel+'</a> </div> </div>';
    return html;
}

_upload.uploaddone=function(flag) {
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
};

_upload.uploadprogress=function(speed, per) {
	
	 $('#upload_header_progress').css('width', per);
     $('#upload_header_speed').show().html(_upload.bitrate + '/s');
   
};
function replace_img(obj) {
    jQuery(obj).attr('src', 'dzz/images/default/icodefault.png');
}
function layerupload() {
    layer.open({
        type: 1,
        offset: 'rb',
        anim: 'slideUp',
        area: ['100%','20%'],
        shade: 0,
        maxmin:true,
        title:'<div id="upload_header"><span id="upload_header_status">文件准备中...</span><span id="upload_header_number_container" style="display: none">[<span id="upload_header_completed">0</span><span>/</span><span id="upload_header_total">0</span>]</span><span id="upload_header_speed"></span><div id="upload_header_progress" class="process" style="position:absolute;left:0;background-color:#e8f5e9;top:0;z-index:-1;height:50px;-webkit-transition:width 0.6s ease in;-o-transition:width 0.6s ease in;transition:width 0.6s ease in;width:0%;"></div></div>',
        id: 'layer-upload',
        content: '<div class="upload-list-wrapper"><div class="upload-list-header border-bottom"><div class="upload-file-name">文件(夹)名</div><div class="upload-file-size">大小</div><div class="upload-file-path">上传目录</div><div class="upload-file-status">状态</div><div class="upload-file-operate"></div></div><div class="dialog-tips"> </div><div class="dialog-uploader-list"><ul class="dialog-filelist-ul" id="uploading_file_list"></ul></div><div class="progress-in"></div></div>',
        hideOnClose:true,
        cancel: function(index, layero){ 
            $('#upload_header_number_container').hide();
            $('#uploading_file_list').html('');
        }
    });
}
function formatSize(bytes) {
    var i = -1;
    do {
        bytes = bytes / 1024;
        i++;
    } while (bytes > 99);

    return Math.max(bytes, 0).toFixed(1) + ['KB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
}