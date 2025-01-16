/**
 * 联动下拉插件
 * 来源于layui的一个插件，这里根据需求做了些修改
 * @原作者 tomato
 */
;(function($, window, document, undefined) {
    
    var bootstrapSelectN = function(ele, opt, callback) {
        this.$element = ele;
        this.callback = callback;
        // 是否引入lyearSelect插件
        this.sSelect = typeof(jQuery.fn.lyearSelect) == 'function';
        // 当前选中数据值名数据
        this.selected = [];
        // 当前选中的值
        this.values = [];
        // 当前选中的名
        this.names = [];
        // 当前选中最后一个值
        this.lastValue = '';
        // 当前选中最后一个名
        this.lastName = '';
        // 是否已选
        this.isSelected = false;
        this.defaults = {
            // 数据
            data: [],
            // 默认选中值
            selected: [],
            // 空值项提示，可设置为数组['请选择省', '请选择市', '请选择县']
            placeholder: '请选择',
            // 是否允许搜索，可设置为数组[true, true, true],
            search: false,
            // 宽度，可设置为数组['80px','90px','100px'],['15%%','20%','25%'],['col-md-4','col-md-3','col-ms-2']
            width: '',
            // 为真只取最后一个值
            last: false,
            // select的name值，不设置则没有，数组格式
            name: [],
            // 数据分隔符
            delimiter: ',',
            // 数据的键名 status=0为禁用状态
            field: {idName: 'id', titleName: 'name', statusName: 'status', childName: 'children'},
        };
        this.configs = $.extend({}, this.defaults, opt);
    
        // '请选择'文字
        this.setTips = function() {
            if (Object.prototype.toString.call(this.configs.placeholder) != '[object Array]') {
                return this.configs.placeholder;
            } else {
                var i = this.$element.find('select').length;
                return this.configs.placeholder.hasOwnProperty(i) ? this.configs.placeholder[i] : '请选择';
            }
        };
        
        // 是否允许搜索
        this.setSearch  = function() {
            if (Object.prototype.toString.call(this.configs.search) != '[object Array]') {
                return this.configs.search == true ? 'data-search="true" ' : ' ';
            } else {
                var i = this.$element.find('select').length;
                if (this.configs.search.hasOwnProperty(i)) {
                    return this.configs.search[i] == true ? 'data-search="true" ' : ' ';
                }
            }
            return '';
        };
        
        // 设置选择项的宽度
        this.setWidth = function() {
            if (this.configs.width == '') {
                return ' me-1"';
            }if (Object.prototype.toString.call(this.configs.width) != '[object Array]') {
                return /^col-*/.test(this.configs.width) ? ' ' + this.configs.width + '"' : 'me-1" style="width:' + this.configs.width + ';" ';
            } else {
                var i = this.$element.find('select').length;
                if (this.configs.width.hasOwnProperty(i)) {
                    return /^col-*/.test(this.configs.width[i]) ? ' ' + this.configs.width[i] + '"' : 'me-1" style="width:' + this.configs.width[i] + ';" ';
                }
            }
            return ' me-1"';
        };
        
        // 设置select的name值
        this.setName = function() {
            if (Object.prototype.toString.call(this.configs.name) != '[object Array]') {
                return ' name="' + this.configs.name + '"';
            } else {
                var i = this.$element.find('select').length;
                if (this.configs.name.hasOwnProperty(i)) {
                    return this.configs.name[i] ? ' name="' + this.configs.name[i] + '"' : '';
                }
            }
            return '';
        };
        
        // 创建一个select
        this.createSelect = function(optionData) {
            var f = this.configs.field;
            var html = '';
            html += '<div class="d-inline-block ' + this.setWidth() + '>';
            html += '  <select class="selectN form-select" ' + this.setSearch() + ' ' + this.setName() + '>';
            html += '    <option value="">' + this.setTips() + '</option>';
            for (var i = 0; i < optionData.length; i++) {
                var disabled = optionData[i][f.statusName] == 0 ? 'disabled="" ' : '';
                html += '  <option ' + disabled + 'value="' + optionData[i][f.idName] + '">' + optionData[i][f.titleName] + '</option>';
            }
            html += '  </select>';
            html += '</div>';
            
            return html;
        };
        
        // 获取当前option的数据
        this.getOptionData = function(catData, optionIndex) {
            var f = this.configs.field;
            var item = catData;
            
            for(var i = 0; i < optionIndex.length; i++) {
                if ('undefined' == typeof item[optionIndex[i]]) {
                    item = null;
                    break;
                } else if ('undefined' == typeof item[optionIndex[i]][f.childName]) {
                    item = null;
                    break;
                } else {
                    item = item[optionIndex[i]][f.childName];
                }
            }
            return item;
        };
        
        // 初始化
        this.init = function(selected) {
            var html = this.createSelect(this.configs.data);
            this.$element.append(html);
            selected = typeof selected == 'undefined' ? this.configs.selected : selected;
            var index = [];
            for (var i = 0; i < selected.length; i++) {
                // 设置最后一个selecte的选中值
                this.$element.find('select:last').val(selected[i]);
                // 获取该选中值的索引
                var lastIndex = this.$element.find('select:last').get(0).selectedIndex - 1;
                index.push(lastIndex);
                // 取出下级的选项值
                var childItem = this.getOptionData(this.configs.data, index);
                // 下级选项值存在则创建select
                if (childItem) {
                    var html = this.createSelect(childItem);
                    this.$element.append(html);
                }
            }
            
            if (this.sSelect) {
                var $thisSelect = $('.selectN');
                $thisSelect.lyearSelect({search: $thisSelect.data('search')});
            }
            this.getSelected();
        };
        
        // 下拉事件
        this.change = function(elem) {
            var $thisItem = elem.parent();
            // 移除后面的select
            $thisItem.nextAll('div.d-inline-block').remove();
            var index = [];
            // 获取所有select，取出选中项的值和索引
            $thisItem.parent().find('select').each(function() {
                index.push($(this).get(0).selectedIndex - 1);
            });
            
            var childItem = this.getOptionData(this.configs.data, index);
            if (childItem) {
                var html = this.createSelect(childItem);
                $thisItem.after(html);
            
                if (this.sSelect) {
                    var $thisSelect = $('.selectN:visible');
                    $thisSelect.lyearSelect({search: $thisSelect.data('search')});
                } else {
                    var $thisSelect = $('select:last');
                }
                if(typeof this.callback === 'function'){
                    this.callback($thisSelect);
                }
            }
            this.getSelected();
        };
            
        // 获取所有值 - 数组 每次选择后执行
        this.getSelected = function() {
            var values   = [];
            var names    = [];
            var selected = [];
            
            this.$element.find('select').each(function() {
                var item = {};
                var v    = $(this).val();
                var n    = $(this).find('option:selected').text();
                item.value = v;
                item.name  = n;
                values.push(v);
                names.push(n);
                selected.push(item);
            });
            this.selected = selected;
            this.values = values;
            this.names = names;
            this.lastValue = this.$element.find('select:last').val();
            this.lastName = this.$element.find('option:selected:last').text();
            
            this.isSelected = this.lastValue == '' ? false : true;
            var inputVal = this.configs.last == true ? this.lastValue : this.values.join(this.configs.delimiter);
        };
            
        // ajax方式获取候选数据
        this.getData = function(url) {
            var d;
            $.ajax({
                url: url,
                dataType: 'json',
                async: false,
                success: function(json) {
                    d = json;
                },
                error: function() {
                    console.error('候选数据ajax请求错误');
                    d = false;
                }
            });
            return d;
        };
    };
    
    bootstrapSelectN.prototype = {
        render: function() {
            var $this = this;
            if (this.$element.length == 0) {
                console.error('找不到容器');
                return false;
            }
            if (Object.prototype.toString.call(this.configs.data) != '[object Array]') {
                var data = this.getData(this.configs.data);
                if (data === false) {
                    console.log('缺少分类数据');
                    return false;
                }
                this.configs.data = data;
            }            
            
            // 初始化
            this.init();
            
            // 监听下拉事件
		    this.$element.on('change', '.selectN',function(e){
		    	$this.change($(this));	
		    });
        }
    }

    $.fn.bootstrapSelectN = function(options, callback) {
        var _this = new bootstrapSelectN(this, options, callback);
        _this.render();

        return _this;
    }
})(jQuery, window, document);