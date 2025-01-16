
function fileupload (el,typeallow) {//图片上传
	if (!typeallow) typeallow = "\.*$";
    var self = this;
    obj = $(this);
    el.fileupload({
        url: MOD_URL + '&op=mobile&do=ajax&operation=uploadfiles&container=' + fid,
        dataType: 'json',
        autoUpload: true,
        maxFileSize: 20000000, // 20MB
        maxChunkSize: 2000000, //2M
        acceptFileTypes: new RegExp(typeallow, 'i'),
        sequentialUploads: true,        
        add: function (e, data) {
        	console.log($(this));
           data.content = jQuery(this).parents('.weui-footer').siblings('.weui-cells');
            jQuery.each(data.files, function (index, file) { 
            	var ext = file.name.split('.').pop().toLowerCase();
                if (jQuery.inArray(ext, ['jpg', 'jpeg', 'gif', 'png', 'bmp']) > -1) {
                    var img = 'dzz/images/default/thumb.png';
                } else {
                    var img = 'dzz/images/extimg/' + ext + '.png';
                }
                 data.list = jQuery('<div class="weui-uploader__file weui-uploader__file_status"  style="background-image:url(' + img + ')"><div class="weui-uploader__file-content">0%</div></div>');
                 $('#'+_filemanage.contains).prepend(data.list);
            });
            data.process().done(function () {
                data.submit();
            });
        },
        progress: function (e, data) {
            var index = 0;
            var progress = parseInt(data.loaded / data.total * 100, 10);
            data.list.find('.weui-uploader__file-content').text(progress + '%')
        },
        done: function (e, data) {
             $.each(data.result.files, function (index, file) {
	            if (file.error) {
	                var relativePath = (file.relativePath ? file.relativePath : '');
	            } else {
	                if (file.data.icoarr) {	                	
	                    for (var i = 0; i < file.data.icoarr.length; i++) {
	                        var data = file.data.icoarr[i],
	                        newhtml = _filemanage.getNewIcos(data);
	                        $('.weui-uploader__file_status').replaceWith(newhtml);
	                        _filemanage.datajson.data[data.rid]=data;	                        
	                    }
	                    $('div.new-more').addClass('hide');
				        $('div.new-more').next('div.background-none').hide();
				        $('div.new-more').prevAll('.weui-footer-none').find('p').css({'color': '#666666'});
	                }
					           
	            
	            }
        });

        }
    });
}