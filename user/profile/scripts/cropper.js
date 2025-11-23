function cropImage()
{
    this.$uploadUrl    = MOD_URL+"&op=avatar&do=imageupload";          // 上传地址
    this.$triggerBtn   = $('.trigger-btn');                            // 上传按钮
    this.$imageModal   = $('#image-modal');                            // 弹出框
    this.$imageName    = this.$imageModal.find('.image-name')          // 图片名称
    this.$imageInput   = this.$imageModal.find('.image-input');        // file上传文件
    this.$imageUpload  = this.$imageModal.find('.image-upload');       // file所在元素
    this.$imageBtns    = this.$imageModal.find('.image-btns');         // 图片调整按钮(旋转、放大等)
    this.$imageWrapper = this.$imageModal.find('.image-wrapper');      // 图片处理区域
    this.$imagePreview = this.$imageModal.find('.image-preview');      // 图片裁剪预览区域
    this.$uploadBtn    = this.$imageModal.find('.upload-btn');         // 上传按钮
    this.$togglesBtns  = this.$imageModal.find('.toggles-btns');       // 比例切换按钮
    this.URL           = window.URL || window.webkitURL;
    this.$options = {
        aspectRatio: 1,
        viewMode: 1, // 确保裁剪框不能超出图片
        minContainerWidth: 200,
        minContainerHeight: 200,
        preview: '.image-preview',
    };                                                                 // 配置
    this.init();
}
cropImage.prototype = {
    constructor: cropImage,
    init: function() {
        this.initModal();
        this.addListener();
        this.$uploadBtn.prop('disabled', true);
    },
    initModal: function() {
        this.$imageModal.modal({
            show: false
        });
        
        if(!URL) {
            this.$imageInput.prop('disabled', true);
        }
    },
    addListener: function() {
        // 绑定事件
        this.$triggerBtn.on('click', $.proxy(this.click, this));
        this.$imageInput.on('change', $.proxy(this.change, this));
        this.$togglesBtns.on('change', $.proxy(this.choose, this));
        this.$uploadBtn.on('click', $.proxy(this.ajaxUpload, this));
        this.$imageBtns.on('click', $.proxy(this.rotate, this));
    },
    click: function(e) {
        // 点击上传按钮
        this.$imageText = jQuery('.image-src');
        this.$imageModal.modal('show');
        this.initPreview();
    },
    initPreview: function() {
        this.active = false;
        this.$imageInput.val('');
        this.$imageName.text('');
        this.$imageWrapper.empty();
        this.$uploadBtn.prop('disabled', true);
        // 如果已有图片地址，初始化图片预览区域
        this.$imagePreview.empty();
        var url = this.$imageText.val();
        (url.length > 0) && this.$imagePreview.html('<img src="' + url + '">');
    },
    change: function() {
        // 选择图片
        var files, file;
        files = this.$imageInput.prop('files');
        if (files.length === 0) {
            return showmessage('请选择照片', 'danger', 3000, 1);
        }
        if (files && files.length > 0) {
            file = files[0];
            if (file.size > 2 * 1024 * 1024) { // 2MB
                showmessage('上传的图片大小不能超过2MB', 'danger', 3000, 1);
                return;
            }
            if (this.isImageFile(file)) {
                this.$imageName.text(file.name);
                if (this.imageUrl) {
                    this.URL.revokeObjectURL(this.imageUrl);
                }
                this.imageUrl = this.URL.createObjectURL(file);
                this.startCropper();
                this.$uploadBtn.prop('disabled', false);
            }
        }
    },
    startCropper: function() {
        // 选择图片后初始化
        if (this.active) {
            this.$image.cropper('replace', this.imageUrl, true);
        } else {
            this.$image = $('<img src="' + this.imageUrl + '">');
            this.$imageWrapper.empty().html(this.$image);
            this.$image.cropper('destroy').cropper(this.$options);
            
            this.active = true;
        }
    },
    isImageFile: function(file) {
        // 判断是否图片格式
        if (file.type) {
            return /^image\/\w+$/.test(file.type);
        } else {
            return /\.(jpg|jpeg|png|gif|bmp|tiff)$/.test(file);
        }
    },
    choose: function(e) {
        var $this = $(e.target);
        var name  = $this.attr('name');
        
        if (!this.active) {
            return;
        }
        
        this.$options[name] = $this.val();
        this.$image.cropper('destroy').cropper(this.$options);
    },
    rotate: function(e) {
        // 调整图片操作
        var data;
        if (this.active) {
            data = $(e.target).data();
            if (data.method) {
                this.$image.cropper(data.method, data.option);
            }
        }
    },
    stopCropper: function() {
        // 裁剪上传完成后重置
        if (this.active) {
            this.$image.cropper('destroy');
            this.$image.remove();
            this.$imageModal.modal('hide');
            this.$imageInput.val('');
            this.$imageName.text('');
            this.$togglesBtns.find('#aspectRatio1').attr('checked', true);
            this.active = false;
        }
    },
    ajaxUpload: function() {
        files = this.$imageInput.prop('files');
        if (files.length === 0) {
            return showmessage('请选择照片', 'danger', 3000, 1);
        }
        var cas = this.$image.cropper('getCroppedCanvas', {
            width: 200,
            height: 200
        }),
        base64Data = cas.toDataURL('image/png'),
        _this = this,
        $loading;
        
        // ajax上传
        $.ajax(this.$uploadUrl, {
            type: 'post',
            data: {"formhash":formhash,"imagedata" : base64Data,"avatarsubmit": 1},
            dataType: 'json',
            beforeSend: function() {
                _this.$uploadBtn.prop('disabled', true);
                $loading = $('.upload-btn').lyearloading({
                    opacity: 0.2,
                    spinnerSize: 'nm'
                });
            },
            success: function(data) {
                if(data.msg == 'success') {
                    showmessage(__lang.avatar_uploaded_successfully_time, 'success', 3000, 1);
                    _this.stopCropper();
                    jQuery('.cropimage').attr('src', base64Data);
                } else if(data.error) {
                    showmessage(data.error, 'danger', 3000, 1);
                } else {
                    showmessage('上传失败', 'danger', 3000, 1);
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                showmessage('上传失败', 'danger', 3000, 1);
            },
            complete: function() {
                _this.$uploadBtn.prop('disabled', false);
                $loading.destroy();
            }
        });
    }
};
$(document).ready(function(){
    new cropImage();
});