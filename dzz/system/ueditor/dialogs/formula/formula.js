function preg_quote(str, delimiter) {
    // Quote regular expression characters plus an optional character
    //
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/preg_quote
    // +   original by: booeyOH
    // +   improved by: Ates Goral (http://magnetiq.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: preg_quote("$40");
    // *     returns 1: '\$40'
    // *     example 2: preg_quote("*RRRING* Hello?");
    // *     returns 2: '\*RRRING\* Hello\?'
    // *     example 3: preg_quote("\\.+*?[^]$(){}=!<>|:");
    // *     returns 3: '\\\.\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:'
    return (str + '').replace(new RegExp('[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\' + (delimiter || '') + '-]', 'g'), '\\$&');
}

function loadScript(url, cb) {
    var script;
    script = document.createElement('script');
    script.src = url;
    script.onload = function () {
        cb && cb({isNew: true})
    };
    document.getElementsByTagName('head')[0].appendChild(script);
}

var Formula = {
    mode: 'plain',
    latexeasy: null,
    init: function () {
        // console.log('Formula.init')
        Formula.initMode();
        Formula.initEvent();
        Formula.initSubmit();
    },
    renderPlain: function () {
        var $preview = $('#preview');
        var value = $('#editor').val();
        if (!value) {
            $preview.hide();
            return;
        }
        value = encodeURIComponent(value);
        var formulaConfig = editor.getOpt('formulaConfig');
        var src = formulaConfig.imageUrlTemplate.replace(/\{\}/, value);
        $('#previewImage').attr('src', src);
        $preview.show();
    },
    setValuePlain: function (value) {
        $('#editor').val(value);
        Formula.renderPlain();
    },
    setValueLive: function (value) {
        if (!Formula.latexeasy) {
            setTimeout(function () {
                Formula.setValueLive(value);
            }, 100);
            return;
        }
        Formula.latexeasy.call('set.latex', {latex: value});
    },
    initMode: function () {
        var formulaConfig = editor.getOpt('formulaConfig');
        if ('live' === formulaConfig.editorMode) {
            $('#liveEditor').attr('src', formulaConfig.editorLiveServer + '/editor');
            $('#modeLive').show();
            Formula.mode = 'live';
        } else {
            $('#modePlain').show();
            Formula.mode = 'plain';
        }
        var img = editor.selection.getRange().getClosedNode();
        if (img && img.getAttribute('data-formula-image') !== null) {
            var value = img.getAttribute('data-formula-image');
            if (value) {
                Formula.setValue(decodeURIComponent(value));
            }
        }
    },
    setValue: function (value) {
        switch (Formula.mode) {
            case 'plain':
                Formula.setValuePlain(value);
                break;
            case 'live':
                Formula.setValueLive(value);
                break;
        }
    },
    getValue: function (cb) {
        switch (Formula.mode) {
            case 'plain':
                cb($.trim($('#editor').val()));
                break;
            case 'live':
                Formula.latexeasy.call('get.latex', {}, function (data) {
                    cb(data.latex);
                });
                break;
        }
    },
    initEvent: function () {
        var changeTimer = null, le;
        switch (Formula.mode) {
            case 'plain':
                // console.log('Formula.initEvent');
                $('#editor').on('change keypress', function () {
                    changeTimer && clearTimeout(changeTimer);
                    changeTimer = setTimeout(function () {
                        Formula.renderPlain();
                    }, 1000);
                });
                $('#inputDemo').on('click', function () {
                    $('#editor').val('f(a) = \\frac{1}{2\\pi i} \\oint\\frac{f(z)}{z-a}dz');
                    Formula.renderPlain();
                });
                break;
            case 'live':
                var formulaConfig = editor.getOpt('formulaConfig');
                loadScript(formulaConfig.editorLiveServer + '/vendor/LatexEasyEditor/editor/sdk.js', function () {
                    le = new window.LatexEasy(document.getElementById('liveEditor'));
                    le.on('ready', function () {
                        Formula.latexeasy = le;
                    });
                    le.init();
                });
                break;
        }
    },
    initSubmit: function () {
        dialog.onclose = function (t, ok) {
            if (!ok) {
                return true;
            }
            // console.log('onclose', t, ok);
            Formula.getValue(function (value) {
                editor.execCommand('formula', value);
                editor.fireEvent('saveScene');
                dialog.close(false);
            });
            return false;
        };
    }
};
