//  ----------------------------------------------------------------------------
//
//  bootstrap-typeahead.js  
//
//  Twitter Bootstrap Typeahead Plugin
//  v1.2.2
//  https://github.com/tcrosen/twitter-bootstrap-typeahead
//
//
//  Author
//  ----------
//  Terry Rosen
//  tcrosen@gmail.com | @rerrify | github.com/tcrosen/
//
//
//  Description
//  ----------
//  Custom implementation of Twitter's Bootstrap Typeahead Plugin
//  http://twitter.github.com/bootstrap/javascript.html#typeahead
//
//
//  Requirements
//  ----------
//  jQuery 1.7+
//  Twitter Bootstrap 2.0+
//
//  ----------------------------------------------------------------------------

!
function ($) {

    "use strict";

    //------------------------------------------------------------------
    //
    //  Constructor
    //
    var Typeahead = function (element, options) {
        this.$element = $(element);
		
        this.options = $.extend(true, {}, $.fn.typeahead.defaults, options);
        this.$menu = $(this.options.menu).appendTo(this.$element.parent());
        this.shown = false;

        // Method overrides    
        this.eventSupported = this.options.eventSupported || this.eventSupported;
        this.grepper = this.options.grepper || this.grepper;
        this.highlighter = this.options.highlighter || this.highlighter;
        this.lookup = this.options.lookup || this.lookup;
        this.matcher = this.options.matcher || this.matcher;
        this.render = this.options.render || this.render;
        this.select = this.options.select || this.select;
        this.sorter = this.options.sorter || this.sorter;
        this.source = this.options.source || this.source;        
        this.inputval=this.options.taginput || null;
       // if (!this.source.length) {
            var ajax = this.options.ajax;

            if (typeof ajax === 'string') {
                this.ajax = $.extend({}, $.fn.typeahead.defaults.ajax, { url: ajax });
            } else {
                this.ajax = $.extend({}, $.fn.typeahead.defaults.ajax, ajax);
            }

            if (!this.ajax.url) {
                this.ajax = null;
            }
       // }

        this.listen();        
    }

    Typeahead.prototype = {

        constructor: Typeahead,

        //=============================================================================================================
        //
        //  Utils
        //
        //=============================================================================================================

        //------------------------------------------------------------------
        //
        //  Check if an event is supported by the browser eg. 'keypress'
        //  * This was included to handle the "exhaustive deprecation" of jQuery.browser in jQuery 1.8
        //
        eventSupported: function(eventName) {         
            var isSupported = (eventName in this.$element);

            if (!isSupported) {
              this.$element.setAttribute(eventName, 'return;');
              isSupported = typeof this.$element[eventName] === 'function';
            }

            return isSupported;
        },

        //=============================================================================================================
        //
        //  AJAX
        //
        //=============================================================================================================

        //------------------------------------------------------------------
        //
        //  Handle AJAX source 
        //
        ajaxer: function () { 
            var that = this,
                query = that.$element.val();
            if (query === that.query) {
				 if (this.ajax.preProcess) {
					data = this.ajax.preProcess(this.ajax.data);
				}
	
				// Save for selection retreival
				var items = this.grepper(this.ajax.data);
				if (!items || !items.length) {
					return this.shown ? this.hide() : this;
				}
				return this.render(items.slice(0, this.options.items)).show();
            }
    
            // Query changed
            that.query = query;

            // Cancel last timer if set
            if (that.ajax.timerId) {
                clearTimeout(that.ajax.timerId);
                that.ajax.timerId = null;
            }
            
            if ( query.length < that.ajax.triggerLength) {
                // Cancel the ajax callback if in progress
                if (that.ajax.xhr) {
                    that.ajax.xhr.abort();
                    that.ajax.xhr = null;
                    that.ajaxToggleLoadClass(false);
                }

                return that.shown ? that.hide() : that;
            }
                    
            // Query is good to send, set a timer
            that.ajax.timerId = setTimeout(function() {
                $.proxy(that.ajaxExecute(query), that)
            }, that.ajax.timeout);
                    
            return that;
        },

        //------------------------------------------------------------------
        //
        //  Execute an AJAX request
        //
        ajaxExecute: function(query) {
            this.ajaxToggleLoadClass(true);
            
            // Cancel last call if already in progress
            if (this.ajax.xhr) this.ajax.xhr.abort();
            
            var params = this.ajax.preDispatch ? this.ajax.preDispatch(query) : { query : query };
            var jAjax = (this.ajax.method === "post") ? $.post : $.get;
            this.ajax.xhr = jAjax(this.ajax.url, params, $.proxy(this.ajaxLookup, this),'json');
            this.ajax.timerId = null;
        },
    
        //------------------------------------------------------------------
        //
        //  Perform a lookup in the AJAX results
        //
        ajaxLookup: function (data) { 
            var items;
            
            this.ajaxToggleLoadClass(false);

            if (!this.ajax.xhr) return;
            
            if (this.ajax.preProcess) {
                data = this.ajax.preProcess(data);
            }

            // Save for selection retreival
            this.ajax.data = data;
            items = this.grepper(this.ajax.data);
            if (!items || !items.length) {
                return this.shown ? this.hide() : this;
            }

            this.ajax.xhr = null;

            return this.render(items.slice(0, this.options.items)).show();
        },
        
        //------------------------------------------------------------------
        //
        //  Toggle the loading class
        //
        ajaxToggleLoadClass: function (enable) {
            if (!this.ajax.loadingClass) return;
            this.$element.toggleClass(this.ajax.loadingClass, enable);
        },

        //=============================================================================================================
        //
        //  Data manipulation
        //
        //=============================================================================================================

        //------------------------------------------------------------------
        //
        //  Search source
        //
        lookup: function (event) {
            var that = this,
                items;

            if (that.ajax) {
                that.ajaxer();
            }
            else {
                that.query = that.$element.val();

                if (!that.query) {
                   // return that.shown ? that.hide() : that;
                }
               
                items = that.grepper(that.source);
               
                if (!items || !items.length) {
                    return that.shown ? that.hide() : that;
                }

                return that.render(items.slice(0, that.options.items)).show();
            }
        },

        //------------------------------------------------------------------
        //
        //  Filters relevent results 
        //
        grepper: function(data) {
            var that = this,
                items;
            if (data && data.length && !data[0].hasOwnProperty(that.options.display)) {                
                return null;
            } 

            items = $.grep(data, function (item) {
                return that.matcher(item[that.options.display], item);
            });

            return this.sorter(items);                
        },

        //------------------------------------------------------------------
        //
        //  Looks for a match in the source
        //
        matcher: function (item) {
			if(this.inputval){
				var vals=this.inputval.val().split(',');
				if($.inArray(item,vals)>-1) return 0;
			}
            return ~item.toLowerCase().indexOf(this.query.toLowerCase());
        },

        //------------------------------------------------------------------
        //
        //  Sorts the results
        //
        sorter: function (items) {
            var that = this,
                beginswith = [],
                caseSensitive = [],
                caseInsensitive = [],
                item;

            while (item = items.shift()) {
                if (!item[that.options.display].toLowerCase().indexOf(this.query.toLowerCase())) {
                    beginswith.push(item);
                }
                else if (~item[that.options.display].indexOf(this.query)) {
                    caseSensitive.push(item);
                }
                else {
                    caseInsensitive.push(item);
                }
            }

            return beginswith.concat(caseSensitive, caseInsensitive);
        },       

        //=============================================================================================================
        //
        //  DOM manipulation
        //
        //=============================================================================================================

        //------------------------------------------------------------------
        //
        //  Shows the results list
        //
        show: function () {
            var pos = $.extend({}, this.$element.position(), {
                height: this.$element[0].offsetHeight
            });

            this.$menu.css({
                top: pos.top + pos.height,
                left: pos.left
            });

            this.$menu.show();
            this.shown = true;

            return this;
        },

        //------------------------------------------------------------------
        //
        //  Hides the results list
        //
        hide: function () {
            this.$menu.hide();
            this.shown = false;
            return this;
        },
        
        //------------------------------------------------------------------
        //
        //  Highlights the match(es) within the results
        //
        highlighter: function (item) {
            var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
            return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
                return '<strong>' + match + '</strong>';
            });
        },

        //------------------------------------------------------------------
        //
        //  Renders the results list
        //
        render: function (items) {
            var that = this;
				
            items = $(items).map(function (i, item) {
                i = $(that.options.item).attr('data-value', item[that.options.val]);
                i.find('a').html(that.highlighter(item[that.options.display], item));
                return i[0];
            });

            items.first().addClass('active');
            this.$menu.html(items);
            return this;
        },

        //------------------------------------------------------------------
        //
        //  Item is selected
        //
        select: function () {
            var $selectedItem = this.$menu.find('.active');
            this.$element.val($selectedItem.text()).trigger('change');
            this.options.itemSelected($selectedItem, $selectedItem.attr('data-value'), $selectedItem.text());
            return this.hide();
        },

        //------------------------------------------------------------------
        //
        //  Selects the next result
        //
        next: function (event) {
            var active = this.$menu.find('.active').removeClass('active');
            var next = active.next();

            if (!next.length) {
                next = $(this.$menu.find('li')[0]);
            }

            next.addClass('active');
        },

        //------------------------------------------------------------------
        //
        //  Selects the previous result
        //
        prev: function (event) {
            var active = this.$menu.find('.active').removeClass('active');
            var prev = active.prev();

            if (!prev.length) {
                prev = this.$menu.find('li').last();
            }

            prev.addClass('active');
        },

        //=============================================================================================================
        //
        //  Events
        //
        //=============================================================================================================

        //------------------------------------------------------------------
        //
        //  Listens for user events
        //
        listen: function () {
			
            this.$element.on('blur', $.proxy(this.blur, this))
                         .on('keyup', $.proxy(this.keyup, this))
						 .on('click', $.proxy(this.lookup, this));

    		if (this.eventSupported('keydown')) {
				this.$element.on('keydown', $.proxy(this.keypress, this));
			} else {
				this.$element.on('keypress', $.proxy(this.keypress, this));
			}

            this.$menu.on('click', $.proxy(this.click, this))
                      .end().on('mouseenter', 'li', $.proxy(this.mouseenter, this));
        },

        //------------------------------------------------------------------
        //
        //  Handles a key being raised up
        //
        keyup: function (e) {
            e.stopPropagation();
            e.preventDefault();

            switch (e.keyCode) {
                case 40:
                    // down arrow
                case 38:
                    // up arrow
                    break;
                case 9:
                    // tab
                case 13:
                    // enter
                    if (!this.shown) {
                        return;
                    }
                    this.select();
                    break;
                case 27:
                    // escape
                    this.hide();
                    break;
                default:
                    this.lookup();
            }
        },

        //------------------------------------------------------------------
        //
        //  Handles a key being pressed
        //
        keypress: function (e) {
            e.stopPropagation();
            if (!this.shown) {
                return;
            }
            switch (e.keyCode) {
                case 9:
                    // tab
                case 13:
                    // enter
                case 27:
                    // escape
                    e.preventDefault();
                    break;
                case 38:
                    // up arrow
                    e.preventDefault();
                    this.prev();
                    break;
                case 40:
                    // down arrow
                    e.preventDefault();
                    this.next();
                    break;
            }
        },

        //------------------------------------------------------------------
        //
        //  Handles cursor exiting the textbox
        //
        blur: function (e) {
            var that = this;
            e.stopPropagation();
            e.preventDefault();
            setTimeout(function () {
                if (!that.$menu.is(':focus')) {
                  that.hide();
                }
            }, 150)
        },

        //------------------------------------------------------------------
        //
        //  Handles clicking on the results list
        //
        click: function (e) {
            e.stopPropagation();
            e.preventDefault();
            this.select();
        },

        //------------------------------------------------------------------
        //
        //  Handles the mouse entering the results list
        //
        mouseenter: function (e) {
            this.$menu.find('.active').removeClass('active');
            $(e.currentTarget).addClass('active');
        }
    }

    //------------------------------------------------------------------
    //
    //  Plugin definition
    //
    $.fn.typeahead = function (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('typeahead'),
                options = typeof option === 'object' && option;

            if (!data) {
                $this.data('typeahead', (data = new Typeahead(this, options)));
            }

            if (typeof option === 'string') {
                data[option]();
            }
        });
    }

    //------------------------------------------------------------------
    //
    //  Defaults
    //
    $.fn.typeahead.defaults = {
        source: [],
        items: 8,
        menu: '<ul class="typeahead dropdown-menu"></ul>',
        item: '<li><a href="#"></a></li>',
        display: 'name',
        val: 'id',
        itemSelected: function (el,val,text) {},
        ajax: {
            url: null,
            timeout: 300,
            method: 'post',
            triggerLength: 3,
            loadingClass: null,
            displayField: null,
            preDispatch: null,
            preProcess: null
        }
    }

    $.fn.typeahead.Constructor = Typeahead;

    //------------------------------------------------------------------
    //
    //  DOM-ready call for the Data API (no-JS implementation)
    //    
    //  Note: As of Bootstrap v2.0 this feature may be disabled using $('body').off('.data-api')    
    //  More info here: https://github.com/twitter/bootstrap/tree/master/js
    //
    $(function () {
        $('body').on('focus.typeahead.data-api', '[data-provide="typeahead"]', function (e) {
            var $this = $(this);
			
            if ($this.data('typeahead')) {
                return;
            }

            e.preventDefault();
            $this.typeahead($this.data());
        })
    });

} (window.jQuery);
