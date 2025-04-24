var aiConfig = editor.getOpt('ai')

var aiFunctions = editor.getOpt('aiFunctions');

var isMultiLine = function (text) {
    return text.indexOf('\n') !== -1;
}

var fetchStream = function (url, option, onStream, onFinish) {
    fetch(url, Object.assign({
        method: 'POST',
    }, option)).then(response => {
        if (!response.ok) {
            onFinish({code: -1, msg: `HTTP error! status: ${response.status}`})
            return
        }
        const reader = response.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';
        const textList = []

        function processChunk(chunk) {
            buffer += decoder.decode(chunk, {stream: true});
            // 分割事件流，每个事件以"data:"开头，以两个换行符结束
            const lines = buffer.split('\n');
            for (let line of lines) {
                line = line.trim();
                if (line.startsWith('data:')) {
                    const jsonStr = line.replace('data:', '').trim();
                    if (jsonStr === '[DONE]') {
                        onFinish({code: 0, msg: 'ok', data: {text: textList.join('')}})
                        return;
                    }
                    try {
                        let text = null
                        const data = JSON.parse(jsonStr);
                        if (data.choices && data.choices.length > 0 && data.choices[0].delta) {
                            text = data.choices[0].delta.content
                        } else if (data.type) {
                            // 兼容ModStart
                            // {"type":"error","data":"xxx"}
                            // {"type":"end","data":"xxx"}
                            // {"type":"data","data":"xxx"}
                            if (data.type === 'error') {
                                onFinish({code: -1, msg: data.data})
                                return;
                            } else if (data.type === 'end') {
                                onFinish({code: 0, msg: 'ok', data: {text: textList.join('')}})
                                return;
                            } else if (data.type === 'data') {
                                text = data.data
                            }
                        }
                        if (text !== null) {
                            textList.push(text)
                            onStream({code: 0, msg: 'ok', data: {text: text}})
                        } else {
                            onFinish({code: -1, msg: 'No text found!'})
                            console.log('data:', data)
                        }
                    } catch (e) {
                        onFinish({code: -1, msg: 'JSON parse error!' + e})
                    }
                }
            }
            buffer = lines.pop() || '';
        }

        function read() {
            reader.read().then(({done, value}) => {
                if (done) {
                    if (buffer) processChunk(new Uint8Array());
                    return;
                }
                processChunk(value);
                read();
            }).catch(error => {
                onFinish({code: -1, msg: 'Stream error!' + error})
            });
        }

        read();
    }).catch(error => {
        onFinish({code: -1, msg: 'Request error!' + error})
    });
}

var openAiCompletion = function (url, param, option) {
    option = Object.assign({
        body: null
    }, option)
    if (!option.body) {
        option.body = {
            model: aiConfig.driverConfig.model,
            messages: [{role: 'user', content: param.promptText}],
            stream: true,
        }
    }
    fetchStream(
        url,
        {
            headers: {
                'Authorization': `Bearer ${aiConfig.driverConfig.key}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(option.body)
        },
        param.onStream,
        param.onFinish
    )
}

var drivers = {
    'ModStart': function (param) {
        openAiCompletion(aiConfig.driverConfig.url, param, {
            body: {
                prompt: param.promptText
            }
        })
    },
    'OpenAi': function (param) {
        openAiCompletion(aiConfig.driverConfig.url || 'https://api.openai.com/v1/engines/davinci/completions', param)
    },
    'DeepSeek': function (param) {
        openAiCompletion(aiConfig.driverConfig.url || 'https://api.deepseek.com/chat/completions', param)
    },
}

function getRequest(driver) {
    if (aiConfig.driverRequest) {
        return aiConfig.driverRequest
    }
    if (driver in drivers) {
        return drivers[driver]
    }
    return null
}

var converter = new window.showdown.Converter();

var Ai = {
    runtime: {
        range: null,
    },
    init: function () {
        new Vue({
            el: '#app',
            data: {
                loading: false,
                selectText: '',
                inputText: '',
                promptText: '',
                resultText: '',
                resultError: '',
                functions: [],
            },
            mounted: function () {
                Ai.runtime.range = editor.selection.getRange();
                var cloneContents = Ai.runtime.range.cloneContents();
                if (cloneContents) {
                    this.selectText = cloneContents.textContent.trim();
                } else {
                    this.selectText = '';
                }
                this.buildFunctions()
            },
            computed: {
                resultHtml: function () {
                    if (!this.resultText) {
                        return '';
                    }
                    return converter.makeHtml(this.resultText);
                },
                resultHeight: function () {
                    let height = 255
                    if (this.selectText) {
                        height -= 45
                    }
                    if (this.resultError) {
                        height -= 45
                    }
                    return height + 'px'
                }
            },
            methods: {
                buildFunctions: function () {
                    var enableParam = {
                        selectText: this.selectText
                    }
                    this.functions = aiFunctions.map(function (f) {
                        if (!f.enable(enableParam)) {
                            return null;
                        }
                        f.prompt = f.prompt.replace(/\{selectText\}/g, enableParam.selectText);
                        return f;
                    }).filter(function (f) {
                        return !!f;
                    });
                },
                doSubmit: function () {
                    if (this.loading) {
                        return;
                    }
                    if (this.inputText) {
                        if (this.selectText) {
                            this.promptText = this.selectText + '\n\n' + this.inputText;
                        } else {
                            this.promptText = this.inputText;
                        }
                    }
                    if (!this.promptText) {
                        editor.tipError('请输入内容');
                        return;
                    }
                    this.loading = true;
                    this.resultError = '';
                    this.resultText = '';
                    var driverRequest = getRequest(aiConfig.driver)
                    if (!driverRequest) {
                        editor.tipError('未找到驱动');
                        return;
                    }
                    driverRequest({
                        promptText: this.promptText,
                        onStream: (res) => {
                            if (res.code) {
                                this.resultError = res.msg;
                                return;
                            }
                            this.resultText += res.data.text
                        },
                        onFinish: (res) => {
                            this.loading = false;
                            if (res.code) {
                                this.resultError = res.msg;
                                return;
                            }
                            this.resultText = res.data.text;
                        }
                    })
                },
                doSubmitFunction: function (f) {
                    this.promptText = f.prompt;
                    this.doSubmit()
                },
                doInsert: function () {
                    editor.fireEvent('saveScene');
                    if (this.selectText) {
                        if (isMultiLine(this.resultText)) {
                            Ai.runtime.range.insertNode(document.createRange().createContextualFragment(this.resultHtml));
                        } else {
                            Ai.runtime.range.insertNode(document.createTextNode(this.resultText));
                        }
                    } else {
                        if (isMultiLine(this.resultText)) {
                            editor.execCommand('insertHtml', this.resultHtml);
                        } else {
                            editor.execCommand('insertHtml', this.resultText);
                        }
                    }
                    dialog.close(true);
                },
                doReplace: function () {
                    editor.fireEvent('saveScene');
                    Ai.runtime.range.deleteContents();
                    if (isMultiLine(this.resultText)) {
                        Ai.runtime.range.insertNode(document.createRange().createContextualFragment(this.resultHtml));
                    } else {
                        Ai.runtime.range.insertNode(document.createTextNode(this.resultText));
                    }
                    dialog.close(true);
                },
            }
        });
    },
};
utils.domReady(function () {
    Ai.init();
});
