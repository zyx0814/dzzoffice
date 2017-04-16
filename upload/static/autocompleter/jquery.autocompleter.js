;(function ($, window) {
    'use strict';

    var guid = 0,
        ignoredKeyCode = [9, 13, 17, 19, 20, 27, 33, 34, 35, 36, 37, 39, 44, 92, 113, 114, 115, 118, 119, 120, 122, 123, 144, 145],
        allowOptions = [
            'source',
            'empty',
            'limit',
            'cache',
            'cacheExpires',
            'focusOpen',
            'selectFirst',
            'changeWhenSelect',
            'highlightMatches',
            'ignoredKeyCode',
            'customLabel',
            'customValue',
            'template',
            'offset',
            'combine',
            'callback',
            'minLength',
            'delay'
        ],
        userAgent = (window.navigator.userAgent||window.navigator.vendor||window.opera),
        isFirefox = /Firefox/i.test(userAgent),
        isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(userAgent),
        isFirefoxMobile = (isFirefox && isMobile),
        $body = null,
        delayTimeout = null,
        localStorageKey = 'autocompleterCache',
        supportLocalStorage = (function () {
            var supported = typeof window.localStorage !== 'undefined';

            if (supported) {
                try {
                    localStorage.setItem('autocompleter', 'autocompleter');
                    localStorage.removeItem('autocompleter');
                } catch (e) {
                    supported = false;
                }
            }

            return supported;
        })();

    /**
     * Don't forget update README.md
     *
     * @options
     * @param source [(string|object)] <null> "URL to the server or a local object"
     * @param asLocal [boolean] <false> "Parse remote response as local source"
     * @param empty [boolean] <true> "Launch if value is empty"
     * @param limit [int] <10> "Number of results to be displayed"
     * @param minLength [int] <0> "Minimum length for autocompleter"
     * @param delay [int] <0> "Few milliseconds to defer the request"
     * @param customClass [array] <[]> "Array with custom classes for autocompleter element"
     * @param cache [boolean] <true> "Save xhr data to localStorage to avoid the repetition of requests"
     * @param cacheExpires [int] <86400> "localStorage data lifetime"
     * @param focusOpen [boolean] <true> "Launch autocompleter when input gets focus"
     * @param hint [boolean] <false> "Add hint to input with first matched label, correct styles should be installed"
     * @param selectFirst [boolean] <false> "If set to true, first element in autocomplete list will be selected automatically, ignore if changeWhenSelect is on"
     * @param changeWhenSelect [boolean] <true> "Allows to change input value using arrow keys navigation in autocomplete list"
     * @param highlightMatches [boolean] <false> "This option defines <strong> tag wrap for matches in autocomplete results"
     * @param ignoredKeyCode [array] <[]> "Array with ignorable keycodes"
     * @param customLabel [boolean] <false> "The name of object's property which will be used as a label"
     * @param customValue [boolean] <false> "The name of object's property which will be used as a value"
     * @param template [(string|boolean)] <false> "Custom template for list items"
     * @param offset [(string|boolean)] <false> "Source response offset, for example: response.items.posts"
     * @param combine [function] <$.noop> "Returns an object which extends ajax data. Useful if you want to pass some additional server options"
     * @param callback [function] <$.noop> "Select value callback function. Arguments: value, index"
     */
    var options = {
        source: null,
        asLocal: false,
        empty: true,
        limit: 10,
        minLength: 0,
        delay: 0,
        customClass: [],
        cache: true,
        cacheExpires: 86400,
        focusOpen: true,
        hint: false,
        selectFirst: false,
        changeWhenSelect: true,
        highlightMatches: false,
        ignoredKeyCode: [],
        customLabel: false,
        customValue: false,
        template: false,
        offset: false,
        combine: $.noop,
        callback: $.noop
    };

    var publics = {
        /**
         * @method
         * @name defaults
         * @description Sets default plugin options
         * @param opts [object] <{}> "Options object"
         * @example $.autocompleter('defaults', opts);
         */
        defaults: function (opts) {
            options = $.extend(options, opts || {});

            return (typeof this === 'object') ? $(this) : true;
        },

        /**
         * @method
         * @name option
         * @description Open autocompleter list
         */
        option: function (properties) {
            return $(this).each(function (i, input) {
                var data = $(input).next('.autocompleter').data('autocompleter');

                for (var property in properties) {
                    if ($.inArray(property, allowOptions) !== -1) {
                        data[property] = properties[property];
                    }
                }
            });
        },

        /**
         * @method
         * @name open
         * @description Open autocompleter list
         */
        open: function () {
            return $(this).each(function (i, input) {
                var data = $(input).next('.autocompleter').data('autocompleter');

                if (data) {
                    _open(null, data);
                }
            });
        },

        /**
         * @method
         * @name close
         * @description Close autocompleter list
         */
        close: function () {
            return $(this).each(function (i, input) {
                var data = $(input).next('.autocompleter').data('autocompleter');

                if (data) {
                    _close(null, data);
                }
            });
        },

        /**
         * @method
         * @name clearCache
         * @description Remove localStorage cache
         */
        clearCache: function () {
            _deleteCache();
        },

        /**
         * @method
         * @name destroy
         * @description Removes instance of plugin
         * @example $('.target').autocompleter('destroy');
         */
        destroy: function () {
            return $(this).each(function (i, input) {
                var data = $(input).next('.autocompleter').data('autocompleter');

                if (data) {
                    // Abort xhr
                    if (data.jqxhr) {
                        data.jqxhr.abort();
                    }

                    // If has selected item & open - confirm it
                    if (data.$autocompleter.hasClass('open')) {
                        data.$autocompleter.find('.autocompleter-selected')
                                           .trigger('click.autocompleter');
                    }

                    // Restore original autocomplete attr
                    if (!data.originalAutocomplete) {
                        data.$node.removeAttr('autocomplete');
                    } else {
                        data.$node.attr('autocomplete', data.originalAutocomplete);
                    }

                    // Remove autocompleter & unbind events
                    data.$node.off('.autocompleter')
                               .removeClass('autocompleter-node');
                    data.$autocompleter.off('.autocompleter')
                                         .remove();
                }
            });
        }
    };

    /**
     * @method private
     * @name _init
     * @description Initializes plugin
     * @param opts [object] "Initialization options"
     */
    function _init(opts) {
        // Local options
        opts = $.extend({}, options, opts || {});

        // Check for Body
        if ($body === null) {
            $body = $('body');
        }

        // Apply to each element
        var $items = $(this);
        for (var i = 0, count = $items.length; i < count; i++) {
            _build($items.eq(i), opts);
        }

        return $items;
    }

    /**
     * @method private
     * @name _build
     * @description Builds each instance
     * @param $node [jQuery object] "Target jQuery object"
     * @param opts [object] <{}> "Options object"
     */
    function _build($node, opts) {
        if (!$node.hasClass('autocompleter-node')) {
            // Extend options
            opts = $.extend({}, opts, $node.data('autocompleter-options'));

            // Check for as local or .json file
            if (typeof opts.source === 'string' && (opts.source.slice(-5) === '.json' || opts.asLocal === true)) {
                $.ajax({
                    url: opts.source,
                    type: 'GET',
                    dataType: 'json',
                    async: false
                }).done(function (response) {
                    opts.source = response;
                });
            }

            var html = '<div class="autocompleter ' + opts.customClass.join(' ') + '" id="autocompleter-' + (guid + 1) + '">';

            if (opts.hint) {
                html += '<div class="autocompleter-hint"></div>';
            }

            html += '<ul class="autocompleter-list"></ul>';
            html += '</div>';

            $node.addClass('autocompleter-node')
                 .after(html);

            var $autocompleter = $node.next('.autocompleter').eq(0);

            // Set autocomplete to off for warn overlay
            var originalAutocomplete = $node.attr('autocomplete');
            $node.attr('autocomplete', 'off');

            // Store plugin data
            var data = $.extend({
                $node: $node,
                $autocompleter: $autocompleter,
                $selected: null,
                $list: null,
                index: -1,
                hintText: false,
                source: false,
                jqxhr: false,
                response: null,
                focused: false,
                query: '',
                originalAutocomplete: originalAutocomplete,
                guid: guid++
            }, opts);

            // Bind autocompleter events
            data.$autocompleter.on('mousedown.autocompleter', '.autocompleter-item', data, _select)
                               .data('autocompleter', data);

            // Bind node events
            data.$node.on('keyup.autocompleter', data, _onKeyup)
                      .on('keydown.autocompleter', data, _onKeydown)
                      .on('focus.autocompleter', data, _onFocus)
                      .on('blur.autocompleter', data, _onBlur)
                      .on('mousedown.autocompleter', data, _onMousedown);
        }
    }

    /**
     * @method private
     * @name _search
     * @description Local search function, return best collation
     * @param query [string] "Query string"
     * @param source [object] "Source data"
     * @param data [object] "Instance data"
     */
    function _search(query, source, data) {
        var response = [];

        query = query.toUpperCase();

        if (source.length) {
            for (var i = 0; i < 2; i++) {
                for (var item in source) {
                    if (response.length < data.limit) {
                        var label = (data.customLabel && source[item][data.customLabel]) ? source[item][data.customLabel] : source[item].label;

                        switch (i) {
                        case 0:
                            if (label.toUpperCase().search(query) === 0) {
                                response.push(source[item]);
                                delete source[item];
                            }
                            break;

                        case 1:
                            if (label.toUpperCase().search(query) !== -1) {
                                response.push(source[item]);
                                delete source[item];
                            }
                            break;
                        }
                    }
                }
            }
        }

        return response;
    }

    /**
     * @method private
     * @name _launch
     * @description Launch autocompleter
     * @param data [object] "Instance data"
     */
    function _launch(data) {
        // Clear previous timeout
        clearTimeout(delayTimeout);

        data.query = $.trim(data.$node.val());

        if ((!data.empty && data.query.length === 0) || (data.minLength && (data.query.length < data.minLength))) {
            _clear(data);
            return;
        }

        if (data.delay) {
            // Be careful: delay used also with local source
            delayTimeout = setTimeout(function () { _xhr(data); }, data.delay);
        } else {
            _xhr(data);
        }
    }

    /**
     * @method private
     * @name _xhr
     * @description Use source locally or create xhr
     * @param data [object] "Instance data"
     */
    function _xhr(data) {
        if (typeof data.source === 'object') {
            _clear(data);

            // Local search
            var search = _search(data.query, _clone(data.source), data);

            if (search.length) {
                _response(search, data);
            }
        } else {
            if (data.jqxhr) {
                data.jqxhr.abort();
            }

            var ajaxData = $.extend({
                limit: data.limit,
                query: data.query
            }, data.combine(data.query));

            data.jqxhr = $.ajax({
                url:        data.source,
                dataType:   'json',
                crossDomain: true,
                data:       ajaxData,
                beforeSend: function (xhr) {
                    data.$autocompleter.addClass('autocompleter-ajax');
                    _clear(data);

                    if (data.cache) {
                        var stored = _getCache(this.url, data.cacheExpires);

                        if (stored) {
                            xhr.abort();
                            _response(stored, data);
                        }
                    }
                }
            })
            .done(function (response) {
                // Get subobject from responce
                if (data.offset) {
                    response = _grab(response, data.offset);
                }

                // Set cache
                if (data.cache) {
                    _setCache(this.url, response);
                }

                _response(response, data);
            })
            .always(function () {
                data.$autocompleter.removeClass('autocompleter-ajax');
            });
        }
    }

    /**
     * @method private
     * @name _clear
     * @param data [object] "Instance data"
     */
    function _clear(data) {
        // Clear data
        data.response = null;
        data.$list = null;
        data.$selected = null;
        data.index = 0;
        data.$autocompleter.find('.autocompleter-list').empty();
        data.$autocompleter.find('.autocompleter-hint').removeClass('autocompleter-hint-show').empty();
        data.hintText = false;

        _close(null, data);
    }

    /**
     * @method private
     * @name _response
     * @description Main source response function
     * @param response [object] "Source data"
     * @param data [object] "Instance data"
     */
    function _response(response, data) {
        _buildList(response, data);

        if (data.$autocompleter.hasClass('autocompleter-focus')) {
            _open(null, data);
        }
    }

    /**
     * @method private
     * @name _buildList
     * @description Generate autocompleter-list and update instance data by source
     * @param list [object] "Source data"
     * @param data [object] "Instance data"
     */
    function _buildList(list, data) {
        var menu = '';

        for (var item = 0, count = list.length; item < count; item++) {
            var classes = ['autocompleter-item'],
                highlightReg = new RegExp(data.query, 'gi');

            if (data.selectFirst && item === 0 && !data.changeWhenSelect) {
                classes.push('autocompleter-item-selected');
            }

            var label = (data.customLabel && list[item][data.customLabel]) ? list[item][data.customLabel] : list[item].label,
                clear = label;

            label = data.highlightMatches ? label.replace(highlightReg, '<strong>$&</strong>') : label;

            var value = (data.customValue && list[item][data.customValue]) ? list[item][data.customValue] : list[item].value;

            // Apply custom template
            if (data.template) {
                var template = data.template.replace(/({{ label }})/gi, label);

                for (var property in list[item]) {
                    if (list[item].hasOwnProperty(property)) {
                        var regex = new RegExp('{{ ' + property + ' }}', 'gi');

                        template = template.replace(regex, list[item][property]);
                    }
                }

                label = template;
            }

            if (value) {
                menu += '<li data-value="' + value + '" data-label="' + clear + '" class="' + classes.join(' ') + '">' + label + '</li>';
            } else {
                menu += '<li data-label="' + clear + '" class="' + classes.join(' ') + '">' + label + '</li>';
            }
        }

        // Set hint
        if (list.length && data.hint) {
            var hintLabel = (data.customLabel && list[0][data.customLabel]) ? list[0][data.customLabel] : list[0].label,
                hint = ( hintLabel.substr(0, data.query.length).toUpperCase() === data.query.toUpperCase() ) ? hintLabel : false;

            if (hint && (data.query !== hintLabel)) {
                var hintReg = new RegExp(data.query, 'i');
                var hintText = hint.replace(hintReg, '<span>' + data.query + '</span>');

                data.$autocompleter.find('.autocompleter-hint').addClass('autocompleter-hint-show').html(hintText);
                data.hintText = hintText;
            }
        }

        // Update data
        data.response = list;
        data.$autocompleter.find('.autocompleter-list').html(menu);
        data.$selected = (data.$autocompleter.find('.autocompleter-item-selected').length) ? data.$autocompleter.find('.autocompleter-item-selected') : null;
        data.$list = (list.length) ? data.$autocompleter.find('.autocompleter-item') : null;
        data.index = data.$selected ? data.$list.index(data.$selected) : -1;
        data.$autocompleter.find('.autocompleter-item').each(function (i, j) {
            $(j).data(data.response[i]);
        });
    }

    /**
     * @method private
     * @name _onKeyup
     * @description Keyup events in node, up/down autocompleter-list navigation, typing and enter button callbacks
     * @param e [object] "Event data"
     */
    function _onKeyup(e) {
        var data = e.data,
            code = e.keyCode ? e.keyCode : e.which;

        if ( (code === 40 || code === 38) && data.$autocompleter.hasClass('autocompleter-show') ) {
            // Arrows up & down
            var len = data.$list.length,
                next,
                prev;

            if (len) {
                // Determine new index
                if (len > 1) {
                    if (data.index === len - 1) {
                        next = data.changeWhenSelect ? -1 : 0;
                        prev = data.index - 1;
                    } else if (data.index === 0) {
                        next = data.index + 1;
                        prev = data.changeWhenSelect ? -1 : len - 1;
                    } else if (data.index === -1) {
                        next = 0;
                        prev = len - 1;
                    } else {
                        next = data.index + 1;
                        prev = data.index - 1;
                    }
                } else if (data.index === -1) {
                    next = 0;
                    prev = 0;
                } else {
                    prev = -1;
                    next = -1;
                }

                data.index = (code === 40) ? next : prev;

                // Update HTML
                data.$list.removeClass('autocompleter-item-selected');

                if (data.index !== -1) {
                    data.$list.eq(data.index).addClass('autocompleter-item-selected');
                }

                data.$selected = data.$autocompleter.find('.autocompleter-item-selected').length ? data.$autocompleter.find('.autocompleter-item-selected') : null;

                if (data.changeWhenSelect) {
                    _setValue(data);
                }
            }
        } else if ($.inArray(code, ignoredKeyCode) === -1 && $.inArray(code, data.ignoredKeyCode) === -1) {
            // Typing
            _launch(data);
        }
    }

    /**
     * @method private
     * @name _onKeydown
     * @description Keydown events in node, up/down for prevent cursor moving and right arrow for hint
     * @param e [object] "Event data"
     */
    function _onKeydown(e) {
        var data = e.data,
            code = e.keyCode ? e.keyCode : e.which;

        if (code === 40 || code === 38 ) {
            e.preventDefault();
            e.stopPropagation();
        } else if (code === 39) {
            // Right arrow
            if (data.hint && data.hintText && data.$autocompleter.find('.autocompleter-hint').hasClass('autocompleter-hint-show')) {
                e.preventDefault();
                e.stopPropagation();

                var hintOrigin = data.$autocompleter.find('.autocompleter-item').length ? data.$autocompleter.find('.autocompleter-item').eq(0).attr('data-label') : false;

                if (hintOrigin) {
                    data.query = hintOrigin;
                    _setHint(data);
                }
            }
        } else if (code === 13) {
            // Enter
            if (data.$autocompleter.hasClass('autocompleter-show') && data.$selected) {
                _select(e);
            }
        }
    }

    /**
     * @method private
     * @name _onFocus
     * @description Handles instance focus
     * @param e [object] "Event data"
     * @param internal [boolean] "Called by plugin"
     */
    function _onFocus(e, internal) {
        if (!internal) {
            var data = e.data;

            data.$autocompleter.addClass('autocompleter-focus');

            if (!data.$node.prop('disabled') && !data.$autocompleter.hasClass('autocompleter-show')) {
                if (data.focusOpen) {
                    _launch(data);
                    data.focused = true;

                    setTimeout(function () {
                        data.focused = false;
                    }, 500);
                }
            }
        }
    }

    /**
     * @method private
     * @name _onBlur
     * @description Handles instance blur
     * @param e [object] "Event data"
     * @param internal [boolean] "Called by plugin"
     */
    function _onBlur(e, internal) {
        e.preventDefault();
        e.stopPropagation();

        var data = e.data;

        if (!internal) {
            data.$autocompleter.removeClass('autocompleter-focus');
            _close(e);
        }
    }

    /**
     * @method private
     * @name _onMousedown
     * @description Handles mousedown to node
     * @param e [object] "Event data"
     */
    function _onMousedown(e) {
        // Disable middle & right mouse click
        if (e.type === 'mousedown' && $.inArray(e.which, [2, 3]) !== -1) { return; }

        var data = e.data;

        if (data.$list && !data.focused) {
            if (!data.$node.is(':disabled')) {
                if (isMobile && !isFirefoxMobile) {
                    var el = data.$select[0];

                    if (window.document.createEvent) { // All
                        var evt = window.document.createEvent('MouseEvents');
                        evt.initMouseEvent('mousedown', false, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                        el.dispatchEvent(evt);
                    } else if (el.fireEvent) { // IE
                        el.fireEvent('onmousedown');
                    }
                } else {
                    // Delegate intent
                    if (data.$autocompleter.hasClass('autocompleter-closed')) {
                        _open(e);
                    } else if (data.$autocompleter.hasClass('autocompleter-show')) {
                        _close(e);
                    }
                }
            }
        }
    }

    /**
     * @method private
     * @name _open
     * @description Opens option set
     * @param e [object] "Event data"
     * @param instanceData [object] "Instance data"
     */
    function _open(e, instanceData) {
        var data = e ? e.data : instanceData;

        if (!data.$node.prop('disabled') && !data.$autocompleter.hasClass('autocompleter-show') && data.$list && data.$list.length) {
            data.$autocompleter.removeClass('autocompleter-closed').addClass('autocompleter-show');
            $body.on('click.autocompleter-' + data.guid, ':not(.autocompleter-item)', data, _closeHelper);
        }
    }

    /**
     * @method private
     * @name _closeHelper
     * @description Determines if event target is outside instance before closing
     * @param e [object] "Event data"
     */
    function _closeHelper(e) {
        if ( $(e.target).hasClass('autocompleter-node') ) {
            return;
        }

        if ($(e.currentTarget).parents('.autocompleter').length === 0) {
            _close(e);
        }
    }

    /**
     * @method private
     * @name _close
     * @description Closes option set
     * @param e [object] "Event data"
     * @param instanceData [object] "Instance data"
     */
    function _close(e, instanceData) {
        var data = e ? e.data : instanceData;

        if (data.$autocompleter.hasClass('autocompleter-show')) {
            data.$autocompleter.removeClass('autocompleter-show').addClass('autocompleter-closed');
            $body.off('.autocompleter-' + data.guid);
        }
    }

    /**
     * @method private
     * @name _select
     * @description Select item from .autocompleter-list
     * @param e [object] "Event data"
     */
    function _select(e) {
        // Disable middle & right mouse click
        if (e.type === 'mousedown' && $.inArray(e.which, [2, 3]) !== -1) {
            return;
        }

        var data = e.data;

        e.preventDefault();
        e.stopPropagation();

        if (e.type === 'mousedown' && $(this).length) {
            data.$selected = $(this);
            data.index = data.$list.index(data.$selected);
        }

        if (!data.$node.prop('disabled')) {
            _close(e);
            _update(data);

            if (e.type === 'click') {
                data.$node.trigger('focus', [ true ]);
            }
        }
    }

    /**
     * @method private
     * @name _setHint
     * @description Set autocompleter by hint
     * @param data [object] "Instance data"
     */
    function _setHint(data) {
        _setValue(data);
        _handleChange(data);
        _launch(data);
    }

    /**
     * @method private
     * @name _setValue
     * @description Set value for native field
     * @param data [object] "Instance data"
     */
    function _setValue(data) {
        if (data.$selected) {
            if (data.hintText && data.$autocompleter.find('.autocompleter-hint').hasClass('autocompleter-hint-show')) {
                data.$autocompleter.find('.autocompleter-hint').removeClass('autocompleter-hint-show');
            }

            data.$node.val(data.$selected.attr('data-value') ? data.$selected.attr('data-value') : data.$selected.attr('data-label'));
        } else {
            if (data.hintText && !data.$autocompleter.find('.autocompleter-hint').hasClass('autocompleter-hint-show')) {
                data.$autocompleter.find('.autocompleter-hint').addClass('autocompleter-hint-show');
            }

            data.$node.val(data.query);
        }
    }

    /**
     * @method private
     * @name _update
     * @param data [object] "Instance data"
     */
    function _update(data) {
        _setValue(data);
        _handleChange(data);
        _clear(data);
    }

    /**
     * @method private
     * @name _handleChange
     * @description Trigger node change event and call the callback function
     * @param data [object] "Instance data"
     */
    function _handleChange(data) {
        data.callback.call(data.$autocompleter, data.$node.val(), data.index, data.response[data.index]);
        data.$node.trigger('change');
    }

    /**
     * @method private
     * @name _grab
     * @description Grab sub-object from object
     * @param response [object] "Native object"
     * @param offset [string] "Offset string"
     */
    function _grab(response, offset) {
        offset = offset.split('.');

        while (response && offset.length) {
            response = response[offset.shift()];
        }

        return response;
    }

    /**
     * @method private
     * @name _setCache
     * @description Store AJAX response in plugin cache
     * @param url [string] "AJAX get query string"
     * @param data [object] "AJAX response data"
     */
    function _setCache(url, data) {
        if (!supportLocalStorage) {
            return;
        }

        if (url && data) {
            cache[url] = {
                value: data,
                timestamp: +new Date()
            };

            // Proccess to localStorage
            try {
                localStorage.setItem(localStorageKey, JSON.stringify(cache));
            } catch (e) {
                var code = e.code || e.number || e.message;

                if (code === 22) {
                    _deleteCache();
                } else {
                    throw(e);
                }
            }
        }
    }

    /**
     * @method private
     * @name _getCache
     * @description Get cached data by url if exist
     * @param url [string] "AJAX get query string"
     */
    function _getCache(url, expires) {
        var response = false,
            item;

        expires = expires || false;

        if (!url) {
            return false;
        }

        item = cache[url];

        if (item && item.value) {
            response = item.value;

            if (item.timestamp && expires && (+new Date() - item.timestamp > expires * 1000)) {
                return false;
            } else {
                return response;
            }
        } else {
            return false;
        }
    }

    /**
     * @method private
     * @name _loadCache
     * @description Load all plugin cache from localStorage
     */
    function _loadCache() {
        if (!supportLocalStorage) {
            return;
        }

        return JSON.parse(localStorage.getItem(localStorageKey) || '{}');
    }

    /**
     * @method private
     * @name _deleteCache
     * @description Delete all plugin cache from localStorage
     */
    function _deleteCache() {
        try {
            localStorage.removeItem(localStorageKey);
            cache = _loadCache();
        } catch (e) {
            throw(e);
        }
    }

    /**
     * @method private
     * @name _clone
     * @description Clone JavaScript object
     */
    function _clone(obj) {
        var copy;

        if (null === obj || 'object' !== typeof obj) {
            return obj;
        }

        copy = obj.constructor();

        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) {
                copy[attr] = obj[attr];
            }
        }

        return copy;
    }

    // Load cache
    var cache = _loadCache();

    $.fn.autocompleter = function (method) {
        if (publics[method]) {
            return publics[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return _init.apply(this, arguments);
        }
        return this;
    };

    $.autocompleter = function (method) {
        if (method === 'defaults') {
            publics.defaults.apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (method === 'clearCache') {
            publics.clearCache.apply(this, null);
        }
    };
})(jQuery, window);