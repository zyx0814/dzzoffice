(function(global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory(require('jquery')) :
		typeof define === 'function' && define.amd ? define(['jquery'], factory) :
		(global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.BootstrapTable = factory(
			global.jQuery));
})(this, (function($) {
	'use strict';

	function _interopDefaultLegacy(e) {
		return e && typeof e === 'object' && 'default' in e ? e : {
			'default': e
		};
	}

	var $__default = /*#__PURE__*/ _interopDefaultLegacy($);

	function _typeof(obj) {
		"@babel/helpers - typeof";

		return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function(obj) {
			return typeof obj;
		} : function(obj) {
			return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol
				.prototype ? "symbol" : typeof obj;
		}, _typeof(obj);
	}

	function _classCallCheck(instance, Constructor) {
		if (!(instance instanceof Constructor)) {
			throw new TypeError("Cannot call a class as a function");
		}
	}

	function _defineProperties(target, props) {
		for (var i = 0; i < props.length; i++) {
			var descriptor = props[i];
			descriptor.enumerable = descriptor.enumerable || false;
			descriptor.configurable = true;
			if ("value" in descriptor) descriptor.writable = true;
			Object.defineProperty(target, descriptor.key, descriptor);
		}
	}

	function _createClass(Constructor, protoProps, staticProps) {
		if (protoProps) _defineProperties(Constructor.prototype, protoProps);
		if (staticProps) _defineProperties(Constructor, staticProps);
		Object.defineProperty(Constructor, "prototype", {
			writable: false
		});
		return Constructor;
	}

	function _slicedToArray(arr, i) {
		return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) ||
			_nonIterableRest();
	}

	function _toConsumableArray(arr) {
		return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) ||
			_nonIterableSpread();
	}

	function _arrayWithoutHoles(arr) {
		if (Array.isArray(arr)) return _arrayLikeToArray(arr);
	}

	function _arrayWithHoles(arr) {
		if (Array.isArray(arr)) return arr;
	}

	function _iterableToArray(iter) {
		if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null)
			return Array.from(iter);
	}

	function _iterableToArrayLimit(arr, i) {
		var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr[
			"@@iterator"];

		if (_i == null) return;
		var _arr = [];
		var _n = true;
		var _d = false;

		var _s, _e;

		try {
			for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) {
				_arr.push(_s.value);

				if (i && _arr.length === i) break;
			}
		} catch (err) {
			_d = true;
			_e = err;
		} finally {
			try {
				if (!_n && _i["return"] != null) _i["return"]();
			} finally {
				if (_d) throw _e;
			}
		}

		return _arr;
	}

	function _unsupportedIterableToArray(o, minLen) {
		if (!o) return;
		if (typeof o === "string") return _arrayLikeToArray(o, minLen);
		var n = Object.prototype.toString.call(o).slice(8, -1);
		if (n === "Object" && o.constructor) n = o.constructor.name;
		if (n === "Map" || n === "Set") return Array.from(o);
		if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o,
			minLen);
	}

	function _arrayLikeToArray(arr, len) {
		if (len == null || len > arr.length) len = arr.length;

		for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];

		return arr2;
	}

	function _nonIterableSpread() {
		throw new TypeError(
			"Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."
		);
	}

	function _nonIterableRest() {
		throw new TypeError(
			"Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."
		);
	}

	function _createForOfIteratorHelper(o, allowArrayLike) {
		var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"];

		if (!it) {
			if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o
				.length === "number") {
				if (it) o = it;
				var i = 0;

				var F = function() {};

				return {
					s: F,
					n: function() {
						if (i >= o.length) return {
							done: true
						};
						return {
							done: false,
							value: o[i++]
						};
					},
					e: function(e) {
						throw e;
					},
					f: F
				};
			}

			throw new TypeError(
				"Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."
			);
		}

		var normalCompletion = true,
			didErr = false,
			err;
		return {
			s: function() {
				it = it.call(o);
			},
			n: function() {
				var step = it.next();
				normalCompletion = step.done;
				return step;
			},
			e: function(e) {
				didErr = true;
				err = e;
			},
			f: function() {
				try {
					if (!normalCompletion && it.return != null) it.return();
				} finally {
					if (didErr) throw err;
				}
			}
		};
	}

	var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ?
		window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

	function createCommonjsModule(fn, module) {
		return module = {
			exports: {}
		}, fn(module, module.exports), module.exports;
	}

	var check = function(it) {
		return it && it.Math == Math && it;
	};

	// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
	var global_1 =
		// eslint-disable-next-line es/no-global-this -- safe
		check(typeof globalThis == 'object' && globalThis) ||
		check(typeof window == 'object' && window) ||
		// eslint-disable-next-line no-restricted-globals -- safe
		check(typeof self == 'object' && self) ||
		check(typeof commonjsGlobal == 'object' && commonjsGlobal) ||
		// eslint-disable-next-line no-new-func -- fallback
		(function() {
			return this;
		})() || Function('return this')();

	var fails = function(exec) {
		try {
			return !!exec();
		} catch (error) {
			return true;
		}
	};

	// Detect IE8's incomplete defineProperty implementation
	var descriptors = !fails(function() {
		// eslint-disable-next-line es/no-object-defineproperty -- required for testing
		return Object.defineProperty({}, 1, {
			get: function() {
				return 7;
			}
		})[1] != 7;
	});

	var functionBindNative = !fails(function() {
		var test = (function() {
			/* empty */
		}).bind();
		// eslint-disable-next-line no-prototype-builtins -- safe
		return typeof test != 'function' || test.hasOwnProperty('prototype');
	});

	var call$2 = Function.prototype.call;

	var functionCall = functionBindNative ? call$2.bind(call$2) : function() {
		return call$2.apply(call$2, arguments);
	};

	var $propertyIsEnumerable$1 = {}.propertyIsEnumerable;
	// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
	var getOwnPropertyDescriptor$4 = Object.getOwnPropertyDescriptor;

	// Nashorn ~ JDK8 bug
	var NASHORN_BUG = getOwnPropertyDescriptor$4 && !$propertyIsEnumerable$1.call({
		1: 2
	}, 1);

	// `Object.prototype.propertyIsEnumerable` method implementation
	// https://tc39.es/ecma262/#sec-object.prototype.propertyisenumerable
	var f$5 = NASHORN_BUG ? function propertyIsEnumerable(V) {
		var descriptor = getOwnPropertyDescriptor$4(this, V);
		return !!descriptor && descriptor.enumerable;
	} : $propertyIsEnumerable$1;

	var objectPropertyIsEnumerable = {
		f: f$5
	};

	var createPropertyDescriptor = function(bitmap, value) {
		return {
			enumerable: !(bitmap & 1),
			configurable: !(bitmap & 2),
			writable: !(bitmap & 4),
			value: value
		};
	};

	var FunctionPrototype$2 = Function.prototype;
	var bind$1 = FunctionPrototype$2.bind;
	var call$1 = FunctionPrototype$2.call;
	var uncurryThis = functionBindNative && bind$1.bind(call$1, call$1);

	var functionUncurryThis = functionBindNative ? function(fn) {
		return fn && uncurryThis(fn);
	} : function(fn) {
		return fn && function() {
			return call$1.apply(fn, arguments);
		};
	};

	var toString$1 = functionUncurryThis({}.toString);
	var stringSlice$7 = functionUncurryThis(''.slice);

	var classofRaw = function(it) {
		return stringSlice$7(toString$1(it), 8, -1);
	};

	var Object$5 = global_1.Object;
	var split = functionUncurryThis(''.split);

	// fallback for non-array-like ES3 and non-enumerable old V8 strings
	var indexedObject = fails(function() {
		// throws an error in rhino, see https://github.com/mozilla/rhino/issues/346
		// eslint-disable-next-line no-prototype-builtins -- safe
		return !Object$5('z').propertyIsEnumerable(0);
	}) ? function(it) {
		return classofRaw(it) == 'String' ? split(it, '') : Object$5(it);
	} : Object$5;

	var TypeError$e = global_1.TypeError;

	// `RequireObjectCoercible` abstract operation
	// https://tc39.es/ecma262/#sec-requireobjectcoercible
	var requireObjectCoercible = function(it) {
		if (it == undefined) throw TypeError$e("Can't call method on " + it);
		return it;
	};

	// toObject with fallback for non-array-like ES3 strings



	var toIndexedObject = function(it) {
		return indexedObject(requireObjectCoercible(it));
	};

	// `IsCallable` abstract operation
	// https://tc39.es/ecma262/#sec-iscallable
	var isCallable = function(argument) {
		return typeof argument == 'function';
	};

	var isObject = function(it) {
		return typeof it == 'object' ? it !== null : isCallable(it);
	};

	var aFunction = function(argument) {
		return isCallable(argument) ? argument : undefined;
	};

	var getBuiltIn = function(namespace, method) {
		return arguments.length < 2 ? aFunction(global_1[namespace]) : global_1[namespace] && global_1[
			namespace][method];
	};

	var objectIsPrototypeOf = functionUncurryThis({}.isPrototypeOf);

	var engineUserAgent = getBuiltIn('navigator', 'userAgent') || '';

	var process = global_1.process;
	var Deno = global_1.Deno;
	var versions = process && process.versions || Deno && Deno.version;
	var v8 = versions && versions.v8;
	var match, version;

	if (v8) {
		match = v8.split('.');
		// in old Chrome, versions of V8 isn't V8 = Chrome / 10
		// but their correct versions are not interesting for us
		version = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);
	}

	// BrowserFS NodeJS `process` polyfill incorrectly set `.v8` to `0.0`
	// so check `userAgent` even if `.v8` exists, but 0
	if (!version && engineUserAgent) {
		match = engineUserAgent.match(/Edge\/(\d+)/);
		if (!match || match[1] >= 74) {
			match = engineUserAgent.match(/Chrome\/(\d+)/);
			if (match) version = +match[1];
		}
	}

	var engineV8Version = version;

	/* eslint-disable es/no-symbol -- required for testing */



	// eslint-disable-next-line es/no-object-getownpropertysymbols -- required for testing
	var nativeSymbol = !!Object.getOwnPropertySymbols && !fails(function() {
		var symbol = Symbol();
		// Chrome 38 Symbol has incorrect toString conversion
		// `get-own-property-symbols` polyfill symbols converted to object are not Symbol instances
		return !String(symbol) || !(Object(symbol) instanceof Symbol) ||
			// Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances
			!Symbol.sham && engineV8Version && engineV8Version < 41;
	});

	/* eslint-disable es/no-symbol -- required for testing */


	var useSymbolAsUid = nativeSymbol &&
		!Symbol.sham &&
		typeof Symbol.iterator == 'symbol';

	var Object$4 = global_1.Object;

	var isSymbol = useSymbolAsUid ? function(it) {
		return typeof it == 'symbol';
	} : function(it) {
		var $Symbol = getBuiltIn('Symbol');
		return isCallable($Symbol) && objectIsPrototypeOf($Symbol.prototype, Object$4(it));
	};

	var String$4 = global_1.String;

	var tryToString = function(argument) {
		try {
			return String$4(argument);
		} catch (error) {
			return 'Object';
		}
	};

	var TypeError$d = global_1.TypeError;

	// `Assert: IsCallable(argument) is true`
	var aCallable = function(argument) {
		if (isCallable(argument)) return argument;
		throw TypeError$d(tryToString(argument) + ' is not a function');
	};

	// `GetMethod` abstract operation
	// https://tc39.es/ecma262/#sec-getmethod
	var getMethod = function(V, P) {
		var func = V[P];
		return func == null ? undefined : aCallable(func);
	};

	var TypeError$c = global_1.TypeError;

	// `OrdinaryToPrimitive` abstract operation
	// https://tc39.es/ecma262/#sec-ordinarytoprimitive
	var ordinaryToPrimitive = function(input, pref) {
		var fn, val;
		if (pref === 'string' && isCallable(fn = input.toString) && !isObject(val = functionCall(fn,
				input))) return val;
		if (isCallable(fn = input.valueOf) && !isObject(val = functionCall(fn, input))) return val;
		if (pref !== 'string' && isCallable(fn = input.toString) && !isObject(val = functionCall(fn,
				input))) return val;
		throw TypeError$c("Can't convert object to primitive value");
	};

	// eslint-disable-next-line es/no-object-defineproperty -- safe
	var defineProperty$5 = Object.defineProperty;

	var setGlobal = function(key, value) {
		try {
			defineProperty$5(global_1, key, {
				value: value,
				configurable: true,
				writable: true
			});
		} catch (error) {
			global_1[key] = value;
		}
		return value;
	};

	var SHARED = '__core-js_shared__';
	var store$1 = global_1[SHARED] || setGlobal(SHARED, {});

	var sharedStore = store$1;

	var shared = createCommonjsModule(function(module) {
		(module.exports = function(key, value) {
			return sharedStore[key] || (sharedStore[key] = value !== undefined ? value : {});
		})('versions', []).push({
			version: '3.21.1',
			mode: 'global',
			copyright: '© 2014-2022 Denis Pushkarev (zloirock.ru)',
			license: 'https://github.com/zloirock/core-js/blob/v3.21.1/LICENSE',
			source: 'https://github.com/zloirock/core-js'
		});
	});

	var Object$3 = global_1.Object;

	// `ToObject` abstract operation
	// https://tc39.es/ecma262/#sec-toobject
	var toObject = function(argument) {
		return Object$3(requireObjectCoercible(argument));
	};

	var hasOwnProperty = functionUncurryThis({}.hasOwnProperty);

	// `HasOwnProperty` abstract operation
	// https://tc39.es/ecma262/#sec-hasownproperty
	var hasOwnProperty_1 = Object.hasOwn || function hasOwn(it, key) {
		return hasOwnProperty(toObject(it), key);
	};

	var id = 0;
	var postfix = Math.random();
	var toString = functionUncurryThis(1.0.toString);

	var uid = function(key) {
		return 'Symbol(' + (key === undefined ? '' : key) + ')_' + toString(++id + postfix, 36);
	};

	var WellKnownSymbolsStore = shared('wks');
	var Symbol$3 = global_1.Symbol;
	var symbolFor = Symbol$3 && Symbol$3['for'];
	var createWellKnownSymbol = useSymbolAsUid ? Symbol$3 : Symbol$3 && Symbol$3.withoutSetter || uid;

	var wellKnownSymbol = function(name) {
		if (!hasOwnProperty_1(WellKnownSymbolsStore, name) || !(nativeSymbol ||
				typeof WellKnownSymbolsStore[name] == 'string')) {
			var description = 'Symbol.' + name;
			if (nativeSymbol && hasOwnProperty_1(Symbol$3, name)) {
				WellKnownSymbolsStore[name] = Symbol$3[name];
			} else if (useSymbolAsUid && symbolFor) {
				WellKnownSymbolsStore[name] = symbolFor(description);
			} else {
				WellKnownSymbolsStore[name] = createWellKnownSymbol(description);
			}
		}
		return WellKnownSymbolsStore[name];
	};

	var TypeError$b = global_1.TypeError;
	var TO_PRIMITIVE = wellKnownSymbol('toPrimitive');

	// `ToPrimitive` abstract operation
	// https://tc39.es/ecma262/#sec-toprimitive
	var toPrimitive = function(input, pref) {
		if (!isObject(input) || isSymbol(input)) return input;
		var exoticToPrim = getMethod(input, TO_PRIMITIVE);
		var result;
		if (exoticToPrim) {
			if (pref === undefined) pref = 'default';
			result = functionCall(exoticToPrim, input, pref);
			if (!isObject(result) || isSymbol(result)) return result;
			throw TypeError$b("Can't convert object to primitive value");
		}
		if (pref === undefined) pref = 'number';
		return ordinaryToPrimitive(input, pref);
	};

	// `ToPropertyKey` abstract operation
	// https://tc39.es/ecma262/#sec-topropertykey
	var toPropertyKey = function(argument) {
		var key = toPrimitive(argument, 'string');
		return isSymbol(key) ? key : key + '';
	};

	var document$1 = global_1.document;
	// typeof document.createElement is 'object' in old IE
	var EXISTS$1 = isObject(document$1) && isObject(document$1.createElement);

	var documentCreateElement = function(it) {
		return EXISTS$1 ? document$1.createElement(it) : {};
	};

	// Thanks to IE8 for its funny defineProperty
	var ie8DomDefine = !descriptors && !fails(function() {
		// eslint-disable-next-line es/no-object-defineproperty -- required for testing
		return Object.defineProperty(documentCreateElement('div'), 'a', {
			get: function() {
				return 7;
			}
		}).a != 7;
	});

	// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
	var $getOwnPropertyDescriptor$1 = Object.getOwnPropertyDescriptor;

	// `Object.getOwnPropertyDescriptor` method
	// https://tc39.es/ecma262/#sec-object.getownpropertydescriptor
	var f$4 = descriptors ? $getOwnPropertyDescriptor$1 : function getOwnPropertyDescriptor(O, P) {
		O = toIndexedObject(O);
		P = toPropertyKey(P);
		if (ie8DomDefine) try {
			return $getOwnPropertyDescriptor$1(O, P);
		} catch (error) {
			/* empty */
		}
		if (hasOwnProperty_1(O, P)) return createPropertyDescriptor(!functionCall(objectPropertyIsEnumerable
			.f, O, P), O[P]);
	};

	var objectGetOwnPropertyDescriptor = {
		f: f$4
	};

	// V8 ~ Chrome 36-
	// https://bugs.chromium.org/p/v8/issues/detail?id=3334
	var v8PrototypeDefineBug = descriptors && fails(function() {
		// eslint-disable-next-line es/no-object-defineproperty -- required for testing
		return Object.defineProperty(function() {
			/* empty */
		}, 'prototype', {
			value: 42,
			writable: false
		}).prototype != 42;
	});

	var String$3 = global_1.String;
	var TypeError$a = global_1.TypeError;

	// `Assert: Type(argument) is Object`
	var anObject = function(argument) {
		if (isObject(argument)) return argument;
		throw TypeError$a(String$3(argument) + ' is not an object');
	};

	var TypeError$9 = global_1.TypeError;
	// eslint-disable-next-line es/no-object-defineproperty -- safe
	var $defineProperty = Object.defineProperty;
	// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
	var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
	var ENUMERABLE = 'enumerable';
	var CONFIGURABLE$1 = 'configurable';
	var WRITABLE = 'writable';

	// `Object.defineProperty` method
	// https://tc39.es/ecma262/#sec-object.defineproperty
	var f$3 = descriptors ? v8PrototypeDefineBug ? function defineProperty(O, P, Attributes) {
		anObject(O);
		P = toPropertyKey(P);
		anObject(Attributes);
		if (typeof O === 'function' && P === 'prototype' && 'value' in Attributes && WRITABLE in
			Attributes && !Attributes[WRITABLE]) {
			var current = $getOwnPropertyDescriptor(O, P);
			if (current && current[WRITABLE]) {
				O[P] = Attributes.value;
				Attributes = {
					configurable: CONFIGURABLE$1 in Attributes ? Attributes[CONFIGURABLE$1] : current[
						CONFIGURABLE$1],
					enumerable: ENUMERABLE in Attributes ? Attributes[ENUMERABLE] : current[ENUMERABLE],
					writable: false
				};
			}
		}
		return $defineProperty(O, P, Attributes);
	} : $defineProperty : function defineProperty(O, P, Attributes) {
		anObject(O);
		P = toPropertyKey(P);
		anObject(Attributes);
		if (ie8DomDefine) try {
			return $defineProperty(O, P, Attributes);
		} catch (error) {
			/* empty */
		}
		if ('get' in Attributes || 'set' in Attributes) throw TypeError$9('Accessors not supported');
		if ('value' in Attributes) O[P] = Attributes.value;
		return O;
	};

	var objectDefineProperty = {
		f: f$3
	};

	var createNonEnumerableProperty = descriptors ? function(object, key, value) {
		return objectDefineProperty.f(object, key, createPropertyDescriptor(1, value));
	} : function(object, key, value) {
		object[key] = value;
		return object;
	};

	var functionToString = functionUncurryThis(Function.toString);

	// this helper broken in `core-js@3.4.1-3.4.4`, so we can't use `shared` helper
	if (!isCallable(sharedStore.inspectSource)) {
		sharedStore.inspectSource = function(it) {
			return functionToString(it);
		};
	}

	var inspectSource = sharedStore.inspectSource;

	var WeakMap$1 = global_1.WeakMap;

	var nativeWeakMap = isCallable(WeakMap$1) && /native code/.test(inspectSource(WeakMap$1));

	var keys$2 = shared('keys');

	var sharedKey = function(key) {
		return keys$2[key] || (keys$2[key] = uid(key));
	};

	var hiddenKeys$1 = {};

	var OBJECT_ALREADY_INITIALIZED = 'Object already initialized';
	var TypeError$8 = global_1.TypeError;
	var WeakMap = global_1.WeakMap;
	var set, get, has;

	var enforce = function(it) {
		return has(it) ? get(it) : set(it, {});
	};

	var getterFor = function(TYPE) {
		return function(it) {
			var state;
			if (!isObject(it) || (state = get(it)).type !== TYPE) {
				throw TypeError$8('Incompatible receiver, ' + TYPE + ' required');
			}
			return state;
		};
	};

	if (nativeWeakMap || sharedStore.state) {
		var store = sharedStore.state || (sharedStore.state = new WeakMap());
		var wmget = functionUncurryThis(store.get);
		var wmhas = functionUncurryThis(store.has);
		var wmset = functionUncurryThis(store.set);
		set = function(it, metadata) {
			if (wmhas(store, it)) throw new TypeError$8(OBJECT_ALREADY_INITIALIZED);
			metadata.facade = it;
			wmset(store, it, metadata);
			return metadata;
		};
		get = function(it) {
			return wmget(store, it) || {};
		};
		has = function(it) {
			return wmhas(store, it);
		};
	} else {
		var STATE = sharedKey('state');
		hiddenKeys$1[STATE] = true;
		set = function(it, metadata) {
			if (hasOwnProperty_1(it, STATE)) throw new TypeError$8(OBJECT_ALREADY_INITIALIZED);
			metadata.facade = it;
			createNonEnumerableProperty(it, STATE, metadata);
			return metadata;
		};
		get = function(it) {
			return hasOwnProperty_1(it, STATE) ? it[STATE] : {};
		};
		has = function(it) {
			return hasOwnProperty_1(it, STATE);
		};
	}

	var internalState = {
		set: set,
		get: get,
		has: has,
		enforce: enforce,
		getterFor: getterFor
	};

	var FunctionPrototype$1 = Function.prototype;
	// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
	var getDescriptor = descriptors && Object.getOwnPropertyDescriptor;

	var EXISTS = hasOwnProperty_1(FunctionPrototype$1, 'name');
	// additional protection from minified / mangled / dropped function names
	var PROPER = EXISTS && (function something() {
		/* empty */
	}).name === 'something';
	var CONFIGURABLE = EXISTS && (!descriptors || (descriptors && getDescriptor(FunctionPrototype$1, 'name')
		.configurable));

	var functionName = {
		EXISTS: EXISTS,
		PROPER: PROPER,
		CONFIGURABLE: CONFIGURABLE
	};

	var redefine = createCommonjsModule(function(module) {
		var CONFIGURABLE_FUNCTION_NAME = functionName.CONFIGURABLE;

		var getInternalState = internalState.get;
		var enforceInternalState = internalState.enforce;
		var TEMPLATE = String(String).split('String');

		(module.exports = function(O, key, value, options) {
			var unsafe = options ? !!options.unsafe : false;
			var simple = options ? !!options.enumerable : false;
			var noTargetGet = options ? !!options.noTargetGet : false;
			var name = options && options.name !== undefined ? options.name : key;
			var state;
			if (isCallable(value)) {
				if (String(name).slice(0, 7) === 'Symbol(') {
					name = '[' + String(name).replace(/^Symbol\(([^)]*)\)/, '$1') + ']';
				}
				if (!hasOwnProperty_1(value, 'name') || (CONFIGURABLE_FUNCTION_NAME && value
						.name !== name)) {
					createNonEnumerableProperty(value, 'name', name);
				}
				state = enforceInternalState(value);
				if (!state.source) {
					state.source = TEMPLATE.join(typeof name == 'string' ? name : '');
				}
			}
			if (O === global_1) {
				if (simple) O[key] = value;
				else setGlobal(key, value);
				return;
			} else if (!unsafe) {
				delete O[key];
			} else if (!noTargetGet && O[key]) {
				simple = true;
			}
			if (simple) O[key] = value;
			else createNonEnumerableProperty(O, key, value);
			// add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
		})(Function.prototype, 'toString', function toString() {
			return isCallable(this) && getInternalState(this).source || inspectSource(this);
		});
	});

	var ceil = Math.ceil;
	var floor$2 = Math.floor;

	// `ToIntegerOrInfinity` abstract operation
	// https://tc39.es/ecma262/#sec-tointegerorinfinity
	var toIntegerOrInfinity = function(argument) {
		var number = +argument;
		// eslint-disable-next-line no-self-compare -- safe
		return number !== number || number === 0 ? 0 : (number > 0 ? floor$2 : ceil)(number);
	};

	var max$4 = Math.max;
	var min$6 = Math.min;

	// Helper for a popular repeating case of the spec:
	// Let integer be ? ToInteger(index).
	// If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).
	var toAbsoluteIndex = function(index, length) {
		var integer = toIntegerOrInfinity(index);
		return integer < 0 ? max$4(integer + length, 0) : min$6(integer, length);
	};

	var min$5 = Math.min;

	// `ToLength` abstract operation
	// https://tc39.es/ecma262/#sec-tolength
	var toLength = function(argument) {
		return argument > 0 ? min$5(toIntegerOrInfinity(argument), 0x1FFFFFFFFFFFFF) :
			0; // 2 ** 53 - 1 == 9007199254740991
	};

	// `LengthOfArrayLike` abstract operation
	// https://tc39.es/ecma262/#sec-lengthofarraylike
	var lengthOfArrayLike = function(obj) {
		return toLength(obj.length);
	};

	// `Array.prototype.{ indexOf, includes }` methods implementation
	var createMethod$4 = function(IS_INCLUDES) {
		return function($this, el, fromIndex) {
			var O = toIndexedObject($this);
			var length = lengthOfArrayLike(O);
			var index = toAbsoluteIndex(fromIndex, length);
			var value;
			// Array#includes uses SameValueZero equality algorithm
			// eslint-disable-next-line no-self-compare -- NaN check
			if (IS_INCLUDES && el != el)
				while (length > index) {
					value = O[index++];
					// eslint-disable-next-line no-self-compare -- NaN check
					if (value != value) return true;
					// Array#indexOf ignores holes, Array#includes - not
				} else
					for (; length > index; index++) {
						if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES ||
							index || 0;
					}
			return !IS_INCLUDES && -1;
		};
	};

	var arrayIncludes = {
		// `Array.prototype.includes` method
		// https://tc39.es/ecma262/#sec-array.prototype.includes
		includes: createMethod$4(true),
		// `Array.prototype.indexOf` method
		// https://tc39.es/ecma262/#sec-array.prototype.indexof
		indexOf: createMethod$4(false)
	};

	var indexOf$1 = arrayIncludes.indexOf;


	var push$5 = functionUncurryThis([].push);

	var objectKeysInternal = function(object, names) {
		var O = toIndexedObject(object);
		var i = 0;
		var result = [];
		var key;
		for (key in O) !hasOwnProperty_1(hiddenKeys$1, key) && hasOwnProperty_1(O, key) && push$5(result,
			key);
		// Don't enum bug & hidden keys
		while (names.length > i)
			if (hasOwnProperty_1(O, key = names[i++])) {
				~indexOf$1(result, key) || push$5(result, key);
			}
		return result;
	};

	// IE8- don't enum bug keys
	var enumBugKeys = [
		'constructor',
		'hasOwnProperty',
		'isPrototypeOf',
		'propertyIsEnumerable',
		'toLocaleString',
		'toString',
		'valueOf'
	];

	var hiddenKeys = enumBugKeys.concat('length', 'prototype');

	// `Object.getOwnPropertyNames` method
	// https://tc39.es/ecma262/#sec-object.getownpropertynames
	// eslint-disable-next-line es/no-object-getownpropertynames -- safe
	var f$2 = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
		return objectKeysInternal(O, hiddenKeys);
	};

	var objectGetOwnPropertyNames = {
		f: f$2
	};

	// eslint-disable-next-line es/no-object-getownpropertysymbols -- safe
	var f$1 = Object.getOwnPropertySymbols;

	var objectGetOwnPropertySymbols = {
		f: f$1
	};

	var concat$2 = functionUncurryThis([].concat);

	// all object keys, includes non-enumerable and symbols
	var ownKeys = getBuiltIn('Reflect', 'ownKeys') || function ownKeys(it) {
		var keys = objectGetOwnPropertyNames.f(anObject(it));
		var getOwnPropertySymbols = objectGetOwnPropertySymbols.f;
		return getOwnPropertySymbols ? concat$2(keys, getOwnPropertySymbols(it)) : keys;
	};

	var copyConstructorProperties = function(target, source, exceptions) {
		var keys = ownKeys(source);
		var defineProperty = objectDefineProperty.f;
		var getOwnPropertyDescriptor = objectGetOwnPropertyDescriptor.f;
		for (var i = 0; i < keys.length; i++) {
			var key = keys[i];
			if (!hasOwnProperty_1(target, key) && !(exceptions && hasOwnProperty_1(exceptions, key))) {
				defineProperty(target, key, getOwnPropertyDescriptor(source, key));
			}
		}
	};

	var replacement = /#|\.prototype\./;

	var isForced = function(feature, detection) {
		var value = data[normalize(feature)];
		return value == POLYFILL ? true :
			value == NATIVE ? false :
			isCallable(detection) ? fails(detection) :
			!!detection;
	};

	var normalize = isForced.normalize = function(string) {
		return String(string).replace(replacement, '.').toLowerCase();
	};

	var data = isForced.data = {};
	var NATIVE = isForced.NATIVE = 'N';
	var POLYFILL = isForced.POLYFILL = 'P';

	var isForced_1 = isForced;

	var getOwnPropertyDescriptor$3 = objectGetOwnPropertyDescriptor.f;






	/*
	  options.target      - name of the target object
	  options.global      - target is the global object
	  options.stat        - export as static methods of target
	  options.proto       - export as prototype methods of target
	  options.real        - real prototype method for the `pure` version
	  options.forced      - export even if the native feature is available
	  options.bind        - bind methods to the target, required for the `pure` version
	  options.wrap        - wrap constructors to preventing global pollution, required for the `pure` version
	  options.unsafe      - use the simple assignment of property instead of delete + defineProperty
	  options.sham        - add a flag to not completely full polyfills
	  options.enumerable  - export as enumerable property
	  options.noTargetGet - prevent calling a getter on target
	  options.name        - the .name of the function if it does not match the key
	*/
	var _export = function(options, source) {
		var TARGET = options.target;
		var GLOBAL = options.global;
		var STATIC = options.stat;
		var FORCED, target, key, targetProperty, sourceProperty, descriptor;
		if (GLOBAL) {
			target = global_1;
		} else if (STATIC) {
			target = global_1[TARGET] || setGlobal(TARGET, {});
		} else {
			target = (global_1[TARGET] || {}).prototype;
		}
		if (target)
			for (key in source) {
				sourceProperty = source[key];
				if (options.noTargetGet) {
					descriptor = getOwnPropertyDescriptor$3(target, key);
					targetProperty = descriptor && descriptor.value;
				} else targetProperty = target[key];
				FORCED = isForced_1(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced);
				// contained in target
				if (!FORCED && targetProperty !== undefined) {
					if (typeof sourceProperty == typeof targetProperty) continue;
					copyConstructorProperties(sourceProperty, targetProperty);
				}
				// add a flag to not completely full polyfills
				if (options.sham || (targetProperty && targetProperty.sham)) {
					createNonEnumerableProperty(sourceProperty, 'sham', true);
				}
				// extend global
				redefine(target, key, sourceProperty, options);
			}
	};

	// `Object.keys` method
	// https://tc39.es/ecma262/#sec-object.keys
	// eslint-disable-next-line es/no-object-keys -- safe
	var objectKeys = Object.keys || function keys(O) {
		return objectKeysInternal(O, enumBugKeys);
	};

	// eslint-disable-next-line es/no-object-assign -- safe
	var $assign = Object.assign;
	// eslint-disable-next-line es/no-object-defineproperty -- required for testing
	var defineProperty$4 = Object.defineProperty;
	var concat$1 = functionUncurryThis([].concat);

	// `Object.assign` method
	// https://tc39.es/ecma262/#sec-object.assign
	var objectAssign = !$assign || fails(function() {
		// should have correct order of operations (Edge bug)
		if (descriptors && $assign({
				b: 1
			}, $assign(defineProperty$4({}, 'a', {
				enumerable: true,
				get: function() {
					defineProperty$4(this, 'b', {
						value: 3,
						enumerable: false
					});
				}
			}), {
				b: 2
			})).b !== 1) return true;
		// should work with symbols and should have deterministic property order (V8 bug)
		var A = {};
		var B = {};
		// eslint-disable-next-line es/no-symbol -- safe
		var symbol = Symbol();
		var alphabet = 'abcdefghijklmnopqrst';
		A[symbol] = 7;
		alphabet.split('').forEach(function(chr) {
			B[chr] = chr;
		});
		return $assign({}, A)[symbol] != 7 || objectKeys($assign({}, B)).join('') != alphabet;
	}) ? function assign(target, source) { // eslint-disable-line no-unused-vars -- required for `.length`
		var T = toObject(target);
		var argumentsLength = arguments.length;
		var index = 1;
		var getOwnPropertySymbols = objectGetOwnPropertySymbols.f;
		var propertyIsEnumerable = objectPropertyIsEnumerable.f;
		while (argumentsLength > index) {
			var S = indexedObject(arguments[index++]);
			var keys = getOwnPropertySymbols ? concat$1(objectKeys(S), getOwnPropertySymbols(S)) :
				objectKeys(S);
			var length = keys.length;
			var j = 0;
			var key;
			while (length > j) {
				key = keys[j++];
				if (!descriptors || functionCall(propertyIsEnumerable, S, key)) T[key] = S[key];
			}
		}
		return T;
	} : $assign;

	// `Object.assign` method
	// https://tc39.es/ecma262/#sec-object.assign
	// eslint-disable-next-line es/no-object-assign -- required for testing
	_export({
		target: 'Object',
		stat: true,
		forced: Object.assign !== objectAssign
	}, {
		assign: objectAssign
	});

	var TO_STRING_TAG$3 = wellKnownSymbol('toStringTag');
	var test$2 = {};

	test$2[TO_STRING_TAG$3] = 'z';

	var toStringTagSupport = String(test$2) === '[object z]';

	var TO_STRING_TAG$2 = wellKnownSymbol('toStringTag');
	var Object$2 = global_1.Object;

	// ES3 wrong here
	var CORRECT_ARGUMENTS = classofRaw(function() {
		return arguments;
	}()) == 'Arguments';

	// fallback for IE11 Script Access Denied error
	var tryGet = function(it, key) {
		try {
			return it[key];
		} catch (error) {
			/* empty */
		}
	};

	// getting tag from ES6+ `Object.prototype.toString`
	var classof = toStringTagSupport ? classofRaw : function(it) {
		var O, tag, result;
		return it === undefined ? 'Undefined' : it === null ? 'Null'
			// @@toStringTag case
			:
			typeof(tag = tryGet(O = Object$2(it), TO_STRING_TAG$2)) == 'string' ? tag
			// builtinTag case
			:
			CORRECT_ARGUMENTS ? classofRaw(O)
			// ES3 arguments fallback
			:
			(result = classofRaw(O)) == 'Object' && isCallable(O.callee) ? 'Arguments' : result;
	};

	var String$2 = global_1.String;

	var toString_1 = function(argument) {
		if (classof(argument) === 'Symbol') throw TypeError('Cannot convert a Symbol value to a string');
		return String$2(argument);
	};

	// a string of all valid unicode whitespaces
	var whitespaces = '\u0009\u000A\u000B\u000C\u000D\u0020\u00A0\u1680\u2000\u2001\u2002' +
		'\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200A\u202F\u205F\u3000\u2028\u2029\uFEFF';

	var replace$3 = functionUncurryThis(''.replace);
	var whitespace = '[' + whitespaces + ']';
	var ltrim = RegExp('^' + whitespace + whitespace + '*');
	var rtrim = RegExp(whitespace + whitespace + '*$');

	// `String.prototype.{ trim, trimStart, trimEnd, trimLeft, trimRight }` methods implementation
	var createMethod$3 = function(TYPE) {
		return function($this) {
			var string = toString_1(requireObjectCoercible($this));
			if (TYPE & 1) string = replace$3(string, ltrim, '');
			if (TYPE & 2) string = replace$3(string, rtrim, '');
			return string;
		};
	};

	var stringTrim = {
		// `String.prototype.{ trimLeft, trimStart }` methods
		// https://tc39.es/ecma262/#sec-string.prototype.trimstart
		start: createMethod$3(1),
		// `String.prototype.{ trimRight, trimEnd }` methods
		// https://tc39.es/ecma262/#sec-string.prototype.trimend
		end: createMethod$3(2),
		// `String.prototype.trim` method
		// https://tc39.es/ecma262/#sec-string.prototype.trim
		trim: createMethod$3(3)
	};

	var PROPER_FUNCTION_NAME$2 = functionName.PROPER;



	var non = '\u200B\u0085\u180E';

	// check that a method works with the correct list
	// of whitespaces and has a correct name
	var stringTrimForced = function(METHOD_NAME) {
		return fails(function() {
			return !!whitespaces[METHOD_NAME]() ||
				non[METHOD_NAME]() !== non ||
				(PROPER_FUNCTION_NAME$2 && whitespaces[METHOD_NAME].name !== METHOD_NAME);
		});
	};

	var $trim = stringTrim.trim;


	// `String.prototype.trim` method
	// https://tc39.es/ecma262/#sec-string.prototype.trim
	_export({
		target: 'String',
		proto: true,
		forced: stringTrimForced('trim')
	}, {
		trim: function trim() {
			return $trim(this);
		}
	});

	var arrayMethodIsStrict = function(METHOD_NAME, argument) {
		var method = [][METHOD_NAME];
		return !!method && fails(function() {
			// eslint-disable-next-line no-useless-call -- required for testing
			method.call(null, argument || function() {
				return 1;
			}, 1);
		});
	};

	var un$Join = functionUncurryThis([].join);

	var ES3_STRINGS = indexedObject != Object;
	var STRICT_METHOD$3 = arrayMethodIsStrict('join', ',');

	// `Array.prototype.join` method
	// https://tc39.es/ecma262/#sec-array.prototype.join
	_export({
		target: 'Array',
		proto: true,
		forced: ES3_STRINGS || !STRICT_METHOD$3
	}, {
		join: function join(separator) {
			return un$Join(toIndexedObject(this), separator === undefined ? ',' : separator);
		}
	});

	// `RegExp.prototype.flags` getter implementation
	// https://tc39.es/ecma262/#sec-get-regexp.prototype.flags
	var regexpFlags = function() {
		var that = anObject(this);
		var result = '';
		if (that.global) result += 'g';
		if (that.ignoreCase) result += 'i';
		if (that.multiline) result += 'm';
		if (that.dotAll) result += 's';
		if (that.unicode) result += 'u';
		if (that.sticky) result += 'y';
		return result;
	};

	// babel-minify and Closure Compiler transpiles RegExp('a', 'y') -> /a/y and it causes SyntaxError
	var $RegExp$2 = global_1.RegExp;

	var UNSUPPORTED_Y$3 = fails(function() {
		var re = $RegExp$2('a', 'y');
		re.lastIndex = 2;
		return re.exec('abcd') != null;
	});

	// UC Browser bug
	// https://github.com/zloirock/core-js/issues/1008
	var MISSED_STICKY$1 = UNSUPPORTED_Y$3 || fails(function() {
		return !$RegExp$2('a', 'y').sticky;
	});

	var BROKEN_CARET = UNSUPPORTED_Y$3 || fails(function() {
		// https://bugzilla.mozilla.org/show_bug.cgi?id=773687
		var re = $RegExp$2('^r', 'gy');
		re.lastIndex = 2;
		return re.exec('str') != null;
	});

	var regexpStickyHelpers = {
		BROKEN_CARET: BROKEN_CARET,
		MISSED_STICKY: MISSED_STICKY$1,
		UNSUPPORTED_Y: UNSUPPORTED_Y$3
	};

	// `Object.defineProperties` method
	// https://tc39.es/ecma262/#sec-object.defineproperties
	// eslint-disable-next-line es/no-object-defineproperties -- safe
	var f = descriptors && !v8PrototypeDefineBug ? Object.defineProperties : function defineProperties(O,
		Properties) {
		anObject(O);
		var props = toIndexedObject(Properties);
		var keys = objectKeys(Properties);
		var length = keys.length;
		var index = 0;
		var key;
		while (length > index) objectDefineProperty.f(O, key = keys[index++], props[key]);
		return O;
	};

	var objectDefineProperties = {
		f: f
	};

	var html = getBuiltIn('document', 'documentElement');

	/* global ActiveXObject -- old IE, WSH */








	var GT = '>';
	var LT = '<';
	var PROTOTYPE = 'prototype';
	var SCRIPT = 'script';
	var IE_PROTO$1 = sharedKey('IE_PROTO');

	var EmptyConstructor = function() {
		/* empty */
	};

	var scriptTag = function(content) {
		return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;
	};

	// Create object with fake `null` prototype: use ActiveX Object with cleared prototype
	var NullProtoObjectViaActiveX = function(activeXDocument) {
		activeXDocument.write(scriptTag(''));
		activeXDocument.close();
		var temp = activeXDocument.parentWindow.Object;
		activeXDocument = null; // avoid memory leak
		return temp;
	};

	// Create object with fake `null` prototype: use iframe Object with cleared prototype
	var NullProtoObjectViaIFrame = function() {
		// Thrash, waste and sodomy: IE GC bug
		var iframe = documentCreateElement('iframe');
		var JS = 'java' + SCRIPT + ':';
		var iframeDocument;
		iframe.style.display = 'none';
		html.appendChild(iframe);
		// https://github.com/zloirock/core-js/issues/475
		iframe.src = String(JS);
		iframeDocument = iframe.contentWindow.document;
		iframeDocument.open();
		iframeDocument.write(scriptTag('document.F=Object'));
		iframeDocument.close();
		return iframeDocument.F;
	};

	// Check for document.domain and active x support
	// No need to use active x approach when document.domain is not set
	// see https://github.com/es-shims/es5-shim/issues/150
	// variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346
	// avoid IE GC bug
	var activeXDocument;
	var NullProtoObject = function() {
		try {
			activeXDocument = new ActiveXObject('htmlfile');
		} catch (error) {
			/* ignore */
		}
		NullProtoObject = typeof document != 'undefined' ?
			document.domain && activeXDocument ?
			NullProtoObjectViaActiveX(activeXDocument) // old IE
			:
			NullProtoObjectViaIFrame() :
			NullProtoObjectViaActiveX(activeXDocument); // WSH
		var length = enumBugKeys.length;
		while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys[length]];
		return NullProtoObject();
	};

	hiddenKeys$1[IE_PROTO$1] = true;

	// `Object.create` method
	// https://tc39.es/ecma262/#sec-object.create
	var objectCreate = Object.create || function create(O, Properties) {
		var result;
		if (O !== null) {
			EmptyConstructor[PROTOTYPE] = anObject(O);
			result = new EmptyConstructor();
			EmptyConstructor[PROTOTYPE] = null;
			// add "__proto__" for Object.getPrototypeOf polyfill
			result[IE_PROTO$1] = O;
		} else result = NullProtoObject();
		return Properties === undefined ? result : objectDefineProperties.f(result, Properties);
	};

	// babel-minify and Closure Compiler transpiles RegExp('.', 's') -> /./s and it causes SyntaxError
	var $RegExp$1 = global_1.RegExp;

	var regexpUnsupportedDotAll = fails(function() {
		var re = $RegExp$1('.', 's');
		return !(re.dotAll && re.exec('\n') && re.flags === 's');
	});

	// babel-minify and Closure Compiler transpiles RegExp('(?<a>b)', 'g') -> /(?<a>b)/g and it causes SyntaxError
	var $RegExp = global_1.RegExp;

	var regexpUnsupportedNcg = fails(function() {
		var re = $RegExp('(?<a>b)', 'g');
		return re.exec('b').groups.a !== 'b' ||
			'b'.replace(re, '$<a>c') !== 'bc';
	});

	/* eslint-disable regexp/no-empty-capturing-group, regexp/no-empty-group, regexp/no-lazy-ends -- testing */
	/* eslint-disable regexp/no-useless-quantifier -- testing */







	var getInternalState$1 = internalState.get;



	var nativeReplace = shared('native-string-replace', String.prototype.replace);
	var nativeExec = RegExp.prototype.exec;
	var patchedExec = nativeExec;
	var charAt$5 = functionUncurryThis(''.charAt);
	var indexOf = functionUncurryThis(''.indexOf);
	var replace$2 = functionUncurryThis(''.replace);
	var stringSlice$6 = functionUncurryThis(''.slice);

	var UPDATES_LAST_INDEX_WRONG = (function() {
		var re1 = /a/;
		var re2 = /b*/g;
		functionCall(nativeExec, re1, 'a');
		functionCall(nativeExec, re2, 'a');
		return re1.lastIndex !== 0 || re2.lastIndex !== 0;
	})();

	var UNSUPPORTED_Y$2 = regexpStickyHelpers.BROKEN_CARET;

	// nonparticipating capturing group, copied from es5-shim's String#split patch.
	var NPCG_INCLUDED = /()??/.exec('')[1] !== undefined;

	var PATCH = UPDATES_LAST_INDEX_WRONG || NPCG_INCLUDED || UNSUPPORTED_Y$2 || regexpUnsupportedDotAll ||
		regexpUnsupportedNcg;

	if (PATCH) {
		patchedExec = function exec(string) {
			var re = this;
			var state = getInternalState$1(re);
			var str = toString_1(string);
			var raw = state.raw;
			var result, reCopy, lastIndex, match, i, object, group;

			if (raw) {
				raw.lastIndex = re.lastIndex;
				result = functionCall(patchedExec, raw, str);
				re.lastIndex = raw.lastIndex;
				return result;
			}

			var groups = state.groups;
			var sticky = UNSUPPORTED_Y$2 && re.sticky;
			var flags = functionCall(regexpFlags, re);
			var source = re.source;
			var charsAdded = 0;
			var strCopy = str;

			if (sticky) {
				flags = replace$2(flags, 'y', '');
				if (indexOf(flags, 'g') === -1) {
					flags += 'g';
				}

				strCopy = stringSlice$6(str, re.lastIndex);
				// Support anchored sticky behavior.
				if (re.lastIndex > 0 && (!re.multiline || re.multiline && charAt$5(str, re.lastIndex -
						1) !== '\n')) {
					source = '(?: ' + source + ')';
					strCopy = ' ' + strCopy;
					charsAdded++;
				}
				// ^(? + rx + ) is needed, in combination with some str slicing, to
				// simulate the 'y' flag.
				reCopy = new RegExp('^(?:' + source + ')', flags);
			}

			if (NPCG_INCLUDED) {
				reCopy = new RegExp('^' + source + '$(?!\\s)', flags);
			}
			if (UPDATES_LAST_INDEX_WRONG) lastIndex = re.lastIndex;

			match = functionCall(nativeExec, sticky ? reCopy : re, strCopy);

			if (sticky) {
				if (match) {
					match.input = stringSlice$6(match.input, charsAdded);
					match[0] = stringSlice$6(match[0], charsAdded);
					match.index = re.lastIndex;
					re.lastIndex += match[0].length;
				} else re.lastIndex = 0;
			} else if (UPDATES_LAST_INDEX_WRONG && match) {
				re.lastIndex = re.global ? match.index + match[0].length : lastIndex;
			}
			if (NPCG_INCLUDED && match && match.length > 1) {
				// Fix browsers whose `exec` methods don't consistently return `undefined`
				// for NPCG, like IE8. NOTE: This doesn' work for /(.?)?/
				functionCall(nativeReplace, match[0], reCopy, function() {
					for (i = 1; i < arguments.length - 2; i++) {
						if (arguments[i] === undefined) match[i] = undefined;
					}
				});
			}

			if (match && groups) {
				match.groups = object = objectCreate(null);
				for (i = 0; i < groups.length; i++) {
					group = groups[i];
					object[group[0]] = match[group[1]];
				}
			}

			return match;
		};
	}

	var regexpExec = patchedExec;

	// `RegExp.prototype.exec` method
	// https://tc39.es/ecma262/#sec-regexp.prototype.exec
	_export({
		target: 'RegExp',
		proto: true,
		forced: /./.exec !== regexpExec
	}, {
		exec: regexpExec
	});

	var FunctionPrototype = Function.prototype;
	var apply = FunctionPrototype.apply;
	var call = FunctionPrototype.call;

	// eslint-disable-next-line es/no-reflect -- safe
	var functionApply = typeof Reflect == 'object' && Reflect.apply || (functionBindNative ? call.bind(apply) :
		function() {
			return call.apply(apply, arguments);
		});

	// TODO: Remove from `core-js@4` since it's moved to entry points








	var SPECIES$5 = wellKnownSymbol('species');
	var RegExpPrototype$2 = RegExp.prototype;

	var fixRegexpWellKnownSymbolLogic = function(KEY, exec, FORCED, SHAM) {
		var SYMBOL = wellKnownSymbol(KEY);

		var DELEGATES_TO_SYMBOL = !fails(function() {
			// String methods call symbol-named RegEp methods
			var O = {};
			O[SYMBOL] = function() {
				return 7;
			};
			return '' [KEY](O) != 7;
		});

		var DELEGATES_TO_EXEC = DELEGATES_TO_SYMBOL && !fails(function() {
			// Symbol-named RegExp methods call .exec
			var execCalled = false;
			var re = /a/;

			if (KEY === 'split') {
				// We can't use real regex here since it causes deoptimization
				// and serious performance degradation in V8
				// https://github.com/zloirock/core-js/issues/306
				re = {};
				// RegExp[@@split] doesn't call the regex's exec method, but first creates
				// a new one. We need to return the patched regex when creating the new one.
				re.constructor = {};
				re.constructor[SPECIES$5] = function() {
					return re;
				};
				re.flags = '';
				re[SYMBOL] = /./ [SYMBOL];
			}

			re.exec = function() {
				execCalled = true;
				return null;
			};

			re[SYMBOL]('');
			return !execCalled;
		});

		if (
			!DELEGATES_TO_SYMBOL ||
			!DELEGATES_TO_EXEC ||
			FORCED
		) {
			var uncurriedNativeRegExpMethod = functionUncurryThis(/./ [SYMBOL]);
			var methods = exec(SYMBOL, '' [KEY], function(nativeMethod, regexp, str, arg2,
				forceStringMethod) {
				var uncurriedNativeMethod = functionUncurryThis(nativeMethod);
				var $exec = regexp.exec;
				if ($exec === regexpExec || $exec === RegExpPrototype$2.exec) {
					if (DELEGATES_TO_SYMBOL && !forceStringMethod) {
						// The native String method already delegates to @@method (this
						// polyfilled function), leasing to infinite recursion.
						// We avoid it by directly calling the native @@method method.
						return {
							done: true,
							value: uncurriedNativeRegExpMethod(regexp, str, arg2)
						};
					}
					return {
						done: true,
						value: uncurriedNativeMethod(str, regexp, arg2)
					};
				}
				return {
					done: false
				};
			});

			redefine(String.prototype, KEY, methods[0]);
			redefine(RegExpPrototype$2, SYMBOL, methods[1]);
		}

		if (SHAM) createNonEnumerableProperty(RegExpPrototype$2[SYMBOL], 'sham', true);
	};

	var MATCH$2 = wellKnownSymbol('match');

	// `IsRegExp` abstract operation
	// https://tc39.es/ecma262/#sec-isregexp
	var isRegexp = function(it) {
		var isRegExp;
		return isObject(it) && ((isRegExp = it[MATCH$2]) !== undefined ? !!isRegExp : classofRaw(it) ==
			'RegExp');
	};

	var noop = function() {
		/* empty */
	};
	var empty = [];
	var construct = getBuiltIn('Reflect', 'construct');
	var constructorRegExp = /^\s*(?:class|function)\b/;
	var exec$3 = functionUncurryThis(constructorRegExp.exec);
	var INCORRECT_TO_STRING = !constructorRegExp.exec(noop);

	var isConstructorModern = function isConstructor(argument) {
		if (!isCallable(argument)) return false;
		try {
			construct(noop, empty, argument);
			return true;
		} catch (error) {
			return false;
		}
	};

	var isConstructorLegacy = function isConstructor(argument) {
		if (!isCallable(argument)) return false;
		switch (classof(argument)) {
			case 'AsyncFunction':
			case 'GeneratorFunction':
			case 'AsyncGeneratorFunction':
				return false;
		}
		try {
			// we can't check .prototype since constructors produced by .bind haven't it
			// `Function#toString` throws on some built-it function in some legacy engines
			// (for example, `DOMQuad` and similar in FF41-)
			return INCORRECT_TO_STRING || !!exec$3(constructorRegExp, inspectSource(argument));
		} catch (error) {
			return true;
		}
	};

	isConstructorLegacy.sham = true;

	// `IsConstructor` abstract operation
	// https://tc39.es/ecma262/#sec-isconstructor
	var isConstructor = !construct || fails(function() {
		var called;
		return isConstructorModern(isConstructorModern.call) ||
			!isConstructorModern(Object) ||
			!isConstructorModern(function() {
				called = true;
			}) ||
			called;
	}) ? isConstructorLegacy : isConstructorModern;

	var TypeError$7 = global_1.TypeError;

	// `Assert: IsConstructor(argument) is true`
	var aConstructor = function(argument) {
		if (isConstructor(argument)) return argument;
		throw TypeError$7(tryToString(argument) + ' is not a constructor');
	};

	var SPECIES$4 = wellKnownSymbol('species');

	// `SpeciesConstructor` abstract operation
	// https://tc39.es/ecma262/#sec-speciesconstructor
	var speciesConstructor = function(O, defaultConstructor) {
		var C = anObject(O).constructor;
		var S;
		return C === undefined || (S = anObject(C)[SPECIES$4]) == undefined ? defaultConstructor :
			aConstructor(S);
	};

	var charAt$4 = functionUncurryThis(''.charAt);
	var charCodeAt$1 = functionUncurryThis(''.charCodeAt);
	var stringSlice$5 = functionUncurryThis(''.slice);

	var createMethod$2 = function(CONVERT_TO_STRING) {
		return function($this, pos) {
			var S = toString_1(requireObjectCoercible($this));
			var position = toIntegerOrInfinity(pos);
			var size = S.length;
			var first, second;
			if (position < 0 || position >= size) return CONVERT_TO_STRING ? '' : undefined;
			first = charCodeAt$1(S, position);
			return first < 0xD800 || first > 0xDBFF || position + 1 === size ||
				(second = charCodeAt$1(S, position + 1)) < 0xDC00 || second > 0xDFFF ?
				CONVERT_TO_STRING ?
				charAt$4(S, position) :
				first :
				CONVERT_TO_STRING ?
				stringSlice$5(S, position, position + 2) :
				(first - 0xD800 << 10) + (second - 0xDC00) + 0x10000;
		};
	};

	var stringMultibyte = {
		// `String.prototype.codePointAt` method
		// https://tc39.es/ecma262/#sec-string.prototype.codepointat
		codeAt: createMethod$2(false),
		// `String.prototype.at` method
		// https://github.com/mathiasbynens/String.prototype.at
		charAt: createMethod$2(true)
	};

	var charAt$3 = stringMultibyte.charAt;

	// `AdvanceStringIndex` abstract operation
	// https://tc39.es/ecma262/#sec-advancestringindex
	var advanceStringIndex = function(S, index, unicode) {
		return index + (unicode ? charAt$3(S, index).length : 1);
	};

	var createProperty = function(object, key, value) {
		var propertyKey = toPropertyKey(key);
		if (propertyKey in object) objectDefineProperty.f(object, propertyKey, createPropertyDescriptor(0,
			value));
		else object[propertyKey] = value;
	};

	var Array$3 = global_1.Array;
	var max$3 = Math.max;

	var arraySliceSimple = function(O, start, end) {
		var length = lengthOfArrayLike(O);
		var k = toAbsoluteIndex(start, length);
		var fin = toAbsoluteIndex(end === undefined ? length : end, length);
		var result = Array$3(max$3(fin - k, 0));
		for (var n = 0; k < fin; k++, n++) createProperty(result, n, O[k]);
		result.length = n;
		return result;
	};

	var TypeError$6 = global_1.TypeError;

	// `RegExpExec` abstract operation
	// https://tc39.es/ecma262/#sec-regexpexec
	var regexpExecAbstract = function(R, S) {
		var exec = R.exec;
		if (isCallable(exec)) {
			var result = functionCall(exec, R, S);
			if (result !== null) anObject(result);
			return result;
		}
		if (classofRaw(R) === 'RegExp') return functionCall(regexpExec, R, S);
		throw TypeError$6('RegExp#exec called on incompatible receiver');
	};

	var UNSUPPORTED_Y$1 = regexpStickyHelpers.UNSUPPORTED_Y;
	var MAX_UINT32 = 0xFFFFFFFF;
	var min$4 = Math.min;
	var $push = [].push;
	var exec$2 = functionUncurryThis(/./.exec);
	var push$4 = functionUncurryThis($push);
	var stringSlice$4 = functionUncurryThis(''.slice);

	// Chrome 51 has a buggy "split" implementation when RegExp#exec !== nativeExec
	// Weex JS has frozen built-in prototypes, so use try / catch wrapper
	var SPLIT_WORKS_WITH_OVERWRITTEN_EXEC = !fails(function() {
		// eslint-disable-next-line regexp/no-empty-group -- required for testing
		var re = /(?:)/;
		var originalExec = re.exec;
		re.exec = function() {
			return originalExec.apply(this, arguments);
		};
		var result = 'ab'.split(re);
		return result.length !== 2 || result[0] !== 'a' || result[1] !== 'b';
	});

	// @@split logic
	fixRegexpWellKnownSymbolLogic('split', function(SPLIT, nativeSplit, maybeCallNative) {
		var internalSplit;
		if (
			'abbc'.split(/(b)*/)[1] == 'c' ||
			// eslint-disable-next-line regexp/no-empty-group -- required for testing
			'test'.split(/(?:)/, -1).length != 4 ||
			'ab'.split(/(?:ab)*/).length != 2 ||
			'.'.split(/(.?)(.?)/).length != 4 ||
			// eslint-disable-next-line regexp/no-empty-capturing-group, regexp/no-empty-group -- required for testing
			'.'.split(/()()/).length > 1 ||
			''.split(/.?/).length
		) {
			// based on es5-shim implementation, need to rework it
			internalSplit = function(separator, limit) {
				var string = toString_1(requireObjectCoercible(this));
				var lim = limit === undefined ? MAX_UINT32 : limit >>> 0;
				if (lim === 0) return [];
				if (separator === undefined) return [string];
				// If `separator` is not a regex, use native split
				if (!isRegexp(separator)) {
					return functionCall(nativeSplit, string, separator, lim);
				}
				var output = [];
				var flags = (separator.ignoreCase ? 'i' : '') +
					(separator.multiline ? 'm' : '') +
					(separator.unicode ? 'u' : '') +
					(separator.sticky ? 'y' : '');
				var lastLastIndex = 0;
				// Make `global` and avoid `lastIndex` issues by working with a copy
				var separatorCopy = new RegExp(separator.source, flags + 'g');
				var match, lastIndex, lastLength;
				while (match = functionCall(regexpExec, separatorCopy, string)) {
					lastIndex = separatorCopy.lastIndex;
					if (lastIndex > lastLastIndex) {
						push$4(output, stringSlice$4(string, lastLastIndex, match.index));
						if (match.length > 1 && match.index < string.length) functionApply($push,
							output, arraySliceSimple(match, 1));
						lastLength = match[0].length;
						lastLastIndex = lastIndex;
						if (output.length >= lim) break;
					}
					if (separatorCopy.lastIndex === match.index) separatorCopy
						.lastIndex++; // Avoid an infinite loop
				}
				if (lastLastIndex === string.length) {
					if (lastLength || !exec$2(separatorCopy, '')) push$4(output, '');
				} else push$4(output, stringSlice$4(string, lastLastIndex));
				return output.length > lim ? arraySliceSimple(output, 0, lim) : output;
			};
			// Chakra, V8
		} else if ('0'.split(undefined, 0).length) {
			internalSplit = function(separator, limit) {
				return separator === undefined && limit === 0 ? [] : functionCall(nativeSplit, this,
					separator, limit);
			};
		} else internalSplit = nativeSplit;

		return [
			// `String.prototype.split` method
			// https://tc39.es/ecma262/#sec-string.prototype.split
			function split(separator, limit) {
				var O = requireObjectCoercible(this);
				var splitter = separator == undefined ? undefined : getMethod(separator, SPLIT);
				return splitter ?
					functionCall(splitter, separator, O, limit) :
					functionCall(internalSplit, toString_1(O), separator, limit);
			},
			// `RegExp.prototype[@@split]` method
			// https://tc39.es/ecma262/#sec-regexp.prototype-@@split
			//
			// NOTE: This cannot be properly polyfilled in engines that don't support
			// the 'y' flag.
			function(string, limit) {
				var rx = anObject(this);
				var S = toString_1(string);
				var res = maybeCallNative(internalSplit, rx, S, limit, internalSplit !==
					nativeSplit);

				if (res.done) return res.value;

				var C = speciesConstructor(rx, RegExp);

				var unicodeMatching = rx.unicode;
				var flags = (rx.ignoreCase ? 'i' : '') +
					(rx.multiline ? 'm' : '') +
					(rx.unicode ? 'u' : '') +
					(UNSUPPORTED_Y$1 ? 'g' : 'y');

				// ^(? + rx + ) is needed, in combination with some S slicing, to
				// simulate the 'y' flag.
				var splitter = new C(UNSUPPORTED_Y$1 ? '^(?:' + rx.source + ')' : rx, flags);
				var lim = limit === undefined ? MAX_UINT32 : limit >>> 0;
				if (lim === 0) return [];
				if (S.length === 0) return regexpExecAbstract(splitter, S) === null ? [S] : [];
				var p = 0;
				var q = 0;
				var A = [];
				while (q < S.length) {
					splitter.lastIndex = UNSUPPORTED_Y$1 ? 0 : q;
					var z = regexpExecAbstract(splitter, UNSUPPORTED_Y$1 ? stringSlice$4(S, q) : S);
					var e;
					if (
						z === null ||
						(e = min$4(toLength(splitter.lastIndex + (UNSUPPORTED_Y$1 ? q : 0)), S
							.length)) === p
					) {
						q = advanceStringIndex(S, q, unicodeMatching);
					} else {
						push$4(A, stringSlice$4(S, p, q));
						if (A.length === lim) return A;
						for (var i = 1; i <= z.length - 1; i++) {
							push$4(A, z[i]);
							if (A.length === lim) return A;
						}
						q = p = e;
					}
				}
				push$4(A, stringSlice$4(S, p));
				return A;
			}
		];
	}, !SPLIT_WORKS_WITH_OVERWRITTEN_EXEC, UNSUPPORTED_Y$1);

	var $propertyIsEnumerable = objectPropertyIsEnumerable.f;

	var propertyIsEnumerable = functionUncurryThis($propertyIsEnumerable);
	var push$3 = functionUncurryThis([].push);

	// `Object.{ entries, values }` methods implementation
	var createMethod$1 = function(TO_ENTRIES) {
		return function(it) {
			var O = toIndexedObject(it);
			var keys = objectKeys(O);
			var length = keys.length;
			var i = 0;
			var result = [];
			var key;
			while (length > i) {
				key = keys[i++];
				if (!descriptors || propertyIsEnumerable(O, key)) {
					push$3(result, TO_ENTRIES ? [key, O[key]] : O[key]);
				}
			}
			return result;
		};
	};

	var objectToArray = {
		// `Object.entries` method
		// https://tc39.es/ecma262/#sec-object.entries
		entries: createMethod$1(true),
		// `Object.values` method
		// https://tc39.es/ecma262/#sec-object.values
		values: createMethod$1(false)
	};

	var $entries = objectToArray.entries;

	// `Object.entries` method
	// https://tc39.es/ecma262/#sec-object.entries
	_export({
		target: 'Object',
		stat: true
	}, {
		entries: function entries(O) {
			return $entries(O);
		}
	});

	var UNSCOPABLES = wellKnownSymbol('unscopables');
	var ArrayPrototype = Array.prototype;

	// Array.prototype[@@unscopables]
	// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
	if (ArrayPrototype[UNSCOPABLES] == undefined) {
		objectDefineProperty.f(ArrayPrototype, UNSCOPABLES, {
			configurable: true,
			value: objectCreate(null)
		});
	}

	// add a key to Array.prototype[@@unscopables]
	var addToUnscopables = function(key) {
		ArrayPrototype[UNSCOPABLES][key] = true;
	};

	var $includes = arrayIncludes.includes;


	// `Array.prototype.includes` method
	// https://tc39.es/ecma262/#sec-array.prototype.includes
	_export({
		target: 'Array',
		proto: true
	}, {
		includes: function includes(el /* , fromIndex = 0 */ ) {
			return $includes(this, el, arguments.length > 1 ? arguments[1] : undefined);
		}
	});

	// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
	addToUnscopables('includes');

	// `IsArray` abstract operation
	// https://tc39.es/ecma262/#sec-isarray
	// eslint-disable-next-line es/no-array-isarray -- safe
	var isArray = Array.isArray || function isArray(argument) {
		return classofRaw(argument) == 'Array';
	};

	var SPECIES$3 = wellKnownSymbol('species');
	var Array$2 = global_1.Array;

	// a part of `ArraySpeciesCreate` abstract operation
	// https://tc39.es/ecma262/#sec-arrayspeciescreate
	var arraySpeciesConstructor = function(originalArray) {
		var C;
		if (isArray(originalArray)) {
			C = originalArray.constructor;
			// cross-realm fallback
			if (isConstructor(C) && (C === Array$2 || isArray(C.prototype))) C = undefined;
			else if (isObject(C)) {
				C = C[SPECIES$3];
				if (C === null) C = undefined;
			}
		}
		return C === undefined ? Array$2 : C;
	};

	// `ArraySpeciesCreate` abstract operation
	// https://tc39.es/ecma262/#sec-arrayspeciescreate
	var arraySpeciesCreate = function(originalArray, length) {
		return new(arraySpeciesConstructor(originalArray))(length === 0 ? 0 : length);
	};

	var SPECIES$2 = wellKnownSymbol('species');

	var arrayMethodHasSpeciesSupport = function(METHOD_NAME) {
		// We can't use this feature detection in V8 since it causes
		// deoptimization and serious performance degradation
		// https://github.com/zloirock/core-js/issues/677
		return engineV8Version >= 51 || !fails(function() {
			var array = [];
			var constructor = array.constructor = {};
			constructor[SPECIES$2] = function() {
				return {
					foo: 1
				};
			};
			return array[METHOD_NAME](Boolean).foo !== 1;
		});
	};

	var IS_CONCAT_SPREADABLE = wellKnownSymbol('isConcatSpreadable');
	var MAX_SAFE_INTEGER$1 = 0x1FFFFFFFFFFFFF;
	var MAXIMUM_ALLOWED_INDEX_EXCEEDED = 'Maximum allowed index exceeded';
	var TypeError$5 = global_1.TypeError;

	// We can't use this feature detection in V8 since it causes
	// deoptimization and serious performance degradation
	// https://github.com/zloirock/core-js/issues/679
	var IS_CONCAT_SPREADABLE_SUPPORT = engineV8Version >= 51 || !fails(function() {
		var array = [];
		array[IS_CONCAT_SPREADABLE] = false;
		return array.concat()[0] !== array;
	});

	var SPECIES_SUPPORT = arrayMethodHasSpeciesSupport('concat');

	var isConcatSpreadable = function(O) {
		if (!isObject(O)) return false;
		var spreadable = O[IS_CONCAT_SPREADABLE];
		return spreadable !== undefined ? !!spreadable : isArray(O);
	};

	var FORCED$3 = !IS_CONCAT_SPREADABLE_SUPPORT || !SPECIES_SUPPORT;

	// `Array.prototype.concat` method
	// https://tc39.es/ecma262/#sec-array.prototype.concat
	// with adding support of @@isConcatSpreadable and @@species
	_export({
		target: 'Array',
		proto: true,
		forced: FORCED$3
	}, {
		// eslint-disable-next-line no-unused-vars -- required for `.length`
		concat: function concat(arg) {
			var O = toObject(this);
			var A = arraySpeciesCreate(O, 0);
			var n = 0;
			var i, k, length, len, E;
			for (i = -1, length = arguments.length; i < length; i++) {
				E = i === -1 ? O : arguments[i];
				if (isConcatSpreadable(E)) {
					len = lengthOfArrayLike(E);
					if (n + len > MAX_SAFE_INTEGER$1) throw TypeError$5(
						MAXIMUM_ALLOWED_INDEX_EXCEEDED);
					for (k = 0; k < len; k++, n++)
						if (k in E) createProperty(A, n, E[k]);
				} else {
					if (n >= MAX_SAFE_INTEGER$1) throw TypeError$5(MAXIMUM_ALLOWED_INDEX_EXCEEDED);
					createProperty(A, n++, E);
				}
			}
			A.length = n;
			return A;
		}
	});

	var bind = functionUncurryThis(functionUncurryThis.bind);

	// optional / simple context binding
	var functionBindContext = function(fn, that) {
		aCallable(fn);
		return that === undefined ? fn : functionBindNative ? bind(fn, that) : function( /* ...args */ ) {
			return fn.apply(that, arguments);
		};
	};

	var push$2 = functionUncurryThis([].push);

	// `Array.prototype.{ forEach, map, filter, some, every, find, findIndex, filterReject }` methods implementation
	var createMethod = function(TYPE) {
		var IS_MAP = TYPE == 1;
		var IS_FILTER = TYPE == 2;
		var IS_SOME = TYPE == 3;
		var IS_EVERY = TYPE == 4;
		var IS_FIND_INDEX = TYPE == 6;
		var IS_FILTER_REJECT = TYPE == 7;
		var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
		return function($this, callbackfn, that, specificCreate) {
			var O = toObject($this);
			var self = indexedObject(O);
			var boundFunction = functionBindContext(callbackfn, that);
			var length = lengthOfArrayLike(self);
			var index = 0;
			var create = specificCreate || arraySpeciesCreate;
			var target = IS_MAP ? create($this, length) : IS_FILTER || IS_FILTER_REJECT ? create($this,
				0) : undefined;
			var value, result;
			for (; length > index; index++)
				if (NO_HOLES || index in self) {
					value = self[index];
					result = boundFunction(value, index, O);
					if (TYPE) {
						if (IS_MAP) target[index] = result; // map
						else if (result) switch (TYPE) {
							case 3:
								return true; // some
							case 5:
								return value; // find
							case 6:
								return index; // findIndex
							case 2:
								push$2(target, value); // filter
						} else switch (TYPE) {
							case 4:
								return false; // every
							case 7:
								push$2(target, value); // filterReject
						}
					}
				}
			return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : target;
		};
	};

	var arrayIteration = {
		// `Array.prototype.forEach` method
		// https://tc39.es/ecma262/#sec-array.prototype.foreach
		forEach: createMethod(0),
		// `Array.prototype.map` method
		// https://tc39.es/ecma262/#sec-array.prototype.map
		map: createMethod(1),
		// `Array.prototype.filter` method
		// https://tc39.es/ecma262/#sec-array.prototype.filter
		filter: createMethod(2),
		// `Array.prototype.some` method
		// https://tc39.es/ecma262/#sec-array.prototype.some
		some: createMethod(3),
		// `Array.prototype.every` method
		// https://tc39.es/ecma262/#sec-array.prototype.every
		every: createMethod(4),
		// `Array.prototype.find` method
		// https://tc39.es/ecma262/#sec-array.prototype.find
		find: createMethod(5),
		// `Array.prototype.findIndex` method
		// https://tc39.es/ecma262/#sec-array.prototype.findIndex
		findIndex: createMethod(6),
		// `Array.prototype.filterReject` method
		// https://github.com/tc39/proposal-array-filtering
		filterReject: createMethod(7)
	};

	var $find = arrayIteration.find;


	var FIND = 'find';
	var SKIPS_HOLES$1 = true;

	// Shouldn't skip holes
	if (FIND in []) Array(1)[FIND](function() {
		SKIPS_HOLES$1 = false;
	});

	// `Array.prototype.find` method
	// https://tc39.es/ecma262/#sec-array.prototype.find
	_export({
		target: 'Array',
		proto: true,
		forced: SKIPS_HOLES$1
	}, {
		find: function find(callbackfn /* , that = undefined */ ) {
			return $find(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
		}
	});

	// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
	addToUnscopables(FIND);

	// `Object.prototype.toString` method implementation
	// https://tc39.es/ecma262/#sec-object.prototype.tostring
	var objectToString = toStringTagSupport ? {}.toString : function toString() {
		return '[object ' + classof(this) + ']';
	};

	// `Object.prototype.toString` method
	// https://tc39.es/ecma262/#sec-object.prototype.tostring
	if (!toStringTagSupport) {
		redefine(Object.prototype, 'toString', objectToString, {
			unsafe: true
		});
	}

	var TypeError$4 = global_1.TypeError;

	var notARegexp = function(it) {
		if (isRegexp(it)) {
			throw TypeError$4("The method doesn't accept regular expressions");
		}
		return it;
	};

	var MATCH$1 = wellKnownSymbol('match');

	var correctIsRegexpLogic = function(METHOD_NAME) {
		var regexp = /./;
		try {
			'/./' [METHOD_NAME](regexp);
		} catch (error1) {
			try {
				regexp[MATCH$1] = false;
				return '/./' [METHOD_NAME](regexp);
			} catch (error2) {
				/* empty */
			}
		}
		return false;
	};

	var stringIndexOf$2 = functionUncurryThis(''.indexOf);

	// `String.prototype.includes` method
	// https://tc39.es/ecma262/#sec-string.prototype.includes
	_export({
		target: 'String',
		proto: true,
		forced: !correctIsRegexpLogic('includes')
	}, {
		includes: function includes(searchString /* , position = 0 */ ) {
			return !!~stringIndexOf$2(
				toString_1(requireObjectCoercible(this)),
				toString_1(notARegexp(searchString)),
				arguments.length > 1 ? arguments[1] : undefined
			);
		}
	});

	// iterable DOM collections
	// flag - `iterable` interface - 'entries', 'keys', 'values', 'forEach' methods
	var domIterables = {
		CSSRuleList: 0,
		CSSStyleDeclaration: 0,
		CSSValueList: 0,
		ClientRectList: 0,
		DOMRectList: 0,
		DOMStringList: 0,
		DOMTokenList: 1,
		DataTransferItemList: 0,
		FileList: 0,
		HTMLAllCollection: 0,
		HTMLCollection: 0,
		HTMLFormElement: 0,
		HTMLSelectElement: 0,
		MediaList: 0,
		MimeTypeArray: 0,
		NamedNodeMap: 0,
		NodeList: 1,
		PaintRequestList: 0,
		Plugin: 0,
		PluginArray: 0,
		SVGLengthList: 0,
		SVGNumberList: 0,
		SVGPathSegList: 0,
		SVGPointList: 0,
		SVGStringList: 0,
		SVGTransformList: 0,
		SourceBufferList: 0,
		StyleSheetList: 0,
		TextTrackCueList: 0,
		TextTrackList: 0,
		TouchList: 0
	};

	// in old WebKit versions, `element.classList` is not an instance of global `DOMTokenList`


	var classList = documentCreateElement('span').classList;
	var DOMTokenListPrototype = classList && classList.constructor && classList.constructor.prototype;

	var domTokenListPrototype = DOMTokenListPrototype === Object.prototype ? undefined : DOMTokenListPrototype;

	var $forEach = arrayIteration.forEach;


	var STRICT_METHOD$2 = arrayMethodIsStrict('forEach');

	// `Array.prototype.forEach` method implementation
	// https://tc39.es/ecma262/#sec-array.prototype.foreach
	var arrayForEach = !STRICT_METHOD$2 ? function forEach(callbackfn /* , thisArg */ ) {
		return $forEach(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
		// eslint-disable-next-line es/no-array-prototype-foreach -- safe
	} : [].forEach;

	var handlePrototype$1 = function(CollectionPrototype) {
		// some Chrome versions have non-configurable methods on DOMTokenList
		if (CollectionPrototype && CollectionPrototype.forEach !== arrayForEach) try {
			createNonEnumerableProperty(CollectionPrototype, 'forEach', arrayForEach);
		} catch (error) {
			CollectionPrototype.forEach = arrayForEach;
		}
	};

	for (var COLLECTION_NAME$1 in domIterables) {
		if (domIterables[COLLECTION_NAME$1]) {
			handlePrototype$1(global_1[COLLECTION_NAME$1] && global_1[COLLECTION_NAME$1].prototype);
		}
	}

	handlePrototype$1(domTokenListPrototype);

	var trim$2 = stringTrim.trim;


	var charAt$2 = functionUncurryThis(''.charAt);
	var n$ParseFloat = global_1.parseFloat;
	var Symbol$2 = global_1.Symbol;
	var ITERATOR$4 = Symbol$2 && Symbol$2.iterator;
	var FORCED$2 = 1 / n$ParseFloat(whitespaces + '-0') !== -Infinity
		// MS Edge 18- broken with boxed symbols
		||
		(ITERATOR$4 && !fails(function() {
			n$ParseFloat(Object(ITERATOR$4));
		}));

	// `parseFloat` method
	// https://tc39.es/ecma262/#sec-parsefloat-string
	var numberParseFloat = FORCED$2 ? function parseFloat(string) {
		var trimmedString = trim$2(toString_1(string));
		var result = n$ParseFloat(trimmedString);
		return result === 0 && charAt$2(trimmedString, 0) == '-' ? -0 : result;
	} : n$ParseFloat;

	// `parseFloat` method
	// https://tc39.es/ecma262/#sec-parsefloat-string
	_export({
		global: true,
		forced: parseFloat != numberParseFloat
	}, {
		parseFloat: numberParseFloat
	});

	/* eslint-disable es/no-array-prototype-indexof -- required for testing */


	var $IndexOf = arrayIncludes.indexOf;


	var un$IndexOf = functionUncurryThis([].indexOf);

	var NEGATIVE_ZERO = !!un$IndexOf && 1 / un$IndexOf([1], 1, -0) < 0;
	var STRICT_METHOD$1 = arrayMethodIsStrict('indexOf');

	// `Array.prototype.indexOf` method
	// https://tc39.es/ecma262/#sec-array.prototype.indexof
	_export({
		target: 'Array',
		proto: true,
		forced: NEGATIVE_ZERO || !STRICT_METHOD$1
	}, {
		indexOf: function indexOf(searchElement /* , fromIndex = 0 */ ) {
			var fromIndex = arguments.length > 1 ? arguments[1] : undefined;
			return NEGATIVE_ZERO
				// convert -0 to +0
				?
				un$IndexOf(this, searchElement, fromIndex) || 0 :
				$IndexOf(this, searchElement, fromIndex);
		}
	});

	var floor$1 = Math.floor;

	var mergeSort = function(array, comparefn) {
		var length = array.length;
		var middle = floor$1(length / 2);
		return length < 8 ? insertionSort(array, comparefn) : merge(
			array,
			mergeSort(arraySliceSimple(array, 0, middle), comparefn),
			mergeSort(arraySliceSimple(array, middle), comparefn),
			comparefn
		);
	};

	var insertionSort = function(array, comparefn) {
		var length = array.length;
		var i = 1;
		var element, j;

		while (i < length) {
			j = i;
			element = array[i];
			while (j && comparefn(array[j - 1], element) > 0) {
				array[j] = array[--j];
			}
			if (j !== i++) array[j] = element;
		}
		return array;
	};

	var merge = function(array, left, right, comparefn) {
		var llength = left.length;
		var rlength = right.length;
		var lindex = 0;
		var rindex = 0;

		while (lindex < llength || rindex < rlength) {
			array[lindex + rindex] = (lindex < llength && rindex < rlength) ?
				comparefn(left[lindex], right[rindex]) <= 0 ? left[lindex++] : right[rindex++] :
				lindex < llength ? left[lindex++] : right[rindex++];
		}
		return array;
	};

	var arraySort = mergeSort;

	var firefox = engineUserAgent.match(/firefox\/(\d+)/i);

	var engineFfVersion = !!firefox && +firefox[1];

	var engineIsIeOrEdge = /MSIE|Trident/.test(engineUserAgent);

	var webkit = engineUserAgent.match(/AppleWebKit\/(\d+)\./);

	var engineWebkitVersion = !!webkit && +webkit[1];

	var test$1 = [];
	var un$Sort = functionUncurryThis(test$1.sort);
	var push$1 = functionUncurryThis(test$1.push);

	// IE8-
	var FAILS_ON_UNDEFINED = fails(function() {
		test$1.sort(undefined);
	});
	// V8 bug
	var FAILS_ON_NULL = fails(function() {
		test$1.sort(null);
	});
	// Old WebKit
	var STRICT_METHOD = arrayMethodIsStrict('sort');

	var STABLE_SORT = !fails(function() {
		// feature detection can be too slow, so check engines versions
		if (engineV8Version) return engineV8Version < 70;
		if (engineFfVersion && engineFfVersion > 3) return;
		if (engineIsIeOrEdge) return true;
		if (engineWebkitVersion) return engineWebkitVersion < 603;

		var result = '';
		var code, chr, value, index;

		// generate an array with more 512 elements (Chakra and old V8 fails only in this case)
		for (code = 65; code < 76; code++) {
			chr = String.fromCharCode(code);

			switch (code) {
				case 66:
				case 69:
				case 70:
				case 72:
					value = 3;
					break;
				case 68:
				case 71:
					value = 4;
					break;
				default:
					value = 2;
			}

			for (index = 0; index < 47; index++) {
				test$1.push({
					k: chr + index,
					v: value
				});
			}
		}

		test$1.sort(function(a, b) {
			return b.v - a.v;
		});

		for (index = 0; index < test$1.length; index++) {
			chr = test$1[index].k.charAt(0);
			if (result.charAt(result.length - 1) !== chr) result += chr;
		}

		return result !== 'DGBEFHACIJK';
	});

	var FORCED$1 = FAILS_ON_UNDEFINED || !FAILS_ON_NULL || !STRICT_METHOD || !STABLE_SORT;

	var getSortCompare = function(comparefn) {
		return function(x, y) {
			if (y === undefined) return -1;
			if (x === undefined) return 1;
			if (comparefn !== undefined) return +comparefn(x, y) || 0;
			return toString_1(x) > toString_1(y) ? 1 : -1;
		};
	};

	// `Array.prototype.sort` method
	// https://tc39.es/ecma262/#sec-array.prototype.sort
	_export({
		target: 'Array',
		proto: true,
		forced: FORCED$1
	}, {
		sort: function sort(comparefn) {
			if (comparefn !== undefined) aCallable(comparefn);

			var array = toObject(this);

			if (STABLE_SORT) return comparefn === undefined ? un$Sort(array) : un$Sort(array,
				comparefn);

			var items = [];
			var arrayLength = lengthOfArrayLike(array);
			var itemsLength, index;

			for (index = 0; index < arrayLength; index++) {
				if (index in array) push$1(items, array[index]);
			}

			arraySort(items, getSortCompare(comparefn));

			itemsLength = items.length;
			index = 0;

			while (index < itemsLength) array[index] = items[index++];
			while (index < arrayLength) delete array[index++];

			return array;
		}
	});

	var floor = Math.floor;
	var charAt$1 = functionUncurryThis(''.charAt);
	var replace$1 = functionUncurryThis(''.replace);
	var stringSlice$3 = functionUncurryThis(''.slice);
	var SUBSTITUTION_SYMBOLS = /\$([$&'`]|\d{1,2}|<[^>]*>)/g;
	var SUBSTITUTION_SYMBOLS_NO_NAMED = /\$([$&'`]|\d{1,2})/g;

	// `GetSubstitution` abstract operation
	// https://tc39.es/ecma262/#sec-getsubstitution
	var getSubstitution = function(matched, str, position, captures, namedCaptures, replacement) {
		var tailPos = position + matched.length;
		var m = captures.length;
		var symbols = SUBSTITUTION_SYMBOLS_NO_NAMED;
		if (namedCaptures !== undefined) {
			namedCaptures = toObject(namedCaptures);
			symbols = SUBSTITUTION_SYMBOLS;
		}
		return replace$1(replacement, symbols, function(match, ch) {
			var capture;
			switch (charAt$1(ch, 0)) {
				case '$':
					return '$';
				case '&':
					return matched;
				case '`':
					return stringSlice$3(str, 0, position);
				case "'":
					return stringSlice$3(str, tailPos);
				case '<':
					capture = namedCaptures[stringSlice$3(ch, 1, -1)];
					break;
				default: // \d\d?
					var n = +ch;
					if (n === 0) return match;
					if (n > m) {
						var f = floor(n / 10);
						if (f === 0) return match;
						if (f <= m) return captures[f - 1] === undefined ? charAt$1(ch, 1) :
							captures[f - 1] + charAt$1(ch, 1);
						return match;
					}
					capture = captures[n - 1];
			}
			return capture === undefined ? '' : capture;
		});
	};

	var REPLACE = wellKnownSymbol('replace');
	var max$2 = Math.max;
	var min$3 = Math.min;
	var concat = functionUncurryThis([].concat);
	var push = functionUncurryThis([].push);
	var stringIndexOf$1 = functionUncurryThis(''.indexOf);
	var stringSlice$2 = functionUncurryThis(''.slice);

	var maybeToString = function(it) {
		return it === undefined ? it : String(it);
	};

	// IE <= 11 replaces $0 with the whole match, as if it was $&
	// https://stackoverflow.com/questions/6024666/getting-ie-to-replace-a-regex-with-the-literal-string-0
	var REPLACE_KEEPS_$0 = (function() {
		// eslint-disable-next-line regexp/prefer-escape-replacement-dollar-char -- required for testing
		return 'a'.replace(/./, '$0') === '$0';
	})();

	// Safari <= 13.0.3(?) substitutes nth capture where n>m with an empty string
	var REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE = (function() {
		if (/./ [REPLACE]) {
			return /./ [REPLACE]('a', '$0') === '';
		}
		return false;
	})();

	var REPLACE_SUPPORTS_NAMED_GROUPS = !fails(function() {
		var re = /./;
		re.exec = function() {
			var result = [];
			result.groups = {
				a: '7'
			};
			return result;
		};
		// eslint-disable-next-line regexp/no-useless-dollar-replacements -- false positive
		return ''.replace(re, '$<a>') !== '7';
	});

	// @@replace logic
	fixRegexpWellKnownSymbolLogic('replace', function(_, nativeReplace, maybeCallNative) {
			var UNSAFE_SUBSTITUTE = REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE ? '$' : '$0';

			return [
				// `String.prototype.replace` method
				// https://tc39.es/ecma262/#sec-string.prototype.replace
				function replace(searchValue, replaceValue) {
					var O = requireObjectCoercible(this);
					var replacer = searchValue == undefined ? undefined : getMethod(searchValue,
						REPLACE);
					return replacer ?
						functionCall(replacer, searchValue, O, replaceValue) :
						functionCall(nativeReplace, toString_1(O), searchValue, replaceValue);
				},
				// `RegExp.prototype[@@replace]` method
				// https://tc39.es/ecma262/#sec-regexp.prototype-@@replace
				function(string, replaceValue) {
					var rx = anObject(this);
					var S = toString_1(string);

					if (
						typeof replaceValue == 'string' &&
						stringIndexOf$1(replaceValue, UNSAFE_SUBSTITUTE) === -1 &&
						stringIndexOf$1(replaceValue, '$<') === -1
					) {
						var res = maybeCallNative(nativeReplace, rx, S, replaceValue);
						if (res.done) return res.value;
					}

					var functionalReplace = isCallable(replaceValue);
					if (!functionalReplace) replaceValue = toString_1(replaceValue);

					var global = rx.global;
					if (global) {
						var fullUnicode = rx.unicode;
						rx.lastIndex = 0;
					}
					var results = [];
					while (true) {
						var result = regexpExecAbstract(rx, S);
						if (result === null) break;

						push(results, result);
						if (!global) break;

						var matchStr = toString_1(result[0]);
						if (matchStr === '') rx.lastIndex = advanceStringIndex(S, toLength(rx
							.lastIndex), fullUnicode);
					}

					var accumulatedResult = '';
					var nextSourcePosition = 0;
					for (var i = 0; i < results.length; i++) {
						result = results[i];

						var matched = toString_1(result[0]);
						var position = max$2(min$3(toIntegerOrInfinity(result.index), S.length), 0);
						var captures = [];
						// NOTE: This is equivalent to
						//   captures = result.slice(1).map(maybeToString)
						// but for some reason `nativeSlice.call(result, 1, result.length)` (called in
						// the slice polyfill when slicing native arrays) "doesn't work" in safari 9 and
						// causes a crash (https://pastebin.com/N21QzeQA) when trying to debug it.
						for (var j = 1; j < result.length; j++) push(captures, maybeToString(result[
							j]));
						var namedCaptures = result.groups;
						if (functionalReplace) {
							var replacerArgs = concat([matched], captures, position, S);
							if (namedCaptures !== undefined) push(replacerArgs, namedCaptures);
							var replacement = toString_1(functionApply(replaceValue, undefined,
								replacerArgs));
						} else {
							replacement = getSubstitution(matched, S, position, captures, namedCaptures,
								replaceValue);
						}
						if (position >= nextSourcePosition) {
							accumulatedResult += stringSlice$2(S, nextSourcePosition, position) +
								replacement;
							nextSourcePosition = position + matched.length;
						}
					}
					return accumulatedResult + stringSlice$2(S, nextSourcePosition);
				}
			];
		}, !REPLACE_SUPPORTS_NAMED_GROUPS || !REPLACE_KEEPS_$0 ||
		REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE);

	var $filter = arrayIteration.filter;


	var HAS_SPECIES_SUPPORT$3 = arrayMethodHasSpeciesSupport('filter');

	// `Array.prototype.filter` method
	// https://tc39.es/ecma262/#sec-array.prototype.filter
	// with adding support of @@species
	_export({
		target: 'Array',
		proto: true,
		forced: !HAS_SPECIES_SUPPORT$3
	}, {
		filter: function filter(callbackfn /* , thisArg */ ) {
			return $filter(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
		}
	});

	// `SameValue` abstract operation
	// https://tc39.es/ecma262/#sec-samevalue
	// eslint-disable-next-line es/no-object-is -- safe
	var sameValue = Object.is || function is(x, y) {
		// eslint-disable-next-line no-self-compare -- NaN check
		return x === y ? x !== 0 || 1 / x === 1 / y : x != x && y != y;
	};

	// @@search logic
	fixRegexpWellKnownSymbolLogic('search', function(SEARCH, nativeSearch, maybeCallNative) {
		return [
			// `String.prototype.search` method
			// https://tc39.es/ecma262/#sec-string.prototype.search
			function search(regexp) {
				var O = requireObjectCoercible(this);
				var searcher = regexp == undefined ? undefined : getMethod(regexp, SEARCH);
				return searcher ? functionCall(searcher, regexp, O) : new RegExp(regexp)[SEARCH](
					toString_1(O));
			},
			// `RegExp.prototype[@@search]` method
			// https://tc39.es/ecma262/#sec-regexp.prototype-@@search
			function(string) {
				var rx = anObject(this);
				var S = toString_1(string);
				var res = maybeCallNative(nativeSearch, rx, S);

				if (res.done) return res.value;

				var previousLastIndex = rx.lastIndex;
				if (!sameValue(previousLastIndex, 0)) rx.lastIndex = 0;
				var result = regexpExecAbstract(rx, S);
				if (!sameValue(rx.lastIndex, previousLastIndex)) rx.lastIndex = previousLastIndex;
				return result === null ? -1 : result.index;
			}
		];
	});

	var trim$1 = stringTrim.trim;


	var $parseInt = global_1.parseInt;
	var Symbol$1 = global_1.Symbol;
	var ITERATOR$3 = Symbol$1 && Symbol$1.iterator;
	var hex = /^[+-]?0x/i;
	var exec$1 = functionUncurryThis(hex.exec);
	var FORCED = $parseInt(whitespaces + '08') !== 8 || $parseInt(whitespaces + '0x16') !== 22
		// MS Edge 18- broken with boxed symbols
		||
		(ITERATOR$3 && !fails(function() {
			$parseInt(Object(ITERATOR$3));
		}));

	// `parseInt` method
	// https://tc39.es/ecma262/#sec-parseint-string-radix
	var numberParseInt = FORCED ? function parseInt(string, radix) {
		var S = trim$1(toString_1(string));
		return $parseInt(S, (radix >>> 0) || (exec$1(hex, S) ? 16 : 10));
	} : $parseInt;

	// `parseInt` method
	// https://tc39.es/ecma262/#sec-parseint-string-radix
	_export({
		global: true,
		forced: parseInt != numberParseInt
	}, {
		parseInt: numberParseInt
	});

	var $map = arrayIteration.map;


	var HAS_SPECIES_SUPPORT$2 = arrayMethodHasSpeciesSupport('map');

	// `Array.prototype.map` method
	// https://tc39.es/ecma262/#sec-array.prototype.map
	// with adding support of @@species
	_export({
		target: 'Array',
		proto: true,
		forced: !HAS_SPECIES_SUPPORT$2
	}, {
		map: function map(callbackfn /* , thisArg */ ) {
			return $map(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
		}
	});

	var $findIndex = arrayIteration.findIndex;


	var FIND_INDEX = 'findIndex';
	var SKIPS_HOLES = true;

	// Shouldn't skip holes
	if (FIND_INDEX in []) Array(1)[FIND_INDEX](function() {
		SKIPS_HOLES = false;
	});

	// `Array.prototype.findIndex` method
	// https://tc39.es/ecma262/#sec-array.prototype.findindex
	_export({
		target: 'Array',
		proto: true,
		forced: SKIPS_HOLES
	}, {
		findIndex: function findIndex(callbackfn /* , that = undefined */ ) {
			return $findIndex(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
		}
	});

	// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
	addToUnscopables(FIND_INDEX);

	var String$1 = global_1.String;
	var TypeError$3 = global_1.TypeError;

	var aPossiblePrototype = function(argument) {
		if (typeof argument == 'object' || isCallable(argument)) return argument;
		throw TypeError$3("Can't set " + String$1(argument) + ' as a prototype');
	};

	/* eslint-disable no-proto -- safe */




	// `Object.setPrototypeOf` method
	// https://tc39.es/ecma262/#sec-object.setprototypeof
	// Works with __proto__ only. Old v8 can't work with null proto objects.
	// eslint-disable-next-line es/no-object-setprototypeof -- safe
	var objectSetPrototypeOf = Object.setPrototypeOf || ('__proto__' in {} ? function() {
		var CORRECT_SETTER = false;
		var test = {};
		var setter;
		try {
			// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
			setter = functionUncurryThis(Object.getOwnPropertyDescriptor(Object.prototype, '__proto__')
				.set);
			setter(test, []);
			CORRECT_SETTER = test instanceof Array;
		} catch (error) {
			/* empty */
		}
		return function setPrototypeOf(O, proto) {
			anObject(O);
			aPossiblePrototype(proto);
			if (CORRECT_SETTER) setter(O, proto);
			else O.__proto__ = proto;
			return O;
		};
	}() : undefined);

	// makes subclassing work correct for wrapped built-ins
	var inheritIfRequired = function($this, dummy, Wrapper) {
		var NewTarget, NewTargetPrototype;
		if (
			// it can work only with native `setPrototypeOf`
			objectSetPrototypeOf &&
			// we haven't completely correct pre-ES6 way for getting `new.target`, so use this
			isCallable(NewTarget = dummy.constructor) &&
			NewTarget !== Wrapper &&
			isObject(NewTargetPrototype = NewTarget.prototype) &&
			NewTargetPrototype !== Wrapper.prototype
		) objectSetPrototypeOf($this, NewTargetPrototype);
		return $this;
	};

	var SPECIES$1 = wellKnownSymbol('species');

	var setSpecies = function(CONSTRUCTOR_NAME) {
		var Constructor = getBuiltIn(CONSTRUCTOR_NAME);
		var defineProperty = objectDefineProperty.f;

		if (descriptors && Constructor && !Constructor[SPECIES$1]) {
			defineProperty(Constructor, SPECIES$1, {
				configurable: true,
				get: function() {
					return this;
				}
			});
		}
	};

	var defineProperty$3 = objectDefineProperty.f;
	var getOwnPropertyNames$1 = objectGetOwnPropertyNames.f;








	var enforceInternalState = internalState.enforce;





	var MATCH = wellKnownSymbol('match');
	var NativeRegExp = global_1.RegExp;
	var RegExpPrototype$1 = NativeRegExp.prototype;
	var SyntaxError = global_1.SyntaxError;
	var getFlags$1 = functionUncurryThis(regexpFlags);
	var exec = functionUncurryThis(RegExpPrototype$1.exec);
	var charAt = functionUncurryThis(''.charAt);
	var replace = functionUncurryThis(''.replace);
	var stringIndexOf = functionUncurryThis(''.indexOf);
	var stringSlice$1 = functionUncurryThis(''.slice);
	// TODO: Use only propper RegExpIdentifierName
	var IS_NCG = /^\?<[^\s\d!#%&*+<=>@^][^\s!#%&*+<=>@^]*>/;
	var re1 = /a/g;
	var re2 = /a/g;

	// "new" should create a new object, old webkit bug
	var CORRECT_NEW = new NativeRegExp(re1) !== re1;

	var MISSED_STICKY = regexpStickyHelpers.MISSED_STICKY;
	var UNSUPPORTED_Y = regexpStickyHelpers.UNSUPPORTED_Y;

	var BASE_FORCED = descriptors &&
		(!CORRECT_NEW || MISSED_STICKY || regexpUnsupportedDotAll || regexpUnsupportedNcg || fails(function() {
			re2[MATCH] = false;
			// RegExp constructor can alter flags and IsRegExp works correct with @@match
			return NativeRegExp(re1) != re1 || NativeRegExp(re2) == re2 || NativeRegExp(re1, 'i') !=
				'/a/i';
		}));

	var handleDotAll = function(string) {
		var length = string.length;
		var index = 0;
		var result = '';
		var brackets = false;
		var chr;
		for (; index <= length; index++) {
			chr = charAt(string, index);
			if (chr === '\\') {
				result += chr + charAt(string, ++index);
				continue;
			}
			if (!brackets && chr === '.') {
				result += '[\\s\\S]';
			} else {
				if (chr === '[') {
					brackets = true;
				} else if (chr === ']') {
					brackets = false;
				}
				result += chr;
			}
		}
		return result;
	};

	var handleNCG = function(string) {
		var length = string.length;
		var index = 0;
		var result = '';
		var named = [];
		var names = {};
		var brackets = false;
		var ncg = false;
		var groupid = 0;
		var groupname = '';
		var chr;
		for (; index <= length; index++) {
			chr = charAt(string, index);
			if (chr === '\\') {
				chr = chr + charAt(string, ++index);
			} else if (chr === ']') {
				brackets = false;
			} else if (!brackets) switch (true) {
				case chr === '[':
					brackets = true;
					break;
				case chr === '(':
					if (exec(IS_NCG, stringSlice$1(string, index + 1))) {
						index += 2;
						ncg = true;
					}
					result += chr;
					groupid++;
					continue;
				case chr === '>' && ncg:
					if (groupname === '' || hasOwnProperty_1(names, groupname)) {
						throw new SyntaxError('Invalid capture group name');
					}
					names[groupname] = true;
					named[named.length] = [groupname, groupid];
					ncg = false;
					groupname = '';
					continue;
			}
			if (ncg) groupname += chr;
			else result += chr;
		}
		return [result, named];
	};

	// `RegExp` constructor
	// https://tc39.es/ecma262/#sec-regexp-constructor
	if (isForced_1('RegExp', BASE_FORCED)) {
		var RegExpWrapper = function RegExp(pattern, flags) {
			var thisIsRegExp = objectIsPrototypeOf(RegExpPrototype$1, this);
			var patternIsRegExp = isRegexp(pattern);
			var flagsAreUndefined = flags === undefined;
			var groups = [];
			var rawPattern = pattern;
			var rawFlags, dotAll, sticky, handled, result, state;

			if (!thisIsRegExp && patternIsRegExp && flagsAreUndefined && pattern.constructor ===
				RegExpWrapper) {
				return pattern;
			}

			if (patternIsRegExp || objectIsPrototypeOf(RegExpPrototype$1, pattern)) {
				pattern = pattern.source;
				if (flagsAreUndefined) flags = 'flags' in rawPattern ? rawPattern.flags : getFlags$1(
					rawPattern);
			}

			pattern = pattern === undefined ? '' : toString_1(pattern);
			flags = flags === undefined ? '' : toString_1(flags);
			rawPattern = pattern;

			if (regexpUnsupportedDotAll && 'dotAll' in re1) {
				dotAll = !!flags && stringIndexOf(flags, 's') > -1;
				if (dotAll) flags = replace(flags, /s/g, '');
			}

			rawFlags = flags;

			if (MISSED_STICKY && 'sticky' in re1) {
				sticky = !!flags && stringIndexOf(flags, 'y') > -1;
				if (sticky && UNSUPPORTED_Y) flags = replace(flags, /y/g, '');
			}

			if (regexpUnsupportedNcg) {
				handled = handleNCG(pattern);
				pattern = handled[0];
				groups = handled[1];
			}

			result = inheritIfRequired(NativeRegExp(pattern, flags), thisIsRegExp ? this :
				RegExpPrototype$1, RegExpWrapper);

			if (dotAll || sticky || groups.length) {
				state = enforceInternalState(result);
				if (dotAll) {
					state.dotAll = true;
					state.raw = RegExpWrapper(handleDotAll(pattern), rawFlags);
				}
				if (sticky) state.sticky = true;
				if (groups.length) state.groups = groups;
			}

			if (pattern !== rawPattern) try {
				// fails in old engines, but we have no alternatives for unsupported regex syntax
				createNonEnumerableProperty(result, 'source', rawPattern === '' ? '(?:)' : rawPattern);
			} catch (error) {
				/* empty */
			}

			return result;
		};

		var proxy = function(key) {
			key in RegExpWrapper || defineProperty$3(RegExpWrapper, key, {
				configurable: true,
				get: function() {
					return NativeRegExp[key];
				},
				set: function(it) {
					NativeRegExp[key] = it;
				}
			});
		};

		for (var keys$1 = getOwnPropertyNames$1(NativeRegExp), index = 0; keys$1.length > index;) {
			proxy(keys$1[index++]);
		}

		RegExpPrototype$1.constructor = RegExpWrapper;
		RegExpWrapper.prototype = RegExpPrototype$1;
		redefine(global_1, 'RegExp', RegExpWrapper);
	}

	// https://tc39.es/ecma262/#sec-get-regexp-@@species
	setSpecies('RegExp');

	var PROPER_FUNCTION_NAME$1 = functionName.PROPER;







	var TO_STRING = 'toString';
	var RegExpPrototype = RegExp.prototype;
	var n$ToString = RegExpPrototype[TO_STRING];
	var getFlags = functionUncurryThis(regexpFlags);

	var NOT_GENERIC = fails(function() {
		return n$ToString.call({
			source: 'a',
			flags: 'b'
		}) != '/a/b';
	});
	// FF44- RegExp#toString has a wrong name
	var INCORRECT_NAME = PROPER_FUNCTION_NAME$1 && n$ToString.name != TO_STRING;

	// `RegExp.prototype.toString` method
	// https://tc39.es/ecma262/#sec-regexp.prototype.tostring
	if (NOT_GENERIC || INCORRECT_NAME) {
		redefine(RegExp.prototype, TO_STRING, function toString() {
			var R = anObject(this);
			var p = toString_1(R.source);
			var rf = R.flags;
			var f = toString_1(rf === undefined && objectIsPrototypeOf(RegExpPrototype, R) && !(
				'flags' in RegExpPrototype) ? getFlags(R) : rf);
			return '/' + p + '/' + f;
		}, {
			unsafe: true
		});
	}

	var arraySlice$1 = functionUncurryThis([].slice);

	var HAS_SPECIES_SUPPORT$1 = arrayMethodHasSpeciesSupport('slice');

	var SPECIES = wellKnownSymbol('species');
	var Array$1 = global_1.Array;
	var max$1 = Math.max;

	// `Array.prototype.slice` method
	// https://tc39.es/ecma262/#sec-array.prototype.slice
	// fallback for not array-like ES3 strings and DOM objects
	_export({
		target: 'Array',
		proto: true,
		forced: !HAS_SPECIES_SUPPORT$1
	}, {
		slice: function slice(start, end) {
			var O = toIndexedObject(this);
			var length = lengthOfArrayLike(O);
			var k = toAbsoluteIndex(start, length);
			var fin = toAbsoluteIndex(end === undefined ? length : end, length);
			// inline `ArraySpeciesCreate` for usage native `Array#slice` where it's possible
			var Constructor, result, n;
			if (isArray(O)) {
				Constructor = O.constructor;
				// cross-realm fallback
				if (isConstructor(Constructor) && (Constructor === Array$1 || isArray(Constructor
						.prototype))) {
					Constructor = undefined;
				} else if (isObject(Constructor)) {
					Constructor = Constructor[SPECIES];
					if (Constructor === null) Constructor = undefined;
				}
				if (Constructor === Array$1 || Constructor === undefined) {
					return arraySlice$1(O, k, fin);
				}
			}
			result = new(Constructor === undefined ? Array$1 : Constructor)(max$1(fin - k, 0));
			for (n = 0; k < fin; k++, n++)
				if (k in O) createProperty(result, n, O[k]);
			result.length = n;
			return result;
		}
	});

	var iterators = {};

	var correctPrototypeGetter = !fails(function() {
		function F() {
			/* empty */
		}
		F.prototype.constructor = null;
		// eslint-disable-next-line es/no-object-getprototypeof -- required for testing
		return Object.getPrototypeOf(new F()) !== F.prototype;
	});

	var IE_PROTO = sharedKey('IE_PROTO');
	var Object$1 = global_1.Object;
	var ObjectPrototype = Object$1.prototype;

	// `Object.getPrototypeOf` method
	// https://tc39.es/ecma262/#sec-object.getprototypeof
	var objectGetPrototypeOf = correctPrototypeGetter ? Object$1.getPrototypeOf : function(O) {
		var object = toObject(O);
		if (hasOwnProperty_1(object, IE_PROTO)) return object[IE_PROTO];
		var constructor = object.constructor;
		if (isCallable(constructor) && object instanceof constructor) {
			return constructor.prototype;
		}
		return object instanceof Object$1 ? ObjectPrototype : null;
	};

	var ITERATOR$2 = wellKnownSymbol('iterator');
	var BUGGY_SAFARI_ITERATORS$1 = false;

	// `%IteratorPrototype%` object
	// https://tc39.es/ecma262/#sec-%iteratorprototype%-object
	var IteratorPrototype$2, PrototypeOfArrayIteratorPrototype, arrayIterator;

	/* eslint-disable es/no-array-prototype-keys -- safe */
	if ([].keys) {
		arrayIterator = [].keys();
		// Safari 8 has buggy iterators w/o `next`
		if (!('next' in arrayIterator)) BUGGY_SAFARI_ITERATORS$1 = true;
		else {
			PrototypeOfArrayIteratorPrototype = objectGetPrototypeOf(objectGetPrototypeOf(arrayIterator));
			if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype$2 =
				PrototypeOfArrayIteratorPrototype;
		}
	}

	var NEW_ITERATOR_PROTOTYPE = IteratorPrototype$2 == undefined || fails(function() {
		var test = {};
		// FF44- legacy iterators case
		return IteratorPrototype$2[ITERATOR$2].call(test) !== test;
	});

	if (NEW_ITERATOR_PROTOTYPE) IteratorPrototype$2 = {};

	// `%IteratorPrototype%[@@iterator]()` method
	// https://tc39.es/ecma262/#sec-%iteratorprototype%-@@iterator
	if (!isCallable(IteratorPrototype$2[ITERATOR$2])) {
		redefine(IteratorPrototype$2, ITERATOR$2, function() {
			return this;
		});
	}

	var iteratorsCore = {
		IteratorPrototype: IteratorPrototype$2,
		BUGGY_SAFARI_ITERATORS: BUGGY_SAFARI_ITERATORS$1
	};

	var defineProperty$2 = objectDefineProperty.f;



	var TO_STRING_TAG$1 = wellKnownSymbol('toStringTag');

	var setToStringTag = function(target, TAG, STATIC) {
		if (target && !STATIC) target = target.prototype;
		if (target && !hasOwnProperty_1(target, TO_STRING_TAG$1)) {
			defineProperty$2(target, TO_STRING_TAG$1, {
				configurable: true,
				value: TAG
			});
		}
	};

	var IteratorPrototype$1 = iteratorsCore.IteratorPrototype;





	var returnThis$1 = function() {
		return this;
	};

	var createIteratorConstructor = function(IteratorConstructor, NAME, next, ENUMERABLE_NEXT) {
		var TO_STRING_TAG = NAME + ' Iterator';
		IteratorConstructor.prototype = objectCreate(IteratorPrototype$1, {
			next: createPropertyDescriptor(+!ENUMERABLE_NEXT, next)
		});
		setToStringTag(IteratorConstructor, TO_STRING_TAG, false);
		iterators[TO_STRING_TAG] = returnThis$1;
		return IteratorConstructor;
	};

	var PROPER_FUNCTION_NAME = functionName.PROPER;
	var CONFIGURABLE_FUNCTION_NAME = functionName.CONFIGURABLE;
	var IteratorPrototype = iteratorsCore.IteratorPrototype;
	var BUGGY_SAFARI_ITERATORS = iteratorsCore.BUGGY_SAFARI_ITERATORS;
	var ITERATOR$1 = wellKnownSymbol('iterator');
	var KEYS = 'keys';
	var VALUES = 'values';
	var ENTRIES = 'entries';

	var returnThis = function() {
		return this;
	};

	var defineIterator = function(Iterable, NAME, IteratorConstructor, next, DEFAULT, IS_SET, FORCED) {
		createIteratorConstructor(IteratorConstructor, NAME, next);

		var getIterationMethod = function(KIND) {
			if (KIND === DEFAULT && defaultIterator) return defaultIterator;
			if (!BUGGY_SAFARI_ITERATORS && KIND in IterablePrototype) return IterablePrototype[KIND];
			switch (KIND) {
				case KEYS:
					return function keys() {
						return new IteratorConstructor(this, KIND);
					};
				case VALUES:
					return function values() {
						return new IteratorConstructor(this, KIND);
					};
				case ENTRIES:
					return function entries() {
						return new IteratorConstructor(this, KIND);
					};
			}
			return function() {
				return new IteratorConstructor(this);
			};
		};

		var TO_STRING_TAG = NAME + ' Iterator';
		var INCORRECT_VALUES_NAME = false;
		var IterablePrototype = Iterable.prototype;
		var nativeIterator = IterablePrototype[ITERATOR$1] ||
			IterablePrototype['@@iterator'] ||
			DEFAULT && IterablePrototype[DEFAULT];
		var defaultIterator = !BUGGY_SAFARI_ITERATORS && nativeIterator || getIterationMethod(DEFAULT);
		var anyNativeIterator = NAME == 'Array' ? IterablePrototype.entries || nativeIterator :
			nativeIterator;
		var CurrentIteratorPrototype, methods, KEY;

		// fix native
		if (anyNativeIterator) {
			CurrentIteratorPrototype = objectGetPrototypeOf(anyNativeIterator.call(new Iterable()));
			if (CurrentIteratorPrototype !== Object.prototype && CurrentIteratorPrototype.next) {
				if (objectGetPrototypeOf(CurrentIteratorPrototype) !== IteratorPrototype) {
					if (objectSetPrototypeOf) {
						objectSetPrototypeOf(CurrentIteratorPrototype, IteratorPrototype);
					} else if (!isCallable(CurrentIteratorPrototype[ITERATOR$1])) {
						redefine(CurrentIteratorPrototype, ITERATOR$1, returnThis);
					}
				}
				// Set @@toStringTag to native iterators
				setToStringTag(CurrentIteratorPrototype, TO_STRING_TAG, true);
			}
		}

		// fix Array.prototype.{ values, @@iterator }.name in V8 / FF
		if (PROPER_FUNCTION_NAME && DEFAULT == VALUES && nativeIterator && nativeIterator.name !== VALUES) {
			if (CONFIGURABLE_FUNCTION_NAME) {
				createNonEnumerableProperty(IterablePrototype, 'name', VALUES);
			} else {
				INCORRECT_VALUES_NAME = true;
				defaultIterator = function values() {
					return functionCall(nativeIterator, this);
				};
			}
		}

		// export additional methods
		if (DEFAULT) {
			methods = {
				values: getIterationMethod(VALUES),
				keys: IS_SET ? defaultIterator : getIterationMethod(KEYS),
				entries: getIterationMethod(ENTRIES)
			};
			if (FORCED)
				for (KEY in methods) {
					if (BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME || !(KEY in IterablePrototype)) {
						redefine(IterablePrototype, KEY, methods[KEY]);
					}
				} else _export({
					target: NAME,
					proto: true,
					forced: BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME
				}, methods);
		}

		// define iterator
		if (IterablePrototype[ITERATOR$1] !== defaultIterator) {
			redefine(IterablePrototype, ITERATOR$1, defaultIterator, {
				name: DEFAULT
			});
		}
		iterators[NAME] = defaultIterator;

		return methods;
	};

	var defineProperty$1 = objectDefineProperty.f;




	var ARRAY_ITERATOR = 'Array Iterator';
	var setInternalState = internalState.set;
	var getInternalState = internalState.getterFor(ARRAY_ITERATOR);

	// `Array.prototype.entries` method
	// https://tc39.es/ecma262/#sec-array.prototype.entries
	// `Array.prototype.keys` method
	// https://tc39.es/ecma262/#sec-array.prototype.keys
	// `Array.prototype.values` method
	// https://tc39.es/ecma262/#sec-array.prototype.values
	// `Array.prototype[@@iterator]` method
	// https://tc39.es/ecma262/#sec-array.prototype-@@iterator
	// `CreateArrayIterator` internal method
	// https://tc39.es/ecma262/#sec-createarrayiterator
	var es_array_iterator = defineIterator(Array, 'Array', function(iterated, kind) {
		setInternalState(this, {
			type: ARRAY_ITERATOR,
			target: toIndexedObject(iterated), // target
			index: 0, // next index
			kind: kind // kind
		});
		// `%ArrayIteratorPrototype%.next` method
		// https://tc39.es/ecma262/#sec-%arrayiteratorprototype%.next
	}, function() {
		var state = getInternalState(this);
		var target = state.target;
		var kind = state.kind;
		var index = state.index++;
		if (!target || index >= target.length) {
			state.target = undefined;
			return {
				value: undefined,
				done: true
			};
		}
		if (kind == 'keys') return {
			value: index,
			done: false
		};
		if (kind == 'values') return {
			value: target[index],
			done: false
		};
		return {
			value: [index, target[index]],
			done: false
		};
	}, 'values');

	// argumentsList[@@iterator] is %ArrayProto_values%
	// https://tc39.es/ecma262/#sec-createunmappedargumentsobject
	// https://tc39.es/ecma262/#sec-createmappedargumentsobject
	var values = iterators.Arguments = iterators.Array;

	// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
	addToUnscopables('keys');
	addToUnscopables('values');
	addToUnscopables('entries');

	// V8 ~ Chrome 45- bug
	if (descriptors && values.name !== 'values') try {
		defineProperty$1(values, 'name', {
			value: 'values'
		});
	} catch (error) {
		/* empty */
	}

	var ITERATOR = wellKnownSymbol('iterator');
	var TO_STRING_TAG = wellKnownSymbol('toStringTag');
	var ArrayValues = es_array_iterator.values;

	var handlePrototype = function(CollectionPrototype, COLLECTION_NAME) {
		if (CollectionPrototype) {
			// some Chrome versions have non-configurable methods on DOMTokenList
			if (CollectionPrototype[ITERATOR] !== ArrayValues) try {
				createNonEnumerableProperty(CollectionPrototype, ITERATOR, ArrayValues);
			} catch (error) {
				CollectionPrototype[ITERATOR] = ArrayValues;
			}
			if (!CollectionPrototype[TO_STRING_TAG]) {
				createNonEnumerableProperty(CollectionPrototype, TO_STRING_TAG, COLLECTION_NAME);
			}
			if (domIterables[COLLECTION_NAME])
				for (var METHOD_NAME in es_array_iterator) {
					// some Chrome versions have non-configurable methods on DOMTokenList
					if (CollectionPrototype[METHOD_NAME] !== es_array_iterator[METHOD_NAME]) try {
						createNonEnumerableProperty(CollectionPrototype, METHOD_NAME, es_array_iterator[
							METHOD_NAME]);
					} catch (error) {
						CollectionPrototype[METHOD_NAME] = es_array_iterator[METHOD_NAME];
					}
				}
		}
	};

	for (var COLLECTION_NAME in domIterables) {
		handlePrototype(global_1[COLLECTION_NAME] && global_1[COLLECTION_NAME].prototype, COLLECTION_NAME);
	}

	handlePrototype(domTokenListPrototype, 'DOMTokenList');

	var HAS_SPECIES_SUPPORT = arrayMethodHasSpeciesSupport('splice');

	var TypeError$2 = global_1.TypeError;
	var max = Math.max;
	var min$2 = Math.min;
	var MAX_SAFE_INTEGER = 0x1FFFFFFFFFFFFF;
	var MAXIMUM_ALLOWED_LENGTH_EXCEEDED = 'Maximum allowed length exceeded';

	// `Array.prototype.splice` method
	// https://tc39.es/ecma262/#sec-array.prototype.splice
	// with adding support of @@species
	_export({
		target: 'Array',
		proto: true,
		forced: !HAS_SPECIES_SUPPORT
	}, {
		splice: function splice(start, deleteCount /* , ...items */ ) {
			var O = toObject(this);
			var len = lengthOfArrayLike(O);
			var actualStart = toAbsoluteIndex(start, len);
			var argumentsLength = arguments.length;
			var insertCount, actualDeleteCount, A, k, from, to;
			if (argumentsLength === 0) {
				insertCount = actualDeleteCount = 0;
			} else if (argumentsLength === 1) {
				insertCount = 0;
				actualDeleteCount = len - actualStart;
			} else {
				insertCount = argumentsLength - 2;
				actualDeleteCount = min$2(max(toIntegerOrInfinity(deleteCount), 0), len -
					actualStart);
			}
			if (len + insertCount - actualDeleteCount > MAX_SAFE_INTEGER) {
				throw TypeError$2(MAXIMUM_ALLOWED_LENGTH_EXCEEDED);
			}
			A = arraySpeciesCreate(O, actualDeleteCount);
			for (k = 0; k < actualDeleteCount; k++) {
				from = actualStart + k;
				if (from in O) createProperty(A, k, O[from]);
			}
			A.length = actualDeleteCount;
			if (insertCount < actualDeleteCount) {
				for (k = actualStart; k < len - actualDeleteCount; k++) {
					from = k + actualDeleteCount;
					to = k + insertCount;
					if (from in O) O[to] = O[from];
					else delete O[to];
				}
				for (k = len; k > len - actualDeleteCount + insertCount; k--) delete O[k - 1];
			} else if (insertCount > actualDeleteCount) {
				for (k = len - actualDeleteCount; k > actualStart; k--) {
					from = k + actualDeleteCount - 1;
					to = k + insertCount - 1;
					if (from in O) O[to] = O[from];
					else delete O[to];
				}
			}
			for (k = 0; k < insertCount; k++) {
				O[k + actualStart] = arguments[k + 2];
			}
			O.length = len - actualDeleteCount + insertCount;
			return A;
		}
	});

	// `thisNumberValue` abstract operation
	// https://tc39.es/ecma262/#sec-thisnumbervalue
	var thisNumberValue = functionUncurryThis(1.0.valueOf);

	var getOwnPropertyNames = objectGetOwnPropertyNames.f;
	var getOwnPropertyDescriptor$2 = objectGetOwnPropertyDescriptor.f;
	var defineProperty = objectDefineProperty.f;

	var trim = stringTrim.trim;

	var NUMBER = 'Number';
	var NativeNumber = global_1[NUMBER];
	var NumberPrototype = NativeNumber.prototype;
	var TypeError$1 = global_1.TypeError;
	var arraySlice = functionUncurryThis(''.slice);
	var charCodeAt = functionUncurryThis(''.charCodeAt);

	// `ToNumeric` abstract operation
	// https://tc39.es/ecma262/#sec-tonumeric
	var toNumeric = function(value) {
		var primValue = toPrimitive(value, 'number');
		return typeof primValue == 'bigint' ? primValue : toNumber(primValue);
	};

	// `ToNumber` abstract operation
	// https://tc39.es/ecma262/#sec-tonumber
	var toNumber = function(argument) {
		var it = toPrimitive(argument, 'number');
		var first, third, radix, maxCode, digits, length, index, code;
		if (isSymbol(it)) throw TypeError$1('Cannot convert a Symbol value to a number');
		if (typeof it == 'string' && it.length > 2) {
			it = trim(it);
			first = charCodeAt(it, 0);
			if (first === 43 || first === 45) {
				third = charCodeAt(it, 2);
				if (third === 88 || third === 120) return NaN; // Number('+0x1') should be NaN, old V8 fix
			} else if (first === 48) {
				switch (charCodeAt(it, 1)) {
					case 66:
					case 98:
						radix = 2;
						maxCode = 49;
						break; // fast equal of /^0b[01]+$/i
					case 79:
					case 111:
						radix = 8;
						maxCode = 55;
						break; // fast equal of /^0o[0-7]+$/i
					default:
						return +it;
				}
				digits = arraySlice(it, 2);
				length = digits.length;
				for (index = 0; index < length; index++) {
					code = charCodeAt(digits, index);
					// parseInt parses a string to a first unavailable symbol
					// but ToNumber should return NaN if a string contains unavailable symbols
					if (code < 48 || code > maxCode) return NaN;
				}
				return parseInt(digits, radix);
			}
		}
		return +it;
	};

	// `Number` constructor
	// https://tc39.es/ecma262/#sec-number-constructor
	if (isForced_1(NUMBER, !NativeNumber(' 0o1') || !NativeNumber('0b1') || NativeNumber('+0x1'))) {
		var NumberWrapper = function Number(value) {
			var n = arguments.length < 1 ? 0 : NativeNumber(toNumeric(value));
			var dummy = this;
			// check on 1..constructor(foo) case
			return objectIsPrototypeOf(NumberPrototype, dummy) && fails(function() {
					thisNumberValue(dummy);
				}) ?
				inheritIfRequired(Object(n), dummy, NumberWrapper) : n;
		};
		for (var keys = descriptors ? getOwnPropertyNames(NativeNumber) : (
				// ES3:
				'MAX_VALUE,MIN_VALUE,NaN,NEGATIVE_INFINITY,POSITIVE_INFINITY,' +
				// ES2015 (in case, if modules with ES2015 Number statics required before):
				'EPSILON,MAX_SAFE_INTEGER,MIN_SAFE_INTEGER,isFinite,isInteger,isNaN,isSafeInteger,parseFloat,parseInt,' +
				// ESNext
				'fromString,range'
			).split(','), j = 0, key; keys.length > j; j++) {
			if (hasOwnProperty_1(NativeNumber, key = keys[j]) && !hasOwnProperty_1(NumberWrapper, key)) {
				defineProperty(NumberWrapper, key, getOwnPropertyDescriptor$2(NativeNumber, key));
			}
		}
		NumberWrapper.prototype = NumberPrototype;
		NumberPrototype.constructor = NumberWrapper;
		redefine(global_1, NUMBER, NumberWrapper);
	}

	var un$Reverse = functionUncurryThis([].reverse);
	var test = [1, 2];

	// `Array.prototype.reverse` method
	// https://tc39.es/ecma262/#sec-array.prototype.reverse
	// fix for Safari 12.0 bug
	// https://bugs.webkit.org/show_bug.cgi?id=188794
	_export({
		target: 'Array',
		proto: true,
		forced: String(test) === String(test.reverse())
	}, {
		reverse: function reverse() {
			// eslint-disable-next-line no-self-assign -- dirty hack
			if (isArray(this)) this.length = this.length;
			return un$Reverse(this);
		}
	});

	var FAILS_ON_PRIMITIVES = fails(function() {
		objectKeys(1);
	});

	// `Object.keys` method
	// https://tc39.es/ecma262/#sec-object.keys
	_export({
		target: 'Object',
		stat: true,
		forced: FAILS_ON_PRIMITIVES
	}, {
		keys: function keys(it) {
			return objectKeys(toObject(it));
		}
	});

	// @@match logic
	fixRegexpWellKnownSymbolLogic('match', function(MATCH, nativeMatch, maybeCallNative) {
		return [
			// `String.prototype.match` method
			// https://tc39.es/ecma262/#sec-string.prototype.match
			function match(regexp) {
				var O = requireObjectCoercible(this);
				var matcher = regexp == undefined ? undefined : getMethod(regexp, MATCH);
				return matcher ? functionCall(matcher, regexp, O) : new RegExp(regexp)[MATCH](
					toString_1(O));
			},
			// `RegExp.prototype[@@match]` method
			// https://tc39.es/ecma262/#sec-regexp.prototype-@@match
			function(string) {
				var rx = anObject(this);
				var S = toString_1(string);
				var res = maybeCallNative(nativeMatch, rx, S);

				if (res.done) return res.value;

				if (!rx.global) return regexpExecAbstract(rx, S);

				var fullUnicode = rx.unicode;
				rx.lastIndex = 0;
				var A = [];
				var n = 0;
				var result;
				while ((result = regexpExecAbstract(rx, S)) !== null) {
					var matchStr = toString_1(result[0]);
					A[n] = matchStr;
					if (matchStr === '') rx.lastIndex = advanceStringIndex(S, toLength(rx
						.lastIndex), fullUnicode);
					n++;
				}
				return n === 0 ? null : A;
			}
		];
	});

	var getOwnPropertyDescriptor$1 = objectGetOwnPropertyDescriptor.f;







	// eslint-disable-next-line es/no-string-prototype-startswith -- safe
	var un$StartsWith = functionUncurryThis(''.startsWith);
	var stringSlice = functionUncurryThis(''.slice);
	var min$1 = Math.min;

	var CORRECT_IS_REGEXP_LOGIC$1 = correctIsRegexpLogic('startsWith');
	// https://github.com/zloirock/core-js/pull/702
	var MDN_POLYFILL_BUG$1 = !CORRECT_IS_REGEXP_LOGIC$1 && !! function() {
		var descriptor = getOwnPropertyDescriptor$1(String.prototype, 'startsWith');
		return descriptor && !descriptor.writable;
	}();

	// `String.prototype.startsWith` method
	// https://tc39.es/ecma262/#sec-string.prototype.startswith
	_export({
		target: 'String',
		proto: true,
		forced: !MDN_POLYFILL_BUG$1 && !CORRECT_IS_REGEXP_LOGIC$1
	}, {
		startsWith: function startsWith(searchString /* , position = 0 */ ) {
			var that = toString_1(requireObjectCoercible(this));
			notARegexp(searchString);
			var index = toLength(min$1(arguments.length > 1 ? arguments[1] : undefined, that
				.length));
			var search = toString_1(searchString);
			return un$StartsWith ?
				un$StartsWith(that, search, index) :
				stringSlice(that, index, index + search.length) === search;
		}
	});

	var getOwnPropertyDescriptor = objectGetOwnPropertyDescriptor.f;







	// eslint-disable-next-line es/no-string-prototype-endswith -- safe
	var un$EndsWith = functionUncurryThis(''.endsWith);
	var slice = functionUncurryThis(''.slice);
	var min = Math.min;

	var CORRECT_IS_REGEXP_LOGIC = correctIsRegexpLogic('endsWith');
	// https://github.com/zloirock/core-js/pull/702
	var MDN_POLYFILL_BUG = !CORRECT_IS_REGEXP_LOGIC && !! function() {
		var descriptor = getOwnPropertyDescriptor(String.prototype, 'endsWith');
		return descriptor && !descriptor.writable;
	}();

	// `String.prototype.endsWith` method
	// https://tc39.es/ecma262/#sec-string.prototype.endswith
	_export({
		target: 'String',
		proto: true,
		forced: !MDN_POLYFILL_BUG && !CORRECT_IS_REGEXP_LOGIC
	}, {
		endsWith: function endsWith(searchString /* , endPosition = @length */ ) {
			var that = toString_1(requireObjectCoercible(this));
			notARegexp(searchString);
			var endPosition = arguments.length > 1 ? arguments[1] : undefined;
			var len = that.length;
			var end = endPosition === undefined ? len : min(toLength(endPosition), len);
			var search = toString_1(searchString);
			return un$EndsWith ?
				un$EndsWith(that, search, end) :
				slice(that, end - search.length, end) === search;
		}
	});

	var Utils = {
		getBootstrapVersion: function getBootstrapVersion() {
			var bootstrapVersion = 5;

			try {
				var rawVersion = $__default["default"].fn.dropdown.Constructor
					.VERSION; // Only try to parse VERSION if it is defined.
				// It is undefined in older versions of Bootstrap (tested with 3.1.1).

				if (rawVersion !== undefined) {
					bootstrapVersion = parseInt(rawVersion, 10);
				}
			} catch (e) { // ignore
			}

			try {
				// eslint-disable-next-line no-undef
				var _rawVersion = bootstrap.Tooltip.VERSION;

				if (_rawVersion !== undefined) {
					bootstrapVersion = parseInt(_rawVersion, 10);
				}
			} catch (e) { // ignore
			}

			return bootstrapVersion;
		},
		getIconsPrefix: function getIconsPrefix(theme) {
			return {
				bootstrap3: 'glyphicon',
				bootstrap4: 'fa',
				bootstrap5: 'bi',
				'bootstrap-table': 'icon',
				bulma: 'fa',
				foundation: 'fa',
				materialize: 'material-icons',
				semantic: 'fa'
			} [theme] || 'fa';
		},
		getIcons: function getIcons(prefix) {
			return {
				glyphicon: {
					paginationSwitchDown: 'glyphicon-collapse-down icon-chevron-down',
					paginationSwitchUp: 'glyphicon-collapse-up icon-chevron-up',
					refresh: 'glyphicon-refresh icon-refresh',
					toggleOff: 'glyphicon-list-alt icon-list-alt',
					toggleOn: 'glyphicon-list-alt icon-list-alt',
					columns: 'glyphicon-th icon-th',
					detailOpen: 'glyphicon-plus icon-plus',
					detailClose: 'glyphicon-minus icon-minus',
					fullscreen: 'glyphicon-fullscreen',
					search: 'glyphicon-search',
					clearSearch: 'glyphicon-trash'
				},
				fa: {
					paginationSwitchDown: 'fa-caret-square-down',
					paginationSwitchUp: 'fa-caret-square-up',
					refresh: 'fa-sync',
					toggleOff: 'fa-toggle-off',
					toggleOn: 'fa-toggle-on',
					columns: 'fa-th-list',
					detailOpen: 'fa-plus',
					detailClose: 'fa-minus',
					fullscreen: 'fa-arrows-alt',
					search: 'fa-search',
					clearSearch: 'fa-trash'
				},
				bi: {
					paginationSwitchDown: 'bi-caret-down-square',
					paginationSwitchUp: 'bi-caret-up-square',
					refresh: 'bi-arrow-clockwise',
					toggleOff: 'bi-toggle-off',
					toggleOn: 'bi-toggle-on',
					columns: 'bi-list-ul',
					detailOpen: 'bi-plus',
					detailClose: 'bi-dash',
					fullscreen: 'bi-arrows-move',
					search: 'bi-search',
					clearSearch: 'bi-trash'
				},
				icon: {
					paginationSwitchDown: 'icon-arrow-up-circle',
					paginationSwitchUp: 'icon-arrow-down-circle',
					refresh: 'icon-refresh-cw',
					toggleOff: 'icon-toggle-right',
					toggleOn: 'icon-toggle-right',
					columns: 'icon-list',
					detailOpen: 'icon-plus',
					detailClose: 'icon-minus',
					fullscreen: 'icon-maximize',
					search: 'icon-search',
					clearSearch: 'icon-trash-2'
				},
				'material-icons': {
					paginationSwitchDown: 'grid_on',
					paginationSwitchUp: 'grid_off',
					refresh: 'refresh',
					toggleOff: 'tablet',
					toggleOn: 'tablet_android',
					columns: 'view_list',
					detailOpen: 'add',
					detailClose: 'remove',
					fullscreen: 'fullscreen',
					sort: 'sort',
					search: 'search',
					clearSearch: 'delete'
				}
			} [prefix];
		},
		getSearchInput: function getSearchInput(that) {
			if (typeof that.options.searchSelector === 'string') {
				return $__default["default"](that.options.searchSelector);
			}

			return that.$toolbar.find('.search input');
		},
		// it only does '%s', and return '' when arguments are undefined
		sprintf: function sprintf(_str) {
			for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key =
					1; _key < _len; _key++) {
				args[_key - 1] = arguments[_key];
			}

			var flag = true;
			var i = 0;

			var str = _str.replace(/%s/g, function() {
				var arg = args[i++];

				if (typeof arg === 'undefined') {
					flag = false;
					return '';
				}

				return arg;
			});

			return flag ? str : '';
		},
		isObject: function isObject(val) {
			return val instanceof Object && !Array.isArray(val);
		},
		isEmptyObject: function isEmptyObject() {
			var obj = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
			return Object.entries(obj).length === 0 && obj.constructor === Object;
		},
		isNumeric: function isNumeric(n) {
			return !isNaN(parseFloat(n)) && isFinite(n);
		},
		getFieldTitle: function getFieldTitle(list, value) {
			var _iterator = _createForOfIteratorHelper(list),
				_step;

			try {
				for (_iterator.s(); !(_step = _iterator.n()).done;) {
					var item = _step.value;

					if (item.field === value) {
						return item.title;
					}
				}
			} catch (err) {
				_iterator.e(err);
			} finally {
				_iterator.f();
			}

			return '';
		},
		setFieldIndex: function setFieldIndex(columns) {
			var totalCol = 0;
			var flag = [];

			var _iterator2 = _createForOfIteratorHelper(columns[0]),
				_step2;

			try {
				for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
					var column = _step2.value;
					totalCol += column.colspan || 1;
				}
			} catch (err) {
				_iterator2.e(err);
			} finally {
				_iterator2.f();
			}

			for (var i = 0; i < columns.length; i++) {
				flag[i] = [];

				for (var j = 0; j < totalCol; j++) {
					flag[i][j] = false;
				}
			}

			for (var _i = 0; _i < columns.length; _i++) {
				var _iterator3 = _createForOfIteratorHelper(columns[_i]),
					_step3;

				try {
					for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
						var r = _step3.value;
						var rowspan = r.rowspan || 1;
						var colspan = r.colspan || 1;

						var index = flag[_i].indexOf(false);

						r.colspanIndex = index;

						if (colspan === 1) {
							r.fieldIndex = index; // when field is undefined, use index instead

							if (typeof r.field === 'undefined') {
								r.field = index;
							}
						} else {
							r.colspanGroup = r.colspan;
						}

						for (var _j = 0; _j < rowspan; _j++) {
							for (var k = 0; k < colspan; k++) {
								flag[_i + _j][index + k] = true;
							}
						}
					}
				} catch (err) {
					_iterator3.e(err);
				} finally {
					_iterator3.f();
				}
			}
		},
		normalizeAccent: function normalizeAccent(value) {
			if (typeof value !== 'string') {
				return value;
			}

			return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
		},
		updateFieldGroup: function updateFieldGroup(columns) {
			var _ref;

			var allColumns = (_ref = []).concat.apply(_ref, _toConsumableArray(columns));

			var _iterator4 = _createForOfIteratorHelper(columns),
				_step4;

			try {
				for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
					var c = _step4.value;

					var _iterator5 = _createForOfIteratorHelper(c),
						_step5;

					try {
						for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
							var r = _step5.value;

							if (r.colspanGroup > 1) {
								var colspan = 0;

								var _loop = function _loop(i) {
									var column = allColumns.find(function(col) {
										return col.fieldIndex === i;
									});

									if (column.visible) {
										colspan++;
									}
								};

								for (var i = r.colspanIndex; i < r.colspanIndex + r.colspanGroup; i++) {
									_loop(i);
								}

								r.colspan = colspan;
								r.visible = colspan > 0;
							}
						}
					} catch (err) {
						_iterator5.e(err);
					} finally {
						_iterator5.f();
					}
				}
			} catch (err) {
				_iterator4.e(err);
			} finally {
				_iterator4.f();
			}
		},
		getScrollBarWidth: function getScrollBarWidth() {
			if (this.cachedWidth === undefined) {
				var $inner = $__default["default"]('<div/>').addClass('fixed-table-scroll-inner');
				var $outer = $__default["default"]('<div/>').addClass('fixed-table-scroll-outer');
				$outer.append($inner);
				$__default["default"]('body').append($outer);
				var w1 = $inner[0].offsetWidth;
				$outer.css('overflow', 'scroll');
				var w2 = $inner[0].offsetWidth;

				if (w1 === w2) {
					w2 = $outer[0].clientWidth;
				}

				$outer.remove();
				this.cachedWidth = w1 - w2;
			}

			return this.cachedWidth;
		},
		calculateObjectValue: function calculateObjectValue(self, name, args, defaultValue) {
			var func = name;

			if (typeof name === 'string') {
				// support obj.func1.func2
				var names = name.split('.');

				if (names.length > 1) {
					func = window;

					var _iterator6 = _createForOfIteratorHelper(names),
						_step6;

					try {
						for (_iterator6.s(); !(_step6 = _iterator6.n()).done;) {
							var f = _step6.value;
							func = func[f];
						}
					} catch (err) {
						_iterator6.e(err);
					} finally {
						_iterator6.f();
					}
				} else {
					func = window[name];
				}
			}

			if (func !== null && _typeof(func) === 'object') {
				return func;
			}

			if (typeof func === 'function') {
				return func.apply(self, args || []);
			}

			if (!func && typeof name === 'string' && args && this.sprintf.apply(this, [name].concat(
					_toConsumableArray(args)))) {
				return this.sprintf.apply(this, [name].concat(_toConsumableArray(args)));
			}

			return defaultValue;
		},
		compareObjects: function compareObjects(objectA, objectB, compareLength) {
			var aKeys = Object.keys(objectA);
			var bKeys = Object.keys(objectB);

			if (compareLength && aKeys.length !== bKeys.length) {
				return false;
			}

			for (var _i2 = 0, _aKeys = aKeys; _i2 < _aKeys.length; _i2++) {
				var key = _aKeys[_i2];

				if (bKeys.includes(key) && objectA[key] !== objectB[key]) {
					return false;
				}
			}

			return true;
		},
		regexCompare: function regexCompare(value, search) {
			try {
				var regexpParts = search.match(/^\/(.*?)\/([gim]*)$/);

				if (value.toString().search(regexpParts ? new RegExp(regexpParts[1], regexpParts[2]) :
						new RegExp(search, 'gim')) !== -1) {
					return true;
				}
			} catch (e) {
				return false;
			}
		},
		escapeHTML: function escapeHTML(text) {
			if (!text) {
				return text;
			}

			return text.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
		},
		unescapeHTML: function unescapeHTML(text) {
			if (typeof text !== 'string' || !text) {
				return text;
			}

			return text.toString().replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
				.replace(/&quot;/g, '"').replace(/&#39;/g, '\'');
		},
		removeHTML: function removeHTML(text) {
			if (!text) {
				return text;
			}

			return text.toString().replace(/(<([^>]+)>)/ig, '').replace(/&[#A-Za-z0-9]+;/gi, '').trim();
		},
		getRealDataAttr: function getRealDataAttr(dataAttr) {
			for (var _i3 = 0, _Object$entries = Object.entries(dataAttr); _i3 < _Object$entries
				.length; _i3++) {
				var _Object$entries$_i = _slicedToArray(_Object$entries[_i3], 2),
					attr = _Object$entries$_i[0],
					value = _Object$entries$_i[1];

				var auxAttr = attr.split(/(?=[A-Z])/).join('-').toLowerCase();

				if (auxAttr !== attr) {
					dataAttr[auxAttr] = value;
					delete dataAttr[attr];
				}
			}

			return dataAttr;
		},
		getItemField: function getItemField(item, field, escape) {
			var columnEscape = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] :
				undefined;
			var value = item; // use column escape if it is defined

			if (typeof columnEscape !== 'undefined') {
				escape = columnEscape;
			}

			if (typeof field !== 'string' || item.hasOwnProperty(field)) {
				return escape ? this.escapeHTML(item[field]) : item[field];
			}

			var props = field.split('.');

			var _iterator7 = _createForOfIteratorHelper(props),
				_step7;

			try {
				for (_iterator7.s(); !(_step7 = _iterator7.n()).done;) {
					var p = _step7.value;
					value = value && value[p];
				}
			} catch (err) {
				_iterator7.e(err);
			} finally {
				_iterator7.f();
			}

			return escape ? this.escapeHTML(value) : value;
		},
		isIEBrowser: function isIEBrowser() {
			return navigator.userAgent.includes('MSIE ') || /Trident.*rv:11\./.test(navigator
				.userAgent);
		},
		findIndex: function findIndex(items, item) {
			var _iterator8 = _createForOfIteratorHelper(items),
				_step8;

			try {
				for (_iterator8.s(); !(_step8 = _iterator8.n()).done;) {
					var it = _step8.value;

					if (JSON.stringify(it) === JSON.stringify(item)) {
						return items.indexOf(it);
					}
				}
			} catch (err) {
				_iterator8.e(err);
			} finally {
				_iterator8.f();
			}

			return -1;
		},
		trToData: function trToData(columns, $els) {
			var _this = this;

			var data = [];
			var m = [];
			$els.each(function(y, el) {
				var $el = $__default["default"](el);
				var row = {}; // save tr's id, class and data-* attributes

				row._id = $el.attr('id');
				row._class = $el.attr('class');
				row._data = _this.getRealDataAttr($el.data());
				row._style = $el.attr('style');
				$el.find('>td,>th').each(function(_x, el) {
					var $el = $__default["default"](el);
					var cspan = +$el.attr('colspan') || 1;
					var rspan = +$el.attr('rowspan') || 1;
					var x = _x; // skip already occupied cells in current row

					for (; m[y] && m[y][x]; x++) { // ignore
					} // mark matrix elements occupied by current cell with true


					for (var tx = x; tx < x + cspan; tx++) {
						for (var ty = y; ty < y + rspan; ty++) {
							if (!m[ty]) {
								// fill missing rows
								m[ty] = [];
							}

							m[ty][tx] = true;
						}
					}

					var field = columns[x].field;
					row[field] = $el.html()
						.trim(); // save td's id, class and data-* attributes

					row["_".concat(field, "_id")] = $el.attr('id');
					row["_".concat(field, "_class")] = $el.attr('class');
					row["_".concat(field, "_rowspan")] = $el.attr('rowspan');
					row["_".concat(field, "_colspan")] = $el.attr('colspan');
					row["_".concat(field, "_title")] = $el.attr('title');
					row["_".concat(field, "_data")] = _this.getRealDataAttr($el.data());
					row["_".concat(field, "_style")] = $el.attr('style');
				});
				data.push(row);
			});
			return data;
		},
		sort: function sort(a, b, order, sortStable, aPosition, bPosition) {
			if (a === undefined || a === null) {
				a = '';
			}

			if (b === undefined || b === null) {
				b = '';
			}

			if (sortStable && a === b) {
				a = aPosition;
				b = bPosition;
			} // If both values are numeric, do a numeric comparison


			if (this.isNumeric(a) && this.isNumeric(b)) {
				// Convert numerical values form string to float.
				a = parseFloat(a);
				b = parseFloat(b);

				if (a < b) {
					return order * -1;
				}

				if (a > b) {
					return order;
				}

				return 0;
			}

			if (a === b) {
				return 0;
			} // If value is not a string, convert to string


			if (typeof a !== 'string') {
				a = a.toString();
			}

			if (a.localeCompare(b) === -1) {
				return order * -1;
			}

			return order;
		},
		getEventName: function getEventName(eventPrefix) {
			var id = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
			id = id || "".concat(+new Date()).concat(~~(Math.random() * 1000000));
			return "".concat(eventPrefix, "-").concat(id);
		},
		hasDetailViewIcon: function hasDetailViewIcon(options) {
			return options.detailView && options.detailViewIcon && !options.cardView;
		},
		getDetailViewIndexOffset: function getDetailViewIndexOffset(options) {
			return this.hasDetailViewIcon(options) && options.detailViewAlign !== 'right' ? 1 : 0;
		},
		checkAutoMergeCells: function checkAutoMergeCells(data) {
			var _iterator9 = _createForOfIteratorHelper(data),
				_step9;

			try {
				for (_iterator9.s(); !(_step9 = _iterator9.n()).done;) {
					var row = _step9.value;

					for (var _i4 = 0, _Object$keys = Object.keys(row); _i4 < _Object$keys
						.length; _i4++) {
						var key = _Object$keys[_i4];

						if (key.startsWith('_') && (key.endsWith('_rowspan') || key.endsWith(
								'_colspan'))) {
							return true;
						}
					}
				}
			} catch (err) {
				_iterator9.e(err);
			} finally {
				_iterator9.f();
			}

			return false;
		},
		deepCopy: function deepCopy(arg) {
			if (arg === undefined) {
				return arg;
			}

			return $__default["default"].extend(true, Array.isArray(arg) ? [] : {}, arg);
		},
		debounce: function debounce(func, wait, immediate) {
			var timeout;
			return function executedFunction() {
				var context = this;
				var args = arguments;

				var later = function later() {
					timeout = null;
					if (!immediate) func.apply(context, args);
				};

				var callNow = immediate && !timeout;
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
				if (callNow) func.apply(context, args);
			};
		}
	};

	var VERSION = '1.20.0';
	var bootstrapVersion = Utils.getBootstrapVersion();
	var CONSTANTS = {
		3: {
			classes: {
				buttonsPrefix: 'btn',
				buttons: 'default',
				buttonsGroup: 'btn-group',
				buttonsDropdown: 'btn-group',
				pull: 'pull',
				inputGroup: 'input-group',
				inputPrefix: 'input-',
				input: 'form-control',
				select: 'form-control',
				paginationDropdown: 'btn-group dropdown',
				dropup: 'dropup',
				dropdownActive: 'active',
				paginationActive: 'active',
				buttonActive: 'active'
			},
			html: {
				toolbarDropdown: ['<ul class="dropdown-menu" role="menu">', '</ul>'],
				toolbarDropdownItem: '<li class="dropdown-item-marker" role="menuitem"><label>%s</label></li>',
				toolbarDropdownSeparator: '<li class="divider"></li>',
				pageDropdown: ['<ul class="dropdown-menu" role="menu">', '</ul>'],
				pageDropdownItem: '<li role="menuitem" class="%s"><a href="#">%s</a></li>',
				dropdownCaret: '<span class="caret"></span>',
				pagination: ['<ul class="pagination%s">', '</ul>'],
				paginationItem: '<li class="page-item%s"><a class="page-link" aria-label="%s" href="javascript:void(0)">%s</a></li>',
				icon: '<i class="%s %s"></i>',
				inputGroup: '<div class="input-group">%s<span class="input-group-btn">%s</span></div>',
				searchInput: '<input class="%s%s" type="text" placeholder="%s">',
				searchButton: '<button class="%s" type="button" name="search" title="%s">%s %s</button>',
				searchClearButton: '<button class="%s" type="button" name="clearSearch" title="%s">%s %s</button>'
			}
		},
		4: {
			classes: {
				buttonsPrefix: 'btn',
				buttons: 'secondary',
				buttonsGroup: 'btn-group',
				buttonsDropdown: 'btn-group',
				pull: 'float',
				inputGroup: 'btn-group',
				inputPrefix: 'form-control-',
				input: 'form-control',
				select: 'form-control',
				paginationDropdown: 'btn-group dropdown',
				dropup: 'dropup',
				dropdownActive: 'active',
				paginationActive: 'active',
				buttonActive: 'active'
			},
			html: {
				toolbarDropdown: ['<div class="dropdown-menu dropdown-menu-right">', '</div>'],
				toolbarDropdownItem: '<label class="dropdown-item dropdown-item-marker">%s</label>',
				pageDropdown: ['<div class="dropdown-menu">', '</div>'],
				pageDropdownItem: '<a class="dropdown-item %s" href="#">%s</a>',
				toolbarDropdownSeparator: '<div class="dropdown-divider"></div>',
				dropdownCaret: '<span class="caret"></span>',
				pagination: ['<ul class="pagination%s">', '</ul>'],
				paginationItem: '<li class="page-item%s"><a class="page-link" aria-label="%s" href="javascript:void(0)">%s</a></li>',
				icon: '<i class="%s %s"></i>',
				inputGroup: '<div class="input-group">%s<div class="input-group-append">%s</div></div>',
				searchInput: '<input class="%s%s" type="text" placeholder="%s">',
				searchButton: '<button class="%s" type="button" name="search" title="%s">%s %s</button>',
				searchClearButton: '<button class="%s" type="button" name="clearSearch" title="%s">%s %s</button>'
			}
		},
		5: {
			classes: {
				buttonsPrefix: 'btn',
				buttons: 'secondary',
				buttonsGroup: 'btn-group',
				buttonsDropdown: 'btn-group',
				pull: 'float',
				inputGroup: 'btn-group',
				inputPrefix: 'form-control-',
				input: 'form-control',
				select: 'form-select',
				paginationDropdown: 'btn-group dropdown',
				dropup: 'dropup',
				dropdownActive: 'active',
				paginationActive: 'active',
				buttonActive: 'active'
			},
			html: {
				dataToggle: 'data-bs-toggle',
				toolbarDropdown: ['<div class="dropdown-menu dropdown-menu-right">', '</div>'],
				toolbarDropdownItem: '<label class="dropdown-item dropdown-item-marker">%s</label>',
				pageDropdown: ['<div class="dropdown-menu">', '</div>'],
				pageDropdownItem: '<a class="dropdown-item %s" href="#">%s</a>',
				toolbarDropdownSeparator: '<div class="dropdown-divider"></div>',
				dropdownCaret: '<span class="caret"></span>',
				pagination: ['<ul class="pagination%s">', '</ul>'],
				paginationItem: '<li class="page-item%s"><a class="page-link" aria-label="%s" href="javascript:void(0)">%s</a></li>',
				icon: '<i class="%s %s"></i>',
				inputGroup: '<div class="input-group">%s%s</div>',
				searchInput: '<input class="%s%s" type="text" placeholder="%s">',
				searchButton: '<button class="%s" type="button" name="search" title="%s">%s %s</button>',
				searchClearButton: '<button class="%s" type="button" name="clearSearch" title="%s">%s %s</button>'
			}
		}
	} [bootstrapVersion];
	var DEFAULTS = {
		height: undefined,
		classes: 'table table-bordered table-hover',
		buttons: {},
		theadClasses: '',
		headerStyle: function headerStyle(column) {
			return {};
		},
		rowStyle: function rowStyle(row, index) {
			return {};
		},
		rowAttributes: function rowAttributes(row, index) {
			return {};
		},
		undefinedText: '-',
		locale: undefined,
		virtualScroll: false,
		virtualScrollItemHeight: undefined,
		sortable: true,
		sortClass: undefined,
		silentSort: true,
		sortName: undefined,
		sortOrder: undefined,
		sortReset: false,
		sortStable: false,
		rememberOrder: false,
		serverSort: true,
		customSort: undefined,
		columns: [
			[]
		],
		data: [],
		url: undefined,
		method: 'get',
		cache: true,
		contentType: 'application/json',
		dataType: 'json',
		ajax: undefined,
		ajaxOptions: {},
		queryParams: function queryParams(params) {
			return params;
		},
		queryParamsType: 'limit',
		// 'limit', undefined
		responseHandler: function responseHandler(res) {
			return res;
		},
		totalField: 'total',
		totalNotFilteredField: 'totalNotFiltered',
		dataField: 'rows',
		footerField: 'footer',
		pagination: false,
		paginationParts: ['pageInfo', 'pageSize', 'pageList'],
		showExtendedPagination: false,
		paginationLoop: true,
		sidePagination: 'client',
		// client or server
		totalRows: 0,
		totalNotFiltered: 0,
		pageNumber: 1,
		pageSize: 10,
		pageList: [10, 25, 50, 100],
		paginationHAlign: 'right',
		// right, left
		paginationVAlign: 'bottom',
		// bottom, top, both
		paginationDetailHAlign: 'left',
		// right, left
		paginationPreText: '&lsaquo;',
		paginationNextText: '&rsaquo;',
		paginationSuccessivelySize: 5,
		// Maximum successively number of pages in a row
		paginationPagesBySide: 1,
		// Number of pages on each side (right, left) of the current page.
		paginationUseIntermediate: false,
		// Calculate intermediate pages for quick access
		search: false,
		searchHighlight: false,
		searchOnEnterKey: false,
		strictSearch: false,
		regexSearch: false,
		searchSelector: false,
		visibleSearch: false,
		showButtonIcons: true,
		showButtonText: false,
		showSearchButton: false,
		showSearchClearButton: false,
		trimOnSearch: true,
		searchAlign: 'right',
		searchTimeOut: 500,
		searchText: '',
		customSearch: undefined,
		showHeader: true,
		showFooter: false,
		footerStyle: function footerStyle(column) {
			return {};
		},
		searchAccentNeutralise: false,
		showColumns: false,
		showColumnsToggleAll: false,
		showColumnsSearch: false,
		minimumCountColumns: 1,
		showPaginationSwitch: false,
		showRefresh: false,
		showToggle: false,
		showFullscreen: false,
		smartDisplay: true,
		escape: false,
		filterOptions: {
			filterAlgorithm: 'and'
		},
		idField: undefined,
		selectItemName: 'btSelectItem',
		clickToSelect: false,
		ignoreClickToSelectOn: function ignoreClickToSelectOn(_ref) {
			var tagName = _ref.tagName;
			return ['A', 'BUTTON'].includes(tagName);
		},
		singleSelect: false,
		checkboxHeader: true,
		maintainMetaData: false,
		multipleSelectRow: false,
		uniqueId: undefined,
		cardView: false,
		detailView: false,
		detailViewIcon: true,
		detailViewByClick: false,
		detailViewAlign: 'left',
		detailFormatter: function detailFormatter(index, row) {
			return '';
		},
		detailFilter: function detailFilter(index, row) {
			return true;
		},
		toolbar: undefined,
		toolbarAlign: 'left',
		buttonsToolbar: undefined,
		buttonsAlign: 'right',
		buttonsOrder: ['paginationSwitch', 'refresh', 'toggle', 'fullscreen', 'columns'],
		buttonsPrefix: CONSTANTS.classes.buttonsPrefix,
		buttonsClass: CONSTANTS.classes.buttons,
		iconsPrefix: undefined,
		// init in initConstants
		icons: {},
		// init in initConstants
		iconSize: undefined,
		loadingFontSize: 'auto',
		loadingTemplate: function loadingTemplate(loadingMessage) {
			return "<span class=\"loading-wrap\">\n      <span class=\"loading-text\">".concat(
				loadingMessage,
				"</span>\n      <span class=\"animation-wrap\"><span class=\"animation-dot\"></span></span>\n      </span>\n    "
			);
		},
		onAll: function onAll(name, args) {
			return false;
		},
		onClickCell: function onClickCell(field, value, row, $element) {
			return false;
		},
		onDblClickCell: function onDblClickCell(field, value, row, $element) {
			return false;
		},
		onClickRow: function onClickRow(item, $element) {
			return false;
		},
		onDblClickRow: function onDblClickRow(item, $element) {
			return false;
		},
		onSort: function onSort(name, order) {
			return false;
		},
		onCheck: function onCheck(row) {
			return false;
		},
		onUncheck: function onUncheck(row) {
			return false;
		},
		onCheckAll: function onCheckAll(rows) {
			return false;
		},
		onUncheckAll: function onUncheckAll(rows) {
			return false;
		},
		onCheckSome: function onCheckSome(rows) {
			return false;
		},
		onUncheckSome: function onUncheckSome(rows) {
			return false;
		},
		onLoadSuccess: function onLoadSuccess(data) {
			return false;
		},
		onLoadError: function onLoadError(status) {
			return false;
		},
		onColumnSwitch: function onColumnSwitch(field, checked) {
			return false;
		},
		onColumnSwitchAll: function onColumnSwitchAll(checked) {
			return false;
		},
		onPageChange: function onPageChange(number, size) {
			return false;
		},
		onSearch: function onSearch(text) {
			return false;
		},
		onToggle: function onToggle(cardView) {
			return false;
		},
		onPreBody: function onPreBody(data) {
			return false;
		},
		onPostBody: function onPostBody() {
			return false;
		},
		onPostHeader: function onPostHeader() {
			return false;
		},
		onPostFooter: function onPostFooter() {
			return false;
		},
		onExpandRow: function onExpandRow(index, row, $detail) {
			return false;
		},
		onCollapseRow: function onCollapseRow(index, row) {
			return false;
		},
		onRefreshOptions: function onRefreshOptions(options) {
			return false;
		},
		onRefresh: function onRefresh(params) {
			return false;
		},
		onResetView: function onResetView() {
			return false;
		},
		onScrollBody: function onScrollBody() {
			return false;
		},
		onTogglePagination: function onTogglePagination(newState) {
			return false;
		},
		onVirtualScroll: function onVirtualScroll(startIndex, endIndex) {
			return false;
		}
	};
	var EN = {
		formatLoadingMessage: function formatLoadingMessage() {
			return 'Loading, please wait';
		},
		formatRecordsPerPage: function formatRecordsPerPage(pageNumber) {
			return "".concat(pageNumber, " rows per page");
		},
		formatShowingRows: function formatShowingRows(pageFrom, pageTo, totalRows, totalNotFiltered) {
			if (totalNotFiltered !== undefined && totalNotFiltered > 0 && totalNotFiltered >
				totalRows) {
				return "Showing ".concat(pageFrom, " to ").concat(pageTo, " of ").concat(totalRows,
					" rows (filtered from ").concat(totalNotFiltered, " total rows)");
			}

			return "Showing ".concat(pageFrom, " to ").concat(pageTo, " of ").concat(totalRows,
				" rows");
		},
		formatSRPaginationPreText: function formatSRPaginationPreText() {
			return 'previous page';
		},
		formatSRPaginationPageText: function formatSRPaginationPageText(page) {
			return "to page ".concat(page);
		},
		formatSRPaginationNextText: function formatSRPaginationNextText() {
			return 'next page';
		},
		formatDetailPagination: function formatDetailPagination(totalRows) {
			return "Showing ".concat(totalRows, " rows");
		},
		formatSearch: function formatSearch() {
			return 'Search';
		},
		formatClearSearch: function formatClearSearch() {
			return 'Clear Search';
		},
		formatNoMatches: function formatNoMatches() {
			return 'No matching records found';
		},
		formatPaginationSwitch: function formatPaginationSwitch() {
			return 'Hide/Show pagination';
		},
		formatPaginationSwitchDown: function formatPaginationSwitchDown() {
			return 'Show pagination';
		},
		formatPaginationSwitchUp: function formatPaginationSwitchUp() {
			return 'Hide pagination';
		},
		formatRefresh: function formatRefresh() {
			return 'Refresh';
		},
		formatToggle: function formatToggle() {
			return 'Toggle';
		},
		formatToggleOn: function formatToggleOn() {
			return 'Show card view';
		},
		formatToggleOff: function formatToggleOff() {
			return 'Hide card view';
		},
		formatColumns: function formatColumns() {
			return 'Columns';
		},
		formatColumnsToggleAll: function formatColumnsToggleAll() {
			return 'Toggle all';
		},
		formatFullscreen: function formatFullscreen() {
			return 'Fullscreen';
		},
		formatAllRows: function formatAllRows() {
			return 'All';
		}
	};
	var COLUMN_DEFAULTS = {
		field: undefined,
		title: undefined,
		titleTooltip: undefined,
		class: undefined,
		width: undefined,
		widthUnit: 'px',
		rowspan: undefined,
		colspan: undefined,
		align: undefined,
		// left, right, center
		halign: undefined,
		// left, right, center
		falign: undefined,
		// left, right, center
		valign: undefined,
		// top, middle, bottom
		cellStyle: undefined,
		radio: false,
		checkbox: false,
		checkboxEnabled: true,
		clickToSelect: true,
		showSelectTitle: false,
		sortable: false,
		sortName: undefined,
		order: 'asc',
		// asc, desc
		sorter: undefined,
		visible: true,
		switchable: true,
		cardVisible: true,
		searchable: true,
		formatter: undefined,
		footerFormatter: undefined,
		detailFormatter: undefined,
		searchFormatter: true,
		searchHighlightFormatter: false,
		escape: undefined,
		events: undefined
	};
	var METHODS = ['getOptions', 'refreshOptions', 'getData', 'getSelections', 'load', 'append', 'prepend',
		'remove', 'removeAll', 'insertRow', 'updateRow', 'getRowByUniqueId', 'updateByUniqueId',
		'removeByUniqueId', 'updateCell', 'updateCellByUniqueId', 'showRow', 'hideRow', 'getHiddenRows',
		'showColumn', 'hideColumn', 'getVisibleColumns', 'getHiddenColumns', 'showAllColumns',
		'hideAllColumns', 'mergeCells', 'checkAll', 'uncheckAll', 'checkInvert', 'check', 'uncheck',
		'checkBy', 'uncheckBy', 'refresh', 'destroy', 'resetView', 'showLoading', 'hideLoading',
		'togglePagination', 'toggleFullscreen', 'toggleView', 'resetSearch', 'filterBy', 'scrollTo',
		'getScrollPosition', 'selectPage', 'prevPage', 'nextPage', 'toggleDetailView', 'expandRow',
		'collapseRow', 'expandRowByUniqueId', 'collapseRowByUniqueId', 'expandAllRows', 'collapseAllRows',
		'updateColumnTitle', 'updateFormatText'
	];
	var EVENTS = {
		'all.bs.table': 'onAll',
		'click-row.bs.table': 'onClickRow',
		'dbl-click-row.bs.table': 'onDblClickRow',
		'click-cell.bs.table': 'onClickCell',
		'dbl-click-cell.bs.table': 'onDblClickCell',
		'sort.bs.table': 'onSort',
		'check.bs.table': 'onCheck',
		'uncheck.bs.table': 'onUncheck',
		'check-all.bs.table': 'onCheckAll',
		'uncheck-all.bs.table': 'onUncheckAll',
		'check-some.bs.table': 'onCheckSome',
		'uncheck-some.bs.table': 'onUncheckSome',
		'load-success.bs.table': 'onLoadSuccess',
		'load-error.bs.table': 'onLoadError',
		'column-switch.bs.table': 'onColumnSwitch',
		'column-switch-all.bs.table': 'onColumnSwitchAll',
		'page-change.bs.table': 'onPageChange',
		'search.bs.table': 'onSearch',
		'toggle.bs.table': 'onToggle',
		'pre-body.bs.table': 'onPreBody',
		'post-body.bs.table': 'onPostBody',
		'post-header.bs.table': 'onPostHeader',
		'post-footer.bs.table': 'onPostFooter',
		'expand-row.bs.table': 'onExpandRow',
		'collapse-row.bs.table': 'onCollapseRow',
		'refresh-options.bs.table': 'onRefreshOptions',
		'reset-view.bs.table': 'onResetView',
		'refresh.bs.table': 'onRefresh',
		'scroll-body.bs.table': 'onScrollBody',
		'toggle-pagination.bs.table': 'onTogglePagination',
		'virtual-scroll.bs.table': 'onVirtualScroll'
	};
	Object.assign(DEFAULTS, EN);
	var Constants = {
		VERSION: VERSION,
		THEME: "bootstrap".concat(bootstrapVersion),
		CONSTANTS: CONSTANTS,
		DEFAULTS: DEFAULTS,
		COLUMN_DEFAULTS: COLUMN_DEFAULTS,
		METHODS: METHODS,
		EVENTS: EVENTS,
		LOCALES: {
			en: EN,
			'en-US': EN
		}
	};

	var BLOCK_ROWS = 50;
	var CLUSTER_BLOCKS = 4;

	var VirtualScroll = /*#__PURE__*/ function() {
		function VirtualScroll(options) {
			var _this = this;

			_classCallCheck(this, VirtualScroll);

			this.rows = options.rows;
			this.scrollEl = options.scrollEl;
			this.contentEl = options.contentEl;
			this.callback = options.callback;
			this.itemHeight = options.itemHeight;
			this.cache = {};
			this.scrollTop = this.scrollEl.scrollTop;
			this.initDOM(this.rows, options.fixedScroll);
			this.scrollEl.scrollTop = this.scrollTop;
			this.lastCluster = 0;

			var onScroll = function onScroll() {
				if (_this.lastCluster !== (_this.lastCluster = _this.getNum())) {
					_this.initDOM(_this.rows);

					_this.callback(_this.startIndex, _this.endIndex);
				}
			};

			this.scrollEl.addEventListener('scroll', onScroll, false);

			this.destroy = function() {
				_this.contentEl.innerHtml = '';

				_this.scrollEl.removeEventListener('scroll', onScroll, false);
			};
		}

		_createClass(VirtualScroll, [{
			key: "initDOM",
			value: function initDOM(rows, fixedScroll) {
				if (typeof this.clusterHeight === 'undefined') {
					this.cache.scrollTop = this.scrollEl.scrollTop;
					this.cache.data = this.contentEl.innerHTML = rows[0] + rows[0] + rows[
						0];
					this.getRowsHeight(rows);
				}

				var data = this.initData(rows, this.getNum(fixedScroll));
				var thisRows = data.rows.join('');
				var dataChanged = this.checkChanges('data', thisRows);
				var topOffsetChanged = this.checkChanges('top', data.topOffset);
				var bottomOffsetChanged = this.checkChanges('bottom', data.bottomOffset);
				var html = [];

				if (dataChanged && topOffsetChanged) {
					if (data.topOffset) {
						html.push(this.getExtra('top', data.topOffset));
					}

					html.push(thisRows);

					if (data.bottomOffset) {
						html.push(this.getExtra('bottom', data.bottomOffset));
					}

					this.startIndex = data.start;
					this.endIndex = data.end;
					this.contentEl.innerHTML = html.join('');

					if (fixedScroll) {
						this.contentEl.scrollTop = this.cache.scrollTop;
					}
				} else if (bottomOffsetChanged) {
					this.contentEl.lastChild.style.height = "".concat(data.bottomOffset,
						"px");
				}
			}
		}, {
			key: "getRowsHeight",
			value: function getRowsHeight() {
				if (typeof this.itemHeight === 'undefined') {
					var nodes = this.contentEl.children;
					var node = nodes[Math.floor(nodes.length / 2)];
					this.itemHeight = node.offsetHeight;
				}

				this.blockHeight = this.itemHeight * BLOCK_ROWS;
				this.clusterRows = BLOCK_ROWS * CLUSTER_BLOCKS;
				this.clusterHeight = this.blockHeight * CLUSTER_BLOCKS;
			}
		}, {
			key: "getNum",
			value: function getNum(fixedScroll) {
				this.scrollTop = fixedScroll ? this.cache.scrollTop : this.scrollEl
					.scrollTop;
				return Math.floor(this.scrollTop / (this.clusterHeight - this
					.blockHeight)) || 0;
			}
		}, {
			key: "initData",
			value: function initData(rows, num) {
				if (rows.length < BLOCK_ROWS) {
					return {
						topOffset: 0,
						bottomOffset: 0,
						rowsAbove: 0,
						rows: rows
					};
				}

				var start = Math.max((this.clusterRows - BLOCK_ROWS) * num, 0);
				var end = start + this.clusterRows;
				var topOffset = Math.max(start * this.itemHeight, 0);
				var bottomOffset = Math.max((rows.length - end) * this.itemHeight, 0);
				var thisRows = [];
				var rowsAbove = start;

				if (topOffset < 1) {
					rowsAbove++;
				}

				for (var i = start; i < end; i++) {
					rows[i] && thisRows.push(rows[i]);
				}

				return {
					start: start,
					end: end,
					topOffset: topOffset,
					bottomOffset: bottomOffset,
					rowsAbove: rowsAbove,
					rows: thisRows
				};
			}
		}, {
			key: "checkChanges",
			value: function checkChanges(type, value) {
				var changed = value !== this.cache[type];
				this.cache[type] = value;
				return changed;
			}
		}, {
			key: "getExtra",
			value: function getExtra(className, height) {
				var tag = document.createElement('tr');
				tag.className = "virtual-scroll-".concat(className);

				if (height) {
					tag.style.height = "".concat(height, "px");
				}

				return tag.outerHTML;
			}
		}]);

		return VirtualScroll;
	}();

	var BootstrapTable = /*#__PURE__*/ function() {
		function BootstrapTable(el, options) {
			_classCallCheck(this, BootstrapTable);

			this.options = options;
			this.$el = $__default["default"](el);
			this.$el_ = this.$el.clone();
			this.timeoutId_ = 0;
			this.timeoutFooter_ = 0;
		}

		_createClass(BootstrapTable, [{
			key: "init",
			value: function init() {
				this.initConstants();
				this.initLocale();
				this.initContainer();
				this.initTable();
				this.initHeader();
				this.initData();
				this.initHiddenRows();
				this.initToolbar();
				this.initPagination();
				this.initBody();
				this.initSearchText();
				this.initServer();
			}
		}, {
			key: "initConstants",
			value: function initConstants() {
				var opts = this.options;
				this.constants = Constants.CONSTANTS;
				this.constants.theme = $__default["default"].fn.bootstrapTable.theme;
				this.constants.dataToggle = this.constants.html.dataToggle ||
					'data-toggle'; // init iconsPrefix and icons

				var iconsPrefix = Utils.getIconsPrefix($__default["default"].fn
					.bootstrapTable.theme);
				var icons = Utils.getIcons(iconsPrefix);
				opts.iconsPrefix = opts.iconsPrefix || $__default["default"].fn
					.bootstrapTable.defaults.iconsPrefix || iconsPrefix;
				opts.icons = Object.assign(icons, $__default["default"].fn.bootstrapTable
					.defaults.icons, opts.icons); // init buttons class

				var buttonsPrefix = opts.buttonsPrefix ? "".concat(opts.buttonsPrefix,
					"-") : '';
				this.constants.buttonsClass = [opts.buttonsPrefix, buttonsPrefix + opts
					.buttonsClass, Utils.sprintf("".concat(buttonsPrefix, "%s"), opts
						.iconSize)
				].join(' ').trim();
				this.buttons = Utils.calculateObjectValue(this, opts.buttons, [], {});

				if (_typeof(this.buttons) !== 'object') {
					this.buttons = {};
				}

				if (typeof opts.icons === 'string') {
					opts.icons = Utils.calculateObjectValue(null, opts.icons);
				}
			}
		}, {
			key: "initLocale",
			value: function initLocale() {
				if (this.options.locale) {
					var locales = $__default["default"].fn.bootstrapTable.locales;
					var parts = this.options.locale.split(/-|_/);
					parts[0] = parts[0].toLowerCase();

					if (parts[1]) {
						parts[1] = parts[1].toUpperCase();
					}

					var localesToExtend = {};

					if (locales[this.options.locale]) {
						localesToExtend = locales[this.options.locale];
					} else if (locales[parts.join('-')]) {
						localesToExtend = locales[parts.join('-')];
					} else if (locales[parts[0]]) {
						localesToExtend = locales[parts[0]];
					}

					for (var _i = 0, _Object$entries = Object.entries(localesToExtend); _i <
						_Object$entries.length; _i++) {
						var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
							formatName = _Object$entries$_i[0],
							func = _Object$entries$_i[1];

						if (this.options[formatName] !== BootstrapTable.DEFAULTS[
								formatName]) {
							continue;
						}

						this.options[formatName] = func;
					}
				}
			}
		}, {
			key: "initContainer",
			value: function initContainer() {
				var topPagination = ['top', 'both'].includes(this.options
						.paginationVAlign) ?
					'<div class="fixed-table-pagination clearfix"></div>' : '';
				var bottomPagination = ['bottom', 'both'].includes(this.options
						.paginationVAlign) ? '<div class="fixed-table-pagination"></div>' :
					'';
				var loadingTemplate = Utils.calculateObjectValue(this.options, this.options
					.loadingTemplate, [this.options.formatLoadingMessage()]);
				this.$container = $__default["default"](
					"\n      <div class=\"bootstrap-table ".concat(this.constants.theme,
						"\">\n      <div class=\"fixed-table-toolbar\"></div>\n      ")
					.concat(topPagination,
						"\n      <div class=\"fixed-table-container\">\n      <div class=\"fixed-table-header\"><table></table></div>\n      <div class=\"fixed-table-body\">\n      <div class=\"fixed-table-loading\">\n      "
					).concat(loadingTemplate,
						"\n      </div>\n      </div>\n      <div class=\"fixed-table-footer\"></div>\n      </div>\n      "
					).concat(bottomPagination, "\n      </div>\n    "));
				this.$container.insertAfter(this.$el);
				this.$tableContainer = this.$container.find('.fixed-table-container');
				this.$tableHeader = this.$container.find('.fixed-table-header');
				this.$tableBody = this.$container.find('.fixed-table-body');
				this.$tableLoading = this.$container.find('.fixed-table-loading');
				this.$tableFooter = this.$el.find(
					'tfoot'); // checking if custom table-toolbar exists or not

				if (this.options.buttonsToolbar) {
					this.$toolbar = $__default["default"]('body').find(this.options
						.buttonsToolbar);
				} else {
					this.$toolbar = this.$container.find('.fixed-table-toolbar');
				}

				this.$pagination = this.$container.find('.fixed-table-pagination');
				this.$tableBody.append(this.$el);
				this.$container.after('<div class="clearfix"></div>');
				this.$el.addClass(this.options.classes);
				this.$tableLoading.addClass(this.options.classes);

				if (this.options.height) {
					this.$tableContainer.addClass('fixed-height');

					if (this.options.showFooter) {
						this.$tableContainer.addClass('has-footer');
					}

					if (this.options.classes.split(' ').includes('table-bordered')) {
						this.$tableBody.append('<div class="fixed-table-border"></div>');
						this.$tableBorder = this.$tableBody.find('.fixed-table-border');
						this.$tableLoading.addClass('fixed-table-border');
					}

					this.$tableFooter = this.$container.find('.fixed-table-footer');
				}
			}
		}, {
			key: "initTable",
			value: function initTable() {
				var _this = this;

				var columns = [];
				this.$header = this.$el.find('>thead');

				if (!this.$header.length) {
					this.$header = $__default["default"]("<thead class=\"".concat(this
						.options.theadClasses, "\"></thead>")).appendTo(this.$el);
				} else if (this.options.theadClasses) {
					this.$header.addClass(this.options.theadClasses);
				}

				this._headerTrClasses = [];
				this._headerTrStyles = [];
				this.$header.find('tr').each(function(i, el) {
					var $tr = $__default["default"](el);
					var column = [];
					$tr.find('th').each(function(i, el) {
						var $th = $__default["default"](
							el
							); // #2014: getFieldIndex and elsewhere assume this is string, causes issues if not

						if (typeof $th.data('field') !== 'undefined') {
							$th.data('field', "".concat($th.data('field')));
						}

						column.push($__default["default"].extend({}, {
							title: $th.html(),
							class: $th.attr('class'),
							titleTooltip: $th.attr('title'),
							rowspan: $th.attr('rowspan') ? +$th
								.attr('rowspan') : undefined,
							colspan: $th.attr('colspan') ? +$th
								.attr('colspan') : undefined
						}, $th.data()));
					});
					columns.push(column);

					if ($tr.attr('class')) {
						_this._headerTrClasses.push($tr.attr('class'));
					}

					if ($tr.attr('style')) {
						_this._headerTrStyles.push($tr.attr('style'));
					}
				});

				if (!Array.isArray(this.options.columns[0])) {
					this.options.columns = [this.options.columns];
				}

				this.options.columns = $__default["default"].extend(true, [], columns, this
					.options.columns);
				this.columns = [];
				this.fieldsColumnsIndex = [];
				Utils.setFieldIndex(this.options.columns);
				this.options.columns.forEach(function(columns, i) {
					columns.forEach(function(_column, j) {
						var column = $__default["default"].extend({},
							BootstrapTable.COLUMN_DEFAULTS, _column);

						if (typeof column.fieldIndex !== 'undefined') {
							_this.columns[column.fieldIndex] = column;
							_this.fieldsColumnsIndex[column.field] = column
								.fieldIndex;
						}

						_this.options.columns[i][j] = column;
					});
				}); // if options.data is setting, do not process tbody and tfoot data

				if (!this.options.data.length) {
					var htmlData = Utils.trToData(this.columns, this.$el.find('>tbody>tr'));

					if (htmlData.length) {
						this.options.data = htmlData;
						this.fromHtml = true;
					}
				}

				if (!(this.options.pagination && this.options.sidePagination !==
						'server')) {
					this.footerData = Utils.trToData(this.columns, this.$el.find(
						'>tfoot>tr'));
				}

				if (this.footerData) {
					this.$el.find('tfoot').html('<tr></tr>');
				}

				if (!this.options.showFooter || this.options.cardView) {
					this.$tableFooter.hide();
				} else {
					this.$tableFooter.show();
				}
			}
		}, {
			key: "initHeader",
			value: function initHeader() {
				var _this2 = this;

				var visibleColumns = {};
				var headerHtml = [];
				this.header = {
					fields: [],
					styles: [],
					classes: [],
					formatters: [],
					detailFormatters: [],
					events: [],
					sorters: [],
					sortNames: [],
					cellStyles: [],
					searchables: []
				};
				Utils.updateFieldGroup(this.options.columns);
				this.options.columns.forEach(function(columns, i) {
					var html = [];
					html.push("<tr".concat(Utils.sprintf(' class="%s"', _this2
						._headerTrClasses[i]), " ").concat(Utils.sprintf(
						' style="%s"', _this2._headerTrStyles[i]), ">"));
					var detailViewTemplate = '';

					if (i === 0 && Utils.hasDetailViewIcon(_this2.options)) {
						var rowspan = _this2.options.columns.length > 1 ?
							" rowspan=\"".concat(_this2.options.columns.length,
								"\"") : '';
						detailViewTemplate = "<th class=\"detail\"".concat(rowspan,
							">\n          <div class=\"fht-cell\"></div>\n          </th>"
						);
					}

					if (detailViewTemplate && _this2.options.detailViewAlign !==
						'right') {
						html.push(detailViewTemplate);
					}

					columns.forEach(function(column, j) {
						var class_ = Utils.sprintf(' class="%s"', column[
							'class']);
						var unitWidth = column.widthUnit;
						var width = parseFloat(column.width);
						var halign = Utils.sprintf('text-align: %s; ',
							column.halign ? column.halign : column.align
						);
						var align = Utils.sprintf('text-align: %s; ', column
							.align);
						var style = Utils.sprintf('vertical-align: %s; ',
							column.valign);
						style += Utils.sprintf('width: %s; ', (column
								.checkbox || column.radio) && !width ? !
							column.showSelectTitle ? '36px' :
							undefined : width ? width + unitWidth :
							undefined);

						if (typeof column.fieldIndex === 'undefined' && !
							column.visible) {
							return;
						}

						var headerStyle = Utils.calculateObjectValue(null,
							_this2.options.headerStyle, [column]);
						var csses = [];
						var classes = '';

						if (headerStyle && headerStyle.css) {
							for (var _i2 = 0, _Object$entries2 = Object
									.entries(headerStyle.css); _i2 <
								_Object$entries2.length; _i2++) {
								var _Object$entries2$_i = _slicedToArray(
										_Object$entries2[_i2], 2),
									key = _Object$entries2$_i[0],
									value = _Object$entries2$_i[1];

								csses.push("".concat(key, ": ").concat(
									value));
							}
						}

						if (headerStyle && headerStyle.classes) {
							classes = Utils.sprintf(' class="%s"', column[
								'class'] ? [column['class'],
								headerStyle.classes
							].join(' ') : headerStyle.classes);
						}

						if (typeof column.fieldIndex !== 'undefined') {
							_this2.header.fields[column.fieldIndex] = column
								.field;
							_this2.header.styles[column.fieldIndex] =
								align + style;
							_this2.header.classes[column.fieldIndex] =
								class_;
							_this2.header.formatters[column.fieldIndex] =
								column.formatter;
							_this2.header.detailFormatters[column
								.fieldIndex] = column.detailFormatter;
							_this2.header.events[column.fieldIndex] = column
								.events;
							_this2.header.sorters[column.fieldIndex] =
								column.sorter;
							_this2.header.sortNames[column.fieldIndex] =
								column.sortName;
							_this2.header.cellStyles[column.fieldIndex] =
								column.cellStyle;
							_this2.header.searchables[column.fieldIndex] =
								column.searchable;

							if (!column.visible) {
								return;
							}

							if (_this2.options.cardView && !column
								.cardVisible) {
								return;
							}

							visibleColumns[column.field] = column;
						}

						html.push("<th".concat(Utils.sprintf(' title="%s"',
								column.titleTooltip)), column
							.checkbox || column.radio ? Utils.sprintf(
								' class="bs-checkbox %s"', column[
									'class'] || '') : classes || class_,
							Utils.sprintf(' style="%s"', halign +
								style + csses.join('; ')), Utils
							.sprintf(' rowspan="%s"', column.rowspan),
							Utils.sprintf(' colspan="%s"', column
								.colspan), Utils.sprintf(
								' data-field="%s"', column.field
							), // If `column` is not the first element of `this.options.columns[0]`, then className 'data-not-first-th' should be added.
							j === 0 && i > 0 ? ' data-not-first-th' :
							'', '>');
						html.push(Utils.sprintf('<div class="th-inner %s">',
							_this2.options.sortable && column
							.sortable ? 'sortable both' : ''));
						var text = _this2.options.escape ? Utils.escapeHTML(
							column.title) : column.title;
						var title = text;

						if (column.checkbox) {
							text = '';

							if (!_this2.options.singleSelect && _this2
								.options.checkboxHeader) {
								text =
									'<input name="btSelectAll" class="form-check-input" type="checkbox" />';
							}

							_this2.header.stateField = column.field;
						}

						if (column.radio) {
							text = '';
							_this2.header.stateField = column.field;
						}

						if (!text && column.showSelectTitle) {
							text += title;
						}

						html.push(text);
						html.push('</div>');
						html.push('<div class="fht-cell"></div>');
						html.push('</div>');
						html.push('</th>');
					});

					if (detailViewTemplate && _this2.options.detailViewAlign ===
						'right') {
						html.push(detailViewTemplate);
					}

					html.push('</tr>');

					if (html.length > 3) {
						headerHtml.push(html.join(''));
					}
				});
				this.$header.html(headerHtml.join(''));
				this.$header.find('th[data-field]').each(function(i, el) {
					$__default["default"](el).data(visibleColumns[$__default[
						"default"](el).data('field')]);
				});
				this.$container.off('click', '.th-inner').on('click', '.th-inner', function(
					e) {
					var $this = $__default["default"](e.currentTarget);

					if (_this2.options.detailView && !$this.parent().hasClass(
							'bs-checkbox')) {
						if ($this.closest('.bootstrap-table')[0] !== _this2
							.$container[0]) {
							return false;
						}
					}

					if (_this2.options.sortable && $this.parent().data().sortable) {
						_this2.onSort(e);
					}
				});
				var resizeEvent = Utils.getEventName('resize.bootstrap-table', this.$el
					.attr('id'));
				$__default["default"](window).off(resizeEvent);

				if (!this.options.showHeader || this.options.cardView) {
					this.$header.hide();
					this.$tableHeader.hide();
					this.$tableLoading.css('top', 0);
				} else {
					this.$header.show();
					this.$tableHeader.show();
					this.$tableLoading.css('top', this.$header.outerHeight() +
						1); // Assign the correct sortable arrow

					this.getCaret();
					$__default["default"](window).on(resizeEvent, function() {
						return _this2.resetView();
					});
				}

				this.$selectAll = this.$header.find('[name="btSelectAll"]');
				this.$selectAll.off('click').on('click', function(e) {
					e.stopPropagation();
					var checked = $__default["default"](e.currentTarget).prop(
						'checked');

					_this2[checked ? 'checkAll' : 'uncheckAll']();

					_this2.updateSelected();
				});
			}
		}, {
			key: "initData",
			value: function initData(data, type) {
				if (type === 'append') {
					this.options.data = this.options.data.concat(data);
				} else if (type === 'prepend') {
					this.options.data = [].concat(data).concat(this.options.data);
				} else {
					data = data || Utils.deepCopy(this.options.data);
					this.options.data = Array.isArray(data) ? data : data[this.options
						.dataField];
				}

				this.data = _toConsumableArray(this.options.data);

				if (this.options.sortReset) {
					this.unsortedData = _toConsumableArray(this.data);
				}

				if (this.options.sidePagination === 'server') {
					return;
				}

				this.initSort();
			}
		}, {
			key: "initSort",
			value: function initSort() {
				var _this3 = this;

				var name = this.options.sortName;
				var order = this.options.sortOrder === 'desc' ? -1 : 1;
				var index = this.header.fields.indexOf(this.options.sortName);
				var timeoutId = 0;

				if (index !== -1) {
					if (this.options.sortStable) {
						this.data.forEach(function(row, i) {
							if (!row.hasOwnProperty('_position')) {
								row._position = i;
							}
						});
					}

					if (this.options.customSort) {
						Utils.calculateObjectValue(this.options, this.options.customSort, [
							this.options.sortName, this.options.sortOrder, this.data
						]);
					} else {
						this.data.sort(function(a, b) {
							if (_this3.header.sortNames[index]) {
								name = _this3.header.sortNames[index];
							}

							var aa = Utils.getItemField(a, name, _this3.options
								.escape);
							var bb = Utils.getItemField(b, name, _this3.options
								.escape);
							var value = Utils.calculateObjectValue(_this3.header,
								_this3.header.sorters[index], [aa, bb, a, b]);

							if (value !== undefined) {
								if (_this3.options.sortStable && value === 0) {
									return order * (a._position - b._position);
								}

								return order * value;
							}

							return Utils.sort(aa, bb, order, _this3.options
								.sortStable, a._position, b._position);
						});
					}

					if (this.options.sortClass !== undefined) {
						clearTimeout(timeoutId);
						timeoutId = setTimeout(function() {
							_this3.$el.removeClass(_this3.options.sortClass);

							var index = _this3.$header.find("[data-field=\"".concat(
								_this3.options.sortName, "\"]")).index();

							_this3.$el.find("tr td:nth-child(".concat(index + 1,
								")")).addClass(_this3.options.sortClass);
						}, 250);
					}
				} else if (this.options.sortReset) {
					this.data = _toConsumableArray(this.unsortedData);
				}
			}
		}, {
			key: "onSort",
			value: function onSort(_ref) {
				var type = _ref.type,
					currentTarget = _ref.currentTarget;
				var $this = type === 'keypress' ? $__default["default"](currentTarget) :
					$__default["default"](currentTarget).parent();
				var $this_ = this.$header.find('th').eq($this.index());
				this.$header.add(this.$header_).find('span.order').remove();

				if (this.options.sortName === $this.data('field')) {
					var currentSortOrder = this.options.sortOrder;

					if (currentSortOrder === undefined) {
						this.options.sortOrder = 'asc';
					} else if (currentSortOrder === 'asc') {
						this.options.sortOrder = 'desc';
					} else if (this.options.sortOrder === 'desc') {
						this.options.sortOrder = this.options.sortReset ? undefined : 'asc';
					}

					if (this.options.sortOrder === undefined) {
						this.options.sortName = undefined;
					}
				} else {
					this.options.sortName = $this.data('field');

					if (this.options.rememberOrder) {
						this.options.sortOrder = $this.data('order') === 'asc' ? 'desc' :
							'asc';
					} else {
						this.options.sortOrder = this.columns[this.fieldsColumnsIndex[$this
							.data('field')]].sortOrder || this.columns[this
							.fieldsColumnsIndex[$this.data('field')]].order;
					}
				}

				this.trigger('sort', this.options.sortName, this.options.sortOrder);
				$this.add($this_).data('order', this.options
					.sortOrder); // Assign the correct sortable arrow

				this.getCaret();

				if (this.options.sidePagination === 'server' && this.options.serverSort) {
					this.options.pageNumber = 1;
					this.initServer(this.options.silentSort);
					return;
				}

				this.initSort();
				this.initBody();
			}
		}, {
			key: "initToolbar",
			value: function initToolbar() {
				var _this4 = this;

				var opts = this.options;
				var html = [];
				var timeoutId = 0;
				var $keepOpen;
				var switchableCount = 0;

				if (this.$toolbar.find('.bs-bars').children().length) {
					$__default["default"]('body').append($__default["default"](opts
						.toolbar));
				}

				this.$toolbar.html('');

				if (typeof opts.toolbar === 'string' || _typeof(opts.toolbar) ===
					'object') {
					$__default["default"](Utils.sprintf('<div class="bs-bars %s-%s"></div>',
						this.constants.classes.pull, opts.toolbarAlign)).appendTo(this
						.$toolbar).append($__default["default"](opts.toolbar));
				} // showColumns, showToggle, showRefresh


				html = ["<div class=\"".concat(['columns', "columns-".concat(opts
						.buttonsAlign), this.constants.classes.buttonsGroup, ""
					.concat(this.constants.classes.pull, "-").concat(opts
						.buttonsAlign)
				].join(' '), "\">")];

				if (typeof opts.buttonsOrder === 'string') {
					opts.buttonsOrder = opts.buttonsOrder.replace(/\[|\]| |'/g, '').split(
						',');
				}

				this.buttons = Object.assign(this.buttons, {
					paginationSwitch: {
						text: opts.pagination ? opts.formatPaginationSwitchUp() :
							opts.formatPaginationSwitchDown(),
						icon: opts.pagination ? opts.icons.paginationSwitchDown :
							opts.icons.paginationSwitchUp,
						render: false,
						event: this.togglePagination,
						attributes: {
							'aria-label': opts.formatPaginationSwitch(),
							title: opts.formatPaginationSwitch()
						}
					},
					refresh: {
						text: opts.formatRefresh(),
						icon: opts.icons.refresh,
						render: false,
						event: this.refresh,
						attributes: {
							'aria-label': opts.formatRefresh(),
							title: opts.formatRefresh()
						}
					},
					toggle: {
						text: opts.formatToggle(),
						icon: opts.icons.toggleOff,
						render: false,
						event: this.toggleView,
						attributes: {
							'aria-label': opts.formatToggleOn(),
							title: opts.formatToggleOn()
						}
					},
					fullscreen: {
						text: opts.formatFullscreen(),
						icon: opts.icons.fullscreen,
						render: false,
						event: this.toggleFullscreen,
						attributes: {
							'aria-label': opts.formatFullscreen(),
							title: opts.formatFullscreen()
						}
					},
					columns: {
						render: false,
						html: function html() {
							var html = [];
							html.push("<div class=\"keep-open ".concat(_this4
									.constants.classes.buttonsDropdown,
									"\" title=\"").concat(opts
									.formatColumns(),
									"\">\n            <button class=\"")
								.concat(_this4.constants.buttonsClass,
									" dropdown-toggle\" type=\"button\" ")
								.concat(_this4.constants.dataToggle,
									"=\"dropdown\"\n            aria-label=\"Columns\" title=\""
								).concat(opts.formatColumns(),
									"\">\n            ").concat(opts
									.showButtonIcons ? Utils.sprintf(_this4
										.constants.html.icon, opts
										.iconsPrefix, opts.icons.columns) :
									'', "\n            ").concat(opts
									.showButtonText ? opts.formatColumns() :
									'', "\n            ").concat(_this4
									.constants.html.dropdownCaret,
									"\n            </button>\n            ")
								.concat(_this4.constants.html
									.toolbarDropdown[0]));

							if (opts.showColumnsSearch) {
								html.push(Utils.sprintf(_this4.constants.html
									.toolbarDropdownItem, Utils.sprintf(
										'<input type="text" class="%s" name="columnsSearch" placeholder="%s" autocomplete="off">',
										_this4.constants.classes.input,
										opts.formatSearch())));
								html.push(_this4.constants.html
									.toolbarDropdownSeparator);
							}

							if (opts.showColumnsToggleAll) {
								var allFieldsVisible = _this4
									.getVisibleColumns().length === _this4
									.columns.filter(function(column) {
										return !_this4.isSelectionColumn(
											column);
									}).length;

								html.push(Utils.sprintf(_this4.constants.html
									.toolbarDropdownItem, Utils.sprintf(
										'<input type="checkbox" class="toggle-all" %s> <span>%s</span>',
										allFieldsVisible ?
										'checked="checked"' : '', opts
										.formatColumnsToggleAll())));
								html.push(_this4.constants.html
									.toolbarDropdownSeparator);
							}

							var visibleColumns = 0;

							_this4.columns.forEach(function(column) {
								if (column.visible) {
									visibleColumns++;
								}
							});

							_this4.columns.forEach(function(column, i) {
								if (_this4.isSelectionColumn(column)) {
									return;
								}

								if (opts.cardView && !column
									.cardVisible) {
									return;
								}

								var checked = column.visible ?
									' checked="checked"' : '';
								var disabled = visibleColumns <= opts
									.minimumCountColumns && checked ?
									' disabled="disabled"' : '';

								if (column.switchable) {
									html.push(Utils.sprintf(_this4
										.constants.html
										.toolbarDropdownItem,
										Utils.sprintf(
											'<input type="checkbox" data-field="%s" class="form-check-input" value="%s"%s%s style="width: 13px;height: 13px;margin-right: 10px;">  <span>%s</span>',
											column.field, i,
											checked, disabled,
											column.title)));
									switchableCount++;
								}
							});

							html.push(_this4.constants.html.toolbarDropdown[1],
								'</div>');
							return html.join('');
						}
					}
				});
				var buttonsHtml = {};

				for (var _i3 = 0, _Object$entries3 = Object.entries(this.buttons); _i3 <
					_Object$entries3.length; _i3++) {
					var _Object$entries3$_i = _slicedToArray(_Object$entries3[_i3], 2),
						buttonName = _Object$entries3$_i[0],
						buttonConfig = _Object$entries3$_i[1];

					var buttonHtml = void 0;

					if (buttonConfig.hasOwnProperty('html')) {
						if (typeof buttonConfig.html === 'function') {
							buttonHtml = buttonConfig.html();
						} else if (typeof buttonConfig.html === 'string') {
							buttonHtml = buttonConfig.html;
						}
					} else {
						buttonHtml = "<button class=\"".concat(this.constants.buttonsClass,
							"\" type=\"button\" name=\"").concat(buttonName, "\"");

						if (buttonConfig.hasOwnProperty('attributes')) {
							for (var _i4 = 0, _Object$entries4 = Object.entries(buttonConfig
									.attributes); _i4 < _Object$entries4.length; _i4++) {
								var _Object$entries4$_i = _slicedToArray(_Object$entries4[
										_i4], 2),
									attributeName = _Object$entries4$_i[0],
									value = _Object$entries4$_i[1];

								buttonHtml += " ".concat(attributeName, "=\"").concat(value,
									"\"");
							}
						}

						buttonHtml += '>';

						if (opts.showButtonIcons && buttonConfig.hasOwnProperty('icon')) {
							buttonHtml += "".concat(Utils.sprintf(this.constants.html.icon,
								opts.iconsPrefix, buttonConfig.icon), " ");
						}

						if (opts.showButtonText && buttonConfig.hasOwnProperty('text')) {
							buttonHtml += buttonConfig.text;
						}

						buttonHtml += '</button>';
					}

					buttonsHtml[buttonName] = buttonHtml;
					var optionName = "show".concat(buttonName.charAt(0).toUpperCase())
						.concat(buttonName.substring(1));
					var showOption = opts[optionName];

					if ((!buttonConfig.hasOwnProperty('render') || buttonConfig
							.hasOwnProperty('render') && buttonConfig.render) && (
							showOption === undefined || showOption === true)) {
						opts[optionName] = true;
					}

					if (!opts.buttonsOrder.includes(buttonName)) {
						opts.buttonsOrder.push(buttonName);
					}
				} // Adding the button html to the final toolbar html when the showOption is true


				var _iterator = _createForOfIteratorHelper(opts.buttonsOrder),
					_step;

				try {
					for (_iterator.s(); !(_step = _iterator.n()).done;) {
						var button = _step.value;
						var _showOption = opts["show".concat(button.charAt(0).toUpperCase())
							.concat(button.substring(1))];

						if (_showOption) {
							html.push(buttonsHtml[button]);
						}
					}
				} catch (err) {
					_iterator.e(err);
				} finally {
					_iterator.f();
				}

				html.push('</div>'); // Fix #188: this.showToolbar is for extensions

				if (this.showToolbar || html.length > 2) {
					this.$toolbar.append(html.join(''));
				}

				for (var _i5 = 0, _Object$entries5 = Object.entries(this.buttons); _i5 <
					_Object$entries5.length; _i5++) {
					var _Object$entries5$_i = _slicedToArray(_Object$entries5[_i5], 2),
						_buttonName = _Object$entries5$_i[0],
						_buttonConfig = _Object$entries5$_i[1];

					if (_buttonConfig.hasOwnProperty('event')) {
						if (typeof _buttonConfig.event === 'function' ||
							typeof _buttonConfig.event === 'string') {
							var _ret = function() {
								var event = typeof _buttonConfig.event === 'string' ?
									window[_buttonConfig.event] : _buttonConfig.event;

								_this4.$toolbar.find("button[name=\"".concat(
									_buttonName, "\"]")).off('click').on('click',
									function() {
										return event.call(_this4);
									});

								return "continue";
							}();

							if (_ret === "continue") continue;
						}

						var _loop = function _loop() {
							var _Object$entries6$_i = _slicedToArray(_Object$entries6[
									_i6], 2),
								eventType = _Object$entries6$_i[0],
								eventFunction = _Object$entries6$_i[1];

							var event = typeof eventFunction === 'string' ? window[
								eventFunction] : eventFunction;

							_this4.$toolbar.find("button[name=\"".concat(_buttonName,
								"\"]")).off(eventType).on(eventType, function() {
								return event.call(_this4);
							});
						};

						for (var _i6 = 0, _Object$entries6 = Object.entries(_buttonConfig
								.event); _i6 < _Object$entries6.length; _i6++) {
							_loop();
						}
					}
				}

				if (opts.showColumns) {
					$keepOpen = this.$toolbar.find('.keep-open');
					var $checkboxes = $keepOpen.find(
						'input[type="checkbox"]:not(".toggle-all")');
					var $toggleAll = $keepOpen.find('input[type="checkbox"].toggle-all');

					if (switchableCount <= opts.minimumCountColumns) {
						$keepOpen.find('input').prop('disabled', true);
					}

					$keepOpen.find('li, label').off('click').on('click', function(e) {
						e.stopImmediatePropagation();
					});
					$checkboxes.off('click').on('click', function(_ref2) {
						var currentTarget = _ref2.currentTarget;
						var $this = $__default["default"](currentTarget);

						_this4._toggleColumn($this.val(), $this.prop('checked'),
							false);

						_this4.trigger('column-switch', $this.data('field'), $this
							.prop('checked'));

						$toggleAll.prop('checked', $checkboxes.filter(':checked')
							.length === _this4.columns.filter(function(column) {
								return !_this4.isSelectionColumn(column);
							}).length);
					});
					$toggleAll.off('click').on('click', function(_ref3) {
						var currentTarget = _ref3.currentTarget;

						_this4._toggleAllColumns($__default["default"](
							currentTarget).prop('checked'));

						_this4.trigger('column-switch-all', $__default["default"](
							currentTarget).prop('checked'));
					});

					if (opts.showColumnsSearch) {
						var $columnsSearch = $keepOpen.find('[name="columnsSearch"]');
						var $listItems = $keepOpen.find('.dropdown-item-marker');
						$columnsSearch.on('keyup paste change', function(_ref4) {
							var currentTarget = _ref4.currentTarget;
							var $this = $__default["default"](currentTarget);
							var searchValue = $this.val().toLowerCase();
							$listItems.show();
							$checkboxes.each(function(i, el) {
								var $checkbox = $__default["default"](el);
								var $listItem = $checkbox.parents(
									'.dropdown-item-marker');
								var text = $listItem.text().toLowerCase();

								if (!text.includes(searchValue)) {
									$listItem.hide();
								}
							});
						});
					}
				}

				var handleInputEvent = function handleInputEvent($searchInput) {
					var eventTriggers = 'keyup drop blur mouseup';
					$searchInput.off(eventTriggers).on(eventTriggers, function(event) {
						if (opts.searchOnEnterKey && event.keyCode !== 13) {
							return;
						}

						if ([37, 38, 39, 40].includes(event.keyCode)) {
							return;
						}

						clearTimeout(timeoutId); // doesn't matter if it's 0

						timeoutId = setTimeout(function() {
							_this4.onSearch({
								currentTarget: event
									.currentTarget
							});
						}, opts.searchTimeOut);
					});
				}; // Fix #4516: this.showSearchClearButton is for extensions


				if ((opts.search || this.showSearchClearButton) && typeof opts
					.searchSelector !== 'string') {
					html = [];
					var showSearchButton = Utils.sprintf(this.constants.html.searchButton,
						this.constants.buttonsClass, opts.formatSearch(), opts
						.showButtonIcons ? Utils.sprintf(this.constants.html.icon, opts
							.iconsPrefix, opts.icons.search) : '', opts.showButtonText ?
						opts.formatSearch() : '');
					var showSearchClearButton = Utils.sprintf(this.constants.html
						.searchClearButton, this.constants.buttonsClass, opts
						.formatClearSearch(), opts.showButtonIcons ? Utils.sprintf(this
							.constants.html.icon, opts.iconsPrefix, opts.icons
							.clearSearch) : '', opts.showButtonText ? opts
						.formatClearSearch() : '');
					var searchInputHtml = "<input class=\"".concat(this.constants.classes
							.input, "\n        ").concat(Utils.sprintf(' %s%s', this
								.constants.classes.inputPrefix, opts.iconSize),
							"\n        search-input\" type=\"search\" placeholder=\"")
						.concat(opts.formatSearch(), "\" autocomplete=\"off\">");
					var searchInputFinalHtml = searchInputHtml;

					if (opts.showSearchButton || opts.showSearchClearButton) {
						var _buttonsHtml = (opts.showSearchButton ? showSearchButton : '') +
							(opts.showSearchClearButton ? showSearchClearButton : '');

						searchInputFinalHtml = opts.search ? Utils.sprintf(this.constants
								.html.inputGroup, searchInputHtml, _buttonsHtml) :
							_buttonsHtml;
					}

					html.push(Utils.sprintf("\n        <div class=\"".concat(this.constants
							.classes.pull, "-").concat(opts.searchAlign, " search ")
						.concat(this.constants.classes.inputGroup,
							"\">\n          %s\n        </div>\n      "),
						searchInputFinalHtml));
					this.$toolbar.append(html.join(''));
					var $searchInput = Utils.getSearchInput(this);

					if (opts.showSearchButton) {
						this.$toolbar.find('.search button[name=search]').off('click').on(
							'click',
							function() {
								clearTimeout(timeoutId); // doesn't matter if it's 0

								timeoutId = setTimeout(function() {
									_this4.onSearch({
										currentTarget: $searchInput
									});
								}, opts.searchTimeOut);
							});

						if (opts.searchOnEnterKey) {
							handleInputEvent($searchInput);
						}
					} else {
						handleInputEvent($searchInput);
					}

					if (opts.showSearchClearButton) {
						this.$toolbar.find('.search button[name=clearSearch]').click(
							function() {
								_this4.resetSearch();
							});
					}
				} else if (typeof opts.searchSelector === 'string') {
					var _$searchInput = Utils.getSearchInput(this);

					handleInputEvent(_$searchInput);
				}
			}
		}, {
			key: "onSearch",
			value: function onSearch() {
				var _ref5 = arguments.length > 0 && arguments[0] !== undefined ? arguments[
						0] : {},
					currentTarget = _ref5.currentTarget,
					firedByInitSearchText = _ref5.firedByInitSearchText;

				var overwriteSearchText = arguments.length > 1 && arguments[1] !==
					undefined ? arguments[1] : true;

				if (currentTarget !== undefined && $__default["default"](currentTarget)
					.length && overwriteSearchText) {
					var text = $__default["default"](currentTarget).val().trim();

					if (this.options.trimOnSearch && $__default["default"](currentTarget)
						.val() !== text) {
						$__default["default"](currentTarget).val(text);
					}

					if (this.searchText === text) {
						return;
					}

					var $searchInput = Utils.getSearchInput(this);
					var $currentTarget = currentTarget instanceof jQuery ? currentTarget :
						$__default["default"](currentTarget);

					if ($currentTarget.is($searchInput) || $currentTarget.hasClass(
							'search-input')) {
						this.searchText = text;
						this.options.searchText = text;
					}
				}

				if (!firedByInitSearchText && !this.options.cookie) {
					this.options.pageNumber = 1;
				}

				this.initSearch();

				if (firedByInitSearchText) {
					if (this.options.sidePagination === 'client') {
						this.updatePagination();
					}
				} else {
					this.updatePagination();
				}

				this.trigger('search', this.searchText);
			}
		}, {
			key: "initSearch",
			value: function initSearch() {
				var _this5 = this;

				this.filterOptions = this.filterOptions || this.options.filterOptions;

				if (this.options.sidePagination !== 'server') {
					if (this.options.customSearch) {
						this.data = Utils.calculateObjectValue(this.options, this.options
							.customSearch, [this.options.data, this.searchText, this
								.filterColumns
							]);

						if (this.options.sortReset) {
							this.unsortedData = _toConsumableArray(this.data);
						}

						return;
					}

					var rawSearchText = this.searchText && (this.fromHtml ? Utils
						.escapeHTML(this.searchText) : this.searchText);
					var searchText = rawSearchText ? rawSearchText.toLowerCase() : '';
					var f = Utils.isEmptyObject(this.filterColumns) ? null : this
						.filterColumns;

					if (this.options.searchAccentNeutralise) {
						searchText = Utils.normalizeAccent(searchText);
					} // Check filter


					if (typeof this.filterOptions.filterAlgorithm === 'function') {
						this.data = this.options.data.filter(function(item) {
							return _this5.filterOptions.filterAlgorithm.apply(null,
								[item, f]);
						});
					} else if (typeof this.filterOptions.filterAlgorithm === 'string') {
						this.data = f ? this.options.data.filter(function(item) {
							var filterAlgorithm = _this5.filterOptions
								.filterAlgorithm;

							if (filterAlgorithm === 'and') {
								for (var key in f) {
									if (Array.isArray(f[key]) && !f[key].includes(
											item[key]) || !Array.isArray(f[key]) &&
										item[key] !== f[key]) {
										return false;
									}
								}
							} else if (filterAlgorithm === 'or') {
								var match = false;

								for (var _key in f) {
									if (Array.isArray(f[_key]) && f[_key].includes(
											item[_key]) || !Array.isArray(f[
											_key]) && item[_key] === f[_key]) {
										match = true;
									}
								}

								return match;
							}

							return true;
						}) : _toConsumableArray(this.options.data);
					}

					var visibleFields = this.getVisibleFields();
					this.data = searchText ? this.data.filter(function(item, i) {
						for (var j = 0; j < _this5.header.fields.length; j++) {
							if (!_this5.header.searchables[j] || _this5.options
								.visibleSearch && visibleFields.indexOf(_this5
									.header.fields[j]) === -1) {
								continue;
							}

							var key = Utils.isNumeric(_this5.header.fields[j]) ?
								parseInt(_this5.header.fields[j], 10) : _this5
								.header.fields[j];
							var column = _this5.columns[_this5.fieldsColumnsIndex[
								key]];
							var value = void 0;

							if (typeof key === 'string') {
								value = item;
								var props = key.split('.');

								for (var _i7 = 0; _i7 < props.length; _i7++) {
									if (value[props[_i7]] !== null) {
										value = value[props[_i7]];
									}
								}
							} else {
								value = item[key];
							}

							if (_this5.options.searchAccentNeutralise) {
								value = Utils.normalizeAccent(value);
							} // Fix #142: respect searchFormatter boolean


							if (column && column.searchFormatter) {
								value = Utils.calculateObjectValue(column, _this5
									.header.formatters[j], [value, item, i,
										column.field
									], value);
							}

							if (typeof value === 'string' || typeof value ===
								'number') {
								if (_this5.options.strictSearch && "".concat(value)
									.toLowerCase() === searchText || _this5.options
									.regexSearch && Utils.regexCompare(value,
										rawSearchText)) {
									return true;
								}

								var largerSmallerEqualsRegex =
									/(?:(<=|=>|=<|>=|>|<)(?:\s+)?(-?\d+)?|(-?\d+)?(\s+)?(<=|=>|=<|>=|>|<))/gm;
								var matches = largerSmallerEqualsRegex.exec(_this5
									.searchText);
								var comparisonCheck = false;

								if (matches) {
									var operator = matches[1] || "".concat(matches[
										5], "l");
									var comparisonValue = matches[2] || matches[3];
									var int = parseInt(value, 10);
									var comparisonInt = parseInt(comparisonValue,
										10);

									switch (operator) {
										case '>':
										case '<l':
											comparisonCheck = int > comparisonInt;
											break;

										case '<':
										case '>l':
											comparisonCheck = int < comparisonInt;
											break;

										case '<=':
										case '=<':
										case '>=l':
										case '=>l':
											comparisonCheck = int <= comparisonInt;
											break;

										case '>=':
										case '=>':
										case '<=l':
										case '=<l':
											comparisonCheck = int >= comparisonInt;
											break;
									}
								}

								if (comparisonCheck || "".concat(value)
									.toLowerCase().includes(searchText)) {
									return true;
								}
							}
						}

						return false;
					}) : this.data;

					if (this.options.sortReset) {
						this.unsortedData = _toConsumableArray(this.data);
					}

					this.initSort();
				}
			}
		}, {
			key: "initPagination",
			value: function initPagination() {
				var _this6 = this;

				var opts = this.options;

				if (!opts.pagination) {
					this.$pagination.hide();
					return;
				}

				this.$pagination.show();
				var html = [];
				var allSelected = false;
				var i;
				var from;
				var to;
				var $pageList;
				var $pre;
				var $next;
				var $number;
				var data = this.getData({
					includeHiddenRows: false
				});
				var pageList = opts.pageList;

				if (typeof pageList === 'string') {
					pageList = pageList.replace(/\[|\]| /g, '').toLowerCase().split(',');
				}

				pageList = pageList.map(function(value) {
					if (typeof value === 'string') {
						return value.toLowerCase() === opts.formatAllRows()
							.toLowerCase() || ['all', 'unlimited'].includes(value
								.toLowerCase()) ? opts.formatAllRows() : +value;
					}

					return value;
				});
				this.paginationParts = opts.paginationParts;

				if (typeof this.paginationParts === 'string') {
					this.paginationParts = this.paginationParts.replace(/\[|\]| |'/g, '')
						.split(',');
				}

				if (opts.sidePagination !== 'server') {
					opts.totalRows = data.length;
				}

				this.totalPages = 0;

				if (opts.totalRows) {
					if (opts.pageSize === opts.formatAllRows()) {
						opts.pageSize = opts.totalRows;
						allSelected = true;
					}

					this.totalPages = ~~((opts.totalRows - 1) / opts.pageSize) + 1;
					opts.totalPages = this.totalPages;
				}

				if (this.totalPages > 0 && opts.pageNumber > this.totalPages) {
					opts.pageNumber = this.totalPages;
				}

				this.pageFrom = (opts.pageNumber - 1) * opts.pageSize + 1;
				this.pageTo = opts.pageNumber * opts.pageSize;

				if (this.pageTo > opts.totalRows) {
					this.pageTo = opts.totalRows;
				}

				if (this.options.pagination && this.options.sidePagination !== 'server') {
					this.options.totalNotFiltered = this.options.data.length;
				}

				if (!this.options.showExtendedPagination) {
					this.options.totalNotFiltered = undefined;
				}

				if (this.paginationParts.includes('pageInfo') || this.paginationParts
					.includes('pageInfoShort') || this.paginationParts.includes('pageSize')
				) {
					html.push("<div class=\"".concat(this.constants.classes.pull, "-")
						.concat(opts.paginationDetailHAlign, " pagination-detail\">"));
				}

				if (this.paginationParts.includes('pageInfo') || this.paginationParts
					.includes('pageInfoShort')) {
					var paginationInfo = this.paginationParts.includes('pageInfoShort') ?
						opts.formatDetailPagination(opts.totalRows) : opts
						.formatShowingRows(this.pageFrom, this.pageTo, opts.totalRows, opts
							.totalNotFiltered);
					html.push("<span class=\"pagination-info\">\n      ".concat(
						paginationInfo, "\n      </span>"));
				}

				if (this.paginationParts.includes('pageSize')) {
					html.push('<div class="page-list">');
					var pageNumber = ["<div class=\"".concat(this.constants.classes
							.paginationDropdown, "\">\n        <button class=\"")
						.concat(this.constants.buttonsClass,
							" dropdown-toggle\" type=\"button\" ").concat(this.constants
							.dataToggle,
							"=\"dropdown\">\n        <span class=\"page-size\">\n        "
						).concat(allSelected ? opts.formatAllRows() : opts.pageSize,
							"\n        </span>\n        ").concat(this.constants.html
							.dropdownCaret, "\n        </button>\n        ").concat(this
							.constants.html.pageDropdown[0])
					];
					pageList.forEach(function(page, i) {
						if (!opts.smartDisplay || i === 0 || pageList[i - 1] < opts
							.totalRows || page === opts.formatAllRows()) {
							var active;

							if (allSelected) {
								active = page === opts.formatAllRows() ? _this6
									.constants.classes.dropdownActive : '';
							} else {
								active = page === opts.pageSize ? _this6.constants
									.classes.dropdownActive : '';
							}

							pageNumber.push(Utils.sprintf(_this6.constants.html
								.pageDropdownItem, active, page));
						}
					});
					pageNumber.push("".concat(this.constants.html.pageDropdown[1],
						"</div>"));
					html.push(opts.formatRecordsPerPage(pageNumber.join('')));
				}

				if (this.paginationParts.includes('pageInfo') || this.paginationParts
					.includes('pageInfoShort') || this.paginationParts.includes('pageSize')
				) {
					html.push('</div></div>');
				}

				if (this.paginationParts.includes('pageList')) {
					html.push("<div class=\"".concat(this.constants.classes.pull, "-")
						.concat(opts.paginationHAlign, " pagination\">"), Utils.sprintf(
							this.constants.html.pagination[0], Utils.sprintf(
								' pagination-%s', opts.iconSize)), Utils.sprintf(this
							.constants.html.paginationItem, ' page-pre', opts
							.formatSRPaginationPreText(), opts.paginationPreText));

					if (this.totalPages < opts.paginationSuccessivelySize) {
						from = 1;
						to = this.totalPages;
					} else {
						from = opts.pageNumber - opts.paginationPagesBySide;
						to = from + opts.paginationPagesBySide * 2;
					}

					if (opts.pageNumber < opts.paginationSuccessivelySize - 1) {
						to = opts.paginationSuccessivelySize;
					}

					if (opts.paginationSuccessivelySize > this.totalPages - from) {
						from = from - (opts.paginationSuccessivelySize - (this.totalPages -
							from)) + 1;
					}

					if (from < 1) {
						from = 1;
					}

					if (to > this.totalPages) {
						to = this.totalPages;
					}

					var middleSize = Math.round(opts.paginationPagesBySide / 2);

					var pageItem = function pageItem(i) {
						var classes = arguments.length > 1 && arguments[1] !==
							undefined ? arguments[1] : '';
						return Utils.sprintf(_this6.constants.html.paginationItem,
							classes + (i === opts.pageNumber ? " ".concat(_this6
								.constants.classes.paginationActive) : ''), opts
							.formatSRPaginationPageText(i), i);
					};

					if (from > 1) {
						var max = opts.paginationPagesBySide;
						if (max >= from) max = from - 1;

						for (i = 1; i <= max; i++) {
							html.push(pageItem(i));
						}

						if (from - 1 === max + 1) {
							i = from - 1;
							html.push(pageItem(i));
						} else if (from - 1 > max) {
							if (from - opts.paginationPagesBySide * 2 > opts
								.paginationPagesBySide && opts.paginationUseIntermediate) {
								i = Math.round((from - middleSize) / 2 + middleSize);
								html.push(pageItem(i, ' page-intermediate'));
							} else {
								html.push(Utils.sprintf(this.constants.html.paginationItem,
									' page-first-separator disabled', '', '...'));
							}
						}
					}

					for (i = from; i <= to; i++) {
						html.push(pageItem(i));
					}

					if (this.totalPages > to) {
						var min = this.totalPages - (opts.paginationPagesBySide - 1);
						if (to >= min) min = to + 1;

						if (to + 1 === min - 1) {
							i = to + 1;
							html.push(pageItem(i));
						} else if (min > to + 1) {
							if (this.totalPages - to > opts.paginationPagesBySide * 2 &&
								opts.paginationUseIntermediate) {
								i = Math.round((this.totalPages - middleSize - to) / 2 +
									to);
								html.push(pageItem(i, ' page-intermediate'));
							} else {
								html.push(Utils.sprintf(this.constants.html.paginationItem,
									' page-last-separator disabled', '', '...'));
							}
						}

						for (i = min; i <= this.totalPages; i++) {
							html.push(pageItem(i));
						}
					}

					html.push(Utils.sprintf(this.constants.html.paginationItem,
						' page-next', opts.formatSRPaginationNextText(), opts
						.paginationNextText));
					html.push(this.constants.html.pagination[1], '</div>');
				}

				this.$pagination.html(html.join(''));
				var dropupClass = ['bottom', 'both'].includes(opts.paginationVAlign) ? " "
					.concat(this.constants.classes.dropup) : '';
				this.$pagination.last().find('.page-list > div').addClass(dropupClass);

				if (!opts.onlyInfoPagination) {
					$pageList = this.$pagination.find('.page-list a');
					$pre = this.$pagination.find('.page-pre');
					$next = this.$pagination.find('.page-next');
					$number = this.$pagination.find('.page-item').not(
						'.page-next, .page-pre, .page-last-separator, .page-first-separator'
					);

					if (this.totalPages <= 1) {
						this.$pagination.find('div.pagination').hide();
					}

					if (opts.smartDisplay) {
						if (pageList.length < 2 || opts.totalRows <= pageList[0]) {
							this.$pagination.find('div.page-list').hide();
						}
					} // when data is empty, hide the pagination


					this.$pagination[this.getData().length ? 'show' : 'hide']();

					if (!opts.paginationLoop) {
						if (opts.pageNumber === 1) {
							$pre.addClass('disabled');
						}

						if (opts.pageNumber === this.totalPages) {
							$next.addClass('disabled');
						}
					}

					if (allSelected) {
						opts.pageSize = opts.formatAllRows();
					} // removed the events for last and first, onPageNumber executeds the same logic


					$pageList.off('click').on('click', function(e) {
						return _this6.onPageListChange(e);
					});
					$pre.off('click').on('click', function(e) {
						return _this6.onPagePre(e);
					});
					$next.off('click').on('click', function(e) {
						return _this6.onPageNext(e);
					});
					$number.off('click').on('click', function(e) {
						return _this6.onPageNumber(e);
					});
				}
			}
		}, {
			key: "updatePagination",
			value: function updatePagination(event) {
				// Fix #171: IE disabled button can be clicked bug.
				if (event && $__default["default"](event.currentTarget).hasClass(
						'disabled')) {
					return;
				}

				if (!this.options.maintainMetaData) {
					this.resetRows();
				}

				this.initPagination();
				this.trigger('page-change', this.options.pageNumber, this.options.pageSize);

				if (this.options.sidePagination === 'server') {
					this.initServer();
				} else {
					this.initBody();
				}
			}
		}, {
			key: "onPageListChange",
			value: function onPageListChange(event) {
				event.preventDefault();
				var $this = $__default["default"](event.currentTarget);
				$this.parent().addClass(this.constants.classes.dropdownActive).siblings()
					.removeClass(this.constants.classes.dropdownActive);
				this.options.pageSize = $this.text().toUpperCase() === this.options
					.formatAllRows().toUpperCase() ? this.options.formatAllRows() : +$this
					.text();
				this.$toolbar.find('.page-size').text(this.options.pageSize);
				this.updatePagination(event);
				return false;
			}
		}, {
			key: "onPagePre",
			value: function onPagePre(event) {
				if ($__default["default"](event.target).hasClass('disabled')) {
					return;
				}

				event.preventDefault();

				if (this.options.pageNumber - 1 === 0) {
					this.options.pageNumber = this.options.totalPages;
				} else {
					this.options.pageNumber--;
				}

				this.updatePagination(event);
				return false;
			}
		}, {
			key: "onPageNext",
			value: function onPageNext(event) {
				if ($__default["default"](event.target).hasClass('disabled')) {
					return;
				}

				event.preventDefault();

				if (this.options.pageNumber + 1 > this.options.totalPages) {
					this.options.pageNumber = 1;
				} else {
					this.options.pageNumber++;
				}

				this.updatePagination(event);
				return false;
			}
		}, {
			key: "onPageNumber",
			value: function onPageNumber(event) {
				event.preventDefault();

				if (this.options.pageNumber === +$__default["default"](event.currentTarget)
					.text()) {
					return;
				}

				this.options.pageNumber = +$__default["default"](event.currentTarget)
					.text();
				this.updatePagination(event);
				return false;
			} // eslint-disable-next-line no-unused-vars

		}, {
			key: "initRow",
			value: function initRow(item, i, data, trFragments) {
				var _this7 = this;

				var html = [];
				var style = {};
				var csses = [];
				var data_ = '';
				var attributes = {};
				var htmlAttributes = [];

				if (Utils.findIndex(this.hiddenRows, item) > -1) {
					return;
				}

				style = Utils.calculateObjectValue(this.options, this.options.rowStyle, [
					item, i
				], style);

				if (style && style.css) {
					for (var _i8 = 0, _Object$entries7 = Object.entries(style.css); _i8 <
						_Object$entries7.length; _i8++) {
						var _Object$entries7$_i = _slicedToArray(_Object$entries7[_i8], 2),
							key = _Object$entries7$_i[0],
							value = _Object$entries7$_i[1];

						csses.push("".concat(key, ": ").concat(value));
					}
				}

				attributes = Utils.calculateObjectValue(this.options, this.options
					.rowAttributes, [item, i], attributes);

				if (attributes) {
					for (var _i9 = 0, _Object$entries8 = Object.entries(attributes); _i9 <
						_Object$entries8.length; _i9++) {
						var _Object$entries8$_i = _slicedToArray(_Object$entries8[_i9], 2),
							_key2 = _Object$entries8$_i[0],
							_value = _Object$entries8$_i[1];

						htmlAttributes.push("".concat(_key2, "=\"").concat(Utils.escapeHTML(
							_value), "\""));
					}
				}

				if (item._data && !Utils.isEmptyObject(item._data)) {
					for (var _i10 = 0, _Object$entries9 = Object.entries(item._data); _i10 <
						_Object$entries9.length; _i10++) {
						var _Object$entries9$_i = _slicedToArray(_Object$entries9[_i10], 2),
							k = _Object$entries9$_i[0],
							v = _Object$entries9$_i[1];

						// ignore data-index
						if (k === 'index') {
							return;
						}

						data_ += " data-".concat(k, "='").concat(_typeof(v) === 'object' ?
							JSON.stringify(v) : v, "'");
					}
				}

				html.push('<tr', Utils.sprintf(' %s', htmlAttributes.length ? htmlAttributes
						.join(' ') : undefined), Utils.sprintf(' id="%s"', Array
						.isArray(item) ? undefined : item._id), Utils.sprintf(
						' class="%s"', style.classes || (Array.isArray(item) ?
							undefined : item._class)), Utils.sprintf(' style="%s"',
						Array.isArray(item) ? undefined : item._style), " data-index=\""
					.concat(i, "\""), Utils.sprintf(' data-uniqueid="%s"', Utils
						.getItemField(item, this.options.uniqueId, false)), Utils
					.sprintf(' data-has-detail-view="%s"', this.options.detailView &&
						Utils.calculateObjectValue(null, this.options.detailFilter, [i,
							item
						]) ? 'true' : undefined), Utils.sprintf('%s', data_), '>');

				if (this.options.cardView) {
					html.push("<td colspan=\"".concat(this.header.fields.length,
						"\"><div class=\"card-views\">"));
				}

				var detailViewTemplate = '';

				if (Utils.hasDetailViewIcon(this.options)) {
					detailViewTemplate = '<td>';

					if (Utils.calculateObjectValue(null, this.options.detailFilter, [i,
							item
						])) {
						detailViewTemplate +=
							"\n          <a class=\"detail-icon\" href=\"#\">\n          "
							.concat(Utils.sprintf(this.constants.html.icon, this.options
									.iconsPrefix, this.options.icons.detailOpen),
								"\n          </a>\n        ");
					}

					detailViewTemplate += '</td>';
				}

				if (detailViewTemplate && this.options.detailViewAlign !== 'right') {
					html.push(detailViewTemplate);
				}

				this.header.fields.forEach(function(field, j) {
					var column = _this7.columns[j];
					var text = '';
					var value_ = Utils.getItemField(item, field, _this7.options
						.escape, column.escape);
					var value = '';
					var type = '';
					var cellStyle = {};
					var id_ = '';
					var class_ = _this7.header.classes[j];
					var style_ = '';
					var styleToAdd_ = '';
					var data_ = '';
					var rowspan_ = '';
					var colspan_ = '';
					var title_ = '';

					if ((_this7.fromHtml || _this7.autoMergeCells) &&
						typeof value_ === 'undefined') {
						if (!column.checkbox && !column.radio) {
							return;
						}
					}

					if (!column.visible) {
						return;
					}

					if (_this7.options.cardView && !column.cardVisible) {
						return;
					}

					if (column.escape) {
						value_ = Utils.escapeHTML(value_);
					} // Style concat


					if (csses.concat([_this7.header.styles[j]]).length) {
						styleToAdd_ += "".concat(csses.concat([_this7.header.styles[
							j]]).join('; '));
					}

					if (item["_".concat(field, "_style")]) {
						styleToAdd_ += "".concat(item["_".concat(field, "_style")]);
					}

					if (styleToAdd_) {
						style_ = " style=\"".concat(styleToAdd_, "\"");
					} // Style concat
					// handle id and class of td


					if (item["_".concat(field, "_id")]) {
						id_ = Utils.sprintf(' id="%s"', item["_".concat(field,
							"_id")]);
					}

					if (item["_".concat(field, "_class")]) {
						class_ = Utils.sprintf(' class="%s"', item["_".concat(field,
							"_class")]);
					}

					if (item["_".concat(field, "_rowspan")]) {
						rowspan_ = Utils.sprintf(' rowspan="%s"', item["_".concat(
							field, "_rowspan")]);
					}

					if (item["_".concat(field, "_colspan")]) {
						colspan_ = Utils.sprintf(' colspan="%s"', item["_".concat(
							field, "_colspan")]);
					}

					if (item["_".concat(field, "_title")]) {
						title_ = Utils.sprintf(' title="%s"', item["_".concat(field,
							"_title")]);
					}

					cellStyle = Utils.calculateObjectValue(_this7.header, _this7
						.header.cellStyles[j], [value_, item, i, field],
						cellStyle);

					if (cellStyle.classes) {
						class_ = " class=\"".concat(cellStyle.classes, "\"");
					}

					if (cellStyle.css) {
						var csses_ = [];

						for (var _i11 = 0, _Object$entries10 = Object.entries(
								cellStyle.css); _i11 < _Object$entries10
							.length; _i11++) {
							var _Object$entries10$_i = _slicedToArray(
									_Object$entries10[_i11], 2),
								_key3 = _Object$entries10$_i[0],
								_value2 = _Object$entries10$_i[1];

							csses_.push("".concat(_key3, ": ").concat(_value2));
						}

						style_ = " style=\"".concat(csses_.concat(_this7.header
							.styles[j]).join('; '), "\"");
					}

					value = Utils.calculateObjectValue(column, _this7.header
						.formatters[j], [value_, item, i, field], value_);

					if (!(column.checkbox || column.radio)) {
						value = typeof value === 'undefined' || value === null ?
							_this7.options.undefinedText : value;
					}

					if (column.searchable && _this7.searchText && _this7.options
						.searchHighlight && !(column.checkbox || column.radio)) {
						var defValue = '';
						var regExp = new RegExp("(".concat(_this7.searchText
								.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), ")"),
							'gim');
						var marker = '<mark>$1</mark>';
						var isHTML = value &&
							/<(?=.*? .*?\/ ?>|br|hr|input|!--|wbr)[a-z]+.*?>|<([a-z]+).*?<\/\1>/i
							.test(value);

						if (isHTML) {
							// value can contains a HTML tags
							var textContent = new DOMParser().parseFromString(value
									.toString(), 'text/html').documentElement
								.textContent;
							var textReplaced = textContent.replace(regExp, marker);
							textContent = textContent.replace(/[.*+?^${}()|[\]\\]/g,
								'\\$&');
							defValue = value.replace(new RegExp("(>\\s*)(".concat(
								textContent, ")(\\s*)"), 'gm'), "$1".concat(
								textReplaced, "$3"));
						} else {
							// but usually not
							defValue = value.toString().replace(regExp, marker);
						}

						value = Utils.calculateObjectValue(column, column
							.searchHighlightFormatter, [value, _this7
								.searchText
							], defValue);
					}

					if (item["_".concat(field, "_data")] && !Utils.isEmptyObject(
							item["_".concat(field, "_data")])) {
						for (var _i12 = 0, _Object$entries11 = Object.entries(item[
								"_".concat(field, "_data")]); _i12 <
							_Object$entries11.length; _i12++) {
							var _Object$entries11$_i = _slicedToArray(
									_Object$entries11[_i12], 2),
								_k = _Object$entries11$_i[0],
								_v = _Object$entries11$_i[1];

							// ignore data-index
							if (_k === 'index') {
								return;
							}

							data_ += " data-".concat(_k, "=\"").concat(_v, "\"");
						}
					}

					if (column.checkbox || column.radio) {
						type = column.checkbox ? 'checkbox' : type;
						type = column.radio ? 'radio' : type;
						var c = column['class'] || '';
						var isChecked = Utils.isObject(value) && value
							.hasOwnProperty('checked') ? value.checked : (value ===
								true || value_) && value !== false;
						var isDisabled = !column.checkboxEnabled || value && value
							.disabled;
						text = [_this7.options.cardView ? "<div class=\"card-view "
							.concat(c, "\">") : "<td class=\"bs-checkbox "
							.concat(c, "\"").concat(class_).concat(style_, ">"),
							"\n            <input class='form-check-input' \n            data-index=\""
							.concat(i, "\"\n            name=\"").concat(_this7
								.options.selectItemName,
								"\"\n            type=\"").concat(type,
								"\"\n            ").concat(Utils.sprintf(
									'value="%s"', item[_this7.options.idField]),
								"\n            ").concat(Utils.sprintf(
								'checked="%s"', isChecked ? 'checked' :
								undefined), "\n            ").concat(Utils
								.sprintf('disabled="%s"', isDisabled ?
									'disabled' : undefined), " />"), _this7
							.header.formatters[j] && typeof value === 'string' ?
							value : '', _this7.options.cardView ? '</div>' :
							'</td>'
						].join('');
						item[_this7.header.stateField] = value === true || !!
							value_ || value && value.checked;
					} else if (_this7.options.cardView) {
						var cardTitle = _this7.options.showHeader ?
							"<span class=\"card-view-title ".concat(cellStyle
								.classes || '', "\"").concat(style_, ">").concat(
								Utils.getFieldTitle(_this7.columns, field),
								"</span>") : '';
						text = "<div class=\"card-view\">".concat(cardTitle,
							"<span class=\"card-view-value ").concat(cellStyle
							.classes || '', "\"").concat(style_, ">").concat(
							value, "</span></div>");

						if (_this7.options.smartDisplay && value === '') {
							text = '<div class="card-view"></div>';
						}
					} else {
						text = "<td".concat(id_).concat(class_).concat(style_)
							.concat(data_).concat(rowspan_).concat(colspan_).concat(
								title_, ">").concat(value, "</td>");
					}

					html.push(text);
				});

				if (detailViewTemplate && this.options.detailViewAlign === 'right') {
					html.push(detailViewTemplate);
				}

				if (this.options.cardView) {
					html.push('</div></td>');
				}

				html.push('</tr>');
				return html.join('');
			}
		}, {
			key: "initBody",
			value: function initBody(fixedScroll, updatedUid) {
				var _this8 = this;

				var data = this.getData();
				this.trigger('pre-body', data);
				this.$body = this.$el.find('>tbody');

				if (!this.$body.length) {
					this.$body = $__default["default"]('<tbody></tbody>').appendTo(this
						.$el);
				} // Fix #389 Bootstrap-table-flatJSON is not working


				if (!this.options.pagination || this.options.sidePagination === 'server') {
					this.pageFrom = 1;
					this.pageTo = data.length;
				}

				var rows = [];
				var trFragments = $__default["default"](document.createDocumentFragment());
				var hasTr = false;
				var toExpand = [];
				this.autoMergeCells = Utils.checkAutoMergeCells(data.slice(this.pageFrom -
					1, this.pageTo));

				for (var i = this.pageFrom - 1; i < this.pageTo; i++) {
					var item = data[i];
					var tr = this.initRow(item, i, data, trFragments);
					hasTr = hasTr || !!tr;

					if (tr && typeof tr === 'string') {
						var uniqueId = this.options.uniqueId;

						if (uniqueId && item.hasOwnProperty(uniqueId)) {
							var itemUniqueId = item[uniqueId];
							var oldTr = this.$body.find(Utils.sprintf(
								'> tr[data-uniqueid="%s"][data-has-detail-view]',
								itemUniqueId));
							var oldTrNext = oldTr.next();

							if (oldTrNext.is('tr.detail-view')) {
								toExpand.push(i);

								if (!updatedUid || itemUniqueId !== updatedUid) {
									tr += oldTrNext[0].outerHTML;
								}
							}
						}

						if (!this.options.virtualScroll) {
							trFragments.append(tr);
						} else {
							rows.push(tr);
						}
					}
				} // show no records


				if (!hasTr) {
					this.$body.html("<tr class=\"no-records-found\">".concat(Utils.sprintf(
						'<td colspan="%s">%s</td>', this.getVisibleFields()
						.length + Utils.getDetailViewIndexOffset(this.options),
						this.options.formatNoMatches()), "</tr>"));
				} else if (!this.options.virtualScroll) {
					this.$body.html(trFragments);
				} else {
					if (this.virtualScroll) {
						this.virtualScroll.destroy();
					}

					this.virtualScroll = new VirtualScroll({
						rows: rows,
						fixedScroll: fixedScroll,
						scrollEl: this.$tableBody[0],
						contentEl: this.$body[0],
						itemHeight: this.options.virtualScrollItemHeight,
						callback: function callback(startIndex, endIndex) {
							_this8.fitHeader();

							_this8.initBodyEvent();

							_this8.trigger('virtual-scroll', startIndex,
								endIndex);
						}
					});
				}

				toExpand.forEach(function(index) {
					_this8.expandRow(index);
				});

				if (!fixedScroll) {
					this.scrollTo(0);
				}

				this.initBodyEvent();
				this.initFooter();
				this.resetView();
				this.updateSelected();

				if (this.options.sidePagination !== 'server') {
					this.options.totalRows = data.length;
				}

				this.trigger('post-body', data);
			}
		}, {
			key: "initBodyEvent",
			value: function initBodyEvent() {
				var _this9 = this;

				// click to select by column
				this.$body.find('> tr[data-index] > td').off('click dblclick').on(
					'click dblclick',
					function(e) {
						var $td = $__default["default"](e.currentTarget);
						var $tr = $td.parent();
						var $cardViewArr = $__default["default"](e.target).parents(
							'.card-views').children();
						var $cardViewTarget = $__default["default"](e.target).parents(
							'.card-view');
						var rowIndex = $tr.data('index');
						var item = _this9.data[rowIndex];
						var index = _this9.options.cardView ? $cardViewArr.index(
							$cardViewTarget) : $td[0].cellIndex;

						var fields = _this9.getVisibleFields();

						var field = fields[index - Utils.getDetailViewIndexOffset(_this9
							.options)];
						var column = _this9.columns[_this9.fieldsColumnsIndex[field]];
						var value = Utils.getItemField(item, field, _this9.options
							.escape, column.escape);

						if ($td.find('.detail-icon').length) {
							return;
						}

						_this9.trigger(e.type === 'click' ? 'click-cell' :
							'dbl-click-cell', field, value, item, $td);

						_this9.trigger(e.type === 'click' ? 'click-row' :
							'dbl-click-row', item, $tr, field
						); // if click to select - then trigger the checkbox/radio click


						if (e.type === 'click' && _this9.options.clickToSelect && column
							.clickToSelect && !Utils.calculateObjectValue(_this9
								.options, _this9.options.ignoreClickToSelectOn, [e
									.target
								])) {
							var $selectItem = $tr.find(Utils.sprintf('[name="%s"]',
								_this9.options.selectItemName));

							if ($selectItem.length) {
								$selectItem[0].click();
							}
						}

						if (e.type === 'click' && _this9.options.detailViewByClick) {
							_this9.toggleDetailView(rowIndex, _this9.header
								.detailFormatters[_this9.fieldsColumnsIndex[field]]);
						}
					}).off('mousedown').on('mousedown', function(e) {
					// https://github.com/jquery/jquery/issues/1741
					_this9.multipleSelectRowCtrlKey = e.ctrlKey || e.metaKey;
					_this9.multipleSelectRowShiftKey = e.shiftKey;
				});
				this.$body.find('> tr[data-index] > td > .detail-icon').off('click').on(
					'click',
					function(e) {
						e.preventDefault();

						_this9.toggleDetailView($__default["default"](e.currentTarget)
							.parent().parent().data('index'));

						return false;
					});
				this.$selectItem = this.$body.find(Utils.sprintf('[name="%s"]', this.options
					.selectItemName));
				this.$selectItem.off('click').on('click', function(e) {
					e.stopImmediatePropagation();
					var $this = $__default["default"](e.currentTarget);

					_this9._toggleCheck($this.prop('checked'), $this.data('index'));
				});
				this.header.events.forEach(function(_events, i) {
					var events = _events;

					if (!events) {
						return;
					} // fix bug, if events is defined with namespace


					if (typeof events === 'string') {
						events = Utils.calculateObjectValue(null, events);
					}

					if (!events) {
						throw new Error("Unknown event in the scope: ".concat(
							_events));
					}

					var field = _this9.header.fields[i];

					var fieldIndex = _this9.getVisibleFields().indexOf(field);

					if (fieldIndex === -1) {
						return;
					}

					fieldIndex += Utils.getDetailViewIndexOffset(_this9.options);

					var _loop2 = function _loop2(key) {
						if (!events.hasOwnProperty(key)) {
							return "continue";
						}

						var event = events[key];

						_this9.$body.find('>tr:not(.no-records-found)').each(
							function(i, tr) {
								var $tr = $__default["default"](tr);
								var $td = $tr.find(_this9.options.cardView ?
										'.card-views>.card-view' : '>td')
									.eq(fieldIndex);
								var index = key.indexOf(' ');
								var name = key.substring(0, index);
								var el = key.substring(index + 1);
								$td.find(el).off(name).on(name, function(
									e) {
									var index = $tr.data('index');
									var row = _this9.data[index];
									var value = row[field];
									event.apply(_this9, [e, value,
										row, index
									]);
								});
							});
					};

					for (var key in events) {
						var _ret2 = _loop2(key);

						if (_ret2 === "continue") continue;
					}
				});
			}
		}, {
			key: "initServer",
			value: function initServer(silent, query, url) {
				var _this10 = this;

				var data = {};
				var index = this.header.fields.indexOf(this.options.sortName);
				var params = {
					searchText: this.searchText,
					sortName: this.options.sortName,
					sortOrder: this.options.sortOrder
				};

				if (this.header.sortNames[index]) {
					params.sortName = this.header.sortNames[index];
				}

				if (this.options.pagination && this.options.sidePagination === 'server') {
					params.pageSize = this.options.pageSize === this.options
						.formatAllRows() ? this.options.totalRows : this.options.pageSize;
					params.pageNumber = this.options.pageNumber;
				}

				if (!(url || this.options.url) && !this.options.ajax) {
					return;
				}

				if (this.options.queryParamsType === 'limit') {
					params = {
						search: params.searchText,
						sort: params.sortName,
						order: params.sortOrder
					};

					if (this.options.pagination && this.options.sidePagination ===
						'server') {
						params.offset = this.options.pageSize === this.options
							.formatAllRows() ? 0 : this.options.pageSize * (this.options
								.pageNumber - 1);
						params.limit = this.options.pageSize;

						if (params.limit === 0 || this.options.pageSize === this.options
							.formatAllRows()) {
							delete params.limit;
						}
					}
				}

				if (this.options.search && this.options.sidePagination === 'server' && this
					.columns.filter(function(column) {
						return !column.searchable;
					}).length) {
					params.searchable = [];

					var _iterator2 = _createForOfIteratorHelper(this.columns),
						_step2;

					try {
						for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
							var column = _step2.value;

							if (!column.checkbox && column.searchable && (this.options
									.visibleSearch && column.visible || !this.options
									.visibleSearch)) {
								params.searchable.push(column.field);
							}
						}
					} catch (err) {
						_iterator2.e(err);
					} finally {
						_iterator2.f();
					}
				}

				if (!Utils.isEmptyObject(this.filterColumnsPartial)) {
					params.filter = JSON.stringify(this.filterColumnsPartial, null);
				}

				$__default["default"].extend(params, query || {});
				data = Utils.calculateObjectValue(this.options, this.options.queryParams, [
					params
				], data); // false to stop request

				if (data === false) {
					return;
				}

				if (!silent) {
					this.showLoading();
				}

				var request = $__default["default"].extend({}, Utils.calculateObjectValue(
					null, this.options.ajaxOptions), {
					type: this.options.method,
					url: url || this.options.url,
					data: this.options.contentType === 'application/json' && this
						.options.method === 'post' ? JSON.stringify(data) : data,
					cache: this.options.cache,
					contentType: this.options.contentType,
					dataType: this.options.dataType,
					success: function success(_res, textStatus, jqXHR) {
						var res = Utils.calculateObjectValue(_this10.options,
							_this10.options.responseHandler, [_res, jqXHR],
							_res);

						_this10.load(res);

						_this10.trigger('load-success', res, jqXHR && jqXHR
							.status, jqXHR);

						if (!silent) {
							_this10.hideLoading();
						}

						if (_this10.options.sidePagination === 'server' &&
							_this10.options.pageNumber > 1 && res[_this10
								.options.totalField] > 0 && !res[_this10.options
								.dataField].length) {
							_this10.updatePagination();
						}
					},
					error: function error(jqXHR) {
						// abort ajax by multiple request
						if (jqXHR && jqXHR.status === 0 && _this10._xhrAbort) {
							_this10._xhrAbort = false;
							return;
						}

						var data = [];

						if (_this10.options.sidePagination === 'server') {
							data = {};
							data[_this10.options.totalField] = 0;
							data[_this10.options.dataField] = [];
						}

						_this10.load(data);

						_this10.trigger('load-error', jqXHR && jqXHR.status,
							jqXHR);

						if (!silent) {
							_this10.hideLoading();
						}
					}
				});

				if (this.options.ajax) {
					Utils.calculateObjectValue(this, this.options.ajax, [request], null);
				} else {
					if (this._xhr && this._xhr.readyState !== 4) {
						this._xhrAbort = true;

						this._xhr.abort();
					}

					this._xhr = $__default["default"].ajax(request);
				}

				return data;
			}
		}, {
			key: "initSearchText",
			value: function initSearchText() {
				if (this.options.search) {
					this.searchText = '';

					if (this.options.searchText !== '') {
						var $search = Utils.getSearchInput(this);
						$search.val(this.options.searchText);
						this.onSearch({
							currentTarget: $search,
							firedByInitSearchText: true
						});
					}
				}
			}
		}, {
			key: "getCaret",
			value: function getCaret() {
				var _this11 = this;

				this.$header.find('th').each(function(i, th) {
					$__default["default"](th).find('.sortable').removeClass(
						'desc asc').addClass($__default["default"](th).data(
							'field') === _this11.options.sortName ? _this11
						.options.sortOrder : 'both');
				});
			}
		}, {
			key: "updateSelected",
			value: function updateSelected() {
				var checkAll = this.$selectItem.filter(':enabled').length && this
					.$selectItem.filter(':enabled').length === this.$selectItem.filter(
						':enabled').filter(':checked').length;
				this.$selectAll.add(this.$selectAll_).prop('checked', checkAll);
				this.$selectItem.each(function(i, el) {
					$__default["default"](el).closest('tr')[$__default["default"](
						el).prop('checked') ? 'addClass' : 'removeClass'](
						'selected');
				});
			}
		}, {
			key: "updateRows",
			value: function updateRows() {
				var _this12 = this;

				this.$selectItem.each(function(i, el) {
					_this12.data[$__default["default"](el).data('index')][_this12
						.header.stateField
					] = $__default["default"](el).prop('checked');
				});
			}
		}, {
			key: "resetRows",
			value: function resetRows() {
				var _iterator3 = _createForOfIteratorHelper(this.data),
					_step3;

				try {
					for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
						var row = _step3.value;
						this.$selectAll.prop('checked', false);
						this.$selectItem.prop('checked', false);

						if (this.header.stateField) {
							row[this.header.stateField] = false;
						}
					}
				} catch (err) {
					_iterator3.e(err);
				} finally {
					_iterator3.f();
				}

				this.initHiddenRows();
			}
		}, {
			key: "trigger",
			value: function trigger(_name) {
				var _this$options, _this$options2;

				var name = "".concat(_name, ".bs.table");

				for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0),
						_key4 = 1; _key4 < _len; _key4++) {
					args[_key4 - 1] = arguments[_key4];
				}

				(_this$options = this.options)[BootstrapTable.EVENTS[name]].apply(
					_this$options, [].concat(args, [this]));

				this.$el.trigger($__default["default"].Event(name, {
					sender: this
				}), args);

				(_this$options2 = this.options).onAll.apply(_this$options2, [name].concat([]
					.concat(args, [this])));

				this.$el.trigger($__default["default"].Event('all.bs.table', {
					sender: this
				}), [name, args]);
			}
		}, {
			key: "resetHeader",
			value: function resetHeader() {
				var _this13 = this;

				// fix #61: the hidden table reset header bug.
				// fix bug: get $el.css('width') error sometime (height = 500)
				clearTimeout(this.timeoutId_);
				this.timeoutId_ = setTimeout(function() {
					return _this13.fitHeader();
				}, this.$el.is(':hidden') ? 100 : 0);
			}
		}, {
			key: "fitHeader",
			value: function fitHeader() {
				var _this14 = this;

				if (this.$el.is(':hidden')) {
					this.timeoutId_ = setTimeout(function() {
						return _this14.fitHeader();
					}, 100);
					return;
				}

				var fixedBody = this.$tableBody.get(0);
				var scrollWidth = this.hasScrollBar && fixedBody.scrollHeight > fixedBody
					.clientHeight + this.$header.outerHeight() ? Utils.getScrollBarWidth() :
					0;
				this.$el.css('margin-top', -this.$header.outerHeight());
				var focused = $__default["default"](':focus');

				if (focused.length > 0) {
					var $th = focused.parents('th');

					if ($th.length > 0) {
						var dataField = $th.attr('data-field');

						if (dataField !== undefined) {
							var $headerTh = this.$header.find("[data-field='".concat(
								dataField, "']"));

							if ($headerTh.length > 0) {
								$headerTh.find(':input').addClass('focus-temp');
							}
						}
					}
				}

				this.$header_ = this.$header.clone(true, true);
				this.$selectAll_ = this.$header_.find('[name="btSelectAll"]');
				this.$tableHeader.css('margin-right', scrollWidth).find('table').css(
					'width', this.$el.outerWidth()).html('').attr('class', this.$el
					.attr('class')).append(this.$header_);
				this.$tableLoading.css('width', this.$el.outerWidth());
				var focusedTemp = $__default["default"]('.focus-temp:visible:eq(0)');

				if (focusedTemp.length > 0) {
					focusedTemp.focus();
					this.$header.find('.focus-temp').removeClass('focus-temp');
				} // fix bug: $.data() is not working as expected after $.append()


				this.$header.find('th[data-field]').each(function(i, el) {
					_this14.$header_.find(Utils.sprintf('th[data-field="%s"]',
						$__default["default"](el).data('field'))).data(
						$__default["default"](el).data());
				});
				var visibleFields = this.getVisibleFields();
				var $ths = this.$header_.find('th');
				var $tr = this.$body.find('>tr:not(.no-records-found,.virtual-scroll-top)')
					.eq(0);

				while ($tr.length && $tr.find('>td[colspan]:not([colspan="1"])').length) {
					$tr = $tr.next();
				}

				var trLength = $tr.find('> *').length;
				$tr.find('> *').each(function(i, el) {
					var $this = $__default["default"](el);

					if (Utils.hasDetailViewIcon(_this14.options)) {
						if (i === 0 && _this14.options.detailViewAlign !==
							'right' || i === trLength - 1 && _this14.options
							.detailViewAlign === 'right') {
							var $thDetail = $ths.filter('.detail');

							var _zoomWidth = $thDetail.innerWidth() - $thDetail
								.find('.fht-cell').width();

							$thDetail.find('.fht-cell').width($this.innerWidth() -
								_zoomWidth);
							return;
						}
					}

					var index = i - Utils.getDetailViewIndexOffset(_this14.options);

					var $th = _this14.$header_.find(Utils.sprintf(
						'th[data-field="%s"]', visibleFields[index]));

					if ($th.length > 1) {
						$th = $__default["default"]($ths[$this[0].cellIndex]);
					}

					var zoomWidth = $th.innerWidth() - $th.find('.fht-cell')
						.width();
					$th.find('.fht-cell').width($this.innerWidth() - zoomWidth);
				});
				this.horizontalScroll();
				this.trigger('post-header');
			}
		}, {
			key: "initFooter",
			value: function initFooter() {
				if (!this.options.showFooter || this.options.cardView) {
					// do nothing
					return;
				}

				var data = this.getData();
				var html = [];
				var detailTemplate = '';

				if (Utils.hasDetailViewIcon(this.options)) {
					detailTemplate =
						'<th class="detail"><div class="th-inner"></div><div class="fht-cell"></div></th>';
				}

				if (detailTemplate && this.options.detailViewAlign !== 'right') {
					html.push(detailTemplate);
				}

				var _iterator4 = _createForOfIteratorHelper(this.columns),
					_step4;

				try {
					for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
						var column = _step4.value;
						var falign = '';
						var valign = '';
						var csses = [];
						var style = {};
						var class_ = Utils.sprintf(' class="%s"', column['class']);

						if (!column.visible || this.footerData && this.footerData.length >
							0 && !(column.field in this.footerData[0])) {
							continue;
						}

						if (this.options.cardView && !column.cardVisible) {
							return;
						}

						falign = Utils.sprintf('text-align: %s; ', column.falign ? column
							.falign : column.align);
						valign = Utils.sprintf('vertical-align: %s; ', column.valign);
						style = Utils.calculateObjectValue(null, this.options.footerStyle, [
							column
						]);

						if (style && style.css) {
							for (var _i13 = 0, _Object$entries12 = Object.entries(style
									.css); _i13 < _Object$entries12.length; _i13++) {
								var _Object$entries12$_i = _slicedToArray(_Object$entries12[
										_i13], 2),
									key = _Object$entries12$_i[0],
									_value3 = _Object$entries12$_i[1];

								csses.push("".concat(key, ": ").concat(_value3));
							}
						}

						if (style && style.classes) {
							class_ = Utils.sprintf(' class="%s"', column['class'] ? [column[
								'class'], style.classes].join(' ') : style.classes);
						}

						html.push('<th', class_, Utils.sprintf(' style="%s"', falign +
							valign + csses.concat().join('; ')));
						var colspan = 0;

						if (this.footerData && this.footerData.length > 0) {
							colspan = this.footerData[0]["_".concat(column.field,
								"_colspan")] || 0;
						}

						if (colspan) {
							html.push(" colspan=\"".concat(colspan, "\" "));
						}

						html.push('>');
						html.push('<div class="th-inner">');
						var value = '';

						if (this.footerData && this.footerData.length > 0) {
							value = this.footerData[0][column.field] || '';
						}

						html.push(Utils.calculateObjectValue(column, column.footerFormatter,
							[data, value], value));
						html.push('</div>');
						html.push('<div class="fht-cell"></div>');
						html.push('</div>');
						html.push('</th>');
					}
				} catch (err) {
					_iterator4.e(err);
				} finally {
					_iterator4.f();
				}

				if (detailTemplate && this.options.detailViewAlign === 'right') {
					html.push(detailTemplate);
				}

				if (!this.options.height && !this.$tableFooter.length) {
					this.$el.append('<tfoot><tr></tr></tfoot>');
					this.$tableFooter = this.$el.find('tfoot');
				}

				if (!this.$tableFooter.find('tr').length) {
					this.$tableFooter.html('<table><thead><tr></tr></thead></table>');
				}

				this.$tableFooter.find('tr').html(html.join(''));
				this.trigger('post-footer', this.$tableFooter);
			}
		}, {
			key: "fitFooter",
			value: function fitFooter() {
				var _this15 = this;

				if (this.$el.is(':hidden')) {
					setTimeout(function() {
						return _this15.fitFooter();
					}, 100);
					return;
				}

				var fixedBody = this.$tableBody.get(0);
				var scrollWidth = this.hasScrollBar && fixedBody.scrollHeight > fixedBody
					.clientHeight + this.$header.outerHeight() ? Utils.getScrollBarWidth() :
					0;
				this.$tableFooter.css('margin-right', scrollWidth).find('table').css(
					'width', this.$el.outerWidth()).attr('class', this.$el.attr(
					'class'));
				var $ths = this.$tableFooter.find('th');
				var $tr = this.$body.find('>tr:first-child:not(.no-records-found)');
				$ths.find('.fht-cell').width('auto');

				while ($tr.length && $tr.find('>td[colspan]:not([colspan="1"])').length) {
					$tr = $tr.next();
				}

				var trLength = $tr.find('> *').length;
				$tr.find('> *').each(function(i, el) {
					var $this = $__default["default"](el);

					if (Utils.hasDetailViewIcon(_this15.options)) {
						if (i === 0 && _this15.options.detailViewAlign === 'left' ||
							i === trLength - 1 && _this15.options
							.detailViewAlign === 'right') {
							var $thDetail = $ths.filter('.detail');

							var _zoomWidth2 = $thDetail.innerWidth() - $thDetail
								.find('.fht-cell').width();

							$thDetail.find('.fht-cell').width($this.innerWidth() -
								_zoomWidth2);
							return;
						}
					}

					var $th = $ths.eq(i);
					var zoomWidth = $th.innerWidth() - $th.find('.fht-cell')
						.width();
					$th.find('.fht-cell').width($this.innerWidth() - zoomWidth);
				});
				this.horizontalScroll();
			}
		}, {
			key: "horizontalScroll",
			value: function horizontalScroll() {
				var _this16 = this;

				// horizontal scroll event
				// TODO: it's probably better improving the layout than binding to scroll event
				this.$tableBody.off('scroll').on('scroll', function() {
					var scrollLeft = _this16.$tableBody.scrollLeft();

					if (_this16.options.showHeader && _this16.options.height) {
						_this16.$tableHeader.scrollLeft(scrollLeft);
					}

					if (_this16.options.showFooter && !_this16.options.cardView) {
						_this16.$tableFooter.scrollLeft(scrollLeft);
					}

					_this16.trigger('scroll-body', _this16.$tableBody);
				});
			}
		}, {
			key: "getVisibleFields",
			value: function getVisibleFields() {
				var visibleFields = [];

				var _iterator5 = _createForOfIteratorHelper(this.header.fields),
					_step5;

				try {
					for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
						var field = _step5.value;
						var column = this.columns[this.fieldsColumnsIndex[field]];

						if (!column || !column.visible || this.options.cardView && !column
							.cardVisible) {
							continue;
						}

						visibleFields.push(field);
					}
				} catch (err) {
					_iterator5.e(err);
				} finally {
					_iterator5.f();
				}

				return visibleFields;
			}
		}, {
			key: "initHiddenRows",
			value: function initHiddenRows() {
				this.hiddenRows = [];
			} // PUBLIC FUNCTION DEFINITION
			// =======================

		}, {
			key: "getOptions",
			value: function getOptions() {
				// deep copy and remove data
				var options = $__default["default"].extend({}, this.options);
				delete options.data;
				return $__default["default"].extend(true, {}, options);
			}
		}, {
			key: "refreshOptions",
			value: function refreshOptions(options) {
				// If the objects are equivalent then avoid the call of destroy / init methods
				if (Utils.compareObjects(this.options, options, true)) {
					return;
				}

				this.options = $__default["default"].extend(this.options, options);
				this.trigger('refresh-options', this.options);
				this.destroy();
				this.init();
			}
		}, {
			key: "getData",
			value: function getData(params) {
				var _this17 = this;

				var data = this.options.data;

				if ((this.searchText || this.options.customSearch || this.options
						.sortName !== undefined || this.enableCustomSort ||
						// Fix #4616: this.enableCustomSort is for extensions
						!Utils.isEmptyObject(this.filterColumns) || !Utils.isEmptyObject(
							this.filterColumnsPartial)) && (!params || !params
						.unfiltered)) {
					data = this.data;
				}

				if (params && params.useCurrentPage) {
					data = data.slice(this.pageFrom - 1, this.pageTo);
				}

				if (params && !params.includeHiddenRows) {
					var hiddenRows = this.getHiddenRows();
					data = data.filter(function(row) {
						return Utils.findIndex(hiddenRows, row) === -1;
					});
				}

				if (params && params.formatted) {
					data.forEach(function(row) {
						for (var _i14 = 0, _Object$entries13 = Object.entries(
								row); _i14 < _Object$entries13.length; _i14++) {
							var _Object$entries13$_i = _slicedToArray(
									_Object$entries13[_i14], 2),
								key = _Object$entries13$_i[0],
								value = _Object$entries13$_i[1];

							var column = _this17.columns[_this17.fieldsColumnsIndex[
								key]];

							if (!column) {
								return;
							}

							row[key] = Utils.calculateObjectValue(column, _this17
								.header.formatters[column.fieldIndex], [value,
									row, row.index, column.field
								], value);
						}
					});
				}

				return data;
			}
		}, {
			key: "getSelections",
			value: function getSelections() {
				var _this18 = this;

				return (this.options.maintainMetaData ? this.options.data : this.data)
					.filter(function(row) {
						return row[_this18.header.stateField] === true;
					});
			}
		}, {
			key: "load",
			value: function load(_data) {
				var fixedScroll = false;
				var data = _data; // #431: support pagination

				if (this.options.pagination && this.options.sidePagination === 'server') {
					this.options.totalRows = data[this.options.totalField];
					this.options.totalNotFiltered = data[this.options
						.totalNotFilteredField];
					this.footerData = data[this.options.footerField] ? [data[this.options
						.footerField]] : undefined;
				}

				fixedScroll = data.fixedScroll;
				data = Array.isArray(data) ? data : data[this.options.dataField];
				this.initData(data);
				this.initSearch();
				this.initPagination();
				this.initBody(fixedScroll);
			}
		}, {
			key: "append",
			value: function append(data) {
				this.initData(data, 'append');
				this.initSearch();
				this.initPagination();
				this.initSort();
				this.initBody(true);
			}
		}, {
			key: "prepend",
			value: function prepend(data) {
				this.initData(data, 'prepend');
				this.initSearch();
				this.initPagination();
				this.initSort();
				this.initBody(true);
			}
		}, {
			key: "remove",
			value: function remove(params) {
				var removed = 0;

				for (var i = this.options.data.length - 1; i >= 0; i--) {
					var row = this.options.data[i];

					if (!row.hasOwnProperty(params.field) && params.field !== '$index') {
						continue;
					}

					if (!row.hasOwnProperty(params.field) && params.field === '$index' &&
						params.values.includes(i) || params.values.includes(row[params
							.field])) {
						removed++;
						this.options.data.splice(i, 1);
					}
				}

				if (!removed) {
					return;
				}

				if (this.options.sidePagination === 'server') {
					this.options.totalRows -= removed;
					this.data = _toConsumableArray(this.options.data);
				}

				this.initSearch();
				this.initPagination();
				this.initSort();
				this.initBody(true);
			}
		}, {
			key: "removeAll",
			value: function removeAll() {
				if (this.options.data.length > 0) {
					this.options.data.splice(0, this.options.data.length);
					this.initSearch();
					this.initPagination();
					this.initBody(true);
				}
			}
		}, {
			key: "insertRow",
			value: function insertRow(params) {
				if (!params.hasOwnProperty('index') || !params.hasOwnProperty('row')) {
					return;
				}

				this.options.data.splice(params.index, 0, params.row);
				this.initSearch();
				this.initPagination();
				this.initSort();
				this.initBody(true);
			}
		}, {
			key: "updateRow",
			value: function updateRow(params) {
				var allParams = Array.isArray(params) ? params : [params];

				var _iterator6 = _createForOfIteratorHelper(allParams),
					_step6;

				try {
					for (_iterator6.s(); !(_step6 = _iterator6.n()).done;) {
						var _params = _step6.value;

						if (!_params.hasOwnProperty('index') || !_params.hasOwnProperty(
								'row')) {
							continue;
						}

						if (_params.hasOwnProperty('replace') && _params.replace) {
							this.options.data[_params.index] = _params.row;
						} else {
							$__default["default"].extend(this.options.data[_params.index],
								_params.row);
						}
					}
				} catch (err) {
					_iterator6.e(err);
				} finally {
					_iterator6.f();
				}

				this.initSearch();
				this.initPagination();
				this.initSort();
				this.initBody(true);
			}
		}, {
			key: "getRowByUniqueId",
			value: function getRowByUniqueId(_id) {
				var uniqueId = this.options.uniqueId;
				var len = this.options.data.length;
				var id = _id;
				var dataRow = null;
				var i;
				var row;
				var rowUniqueId;

				for (i = len - 1; i >= 0; i--) {
					row = this.options.data[i];

					if (row.hasOwnProperty(uniqueId)) {
						// uniqueId is a column
						rowUniqueId = row[uniqueId];
					} else if (row._data && row._data.hasOwnProperty(uniqueId)) {
						// uniqueId is a row data property
						rowUniqueId = row._data[uniqueId];
					} else {
						continue;
					}

					if (typeof rowUniqueId === 'string') {
						id = id.toString();
					} else if (typeof rowUniqueId === 'number') {
						if (Number(rowUniqueId) === rowUniqueId && rowUniqueId % 1 === 0) {
							id = parseInt(id, 10);
						} else if (rowUniqueId === Number(rowUniqueId) && rowUniqueId !==
							0) {
							id = parseFloat(id);
						}
					}

					if (rowUniqueId === id) {
						dataRow = row;
						break;
					}
				}

				return dataRow;
			}
		}, {
			key: "updateByUniqueId",
			value: function updateByUniqueId(params) {
				var allParams = Array.isArray(params) ? params : [params];
				var updatedUid = null;

				var _iterator7 = _createForOfIteratorHelper(allParams),
					_step7;

				try {
					for (_iterator7.s(); !(_step7 = _iterator7.n()).done;) {
						var _params2 = _step7.value;

						if (!_params2.hasOwnProperty('id') || !_params2.hasOwnProperty(
								'row')) {
							continue;
						}

						var rowId = this.options.data.indexOf(this.getRowByUniqueId(_params2
							.id));

						if (rowId === -1) {
							continue;
						}

						if (_params2.hasOwnProperty('replace') && _params2.replace) {
							this.options.data[rowId] = _params2.row;
						} else {
							$__default["default"].extend(this.options.data[rowId], _params2
								.row);
						}

						updatedUid = _params2.id;
					}
				} catch (err) {
					_iterator7.e(err);
				} finally {
					_iterator7.f();
				}

				this.initSearch();
				this.initPagination();
				this.initSort();
				this.initBody(true, updatedUid);
			}
		}, {
			key: "removeByUniqueId",
			value: function removeByUniqueId(id) {
				var len = this.options.data.length;
				var row = this.getRowByUniqueId(id);

				if (row) {
					this.options.data.splice(this.options.data.indexOf(row), 1);
				}

				if (len === this.options.data.length) {
					return;
				}

				if (this.options.sidePagination === 'server') {
					this.options.totalRows -= 1;
					this.data = _toConsumableArray(this.options.data);
				}

				this.initSearch();
				this.initPagination();
				this.initBody(true);
			}
		}, {
			key: "updateCell",
			value: function updateCell(params) {
				if (!params.hasOwnProperty('index') || !params.hasOwnProperty('field') || !
					params.hasOwnProperty('value')) {
					return;
				}

				this.data[params.index][params.field] = params.value;

				if (params.reinit === false) {
					return;
				}

				this.initSort();
				this.initBody(true);
			}
		}, {
			key: "updateCellByUniqueId",
			value: function updateCellByUniqueId(params) {
				var _this19 = this;

				var allParams = Array.isArray(params) ? params : [params];
				allParams.forEach(function(_ref6) {
					var id = _ref6.id,
						field = _ref6.field,
						value = _ref6.value;

					var rowId = _this19.options.data.indexOf(_this19
						.getRowByUniqueId(id));

					if (rowId === -1) {
						return;
					}

					_this19.options.data[rowId][field] = value;
				});

				if (params.reinit === false) {
					return;
				}

				this.initSort();
				this.initBody(true);
			}
		}, {
			key: "showRow",
			value: function showRow(params) {
				this._toggleRow(params, true);
			}
		}, {
			key: "hideRow",
			value: function hideRow(params) {
				this._toggleRow(params, false);
			}
		}, {
			key: "_toggleRow",
			value: function _toggleRow(params, visible) {
				var row;

				if (params.hasOwnProperty('index')) {
					row = this.getData()[params.index];
				} else if (params.hasOwnProperty('uniqueId')) {
					row = this.getRowByUniqueId(params.uniqueId);
				}

				if (!row) {
					return;
				}

				var index = Utils.findIndex(this.hiddenRows, row);

				if (!visible && index === -1) {
					this.hiddenRows.push(row);
				} else if (visible && index > -1) {
					this.hiddenRows.splice(index, 1);
				}

				this.initBody(true);
				this.initPagination();
			}
		}, {
			key: "getHiddenRows",
			value: function getHiddenRows(show) {
				if (show) {
					this.initHiddenRows();
					this.initBody(true);
					this.initPagination();
					return;
				}

				var data = this.getData();
				var rows = [];

				var _iterator8 = _createForOfIteratorHelper(data),
					_step8;

				try {
					for (_iterator8.s(); !(_step8 = _iterator8.n()).done;) {
						var row = _step8.value;

						if (this.hiddenRows.includes(row)) {
							rows.push(row);
						}
					}
				} catch (err) {
					_iterator8.e(err);
				} finally {
					_iterator8.f();
				}

				this.hiddenRows = rows;
				return rows;
			}
		}, {
			key: "showColumn",
			value: function showColumn(field) {
				var _this20 = this;

				var fields = Array.isArray(field) ? field : [field];
				fields.forEach(function(field) {
					_this20._toggleColumn(_this20.fieldsColumnsIndex[field], true,
						true);
				});
			}
		}, {
			key: "hideColumn",
			value: function hideColumn(field) {
				var _this21 = this;

				var fields = Array.isArray(field) ? field : [field];
				fields.forEach(function(field) {
					_this21._toggleColumn(_this21.fieldsColumnsIndex[field], false,
						true);
				});
			}
		}, {
			key: "_toggleColumn",
			value: function _toggleColumn(index, checked, needUpdate) {
				if (index === -1 || this.columns[index].visible === checked) {
					return;
				}

				this.columns[index].visible = checked;
				this.initHeader();
				this.initSearch();
				this.initPagination();
				this.initBody();

				if (this.options.showColumns) {
					var $items = this.$toolbar.find('.keep-open input:not(".toggle-all")')
						.prop('disabled', false);

					if (needUpdate) {
						$items.filter(Utils.sprintf('[value="%s"]', index)).prop('checked',
							checked);
					}

					if ($items.filter(':checked').length <= this.options
						.minimumCountColumns) {
						$items.filter(':checked').prop('disabled', true);
					}
				}
			}
		}, {
			key: "getVisibleColumns",
			value: function getVisibleColumns() {
				var _this22 = this;

				return this.columns.filter(function(column) {
					return column.visible && !_this22.isSelectionColumn(column);
				});
			}
		}, {
			key: "getHiddenColumns",
			value: function getHiddenColumns() {
				return this.columns.filter(function(_ref7) {
					var visible = _ref7.visible;
					return !visible;
				});
			}
		}, {
			key: "isSelectionColumn",
			value: function isSelectionColumn(column) {
				return column.radio || column.checkbox;
			}
		}, {
			key: "showAllColumns",
			value: function showAllColumns() {
				this._toggleAllColumns(true);
			}
		}, {
			key: "hideAllColumns",
			value: function hideAllColumns() {
				this._toggleAllColumns(false);
			}
		}, {
			key: "_toggleAllColumns",
			value: function _toggleAllColumns(visible) {
				var _this23 = this;

				var _iterator9 = _createForOfIteratorHelper(this.columns.slice().reverse()),
					_step9;

				try {
					for (_iterator9.s(); !(_step9 = _iterator9.n()).done;) {
						var column = _step9.value;

						if (column.switchable) {
							if (!visible && this.options.showColumns && this
								.getVisibleColumns().filter(function(it) {
									return it.switchable;
								}).length === this.options.minimumCountColumns) {
								continue;
							}

							column.visible = visible;
						}
					}
				} catch (err) {
					_iterator9.e(err);
				} finally {
					_iterator9.f();
				}

				this.initHeader();
				this.initSearch();
				this.initPagination();
				this.initBody();

				if (this.options.showColumns) {
					var $items = this.$toolbar.find(
						'.keep-open input[type="checkbox"]:not(".toggle-all")').prop(
						'disabled', false);

					if (visible) {
						$items.prop('checked', visible);
					} else {
						$items.get().reverse().forEach(function(item) {
							if ($items.filter(':checked').length > _this23.options
								.minimumCountColumns) {
								$__default["default"](item).prop('checked',
									visible);
							}
						});
					}

					if ($items.filter(':checked').length <= this.options
						.minimumCountColumns) {
						$items.filter(':checked').prop('disabled', true);
					}
				}
			}
		}, {
			key: "mergeCells",
			value: function mergeCells(options) {
				var row = options.index;
				var col = this.getVisibleFields().indexOf(options.field);
				var rowspan = options.rowspan || 1;
				var colspan = options.colspan || 1;
				var i;
				var j;
				var $tr = this.$body.find('>tr[data-index]');
				col += Utils.getDetailViewIndexOffset(this.options);
				var $td = $tr.eq(row).find('>td').eq(col);

				if (row < 0 || col < 0 || row >= this.data.length) {
					return;
				}

				for (i = row; i < row + rowspan; i++) {
					for (j = col; j < col + colspan; j++) {
						$tr.eq(i).find('>td').eq(j).hide();
					}
				}

				$td.attr('rowspan', rowspan).attr('colspan', colspan).show();
			}
		}, {
			key: "checkAll",
			value: function checkAll() {
				this._toggleCheckAll(true);
			}
		}, {
			key: "uncheckAll",
			value: function uncheckAll() {
				this._toggleCheckAll(false);
			}
		}, {
			key: "_toggleCheckAll",
			value: function _toggleCheckAll(checked) {
				var rowsBefore = this.getSelections();
				this.$selectAll.add(this.$selectAll_).prop('checked', checked);
				this.$selectItem.filter(':enabled').prop('checked', checked);
				this.updateRows();
				this.updateSelected();
				var rowsAfter = this.getSelections();

				if (checked) {
					this.trigger('check-all', rowsAfter, rowsBefore);
					return;
				}

				this.trigger('uncheck-all', rowsAfter, rowsBefore);
			}
		}, {
			key: "checkInvert",
			value: function checkInvert() {
				var $items = this.$selectItem.filter(':enabled');
				var checked = $items.filter(':checked');
				$items.each(function(i, el) {
					$__default["default"](el).prop('checked', !$__default["default"]
						(el).prop('checked'));
				});
				this.updateRows();
				this.updateSelected();
				this.trigger('uncheck-some', checked);
				checked = this.getSelections();
				this.trigger('check-some', checked);
			}
		}, {
			key: "check",
			value: function check(index) {
				this._toggleCheck(true, index);
			}
		}, {
			key: "uncheck",
			value: function uncheck(index) {
				this._toggleCheck(false, index);
			}
		}, {
			key: "_toggleCheck",
			value: function _toggleCheck(checked, index) {
				var $el = this.$selectItem.filter("[data-index=\"".concat(index, "\"]"));
				var row = this.data[index];

				if ($el.is(':radio') || this.options.singleSelect || this.options
					.multipleSelectRow && !this.multipleSelectRowCtrlKey && !this
					.multipleSelectRowShiftKey) {
					var _iterator10 = _createForOfIteratorHelper(this.options.data),
						_step10;

					try {
						for (_iterator10.s(); !(_step10 = _iterator10.n()).done;) {
							var r = _step10.value;
							r[this.header.stateField] = false;
						}
					} catch (err) {
						_iterator10.e(err);
					} finally {
						_iterator10.f();
					}

					this.$selectItem.filter(':checked').not($el).prop('checked', false);
				}

				row[this.header.stateField] = checked;

				if (this.options.multipleSelectRow) {
					if (this.multipleSelectRowShiftKey && this
						.multipleSelectRowLastSelectedIndex >= 0) {
						var _ref8 = this.multipleSelectRowLastSelectedIndex < index ? [this
								.multipleSelectRowLastSelectedIndex, index
							] : [index, this.multipleSelectRowLastSelectedIndex],
							_ref9 = _slicedToArray(_ref8, 2),
							fromIndex = _ref9[0],
							toIndex = _ref9[1];

						for (var i = fromIndex + 1; i < toIndex; i++) {
							this.data[i][this.header.stateField] = true;
							this.$selectItem.filter("[data-index=\"".concat(i, "\"]")).prop(
								'checked', true);
						}
					}

					this.multipleSelectRowCtrlKey = false;
					this.multipleSelectRowShiftKey = false;
					this.multipleSelectRowLastSelectedIndex = checked ? index : -1;
				}

				$el.prop('checked', checked);
				this.updateSelected();
				this.trigger(checked ? 'check' : 'uncheck', this.data[index], $el);
			}
		}, {
			key: "checkBy",
			value: function checkBy(obj) {
				this._toggleCheckBy(true, obj);
			}
		}, {
			key: "uncheckBy",
			value: function uncheckBy(obj) {
				this._toggleCheckBy(false, obj);
			}
		}, {
			key: "_toggleCheckBy",
			value: function _toggleCheckBy(checked, obj) {
				var _this24 = this;

				if (!obj.hasOwnProperty('field') || !obj.hasOwnProperty('values')) {
					return;
				}

				var rows = [];
				this.data.forEach(function(row, i) {
					if (!row.hasOwnProperty(obj.field)) {
						return false;
					}

					if (obj.values.includes(row[obj.field])) {
						var $el = _this24.$selectItem.filter(':enabled').filter(
							Utils.sprintf('[data-index="%s"]', i));

						var onlyCurrentPage = obj.hasOwnProperty(
							'onlyCurrentPage') ? obj.onlyCurrentPage : false;
						$el = checked ? $el.not(':checked') : $el.filter(
							':checked');

						if (!$el.length && onlyCurrentPage) {
							return;
						}

						$el.prop('checked', checked);
						row[_this24.header.stateField] = checked;
						rows.push(row);

						_this24.trigger(checked ? 'check' : 'uncheck', row, $el);
					}
				});
				this.updateSelected();
				this.trigger(checked ? 'check-some' : 'uncheck-some', rows);
			}
		}, {
			key: "refresh",
			value: function refresh(params) {
				if (params && params.url) {
					this.options.url = params.url;
				}

				if (params && params.pageNumber) {
					this.options.pageNumber = params.pageNumber;
				}

				if (params && params.pageSize) {
					this.options.pageSize = params.pageSize;
				}

				this.trigger('refresh', this.initServer(params && params.silent, params &&
					params.query, params && params.url));
			}
		}, {
			key: "destroy",
			value: function destroy() {
				this.$el.insertBefore(this.$container);
				$__default["default"](this.options.toolbar).insertBefore(this.$el);
				this.$container.next().remove();
				this.$container.remove();
				this.$el.html(this.$el_.html()).css('margin-top', '0').attr('class', this
					.$el_.attr('class') || ''); // reset the class
			}
		}, {
			key: "resetView",
			value: function resetView(params) {
				var padding = 0;

				if (params && params.height) {
					this.options.height = params.height;
				}

				this.$tableContainer.toggleClass('has-card-view', this.options.cardView);

				if (this.options.height) {
					var fixedBody = this.$tableBody.get(0);
					this.hasScrollBar = fixedBody.scrollWidth > fixedBody.clientWidth;
				}

				if (!this.options.cardView && this.options.showHeader && this.options
					.height) {
					this.$tableHeader.show();
					this.resetHeader();
					padding += this.$header.outerHeight(true) + 1;
				} else {
					this.$tableHeader.hide();
					this.trigger('post-header');
				}

				if (!this.options.cardView && this.options.showFooter) {
					this.$tableFooter.show();
					this.fitFooter();

					if (this.options.height) {
						padding += this.$tableFooter.outerHeight(true);
					}
				}

				if (this.$container.hasClass('fullscreen')) {
					this.$tableContainer.css('height', '');
					this.$tableContainer.css('width', '');
				} else if (this.options.height) {
					if (this.$tableBorder) {
						this.$tableBorder.css('width', '');
						this.$tableBorder.css('height', '');
					}

					var toolbarHeight = this.$toolbar.outerHeight(true);
					var paginationHeight = this.$pagination.outerHeight(true);
					var height = this.options.height - toolbarHeight - paginationHeight;
					var $bodyTable = this.$tableBody.find('>table');
					var tableHeight = $bodyTable.outerHeight();
					this.$tableContainer.css('height', "".concat(height, "px"));

					if (this.$tableBorder && $bodyTable.is(':visible')) {
						var tableBorderHeight = height - tableHeight - 2;

						if (this.hasScrollBar) {
							tableBorderHeight -= Utils.getScrollBarWidth();
						}

						this.$tableBorder.css('width', "".concat($bodyTable.outerWidth(),
							"px"));
						this.$tableBorder.css('height', "".concat(tableBorderHeight, "px"));
					}
				}

				if (this.options.cardView) {
					// remove the element css
					this.$el.css('margin-top', '0');
					this.$tableContainer.css('padding-bottom', '0');
					this.$tableFooter.hide();
				} else {
					// Assign the correct sortable arrow
					this.getCaret();
					this.$tableContainer.css('padding-bottom', "".concat(padding, "px"));
				}

				this.trigger('reset-view');
			}
		}, {
			key: "showLoading",
			value: function showLoading() {
				this.$tableLoading.toggleClass('open', true);
				var fontSize = this.options.loadingFontSize;

				if (this.options.loadingFontSize === 'auto') {
					fontSize = this.$tableLoading.width() * 0.04;
					fontSize = Math.max(12, fontSize);
					fontSize = Math.min(32, fontSize);
					fontSize = "".concat(fontSize, "px");
				}

				this.$tableLoading.find('.loading-text').css('font-size', fontSize);
			}
		}, {
			key: "hideLoading",
			value: function hideLoading() {
				this.$tableLoading.toggleClass('open', false);
			}
		}, {
			key: "togglePagination",
			value: function togglePagination() {
				this.options.pagination = !this.options.pagination;
				var icon = this.options.showButtonIcons ? this.options.pagination ? this
					.options.icons.paginationSwitchDown : this.options.icons
					.paginationSwitchUp : '';
				var text = this.options.showButtonText ? this.options.pagination ? this
					.options.formatPaginationSwitchUp() : this.options
					.formatPaginationSwitchDown() : '';
				this.$toolbar.find('button[name="paginationSwitch"]').html("".concat(Utils
					.sprintf(this.constants.html.icon, this.options.iconsPrefix,
						icon), " ").concat(text));
				this.updatePagination();
				this.trigger('toggle-pagination', this.options.pagination);
			}
		}, {
			key: "toggleFullscreen",
			value: function toggleFullscreen() {
				this.$el.closest('.bootstrap-table').toggleClass('fullscreen');
				this.resetView();
			}
		}, {
			key: "toggleView",
			value: function toggleView() {
				this.options.cardView = !this.options.cardView;
				this.initHeader();
				var icon = this.options.showButtonIcons ? this.options.cardView ? this
					.options.icons.toggleOn : this.options.icons.toggleOff : '';
				var text = this.options.showButtonText ? this.options.cardView ? this
					.options.formatToggleOff() : this.options.formatToggleOn() : '';
				this.$toolbar.find('button[name="toggle"]').html("".concat(Utils.sprintf(
						this.constants.html.icon, this.options.iconsPrefix, icon),
					" ").concat(text));
				this.initBody();
				this.trigger('toggle', this.options.cardView);
			}
		}, {
			key: "resetSearch",
			value: function resetSearch(text) {
				var $search = Utils.getSearchInput(this);
				var textToUse = text || '';
				$search.val(textToUse);
				this.searchText = textToUse;
				this.onSearch({
					currentTarget: $search
				}, false);
			}
		}, {
			key: "filterBy",
			value: function filterBy(columns, options) {
				this.filterOptions = Utils.isEmptyObject(options) ? this.options
					.filterOptions : $__default["default"].extend(this.options
						.filterOptions, options);
				this.filterColumns = Utils.isEmptyObject(columns) ? {} : columns;
				this.options.pageNumber = 1;
				this.initSearch();
				this.updatePagination();
			}
		}, {
			key: "scrollTo",
			value: function scrollTo(params) {
				var options = {
					unit: 'px',
					value: 0
				};

				if (_typeof(params) === 'object') {
					options = Object.assign(options, params);
				} else if (typeof params === 'string' && params === 'bottom') {
					options.value = this.$tableBody[0].scrollHeight;
				} else if (typeof params === 'string' || typeof params === 'number') {
					options.value = params;
				}

				var scrollTo = options.value;

				if (options.unit === 'rows') {
					scrollTo = 0;
					this.$body.find("> tr:lt(".concat(options.value, ")")).each(function(i,
						el) {
						scrollTo += $__default["default"](el).outerHeight(true);
					});
				}

				this.$tableBody.scrollTop(scrollTo);
			}
		}, {
			key: "getScrollPosition",
			value: function getScrollPosition() {
				return this.$tableBody.scrollTop();
			}
		}, {
			key: "selectPage",
			value: function selectPage(page) {
				if (page > 0 && page <= this.options.totalPages) {
					this.options.pageNumber = page;
					this.updatePagination();
				}
			}
		}, {
			key: "prevPage",
			value: function prevPage() {
				if (this.options.pageNumber > 1) {
					this.options.pageNumber--;
					this.updatePagination();
				}
			}
		}, {
			key: "nextPage",
			value: function nextPage() {
				if (this.options.pageNumber < this.options.totalPages) {
					this.options.pageNumber++;
					this.updatePagination();
				}
			}
		}, {
			key: "toggleDetailView",
			value: function toggleDetailView(index, _columnDetailFormatter) {
				var $tr = this.$body.find(Utils.sprintf('> tr[data-index="%s"]', index));

				if ($tr.next().is('tr.detail-view')) {
					this.collapseRow(index);
				} else {
					this.expandRow(index, _columnDetailFormatter);
				}

				this.resetView();
			}
		}, {
			key: "expandRow",
			value: function expandRow(index, _columnDetailFormatter) {
				var row = this.data[index];
				var $tr = this.$body.find(Utils.sprintf(
					'> tr[data-index="%s"][data-has-detail-view]', index));

				if (this.options.detailViewIcon) {
					$tr.find('a.detail-icon').html(Utils.sprintf(this.constants.html.icon,
						this.options.iconsPrefix, this.options.icons.detailClose));
				}

				if ($tr.next().is('tr.detail-view')) {
					return;
				}

				$tr.after(Utils.sprintf(
					'<tr class="detail-view"><td colspan="%s"></td></tr>', $tr
					.children('td').length));
				var $element = $tr.next().find('td');
				var detailFormatter = _columnDetailFormatter || this.options
					.detailFormatter;
				var content = Utils.calculateObjectValue(this.options, detailFormatter, [
					index, row, $element
				], '');

				if ($element.length === 1) {
					$element.append(content);
				}

				this.trigger('expand-row', index, row, $element);
			}
		}, {
			key: "expandRowByUniqueId",
			value: function expandRowByUniqueId(uniqueId) {
				var row = this.getRowByUniqueId(uniqueId);

				if (!row) {
					return;
				}

				this.expandRow(this.data.indexOf(row));
			}
		}, {
			key: "collapseRow",
			value: function collapseRow(index) {
				var row = this.data[index];
				var $tr = this.$body.find(Utils.sprintf(
					'> tr[data-index="%s"][data-has-detail-view]', index));

				if (!$tr.next().is('tr.detail-view')) {
					return;
				}

				if (this.options.detailViewIcon) {
					$tr.find('a.detail-icon').html(Utils.sprintf(this.constants.html.icon,
						this.options.iconsPrefix, this.options.icons.detailOpen));
				}

				this.trigger('collapse-row', index, row, $tr.next());
				$tr.next().remove();
			}
		}, {
			key: "collapseRowByUniqueId",
			value: function collapseRowByUniqueId(uniqueId) {
				var row = this.getRowByUniqueId(uniqueId);

				if (!row) {
					return;
				}

				this.collapseRow(this.data.indexOf(row));
			}
		}, {
			key: "expandAllRows",
			value: function expandAllRows() {
				var trs = this.$body.find('> tr[data-index][data-has-detail-view]');

				for (var i = 0; i < trs.length; i++) {
					this.expandRow($__default["default"](trs[i]).data('index'));
				}
			}
		}, {
			key: "collapseAllRows",
			value: function collapseAllRows() {
				var trs = this.$body.find('> tr[data-index][data-has-detail-view]');

				for (var i = 0; i < trs.length; i++) {
					this.collapseRow($__default["default"](trs[i]).data('index'));
				}
			}
		}, {
			key: "updateColumnTitle",
			value: function updateColumnTitle(params) {
				if (!params.hasOwnProperty('field') || !params.hasOwnProperty('title')) {
					return;
				}

				this.columns[this.fieldsColumnsIndex[params.field]].title = this.options
					.escape ? Utils.escapeHTML(params.title) : params.title;

				if (this.columns[this.fieldsColumnsIndex[params.field]].visible) {
					this.$header.find('th[data-field]').each(function(i, el) {
						if ($__default["default"](el).data('field') === params
							.field) {
							$__default["default"]($__default["default"](el).find(
								'.th-inner')[0]).text(params.title);
							return false;
						}
					});
					this.resetView();
				}
			}
		}, {
			key: "updateFormatText",
			value: function updateFormatText(formatName, text) {
				if (!/^format/.test(formatName) || !this.options[formatName]) {
					return;
				}

				if (typeof text === 'string') {
					this.options[formatName] = function() {
						return text;
					};
				} else if (typeof text === 'function') {
					this.options[formatName] = text;
				}

				this.initToolbar();
				this.initPagination();
				this.initBody();
			}
		}]);

		return BootstrapTable;
	}();

	BootstrapTable.VERSION = Constants.VERSION;
	BootstrapTable.DEFAULTS = Constants.DEFAULTS;
	BootstrapTable.LOCALES = Constants.LOCALES;
	BootstrapTable.COLUMN_DEFAULTS = Constants.COLUMN_DEFAULTS;
	BootstrapTable.METHODS = Constants.METHODS;
	BootstrapTable.EVENTS = Constants.EVENTS; // BOOTSTRAP TABLE PLUGIN DEFINITION
	// =======================

	$__default["default"].BootstrapTable = BootstrapTable;

	$__default["default"].fn.bootstrapTable = function(option) {
		for (var _len2 = arguments.length, args = new Array(_len2 > 1 ? _len2 - 1 : 0), _key5 = 1; _key5 <
			_len2; _key5++) {
			args[_key5 - 1] = arguments[_key5];
		}

		var value;
		this.each(function(i, el) {
			var data = $__default["default"](el).data('bootstrap.table');
			var options = $__default["default"].extend({}, BootstrapTable.DEFAULTS, $__default[
				"default"](el).data(), _typeof(option) === 'object' && option);

			if (typeof option === 'string') {
				var _data2;

				if (!Constants.METHODS.includes(option)) {
					throw new Error("Unknown method: ".concat(option));
				}

				if (!data) {
					return;
				}

				value = (_data2 = data)[option].apply(_data2, args);

				if (option === 'destroy') {
					$__default["default"](el).removeData('bootstrap.table');
				}
			}

			if (!data) {
				data = new $__default["default"].BootstrapTable(el, options);
				$__default["default"](el).data('bootstrap.table', data);
				data.init();
			}
		});
		return typeof value === 'undefined' ? this : value;
	};

	$__default["default"].fn.bootstrapTable.Constructor = BootstrapTable;
	$__default["default"].fn.bootstrapTable.theme = Constants.THEME;
	$__default["default"].fn.bootstrapTable.VERSION = Constants.VERSION;
	$__default["default"].fn.bootstrapTable.defaults = BootstrapTable.DEFAULTS;
	$__default["default"].fn.bootstrapTable.columnDefaults = BootstrapTable.COLUMN_DEFAULTS;
	$__default["default"].fn.bootstrapTable.events = BootstrapTable.EVENTS;
	$__default["default"].fn.bootstrapTable.locales = BootstrapTable.LOCALES;
	$__default["default"].fn.bootstrapTable.methods = BootstrapTable.METHODS;
	$__default["default"].fn.bootstrapTable.utils = Utils; // BOOTSTRAP TABLE INIT
	// =======================

	$__default["default"](function() {
		$__default["default"]('[data-toggle="table"]').bootstrapTable();
	});

	return BootstrapTable;

}));
