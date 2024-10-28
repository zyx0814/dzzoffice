/*! WebUploader 1.0.0 */


/**
 * @fileOverview 让内部各个部件的代码可以用[amd](https://github.com/amdjs/amdjs-api/wiki/AMD)模块定义方式组织起来。
 *
 * AMD API 内部的简单不完全实现，请忽略。只有当WebUploader被合并成一个文件的时候才会引入。
 */
(function( root, factory ) {
    var modules = {},

        // 内部require, 简单不完全实现。
        // https://github.com/amdjs/amdjs-api/wiki/require
        _require = function( deps, callback ) {
            var args, len, i;

            // 如果deps不是数组，则直接返回指定module
            if ( typeof deps === 'string' ) {
                return getModule( deps );
            } else {
                args = [];
                for( len = deps.length, i = 0; i < len; i++ ) {
                    args.push( getModule( deps[ i ] ) );
                }

                return callback.apply( null, args );
            }
        },

        // 内部define，暂时不支持不指定id.
        _define = function( id, deps, factory ) {
            if ( arguments.length === 2 ) {
                factory = deps;
                deps = null;
            }

            _require( deps || [], function() {
                setModule( id, factory, arguments );
            });
        },

        // 设置module, 兼容CommonJs写法。
        setModule = function( id, factory, args ) {
            var module = {
                    exports: factory
                },
                returned;

            if ( typeof factory === 'function' ) {
                args.length || (args = [ _require, module.exports, module ]);
                returned = factory.apply( null, args );
                returned !== undefined && (module.exports = returned);
            }

            modules[ id ] = module.exports;
        },

        // 根据id获取module
        getModule = function( id ) {
            var module = modules[ id ] || root[ id ];

            if ( !module ) {
                throw new Error( '`' + id + '` is undefined' );
            }

            return module;
        },

        // 将所有modules，将路径ids装换成对象。
        exportsTo = function( obj ) {
            var key, host, parts, part, last, ucFirst;

            // make the first character upper case.
            ucFirst = function( str ) {
                return str && (str.charAt( 0 ).toUpperCase() + str.substr( 1 ));
            };

            for ( key in modules ) {
                host = obj;

                if ( !modules.hasOwnProperty( key ) ) {
                    continue;
                }

                parts = key.split('/');
                last = ucFirst( parts.pop() );

                while( (part = ucFirst( parts.shift() )) ) {
                    host[ part ] = host[ part ] || {};
                    host = host[ part ];
                }

                host[ last ] = modules[ key ];
            }

            return obj;
        },

        makeExport = function( dollar ) {
            root.__dollar = dollar;

            // exports every module.
            return exportsTo( factory( root, _define, _require ) );
        },

        origin;

    if ( typeof module === 'object' && typeof module.exports === 'object' ) {

        // For CommonJS and CommonJS-like environments where a proper window is present,
        module.exports = makeExport();
    } else if ( typeof define === 'function' && define.amd ) {

        // Allow using this built library as an AMD module
        // in another project. That other project will only
        // see this AMD call, not the internal modules in
        // the closure below.
        define([ 'jquery' ], makeExport );
    } else {

        // Browser globals case. Just assign the
        // result to a property on the global.
        origin = root.WebUploader;
        root.WebUploader = makeExport();
        root.WebUploader.noConflict = function() {
            root.WebUploader = origin;
        };
    }
})( window, function( window, define, require ) {


    /**
     * @fileOverview jQuery or Zepto
     * @require "jquery"
     * @require "zepto"
     */
    define('dollar-third',[],function() {
        var req = window.require;
        var $ = window.__dollar || 
            window.jQuery || 
            window.Zepto || 
            req('jquery') || 
            req('zepto');
    
        if ( !$ ) {
            throw new Error('jQuery or Zepto not found!');
        }
    
        return $;
    });
    
    /**
     * @fileOverview Dom 操作相关
     */
    define('dollar',[
        'dollar-third'
    ], function( _ ) {
        return _;
    });
    /**
     * @fileOverview 使用jQuery的Promise
     */
    define('promise-third',[
        'dollar'
    ], function( $ ) {
        return {
            Deferred: $.Deferred,
            when: $.when,
    
            isPromise: function( anything ) {
                return anything && typeof anything.then === 'function';
            }
        };
    });
    /**
     * @fileOverview Promise/A+
     */
    define('promise',[
        'promise-third'
    ], function( _ ) {
        return _;
    });
    /**
     * @fileOverview 基础类方法。
     */
    
    /**
     * Web Uploader内部类的详细说明，以下提及的功能类，都可以在`WebUploader`这个变量中访问到。
     *
     * As you know, Web Uploader的每个文件都是用过[AMD](https://github.com/amdjs/amdjs-api/wiki/AMD)规范中的`define`组织起来的, 每个Module都会有个module id.
     * 默认module id为该文件的路径，而此路径将会转化成名字空间存放在WebUploader中。如：
     *
     * * module `base`：WebUploader.Base
     * * module `file`: WebUploader.File
     * * module `lib/dnd`: WebUploader.Lib.Dnd
     * * module `runtime/html5/dnd`: WebUploader.Runtime.Html5.Dnd
     *
     *
     * 以下文档中对类的使用可能省略掉了`WebUploader`前缀。
     * @module WebUploader
     * @title WebUploader API文档
     */
    define('base',[
        'dollar',
        'promise'
    ], function( $, promise ) {
    
        var noop = function() {},
            call = Function.call;
    
        // http://jsperf.com/uncurrythis
        // 反科里化
        function uncurryThis( fn ) {
            return function() {
                return call.apply( fn, arguments );
            };
        }
    
        function bindFn( fn, context ) {
            return function() {
                return fn.apply( context, arguments );
            };
        }
    
        function createObject( proto ) {
            var f;
    
            if ( Object.create ) {
                return Object.create( proto );
            } else {
                f = function() {};
                f.prototype = proto;
                return new f();
            }
        }
    
    
        /**
         * 基础类，提供一些简单常用的方法。
         * @class Base
         */
        return {
    
            /**
             * @property {String} version 当前版本号。
             */
            version: '1.0.0',
    
            /**
             * @property {jQuery|Zepto} $ 引用依赖的jQuery或者Zepto对象。
             */
            $: $,
    
            Deferred: promise.Deferred,
    
            isPromise: promise.isPromise,
    
            when: promise.when,
    
            /**
             * @description  简单的浏览器检查结果。
             *
             * * `webkit`  webkit版本号，如果浏览器为非webkit内核，此属性为`undefined`。
             * * `chrome`  chrome浏览器版本号，如果浏览器为chrome，此属性为`undefined`。
             * * `ie`  ie浏览器版本号，如果浏览器为非ie，此属性为`undefined`。**暂不支持ie10+**
             * * `firefox`  firefox浏览器版本号，如果浏览器为非firefox，此属性为`undefined`。
             * * `safari`  safari浏览器版本号，如果浏览器为非safari，此属性为`undefined`。
             * * `opera`  opera浏览器版本号，如果浏览器为非opera，此属性为`undefined`。
             *
             * @property {Object} [browser]
             */
            browser: (function( ua ) {
                var ret = {},
                    webkit = ua.match( /WebKit\/([\d.]+)/ ),
                    chrome = ua.match( /Chrome\/([\d.]+)/ ) ||
                        ua.match( /CriOS\/([\d.]+)/ ),
    
                    ie = ua.match( /MSIE\s([\d\.]+)/ ) ||
                        ua.match( /(?:trident)(?:.*rv:([\w.]+))?/i ),
                    firefox = ua.match( /Firefox\/([\d.]+)/ ),
                    safari = ua.match( /Safari\/([\d.]+)/ ),
                    opera = ua.match( /OPR\/([\d.]+)/ );
    
                webkit && (ret.webkit = parseFloat( webkit[ 1 ] ));
                chrome && (ret.chrome = parseFloat( chrome[ 1 ] ));
                ie && (ret.ie = parseFloat( ie[ 1 ] ));
                firefox && (ret.firefox = parseFloat( firefox[ 1 ] ));
                safari && (ret.safari = parseFloat( safari[ 1 ] ));
                opera && (ret.opera = parseFloat( opera[ 1 ] ));
    
                return ret;
            })( navigator.userAgent ),
    
            /**
             * @description  操作系统检查结果。
             *
             * * `android`  如果在android浏览器环境下，此值为对应的android版本号，否则为`undefined`。
             * * `ios` 如果在ios浏览器环境下，此值为对应的ios版本号，否则为`undefined`。
             * @property {Object} [os]
             */
            os: (function( ua ) {
                var ret = {},
    
                    // osx = !!ua.match( /\(Macintosh\; Intel / ),
                    android = ua.match( /(?:Android);?[\s\/]+([\d.]+)?/ ),
                    ios = ua.match( /(?:iPad|iPod|iPhone).*OS\s([\d_]+)/ );
    
                // osx && (ret.osx = true);
                android && (ret.android = parseFloat( android[ 1 ] ));
                ios && (ret.ios = parseFloat( ios[ 1 ].replace( /_/g, '.' ) ));
    
                return ret;
            })( navigator.userAgent ),
    
            /**
             * 实现类与类之间的继承。
             * @method inherits
             * @grammar Base.inherits( super ) => child
             * @grammar Base.inherits( super, protos ) => child
             * @grammar Base.inherits( super, protos, statics ) => child
             * @param  {Class} super 父类
             * @param  {Object | Function} [protos] 子类或者对象。如果对象中包含constructor，子类将是用此属性值。
             * @param  {Function} [protos.constructor] 子类构造器，不指定的话将创建个临时的直接执行父类构造器的方法。
             * @param  {Object} [statics] 静态属性或方法。
             * @return {Class} 返回子类。
             * @example
             * function Person() {
             *     console.log( 'Super' );
             * }
             * Person.prototype.hello = function() {
             *     console.log( 'hello' );
             * };
             *
             * var Manager = Base.inherits( Person, {
             *     world: function() {
             *         console.log( 'World' );
             *     }
             * });
             *
             * // 因为没有指定构造器，父类的构造器将会执行。
             * var instance = new Manager();    // => Super
             *
             * // 继承子父类的方法
             * instance.hello();    // => hello
             * instance.world();    // => World
             *
             * // 子类的__super__属性指向父类
             * console.log( Manager.__super__ === Person );    // => true
             */
            inherits: function( Super, protos, staticProtos ) {
                var child;
    
                if ( typeof protos === 'function' ) {
                    child = protos;
                    protos = null;
                } else if ( protos && protos.hasOwnProperty('constructor') ) {
                    child = protos.constructor;
                } else {
                    child = function() {
                        return Super.apply( this, arguments );
                    };
                }
    
                // 复制静态方法
                $.extend( true, child, Super, staticProtos || {} );
    
                /* jshint camelcase: false */
    
                // 让子类的__super__属性指向父类。
                child.__super__ = Super.prototype;
    
                // 构建原型，添加原型方法或属性。
                // 暂时用Object.create实现。
                child.prototype = createObject( Super.prototype );
                protos && $.extend( true, child.prototype, protos );
    
                return child;
            },
    
            /**
             * 一个不做任何事情的方法。可以用来赋值给默认的callback.
             * @method noop
             */
            noop: noop,
    
            /**
             * 返回一个新的方法，此方法将已指定的`context`来执行。
             * @grammar Base.bindFn( fn, context ) => Function
             * @method bindFn
             * @example
             * var doSomething = function() {
             *         console.log( this.name );
             *     },
             *     obj = {
             *         name: 'Object Name'
             *     },
             *     aliasFn = Base.bind( doSomething, obj );
             *
             *  aliasFn();    // => Object Name
             *
             */
            bindFn: bindFn,
    
            /**
             * 引用Console.log如果存在的话，否则引用一个[空函数noop](#WebUploader:Base.noop)。
             * @grammar Base.log( args... ) => undefined
             * @method log
             */
            log: (function() {
                if ( window.console ) {
                    return bindFn( console.log, console );
                }
                return noop;
            })(),
    
            nextTick: (function() {
    
                return function( cb ) {
                    setTimeout( cb, 1 );
                };
    
                // @bug 当浏览器不在当前窗口时就停了。
                // var next = window.requestAnimationFrame ||
                //     window.webkitRequestAnimationFrame ||
                //     window.mozRequestAnimationFrame ||
                //     function( cb ) {
                //         window.setTimeout( cb, 1000 / 60 );
                //     };
    
                // // fix: Uncaught TypeError: Illegal invocation
                // return bindFn( next, window );
            })(),
    
            /**
             * 被[uncurrythis](http://www.2ality.com/2011/11/uncurrying-this.html)的数组slice方法。
             * 将用来将非数组对象转化成数组对象。
             * @grammar Base.slice( target, start[, end] ) => Array
             * @method slice
             * @example
             * function doSomthing() {
             *     var args = Base.slice( arguments, 1 );
             *     console.log( args );
             * }
             *
             * doSomthing( 'ignored', 'arg2', 'arg3' );    // => Array ["arg2", "arg3"]
             */
            slice: uncurryThis( [].slice ),
    
            /**
             * 生成唯一的ID
             * @method guid
             * @grammar Base.guid() => String
             * @grammar Base.guid( prefx ) => String
             */
            guid: (function() {
                var counter = 0;
    
                return function( prefix ) {
                    var guid = (+new Date()).toString( 32 ),
                        i = 0;
    
                    for ( ; i < 5; i++ ) {
                        guid += Math.floor( Math.random() * 65535 ).toString( 32 );
                    }
    
                    return (prefix || 'wu_') + guid + (counter++).toString( 32 );
                };
            })(),
    
            /**
             * 格式化文件大小, 输出成带单位的字符串
             * @method formatSize
             * @grammar Base.formatSize( size ) => String
             * @grammar Base.formatSize( size, pointLength ) => String
             * @grammar Base.formatSize( size, pointLength, units ) => String
             * @param {Number} size 文件大小
             * @param {Number} [pointLength=2] 精确到的小数点数。
             * @param {Array} [units=[ 'B', 'K', 'M', 'G', 'TB' ]] 单位数组。从字节，到千字节，一直往上指定。如果单位数组里面只指定了到了K(千字节)，同时文件大小大于M, 此方法的输出将还是显示成多少K.
             * @example
             * console.log( Base.formatSize( 100 ) );    // => 100B
             * console.log( Base.formatSize( 1024 ) );    // => 1.00K
             * console.log( Base.formatSize( 1024, 0 ) );    // => 1K
             * console.log( Base.formatSize( 1024 * 1024 ) );    // => 1.00M
             * console.log( Base.formatSize( 1024 * 1024 * 1024 ) );    // => 1.00G
             * console.log( Base.formatSize( 1024 * 1024 * 1024, 0, ['B', 'KB', 'MB'] ) );    // => 1024MB
             */
            formatSize: function( size, pointLength, units ) {
                var unit;
    
                units = units || [ 'B', 'K', 'M', 'G', 'TB' ];
    
                while ( (unit = units.shift()) && size > 1024 ) {
                    size = size / 1024;
                }
    
                return (unit === 'B' ? size : size.toFixed( pointLength || 2 )) +
                        unit;
            }
        };
    });
    /**
     * 事件处理类，可以独立使用，也可以扩展给对象使用。
     * @fileOverview Mediator
     */
    define('mediator',[
        'base'
    ], function( Base ) {
        var $ = Base.$,
            slice = [].slice,
            separator = /\s+/,
            protos;
    
        // 根据条件过滤出事件handlers.
        function findHandlers( arr, name, callback, context ) {
            return $.grep( arr, function( handler ) {
                return handler &&
                        (!name || handler.e === name) &&
                        (!callback || handler.cb === callback ||
                        handler.cb._cb === callback) &&
                        (!context || handler.ctx === context);
            });
        }
    
        function eachEvent( events, callback, iterator ) {
            // 不支持对象，只支持多个event用空格隔开
            $.each( (events || '').split( separator ), function( _, key ) {
                iterator( key, callback );
            });
        }
    
        function triggerHanders( events, args ) {
            var stoped = false,
                i = -1,
                len = events.length,
                handler;
    
            while ( ++i < len ) {
                handler = events[ i ];
    
                if ( handler.cb.apply( handler.ctx2, args ) === false ) {
                    stoped = true;
                    break;
                }
            }
    
            return !stoped;
        }
    
        protos = {
    
            /**
             * 绑定事件。
             *
             * `callback`方法在执行时，arguments将会来源于trigger的时候携带的参数。如
             * ```javascript
             * var obj = {};
             *
             * // 使得obj有事件行为
             * Mediator.installTo( obj );
             *
             * obj.on( 'testa', function( arg1, arg2 ) {
             *     console.log( arg1, arg2 ); // => 'arg1', 'arg2'
             * });
             *
             * obj.trigger( 'testa', 'arg1', 'arg2' );
             * ```
             *
             * 如果`callback`中，某一个方法`return false`了，则后续的其他`callback`都不会被执行到。
             * 切会影响到`trigger`方法的返回值，为`false`。
             *
             * `on`还可以用来添加一个特殊事件`all`, 这样所有的事件触发都会响应到。同时此类`callback`中的arguments有一个不同处，
             * 就是第一个参数为`type`，记录当前是什么事件在触发。此类`callback`的优先级比脚低，会再正常`callback`执行完后触发。
             * ```javascript
             * obj.on( 'all', function( type, arg1, arg2 ) {
             *     console.log( type, arg1, arg2 ); // => 'testa', 'arg1', 'arg2'
             * });
             * ```
             *
             * @method on
             * @grammar on( name, callback[, context] ) => self
             * @param  {String}   name     事件名，支持多个事件用空格隔开
             * @param  {Function} callback 事件处理器
             * @param  {Object}   [context]  事件处理器的上下文。
             * @return {self} 返回自身，方便链式
             * @chainable
             * @class Mediator
             */
            on: function( name, callback, context ) {
                var me = this,
                    set;
    
                if ( !callback ) {
                    return this;
                }
    
                set = this._events || (this._events = []);
    
                eachEvent( name, callback, function( name, callback ) {
                    var handler = { e: name };
    
                    handler.cb = callback;
                    handler.ctx = context;
                    handler.ctx2 = context || me;
                    handler.id = set.length;
    
                    set.push( handler );
                });
    
                return this;
            },
    
            /**
             * 绑定事件，且当handler执行完后，自动解除绑定。
             * @method once
             * @grammar once( name, callback[, context] ) => self
             * @param  {String}   name     事件名
             * @param  {Function} callback 事件处理器
             * @param  {Object}   [context]  事件处理器的上下文。
             * @return {self} 返回自身，方便链式
             * @chainable
             */
            once: function( name, callback, context ) {
                var me = this;
    
                if ( !callback ) {
                    return me;
                }
    
                eachEvent( name, callback, function( name, callback ) {
                    var once = function() {
                            me.off( name, once );
                            return callback.apply( context || me, arguments );
                        };
    
                    once._cb = callback;
                    me.on( name, once, context );
                });
    
                return me;
            },
    
            /**
             * 解除事件绑定
             * @method off
             * @grammar off( [name[, callback[, context] ] ] ) => self
             * @param  {String}   [name]     事件名
             * @param  {Function} [callback] 事件处理器
             * @param  {Object}   [context]  事件处理器的上下文。
             * @return {self} 返回自身，方便链式
             * @chainable
             */
            off: function( name, cb, ctx ) {
                var events = this._events;
    
                if ( !events ) {
                    return this;
                }
    
                if ( !name && !cb && !ctx ) {
                    this._events = [];
                    return this;
                }
    
                eachEvent( name, cb, function( name, cb ) {
                    $.each( findHandlers( events, name, cb, ctx ), function() {
                        delete events[ this.id ];
                    });
                });
    
                return this;
            },
    
            /**
             * 触发事件
             * @method trigger
             * @grammar trigger( name[, args...] ) => self
             * @param  {String}   type     事件名
             * @param  {*} [...] 任意参数
             * @return {Boolean} 如果handler中return false了，则返回false, 否则返回true
             */
            trigger: function( type ) {
                var args, events, allEvents;
    
                if ( !this._events || !type ) {
                    return this;
                }
    
                args = slice.call( arguments, 1 );
                events = findHandlers( this._events, type );
                allEvents = findHandlers( this._events, 'all' );
    
                return triggerHanders( events, args ) &&
                        triggerHanders( allEvents, arguments );
            }
        };
    
        /**
         * 中介者，它本身是个单例，但可以通过[installTo](#WebUploader:Mediator:installTo)方法，使任何对象具备事件行为。
         * 主要目的是负责模块与模块之间的合作，降低耦合度。
         *
         * @class Mediator
         */
        return $.extend({
    
            /**
             * 可以通过这个接口，使任何对象具备事件功能。
             * @method installTo
             * @param  {Object} obj 需要具备事件行为的对象。
             * @return {Object} 返回obj.
             */
            installTo: function( obj ) {
                return $.extend( obj, protos );
            }
    
        }, protos );
    });
    /**
     * @fileOverview Uploader上传类
     */
    define('uploader',[
        'base',
        'mediator'
    ], function( Base, Mediator ) {
    
        var $ = Base.$;
    
        /**
         * 上传入口类。
         * @class Uploader
         * @constructor
         * @grammar new Uploader( opts ) => Uploader
         * @example
         * var uploader = WebUploader.Uploader({
         *     swf: 'path_of_swf/Uploader.swf',
         *
         *     // 开起分片上传。
         *     chunked: true
         * });
         */
        function Uploader( opts ) {
            this.options = $.extend( true, {}, Uploader.options, opts );
            this._init( this.options );
        }
    
        // default Options
        // widgets中有相应扩展
        Uploader.options = {
            // 是否开启调试模式
            debug: false,
        };
        Mediator.installTo( Uploader.prototype );
    
        // 批量添加纯命令式方法。
        $.each({
            upload: 'start-upload',
            stop: 'stop-upload',
            getFile: 'get-file',
            getFiles: 'get-files',
            addFile: 'add-file',
            addFiles: 'add-file',
            sort: 'sort-files',
            removeFile: 'remove-file',
            cancelFile: 'cancel-file',
            skipFile: 'skip-file',
            retry: 'retry',
            isInProgress: 'is-in-progress',
            makeThumb: 'make-thumb',
            md5File: 'md5-file',
            getDimension: 'get-dimension',
            addButton: 'add-btn',
            predictRuntimeType: 'predict-runtime-type',
            refresh: 'refresh',
            disable: 'disable',
            enable: 'enable',
            reset: 'reset'
        }, function( fn, command ) {
            Uploader.prototype[ fn ] = function() {
                return this.request( command, arguments );
            };
        });
    
        $.extend( Uploader.prototype, {
            state: 'pending',
    
            _init: function( opts ) {
                var me = this;
    
                me.request( 'init', opts, function() {
                    me.state = 'ready';
                    me.trigger('ready');
                });
            },
    
            /**
             * 获取或者设置Uploader配置项。
             * @method option
             * @grammar option( key ) => *
             * @grammar option( key, val ) => self
             * @example
             *
             * // 初始状态图片上传前不会压缩
             * var uploader = new WebUploader.Uploader({
             *     compress: null;
             * });
             *
             * // 修改后图片上传前，尝试将图片压缩到1600 * 1600
             * uploader.option( 'compress', {
             *     width: 1600,
             *     height: 1600
             * });
             */
            option: function( key, val ) {
                var opts = this.options;
    
                // setter
                if ( arguments.length > 1 ) {
    
                    if ( $.isPlainObject( val ) &&
                            $.isPlainObject( opts[ key ] ) ) {
                        $.extend( opts[ key ], val );
                    } else {
                        opts[ key ] = val;
                    }
    
                } else {    // getter
                    return key ? opts[ key ] : opts;
                }
            },
    
            /**
             * 获取文件统计信息。返回一个包含一下信息的对象。
             * * `successNum` 上传成功的文件数
             * * `progressNum` 上传中的文件数
             * * `cancelNum` 被删除的文件数
             * * `invalidNum` 无效的文件数
             * * `uploadFailNum` 上传失败的文件数
             * * `queueNum` 还在队列中的文件数
             * * `interruptNum` 被暂停的文件数
             * @method getStats
             * @grammar getStats() => Object
             */
            getStats: function() {
                // return this._mgr.getStats.apply( this._mgr, arguments );
                var stats = this.request('get-stats');
    
                return stats ? {
                    successNum: stats.numOfSuccess,
                    progressNum: stats.numOfProgress,
    
                    // who care?
                    // queueFailNum: 0,
                    cancelNum: stats.numOfCancel,
                    invalidNum: stats.numOfInvalid,
                    uploadFailNum: stats.numOfUploadFailed,
                    queueNum: stats.numOfQueue,
                    interruptNum: stats.numOfInterrupt
                } : {};
            },
    
            // 需要重写此方法来来支持opts.onEvent和instance.onEvent的处理器
            trigger: function( type/*, args...*/ ) {
                var args = [].slice.call( arguments, 1 ),
                    opts = this.options,
                    name = 'on' + type.substring( 0, 1 ).toUpperCase() +
                        type.substring( 1 );
    
                if (
                        // 调用通过on方法注册的handler.
                        Mediator.trigger.apply( this, arguments ) === false ||
    
                        // 调用opts.onEvent
                        $.isFunction( opts[ name ] ) &&
                        opts[ name ].apply( this, args ) === false ||
    
                        // 调用this.onEvent
                        $.isFunction( this[ name ] ) &&
                        this[ name ].apply( this, args ) === false ||
    
                        // 广播所有uploader的事件。
                        Mediator.trigger.apply( Mediator,
                        [ this, type ].concat( args ) ) === false ) {
    
                    return false;
                }
    
                return true;
            },
    
            /**
             * 销毁 webuploader 实例
             * @method destroy
             * @grammar destroy() => undefined
             */
            destroy: function() {
                this.request( 'destroy', arguments );
                this.off();
            },
    
            // widgets/widget.js将补充此方法的详细文档。
            request: Base.noop
        });
    
        /**
         * 创建Uploader实例，等同于new Uploader( opts );
         * @method create
         * @class Base
         * @static
         * @grammar Base.create( opts ) => Uploader
         */
        Base.create = Uploader.create = function( opts ) {
            return new Uploader( opts );
        };
    
        // 暴露Uploader，可以通过它来扩展业务逻辑。
        Base.Uploader = Uploader;
    
        return Uploader;
    });
    
    /**
     * @fileOverview Runtime管理器，负责Runtime的选择, 连接
     */
    define('runtime/runtime',[
        'base',
        'mediator'
    ], function( Base, Mediator ) {
    
        var $ = Base.$,
            factories = {},
    
            // 获取对象的第一个key
            getFirstKey = function( obj ) {
                for ( var key in obj ) {
                    if ( obj.hasOwnProperty( key ) ) {
                        return key;
                    }
                }
                return null;
            };
    
        // 接口类。
        function Runtime( options ) {
            this.options = $.extend({
                container: document.body
            }, options );
            this.uid = Base.guid('rt_');
        }
    
        $.extend( Runtime.prototype, {
    
            getContainer: function() {
                var opts = this.options,
                    parent, container;
    
                if ( this._container ) {
                    return this._container;
                }
    
                parent = $( opts.container || document.body );
                container = $( document.createElement('div') );
    
                container.attr( 'id', 'rt_' + this.uid );
                container.css({
                    position: 'absolute',
                    top: '0px',
                    left: '0px',
                    width: '1px',
                    height: '1px',
                    overflow: 'hidden'
                });
    
                parent.append( container );
                parent.addClass('webuploader-container');
                this._container = container;
                this._parent = parent;
                return container;
            },
    
            init: Base.noop,
            exec: Base.noop,
    
            destroy: function() {
                this._container && this._container.remove();
                this._parent && this._parent.removeClass('webuploader-container');
                this.off();
            }
        });
    
        Runtime.orders = 'html5,flash';
    
    
        /**
         * 添加Runtime实现。
         * @param {String} type    类型
         * @param {Runtime} factory 具体Runtime实现。
         */
        Runtime.addRuntime = function( type, factory ) {
            factories[ type ] = factory;
        };
    
        Runtime.hasRuntime = function( type ) {
            return !!(type ? factories[ type ] : getFirstKey( factories ));
        };
    
        Runtime.create = function( opts, orders ) {
            var type, runtime;
    
            orders = orders || Runtime.orders;
            $.each( orders.split( /\s*,\s*/g ), function() {
                if ( factories[ this ] ) {
                    type = this;
                    return false;
                }
            });
    
            type = type || getFirstKey( factories );
    
            if ( !type ) {
                throw new Error('Runtime Error');
            }
    
            runtime = new factories[ type ]( opts );
            return runtime;
        };
    
        Mediator.installTo( Runtime.prototype );
        return Runtime;
    });
    
    /**
     * @fileOverview Runtime管理器，负责Runtime的选择, 连接
     */
    define('runtime/client',[
        'base',
        'mediator',
        'runtime/runtime'
    ], function( Base, Mediator, Runtime ) {
    
        var cache;
    
        cache = (function() {
            var obj = {};
    
            return {
                add: function( runtime ) {
                    obj[ runtime.uid ] = runtime;
                },
    
                get: function( ruid, standalone ) {
                    var i;
    
                    if ( ruid ) {
                        return obj[ ruid ];
                    }
    
                    for ( i in obj ) {
                        // 有些类型不能重用，比如filepicker.
                        if ( standalone && obj[ i ].__standalone ) {
                            continue;
                        }
    
                        return obj[ i ];
                    }
    
                    return null;
                },
    
                remove: function( runtime ) {
                    delete obj[ runtime.uid ];
                }
            };
        })();
    
        function RuntimeClient( component, standalone ) {
            var deferred = Base.Deferred(),
                runtime;
    
            this.uid = Base.guid('client_');
    
            // 允许runtime没有初始化之前，注册一些方法在初始化后执行。
            this.runtimeReady = function( cb ) {
                return deferred.done( cb );
            };
    
            this.connectRuntime = function( opts, cb ) {
    
                // already connected.
                if ( runtime ) {
                    throw new Error('already connected!');
                }
    
                deferred.done( cb );
    
                if ( typeof opts === 'string' && cache.get( opts ) ) {
                    runtime = cache.get( opts );
                }
    
                // 像filePicker只能独立存在，不能公用。
                runtime = runtime || cache.get( null, standalone );
    
                // 需要创建
                if ( !runtime ) {
                    runtime = Runtime.create( opts, opts.runtimeOrder );
                    runtime.__promise = deferred.promise();
                    runtime.once( 'ready', deferred.resolve );
                    runtime.init();
                    cache.add( runtime );
                    runtime.__client = 1;
                } else {
                    // 来自cache
                    Base.$.extend( runtime.options, opts );
                    runtime.__promise.then( deferred.resolve );
                    runtime.__client++;
                }
    
                standalone && (runtime.__standalone = standalone);
                return runtime;
            };
    
            this.getRuntime = function() {
                return runtime;
            };
    
            this.disconnectRuntime = function() {
                if ( !runtime ) {
                    return;
                }
    
                runtime.__client--;
    
                if ( runtime.__client <= 0 ) {
                    cache.remove( runtime );
                    delete runtime.__promise;
                    runtime.destroy();
                }
    
                runtime = null;
            };
    
            this.exec = function() {
                if ( !runtime ) {
                    return;
                }
    
                var args = Base.slice( arguments );
                component && args.unshift( component );
    
                return runtime.exec.apply( this, args );
            };
    
            this.getRuid = function() {
                return runtime && runtime.uid;
            };
    
            this.destroy = (function( destroy ) {
                return function() {
                    destroy && destroy.apply( this, arguments );
                    this.trigger('destroy');
                    this.off();
                    this.exec('destroy');
                    this.disconnectRuntime();
                };
            })( this.destroy );
        }
    
        Mediator.installTo( RuntimeClient.prototype );
        return RuntimeClient;
    });
    /**
     * @fileOverview 错误信息
     */
    define('lib/dnd',[
        'base',
        'mediator',
        'runtime/client'
    ], function( Base, Mediator, RuntimeClent ) {
    
        var $ = Base.$;
    
        function DragAndDrop( opts ) {
            opts = this.options = $.extend({}, DragAndDrop.options, opts );
    
            opts.container = $( opts.container );
    
            if ( !opts.container.length ) {
                return;
            }
    
            RuntimeClent.call( this, 'DragAndDrop' );
        }
    
        DragAndDrop.options = {
            accept: null,
            disableGlobalDnd: false
        };
    
        Base.inherits( RuntimeClent, {
            constructor: DragAndDrop,
    
            init: function() {
                var me = this;
    
                me.connectRuntime( me.options, function() {
                    me.exec('init');
                    me.trigger('ready');
                });
            }
        });
    
        Mediator.installTo( DragAndDrop.prototype );
    
        return DragAndDrop;
    });
    /**
     * @fileOverview 组件基类。
     */
    define('widgets/widget',[
        'base',
        'uploader'
    ], function( Base, Uploader ) {
    
        var $ = Base.$,
            _init = Uploader.prototype._init,
            _destroy = Uploader.prototype.destroy,
            IGNORE = {},
            widgetClass = [];
    
        function isArrayLike( obj ) {
            if ( !obj ) {
                return false;
            }
    
            var length = obj.length,
                type = $.type( obj );
    
            if ( obj.nodeType === 1 && length ) {
                return true;
            }
    
            return type === 'array' || type !== 'function' && type !== 'string' &&
                    (length === 0 || typeof length === 'number' && length > 0 &&
                    (length - 1) in obj);
        }
    
        function Widget( uploader ) {
            this.owner = uploader;
            this.options = uploader.options;
        }
    
        $.extend( Widget.prototype, {
    
            init: Base.noop,
    
            // 类Backbone的事件监听声明，监听uploader实例上的事件
            // widget直接无法监听事件，事件只能通过uploader来传递
            invoke: function( apiName, args ) {
    
                /*
                    {
                        'make-thumb': 'makeThumb'
                    }
                 */
                var map = this.responseMap;
    
                // 如果无API响应声明则忽略
                if ( !map || !(apiName in map) || !(map[ apiName ] in this) ||
                        !$.isFunction( this[ map[ apiName ] ] ) ) {
    
                    return IGNORE;
                }
    
                return this[ map[ apiName ] ].apply( this, args );
    
            },
    
            /**
             * 发送命令。当传入`callback`或者`handler`中返回`promise`时。返回一个当所有`handler`中的promise都完成后完成的新`promise`。
             * @method request
             * @grammar request( command, args ) => * | Promise
             * @grammar request( command, args, callback ) => Promise
             * @for  Uploader
             */
            request: function() {
                return this.owner.request.apply( this.owner, arguments );
            }
        });
    
        // 扩展Uploader.
        $.extend( Uploader.prototype, {
    
            /**
             * @property {String | Array} [disableWidgets=undefined]
             * @namespace options
             * @for Uploader
             * @description 默认所有 Uploader.register 了的 widget 都会被加载，如果禁用某一部分，请通过此 option 指定黑名单。
             */
    
            // 覆写_init用来初始化widgets
            _init: function() {
                var me = this,
                    widgets = me._widgets = [],
                    deactives = me.options.disableWidgets || '';
    
                $.each( widgetClass, function( _, klass ) {
                    (!deactives || !~deactives.indexOf( klass._name )) &&
                        widgets.push( new klass( me ) );
                });
    
                return _init.apply( me, arguments );
            },
    
            request: function( apiName, args, callback ) {
                var i = 0,
                    widgets = this._widgets,
                    len = widgets && widgets.length,
                    rlts = [],
                    dfds = [],
                    widget, rlt, promise, key;
    
                args = isArrayLike( args ) ? args : [ args ];
    
                for ( ; i < len; i++ ) {
                    widget = widgets[ i ];
                    rlt = widget.invoke( apiName, args );
    
                    if ( rlt !== IGNORE ) {
    
                        // Deferred对象
                        if ( Base.isPromise( rlt ) ) {
                            dfds.push( rlt );
                        } else {
                            rlts.push( rlt );
                        }
                    }
                }
    
                // 如果有callback，则用异步方式。
                if ( callback || dfds.length ) {
                    promise = Base.when.apply( Base, dfds );
                    key = promise.pipe ? 'pipe' : 'then';
    
                    // 很重要不能删除。删除了会死循环。
                    // 保证执行顺序。让callback总是在下一个 tick 中执行。
                    return promise[ key ](function() {
                                var deferred = Base.Deferred(),
                                    args = arguments;
    
                                if ( args.length === 1 ) {
                                    args = args[ 0 ];
                                }
    
                                setTimeout(function() {
                                    deferred.resolve( args );
                                }, 1 );
    
                                return deferred.promise();
                            })[ callback ? key : 'done' ]( callback || Base.noop );
                } else {
                    return rlts[ 0 ];
                }
            },
    
            destroy: function() {
                _destroy.apply( this, arguments );
                this._widgets = null;
            }
        });
    
        /**
         * 添加组件
         * @grammar Uploader.register(proto);
         * @grammar Uploader.register(map, proto);
         * @param  {object} responseMap API 名称与函数实现的映射
         * @param  {object} proto 组件原型，构造函数通过 constructor 属性定义
         * @method Uploader.register
         * @for Uploader
         * @example
         * Uploader.register({
         *     'make-thumb': 'makeThumb'
         * }, {
         *     init: function( options ) {},
         *     makeThumb: function() {}
         * });
         *
         * Uploader.register({
         *     'make-thumb': function() {
         *         
         *     }
         * });
         */
        Uploader.register = Widget.register = function( responseMap, widgetProto ) {
            var map = { init: 'init', destroy: 'destroy', name: 'anonymous' },
                klass;
    
            if ( arguments.length === 1 ) {
                widgetProto = responseMap;
    
                // 自动生成 map 表。
                $.each(widgetProto, function(key) {
                    if ( key[0] === '_' || key === 'name' ) {
                        key === 'name' && (map.name = widgetProto.name);
                        return;
                    }
    
                    map[key.replace(/[A-Z]/g, '-$&').toLowerCase()] = key;
                });
    
            } else {
                map = $.extend( map, responseMap );
            }
    
            widgetProto.responseMap = map;
            klass = Base.inherits( Widget, widgetProto );
            klass._name = map.name;
            widgetClass.push( klass );
    
            return klass;
        };
    
        /**
         * 删除插件，只有在注册时指定了名字的才能被删除。
         * @grammar Uploader.unRegister(name);
         * @param  {string} name 组件名字
         * @method Uploader.unRegister
         * @for Uploader
         * @example
         *
         * Uploader.register({
         *     name: 'custom',
         *     
         *     'make-thumb': function() {
         *         
         *     }
         * });
         *
         * Uploader.unRegister('custom');
         */
        Uploader.unRegister = Widget.unRegister = function( name ) {
            if ( !name || name === 'anonymous' ) {
                return;
            }
            
            // 删除指定的插件。
            for ( var i = widgetClass.length; i--; ) {
                if ( widgetClass[i]._name === name ) {
                    widgetClass.splice(i, 1)
                }
            }
        };
    
        return Widget;
    });
    /**
     * @fileOverview DragAndDrop Widget。
     */
    define('widgets/filednd',[
        'base',
        'uploader',
        'lib/dnd',
        'widgets/widget'
    ], function( Base, Uploader, Dnd ) {
        var $ = Base.$;
    
        Uploader.options.dnd = '';
    
        /**
         * @property {Selector} [dnd=undefined]  指定Drag And Drop拖拽的容器，如果不指定，则不启动。
         * @namespace options
         * @for Uploader
         */
        
        /**
         * @property {Selector} [disableGlobalDnd=false]  是否禁掉整个页面的拖拽功能，如果不禁用，图片拖进来的时候会默认被浏览器打开。
         * @namespace options
         * @for Uploader
         */
    
        /**
         * @event dndAccept
         * @param {DataTransferItemList} items DataTransferItem
         * @description 阻止此事件可以拒绝某些类型的文件拖入进来。目前只有 chrome 提供这样的 API，且只能通过 mime-type 验证。
         * @for  Uploader
         */
        return Uploader.register({
            name: 'dnd',
            
            init: function( opts ) {
    
                if ( !opts.dnd ||
                        this.request('predict-runtime-type') !== 'html5' ) {
                    return;
                }
    
                var me = this,
                    deferred = Base.Deferred(),
                    options = $.extend({}, {
                        disableGlobalDnd: opts.disableGlobalDnd,
                        container: opts.dnd,
                        accept: opts.accept
                    }),
                    dnd;
    
                this.dnd = dnd = new Dnd( options );
    
                dnd.once( 'ready', deferred.resolve );
                dnd.on( 'drop', function( files ) {
                    me.request( 'add-file', [ files ]);
                });
    
                // 检测文件是否全部允许添加。
                dnd.on( 'accept', function( items ) {
                    return me.owner.trigger( 'dndAccept', items );
                });
    
                dnd.init();
    
                return deferred.promise();
            },
    
            destroy: function() {
                this.dnd && this.dnd.destroy();
            }
        });
    });
    
    /**
     * @fileOverview 错误信息
     */
    define('lib/filepaste',[
        'base',
        'mediator',
        'runtime/client'
    ], function( Base, Mediator, RuntimeClent ) {
    
        var $ = Base.$;
    
        function FilePaste( opts ) {
            opts = this.options = $.extend({}, opts );
            opts.container = $( opts.container || document.body );
            RuntimeClent.call( this, 'FilePaste' );
        }
    
        Base.inherits( RuntimeClent, {
            constructor: FilePaste,
    
            init: function() {
                var me = this;
    
                me.connectRuntime( me.options, function() {
                    me.exec('init');
                    me.trigger('ready');
                });
            }
        });
    
        Mediator.installTo( FilePaste.prototype );
    
        return FilePaste;
    });
    /**
     * @fileOverview 组件基类。
     */
    define('widgets/filepaste',[
        'base',
        'uploader',
        'lib/filepaste',
        'widgets/widget'
    ], function( Base, Uploader, FilePaste ) {
        var $ = Base.$;
    
        /**
         * @property {Selector} [paste=undefined]  指定监听paste事件的容器，如果不指定，不启用此功能。此功能为通过粘贴来添加截屏的图片。建议设置为`document.body`.
         * @namespace options
         * @for Uploader
         */
        return Uploader.register({
            name: 'paste',
            
            init: function( opts ) {
    
                if ( !opts.paste ||
                        this.request('predict-runtime-type') !== 'html5' ) {
                    return;
                }
    
                var me = this,
                    deferred = Base.Deferred(),
                    options = $.extend({}, {
                        container: opts.paste,
                        accept: opts.accept
                    }),
                    paste;
    
                this.paste = paste = new FilePaste( options );
    
                paste.once( 'ready', deferred.resolve );
                paste.on( 'paste', function( files ) {
                    me.owner.request( 'add-file', [ files ]);
                });
                paste.init();
    
                return deferred.promise();
            },
    
            destroy: function() {
                this.paste && this.paste.destroy();
            }
        });
    });
    /**
     * @fileOverview Blob
     */
    define('lib/blob',[
        'base',
        'runtime/client'
    ], function( Base, RuntimeClient ) {
    
        function Blob( ruid, source ) {
            var me = this;
    
            me.source = source;
            me.ruid = ruid;
            this.size = source.size || 0;
    
            // 如果没有指定 mimetype, 但是知道文件后缀。
            if ( !source.type && this.ext &&
                    ~'jpg,jpeg,png,gif,bmp'.indexOf( this.ext ) ) {
                this.type = 'image/' + (this.ext === 'jpg' ? 'jpeg' : this.ext);
            } else {
                this.type = source.type || 'application/octet-stream';
            }
    
            RuntimeClient.call( me, 'Blob' );
            this.uid = source.uid || this.uid;
    
            if ( ruid ) {
                me.connectRuntime( ruid );
            }
        }
    
        Base.inherits( RuntimeClient, {
            constructor: Blob,
    
            slice: function( start, end ) {
                return this.exec( 'slice', start, end );
            },
    
            getSource: function() {
                return this.source;
            }
        });
    
        return Blob;
    });
    /**
     * 为了统一化Flash的File和HTML5的File而存在。
     * 以至于要调用Flash里面的File，也可以像调用HTML5版本的File一下。
     * @fileOverview File
     */
    define('lib/file',[
        'base',
        'lib/blob'
    ], function( Base, Blob ) {
    
        var uid = 1,
            rExt = /\.([^.]+)$/;
    
        function File( ruid, file ) {
            var ext;
    
            this.name = file.name || ('untitled' + uid++);
            ext = rExt.exec( file.name ) ? RegExp.$1.toLowerCase() : '';
    
            // todo 支持其他类型文件的转换。
            // 如果有 mimetype, 但是文件名里面没有找出后缀规律
            if ( !ext && file.type ) {
                ext = /\/(jpg|jpeg|png|gif|bmp)$/i.exec( file.type ) ?
                        RegExp.$1.toLowerCase() : '';
                this.name += '.' + ext;
            }
    
            this.ext = ext;
            this.lastModifiedDate = file.lastModifiedDate || 
                    file.lastModified && new Date(file.lastModified).toLocaleString() ||
                    (new Date()).toLocaleString();
    
            Blob.apply( this, arguments );
        }
    
        return Base.inherits( Blob, File );
    });
    
    /**
     * @fileOverview 错误信息
     */
    define('lib/filepicker',[
        'base',
        'runtime/client',
        'lib/file'
    ], function( Base, RuntimeClient, File ) {
    
        var $ = Base.$;
    
        function FilePicker( opts ) {
            opts = this.options = $.extend({}, FilePicker.options, opts );
    
            opts.container = $( opts.id );
    
            if ( !opts.container.length ) {
                throw new Error('按钮指定错误');
            }
    
            opts.innerHTML = opts.innerHTML || opts.label ||
                    opts.container.html() || '';
    
            opts.button = $( opts.button || document.createElement('div') );
            opts.button.html( opts.innerHTML );
            opts.container.html( opts.button );
    
            RuntimeClient.call( this, 'FilePicker', true );
        }
    
        FilePicker.options = {
            button: null,
            container: null,
            label: null,
            innerHTML: null,
            multiple: true,
            accept: null,
            name: 'file',
            style: 'webuploader-pick'   //pick element class attribute, default is "webuploader-pick"
        };
    
        Base.inherits( RuntimeClient, {
            constructor: FilePicker,
    
            init: function() {
                var me = this,
                    opts = me.options,
                    button = opts.button,
                    style = opts.style;
    
                if (style)
                    button.addClass('webuploader-pick');
    
                me.on( 'all', function( type ) {
                    var files;
    
                    switch ( type ) {
                        case 'mouseenter':
                            if (style)
                                button.addClass('webuploader-pick-hover');
                            break;
    
                        case 'mouseleave':
                            if (style)
                                button.removeClass('webuploader-pick-hover');
                            break;
    
                        case 'change':
                            files = me.exec('getFiles');
                            me.trigger( 'select', $.map( files, function( file ) {
                                file = new File( me.getRuid(), file );
    
                                // 记录来源。
                                file._refer = opts.container;
                                return file;
                            }), opts.container );
                            break;
                    }
                });
    
                me.connectRuntime( opts, function() {
                    me.refresh();
                    me.exec( 'init', opts );
                    me.trigger('ready');
                });
    
                this._resizeHandler = Base.bindFn( this.refresh, this );
                $( window ).on( 'resize', this._resizeHandler );
            },
    
            refresh: function() {
                var shimContainer = this.getRuntime().getContainer(),
                    button = this.options.button,
                    /*
                    width = button.outerWidth ?
                            button.outerWidth() : button.width(),
    
                    height = button.outerHeight ?
                            button.outerHeight() : button.height(),
                    */
                    width = button[0] && button[0].offsetWidth || button.outerWidth() || button.width(),
                    height = button[0] && button[0].offsetHeight || button.outerHeight() || button.height(),
                    pos = button.offset();
    
                width && height && shimContainer.css({
                    bottom: 'auto',
                    right: 'auto',
                    width: width + 'px',
                    height: height + 'px'
                }).offset( pos );
            },
    
            enable: function() {
                var btn = this.options.button;
    
                btn.removeClass('webuploader-pick-disable');
                this.refresh();
            },
    
            disable: function() {
                var btn = this.options.button;
    
                this.getRuntime().getContainer().css({
                    top: '-99999px'
                });
    
                btn.addClass('webuploader-pick-disable');
            },
    
            destroy: function() {
                var btn = this.options.button;
                $( window ).off( 'resize', this._resizeHandler );
                btn.removeClass('webuploader-pick-disable webuploader-pick-hover ' +
                    'webuploader-pick');
            }
        });
    
        return FilePicker;
    });
    
    /**
     * @fileOverview 文件选择相关
     */
    define('widgets/filepicker',[
        'base',
        'uploader',
        'lib/filepicker',
        'widgets/widget'
    ], function( Base, Uploader, FilePicker ) {
        var $ = Base.$;
    
        $.extend( Uploader.options, {
    
            /**
             * @property {Selector | Object} [pick=undefined]
             * @namespace options
             * @for Uploader
             * @description 指定选择文件的按钮容器，不指定则不创建按钮。
             *
             * * `id` {Seletor|dom} 指定选择文件的按钮容器，不指定则不创建按钮。**注意** 这里虽然写的是 id, 但是不是只支持 id, 还支持 class, 或者 dom 节点。
             * * `label` {String} 请采用 `innerHTML` 代替
             * * `innerHTML` {String} 指定按钮文字。不指定时优先从指定的容器中看是否自带文字。
             * * `multiple` {Boolean} 是否开起同时选择多个文件能力。
             */
            pick: null,
    
            /**
             * @property {Array} [accept=null]
             * @namespace options
             * @for Uploader
             * @description 指定接受哪些类型的文件。 由于目前还有ext转mimeType表，所以这里需要分开指定。
             *
             * * `title` {String} 文字描述
             * * `extensions` {String} 允许的文件后缀，不带点，多个用逗号分割。
             * * `mimeTypes` {String} 多个用逗号分割。
             *
             * 如：
             *
             * ```
             * {
             *     title: 'Images',
             *     extensions: 'gif,jpg,jpeg,bmp,png',
             *     mimeTypes: 'image/*'
             * }
             * ```
             */
            accept: null/*{
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/*'
            }*/
        });
    
        return Uploader.register({
            name: 'picker',
    
            init: function( opts ) {
                this.pickers = [];
                return opts.pick && this.addBtn( opts.pick );
            },
    
            refresh: function() {
                $.each( this.pickers, function() {
                    this.refresh();
                });
            },
    
            /**
             * @method addButton
             * @for Uploader
             * @grammar addButton( pick ) => Promise
             * @description
             * 添加文件选择按钮，如果一个按钮不够，需要调用此方法来添加。参数跟[options.pick](#WebUploader:Uploader:options)一致。
             * @example
             * uploader.addButton({
             *     id: '#btnContainer',
             *     innerHTML: '选择文件'
             * });
             */
            addBtn: function( pick ) {
                var me = this,
                    opts = me.options,
                    accept = opts.accept,
                    promises = [];
    
                if ( !pick ) {
                    return;
                }
    
                $.isPlainObject( pick ) || (pick = {
                    id: pick
                });
    
                $( pick.id ).each(function() {
                    var options, picker, deferred;
    
                    deferred = Base.Deferred();
    
                    options = $.extend({}, pick, {
                        accept: $.isPlainObject( accept ) ? [ accept ] : accept,
                        swf: opts.swf,
                        runtimeOrder: opts.runtimeOrder,
                        id: this
                    });
    
                    picker = new FilePicker( options );
    
                    picker.once( 'ready', deferred.resolve );
                    picker.on( 'select', function( files ) {
                        me.owner.request( 'add-file', [ files ]);
                    });
                    picker.on('dialogopen', function() {
                        me.owner.trigger('dialogOpen', picker.button);
                    });
                    picker.init();
    
                    me.pickers.push( picker );
    
                    promises.push( deferred.promise() );
                });
    
                return Base.when.apply( Base, promises );
            },
    
            disable: function() {
                $.each( this.pickers, function() {
                    this.disable();
                });
            },
    
            enable: function() {
                $.each( this.pickers, function() {
                    this.enable();
                });
            },
    
            destroy: function() {
                $.each( this.pickers, function() {
                    this.destroy();
                });
                this.pickers = null;
            }
        });
    });
    /**
     * @fileOverview Image
     */
    define('lib/image',[
        'base',
        'runtime/client',
        'lib/blob'
    ], function( Base, RuntimeClient, Blob ) {
        var $ = Base.$;
    
        // 构造器。
        function Image( opts ) {
            this.options = $.extend({}, Image.options, opts );
            RuntimeClient.call( this, 'Image' );
    
            this.on( 'load', function() {
                this._info = this.exec('info');
                this._meta = this.exec('meta');
            });
        }
    
        // 默认选项。
        Image.options = {
    
            // 默认的图片处理质量
            quality: 90,
    
            // 是否裁剪
            crop: false,
    
            // 是否保留头部信息
            preserveHeaders: false,
    
            // 是否允许放大。
            allowMagnify: false
        };
    
        // 继承RuntimeClient.
        Base.inherits( RuntimeClient, {
            constructor: Image,
    
            info: function( val ) {
    
                // setter
                if ( val ) {
                    this._info = val;
                    return this;
                }
    
                // getter
                return this._info;
            },
    
            meta: function( val ) {
    
                // setter
                if ( val ) {
                    this._meta = val;
                    return this;
                }
    
                // getter
                return this._meta;
            },
    
            loadFromBlob: function( blob ) {
                var me = this,
                    ruid = blob.getRuid();
    
                this.connectRuntime( ruid, function() {
                    me.exec( 'init', me.options );
                    me.exec( 'loadFromBlob', blob );
                });
            },
    
            resize: function() {
                var args = Base.slice( arguments );
                return this.exec.apply( this, [ 'resize' ].concat( args ) );
            },
    
            crop: function() {
                var args = Base.slice( arguments );
                return this.exec.apply( this, [ 'crop' ].concat( args ) );
            },
    
            getAsDataUrl: function( type ) {
                return this.exec( 'getAsDataUrl', type );
            },
    
            getAsBlob: function( type ) {
                var blob = this.exec( 'getAsBlob', type );
    
                return new Blob( this.getRuid(), blob );
            }
        });
    
        return Image;
    });
    /**
     * Browser Image Compression
     * v2.0.2
     * by Donald <donaldcwl@gmail.com>
     * https://github.com/Donaldcwl/browser-image-compression
     */
    define('lib/browser-image-compression',[], function () {
    
        var __assign = (this && this.__assign) || function () {
            __assign = Object.assign || function (t) {
                for (var s, i = 1, n = arguments.length; i < n; i++) {
                    s = arguments[i];
                    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                        t[p] = s[p];
                }
                return t;
            };
            return __assign.apply(this, arguments);
        };
    
        var _a;
    
        function _mergeNamespaces(e, t) {
            return t.forEach((function (t) {
                t && "string" != typeof t && !Array.isArray(t) && Object.keys(t).forEach((function (r) {
                    if ("default" !== r && !(r in e)) {
                        var i = Object.getOwnPropertyDescriptor(t, r);
                        Object.defineProperty(e, r, i.get ? i : {
                            enumerable: !0, get: function () {
                                return t[r];
                            }
                        });
                    }
                }));
            })), Object.freeze(e);
        }
    
        function copyExifWithoutOrientation(e, t) {
            return new Promise((function (r, i) {
                var o;
                return getApp1Segment(e).then((function (e) {
                    try {
                        return o = e, r(new window.Blob([t.slice(0, 2), o, t.slice(2)], {type: "image/jpeg"}));
                    } catch (e) {
                        return i(e);
                    }
                }), i);
            }));
        }
    
        var getApp1Segment = function (e) {
            return new Promise((function (t, r) {
                var i = new FileReader;
                i.addEventListener("load", (function (_a) {
                    var e = _a.target.result;
                    var i = new DataView(e);
                    var o = 0;
                    if (65496 !== i.getUint16(o))
                        return r("not a valid JPEG");
                    for (o += 2; ;) {
                        var a_1 = i.getUint16(o);
                        if (65498 === a_1)
                            break;
                        var s_1 = i.getUint16(o + 2);
                        if (65505 === a_1 && 1165519206 === i.getUint32(o + 4)) {
                            var a_2 = o + 10;
                            var f_1 = void 0;
                            switch (i.getUint16(a_2)) {
                                case 18761:
                                    f_1 = !0;
                                    break;
                                case 19789:
                                    f_1 = !1;
                                    break;
                                default:
                                    return r("TIFF header contains invalid endian");
                            }
                            if (42 !== i.getUint16(a_2 + 2, f_1))
                                return r("TIFF header contains invalid version");
                            var l_1 = i.getUint32(a_2 + 4, f_1), c = a_2 + l_1 + 2 + 12 * i.getUint16(a_2 + l_1, f_1);
                            for (var e_1 = a_2 + l_1 + 2; e_1 < c; e_1 += 12) {
                                if (274 == i.getUint16(e_1, f_1)) {
                                    if (3 !== i.getUint16(e_1 + 2, f_1))
                                        return r("Orientation data type is invalid");
                                    if (1 !== i.getUint32(e_1 + 4, f_1))
                                        return r("Orientation data count is invalid");
                                    i.setUint16(e_1 + 8, 1, f_1);
                                    break;
                                }
                            }
                            return t(e.slice(o, o + 2 + s_1));
                        }
                        o += 2 + s_1;
                    }
                    return t(new window.Blob);
                })), i.readAsArrayBuffer(e);
            }));
        };
        var e = {};
        !function (e) {
            var t, r, UZIP = {};
            e.exports = UZIP, UZIP.parse = function (e, t) {
                for (var r = UZIP.bin.readUshort, i = UZIP.bin.readUint, o = 0, a = {}, s = new Uint8Array(e), f = s.length - 4; 101010256 != i(s, f);)
                    f--;
                o = f;
                o += 4;
                var l = r(s, o += 4);
                r(s, o += 2);
                var c = i(s, o += 2), u = i(s, o += 4);
                o += 4, o = u;
                for (var h = 0; h < l; h++) {
                    i(s, o), o += 4, o += 4, o += 4, i(s, o += 4);
                    c = i(s, o += 4);
                    var d = i(s, o += 4), A = r(s, o += 4), g = r(s, o + 2), p = r(s, o + 4);
                    o += 6;
                    var m = i(s, o += 8);
                    o += 4, o += A + g + p, UZIP._readLocal(s, m, a, c, d, t);
                }
                return a;
            }, UZIP._readLocal = function (e, t, r, i, o, a) {
                var s = UZIP.bin.readUshort, f = UZIP.bin.readUint;
                f(e, t), s(e, t += 4), s(e, t += 2);
                var l = s(e, t += 2);
                f(e, t += 2), f(e, t += 4), t += 4;
                var c = s(e, t += 8), u = s(e, t += 2);
                t += 2;
                var h = UZIP.bin.readUTF8(e, t, c);
                if (t += c, t += u, a)
                    r[h] = {size: o, csize: i};
                else {
                    var d = new Uint8Array(e.buffer, t);
                    if (0 == l)
                        r[h] = new Uint8Array(d.buffer.slice(t, t + i));
                    else {
                        if (8 != l)
                            throw "unknown compression method: " + l;
                        var A = new Uint8Array(o);
                        UZIP.inflateRaw(d, A), r[h] = A;
                    }
                }
            }, UZIP.inflateRaw = function (e, t) {
                return UZIP.F.inflate(e, t);
            }, UZIP.inflate = function (e, t) {
                return e[0], e[1], UZIP.inflateRaw(new Uint8Array(e.buffer, e.byteOffset + 2, e.length - 6), t);
            }, UZIP.deflate = function (e, t) {
                null == t && (t = {level: 6});
                var r = 0, i = new Uint8Array(50 + Math.floor(1.1 * e.length));
                i[r] = 120, i[r + 1] = 156, r += 2, r = UZIP.F.deflateRaw(e, i, r, t.level);
                var o = UZIP.adler(e, 0, e.length);
                return i[r + 0] = o >>> 24 & 255, i[r + 1] = o >>> 16 & 255, i[r + 2] = o >>> 8 & 255, i[r + 3] = o >>> 0 & 255, new Uint8Array(i.buffer, 0, r + 4);
            }, UZIP.deflateRaw = function (e, t) {
                null == t && (t = {level: 6});
                var r = new Uint8Array(50 + Math.floor(1.1 * e.length)), i = UZIP.F.deflateRaw(e, r, i, t.level);
                return new Uint8Array(r.buffer, 0, i);
            }, UZIP.encode = function (e, t) {
                null == t && (t = !1);
                var r = 0, i = UZIP.bin.writeUint, o = UZIP.bin.writeUshort, a = {};
                for (var s in e) {
                    var f = !UZIP._noNeed(s) && !t, l = e[s], c = UZIP.crc.crc(l, 0, l.length);
                    a[s] = {cpr: f, usize: l.length, crc: c, file: f ? UZIP.deflateRaw(l) : l};
                }
                for (var s in a)
                    r += a[s].file.length + 30 + 46 + 2 * UZIP.bin.sizeUTF8(s);
                r += 22;
                var u = new Uint8Array(r), h = 0, d = [];
                for (var s in a) {
                    var A = a[s];
                    d.push(h), h = UZIP._writeHeader(u, h, s, A, 0);
                }
                var g = 0, p = h;
                for (var s in a) {
                    A = a[s];
                    d.push(h), h = UZIP._writeHeader(u, h, s, A, 1, d[g++]);
                }
                var m = h - p;
                return i(u, h, 101010256), h += 4, o(u, h += 4, g), o(u, h += 2, g), i(u, h += 2, m), i(u, h += 4, p), h += 4, h += 2, u.buffer;
            }, UZIP._noNeed = function (e) {
                var t = e.split(".").pop().toLowerCase();
                return -1 != "png,jpg,jpeg,zip".indexOf(t);
            }, UZIP._writeHeader = function (e, t, r, i, o, a) {
                var s = UZIP.bin.writeUint, f = UZIP.bin.writeUshort, l = i.file;
                return s(e, t, 0 == o ? 67324752 : 33639248), t += 4, 1 == o && (t += 2), f(e, t, 20), f(e, t += 2, 0), f(e, t += 2, i.cpr ? 8 : 0), s(e, t += 2, 0), s(e, t += 4, i.crc), s(e, t += 4, l.length), s(e, t += 4, i.usize), f(e, t += 4, UZIP.bin.sizeUTF8(r)), f(e, t += 2, 0), t += 2, 1 == o && (t += 2, t += 2, s(e, t += 6, a), t += 4), t += UZIP.bin.writeUTF8(e, t, r), 0 == o && (e.set(l, t), t += l.length), t;
            }, UZIP.crc = {
                table: function () {
                    for (var e = new Uint32Array(256), t = 0; t < 256; t++) {
                        for (var r = t, i = 0; i < 8; i++)
                            1 & r ? r = 3988292384 ^ r >>> 1 : r >>>= 1;
                        e[t] = r;
                    }
                    return e;
                }(), update: function (e, t, r, i) {
                    for (var o = 0; o < i; o++)
                        e = UZIP.crc.table[255 & (e ^ t[r + o])] ^ e >>> 8;
                    return e;
                }, crc: function (e, t, r) {
                    return 4294967295 ^ UZIP.crc.update(4294967295, e, t, r);
                }
            }, UZIP.adler = function (e, t, r) {
                for (var i = 1, o = 0, a = t, s = t + r; a < s;) {
                    for (var f = Math.min(a + 5552, s); a < f;)
                        o += i += e[a++];
                    i %= 65521, o %= 65521;
                }
                return o << 16 | i;
            }, UZIP.bin = {
                readUshort: function (e, t) {
                    return e[t] | e[t + 1] << 8;
                }, writeUshort: function (e, t, r) {
                    e[t] = 255 & r, e[t + 1] = r >> 8 & 255;
                }, readUint: function (e, t) {
                    return 16777216 * e[t + 3] + (e[t + 2] << 16 | e[t + 1] << 8 | e[t]);
                }, writeUint: function (e, t, r) {
                    e[t] = 255 & r, e[t + 1] = r >> 8 & 255, e[t + 2] = r >> 16 & 255, e[t + 3] = r >> 24 & 255;
                }, readASCII: function (e, t, r) {
                    for (var i = "", o = 0; o < r; o++)
                        i += String.fromCharCode(e[t + o]);
                    return i;
                }, writeASCII: function (e, t, r) {
                    for (var i = 0; i < r.length; i++)
                        e[t + i] = r.charCodeAt(i);
                }, pad: function (e) {
                    return e.length < 2 ? "0" + e : e;
                }, readUTF8: function (e, t, r) {
                    for (var i, o = "", a = 0; a < r; a++)
                        o += "%" + UZIP.bin.pad(e[t + a].toString(16));
                    try {
                        i = decodeURIComponent(o);
                    } catch (i) {
                        return UZIP.bin.readASCII(e, t, r);
                    }
                    return i;
                }, writeUTF8: function (e, t, r) {
                    for (var i = r.length, o = 0, a = 0; a < i; a++) {
                        var s = r.charCodeAt(a);
                        if (0 == (4294967168 & s))
                            e[t + o] = s, o++;
                        else if (0 == (4294965248 & s))
                            e[t + o] = 192 | s >> 6, e[t + o + 1] = 128 | s >> 0 & 63, o += 2;
                        else if (0 == (4294901760 & s))
                            e[t + o] = 224 | s >> 12, e[t + o + 1] = 128 | s >> 6 & 63, e[t + o + 2] = 128 | s >> 0 & 63, o += 3;
                        else {
                            if (0 != (4292870144 & s))
                                throw "e";
                            e[t + o] = 240 | s >> 18, e[t + o + 1] = 128 | s >> 12 & 63, e[t + o + 2] = 128 | s >> 6 & 63, e[t + o + 3] = 128 | s >> 0 & 63, o += 4;
                        }
                    }
                    return o;
                }, sizeUTF8: function (e) {
                    for (var t = e.length, r = 0, i = 0; i < t; i++) {
                        var o = e.charCodeAt(i);
                        if (0 == (4294967168 & o))
                            r++;
                        else if (0 == (4294965248 & o))
                            r += 2;
                        else if (0 == (4294901760 & o))
                            r += 3;
                        else {
                            if (0 != (4292870144 & o))
                                throw "e";
                            r += 4;
                        }
                    }
                    return r;
                }
            }, UZIP.F = {}, UZIP.F.deflateRaw = function (e, t, r, i) {
                var o = [[0, 0, 0, 0, 0], [4, 4, 8, 4, 0], [4, 5, 16, 8, 0], [4, 6, 16, 16, 0], [4, 10, 16, 32, 0], [8, 16, 32, 32, 0], [8, 16, 128, 128, 0], [8, 32, 128, 256, 0], [32, 128, 258, 1024, 1], [32, 258, 258, 4096, 1]][i],
                    a = UZIP.F.U, s = UZIP.F._goodIndex;
                UZIP.F._hash;
                var f = UZIP.F._putsE, l = 0, c = r << 3, u = 0, h = e.length;
                if (0 == i) {
                    for (; l < h;) {
                        f(t, c, l + (_ = Math.min(65535, h - l)) == h ? 1 : 0), c = UZIP.F._copyExact(e, l, _, t, c + 8), l += _;
                    }
                    return c >>> 3;
                }
                var d = a.lits, A = a.strt, g = a.prev, p = 0, m = 0, w = 0, v = 0, b = 0, y = 0;
                for (h > 2 && (A[y = UZIP.F._hash(e, 0)] = 0), l = 0; l < h; l++) {
                    if (b = y, l + 1 < h - 2) {
                        y = UZIP.F._hash(e, l + 1);
                        var E = l + 1 & 32767;
                        g[E] = A[y], A[y] = E;
                    }
                    if (u <= l) {
                        (p > 14e3 || m > 26697) && h - l > 100 && (u < l && (d[p] = l - u, p += 2, u = l), c = UZIP.F._writeBlock(l == h - 1 || u == h ? 1 : 0, d, p, v, e, w, l - w, t, c), p = m = v = 0, w = l);
                        var F = 0;
                        l < h - 2 && (F = UZIP.F._bestMatch(e, l, g, b, Math.min(o[2], h - l), o[3]));
                        var _ = F >>> 16, B = 65535 & F;
                        if (0 != F) {
                            B = 65535 & F;
                            var U = s(_ = F >>> 16, a.of0);
                            a.lhst[257 + U]++;
                            var C = s(B, a.df0);
                            a.dhst[C]++, v += a.exb[U] + a.dxb[C], d[p] = _ << 23 | l - u, d[p + 1] = B << 16 | U << 8 | C, p += 2, u = l + _;
                        } else
                            a.lhst[e[l]]++;
                        m++;
                    }
                }
                for (w == l && 0 != e.length || (u < l && (d[p] = l - u, p += 2, u = l), c = UZIP.F._writeBlock(1, d, p, v, e, w, l - w, t, c), p = 0, m = 0, p = m = v = 0, w = l); 0 != (7 & c);)
                    c++;
                return c >>> 3;
            }, UZIP.F._bestMatch = function (e, t, r, i, o, a) {
                var s = 32767 & t, f = r[s], l = s - f + 32768 & 32767;
                if (f == s || i != UZIP.F._hash(e, t - l))
                    return 0;
                for (var c = 0, u = 0, h = Math.min(32767, t); l <= h && 0 != --a && f != s;) {
                    if (0 == c || e[t + c] == e[t + c - l]) {
                        var d = UZIP.F._howLong(e, t, l);
                        if (d > c) {
                            if (u = l, (c = d) >= o)
                                break;
                            l + 2 < d && (d = l + 2);
                            for (var A = 0, g = 0; g < d - 2; g++) {
                                var p = t - l + g + 32768 & 32767, m = p - r[p] + 32768 & 32767;
                                m > A && (A = m, f = p);
                            }
                        }
                    }
                    l += (s = f) - (f = r[s]) + 32768 & 32767;
                }
                return c << 16 | u;
            }, UZIP.F._howLong = function (e, t, r) {
                if (e[t] != e[t - r] || e[t + 1] != e[t + 1 - r] || e[t + 2] != e[t + 2 - r])
                    return 0;
                var i = t, o = Math.min(e.length, t + 258);
                for (t += 3; t < o && e[t] == e[t - r];)
                    t++;
                return t - i;
            }, UZIP.F._hash = function (e, t) {
                return (e[t] << 8 | e[t + 1]) + (e[t + 2] << 4) & 65535;
            }, UZIP.saved = 0, UZIP.F._writeBlock = function (e, t, r, i, o, a, s, f, l) {
                var c, u, h, d, A, g, p, m, w, v = UZIP.F.U, b = UZIP.F._putsF, y = UZIP.F._putsE;
                v.lhst[256]++, u = (c = UZIP.F.getTrees())[0], h = c[1], d = c[2], A = c[3], g = c[4], p = c[5], m = c[6], w = c[7];
                var E = 32 + (0 == (l + 3 & 7) ? 0 : 8 - (l + 3 & 7)) + (s << 3),
                    F = i + UZIP.F.contSize(v.fltree, v.lhst) + UZIP.F.contSize(v.fdtree, v.dhst),
                    _ = i + UZIP.F.contSize(v.ltree, v.lhst) + UZIP.F.contSize(v.dtree, v.dhst);
                _ += 14 + 3 * p + UZIP.F.contSize(v.itree, v.ihst) + (2 * v.ihst[16] + 3 * v.ihst[17] + 7 * v.ihst[18]);
                for (var B = 0; B < 286; B++)
                    v.lhst[B] = 0;
                for (B = 0; B < 30; B++)
                    v.dhst[B] = 0;
                for (B = 0; B < 19; B++)
                    v.ihst[B] = 0;
                var U = E < F && E < _ ? 0 : F < _ ? 1 : 2;
                if (b(f, l, e), b(f, l + 1, U), l += 3, 0 == U) {
                    for (; 0 != (7 & l);)
                        l++;
                    l = UZIP.F._copyExact(o, a, s, f, l);
                } else {
                    var C, I;
                    if (1 == U && (C = v.fltree, I = v.fdtree), 2 == U) {
                        UZIP.F.makeCodes(v.ltree, u), UZIP.F.revCodes(v.ltree, u), UZIP.F.makeCodes(v.dtree, h), UZIP.F.revCodes(v.dtree, h), UZIP.F.makeCodes(v.itree, d), UZIP.F.revCodes(v.itree, d), C = v.ltree, I = v.dtree, y(f, l, A - 257), y(f, l += 5, g - 1), y(f, l += 5, p - 4), l += 4;
                        for (var Q = 0; Q < p; Q++)
                            y(f, l + 3 * Q, v.itree[1 + (v.ordr[Q] << 1)]);
                        l += 3 * p, l = UZIP.F._codeTiny(m, v.itree, f, l), l = UZIP.F._codeTiny(w, v.itree, f, l);
                    }
                    for (var M = a, x = 0; x < r; x += 2) {
                        for (var T = t[x], S = T >>> 23, R = M + (8388607 & T); M < R;)
                            l = UZIP.F._writeLit(o[M++], C, f, l);
                        if (0 != S) {
                            var O = t[x + 1], P = O >> 16, H = O >> 8 & 255, L = 255 & O;
                            y(f, l = UZIP.F._writeLit(257 + H, C, f, l), S - v.of0[H]), l += v.exb[H], b(f, l = UZIP.F._writeLit(L, I, f, l), P - v.df0[L]), l += v.dxb[L], M += S;
                        }
                    }
                    l = UZIP.F._writeLit(256, C, f, l);
                }
                return l;
            }, UZIP.F._copyExact = function (e, t, r, i, o) {
                var a = o >>> 3;
                return i[a] = r, i[a + 1] = r >>> 8, i[a + 2] = 255 - i[a], i[a + 3] = 255 - i[a + 1], a += 4, i.set(new Uint8Array(e.buffer, t, r), a), o + (r + 4 << 3);
            }, UZIP.F.getTrees = function () {
                for (var e = UZIP.F.U, t = UZIP.F._hufTree(e.lhst, e.ltree, 15), r = UZIP.F._hufTree(e.dhst, e.dtree, 15), i = [], o = UZIP.F._lenCodes(e.ltree, i), a = [], s = UZIP.F._lenCodes(e.dtree, a), f = 0; f < i.length; f += 2)
                    e.ihst[i[f]]++;
                for (f = 0; f < a.length; f += 2)
                    e.ihst[a[f]]++;
                for (var l = UZIP.F._hufTree(e.ihst, e.itree, 7), c = 19; c > 4 && 0 == e.itree[1 + (e.ordr[c - 1] << 1)];)
                    c--;
                return [t, r, l, o, s, c, i, a];
            }, UZIP.F.getSecond = function (e) {
                for (var t = [], r = 0; r < e.length; r += 2)
                    t.push(e[r + 1]);
                return t;
            }, UZIP.F.nonZero = function (e) {
                for (var t = "", r = 0; r < e.length; r += 2)
                    0 != e[r + 1] && (t += (r >> 1) + ",");
                return t;
            }, UZIP.F.contSize = function (e, t) {
                for (var r = 0, i = 0; i < t.length; i++)
                    r += t[i] * e[1 + (i << 1)];
                return r;
            }, UZIP.F._codeTiny = function (e, t, r, i) {
                for (var o = 0; o < e.length; o += 2) {
                    var a = e[o], s = e[o + 1];
                    i = UZIP.F._writeLit(a, t, r, i);
                    var f = 16 == a ? 2 : 17 == a ? 3 : 7;
                    a > 15 && (UZIP.F._putsE(r, i, s, f), i += f);
                }
                return i;
            }, UZIP.F._lenCodes = function (e, t) {
                for (var r = e.length; 2 != r && 0 == e[r - 1];)
                    r -= 2;
                for (var i = 0; i < r; i += 2) {
                    var o = e[i + 1], a = i + 3 < r ? e[i + 3] : -1, s = i + 5 < r ? e[i + 5] : -1,
                        f = 0 == i ? -1 : e[i - 1];
                    if (0 == o && a == o && s == o) {
                        for (var l = i + 5; l + 2 < r && e[l + 2] == o;)
                            l += 2;
                        (c = Math.min(l + 1 - i >>> 1, 138)) < 11 ? t.push(17, c - 3) : t.push(18, c - 11), i += 2 * c - 2;
                    } else if (o == f && a == o && s == o) {
                        for (l = i + 5; l + 2 < r && e[l + 2] == o;)
                            l += 2;
                        var c = Math.min(l + 1 - i >>> 1, 6);
                        t.push(16, c - 3), i += 2 * c - 2;
                    } else
                        t.push(o, 0);
                }
                return r >>> 1;
            }, UZIP.F._hufTree = function (e, t, r) {
                var i = [], o = e.length, a = t.length, s = 0;
                for (s = 0; s < a; s += 2)
                    t[s] = 0, t[s + 1] = 0;
                for (s = 0; s < o; s++)
                    0 != e[s] && i.push({lit: s, f: e[s]});
                var f = i.length, l = i.slice(0);
                if (0 == f)
                    return 0;
                if (1 == f) {
                    var c = i[0].lit;
                    l = 0 == c ? 1 : 0;
                    return t[1 + (c << 1)] = 1, t[1 + (l << 1)] = 1, 1;
                }
                i.sort((function (e, t) {
                    return e.f - t.f;
                }));
                var u = i[0], h = i[1], d = 0, A = 1, g = 2;
                for (i[0] = {
                    lit: -1,
                    f: u.f + h.f,
                    l: u,
                    r: h,
                    d: 0
                }; A != f - 1;)
                    u = d != A && (g == f || i[d].f < i[g].f) ? i[d++] : i[g++], h = d != A && (g == f || i[d].f < i[g].f) ? i[d++] : i[g++], i[A++] = {
                        lit: -1,
                        f: u.f + h.f,
                        l: u,
                        r: h
                    };
                var p = UZIP.F.setDepth(i[A - 1], 0);
                for (p > r && (UZIP.F.restrictDepth(l, r, p), p = r), s = 0; s < f; s++)
                    t[1 + (l[s].lit << 1)] = l[s].d;
                return p;
            }, UZIP.F.setDepth = function (e, t) {
                return -1 != e.lit ? (e.d = t, t) : Math.max(UZIP.F.setDepth(e.l, t + 1), UZIP.F.setDepth(e.r, t + 1));
            }, UZIP.F.restrictDepth = function (e, t, r) {
                var i = 0, o = 1 << r - t, a = 0;
                for (e.sort((function (e, t) {
                    return t.d == e.d ? e.f - t.f : t.d - e.d;
                })), i = 0; i < e.length && e[i].d > t; i++) {
                    var s = e[i].d;
                    e[i].d = t, a += o - (1 << r - s);
                }
                for (a >>>= r - t; a > 0;) {
                    (s = e[i].d) < t ? (e[i].d++, a -= 1 << t - s - 1) : i++;
                }
                for (; i >= 0; i--)
                    e[i].d == t && a < 0 && (e[i].d--, a++);
                0 != a && console.log("debt left");
            }, UZIP.F._goodIndex = function (e, t) {
                var r = 0;
                return t[16 | r] <= e && (r |= 16), t[8 | r] <= e && (r |= 8), t[4 | r] <= e && (r |= 4), t[2 | r] <= e && (r |= 2), t[1 | r] <= e && (r |= 1), r;
            }, UZIP.F._writeLit = function (e, t, r, i) {
                return UZIP.F._putsF(r, i, t[e << 1]), i + t[1 + (e << 1)];
            }, UZIP.F.inflate = function (e, t) {
                var r = Uint8Array;
                if (3 == e[0] && 0 == e[1])
                    return t || new r(0);
                var i = UZIP.F, o = i._bitsF, a = i._bitsE, s = i._decodeTiny, f = i.makeCodes, l = i.codes2map,
                    c = i._get17, u = i.U, h = null == t;
                h && (t = new r(e.length >>> 2 << 3));
                for (var d, A, g = 0, p = 0, m = 0, w = 0, v = 0, b = 0, y = 0, E = 0, F = 0; 0 == g;)
                    if (g = o(e, F, 1), p = o(e, F + 1, 2), F += 3, 0 != p) {
                        if (h && (t = UZIP.F._check(t, E + (1 << 17))), 1 == p && (d = u.flmap, A = u.fdmap, b = 511, y = 31), 2 == p) {
                            m = a(e, F, 5) + 257, w = a(e, F + 5, 5) + 1, v = a(e, F + 10, 4) + 4, F += 14;
                            for (var _ = 0; _ < 38; _ += 2)
                                u.itree[_] = 0, u.itree[_ + 1] = 0;
                            var B = 1;
                            for (_ = 0; _ < v; _++) {
                                var U = a(e, F + 3 * _, 3);
                                u.itree[1 + (u.ordr[_] << 1)] = U, U > B && (B = U);
                            }
                            F += 3 * v, f(u.itree, B), l(u.itree, B, u.imap), d = u.lmap, A = u.dmap, F = s(u.imap, (1 << B) - 1, m + w, e, F, u.ttree);
                            var C = i._copyOut(u.ttree, 0, m, u.ltree);
                            b = (1 << C) - 1;
                            var I = i._copyOut(u.ttree, m, w, u.dtree);
                            y = (1 << I) - 1, f(u.ltree, C), l(u.ltree, C, d), f(u.dtree, I), l(u.dtree, I, A);
                        }
                        for (; ;) {
                            var Q = d[c(e, F) & b];
                            F += 15 & Q;
                            var M = Q >>> 4;
                            if (M >>> 8 == 0)
                                t[E++] = M;
                            else {
                                if (256 == M)
                                    break;
                                var x = E + M - 254;
                                if (M > 264) {
                                    var T = u.ldef[M - 257];
                                    x = E + (T >>> 3) + a(e, F, 7 & T), F += 7 & T;
                                }
                                var S = A[c(e, F) & y];
                                F += 15 & S;
                                var R = S >>> 4, O = u.ddef[R], P = (O >>> 4) + o(e, F, 15 & O);
                                for (F += 15 & O, h && (t = UZIP.F._check(t, E + (1 << 17))); E < x;)
                                    t[E] = t[E++ - P], t[E] = t[E++ - P], t[E] = t[E++ - P], t[E] = t[E++ - P];
                                E = x;
                            }
                        }
                    } else {
                        0 != (7 & F) && (F += 8 - (7 & F));
                        var H = 4 + (F >>> 3), L = e[H - 4] | e[H - 3] << 8;
                        h && (t = UZIP.F._check(t, E + L)), t.set(new r(e.buffer, e.byteOffset + H, L), E), F = H + L << 3, E += L;
                    }
                return t.length == E ? t : t.slice(0, E);
            }, UZIP.F._check = function (e, t) {
                var r = e.length;
                if (t <= r)
                    return e;
                var i = new Uint8Array(Math.max(r << 1, t));
                return i.set(e, 0), i;
            }, UZIP.F._decodeTiny = function (e, t, r, i, o, a) {
                for (var s = UZIP.F._bitsE, f = UZIP.F._get17, l = 0; l < r;) {
                    var c = e[f(i, o) & t];
                    o += 15 & c;
                    var u = c >>> 4;
                    if (u <= 15)
                        a[l] = u, l++;
                    else {
                        var h = 0, d = 0;
                        16 == u ? (d = 3 + s(i, o, 2), o += 2, h = a[l - 1]) : 17 == u ? (d = 3 + s(i, o, 3), o += 3) : 18 == u && (d = 11 + s(i, o, 7), o += 7);
                        for (var A = l + d; l < A;)
                            a[l] = h, l++;
                    }
                }
                return o;
            }, UZIP.F._copyOut = function (e, t, r, i) {
                for (var o = 0, a = 0, s = i.length >>> 1; a < r;) {
                    var f = e[a + t];
                    i[a << 1] = 0, i[1 + (a << 1)] = f, f > o && (o = f), a++;
                }
                for (; a < s;)
                    i[a << 1] = 0, i[1 + (a << 1)] = 0, a++;
                return o;
            }, UZIP.F.makeCodes = function (e, t) {
                for (var r, i, o, a, s = UZIP.F.U, f = e.length, l = s.bl_count, c = 0; c <= t; c++)
                    l[c] = 0;
                for (c = 1; c < f; c += 2)
                    l[e[c]]++;
                var u = s.next_code;
                for (r = 0, l[0] = 0, i = 1; i <= t; i++)
                    r = r + l[i - 1] << 1, u[i] = r;
                for (o = 0; o < f; o += 2)
                    0 != (a = e[o + 1]) && (e[o] = u[a], u[a]++);
            }, UZIP.F.codes2map = function (e, t, r) {
                for (var i = e.length, o = UZIP.F.U.rev15, a = 0; a < i; a += 2)
                    if (0 != e[a + 1])
                        for (var s = a >> 1, f = e[a + 1], l = s << 4 | f, c = t - f, u = e[a] << c, h = u + (1 << c); u != h;) {
                            r[o[u] >>> 15 - t] = l, u++;
                        }
            }, UZIP.F.revCodes = function (e, t) {
                for (var r = UZIP.F.U.rev15, i = 15 - t, o = 0; o < e.length; o += 2) {
                    var a = e[o] << t - e[o + 1];
                    e[o] = r[a] >>> i;
                }
            }, UZIP.F._putsE = function (e, t, r) {
                r <<= 7 & t;
                var i = t >>> 3;
                e[i] |= r, e[i + 1] |= r >>> 8;
            }, UZIP.F._putsF = function (e, t, r) {
                r <<= 7 & t;
                var i = t >>> 3;
                e[i] |= r, e[i + 1] |= r >>> 8, e[i + 2] |= r >>> 16;
            }, UZIP.F._bitsE = function (e, t, r) {
                return (e[t >>> 3] | e[1 + (t >>> 3)] << 8) >>> (7 & t) & (1 << r) - 1;
            }, UZIP.F._bitsF = function (e, t, r) {
                return (e[t >>> 3] | e[1 + (t >>> 3)] << 8 | e[2 + (t >>> 3)] << 16) >>> (7 & t) & (1 << r) - 1;
            }, UZIP.F._get17 = function (e, t) {
                return (e[t >>> 3] | e[1 + (t >>> 3)] << 8 | e[2 + (t >>> 3)] << 16) >>> (7 & t);
            }, UZIP.F._get25 = function (e, t) {
                return (e[t >>> 3] | e[1 + (t >>> 3)] << 8 | e[2 + (t >>> 3)] << 16 | e[3 + (t >>> 3)] << 24) >>> (7 & t);
            }, UZIP.F.U = (t = Uint16Array, r = Uint32Array, {
                next_code: new t(16),
                bl_count: new t(16),
                ordr: [16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15],
                of0: [3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 15, 17, 19, 23, 27, 31, 35, 43, 51, 59, 67, 83, 99, 115, 131, 163, 195, 227, 258, 999, 999, 999],
                exb: [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 0, 0, 0, 0],
                ldef: new t(32),
                df0: [1, 2, 3, 4, 5, 7, 9, 13, 17, 25, 33, 49, 65, 97, 129, 193, 257, 385, 513, 769, 1025, 1537, 2049, 3073, 4097, 6145, 8193, 12289, 16385, 24577, 65535, 65535],
                dxb: [0, 0, 0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 11, 12, 12, 13, 13, 0, 0],
                ddef: new r(32),
                flmap: new t(512),
                fltree: [],
                fdmap: new t(32),
                fdtree: [],
                lmap: new t(32768),
                ltree: [],
                ttree: [],
                dmap: new t(32768),
                dtree: [],
                imap: new t(512),
                itree: [],
                rev15: new t(32768),
                lhst: new r(286),
                dhst: new r(30),
                ihst: new r(19),
                lits: new r(15e3),
                strt: new t(65536),
                prev: new t(32768)
            }), function () {
                for (var e = UZIP.F.U, t = 0; t < 32768; t++) {
                    var r = t;
                    r = (4278255360 & (r = (4042322160 & (r = (3435973836 & (r = (2863311530 & r) >>> 1 | (1431655765 & r) << 1)) >>> 2 | (858993459 & r) << 2)) >>> 4 | (252645135 & r) << 4)) >>> 8 | (16711935 & r) << 8, e.rev15[t] = (r >>> 16 | r << 16) >>> 17;
                }
    
                function pushV(e, t, r) {
                    for (; 0 != t--;)
                        e.push(0, r);
                }
    
                for (t = 0; t < 32; t++)
                    e.ldef[t] = e.of0[t] << 3 | e.exb[t], e.ddef[t] = e.df0[t] << 4 | e.dxb[t];
                pushV(e.fltree, 144, 8), pushV(e.fltree, 112, 9), pushV(e.fltree, 24, 7), pushV(e.fltree, 8, 8), UZIP.F.makeCodes(e.fltree, 9), UZIP.F.codes2map(e.fltree, 9, e.flmap), UZIP.F.revCodes(e.fltree, 9), pushV(e.fdtree, 32, 5), UZIP.F.makeCodes(e.fdtree, 5), UZIP.F.codes2map(e.fdtree, 5, e.fdmap), UZIP.F.revCodes(e.fdtree, 5), pushV(e.itree, 19, 0), pushV(e.ltree, 286, 0), pushV(e.dtree, 30, 0), pushV(e.ttree, 320, 0);
            }();
        }({
            get exports() {
                return e;
            }, set exports(t) {
                e = t;
            }
        });
        var UZIP = _mergeNamespaces({__proto__: null, default: e}, [e]);
        var UPNG = function () {
            var e = {
                nextZero: function (e, t) {
                    for (; 0 != e[t];)
                        t++;
                    return t;
                },
                readUshort: function (e, t) {
                    return e[t] << 8 | e[t + 1];
                },
                writeUshort: function (e, t, r) {
                    e[t] = r >> 8 & 255, e[t + 1] = 255 & r;
                },
                readUint: function (e, t) {
                    return 16777216 * e[t] + (e[t + 1] << 16 | e[t + 2] << 8 | e[t + 3]);
                },
                writeUint: function (e, t, r) {
                    e[t] = r >> 24 & 255, e[t + 1] = r >> 16 & 255, e[t + 2] = r >> 8 & 255, e[t + 3] = 255 & r;
                },
                readASCII: function (e, t, r) {
                    var i = "";
                    for (var o_1 = 0; o_1 < r; o_1++)
                        i += String.fromCharCode(e[t + o_1]);
                    return i;
                },
                writeASCII: function (e, t, r) {
                    for (var i_1 = 0; i_1 < r.length; i_1++)
                        e[t + i_1] = r.charCodeAt(i_1);
                },
                readBytes: function (e, t, r) {
                    var i = [];
                    for (var o_2 = 0; o_2 < r; o_2++)
                        i.push(e[t + o_2]);
                    return i;
                },
                pad: function (e) {
                    return e.length < 2 ? "0".concat(e) : e;
                },
                readUTF8: function (t, r, i) {
                    var o, a = "";
                    for (var o_3 = 0; o_3 < i; o_3++)
                        a += "%".concat(e.pad(t[r + o_3].toString(16)));
                    try {
                        o = decodeURIComponent(a);
                    } catch (o) {
                        return e.readASCII(t, r, i);
                    }
                    return o;
                }
            };
    
            function decodeImage(t, r, i, o) {
                var a = r * i, s = _getBPP(o), f = Math.ceil(r * s / 8), l = new Uint8Array(4 * a),
                    c = new Uint32Array(l.buffer), u = o.ctype, h = o.depth, d = e.readUshort;
                if (6 == u) {
                    var e_2 = a << 2;
                    if (8 == h)
                        for (var A = 0; A < e_2; A += 4)
                            l[A] = t[A], l[A + 1] = t[A + 1], l[A + 2] = t[A + 2], l[A + 3] = t[A + 3];
                    if (16 == h)
                        for (A = 0; A < e_2; A++)
                            l[A] = t[A << 1];
                } else if (2 == u) {
                    var e_3 = o.tabs.tRNS;
                    if (null == e_3) {
                        if (8 == h)
                            for (A = 0; A < a; A++) {
                                var g = 3 * A;
                                c[A] = 255 << 24 | t[g + 2] << 16 | t[g + 1] << 8 | t[g];
                            }
                        if (16 == h)
                            for (A = 0; A < a; A++) {
                                g = 6 * A;
                                c[A] = 255 << 24 | t[g + 4] << 16 | t[g + 2] << 8 | t[g];
                            }
                    } else {
                        var p = e_3[0];
                        var r_1 = e_3[1], i_2 = e_3[2];
                        if (8 == h)
                            for (A = 0; A < a; A++) {
                                var m = A << 2;
                                g = 3 * A;
                                c[A] = 255 << 24 | t[g + 2] << 16 | t[g + 1] << 8 | t[g], t[g] == p && t[g + 1] == r_1 && t[g + 2] == i_2 && (l[m + 3] = 0);
                            }
                        if (16 == h)
                            for (A = 0; A < a; A++) {
                                m = A << 2, g = 6 * A;
                                c[A] = 255 << 24 | t[g + 4] << 16 | t[g + 2] << 8 | t[g], d(t, g) == p && d(t, g + 2) == r_1 && d(t, g + 4) == i_2 && (l[m + 3] = 0);
                            }
                    }
                } else if (3 == u) {
                    var e_4 = o.tabs.PLTE, s_2 = o.tabs.tRNS, c_1 = s_2 ? s_2.length : 0;
                    if (1 == h)
                        for (var w = 0; w < i; w++) {
                            var v = w * f, b = w * r;
                            for (A = 0; A < r; A++) {
                                m = b + A << 2;
                                var y = 3 * (E = t[v + (A >> 3)] >> 7 - ((7 & A) << 0) & 1);
                                l[m] = e_4[y], l[m + 1] = e_4[y + 1], l[m + 2] = e_4[y + 2], l[m + 3] = E < c_1 ? s_2[E] : 255;
                            }
                        }
                    if (2 == h)
                        for (w = 0; w < i; w++)
                            for (v = w * f, b = w * r, A = 0; A < r; A++) {
                                m = b + A << 2, y = 3 * (E = t[v + (A >> 2)] >> 6 - ((3 & A) << 1) & 3);
                                l[m] = e_4[y], l[m + 1] = e_4[y + 1], l[m + 2] = e_4[y + 2], l[m + 3] = E < c_1 ? s_2[E] : 255;
                            }
                    if (4 == h)
                        for (w = 0; w < i; w++)
                            for (v = w * f, b = w * r, A = 0; A < r; A++) {
                                m = b + A << 2, y = 3 * (E = t[v + (A >> 1)] >> 4 - ((1 & A) << 2) & 15);
                                l[m] = e_4[y], l[m + 1] = e_4[y + 1], l[m + 2] = e_4[y + 2], l[m + 3] = E < c_1 ? s_2[E] : 255;
                            }
                    if (8 == h)
                        for (A = 0; A < a; A++) {
                            var E;
                            m = A << 2, y = 3 * (E = t[A]);
                            l[m] = e_4[y], l[m + 1] = e_4[y + 1], l[m + 2] = e_4[y + 2], l[m + 3] = E < c_1 ? s_2[E] : 255;
                        }
                } else if (4 == u) {
                    if (8 == h)
                        for (A = 0; A < a; A++) {
                            m = A << 2;
                            var F = t[_ = A << 1];
                            l[m] = F, l[m + 1] = F, l[m + 2] = F, l[m + 3] = t[_ + 1];
                        }
                    if (16 == h)
                        for (A = 0; A < a; A++) {
                            var _;
                            m = A << 2, F = t[_ = A << 2];
                            l[m] = F, l[m + 1] = F, l[m + 2] = F, l[m + 3] = t[_ + 2];
                        }
                } else if (0 == u)
                    for (p = o.tabs.tRNS ? o.tabs.tRNS : -1, w = 0; w < i; w++) {
                        var e_5 = w * f, i_3 = w * r;
                        if (1 == h)
                            for (var B = 0; B < r; B++) {
                                var U = (F = 255 * (t[e_5 + (B >>> 3)] >>> 7 - (7 & B) & 1)) == 255 * p ? 0 : 255;
                                c[i_3 + B] = U << 24 | F << 16 | F << 8 | F;
                            }
                        else if (2 == h)
                            for (B = 0; B < r; B++) {
                                U = (F = 85 * (t[e_5 + (B >>> 2)] >>> 6 - ((3 & B) << 1) & 3)) == 85 * p ? 0 : 255;
                                c[i_3 + B] = U << 24 | F << 16 | F << 8 | F;
                            }
                        else if (4 == h)
                            for (B = 0; B < r; B++) {
                                U = (F = 17 * (t[e_5 + (B >>> 1)] >>> 4 - ((1 & B) << 2) & 15)) == 17 * p ? 0 : 255;
                                c[i_3 + B] = U << 24 | F << 16 | F << 8 | F;
                            }
                        else if (8 == h)
                            for (B = 0; B < r; B++) {
                                U = (F = t[e_5 + B]) == p ? 0 : 255;
                                c[i_3 + B] = U << 24 | F << 16 | F << 8 | F;
                            }
                        else if (16 == h)
                            for (B = 0; B < r; B++) {
                                F = t[e_5 + (B << 1)], U = d(t, e_5 + (B << 1)) == p ? 0 : 255;
                                c[i_3 + B] = U << 24 | F << 16 | F << 8 | F;
                            }
                    }
                return l;
            }
    
            function _decompress(e, r, i, o) {
                var a = _getBPP(e), s = Math.ceil(i * a / 8), f = new Uint8Array((s + 1 + e.interlace) * o);
                return r = e.tabs.CgBI ? t(r, f) : _inflate(r, f), 0 == e.interlace ? r = _filterZero(r, e, 0, i, o) : 1 == e.interlace && (r = function _readInterlace(e, t) {
                    var r = t.width, i = t.height, o = _getBPP(t), a = o >> 3, s = Math.ceil(r * o / 8),
                        f = new Uint8Array(i * s);
                    var l = 0;
                    var c = [0, 0, 4, 0, 2, 0, 1], u = [0, 4, 0, 2, 0, 1, 0], h = [8, 8, 8, 4, 4, 2, 2],
                        d = [8, 8, 4, 4, 2, 2, 1];
                    var A = 0;
                    for (; A < 7;) {
                        var p = h[A], m = d[A];
                        var w = 0, v = 0, b = c[A];
                        for (; b < i;)
                            b += p, v++;
                        var y = u[A];
                        for (; y < r;)
                            y += m, w++;
                        var E = Math.ceil(w * o / 8);
                        _filterZero(e, t, l, w, v);
                        var F = 0, _ = c[A];
                        for (; _ < i;) {
                            var t_1 = u[A], i_4 = l + F * E << 3;
                            for (; t_1 < r;) {
                                var g;
                                if (1 == o)
                                    g = (g = e[i_4 >> 3]) >> 7 - (7 & i_4) & 1, f[_ * s + (t_1 >> 3)] |= g << 7 - ((7 & t_1) << 0);
                                if (2 == o)
                                    g = (g = e[i_4 >> 3]) >> 6 - (7 & i_4) & 3, f[_ * s + (t_1 >> 2)] |= g << 6 - ((3 & t_1) << 1);
                                if (4 == o)
                                    g = (g = e[i_4 >> 3]) >> 4 - (7 & i_4) & 15, f[_ * s + (t_1 >> 1)] |= g << 4 - ((1 & t_1) << 2);
                                if (o >= 8) {
                                    var r_2 = _ * s + t_1 * a;
                                    for (var t_2 = 0; t_2 < a; t_2++)
                                        f[r_2 + t_2] = e[(i_4 >> 3) + t_2];
                                }
                                i_4 += o, t_1 += m;
                            }
                            F++, _ += p;
                        }
                        w * v != 0 && (l += v * (1 + E)), A += 1;
                    }
                    return f;
                }(r, e)), r;
            }
    
            function _inflate(e, r) {
                return t(new Uint8Array(e.buffer, 2, e.length - 6), r);
            }
    
            var t = function () {
                var e = {H: {}};
                return e.H.N = function (t, r) {
                    var i = Uint8Array;
                    var o, a, s = 0, f = 0, l = 0, c = 0, u = 0, h = 0, d = 0, A = 0, g = 0;
                    if (3 == t[0] && 0 == t[1])
                        return r || new i(0);
                    var p = e.H, m = p.b, w = p.e, v = p.R, b = p.n, y = p.A, E = p.Z, F = p.m, _ = null == r;
                    for (_ && (r = new i(t.length >>> 2 << 5)); 0 == s;)
                        if (s = m(t, g, 1), f = m(t, g + 1, 2), g += 3, 0 != f) {
                            if (_ && (r = e.H.W(r, A + (1 << 17))), 1 == f && (o = F.J, a = F.h, h = 511, d = 31), 2 == f) {
                                l = w(t, g, 5) + 257, c = w(t, g + 5, 5) + 1, u = w(t, g + 10, 4) + 4, g += 14;
                                var e_6 = 1;
                                for (var B = 0; B < 38; B += 2)
                                    F.Q[B] = 0, F.Q[B + 1] = 0;
                                for (B = 0; B < u; B++) {
                                    var r_3 = w(t, g + 3 * B, 3);
                                    F.Q[1 + (F.X[B] << 1)] = r_3, r_3 > e_6 && (e_6 = r_3);
                                }
                                g += 3 * u, b(F.Q, e_6), y(F.Q, e_6, F.u), o = F.w, a = F.d, g = v(F.u, (1 << e_6) - 1, l + c, t, g, F.v);
                                var r_4 = p.V(F.v, 0, l, F.C);
                                h = (1 << r_4) - 1;
                                var i_5 = p.V(F.v, l, c, F.D);
                                d = (1 << i_5) - 1, b(F.C, r_4), y(F.C, r_4, o), b(F.D, i_5), y(F.D, i_5, a);
                            }
                            for (; ;) {
                                var e_7 = o[E(t, g) & h];
                                g += 15 & e_7;
                                var i_6 = e_7 >>> 4;
                                if (i_6 >>> 8 == 0)
                                    r[A++] = i_6;
                                else {
                                    if (256 == i_6)
                                        break;
                                    {
                                        var e_8 = A + i_6 - 254;
                                        if (i_6 > 264) {
                                            var r_5 = F.q[i_6 - 257];
                                            e_8 = A + (r_5 >>> 3) + w(t, g, 7 & r_5), g += 7 & r_5;
                                        }
                                        var o_4 = a[E(t, g) & d];
                                        g += 15 & o_4;
                                        var s_3 = o_4 >>> 4, f_2 = F.c[s_3], l_2 = (f_2 >>> 4) + m(t, g, 15 & f_2);
                                        for (g += 15 & f_2; A < e_8;)
                                            r[A] = r[A++ - l_2], r[A] = r[A++ - l_2], r[A] = r[A++ - l_2], r[A] = r[A++ - l_2];
                                        A = e_8;
                                    }
                                }
                            }
                        } else {
                            0 != (7 & g) && (g += 8 - (7 & g));
                            var o_5 = 4 + (g >>> 3), a_3 = t[o_5 - 4] | t[o_5 - 3] << 8;
                            _ && (r = e.H.W(r, A + a_3)), r.set(new i(t.buffer, t.byteOffset + o_5, a_3), A), g = o_5 + a_3 << 3, A += a_3;
                        }
                    return r.length == A ? r : r.slice(0, A);
                }, e.H.W = function (e, t) {
                    var r = e.length;
                    if (t <= r)
                        return e;
                    var i = new Uint8Array(r << 1);
                    return i.set(e, 0), i;
                }, e.H.R = function (t, r, i, o, a, s) {
                    var f = e.H.e, l = e.H.Z;
                    var c = 0;
                    for (; c < i;) {
                        var e_9 = t[l(o, a) & r];
                        a += 15 & e_9;
                        var i_7 = e_9 >>> 4;
                        if (i_7 <= 15)
                            s[c] = i_7, c++;
                        else {
                            var e_10 = 0, t_3 = 0;
                            16 == i_7 ? (t_3 = 3 + f(o, a, 2), a += 2, e_10 = s[c - 1]) : 17 == i_7 ? (t_3 = 3 + f(o, a, 3), a += 3) : 18 == i_7 && (t_3 = 11 + f(o, a, 7), a += 7);
                            var r_6 = c + t_3;
                            for (; c < r_6;)
                                s[c] = e_10, c++;
                        }
                    }
                    return a;
                }, e.H.V = function (e, t, r, i) {
                    var o = 0, a = 0;
                    var s = i.length >>> 1;
                    for (; a < r;) {
                        var r_7 = e[a + t];
                        i[a << 1] = 0, i[1 + (a << 1)] = r_7, r_7 > o && (o = r_7), a++;
                    }
                    for (; a < s;)
                        i[a << 1] = 0, i[1 + (a << 1)] = 0, a++;
                    return o;
                }, e.H.n = function (t, r) {
                    var i = e.H.m, o = t.length;
                    var a, s, f;
                    var l;
                    var c = i.j;
                    for (var u = 0; u <= r; u++)
                        c[u] = 0;
                    for (u = 1; u < o; u += 2)
                        c[t[u]]++;
                    var h = i.K;
                    for (a = 0, c[0] = 0, s = 1; s <= r; s++)
                        a = a + c[s - 1] << 1, h[s] = a;
                    for (f = 0; f < o; f += 2)
                        l = t[f + 1], 0 != l && (t[f] = h[l], h[l]++);
                }, e.H.A = function (t, r, i) {
                    var o = t.length, a = e.H.m.r;
                    for (var e_11 = 0; e_11 < o; e_11 += 2)
                        if (0 != t[e_11 + 1]) {
                            var o_6 = e_11 >> 1, s_4 = t[e_11 + 1], f_3 = o_6 << 4 | s_4, l_3 = r - s_4;
                            var c = t[e_11] << l_3;
                            var u = c + (1 << l_3);
                            for (; c != u;) {
                                i[a[c] >>> 15 - r] = f_3, c++;
                            }
                        }
                }, e.H.l = function (t, r) {
                    var i = e.H.m.r, o = 15 - r;
                    for (var e_12 = 0; e_12 < t.length; e_12 += 2) {
                        var a_4 = t[e_12] << r - t[e_12 + 1];
                        t[e_12] = i[a_4] >>> o;
                    }
                }, e.H.M = function (e, t, r) {
                    r <<= 7 & t;
                    var i = t >>> 3;
                    e[i] |= r, e[i + 1] |= r >>> 8;
                }, e.H.I = function (e, t, r) {
                    r <<= 7 & t;
                    var i = t >>> 3;
                    e[i] |= r, e[i + 1] |= r >>> 8, e[i + 2] |= r >>> 16;
                }, e.H.e = function (e, t, r) {
                    return (e[t >>> 3] | e[1 + (t >>> 3)] << 8) >>> (7 & t) & (1 << r) - 1;
                }, e.H.b = function (e, t, r) {
                    return (e[t >>> 3] | e[1 + (t >>> 3)] << 8 | e[2 + (t >>> 3)] << 16) >>> (7 & t) & (1 << r) - 1;
                }, e.H.Z = function (e, t) {
                    return (e[t >>> 3] | e[1 + (t >>> 3)] << 8 | e[2 + (t >>> 3)] << 16) >>> (7 & t);
                }, e.H.i = function (e, t) {
                    return (e[t >>> 3] | e[1 + (t >>> 3)] << 8 | e[2 + (t >>> 3)] << 16 | e[3 + (t >>> 3)] << 24) >>> (7 & t);
                }, e.H.m = function () {
                    var e = Uint16Array, t = Uint32Array;
                    return {
                        K: new e(16),
                        j: new e(16),
                        X: [16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15],
                        S: [3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 15, 17, 19, 23, 27, 31, 35, 43, 51, 59, 67, 83, 99, 115, 131, 163, 195, 227, 258, 999, 999, 999],
                        T: [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 0, 0, 0, 0],
                        q: new e(32),
                        p: [1, 2, 3, 4, 5, 7, 9, 13, 17, 25, 33, 49, 65, 97, 129, 193, 257, 385, 513, 769, 1025, 1537, 2049, 3073, 4097, 6145, 8193, 12289, 16385, 24577, 65535, 65535],
                        z: [0, 0, 0, 0, 1, 1, 2, 2, 3, 3, 4, 4, 5, 5, 6, 6, 7, 7, 8, 8, 9, 9, 10, 10, 11, 11, 12, 12, 13, 13, 0, 0],
                        c: new t(32),
                        J: new e(512),
                        _: [],
                        h: new e(32),
                        $: [],
                        w: new e(32768),
                        C: [],
                        v: [],
                        d: new e(32768),
                        D: [],
                        u: new e(512),
                        Q: [],
                        r: new e(32768),
                        s: new t(286),
                        Y: new t(30),
                        a: new t(19),
                        t: new t(15e3),
                        k: new e(65536),
                        g: new e(32768)
                    };
                }(), function () {
                    var t = e.H.m;
                    for (var r = 0; r < 32768; r++) {
                        var e_13 = r;
                        e_13 = (2863311530 & e_13) >>> 1 | (1431655765 & e_13) << 1, e_13 = (3435973836 & e_13) >>> 2 | (858993459 & e_13) << 2, e_13 = (4042322160 & e_13) >>> 4 | (252645135 & e_13) << 4, e_13 = (4278255360 & e_13) >>> 8 | (16711935 & e_13) << 8, t.r[r] = (e_13 >>> 16 | e_13 << 16) >>> 17;
                    }
    
                    function n(e, t, r) {
                        for (; 0 != t--;)
                            e.push(0, r);
                    }
    
                    for (r = 0; r < 32; r++)
                        t.q[r] = t.S[r] << 3 | t.T[r], t.c[r] = t.p[r] << 4 | t.z[r];
                    n(t._, 144, 8), n(t._, 112, 9), n(t._, 24, 7), n(t._, 8, 8), e.H.n(t._, 9), e.H.A(t._, 9, t.J), e.H.l(t._, 9), n(t.$, 32, 5), e.H.n(t.$, 5), e.H.A(t.$, 5, t.h), e.H.l(t.$, 5), n(t.Q, 19, 0), n(t.C, 286, 0), n(t.D, 30, 0), n(t.v, 320, 0);
                }(), e.H.N;
            }();
    
            function _getBPP(e) {
                return [1, null, 3, 1, 2, null, 4][e.ctype] * e.depth;
            }
    
            function _filterZero(e, t, r, i, o) {
                var a = _getBPP(t);
                var s = Math.ceil(i * a / 8);
                var f, l;
                a = Math.ceil(a / 8);
                var c = e[r], u = 0;
                if (c > 1 && (e[r] = [0, 0, 1][c - 2]), 3 == c)
                    for (u = a; u < s; u++)
                        e[u + 1] = e[u + 1] + (e[u + 1 - a] >>> 1) & 255;
                for (var t_4 = 0; t_4 < o; t_4++)
                    if (f = r + t_4 * s, l = f + t_4 + 1, c = e[l - 1], u = 0, 0 == c)
                        for (; u < s; u++)
                            e[f + u] = e[l + u];
                    else if (1 == c) {
                        for (; u < a; u++)
                            e[f + u] = e[l + u];
                        for (; u < s; u++)
                            e[f + u] = e[l + u] + e[f + u - a];
                    } else if (2 == c)
                        for (; u < s; u++)
                            e[f + u] = e[l + u] + e[f + u - s];
                    else if (3 == c) {
                        for (; u < a; u++)
                            e[f + u] = e[l + u] + (e[f + u - s] >>> 1);
                        for (; u < s; u++)
                            e[f + u] = e[l + u] + (e[f + u - s] + e[f + u - a] >>> 1);
                    } else {
                        for (; u < a; u++)
                            e[f + u] = e[l + u] + _paeth(0, e[f + u - s], 0);
                        for (; u < s; u++)
                            e[f + u] = e[l + u] + _paeth(e[f + u - a], e[f + u - s], e[f + u - a - s]);
                    }
                return e;
            }
    
            function _paeth(e, t, r) {
                var i = e + t - r, o = i - e, a = i - t, s = i - r;
                return o * o <= a * a && o * o <= s * s ? e : a * a <= s * s ? t : r;
            }
    
            function _IHDR(t, r, i) {
                i.width = e.readUint(t, r), r += 4, i.height = e.readUint(t, r), r += 4, i.depth = t[r], r++, i.ctype = t[r], r++, i.compress = t[r], r++, i.filter = t[r], r++, i.interlace = t[r], r++;
            }
    
            function _copyTile(e, t, r, i, o, a, s, f, l) {
                var c = Math.min(t, o), u = Math.min(r, a);
                var h = 0, d = 0;
                for (var r_8 = 0; r_8 < u; r_8++)
                    for (var a_5 = 0; a_5 < c; a_5++)
                        if (s >= 0 && f >= 0 ? (h = r_8 * t + a_5 << 2, d = (f + r_8) * o + s + a_5 << 2) : (h = (-f + r_8) * t - s + a_5 << 2, d = r_8 * o + a_5 << 2), 0 == l)
                            i[d] = e[h], i[d + 1] = e[h + 1], i[d + 2] = e[h + 2], i[d + 3] = e[h + 3];
                        else if (1 == l) {
                            var A = e[h + 3] * (1 / 255), g = e[h] * A, p = e[h + 1] * A, m = e[h + 2] * A,
                                w = i[d + 3] * (1 / 255), v = i[d] * w, b = i[d + 1] * w, y = i[d + 2] * w;
                            var t_5 = 1 - A, r_9 = A + w * t_5, o_7 = 0 == r_9 ? 0 : 1 / r_9;
                            i[d + 3] = 255 * r_9, i[d + 0] = (g + v * t_5) * o_7, i[d + 1] = (p + b * t_5) * o_7, i[d + 2] = (m + y * t_5) * o_7;
                        } else if (2 == l) {
                            A = e[h + 3], g = e[h], p = e[h + 1], m = e[h + 2], w = i[d + 3], v = i[d], b = i[d + 1], y = i[d + 2];
                            A == w && g == v && p == b && m == y ? (i[d] = 0, i[d + 1] = 0, i[d + 2] = 0, i[d + 3] = 0) : (i[d] = g, i[d + 1] = p, i[d + 2] = m, i[d + 3] = A);
                        } else if (3 == l) {
                            A = e[h + 3], g = e[h], p = e[h + 1], m = e[h + 2], w = i[d + 3], v = i[d], b = i[d + 1], y = i[d + 2];
                            if (A == w && g == v && p == b && m == y)
                                continue;
                            if (A < 220 && w > 20)
                                return !1;
                        }
                return !0;
            }
    
            return {
                decode: function decode(r) {
                    var i = new Uint8Array(r);
                    var o = 8;
                    var a = e, s = a.readUshort, f = a.readUint, l = {tabs: {}, frames: []}, c = new Uint8Array(i.length);
                    var u, h = 0, d = 0;
                    var A = [137, 80, 78, 71, 13, 10, 26, 10];
                    for (var g = 0; g < 8; g++)
                        if (i[g] != A[g])
                            throw "The input is not a PNG file!";
                    for (; o < i.length;) {
                        var e_14 = a.readUint(i, o);
                        o += 4;
                        var r_10 = a.readASCII(i, o, 4);
                        if (o += 4, "IHDR" == r_10)
                            _IHDR(i, o, l);
                        else if ("iCCP" == r_10) {
                            for (var p = o; 0 != i[p];)
                                p++;
                            a.readASCII(i, o, p - o), i[p + 1];
                            var s_5 = i.slice(p + 2, o + e_14);
                            var f_4 = null;
                            try {
                                f_4 = _inflate(s_5);
                            } catch (e) {
                                f_4 = t(s_5);
                            }
                            l.tabs[r_10] = f_4;
                        } else if ("CgBI" == r_10)
                            l.tabs[r_10] = i.slice(o, o + 4);
                        else if ("IDAT" == r_10) {
                            for (g = 0; g < e_14; g++)
                                c[h + g] = i[o + g];
                            h += e_14;
                        } else if ("acTL" == r_10)
                            l.tabs[r_10] = {
                                num_frames: f(i, o),
                                num_plays: f(i, o + 4)
                            }, u = new Uint8Array(i.length);
                        else if ("fcTL" == r_10) {
                            if (0 != d)
                                (E = l.frames[l.frames.length - 1]).data = _decompress(l, u.slice(0, d), E.rect.width, E.rect.height), d = 0;
                            var e_15 = {x: f(i, o + 12), y: f(i, o + 16), width: f(i, o + 4), height: f(i, o + 8)};
                            var t_6 = s(i, o + 22);
                            t_6 = s(i, o + 20) / (0 == t_6 ? 100 : t_6);
                            var r_11 = {rect: e_15, delay: Math.round(1e3 * t_6), dispose: i[o + 24], blend: i[o + 25]};
                            l.frames.push(r_11);
                        } else if ("fdAT" == r_10) {
                            for (g = 0; g < e_14 - 4; g++)
                                u[d + g] = i[o + g + 4];
                            d += e_14 - 4;
                        } else if ("pHYs" == r_10)
                            l.tabs[r_10] = [a.readUint(i, o), a.readUint(i, o + 4), i[o + 8]];
                        else if ("cHRM" == r_10) {
                            l.tabs[r_10] = [];
                            for (g = 0; g < 8; g++)
                                l.tabs[r_10].push(a.readUint(i, o + 4 * g));
                        } else if ("tEXt" == r_10 || "zTXt" == r_10) {
                            null == l.tabs[r_10] && (l.tabs[r_10] = {});
                            var m = a.nextZero(i, o), w = a.readASCII(i, o, m - o), v = o + e_14 - m - 1;
                            if ("tEXt" == r_10)
                                y = a.readASCII(i, m + 1, v);
                            else {
                                var b = _inflate(i.slice(m + 2, m + 2 + v));
                                y = a.readUTF8(b, 0, b.length);
                            }
                            l.tabs[r_10][w] = y;
                        } else if ("iTXt" == r_10) {
                            null == l.tabs[r_10] && (l.tabs[r_10] = {});
                            m = 0, p = o;
                            m = a.nextZero(i, p);
                            w = a.readASCII(i, p, m - p);
                            var t_7 = i[p = m + 1];
                            var y;
                            i[p + 1], p += 2, m = a.nextZero(i, p), a.readASCII(i, p, m - p), p = m + 1, m = a.nextZero(i, p), a.readUTF8(i, p, m - p);
                            v = e_14 - ((p = m + 1) - o);
                            if (0 == t_7)
                                y = a.readUTF8(i, p, v);
                            else {
                                b = _inflate(i.slice(p, p + v));
                                y = a.readUTF8(b, 0, b.length);
                            }
                            l.tabs[r_10][w] = y;
                        } else if ("PLTE" == r_10)
                            l.tabs[r_10] = a.readBytes(i, o, e_14);
                        else if ("hIST" == r_10) {
                            var e_16 = l.tabs.PLTE.length / 3;
                            l.tabs[r_10] = [];
                            for (g = 0; g < e_16; g++)
                                l.tabs[r_10].push(s(i, o + 2 * g));
                        } else if ("tRNS" == r_10)
                            3 == l.ctype ? l.tabs[r_10] = a.readBytes(i, o, e_14) : 0 == l.ctype ? l.tabs[r_10] = s(i, o) : 2 == l.ctype && (l.tabs[r_10] = [s(i, o), s(i, o + 2), s(i, o + 4)]);
                        else if ("gAMA" == r_10)
                            l.tabs[r_10] = a.readUint(i, o) / 1e5;
                        else if ("sRGB" == r_10)
                            l.tabs[r_10] = i[o];
                        else if ("bKGD" == r_10)
                            0 == l.ctype || 4 == l.ctype ? l.tabs[r_10] = [s(i, o)] : 2 == l.ctype || 6 == l.ctype ? l.tabs[r_10] = [s(i, o), s(i, o + 2), s(i, o + 4)] : 3 == l.ctype && (l.tabs[r_10] = i[o]);
                        else if ("IEND" == r_10)
                            break;
                        o += e_14, a.readUint(i, o), o += 4;
                    }
                    var E;
                    return 0 != d && ((E = l.frames[l.frames.length - 1]).data = _decompress(l, u.slice(0, d), E.rect.width, E.rect.height)), l.data = _decompress(l, c, l.width, l.height), delete l.compress, delete l.interlace, delete l.filter, l;
                }, toRGBA8: function toRGBA8(e) {
                    var t = e.width, r = e.height;
                    if (null == e.tabs.acTL)
                        return [decodeImage(e.data, t, r, e).buffer];
                    var i = [];
                    null == e.frames[0].data && (e.frames[0].data = e.data);
                    var o = t * r * 4, a = new Uint8Array(o), s = new Uint8Array(o), f = new Uint8Array(o);
                    for (var c = 0; c < e.frames.length; c++) {
                        var u = e.frames[c], h = u.rect.x, d = u.rect.y, A = u.rect.width, g = u.rect.height,
                            p = decodeImage(u.data, A, g, e);
                        if (0 != c)
                            for (var l = 0; l < o; l++)
                                f[l] = a[l];
                        if (0 == u.blend ? _copyTile(p, A, g, a, t, r, h, d, 0) : 1 == u.blend && _copyTile(p, A, g, a, t, r, h, d, 1), i.push(a.buffer.slice(0)), 0 == u.dispose)
                            ;
                        else if (1 == u.dispose)
                            _copyTile(s, A, g, a, t, r, h, d, 0);
                        else if (2 == u.dispose)
                            for (l = 0; l < o; l++)
                                a[l] = f[l];
                    }
                    return i;
                }, _paeth: _paeth, _copyTile: _copyTile, _bin: e
            };
        }();
        !function () {
            var e = UPNG._copyTile, t = UPNG._bin, r = UPNG._paeth;
            var i = {
                table: function () {
                    var e = new Uint32Array(256);
                    for (var t_8 = 0; t_8 < 256; t_8++) {
                        var r_12 = t_8;
                        for (var e_17 = 0; e_17 < 8; e_17++)
                            1 & r_12 ? r_12 = 3988292384 ^ r_12 >>> 1 : r_12 >>>= 1;
                        e[t_8] = r_12;
                    }
                    return e;
                }(),
                update: function (e, t, r, o) {
                    for (var a_6 = 0; a_6 < o; a_6++)
                        e = i.table[255 & (e ^ t[r + a_6])] ^ e >>> 8;
                    return e;
                },
                crc: function (e, t, r) {
                    return 4294967295 ^ i.update(4294967295, e, t, r);
                }
            };
    
            function addErr(e, t, r, i) {
                t[r] += e[0] * i >> 4, t[r + 1] += e[1] * i >> 4, t[r + 2] += e[2] * i >> 4, t[r + 3] += e[3] * i >> 4;
            }
    
            function N(e) {
                return Math.max(0, Math.min(255, e));
            }
    
            function D(e, t) {
                var r = e[0] - t[0], i = e[1] - t[1], o = e[2] - t[2], a = e[3] - t[3];
                return r * r + i * i + o * o + a * a;
            }
    
            function dither(e, t, r, i, o, a, s) {
                null == s && (s = 1);
                var f = i.length, l = [];
                for (var c = 0; c < f; c++) {
                    var e_18 = i[c];
                    l.push([e_18 >>> 0 & 255, e_18 >>> 8 & 255, e_18 >>> 16 & 255, e_18 >>> 24 & 255]);
                }
                for (c = 0; c < f; c++) {
                    var e_19 = 4294967295;
                    for (var u = 0, h = 0; h < f; h++) {
                        var d = D(l[c], l[h]);
                        h != c && d < e_19 && (e_19 = d, u = h);
                    }
                }
                var A = new Uint32Array(o.buffer), g = new Int16Array(t * r * 4),
                    p = [0, 8, 2, 10, 12, 4, 14, 6, 3, 11, 1, 9, 15, 7, 13, 5];
                for (c = 0; c < p.length; c++)
                    p[c] = 255 * ((p[c] + .5) / 16 - .5);
                for (var o_8 = 0; o_8 < r; o_8++)
                    for (var w = 0; w < t; w++) {
                        var m;
                        c = 4 * (o_8 * t + w);
                        if (2 != s)
                            m = [N(e[c] + g[c]), N(e[c + 1] + g[c + 1]), N(e[c + 2] + g[c + 2]), N(e[c + 3] + g[c + 3])];
                        else {
                            d = p[4 * (3 & o_8) + (3 & w)];
                            m = [N(e[c] + d), N(e[c + 1] + d), N(e[c + 2] + d), N(e[c + 3] + d)];
                        }
                        u = 0;
                        var v = 16777215;
                        for (h = 0; h < f; h++) {
                            var e_20 = D(m, l[h]);
                            e_20 < v && (v = e_20, u = h);
                        }
                        var b = l[u], y = [m[0] - b[0], m[1] - b[1], m[2] - b[2], m[3] - b[3]];
                        1 == s && (w != t - 1 && addErr(y, g, c + 4, 7), o_8 != r - 1 && (0 != w && addErr(y, g, c + 4 * t - 4, 3), addErr(y, g, c + 4 * t, 5), w != t - 1 && addErr(y, g, c + 4 * t + 4, 1))), a[c >> 2] = u, A[c >> 2] = i[u];
                    }
            }
    
            function _main(e, r, o, a, s) {
                null == s && (s = {});
                var f = i.crc, l = t.writeUint, c = t.writeUshort, u = t.writeASCII;
                var h = 8;
                var d = e.frames.length > 1;
                var A, g = !1, p = 33 + (d ? 20 : 0);
                if (null != s.sRGB && (p += 13), null != s.pHYs && (p += 21), null != s.iCCP && (A = pako.deflate(s.iCCP), p += 21 + A.length + 4), 3 == e.ctype) {
                    for (var m = e.plte.length, w = 0; w < m; w++)
                        e.plte[w] >>> 24 != 255 && (g = !0);
                    p += 8 + 3 * m + 4 + (g ? 8 + 1 * m + 4 : 0);
                }
                for (var v = 0; v < e.frames.length; v++) {
                    d && (p += 38), p += (F = e.frames[v]).cimg.length + 12, 0 != v && (p += 4);
                }
                p += 12;
                var b = new Uint8Array(p), y = [137, 80, 78, 71, 13, 10, 26, 10];
                for (w = 0; w < 8; w++)
                    b[w] = y[w];
                if (l(b, h, 13), h += 4, u(b, h, "IHDR"), h += 4, l(b, h, r), h += 4, l(b, h, o), h += 4, b[h] = e.depth, h++, b[h] = e.ctype, h++, b[h] = 0, h++, b[h] = 0, h++, b[h] = 0, h++, l(b, h, f(b, h - 17, 17)), h += 4, null != s.sRGB && (l(b, h, 1), h += 4, u(b, h, "sRGB"), h += 4, b[h] = s.sRGB, h++, l(b, h, f(b, h - 5, 5)), h += 4), null != s.iCCP) {
                    var e_21 = 13 + A.length;
                    l(b, h, e_21), h += 4, u(b, h, "iCCP"), h += 4, u(b, h, "ICC profile"), h += 11, h += 2, b.set(A, h), h += A.length, l(b, h, f(b, h - (e_21 + 4), e_21 + 4)), h += 4;
                }
                if (null != s.pHYs && (l(b, h, 9), h += 4, u(b, h, "pHYs"), h += 4, l(b, h, s.pHYs[0]), h += 4, l(b, h, s.pHYs[1]), h += 4, b[h] = s.pHYs[2], h++, l(b, h, f(b, h - 13, 13)), h += 4), d && (l(b, h, 8), h += 4, u(b, h, "acTL"), h += 4, l(b, h, e.frames.length), h += 4, l(b, h, null != s.loop ? s.loop : 0), h += 4, l(b, h, f(b, h - 12, 12)), h += 4), 3 == e.ctype) {
                    l(b, h, 3 * (m = e.plte.length)), h += 4, u(b, h, "PLTE"), h += 4;
                    for (w = 0; w < m; w++) {
                        var t_9 = 3 * w, r_13 = e.plte[w], i_8 = 255 & r_13, o_9 = r_13 >>> 8 & 255,
                            a_7 = r_13 >>> 16 & 255;
                        b[h + t_9 + 0] = i_8, b[h + t_9 + 1] = o_9, b[h + t_9 + 2] = a_7;
                    }
                    if (h += 3 * m, l(b, h, f(b, h - 3 * m - 4, 3 * m + 4)), h += 4, g) {
                        l(b, h, m), h += 4, u(b, h, "tRNS"), h += 4;
                        for (w = 0; w < m; w++)
                            b[h + w] = e.plte[w] >>> 24 & 255;
                        h += m, l(b, h, f(b, h - m - 4, m + 4)), h += 4;
                    }
                }
                var E = 0;
                for (v = 0; v < e.frames.length; v++) {
                    var F = e.frames[v];
                    d && (l(b, h, 26), h += 4, u(b, h, "fcTL"), h += 4, l(b, h, E++), h += 4, l(b, h, F.rect.width), h += 4, l(b, h, F.rect.height), h += 4, l(b, h, F.rect.x), h += 4, l(b, h, F.rect.y), h += 4, c(b, h, a[v]), h += 2, c(b, h, 1e3), h += 2, b[h] = F.dispose, h++, b[h] = F.blend, h++, l(b, h, f(b, h - 30, 30)), h += 4);
                    var t_10 = F.cimg;
                    l(b, h, (m = t_10.length) + (0 == v ? 0 : 4)), h += 4;
                    var r_14 = h;
                    u(b, h, 0 == v ? "IDAT" : "fdAT"), h += 4, 0 != v && (l(b, h, E++), h += 4), b.set(t_10, h), h += m, l(b, h, f(b, r_14, h - r_14)), h += 4;
                }
                return l(b, h, 0), h += 4, u(b, h, "IEND"), h += 4, l(b, h, f(b, h - 4, 4)), h += 4, b.buffer;
            }
    
            function compressPNG(e, t, r) {
                for (var i_9 = 0; i_9 < e.frames.length; i_9++) {
                    var o_10 = e.frames[i_9];
                    o_10.rect.width;
                    var a_8 = o_10.rect.height, s_6 = new Uint8Array(a_8 * o_10.bpl + a_8);
                    o_10.cimg = _filterZero(o_10.img, a_8, o_10.bpp, o_10.bpl, s_6, t, r);
                }
            }
    
            function compress(t, r, i, o, a) {
                var s = a[0], f = a[1], l = a[2], c = a[3], u = a[4], h = a[5];
                var d = 6, A = 8, g = 255;
                for (var p = 0; p < t.length; p++) {
                    var e_22 = new Uint8Array(t[p]);
                    for (var m = e_22.length, w = 0; w < m; w += 4)
                        g &= e_22[w + 3];
                }
                var v = 255 != g, b = function framize(t, r, i, o, a, s) {
                    var f = [];
                    for (var l = 0; l < t.length; l++) {
                        var h_1 = new Uint8Array(t[l]), A_1 = new Uint32Array(h_1.buffer);
                        var c;
                        var g_1 = 0, p_1 = 0, m_1 = r, w_1 = i, v_1 = o ? 1 : 0;
                        if (0 != l) {
                            var b_1 = s || o || 1 == l || 0 != f[l - 2].dispose ? 1 : 2;
                            var y_1 = 0, E_1 = 1e9;
                            for (var e_23 = 0; e_23 < b_1; e_23++) {
                                var u = new Uint8Array(t[l - 1 - e_23]);
                                var o_11 = new Uint32Array(t[l - 1 - e_23]);
                                var s_7 = r, f_5 = i, c_2 = -1, h_2 = -1;
                                for (var e_24 = 0; e_24 < i; e_24++)
                                    for (var t_11 = 0; t_11 < r; t_11++) {
                                        A_1[d = e_24 * r + t_11] != o_11[d] && (t_11 < s_7 && (s_7 = t_11), t_11 > c_2 && (c_2 = t_11), e_24 < f_5 && (f_5 = e_24), e_24 > h_2 && (h_2 = e_24));
                                    }
                                -1 == c_2 && (s_7 = f_5 = c_2 = h_2 = 0), a && (1 == (1 & s_7) && s_7--, 1 == (1 & f_5) && f_5--);
                                var v_2 = (c_2 - s_7 + 1) * (h_2 - f_5 + 1);
                                v_2 < E_1 && (E_1 = v_2, y_1 = e_23, g_1 = s_7, p_1 = f_5, m_1 = c_2 - s_7 + 1, w_1 = h_2 - f_5 + 1);
                            }
                            u = new Uint8Array(t[l - 1 - y_1]);
                            1 == y_1 && (f[l - 1].dispose = 2), c = new Uint8Array(m_1 * w_1 * 4), e(u, r, i, c, m_1, w_1, -g_1, -p_1, 0), v_1 = e(h_1, r, i, c, m_1, w_1, -g_1, -p_1, 3) ? 1 : 0, 1 == v_1 ? _prepareDiff(h_1, r, i, c, {
                                x: g_1,
                                y: p_1,
                                width: m_1,
                                height: w_1
                            }) : e(h_1, r, i, c, m_1, w_1, -g_1, -p_1, 0);
                        } else
                            c = h_1.slice(0);
                        f.push({rect: {x: g_1, y: p_1, width: m_1, height: w_1}, img: c, blend: v_1, dispose: 0});
                    }
                    if (o)
                        for (l = 0; l < f.length; l++) {
                            if (1 == (A = f[l]).blend)
                                continue;
                            var e_25 = A.rect, o_12 = f[l - 1].rect, s_8 = Math.min(e_25.x, o_12.x),
                                c_3 = Math.min(e_25.y, o_12.y), u_1 = {
                                    x: s_8,
                                    y: c_3,
                                    width: Math.max(e_25.x + e_25.width, o_12.x + o_12.width) - s_8,
                                    height: Math.max(e_25.y + e_25.height, o_12.y + o_12.height) - c_3
                                };
                            f[l - 1].dispose = 1, l - 1 != 0 && _updateFrame(t, r, i, f, l - 1, u_1, a), _updateFrame(t, r, i, f, l, u_1, a);
                        }
                    var h = 0;
                    if (1 != t.length)
                        for (var d = 0; d < f.length; d++) {
                            var A;
                            h += (A = f[d]).rect.width * A.rect.height;
                        }
                    return f;
                }(t, r, i, s, f, l), y = {}, E = [], F = [];
                if (0 != o) {
                    var e_26 = [];
                    for (w = 0; w < b.length; w++)
                        e_26.push(b[w].img.buffer);
                    var t_12 = function concatRGBA(e) {
                        var t = 0;
                        for (var r = 0; r < e.length; r++)
                            t += e[r].byteLength;
                        var i = new Uint8Array(t);
                        var o = 0;
                        for (r = 0; r < e.length; r++) {
                            var t_13 = new Uint8Array(e[r]), a_9 = t_13.length;
                            for (var e_27 = 0; e_27 < a_9; e_27 += 4) {
                                var r_15 = t_13[e_27], a_10 = t_13[e_27 + 1], s_9 = t_13[e_27 + 2];
                                var f_6 = t_13[e_27 + 3];
                                0 == f_6 && (r_15 = a_10 = s_9 = 0), i[o + e_27] = r_15, i[o + e_27 + 1] = a_10, i[o + e_27 + 2] = s_9, i[o + e_27 + 3] = f_6;
                            }
                            o += a_9;
                        }
                        return i.buffer;
                    }(e_26), r_16 = quantize(t_12, o);
                    for (w = 0; w < r_16.plte.length; w++)
                        E.push(r_16.plte[w].est.rgba);
                    var i_10 = 0;
                    for (w = 0; w < b.length; w++) {
                        var e_28 = (B = b[w]).img.length;
                        var _ = new Uint8Array(r_16.inds.buffer, i_10 >> 2, e_28 >> 2);
                        F.push(_);
                        var t_14 = new Uint8Array(r_16.abuf, i_10, e_28);
                        h && dither(B.img, B.rect.width, B.rect.height, E, t_14, _), B.img.set(t_14), i_10 += e_28;
                    }
                } else
                    for (p = 0; p < b.length; p++) {
                        var B = b[p];
                        var e_29 = new Uint32Array(B.img.buffer);
                        var U = B.rect.width;
                        m = e_29.length, _ = new Uint8Array(m);
                        F.push(_);
                        for (w = 0; w < m; w++) {
                            var t_15 = e_29[w];
                            if (0 != w && t_15 == e_29[w - 1])
                                _[w] = _[w - 1];
                            else if (w > U && t_15 == e_29[w - U])
                                _[w] = _[w - U];
                            else {
                                var e_30 = y[t_15];
                                if (null == e_30 && (y[t_15] = e_30 = E.length, E.push(t_15), E.length >= 300))
                                    break;
                                _[w] = e_30;
                            }
                        }
                    }
                var C = E.length;
                C <= 256 && 0 == u && (A = C <= 2 ? 1 : C <= 4 ? 2 : C <= 16 ? 4 : 8, A = Math.max(A, c));
                for (p = 0; p < b.length; p++) {
                    (B = b[p]).rect.x, B.rect.y;
                    U = B.rect.width;
                    var e_31 = B.rect.height;
                    var t_16 = B.img;
                    new Uint32Array(t_16.buffer);
                    var r_17 = 4 * U, i_11 = 4;
                    if (C <= 256 && 0 == u) {
                        r_17 = Math.ceil(A * U / 8);
                        var I = new Uint8Array(r_17 * e_31);
                        var o_13 = F[p];
                        for (var t_17 = 0; t_17 < e_31; t_17++) {
                            w = t_17 * r_17;
                            var e_32 = t_17 * U;
                            if (8 == A)
                                for (var Q = 0; Q < U; Q++)
                                    I[w + Q] = o_13[e_32 + Q];
                            else if (4 == A)
                                for (Q = 0; Q < U; Q++)
                                    I[w + (Q >> 1)] |= o_13[e_32 + Q] << 4 - 4 * (1 & Q);
                            else if (2 == A)
                                for (Q = 0; Q < U; Q++)
                                    I[w + (Q >> 2)] |= o_13[e_32 + Q] << 6 - 2 * (3 & Q);
                            else if (1 == A)
                                for (Q = 0; Q < U; Q++)
                                    I[w + (Q >> 3)] |= o_13[e_32 + Q] << 7 - 1 * (7 & Q);
                        }
                        t_16 = I, d = 3, i_11 = 1;
                    } else if (0 == v && 1 == b.length) {
                        I = new Uint8Array(U * e_31 * 3);
                        var o_14 = U * e_31;
                        for (w = 0; w < o_14; w++) {
                            var e_33 = 3 * w, r_18 = 4 * w;
                            I[e_33] = t_16[r_18], I[e_33 + 1] = t_16[r_18 + 1], I[e_33 + 2] = t_16[r_18 + 2];
                        }
                        t_16 = I, d = 2, i_11 = 3, r_17 = 3 * U;
                    }
                    B.img = t_16, B.bpl = r_17, B.bpp = i_11;
                }
                return {ctype: d, depth: A, plte: E, frames: b};
            }
    
            function _updateFrame(t, r, i, o, a, s, f) {
                var l = Uint8Array, c = Uint32Array, u = new l(t[a - 1]), h = new c(t[a - 1]),
                    d = a + 1 < t.length ? new l(t[a + 1]) : null, A = new l(t[a]), g = new c(A.buffer);
                var p = r, m = i, w = -1, v = -1;
                for (var e_34 = 0; e_34 < s.height; e_34++)
                    for (var t_18 = 0; t_18 < s.width; t_18++) {
                        var i_12 = s.x + t_18, f_7 = s.y + e_34, l_4 = f_7 * r + i_12, c_4 = g[l_4];
                        0 == c_4 || 0 == o[a - 1].dispose && h[l_4] == c_4 && (null == d || 0 != d[4 * l_4 + 3]) || (i_12 < p && (p = i_12), i_12 > w && (w = i_12), f_7 < m && (m = f_7), f_7 > v && (v = f_7));
                    }
                -1 == w && (p = m = w = v = 0), f && (1 == (1 & p) && p--, 1 == (1 & m) && m--), s = {
                    x: p,
                    y: m,
                    width: w - p + 1,
                    height: v - m + 1
                };
                var b = o[a];
                b.rect = s, b.blend = 1, b.img = new Uint8Array(s.width * s.height * 4), 0 == o[a - 1].dispose ? (e(u, r, i, b.img, s.width, s.height, -s.x, -s.y, 0), _prepareDiff(A, r, i, b.img, s)) : e(A, r, i, b.img, s.width, s.height, -s.x, -s.y, 0);
            }
    
            function _prepareDiff(t, r, i, o, a) {
                e(t, r, i, o, a.width, a.height, -a.x, -a.y, 2);
            }
    
            function _filterZero(e, t, r, i, o, a, s) {
                var f = [];
                var l, c = [0, 1, 2, 3, 4];
                -1 != a ? c = [a] : (t * i > 5e5 || 1 == r) && (c = [0]), s && (l = {level: 0});
                var u = UZIP;
                for (var h = 0; h < c.length; h++) {
                    for (var a_11 = 0; a_11 < t; a_11++)
                        _filterLine(o, e, a_11, i, r, c[h]);
                    f.push(u.deflate(o, l));
                }
                var d, A = 1e9;
                for (h = 0; h < f.length; h++)
                    f[h].length < A && (d = h, A = f[h].length);
                return f[d];
            }
    
            function _filterLine(e, t, i, o, a, s) {
                var f = i * o;
                var l = f + i;
                if (e[l] = s, l++, 0 == s)
                    if (o < 500)
                        for (var c = 0; c < o; c++)
                            e[l + c] = t[f + c];
                    else
                        e.set(new Uint8Array(t.buffer, f, o), l);
                else if (1 == s) {
                    for (c = 0; c < a; c++)
                        e[l + c] = t[f + c];
                    for (c = a; c < o; c++)
                        e[l + c] = t[f + c] - t[f + c - a] + 256 & 255;
                } else if (0 == i) {
                    for (c = 0; c < a; c++)
                        e[l + c] = t[f + c];
                    if (2 == s)
                        for (c = a; c < o; c++)
                            e[l + c] = t[f + c];
                    if (3 == s)
                        for (c = a; c < o; c++)
                            e[l + c] = t[f + c] - (t[f + c - a] >> 1) + 256 & 255;
                    if (4 == s)
                        for (c = a; c < o; c++)
                            e[l + c] = t[f + c] - r(t[f + c - a], 0, 0) + 256 & 255;
                } else {
                    if (2 == s)
                        for (c = 0; c < o; c++)
                            e[l + c] = t[f + c] + 256 - t[f + c - o] & 255;
                    if (3 == s) {
                        for (c = 0; c < a; c++)
                            e[l + c] = t[f + c] + 256 - (t[f + c - o] >> 1) & 255;
                        for (c = a; c < o; c++)
                            e[l + c] = t[f + c] + 256 - (t[f + c - o] + t[f + c - a] >> 1) & 255;
                    }
                    if (4 == s) {
                        for (c = 0; c < a; c++)
                            e[l + c] = t[f + c] + 256 - r(0, t[f + c - o], 0) & 255;
                        for (c = a; c < o; c++)
                            e[l + c] = t[f + c] + 256 - r(t[f + c - a], t[f + c - o], t[f + c - a - o]) & 255;
                    }
                }
            }
    
            function quantize(e, t) {
                var r = new Uint8Array(e), i = r.slice(0), o = new Uint32Array(i.buffer), a = getKDtree(i, t), s = a[0],
                    f = a[1], l = r.length, c = new Uint8Array(l >> 2);
                var u;
                if (r.length < 2e7)
                    for (var h = 0; h < l; h += 4) {
                        u = getNearest(s, d = r[h] * (1 / 255), A = r[h + 1] * (1 / 255), g = r[h + 2] * (1 / 255), p = r[h + 3] * (1 / 255)), c[h >> 2] = u.ind, o[h >> 2] = u.est.rgba;
                    }
                else
                    for (h = 0; h < l; h += 4) {
                        var d = r[h] * (1 / 255), A = r[h + 1] * (1 / 255), g = r[h + 2] * (1 / 255),
                            p = r[h + 3] * (1 / 255);
                        for (u = s; u.left;)
                            u = planeDst(u.est, d, A, g, p) <= 0 ? u.left : u.right;
                        c[h >> 2] = u.ind, o[h >> 2] = u.est.rgba;
                    }
                return {abuf: i.buffer, inds: c, plte: f};
            }
    
            function getKDtree(e, t, r) {
                null == r && (r = 1e-4);
                var i = new Uint32Array(e.buffer),
                    o = {i0: 0, i1: e.length, bst: null, est: null, tdst: 0, left: null, right: null};
                o.bst = stats(e, o.i0, o.i1), o.est = estats(o.bst);
                var a = [o];
                for (; a.length < t;) {
                    var t_19 = 0, o_15 = 0;
                    for (var s = 0; s < a.length; s++)
                        a[s].est.L > t_19 && (t_19 = a[s].est.L, o_15 = s);
                    if (t_19 < r)
                        break;
                    var f_8 = a[o_15], l_5 = splitPixels(e, i, f_8.i0, f_8.i1, f_8.est.e, f_8.est.eMq255);
                    if (f_8.i0 >= l_5 || f_8.i1 <= l_5) {
                        f_8.est.L = 0;
                        continue;
                    }
                    var c = {i0: f_8.i0, i1: l_5, bst: null, est: null, tdst: 0, left: null, right: null};
                    c.bst = stats(e, c.i0, c.i1), c.est = estats(c.bst);
                    var u = {i0: l_5, i1: f_8.i1, bst: null, est: null, tdst: 0, left: null, right: null};
                    u.bst = {R: [], m: [], N: f_8.bst.N - c.bst.N};
                    for (s = 0; s < 16; s++)
                        u.bst.R[s] = f_8.bst.R[s] - c.bst.R[s];
                    for (s = 0; s < 4; s++)
                        u.bst.m[s] = f_8.bst.m[s] - c.bst.m[s];
                    u.est = estats(u.bst), f_8.left = c, f_8.right = u, a[o_15] = c, a.push(u);
                }
                a.sort((function (e, t) {
                    return t.bst.N - e.bst.N;
                }));
                for (s = 0; s < a.length; s++)
                    a[s].ind = s;
                return [o, a];
            }
    
            function getNearest(e, t, r, i, o) {
                if (null == e.left)
                    return e.tdst = function dist(e, t, r, i, o) {
                        var a = t - e[0], s = r - e[1], f = i - e[2], l = o - e[3];
                        return a * a + s * s + f * f + l * l;
                    }(e.est.q, t, r, i, o), e;
                var a = planeDst(e.est, t, r, i, o);
                var s = e.left, f = e.right;
                a > 0 && (s = e.right, f = e.left);
                var l = getNearest(s, t, r, i, o);
                if (l.tdst <= a * a)
                    return l;
                var c = getNearest(f, t, r, i, o);
                return c.tdst < l.tdst ? c : l;
            }
    
            function planeDst(e, t, r, i, o) {
                var a = e.e;
                return a[0] * t + a[1] * r + a[2] * i + a[3] * o - e.eMq;
            }
    
            function splitPixels(e, t, r, i, o, a) {
                for (i -= 4; r < i;) {
                    for (; vecDot(e, r, o) <= a;)
                        r += 4;
                    for (; vecDot(e, i, o) > a;)
                        i -= 4;
                    if (r >= i)
                        break;
                    var s_10 = t[r >> 2];
                    t[r >> 2] = t[i >> 2], t[i >> 2] = s_10, r += 4, i -= 4;
                }
                for (; vecDot(e, r, o) > a;)
                    r -= 4;
                return r + 4;
            }
    
            function vecDot(e, t, r) {
                return e[t] * r[0] + e[t + 1] * r[1] + e[t + 2] * r[2] + e[t + 3] * r[3];
            }
    
            function stats(e, t, r) {
                var i = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0], o = [0, 0, 0, 0], a = r - t >> 2;
                for (var a_12 = t; a_12 < r; a_12 += 4) {
                    var t_20 = e[a_12] * (1 / 255), r_19 = e[a_12 + 1] * (1 / 255), s_11 = e[a_12 + 2] * (1 / 255),
                        f_9 = e[a_12 + 3] * (1 / 255);
                    o[0] += t_20, o[1] += r_19, o[2] += s_11, o[3] += f_9, i[0] += t_20 * t_20, i[1] += t_20 * r_19, i[2] += t_20 * s_11, i[3] += t_20 * f_9, i[5] += r_19 * r_19, i[6] += r_19 * s_11, i[7] += r_19 * f_9, i[10] += s_11 * s_11, i[11] += s_11 * f_9, i[15] += f_9 * f_9;
                }
                return i[4] = i[1], i[8] = i[2], i[9] = i[6], i[12] = i[3], i[13] = i[7], i[14] = i[11], {
                    R: i,
                    m: o,
                    N: a
                };
            }
    
            function estats(e) {
                var t = e.R, r = e.m, i = e.N, a = r[0], s = r[1], f = r[2], l = r[3], c = 0 == i ? 0 : 1 / i,
                    u = [t[0] - a * a * c, t[1] - a * s * c, t[2] - a * f * c, t[3] - a * l * c, t[4] - s * a * c, t[5] - s * s * c, t[6] - s * f * c, t[7] - s * l * c, t[8] - f * a * c, t[9] - f * s * c, t[10] - f * f * c, t[11] - f * l * c, t[12] - l * a * c, t[13] - l * s * c, t[14] - l * f * c, t[15] - l * l * c],
                    h = u, d = o;
                var A = [Math.random(), Math.random(), Math.random(), Math.random()], g = 0, p = 0;
                if (0 != i)
                    for (var e_35 = 0; e_35 < 16 && (A = d.multVec(h, A), p = Math.sqrt(d.dot(A, A)), A = d.sml(1 / p, A), !(0 != e_35 && Math.abs(p - g) < 1e-9)); e_35++)
                        g = p;
                var m = [a * c, s * c, f * c, l * c];
                return {
                    Cov: u,
                    q: m,
                    e: A,
                    L: g,
                    eMq255: d.dot(d.sml(255, m), A),
                    eMq: d.dot(A, m),
                    rgba: (Math.round(255 * m[3]) << 24 | Math.round(255 * m[2]) << 16 | Math.round(255 * m[1]) << 8 | Math.round(255 * m[0]) << 0) >>> 0
                };
            }
    
            var o = {
                multVec: function (e, t) {
                    return [e[0] * t[0] + e[1] * t[1] + e[2] * t[2] + e[3] * t[3], e[4] * t[0] + e[5] * t[1] + e[6] * t[2] + e[7] * t[3], e[8] * t[0] + e[9] * t[1] + e[10] * t[2] + e[11] * t[3], e[12] * t[0] + e[13] * t[1] + e[14] * t[2] + e[15] * t[3]];
                },
                dot: function (e, t) {
                    return e[0] * t[0] + e[1] * t[1] + e[2] * t[2] + e[3] * t[3];
                },
                sml: function (e, t) {
                    return [e * t[0], e * t[1], e * t[2], e * t[3]];
                }
            };
            UPNG.encode = function encode(e, t, r, i, o, a, s) {
                null == i && (i = 0), null == s && (s = !1);
                var f = compress(e, t, r, i, [!1, !1, !1, 0, s, !1]);
                return compressPNG(f, -1), _main(f, t, r, o, a);
            }, UPNG.encodeLL = function encodeLL(e, t, r, i, o, a, s, f) {
                var l = {ctype: 0 + (1 == i ? 0 : 2) + (0 == o ? 0 : 4), depth: a, frames: []}, c = (i + o) * a, u = c * t;
                for (var i_13 = 0; i_13 < e.length; i_13++)
                    l.frames.push({
                        rect: {x: 0, y: 0, width: t, height: r},
                        img: new Uint8Array(e[i_13]),
                        blend: 0,
                        dispose: 1,
                        bpp: Math.ceil(c / 8),
                        bpl: Math.ceil(u / 8)
                    });
                return compressPNG(l, 0, !0), _main(l, t, r, s, f);
            }, UPNG.encode.compress = compress, UPNG.encode.dither = dither, UPNG.quantize = quantize, UPNG.quantize.getKDtree = getKDtree, UPNG.quantize.getNearest = getNearest;
        }();
        var t = {
            toArrayBuffer: function (e, r) {
                var i = e.width, o = e.height, a = i << 2, s = e.getContext("2d").getImageData(0, 0, i, o),
                    f = new Uint32Array(s.data.buffer), l = (32 * i + 31) / 32 << 2, c = l * o, u = 122 + c,
                    h = new ArrayBuffer(u), d = new DataView(h), A = 1 << 20;
                var g, p, m, w, v = A, b = 0, y = 0, E = 0;
    
                function set16(e) {
                    d.setUint16(y, e, !0), y += 2;
                }
    
                function set32(e) {
                    d.setUint32(y, e, !0), y += 4;
                }
    
                function seek(e) {
                    y += e;
                }
    
                set16(19778), set32(u), seek(4), set32(122), set32(108), set32(i), set32(-o >>> 0), set16(1), set16(32), set32(3), set32(c), set32(2835), set32(2835), seek(8), set32(16711680), set32(65280), set32(255), set32(4278190080), set32(1466527264), function convert() {
                    for (; b < o && v > 0;) {
                        for (w = 122 + b * l, g = 0; g < a;)
                            v--, p = f[E++], m = p >>> 24, d.setUint32(w + g, p << 8 | m), g += 4;
                        b++;
                    }
                    E < f.length ? (v = A, setTimeout(convert, t._dly)) : r(h);
                }();
            },
            toBlob: function (e, t) {
                this.toArrayBuffer(e, (function (e) {
                    t(new window.Blob([e], {type: "image/bmp"}));
                }));
            },
            _dly: 9
        };
        var r = {
            CHROME: "CHROME",
            FIREFOX: "FIREFOX",
            DESKTOP_SAFARI: "DESKTOP_SAFARI",
            IE: "IE",
            IOS: "IOS",
            ETC: "ETC"
        }, i = (_a = {},
            _a[r.CHROME] = 16384,
            _a[r.FIREFOX] = 11180,
            _a[r.DESKTOP_SAFARI] = 16384,
            _a[r.IE] = 8192,
            _a[r.IOS] = 4096,
            _a[r.ETC] = 8192,
            _a);
        var o = "undefined" != typeof window,
            a = "undefined" != typeof WorkerGlobalScope && self instanceof WorkerGlobalScope,
            s = o && window.cordova && window.cordova.require && window.cordova.require("cordova/modulemapper"),
            CustomFile = (o || a) && (s && s.getOriginalSymbol(window, "File") || "undefined" != typeof File && File),
            CustomFileReader = (o || a) && (s && s.getOriginalSymbol(window, "FileReader") || "undefined" != typeof FileReader && FileReader);
    
        function getFilefromDataUrl(e, t, r) {
            if (r === void 0) {
                r = Date.now();
            }
            return new Promise((function (i) {
                var o = e.split(","), a = o[0].match(/:(.*?);/)[1], s = globalThis.atob(o[1]);
                var f = s.length;
                var l = new Uint8Array(f);
                for (; f--;)
                    l[f] = s.charCodeAt(f);
                var c = new window.Blob([l], {type: a});
                c.name = t, c.lastModified = r, i(c);
            }));
        }
    
        function getDataUrlFromFile(e) {
            return new Promise((function (t, r) {
                var i = new CustomFileReader;
                i.onload = function () {
                    return t(i.result);
                }, i.onerror = function (e) {
                    return r(e);
                }, i.readAsDataURL(e);
            }));
        }
    
        function loadImage(e) {
            return new Promise((function (t, r) {
                var i = new Image;
                i.onload = function () {
                    return t(i);
                }, i.onerror = function (e) {
                    return r(e);
                }, i.src = e;
            }));
        }
    
        function getBrowserName() {
            if (void 0 !== getBrowserName.cachedResult)
                return getBrowserName.cachedResult;
            var e = r.ETC;
            var t = navigator.userAgent;
            return /Chrom(e|ium)/i.test(t) ? e = r.CHROME : /iP(ad|od|hone)/i.test(t) && /WebKit/i.test(t) ? e = r.IOS : /Safari/i.test(t) ? e = r.DESKTOP_SAFARI : /Firefox/i.test(t) ? e = r.FIREFOX : (/MSIE/i.test(t) || !0 == !!document.documentMode) && (e = r.IE), getBrowserName.cachedResult = e, getBrowserName.cachedResult;
        }
    
        function approximateBelowMaximumCanvasSizeOfBrowser(e, t) {
            var r = getBrowserName(), o = i[r];
            var a = e, s = t, f = a * s;
            var l = a > s ? s / a : a / s;
            for (; f > o * o;) {
                var e_36 = (o + a) / 2, t_21 = (o + s) / 2;
                e_36 < t_21 ? (s = t_21, a = t_21 * l) : (s = e_36 * l, a = e_36), f = a * s;
            }
            return {width: a, height: s};
        }
    
        function getNewCanvasAndCtx(e, t) {
            var r, i;
            try {
                if (r = new OffscreenCanvas(e, t), i = r.getContext("2d"), null === i)
                    throw new Error("getContext of OffscreenCanvas returns null");
            } catch (e) {
                r = document.createElement("canvas"), i = r.getContext("2d");
            }
            return r.width = e, r.height = t, [r, i];
        }
    
        function drawImageInCanvas(e, t) {
            var _a = approximateBelowMaximumCanvasSizeOfBrowser(e.width, e.height), r = _a.width, i = _a.height,
                _b = getNewCanvasAndCtx(r, i), o = _b[0], a = _b[1];
            return t && /jpe?g/.test(t) && (a.fillStyle = "white", a.fillRect(0, 0, o.width, o.height)), a.drawImage(e, 0, 0, o.width, o.height), o;
        }
    
        function isIOS() {
            return void 0 !== isIOS.cachedResult || (isIOS.cachedResult = ["iPad Simulator", "iPhone Simulator", "iPod Simulator", "iPad", "iPhone", "iPod"].includes(navigator.platform) || navigator.userAgent.includes("Mac") && "undefined" != typeof document && "ontouchend" in document), isIOS.cachedResult;
        }
    
        function drawFileInCanvas(e, t) {
            if (t === void 0) {
                t = {};
            }
            return new Promise((function (i, o) {
                var a, s;
                var $Try_2_Post = function () {
                    try {
                        return s = drawImageInCanvas(a, t.fileType || e.type), i([a, s]);
                    } catch (e) {
                        return o(e);
                    }
                }, $Try_2_Catch = function (t) {
                    try {
                        0;
                        var $Try_3_Catch = function (e) {
                            try {
                                throw e;
                            } catch (e) {
                                return o(e);
                            }
                        };
                        try {
                            var t_22;
                            return getDataUrlFromFile(e).then((function (e) {
                                try {
                                    return t_22 = e, loadImage(t_22).then((function (e) {
                                        try {
                                            return a = e, function () {
                                                try {
                                                    return $Try_2_Post();
                                                } catch (e) {
                                                    return o(e);
                                                }
                                            }();
                                        } catch (e) {
                                            return $Try_3_Catch(e);
                                        }
                                    }), $Try_3_Catch);
                                } catch (e) {
                                    return $Try_3_Catch(e);
                                }
                            }), $Try_3_Catch);
                        } catch (e) {
                            $Try_3_Catch(e);
                        }
                    } catch (e) {
                        return o(e);
                    }
                };
                try {
                    if (isIOS() || [r.DESKTOP_SAFARI, r.MOBILE_SAFARI].includes(getBrowserName()))
                        throw new Error("Skip createImageBitmap on IOS and Safari");
                    return createImageBitmap(e).then((function (e) {
                        try {
                            return a = e, $Try_2_Post();
                        } catch (e) {
                            return $Try_2_Catch();
                        }
                    }), $Try_2_Catch);
                } catch (e) {
                    $Try_2_Catch();
                }
            }));
        }
    
        function canvasToFile(e, r, i, o, a) {
            if (a === void 0) {
                a = 1;
            }
            return new Promise((function (s, f) {
                var l;
                if ("image/png" === r) {
                    var c = void 0, u = void 0, h = void 0;
                    return c = e.getContext("2d"), (u = c.getImageData(0, 0, e.width, e.height).data), h = UPNG.encode([u.buffer], e.width, e.height, 4096 * a), l = new window.Blob([h], {type: r}), l.name = i, l.lastModified = o, $If_4.call(this);
                }
                {
                    if ("image/bmp" === r)
                        return new Promise((function (r) {
                            return t.toBlob(e, r);
                        })).then(function (e) {
                            try {
                                return l = e, l.name = i, l.lastModified = o, $If_5.call(this);
                            } catch (e) {
                                return f(e);
                            }
                        }.bind(this), f);
                    {
                        if ("function" == typeof OffscreenCanvas && e instanceof OffscreenCanvas)
                            return e.convertToBlob({
                                type: r,
                                quality: a
                            }).then(function (e) {
                                try {
                                    return l = e, l.name = i, l.lastModified = o, $If_6.call(this);
                                } catch (e) {
                                    return f(e);
                                }
                            }.bind(this), f);
                        {
                            var d = void 0;
                            return d = e.toDataURL(r, a), getFilefromDataUrl(d, i, o).then(function (e) {
                                try {
                                    return l = e, $If_6.call(this);
                                } catch (e) {
                                    return f(e);
                                }
                            }.bind(this), f);
                        }
    
                        function $If_6() {
                            return $If_5.call(this);
                        }
                    }
    
                    function $If_5() {
                        return $If_4.call(this);
                    }
                }
    
                function $If_4() {
                    return s(l);
                }
            }));
        }
    
        function cleanupCanvasMemory(e) {
            e.width = 0, e.height = 0;
        }
    
        function isAutoOrientationInBrowser() {
            return new Promise((function (e, t) {
                var r, i, o, a, s;
                return void 0 !== isAutoOrientationInBrowser.cachedResult ? e(isAutoOrientationInBrowser.cachedResult) : (r = "data:image/jpeg;base64,/9j/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAYAAAAAAAD/2wCEAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf/AABEIAAEAAgMBEQACEQEDEQH/xABKAAEAAAAAAAAAAAAAAAAAAAALEAEAAAAAAAAAAAAAAAAAAAAAAQEAAAAAAAAAAAAAAAAAAAAAEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8H//2Q==", getFilefromDataUrl("data:image/jpeg;base64,/9j/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAYAAAAAAAD/2wCEAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf/AABEIAAEAAgMBEQACEQEDEQH/xABKAAEAAAAAAAAAAAAAAAAAAAALEAEAAAAAAAAAAAAAAAAAAAAAAQEAAAAAAAAAAAAAAAAAAAAAEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/8H//2Q==", "test.jpg", Date.now()).then((function (r) {
                    try {
                        return i = r, drawFileInCanvas(i).then((function (r) {
                            try {
                                return o = r[1], canvasToFile(o, i.type, i.name, i.lastModified).then((function (r) {
                                    try {
                                        return a = r, cleanupCanvasMemory(o), drawFileInCanvas(a).then((function (r) {
                                            try {
                                                return s = r[0], isAutoOrientationInBrowser.cachedResult = 1 === s.width && 2 === s.height, e(isAutoOrientationInBrowser.cachedResult);
                                            } catch (e) {
                                                return t(e);
                                            }
                                        }), t);
                                    } catch (e) {
                                        return t(e);
                                    }
                                }), t);
                            } catch (e) {
                                return t(e);
                            }
                        }), t);
                    } catch (e) {
                        return t(e);
                    }
                }), t));
            }));
        }
    
        function getExifOrientation(e) {
            return new Promise((function (t, r) {
                var i = new CustomFileReader;
                i.onload = function (e) {
                    var r = new DataView(e.target.result);
                    if (65496 != r.getUint16(0, !1))
                        return t(-2);
                    var i = r.byteLength;
                    var o = 2;
                    for (; o < i;) {
                        if (r.getUint16(o + 2, !1) <= 8)
                            return t(-1);
                        var e_37 = r.getUint16(o, !1);
                        if (o += 2, 65505 == e_37) {
                            if (1165519206 != r.getUint32(o += 2, !1))
                                return t(-1);
                            var e_38 = 18761 == r.getUint16(o += 6, !1);
                            o += r.getUint32(o + 4, e_38);
                            var i_14 = r.getUint16(o, e_38);
                            o += 2;
                            for (var a_13 = 0; a_13 < i_14; a_13++)
                                if (274 == r.getUint16(o + 12 * a_13, e_38))
                                    return t(r.getUint16(o + 12 * a_13 + 8, e_38));
                        } else {
                            if (65280 != (65280 & e_37))
                                break;
                            o += r.getUint16(o, !1);
                        }
                    }
                    return t(-1);
                }, i.onerror = function (e) {
                    return r(e);
                }, i.readAsArrayBuffer(e);
            }));
        }
    
        function handleMaxWidthOrHeight(e, t) {
            var _a;
            var r = e.width, i = e.height, o = t.maxWidthOrHeight;
            var a, s = e;
            return isFinite(o) && (r > o || i > o) && (_a = getNewCanvasAndCtx(r, i), s = _a[0], a = _a[1], r > i ? (s.width = o, s.height = i / r * o) : (s.width = r / i * o, s.height = o), a.drawImage(e, 0, 0, s.width, s.height), cleanupCanvasMemory(e)), s;
        }
    
        function followExifOrientation(e, t) {
            var r = e.width, i = e.height, _a = getNewCanvasAndCtx(r, i), o = _a[0], a = _a[1];
            switch (t > 4 && t < 9 ? (o.width = i, o.height = r) : (o.width = r, o.height = i), t) {
                case 2:
                    a.transform(-1, 0, 0, 1, r, 0);
                    break;
                case 3:
                    a.transform(-1, 0, 0, -1, r, i);
                    break;
                case 4:
                    a.transform(1, 0, 0, -1, 0, i);
                    break;
                case 5:
                    a.transform(0, 1, 1, 0, 0, 0);
                    break;
                case 6:
                    a.transform(0, 1, -1, 0, i, 0);
                    break;
                case 7:
                    a.transform(0, -1, -1, 0, i, r);
                    break;
                case 8:
                    a.transform(0, -1, 1, 0, 0, r);
            }
            return a.drawImage(e, 0, 0, r, i), cleanupCanvasMemory(e), o;
        }
    
        function compress(e, t, r) {
            if (r === void 0) {
                r = 0;
            }
            return new Promise((function (i, o) {
                var a, s, f, l, c, u, h, d, A, g, p, m, w, v, b, y, E, F, _, B;
    
                function incProgress(e) {
                    if (e === void 0) {
                        e = 5;
                    }
                    if (t.signal && t.signal.aborted)
                        throw t.signal.reason;
                    a += e, t.onProgress(Math.min(a, 100));
                }
    
                function setProgress(e) {
                    if (t.signal && t.signal.aborted)
                        throw t.signal.reason;
                    a = Math.min(Math.max(e, a), 100), t.onProgress(a);
                }
    
                return a = r, s = t.maxIteration || 10, f = 1024 * t.maxSizeMB * 1024, incProgress(), drawFileInCanvas(e, t).then(function (r) {
                    try {
                        return l = r[1], incProgress(), c = handleMaxWidthOrHeight(l, t), incProgress(), new Promise((function (r, i) {
                            var o;
                            if (!(o = t.exifOrientation))
                                return getExifOrientation(e).then(function (e) {
                                    try {
                                        return o = e, $If_2.call(this);
                                    } catch (e) {
                                        return i(e);
                                    }
                                }.bind(this), i);
    
                            function $If_2() {
                                return r(o);
                            }
    
                            return $If_2.call(this);
                        })).then(function (r) {
                            try {
                                return u = r, incProgress(), isAutoOrientationInBrowser().then(function (r) {
                                    try {
                                        return h = r ? c : followExifOrientation(c, u), incProgress(), d = t.initialQuality || 1, A = t.fileType || e.type, canvasToFile(h, A, e.name, e.lastModified, d).then(function (r) {
                                            try {
                                                {
                                                    if (g = r, incProgress(), p = g.size > f, m = g.size > e.size, !p && !m)
                                                        return setProgress(100), i(g);
                                                    var a;
    
                                                    function $Loop_3() {
                                                        var _a;
                                                        if (s-- && (b > f || b > w)) {
                                                            var t_23, r_20;
                                                            return t_23 = B ? .95 * _.width : _.width, r_20 = B ? .95 * _.height : _.height, _a = getNewCanvasAndCtx(t_23, r_20), E = _a[0], F = _a[1], F.drawImage(_, 0, 0, t_23, r_20), d *= "image/png" === A ? .85 : .95, canvasToFile(E, A, e.name, e.lastModified, d).then((function (e) {
                                                                try {
                                                                    return y = e, cleanupCanvasMemory(_), _ = E, b = y.size, setProgress(Math.min(99, Math.floor((v - b) / (v - f) * 100))), $Loop_3;
                                                                } catch (e) {
                                                                    return o(e);
                                                                }
                                                            }), o);
                                                        }
                                                        return [1];
                                                    }
    
                                                    return w = e.size, v = g.size, b = v, _ = h, B = !t.alwaysKeepResolution && p, (a = function (e) {
                                                        for (; e;) {
                                                            if (e.then)
                                                                return void e.then(a, o);
                                                            try {
                                                                if (e.pop) {
                                                                    if (e.length)
                                                                        return e.pop() ? $Loop_3_exit.call(this) : e;
                                                                    e = $Loop_3;
                                                                } else
                                                                    e = e.call(this);
                                                            } catch (e) {
                                                                return o(e);
                                                            }
                                                        }
                                                    }.bind(this))($Loop_3);
    
                                                    function $Loop_3_exit() {
                                                        return cleanupCanvasMemory(_), cleanupCanvasMemory(E), cleanupCanvasMemory(c), cleanupCanvasMemory(h), cleanupCanvasMemory(l), setProgress(100), i(y);
                                                    }
                                                }
                                            } catch (u) {
                                                return o(u);
                                            }
                                        }.bind(this), o);
                                    } catch (e) {
                                        return o(e);
                                    }
                                }.bind(this), o);
                            } catch (e) {
                                return o(e);
                            }
                        }.bind(this), o);
                    } catch (e) {
                        return o(e);
                    }
                }.bind(this), o);
            }));
        }
    
        var f = "\nlet scriptImported = false\nself.addEventListener('message', async (e) => {\n  const { file, id, imageCompressionLibUrl, options } = e.data\n  options.onProgress = (progress) => self.postMessage({ progress, id })\n  try {\n    if (!scriptImported) {\n      // console.log('[worker] importScripts', imageCompressionLibUrl)\n      self.importScripts(imageCompressionLibUrl)\n      scriptImported = true\n    }\n    // console.log('[worker] self', self)\n    const compressedFile = await imageCompression(file, options)\n    self.postMessage({ file: compressedFile, id })\n  } catch (e) {\n    // console.error('[worker] error', e)\n    self.postMessage({ error: e.message + '\\n' + e.stack, id })\n  }\n})\n";
        var l;
    
        function compressOnWebWorker(e, t) {
            return new Promise((function (r, i) {
                l || (l = function createWorkerScriptURL(e) {
                    var t = [];
                    return "function" == typeof e ? t.push("(".concat(e, ")()")) : t.push(e), URL.createObjectURL(new window.Blob(t));
                }(f));
                var o = new Worker(l);
                o.addEventListener("message", (function handler(e) {
                    if (t.signal && t.signal.aborted)
                        o.terminate();
                    else if (void 0 === e.data.progress) {
                        if (e.data.error)
                            return i(new Error(e.data.error)), void o.terminate();
                        r(e.data.file), o.terminate();
                    } else
                        t.onProgress(e.data.progress);
                })), o.addEventListener("error", i), t.signal && t.signal.addEventListener("abort", (function () {
                    i(t.signal.reason), o.terminate();
                })), o.postMessage({
                    file: e,
                    imageCompressionLibUrl: t.libURL,
                    options: __assign(__assign({}, t), {onProgress: void 0, signal: void 0})
                });
            }));
        }
    
        function imageCompression(e, t) {
            return new Promise((function (r, i) {
                var o, a, s, f, l, c;
                if (o = __assign({}, t), s = 0, (f = o.onProgress), o.maxSizeMB = o.maxSizeMB || Number.POSITIVE_INFINITY, l = "boolean" != typeof o.useWebWorker || o.useWebWorker, delete o.useWebWorker, o.onProgress = function (e) {
                    s = e, "function" == typeof f && f(s);
                }, !(e instanceof window.Blob || e instanceof CustomFile))
                    return i(new Error("The file given is not an instance of Blob or File"));
                if (!/^image/.test(e.type))
                    return i(new Error("The file given is not an image"));
                if (c = "undefined" != typeof WorkerGlobalScope && self instanceof WorkerGlobalScope, !l || "function" != typeof Worker || c)
                    return compress(e, o).then(function (e) {
                        try {
                            return a = e, $If_4.call(this);
                        } catch (e) {
                            return i(e);
                        }
                    }.bind(this), i);
                var u = function () {
                    try {
                        return $If_4.call(this);
                    } catch (e) {
                        return i(e);
                    }
                }.bind(this), $Try_1_Catch = function (t) {
                    try {
                        return compress(e, o).then((function (e) {
                            try {
                                return a = e, u();
                            } catch (e) {
                                return i(e);
                            }
                        }), i);
                    } catch (e) {
                        return i(e);
                    }
                };
                try {
                    return o.libURL = o.libURL || "https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js", compressOnWebWorker(e, o).then((function (e) {
                        try {
                            return a = e, u();
                        } catch (e) {
                            return $Try_1_Catch();
                        }
                    }), $Try_1_Catch);
                } catch (e) {
                    $Try_1_Catch();
                }
    
                function $If_4() {
                    try {
                        a.name = e.name, a.lastModified = e.lastModified;
                    } catch (e) {
                    }
                    try {
                        o.preserveExif && "image/jpeg" === e.type && (!o.fileType || o.fileType && o.fileType === e.type) && (a = copyExifWithoutOrientation(e, a));
                    } catch (e) {
                    }
                    return r(a);
                }
            }));
        }
    
        return imageCompression.getDataUrlFromFile = getDataUrlFromFile, imageCompression.getFilefromDataUrl = getFilefromDataUrl, imageCompression.loadImage = loadImage, imageCompression.drawImageInCanvas = drawImageInCanvas, imageCompression.drawFileInCanvas = drawFileInCanvas, imageCompression.canvasToFile = canvasToFile, imageCompression.getExifOrientation = getExifOrientation, imageCompression.handleMaxWidthOrHeight = handleMaxWidthOrHeight, imageCompression.followExifOrientation = followExifOrientation, imageCompression.cleanupCanvasMemory = cleanupCanvasMemory, imageCompression.isAutoOrientationInBrowser = isAutoOrientationInBrowser, imageCompression.approximateBelowMaximumCanvasSizeOfBrowser = approximateBelowMaximumCanvasSizeOfBrowser, imageCompression.copyExifWithoutOrientation = copyExifWithoutOrientation, imageCompression.getBrowserName = getBrowserName, imageCompression.version = "2.0.2", imageCompression;
    });
    
    /**
     * @fileOverview 图片操作, 负责预览图片和上传前压缩图片
     */
    define('widgets/image',[
        'base',
        'uploader',
        'lib/image',
        'lib/browser-image-compression',
        'widgets/widget'
    ], function( Base, Uploader, Image, imageCompression ) {
    
        var $ = Base.$,
            throttle;
    
        // 根据要处理的文件大小来节流，一次不能处理太多，会卡。
        throttle = (function( max ) {
            var occupied = 0,
                waiting = [],
                tick = function() {
                    var item;
    
                    while ( waiting.length && occupied < max ) {
                        item = waiting.shift();
                        occupied += item[ 0 ];
                        item[ 1 ]();
                    }
                };
    
            return function( emiter, size, cb ) {
                waiting.push([ size, cb ]);
                emiter.once( 'destroy', function() {
                    occupied -= size;
                    setTimeout( tick, 1 );
                });
                setTimeout( tick, 1 );
            };
        })( 5 * 1024 * 1024 );
    
        $.extend( Uploader.options, {
    
            /**
             * @property {Object} [thumb]
             * @namespace options
             * @for Uploader
             * @description 配置生成缩略图的选项。
             *
             * 默认为：
             *
             * ```javascript
             * {
             *     width: 110,
             *     height: 110,
             *
             *     // 图片质量，只有type为`image/jpeg`的时候才有效。
             *     quality: 70,
             *
             *     // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.
             *     allowMagnify: true,
             *
             *     // 是否允许裁剪。
             *     crop: true,
             *
             *     // 为空的话则保留原有图片格式。
             *     // 否则强制转换成指定的类型。
             *     type: 'image/jpeg'
             * }
             * ```
             */
            thumb: {
                width: 110,
                height: 110,
                quality: 70,
                allowMagnify: true,
                crop: true,
                preserveHeaders: false,
    
                // 为空的话则保留原有图片格式。
                // 否则强制转换成指定的类型。
                // IE 8下面 base64 大小不能超过 32K 否则预览失败，而非 jpeg 编码的图片很可
                // 能会超过 32k, 所以这里设置成预览的时候都是 image/jpeg
                type: 'image/jpeg'
            },
            compress: {
                // 是否开启
                enable: false,
                // 压缩最大宽度或高度
                maxWidthOrHeight: 4000,
                // 压缩的最大大小
                maxSize: 10*1024*1024,
            }
        });
    
        return Uploader.register({
    
            name: 'image',
    
    
            /**
             * 生成缩略图，此过程为异步，所以需要传入`callback`。
             * 通常情况在图片加入队里后调用此方法来生成预览图以增强交互效果。
             *
             * 当 width 或者 height 的值介于 0 - 1 时，被当成百分比使用。
             *
             * `callback`中可以接收到两个参数。
             * * 第一个为error，如果生成缩略图有错误，此error将为真。
             * * 第二个为ret, 缩略图的Data URL值。
             *
             * **注意**
             * Date URL在IE6/7中不支持，所以不用调用此方法了，直接显示一张暂不支持预览图片好了。
             * 也可以借助服务端，将 base64 数据传给服务端，生成一个临时文件供预览。
             *
             * @method makeThumb
             * @grammar makeThumb( file, callback ) => undefined
             * @grammar makeThumb( file, callback, width, height ) => undefined
             * @for Uploader
             * @example
             *
             * uploader.on( 'fileQueued', function( file ) {
             *     var $li = ...;
             *
             *     uploader.makeThumb( file, function( error, ret ) {
             *         if ( error ) {
             *             $li.text('预览错误');
             *         } else {
             *             $li.append('<img alt="" src="' + ret + '" />');
             *         }
             *     });
             *
             * });
             */
            makeThumb: function( file, cb, width, height ) {
                var opts, image;
    
                file = this.request( 'get-file', file );
    
                // 只预览图片格式。
                if ( !file.type.match( /^image/ ) ) {
                    cb( true );
                    return;
                }
    
                opts = $.extend({}, this.options.thumb );
    
                // 如果传入的是object.
                if ( $.isPlainObject( width ) ) {
                    opts = $.extend( opts, width );
                    width = null;
                }
    
                width = width || opts.width;
                height = height || opts.height;
    
                image = new Image( opts );
    
                image.once( 'load', function() {
                    file._info = file._info || image.info();
                    file._meta = file._meta || image.meta();
    
                    // 如果 width 的值介于 0 - 1
                    // 说明设置的是百分比。
                    if ( width <= 1 && width > 0 ) {
                        width = file._info.width * width;
                    }
    
                    // 同样的规则应用于 height
                    if ( height <= 1 && height > 0 ) {
                        height = file._info.height * height;
                    }
    
                    image.resize( width, height );
                });
    
                // 当 resize 完后
                image.once( 'complete', function() {
                    cb( false, image.getAsDataUrl( opts.type ) );
                    image.destroy();
                });
    
                image.once( 'error', function( reason ) {
                    cb( reason || true );
                    image.destroy();
                });
    
                throttle( image, file.source.size, function() {
                    file._info && image.info( file._info );
                    file._meta && image.meta( file._meta );
                    image.loadFromBlob( file.source );
                });
            },
    
            beforeSendFile: function( file ) {
                var opts = this.options.compress, image, me=this, deferred;
    
                // console.log('image.beforeSendFile',opts, file)
    
                file = this.request( 'get-file', file );
    
                if(file._widgetImageData){
                    return;
                }
    
                var data = {
                    processed: false,
                    success: false,
                    originalSize: file.size,
                };
    
                if ( !opts || !opts.enable || !~'image/jpeg,image/jpg,image/png'.indexOf( file.type ) ) {
                    file._widgetImageData = data;
                    return;
                }
    
                opts = $.extend({}, opts );
                deferred = Base.Deferred();
    
                me.owner.trigger( 'fileProcessStart', 'imageCompress', file);
    
                imageCompression(file.source.source,{
                    maxSizeMB: opts.maxSize/1024/1024,
                    maxWidthOrHeight: opts.maxWidthOrHeight,
                }).then(function (compressedBlob) {
                    me.owner.trigger( 'fileProcessEnd', 'imageCompress', file);
                    if(opts.debug){
                        console.log('webuploader.compress.output', (compressedBlob.size / file.size * 100).toFixed(2) + '%');
                    }
                    var oldSize = file.size;
                    file.source.source = compressedBlob;
                    file.source.size = compressedBlob.size;
                    file.size = compressedBlob.size;
                    file.trigger( 'resize', compressedBlob.size, oldSize );
                    data.processed = true;
                    data.success = true;
                    file._widgetImageData = data;
                    deferred.resolve();
                }).catch(function (error) {
                    console.warn('webuploader.compress.error',error);
                    me.owner.trigger( 'fileProcessEnd', 'imageCompress', file);
                    data.processed = true;
                    file._widgetImageData = data;
                    deferred.resolve();
                });
    
                // image = new Image( opts );
                //
                // deferred.always(function() {
                //     image.destroy();
                //     image = null;
                // });
                // image.once( 'error', deferred.reject );
                // image.once( 'load', function() {
                //     var width = opts.width,
                //         height = opts.height;
                //
                //     file._info = file._info || image.info();
                //     file._meta = file._meta || image.meta();
                //
                //     // 如果 width 的值介于 0 - 1
                //     // 说明设置的是百分比。
                //     if ( width <= 1 && width > 0 ) {
                //         width = file._info.width * width;
                //     }
                //
                //     // 同样的规则应用于 height
                //     if ( height <= 1 && height > 0 ) {
                //         height = file._info.height * height;
                //     }
                //
                //     image.resize( width, height );
                // });
                //
                // image.once( 'complete', function() {
                //     var blob, size;
                //
                //     // 移动端 UC / qq 浏览器的无图模式下
                //     // ctx.getImageData 处理大图的时候会报 Exception
                //     // INDEX_SIZE_ERR: DOM Exception 1
                //     try {
                //         blob = image.getAsBlob( opts.type );
                //
                //         size = file.size;
                //
                //         // 如果压缩后，比原来还大则不用压缩后的。
                //         if ( !noCompressIfLarger || blob.size < size ) {
                //             // file.source.destroy && file.source.destroy();
                //             file.source = blob;
                //             file.size = blob.size;
                //
                //             file.trigger( 'resize', blob.size, size );
                //         }
                //
                //         // 标记，避免重复压缩。
                //         file._compressed = true;
                //         deferred.resolve();
                //     } catch ( e ) {
                //         // 出错了直接继续，让其上传原始图片
                //         deferred.resolve();
                //     }
                // });
                //
                // file._info && image.info( file._info );
                // file._meta && image.meta( file._meta );
                //
                // image.loadFromBlob( file.source );
                return deferred.promise();
            }
        });
    });
    
    /**
     * @fileOverview 文件属性封装
     */
    define('file',[
        'base',
        'mediator'
    ], function( Base, Mediator ) {
    
        var $ = Base.$,
            idPrefix = 'WU_FILE_',
            idSuffix = 0,
            rExt = /\.([^.]+)$/,
            statusMap = {};
    
        function gid() {
            return idPrefix + idSuffix++;
        }
    
        /**
         * 文件类
         * @class File
         * @constructor 构造函数
         * @grammar new File( source ) => File
         * @param {Lib.File} source [lib.File](#Lib.File)实例, 此source对象是带有Runtime信息的。
         */
        function WUFile( source ) {
    
            /**
             * 文件名，包括扩展名（后缀）
             * @property name
             * @type {string}
             */
            this.name = source.name || 'Untitled';
    
            /**
             * 文件体积（字节）
             * @property size
             * @type {uint}
             * @default 0
             */
            this.size = source.size || 0;
    
            /**
             * 文件MIMETYPE类型，与文件类型的对应关系请参考[http://t.cn/z8ZnFny](http://t.cn/z8ZnFny)
             * @property type
             * @type {string}
             * @default 'application/octet-stream'
             */
            this.type = source.type || 'application/octet-stream';
    
            /**
             * 文件最后修改日期
             * @property lastModifiedDate
             * @type {int}
             * @default 当前时间戳
             */
            this.lastModifiedDate = source.lastModifiedDate || (new Date() * 1);
    
            /**
             * 文件ID，每个对象具有唯一ID，与文件名无关
             * @property id
             * @type {string}
             */
            this.id = gid();
    
            /**
             * 文件扩展名，通过文件名获取，例如test.png的扩展名为png
             * @property ext
             * @type {string}
             */
            this.ext = rExt.exec( this.name ) ? RegExp.$1 : '';
    
    
            /**
             * 状态文字说明。在不同的status语境下有不同的用途。
             * @property statusText
             * @type {string}
             */
            this.statusText = '';
    
            // 存储文件状态，防止通过属性直接修改
            statusMap[ this.id ] = WUFile.Status.INITED;
    
            this.source = source;
            this.loaded = 0;
    
            this.on( 'error', function( msg ) {
                this.setStatus( WUFile.Status.ERROR, msg );
            });
        }
    
        $.extend( WUFile.prototype, {
    
            /**
             * 设置状态，状态变化时会触发`change`事件。
             * @method setStatus
             * @grammar setStatus( status[, statusText] );
             * @param {File.Status|String} status [文件状态值](#WebUploader:File:File.Status)
             * @param {String} [statusText=''] 状态说明，常在error时使用，用http, abort,server等来标记是由于什么原因导致文件错误。
             */
            setStatus: function( status, text ) {
    
                var prevStatus = statusMap[ this.id ];
    
                typeof text !== 'undefined' && (this.statusText = text);
    
                if ( status !== prevStatus ) {
                    statusMap[ this.id ] = status;
                    /**
                     * 文件状态变化
                     * @event statuschange
                     */
                    this.trigger( 'statuschange', status, prevStatus );
                }
    
            },
    
            /**
             * 获取文件状态
             * @return {File.Status}
             * @example
                     文件状态具体包括以下几种类型：
                     {
                         // 初始化
                        INITED:     0,
                        // 已入队列
                        QUEUED:     1,
                        // 正在上传
                        PROGRESS:     2,
                        // 上传出错
                        ERROR:         3,
                        // 上传成功
                        COMPLETE:     4,
                        // 上传取消
                        CANCELLED:     5
                    }
             */
            getStatus: function() {
                return statusMap[ this.id ];
            },
    
            /**
             * 获取文件原始信息。
             * @return {*}
             */
            getSource: function() {
                return this.source;
            },
    
            destroy: function() {
                this.off();
                delete statusMap[ this.id ];
            }
        });
    
        Mediator.installTo( WUFile.prototype );
    
        /**
         * 文件状态值，具体包括以下几种类型：
         * * `inited` 初始状态
         * * `queued` 已经进入队列, 等待上传
         * * `progress` 上传中
         * * `complete` 上传完成。
         * * `error` 上传出错，可重试
         * * `interrupt` 上传中断，可续传。
         * * `invalid` 文件不合格，不能重试上传。会自动从队列中移除。
         * * `cancelled` 文件被移除。
         * @property {Object} Status
         * @namespace File
         * @class File
         * @static
         */
        WUFile.Status = {
            INITED:     'inited',    // 初始状态
            QUEUED:     'queued',    // 已经进入队列, 等待上传
            PROGRESS:   'progress',    // 上传中
            ERROR:      'error',    // 上传出错，可重试
            COMPLETE:   'complete',    // 上传完成。
            CANCELLED:  'cancelled',    // 上传取消。
            INTERRUPT:  'interrupt',    // 上传中断，可续传。
            INVALID:    'invalid'    // 文件不合格，不能重试上传。
        };
    
        return WUFile;
    });
    
    /**
     * @fileOverview 文件队列
     */
    define('queue',[
        'base',
        'mediator',
        'file'
    ], function( Base, Mediator, WUFile ) {
    
        var $ = Base.$,
            STATUS = WUFile.Status;
    
        /**
         * 文件队列, 用来存储各个状态中的文件。
         * @class Queue
         * @extends Mediator
         */
        function Queue() {
    
            /**
             * 统计文件数。
             * * `numOfQueue` 队列中的文件数。
             * * `numOfSuccess` 上传成功的文件数
             * * `numOfCancel` 被取消的文件数
             * * `numOfProgress` 正在上传中的文件数
             * * `numOfUploadFailed` 上传错误的文件数。
             * * `numOfInvalid` 无效的文件数。
             * * `numOfDeleted` 被移除的文件数。
             * * `numOfInterrupt` 被中断的文件数。
             * @property {Object} stats
             */
            this.stats = {
                numOfQueue: 0,
                numOfSuccess: 0,
                numOfCancel: 0,
                numOfProgress: 0,
                numOfUploadFailed: 0,
                numOfInvalid: 0,
                numOfDeleted: 0,
                numOfInterrupt: 0
            };
    
            // 上传队列，仅包括等待上传的文件
            this._queue = [];
    
            // 存储所有文件
            this._map = {};
        }
    
        $.extend( Queue.prototype, {
    
            /**
             * 将新文件加入对队列尾部
             *
             * @method append
             * @param  {File} file   文件对象
             */
            append: function( file ) {
                this._queue.push( file );
                this._fileAdded( file );
                return this;
            },
    
            /**
             * 将新文件加入对队列头部
             *
             * @method prepend
             * @param  {File} file   文件对象
             */
            prepend: function( file ) {
                this._queue.unshift( file );
                this._fileAdded( file );
                return this;
            },
    
            /**
             * 获取文件对象
             *
             * @method getFile
             * @param  {String} fileId   文件ID
             * @return {File}
             */
            getFile: function( fileId ) {
                if ( typeof fileId !== 'string' ) {
                    return fileId;
                }
                return this._map[ fileId ];
            },
    
            /**
             * 从队列中取出一个指定状态的文件。
             * @grammar fetch( status ) => File
             * @method fetch
             * @param {String} status [文件状态值](#WebUploader:File:File.Status)
             * @return {File} [File](#WebUploader:File)
             */
            fetch: function( status ) {
                var len = this._queue.length,
                    i, file;
    
                status = status || STATUS.QUEUED;
    
                for ( i = 0; i < len; i++ ) {
                    file = this._queue[ i ];
    
                    if ( status === file.getStatus() ) {
                        return file;
                    }
                }
    
                return null;
            },
    
            /**
             * 对队列进行排序，能够控制文件上传顺序。
             * @grammar sort( fn ) => undefined
             * @method sort
             * @param {Function} fn 排序方法
             */
            sort: function( fn ) {
                if ( typeof fn === 'function' ) {
                    this._queue.sort( fn );
                }
            },
    
            /**
             * 获取指定类型的文件列表, 列表中每一个成员为[File](#WebUploader:File)对象。
             * @grammar getFiles( [status1[, status2 ...]] ) => Array
             * @method getFiles
             * @param {String} [status] [文件状态值](#WebUploader:File:File.Status)
             */
            getFiles: function() {
                var sts = [].slice.call( arguments, 0 ),
                    ret = [],
                    i = 0,
                    len = this._queue.length,
                    file;
    
                for ( ; i < len; i++ ) {
                    file = this._queue[ i ];
    
                    if ( sts.length && !~$.inArray( file.getStatus(), sts ) ) {
                        continue;
                    }
    
                    ret.push( file );
                }
    
                return ret;
            },
    
            /**
             * 在队列中删除文件。
             * @grammar removeFile( file ) => Array
             * @method removeFile
             * @param {File} 文件对象。
             */
            removeFile: function( file ) {
                var me = this,
                    existing = this._map[ file.id ];
    
                if ( existing ) {
                    delete this._map[ file.id ];
                    this._delFile(file);
                    file.destroy();
                    this.stats.numOfDeleted++;
    
                }
            },
    
            _fileAdded: function( file ) {
                var me = this,
                    existing = this._map[ file.id ];
    
                if ( !existing ) {
                    this._map[ file.id ] = file;
    
                    file.on( 'statuschange', function( cur, pre ) {
                        me._onFileStatusChange( cur, pre );
                    });
                }
    
                file.setStatus( STATUS.QUEUED );
            },
    
            _delFile : function(file){
                for(var i = this._queue.length - 1 ; i >= 0 ; i-- ){
                    if(this._queue[i] == file){
                        this._queue.splice(i,1);
                        break;
                    }
                }
            },
    
            _onFileStatusChange: function( curStatus, preStatus ) {
                var stats = this.stats;
    
                switch ( preStatus ) {
                    case STATUS.PROGRESS:
                        stats.numOfProgress--;
                        break;
    
                    case STATUS.QUEUED:
                        stats.numOfQueue --;
                        break;
    
                    case STATUS.ERROR:
                        stats.numOfUploadFailed--;
                        break;
    
                    case STATUS.INVALID:
                        stats.numOfInvalid--;
                        break;
    
                    case STATUS.INTERRUPT:
                        stats.numOfInterrupt--;
                        break;
                }
    
                switch ( curStatus ) {
                    case STATUS.QUEUED:
                        stats.numOfQueue++;
                        break;
    
                    case STATUS.PROGRESS:
                        stats.numOfProgress++;
                        break;
    
                    case STATUS.ERROR:
                        stats.numOfUploadFailed++;
                        break;
    
                    case STATUS.COMPLETE:
                        stats.numOfSuccess++;
                        break;
    
                    case STATUS.CANCELLED:
                        stats.numOfCancel++;
                        break;
    
    
                    case STATUS.INVALID:
                        stats.numOfInvalid++;
                        break;
    
                    case STATUS.INTERRUPT:
                        stats.numOfInterrupt++;
                        break;
                }
            }
    
        });
    
        Mediator.installTo( Queue.prototype );
    
        return Queue;
    });
    
    /**
     * @fileOverview 队列
     */
    define('widgets/queue',[
        'base',
        'uploader',
        'queue',
        'file',
        'lib/file',
        'runtime/client',
        'widgets/widget'
    ], function( Base, Uploader, Queue, WUFile, File, RuntimeClient ) {
    
        var $ = Base.$,
            rExt = /\.\w+$/,
            Status = WUFile.Status;
    
        return Uploader.register({
            name: 'queue',
    
            init: function( opts ) {
                var me = this,
                    deferred, len, i, item, arr, accept, runtime;
    
                if ( $.isPlainObject( opts.accept ) ) {
                    opts.accept = [ opts.accept ];
                }
    
                // accept中的中生成匹配正则。
                if ( opts.accept ) {
                    arr = [];
    
                    for ( i = 0, len = opts.accept.length; i < len; i++ ) {
                        item = opts.accept[ i ].extensions;
                        item && arr.push( item );
                    }
    
                    if ( arr.length ) {
                        accept = '\\.' + arr.join(',')
                                .replace( /,/g, '$|\\.' )
                                .replace( /\*/g, '.*' ) + '$';
                    }
    
                    me.accept = new RegExp( accept, 'i' );
                }
    
                me.queue = new Queue();
                me.stats = me.queue.stats;
    
                // 如果当前不是html5运行时，那就算了。
                // 不执行后续操作
                if ( this.request('predict-runtime-type') !== 'html5' ) {
                    return;
                }
    
                // 创建一个 html5 运行时的 placeholder
                // 以至于外部添加原生 File 对象的时候能正确包裹一下供 webuploader 使用。
                deferred = Base.Deferred();
                this.placeholder = runtime = new RuntimeClient('Placeholder');
                runtime.connectRuntime({
                    runtimeOrder: 'html5'
                }, function() {
                    me._ruid = runtime.getRuid();
                    deferred.resolve();
                });
                return deferred.promise();
            },
    
    
            // 为了支持外部直接添加一个原生File对象。
            _wrapFile: function( file ) {
                if ( !(file instanceof WUFile) ) {
    
                    if ( !(file instanceof File) ) {
                        if ( !this._ruid ) {
                            throw new Error('Can\'t add external files.');
                        }
                        file = new File( this._ruid, file );
                    }
    
                    file = new WUFile( file );
                }
    
                return file;
            },
    
            // 判断文件是否可以被加入队列
            acceptFile: function( file ) {
                var invalid = !file || !file.size || this.accept &&
    
                        // 如果名字中有后缀，才做后缀白名单处理。
                        rExt.exec( file.name ) && !this.accept.test( file.name );
    
                return !invalid;
            },
    
    
            /**
             * @event beforeFileQueued
             * @param {File} file File对象
             * @description 当文件被加入队列之前触发。如果此事件handler的返回值为`false`，则此文件不会被添加进入队列。
             * @for  Uploader
             */
    
            /**
             * @event fileQueued
             * @param {File} file File对象
             * @description 当文件被加入队列以后触发。
             * @for  Uploader
             */
    
            _addFile: function( file ) {
                var me = this;
    
                file = me._wrapFile( file );
    
                // 不过类型判断允许不允许，先派送 `beforeFileQueued`
                if ( !me.owner.trigger( 'beforeFileQueued', file ) ) {
                    return;
                }
    
                // 类型不匹配，则派送错误事件，并返回。
                if ( !me.acceptFile( file ) ) {
                    me.owner.trigger( 'error', 'Q_TYPE_DENIED', file );
                    return;
                }
    
                me.queue.append( file );
                me.owner.trigger( 'fileQueued', file );
                return file;
            },
    
            getFile: function( fileId ) {
                return this.queue.getFile( fileId );
            },
    
            /**
             * @event filesQueued
             * @param {File} files 数组，内容为原始File(lib/File）对象。
             * @description 当一批文件添加进队列以后触发。
             * @for  Uploader
             */
            
            /**
             * @property {Boolean} [auto=false]
             * @namespace options
             * @for Uploader
             * @description 设置为 true 后，不需要手动调用上传，有文件选择即开始上传。
             * 
             */
    
            /**
             * @method addFiles
             * @grammar addFiles( file ) => undefined
             * @grammar addFiles( [file1, file2 ...] ) => undefined
             * @param {Array of File or File} [files] Files 对象 数组
             * @description 添加文件到队列
             * @for  Uploader
             */
            addFile: function( files ) {
                var me = this;
    
                if ( !files.length ) {
                    files = [ files ];
                }
    
                files = $.map( files, function( file ) {
                    return me._addFile( file );
                });
    			
    			if ( files.length ) {
    
                    me.owner.trigger( 'filesQueued', files );
    
    				if ( me.options.auto ) {
    					setTimeout(function() {
    						me.request('start-upload');
    					}, 20 );
    				}
                }
            },
    
            getStats: function() {
                return this.stats;
            },
    
            /**
             * @event fileDequeued
             * @param {File} file File对象
             * @description 当文件被移除队列后触发。
             * @for  Uploader
             */
    
             /**
             * @method removeFile
             * @grammar removeFile( file ) => undefined
             * @grammar removeFile( id ) => undefined
             * @grammar removeFile( file, true ) => undefined
             * @grammar removeFile( id, true ) => undefined
             * @param {File|id} file File对象或这File对象的id
             * @description 移除某一文件, 默认只会标记文件状态为已取消，如果第二个参数为 `true` 则会从 queue 中移除。
             * @for  Uploader
             * @example
             *
             * $li.on('click', '.remove-this', function() {
             *     uploader.removeFile( file );
             * })
             */
            removeFile: function( file, remove ) {
                var me = this;
    
                file = file.id ? file : me.queue.getFile( file );
    
                this.request( 'cancel-file', file );
    
                if ( remove ) {
                    this.queue.removeFile( file );
                }
            },
    
            /**
             * @method getFiles
             * @grammar getFiles() => Array
             * @grammar getFiles( status1, status2, status... ) => Array
             * @description 返回指定状态的文件集合，不传参数将返回所有状态的文件。
             * @for  Uploader
             * @example
             * console.log( uploader.getFiles() );    // => all files
             * console.log( uploader.getFiles('error') )    // => all error files.
             */
            getFiles: function() {
                return this.queue.getFiles.apply( this.queue, arguments );
            },
    
            fetchFile: function() {
                return this.queue.fetch.apply( this.queue, arguments );
            },
    
            /**
             * @method retry
             * @grammar retry() => undefined
             * @grammar retry( file ) => undefined
             * @description 重试上传，重试指定文件，或者从出错的文件开始重新上传。
             * @for  Uploader
             * @example
             * function retry() {
             *     uploader.retry();
             * }
             */
            retry: function( file, noForceStart ) {
                var me = this,
                    files, i, len;
    
                if ( file ) {
                    file = file.id ? file : me.queue.getFile( file );
                    file.setStatus( Status.QUEUED );
                    noForceStart || me.request('start-upload');
                    return;
                }
    
                files = me.queue.getFiles( Status.ERROR );
                i = 0;
                len = files.length;
    
                for ( ; i < len; i++ ) {
                    file = files[ i ];
                    file.setStatus( Status.QUEUED );
                }
    
                me.request('start-upload');
            },
    
            /**
             * @method sort
             * @grammar sort( fn ) => undefined
             * @description 排序队列中的文件，在上传之前调整可以控制上传顺序。
             * @for  Uploader
             */
            sortFiles: function() {
                return this.queue.sort.apply( this.queue, arguments );
            },
    
            /**
             * @event reset
             * @description 当 uploader 被重置的时候触发。
             * @for  Uploader
             */
    
            /**
             * @method reset
             * @grammar reset() => undefined
             * @description 重置uploader。目前只重置了队列。
             * @for  Uploader
             * @example
             * uploader.reset();
             */
            reset: function() {
                this.owner.trigger('reset');
                this.queue = new Queue();
                this.stats = this.queue.stats;
            },
    
            destroy: function() {
                this.reset();
                this.placeholder && this.placeholder.destroy();
            }
        });
    
    });
    /**
     * @fileOverview 添加获取Runtime相关信息的方法。
     */
    define('widgets/runtime',[
        'uploader',
        'runtime/runtime',
        'widgets/widget'
    ], function( Uploader, Runtime ) {
    
        Uploader.support = function() {
            return Runtime.hasRuntime.apply( Runtime, arguments );
        };
    
        /**
         * @property {Object} [runtimeOrder=html5,flash]
         * @namespace options
         * @for Uploader
         * @description 指定运行时启动顺序。默认会先尝试 html5 是否支持，如果支持则使用 html5, 否则使用 flash.
         *
         * 可以将此值设置成 `flash`，来强制使用 flash 运行时。
         */
    
        return Uploader.register({
            name: 'runtime',
    
            init: function() {
                if ( !this.predictRuntimeType() ) {
                    throw Error('Runtime Error');
                }
            },
    
            /**
             * 预测Uploader将采用哪个`Runtime`
             * @grammar predictRuntimeType() => String
             * @method predictRuntimeType
             * @for  Uploader
             */
            predictRuntimeType: function() {
                var orders = this.options.runtimeOrder || Runtime.orders,
                    type = this.type,
                    i, len;
    
                if ( !type ) {
                    orders = orders.split( /\s*,\s*/g );
    
                    for ( i = 0, len = orders.length; i < len; i++ ) {
                        if ( Runtime.hasRuntime( orders[ i ] ) ) {
                            this.type = type = orders[ i ];
                            break;
                        }
                    }
                }
    
                return type;
            }
        });
    });
    /**
     * @fileOverview Transport
     */
    define('lib/transport',[
        'base',
        'runtime/client',
        'mediator'
    ], function( Base, RuntimeClient, Mediator ) {
    
        var $ = Base.$;
    
        function Transport( opts ) {
            var me = this;
    
            opts = me.options = $.extend( true, {}, Transport.options, opts || {} );
            RuntimeClient.call( this, 'Transport' );
    
            this._block = null;
            this._blob = null;
            this._formData = opts.formData || {};
            this._headers = opts.headers || {};
    
            this.on( 'progress', this._timeout );
            this.on( 'load error', function() {
                me.trigger( 'progress', 1 );
                clearTimeout( me._timer );
            });
        }
    
        Transport.options = {
            server: '',
            method: 'POST',
    
            // 跨域时，是否允许携带cookie, 只有html5 runtime才有效
            withCredentials: false,
            fileVal: 'file',
            timeout: 2 * 60 * 1000,    // 2分钟
            formData: {},
            headers: {},
            sendAsBinary: false,
    
            customUploadResponse: null,
        };
    
        $.extend( Transport.prototype, {
    
            // 添加Blob, 只能添加一次，最后一次有效。
            appendBlob: function( key, blob, filename, block) {
                var me = this,
                    opts = me.options;
    
                if ( me.getRuid() ) {
                    me.disconnectRuntime();
                }
    
                // 连接到blob归属的同一个runtime.
                me.connectRuntime( blob.ruid, function() {
                    me.exec('init');
                });
    
                me._block = block;
                me._blob = blob;
                opts.fileVal = key || opts.fileVal;
                opts.filename = filename || opts.filename;
            },
    
            // 添加其他字段
            append: function( key, value ) {
                if ( typeof key === 'object' ) {
                    $.extend( this._formData, key );
                } else {
                    this._formData[ key ] = value;
                }
            },
    
            setRequestHeader: function( key, value ) {
                if ( typeof key === 'object' ) {
                    $.extend( this._headers, key );
                } else {
                    this._headers[ key ] = value;
                }
            },
    
            send: function( method ) {
                var me = this,
                    opts = me.options;
                if( opts.customUpload ){
                    opts.customUpload(me._block, {
                        onProgress: function (file, percentage) {
                            me.trigger('progress', percentage);
                        },
                        onSuccess: function (file, res) {
                            me.customUploadResponse = res;
                            me.trigger('load');
                        },
                        onError: function (file, error) {
                            me.trigger('error', error, true);
                        }
                    });
                }else{
                    this.exec( 'send', method );
                    this._timeout();
                }
            },
    
            abort: function() {
                clearTimeout( this._timer );
                return this.exec('abort');
            },
    
            destroy: function() {
                this.trigger('destroy');
                this.off();
                this.exec('destroy');
                this.disconnectRuntime();
            },
    
            getResponseHeaders: function() {
                return this.exec('getResponseHeaders');
            },
    
            getResponse: function() {
                return this.exec('getResponse');
            },
    
            getResponseAsJson: function() {
                return this.exec('getResponseAsJson');
            },
    
            getStatus: function() {
                return this.exec('getStatus');
            },
    
            _timeout: function() {
                var me = this,
                    duration = me.options.timeout;
    
                if ( !duration ) {
                    return;
                }
    
                clearTimeout( me._timer );
                me._timer = setTimeout(function() {
                    me.abort();
                    me.trigger( 'error', 'timeout' );
                }, duration );
            }
    
        });
    
        // 让Transport具备事件功能。
        Mediator.installTo( Transport.prototype );
    
        return Transport;
    });
    
    /**
     * @fileOverview 负责文件上传相关。
     */
    define('widgets/upload',[
        'base',
        'uploader',
        'file',
        'lib/transport',
        'widgets/widget'
    ], function( Base, Uploader, WUFile, Transport ) {
    
        var $ = Base.$,
            isPromise = Base.isPromise,
            Status = WUFile.Status;
    
        // 添加默认配置项
        $.extend( Uploader.options, {
    
    
            /**
             * @property {Boolean} [prepareNextFile=false]
             * @namespace options
             * @for Uploader
             * @description 是否允许在文件传输时提前把下一个文件准备好。
             * 某些文件的准备工作比较耗时，比如图片压缩，md5序列化。
             * 如果能提前在当前文件传输期处理，可以节省总体耗时。
             */
            prepareNextFile: false,
    
            /**
             * @property {Boolean} [chunked=false]
             * @namespace options
             * @for Uploader
             * @description 是否要分片处理大文件上传。
             */
            chunked: false,
    
            /**
             * @property {Boolean} [chunkSize=5242880]
             * @namespace options
             * @for Uploader
             * @description 如果要分片，分多大一片？ 默认大小为5M.
             */
            chunkSize: 5 * 1024 * 1024,
    
            /**
             * @property {Boolean} [chunkRetry=2]
             * @namespace options
             * @for Uploader
             * @description 如果某个分片由于网络问题出错，允许自动重传多少次？
             */
            chunkRetry: 2,
    
            /**
             * @property {Number} [chunkRetryDelay=1000]
             * @namespace options
             * @for Uploader
             * @description 开启重试后，设置重试延时时间, 单位毫秒。默认1000毫秒，即1秒.
             */
            chunkRetryDelay: 1000,
    
            /**
             * @property {Boolean} [threads=3]
             * @namespace options
             * @for Uploader
             * @description 上传并发数。允许同时最大上传进程数。
             */
            threads: 1,
    
    
            /**
             * @property {Object} [formData={}]
             * @namespace options
             * @for Uploader
             * @description 文件上传请求的参数表，每次发送都会发送此对象中的参数。
             */
            formData: {}
    
            /**
             * @property {Object} [fileVal='file']
             * @namespace options
             * @for Uploader
             * @description 设置文件上传域的name。
             */
    
             /**
             * @property {Object} [method=POST]
             * @namespace options
             * @for Uploader
             * @description 文件上传方式，`POST` 或者 `GET`。
             */
    
            /**
             * @property {Object} [sendAsBinary=false]
             * @namespace options
             * @for Uploader
             * @description 是否已二进制的流的方式发送文件，这样整个上传内容`php://input`都为文件内容，
             * 其他参数在$_GET数组中。
             */
        });
    
        // 负责将文件切片。
        function CuteFile( file, chunkSize ) {
            var pending = [],
                blob = file.source,
                total = blob.size,
                chunks = chunkSize ? Math.ceil( total / chunkSize ) : 1,
                start = 0,
                index = 0,
                len, api;
    
            api = {
                file: file,
    
                has: function() {
                    return !!pending.length;
                },
    
                shift: function() {
                    return pending.shift();
                },
    
                unshift: function( block ) {
                    pending.unshift( block );
                }
            };
    
            while ( index < chunks ) {
                len = Math.min( chunkSize, total - start );
    
                pending.push({
                    file: file,
                    start: start,
                    end: chunkSize ? (start + len) : total,
                    total: total,
                    chunks: chunks,
                    chunk: index++,
                    cuted: api
                });
                start += len;
            }
    
            file.blocks = pending.concat();
            file.remaning = pending.length;
    
            return api;
        }
    
        Uploader.register({
            name: 'upload',
    
            init: function() {
                var owner = this.owner,
                    me = this;
    
                this.runing = false;
                this.progress = false;
    
                owner
                    .on( 'startUpload', function() {
                        me.progress = true;
                    })
                    .on( 'uploadFinished', function() {
                        me.progress = false;
                    });
    
                // 记录当前正在传的数据，跟threads相关
                this.pool = [];
    
                // 缓存分好片的文件。
                this.stack = [];
    
                // 缓存即将上传的文件。
                this.pending = [];
    
                // 跟踪还有多少分片在上传中但是没有完成上传。
                this.remaning = 0;
                this.__tick = Base.bindFn( this._tick, this );
    
                // 销毁上传相关的属性。
                owner.on( 'uploadComplete', function( file ) {
    
                    // 把其他块取消了。
                    file.blocks && $.each( file.blocks, function( _, v ) {
                        v.transport && (v.transport.abort(), v.transport.destroy());
                        delete v.transport;
                    });
    
                    delete file.blocks;
                    delete file.remaning;
                });
            },
    
            reset: function() {
                this.request( 'stop-upload', true );
                this.runing = false;
                this.pool = [];
                this.stack = [];
                this.pending = [];
                this.remaning = 0;
                this._trigged = false;
                this._promise = null;
            },
    
            /**
             * @event startUpload
             * @description 当开始上传流程时触发。
             * @for  Uploader
             */
    
            /**
             * 开始上传。此方法可以从初始状态调用开始上传流程，也可以从暂停状态调用，继续上传流程。
             *
             * 可以指定开始某一个文件。
             * @grammar upload() => undefined
             * @grammar upload( file | fileId) => undefined
             * @method upload
             * @for  Uploader
             */
            startUpload: function(file) {
                var me = this;
    
                // 移出invalid的文件
                $.each( me.request( 'get-files', Status.INVALID ), function() {
                    me.request( 'remove-file', this );
                });
    
                // 如果指定了开始某个文件，则只开始指定的文件。
                if ( file ) {
                    file = file.id ? file : me.request( 'get-file', file );
    
                    if (file.getStatus() === Status.INTERRUPT) {
                        file.setStatus( Status.QUEUED );
    
                        $.each( me.pool, function( _, v ) {
    
                            // 之前暂停过。
                            if (v.file !== file) {
                                return;
                            }
    
                            v.transport && v.transport.send();
                            file.setStatus( Status.PROGRESS );
                        });
    
    
                    } else if (file.getStatus() !== Status.PROGRESS) {
                        file.setStatus( Status.QUEUED );
                    }
                } else {
                    $.each( me.request( 'get-files', [ Status.INITED ] ), function() {
                        this.setStatus( Status.QUEUED );
                    });
                }
    
                if ( me.runing ) {
                    me.owner.trigger('startUpload', file);// 开始上传或暂停恢复的，trigger event
                    return Base.nextTick( me.__tick );
                }
    
                me.runing = true;
                var files = [];
    
                // 如果有暂停的，则续传
                file || $.each( me.pool, function( _, v ) {
                    var file = v.file;
    
                    if ( file.getStatus() === Status.INTERRUPT ) {
                        me._trigged = false;
                        files.push(file);
    
                        if (v.waiting) {
                            return;
                        }
    
                        // 文件 prepare 完后，如果暂停了，这个时候只会把文件插入 pool, 而不会创建 tranport，
                        v.transport ? v.transport.send() : me._doSend(v);
                    }
                });
    
                $.each(files, function() {
                    this.setStatus( Status.PROGRESS );
                });
    
                file || $.each( me.request( 'get-files',
                        Status.INTERRUPT ), function() {
                    this.setStatus( Status.PROGRESS );
                });
    
                me._trigged = false;
                Base.nextTick( me.__tick );
                me.owner.trigger('startUpload');
            },
    
            /**
             * @event stopUpload
             * @description 当开始上传流程暂停时触发。
             * @for  Uploader
             */
    
            /**
             * 暂停上传。第一个参数为是否中断上传当前正在上传的文件。
             *
             * 如果第一个参数是文件，则只暂停指定文件。
             * @grammar stop() => undefined
             * @grammar stop( true ) => undefined
             * @grammar stop( file ) => undefined
             * @method stop
             * @for  Uploader
             */
            stopUpload: function( file, interrupt ) {
                var me = this;
    
                if (file === true) {
                    interrupt = file;
                    file = null;
                }
    
                if ( me.runing === false ) {
                    return;
                }
    
                // 如果只是暂停某个文件。
                if ( file ) {
                    file = file.id ? file : me.request( 'get-file', file );
    
                    if ( file.getStatus() !== Status.PROGRESS &&
                            file.getStatus() !== Status.QUEUED ) {
                        return;
                    }
    
                    file.setStatus( Status.INTERRUPT );
    
    
                    $.each( me.pool, function( _, v ) {
    
                        // 只 abort 指定的文件，每一个分片。
                        if (v.file === file) {
                            v.transport && v.transport.abort();
    
                            if (interrupt) {
                                me._putback(v);
                                me._popBlock(v);
                            }
                        }
                    });
    
                    me.owner.trigger('stopUpload', file);// 暂停，trigger event
    
                    return Base.nextTick( me.__tick );
                }
    
                me.runing = false;
    
                // 正在准备中的文件。
                if (this._promise && this._promise.file) {
                    this._promise.file.setStatus( Status.INTERRUPT );
                }
    
                interrupt && $.each( me.pool, function( _, v ) {
                    v.transport && v.transport.abort();
                    v.file.setStatus( Status.INTERRUPT );
                });
    
                me.owner.trigger('stopUpload');
            },
    
            /**
             * @method cancelFile
             * @grammar cancelFile( file ) => undefined
             * @grammar cancelFile( id ) => undefined
             * @param {File|id} file File对象或这File对象的id
             * @description 标记文件状态为已取消, 同时将中断文件传输。
             * @for  Uploader
             * @example
             *
             * $li.on('click', '.remove-this', function() {
             *     uploader.cancelFile( file );
             * })
             */
            cancelFile: function( file ) {
                file = file.id ? file : this.request( 'get-file', file );
    
                // 如果正在上传。
                file.blocks && $.each( file.blocks, function( _, v ) {
                    var _tr = v.transport;
    
                    if ( _tr ) {
                        _tr.abort();
                        _tr.destroy();
                        delete v.transport;
                    }
                });
    
                file.setStatus( Status.CANCELLED );
                this.owner.trigger( 'fileDequeued', file );
            },
    
            /**
             * 判断`Uploader`是否正在上传中。
             * @grammar isInProgress() => Boolean
             * @method isInProgress
             * @for  Uploader
             */
            isInProgress: function() {
                return !!this.progress;
            },
    
            _getStats: function() {
                return this.request('get-stats');
            },
    
            /**
             * 跳过一个文件上传，直接标记指定文件为已上传状态。
             * @grammar skipFile( file ) => undefined
             * @method skipFile
             * @for  Uploader
             */
            skipFile: function( file, status ) {
                file = file.id ? file : this.request( 'get-file', file );
    
                file.setStatus( status || Status.COMPLETE );
                file.skipped = true;
    
                // 如果正在上传。
                file.blocks && $.each( file.blocks, function( _, v ) {
                    var _tr = v.transport;
    
                    if ( _tr ) {
                        _tr.abort();
                        _tr.destroy();
                        delete v.transport;
                    }
                });
    
                this.owner.trigger( 'uploadSkip', file );
            },
    
            /**
             * @event uploadFinished
             * @description 当所有文件上传结束时触发。
             * @for  Uploader
             */
            _tick: function() {
                var me = this,
                    opts = me.options,
                    fn, val;
    
                // 上一个promise还没有结束，则等待完成后再执行。
                if ( me._promise ) {
                    return me._promise.always( me.__tick );
                }
    
                // 还有位置，且还有文件要处理的话。
                if ( me.pool.length < opts.threads && (val = me._nextBlock()) ) {
                    me._trigged = false;
    
                    fn = function( val ) {
                        me._promise = null;
    
                        // 有可能是reject过来的，所以要检测val的类型。
                        val && val.file && me._startSend( val );
                        Base.nextTick( me.__tick );
                    };
    
                    me._promise = isPromise( val ) ? val.always( fn ) : fn( val );
    
                // 没有要上传的了，且没有正在传输的了。
                } else if ( !me.remaning && !me._getStats().numOfQueue &&
                    !me._getStats().numOfInterrupt ) {
                    me.runing = false;
    
                    me._trigged || Base.nextTick(function() {
                        me.owner.trigger('uploadFinished');
                    });
                    me._trigged = true;
                }
            },
    
            _putback: function(block) {
                var idx;
    
                block.cuted.unshift(block);
                idx = this.stack.indexOf(block.cuted);
    
                if (!~idx) {
                    // 如果不在里面，说明移除过，需要把计数还原回去。
                    this.remaning++;
                    block.file.remaning++;
                    this.stack.unshift(block.cuted);
                }
            },
    
            _getStack: function() {
                var i = 0,
                    act;
    
                while ( (act = this.stack[ i++ ]) ) {
                    if ( act.has() && act.file.getStatus() === Status.PROGRESS ) {
                        return act;
                    } else if (!act.has() ||
                            act.file.getStatus() !== Status.PROGRESS &&
                            act.file.getStatus() !== Status.INTERRUPT ) {
    
                        // 把已经处理完了的，或者，状态为非 progress（上传中）、
                        // interupt（暂停中） 的移除。
                        this.stack.splice( --i, 1 );
                    }
                }
    
                return null;
            },
    
            _nextBlock: function() {
                var me = this,
                    opts = me.options,
                    act, next, done, preparing;
    
                // 如果当前文件还有没有需要传输的，则直接返回剩下的。
                if ( (act = this._getStack()) ) {
    
                    // 是否提前准备下一个文件
                    if ( opts.prepareNextFile && !me.pending.length ) {
                        me._prepareNextFile();
                    }
    
                    return act.shift();
    
                // 否则，如果正在运行，则准备下一个文件，并等待完成后返回下个分片。
                } else if ( me.runing ) {
    
                    // 如果缓存中有，则直接在缓存中取，没有则去queue中取。
                    if ( !me.pending.length && me._getStats().numOfQueue ) {
                        me._prepareNextFile();
                    }
    
                    next = me.pending.shift();
                    done = function( file ) {
                        if ( !file ) {
                            return null;
                        }
    
                        if (opts.customUpload) {
                            act = CuteFile(file, 0);
                        } else {
                            act = CuteFile(file, opts.chunked ? opts.chunkSize : 0);
                        }
                        me.stack.push(act);
                        return act.shift();
                    };
    
                    // 文件可能还在prepare中，也有可能已经完全准备好了。
                    if ( isPromise( next) ) {
                        preparing = next.file;
                        next = next[ next.pipe ? 'pipe' : 'then' ]( done );
                        next.file = preparing;
                        return next;
                    }
    
                    return done( next );
                }
            },
    
    
            /**
             * @event uploadStart
             * @param {File} file File对象
             * @description 某个文件开始上传前触发，一个文件只会触发一次。
             * @for  Uploader
             */
            _prepareNextFile: function() {
                var me = this,
                    file = me.request('fetch-file'),
                    pending = me.pending,
                    promise;
    
                if ( file ) {
                    promise = me.request( 'before-send-file', file, function() {
    
                        // 有可能文件被skip掉了。文件被skip掉后，状态坑定不是Queued.
                        if ( file.getStatus() === Status.PROGRESS ||
                            file.getStatus() === Status.INTERRUPT ) {
                            return file;
                        }
    
                        return me._finishFile( file );
                    });
    
                    me.owner.trigger( 'uploadStart', file );
                    file.setStatus( Status.PROGRESS );
    
                    promise.file = file;
    
                    // 如果还在pending中，则替换成文件本身。
                    promise.done(function() {
                        var idx = $.inArray( promise, pending );
    
                        ~idx && pending.splice( idx, 1, file );
                    });
    
                    // befeore-send-file的钩子就有错误发生。
                    promise.fail(function( reason ) {
                        file.setStatus( Status.ERROR, reason );
                        me.owner.trigger( 'uploadError', file, reason );
                        me.owner.trigger( 'uploadComplete', file );
                    });
    
                    pending.push( promise );
                }
            },
    
            // 让出位置了，可以让其他分片开始上传
            _popBlock: function( block ) {
                var idx = $.inArray( block, this.pool );
    
                this.pool.splice( idx, 1 );
                block.file.remaning--;
                this.remaning--;
            },
    
            // 开始上传，可以被掉过。如果promise被reject了，则表示跳过此分片。
            _startSend: function( block ) {
                var me = this,
                    file = block.file,
                    promise;
    
                // 有可能在 before-send-file 的 promise 期间改变了文件状态。
                // 如：暂停，取消
                // 我们不能中断 promise, 但是可以在 promise 完后，不做上传操作。
                if ( file.getStatus() !== Status.PROGRESS ) {
    
                    // 如果是中断，则还需要放回去。
                    if (file.getStatus() === Status.INTERRUPT) {
                        me._putback(block);
                    }
    
                    return;
                }
    
                me.pool.push( block );
                me.remaning++;
    
                // 如果没有分片，则直接使用原始的。
                // 不会丢失content-type信息。
                block.blob = block.chunks === 1 ? file.source :
                        file.source.slice( block.start, block.end );
    
                // hook, 每个分片发送之前可能要做些异步的事情。
                block.waiting = promise = me.request( 'before-send', block, function() {
                    delete block.waiting;
    
                    // 有可能文件已经上传出错了，所以不需要再传输了。
                    if ( file.getStatus() === Status.PROGRESS ) {
                        me._doSend( block );
                    } else if (block.file.getStatus() !== Status.INTERRUPT) {
                        me._popBlock(block);
                    }
    
                    Base.nextTick(me.__tick);
                });
    
                // 如果为fail了，则跳过此分片。
                promise.fail(function() {
                    delete block.waiting;
    
                    if ( file.remaning === 1 ) {
                        me._finishFile( file ).always(function() {
                            block.percentage = 1;
                            me._popBlock( block );
                            me.owner.trigger( 'uploadComplete', file );
                            Base.nextTick( me.__tick );
                        });
                    } else {
                        block.percentage = 1;
                        me.updateFileProgress( file );
                        me._popBlock( block );
                        Base.nextTick( me.__tick );
                    }
                });
            },
    
    
            /**
             * @event uploadBeforeSend
             * @param {Object} object
             * @param {Object} data 默认的上传参数，可以扩展此对象来控制上传参数。
             * @param {Object} headers 可以扩展此对象来控制上传头部。
             * @description 当某个文件的分块在发送前触发，主要用来询问是否要添加附带参数，大文件在开起分片上传的前提下此事件可能会触发多次。
             * @for  Uploader
             */
    
            /**
             * @event uploadAccept
             * @param {Object} object
             * @param {Object} ret 服务端的返回数据，json格式，如果服务端不是json格式，从ret._raw中取数据，自行解析。
             * @description 当某个文件上传到服务端响应后，会派送此事件来询问服务端响应是否有效。如果此事件handler返回值为`false`, 则此文件将派送`server`类型的`uploadError`事件。
             * @for  Uploader
             */
    
            /**
             * @event uploadProgress
             * @param {File} file File对象
             * @param {Number} percentage 上传进度
             * @description 上传过程中触发，携带上传进度。
             * @for  Uploader
             */
    
    
            /**
             * @event uploadError
             * @param {File} file File对象
             * @param {String} reason 出错的code
             * @description 当文件上传出错时触发。
             * @for  Uploader
             */
    
            /**
             * @event uploadSuccess
             * @param {File} file File对象
             * @param {Object} response 服务端返回的数据
             * @description 当文件上传成功时触发。
             * @for  Uploader
             */
    
            /**
             * @event uploadComplete
             * @param {File} [file] File对象
             * @description 不管成功或者失败，文件上传完成时触发。
             * @for  Uploader
             */
    
            // 做上传操作。
            _doSend: function( block ) {
                var me = this,
                    owner = me.owner,
                    opts = $.extend({}, me.options, block.options),
                    file = block.file,
                    tr = new Transport( opts ),
                    data = $.extend({}, opts.formData ),
                    headers = $.extend({}, opts.headers ),
                    requestAccept, ret;
    
                block.transport = tr;
    
                tr.on( 'destroy', function() {
                    delete block.transport;
                    me._popBlock( block );
                    Base.nextTick( me.__tick );
                });
    
                // 广播上传进度。以文件为单位。
                tr.on( 'progress', function( percentage ) {
                    block.percentage = percentage;
                    me.updateFileProgress( file );
                });
    
                // 用来询问，是否返回的结果是有错误的。
                requestAccept = function( reject ) {
                    var fn;
    
                    if( opts.customUpload ){
                        ret = tr.customUploadResponse;
                    }else{
                        ret = tr.getResponseAsJson() || {};
                        ret._raw = tr.getResponse();
                        ret._headers = tr.getResponseHeaders();
                    }
                    block.response = ret;
                    fn = function( value ) {
                        reject = value;
                    };
    
                    // 服务端响应了，不代表成功了，询问是否响应正确。
                    if ( !owner.trigger( 'uploadAccept', block, ret, fn ) ) {
                        reject = reject || 'server';
                    }
    
                    return reject;
                };
    
                // 尝试重试，然后广播文件上传出错。
                tr.on( 'error', function( type, flag ) {
                    // 在 runtime/html5/transport.js 上为 type 加上了状态码，形式：type|status|text（如：http|403|Forbidden）
                    // 这里把状态码解释出来，并还原后面代码所依赖的 type 变量
                    var typeArr = type.split( '|' ), status, statusText;
                    type = typeArr[0];
                    status = parseFloat( typeArr[1] ),
                    statusText = typeArr[2];
    
                    block.retried = block.retried || 0;
    
                    // 自动重试
                    if ( block.chunks > 1 && ~'http,abort,server'.indexOf( type.replace( /-.*/, '' ) ) &&
                            block.retried < opts.chunkRetry ) {
    
                        block.retried++;
    
                        me.retryTimer = setTimeout(function() {
                            tr.send();
                        }, opts.chunkRetryDelay || 1000);
    
                    } else {
    
                        // http status 500 ~ 600
                        if ( !flag && type === 'server' ) {
                            type = requestAccept( type );
                        }
    
                        file.setStatus( Status.ERROR, type );
                        owner.trigger( 'uploadError', file, type, status, statusText );
                        owner.trigger( 'uploadComplete', file );
                    }
                });
    
                // 上传成功
                tr.on( 'load', function() {
                    var reason;
    
                    // 如果非预期，转向上传出错。
                    if ( (reason = requestAccept()) ) {
                        tr.trigger( 'error', reason, true );
                        return;
                    }
    
                    // 全部上传完成。
                    if ( file.remaning === 1 ) {
                        me._finishFile( file, ret );
                    } else {
                        tr.destroy();
                    }
                });
    
                // 配置默认的上传字段。
                data = $.extend( data, {
                    id: file.id,
                    name: file.name,
                    type: file.type,
                    lastModifiedDate: file.lastModifiedDate,
                    size: file.size
                });
    
                block.chunks > 1 && $.extend( data, {
                    chunks: block.chunks,
                    chunk: block.chunk
                });
    
                // 在发送之间可以添加字段什么的。。。
                // 如果默认的字段不够使用，可以通过监听此事件来扩展
                owner.trigger( 'uploadBeforeSend', block, data, headers );
    
                // 开始发送。
                tr.appendBlob( opts.fileVal, block.blob, file.name, block);
                tr.append( data );
                tr.setRequestHeader( headers );
                tr.send();
            },
    
            // 完成上传。
            _finishFile: function( file, ret, hds ) {
                var owner = this.owner;
    
                return owner
                        .request( 'after-send-file', arguments, function() {
                            file.setStatus( Status.COMPLETE );
                            owner.trigger( 'uploadSuccess', file, ret, hds );
                        })
                        .fail(function( reason ) {
    
                            // 如果外部已经标记为invalid什么的，不再改状态。
                            if ( file.getStatus() === Status.PROGRESS ) {
                                file.setStatus( Status.ERROR, reason );
                            }
    
                            owner.trigger( 'uploadError', file, reason );
                        })
                        .always(function() {
                            owner.trigger( 'uploadComplete', file );
                        });
            },
    
            updateFileProgress: function(file) {
                var totalPercent = 0,
                    uploaded = 0;
    
                if (!file.blocks) {
                    return;
                }
    
                $.each( file.blocks, function( _, v ) {
                    uploaded += (v.percentage || 0) * (v.end - v.start);
                });
    
                totalPercent = uploaded / file.size;
                this.owner.trigger( 'uploadProgress', file, totalPercent || 0 );
            },
    
            destroy: function() {
                clearTimeout(this.retryTimer);
            }
    
        });
    });
    
    /**
     * @fileOverview 各种验证，包括文件总大小是否超出、单文件是否超出和文件是否重复。
     */
    
    define('widgets/validator',[
        'base',
        'uploader',
        'file',
        'widgets/widget'
    ], function( Base, Uploader, WUFile ) {
    
        var $ = Base.$,
            validators = {},
            api;
    
        /**
         * @event error
         * @param {String} type 错误类型。
         * @description 当validate不通过时，会以派送错误事件的形式通知调用者。通过`upload.on('error', handler)`可以捕获到此类错误，目前有以下错误会在特定的情况下派送错来。
         *
         * * `Q_EXCEED_NUM_LIMIT` 在设置了`fileNumLimit`且尝试给`uploader`添加的文件数量超出这个值时派送。
         * * `Q_EXCEED_SIZE_LIMIT` 在设置了`Q_EXCEED_SIZE_LIMIT`且尝试给`uploader`添加的文件总大小超出这个值时派送。
         * * `Q_TYPE_DENIED` 当文件类型不满足时触发。。
         * @for  Uploader
         */
    
        // 暴露给外面的api
        api = {
    
            // 添加验证器
            addValidator: function( type, cb ) {
                validators[ type ] = cb;
            },
    
            // 移除验证器
            removeValidator: function( type ) {
                delete validators[ type ];
            }
        };
    
        // 在Uploader初始化的时候启动Validators的初始化
        Uploader.register({
            name: 'validator',
    
            init: function() {
                var me = this;
                Base.nextTick(function() {
                    $.each( validators, function() {
                        this.call( me.owner );
                    });
                });
            }
        });
    
        /**
         * @property {int} [fileNumLimit=undefined]
         * @namespace options
         * @for Uploader
         * @description 验证文件总数量, 超出则不允许加入队列。
         */
        api.addValidator( 'fileNumLimit', function() {
            var uploader = this,
                opts = uploader.options,
                count = 0,
                max = parseInt( opts.fileNumLimit, 10 ),
                flag = true;
    
            if ( !max ) {
                return;
            }
    
            uploader.on( 'beforeFileQueued', function( file ) {
                    // 增加beforeFileQueuedCheckfileNumLimit验证,主要为了再次加载时(已存在历史文件)验证数量是否超过设置项
                if (!this.trigger('beforeFileQueuedCheckfileNumLimit', file,count)) {
                    return false;
                }
                if ( count >= max && flag ) {
                    flag = false;
                    this.trigger( 'error', 'Q_EXCEED_NUM_LIMIT', max, file );
                    setTimeout(function() {
                        flag = true;
                    }, 1 );
                }
    
                return count >= max ? false : true;
            });
    
            uploader.on( 'fileQueued', function() {
                count++;
            });
    
            uploader.on( 'fileDequeued', function() {
                count--;
            });
    
            uploader.on( 'reset', function() {
                count = 0;
            });
        });
    
    
        /**
         * @property {int} [fileSizeLimit=undefined]
         * @namespace options
         * @for Uploader
         * @description 验证文件总大小是否超出限制, 超出则不允许加入队列。
         */
        api.addValidator( 'fileSizeLimit', function() {
            var uploader = this,
                opts = uploader.options,
                count = 0,
                max = parseInt( opts.fileSizeLimit, 10 ),
                flag = true;
    
            if ( !max ) {
                return;
            }
    
            uploader.on( 'beforeFileQueued', function( file ) {
                var invalid = count + file.size > max;
    
                if ( invalid && flag ) {
                    flag = false;
                    this.trigger( 'error', 'Q_EXCEED_SIZE_LIMIT', max, file );
                    setTimeout(function() {
                        flag = true;
                    }, 1 );
                }
    
                return invalid ? false : true;
            });
    
            uploader.on( 'fileQueued', function( file ) {
                count += file.size;
            });
    
            uploader.on( 'fileDequeued', function( file ) {
                count -= file.size;
            });
    
            uploader.on( 'reset', function() {
                count = 0;
            });
        });
    
        /**
         * @property {int} [fileSingleSizeLimit=undefined]
         * @namespace options
         * @for Uploader
         * @description 验证单个文件大小是否超出限制, 超出则不允许加入队列。
         */
        api.addValidator( 'fileSingleSizeLimit', function() {
            var uploader = this,
                opts = uploader.options,
                max = opts.fileSingleSizeLimit;
    
            if ( !max ) {
                return;
            }
    
            uploader.on( 'beforeFileQueued', function( file ) {
    
                if ( file.size > max ) {
                    file.setStatus( WUFile.Status.INVALID, 'exceed_size' );
                    this.trigger( 'error', 'F_EXCEED_SIZE', max, file );
                    return false;
                }
    
            });
    
        });
    
        /**
         * @property {Boolean} [duplicate=undefined]
         * @namespace options
         * @for Uploader
         * @description 去重， 根据文件名字、文件大小和最后修改时间来生成hash Key.
         */
        api.addValidator( 'duplicate', function() {
            var uploader = this,
                opts = uploader.options,
                mapping = {};
    
            if ( opts.duplicate ) {
                return;
            }
    
            function hashString( str ) {
                var hash = 0,
                    i = 0,
                    len = str.length,
                    _char;
    
                for ( ; i < len; i++ ) {
                    _char = str.charCodeAt( i );
                    hash = _char + (hash << 6) + (hash << 16) - hash;
                }
    
                return hash;
            }
    
            uploader.on( 'beforeFileQueued', function( file ) {
                var hash = file.__hash || (file.__hash = hashString( file.name +
                        file.size + file.lastModifiedDate ));
    
                // 已经重复了
                if ( mapping[ hash ] ) {
                    this.trigger( 'error', 'F_DUPLICATE', file );
                    return false;
                }
            });
    
            uploader.on( 'fileQueued', function( file ) {
                var hash = file.__hash;
    
                hash && (mapping[ hash ] = true);
            });
    
            uploader.on( 'fileDequeued', function( file ) {
                var hash = file.__hash;
    
                hash && (delete mapping[ hash ]);
            });
    
            uploader.on( 'reset', function() {
                mapping = {};
            });
        });
    
        return api;
    });
    
    /**
     * @fileOverview Md5
     */
    define('lib/md5',[
        'runtime/client',
        'mediator'
    ], function( RuntimeClient, Mediator ) {
    
        function Md5() {
            RuntimeClient.call( this, 'Md5' );
        }
    
        // 让 Md5 具备事件功能。
        Mediator.installTo( Md5.prototype );
    
        Md5.prototype.loadFromBlob = function( blob ) {
            var me = this;
    
            if ( me.getRuid() ) {
                me.disconnectRuntime();
            }
    
            // 连接到blob归属的同一个runtime.
            me.connectRuntime( blob.ruid, function() {
                me.exec('init');
                me.exec( 'loadFromBlob', blob );
            });
        };
    
        Md5.prototype.getResult = function() {
            return this.exec('getResult');
        };
    
        return Md5;
    });
    /**
     * @fileOverview 图片操作, 负责预览图片和上传前压缩图片
     */
    define('widgets/md5',[
        'base',
        'uploader',
        'lib/md5',
        'lib/blob',
        'widgets/widget'
    ], function( Base, Uploader, Md5, Blob ) {
    
        return Uploader.register({
            name: 'md5',
    
    
            /**
             * 计算文件 md5 值，返回一个 promise 对象，可以监听 progress 进度。
             *
             *
             * @method md5File
             * @grammar md5File( file[, start[, end]] ) => promise
             * @for Uploader
             * @example
             *
             * uploader.on( 'fileQueued', function( file ) {
             *     var $li = ...;
             *
             *     uploader.md5File( file )
             *
             *         // 及时显示进度
             *         .progress(function(percentage) {
             *             console.log('Percentage:', percentage);
             *         })
             *
             *         // 完成
             *         .then(function(val) {
             *             console.log('md5 result:', val);
             *         });
             *
             * });
             */
            md5File: function( file, start, end ) {
                var md5 = new Md5(),
                    deferred = Base.Deferred(),
                    blob = (file instanceof Blob) ? file :
                        this.request( 'get-file', file ).source;
    
                md5.on( 'progress load', function( e ) {
                    e = e || {};
                    deferred.notify( e.total ? e.loaded / e.total : 1 );
                });
    
                md5.on( 'complete', function() {
                    deferred.resolve( md5.getResult() );
                });
    
                md5.on( 'error', function( reason ) {
                    deferred.reject( reason );
                });
    
                if ( arguments.length > 1 ) {
                    start = start || 0;
                    end = end || 0;
                    start < 0 && (start = blob.size + start);
                    end < 0 && (end = blob.size + end);
                    end = Math.min( end, blob.size );
                    blob = blob.slice( start, end );
                }
    
                md5.loadFromBlob( blob );
    
                return deferred.promise();
            }
        });
    });
    /**
     * @fileOverview Runtime管理器，负责Runtime的选择, 连接
     */
    define('runtime/compbase',[],function() {
    
        function CompBase( owner, runtime ) {
    
            this.owner = owner;
            this.options = owner.options;
    
            this.getRuntime = function() {
                return runtime;
            };
    
            this.getRuid = function() {
                return runtime.uid;
            };
    
            this.trigger = function() {
                return owner.trigger.apply( owner, arguments );
            };
        }
    
        return CompBase;
    });
    /**
     * @fileOverview Html5Runtime
     */
    define('runtime/html5/runtime',[
        'base',
        'runtime/runtime',
        'runtime/compbase'
    ], function( Base, Runtime, CompBase ) {
    
        var type = 'html5',
            components = {};
    
        function Html5Runtime() {
            var pool = {},
                me = this,
                destroy = this.destroy;
    
            Runtime.apply( me, arguments );
            me.type = type;
    
    
            // 这个方法的调用者，实际上是RuntimeClient
            me.exec = function( comp, fn/*, args...*/) {
                var client = this,
                    uid = client.uid,
                    args = Base.slice( arguments, 2 ),
                    instance;
    
                if ( components[ comp ] ) {
                    instance = pool[ uid ] = pool[ uid ] ||
                            new components[ comp ]( client, me );
    
                    if ( instance[ fn ] ) {
                        return instance[ fn ].apply( instance, args );
                    }
                }
            };
    
            me.destroy = function() {
                // @todo 删除池子中的所有实例
                return destroy && destroy.apply( this, arguments );
            };
        }
    
        Base.inherits( Runtime, {
            constructor: Html5Runtime,
    
            // 不需要连接其他程序，直接执行callback
            init: function() {
                var me = this;
                setTimeout(function() {
                    me.trigger('ready');
                }, 1 );
            }
    
        });
    
        // 注册Components
        Html5Runtime.register = function( name, component ) {
            var klass = components[ name ] = Base.inherits( CompBase, component );
            return klass;
        };
    
        // 注册html5运行时。
        // 只有在支持的前提下注册。
        if ( window.Blob && window.FileReader && window.DataView ) {
            Runtime.addRuntime( type, Html5Runtime );
        }
    
        return Html5Runtime;
    });
    /**
     * @fileOverview Blob Html实现
     */
    define('runtime/html5/blob',[
        'runtime/html5/runtime',
        'lib/blob'
    ], function( Html5Runtime, Blob ) {
    
        return Html5Runtime.register( 'Blob', {
            slice: function( start, end ) {
                var blob = this.owner.source,
                    slice = blob.slice || blob.webkitSlice || blob.mozSlice;
    
                blob = slice.call( blob, start, end );
    
                return new Blob( this.getRuid(), blob );
            }
        });
    });
    /**
     * @fileOverview FilePaste
     */
    define('runtime/html5/dnd',[
        'base',
        'runtime/html5/runtime',
        'lib/file'
    ], function( Base, Html5Runtime, File ) {
    
        var $ = Base.$,
            prefix = 'webuploader-dnd-';
    
        return Html5Runtime.register( 'DragAndDrop', {
            init: function() {
                var elem = this.elem = this.options.container;
    
                this.dragEnterHandler = Base.bindFn( this._dragEnterHandler, this );
                this.dragOverHandler = Base.bindFn( this._dragOverHandler, this );
                this.dragLeaveHandler = Base.bindFn( this._dragLeaveHandler, this );
                this.dropHandler = Base.bindFn( this._dropHandler, this );
                this.dndOver = false;
    
                elem.on( 'dragenter', this.dragEnterHandler );
                elem.on( 'dragover', this.dragOverHandler );
                elem.on( 'dragleave', this.dragLeaveHandler );
                elem.on( 'drop', this.dropHandler );
    
                if ( this.options.disableGlobalDnd ) {
                    $( document ).on( 'dragover', this.dragOverHandler );
                    $( document ).on( 'drop', this.dropHandler );
                }
            },
    
            _dragEnterHandler: function( e ) {
                var me = this,
                    denied = me._denied || false,
                    items;
    
                e = e.originalEvent || e;
    
                if ( !me.dndOver ) {
                    me.dndOver = true;
    
                    // 注意只有 chrome 支持。
                    items = e.dataTransfer.items;
    
                    if ( items && items.length ) {
                        me._denied = denied = !me.trigger( 'accept', items );
                    }
    
                    me.elem.addClass( prefix + 'over' );
                    me.elem[ denied ? 'addClass' :
                            'removeClass' ]( prefix + 'denied' );
                }
    
                e.dataTransfer.dropEffect = denied ? 'none' : 'copy';
    
                return false;
            },
    
            _dragOverHandler: function( e ) {
                // 只处理框内的。
                var parentElem = this.elem.parent().get( 0 );
                if ( parentElem && !$.contains( parentElem, e.currentTarget ) ) {
                    return false;
                }
    
                clearTimeout( this._leaveTimer );
                this._dragEnterHandler.call( this, e );
    
                return false;
            },
    
            _dragLeaveHandler: function() {
                var me = this,
                    handler;
    
                handler = function() {
                    me.dndOver = false;
                    me.elem.removeClass( prefix + 'over ' + prefix + 'denied' );
                };
    
                clearTimeout( me._leaveTimer );
                me._leaveTimer = setTimeout( handler, 100 );
                return false;
            },
    
            _dropHandler: function( e ) {
                var me = this,
                    ruid = me.getRuid(),
                    parentElem = me.elem.parent().get( 0 ),
                    dataTransfer, data;
    
                // 只处理框内的。
                if ( parentElem && !$.contains( parentElem, e.currentTarget ) ) {
                    return false;
                }
    
                e = e.originalEvent || e;
                dataTransfer = e.dataTransfer;
    
                // 如果是页面内拖拽，还不能处理，不阻止事件。
                // 此处 ie11 下会报参数错误，
                try {
                    data = dataTransfer.getData('text/html');
                } catch( err ) {
                }
    
                me.dndOver = false;
                me.elem.removeClass( prefix + 'over' );
    
                if ( !dataTransfer || data ) {
                    return;
                }
    
                me._getTansferFiles( dataTransfer, function( results ) {
                    me.trigger( 'drop', $.map( results, function( file ) {
                        return new File( ruid, file );
                    }) );
                });
    
                return false;
            },
    
            // 如果传入 callback 则去查看文件夹，否则只管当前文件夹。
            _getTansferFiles: function( dataTransfer, callback ) {
                var results  = [],
                    promises = [],
                    items, files, file, item, i, len, canAccessFolder;
    
                items = dataTransfer.items;
                files = dataTransfer.files;
    
                canAccessFolder = !!(items && items[ 0 ].webkitGetAsEntry);
    
                for ( i = 0, len = files.length; i < len; i++ ) {
                    file = files[ i ];
                    item = items && items[ i ];
    
                    if ( canAccessFolder && item.webkitGetAsEntry().isDirectory ) {
    
                        promises.push( this._traverseDirectoryTree(
                                item.webkitGetAsEntry(), results ) );
                    } else {
                        results.push( file );
                    }
                }
    
                Base.when.apply( Base, promises ).done(function() {
    
                    if ( !results.length ) {
                        return;
                    }
    
                    callback( results );
                });
            },
    
            _traverseDirectoryTree: function( entry, results ) {
                var deferred = Base.Deferred(),
                    me = this;
    
                if ( entry.isFile ) {
                    entry.file(function( file ) {
                        results.push( file );
                        deferred.resolve();
                    });
                } else if ( entry.isDirectory ) {
                    entry.createReader().readEntries(function( entries ) {
                        var len = entries.length,
                            promises = [],
                            arr = [],    // 为了保证顺序。
                            i;
    
                        for ( i = 0; i < len; i++ ) {
                            promises.push( me._traverseDirectoryTree(
                                    entries[ i ], arr ) );
                        }
    
                        Base.when.apply( Base, promises ).then(function() {
                            results.push.apply( results, arr );
                            deferred.resolve();
                        }, deferred.reject );
                    });
                }
    
                return deferred.promise();
            },
    
            destroy: function() {
                var elem = this.elem;
    
                // 还没 init 就调用 destroy
                if (!elem) {
                    return;
                }
    
                elem.off( 'dragenter', this.dragEnterHandler );
                elem.off( 'dragover', this.dragOverHandler );
                elem.off( 'dragleave', this.dragLeaveHandler );
                elem.off( 'drop', this.dropHandler );
    
                if ( this.options.disableGlobalDnd ) {
                    $( document ).off( 'dragover', this.dragOverHandler );
                    $( document ).off( 'drop', this.dropHandler );
                }
            }
        });
    });
    
    /**
     * @fileOverview FilePaste
     */
    define('runtime/html5/filepaste',[
        'base',
        'runtime/html5/runtime',
        'lib/file'
    ], function( Base, Html5Runtime, File ) {
    
        return Html5Runtime.register( 'FilePaste', {
            init: function() {
                var opts = this.options,
                    elem = this.elem = opts.container,
                    accept = '.*',
                    arr, i, len, item;
    
                // accetp的mimeTypes中生成匹配正则。
                if ( opts.accept ) {
                    arr = [];
    
                    for ( i = 0, len = opts.accept.length; i < len; i++ ) {
                        item = opts.accept[ i ].mimeTypes;
                        item && arr.push( item );
                    }
    
                    if ( arr.length ) {
                        accept = arr.join(',');
                        accept = accept.replace( /,/g, '|' ).replace( /\*/g, '.*' );
                    }
                }
                this.accept = accept = new RegExp( accept, 'i' );
                this.hander = Base.bindFn( this._pasteHander, this );
                elem.on( 'paste', this.hander );
            },
    
            _pasteHander: function( e ) {
                var allowed = [],
                    ruid = this.getRuid(),
                    items, item, blob, i, len;
    
                e = e.originalEvent || e;
                items = e.clipboardData.items;
    
                for ( i = 0, len = items.length; i < len; i++ ) {
                    item = items[ i ];
    
                    if ( item.kind !== 'file' || !(blob = item.getAsFile()) ) {
                        continue;
                    }
    
                    allowed.push( new File( ruid, blob ) );
                }
    
                if ( allowed.length ) {
                    // 不阻止非文件粘贴（文字粘贴）的事件冒泡
                    e.preventDefault();
                    e.stopPropagation();
                    this.trigger( 'paste', allowed );
                }
            },
    
            destroy: function() {
                this.elem.off( 'paste', this.hander );
            }
        });
    });
    
    /**
     * @fileOverview FilePicker
     */
    define('runtime/html5/filepicker',[
        'base',
        'runtime/html5/runtime'
    ], function( Base, Html5Runtime ) {
    
        var $ = Base.$;
    
        return Html5Runtime.register( 'FilePicker', {
            init: function() {
                var container = this.getRuntime().getContainer(),
                    me = this,
                    owner = me.owner,
                    opts = me.options,
                    label = this.label = $( document.createElement('label') ),
                    input =  this.input = $( document.createElement('input') ),
                    arr, i, len, mouseHandler, changeHandler;
    
                input.attr( 'type', 'file' );
                // input.attr( 'capture', 'camera');
                input.attr( 'name', opts.name );
                input.addClass('webuploader-element-invisible');
    
                label.on( 'click', function(e) {
                    input.trigger('click');
                    e.stopPropagation();
                    owner.trigger('dialogopen');
                });
    
                label.css({
                    opacity: 0,
                    width: '100%',
                    height: '100%',
                    display: 'block',
                    cursor: 'pointer',
                    background: '#ffffff'
                });
    
                if ( opts.multiple ) {
                    input.attr( 'multiple', 'multiple' );
                }
    
                // @todo Firefox不支持单独指定后缀
                if ( opts.accept && opts.accept.length > 0 ) {
                    arr = [];
    
                    for ( i = 0, len = opts.accept.length; i < len; i++ ) {
                        arr.push( opts.accept[ i ].mimeTypes );
                    }
    
                    input.attr( 'accept', arr.join(',') );
                }
    
                container.append( input );
                container.append( label );
    
                mouseHandler = function( e ) {
                    owner.trigger( e.type );
                };
    
                changeHandler = function( e ) {
                    var clone;
    
                    // 解决chrome 56 第二次打开文件选择器，然后点击取消，依然会触发change事件的问题
                    if (e.target.files.length === 0){
                        return false;
                    }
    
                    // 第一次上传图片后，第二次再点击弹出文件选择器窗，等待
                    me.files = e.target.files;
    
    
                    // reset input
                    clone = this.cloneNode( true );
                    clone.value = null;
                    this.parentNode.replaceChild( clone, this );
    
                    input.off();
                    input = $( clone ).on( 'change', changeHandler )
                            .on( 'mouseenter mouseleave', mouseHandler );
    
                    owner.trigger('change');
                }
                input.on( 'change', changeHandler);
                label.on( 'mouseenter mouseleave', mouseHandler );
    
            },
    
    
            getFiles: function() {
                return this.files;
            },
    
            destroy: function() {
                this.input.off();
                this.label.off();
            }
        });
    });
    
    /**
     * Terms:
     *
     * Uint8Array, FileReader, BlobBuilder, atob, ArrayBuffer
     * @fileOverview Image控件
     */
    define('runtime/html5/util',[
        'base'
    ], function( Base ) {
    
        var urlAPI = window.createObjectURL && window ||
                window.URL && URL.revokeObjectURL && URL ||
                window.webkitURL,
            createObjectURL = Base.noop,
            revokeObjectURL = createObjectURL;
    
        if ( urlAPI ) {
    
            // 更安全的方式调用，比如android里面就能把context改成其他的对象。
            createObjectURL = function() {
                return urlAPI.createObjectURL.apply( urlAPI, arguments );
            };
    
            revokeObjectURL = function() {
                return urlAPI.revokeObjectURL.apply( urlAPI, arguments );
            };
        }
    
        return {
            createObjectURL: createObjectURL,
            revokeObjectURL: revokeObjectURL,
    
            dataURL2Blob: function( dataURI ) {
                var byteStr, intArray, ab, i, mimetype, parts;
    
                parts = dataURI.split(',');
    
                if ( ~parts[ 0 ].indexOf('base64') ) {
                    byteStr = atob( parts[ 1 ] );
                } else {
                    byteStr = decodeURIComponent( parts[ 1 ] );
                }
    
                ab = new ArrayBuffer( byteStr.length );
                intArray = new Uint8Array( ab );
    
                for ( i = 0; i < byteStr.length; i++ ) {
                    intArray[ i ] = byteStr.charCodeAt( i );
                }
    
                mimetype = parts[ 0 ].split(':')[ 1 ].split(';')[ 0 ];
    
                return this.arrayBufferToBlob( ab, mimetype );
            },
    
            dataURL2ArrayBuffer: function( dataURI ) {
                var byteStr, intArray, i, parts;
    
                parts = dataURI.split(',');
    
                if ( ~parts[ 0 ].indexOf('base64') ) {
                    byteStr = atob( parts[ 1 ] );
                } else {
                    byteStr = decodeURIComponent( parts[ 1 ] );
                }
    
                intArray = new Uint8Array( byteStr.length );
    
                for ( i = 0; i < byteStr.length; i++ ) {
                    intArray[ i ] = byteStr.charCodeAt( i );
                }
    
                return intArray.buffer;
            },
    
            arrayBufferToBlob: function( buffer, type ) {
                var builder = window.BlobBuilder || window.WebKitBlobBuilder,
                    bb;
    
                // android不支持直接new Blob, 只能借助blobbuilder.
                if ( builder ) {
                    bb = new builder();
                    bb.append( buffer );
                    return bb.getBlob( type );
                }
    
                return new Blob([ buffer ], type ? { type: type } : {} );
            },
    
            // 抽出来主要是为了解决android下面canvas.toDataUrl不支持jpeg.
            // 你得到的结果是png.
            canvasToDataUrl: function( canvas, type, quality ) {
                return canvas.toDataURL( type, quality / 100 );
            },
    
            // imagemeat会复写这个方法，如果用户选择加载那个文件了的话。
            parseMeta: function( blob, callback ) {
                callback( false, {});
            },
    
            // imagemeat会复写这个方法，如果用户选择加载那个文件了的话。
            updateImageHead: function( data ) {
                return data;
            }
        };
    });
    /**
     * Terms:
     *
     * Uint8Array, FileReader, BlobBuilder, atob, ArrayBuffer
     * @fileOverview Image控件
     */
    define('runtime/html5/imagemeta',[
        'runtime/html5/util'
    ], function( Util ) {
    
        var api;
    
        api = {
            parsers: {
                0xffe1: []
            },
    
            maxMetaDataSize: 262144,
    
            parse: function( blob, cb ) {
                var me = this,
                    fr = new FileReader();
    
                fr.onload = function() {
                    cb( false, me._parse( this.result ) );
                    fr = fr.onload = fr.onerror = null;
                };
    
                fr.onerror = function( e ) {
                    cb( e.message );
                    fr = fr.onload = fr.onerror = null;
                };
    
                blob = blob.slice( 0, me.maxMetaDataSize );
                fr.readAsArrayBuffer( blob.getSource() );
            },
    
            _parse: function( buffer, noParse ) {
                if ( buffer.byteLength < 6 ) {
                    return;
                }
    
                var dataview = new DataView( buffer ),
                    offset = 2,
                    maxOffset = dataview.byteLength - 4,
                    headLength = offset,
                    ret = {},
                    markerBytes, markerLength, parsers, i;
    
                if ( dataview.getUint16( 0 ) === 0xffd8 ) {
    
                    while ( offset < maxOffset ) {
                        markerBytes = dataview.getUint16( offset );
    
                        if ( markerBytes >= 0xffe0 && markerBytes <= 0xffef ||
                                markerBytes === 0xfffe ) {
    
                            markerLength = dataview.getUint16( offset + 2 ) + 2;
    
                            if ( offset + markerLength > dataview.byteLength ) {
                                break;
                            }
    
                            parsers = api.parsers[ markerBytes ];
    
                            if ( !noParse && parsers ) {
                                for ( i = 0; i < parsers.length; i += 1 ) {
                                    parsers[ i ].call( api, dataview, offset,
                                            markerLength, ret );
                                }
                            }
    
                            offset += markerLength;
                            headLength = offset;
                        } else {
                            break;
                        }
                    }
    
                    if ( headLength > 6 ) {
                        if ( buffer.slice ) {
                            ret.imageHead = buffer.slice( 2, headLength );
                        } else {
                            // Workaround for IE10, which does not yet
                            // support ArrayBuffer.slice:
                            ret.imageHead = new Uint8Array( buffer )
                                    .subarray( 2, headLength );
                        }
                    }
                }
    
                return ret;
            },
    
            updateImageHead: function( buffer, head ) {
                var data = this._parse( buffer, true ),
                    buf1, buf2, bodyoffset;
    
    
                bodyoffset = 2;
                if ( data.imageHead ) {
                    bodyoffset = 2 + data.imageHead.byteLength;
                }
    
                if ( buffer.slice ) {
                    buf2 = buffer.slice( bodyoffset );
                } else {
                    buf2 = new Uint8Array( buffer ).subarray( bodyoffset );
                }
    
                buf1 = new Uint8Array( head.byteLength + 2 + buf2.byteLength );
    
                buf1[ 0 ] = 0xFF;
                buf1[ 1 ] = 0xD8;
                buf1.set( new Uint8Array( head ), 2 );
                buf1.set( new Uint8Array( buf2 ), head.byteLength + 2 );
    
                return buf1.buffer;
            }
        };
    
        Util.parseMeta = function() {
            return api.parse.apply( api, arguments );
        };
    
        Util.updateImageHead = function() {
            return api.updateImageHead.apply( api, arguments );
        };
    
        return api;
    });
    /**
     * 代码来自于：https://github.com/blueimp/JavaScript-Load-Image
     * 暂时项目中只用了orientation.
     *
     * 去除了 Exif Sub IFD Pointer, GPS Info IFD Pointer, Exif Thumbnail.
     * @fileOverview EXIF解析
     */
    
    // Sample
    // ====================================
    // Make : Apple
    // Model : iPhone 4S
    // Orientation : 1
    // XResolution : 72 [72/1]
    // YResolution : 72 [72/1]
    // ResolutionUnit : 2
    // Software : QuickTime 7.7.1
    // DateTime : 2013:09:01 22:53:55
    // ExifIFDPointer : 190
    // ExposureTime : 0.058823529411764705 [1/17]
    // FNumber : 2.4 [12/5]
    // ExposureProgram : Normal program
    // ISOSpeedRatings : 800
    // ExifVersion : 0220
    // DateTimeOriginal : 2013:09:01 22:52:51
    // DateTimeDigitized : 2013:09:01 22:52:51
    // ComponentsConfiguration : YCbCr
    // ShutterSpeedValue : 4.058893515764426
    // ApertureValue : 2.5260688216892597 [4845/1918]
    // BrightnessValue : -0.3126686601998395
    // MeteringMode : Pattern
    // Flash : Flash did not fire, compulsory flash mode
    // FocalLength : 4.28 [107/25]
    // SubjectArea : [4 values]
    // FlashpixVersion : 0100
    // ColorSpace : 1
    // PixelXDimension : 2448
    // PixelYDimension : 3264
    // SensingMethod : One-chip color area sensor
    // ExposureMode : 0
    // WhiteBalance : Auto white balance
    // FocalLengthIn35mmFilm : 35
    // SceneCaptureType : Standard
    define('runtime/html5/imagemeta/exif',[
        'base',
        'runtime/html5/imagemeta'
    ], function( Base, ImageMeta ) {
    
        var EXIF = {};
    
        EXIF.ExifMap = function() {
            return this;
        };
    
        EXIF.ExifMap.prototype.map = {
            'Orientation': 0x0112
        };
    
        EXIF.ExifMap.prototype.get = function( id ) {
            return this[ id ] || this[ this.map[ id ] ];
        };
    
        EXIF.exifTagTypes = {
            // byte, 8-bit unsigned int:
            1: {
                getValue: function( dataView, dataOffset ) {
                    return dataView.getUint8( dataOffset );
                },
                size: 1
            },
    
            // ascii, 8-bit byte:
            2: {
                getValue: function( dataView, dataOffset ) {
                    return String.fromCharCode( dataView.getUint8( dataOffset ) );
                },
                size: 1,
                ascii: true
            },
    
            // short, 16 bit int:
            3: {
                getValue: function( dataView, dataOffset, littleEndian ) {
                    return dataView.getUint16( dataOffset, littleEndian );
                },
                size: 2
            },
    
            // long, 32 bit int:
            4: {
                getValue: function( dataView, dataOffset, littleEndian ) {
                    return dataView.getUint32( dataOffset, littleEndian );
                },
                size: 4
            },
    
            // rational = two long values,
            // first is numerator, second is denominator:
            5: {
                getValue: function( dataView, dataOffset, littleEndian ) {
                    return dataView.getUint32( dataOffset, littleEndian ) /
                        dataView.getUint32( dataOffset + 4, littleEndian );
                },
                size: 8
            },
    
            // slong, 32 bit signed int:
            9: {
                getValue: function( dataView, dataOffset, littleEndian ) {
                    return dataView.getInt32( dataOffset, littleEndian );
                },
                size: 4
            },
    
            // srational, two slongs, first is numerator, second is denominator:
            10: {
                getValue: function( dataView, dataOffset, littleEndian ) {
                    return dataView.getInt32( dataOffset, littleEndian ) /
                        dataView.getInt32( dataOffset + 4, littleEndian );
                },
                size: 8
            }
        };
    
        // undefined, 8-bit byte, value depending on field:
        EXIF.exifTagTypes[ 7 ] = EXIF.exifTagTypes[ 1 ];
    
        EXIF.getExifValue = function( dataView, tiffOffset, offset, type, length,
                littleEndian ) {
    
            var tagType = EXIF.exifTagTypes[ type ],
                tagSize, dataOffset, values, i, str, c;
    
            if ( !tagType ) {
                Base.log('Invalid Exif data: Invalid tag type.');
                return;
            }
    
            tagSize = tagType.size * length;
    
            // Determine if the value is contained in the dataOffset bytes,
            // or if the value at the dataOffset is a pointer to the actual data:
            dataOffset = tagSize > 4 ? tiffOffset + dataView.getUint32( offset + 8,
                    littleEndian ) : (offset + 8);
    
            if ( dataOffset + tagSize > dataView.byteLength ) {
                Base.log('Invalid Exif data: Invalid data offset.');
                return;
            }
    
            if ( length === 1 ) {
                return tagType.getValue( dataView, dataOffset, littleEndian );
            }
    
            values = [];
    
            for ( i = 0; i < length; i += 1 ) {
                values[ i ] = tagType.getValue( dataView,
                        dataOffset + i * tagType.size, littleEndian );
            }
    
            if ( tagType.ascii ) {
                str = '';
    
                // Concatenate the chars:
                for ( i = 0; i < values.length; i += 1 ) {
                    c = values[ i ];
    
                    // Ignore the terminating NULL byte(s):
                    if ( c === '\u0000' ) {
                        break;
                    }
                    str += c;
                }
    
                return str;
            }
            return values;
        };
    
        EXIF.parseExifTag = function( dataView, tiffOffset, offset, littleEndian,
                data ) {
    
            var tag = dataView.getUint16( offset, littleEndian );
            data.exif[ tag ] = EXIF.getExifValue( dataView, tiffOffset, offset,
                    dataView.getUint16( offset + 2, littleEndian ),    // tag type
                    dataView.getUint32( offset + 4, littleEndian ),    // tag length
                    littleEndian );
        };
    
        EXIF.parseExifTags = function( dataView, tiffOffset, dirOffset,
                littleEndian, data ) {
    
            var tagsNumber, dirEndOffset, i;
    
            if ( dirOffset + 6 > dataView.byteLength ) {
                Base.log('Invalid Exif data: Invalid directory offset.');
                return;
            }
    
            tagsNumber = dataView.getUint16( dirOffset, littleEndian );
            dirEndOffset = dirOffset + 2 + 12 * tagsNumber;
    
            if ( dirEndOffset + 4 > dataView.byteLength ) {
                Base.log('Invalid Exif data: Invalid directory size.');
                return;
            }
    
            for ( i = 0; i < tagsNumber; i += 1 ) {
                this.parseExifTag( dataView, tiffOffset,
                        dirOffset + 2 + 12 * i,    // tag offset
                        littleEndian, data );
            }
    
            // Return the offset to the next directory:
            return dataView.getUint32( dirEndOffset, littleEndian );
        };
    
        // EXIF.getExifThumbnail = function(dataView, offset, length) {
        //     var hexData,
        //         i,
        //         b;
        //     if (!length || offset + length > dataView.byteLength) {
        //         Base.log('Invalid Exif data: Invalid thumbnail data.');
        //         return;
        //     }
        //     hexData = [];
        //     for (i = 0; i < length; i += 1) {
        //         b = dataView.getUint8(offset + i);
        //         hexData.push((b < 16 ? '0' : '') + b.toString(16));
        //     }
        //     return 'data:image/jpeg,%' + hexData.join('%');
        // };
    
        EXIF.parseExifData = function( dataView, offset, length, data ) {
    
            var tiffOffset = offset + 10,
                littleEndian, dirOffset;
    
            // Check for the ASCII code for "Exif" (0x45786966):
            if ( dataView.getUint32( offset + 4 ) !== 0x45786966 ) {
                // No Exif data, might be XMP data instead
                return;
            }
            if ( tiffOffset + 8 > dataView.byteLength ) {
                Base.log('Invalid Exif data: Invalid segment size.');
                return;
            }
    
            // Check for the two null bytes:
            if ( dataView.getUint16( offset + 8 ) !== 0x0000 ) {
                Base.log('Invalid Exif data: Missing byte alignment offset.');
                return;
            }
    
            // Check the byte alignment:
            switch ( dataView.getUint16( tiffOffset ) ) {
                case 0x4949:
                    littleEndian = true;
                    break;
    
                case 0x4D4D:
                    littleEndian = false;
                    break;
    
                default:
                    Base.log('Invalid Exif data: Invalid byte alignment marker.');
                    return;
            }
    
            // Check for the TIFF tag marker (0x002A):
            if ( dataView.getUint16( tiffOffset + 2, littleEndian ) !== 0x002A ) {
                Base.log('Invalid Exif data: Missing TIFF marker.');
                return;
            }
    
            // Retrieve the directory offset bytes, usually 0x00000008 or 8 decimal:
            dirOffset = dataView.getUint32( tiffOffset + 4, littleEndian );
            // Create the exif object to store the tags:
            data.exif = new EXIF.ExifMap();
            // Parse the tags of the main image directory and retrieve the
            // offset to the next directory, usually the thumbnail directory:
            dirOffset = EXIF.parseExifTags( dataView, tiffOffset,
                    tiffOffset + dirOffset, littleEndian, data );
    
            // 尝试读取缩略图
            // if ( dirOffset ) {
            //     thumbnailData = {exif: {}};
            //     dirOffset = EXIF.parseExifTags(
            //         dataView,
            //         tiffOffset,
            //         tiffOffset + dirOffset,
            //         littleEndian,
            //         thumbnailData
            //     );
    
            //     // Check for JPEG Thumbnail offset:
            //     if (thumbnailData.exif[0x0201]) {
            //         data.exif.Thumbnail = EXIF.getExifThumbnail(
            //             dataView,
            //             tiffOffset + thumbnailData.exif[0x0201],
            //             thumbnailData.exif[0x0202] // Thumbnail data length
            //         );
            //     }
            // }
        };
    
        ImageMeta.parsers[ 0xffe1 ].push( EXIF.parseExifData );
        return EXIF;
    });
    /**
     * 这个方式性能不行，但是可以解决android里面的toDataUrl的bug
     * android里面toDataUrl('image/jpege')得到的结果却是png.
     *
     * 所以这里没辙，只能借助这个工具
     * @fileOverview jpeg encoder
     */
    define('runtime/html5/jpegencoder',[], function( require, exports, module ) {
    
        /*
          Copyright (c) 2008, Adobe Systems Incorporated
          All rights reserved.
    
          Redistribution and use in source and binary forms, with or without
          modification, are permitted provided that the following conditions are
          met:
    
          * Redistributions of source code must retain the above copyright notice,
            this list of conditions and the following disclaimer.
    
          * Redistributions in binary form must reproduce the above copyright
            notice, this list of conditions and the following disclaimer in the
            documentation and/or other materials provided with the distribution.
    
          * Neither the name of Adobe Systems Incorporated nor the names of its
            contributors may be used to endorse or promote products derived from
            this software without specific prior written permission.
    
          THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
          IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
          THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
          PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
          CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
          EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
          PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
          PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
          LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
          NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
          SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
        */
        /*
        JPEG encoder ported to JavaScript and optimized by Andreas Ritter, www.bytestrom.eu, 11/2009
    
        Basic GUI blocking jpeg encoder
        */
    
        function JPEGEncoder(quality) {
          var self = this;
            var fround = Math.round;
            var ffloor = Math.floor;
            var YTable = new Array(64);
            var UVTable = new Array(64);
            var fdtbl_Y = new Array(64);
            var fdtbl_UV = new Array(64);
            var YDC_HT;
            var UVDC_HT;
            var YAC_HT;
            var UVAC_HT;
    
            var bitcode = new Array(65535);
            var category = new Array(65535);
            var outputfDCTQuant = new Array(64);
            var DU = new Array(64);
            var byteout = [];
            var bytenew = 0;
            var bytepos = 7;
    
            var YDU = new Array(64);
            var UDU = new Array(64);
            var VDU = new Array(64);
            var clt = new Array(256);
            var RGB_YUV_TABLE = new Array(2048);
            var currentQuality;
    
            var ZigZag = [
                     0, 1, 5, 6,14,15,27,28,
                     2, 4, 7,13,16,26,29,42,
                     3, 8,12,17,25,30,41,43,
                     9,11,18,24,31,40,44,53,
                    10,19,23,32,39,45,52,54,
                    20,22,33,38,46,51,55,60,
                    21,34,37,47,50,56,59,61,
                    35,36,48,49,57,58,62,63
                ];
    
            var std_dc_luminance_nrcodes = [0,0,1,5,1,1,1,1,1,1,0,0,0,0,0,0,0];
            var std_dc_luminance_values = [0,1,2,3,4,5,6,7,8,9,10,11];
            var std_ac_luminance_nrcodes = [0,0,2,1,3,3,2,4,3,5,5,4,4,0,0,1,0x7d];
            var std_ac_luminance_values = [
                    0x01,0x02,0x03,0x00,0x04,0x11,0x05,0x12,
                    0x21,0x31,0x41,0x06,0x13,0x51,0x61,0x07,
                    0x22,0x71,0x14,0x32,0x81,0x91,0xa1,0x08,
                    0x23,0x42,0xb1,0xc1,0x15,0x52,0xd1,0xf0,
                    0x24,0x33,0x62,0x72,0x82,0x09,0x0a,0x16,
                    0x17,0x18,0x19,0x1a,0x25,0x26,0x27,0x28,
                    0x29,0x2a,0x34,0x35,0x36,0x37,0x38,0x39,
                    0x3a,0x43,0x44,0x45,0x46,0x47,0x48,0x49,
                    0x4a,0x53,0x54,0x55,0x56,0x57,0x58,0x59,
                    0x5a,0x63,0x64,0x65,0x66,0x67,0x68,0x69,
                    0x6a,0x73,0x74,0x75,0x76,0x77,0x78,0x79,
                    0x7a,0x83,0x84,0x85,0x86,0x87,0x88,0x89,
                    0x8a,0x92,0x93,0x94,0x95,0x96,0x97,0x98,
                    0x99,0x9a,0xa2,0xa3,0xa4,0xa5,0xa6,0xa7,
                    0xa8,0xa9,0xaa,0xb2,0xb3,0xb4,0xb5,0xb6,
                    0xb7,0xb8,0xb9,0xba,0xc2,0xc3,0xc4,0xc5,
                    0xc6,0xc7,0xc8,0xc9,0xca,0xd2,0xd3,0xd4,
                    0xd5,0xd6,0xd7,0xd8,0xd9,0xda,0xe1,0xe2,
                    0xe3,0xe4,0xe5,0xe6,0xe7,0xe8,0xe9,0xea,
                    0xf1,0xf2,0xf3,0xf4,0xf5,0xf6,0xf7,0xf8,
                    0xf9,0xfa
                ];
    
            var std_dc_chrominance_nrcodes = [0,0,3,1,1,1,1,1,1,1,1,1,0,0,0,0,0];
            var std_dc_chrominance_values = [0,1,2,3,4,5,6,7,8,9,10,11];
            var std_ac_chrominance_nrcodes = [0,0,2,1,2,4,4,3,4,7,5,4,4,0,1,2,0x77];
            var std_ac_chrominance_values = [
                    0x00,0x01,0x02,0x03,0x11,0x04,0x05,0x21,
                    0x31,0x06,0x12,0x41,0x51,0x07,0x61,0x71,
                    0x13,0x22,0x32,0x81,0x08,0x14,0x42,0x91,
                    0xa1,0xb1,0xc1,0x09,0x23,0x33,0x52,0xf0,
                    0x15,0x62,0x72,0xd1,0x0a,0x16,0x24,0x34,
                    0xe1,0x25,0xf1,0x17,0x18,0x19,0x1a,0x26,
                    0x27,0x28,0x29,0x2a,0x35,0x36,0x37,0x38,
                    0x39,0x3a,0x43,0x44,0x45,0x46,0x47,0x48,
                    0x49,0x4a,0x53,0x54,0x55,0x56,0x57,0x58,
                    0x59,0x5a,0x63,0x64,0x65,0x66,0x67,0x68,
                    0x69,0x6a,0x73,0x74,0x75,0x76,0x77,0x78,
                    0x79,0x7a,0x82,0x83,0x84,0x85,0x86,0x87,
                    0x88,0x89,0x8a,0x92,0x93,0x94,0x95,0x96,
                    0x97,0x98,0x99,0x9a,0xa2,0xa3,0xa4,0xa5,
                    0xa6,0xa7,0xa8,0xa9,0xaa,0xb2,0xb3,0xb4,
                    0xb5,0xb6,0xb7,0xb8,0xb9,0xba,0xc2,0xc3,
                    0xc4,0xc5,0xc6,0xc7,0xc8,0xc9,0xca,0xd2,
                    0xd3,0xd4,0xd5,0xd6,0xd7,0xd8,0xd9,0xda,
                    0xe2,0xe3,0xe4,0xe5,0xe6,0xe7,0xe8,0xe9,
                    0xea,0xf2,0xf3,0xf4,0xf5,0xf6,0xf7,0xf8,
                    0xf9,0xfa
                ];
    
            function initQuantTables(sf){
                    var YQT = [
                        16, 11, 10, 16, 24, 40, 51, 61,
                        12, 12, 14, 19, 26, 58, 60, 55,
                        14, 13, 16, 24, 40, 57, 69, 56,
                        14, 17, 22, 29, 51, 87, 80, 62,
                        18, 22, 37, 56, 68,109,103, 77,
                        24, 35, 55, 64, 81,104,113, 92,
                        49, 64, 78, 87,103,121,120,101,
                        72, 92, 95, 98,112,100,103, 99
                    ];
    
                    for (var i = 0; i < 64; i++) {
                        var t = ffloor((YQT[i]*sf+50)/100);
                        if (t < 1) {
                            t = 1;
                        } else if (t > 255) {
                            t = 255;
                        }
                        YTable[ZigZag[i]] = t;
                    }
                    var UVQT = [
                        17, 18, 24, 47, 99, 99, 99, 99,
                        18, 21, 26, 66, 99, 99, 99, 99,
                        24, 26, 56, 99, 99, 99, 99, 99,
                        47, 66, 99, 99, 99, 99, 99, 99,
                        99, 99, 99, 99, 99, 99, 99, 99,
                        99, 99, 99, 99, 99, 99, 99, 99,
                        99, 99, 99, 99, 99, 99, 99, 99,
                        99, 99, 99, 99, 99, 99, 99, 99
                    ];
                    for (var j = 0; j < 64; j++) {
                        var u = ffloor((UVQT[j]*sf+50)/100);
                        if (u < 1) {
                            u = 1;
                        } else if (u > 255) {
                            u = 255;
                        }
                        UVTable[ZigZag[j]] = u;
                    }
                    var aasf = [
                        1.0, 1.387039845, 1.306562965, 1.175875602,
                        1.0, 0.785694958, 0.541196100, 0.275899379
                    ];
                    var k = 0;
                    for (var row = 0; row < 8; row++)
                    {
                        for (var col = 0; col < 8; col++)
                        {
                            fdtbl_Y[k]  = (1.0 / (YTable [ZigZag[k]] * aasf[row] * aasf[col] * 8.0));
                            fdtbl_UV[k] = (1.0 / (UVTable[ZigZag[k]] * aasf[row] * aasf[col] * 8.0));
                            k++;
                        }
                    }
                }
    
                function computeHuffmanTbl(nrcodes, std_table){
                    var codevalue = 0;
                    var pos_in_table = 0;
                    var HT = new Array();
                    for (var k = 1; k <= 16; k++) {
                        for (var j = 1; j <= nrcodes[k]; j++) {
                            HT[std_table[pos_in_table]] = [];
                            HT[std_table[pos_in_table]][0] = codevalue;
                            HT[std_table[pos_in_table]][1] = k;
                            pos_in_table++;
                            codevalue++;
                        }
                        codevalue*=2;
                    }
                    return HT;
                }
    
                function initHuffmanTbl()
                {
                    YDC_HT = computeHuffmanTbl(std_dc_luminance_nrcodes,std_dc_luminance_values);
                    UVDC_HT = computeHuffmanTbl(std_dc_chrominance_nrcodes,std_dc_chrominance_values);
                    YAC_HT = computeHuffmanTbl(std_ac_luminance_nrcodes,std_ac_luminance_values);
                    UVAC_HT = computeHuffmanTbl(std_ac_chrominance_nrcodes,std_ac_chrominance_values);
                }
    
                function initCategoryNumber()
                {
                    var nrlower = 1;
                    var nrupper = 2;
                    for (var cat = 1; cat <= 15; cat++) {
                        //Positive numbers
                        for (var nr = nrlower; nr<nrupper; nr++) {
                            category[32767+nr] = cat;
                            bitcode[32767+nr] = [];
                            bitcode[32767+nr][1] = cat;
                            bitcode[32767+nr][0] = nr;
                        }
                        //Negative numbers
                        for (var nrneg =-(nrupper-1); nrneg<=-nrlower; nrneg++) {
                            category[32767+nrneg] = cat;
                            bitcode[32767+nrneg] = [];
                            bitcode[32767+nrneg][1] = cat;
                            bitcode[32767+nrneg][0] = nrupper-1+nrneg;
                        }
                        nrlower <<= 1;
                        nrupper <<= 1;
                    }
                }
    
                function initRGBYUVTable() {
                    for(var i = 0; i < 256;i++) {
                        RGB_YUV_TABLE[i]            =  19595 * i;
                        RGB_YUV_TABLE[(i+ 256)>>0]  =  38470 * i;
                        RGB_YUV_TABLE[(i+ 512)>>0]  =   7471 * i + 0x8000;
                        RGB_YUV_TABLE[(i+ 768)>>0]  = -11059 * i;
                        RGB_YUV_TABLE[(i+1024)>>0]  = -21709 * i;
                        RGB_YUV_TABLE[(i+1280)>>0]  =  32768 * i + 0x807FFF;
                        RGB_YUV_TABLE[(i+1536)>>0]  = -27439 * i;
                        RGB_YUV_TABLE[(i+1792)>>0]  = - 5329 * i;
                    }
                }
    
                // IO functions
                function writeBits(bs)
                {
                    var value = bs[0];
                    var posval = bs[1]-1;
                    while ( posval >= 0 ) {
                        if (value & (1 << posval) ) {
                            bytenew |= (1 << bytepos);
                        }
                        posval--;
                        bytepos--;
                        if (bytepos < 0) {
                            if (bytenew == 0xFF) {
                                writeByte(0xFF);
                                writeByte(0);
                            }
                            else {
                                writeByte(bytenew);
                            }
                            bytepos=7;
                            bytenew=0;
                        }
                    }
                }
    
                function writeByte(value)
                {
                    byteout.push(clt[value]); // write char directly instead of converting later
                }
    
                function writeWord(value)
                {
                    writeByte((value>>8)&0xFF);
                    writeByte((value   )&0xFF);
                }
    
                // DCT & quantization core
                function fDCTQuant(data, fdtbl)
                {
                    var d0, d1, d2, d3, d4, d5, d6, d7;
                    /* Pass 1: process rows. */
                    var dataOff=0;
                    var i;
                    var I8 = 8;
                    var I64 = 64;
                    for (i=0; i<I8; ++i)
                    {
                        d0 = data[dataOff];
                        d1 = data[dataOff+1];
                        d2 = data[dataOff+2];
                        d3 = data[dataOff+3];
                        d4 = data[dataOff+4];
                        d5 = data[dataOff+5];
                        d6 = data[dataOff+6];
                        d7 = data[dataOff+7];
    
                        var tmp0 = d0 + d7;
                        var tmp7 = d0 - d7;
                        var tmp1 = d1 + d6;
                        var tmp6 = d1 - d6;
                        var tmp2 = d2 + d5;
                        var tmp5 = d2 - d5;
                        var tmp3 = d3 + d4;
                        var tmp4 = d3 - d4;
    
                        /* Even part */
                        var tmp10 = tmp0 + tmp3;    /* phase 2 */
                        var tmp13 = tmp0 - tmp3;
                        var tmp11 = tmp1 + tmp2;
                        var tmp12 = tmp1 - tmp2;
    
                        data[dataOff] = tmp10 + tmp11; /* phase 3 */
                        data[dataOff+4] = tmp10 - tmp11;
    
                        var z1 = (tmp12 + tmp13) * 0.707106781; /* c4 */
                        data[dataOff+2] = tmp13 + z1; /* phase 5 */
                        data[dataOff+6] = tmp13 - z1;
    
                        /* Odd part */
                        tmp10 = tmp4 + tmp5; /* phase 2 */
                        tmp11 = tmp5 + tmp6;
                        tmp12 = tmp6 + tmp7;
    
                        /* The rotator is modified from fig 4-8 to avoid extra negations. */
                        var z5 = (tmp10 - tmp12) * 0.382683433; /* c6 */
                        var z2 = 0.541196100 * tmp10 + z5; /* c2-c6 */
                        var z4 = 1.306562965 * tmp12 + z5; /* c2+c6 */
                        var z3 = tmp11 * 0.707106781; /* c4 */
    
                        var z11 = tmp7 + z3;    /* phase 5 */
                        var z13 = tmp7 - z3;
    
                        data[dataOff+5] = z13 + z2; /* phase 6 */
                        data[dataOff+3] = z13 - z2;
                        data[dataOff+1] = z11 + z4;
                        data[dataOff+7] = z11 - z4;
    
                        dataOff += 8; /* advance pointer to next row */
                    }
    
                    /* Pass 2: process columns. */
                    dataOff = 0;
                    for (i=0; i<I8; ++i)
                    {
                        d0 = data[dataOff];
                        d1 = data[dataOff + 8];
                        d2 = data[dataOff + 16];
                        d3 = data[dataOff + 24];
                        d4 = data[dataOff + 32];
                        d5 = data[dataOff + 40];
                        d6 = data[dataOff + 48];
                        d7 = data[dataOff + 56];
    
                        var tmp0p2 = d0 + d7;
                        var tmp7p2 = d0 - d7;
                        var tmp1p2 = d1 + d6;
                        var tmp6p2 = d1 - d6;
                        var tmp2p2 = d2 + d5;
                        var tmp5p2 = d2 - d5;
                        var tmp3p2 = d3 + d4;
                        var tmp4p2 = d3 - d4;
    
                        /* Even part */
                        var tmp10p2 = tmp0p2 + tmp3p2;  /* phase 2 */
                        var tmp13p2 = tmp0p2 - tmp3p2;
                        var tmp11p2 = tmp1p2 + tmp2p2;
                        var tmp12p2 = tmp1p2 - tmp2p2;
    
                        data[dataOff] = tmp10p2 + tmp11p2; /* phase 3 */
                        data[dataOff+32] = tmp10p2 - tmp11p2;
    
                        var z1p2 = (tmp12p2 + tmp13p2) * 0.707106781; /* c4 */
                        data[dataOff+16] = tmp13p2 + z1p2; /* phase 5 */
                        data[dataOff+48] = tmp13p2 - z1p2;
    
                        /* Odd part */
                        tmp10p2 = tmp4p2 + tmp5p2; /* phase 2 */
                        tmp11p2 = tmp5p2 + tmp6p2;
                        tmp12p2 = tmp6p2 + tmp7p2;
    
                        /* The rotator is modified from fig 4-8 to avoid extra negations. */
                        var z5p2 = (tmp10p2 - tmp12p2) * 0.382683433; /* c6 */
                        var z2p2 = 0.541196100 * tmp10p2 + z5p2; /* c2-c6 */
                        var z4p2 = 1.306562965 * tmp12p2 + z5p2; /* c2+c6 */
                        var z3p2 = tmp11p2 * 0.707106781; /* c4 */
    
                        var z11p2 = tmp7p2 + z3p2;  /* phase 5 */
                        var z13p2 = tmp7p2 - z3p2;
    
                        data[dataOff+40] = z13p2 + z2p2; /* phase 6 */
                        data[dataOff+24] = z13p2 - z2p2;
                        data[dataOff+ 8] = z11p2 + z4p2;
                        data[dataOff+56] = z11p2 - z4p2;
    
                        dataOff++; /* advance pointer to next column */
                    }
    
                    // Quantize/descale the coefficients
                    var fDCTQuant;
                    for (i=0; i<I64; ++i)
                    {
                        // Apply the quantization and scaling factor & Round to nearest integer
                        fDCTQuant = data[i]*fdtbl[i];
                        outputfDCTQuant[i] = (fDCTQuant > 0.0) ? ((fDCTQuant + 0.5)|0) : ((fDCTQuant - 0.5)|0);
                        //outputfDCTQuant[i] = fround(fDCTQuant);
    
                    }
                    return outputfDCTQuant;
                }
    
                function writeAPP0()
                {
                    writeWord(0xFFE0); // marker
                    writeWord(16); // length
                    writeByte(0x4A); // J
                    writeByte(0x46); // F
                    writeByte(0x49); // I
                    writeByte(0x46); // F
                    writeByte(0); // = "JFIF",'\0'
                    writeByte(1); // versionhi
                    writeByte(1); // versionlo
                    writeByte(0); // xyunits
                    writeWord(1); // xdensity
                    writeWord(1); // ydensity
                    writeByte(0); // thumbnwidth
                    writeByte(0); // thumbnheight
                }
    
                function writeSOF0(width, height)
                {
                    writeWord(0xFFC0); // marker
                    writeWord(17);   // length, truecolor YUV JPG
                    writeByte(8);    // precision
                    writeWord(height);
                    writeWord(width);
                    writeByte(3);    // nrofcomponents
                    writeByte(1);    // IdY
                    writeByte(0x11); // HVY
                    writeByte(0);    // QTY
                    writeByte(2);    // IdU
                    writeByte(0x11); // HVU
                    writeByte(1);    // QTU
                    writeByte(3);    // IdV
                    writeByte(0x11); // HVV
                    writeByte(1);    // QTV
                }
    
                function writeDQT()
                {
                    writeWord(0xFFDB); // marker
                    writeWord(132);    // length
                    writeByte(0);
                    for (var i=0; i<64; i++) {
                        writeByte(YTable[i]);
                    }
                    writeByte(1);
                    for (var j=0; j<64; j++) {
                        writeByte(UVTable[j]);
                    }
                }
    
                function writeDHT()
                {
                    writeWord(0xFFC4); // marker
                    writeWord(0x01A2); // length
    
                    writeByte(0); // HTYDCinfo
                    for (var i=0; i<16; i++) {
                        writeByte(std_dc_luminance_nrcodes[i+1]);
                    }
                    for (var j=0; j<=11; j++) {
                        writeByte(std_dc_luminance_values[j]);
                    }
    
                    writeByte(0x10); // HTYACinfo
                    for (var k=0; k<16; k++) {
                        writeByte(std_ac_luminance_nrcodes[k+1]);
                    }
                    for (var l=0; l<=161; l++) {
                        writeByte(std_ac_luminance_values[l]);
                    }
    
                    writeByte(1); // HTUDCinfo
                    for (var m=0; m<16; m++) {
                        writeByte(std_dc_chrominance_nrcodes[m+1]);
                    }
                    for (var n=0; n<=11; n++) {
                        writeByte(std_dc_chrominance_values[n]);
                    }
    
                    writeByte(0x11); // HTUACinfo
                    for (var o=0; o<16; o++) {
                        writeByte(std_ac_chrominance_nrcodes[o+1]);
                    }
                    for (var p=0; p<=161; p++) {
                        writeByte(std_ac_chrominance_values[p]);
                    }
                }
    
                function writeSOS()
                {
                    writeWord(0xFFDA); // marker
                    writeWord(12); // length
                    writeByte(3); // nrofcomponents
                    writeByte(1); // IdY
                    writeByte(0); // HTY
                    writeByte(2); // IdU
                    writeByte(0x11); // HTU
                    writeByte(3); // IdV
                    writeByte(0x11); // HTV
                    writeByte(0); // Ss
                    writeByte(0x3f); // Se
                    writeByte(0); // Bf
                }
    
                function processDU(CDU, fdtbl, DC, HTDC, HTAC){
                    var EOB = HTAC[0x00];
                    var M16zeroes = HTAC[0xF0];
                    var pos;
                    var I16 = 16;
                    var I63 = 63;
                    var I64 = 64;
                    var DU_DCT = fDCTQuant(CDU, fdtbl);
                    //ZigZag reorder
                    for (var j=0;j<I64;++j) {
                        DU[ZigZag[j]]=DU_DCT[j];
                    }
                    var Diff = DU[0] - DC; DC = DU[0];
                    //Encode DC
                    if (Diff==0) {
                        writeBits(HTDC[0]); // Diff might be 0
                    } else {
                        pos = 32767+Diff;
                        writeBits(HTDC[category[pos]]);
                        writeBits(bitcode[pos]);
                    }
                    //Encode ACs
                    var end0pos = 63; // was const... which is crazy
                    for (; (end0pos>0)&&(DU[end0pos]==0); end0pos--) {};
                    //end0pos = first element in reverse order !=0
                    if ( end0pos == 0) {
                        writeBits(EOB);
                        return DC;
                    }
                    var i = 1;
                    var lng;
                    while ( i <= end0pos ) {
                        var startpos = i;
                        for (; (DU[i]==0) && (i<=end0pos); ++i) {}
                        var nrzeroes = i-startpos;
                        if ( nrzeroes >= I16 ) {
                            lng = nrzeroes>>4;
                            for (var nrmarker=1; nrmarker <= lng; ++nrmarker)
                                writeBits(M16zeroes);
                            nrzeroes = nrzeroes&0xF;
                        }
                        pos = 32767+DU[i];
                        writeBits(HTAC[(nrzeroes<<4)+category[pos]]);
                        writeBits(bitcode[pos]);
                        i++;
                    }
                    if ( end0pos != I63 ) {
                        writeBits(EOB);
                    }
                    return DC;
                }
    
                function initCharLookupTable(){
                    var sfcc = String.fromCharCode;
                    for(var i=0; i < 256; i++){ ///// ACHTUNG // 255
                        clt[i] = sfcc(i);
                    }
                }
    
                this.encode = function(image,quality) // image data object
                {
                    // var time_start = new Date().getTime();
    
                    if(quality) setQuality(quality);
    
                    // Initialize bit writer
                    byteout = new Array();
                    bytenew=0;
                    bytepos=7;
    
                    // Add JPEG headers
                    writeWord(0xFFD8); // SOI
                    writeAPP0();
                    writeDQT();
                    writeSOF0(image.width,image.height);
                    writeDHT();
                    writeSOS();
    
    
                    // Encode 8x8 macroblocks
                    var DCY=0;
                    var DCU=0;
                    var DCV=0;
    
                    bytenew=0;
                    bytepos=7;
    
    
                    this.encode.displayName = "_encode_";
    
                    var imageData = image.data;
                    var width = image.width;
                    var height = image.height;
    
                    var quadWidth = width*4;
                    var tripleWidth = width*3;
    
                    var x, y = 0;
                    var r, g, b;
                    var start,p, col,row,pos;
                    while(y < height){
                        x = 0;
                        while(x < quadWidth){
                        start = quadWidth * y + x;
                        p = start;
                        col = -1;
                        row = 0;
    
                        for(pos=0; pos < 64; pos++){
                            row = pos >> 3;// /8
                            col = ( pos & 7 ) * 4; // %8
                            p = start + ( row * quadWidth ) + col;
    
                            if(y+row >= height){ // padding bottom
                                p-= (quadWidth*(y+1+row-height));
                            }
    
                            if(x+col >= quadWidth){ // padding right
                                p-= ((x+col) - quadWidth +4)
                            }
    
                            r = imageData[ p++ ];
                            g = imageData[ p++ ];
                            b = imageData[ p++ ];
    
    
                            /* // calculate YUV values dynamically
                            YDU[pos]=((( 0.29900)*r+( 0.58700)*g+( 0.11400)*b))-128; //-0x80
                            UDU[pos]=(((-0.16874)*r+(-0.33126)*g+( 0.50000)*b));
                            VDU[pos]=((( 0.50000)*r+(-0.41869)*g+(-0.08131)*b));
                            */
    
                            // use lookup table (slightly faster)
                            YDU[pos] = ((RGB_YUV_TABLE[r]             + RGB_YUV_TABLE[(g +  256)>>0] + RGB_YUV_TABLE[(b +  512)>>0]) >> 16)-128;
                            UDU[pos] = ((RGB_YUV_TABLE[(r +  768)>>0] + RGB_YUV_TABLE[(g + 1024)>>0] + RGB_YUV_TABLE[(b + 1280)>>0]) >> 16)-128;
                            VDU[pos] = ((RGB_YUV_TABLE[(r + 1280)>>0] + RGB_YUV_TABLE[(g + 1536)>>0] + RGB_YUV_TABLE[(b + 1792)>>0]) >> 16)-128;
    
                        }
    
                        DCY = processDU(YDU, fdtbl_Y, DCY, YDC_HT, YAC_HT);
                        DCU = processDU(UDU, fdtbl_UV, DCU, UVDC_HT, UVAC_HT);
                        DCV = processDU(VDU, fdtbl_UV, DCV, UVDC_HT, UVAC_HT);
                        x+=32;
                        }
                        y+=8;
                    }
    
    
                    ////////////////////////////////////////////////////////////////
    
                    // Do the bit alignment of the EOI marker
                    if ( bytepos >= 0 ) {
                        var fillbits = [];
                        fillbits[1] = bytepos+1;
                        fillbits[0] = (1<<(bytepos+1))-1;
                        writeBits(fillbits);
                    }
    
                    writeWord(0xFFD9); //EOI
    
                    var jpegDataUri = 'data:image/jpeg;base64,' + btoa(byteout.join(''));
    
                    byteout = [];
    
                    // benchmarking
                    // var duration = new Date().getTime() - time_start;
                    // console.log('Encoding time: '+ currentQuality + 'ms');
                    //
    
                    return jpegDataUri
            }
    
            function setQuality(quality){
                if (quality <= 0) {
                    quality = 1;
                }
                if (quality > 100) {
                    quality = 100;
                }
    
                if(currentQuality == quality) return // don't recalc if unchanged
    
                var sf = 0;
                if (quality < 50) {
                    sf = Math.floor(5000 / quality);
                } else {
                    sf = Math.floor(200 - quality*2);
                }
    
                initQuantTables(sf);
                currentQuality = quality;
                // console.log('Quality set to: '+quality +'%');
            }
    
            function init(){
                // var time_start = new Date().getTime();
                if(!quality) quality = 50;
                // Create tables
                initCharLookupTable()
                initHuffmanTbl();
                initCategoryNumber();
                initRGBYUVTable();
    
                setQuality(quality);
                // var duration = new Date().getTime() - time_start;
                // console.log('Initialization '+ duration + 'ms');
            }
    
            init();
    
        };
    
        JPEGEncoder.encode = function( data, quality ) {
            var encoder = new JPEGEncoder( quality );
    
            return encoder.encode( data );
        }
    
        return JPEGEncoder;
    });
    /**
     * @fileOverview Fix android canvas.toDataUrl bug.
     */
    define('runtime/html5/androidpatch',[
        'runtime/html5/util',
        'runtime/html5/jpegencoder',
        'base'
    ], function( Util, encoder, Base ) {
        var origin = Util.canvasToDataUrl,
            supportJpeg;
    
        Util.canvasToDataUrl = function( canvas, type, quality ) {
            var ctx, w, h, fragement, parts;
    
            // 非android手机直接跳过。
            if ( !Base.os.android ) {
                return origin.apply( null, arguments );
            }
    
            // 检测是否canvas支持jpeg导出，根据数据格式来判断。
            // JPEG 前两位分别是：255, 216
            if ( type === 'image/jpeg' && typeof supportJpeg === 'undefined' ) {
                fragement = origin.apply( null, arguments );
    
                parts = fragement.split(',');
    
                if ( ~parts[ 0 ].indexOf('base64') ) {
                    fragement = atob( parts[ 1 ] );
                } else {
                    fragement = decodeURIComponent( parts[ 1 ] );
                }
    
                fragement = fragement.substring( 0, 2 );
    
                supportJpeg = fragement.charCodeAt( 0 ) === 255 &&
                        fragement.charCodeAt( 1 ) === 216;
            }
    
            // 只有在android环境下才修复
            if ( type === 'image/jpeg' && !supportJpeg ) {
                w = canvas.width;
                h = canvas.height;
                ctx = canvas.getContext('2d');
    
                return encoder.encode( ctx.getImageData( 0, 0, w, h ), quality );
            }
    
            return origin.apply( null, arguments );
        };
    });
    /**
     * @fileOverview Image
     */
    define('runtime/html5/image',[
        'base',
        'runtime/html5/runtime',
        'runtime/html5/util'
    ], function( Base, Html5Runtime, Util ) {
    
        var BLANK = 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D';
    
        return Html5Runtime.register( 'Image', {
    
            // flag: 标记是否被修改过。
            modified: false,
    
            init: function() {
                var me = this,
                    img = new Image();
    
                img.onload = function() {
    
                    me._info = {
                        type: me.type,
                        width: this.width,
                        height: this.height
                    };
    
                    //debugger;
    
                    // 读取meta信息。
                    if ( !me._metas && 'image/jpeg' === me.type ) {
                        Util.parseMeta( me._blob, function( error, ret ) {
                            me._metas = ret;
                            me.owner.trigger('load');
                        });
                    } else {
                        me.owner.trigger('load');
                    }
                };
    
                img.onerror = function() {
                    me.owner.trigger('error');
                };
    
                me._img = img;
            },
    
            loadFromBlob: function( blob ) {
                var me = this,
                    img = me._img;
    
                me._blob = blob;
                me.type = blob.type;
                img.src = Util.createObjectURL( blob.getSource() );
                me.owner.once( 'load', function() {
                    Util.revokeObjectURL( img.src );
                });
            },
    
            resize: function( width, height ) {
                var canvas = this._canvas ||
                        (this._canvas = document.createElement('canvas'));
    
                this._resize( this._img, canvas, width, height );
                this._blob = null;    // 没用了，可以删掉了。
                this.modified = true;
                this.owner.trigger( 'complete', 'resize' );
            },
    
            crop: function( x, y, w, h, s ) {
                var cvs = this._canvas ||
                        (this._canvas = document.createElement('canvas')),
                    opts = this.options,
                    img = this._img,
                    iw = img.naturalWidth,
                    ih = img.naturalHeight,
                    orientation = this.getOrientation();
    
                s = s || 1;
    
                // todo 解决 orientation 的问题。
                // values that require 90 degree rotation
                // if ( ~[ 5, 6, 7, 8 ].indexOf( orientation ) ) {
    
                //     switch ( orientation ) {
                //         case 6:
                //             tmp = x;
                //             x = y;
                //             y = iw * s - tmp - w;
                //             console.log(ih * s, tmp, w)
                //             break;
                //     }
    
                //     (w ^= h, h ^= w, w ^= h);
                // }
    
                cvs.width = w;
                cvs.height = h;
    
                opts.preserveHeaders || this._rotate2Orientaion( cvs, orientation );
                this._renderImageToCanvas( cvs, img, -x, -y, iw * s, ih * s );
    
                this._blob = null;    // 没用了，可以删掉了。
                this.modified = true;
                this.owner.trigger( 'complete', 'crop' );
            },
    
            getAsBlob: function( type ) {
                var blob = this._blob,
                    opts = this.options,
                    canvas;
    
                type = type || this.type;
    
                // blob需要重新生成。
                if ( this.modified || this.type !== type ) {
                    canvas = this._canvas;
    
                    if ( type === 'image/jpeg' ) {
    
                        blob = Util.canvasToDataUrl( canvas, type, opts.quality );
    
                        if ( opts.preserveHeaders && this._metas &&
                                this._metas.imageHead ) {
    
                            blob = Util.dataURL2ArrayBuffer( blob );
                            blob = Util.updateImageHead( blob,
                                    this._metas.imageHead );
                            blob = Util.arrayBufferToBlob( blob, type );
                            return blob;
                        }
                    } else {
                        blob = Util.canvasToDataUrl( canvas, type );
                    }
    
                    blob = Util.dataURL2Blob( blob );
                }
    
                return blob;
            },
    
            getAsDataUrl: function( type ) {
                var opts = this.options;
    
                type = type || this.type;
    
                if ( type === 'image/jpeg' ) {
                    return Util.canvasToDataUrl( this._canvas, type, opts.quality );
                } else {
                    return this._canvas.toDataURL( type );
                }
            },
    
            getOrientation: function() {
                return this._metas && this._metas.exif &&
                        this._metas.exif.get('Orientation') || 1;
            },
    
            info: function( val ) {
    
                // setter
                if ( val ) {
                    this._info = val;
                    return this;
                }
    
                // getter
                return this._info;
            },
    
            meta: function( val ) {
    
                // setter
                if ( val ) {
                    this._metas = val;
                    return this;
                }
    
                // getter
                return this._metas;
            },
    
            destroy: function() {
                var canvas = this._canvas;
                this._img.onload = null;
    
                if ( canvas ) {
                    canvas.getContext('2d')
                            .clearRect( 0, 0, canvas.width, canvas.height );
                    canvas.width = canvas.height = 0;
                    this._canvas = null;
                }
    
                // 释放内存。非常重要，否则释放不了image的内存。
                this._img.src = BLANK;
                this._img = this._blob = null;
            },
    
            _resize: function( img, cvs, width, height ) {
                var opts = this.options,
                    naturalWidth = img.width,
                    naturalHeight = img.height,
                    orientation = this.getOrientation(),
                    scale, w, h, x, y;
    
                // values that require 90 degree rotation
                if ( ~[ 5, 6, 7, 8 ].indexOf( orientation ) ) {
    
                    // 交换width, height的值。
                    width ^= height;
                    height ^= width;
                    width ^= height;
                }
    
                scale = Math[ opts.crop ? 'max' : 'min' ]( width / naturalWidth,
                        height / naturalHeight );
    
                // 不允许放大。
                opts.allowMagnify || (scale = Math.min( 1, scale ));
    
                w = naturalWidth * scale;
                h = naturalHeight * scale;
    
                if ( opts.crop ) {
                    cvs.width = width;
                    cvs.height = height;
                } else {
                    cvs.width = w;
                    cvs.height = h;
                }
    
                x = (cvs.width - w) / 2;
                y = (cvs.height - h) / 2;
    
                opts.preserveHeaders || this._rotate2Orientaion( cvs, orientation );
    
                this._renderImageToCanvas( cvs, img, x, y, w, h );
            },
    
            _rotate2Orientaion: function( canvas, orientation ) {
                var width = canvas.width,
                    height = canvas.height,
                    ctx = canvas.getContext('2d');
    
                switch ( orientation ) {
                    case 5:
                    case 6:
                    case 7:
                    case 8:
                        canvas.width = height;
                        canvas.height = width;
                        break;
                }
    
                switch ( orientation ) {
                    case 2:    // horizontal flip
                        ctx.translate( width, 0 );
                        ctx.scale( -1, 1 );
                        break;
    
                    case 3:    // 180 rotate left
                        ctx.translate( width, height );
                        ctx.rotate( Math.PI );
                        break;
    
                    case 4:    // vertical flip
                        ctx.translate( 0, height );
                        ctx.scale( 1, -1 );
                        break;
    
                    case 5:    // vertical flip + 90 rotate right
                        ctx.rotate( 0.5 * Math.PI );
                        ctx.scale( 1, -1 );
                        break;
    
                    case 6:    // 90 rotate right
                        ctx.rotate( 0.5 * Math.PI );
                        ctx.translate( 0, -height );
                        break;
    
                    case 7:    // horizontal flip + 90 rotate right
                        ctx.rotate( 0.5 * Math.PI );
                        ctx.translate( width, -height );
                        ctx.scale( -1, 1 );
                        break;
    
                    case 8:    // 90 rotate left
                        ctx.rotate( -0.5 * Math.PI );
                        ctx.translate( -width, 0 );
                        break;
                }
            },
    
            // https://github.com/stomita/ios-imagefile-megapixel/
            // blob/master/src/megapix-image.js
            _renderImageToCanvas: (function() {
    
                // 如果不是ios, 不需要这么复杂！
                if ( !Base.os.ios ) {
                    return function( canvas ) {
                        var args = Base.slice( arguments, 1 ),
                            ctx = canvas.getContext('2d');
    
                        ctx.drawImage.apply( ctx, args );
                    };
                }
    
                /**
                 * Detecting vertical squash in loaded image.
                 * Fixes a bug which squash image vertically while drawing into
                 * canvas for some images.
                 */
                function detectVerticalSquash( img, iw, ih ) {
                    var canvas = document.createElement('canvas'),
                        ctx = canvas.getContext('2d'),
                        sy = 0,
                        ey = ih,
                        py = ih,
                        data, alpha, ratio;
    
    
                    canvas.width = 1;
                    canvas.height = ih;
                    ctx.drawImage( img, 0, 0 );
                    data = ctx.getImageData( 0, 0, 1, ih ).data;
    
                    // search image edge pixel position in case
                    // it is squashed vertically.
                    while ( py > sy ) {
                        alpha = data[ (py - 1) * 4 + 3 ];
    
                        if ( alpha === 0 ) {
                            ey = py;
                        } else {
                            sy = py;
                        }
    
                        py = (ey + sy) >> 1;
                    }
    
                    ratio = (py / ih);
                    return (ratio === 0) ? 1 : ratio;
                }
    
                // fix ie7 bug
                // http://stackoverflow.com/questions/11929099/
                // html5-canvas-drawimage-ratio-bug-ios
                if ( Base.os.ios >= 7 ) {
                    return function( canvas, img, x, y, w, h ) {
                        var iw = img.naturalWidth,
                            ih = img.naturalHeight,
                            vertSquashRatio = detectVerticalSquash( img, iw, ih );
    
                        return canvas.getContext('2d').drawImage( img, 0, 0,
                                iw * vertSquashRatio, ih * vertSquashRatio,
                                x, y, w, h );
                    };
                }
    
                /**
                 * Detect subsampling in loaded image.
                 * In iOS, larger images than 2M pixels may be
                 * subsampled in rendering.
                 */
                function detectSubsampling( img ) {
                    var iw = img.naturalWidth,
                        ih = img.naturalHeight,
                        canvas, ctx;
    
                    // subsampling may happen overmegapixel image
                    if ( iw * ih > 1024 * 1024 ) {
                        canvas = document.createElement('canvas');
                        canvas.width = canvas.height = 1;
                        ctx = canvas.getContext('2d');
                        ctx.drawImage( img, -iw + 1, 0 );
    
                        // subsampled image becomes half smaller in rendering size.
                        // check alpha channel value to confirm image is covering
                        // edge pixel or not. if alpha value is 0
                        // image is not covering, hence subsampled.
                        return ctx.getImageData( 0, 0, 1, 1 ).data[ 3 ] === 0;
                    } else {
                        return false;
                    }
                }
    
    
                return function( canvas, img, x, y, width, height ) {
                    var iw = img.naturalWidth,
                        ih = img.naturalHeight,
                        ctx = canvas.getContext('2d'),
                        subsampled = detectSubsampling( img ),
                        doSquash = this.type === 'image/jpeg',
                        d = 1024,
                        sy = 0,
                        dy = 0,
                        tmpCanvas, tmpCtx, vertSquashRatio, dw, dh, sx, dx;
    
                    if ( subsampled ) {
                        iw /= 2;
                        ih /= 2;
                    }
    
                    ctx.save();
                    tmpCanvas = document.createElement('canvas');
                    tmpCanvas.width = tmpCanvas.height = d;
    
                    tmpCtx = tmpCanvas.getContext('2d');
                    vertSquashRatio = doSquash ?
                            detectVerticalSquash( img, iw, ih ) : 1;
    
                    dw = Math.ceil( d * width / iw );
                    dh = Math.ceil( d * height / ih / vertSquashRatio );
    
                    while ( sy < ih ) {
                        sx = 0;
                        dx = 0;
                        while ( sx < iw ) {
                            tmpCtx.clearRect( 0, 0, d, d );
                            tmpCtx.drawImage( img, -sx, -sy );
                            ctx.drawImage( tmpCanvas, 0, 0, d, d,
                                    x + dx, y + dy, dw, dh );
                            sx += d;
                            dx += dw;
                        }
                        sy += d;
                        dy += dh;
                    }
                    ctx.restore();
                    tmpCanvas = tmpCtx = null;
                };
            })()
        });
    });
    
    /**
     * @fileOverview Transport
     * @todo 支持chunked传输，优势：
     * 可以将大文件分成小块，挨个传输，可以提高大文件成功率，当失败的时候，也只需要重传那小部分，
     * 而不需要重头再传一次。另外断点续传也需要用chunked方式。
     */
    define('runtime/html5/transport',[
        'base',
        'runtime/html5/runtime'
    ], function( Base, Html5Runtime ) {
    
        var noop = Base.noop,
            $ = Base.$;
    
        return Html5Runtime.register( 'Transport', {
            init: function() {
                this._status = 0;
                this._response = null;
            },
    
            send: function() {
                var owner = this.owner,
                    opts = this.options,
                    xhr = this._initAjax(),
                    blob = owner._blob,
                    server = opts.server,
                    formData, binary, fr;
    
                if ( opts.sendAsBinary ) {
                    server += opts.attachInfoToQuery !== false ? ((/\?/.test( server ) ? '&' : '?') +
                            $.param( owner._formData )) : '';
    
                    binary = blob.getSource();
                } else {
                    formData = new FormData();
                    $.each( owner._formData, function( k, v ) {
                        formData.append( k, v );
                    });
    
                    formData.append( opts.fileVal, blob.getSource(),
                            opts.filename || owner._formData.name || '' );
                }
    
                if ( opts.withCredentials && 'withCredentials' in xhr ) {
                    xhr.open( opts.method, server, true );
                    xhr.withCredentials = true;
                } else {
                    xhr.open( opts.method, server );
                }
    
                this._setRequestHeader( xhr, opts.headers );
    
                if ( binary ) {
                    // 强制设置成 content-type 为文件流。
                    xhr.overrideMimeType &&
                            xhr.overrideMimeType('application/octet-stream');
    
                    // android直接发送blob会导致服务端接收到的是空文件。
                    // bug详情。
                    // https://code.google.com/p/android/issues/detail?id=39882
                    // 所以先用fileReader读取出来再通过arraybuffer的方式发送。
                    if ( Base.os.android ) {
                        fr = new FileReader();
    
                        fr.onload = function() {
                            xhr.send( this.result );
                            fr = fr.onload = null;
                        };
    
                        fr.readAsArrayBuffer( binary );
                    } else {
                        xhr.send( binary );
                    }
                } else {
                    xhr.send( formData );
                }
            },
    
            getResponse: function() {
                return this._response;
            },
    
            getResponseAsJson: function() {
                return this._parseJson( this._response );
            },
    
            getResponseHeaders: function() {
                return this._headers;
            },
    
            getStatus: function() {
                return this._status;
            },
    
            abort: function() {
                var xhr = this._xhr;
    
                if ( xhr ) {
                    xhr.upload.onprogress = noop;
                    xhr.onreadystatechange = noop;
                    xhr.abort();
    
                    this._xhr = xhr = null;
                }
            },
    
            destroy: function() {
                this.abort();
            },
    
            _parseHeader: function(raw) {
                var ret = {};
    
                raw && raw.replace(/^([^\:]+):(.*)$/mg, function(_, key, value) {
                    ret[key.trim()] = value.trim();
                });
    
                return ret;
            },
    
            _initAjax: function() {
                var me = this,
                    xhr = new XMLHttpRequest(),
                    opts = this.options;
    
                if ( opts.withCredentials && !('withCredentials' in xhr) &&
                        typeof XDomainRequest !== 'undefined' ) {
                    xhr = new XDomainRequest();
                }
    
                xhr.upload.onprogress = function( e ) {
                    var percentage = 0;
    
                    if ( e.lengthComputable ) {
                        percentage = e.loaded / e.total;
                    }
    
                    return me.trigger( 'progress', percentage );
                };
    
                xhr.onreadystatechange = function() {
    
                    if ( xhr.readyState !== 4 ) {
                        return;
                    }
    
                    xhr.upload.onprogress = noop;
                    xhr.onreadystatechange = noop;
                    me._xhr = null;
                    me._status = xhr.status;
    
                    var separator = '|', // 分隔符
                         // 拼接的状态，在 widgets/upload.js 会有代码用到这个分隔符
                        status = separator + xhr.status +
                                 separator + xhr.statusText;
    
                    if ( xhr.status >= 200 && xhr.status < 300 ) {
                        me._response = xhr.responseText;
                        me._headers = me._parseHeader(xhr.getAllResponseHeaders());
                        return me.trigger('load');
                    } else if ( xhr.status >= 500 && xhr.status < 600 ) {
                        me._response = xhr.responseText;
                        me._headers = me._parseHeader(xhr.getAllResponseHeaders());
                        return me.trigger( 'error', 'server' + status );
                    }
    
    
                    return me.trigger( 'error', me._status ? 'http' + status : 'abort' );
                };
    
                me._xhr = xhr;
                return xhr;
            },
    
            _setRequestHeader: function( xhr, headers ) {
                $.each( headers, function( key, val ) {
                    xhr.setRequestHeader( key, val );
                });
            },
    
            _parseJson: function( str ) {
                var json;
    
                try {
                    json = JSON.parse( str );
                } catch ( ex ) {
                    json = {};
                }
    
                return json;
            }
        });
    });
    
    /**
     * @fileOverview  Transport flash实现
     */
    define('runtime/html5/md5',[
        'runtime/html5/runtime'
    ], function( FlashRuntime ) {
    
        /*
         * Fastest md5 implementation around (JKM md5)
         * Credits: Joseph Myers
         *
         * @see http://www.myersdaily.org/joseph/javascript/md5-text.html
         * @see http://jsperf.com/md5-shootout/7
         */
    
        /* this function is much faster,
          so if possible we use it. Some IEs
          are the only ones I know of that
          need the idiotic second function,
          generated by an if clause.  */
        var add32 = function (a, b) {
            return (a + b) & 0xFFFFFFFF;
        },
    
        cmn = function (q, a, b, x, s, t) {
            a = add32(add32(a, q), add32(x, t));
            return add32((a << s) | (a >>> (32 - s)), b);
        },
    
        ff = function (a, b, c, d, x, s, t) {
            return cmn((b & c) | ((~b) & d), a, b, x, s, t);
        },
    
        gg = function (a, b, c, d, x, s, t) {
            return cmn((b & d) | (c & (~d)), a, b, x, s, t);
        },
    
        hh = function (a, b, c, d, x, s, t) {
            return cmn(b ^ c ^ d, a, b, x, s, t);
        },
    
        ii = function (a, b, c, d, x, s, t) {
            return cmn(c ^ (b | (~d)), a, b, x, s, t);
        },
    
        md5cycle = function (x, k) {
            var a = x[0],
                b = x[1],
                c = x[2],
                d = x[3];
    
            a = ff(a, b, c, d, k[0], 7, -680876936);
            d = ff(d, a, b, c, k[1], 12, -389564586);
            c = ff(c, d, a, b, k[2], 17, 606105819);
            b = ff(b, c, d, a, k[3], 22, -1044525330);
            a = ff(a, b, c, d, k[4], 7, -176418897);
            d = ff(d, a, b, c, k[5], 12, 1200080426);
            c = ff(c, d, a, b, k[6], 17, -1473231341);
            b = ff(b, c, d, a, k[7], 22, -45705983);
            a = ff(a, b, c, d, k[8], 7, 1770035416);
            d = ff(d, a, b, c, k[9], 12, -1958414417);
            c = ff(c, d, a, b, k[10], 17, -42063);
            b = ff(b, c, d, a, k[11], 22, -1990404162);
            a = ff(a, b, c, d, k[12], 7, 1804603682);
            d = ff(d, a, b, c, k[13], 12, -40341101);
            c = ff(c, d, a, b, k[14], 17, -1502002290);
            b = ff(b, c, d, a, k[15], 22, 1236535329);
    
            a = gg(a, b, c, d, k[1], 5, -165796510);
            d = gg(d, a, b, c, k[6], 9, -1069501632);
            c = gg(c, d, a, b, k[11], 14, 643717713);
            b = gg(b, c, d, a, k[0], 20, -373897302);
            a = gg(a, b, c, d, k[5], 5, -701558691);
            d = gg(d, a, b, c, k[10], 9, 38016083);
            c = gg(c, d, a, b, k[15], 14, -660478335);
            b = gg(b, c, d, a, k[4], 20, -405537848);
            a = gg(a, b, c, d, k[9], 5, 568446438);
            d = gg(d, a, b, c, k[14], 9, -1019803690);
            c = gg(c, d, a, b, k[3], 14, -187363961);
            b = gg(b, c, d, a, k[8], 20, 1163531501);
            a = gg(a, b, c, d, k[13], 5, -1444681467);
            d = gg(d, a, b, c, k[2], 9, -51403784);
            c = gg(c, d, a, b, k[7], 14, 1735328473);
            b = gg(b, c, d, a, k[12], 20, -1926607734);
    
            a = hh(a, b, c, d, k[5], 4, -378558);
            d = hh(d, a, b, c, k[8], 11, -2022574463);
            c = hh(c, d, a, b, k[11], 16, 1839030562);
            b = hh(b, c, d, a, k[14], 23, -35309556);
            a = hh(a, b, c, d, k[1], 4, -1530992060);
            d = hh(d, a, b, c, k[4], 11, 1272893353);
            c = hh(c, d, a, b, k[7], 16, -155497632);
            b = hh(b, c, d, a, k[10], 23, -1094730640);
            a = hh(a, b, c, d, k[13], 4, 681279174);
            d = hh(d, a, b, c, k[0], 11, -358537222);
            c = hh(c, d, a, b, k[3], 16, -722521979);
            b = hh(b, c, d, a, k[6], 23, 76029189);
            a = hh(a, b, c, d, k[9], 4, -640364487);
            d = hh(d, a, b, c, k[12], 11, -421815835);
            c = hh(c, d, a, b, k[15], 16, 530742520);
            b = hh(b, c, d, a, k[2], 23, -995338651);
    
            a = ii(a, b, c, d, k[0], 6, -198630844);
            d = ii(d, a, b, c, k[7], 10, 1126891415);
            c = ii(c, d, a, b, k[14], 15, -1416354905);
            b = ii(b, c, d, a, k[5], 21, -57434055);
            a = ii(a, b, c, d, k[12], 6, 1700485571);
            d = ii(d, a, b, c, k[3], 10, -1894986606);
            c = ii(c, d, a, b, k[10], 15, -1051523);
            b = ii(b, c, d, a, k[1], 21, -2054922799);
            a = ii(a, b, c, d, k[8], 6, 1873313359);
            d = ii(d, a, b, c, k[15], 10, -30611744);
            c = ii(c, d, a, b, k[6], 15, -1560198380);
            b = ii(b, c, d, a, k[13], 21, 1309151649);
            a = ii(a, b, c, d, k[4], 6, -145523070);
            d = ii(d, a, b, c, k[11], 10, -1120210379);
            c = ii(c, d, a, b, k[2], 15, 718787259);
            b = ii(b, c, d, a, k[9], 21, -343485551);
    
            x[0] = add32(a, x[0]);
            x[1] = add32(b, x[1]);
            x[2] = add32(c, x[2]);
            x[3] = add32(d, x[3]);
        },
    
        /* there needs to be support for Unicode here,
           * unless we pretend that we can redefine the MD-5
           * algorithm for multi-byte characters (perhaps
           * by adding every four 16-bit characters and
           * shortening the sum to 32 bits). Otherwise
           * I suggest performing MD-5 as if every character
           * was two bytes--e.g., 0040 0025 = @%--but then
           * how will an ordinary MD-5 sum be matched?
           * There is no way to standardize text to something
           * like UTF-8 before transformation; speed cost is
           * utterly prohibitive. The JavaScript standard
           * itself needs to look at this: it should start
           * providing access to strings as preformed UTF-8
           * 8-bit unsigned value arrays.
           */
        md5blk = function (s) {
            var md5blks = [],
                i; /* Andy King said do it this way. */
    
            for (i = 0; i < 64; i += 4) {
                md5blks[i >> 2] = s.charCodeAt(i) + (s.charCodeAt(i + 1) << 8) + (s.charCodeAt(i + 2) << 16) + (s.charCodeAt(i + 3) << 24);
            }
            return md5blks;
        },
    
        md5blk_array = function (a) {
            var md5blks = [],
                i; /* Andy King said do it this way. */
    
            for (i = 0; i < 64; i += 4) {
                md5blks[i >> 2] = a[i] + (a[i + 1] << 8) + (a[i + 2] << 16) + (a[i + 3] << 24);
            }
            return md5blks;
        },
    
        md51 = function (s) {
            var n = s.length,
                state = [1732584193, -271733879, -1732584194, 271733878],
                i,
                length,
                tail,
                tmp,
                lo,
                hi;
    
            for (i = 64; i <= n; i += 64) {
                md5cycle(state, md5blk(s.substring(i - 64, i)));
            }
            s = s.substring(i - 64);
            length = s.length;
            tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            for (i = 0; i < length; i += 1) {
                tail[i >> 2] |= s.charCodeAt(i) << ((i % 4) << 3);
            }
            tail[i >> 2] |= 0x80 << ((i % 4) << 3);
            if (i > 55) {
                md5cycle(state, tail);
                for (i = 0; i < 16; i += 1) {
                    tail[i] = 0;
                }
            }
    
            // Beware that the final length might not fit in 32 bits so we take care of that
            tmp = n * 8;
            tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
            lo = parseInt(tmp[2], 16);
            hi = parseInt(tmp[1], 16) || 0;
    
            tail[14] = lo;
            tail[15] = hi;
    
            md5cycle(state, tail);
            return state;
        },
    
        md51_array = function (a) {
            var n = a.length,
                state = [1732584193, -271733879, -1732584194, 271733878],
                i,
                length,
                tail,
                tmp,
                lo,
                hi;
    
            for (i = 64; i <= n; i += 64) {
                md5cycle(state, md5blk_array(a.subarray(i - 64, i)));
            }
    
            // Not sure if it is a bug, however IE10 will always produce a sub array of length 1
            // containing the last element of the parent array if the sub array specified starts
            // beyond the length of the parent array - weird.
            // https://connect.microsoft.com/IE/feedback/details/771452/typed-array-subarray-issue
            a = (i - 64) < n ? a.subarray(i - 64) : new Uint8Array(0);
    
            length = a.length;
            tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            for (i = 0; i < length; i += 1) {
                tail[i >> 2] |= a[i] << ((i % 4) << 3);
            }
    
            tail[i >> 2] |= 0x80 << ((i % 4) << 3);
            if (i > 55) {
                md5cycle(state, tail);
                for (i = 0; i < 16; i += 1) {
                    tail[i] = 0;
                }
            }
    
            // Beware that the final length might not fit in 32 bits so we take care of that
            tmp = n * 8;
            tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
            lo = parseInt(tmp[2], 16);
            hi = parseInt(tmp[1], 16) || 0;
    
            tail[14] = lo;
            tail[15] = hi;
    
            md5cycle(state, tail);
    
            return state;
        },
    
        hex_chr = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f'],
    
        rhex = function (n) {
            var s = '',
                j;
            for (j = 0; j < 4; j += 1) {
                s += hex_chr[(n >> (j * 8 + 4)) & 0x0F] + hex_chr[(n >> (j * 8)) & 0x0F];
            }
            return s;
        },
    
        hex = function (x) {
            var i;
            for (i = 0; i < x.length; i += 1) {
                x[i] = rhex(x[i]);
            }
            return x.join('');
        },
    
        md5 = function (s) {
            return hex(md51(s));
        },
    
    
    
        ////////////////////////////////////////////////////////////////////////////
    
        /**
         * SparkMD5 OOP implementation.
         *
         * Use this class to perform an incremental md5, otherwise use the
         * static methods instead.
         */
        SparkMD5 = function () {
            // call reset to init the instance
            this.reset();
        };
    
    
        // In some cases the fast add32 function cannot be used..
        if (md5('hello') !== '5d41402abc4b2a76b9719d911017c592') {
            add32 = function (x, y) {
                var lsw = (x & 0xFFFF) + (y & 0xFFFF),
                    msw = (x >> 16) + (y >> 16) + (lsw >> 16);
                return (msw << 16) | (lsw & 0xFFFF);
            };
        }
    
    
        /**
         * Appends a string.
         * A conversion will be applied if an utf8 string is detected.
         *
         * @param {String} str The string to be appended
         *
         * @return {SparkMD5} The instance itself
         */
        SparkMD5.prototype.append = function (str) {
            // converts the string to utf8 bytes if necessary
            if (/[\u0080-\uFFFF]/.test(str)) {
                str = unescape(encodeURIComponent(str));
            }
    
            // then append as binary
            this.appendBinary(str);
    
            return this;
        };
    
        /**
         * Appends a binary string.
         *
         * @param {String} contents The binary string to be appended
         *
         * @return {SparkMD5} The instance itself
         */
        SparkMD5.prototype.appendBinary = function (contents) {
            this._buff += contents;
            this._length += contents.length;
    
            var length = this._buff.length,
                i;
    
            for (i = 64; i <= length; i += 64) {
                md5cycle(this._state, md5blk(this._buff.substring(i - 64, i)));
            }
    
            this._buff = this._buff.substr(i - 64);
    
            return this;
        };
    
        /**
         * Finishes the incremental computation, reseting the internal state and
         * returning the result.
         * Use the raw parameter to obtain the raw result instead of the hex one.
         *
         * @param {Boolean} raw True to get the raw result, false to get the hex result
         *
         * @return {String|Array} The result
         */
        SparkMD5.prototype.end = function (raw) {
            var buff = this._buff,
                length = buff.length,
                i,
                tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                ret;
    
            for (i = 0; i < length; i += 1) {
                tail[i >> 2] |= buff.charCodeAt(i) << ((i % 4) << 3);
            }
    
            this._finish(tail, length);
            ret = !!raw ? this._state : hex(this._state);
    
            this.reset();
    
            return ret;
        };
    
        /**
         * Finish the final calculation based on the tail.
         *
         * @param {Array}  tail   The tail (will be modified)
         * @param {Number} length The length of the remaining buffer
         */
        SparkMD5.prototype._finish = function (tail, length) {
            var i = length,
                tmp,
                lo,
                hi;
    
            tail[i >> 2] |= 0x80 << ((i % 4) << 3);
            if (i > 55) {
                md5cycle(this._state, tail);
                for (i = 0; i < 16; i += 1) {
                    tail[i] = 0;
                }
            }
    
            // Do the final computation based on the tail and length
            // Beware that the final length may not fit in 32 bits so we take care of that
            tmp = this._length * 8;
            tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
            lo = parseInt(tmp[2], 16);
            hi = parseInt(tmp[1], 16) || 0;
    
            tail[14] = lo;
            tail[15] = hi;
            md5cycle(this._state, tail);
        };
    
        /**
         * Resets the internal state of the computation.
         *
         * @return {SparkMD5} The instance itself
         */
        SparkMD5.prototype.reset = function () {
            this._buff = "";
            this._length = 0;
            this._state = [1732584193, -271733879, -1732584194, 271733878];
    
            return this;
        };
    
        /**
         * Releases memory used by the incremental buffer and other aditional
         * resources. If you plan to use the instance again, use reset instead.
         */
        SparkMD5.prototype.destroy = function () {
            delete this._state;
            delete this._buff;
            delete this._length;
        };
    
    
        /**
         * Performs the md5 hash on a string.
         * A conversion will be applied if utf8 string is detected.
         *
         * @param {String}  str The string
         * @param {Boolean} raw True to get the raw result, false to get the hex result
         *
         * @return {String|Array} The result
         */
        SparkMD5.hash = function (str, raw) {
            // converts the string to utf8 bytes if necessary
            if (/[\u0080-\uFFFF]/.test(str)) {
                str = unescape(encodeURIComponent(str));
            }
    
            var hash = md51(str);
    
            return !!raw ? hash : hex(hash);
        };
    
        /**
         * Performs the md5 hash on a binary string.
         *
         * @param {String}  content The binary string
         * @param {Boolean} raw     True to get the raw result, false to get the hex result
         *
         * @return {String|Array} The result
         */
        SparkMD5.hashBinary = function (content, raw) {
            var hash = md51(content);
    
            return !!raw ? hash : hex(hash);
        };
    
        /**
         * SparkMD5 OOP implementation for array buffers.
         *
         * Use this class to perform an incremental md5 ONLY for array buffers.
         */
        SparkMD5.ArrayBuffer = function () {
            // call reset to init the instance
            this.reset();
        };
    
        ////////////////////////////////////////////////////////////////////////////
    
        /**
         * Appends an array buffer.
         *
         * @param {ArrayBuffer} arr The array to be appended
         *
         * @return {SparkMD5.ArrayBuffer} The instance itself
         */
        SparkMD5.ArrayBuffer.prototype.append = function (arr) {
            // TODO: we could avoid the concatenation here but the algorithm would be more complex
            //       if you find yourself needing extra performance, please make a PR.
            var buff = this._concatArrayBuffer(this._buff, arr),
                length = buff.length,
                i;
    
            this._length += arr.byteLength;
    
            for (i = 64; i <= length; i += 64) {
                md5cycle(this._state, md5blk_array(buff.subarray(i - 64, i)));
            }
    
            // Avoids IE10 weirdness (documented above)
            this._buff = (i - 64) < length ? buff.subarray(i - 64) : new Uint8Array(0);
    
            return this;
        };
    
        /**
         * Finishes the incremental computation, reseting the internal state and
         * returning the result.
         * Use the raw parameter to obtain the raw result instead of the hex one.
         *
         * @param {Boolean} raw True to get the raw result, false to get the hex result
         *
         * @return {String|Array} The result
         */
        SparkMD5.ArrayBuffer.prototype.end = function (raw) {
            var buff = this._buff,
                length = buff.length,
                tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                i,
                ret;
    
            for (i = 0; i < length; i += 1) {
                tail[i >> 2] |= buff[i] << ((i % 4) << 3);
            }
    
            this._finish(tail, length);
            ret = !!raw ? this._state : hex(this._state);
    
            this.reset();
    
            return ret;
        };
    
        SparkMD5.ArrayBuffer.prototype._finish = SparkMD5.prototype._finish;
    
        /**
         * Resets the internal state of the computation.
         *
         * @return {SparkMD5.ArrayBuffer} The instance itself
         */
        SparkMD5.ArrayBuffer.prototype.reset = function () {
            this._buff = new Uint8Array(0);
            this._length = 0;
            this._state = [1732584193, -271733879, -1732584194, 271733878];
    
            return this;
        };
    
        /**
         * Releases memory used by the incremental buffer and other aditional
         * resources. If you plan to use the instance again, use reset instead.
         */
        SparkMD5.ArrayBuffer.prototype.destroy = SparkMD5.prototype.destroy;
    
        /**
         * Concats two array buffers, returning a new one.
         *
         * @param  {ArrayBuffer} first  The first array buffer
         * @param  {ArrayBuffer} second The second array buffer
         *
         * @return {ArrayBuffer} The new array buffer
         */
        SparkMD5.ArrayBuffer.prototype._concatArrayBuffer = function (first, second) {
            var firstLength = first.length,
                result = new Uint8Array(firstLength + second.byteLength);
    
            result.set(first);
            result.set(new Uint8Array(second), firstLength);
    
            return result;
        };
    
        /**
         * Performs the md5 hash on an array buffer.
         *
         * @param {ArrayBuffer} arr The array buffer
         * @param {Boolean}     raw True to get the raw result, false to get the hex result
         *
         * @return {String|Array} The result
         */
        SparkMD5.ArrayBuffer.hash = function (arr, raw) {
            var hash = md51_array(new Uint8Array(arr));
    
            return !!raw ? hash : hex(hash);
        };
        
        return FlashRuntime.register( 'Md5', {
            init: function() {
                // do nothing.
            },
    
            loadFromBlob: function( file ) {
                var blob = file.getSource(),
                    chunkSize = 2 * 1024 * 1024,
                    chunks = Math.ceil( blob.size / chunkSize ),
                    chunk = 0,
                    owner = this.owner,
                    spark = new SparkMD5.ArrayBuffer(),
                    me = this,
                    blobSlice = blob.mozSlice || blob.webkitSlice || blob.slice,
                    loadNext, fr;
    
                fr = new FileReader();
    
                loadNext = function() {
                    var start, end;
    
                    start = chunk * chunkSize;
                    end = Math.min( start + chunkSize, blob.size );
    
                    fr.onload = function( e ) {
                        spark.append( e.target.result );
                        owner.trigger( 'progress', {
                            total: file.size,
                            loaded: end
                        });
                    };
    
                    fr.onloadend = function() {
                        fr.onloadend = fr.onload = null;
    
                        if ( ++chunk < chunks ) {
                            setTimeout( loadNext, 1 );
                        } else {
                            setTimeout(function(){
                                owner.trigger('load');
                                me.result = spark.end();
                                loadNext = file = blob = spark = null;
                                owner.trigger('complete');
                            }, 50 );
                        }
                    };
    
                    fr.readAsArrayBuffer( blobSlice.call( blob, start, end ) );
                };
    
                loadNext();
            },
    
            getResult: function() {
                return this.result;
            }
        });
    });
    /**
     * @fileOverview 完全版本。
     */
    define('preset/all',[
        'base',
    
        // widgets
        'widgets/filednd',
        'widgets/filepaste',
        'widgets/filepicker',
        'widgets/image',
        'widgets/queue',
        'widgets/runtime',
        'widgets/upload',
        'widgets/validator',
        'widgets/md5',
    
        // runtimes
        // html5
        'runtime/html5/blob',
        'runtime/html5/dnd',
        'runtime/html5/filepaste',
        'runtime/html5/filepicker',
        'runtime/html5/imagemeta/exif',
        'runtime/html5/androidpatch',
        'runtime/html5/image',
        'runtime/html5/transport',
        'runtime/html5/md5',
    
        // flash
        // 'runtime/flash/filepicker',
        // 'runtime/flash/image',
        // 'runtime/flash/transport',
        // 'runtime/flash/blob',
        // 'runtime/flash/md5'
    ], function( Base ) {
        return Base;
    });
    
    /**
     * @fileOverview 日志组件，主要用来收集错误信息，可以帮助 webuploader 更好的定位问题和发展。
     *
     * 如果您不想要启用此功能，请在打包的时候去掉 log 模块。
     *
     * 或者可以在初始化的时候通过 options.disableWidgets 属性禁用。
     *
     * 如：
     * WebUploader.create({
     *     ...
     *
     *     disableWidgets: 'log',
     *
     *     ...
     * })
     */
    define('widgets/log',[
        'base',
        'uploader',
        'widgets/widget'
    ], function( Base, Uploader ) {
        var $ = Base.$,
            logUrl = ' http://static.tieba.baidu.com/tb/pms/img/st.gif??',
            product = (location.hostname || location.host || 'protected').toLowerCase(),
    
            // 只针对 baidu 内部产品用户做统计功能。
            enable = product && /baidu/i.exec(product),
            base;
    
        if (!enable) {
            return;
        }
    
        base = {
            dv: 3,
            master: 'webuploader',
            online: /test/.exec(product) ? 0 : 1,
            module: '',
            product: product,
            type: 0
        };
    
        function send(data) {
            var obj = $.extend({}, base, data),
                url = logUrl.replace(/^(.*)\?/, '$1' + $.param( obj )),
                image = new Image();
    
            image.src = url;
        }
    
        return Uploader.register({
            name: 'log',
    
            init: function() {
                var owner = this.owner,
                    count = 0,
                    size = 0;
    
                owner
                    .on('error', function(code) {
                        send({
                            type: 2,
                            c_error_code: code
                        });
                    })
                    .on('uploadError', function(file, reason) {
                        send({
                            type: 2,
                            c_error_code: 'UPLOAD_ERROR',
                            c_reason: '' + reason
                        });
                    })
                    .on('uploadComplete', function(file) {
                        count++;
                        size += file.size;
                    }).
                    on('uploadFinished', function() {
                        send({
                            c_count: count,
                            c_size: size
                        });
                        count = size = 0;
                    });
    
                send({
                    c_usage: 1
                });
            }
        });
    });
    /**
     * @fileOverview Uploader上传类
     */
    define('webuploader',[
        'preset/all',
        'widgets/log'
    ], function( preset ) {
        return preset;
    });
    return require('webuploader');
});
