/**
 * Created by JetBrains PhpStorm.
 * User: taoqili
 * Date: 12-2-20
 * Time: 上午11:19
 * To change this template use File | Settings | File Templates.
 */

(function () {

    var audio = {},
        uploadaudioList = [],
        isModifyUploadaudio = false,
        uploadFile;
    var editorOpt = {};

    window.onload = function () {
        editorOpt = editor.getOpt('audioConfig');
        $focus($G("audioUrl"));
        initTabs();
        initAudio();
        initUpload();
    };

    /* 初始化tab标签 */
    function initTabs() {
        var tabs = $G('tabHeads').children;
        for (var i = 0; i < tabs.length; i++) {
            domUtils.on(tabs[i], "click", function (e) {
                var j, bodyId, target = e.target || e.srcElement;
                for (j = 0; j < tabs.length; j++) {
                    bodyId = tabs[j].getAttribute('data-content-id');
                    if (tabs[j] == target) {
                        domUtils.addClass(tabs[j], 'focus');
                        domUtils.addClass($G(bodyId), 'focus');
                    } else {
                        domUtils.removeClasses(tabs[j], 'focus');
                        domUtils.removeClasses($G(bodyId), 'focus');
                    }
                }
            });
        }
        if (!editorOpt.disableUpload) {
            $G('tabHeads').querySelector('[data-content-id="upload"]').style.display = 'inline-block';
        }
        if (!!editorOpt.selectCallback) {
            $G('audioSelect').style.display = 'inline-block';
            domUtils.on($G('audioSelect'), "click", function (e) {
                editorOpt.selectCallback(editor, function (info) {
                    if (info) {
                        $G('audioUrl').value = info.path;
                        createPreview(info.path);
                    }
                });
            });
        }
    }

    function initAudio() {
        createAlignButton(["audioFloat", "upload_alignment"]);
        addUrlChangeListener($G("audioUrl"));
        addOkListener();

        //编辑视频时初始化相关信息
        (function () {
            var img = editor.selection.getRange().getClosedNode(), url;
            if (img && img.className) {
                var hasFakedClass = (img.className == "edui-faked-audio"),
                    hasUploadClass = img.className.indexOf("edui-upload-audio") != -1;
                if (hasFakedClass || hasUploadClass) {
                    $G("audioUrl").value = url = img.getAttribute("_url");
                    var align = domUtils.getComputedStyle(img, "float"),
                        parentAlign = domUtils.getComputedStyle(img.parentNode, "text-align");
                    updateAlignButton(parentAlign === "center" ? "center" : align);
                }
                if (hasUploadClass) {
                    isModifyUploadaudio = true;
                }
            }
            createPreview(url);
        })();
    }

    /**
     * 监听确认和取消两个按钮事件，用户执行插入或者清空正在播放的视频实例操作
     */
    function addOkListener() {
        dialog.onok = function () {
            $G("preview").innerHTML = "";
            var currentTab = findFocus("tabHeads", "tabSrc");
            switch (currentTab) {
                case "audio":
                    return insertSingle();
                    break;
                // case "audioSearch":
                //     return insertSearch("searchList");
                //     break;
                case "upload":
                    return insertUpload();
                    break;
            }
        };
        dialog.oncancel = function () {
            $G("preview").innerHTML = "";
        };
    }

    /**
     * 依据传入的align值更新按钮信息
     * @param align
     */
    function updateAlignButton(align) {
        var aligns = $G("audioFloat").children;
        for (var i = 0, ci; ci = aligns[i++];) {
            if (ci.getAttribute("name") == align) {
                if (ci.className != "focus") {
                    ci.className = "focus";
                }
            } else {
                if (ci.className == "focus") {
                    ci.className = "";
                }
            }
        }
    }

    /**
     * 将单个视频信息插入编辑器中
     */
    function insertSingle() {
        var url = $G('audioUrl').value,
            align = findFocus("audioFloat", "name");
        if (!url) return false;
        editor.execCommand('insertaudio', {
            url: url,
        }, isModifyUploadaudio ? 'upload' : null);
    }

    /**
     * 将元素id下的所有代表视频的图片插入编辑器中
     * @param id
     */
    function insertSearch(id) {
        var imgs = domUtils.getElementsByTagName($G(id), "img"),
            audioObjs = [];
        for (var i = 0, img; img = imgs[i++];) {
            if (img.getAttribute("selected")) {
                audioObjs.push({
                    url: img.getAttribute("ue_audio_url"),
                    width: 420,
                    height: 280,
                    align: "none"
                });
            }
        }
        editor.execCommand('insertaudio', audioObjs);
    }

    /**
     * 找到id下具有focus类的节点并返回该节点下的某个属性
     * @param id
     * @param returnProperty
     */
    function findFocus(id, returnProperty) {
        var tabs = $G(id).children,
            property;
        for (var i = 0, ci; ci = tabs[i++];) {
            if (ci.className == "focus") {
                property = ci.getAttribute(returnProperty);
                break;
            }
        }
        return property;
    }

    /**
     * 数字判断
     * @param value
     */
    function isNumber(value) {
        return /(0|^[1-9]\d*$)/.test(value);
    }

    /**
     * 创建图片浮动选择按钮
     * @param ids
     */
    function createAlignButton(ids) {
        for (var i = 0, ci; ci = ids[i++];) {
            var floatContainer = $G(ci),
                nameMaps = {
                    "none": lang['default'],
                    "left": lang.floatLeft,
                    "right": lang.floatRight,
                    "center": lang.block
                };
            for (var j in nameMaps) {
                var div = document.createElement("div");
                div.setAttribute("name", j);
                if (j == "none") div.className = "focus";
                div.style.cssText = "background:url(images/" + j + "_focus.jpg);";
                div.setAttribute("title", nameMaps[j]);
                floatContainer.appendChild(div);
            }
            switchSelect(ci);
        }
    }

    /**
     * 选择切换
     * @param selectParentId
     */
    function switchSelect(selectParentId) {
        var selects = $G(selectParentId).children;
        for (var i = 0, ci; ci = selects[i++];) {
            domUtils.on(ci, "click", function () {
                for (var j = 0, cj; cj = selects[j++];) {
                    cj.className = "";
                    cj.removeAttribute && cj.removeAttribute("class");
                }
                this.className = "focus";
            })
        }
    }

    /**
     * 监听url改变事件
     * @param url
     */
    function addUrlChangeListener(url) {
        if (browser.ie) {
            url.onpropertychange = function () {
                createPreview(this.value);
            }
        } else {
            url.addEventListener("input", function () {
                createPreview(this.value);
            }, false);
        }
    }

    function createAudioHtml(url, param) {
        param = param || {};
        var str = [
            "<audio",
            (param.id ? ' id="' + param.id + '"' : ""),
            (param.cls ? ' class="' + param.cls + '"' : ''),
            ' controls >',
            '<source src="' + url + '" type="audio/mpeg' + '" />',
            '</audio>',
        ];
        return str.join('');
    }

    /**
     * 根据url生成视频预览
     * @param url
     */
    function createPreview(url) {
        if (!url) {
            return;
        }

        $G("preview").innerHTML = '<div class="previewMsg"><span>' + lang.urlError + '</span></div>' +
            '<div style="position: absolute; inset: 0; background: #FFF; text-align: center; display: flex; justify-items: center; align-items: center;">' +
            '<div style="text-align:center;flex-grow:1;">' + createAudioHtml(url) + '</div>'
            + '</div>';
    }


    /* 插入上传视频 */
    function insertUpload() {
        var audioObjs = [],
            uploadDir = editor.getOpt('audioUrlPrefix'),
            align = findFocus("upload_alignment", "name") || 'none';
        for (var key in uploadaudioList) {
            var file = uploadaudioList[key];
            audioObjs.push({
                url: uploadDir + file.url,
                align: align
            });
        }

        var count = uploadFile.getQueueCount();
        if (count) {
            $('.info', '#queueList').html('<span style="color:red;">' + '还有2个未上传文件'.replace(/[\d]/, count) + '</span>');
            return false;
        } else {
            editor.execCommand('insertaudio', audioObjs, 'upload');
        }
    }

    /*初始化上传标签*/
    function initUpload() {
        uploadFile = new UploadFile('queueList');
    }


    /* 上传附件 */
    function UploadFile(target) {
        this.$wrap = target.constructor == String ? $('#' + target) : $(target);
        this.init();
    }

    UploadFile.prototype = {
        init: function () {
            this.fileList = [];
            this.initContainer();
            this.initUploader();
        },
        initContainer: function () {
            this.$queue = this.$wrap.find('.filelist');
        },
        /* 初始化容器 */
        initUploader: function () {
            var _this = this,
                $ = jQuery,    // just in case. Make sure it's not an other libaray.
                $wrap = _this.$wrap,
                // 图片容器
                $queue = $wrap.find('.filelist'),
                // 状态栏，包括进度和控制按钮
                $statusBar = $wrap.find('.statusBar'),
                // 文件总体选择信息。
                $info = $statusBar.find('.info'),
                // 上传按钮
                $upload = $wrap.find('.uploadBtn'),
                // 上传按钮
                $filePickerBtn = $wrap.find('.filePickerBtn'),
                // 上传按钮
                $filePickerBlock = $wrap.find('.filePickerBlock'),
                // 没选择文件之前的内容。
                $placeHolder = $wrap.find('.placeholder'),
                // 总体进度条
                $progress = $statusBar.find('.progress').hide(),
                // 添加的文件数量
                fileCount = 0,
                // 添加的文件总大小
                fileSize = 0,
                // 优化retina, 在retina下这个值是2
                ratio = window.devicePixelRatio || 1,
                // 缩略图大小
                thumbnailWidth = 113 * ratio,
                thumbnailHeight = 113 * ratio,
                // 可能有pedding, ready, uploading, confirm, done.
                state = '',
                // 所有文件的进度信息，key为file id
                percentages = {},
                supportTransition = (function () {
                    var s = document.createElement('p').style,
                        r = 'transition' in s ||
                            'WebkitTransition' in s ||
                            'MozTransition' in s ||
                            'msTransition' in s ||
                            'OTransition' in s;
                    s = null;
                    return r;
                })(),
                // WebUploader实例
                uploader,
                actionUrl = editor.getActionUrl(editor.getOpt('audioActionName')),
                fileMaxSize = editor.getOpt('audioMaxSize'),
                acceptExtensions = (editor.getOpt('audioAllowFiles') || []).join('').replace(/\./g, ',').replace(/^[,]/, '');
            ;

            if (!WebUploader.Uploader.support()) {
                $('#filePickerReady').after($('<div>').html(lang.errorNotSupport)).hide();
                return;
            } else if (!editor.getOpt('audioActionName')) {
                $('#filePickerReady').after($('<div>').html(lang.errorLoadConfig)).hide();
                return;
            }

            var uploaderOption = {
                pick: {
                    id: '#filePickerReady',
                    label: lang.uploadSelectFile
                },
                swf: '../../third-party/webuploader/Uploader.swf',
                server: actionUrl,
                fileVal: editor.getOpt('audioFieldName'),
                duplicate: true,
                fileSingleSizeLimit: fileMaxSize,
                headers: editor.getOpt('serverHeaders') || {},
                compress: false
            };

            if(editor.getOpt('uploadServiceEnable')) {
                uploaderOption.customUpload = function (file, callback) {
                    editor.getOpt('uploadServiceUpload')('audio', file, {
                        success: function( res ) {
                            callback.onSuccess(file, {_raw:JSON.stringify(res)});
                        },
                        error: function( err ) {
                            callback.onError(file, err);
                        },
                        progress: function( percent ) {
                            callback.onProgress(file, percent);
                        }
                    }, {
                        from: 'audio'
                    });
                };
            }

            uploader = _this.uploader = WebUploader.create(uploaderOption);
            uploader.addButton({
                id: '#filePickerBlock'
            });
            uploader.addButton({
                id: '#filePickerBtn',
                label: lang.uploadAddFile
            });

            setState('pedding');

            // 当有文件添加进来时执行，负责view的创建
            function addFile(file) {
                var $li = $('<li id="' + file.id + '">' +
                        '<p class="title">' + file.name + '</p>' +
                        '<p class="imgWrap"></p>' +
                        '<p class="progress"><span></span></p>' +
                        '</li>'),

                    $btns = $('<div class="file-panel">' +
                        '<span class="cancel">' + lang.uploadDelete + '</span>' +
                        '<span class="rotateRight">' + lang.uploadTurnRight + '</span>' +
                        '<span class="rotateLeft">' + lang.uploadTurnLeft + '</span></div>').appendTo($li),
                    $prgress = $li.find('p.progress span'),
                    $wrap = $li.find('p.imgWrap'),
                    $info = $('<p class="error"></p>').hide().appendTo($li),

                    showError = function (code) {
                        switch (code) {
                            case 'exceed_size':
                                text = lang.errorExceedSize;
                                break;
                            case 'interrupt':
                                text = lang.errorInterrupt;
                                break;
                            case 'http':
                                text = lang.errorHttp;
                                break;
                            case 'not_allow_type':
                                text = lang.errorFileType;
                                break;
                            default:
                                text = lang.errorUploadRetry;
                                break;
                        }
                        $info.text(text).show();
                    };

                if (file.getStatus() === 'invalid') {
                    showError(file.statusText);
                } else {
                    $wrap.text(lang.uploadPreview);
                    if ('|png|jpg|jpeg|bmp|gif|'.indexOf('|' + file.ext.toLowerCase() + '|') == -1) {
                        $wrap.empty().addClass('notimage').append('<i class="file-preview file-type-' + file.ext.toLowerCase() + '"></i>' +
                            '<span class="file-title">' + file.name + '</span>');
                    } else {
                        if (browser.ie && browser.version <= 7) {
                            $wrap.text(lang.uploadNoPreview);
                        } else {
                            uploader.makeThumb(file, function (error, src) {
                                if (error || !src || (/^data:/.test(src) && browser.ie && browser.version <= 7)) {
                                    $wrap.text(lang.uploadNoPreview);
                                } else {
                                    var $img = $('<img src="' + src + '">');
                                    $wrap.empty().append($img);
                                    $img.on('error', function () {
                                        $wrap.text(lang.uploadNoPreview);
                                    });
                                }
                            }, thumbnailWidth, thumbnailHeight);
                        }
                    }
                    percentages[file.id] = [file.size, 0];
                    file.rotation = 0;

                    /* 检查文件格式 */
                    if (!file.ext || acceptExtensions.indexOf(file.ext.toLowerCase()) == -1) {
                        showError('not_allow_type');
                        uploader.removeFile(file);
                    }
                }

                file.on('statuschange', function (cur, prev) {
                    if (prev === 'progress') {
                        $prgress.hide().width(0);
                    } else if (prev === 'queued') {
                        $li.off('mouseenter mouseleave');
                        $btns.remove();
                    }
                    // 成功
                    if (cur === 'error' || cur === 'invalid') {
                        showError(file.statusText);
                        percentages[file.id][1] = 1;
                    } else if (cur === 'interrupt') {
                        showError('interrupt');
                    } else if (cur === 'queued') {
                        percentages[file.id][1] = 0;
                    } else if (cur === 'progress') {
                        $info.hide();
                        $prgress.css('display', 'block');
                    } else if (cur === 'complete') {
                    }

                    $li.removeClass('state-' + prev).addClass('state-' + cur);
                });

                $li.on('mouseenter', function () {
                    $btns.stop().animate({height: 30});
                });
                $li.on('mouseleave', function () {
                    $btns.stop().animate({height: 0});
                });

                $btns.on('click', 'span', function () {
                    var index = $(this).index(),
                        deg;

                    switch (index) {
                        case 0:
                            uploader.removeFile(file);
                            return;
                        case 1:
                            file.rotation += 90;
                            break;
                        case 2:
                            file.rotation -= 90;
                            break;
                    }

                    if (supportTransition) {
                        deg = 'rotate(' + file.rotation + 'deg)';
                        $wrap.css({
                            '-webkit-transform': deg,
                            '-mos-transform': deg,
                            '-o-transform': deg,
                            'transform': deg
                        });
                    } else {
                        $wrap.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (~~((file.rotation / 90) % 4 + 4) % 4) + ')');
                    }

                });

                $li.insertBefore($filePickerBlock);
            }

            // 负责view的销毁
            function removeFile(file) {
                var $li = $('#' + file.id);
                delete percentages[file.id];
                updateTotalProgress();
                $li.off().find('.file-panel').off().end().remove();
            }

            function updateTotalProgress() {
                var loaded = 0,
                    total = 0,
                    spans = $progress.children(),
                    percent;

                $.each(percentages, function (k, v) {
                    total += v[0];
                    loaded += v[0] * v[1];
                });

                percent = total ? loaded / total : 0;

                spans.eq(0).text(Math.round(percent * 100) + '%');
                spans.eq(1).css('width', Math.round(percent * 100) + '%');
                updateStatus();
            }

            function setState(val, files) {

                if (val != state) {

                    var stats = uploader.getStats();

                    $upload.removeClass('state-' + state);
                    $upload.addClass('state-' + val);

                    switch (val) {

                        /* 未选择文件 */
                        case 'pedding':
                            $queue.addClass('element-invisible');
                            $statusBar.addClass('element-invisible');
                            $placeHolder.removeClass('element-invisible');
                            $progress.hide();
                            $info.hide();
                            uploader.refresh();
                            break;

                        /* 可以开始上传 */
                        case 'ready':
                            $placeHolder.addClass('element-invisible');
                            $queue.removeClass('element-invisible');
                            $statusBar.removeClass('element-invisible');
                            $progress.hide();
                            $info.show();
                            $upload.text(lang.uploadStart);
                            uploader.refresh();
                            break;

                        /* 上传中 */
                        case 'uploading':
                            $progress.show();
                            $info.hide();
                            $upload.text(lang.uploadPause);
                            break;

                        /* 暂停上传 */
                        case 'paused':
                            $progress.show();
                            $info.hide();
                            $upload.text(lang.uploadContinue);
                            break;

                        case 'confirm':
                            $progress.show();
                            $info.hide();
                            $upload.text(lang.uploadStart);

                            stats = uploader.getStats();
                            if (stats.successNum && !stats.uploadFailNum) {
                                setState('finish');
                                return;
                            }
                            break;

                        case 'finish':
                            $progress.hide();
                            $info.show();
                            if (stats.uploadFailNum) {
                                $upload.text(lang.uploadRetry);
                            } else {
                                $upload.text(lang.uploadStart);
                            }
                            break;
                    }

                    state = val;
                    updateStatus();

                }

                if (!_this.getQueueCount()) {
                    $upload.addClass('disabled')
                } else {
                    $upload.removeClass('disabled')
                }

            }

            function updateStatus() {
                var text = '', stats;

                if (state === 'ready') {
                    text = lang.updateStatusReady.replace('_', fileCount).replace('_KB', WebUploader.formatSize(fileSize));
                } else if (state === 'confirm') {
                    stats = uploader.getStats();
                    if (stats.uploadFailNum) {
                        text = lang.updateStatusConfirm.replace('_', stats.successNum).replace('_', stats.successNum);
                    }
                } else {
                    stats = uploader.getStats();
                    text = lang.updateStatusFinish.replace('_', fileCount).replace('_KB', WebUploader.formatSize(fileSize)).replace('_', stats.successNum);

                    if (stats.uploadFailNum) {
                        text += lang.updateStatusError.replace('_', stats.uploadFailNum);
                    }
                }

                $info.html(text);
            }

            uploader.on('fileQueued', function (file) {
                fileCount++;
                fileSize += file.size;

                if (fileCount === 1) {
                    $placeHolder.addClass('element-invisible');
                    $statusBar.show();
                }

                addFile(file);
            });

            uploader.on('fileDequeued', function (file) {
                fileCount--;
                fileSize -= file.size;

                removeFile(file);
                updateTotalProgress();
            });

            uploader.on('filesQueued', function (file) {
                if (!uploader.isInProgress() && (state == 'pedding' || state == 'finish' || state == 'confirm' || state == 'ready')) {
                    setState('ready');
                }
                updateTotalProgress();
            });

            uploader.on('all', function (type, files) {
                switch (type) {
                    case 'uploadFinished':
                        setState('confirm', files);
                        break;
                    case 'startUpload':
                        /* 添加额外的GET参数 */
                        var params = utils.serializeParam(editor.queryCommandValue('serverparam')) || '',
                            url = utils.formatUrl(actionUrl + (actionUrl.indexOf('?') == -1 ? '?' : '&') + 'encode=utf-8&' + params);
                        uploader.option('server', url);
                        setState('uploading', files);
                        break;
                    case 'stopUpload':
                        setState('paused', files);
                        break;
                }
            });

            uploader.on('uploadBeforeSend', function (file, data, header) {
                //这里可以通过data对象添加POST参数
                if (actionUrl.toLowerCase().indexOf('jsp') != -1) {
                    header['X_Requested_With'] = 'XMLHttpRequest';
                }
            });

            uploader.on('uploadProgress', function (file, percentage) {
                var $li = $('#' + file.id),
                    $percent = $li.find('.progress span');

                $percent.css('width', percentage * 100 + '%');
                percentages[file.id][1] = percentage;
                updateTotalProgress();
            });

            uploader.on('uploadSuccess', function (file, ret) {
                var $file = $('#' + file.id);
                try {
                    var responseText = (ret._raw || ret),
                        json = utils.str2json(responseText);
                    json = editor.getOpt('serverResponsePrepare')(json);
                    if (json.state == 'SUCCESS') {
                        uploadaudioList.push({
                            'url': json.url,
                            'type': json.type,
                            'original': json.original
                        });
                        $file.append('<span class="success"></span>');
                    } else {
                        $file.find('.error').text(json.state).show();
                    }
                } catch (e) {
                    $file.find('.error').text(lang.errorServerUpload).show();
                }
            });

            uploader.on('uploadError', function (file, code) {
            });
            uploader.on('error', function (code, param1, param2) {
                if (code === 'F_EXCEED_SIZE') {
                    editor.getOpt('tipError')(lang.errorExceedSize + ' ' + (param1 / 1024 / 1024).toFixed(1) + 'MB');
                } else {
                    console.log('error', code, param1, param2);
                }
            });
            uploader.on('uploadComplete', function (file, ret) {
            });

            $upload.on('click', function () {
                if ($(this).hasClass('disabled')) {
                    return false;
                }

                if (state === 'ready') {
                    uploader.upload();
                } else if (state === 'paused') {
                    uploader.upload();
                } else if (state === 'uploading') {
                    uploader.stop();
                }
            });

            $upload.addClass('state-' + state);
            updateTotalProgress();
        },
        getQueueCount: function () {
            var file, i, status, readyFile = 0, files = this.uploader.getFiles();
            for (i = 0; file = files[i++];) {
                status = file.getStatus();
                if (status == 'queued' || status == 'uploading' || status == 'progress') readyFile++;
            }
            return readyFile;
        },
        refresh: function () {
            this.uploader.refresh();
        }
    };

})();
