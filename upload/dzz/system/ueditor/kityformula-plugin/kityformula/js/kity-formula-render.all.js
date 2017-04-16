/*!
 * ====================================================
 * Kity Formula Render - v1.0.0 - 2014-07-30
 * https://github.com/kitygraph/formula
 * GitHub: https://github.com/kitygraph/formula.git 
 * Copyright (c) 2014 Baidu Kity Group; Licensed MIT
 * ====================================================
 */

(function () {
var _p = {
    r: function(index) {
        if (_p[index].inited) {
            return _p[index].value;
        }
        if (typeof _p[index].value === "function") {
            var module = {
                exports: {}
            }, returnValue = _p[index].value(null, module.exports, module);
            _p[index].inited = true;
            _p[index].value = returnValue;
            if (returnValue !== undefined) {
                return returnValue;
            } else {
                for (var key in module.exports) {
                    if (module.exports.hasOwnProperty(key)) {
                        _p[index].inited = true;
                        _p[index].value = module.exports;
                        return module.exports;
                    }
                }
            }
        } else {
            _p[index].inited = true;
            return _p[index].value;
        }
    }
};

/*!
 * canvg库封装
 * canvg官网： https://code.google.com/p/canvg/
 */
_p[0] = {
    value: function(require) {
        /**
     * A class to parse color values
     * @author Stoyan Stefanov <sstoo@gmail.com>
     * @link   http://www.phpied.com/rgb-color-parser-in-javascript/
     * @license Use it if you like it
     */
        function RGBColor(color_string) {
            this.ok = false;
            // strip any leading #
            if (color_string.charAt(0) == "#") {
                // remove # if any
                color_string = color_string.substr(1, 6);
            }
            color_string = color_string.replace(/ /g, "");
            color_string = color_string.toLowerCase();
            // before getting into regexps, try simple matches
            // and overwrite the input
            var simple_colors = {
                aliceblue: "f0f8ff",
                antiquewhite: "faebd7",
                aqua: "00ffff",
                aquamarine: "7fffd4",
                azure: "f0ffff",
                beige: "f5f5dc",
                bisque: "ffe4c4",
                black: "000000",
                blanchedalmond: "ffebcd",
                blue: "0000ff",
                blueviolet: "8a2be2",
                brown: "a52a2a",
                burlywood: "deb887",
                cadetblue: "5f9ea0",
                chartreuse: "7fff00",
                chocolate: "d2691e",
                coral: "ff7f50",
                cornflowerblue: "6495ed",
                cornsilk: "fff8dc",
                crimson: "dc143c",
                cyan: "00ffff",
                darkblue: "00008b",
                darkcyan: "008b8b",
                darkgoldenrod: "b8860b",
                darkgray: "a9a9a9",
                darkgreen: "006400",
                darkkhaki: "bdb76b",
                darkmagenta: "8b008b",
                darkolivegreen: "556b2f",
                darkorange: "ff8c00",
                darkorchid: "9932cc",
                darkred: "8b0000",
                darksalmon: "e9967a",
                darkseagreen: "8fbc8f",
                darkslateblue: "483d8b",
                darkslategray: "2f4f4f",
                darkturquoise: "00ced1",
                darkviolet: "9400d3",
                deeppink: "ff1493",
                deepskyblue: "00bfff",
                dimgray: "696969",
                dodgerblue: "1e90ff",
                feldspar: "d19275",
                firebrick: "b22222",
                floralwhite: "fffaf0",
                forestgreen: "228b22",
                fuchsia: "ff00ff",
                gainsboro: "dcdcdc",
                ghostwhite: "f8f8ff",
                gold: "ffd700",
                goldenrod: "daa520",
                gray: "808080",
                green: "008000",
                greenyellow: "adff2f",
                honeydew: "f0fff0",
                hotpink: "ff69b4",
                indianred: "cd5c5c",
                indigo: "4b0082",
                ivory: "fffff0",
                khaki: "f0e68c",
                lavender: "e6e6fa",
                lavenderblush: "fff0f5",
                lawngreen: "7cfc00",
                lemonchiffon: "fffacd",
                lightblue: "add8e6",
                lightcoral: "f08080",
                lightcyan: "e0ffff",
                lightgoldenrodyellow: "fafad2",
                lightgrey: "d3d3d3",
                lightgreen: "90ee90",
                lightpink: "ffb6c1",
                lightsalmon: "ffa07a",
                lightseagreen: "20b2aa",
                lightskyblue: "87cefa",
                lightslateblue: "8470ff",
                lightslategray: "778899",
                lightsteelblue: "b0c4de",
                lightyellow: "ffffe0",
                lime: "00ff00",
                limegreen: "32cd32",
                linen: "faf0e6",
                magenta: "ff00ff",
                maroon: "800000",
                mediumaquamarine: "66cdaa",
                mediumblue: "0000cd",
                mediumorchid: "ba55d3",
                mediumpurple: "9370d8",
                mediumseagreen: "3cb371",
                mediumslateblue: "7b68ee",
                mediumspringgreen: "00fa9a",
                mediumturquoise: "48d1cc",
                mediumvioletred: "c71585",
                midnightblue: "191970",
                mintcream: "f5fffa",
                mistyrose: "ffe4e1",
                moccasin: "ffe4b5",
                navajowhite: "ffdead",
                navy: "000080",
                oldlace: "fdf5e6",
                olive: "808000",
                olivedrab: "6b8e23",
                orange: "ffa500",
                orangered: "ff4500",
                orchid: "da70d6",
                palegoldenrod: "eee8aa",
                palegreen: "98fb98",
                paleturquoise: "afeeee",
                palevioletred: "d87093",
                papayawhip: "ffefd5",
                peachpuff: "ffdab9",
                peru: "cd853f",
                pink: "ffc0cb",
                plum: "dda0dd",
                powderblue: "b0e0e6",
                purple: "800080",
                red: "ff0000",
                rosybrown: "bc8f8f",
                royalblue: "4169e1",
                saddlebrown: "8b4513",
                salmon: "fa8072",
                sandybrown: "f4a460",
                seagreen: "2e8b57",
                seashell: "fff5ee",
                sienna: "a0522d",
                silver: "c0c0c0",
                skyblue: "87ceeb",
                slateblue: "6a5acd",
                slategray: "708090",
                snow: "fffafa",
                springgreen: "00ff7f",
                steelblue: "4682b4",
                tan: "d2b48c",
                teal: "008080",
                thistle: "d8bfd8",
                tomato: "ff6347",
                turquoise: "40e0d0",
                violet: "ee82ee",
                violetred: "d02090",
                wheat: "f5deb3",
                white: "ffffff",
                whitesmoke: "f5f5f5",
                yellow: "ffff00",
                yellowgreen: "9acd32"
            };
            for (var key in simple_colors) {
                if (color_string == key) {
                    color_string = simple_colors[key];
                }
            }
            // emd of simple type-in colors
            // array of color definition objects
            var color_defs = [ {
                re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
                example: [ "rgb(123, 234, 45)", "rgb(255,234,245)" ],
                process: function(bits) {
                    return [ parseInt(bits[1]), parseInt(bits[2]), parseInt(bits[3]) ];
                }
            }, {
                re: /^(\w{2})(\w{2})(\w{2})$/,
                example: [ "#00ff00", "336699" ],
                process: function(bits) {
                    return [ parseInt(bits[1], 16), parseInt(bits[2], 16), parseInt(bits[3], 16) ];
                }
            }, {
                re: /^(\w{1})(\w{1})(\w{1})$/,
                example: [ "#fb0", "f0f" ],
                process: function(bits) {
                    return [ parseInt(bits[1] + bits[1], 16), parseInt(bits[2] + bits[2], 16), parseInt(bits[3] + bits[3], 16) ];
                }
            } ];
            // search through the definitions to find a match
            for (var i = 0; i < color_defs.length; i++) {
                var re = color_defs[i].re;
                var processor = color_defs[i].process;
                var bits = re.exec(color_string);
                if (bits) {
                    channels = processor(bits);
                    this.r = channels[0];
                    this.g = channels[1];
                    this.b = channels[2];
                    this.ok = true;
                }
            }
            // validate/cleanup values
            this.r = this.r < 0 || isNaN(this.r) ? 0 : this.r > 255 ? 255 : this.r;
            this.g = this.g < 0 || isNaN(this.g) ? 0 : this.g > 255 ? 255 : this.g;
            this.b = this.b < 0 || isNaN(this.b) ? 0 : this.b > 255 ? 255 : this.b;
            // some getters
            this.toRGB = function() {
                return "rgb(" + this.r + ", " + this.g + ", " + this.b + ")";
            };
            this.toHex = function() {
                var r = this.r.toString(16);
                var g = this.g.toString(16);
                var b = this.b.toString(16);
                if (r.length == 1) r = "0" + r;
                if (g.length == 1) g = "0" + g;
                if (b.length == 1) b = "0" + b;
                return "#" + r + g + b;
            };
            // help
            this.getHelpXML = function() {
                var examples = new Array();
                // add regexps
                for (var i = 0; i < color_defs.length; i++) {
                    var example = color_defs[i].example;
                    for (var j = 0; j < example.length; j++) {
                        examples[examples.length] = example[j];
                    }
                }
                // add type-in colors
                for (var sc in simple_colors) {
                    examples[examples.length] = sc;
                }
                var xml = document.createElement("ul");
                xml.setAttribute("id", "rgbcolor-examples");
                for (var i = 0; i < examples.length; i++) {
                    try {
                        var list_item = document.createElement("li");
                        var list_color = new RGBColor(examples[i]);
                        var example_div = document.createElement("div");
                        example_div.style.cssText = "margin: 3px; " + "border: 1px solid black; " + "background:" + list_color.toHex() + "; " + "color:" + list_color.toHex();
                        example_div.appendChild(document.createTextNode("test"));
                        var list_item_value = document.createTextNode(" " + examples[i] + " -> " + list_color.toRGB() + " -> " + list_color.toHex());
                        list_item.appendChild(example_div);
                        list_item.appendChild(list_item_value);
                        xml.appendChild(list_item);
                    } catch (e) {}
                }
                return xml;
            };
        }
        /*

     StackBlur - a fast almost Gaussian Blur For Canvas

     Version: 	0.5
     Author:		Mario Klingemann
     Contact: 	mario@quasimondo.com
     Website:	http://www.quasimondo.com/StackBlurForCanvas
     Twitter:	@quasimondo

     In case you find this class useful - especially in commercial projects -
     I am not totally unhappy for a small donation to my PayPal account
     mario@quasimondo.de

     Or support me on flattr:
     https://flattr.com/thing/72791/StackBlur-a-fast-almost-Gaussian-Blur-Effect-for-CanvasJavascript

     Copyright (c) 2010 Mario Klingemann

     Permission is hereby granted, free of charge, to any person
     obtaining a copy of this software and associated documentation
     files (the "Software"), to deal in the Software without
     restriction, including without limitation the rights to use,
     copy, modify, merge, publish, distribute, sublicense, and/or sell
     copies of the Software, and to permit persons to whom the
     Software is furnished to do so, subject to the following
     conditions:

     The above copyright notice and this permission notice shall be
     included in all copies or substantial portions of the Software.

     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
     EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
     OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
     NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
     HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
     WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
     FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
     OTHER DEALINGS IN THE SOFTWARE.
     */
        var mul_table = [ 512, 512, 456, 512, 328, 456, 335, 512, 405, 328, 271, 456, 388, 335, 292, 512, 454, 405, 364, 328, 298, 271, 496, 456, 420, 388, 360, 335, 312, 292, 273, 512, 482, 454, 428, 405, 383, 364, 345, 328, 312, 298, 284, 271, 259, 496, 475, 456, 437, 420, 404, 388, 374, 360, 347, 335, 323, 312, 302, 292, 282, 273, 265, 512, 497, 482, 468, 454, 441, 428, 417, 405, 394, 383, 373, 364, 354, 345, 337, 328, 320, 312, 305, 298, 291, 284, 278, 271, 265, 259, 507, 496, 485, 475, 465, 456, 446, 437, 428, 420, 412, 404, 396, 388, 381, 374, 367, 360, 354, 347, 341, 335, 329, 323, 318, 312, 307, 302, 297, 292, 287, 282, 278, 273, 269, 265, 261, 512, 505, 497, 489, 482, 475, 468, 461, 454, 447, 441, 435, 428, 422, 417, 411, 405, 399, 394, 389, 383, 378, 373, 368, 364, 359, 354, 350, 345, 341, 337, 332, 328, 324, 320, 316, 312, 309, 305, 301, 298, 294, 291, 287, 284, 281, 278, 274, 271, 268, 265, 262, 259, 257, 507, 501, 496, 491, 485, 480, 475, 470, 465, 460, 456, 451, 446, 442, 437, 433, 428, 424, 420, 416, 412, 408, 404, 400, 396, 392, 388, 385, 381, 377, 374, 370, 367, 363, 360, 357, 354, 350, 347, 344, 341, 338, 335, 332, 329, 326, 323, 320, 318, 315, 312, 310, 307, 304, 302, 299, 297, 294, 292, 289, 287, 285, 282, 280, 278, 275, 273, 271, 269, 267, 265, 263, 261, 259 ];
        var shg_table = [ 9, 11, 12, 13, 13, 14, 14, 15, 15, 15, 15, 16, 16, 16, 16, 17, 17, 17, 17, 17, 17, 17, 18, 18, 18, 18, 18, 18, 18, 18, 18, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 19, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 20, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 22, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 23, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24, 24 ];
        function stackBlurImage(imageID, canvasID, radius, blurAlphaChannel) {
            var img = document.getElementById(imageID);
            var w = img.naturalWidth;
            var h = img.naturalHeight;
            var canvas = document.getElementById(canvasID);
            canvas.style.width = w + "px";
            canvas.style.height = h + "px";
            canvas.width = w;
            canvas.height = h;
            var context = canvas.getContext("2d");
            context.clearRect(0, 0, w, h);
            context.drawImage(img, 0, 0);
            if (isNaN(radius) || radius < 1) return;
            if (blurAlphaChannel) stackBlurCanvasRGBA(canvasID, 0, 0, w, h, radius); else stackBlurCanvasRGB(canvasID, 0, 0, w, h, radius);
        }
        function stackBlurCanvasRGBA(id, top_x, top_y, width, height, radius) {
            if (isNaN(radius) || radius < 1) return;
            radius |= 0;
            var canvas = document.getElementById(id);
            var context = canvas.getContext("2d");
            var imageData;
            try {
                try {
                    imageData = context.getImageData(top_x, top_y, width, height);
                } catch (e) {
                    // NOTE: this part is supposedly only needed if you want to work with local files
                    // so it might be okay to remove the whole try/catch block and just use
                    // imageData = context.getImageData( top_x, top_y, width, height );
                    try {
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
                        imageData = context.getImageData(top_x, top_y, width, height);
                    } catch (e) {
                        alert("Cannot access local image");
                        throw new Error("unable to access local image data: " + e);
                        return;
                    }
                }
            } catch (e) {
                alert("Cannot access image");
                throw new Error("unable to access image data: " + e);
            }
            var pixels = imageData.data;
            var x, y, i, p, yp, yi, yw, r_sum, g_sum, b_sum, a_sum, r_out_sum, g_out_sum, b_out_sum, a_out_sum, r_in_sum, g_in_sum, b_in_sum, a_in_sum, pr, pg, pb, pa, rbs;
            var div = radius + radius + 1;
            var w4 = width << 2;
            var widthMinus1 = width - 1;
            var heightMinus1 = height - 1;
            var radiusPlus1 = radius + 1;
            var sumFactor = radiusPlus1 * (radiusPlus1 + 1) / 2;
            var stackStart = new BlurStack();
            var stack = stackStart;
            for (i = 1; i < div; i++) {
                stack = stack.next = new BlurStack();
                if (i == radiusPlus1) var stackEnd = stack;
            }
            stack.next = stackStart;
            var stackIn = null;
            var stackOut = null;
            yw = yi = 0;
            var mul_sum = mul_table[radius];
            var shg_sum = shg_table[radius];
            for (y = 0; y < height; y++) {
                r_in_sum = g_in_sum = b_in_sum = a_in_sum = r_sum = g_sum = b_sum = a_sum = 0;
                r_out_sum = radiusPlus1 * (pr = pixels[yi]);
                g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
                b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
                a_out_sum = radiusPlus1 * (pa = pixels[yi + 3]);
                r_sum += sumFactor * pr;
                g_sum += sumFactor * pg;
                b_sum += sumFactor * pb;
                a_sum += sumFactor * pa;
                stack = stackStart;
                for (i = 0; i < radiusPlus1; i++) {
                    stack.r = pr;
                    stack.g = pg;
                    stack.b = pb;
                    stack.a = pa;
                    stack = stack.next;
                }
                for (i = 1; i < radiusPlus1; i++) {
                    p = yi + ((widthMinus1 < i ? widthMinus1 : i) << 2);
                    r_sum += (stack.r = pr = pixels[p]) * (rbs = radiusPlus1 - i);
                    g_sum += (stack.g = pg = pixels[p + 1]) * rbs;
                    b_sum += (stack.b = pb = pixels[p + 2]) * rbs;
                    a_sum += (stack.a = pa = pixels[p + 3]) * rbs;
                    r_in_sum += pr;
                    g_in_sum += pg;
                    b_in_sum += pb;
                    a_in_sum += pa;
                    stack = stack.next;
                }
                stackIn = stackStart;
                stackOut = stackEnd;
                for (x = 0; x < width; x++) {
                    pixels[yi + 3] = pa = a_sum * mul_sum >> shg_sum;
                    if (pa != 0) {
                        pa = 255 / pa;
                        pixels[yi] = (r_sum * mul_sum >> shg_sum) * pa;
                        pixels[yi + 1] = (g_sum * mul_sum >> shg_sum) * pa;
                        pixels[yi + 2] = (b_sum * mul_sum >> shg_sum) * pa;
                    } else {
                        pixels[yi] = pixels[yi + 1] = pixels[yi + 2] = 0;
                    }
                    r_sum -= r_out_sum;
                    g_sum -= g_out_sum;
                    b_sum -= b_out_sum;
                    a_sum -= a_out_sum;
                    r_out_sum -= stackIn.r;
                    g_out_sum -= stackIn.g;
                    b_out_sum -= stackIn.b;
                    a_out_sum -= stackIn.a;
                    p = yw + ((p = x + radius + 1) < widthMinus1 ? p : widthMinus1) << 2;
                    r_in_sum += stackIn.r = pixels[p];
                    g_in_sum += stackIn.g = pixels[p + 1];
                    b_in_sum += stackIn.b = pixels[p + 2];
                    a_in_sum += stackIn.a = pixels[p + 3];
                    r_sum += r_in_sum;
                    g_sum += g_in_sum;
                    b_sum += b_in_sum;
                    a_sum += a_in_sum;
                    stackIn = stackIn.next;
                    r_out_sum += pr = stackOut.r;
                    g_out_sum += pg = stackOut.g;
                    b_out_sum += pb = stackOut.b;
                    a_out_sum += pa = stackOut.a;
                    r_in_sum -= pr;
                    g_in_sum -= pg;
                    b_in_sum -= pb;
                    a_in_sum -= pa;
                    stackOut = stackOut.next;
                    yi += 4;
                }
                yw += width;
            }
            for (x = 0; x < width; x++) {
                g_in_sum = b_in_sum = a_in_sum = r_in_sum = g_sum = b_sum = a_sum = r_sum = 0;
                yi = x << 2;
                r_out_sum = radiusPlus1 * (pr = pixels[yi]);
                g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
                b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
                a_out_sum = radiusPlus1 * (pa = pixels[yi + 3]);
                r_sum += sumFactor * pr;
                g_sum += sumFactor * pg;
                b_sum += sumFactor * pb;
                a_sum += sumFactor * pa;
                stack = stackStart;
                for (i = 0; i < radiusPlus1; i++) {
                    stack.r = pr;
                    stack.g = pg;
                    stack.b = pb;
                    stack.a = pa;
                    stack = stack.next;
                }
                yp = width;
                for (i = 1; i <= radius; i++) {
                    yi = yp + x << 2;
                    r_sum += (stack.r = pr = pixels[yi]) * (rbs = radiusPlus1 - i);
                    g_sum += (stack.g = pg = pixels[yi + 1]) * rbs;
                    b_sum += (stack.b = pb = pixels[yi + 2]) * rbs;
                    a_sum += (stack.a = pa = pixels[yi + 3]) * rbs;
                    r_in_sum += pr;
                    g_in_sum += pg;
                    b_in_sum += pb;
                    a_in_sum += pa;
                    stack = stack.next;
                    if (i < heightMinus1) {
                        yp += width;
                    }
                }
                yi = x;
                stackIn = stackStart;
                stackOut = stackEnd;
                for (y = 0; y < height; y++) {
                    p = yi << 2;
                    pixels[p + 3] = pa = a_sum * mul_sum >> shg_sum;
                    if (pa > 0) {
                        pa = 255 / pa;
                        pixels[p] = (r_sum * mul_sum >> shg_sum) * pa;
                        pixels[p + 1] = (g_sum * mul_sum >> shg_sum) * pa;
                        pixels[p + 2] = (b_sum * mul_sum >> shg_sum) * pa;
                    } else {
                        pixels[p] = pixels[p + 1] = pixels[p + 2] = 0;
                    }
                    r_sum -= r_out_sum;
                    g_sum -= g_out_sum;
                    b_sum -= b_out_sum;
                    a_sum -= a_out_sum;
                    r_out_sum -= stackIn.r;
                    g_out_sum -= stackIn.g;
                    b_out_sum -= stackIn.b;
                    a_out_sum -= stackIn.a;
                    p = x + ((p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1) * width << 2;
                    r_sum += r_in_sum += stackIn.r = pixels[p];
                    g_sum += g_in_sum += stackIn.g = pixels[p + 1];
                    b_sum += b_in_sum += stackIn.b = pixels[p + 2];
                    a_sum += a_in_sum += stackIn.a = pixels[p + 3];
                    stackIn = stackIn.next;
                    r_out_sum += pr = stackOut.r;
                    g_out_sum += pg = stackOut.g;
                    b_out_sum += pb = stackOut.b;
                    a_out_sum += pa = stackOut.a;
                    r_in_sum -= pr;
                    g_in_sum -= pg;
                    b_in_sum -= pb;
                    a_in_sum -= pa;
                    stackOut = stackOut.next;
                    yi += width;
                }
            }
            context.putImageData(imageData, top_x, top_y);
        }
        function stackBlurCanvasRGB(id, top_x, top_y, width, height, radius) {
            if (isNaN(radius) || radius < 1) return;
            radius |= 0;
            var canvas = document.getElementById(id);
            var context = canvas.getContext("2d");
            var imageData;
            try {
                try {
                    imageData = context.getImageData(top_x, top_y, width, height);
                } catch (e) {
                    // NOTE: this part is supposedly only needed if you want to work with local files
                    // so it might be okay to remove the whole try/catch block and just use
                    // imageData = context.getImageData( top_x, top_y, width, height );
                    try {
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
                        imageData = context.getImageData(top_x, top_y, width, height);
                    } catch (e) {
                        alert("Cannot access local image");
                        throw new Error("unable to access local image data: " + e);
                        return;
                    }
                }
            } catch (e) {
                alert("Cannot access image");
                throw new Error("unable to access image data: " + e);
            }
            var pixels = imageData.data;
            var x, y, i, p, yp, yi, yw, r_sum, g_sum, b_sum, r_out_sum, g_out_sum, b_out_sum, r_in_sum, g_in_sum, b_in_sum, pr, pg, pb, rbs;
            var div = radius + radius + 1;
            var w4 = width << 2;
            var widthMinus1 = width - 1;
            var heightMinus1 = height - 1;
            var radiusPlus1 = radius + 1;
            var sumFactor = radiusPlus1 * (radiusPlus1 + 1) / 2;
            var stackStart = new BlurStack();
            var stack = stackStart;
            for (i = 1; i < div; i++) {
                stack = stack.next = new BlurStack();
                if (i == radiusPlus1) var stackEnd = stack;
            }
            stack.next = stackStart;
            var stackIn = null;
            var stackOut = null;
            yw = yi = 0;
            var mul_sum = mul_table[radius];
            var shg_sum = shg_table[radius];
            for (y = 0; y < height; y++) {
                r_in_sum = g_in_sum = b_in_sum = r_sum = g_sum = b_sum = 0;
                r_out_sum = radiusPlus1 * (pr = pixels[yi]);
                g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
                b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
                r_sum += sumFactor * pr;
                g_sum += sumFactor * pg;
                b_sum += sumFactor * pb;
                stack = stackStart;
                for (i = 0; i < radiusPlus1; i++) {
                    stack.r = pr;
                    stack.g = pg;
                    stack.b = pb;
                    stack = stack.next;
                }
                for (i = 1; i < radiusPlus1; i++) {
                    p = yi + ((widthMinus1 < i ? widthMinus1 : i) << 2);
                    r_sum += (stack.r = pr = pixels[p]) * (rbs = radiusPlus1 - i);
                    g_sum += (stack.g = pg = pixels[p + 1]) * rbs;
                    b_sum += (stack.b = pb = pixels[p + 2]) * rbs;
                    r_in_sum += pr;
                    g_in_sum += pg;
                    b_in_sum += pb;
                    stack = stack.next;
                }
                stackIn = stackStart;
                stackOut = stackEnd;
                for (x = 0; x < width; x++) {
                    pixels[yi] = r_sum * mul_sum >> shg_sum;
                    pixels[yi + 1] = g_sum * mul_sum >> shg_sum;
                    pixels[yi + 2] = b_sum * mul_sum >> shg_sum;
                    r_sum -= r_out_sum;
                    g_sum -= g_out_sum;
                    b_sum -= b_out_sum;
                    r_out_sum -= stackIn.r;
                    g_out_sum -= stackIn.g;
                    b_out_sum -= stackIn.b;
                    p = yw + ((p = x + radius + 1) < widthMinus1 ? p : widthMinus1) << 2;
                    r_in_sum += stackIn.r = pixels[p];
                    g_in_sum += stackIn.g = pixels[p + 1];
                    b_in_sum += stackIn.b = pixels[p + 2];
                    r_sum += r_in_sum;
                    g_sum += g_in_sum;
                    b_sum += b_in_sum;
                    stackIn = stackIn.next;
                    r_out_sum += pr = stackOut.r;
                    g_out_sum += pg = stackOut.g;
                    b_out_sum += pb = stackOut.b;
                    r_in_sum -= pr;
                    g_in_sum -= pg;
                    b_in_sum -= pb;
                    stackOut = stackOut.next;
                    yi += 4;
                }
                yw += width;
            }
            for (x = 0; x < width; x++) {
                g_in_sum = b_in_sum = r_in_sum = g_sum = b_sum = r_sum = 0;
                yi = x << 2;
                r_out_sum = radiusPlus1 * (pr = pixels[yi]);
                g_out_sum = radiusPlus1 * (pg = pixels[yi + 1]);
                b_out_sum = radiusPlus1 * (pb = pixels[yi + 2]);
                r_sum += sumFactor * pr;
                g_sum += sumFactor * pg;
                b_sum += sumFactor * pb;
                stack = stackStart;
                for (i = 0; i < radiusPlus1; i++) {
                    stack.r = pr;
                    stack.g = pg;
                    stack.b = pb;
                    stack = stack.next;
                }
                yp = width;
                for (i = 1; i <= radius; i++) {
                    yi = yp + x << 2;
                    r_sum += (stack.r = pr = pixels[yi]) * (rbs = radiusPlus1 - i);
                    g_sum += (stack.g = pg = pixels[yi + 1]) * rbs;
                    b_sum += (stack.b = pb = pixels[yi + 2]) * rbs;
                    r_in_sum += pr;
                    g_in_sum += pg;
                    b_in_sum += pb;
                    stack = stack.next;
                    if (i < heightMinus1) {
                        yp += width;
                    }
                }
                yi = x;
                stackIn = stackStart;
                stackOut = stackEnd;
                for (y = 0; y < height; y++) {
                    p = yi << 2;
                    pixels[p] = r_sum * mul_sum >> shg_sum;
                    pixels[p + 1] = g_sum * mul_sum >> shg_sum;
                    pixels[p + 2] = b_sum * mul_sum >> shg_sum;
                    r_sum -= r_out_sum;
                    g_sum -= g_out_sum;
                    b_sum -= b_out_sum;
                    r_out_sum -= stackIn.r;
                    g_out_sum -= stackIn.g;
                    b_out_sum -= stackIn.b;
                    p = x + ((p = y + radiusPlus1) < heightMinus1 ? p : heightMinus1) * width << 2;
                    r_sum += r_in_sum += stackIn.r = pixels[p];
                    g_sum += g_in_sum += stackIn.g = pixels[p + 1];
                    b_sum += b_in_sum += stackIn.b = pixels[p + 2];
                    stackIn = stackIn.next;
                    r_out_sum += pr = stackOut.r;
                    g_out_sum += pg = stackOut.g;
                    b_out_sum += pb = stackOut.b;
                    r_in_sum -= pr;
                    g_in_sum -= pg;
                    b_in_sum -= pb;
                    stackOut = stackOut.next;
                    yi += width;
                }
            }
            context.putImageData(imageData, top_x, top_y);
        }
        function BlurStack() {
            this.r = 0;
            this.g = 0;
            this.b = 0;
            this.a = 0;
            this.next = null;
        }
        /*
     * canvg.js - Javascript SVG parser and renderer on Canvas
     * MIT Licensed 
     * Gabe Lerner (gabelerner@gmail.com)
     * http://code.google.com/p/canvg/
     *
     * Requires: rgbcolor.js - http://www.phpied.com/rgb-color-parser-in-javascript/
     */
        (function() {
            // canvg(target, s)
            // empty parameters: replace all 'svg' elements on page with 'canvas' elements
            // target: canvas element or the id of a canvas element
            // s: svg string, url to svg file, or xml document
            // opts: optional hash of options
            //		 ignoreMouse: true => ignore mouse events
            //		 ignoreAnimation: true => ignore animations
            //		 ignoreDimensions: true => does not try to resize canvas
            //		 ignoreClear: true => does not clear canvas
            //		 offsetX: int => draws at a x offset
            //		 offsetY: int => draws at a y offset
            //		 scaleWidth: int => scales horizontally to width
            //		 scaleHeight: int => scales vertically to height
            //		 renderCallback: function => will call the function after the first render is completed
            //		 forceRedraw: function => will call the function on every frame, if it returns true, will redraw
            this.canvg = function(target, s, opts) {
                // no parameters
                if (target == null && s == null && opts == null) {
                    var svgTags = document.getElementsByTagName("svg");
                    for (var i = 0; i < svgTags.length; i++) {
                        var svgTag = svgTags[i];
                        var c = document.createElement("canvas");
                        c.width = svgTag.clientWidth;
                        c.height = svgTag.clientHeight;
                        svgTag.parentNode.insertBefore(c, svgTag);
                        svgTag.parentNode.removeChild(svgTag);
                        var div = document.createElement("div");
                        div.appendChild(svgTag);
                        canvg(c, div.innerHTML);
                    }
                    return;
                }
                opts = opts || {};
                if (typeof target == "string") {
                    target = document.getElementById(target);
                }
                // store class on canvas
                if (target.svg != null) target.svg.stop();
                var svg = build();
                // on i.e. 8 for flash canvas, we can't assign the property so check for it
                if (!(target.childNodes.length == 1 && target.childNodes[0].nodeName == "OBJECT")) target.svg = svg;
                svg.opts = opts;
                var ctx = target.getContext("2d");
                if (typeof s.documentElement != "undefined") {
                    // load from xml doc
                    svg.loadXmlDoc(ctx, s);
                } else if (s.substr(0, 1) == "<") {
                    // load from xml string
                    svg.loadXml(ctx, s);
                } else {
                    // load from url
                    svg.load(ctx, s);
                }
            };
            function build() {
                var svg = {};
                svg.FRAMERATE = 30;
                svg.MAX_VIRTUAL_PIXELS = 3e4;
                // globals
                svg.init = function(ctx) {
                    var uniqueId = 0;
                    svg.UniqueId = function() {
                        uniqueId++;
                        return "canvg" + uniqueId;
                    };
                    svg.Definitions = {};
                    svg.Styles = {};
                    svg.Animations = [];
                    svg.Images = [];
                    svg.ctx = ctx;
                    svg.ViewPort = new function() {
                        this.viewPorts = [];
                        this.Clear = function() {
                            this.viewPorts = [];
                        };
                        this.SetCurrent = function(width, height) {
                            this.viewPorts.push({
                                width: width,
                                height: height
                            });
                        };
                        this.RemoveCurrent = function() {
                            this.viewPorts.pop();
                        };
                        this.Current = function() {
                            return this.viewPorts[this.viewPorts.length - 1];
                        };
                        this.width = function() {
                            return this.Current().width;
                        };
                        this.height = function() {
                            return this.Current().height;
                        };
                        this.ComputeSize = function(d) {
                            if (d != null && typeof d == "number") return d;
                            if (d == "x") return this.width();
                            if (d == "y") return this.height();
                            return Math.sqrt(Math.pow(this.width(), 2) + Math.pow(this.height(), 2)) / Math.sqrt(2);
                        };
                    }();
                };
                svg.init();
                // images loaded
                svg.ImagesLoaded = function() {
                    for (var i = 0; i < svg.Images.length; i++) {
                        if (!svg.Images[i].loaded) return false;
                    }
                    return true;
                };
                // trim
                svg.trim = function(s) {
                    return s.replace(/^\s+|\s+$/g, "");
                };
                // compress spaces
                svg.compressSpaces = function(s) {
                    return s.replace(/[\s\r\t\n]+/gm, " ");
                };
                // ajax
                svg.ajax = function(url) {
                    var AJAX;
                    if (window.XMLHttpRequest) {
                        AJAX = new XMLHttpRequest();
                    } else {
                        AJAX = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                    if (AJAX) {
                        AJAX.open("GET", url, false);
                        AJAX.send(null);
                        return AJAX.responseText;
                    }
                    return null;
                };
                // parse xml
                svg.parseXml = function(xml) {
                    if (window.DOMParser) {
                        var parser = new DOMParser();
                        return parser.parseFromString(xml, "text/xml");
                    } else {
                        xml = xml.replace(/<!DOCTYPE svg[^>]*>/, "");
                        var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
                        xmlDoc.async = "false";
                        xmlDoc.loadXML(xml);
                        return xmlDoc;
                    }
                };
                svg.Property = function(name, value) {
                    this.name = name;
                    this.value = value;
                };
                svg.Property.prototype.getValue = function() {
                    return this.value;
                };
                svg.Property.prototype.hasValue = function() {
                    return this.value != null && this.value !== "";
                };
                // return the numerical value of the property
                svg.Property.prototype.numValue = function() {
                    if (!this.hasValue()) return 0;
                    var n = parseFloat(this.value);
                    if ((this.value + "").match(/%$/)) {
                        n = n / 100;
                    }
                    return n;
                };
                svg.Property.prototype.valueOrDefault = function(def) {
                    if (this.hasValue()) return this.value;
                    return def;
                };
                svg.Property.prototype.numValueOrDefault = function(def) {
                    if (this.hasValue()) return this.numValue();
                    return def;
                };
                // color extensions
                // augment the current color value with the opacity
                svg.Property.prototype.addOpacity = function(opacity) {
                    var newValue = this.value;
                    if (opacity != null && opacity != "" && typeof this.value == "string") {
                        // can only add opacity to colors, not patterns
                        var color = new RGBColor(this.value);
                        if (color.ok) {
                            newValue = "rgba(" + color.r + ", " + color.g + ", " + color.b + ", " + opacity + ")";
                        }
                    }
                    return new svg.Property(this.name, newValue);
                };
                // definition extensions
                // get the definition from the definitions table
                svg.Property.prototype.getDefinition = function() {
                    var name = this.value.match(/#([^\)'"]+)/);
                    if (name) {
                        name = name[1];
                    }
                    if (!name) {
                        name = this.value;
                    }
                    return svg.Definitions[name];
                };
                svg.Property.prototype.isUrlDefinition = function() {
                    return this.value.indexOf("url(") == 0;
                };
                svg.Property.prototype.getFillStyleDefinition = function(e, opacityProp) {
                    var def = this.getDefinition();
                    // gradient
                    if (def != null && def.createGradient) {
                        return def.createGradient(svg.ctx, e, opacityProp);
                    }
                    // pattern
                    if (def != null && def.createPattern) {
                        if (def.getHrefAttribute().hasValue()) {
                            var pt = def.attribute("patternTransform");
                            def = def.getHrefAttribute().getDefinition();
                            if (pt.hasValue()) {
                                def.attribute("patternTransform", true).value = pt.value;
                            }
                        }
                        return def.createPattern(svg.ctx, e);
                    }
                    return null;
                };
                // length extensions
                svg.Property.prototype.getDPI = function(viewPort) {
                    return 96;
                };
                svg.Property.prototype.getEM = function(viewPort) {
                    var em = 12;
                    var fontSize = new svg.Property("fontSize", svg.Font.Parse(svg.ctx.font).fontSize);
                    if (fontSize.hasValue()) em = fontSize.toPixels(viewPort);
                    return em;
                };
                svg.Property.prototype.getUnits = function() {
                    var s = this.value + "";
                    return s.replace(/[0-9\.\-]/g, "");
                };
                // get the length as pixels
                svg.Property.prototype.toPixels = function(viewPort, processPercent) {
                    if (!this.hasValue()) return 0;
                    var s = this.value + "";
                    if (s.match(/em$/)) return this.numValue() * this.getEM(viewPort);
                    if (s.match(/ex$/)) return this.numValue() * this.getEM(viewPort) / 2;
                    if (s.match(/px$/)) return this.numValue();
                    if (s.match(/pt$/)) return this.numValue() * this.getDPI(viewPort) * (1 / 72);
                    if (s.match(/pc$/)) return this.numValue() * 15;
                    if (s.match(/cm$/)) return this.numValue() * this.getDPI(viewPort) / 2.54;
                    if (s.match(/mm$/)) return this.numValue() * this.getDPI(viewPort) / 25.4;
                    if (s.match(/in$/)) return this.numValue() * this.getDPI(viewPort);
                    if (s.match(/%$/)) return this.numValue() * svg.ViewPort.ComputeSize(viewPort);
                    var n = this.numValue();
                    if (processPercent && n < 1) return n * svg.ViewPort.ComputeSize(viewPort);
                    return n;
                };
                // time extensions
                // get the time as milliseconds
                svg.Property.prototype.toMilliseconds = function() {
                    if (!this.hasValue()) return 0;
                    var s = this.value + "";
                    if (s.match(/s$/)) return this.numValue() * 1e3;
                    if (s.match(/ms$/)) return this.numValue();
                    return this.numValue();
                };
                // angle extensions
                // get the angle as radians
                svg.Property.prototype.toRadians = function() {
                    if (!this.hasValue()) return 0;
                    var s = this.value + "";
                    if (s.match(/deg$/)) return this.numValue() * (Math.PI / 180);
                    if (s.match(/grad$/)) return this.numValue() * (Math.PI / 200);
                    if (s.match(/rad$/)) return this.numValue();
                    return this.numValue() * (Math.PI / 180);
                };
                // fonts
                svg.Font = new function() {
                    this.Styles = "normal|italic|oblique|inherit";
                    this.Variants = "normal|small-caps|inherit";
                    this.Weights = "normal|bold|bolder|lighter|100|200|300|400|500|600|700|800|900|inherit";
                    this.CreateFont = function(fontStyle, fontVariant, fontWeight, fontSize, fontFamily, inherit) {
                        var f = inherit != null ? this.Parse(inherit) : this.CreateFont("", "", "", "", "", svg.ctx.font);
                        return {
                            fontFamily: fontFamily || f.fontFamily,
                            fontSize: fontSize || f.fontSize,
                            fontStyle: fontStyle || f.fontStyle,
                            fontWeight: fontWeight || f.fontWeight,
                            fontVariant: fontVariant || f.fontVariant,
                            toString: function() {
                                return [ this.fontStyle, this.fontVariant, this.fontWeight, this.fontSize, this.fontFamily ].join(" ");
                            }
                        };
                    };
                    var that = this;
                    this.Parse = function(s) {
                        var f = {};
                        var d = svg.trim(svg.compressSpaces(s || "")).split(" ");
                        var set = {
                            fontSize: false,
                            fontStyle: false,
                            fontWeight: false,
                            fontVariant: false
                        };
                        var ff = "";
                        for (var i = 0; i < d.length; i++) {
                            if (!set.fontStyle && that.Styles.indexOf(d[i]) != -1) {
                                if (d[i] != "inherit") f.fontStyle = d[i];
                                set.fontStyle = true;
                            } else if (!set.fontVariant && that.Variants.indexOf(d[i]) != -1) {
                                if (d[i] != "inherit") f.fontVariant = d[i];
                                set.fontStyle = set.fontVariant = true;
                            } else if (!set.fontWeight && that.Weights.indexOf(d[i]) != -1) {
                                if (d[i] != "inherit") f.fontWeight = d[i];
                                set.fontStyle = set.fontVariant = set.fontWeight = true;
                            } else if (!set.fontSize) {
                                if (d[i] != "inherit") f.fontSize = d[i].split("/")[0];
                                set.fontStyle = set.fontVariant = set.fontWeight = set.fontSize = true;
                            } else {
                                if (d[i] != "inherit") ff += d[i];
                            }
                        }
                        if (ff != "") f.fontFamily = ff;
                        return f;
                    };
                }();
                // points and paths
                svg.ToNumberArray = function(s) {
                    var a = svg.trim(svg.compressSpaces((s || "").replace(/,/g, " "))).split(" ");
                    for (var i = 0; i < a.length; i++) {
                        a[i] = parseFloat(a[i]);
                    }
                    return a;
                };
                svg.Point = function(x, y) {
                    this.x = x;
                    this.y = y;
                };
                svg.Point.prototype.angleTo = function(p) {
                    return Math.atan2(p.y - this.y, p.x - this.x);
                };
                svg.Point.prototype.applyTransform = function(v) {
                    var xp = this.x * v[0] + this.y * v[2] + v[4];
                    var yp = this.x * v[1] + this.y * v[3] + v[5];
                    this.x = xp;
                    this.y = yp;
                };
                svg.CreatePoint = function(s) {
                    var a = svg.ToNumberArray(s);
                    return new svg.Point(a[0], a[1]);
                };
                svg.CreatePath = function(s) {
                    var a = svg.ToNumberArray(s);
                    var path = [];
                    for (var i = 0; i < a.length; i += 2) {
                        path.push(new svg.Point(a[i], a[i + 1]));
                    }
                    return path;
                };
                // bounding box
                svg.BoundingBox = function(x1, y1, x2, y2) {
                    // pass in initial points if you want
                    this.x1 = Number.NaN;
                    this.y1 = Number.NaN;
                    this.x2 = Number.NaN;
                    this.y2 = Number.NaN;
                    this.x = function() {
                        return this.x1;
                    };
                    this.y = function() {
                        return this.y1;
                    };
                    this.width = function() {
                        return this.x2 - this.x1;
                    };
                    this.height = function() {
                        return this.y2 - this.y1;
                    };
                    this.addPoint = function(x, y) {
                        if (x != null) {
                            if (isNaN(this.x1) || isNaN(this.x2)) {
                                this.x1 = x;
                                this.x2 = x;
                            }
                            if (x < this.x1) this.x1 = x;
                            if (x > this.x2) this.x2 = x;
                        }
                        if (y != null) {
                            if (isNaN(this.y1) || isNaN(this.y2)) {
                                this.y1 = y;
                                this.y2 = y;
                            }
                            if (y < this.y1) this.y1 = y;
                            if (y > this.y2) this.y2 = y;
                        }
                    };
                    this.addX = function(x) {
                        this.addPoint(x, null);
                    };
                    this.addY = function(y) {
                        this.addPoint(null, y);
                    };
                    this.addBoundingBox = function(bb) {
                        this.addPoint(bb.x1, bb.y1);
                        this.addPoint(bb.x2, bb.y2);
                    };
                    this.addQuadraticCurve = function(p0x, p0y, p1x, p1y, p2x, p2y) {
                        var cp1x = p0x + 2 / 3 * (p1x - p0x);
                        // CP1 = QP0 + 2/3 *(QP1-QP0)
                        var cp1y = p0y + 2 / 3 * (p1y - p0y);
                        // CP1 = QP0 + 2/3 *(QP1-QP0)
                        var cp2x = cp1x + 1 / 3 * (p2x - p0x);
                        // CP2 = CP1 + 1/3 *(QP2-QP0)
                        var cp2y = cp1y + 1 / 3 * (p2y - p0y);
                        // CP2 = CP1 + 1/3 *(QP2-QP0)
                        this.addBezierCurve(p0x, p0y, cp1x, cp2x, cp1y, cp2y, p2x, p2y);
                    };
                    this.addBezierCurve = function(p0x, p0y, p1x, p1y, p2x, p2y, p3x, p3y) {
                        // from http://blog.hackers-cafe.net/2009/06/how-to-calculate-bezier-curves-bounding.html
                        var p0 = [ p0x, p0y ], p1 = [ p1x, p1y ], p2 = [ p2x, p2y ], p3 = [ p3x, p3y ];
                        this.addPoint(p0[0], p0[1]);
                        this.addPoint(p3[0], p3[1]);
                        for (i = 0; i <= 1; i++) {
                            var f = function(t) {
                                return Math.pow(1 - t, 3) * p0[i] + 3 * Math.pow(1 - t, 2) * t * p1[i] + 3 * (1 - t) * Math.pow(t, 2) * p2[i] + Math.pow(t, 3) * p3[i];
                            };
                            var b = 6 * p0[i] - 12 * p1[i] + 6 * p2[i];
                            var a = -3 * p0[i] + 9 * p1[i] - 9 * p2[i] + 3 * p3[i];
                            var c = 3 * p1[i] - 3 * p0[i];
                            if (a == 0) {
                                if (b == 0) continue;
                                var t = -c / b;
                                if (0 < t && t < 1) {
                                    if (i == 0) this.addX(f(t));
                                    if (i == 1) this.addY(f(t));
                                }
                                continue;
                            }
                            var b2ac = Math.pow(b, 2) - 4 * c * a;
                            if (b2ac < 0) continue;
                            var t1 = (-b + Math.sqrt(b2ac)) / (2 * a);
                            if (0 < t1 && t1 < 1) {
                                if (i == 0) this.addX(f(t1));
                                if (i == 1) this.addY(f(t1));
                            }
                            var t2 = (-b - Math.sqrt(b2ac)) / (2 * a);
                            if (0 < t2 && t2 < 1) {
                                if (i == 0) this.addX(f(t2));
                                if (i == 1) this.addY(f(t2));
                            }
                        }
                    };
                    this.isPointInBox = function(x, y) {
                        return this.x1 <= x && x <= this.x2 && this.y1 <= y && y <= this.y2;
                    };
                    this.addPoint(x1, y1);
                    this.addPoint(x2, y2);
                };
                // transforms
                svg.Transform = function(v) {
                    var that = this;
                    this.Type = {};
                    // translate
                    this.Type.translate = function(s) {
                        this.p = svg.CreatePoint(s);
                        this.apply = function(ctx) {
                            ctx.translate(this.p.x || 0, this.p.y || 0);
                        };
                        this.unapply = function(ctx) {
                            ctx.translate(-1 * this.p.x || 0, -1 * this.p.y || 0);
                        };
                        this.applyToPoint = function(p) {
                            p.applyTransform([ 1, 0, 0, 1, this.p.x || 0, this.p.y || 0 ]);
                        };
                    };
                    // rotate
                    this.Type.rotate = function(s) {
                        var a = svg.ToNumberArray(s);
                        this.angle = new svg.Property("angle", a[0]);
                        this.cx = a[1] || 0;
                        this.cy = a[2] || 0;
                        this.apply = function(ctx) {
                            ctx.translate(this.cx, this.cy);
                            ctx.rotate(this.angle.toRadians());
                            ctx.translate(-this.cx, -this.cy);
                        };
                        this.unapply = function(ctx) {
                            ctx.translate(this.cx, this.cy);
                            ctx.rotate(-1 * this.angle.toRadians());
                            ctx.translate(-this.cx, -this.cy);
                        };
                        this.applyToPoint = function(p) {
                            var a = this.angle.toRadians();
                            p.applyTransform([ 1, 0, 0, 1, this.p.x || 0, this.p.y || 0 ]);
                            p.applyTransform([ Math.cos(a), Math.sin(a), -Math.sin(a), Math.cos(a), 0, 0 ]);
                            p.applyTransform([ 1, 0, 0, 1, -this.p.x || 0, -this.p.y || 0 ]);
                        };
                    };
                    this.Type.scale = function(s) {
                        this.p = svg.CreatePoint(s);
                        this.apply = function(ctx) {
                            ctx.scale(this.p.x || 1, this.p.y || this.p.x || 1);
                        };
                        this.unapply = function(ctx) {
                            ctx.scale(1 / this.p.x || 1, 1 / this.p.y || this.p.x || 1);
                        };
                        this.applyToPoint = function(p) {
                            p.applyTransform([ this.p.x || 0, 0, 0, this.p.y || 0, 0, 0 ]);
                        };
                    };
                    this.Type.matrix = function(s) {
                        this.m = svg.ToNumberArray(s);
                        this.apply = function(ctx) {
                            ctx.transform(this.m[0], this.m[1], this.m[2], this.m[3], this.m[4], this.m[5]);
                        };
                        this.applyToPoint = function(p) {
                            p.applyTransform(this.m);
                        };
                    };
                    this.Type.SkewBase = function(s) {
                        this.base = that.Type.matrix;
                        this.base(s);
                        this.angle = new svg.Property("angle", s);
                    };
                    this.Type.SkewBase.prototype = new this.Type.matrix();
                    this.Type.skewX = function(s) {
                        this.base = that.Type.SkewBase;
                        this.base(s);
                        this.m = [ 1, 0, Math.tan(this.angle.toRadians()), 1, 0, 0 ];
                    };
                    this.Type.skewX.prototype = new this.Type.SkewBase();
                    this.Type.skewY = function(s) {
                        this.base = that.Type.SkewBase;
                        this.base(s);
                        this.m = [ 1, Math.tan(this.angle.toRadians()), 0, 1, 0, 0 ];
                    };
                    this.Type.skewY.prototype = new this.Type.SkewBase();
                    this.transforms = [];
                    this.apply = function(ctx) {
                        for (var i = 0; i < this.transforms.length; i++) {
                            this.transforms[i].apply(ctx);
                        }
                    };
                    this.unapply = function(ctx) {
                        for (var i = this.transforms.length - 1; i >= 0; i--) {
                            this.transforms[i].unapply(ctx);
                        }
                    };
                    this.applyToPoint = function(p) {
                        for (var i = 0; i < this.transforms.length; i++) {
                            this.transforms[i].applyToPoint(p);
                        }
                    };
                    var data = svg.trim(svg.compressSpaces(v)).replace(/\)(\s?,\s?)/g, ") ").split(/\s(?=[a-z])/);
                    for (var i = 0; i < data.length; i++) {
                        var type = svg.trim(data[i].split("(")[0]);
                        var s = data[i].split("(")[1].replace(")", "");
                        var transform = new this.Type[type](s);
                        transform.type = type;
                        this.transforms.push(transform);
                    }
                };
                // aspect ratio
                svg.AspectRatio = function(ctx, aspectRatio, width, desiredWidth, height, desiredHeight, minX, minY, refX, refY) {
                    // aspect ratio - http://www.w3.org/TR/SVG/coords.html#PreserveAspectRatioAttribute
                    aspectRatio = svg.compressSpaces(aspectRatio);
                    aspectRatio = aspectRatio.replace(/^defer\s/, "");
                    // ignore defer
                    var align = aspectRatio.split(" ")[0] || "xMidYMid";
                    var meetOrSlice = aspectRatio.split(" ")[1] || "meet";
                    // calculate scale
                    var scaleX = width / desiredWidth;
                    var scaleY = height / desiredHeight;
                    var scaleMin = Math.min(scaleX, scaleY);
                    var scaleMax = Math.max(scaleX, scaleY);
                    if (meetOrSlice == "meet") {
                        desiredWidth *= scaleMin;
                        desiredHeight *= scaleMin;
                    }
                    if (meetOrSlice == "slice") {
                        desiredWidth *= scaleMax;
                        desiredHeight *= scaleMax;
                    }
                    refX = new svg.Property("refX", refX);
                    refY = new svg.Property("refY", refY);
                    if (refX.hasValue() && refY.hasValue()) {
                        ctx.translate(-scaleMin * refX.toPixels("x"), -scaleMin * refY.toPixels("y"));
                    } else {
                        // align
                        if (align.match(/^xMid/) && (meetOrSlice == "meet" && scaleMin == scaleY || meetOrSlice == "slice" && scaleMax == scaleY)) ctx.translate(width / 2 - desiredWidth / 2, 0);
                        if (align.match(/YMid$/) && (meetOrSlice == "meet" && scaleMin == scaleX || meetOrSlice == "slice" && scaleMax == scaleX)) ctx.translate(0, height / 2 - desiredHeight / 2);
                        if (align.match(/^xMax/) && (meetOrSlice == "meet" && scaleMin == scaleY || meetOrSlice == "slice" && scaleMax == scaleY)) ctx.translate(width - desiredWidth, 0);
                        if (align.match(/YMax$/) && (meetOrSlice == "meet" && scaleMin == scaleX || meetOrSlice == "slice" && scaleMax == scaleX)) ctx.translate(0, height - desiredHeight);
                    }
                    // scale
                    if (align == "none") ctx.scale(scaleX, scaleY); else if (meetOrSlice == "meet") ctx.scale(scaleMin, scaleMin); else if (meetOrSlice == "slice") ctx.scale(scaleMax, scaleMax);
                    // translate
                    ctx.translate(minX == null ? 0 : -minX, minY == null ? 0 : -minY);
                };
                // elements
                svg.Element = {};
                svg.EmptyProperty = new svg.Property("EMPTY", "");
                svg.Element.ElementBase = function(node) {
                    this.attributes = {};
                    this.styles = {};
                    this.children = [];
                    // get or create attribute
                    this.attribute = function(name, createIfNotExists) {
                        var a = this.attributes[name];
                        if (a != null) return a;
                        if (createIfNotExists == true) {
                            a = new svg.Property(name, "");
                            this.attributes[name] = a;
                        }
                        return a || svg.EmptyProperty;
                    };
                    this.getHrefAttribute = function() {
                        for (var a in this.attributes) {
                            if (a.match(/:href$/)) {
                                return this.attributes[a];
                            }
                        }
                        return svg.EmptyProperty;
                    };
                    // get or create style, crawls up node tree
                    this.style = function(name, createIfNotExists) {
                        var s = this.styles[name];
                        if (s != null) return s;
                        var a = this.attribute(name);
                        if (a != null && a.hasValue()) {
                            this.styles[name] = a;
                            // move up to me to cache
                            return a;
                        }
                        var p = this.parent;
                        if (p != null) {
                            var ps = p.style(name);
                            if (ps != null && ps.hasValue()) {
                                return ps;
                            }
                        }
                        if (createIfNotExists == true) {
                            s = new svg.Property(name, "");
                            this.styles[name] = s;
                        }
                        return s || svg.EmptyProperty;
                    };
                    // base render
                    this.render = function(ctx) {
                        // don't render display=none
                        if (this.style("display").value == "none") return;
                        // don't render visibility=hidden
                        if (this.attribute("visibility").value == "hidden") return;
                        ctx.save();
                        if (this.attribute("mask").hasValue()) {
                            // mask
                            var mask = this.attribute("mask").getDefinition();
                            if (mask != null) mask.apply(ctx, this);
                        } else if (this.style("filter").hasValue()) {
                            // filter
                            var filter = this.style("filter").getDefinition();
                            if (filter != null) filter.apply(ctx, this);
                        } else {
                            this.setContext(ctx);
                            this.renderChildren(ctx);
                            this.clearContext(ctx);
                        }
                        ctx.restore();
                    };
                    // base set context
                    this.setContext = function(ctx) {};
                    // base clear context
                    this.clearContext = function(ctx) {};
                    // base render children
                    this.renderChildren = function(ctx) {
                        for (var i = 0; i < this.children.length; i++) {
                            this.children[i].render(ctx);
                        }
                    };
                    this.addChild = function(childNode, create) {
                        var child = childNode;
                        if (create) child = svg.CreateElement(childNode);
                        child.parent = this;
                        this.children.push(child);
                    };
                    if (node != null && node.nodeType == 1) {
                        //ELEMENT_NODE
                        // add children
                        for (var i = 0; i < node.childNodes.length; i++) {
                            var childNode = node.childNodes[i];
                            if (childNode.nodeType == 1) this.addChild(childNode, true);
                            //ELEMENT_NODE
                            if (this.captureTextNodes && childNode.nodeType == 3) {
                                var text = childNode.nodeValue || childNode.text || "";
                                if (svg.trim(svg.compressSpaces(text)) != "") {
                                    this.addChild(new svg.Element.tspan(childNode), false);
                                }
                            }
                        }
                        // add attributes
                        for (var i = 0; i < node.attributes.length; i++) {
                            var attribute = node.attributes[i];
                            this.attributes[attribute.nodeName] = new svg.Property(attribute.nodeName, attribute.nodeValue);
                        }
                        // add tag styles
                        var styles = svg.Styles[node.nodeName];
                        if (styles != null) {
                            for (var name in styles) {
                                this.styles[name] = styles[name];
                            }
                        }
                        // add class styles
                        if (this.attribute("class").hasValue()) {
                            var classes = svg.compressSpaces(this.attribute("class").value).split(" ");
                            for (var j = 0; j < classes.length; j++) {
                                styles = svg.Styles["." + classes[j]];
                                if (styles != null) {
                                    for (var name in styles) {
                                        this.styles[name] = styles[name];
                                    }
                                }
                                styles = svg.Styles[node.nodeName + "." + classes[j]];
                                if (styles != null) {
                                    for (var name in styles) {
                                        this.styles[name] = styles[name];
                                    }
                                }
                            }
                        }
                        // add id styles
                        if (this.attribute("id").hasValue()) {
                            var styles = svg.Styles["#" + this.attribute("id").value];
                            if (styles != null) {
                                for (var name in styles) {
                                    this.styles[name] = styles[name];
                                }
                            }
                        }
                        // add inline styles
                        if (this.attribute("style").hasValue()) {
                            var styles = this.attribute("style").value.split(";");
                            for (var i = 0; i < styles.length; i++) {
                                if (svg.trim(styles[i]) != "") {
                                    var style = styles[i].split(":");
                                    var name = svg.trim(style[0]);
                                    var value = svg.trim(style[1]);
                                    this.styles[name] = new svg.Property(name, value);
                                }
                            }
                        }
                        // add id
                        if (this.attribute("id").hasValue()) {
                            if (svg.Definitions[this.attribute("id").value] == null) {
                                svg.Definitions[this.attribute("id").value] = this;
                            }
                        }
                    }
                };
                svg.Element.RenderedElementBase = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.setContext = function(ctx) {
                        // fill
                        if (this.style("fill").isUrlDefinition()) {
                            var fs = this.style("fill").getFillStyleDefinition(this, this.style("fill-opacity"));
                            if (fs != null) ctx.fillStyle = fs;
                        } else if (this.style("fill").hasValue()) {
                            var fillStyle = this.style("fill");
                            if (fillStyle.value == "currentColor") fillStyle.value = this.style("color").value;
                            ctx.fillStyle = fillStyle.value == "none" ? "rgba(0,0,0,0)" : fillStyle.value;
                        }
                        if (this.style("fill-opacity").hasValue()) {
                            var fillStyle = new svg.Property("fill", ctx.fillStyle);
                            fillStyle = fillStyle.addOpacity(this.style("fill-opacity").value);
                            ctx.fillStyle = fillStyle.value;
                        }
                        // stroke
                        if (this.style("stroke").isUrlDefinition()) {
                            var fs = this.style("stroke").getFillStyleDefinition(this, this.style("stroke-opacity"));
                            if (fs != null) ctx.strokeStyle = fs;
                        } else if (this.style("stroke").hasValue()) {
                            var strokeStyle = this.style("stroke");
                            if (strokeStyle.value == "currentColor") strokeStyle.value = this.style("color").value;
                            ctx.strokeStyle = strokeStyle.value == "none" ? "rgba(0,0,0,0)" : strokeStyle.value;
                        }
                        if (this.style("stroke-opacity").hasValue()) {
                            var strokeStyle = new svg.Property("stroke", ctx.strokeStyle);
                            strokeStyle = strokeStyle.addOpacity(this.style("stroke-opacity").value);
                            ctx.strokeStyle = strokeStyle.value;
                        }
                        if (this.style("stroke-width").hasValue()) {
                            var newLineWidth = this.style("stroke-width").toPixels();
                            ctx.lineWidth = newLineWidth == 0 ? .001 : newLineWidth;
                        }
                        if (this.style("stroke-linecap").hasValue()) ctx.lineCap = this.style("stroke-linecap").value;
                        if (this.style("stroke-linejoin").hasValue()) ctx.lineJoin = this.style("stroke-linejoin").value;
                        if (this.style("stroke-miterlimit").hasValue()) ctx.miterLimit = this.style("stroke-miterlimit").value;
                        if (this.style("stroke-dasharray").hasValue()) {
                            var gaps = svg.ToNumberArray(this.style("stroke-dasharray").value);
                            if (typeof ctx.setLineDash != "undefined") {
                                ctx.setLineDash(gaps);
                            } else if (typeof ctx.webkitLineDash != "undefined") {
                                ctx.webkitLineDash = gaps;
                            } else if (typeof ctx.mozDash != "undefined") {
                                ctx.mozDash = gaps;
                            }
                            var offset = this.style("stroke-dashoffset").numValueOrDefault(1);
                            if (typeof ctx.lineDashOffset != "undefined") {
                                ctx.lineDashOffset = offset;
                            } else if (typeof ctx.webkitLineDashOffset != "undefined") {
                                ctx.webkitLineDashOffset = offset;
                            } else if (typeof ctx.mozDashOffset != "undefined") {
                                ctx.mozDashOffset = offset;
                            }
                        }
                        // font
                        if (typeof ctx.font != "undefined") {
                            ctx.font = svg.Font.CreateFont(this.style("font-style").value, this.style("font-variant").value, this.style("font-weight").value, this.style("font-size").hasValue() ? this.style("font-size").toPixels() + "px" : "", this.style("font-family").value).toString();
                        }
                        // transform
                        if (this.attribute("transform").hasValue()) {
                            var transform = new svg.Transform(this.attribute("transform").value);
                            transform.apply(ctx);
                        }
                        // clip
                        if (this.style("clip-path").hasValue()) {
                            var clip = this.style("clip-path").getDefinition();
                            if (clip != null) clip.apply(ctx);
                        }
                        // opacity
                        if (this.style("opacity").hasValue()) {
                            ctx.globalAlpha = this.style("opacity").numValue();
                        }
                    };
                };
                svg.Element.RenderedElementBase.prototype = new svg.Element.ElementBase();
                svg.Element.PathElementBase = function(node) {
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    this.path = function(ctx) {
                        if (ctx != null) ctx.beginPath();
                        return new svg.BoundingBox();
                    };
                    this.renderChildren = function(ctx) {
                        this.path(ctx);
                        svg.Mouse.checkPath(this, ctx);
                        if (ctx.fillStyle != "") {
                            if (this.attribute("fill-rule").hasValue()) {
                                ctx.fill(this.attribute("fill-rule").value);
                            } else {
                                ctx.fill();
                            }
                        }
                        if (ctx.strokeStyle != "") ctx.stroke();
                        var markers = this.getMarkers();
                        if (markers != null) {
                            if (this.style("marker-start").isUrlDefinition()) {
                                var marker = this.style("marker-start").getDefinition();
                                marker.render(ctx, markers[0][0], markers[0][1]);
                            }
                            if (this.style("marker-mid").isUrlDefinition()) {
                                var marker = this.style("marker-mid").getDefinition();
                                for (var i = 1; i < markers.length - 1; i++) {
                                    marker.render(ctx, markers[i][0], markers[i][1]);
                                }
                            }
                            if (this.style("marker-end").isUrlDefinition()) {
                                var marker = this.style("marker-end").getDefinition();
                                marker.render(ctx, markers[markers.length - 1][0], markers[markers.length - 1][1]);
                            }
                        }
                    };
                    this.getBoundingBox = function() {
                        return this.path();
                    };
                    this.getMarkers = function() {
                        return null;
                    };
                };
                svg.Element.PathElementBase.prototype = new svg.Element.RenderedElementBase();
                // svg element
                svg.Element.svg = function(node) {
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    this.baseClearContext = this.clearContext;
                    this.clearContext = function(ctx) {
                        this.baseClearContext(ctx);
                        svg.ViewPort.RemoveCurrent();
                    };
                    this.baseSetContext = this.setContext;
                    this.setContext = function(ctx) {
                        // initial values
                        ctx.strokeStyle = "rgba(0,0,0,0)";
                        ctx.lineCap = "butt";
                        ctx.lineJoin = "miter";
                        ctx.miterLimit = 4;
                        this.baseSetContext(ctx);
                        // create new view port
                        if (!this.attribute("x").hasValue()) this.attribute("x", true).value = 0;
                        if (!this.attribute("y").hasValue()) this.attribute("y", true).value = 0;
                        ctx.translate(this.attribute("x").toPixels("x"), this.attribute("y").toPixels("y"));
                        var width = svg.ViewPort.width();
                        var height = svg.ViewPort.height();
                        if (!this.attribute("width").hasValue()) this.attribute("width", true).value = "100%";
                        if (!this.attribute("height").hasValue()) this.attribute("height", true).value = "100%";
                        if (typeof this.root == "undefined") {
                            width = this.attribute("width").toPixels("x");
                            height = this.attribute("height").toPixels("y");
                            var x = 0;
                            var y = 0;
                            if (this.attribute("refX").hasValue() && this.attribute("refY").hasValue()) {
                                x = -this.attribute("refX").toPixels("x");
                                y = -this.attribute("refY").toPixels("y");
                            }
                            ctx.beginPath();
                            ctx.moveTo(x, y);
                            ctx.lineTo(width, y);
                            ctx.lineTo(width, height);
                            ctx.lineTo(x, height);
                            ctx.closePath();
                            ctx.clip();
                        }
                        svg.ViewPort.SetCurrent(width, height);
                        // viewbox
                        if (this.attribute("viewBox").hasValue()) {
                            var viewBox = svg.ToNumberArray(this.attribute("viewBox").value);
                            var minX = viewBox[0];
                            var minY = viewBox[1];
                            width = viewBox[2];
                            height = viewBox[3];
                            svg.AspectRatio(ctx, this.attribute("preserveAspectRatio").value, svg.ViewPort.width(), width, svg.ViewPort.height(), height, minX, minY, this.attribute("refX").value, this.attribute("refY").value);
                            svg.ViewPort.RemoveCurrent();
                            svg.ViewPort.SetCurrent(viewBox[2], viewBox[3]);
                        }
                    };
                };
                svg.Element.svg.prototype = new svg.Element.RenderedElementBase();
                // rect element
                svg.Element.rect = function(node) {
                    this.base = svg.Element.PathElementBase;
                    this.base(node);
                    this.path = function(ctx) {
                        var x = this.attribute("x").toPixels("x");
                        var y = this.attribute("y").toPixels("y");
                        var width = this.attribute("width").toPixels("x");
                        var height = this.attribute("height").toPixels("y");
                        var rx = this.attribute("rx").toPixels("x");
                        var ry = this.attribute("ry").toPixels("y");
                        if (this.attribute("rx").hasValue() && !this.attribute("ry").hasValue()) ry = rx;
                        if (this.attribute("ry").hasValue() && !this.attribute("rx").hasValue()) rx = ry;
                        rx = Math.min(rx, width / 2);
                        ry = Math.min(ry, height / 2);
                        if (ctx != null) {
                            ctx.beginPath();
                            ctx.moveTo(x + rx, y);
                            ctx.lineTo(x + width - rx, y);
                            ctx.quadraticCurveTo(x + width, y, x + width, y + ry);
                            ctx.lineTo(x + width, y + height - ry);
                            ctx.quadraticCurveTo(x + width, y + height, x + width - rx, y + height);
                            ctx.lineTo(x + rx, y + height);
                            ctx.quadraticCurveTo(x, y + height, x, y + height - ry);
                            ctx.lineTo(x, y + ry);
                            ctx.quadraticCurveTo(x, y, x + rx, y);
                            ctx.closePath();
                        }
                        return new svg.BoundingBox(x, y, x + width, y + height);
                    };
                };
                svg.Element.rect.prototype = new svg.Element.PathElementBase();
                // circle element
                svg.Element.circle = function(node) {
                    this.base = svg.Element.PathElementBase;
                    this.base(node);
                    this.path = function(ctx) {
                        var cx = this.attribute("cx").toPixels("x");
                        var cy = this.attribute("cy").toPixels("y");
                        var r = this.attribute("r").toPixels();
                        if (ctx != null) {
                            ctx.beginPath();
                            ctx.arc(cx, cy, r, 0, Math.PI * 2, true);
                            ctx.closePath();
                        }
                        return new svg.BoundingBox(cx - r, cy - r, cx + r, cy + r);
                    };
                };
                svg.Element.circle.prototype = new svg.Element.PathElementBase();
                // ellipse element
                svg.Element.ellipse = function(node) {
                    this.base = svg.Element.PathElementBase;
                    this.base(node);
                    this.path = function(ctx) {
                        var KAPPA = 4 * ((Math.sqrt(2) - 1) / 3);
                        var rx = this.attribute("rx").toPixels("x");
                        var ry = this.attribute("ry").toPixels("y");
                        var cx = this.attribute("cx").toPixels("x");
                        var cy = this.attribute("cy").toPixels("y");
                        if (ctx != null) {
                            ctx.beginPath();
                            ctx.moveTo(cx, cy - ry);
                            ctx.bezierCurveTo(cx + KAPPA * rx, cy - ry, cx + rx, cy - KAPPA * ry, cx + rx, cy);
                            ctx.bezierCurveTo(cx + rx, cy + KAPPA * ry, cx + KAPPA * rx, cy + ry, cx, cy + ry);
                            ctx.bezierCurveTo(cx - KAPPA * rx, cy + ry, cx - rx, cy + KAPPA * ry, cx - rx, cy);
                            ctx.bezierCurveTo(cx - rx, cy - KAPPA * ry, cx - KAPPA * rx, cy - ry, cx, cy - ry);
                            ctx.closePath();
                        }
                        return new svg.BoundingBox(cx - rx, cy - ry, cx + rx, cy + ry);
                    };
                };
                svg.Element.ellipse.prototype = new svg.Element.PathElementBase();
                // line element
                svg.Element.line = function(node) {
                    this.base = svg.Element.PathElementBase;
                    this.base(node);
                    this.getPoints = function() {
                        return [ new svg.Point(this.attribute("x1").toPixels("x"), this.attribute("y1").toPixels("y")), new svg.Point(this.attribute("x2").toPixels("x"), this.attribute("y2").toPixels("y")) ];
                    };
                    this.path = function(ctx) {
                        var points = this.getPoints();
                        if (ctx != null) {
                            ctx.beginPath();
                            ctx.moveTo(points[0].x, points[0].y);
                            ctx.lineTo(points[1].x, points[1].y);
                        }
                        return new svg.BoundingBox(points[0].x, points[0].y, points[1].x, points[1].y);
                    };
                    this.getMarkers = function() {
                        var points = this.getPoints();
                        var a = points[0].angleTo(points[1]);
                        return [ [ points[0], a ], [ points[1], a ] ];
                    };
                };
                svg.Element.line.prototype = new svg.Element.PathElementBase();
                // polyline element
                svg.Element.polyline = function(node) {
                    this.base = svg.Element.PathElementBase;
                    this.base(node);
                    this.points = svg.CreatePath(this.attribute("points").value);
                    this.path = function(ctx) {
                        var bb = new svg.BoundingBox(this.points[0].x, this.points[0].y);
                        if (ctx != null) {
                            ctx.beginPath();
                            ctx.moveTo(this.points[0].x, this.points[0].y);
                        }
                        for (var i = 1; i < this.points.length; i++) {
                            bb.addPoint(this.points[i].x, this.points[i].y);
                            if (ctx != null) ctx.lineTo(this.points[i].x, this.points[i].y);
                        }
                        return bb;
                    };
                    this.getMarkers = function() {
                        var markers = [];
                        for (var i = 0; i < this.points.length - 1; i++) {
                            markers.push([ this.points[i], this.points[i].angleTo(this.points[i + 1]) ]);
                        }
                        markers.push([ this.points[this.points.length - 1], markers[markers.length - 1][1] ]);
                        return markers;
                    };
                };
                svg.Element.polyline.prototype = new svg.Element.PathElementBase();
                // polygon element
                svg.Element.polygon = function(node) {
                    this.base = svg.Element.polyline;
                    this.base(node);
                    this.basePath = this.path;
                    this.path = function(ctx) {
                        var bb = this.basePath(ctx);
                        if (ctx != null) {
                            ctx.lineTo(this.points[0].x, this.points[0].y);
                            ctx.closePath();
                        }
                        return bb;
                    };
                };
                svg.Element.polygon.prototype = new svg.Element.polyline();
                // path element
                svg.Element.path = function(node) {
                    this.base = svg.Element.PathElementBase;
                    this.base(node);
                    var d = this.attribute("d").value;
                    // TODO: convert to real lexer based on http://www.w3.org/TR/SVG11/paths.html#PathDataBNF
                    d = d.replace(/,/gm, " ");
                    // get rid of all commas
                    d = d.replace(/([MmZzLlHhVvCcSsQqTtAa])([MmZzLlHhVvCcSsQqTtAa])/gm, "$1 $2");
                    // separate commands from commands
                    d = d.replace(/([MmZzLlHhVvCcSsQqTtAa])([MmZzLlHhVvCcSsQqTtAa])/gm, "$1 $2");
                    // separate commands from commands
                    d = d.replace(/([MmZzLlHhVvCcSsQqTtAa])([^\s])/gm, "$1 $2");
                    // separate commands from points
                    d = d.replace(/([^\s])([MmZzLlHhVvCcSsQqTtAa])/gm, "$1 $2");
                    // separate commands from points
                    d = d.replace(/([0-9])([+\-])/gm, "$1 $2");
                    // separate digits when no comma
                    d = d.replace(/(\.[0-9]*)(\.)/gm, "$1 $2");
                    // separate digits when no comma
                    d = d.replace(/([Aa](\s+[0-9]+){3})\s+([01])\s*([01])/gm, "$1 $3 $4 ");
                    // shorthand elliptical arc path syntax
                    d = svg.compressSpaces(d);
                    // compress multiple spaces
                    d = svg.trim(d);
                    this.PathParser = new function(d) {
                        this.tokens = d.split(" ");
                        this.reset = function() {
                            this.i = -1;
                            this.command = "";
                            this.previousCommand = "";
                            this.start = new svg.Point(0, 0);
                            this.control = new svg.Point(0, 0);
                            this.current = new svg.Point(0, 0);
                            this.points = [];
                            this.angles = [];
                        };
                        this.isEnd = function() {
                            return this.i >= this.tokens.length - 1;
                        };
                        this.isCommandOrEnd = function() {
                            if (this.isEnd()) return true;
                            return this.tokens[this.i + 1].match(/^[A-Za-z]$/) != null;
                        };
                        this.isRelativeCommand = function() {
                            switch (this.command) {
                              case "m":
                              case "l":
                              case "h":
                              case "v":
                              case "c":
                              case "s":
                              case "q":
                              case "t":
                              case "a":
                              case "z":
                                return true;
                                break;
                            }
                            return false;
                        };
                        this.getToken = function() {
                            this.i++;
                            return this.tokens[this.i];
                        };
                        this.getScalar = function() {
                            return parseFloat(this.getToken());
                        };
                        this.nextCommand = function() {
                            this.previousCommand = this.command;
                            this.command = this.getToken();
                        };
                        this.getPoint = function() {
                            var p = new svg.Point(this.getScalar(), this.getScalar());
                            return this.makeAbsolute(p);
                        };
                        this.getAsControlPoint = function() {
                            var p = this.getPoint();
                            this.control = p;
                            return p;
                        };
                        this.getAsCurrentPoint = function() {
                            var p = this.getPoint();
                            this.current = p;
                            return p;
                        };
                        this.getReflectedControlPoint = function() {
                            if (this.previousCommand.toLowerCase() != "c" && this.previousCommand.toLowerCase() != "s" && this.previousCommand.toLowerCase() != "q" && this.previousCommand.toLowerCase() != "t") {
                                return this.current;
                            }
                            // reflect point
                            var p = new svg.Point(2 * this.current.x - this.control.x, 2 * this.current.y - this.control.y);
                            return p;
                        };
                        this.makeAbsolute = function(p) {
                            if (this.isRelativeCommand()) {
                                p.x += this.current.x;
                                p.y += this.current.y;
                            }
                            return p;
                        };
                        this.addMarker = function(p, from, priorTo) {
                            // if the last angle isn't filled in because we didn't have this point yet ...
                            if (priorTo != null && this.angles.length > 0 && this.angles[this.angles.length - 1] == null) {
                                this.angles[this.angles.length - 1] = this.points[this.points.length - 1].angleTo(priorTo);
                            }
                            this.addMarkerAngle(p, from == null ? null : from.angleTo(p));
                        };
                        this.addMarkerAngle = function(p, a) {
                            this.points.push(p);
                            this.angles.push(a);
                        };
                        this.getMarkerPoints = function() {
                            return this.points;
                        };
                        this.getMarkerAngles = function() {
                            for (var i = 0; i < this.angles.length; i++) {
                                if (this.angles[i] == null) {
                                    for (var j = i + 1; j < this.angles.length; j++) {
                                        if (this.angles[j] != null) {
                                            this.angles[i] = this.angles[j];
                                            break;
                                        }
                                    }
                                }
                            }
                            return this.angles;
                        };
                    }(d);
                    this.path = function(ctx) {
                        var pp = this.PathParser;
                        pp.reset();
                        var bb = new svg.BoundingBox();
                        if (ctx != null) ctx.beginPath();
                        while (!pp.isEnd()) {
                            pp.nextCommand();
                            switch (pp.command) {
                              case "M":
                              case "m":
                                var p = pp.getAsCurrentPoint();
                                pp.addMarker(p);
                                bb.addPoint(p.x, p.y);
                                if (ctx != null) ctx.moveTo(p.x, p.y);
                                pp.start = pp.current;
                                while (!pp.isCommandOrEnd()) {
                                    var p = pp.getAsCurrentPoint();
                                    pp.addMarker(p, pp.start);
                                    bb.addPoint(p.x, p.y);
                                    if (ctx != null) ctx.lineTo(p.x, p.y);
                                }
                                break;

                              case "L":
                              case "l":
                                while (!pp.isCommandOrEnd()) {
                                    var c = pp.current;
                                    var p = pp.getAsCurrentPoint();
                                    pp.addMarker(p, c);
                                    bb.addPoint(p.x, p.y);
                                    if (ctx != null) ctx.lineTo(p.x, p.y);
                                }
                                break;

                              case "H":
                              case "h":
                                while (!pp.isCommandOrEnd()) {
                                    var newP = new svg.Point((pp.isRelativeCommand() ? pp.current.x : 0) + pp.getScalar(), pp.current.y);
                                    pp.addMarker(newP, pp.current);
                                    pp.current = newP;
                                    bb.addPoint(pp.current.x, pp.current.y);
                                    if (ctx != null) ctx.lineTo(pp.current.x, pp.current.y);
                                }
                                break;

                              case "V":
                              case "v":
                                while (!pp.isCommandOrEnd()) {
                                    var newP = new svg.Point(pp.current.x, (pp.isRelativeCommand() ? pp.current.y : 0) + pp.getScalar());
                                    pp.addMarker(newP, pp.current);
                                    pp.current = newP;
                                    bb.addPoint(pp.current.x, pp.current.y);
                                    if (ctx != null) ctx.lineTo(pp.current.x, pp.current.y);
                                }
                                break;

                              case "C":
                              case "c":
                                while (!pp.isCommandOrEnd()) {
                                    var curr = pp.current;
                                    var p1 = pp.getPoint();
                                    var cntrl = pp.getAsControlPoint();
                                    var cp = pp.getAsCurrentPoint();
                                    pp.addMarker(cp, cntrl, p1);
                                    bb.addBezierCurve(curr.x, curr.y, p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                    if (ctx != null) ctx.bezierCurveTo(p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                }
                                break;

                              case "S":
                              case "s":
                                while (!pp.isCommandOrEnd()) {
                                    var curr = pp.current;
                                    var p1 = pp.getReflectedControlPoint();
                                    var cntrl = pp.getAsControlPoint();
                                    var cp = pp.getAsCurrentPoint();
                                    pp.addMarker(cp, cntrl, p1);
                                    bb.addBezierCurve(curr.x, curr.y, p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                    if (ctx != null) ctx.bezierCurveTo(p1.x, p1.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                }
                                break;

                              case "Q":
                              case "q":
                                while (!pp.isCommandOrEnd()) {
                                    var curr = pp.current;
                                    var cntrl = pp.getAsControlPoint();
                                    var cp = pp.getAsCurrentPoint();
                                    pp.addMarker(cp, cntrl, cntrl);
                                    bb.addQuadraticCurve(curr.x, curr.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                    if (ctx != null) ctx.quadraticCurveTo(cntrl.x, cntrl.y, cp.x, cp.y);
                                }
                                break;

                              case "T":
                              case "t":
                                while (!pp.isCommandOrEnd()) {
                                    var curr = pp.current;
                                    var cntrl = pp.getReflectedControlPoint();
                                    pp.control = cntrl;
                                    var cp = pp.getAsCurrentPoint();
                                    pp.addMarker(cp, cntrl, cntrl);
                                    bb.addQuadraticCurve(curr.x, curr.y, cntrl.x, cntrl.y, cp.x, cp.y);
                                    if (ctx != null) ctx.quadraticCurveTo(cntrl.x, cntrl.y, cp.x, cp.y);
                                }
                                break;

                              case "A":
                              case "a":
                                while (!pp.isCommandOrEnd()) {
                                    var curr = pp.current;
                                    var rx = pp.getScalar();
                                    var ry = pp.getScalar();
                                    var xAxisRotation = pp.getScalar() * (Math.PI / 180);
                                    var largeArcFlag = pp.getScalar();
                                    var sweepFlag = pp.getScalar();
                                    var cp = pp.getAsCurrentPoint();
                                    // Conversion from endpoint to center parameterization
                                    // http://www.w3.org/TR/SVG11/implnote.html#ArcImplementationNotes
                                    // x1', y1'
                                    var currp = new svg.Point(Math.cos(xAxisRotation) * (curr.x - cp.x) / 2 + Math.sin(xAxisRotation) * (curr.y - cp.y) / 2, -Math.sin(xAxisRotation) * (curr.x - cp.x) / 2 + Math.cos(xAxisRotation) * (curr.y - cp.y) / 2);
                                    // adjust radii
                                    var l = Math.pow(currp.x, 2) / Math.pow(rx, 2) + Math.pow(currp.y, 2) / Math.pow(ry, 2);
                                    if (l > 1) {
                                        rx *= Math.sqrt(l);
                                        ry *= Math.sqrt(l);
                                    }
                                    // cx', cy'
                                    var s = (largeArcFlag == sweepFlag ? -1 : 1) * Math.sqrt((Math.pow(rx, 2) * Math.pow(ry, 2) - Math.pow(rx, 2) * Math.pow(currp.y, 2) - Math.pow(ry, 2) * Math.pow(currp.x, 2)) / (Math.pow(rx, 2) * Math.pow(currp.y, 2) + Math.pow(ry, 2) * Math.pow(currp.x, 2)));
                                    if (isNaN(s)) s = 0;
                                    var cpp = new svg.Point(s * rx * currp.y / ry, s * -ry * currp.x / rx);
                                    // cx, cy
                                    var centp = new svg.Point((curr.x + cp.x) / 2 + Math.cos(xAxisRotation) * cpp.x - Math.sin(xAxisRotation) * cpp.y, (curr.y + cp.y) / 2 + Math.sin(xAxisRotation) * cpp.x + Math.cos(xAxisRotation) * cpp.y);
                                    // vector magnitude
                                    var m = function(v) {
                                        return Math.sqrt(Math.pow(v[0], 2) + Math.pow(v[1], 2));
                                    };
                                    // ratio between two vectors
                                    var r = function(u, v) {
                                        return (u[0] * v[0] + u[1] * v[1]) / (m(u) * m(v));
                                    };
                                    // angle between two vectors
                                    var a = function(u, v) {
                                        return (u[0] * v[1] < u[1] * v[0] ? -1 : 1) * Math.acos(r(u, v));
                                    };
                                    // initial angle
                                    var a1 = a([ 1, 0 ], [ (currp.x - cpp.x) / rx, (currp.y - cpp.y) / ry ]);
                                    // angle delta
                                    var u = [ (currp.x - cpp.x) / rx, (currp.y - cpp.y) / ry ];
                                    var v = [ (-currp.x - cpp.x) / rx, (-currp.y - cpp.y) / ry ];
                                    var ad = a(u, v);
                                    if (r(u, v) <= -1) ad = Math.PI;
                                    if (r(u, v) >= 1) ad = 0;
                                    // for markers
                                    var dir = 1 - sweepFlag ? 1 : -1;
                                    var ah = a1 + dir * (ad / 2);
                                    var halfWay = new svg.Point(centp.x + rx * Math.cos(ah), centp.y + ry * Math.sin(ah));
                                    pp.addMarkerAngle(halfWay, ah - dir * Math.PI / 2);
                                    pp.addMarkerAngle(cp, ah - dir * Math.PI);
                                    bb.addPoint(cp.x, cp.y);
                                    // TODO: this is too naive, make it better
                                    if (ctx != null) {
                                        var r = rx > ry ? rx : ry;
                                        var sx = rx > ry ? 1 : rx / ry;
                                        var sy = rx > ry ? ry / rx : 1;
                                        ctx.translate(centp.x, centp.y);
                                        ctx.rotate(xAxisRotation);
                                        ctx.scale(sx, sy);
                                        ctx.arc(0, 0, r, a1, a1 + ad, 1 - sweepFlag);
                                        ctx.scale(1 / sx, 1 / sy);
                                        ctx.rotate(-xAxisRotation);
                                        ctx.translate(-centp.x, -centp.y);
                                    }
                                }
                                break;

                              case "Z":
                              case "z":
                                if (ctx != null) ctx.closePath();
                                pp.current = pp.start;
                            }
                        }
                        return bb;
                    };
                    this.getMarkers = function() {
                        var points = this.PathParser.getMarkerPoints();
                        var angles = this.PathParser.getMarkerAngles();
                        var markers = [];
                        for (var i = 0; i < points.length; i++) {
                            markers.push([ points[i], angles[i] ]);
                        }
                        return markers;
                    };
                };
                svg.Element.path.prototype = new svg.Element.PathElementBase();
                // pattern element
                svg.Element.pattern = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.createPattern = function(ctx, element) {
                        var width = this.attribute("width").toPixels("x", true);
                        var height = this.attribute("height").toPixels("y", true);
                        // render me using a temporary svg element
                        var tempSvg = new svg.Element.svg();
                        tempSvg.attributes["viewBox"] = new svg.Property("viewBox", this.attribute("viewBox").value);
                        tempSvg.attributes["width"] = new svg.Property("width", width + "px");
                        tempSvg.attributes["height"] = new svg.Property("height", height + "px");
                        tempSvg.attributes["transform"] = new svg.Property("transform", this.attribute("patternTransform").value);
                        tempSvg.children = this.children;
                        var c = document.createElement("canvas");
                        c.width = width;
                        c.height = height;
                        var cctx = c.getContext("2d");
                        if (this.attribute("x").hasValue() && this.attribute("y").hasValue()) {
                            cctx.translate(this.attribute("x").toPixels("x", true), this.attribute("y").toPixels("y", true));
                        }
                        // render 3x3 grid so when we transform there's no white space on edges
                        for (var x = -1; x <= 1; x++) {
                            for (var y = -1; y <= 1; y++) {
                                cctx.save();
                                cctx.translate(x * c.width, y * c.height);
                                tempSvg.render(cctx);
                                cctx.restore();
                            }
                        }
                        var pattern = ctx.createPattern(c, "repeat");
                        return pattern;
                    };
                };
                svg.Element.pattern.prototype = new svg.Element.ElementBase();
                // marker element
                svg.Element.marker = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.baseRender = this.render;
                    this.render = function(ctx, point, angle) {
                        ctx.translate(point.x, point.y);
                        if (this.attribute("orient").valueOrDefault("auto") == "auto") ctx.rotate(angle);
                        if (this.attribute("markerUnits").valueOrDefault("strokeWidth") == "strokeWidth") ctx.scale(ctx.lineWidth, ctx.lineWidth);
                        ctx.save();
                        // render me using a temporary svg element
                        var tempSvg = new svg.Element.svg();
                        tempSvg.attributes["viewBox"] = new svg.Property("viewBox", this.attribute("viewBox").value);
                        tempSvg.attributes["refX"] = new svg.Property("refX", this.attribute("refX").value);
                        tempSvg.attributes["refY"] = new svg.Property("refY", this.attribute("refY").value);
                        tempSvg.attributes["width"] = new svg.Property("width", this.attribute("markerWidth").value);
                        tempSvg.attributes["height"] = new svg.Property("height", this.attribute("markerHeight").value);
                        tempSvg.attributes["fill"] = new svg.Property("fill", this.attribute("fill").valueOrDefault("black"));
                        tempSvg.attributes["stroke"] = new svg.Property("stroke", this.attribute("stroke").valueOrDefault("none"));
                        tempSvg.children = this.children;
                        tempSvg.render(ctx);
                        ctx.restore();
                        if (this.attribute("markerUnits").valueOrDefault("strokeWidth") == "strokeWidth") ctx.scale(1 / ctx.lineWidth, 1 / ctx.lineWidth);
                        if (this.attribute("orient").valueOrDefault("auto") == "auto") ctx.rotate(-angle);
                        ctx.translate(-point.x, -point.y);
                    };
                };
                svg.Element.marker.prototype = new svg.Element.ElementBase();
                // definitions element
                svg.Element.defs = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.render = function(ctx) {};
                };
                svg.Element.defs.prototype = new svg.Element.ElementBase();
                // base for gradients
                svg.Element.GradientBase = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.gradientUnits = this.attribute("gradientUnits").valueOrDefault("objectBoundingBox");
                    this.stops = [];
                    for (var i = 0; i < this.children.length; i++) {
                        var child = this.children[i];
                        if (child.type == "stop") this.stops.push(child);
                    }
                    this.getGradient = function() {};
                    this.createGradient = function(ctx, element, parentOpacityProp) {
                        var stopsContainer = this;
                        if (this.getHrefAttribute().hasValue()) {
                            stopsContainer = this.getHrefAttribute().getDefinition();
                        }
                        var addParentOpacity = function(color) {
                            if (parentOpacityProp.hasValue()) {
                                var p = new svg.Property("color", color);
                                return p.addOpacity(parentOpacityProp.value).value;
                            }
                            return color;
                        };
                        var g = this.getGradient(ctx, element);
                        if (g == null) return addParentOpacity(stopsContainer.stops[stopsContainer.stops.length - 1].color);
                        for (var i = 0; i < stopsContainer.stops.length; i++) {
                            g.addColorStop(stopsContainer.stops[i].offset, addParentOpacity(stopsContainer.stops[i].color));
                        }
                        if (this.attribute("gradientTransform").hasValue()) {
                            // render as transformed pattern on temporary canvas
                            var rootView = svg.ViewPort.viewPorts[0];
                            var rect = new svg.Element.rect();
                            rect.attributes["x"] = new svg.Property("x", -svg.MAX_VIRTUAL_PIXELS / 3);
                            rect.attributes["y"] = new svg.Property("y", -svg.MAX_VIRTUAL_PIXELS / 3);
                            rect.attributes["width"] = new svg.Property("width", svg.MAX_VIRTUAL_PIXELS);
                            rect.attributes["height"] = new svg.Property("height", svg.MAX_VIRTUAL_PIXELS);
                            var group = new svg.Element.g();
                            group.attributes["transform"] = new svg.Property("transform", this.attribute("gradientTransform").value);
                            group.children = [ rect ];
                            var tempSvg = new svg.Element.svg();
                            tempSvg.attributes["x"] = new svg.Property("x", 0);
                            tempSvg.attributes["y"] = new svg.Property("y", 0);
                            tempSvg.attributes["width"] = new svg.Property("width", rootView.width);
                            tempSvg.attributes["height"] = new svg.Property("height", rootView.height);
                            tempSvg.children = [ group ];
                            var c = document.createElement("canvas");
                            c.width = rootView.width;
                            c.height = rootView.height;
                            var tempCtx = c.getContext("2d");
                            tempCtx.fillStyle = g;
                            tempSvg.render(tempCtx);
                            return tempCtx.createPattern(c, "no-repeat");
                        }
                        return g;
                    };
                };
                svg.Element.GradientBase.prototype = new svg.Element.ElementBase();
                // linear gradient element
                svg.Element.linearGradient = function(node) {
                    this.base = svg.Element.GradientBase;
                    this.base(node);
                    this.getGradient = function(ctx, element) {
                        var bb = element.getBoundingBox();
                        if (!this.attribute("x1").hasValue() && !this.attribute("y1").hasValue() && !this.attribute("x2").hasValue() && !this.attribute("y2").hasValue()) {
                            this.attribute("x1", true).value = 0;
                            this.attribute("y1", true).value = 0;
                            this.attribute("x2", true).value = 1;
                            this.attribute("y2", true).value = 0;
                        }
                        var x1 = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("x1").numValue() : this.attribute("x1").toPixels("x");
                        var y1 = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("y1").numValue() : this.attribute("y1").toPixels("y");
                        var x2 = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("x2").numValue() : this.attribute("x2").toPixels("x");
                        var y2 = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("y2").numValue() : this.attribute("y2").toPixels("y");
                        if (x1 == x2 && y1 == y2) return null;
                        return ctx.createLinearGradient(x1, y1, x2, y2);
                    };
                };
                svg.Element.linearGradient.prototype = new svg.Element.GradientBase();
                // radial gradient element
                svg.Element.radialGradient = function(node) {
                    this.base = svg.Element.GradientBase;
                    this.base(node);
                    this.getGradient = function(ctx, element) {
                        var bb = element.getBoundingBox();
                        if (!this.attribute("cx").hasValue()) this.attribute("cx", true).value = "50%";
                        if (!this.attribute("cy").hasValue()) this.attribute("cy", true).value = "50%";
                        if (!this.attribute("r").hasValue()) this.attribute("r", true).value = "50%";
                        var cx = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("cx").numValue() : this.attribute("cx").toPixels("x");
                        var cy = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("cy").numValue() : this.attribute("cy").toPixels("y");
                        var fx = cx;
                        var fy = cy;
                        if (this.attribute("fx").hasValue()) {
                            fx = this.gradientUnits == "objectBoundingBox" ? bb.x() + bb.width() * this.attribute("fx").numValue() : this.attribute("fx").toPixels("x");
                        }
                        if (this.attribute("fy").hasValue()) {
                            fy = this.gradientUnits == "objectBoundingBox" ? bb.y() + bb.height() * this.attribute("fy").numValue() : this.attribute("fy").toPixels("y");
                        }
                        var r = this.gradientUnits == "objectBoundingBox" ? (bb.width() + bb.height()) / 2 * this.attribute("r").numValue() : this.attribute("r").toPixels();
                        return ctx.createRadialGradient(fx, fy, 0, cx, cy, r);
                    };
                };
                svg.Element.radialGradient.prototype = new svg.Element.GradientBase();
                // gradient stop element
                svg.Element.stop = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.offset = this.attribute("offset").numValue();
                    if (this.offset < 0) this.offset = 0;
                    if (this.offset > 1) this.offset = 1;
                    var stopColor = this.style("stop-color");
                    if (this.style("stop-opacity").hasValue()) stopColor = stopColor.addOpacity(this.style("stop-opacity").value);
                    this.color = stopColor.value;
                };
                svg.Element.stop.prototype = new svg.Element.ElementBase();
                // animation base element
                svg.Element.AnimateBase = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    svg.Animations.push(this);
                    this.duration = 0;
                    this.begin = this.attribute("begin").toMilliseconds();
                    this.maxDuration = this.begin + this.attribute("dur").toMilliseconds();
                    this.getProperty = function() {
                        var attributeType = this.attribute("attributeType").value;
                        var attributeName = this.attribute("attributeName").value;
                        if (attributeType == "CSS") {
                            return this.parent.style(attributeName, true);
                        }
                        return this.parent.attribute(attributeName, true);
                    };
                    this.initialValue = null;
                    this.initialUnits = "";
                    this.removed = false;
                    this.calcValue = function() {
                        // OVERRIDE ME!
                        return "";
                    };
                    this.update = function(delta) {
                        // set initial value
                        if (this.initialValue == null) {
                            this.initialValue = this.getProperty().value;
                            this.initialUnits = this.getProperty().getUnits();
                        }
                        // if we're past the end time
                        if (this.duration > this.maxDuration) {
                            // loop for indefinitely repeating animations
                            if (this.attribute("repeatCount").value == "indefinite" || this.attribute("repeatDur").value == "indefinite") {
                                this.duration = 0;
                            } else if (this.attribute("fill").valueOrDefault("remove") == "remove" && !this.removed) {
                                this.removed = true;
                                this.getProperty().value = this.initialValue;
                                return true;
                            } else {
                                return false;
                            }
                        }
                        this.duration = this.duration + delta;
                        // if we're past the begin time
                        var updated = false;
                        if (this.begin < this.duration) {
                            var newValue = this.calcValue();
                            // tween
                            if (this.attribute("type").hasValue()) {
                                // for transform, etc.
                                var type = this.attribute("type").value;
                                newValue = type + "(" + newValue + ")";
                            }
                            this.getProperty().value = newValue;
                            updated = true;
                        }
                        return updated;
                    };
                    this.from = this.attribute("from");
                    this.to = this.attribute("to");
                    this.values = this.attribute("values");
                    if (this.values.hasValue()) this.values.value = this.values.value.split(";");
                    // fraction of duration we've covered
                    this.progress = function() {
                        var ret = {
                            progress: (this.duration - this.begin) / (this.maxDuration - this.begin)
                        };
                        if (this.values.hasValue()) {
                            var p = ret.progress * (this.values.value.length - 1);
                            var lb = Math.floor(p), ub = Math.ceil(p);
                            ret.from = new svg.Property("from", parseFloat(this.values.value[lb]));
                            ret.to = new svg.Property("to", parseFloat(this.values.value[ub]));
                            ret.progress = (p - lb) / (ub - lb);
                        } else {
                            ret.from = this.from;
                            ret.to = this.to;
                        }
                        return ret;
                    };
                };
                svg.Element.AnimateBase.prototype = new svg.Element.ElementBase();
                // animate element
                svg.Element.animate = function(node) {
                    this.base = svg.Element.AnimateBase;
                    this.base(node);
                    this.calcValue = function() {
                        var p = this.progress();
                        // tween value linearly
                        var newValue = p.from.numValue() + (p.to.numValue() - p.from.numValue()) * p.progress;
                        return newValue + this.initialUnits;
                    };
                };
                svg.Element.animate.prototype = new svg.Element.AnimateBase();
                // animate color element
                svg.Element.animateColor = function(node) {
                    this.base = svg.Element.AnimateBase;
                    this.base(node);
                    this.calcValue = function() {
                        var p = this.progress();
                        var from = new RGBColor(p.from.value);
                        var to = new RGBColor(p.to.value);
                        if (from.ok && to.ok) {
                            // tween color linearly
                            var r = from.r + (to.r - from.r) * p.progress;
                            var g = from.g + (to.g - from.g) * p.progress;
                            var b = from.b + (to.b - from.b) * p.progress;
                            return "rgb(" + parseInt(r, 10) + "," + parseInt(g, 10) + "," + parseInt(b, 10) + ")";
                        }
                        return this.attribute("from").value;
                    };
                };
                svg.Element.animateColor.prototype = new svg.Element.AnimateBase();
                // animate transform element
                svg.Element.animateTransform = function(node) {
                    this.base = svg.Element.AnimateBase;
                    this.base(node);
                    this.calcValue = function() {
                        var p = this.progress();
                        // tween value linearly
                        var from = svg.ToNumberArray(p.from.value);
                        var to = svg.ToNumberArray(p.to.value);
                        var newValue = "";
                        for (var i = 0; i < from.length; i++) {
                            newValue += from[i] + (to[i] - from[i]) * p.progress + " ";
                        }
                        return newValue;
                    };
                };
                svg.Element.animateTransform.prototype = new svg.Element.animate();
                // font element
                svg.Element.font = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.horizAdvX = this.attribute("horiz-adv-x").numValue();
                    this.isRTL = false;
                    this.isArabic = false;
                    this.fontFace = null;
                    this.missingGlyph = null;
                    this.glyphs = [];
                    for (var i = 0; i < this.children.length; i++) {
                        var child = this.children[i];
                        if (child.type == "font-face") {
                            this.fontFace = child;
                            if (child.style("font-family").hasValue()) {
                                svg.Definitions[child.style("font-family").value] = this;
                            }
                        } else if (child.type == "missing-glyph") this.missingGlyph = child; else if (child.type == "glyph") {
                            if (child.arabicForm != "") {
                                this.isRTL = true;
                                this.isArabic = true;
                                if (typeof this.glyphs[child.unicode] == "undefined") this.glyphs[child.unicode] = [];
                                this.glyphs[child.unicode][child.arabicForm] = child;
                            } else {
                                this.glyphs[child.unicode] = child;
                            }
                        }
                    }
                };
                svg.Element.font.prototype = new svg.Element.ElementBase();
                // font-face element
                svg.Element.fontface = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.ascent = this.attribute("ascent").value;
                    this.descent = this.attribute("descent").value;
                    this.unitsPerEm = this.attribute("units-per-em").numValue();
                };
                svg.Element.fontface.prototype = new svg.Element.ElementBase();
                // missing-glyph element
                svg.Element.missingglyph = function(node) {
                    this.base = svg.Element.path;
                    this.base(node);
                    this.horizAdvX = 0;
                };
                svg.Element.missingglyph.prototype = new svg.Element.path();
                // glyph element
                svg.Element.glyph = function(node) {
                    this.base = svg.Element.path;
                    this.base(node);
                    this.horizAdvX = this.attribute("horiz-adv-x").numValue();
                    this.unicode = this.attribute("unicode").value;
                    this.arabicForm = this.attribute("arabic-form").value;
                };
                svg.Element.glyph.prototype = new svg.Element.path();
                // text element
                svg.Element.text = function(node) {
                    this.captureTextNodes = true;
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    this.baseSetContext = this.setContext;
                    this.setContext = function(ctx) {
                        this.baseSetContext(ctx);
                        if (this.style("dominant-baseline").hasValue()) ctx.textBaseline = this.style("dominant-baseline").value;
                        if (this.style("alignment-baseline").hasValue()) ctx.textBaseline = this.style("alignment-baseline").value;
                    };
                    this.getBoundingBox = function() {
                        // TODO: implement
                        return new svg.BoundingBox(this.attribute("x").toPixels("x"), this.attribute("y").toPixels("y"), 0, 0);
                    };
                    this.renderChildren = function(ctx) {
                        this.x = this.attribute("x").toPixels("x");
                        this.y = this.attribute("y").toPixels("y");
                        this.x += this.getAnchorDelta(ctx, this, 0);
                        for (var i = 0; i < this.children.length; i++) {
                            this.renderChild(ctx, this, i);
                        }
                    };
                    this.getAnchorDelta = function(ctx, parent, startI) {
                        var textAnchor = this.style("text-anchor").valueOrDefault("start");
                        if (textAnchor != "start") {
                            var width = 0;
                            for (var i = startI; i < parent.children.length; i++) {
                                var child = parent.children[i];
                                if (i > startI && child.attribute("x").hasValue()) break;
                                // new group
                                width += child.measureTextRecursive(ctx);
                            }
                            return -1 * (textAnchor == "end" ? width : width / 2);
                        }
                        return 0;
                    };
                    this.renderChild = function(ctx, parent, i) {
                        var child = parent.children[i];
                        if (child.attribute("x").hasValue()) {
                            child.x = child.attribute("x").toPixels("x") + this.getAnchorDelta(ctx, parent, i);
                        } else {
                            if (this.attribute("dx").hasValue()) this.x += this.attribute("dx").toPixels("x");
                            if (child.attribute("dx").hasValue()) this.x += child.attribute("dx").toPixels("x");
                            child.x = this.x;
                        }
                        this.x = child.x + child.measureText(ctx);
                        if (child.attribute("y").hasValue()) {
                            child.y = child.attribute("y").toPixels("y");
                        } else {
                            if (this.attribute("dy").hasValue()) this.y += this.attribute("dy").toPixels("y");
                            if (child.attribute("dy").hasValue()) this.y += child.attribute("dy").toPixels("y");
                            child.y = this.y;
                        }
                        this.y = child.y;
                        child.render(ctx);
                        for (var i = 0; i < child.children.length; i++) {
                            this.renderChild(ctx, child, i);
                        }
                    };
                };
                svg.Element.text.prototype = new svg.Element.RenderedElementBase();
                // text base
                svg.Element.TextElementBase = function(node) {
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    this.getGlyph = function(font, text, i) {
                        var c = text[i];
                        var glyph = null;
                        if (font.isArabic) {
                            var arabicForm = "isolated";
                            if ((i == 0 || text[i - 1] == " ") && i < text.length - 2 && text[i + 1] != " ") arabicForm = "terminal";
                            if (i > 0 && text[i - 1] != " " && i < text.length - 2 && text[i + 1] != " ") arabicForm = "medial";
                            if (i > 0 && text[i - 1] != " " && (i == text.length - 1 || text[i + 1] == " ")) arabicForm = "initial";
                            if (typeof font.glyphs[c] != "undefined") {
                                glyph = font.glyphs[c][arabicForm];
                                if (glyph == null && font.glyphs[c].type == "glyph") glyph = font.glyphs[c];
                            }
                        } else {
                            glyph = font.glyphs[c];
                        }
                        if (glyph == null) glyph = font.missingGlyph;
                        return glyph;
                    };
                    this.renderChildren = function(ctx) {
                        var customFont = this.parent.style("font-family").getDefinition();
                        if (customFont != null) {
                            var fontSize = this.parent.style("font-size").numValueOrDefault(svg.Font.Parse(svg.ctx.font).fontSize);
                            var fontStyle = this.parent.style("font-style").valueOrDefault(svg.Font.Parse(svg.ctx.font).fontStyle);
                            var text = this.getText();
                            if (customFont.isRTL) text = text.split("").reverse().join("");
                            var dx = svg.ToNumberArray(this.parent.attribute("dx").value);
                            for (var i = 0; i < text.length; i++) {
                                var glyph = this.getGlyph(customFont, text, i);
                                var scale = fontSize / customFont.fontFace.unitsPerEm;
                                ctx.translate(this.x, this.y);
                                ctx.scale(scale, -scale);
                                var lw = ctx.lineWidth;
                                ctx.lineWidth = ctx.lineWidth * customFont.fontFace.unitsPerEm / fontSize;
                                if (fontStyle == "italic") ctx.transform(1, 0, .4, 1, 0, 0);
                                glyph.render(ctx);
                                if (fontStyle == "italic") ctx.transform(1, 0, -.4, 1, 0, 0);
                                ctx.lineWidth = lw;
                                ctx.scale(1 / scale, -1 / scale);
                                ctx.translate(-this.x, -this.y);
                                this.x += fontSize * (glyph.horizAdvX || customFont.horizAdvX) / customFont.fontFace.unitsPerEm;
                                if (typeof dx[i] != "undefined" && !isNaN(dx[i])) {
                                    this.x += dx[i];
                                }
                            }
                            return;
                        }
                        if (ctx.fillStyle != "") ctx.fillText(svg.compressSpaces(this.getText()), this.x, this.y);
                        if (ctx.strokeStyle != "") ctx.strokeText(svg.compressSpaces(this.getText()), this.x, this.y);
                    };
                    this.getText = function() {};
                    this.measureTextRecursive = function(ctx) {
                        var width = this.measureText(ctx);
                        for (var i = 0; i < this.children.length; i++) {
                            width += this.children[i].measureTextRecursive(ctx);
                        }
                        return width;
                    };
                    this.measureText = function(ctx) {
                        var customFont = this.parent.style("font-family").getDefinition();
                        if (customFont != null) {
                            var fontSize = this.parent.style("font-size").numValueOrDefault(svg.Font.Parse(svg.ctx.font).fontSize);
                            var measure = 0;
                            var text = this.getText();
                            if (customFont.isRTL) text = text.split("").reverse().join("");
                            var dx = svg.ToNumberArray(this.parent.attribute("dx").value);
                            for (var i = 0; i < text.length; i++) {
                                var glyph = this.getGlyph(customFont, text, i);
                                measure += (glyph.horizAdvX || customFont.horizAdvX) * fontSize / customFont.fontFace.unitsPerEm;
                                if (typeof dx[i] != "undefined" && !isNaN(dx[i])) {
                                    measure += dx[i];
                                }
                            }
                            return measure;
                        }
                        var textToMeasure = svg.compressSpaces(this.getText());
                        if (!ctx.measureText) return textToMeasure.length * 10;
                        ctx.save();
                        this.setContext(ctx);
                        var width = ctx.measureText(textToMeasure).width;
                        ctx.restore();
                        return width;
                    };
                };
                svg.Element.TextElementBase.prototype = new svg.Element.RenderedElementBase();
                // tspan 
                svg.Element.tspan = function(node) {
                    this.captureTextNodes = true;
                    this.base = svg.Element.TextElementBase;
                    this.base(node);
                    this.text = node.nodeValue || node.text || "";
                    this.getText = function() {
                        return this.text;
                    };
                };
                svg.Element.tspan.prototype = new svg.Element.TextElementBase();
                // tref
                svg.Element.tref = function(node) {
                    this.base = svg.Element.TextElementBase;
                    this.base(node);
                    this.getText = function() {
                        var element = this.getHrefAttribute().getDefinition();
                        if (element != null) return element.children[0].getText();
                    };
                };
                svg.Element.tref.prototype = new svg.Element.TextElementBase();
                // a element
                svg.Element.a = function(node) {
                    this.base = svg.Element.TextElementBase;
                    this.base(node);
                    this.hasText = true;
                    for (var i = 0; i < node.childNodes.length; i++) {
                        if (node.childNodes[i].nodeType != 3) this.hasText = false;
                    }
                    // this might contain text
                    this.text = this.hasText ? node.childNodes[0].nodeValue : "";
                    this.getText = function() {
                        return this.text;
                    };
                    this.baseRenderChildren = this.renderChildren;
                    this.renderChildren = function(ctx) {
                        if (this.hasText) {
                            // render as text element
                            this.baseRenderChildren(ctx);
                            var fontSize = new svg.Property("fontSize", svg.Font.Parse(svg.ctx.font).fontSize);
                            svg.Mouse.checkBoundingBox(this, new svg.BoundingBox(this.x, this.y - fontSize.toPixels("y"), this.x + this.measureText(ctx), this.y));
                        } else {
                            // render as temporary group
                            var g = new svg.Element.g();
                            g.children = this.children;
                            g.parent = this;
                            g.render(ctx);
                        }
                    };
                    this.onclick = function() {
                        window.open(this.getHrefAttribute().value);
                    };
                    this.onmousemove = function() {
                        svg.ctx.canvas.style.cursor = "pointer";
                    };
                };
                svg.Element.a.prototype = new svg.Element.TextElementBase();
                // image element
                svg.Element.image = function(node) {
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    var href = this.getHrefAttribute().value;
                    var isSvg = href.match(/\.svg$/);
                    svg.Images.push(this);
                    this.loaded = false;
                    if (!isSvg) {
                        this.img = document.createElement("img");
                        var self = this;
                        this.img.onload = function() {
                            self.loaded = true;
                        };
                        this.img.onerror = function() {
                            if (typeof console != "undefined") {
                                console.log('ERROR: image "' + href + '" not found');
                                self.loaded = true;
                            }
                        };
                        this.img.src = href;
                    } else {
                        this.img = svg.ajax(href);
                        this.loaded = true;
                    }
                    this.renderChildren = function(ctx) {
                        var x = this.attribute("x").toPixels("x");
                        var y = this.attribute("y").toPixels("y");
                        var width = this.attribute("width").toPixels("x");
                        var height = this.attribute("height").toPixels("y");
                        if (width == 0 || height == 0) return;
                        ctx.save();
                        if (isSvg) {
                            ctx.drawSvg(this.img, x, y, width, height);
                        } else {
                            ctx.translate(x, y);
                            svg.AspectRatio(ctx, this.attribute("preserveAspectRatio").value, width, this.img.width, height, this.img.height, 0, 0);
                            ctx.drawImage(this.img, 0, 0);
                        }
                        ctx.restore();
                    };
                    this.getBoundingBox = function() {
                        var x = this.attribute("x").toPixels("x");
                        var y = this.attribute("y").toPixels("y");
                        var width = this.attribute("width").toPixels("x");
                        var height = this.attribute("height").toPixels("y");
                        return new svg.BoundingBox(x, y, x + width, y + height);
                    };
                };
                svg.Element.image.prototype = new svg.Element.RenderedElementBase();
                // group element
                svg.Element.g = function(node) {
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    this.getBoundingBox = function() {
                        var bb = new svg.BoundingBox();
                        for (var i = 0; i < this.children.length; i++) {
                            bb.addBoundingBox(this.children[i].getBoundingBox());
                        }
                        return bb;
                    };
                };
                svg.Element.g.prototype = new svg.Element.RenderedElementBase();
                // symbol element
                svg.Element.symbol = function(node) {
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    this.baseSetContext = this.setContext;
                    this.setContext = function(ctx) {
                        this.baseSetContext(ctx);
                        // viewbox
                        if (this.attribute("viewBox").hasValue()) {
                            var viewBox = svg.ToNumberArray(this.attribute("viewBox").value);
                            var minX = viewBox[0];
                            var minY = viewBox[1];
                            width = viewBox[2];
                            height = viewBox[3];
                            svg.AspectRatio(ctx, this.attribute("preserveAspectRatio").value, this.attribute("width").toPixels("x"), width, this.attribute("height").toPixels("y"), height, minX, minY);
                            svg.ViewPort.SetCurrent(viewBox[2], viewBox[3]);
                        }
                    };
                };
                svg.Element.symbol.prototype = new svg.Element.RenderedElementBase();
                // style element
                svg.Element.style = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    // text, or spaces then CDATA
                    var css = "";
                    for (var i = 0; i < node.childNodes.length; i++) {
                        css += node.childNodes[i].nodeValue;
                    }
                    css = css.replace(/(\/\*([^*]|[\r\n]|(\*+([^*\/]|[\r\n])))*\*+\/)|(^[\s]*\/\/.*)/gm, "");
                    // remove comments
                    css = svg.compressSpaces(css);
                    // replace whitespace
                    var cssDefs = css.split("}");
                    for (var i = 0; i < cssDefs.length; i++) {
                        if (svg.trim(cssDefs[i]) != "") {
                            var cssDef = cssDefs[i].split("{");
                            var cssClasses = cssDef[0].split(",");
                            var cssProps = cssDef[1].split(";");
                            for (var j = 0; j < cssClasses.length; j++) {
                                var cssClass = svg.trim(cssClasses[j]);
                                if (cssClass != "") {
                                    var props = {};
                                    for (var k = 0; k < cssProps.length; k++) {
                                        var prop = cssProps[k].indexOf(":");
                                        var name = cssProps[k].substr(0, prop);
                                        var value = cssProps[k].substr(prop + 1, cssProps[k].length - prop);
                                        if (name != null && value != null) {
                                            props[svg.trim(name)] = new svg.Property(svg.trim(name), svg.trim(value));
                                        }
                                    }
                                    svg.Styles[cssClass] = props;
                                    if (cssClass == "@font-face") {
                                        var fontFamily = props["font-family"].value.replace(/"/g, "");
                                        var srcs = props["src"].value.split(",");
                                        for (var s = 0; s < srcs.length; s++) {
                                            if (srcs[s].indexOf('format("svg")') > 0) {
                                                var urlStart = srcs[s].indexOf("url");
                                                var urlEnd = srcs[s].indexOf(")", urlStart);
                                                var url = srcs[s].substr(urlStart + 5, urlEnd - urlStart - 6);
                                                var doc = svg.parseXml(svg.ajax(url));
                                                var fonts = doc.getElementsByTagName("font");
                                                for (var f = 0; f < fonts.length; f++) {
                                                    var font = svg.CreateElement(fonts[f]);
                                                    svg.Definitions[fontFamily] = font;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                };
                svg.Element.style.prototype = new svg.Element.ElementBase();
                // use element 
                svg.Element.use = function(node) {
                    this.base = svg.Element.RenderedElementBase;
                    this.base(node);
                    this.baseSetContext = this.setContext;
                    this.setContext = function(ctx) {
                        this.baseSetContext(ctx);
                        if (this.attribute("x").hasValue()) ctx.translate(this.attribute("x").toPixels("x"), 0);
                        if (this.attribute("y").hasValue()) ctx.translate(0, this.attribute("y").toPixels("y"));
                    };
                    this.getDefinition = function() {
                        var element = this.getHrefAttribute().getDefinition();
                        if (this.attribute("width").hasValue()) element.attribute("width", true).value = this.attribute("width").value;
                        if (this.attribute("height").hasValue()) element.attribute("height", true).value = this.attribute("height").value;
                        return element;
                    };
                    this.path = function(ctx) {
                        var element = this.getDefinition();
                        if (element != null) element.path(ctx);
                    };
                    this.getBoundingBox = function() {
                        var element = this.getDefinition();
                        if (element != null) return element.getBoundingBox();
                    };
                    this.renderChildren = function(ctx) {
                        var element = this.getDefinition();
                        if (element != null) {
                            // temporarily detach from parent and render
                            var oldParent = element.parent;
                            element.parent = null;
                            element.render(ctx);
                            element.parent = oldParent;
                        }
                    };
                };
                svg.Element.use.prototype = new svg.Element.RenderedElementBase();
                // mask element
                svg.Element.mask = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.apply = function(ctx, element) {
                        // render as temp svg	
                        var x = this.attribute("x").toPixels("x");
                        var y = this.attribute("y").toPixels("y");
                        var width = this.attribute("width").toPixels("x");
                        var height = this.attribute("height").toPixels("y");
                        if (width == 0 && height == 0) {
                            var bb = new svg.BoundingBox();
                            for (var i = 0; i < this.children.length; i++) {
                                bb.addBoundingBox(this.children[i].getBoundingBox());
                            }
                            var x = Math.floor(bb.x1);
                            var y = Math.floor(bb.y1);
                            var width = Math.floor(bb.width());
                            var height = Math.floor(bb.height());
                        }
                        // temporarily remove mask to avoid recursion
                        var mask = element.attribute("mask").value;
                        element.attribute("mask").value = "";
                        var cMask = document.createElement("canvas");
                        cMask.width = x + width;
                        cMask.height = y + height;
                        var maskCtx = cMask.getContext("2d");
                        this.renderChildren(maskCtx);
                        var c = document.createElement("canvas");
                        c.width = x + width;
                        c.height = y + height;
                        var tempCtx = c.getContext("2d");
                        element.render(tempCtx);
                        tempCtx.globalCompositeOperation = "destination-in";
                        tempCtx.fillStyle = maskCtx.createPattern(cMask, "no-repeat");
                        tempCtx.fillRect(0, 0, x + width, y + height);
                        ctx.fillStyle = tempCtx.createPattern(c, "no-repeat");
                        ctx.fillRect(0, 0, x + width, y + height);
                        // reassign mask
                        element.attribute("mask").value = mask;
                    };
                    this.render = function(ctx) {};
                };
                svg.Element.mask.prototype = new svg.Element.ElementBase();
                // clip element
                svg.Element.clipPath = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.apply = function(ctx) {
                        for (var i = 0; i < this.children.length; i++) {
                            var child = this.children[i];
                            if (typeof child.path != "undefined") {
                                var transform = null;
                                if (child.attribute("transform").hasValue()) {
                                    transform = new svg.Transform(child.attribute("transform").value);
                                    transform.apply(ctx);
                                }
                                child.path(ctx);
                                ctx.clip();
                                if (transform) {
                                    transform.unapply(ctx);
                                }
                            }
                        }
                    };
                    this.render = function(ctx) {};
                };
                svg.Element.clipPath.prototype = new svg.Element.ElementBase();
                // filters
                svg.Element.filter = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.apply = function(ctx, element) {
                        // render as temp svg	
                        var bb = element.getBoundingBox();
                        var x = Math.floor(bb.x1);
                        var y = Math.floor(bb.y1);
                        var width = Math.floor(bb.width());
                        var height = Math.floor(bb.height());
                        // temporarily remove filter to avoid recursion
                        var filter = element.style("filter").value;
                        element.style("filter").value = "";
                        var px = 0, py = 0;
                        for (var i = 0; i < this.children.length; i++) {
                            var efd = this.children[i].extraFilterDistance || 0;
                            px = Math.max(px, efd);
                            py = Math.max(py, efd);
                        }
                        var c = document.createElement("canvas");
                        c.width = width + 2 * px;
                        c.height = height + 2 * py;
                        var tempCtx = c.getContext("2d");
                        tempCtx.translate(-x + px, -y + py);
                        element.render(tempCtx);
                        // apply filters
                        for (var i = 0; i < this.children.length; i++) {
                            this.children[i].apply(tempCtx, 0, 0, width + 2 * px, height + 2 * py);
                        }
                        // render on me
                        ctx.drawImage(c, 0, 0, width + 2 * px, height + 2 * py, x - px, y - py, width + 2 * px, height + 2 * py);
                        // reassign filter
                        element.style("filter", true).value = filter;
                    };
                    this.render = function(ctx) {};
                };
                svg.Element.filter.prototype = new svg.Element.ElementBase();
                svg.Element.feMorphology = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.apply = function(ctx, x, y, width, height) {};
                };
                svg.Element.feMorphology.prototype = new svg.Element.ElementBase();
                svg.Element.feColorMatrix = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    function imGet(img, x, y, width, height, rgba) {
                        return img[y * width * 4 + x * 4 + rgba];
                    }
                    function imSet(img, x, y, width, height, rgba, val) {
                        img[y * width * 4 + x * 4 + rgba] = val;
                    }
                    this.apply = function(ctx, x, y, width, height) {
                        // only supporting grayscale for now per Issue 195, need to extend to all matrix
                        // assuming x==0 && y==0 for now
                        var srcData = ctx.getImageData(0, 0, width, height);
                        for (var y = 0; y < height; y++) {
                            for (var x = 0; x < width; x++) {
                                var r = imGet(srcData.data, x, y, width, height, 0);
                                var g = imGet(srcData.data, x, y, width, height, 1);
                                var b = imGet(srcData.data, x, y, width, height, 2);
                                var gray = (r + g + b) / 3;
                                imSet(srcData.data, x, y, width, height, 0, gray);
                                imSet(srcData.data, x, y, width, height, 1, gray);
                                imSet(srcData.data, x, y, width, height, 2, gray);
                            }
                        }
                        ctx.clearRect(0, 0, width, height);
                        ctx.putImageData(srcData, 0, 0);
                    };
                };
                svg.Element.feColorMatrix.prototype = new svg.Element.ElementBase();
                svg.Element.feGaussianBlur = function(node) {
                    this.base = svg.Element.ElementBase;
                    this.base(node);
                    this.blurRadius = Math.floor(this.attribute("stdDeviation").numValue());
                    this.extraFilterDistance = this.blurRadius;
                    this.apply = function(ctx, x, y, width, height) {
                        if (typeof stackBlurCanvasRGBA == "undefined") {
                            if (typeof console != "undefined") {
                                console.log("ERROR: StackBlur.js must be included for blur to work");
                            }
                            return;
                        }
                        // StackBlur requires canvas be on document
                        ctx.canvas.id = svg.UniqueId();
                        ctx.canvas.style.display = "none";
                        document.body.appendChild(ctx.canvas);
                        stackBlurCanvasRGBA(ctx.canvas.id, x, y, width, height, this.blurRadius);
                        document.body.removeChild(ctx.canvas);
                    };
                };
                svg.Element.feGaussianBlur.prototype = new svg.Element.ElementBase();
                // title element, do nothing
                svg.Element.title = function(node) {};
                svg.Element.title.prototype = new svg.Element.ElementBase();
                // desc element, do nothing
                svg.Element.desc = function(node) {};
                svg.Element.desc.prototype = new svg.Element.ElementBase();
                svg.Element.MISSING = function(node) {
                    if (typeof console != "undefined") {
                        console.log("ERROR: Element '" + node.nodeName + "' not yet implemented.");
                    }
                };
                svg.Element.MISSING.prototype = new svg.Element.ElementBase();
                // element factory
                svg.CreateElement = function(node) {
                    var className = node.nodeName.replace(/^[^:]+:/, "");
                    // remove namespace
                    className = className.replace(/\-/g, "");
                    // remove dashes
                    var e = null;
                    if (typeof svg.Element[className] != "undefined") {
                        e = new svg.Element[className](node);
                    } else {
                        e = new svg.Element.MISSING(node);
                    }
                    e.type = node.nodeName;
                    return e;
                };
                // load from url
                svg.load = function(ctx, url) {
                    svg.loadXml(ctx, svg.ajax(url));
                };
                // load from xml
                svg.loadXml = function(ctx, xml) {
                    svg.loadXmlDoc(ctx, svg.parseXml(xml));
                };
                svg.loadXmlDoc = function(ctx, dom) {
                    svg.init(ctx);
                    var mapXY = function(p) {
                        var e = ctx.canvas;
                        while (e) {
                            p.x -= e.offsetLeft;
                            p.y -= e.offsetTop;
                            e = e.offsetParent;
                        }
                        if (window.scrollX) p.x += window.scrollX;
                        if (window.scrollY) p.y += window.scrollY;
                        return p;
                    };
                    // bind mouse
                    if (svg.opts["ignoreMouse"] != true) {
                        ctx.canvas.onclick = function(e) {
                            var p = mapXY(new svg.Point(e != null ? e.clientX : event.clientX, e != null ? e.clientY : event.clientY));
                            svg.Mouse.onclick(p.x, p.y);
                        };
                        ctx.canvas.onmousemove = function(e) {
                            var p = mapXY(new svg.Point(e != null ? e.clientX : event.clientX, e != null ? e.clientY : event.clientY));
                            svg.Mouse.onmousemove(p.x, p.y);
                        };
                    }
                    var e = svg.CreateElement(dom.documentElement);
                    e.root = true;
                    // render loop
                    var isFirstRender = true;
                    var draw = function() {
                        svg.ViewPort.Clear();
                        if (ctx.canvas.parentNode) svg.ViewPort.SetCurrent(ctx.canvas.parentNode.clientWidth, ctx.canvas.parentNode.clientHeight);
                        if (svg.opts["ignoreDimensions"] != true) {
                            // set canvas size
                            if (e.style("width").hasValue()) {
                                ctx.canvas.width = e.style("width").toPixels("x");
                                ctx.canvas.style.width = ctx.canvas.width + "px";
                            }
                            if (e.style("height").hasValue()) {
                                ctx.canvas.height = e.style("height").toPixels("y");
                                ctx.canvas.style.height = ctx.canvas.height + "px";
                            }
                        }
                        var cWidth = ctx.canvas.clientWidth || ctx.canvas.width;
                        var cHeight = ctx.canvas.clientHeight || ctx.canvas.height;
                        if (svg.opts["ignoreDimensions"] == true && e.style("width").hasValue() && e.style("height").hasValue()) {
                            cWidth = e.style("width").toPixels("x");
                            cHeight = e.style("height").toPixels("y");
                        }
                        svg.ViewPort.SetCurrent(cWidth, cHeight);
                        if (svg.opts["offsetX"] != null) e.attribute("x", true).value = svg.opts["offsetX"];
                        if (svg.opts["offsetY"] != null) e.attribute("y", true).value = svg.opts["offsetY"];
                        if (svg.opts["scaleWidth"] != null && svg.opts["scaleHeight"] != null) {
                            var xRatio = 1, yRatio = 1, viewBox = svg.ToNumberArray(e.attribute("viewBox").value);
                            if (e.attribute("width").hasValue()) xRatio = e.attribute("width").toPixels("x") / svg.opts["scaleWidth"]; else if (!isNaN(viewBox[2])) xRatio = viewBox[2] / svg.opts["scaleWidth"];
                            if (e.attribute("height").hasValue()) yRatio = e.attribute("height").toPixels("y") / svg.opts["scaleHeight"]; else if (!isNaN(viewBox[3])) yRatio = viewBox[3] / svg.opts["scaleHeight"];
                            e.attribute("width", true).value = svg.opts["scaleWidth"];
                            e.attribute("height", true).value = svg.opts["scaleHeight"];
                            e.attribute("viewBox", true).value = "0 0 " + cWidth * xRatio + " " + cHeight * yRatio;
                            e.attribute("preserveAspectRatio", true).value = "none";
                        }
                        // clear and render
                        if (svg.opts["ignoreClear"] != true) {
                            ctx.clearRect(0, 0, cWidth, cHeight);
                        }
                        e.render(ctx);
                        if (isFirstRender) {
                            isFirstRender = false;
                            if (typeof svg.opts["renderCallback"] == "function") svg.opts["renderCallback"](dom);
                        }
                    };
                    var waitingForImages = true;
                    if (svg.ImagesLoaded()) {
                        waitingForImages = false;
                        draw();
                    }
                    svg.intervalID = setInterval(function() {
                        var needUpdate = false;
                        if (waitingForImages && svg.ImagesLoaded()) {
                            waitingForImages = false;
                            needUpdate = true;
                        }
                        // need update from mouse events?
                        if (svg.opts["ignoreMouse"] != true) {
                            needUpdate = needUpdate | svg.Mouse.hasEvents();
                        }
                        // need update from animations?
                        if (svg.opts["ignoreAnimation"] != true) {
                            for (var i = 0; i < svg.Animations.length; i++) {
                                needUpdate = needUpdate | svg.Animations[i].update(1e3 / svg.FRAMERATE);
                            }
                        }
                        // need update from redraw?
                        if (typeof svg.opts["forceRedraw"] == "function") {
                            if (svg.opts["forceRedraw"]() == true) needUpdate = true;
                        }
                        // render if needed
                        if (needUpdate) {
                            draw();
                            svg.Mouse.runEvents();
                        }
                    }, 1e3 / svg.FRAMERATE);
                };
                svg.stop = function() {
                    if (svg.intervalID) {
                        clearInterval(svg.intervalID);
                    }
                };
                svg.Mouse = new function() {
                    this.events = [];
                    this.hasEvents = function() {
                        return this.events.length != 0;
                    };
                    this.onclick = function(x, y) {
                        this.events.push({
                            type: "onclick",
                            x: x,
                            y: y,
                            run: function(e) {
                                if (e.onclick) e.onclick();
                            }
                        });
                    };
                    this.onmousemove = function(x, y) {
                        this.events.push({
                            type: "onmousemove",
                            x: x,
                            y: y,
                            run: function(e) {
                                if (e.onmousemove) e.onmousemove();
                            }
                        });
                    };
                    this.eventElements = [];
                    this.checkPath = function(element, ctx) {
                        for (var i = 0; i < this.events.length; i++) {
                            var e = this.events[i];
                            if (ctx.isPointInPath && ctx.isPointInPath(e.x, e.y)) this.eventElements[i] = element;
                        }
                    };
                    this.checkBoundingBox = function(element, bb) {
                        for (var i = 0; i < this.events.length; i++) {
                            var e = this.events[i];
                            if (bb.isPointInBox(e.x, e.y)) this.eventElements[i] = element;
                        }
                    };
                    this.runEvents = function() {
                        svg.ctx.canvas.style.cursor = "";
                        for (var i = 0; i < this.events.length; i++) {
                            var e = this.events[i];
                            var element = this.eventElements[i];
                            while (element) {
                                e.run(element);
                                element = element.parent;
                            }
                        }
                        // done running, clear
                        this.events = [];
                        this.eventElements = [];
                    };
                }();
                return svg;
            }
        })();
        if (typeof CanvasRenderingContext2D != "undefined") {
            CanvasRenderingContext2D.prototype.drawSvg = function(s, dx, dy, dw, dh) {
                canvg(this.canvas, s, {
                    ignoreMouse: true,
                    ignoreAnimation: true,
                    ignoreDimensions: true,
                    ignoreClear: true,
                    offsetX: dx,
                    offsetY: dy,
                    scaleWidth: dw,
                    scaleHeight: dh
                });
            };
        }
        return canvg;
    }
};

/*!
 * 输出转换器，提供输出支持
 */
_p[1] = {
    value: function(require) {
        var kity = _p.r(34), canvg = _p.r(0);
        return kity.createClass("Output", {
            constructor: function(formula) {
                this.formula = formula;
            },
            toJPG: function(cb) {
                toImage(this.formula, "image/jpeg", cb);
            },
            toPNG: function(cb) {
                toImage(this.formula, "image/png", cb);
            }
        });
        function toImage(formula, type, cb) {
            var rectSpace = formula.container.getRenderBox();
            return getBase64DataURL(formula.node.ownerDocument, {
                width: rectSpace.width,
                height: rectSpace.height,
                content: getSVGContent(formula.node)
            }, type, cb);
        }
        function getBase64DataURL(doc, data, type, cb) {
            var canvas = null, args = arguments, ctx = null;
            if (true) {
                drawToCanvas.apply(null, args);
            } else {
                canvas = getImageCanvas(doc, data.width, data.height, type);
                ctx = canvas.getContext("2d");
                var image = new Image();
                image.onload = function() {
                    try {
                        ctx.drawImage(image, 0, 0);
                        cb(canvas.toDataURL(type));
                    } catch (e) {
                        drawToCanvas.apply(null, args);
                    }
                };
                image.src = getSVGDataURL(data.content);
            }
        }
        function getSVGContent(svgNode) {
            var tmp = svgNode.ownerDocument.createElement("div"), start = [ '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="', svgNode.getAttribute("width"), '" height="', svgNode.getAttribute("height"), '">' ];
            tmp.appendChild(svgNode.cloneNode(true));
            return tmp.innerHTML.replace(/<svg[^>]+?>/i, start.join("")).replace(/&nbsp;/g, "");
        }
        function getSVGDataURL(data) {
            return "data:image/svg+xml;base64," + window.btoa(unescape(encodeURIComponent(data)));
        }
        function getImageCanvas(doc, width, height, type) {
            var canvas = doc.createElement("canvas"), ctx = canvas.getContext("2d");
            canvas.width = width;
            canvas.height = height;
            if (type !== "image/png") {
                ctx.fillStyle = "white";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
            return canvas;
        }
        function drawToCanvas(doc, data, type, cb) {
            var canvas = getImageCanvas(doc, data.width, data.height, type);
            canvas.style.cssText = "position: absolute; top: 0; left: 100000px; z-index: -1;";
            window.setTimeout(function() {
                doc.body.appendChild(canvas);
                canvg(canvas, data.content);
                doc.body.removeChild(canvas);
                cb(canvas.toDataURL(type));
            }, 0);
        }
    }
};

/*!
 * 所有字符的列表
 */
_p[2] = {
    value: function() {
        return [ "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "&#x237;", "&#x131;", "&#x3b1;", "&#x3b2;", "&#x3b3;", "&#x3b4;", "&#x3b5;", "&#x3b6;", "&#x3b7;", "&#x3b8;", "&#x3b9;", "&#x3ba;", "&#x3bb;", "&#x3bc;", "&#x3bd;", "&#x3be;", "&#x3bf;", "&#x3c0;", "&#x3c1;", "&#x3c2;", "&#x3c3;", "&#x3c4;", "&#x3c5;", "&#x3c6;", "&#x3c7;", "&#x3c8;", "&#x3c9;", "&#x3d1;", "&#x3d5;", "&#x3d6;", "&#x3de;", "&#x3dc;", "&#x3f5;", "&#x3f1;", "&#x3f9;", "&#x211c;", "&#x2135;", "&#x2111;", "&#x2127;", "&#x2136;", "&#x2137;", "&#x2138;", "&#xf0;", "&#x210f;", "&#x2141;", "&#x210e;", "&#x2202;", "&#x2118;", "&#x214c;", "&#x2132;", "&#x2201;", "&#x2113;", "&#x24c8;", "(", ")", "&#x393;", "&#x394;", "&#x395;", "&#x396;", "&#x397;", "&#x398;", "&#x399;", "&#x39a;", "&#x39b;", "&#x39c;", "&#x39d;", "&#x39e;", "&#x39f;", "&#x3a0;", "&#x3a1;", "&#x3a3;", "&#x3a4;", "&#x3a5;", "&#x3a6;", "&#x3a7;", "&#x3a8;", "&#x3a9;", "&#x391;", "&#x392;", "#", "!", "$", "%", "&#x26;", "&#x2220;", "&#x2032;", "&#x2035;", "&#x2605;", "&#x25c6;", "&#x25a0;", "&#x25b2;", "&#x25bc;", "&#x22a4;", "&#x22a5;", "&#x2663;", "&#x2660;", "&#x2662;", "&#x2661;", "&#x2203;", "&#x2204;", "&#x266d;", "&#x266e;", "&#x266f;", "&#x2200;", "&#x221e;", "&#x2221;", "&#x2207;", "&#xac;", "&#x2222;", "&#x221a;", "&#x25b3;", "&#x25bd;", "&#x2205;", "&#xf8;", "&#x25c7;", "&#x25c0;", "&#x25b8;", "[", "]", "{", "}", "&#x3008;", "&#x3009;", "&#x3f0;", ",", ".", "/", ":", ";", "?", "\\", "&#x22ee;", "&#x22ef;", "&#x22f0;", "&#x2026;", "@", "&#x22;", "'", "|", "^", "`", "&#x201c;", "_", "*", "+", "-", "&#x2210;", "&#x22bc;", "&#x22bb;", "&#x25ef;", "&#x22a1;", "&#x229f;", "&#x229e;", "&#x22a0;", "&#x2022;", "&#x2229;", "&#x222a;", "&#x22d2;", "&#x22d3;", "&#x22d0;", "&#x22d1;", "&#xb7;", "&#x25aa;", "&#x25e6;", "&#x229b;", "&#x229a;", "&#x2296;", "&#x2299;", "&#x229d;", "&#x2295;", "&#x2297;", "&#x2298;", "&#xb1;", "&#x2213;", "&#x22cf;", "&#x22ce;", "&#x2020;", "&#x2021;", "&#x22c4;", "&#xf7;", "&#x22c7;", "&#x2214;", "&#x232d;", "&#x22d7;", "&#x22d6;", "&#x22c9;", "&#x22ca;", "&#x22cb;", "&#x22cc;", "&#x2293;", "&#x2294;", "&#x2291;", "&#x2292;", "&#x228f;", "&#x2290;", "&#x22c6;", "&#xd7;", "&#x22b3;", "&#x22b2;", "&#x22b5;", "&#x22b4;", "&#x228e;", "&#x2228;", "&#x2227;", "&#x2240;", "&#x3c;", "=", "&#x3e;", "&#x2248;", "&#x2247;", "&#x224d;", "&#x2252;", "&#x2253;", "&#x224a;", "&#x223d;", "&#x2241;", "&#x2242;", "&#x2243;", "&#x22cd;", "&#x224f;", "&#x224e;", "&#x2257;", "&#x2245;", "&#x22de;", "&#x22df;", "&#x2250;", "&#x2251;", "&#x2256;", "&#x2a96;", "&#x2a95;", "&#x2261;", "&#x2265;", "&#x2264;", "&#x2266;", "&#x2267;", "&#x2a7e;", "&#x2a7d;", "&#x226b;", "&#x226a;", "&#x2268;", "&#x2269;", "&#x22d8;", "&#x22d9;", "&#x2a87;", "&#x2a88;", "&#x2a89;", "&#x2a8a;", "&#x22e7;", "&#x22e6;", "&#x2a86;", "&#x2a85;", "&#x22db;", "&#x22da;", "&#x2a8b;", "&#x2a8c;", "&#x2277;", "&#x2276;", "&#x2273;", "&#x2272;", "&#x232e;", "&#x232f;", "&#x226f;", "&#x2271;", "&#x2270;", "&#x226e;", "&#x2331;", "&#x2330;", "&#x2332;", "&#x2333;", "&#x226c;", "&#x2280;", "&#x2281;", "&#x22e0;", "&#x22e1;", "&#x227a;", "&#x227b;", "&#x227c;", "&#x227d;", "&#x227e;", "&#x227f;", "&#x2282;", "&#x2283;", "&#x2288;", "&#x2289;", "&#x2286;", "&#x2287;", "&#x228a;", "&#x228b;", "&#x2ab7;", "&#x2ab8;", "&#x2aaf;", "&#x2ab0;", "&#x2ab9;", "&#x2aba;", "&#x2ab5;", "&#x2ab6;", "&#x22e8;", "&#x22e9;", "&#x223c;", "&#x225c;", "&#x21b6;", "&#x21b7;", "&#x21ba;", "&#x21bb;", "&#x21be;", "&#x21bf;", "&#x21c2;", "&#x21c3;", "&#x21c4;", "&#x21c6;", "&#x21c8;", "&#x21ca;", "&#x21cb;", "&#x21cc;", "&#x21cd;", "&#x21ce;", "&#x21cf;", "&#x21d0;", "&#x21d1;", "&#x21d2;", "&#x21d3;", "&#x21d4;", "&#x21d5;", "&#x21da;", "&#x21db;", "&#x21dd;", "&#x21ab;", "&#x21ac;", "&#x21ad;", "&#x21ae;", "&#x2190;", "&#x2191;", "&#x2192;", "&#x2193;", "&#x2194;", "&#x2195;", "&#x2196;", "&#x2197;", "&#x2198;", "&#x2199;", "&#x219e;", "&#x21a0;", "&#x21a2;", "&#x21a3;", "&#x21b0;", "&#x21b1;", "&#x22a2;", "&#x22a3;", "&#x22a8;", "&#x22a9;", "&#x22aa;", "&#x22ad;", "&#x22af;", "&#x22b8;", "&#x22ba;", "&#x22d4;", "&#x22ea;", "&#x22eb;", "&#x22ec;", "&#x22ed;", "&#x2308;", "&#x2309;", "&#x230a;", "&#x230b;", "&#x2acb;", "&#x2acc;", "&#x2ac5;", "&#x2ac6;", "&#x2208;", "&#x220b;", "&#x221d;", "&#x2224;", "&#x2226;", "&#x2234;", "&#x2235;", "&#x220d;", "&#x22c8;", "&#x2322;", "&#x2323;", "&#x2223;", "&#x2225;", "&#x23d0;", "&#x23d1;", "&#x23d2;", "&#x23d3;", "&#x2ac7;", "&#x2ac8;", "&#x22ae;", "&#x22ac;", "&#x2ac9;", "&#x23d4;", "&#x23d5;", "&#x23d6;", "&#x23d7;", "&#x21c7;", "&#x21c9;", "&#x21bc;", "&#x21bd;", "&#x21c0;", "&#x21c1;", "&#x219a;", "&#x219b;", "&#x27f5;", "&#x27f6;", "&#x27f7;", "&#x27f9;", "&#x27f8;", "&#x27fa;", "&#x2262;", "&#x2260;", "&#x2209;" ];
    }
};

/*!
 * 字符配置
 */
_p[3] = {
    value: function() {
        return {
            // 默认字体
            defaultFont: "KF AMS MAIN"
        };
    }
};

/*!
 * 工厂方法，创建兼容各浏览器的text实现
 */
_p[4] = {
    value: function(require) {
        var kity = _p.r(34), divNode = document.createElement("div"), NAMESPACE = "http://www.w3.org/XML/1998/namespace";
        function createText(content) {
            var text = new kity.Text();
            // Non-IE
            if ("innerHTML" in text.node) {
                text.node.setAttributeNS(NAMESPACE, "xml:space", "preserve");
            } else {
                if (content.indexOf(" ") != -1) {
                    content = convertContent(content);
                }
            }
            text.setContent(content);
            return text;
        }
        /**
     * 构建节点来转换内容
     */
        function convertContent(content) {
            divNode.innerHTML = '<svg><text gg="asfdas">' + content.replace(/\s/gi, "&nbsp;") + "</text></svg>";
            return divNode.firstChild.firstChild.textContent;
        }
        return {
            create: function(content) {
                return createText(content);
            }
        };
    }
};

/**
 * 文本
 */
_p[5] = {
    value: function(require) {
        var kity = _p.r(34), FONT_CONF = _p.r(47).font, FontManager = _p.r(25), TextFactory = _p.r(4);
        return kity.createClass("Text", {
            base: _p.r(46),
            constructor: function(content, fontFamily) {
                this.callBase();
                this.fontFamily = fontFamily;
                this.fontSize = 50;
                this.content = content || "";
                // 移除多余的节点
                this.box.remove();
                this.translationContent = this.translation(this.content);
                this.contentShape = new kity.Group();
                this.contentNode = this.createContent();
                this.contentShape.addShape(this.contentNode);
                this.addShape(this.contentShape);
            },
            createContent: function() {
                var contentNode = TextFactory.create(this.translationContent);
                contentNode.setAttr({
                    "font-family": this.fontFamily,
                    "font-size": 50,
                    x: 0,
                    y: FONT_CONF.offset
                });
                return contentNode;
            },
            setFamily: function(fontFamily) {
                this.fontFamily = fontFamily;
                this.contentNode.setAttr("font-family", fontFamily);
            },
            setFontSize: function(fontSize) {
                this.fontSize = fontSize;
                this.contentNode.setAttr("font-size", fontSize + "px");
                this.contentNode.setAttr("y", fontSize / 50 * FONT_CONF.offset);
            },
            getBaseHeight: function() {
                var chars = this.contentShape.getItems(), currentChar = null, index = 0, height = 0;
                while (currentChar = chars[index]) {
                    height = Math.max(height, currentChar.getHeight());
                    index++;
                }
                return height;
            },
            translation: function(content) {
                var fontFamily = this.fontFamily;
                // 首先特殊处理掉两个相连的"`"符号
                return content.replace(/``/g, "“").replace(/\\([a-zA-Z,]+)\\/g, function(match, input) {
                    if (input === ",") {
                        return " ";
                    }
                    var data = FontManager.getCharacterValue(input, fontFamily);
                    if (!data) {
                        return "";
                    }
                    return data;
                });
            }
        });
    }
};

/**
 * 定义公式中各种对象的类型
 */
_p[6] = {
    value: function() {
        return {
            UNKNOWN: -1,
            EXP: 0,
            COMPOUND_EXP: 1,
            OP: 2
        };
    }
};

/**
 * 定义公式中上下标的类型
 */
_p[7] = {
    value: function() {
        return {
            SIDE: "side",
            FOLLOW: "follow"
        };
    }
};

/**
 * 下标表达式
 */
_p[8] = {
    value: function(require) {
        var kity = _p.r(34);
        return kity.createClass("SubscriptExpression", {
            base: _p.r(17),
            constructor: function(operand, subscript) {
                this.callBase(operand, null, subscript);
                this.setFlag("Subscript");
            }
        });
    }
};

/**
 * 上标表达式
 */
_p[9] = {
    value: function(require) {
        var kity = _p.r(34);
        return kity.createClass("SuperscriptExpression", {
            base: _p.r(17),
            constructor: function(operand, superscript) {
                this.callBase(operand, superscript, null);
                this.setFlag("Superscript");
            }
        });
    }
};

/**
 * 二元操作表达式
 */
_p[10] = {
    value: function(require) {
        var kity = _p.r(34);
        return kity.createClass("BinaryExpression", {
            base: _p.r(19),
            constructor: function(firstOperand, lastOperand) {
                this.callBase();
                this.setFirstOperand(firstOperand);
                this.setLastOperand(lastOperand);
            },
            setFirstOperand: function(operand) {
                return this.setOperand(operand, 0);
            },
            getFirstOperand: function() {
                return this.getOperand(0);
            },
            setLastOperand: function(operand) {
                return this.setOperand(operand, 1);
            },
            getLastOperand: function() {
                return this.getOperand(1);
            }
        });
    }
};

/**
 * 自动增长括号表达式
 */
_p[11] = {
    value: function(require) {
        var kity = _p.r(34), BracketsOperator = _p.r(35);
        return kity.createClass("BracketsExpression", {
            base: _p.r(19),
            /**
         * 构造函数调用方式：
         *  new Constructor( 左括号, 右括号, 表达式 )
         *  或者
         *  new Constructor( 括号, 表达式 ), 该构造函数转换成上面的构造函数，是： new Constructor( 括号, 括号, 表达式 )
         * @param left 左括号
         * @param right 右括号
         * @param exp 表达式
         */
            constructor: function(left, right, exp) {
                this.callBase();
                this.setFlag("Brackets");
                // 参数整理
                if (arguments.length === 2) {
                    exp = right;
                    right = left;
                }
                this.leftSymbol = left;
                this.rightSymbol = right;
                this.setOperator(new BracketsOperator());
                this.setOperand(exp, 0);
            },
            getLeftSymbol: function() {
                return this.leftSymbol;
            },
            getRightSymbol: function() {
                return this.rightSymbol;
            }
        });
    }
};

/**
 * 组合表达式
 * 可以组合多个表达式
 */
_p[12] = {
    value: function(require) {
        var kity = _p.r(34), FONT_CONF = _p.r(47).font, CombinationOperator = _p.r(36);
        return kity.createClass("CombinationExpression", {
            base: _p.r(19),
            constructor: function() {
                this.callBase();
                this.setFlag("Combination");
                this.setOperator(new CombinationOperator());
                kity.Utils.each(arguments, function(operand, index) {
                    this.setOperand(operand, index);
                }, this);
            },
            getRenderBox: function(refer) {
                var rectBox = this.callBase(refer);
                if (this.getOperands().length === 0) {
                    rectBox.height = FONT_CONF.spaceHeight;
                }
                return rectBox;
            },
            getBaseline: function(refer) {
                var maxBaseline = 0, operands = this.getOperands();
                if (operands.length === 0) {
                    return this.callBase(refer);
                }
                kity.Utils.each(operands, function(operand) {
                    maxBaseline = Math.max(operand.getBaseline(refer), maxBaseline);
                });
                return maxBaseline;
            },
            getMeanline: function(refer) {
                var minMeanline = 1e7, operands = this.getOperands();
                if (operands.length === 0) {
                    return this.callBase(refer);
                }
                kity.Utils.each(operands, function(operand) {
                    minMeanline = Math.min(operand.getMeanline(refer), minMeanline);
                });
                return minMeanline;
            }
        });
    }
};

/**
 * 分数表达式
 */
_p[13] = {
    value: function(require) {
        var kity = _p.r(34), FractionOperator = _p.r(38);
        return kity.createClass("FractionExpression", {
            base: _p.r(10),
            constructor: function(upOperand, downOperand) {
                this.callBase(upOperand, downOperand);
                this.setFlag("Fraction");
                this.setOperator(new FractionOperator());
            },
            /*------- 重写分数结构的baseline和mealine计算方式 */
            getBaseline: function(refer) {
                var downOperand = this.getOperand(1), rectBox = downOperand.getRenderBox(refer);
                return rectBox.y + downOperand.getBaselineProportion() * rectBox.height;
            },
            getMeanline: function(refer) {
                var upOperand = this.getOperand(0), rectBox = upOperand.getRenderBox(refer);
                return upOperand.getMeanlineProportion() * rectBox.height;
            }
        });
    }
};

/**
 * 函数表达式
 */
_p[14] = {
    value: function(require) {
        var kity = _p.r(34), FUNC_CONF = _p.r(47).func, FunctionOperator = _p.r(39);
        return kity.createClass("FunctionExpression", {
            base: _p.r(19),
            /**
         * function表达式构造函数
         * @param funcName function名称
         * @param expr 函数表达式
         * @param sup 上标
         * @param sub 下标
         */
            constructor: function(funcName, expr, sup, sub) {
                this.callBase();
                this.setFlag("Func");
                this.funcName = funcName;
                this.setOperator(new FunctionOperator(funcName));
                this.setExpr(expr);
                this.setSuperscript(sup);
                this.setSubscript(sub);
            },
            // 当前函数应用的script位置是否是在侧面
            isSideScript: function() {
                return !FUNC_CONF["ud-script"][this.funcName];
            },
            setExpr: function(expr) {
                return this.setOperand(expr, 0);
            },
            setSuperscript: function(sub) {
                return this.setOperand(sub, 1);
            },
            setSubscript: function(sub) {
                return this.setOperand(sub, 2);
            }
        });
    }
};

/**
 * 积分表达式
 */
_p[15] = {
    value: function(require) {
        var kity = _p.r(34), IntegrationOperator = _p.r(40), IntegrationExpression = kity.createClass("IntegrationExpression", {
            base: _p.r(19),
            /**
             * 构造积分表达式
             * @param integrand 被积函数
             * @param supOperand 上限
             * @param subOperand 下限
             */
            constructor: function(integrand, superscript, subscript) {
                this.callBase();
                this.setFlag("Integration");
                this.setOperator(new IntegrationOperator());
                this.setIntegrand(integrand);
                this.setSuperscript(superscript);
                this.setSubscript(subscript);
            },
            setType: function(type) {
                this.getOperator().setType(type);
                return this;
            },
            resetType: function() {
                this.getOperator().resetType();
                return this;
            },
            setIntegrand: function(integrand) {
                this.setOperand(integrand, 0);
            },
            setSuperscript: function(sup) {
                this.setOperand(sup, 1);
            },
            setSubscript: function(sub) {
                this.setOperand(sub, 2);
            }
        });
        return IntegrationExpression;
    }
};

/**
 * 方根表达式
 */
_p[16] = {
    value: function(require) {
        var kity = _p.r(34), RadicalOperator = _p.r(42);
        return kity.createClass("RadicalExpression", {
            base: _p.r(10),
            /**
         * 构造开方表达式
         * @param radicand 被开方数
         * @param exponent 指数
         */
            constructor: function(radicand, exponent) {
                this.callBase(radicand, exponent);
                this.setFlag("Radicand");
                this.setOperator(new RadicalOperator());
            },
            setRadicand: function(operand) {
                return this.setFirstOperand(operand);
            },
            getRadicand: function() {
                return this.getFirstOperand();
            },
            setExponent: function(operand) {
                return this.setLastOperand(operand);
            },
            getExponent: function() {
                return this.getLastOperand();
            }
        });
    }
};

/**
 * 上标表达式
 */
_p[17] = {
    value: function(require) {
        var kity = _p.r(34), ScriptOperator = _p.r(43);
        return kity.createClass("ScriptExpression", {
            base: _p.r(19),
            constructor: function(operand, superscript, subscript) {
                this.callBase();
                this.setFlag("Script");
                this.setOperator(new ScriptOperator());
                this.setOpd(operand);
                this.setSuperscript(superscript);
                this.setSubscript(subscript);
            },
            setOpd: function(operand) {
                this.setOperand(operand, 0);
            },
            setSuperscript: function(sup) {
                this.setOperand(sup, 1);
            },
            setSubscript: function(sub) {
                this.setOperand(sub, 2);
            }
        });
    }
};

/**
 * 求和表达式
 */
_p[18] = {
    value: function(require) {
        var kity = _p.r(34), SummationOperator = _p.r(44);
        return kity.createClass("SummationExpression", {
            base: _p.r(19),
            /**
         * 构造求和表达式
         * @param expr 求和表达式
         * @param upOperand 上标
         * @param downOperand 下标
         */
            constructor: function(expr, superscript, subscript) {
                this.callBase();
                this.setFlag("Summation");
                this.setOperator(new SummationOperator());
                this.setExpr(expr);
                this.setSuperscript(superscript);
                this.setSubscript(subscript);
            },
            setExpr: function(expr) {
                this.setOperand(expr, 0);
            },
            setSuperscript: function(sup) {
                this.setOperand(sup, 1);
            },
            setSubscript: function(sub) {
                this.setOperand(sub, 2);
            }
        });
    }
};

/**
 * 复合表达式
 * @abstract
 */
_p[19] = {
    value: function(require) {
        var kity = _p.r(34), GTYPE = _p.r(6), Expression = _p.r(21);
        return kity.createClass("CompoundExpression", {
            base: _p.r(21),
            constructor: function() {
                this.callBase();
                this.type = GTYPE.COMPOUND_EXP;
                this.operands = [];
                this.operator = null;
                this.operatorBox = new kity.Group();
                this.operatorBox.setAttr("data-type", "kf-editor-exp-op-box");
                this.operandBox = new kity.Group();
                this.operandBox.setAttr("data-type", "kf-editor-exp-operand-box");
                this.setChildren(0, this.operatorBox);
                this.setChildren(1, this.operandBox);
            },
            // 操作符存储在第1位置
            setOperator: function(operator) {
                if (operator === undefined) {
                    return this;
                }
                if (this.operator) {
                    this.operator.remove();
                }
                this.operatorBox.addShape(operator);
                this.operator = operator;
                this.operator.setParentExpression(this);
                // 表达式关联到操作符
                operator.expression = this;
                return this;
            },
            getOperator: function() {
                return this.operator;
            },
            // 操作数存储位置是从1开始
            setOperand: function(operand, index, isWrap) {
                // 不包装操作数
                if (isWrap === false) {
                    this.operands[index] = operand;
                    return this;
                }
                operand = Expression.wrap(operand);
                if (this.operands[index]) {
                    this.operands[index].remove();
                }
                this.operands[index] = operand;
                this.operandBox.addShape(operand);
                return this;
            },
            getOperand: function(index) {
                return this.operands[index];
            },
            getOperands: function() {
                return this.operands;
            },
            addedCall: function() {
                this.operator.applyOperand.apply(this.operator, this.operands);
                return this;
            }
        });
    }
};

/**
 * 空表达式
 * 该表达式主要用途是用于站位
 */
_p[20] = {
    value: function(require) {
        var kity = _p.r(34), FONT_CONF = _p.r(47).font, Expression = _p.r(21), EmptyExpression = kity.createClass("EmptyExpression", {
            base: Expression,
            constructor: function() {
                this.callBase();
                this.setFlag("Empty");
            },
            getRenderBox: function() {
                return {
                    width: 0,
                    height: FONT_CONF.spaceHeight,
                    x: 0,
                    y: 0
                };
            }
        });
        EmptyExpression.isEmpty = function(target) {
            return target instanceof EmptyExpression;
        };
        // 注册打包函数
        Expression.registerWrap("empty", function(operand) {
            if (operand === null || operand === undefined) {
                return new EmptyExpression();
            }
        });
        return EmptyExpression;
    }
};

/**
 * 基础表达式， 该类是表达式和操作数的高层抽象
 * @abstract
 */
_p[21] = {
    value: function(require) {
        var kity = _p.r(34), GTYPE = _p.r(6), FONT_CONF = _p.r(47).font, // 打包函数列表
        WRAP_FN = [], // 注册的打包函数的名称与其在注册器列表中的索引之间的对应关系
        WRAP_FN_INDEX = {}, Expression = kity.createClass("Expression", {
            base: _p.r(46),
            constructor: function() {
                this.callBase();
                this.type = GTYPE.EXP;
                // 表达式的上下偏移
                this._offset = {
                    top: 0,
                    bottom: 0
                };
                this.children = [];
                this.box.fill("transparent").setAttr("data-type", "kf-editor-exp-box");
                this.box.setAttr("data-type", "kf-editor-exp-bg-box");
                this.expContent = new kity.Group();
                this.expContent.setAttr("data-type", "kf-editor-exp-content-box");
                this.addShape(this.expContent);
            },
            getChildren: function() {
                return this.children;
            },
            getChild: function(index) {
                return this.children[index] || null;
            },
            getTopOffset: function() {
                return this._offset.top;
            },
            getBottomOffset: function() {
                return this._offset.bottom;
            },
            getOffset: function() {
                return this._offset;
            },
            setTopOffset: function(val) {
                this._offset.top = val;
            },
            setBottomOffset: function(val) {
                this._offset.bottom = val;
            },
            setOffset: function(top, bottom) {
                this._offset.top = top;
                this._offset.bottom = bottom;
            },
            setFlag: function(flag) {
                this.setAttr("data-flag", flag || "Expression");
            },
            setChildren: function(index, exp) {
                // 首先清理掉之前的表达式
                if (this.children[index]) {
                    this.children[index].remove();
                }
                this.children[index] = exp;
                this.expContent.addShape(exp);
            },
            getBaselineProportion: function() {
                return FONT_CONF.baselinePosition;
            },
            getMeanlineProportion: function() {
                return FONT_CONF.meanlinePosition;
            },
            getBaseline: function(refer) {
                // 上偏移3px
                return this.getRenderBox(refer).height * FONT_CONF.baselinePosition - 3;
            },
            getMeanline: function(refer) {
                // 上偏移1px
                return this.getRenderBox(refer).height * FONT_CONF.meanlinePosition - 1;
            },
            getAscenderline: function() {
                return this.getFixRenderBox().height * FONT_CONF.ascenderPosition;
            },
            getDescenderline: function() {
                return this.getFixRenderBox().height * FONT_CONF.descenderPosition;
            },
            translateElement: function(x, y) {
                this.expContent.translate(x, y);
            },
            expand: function(width, height) {
                var renderBox = this.getFixRenderBox();
                this.setBoxSize(renderBox.width + width, renderBox.height + height);
            },
            getBaseWidth: function() {
                return this.getWidth();
            },
            getBaseHeight: function() {
                return this.getHeight();
            },
            updateBoxSize: function() {
                var renderBox = this.expContent.getFixRenderBox();
                this.setBoxSize(renderBox.width, renderBox.height);
            },
            getBox: function() {
                return this.box;
            }
        });
        // 表达式自动打包
        kity.Utils.extend(Expression, {
            registerWrap: function(name, fn) {
                WRAP_FN_INDEX[name] = WRAP_FN.length;
                WRAP_FN.push(fn);
            },
            revokeWrap: function(name) {
                var fn = null;
                if (name in WRAP_FN_INDEX) {
                    fn = WRAP_FN[WRAP_FN_INDEX[name]];
                    WRAP_FN[WRAP_FN_INDEX[name]] = null;
                    delete WRAP_FN_INDEX[name];
                }
                return fn;
            },
            // 打包函数
            wrap: function(operand) {
                var result;
                kity.Utils.each(WRAP_FN, function(fn) {
                    if (!fn) {
                        return;
                    }
                    result = fn(operand);
                    if (result) {
                        return false;
                    }
                });
                return result;
            }
        });
        return Expression;
    }
};

/**
 * Text表达式
 */
_p[22] = {
    value: function(require) {
        var Text = _p.r(5), kity = _p.r(34), FONT_CONF = _p.r(3), Expression = _p.r(21), TextExpression = kity.createClass("TextExpression", {
            base: _p.r(21),
            constructor: function(content, fontFamily) {
                this.callBase();
                this.fontFamily = fontFamily || FONT_CONF.defaultFont;
                this.setFlag("Text");
                this.content = content + "";
                this.textContent = new Text(this.content, this.fontFamily);
                this.setChildren(0, this.textContent);
                this.setChildren(1, new kity.Rect(0, 0, 0, 0).fill("transparent"));
            },
            setFamily: function(fontFamily) {
                this.textContent.setFamily(fontFamily);
            },
            setFontSize: function(fontSize) {
                this.textContent.setFontSize(fontSize);
            },
            addedCall: function() {
                var box = this.textContent.getFixRenderBox();
                this.getChild(1).setSize(box.width, box.height);
                this.updateBoxSize();
                return this;
            }
        });
        // 注册文本表达式的打包函数
        Expression.registerWrap("text", function(operand) {
            var operandType = typeof operand;
            if (operandType === "number" || operandType === "string") {
                operand = new TextExpression(operand);
            }
            return operand;
        });
        return TextExpression;
    }
};

/*!
 * 字体信息检测模板，用于检测浏览器的字体信息
 */
_p[23] = {
    value: function() {
        return [ '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">', '<text id="abcd" font-family="KF AMS MAIN" font-size="50" x="0" y="0">x</text>', "</svg>" ];
    }
};

/*!
 * 字体安装器
 */
_p[24] = {
    value: function(require) {
        var kity = _p.r(34), FontManager = _p.r(25), $ = _p.r(33), FONT_CONF = _p.r(47).font, CHAR_LIST = _p.r(2), NODE_LIST = [];
        return kity.createClass("FontInstaller", {
            constructor: function(doc, resource) {
                this.callBase();
                this.resource = resource || "../src/resource/";
                this.doc = doc;
            },
            // 挂载字体
            mount: function(callback) {
                var fontList = FontManager.getFontList(), count = 0, _self = this;
                kity.Utils.each(fontList, function(fontInfo) {
                    count++;
                    fontInfo.meta.src = _self.resource + fontInfo.meta.src;
                    _self.createFontStyle(fontInfo);
                    preload(_self.doc, fontInfo, function() {
                        count--;
                        if (count === 0) {
                            complete(_self.doc, callback);
                        }
                    });
                });
            },
            createFontStyle: function(fontInfo) {
                var stylesheet = this.doc.createElement("style"), tpl = '@font-face{\nfont-family: "${fontFamily}";\nsrc: url("${src}");\n}';
                stylesheet.setAttribute("type", "text/css");
                stylesheet.innerHTML = tpl.replace("${fontFamily}", fontInfo.meta.fontFamily).replace("${src}", fontInfo.meta.src);
                this.doc.head.appendChild(stylesheet);
            }
        });
        function preload(doc, fontInfo, callback) {
            $.get(fontInfo.meta.src, function(data, state) {
                if (state === "success") {
                    applyFonts(doc, fontInfo);
                }
                callback();
            });
        }
        function complete(doc, callback) {
            window.setTimeout(function() {
                initFontSystemInfo(doc);
                removeTmpNode();
                callback();
            }, 100);
        }
        function applyFonts(doc, fontInfo) {
            var node = document.createElement("div"), fontFamily = fontInfo.meta.fontFamily;
            node.style.cssText = "position: absolute; top: -10000px; left: -100000px;";
            node.style.fontFamily = fontFamily;
            node.innerHTML = CHAR_LIST.join("");
            doc.body.appendChild(node);
            NODE_LIST.push(node);
        }
        /**
     * 计算字体系统信息
     */
        function initFontSystemInfo(doc) {
            var tmpNode = doc.createElement("div");
            tmpNode.style.cssText = "position: absolute; top: 0; left: -100000px;";
            tmpNode.innerHTML = _p.r(23).join("");
            doc.body.appendChild(tmpNode);
            var rectBox = tmpNode.getElementsByTagName("text")[0].getBBox();
            // text实际占用空间
            FONT_CONF.spaceHeight = rectBox.height;
            // text顶部空间
            FONT_CONF.topSpace = -rectBox.y - FONT_CONF.baseline;
            FONT_CONF.bottomSpace = FONT_CONF.spaceHeight - FONT_CONF.topSpace - FONT_CONF.baseHeight;
            // text偏移值
            FONT_CONF.offset = FONT_CONF.baseline + FONT_CONF.topSpace;
            // baseline比例
            FONT_CONF.baselinePosition = (FONT_CONF.topSpace + FONT_CONF.baseline) / FONT_CONF.spaceHeight;
            // meanline比例
            FONT_CONF.meanlinePosition = (FONT_CONF.topSpace + FONT_CONF.meanline) / FONT_CONF.spaceHeight;
            // 上下延伸性比例
            FONT_CONF.ascenderPosition = FONT_CONF.topSpace / FONT_CONF.spaceHeight;
            FONT_CONF.descenderPosition = (FONT_CONF.topSpace + FONT_CONF.baseHeight) / FONT_CONF.spaceHeight;
            doc.body.removeChild(tmpNode);
        }
        function removeTmpNode() {
            kity.Utils.each(NODE_LIST, function(node) {
                node.parentNode.removeChild(node);
            });
            NODE_LIST = [];
        }
    }
};

/*!
 * 字体管理器
 */
_p[25] = {
    value: function(require) {
        var FONT_LIST = {}, kity = _p.r(34), CONF = _p.r(47).font.list;
        // init
        (function() {
            kity.Utils.each(CONF, function(fontData) {
                FONT_LIST[fontData.meta.fontFamily] = fontData;
            });
        })();
        return {
            getFontList: function() {
                return FONT_LIST;
            },
            getCharacterValue: function(key, fontFamily) {
                if (!FONT_LIST[fontFamily]) {
                    return null;
                }
                return FONT_LIST[fontFamily].map[key] || null;
            }
        };
    }
};

/*!
 * 双线字体
 */
_p[26] = {
    value: function() {
        return {
            meta: {
                fontFamily: "KF AMS BB",
                src: "KF_AMS_BB.woff"
            }
        };
    }
};

/*!
 * 手写体
 */
_p[27] = {
    value: function() {
        return {
            meta: {
                fontFamily: "KF AMS CAL",
                src: "KF_AMS_CAL.woff"
            }
        };
    }
};

/*!
 * 花体
 */
_p[28] = {
    value: function() {
        return {
            meta: {
                fontFamily: "KF AMS FRAK",
                src: "KF_AMS_FRAK.woff"
            }
        };
    }
};

/*!
 * 字体主文件
 */
_p[29] = {
    value: function() {
        return {
            meta: {
                fontFamily: "KF AMS MAIN",
                src: "KF_AMS_MAIN.woff"
            },
            map: {
                // char
                Alpha: "Α",
                Beta: "Β",
                Gamma: "Γ",
                Delta: "Δ",
                Epsilon: "Ε",
                Zeta: "Ζ",
                Eta: "Η",
                Theta: "Θ",
                Iota: "Ι",
                Kappa: "Κ",
                Lambda: "Λ",
                Mu: "Μ",
                Nu: "Ν",
                Xi: "Ξ",
                Omicron: "Ο",
                Pi: "Π",
                Rho: "Ρ",
                Sigma: "Σ",
                Tau: "Τ",
                Upsilon: "Υ",
                Phi: "Φ",
                Chi: "Χ",
                Psi: "Ψ",
                Omega: "Ω",
                alpha: "α",
                beta: "β",
                gamma: "γ",
                delta: "δ",
                epsilon: "ε",
                zeta: "ζ",
                eta: "η",
                theta: "θ",
                iota: "ι",
                kappa: "κ",
                lambda: "λ",
                mu: "μ",
                nu: "ν",
                xi: "ξ",
                omicron: "ο",
                pi: "π",
                rho: "ρ",
                sigma: "σ",
                tau: "τ",
                upsilon: "υ",
                phi: "φ",
                varkappa: "ϰ",
                chi: "χ",
                psi: "ψ",
                omega: "ω",
                digamma: "Ϝ",
                varepsilon: "ϵ",
                varrho: "ϱ",
                varphi: "ϕ",
                vartheta: "ϑ",
                varpi: "ϖ",
                varsigma: "Ϲ",
                aleph: "ℵ",
                beth: "ℶ",
                daleth: "ℸ",
                gimel: "ℷ",
                eth: "ð",
                hbar: "ℎ",
                hslash: "ℏ",
                mho: "℧",
                partial: "∂",
                wp: "℘",
                Game: "⅁",
                Bbbk: "⅌",
                Finv: "Ⅎ",
                Im: "ℑ",
                Re: "ℜ",
                complement: "∁",
                ell: "ℓ",
                circledS: "Ⓢ",
                imath: "ı",
                jmath: "ȷ",
                // symbol
                doublecap: "⋒",
                Cap: "⋒",
                doublecup: "⋓",
                Cup: "⋓",
                ast: "*",
                divideontimes: "⋇",
                rightthreetimes: "⋌",
                leftthreetimes: "⋋",
                cdot: "·",
                odot: "⊙",
                dotplus: "∔",
                rtimes: "⋊",
                ltimes: "⋉",
                centerdot: "▪",
                doublebarwedge: "⌭",
                setminus: "⒁",
                amalg: "∐",
                circ: "◦",
                bigcirc: "◯",
                gtrdot: "⋗",
                lessdot: "⋖",
                smallsetminus: "⒅",
                circledast: "⊛",
                circledcirc: "⊚",
                sqcap: "⊓",
                sqcup: "⊔",
                barwedge: "⊼",
                circleddash: "⊝",
                star: "⋆",
                bigtriangledown: "▽",
                bigtriangleup: "△",
                cup: "∪",
                cap: "∩",
                times: "×",
                mp: "∓",
                pm: "±",
                triangleleft: "⊲",
                triangleright: "⊳",
                boxdot: "⊡",
                curlyvee: "⋏",
                curlywedge: "⋎",
                boxminus: "⊟",
                boxtimes: "⊠",
                ominus: "⊖",
                oplus: "⊕",
                oslash: "⊘",
                otimes: "⊗",
                uplus: "⊎",
                boxplus: "⊞",
                dagger: "†",
                ddagger: "‡",
                vee: "∨",
                lor: "∨",
                veebar: "⊻",
                bullet: "•",
                diamond: "⋄",
                wedge: "∧",
                land: "∧",
                div: "÷",
                wr: "≀",
                geqq: "≧",
                lll: "⋘",
                llless: "⋘",
                ggg: "⋙",
                gggtr: "⋙",
                preccurlyeq: "≼",
                geqslant: "⩾",
                lnapprox: "⪉",
                preceq: "⪯",
                gg: "≫",
                lneq: "⪇",
                precnapprox: "⪹",
                approx: "≈",
                lneqq: "≨",
                precneqq: "⪵",
                approxeq: "≊",
                gnapprox: "⪊",
                lnsim: "⋦",
                precnsim: "⋨",
                asymp: "≍",
                gneq: "⪈",
                lvertneqq: "⌮",
                precsim: "≾",
                backsim: "∽",
                gneqq: "≩",
                ncong: "≇",
                risingdotseq: "≓",
                backsimeq: "⋍",
                gnsim: "⋧",
                sim: "∼",
                simeq: "≃",
                bumpeq: "≏",
                gtrapprox: "⪆",
                ngeq: "≱",
                Bumpeq: "≎",
                gtreqless: "⋛",
                ngeqq: "⌱",
                succ: "≻",
                circeq: "≗",
                gtreqqless: "⪌",
                ngeqslant: "⌳",
                succapprox: "⪸",
                cong: "≅",
                gtrless: "≷",
                ngtr: "≯",
                succcurlyeq: "≽",
                curlyeqprec: "⋞",
                gtrsim: "≳",
                nleq: "≰",
                succeq: "⪰",
                curlyeqsucc: "⋟",
                gvertneqq: "⌯",
                neq: "≠",
                ne: "≠",
                nequiv: "≢",
                nleqq: "⌰",
                succnapprox: "⪺",
                doteq: "≐",
                leq: "≤",
                le: "≤",
                nleqslant: "⌲",
                succneqq: "⪶",
                doteqdot: "≑",
                Doteq: "≑",
                leqq: "≦",
                nless: "≮",
                succnsim: "⋩",
                leqslant: "⩽",
                nprec: "⊀",
                succsim: "≿",
                eqsim: "≂",
                lessapprox: "⪅",
                npreceq: "⋠",
                eqslantgtr: "⪖",
                lesseqgtr: "⋚",
                nsim: "≁",
                eqslantless: "⪕",
                lesseqqgtr: "⪋",
                nsucc: "⊁",
                triangleq: "≜",
                eqcirc: "≖",
                equiv: "≡",
                lessgtr: "≶",
                nsucceq: "⋡",
                fallingdotseq: "≒",
                lesssim: "≲",
                prec: "≺",
                geq: "≥",
                ge: "≥",
                ll: "≪",
                precapprox: "⪷",
                // arrows
                uparrow: "↑",
                downarrow: "↓",
                updownarrow: "↕",
                Uparrow: "⇑",
                Downarrow: "⇓",
                Updownarrow: "⇕",
                circlearrowleft: "↺",
                circlearrowright: "↻",
                curvearrowleft: "↶",
                curvearrowright: "↷",
                downdownarrows: "⇊",
                downharpoonleft: "⇃",
                downharpoonright: "⇂",
                leftarrow: "←",
                gets: "←",
                Leftarrow: "⇐",
                leftarrowtail: "↢",
                leftharpoondown: "↽",
                leftharpoonup: "↼",
                leftleftarrows: "⇇",
                leftrightarrow: "↔",
                Leftrightarrow: "⇔",
                leftrightarrows: "⇄",
                leftrightharpoons: "⇋",
                leftrightsquigarrow: "↭",
                Lleftarrow: "⇚",
                looparrowleft: "↫",
                looparrowright: "↬",
                multimap: "⊸",
                nLeftarrow: "⇍",
                nRightarrow: "⇏",
                nLeftrightarrow: "⇎",
                nearrow: "↗",
                nleftarrow: "↚",
                nleftrightarrow: "↮",
                nrightarrow: "↛",
                nwarrow: "↖",
                rightarrow: "→",
                to: "→",
                Rightarrow: "⇒",
                rightarrowtail: "↣",
                rightharpoondown: "⇁",
                rightharpoonup: "⇀",
                rightleftarrows: "⇆",
                rightleftharpoons: "⇌",
                rightrightarrows: "⇉",
                rightsquigarrow: "⇝",
                Rrightarrow: "⇛",
                searrow: "↘",
                swarrow: "↙",
                twoheadleftarrow: "↞",
                twoheadrightarrow: "↠",
                upharpoonleft: "↿",
                upharpoonright: "↾",
                restriction: "↾",
                upuparrows: "⇈",
                Lsh: "↰",
                Rsh: "↱",
                longleftarrow: "⟵",
                longrightarrow: "⟶",
                Longleftarrow: "⟸",
                Longrightarrow: "⟹",
                implies: "⟹",
                longleftrightarrow: "⟷",
                Longleftrightarrow: "⟺",
                // relation
                backepsilon: "∍",
                because: "∵",
                therefore: "∴",
                between: "≬",
                blacktriangleleft: "◀",
                blacktriangleright: "▸",
                dashv: "⊣",
                bowtie: "⋈",
                frown: "⌢",
                "in": "∈",
                notin: "∉",
                mid: "∣",
                parallel: "∥",
                models: "⊨",
                ni: "∋",
                owns: "∋",
                nmid: "∤",
                nparallel: "∦",
                nshortmid: "⏒",
                nshortparallel: "⏓",
                nsubseteq: "⊈",
                nsubseteqq: "⫇",
                nsupseteq: "⊉",
                nsupseteqq: "⫈",
                ntriangleleft: "⋪",
                ntrianglelefteq: "⋬",
                ntriangleright: "⋫",
                ntrianglerighteq: "⋭",
                nvdash: "⊬",
                nVdash: "⊮",
                nvDash: "⊭",
                nVDash: "⊯",
                perp: "⊥",
                pitchfork: "⋔",
                propto: "∝",
                shortmid: "⏐",
                shortparallel: "⏑",
                smile: "⌣",
                sqsubset: "⊏",
                sqsubseteq: "⊑",
                sqsupset: "⊐",
                sqsupseteq: "⊒",
                subset: "⊂",
                Subset: "⋐",
                subseteq: "⊆",
                subseteqq: "⫅",
                subsetneq: "⊊",
                subsetneqq: "⫋",
                supset: "⊃",
                Supset: "⋑",
                supseteq: "⊇",
                supseteqq: "⫆",
                supsetneq: "⊋",
                supsetneqq: "⫌",
                trianglelefteq: "⊴",
                trianglerighteq: "⊵",
                varpropto: "⫉",
                varsubsetneq: "⏔",
                varsubsetneqq: "⏖",
                varsupsetneq: "⏕",
                varsupsetneqq: "⏗",
                vdash: "⊢",
                Vdash: "⊩",
                vDash: "⊨",
                Vvdash: "⊪",
                vert: "|",
                Vert: "ǁ",
                "|": "ǁ",
                "{": "{",
                "}": "}",
                backslash: "\\",
                langle: "〈",
                rangle: "〉",
                lceil: "⌈",
                rceil: "⌉",
                lbrace: "{",
                rbrace: "}",
                lfloor: "⌊",
                rfloor: "⌋",
                cdots: "⋯",
                ddots: "⋰",
                vdots: "⋮",
                dots: "…",
                ldots: "…",
                "#": "#",
                bot: "⊥",
                angle: "∠",
                backprime: "‵",
                bigstar: "★",
                blacklozenge: "◆",
                blacksquare: "■",
                blacktriangle: "▲",
                blacktriangledown: "▼",
                clubsuit: "♣",
                diagdown: "⒁",
                diagup: "⒂",
                diamondsuit: "♢",
                emptyset: "ø",
                exists: "∃",
                flat: "♭",
                forall: "∀",
                heartsuit: "♡",
                infty: "∞",
                lozenge: "◇",
                measuredangle: "∡",
                nabla: "∇",
                natural: "♮",
                neg: "¬",
                lnot: "¬",
                nexists: "∄",
                prime: "′",
                sharp: "♯",
                spadesuit: "♠",
                sphericalangle: "∢",
                surd: "√",
                top: "⊤",
                varnothing: "∅",
                triangle: "△",
                triangledown: "▽"
            }
        };
    }
};

/*!
 * 罗马字体
 */
_p[30] = {
    value: function() {
        return {
            meta: {
                fontFamily: "KF AMS ROMAN",
                src: "KF_AMS_ROMAN.woff"
            }
        };
    }
};

/**
 * 公式对象，表达式容器
 */
_p[31] = {
    value: function(require) {
        var kity = _p.r(34), GTYPE = _p.r(6), FontManager = _p.r(25), FontInstaller = _p.r(24), DEFAULT_OPTIONS = {
            fontsize: 50,
            autoresize: true,
            padding: [ 0 ]
        }, Output = _p.r(1), EXPRESSION_INTERVAL = 10, ExpressionWrap = kity.createClass("ExpressionWrap", {
            constructor: function(exp, config) {
                this.wrap = new kity.Group();
                this.bg = new kity.Rect(0, 0, 0, 0).fill("transparent");
                this.exp = exp;
                this.config = config;
                this.wrap.setAttr("data-type", "kf-exp-wrap");
                this.bg.setAttr("data-type", "kf-exp-wrap-bg");
                this.wrap.addShape(this.bg);
                this.wrap.addShape(this.exp);
            },
            getWrapShape: function() {
                return this.wrap;
            },
            getExpression: function() {
                return this.exp;
            },
            getBackground: function() {
                return this.bg;
            },
            resize: function() {
                var padding = this.config.padding, expBox = this.exp.getFixRenderBox();
                if (padding.length === 1) {
                    padding[1] = padding[0];
                }
                this.bg.setSize(padding[1] * 2 + expBox.width, padding[0] * 2 + expBox.height);
                this.exp.translate(padding[1], padding[0]);
            }
        }), Formula = kity.createClass("Formula", {
            base: _p.r(32),
            constructor: function(container, config) {
                this.callBase(container);
                this.expressions = [];
                this.fontInstaller = new FontInstaller(this);
                this.config = kity.Utils.extend({}, DEFAULT_OPTIONS, config);
                this.initEnvironment();
                this.initInnerFont();
            },
            getContentContainer: function() {
                return this.container;
            },
            initEnvironment: function() {
                this.zoom = this.config.fontsize / 50;
                if ("width" in this.config) {
                    this.setWidth(this.config.width);
                }
                if ("height" in this.config) {
                    this.setHeight(this.config.height);
                }
                this.node.setAttribute("font-size", DEFAULT_OPTIONS.fontsize);
            },
            initInnerFont: function() {
                var fontList = FontManager.getFontList(), _self = this;
                kity.Utils.each(fontList, function(fontInfo) {
                    createFontStyle(fontInfo);
                });
                function createFontStyle(fontInfo) {
                    var stylesheet = _self.doc.createElement("style"), tpl = '@font-face{font-family: "${fontFamily}";font-style: normal;src: url("${src}") format("woff");}';
                    stylesheet.setAttribute("type", "text/css");
                    stylesheet.innerHTML = tpl.replace("${fontFamily}", fontInfo.meta.fontFamily).replace("${src}", fontInfo.meta.src);
                    _self.resourceNode.appendChild(stylesheet);
                }
            },
            insertExpression: function(expression, index) {
                var expWrap = this.wrap(expression);
                // clear zoom
                this.container.clearTransform();
                this.expressions.splice(index, 0, expWrap.getWrapShape());
                this.addShape(expWrap.getWrapShape());
                notifyExpression.call(this, expWrap.getExpression());
                expWrap.resize();
                correctOffset.call(this);
                this.resetZoom();
                this.config.autoresize && this.resize();
            },
            appendExpression: function(expression) {
                this.insertExpression(expression, this.expressions.length);
            },
            resize: function() {
                var renderBox = this.container.getRenderBox("paper");
                this.node.setAttribute("width", renderBox.width);
                this.node.setAttribute("height", renderBox.height);
            },
            resetZoom: function() {
                var zoomLevel = this.zoom / this.getBaseZoom();
                if (zoomLevel !== 0) {
                    this.container.scale(zoomLevel);
                }
            },
            wrap: function(exp) {
                return new ExpressionWrap(exp, this.config);
            },
            clear: function() {
                this.callBase();
                this.expressions = [];
            },
            clearExpressions: function() {
                kity.Utils.each(this.expressions, function(exp) {
                    exp.remove();
                });
                this.expressions = [];
            },
            toJPG: function(cb) {
                new Output(this).toJPG(cb);
            },
            toPNG: function(cb) {
                new Output(this).toPNG(cb);
            }
        });
        kity.Utils.extend(Formula, {
            registerFont: function(fontData) {
                FontManager.registerFont(fontData);
            }
        });
        // 调整表达式之间的偏移
        function correctOffset() {
            var exprOffset = 0;
            kity.Utils.each(this.expressions, function(expr) {
                var box = null;
                if (!expr) {
                    return;
                }
                expr.setMatrix(new kity.Matrix(1, 0, 0, 1, 0, 0));
                box = expr.getFixRenderBox();
                expr.translate(0 - box.x, exprOffset);
                exprOffset += box.height + EXPRESSION_INTERVAL;
            });
            return this;
        }
        // 通知表达式已接入到paper
        function notifyExpression(expression) {
            var len = 0;
            if (!expression) {
                return;
            }
            if (expression.getType() === GTYPE.EXP) {
                for (var i = 0, len = expression.getChildren().length; i < len; i++) {
                    notifyExpression(expression.getChild(i));
                }
            } else if (expression.getType() === GTYPE.COMPOUND_EXP) {
                // 操作数处理
                for (var i = 0, len = expression.getOperands().length; i < len; i++) {
                    notifyExpression(expression.getOperand(i));
                }
                // 处理操作符
                notifyExpression(expression.getOperator());
            }
            expression.addedCall && expression.addedCall();
        }
        return Formula;
    }
};

/**
 * 公式专用paper
 */
_p[32] = {
    value: function(require) {
        var kity = _p.r(34);
        return kity.createClass("FPaper", {
            base: kity.Paper,
            constructor: function(container) {
                this.callBase(container);
                this.doc = container.ownerDocument;
                this.container = new kity.Group();
                this.container.setAttr("data-type", "kf-container");
                this.background = new kity.Group();
                this.background.setAttr("data-type", "kf-bg");
                this.baseZoom = 1;
                this.zoom = 1;
                this.base("addShape", this.background);
                this.base("addShape", this.container);
            },
            getZoom: function() {
                return this.zoom;
            },
            getBaseZoom: function() {
                return this.baseZoom;
            },
            addShape: function(shape, pos) {
                return this.container.addShape(shape, pos);
            },
            getBackground: function() {
                return this.background;
            },
            removeShape: function(pos) {
                return this.container.removeShape(pos);
            },
            clear: function() {
                return this.container.clear();
            }
        });
    }
};

/**
 * jquery
 */
_p[33] = {
    value: function() {
        if (!window.jQuery) {
            throw new Error("Missing jQuery");
        }
        return window.jQuery;
    }
};

/**
 * kity库封包
 */
_p[34] = {
    value: function() {
        if (!window.kity) {
            throw new Error("Missing Kity Graphic Lib");
        }
        return window.kity;
    }
};

/**
 * 小括号操作符：()
 */
_p[35] = {
    value: function(require) {
        var kity = _p.r(34), Text = _p.r(5);
        return kity.createClass("BracketsOperator", {
            base: _p.r(41),
            constructor: function() {
                this.callBase("Brackets");
            },
            applyOperand: function(exp) {
                generate.call(this, exp);
            }
        });
        function generate(exp) {
            var left = this.getParentExpression().getLeftSymbol(), right = this.getParentExpression().getRightSymbol(), fontSize = exp.getFixRenderBox().height, group = new kity.Group(), offset = 0, leftOp = new Text(left, "KF AMS MAIN").fill("black"), rightOp = new Text(right, "KF AMS MAIN").fill("black");
            leftOp.setFontSize(fontSize);
            rightOp.setFontSize(fontSize);
            this.addOperatorShape(group.addShape(leftOp).addShape(rightOp));
            offset += leftOp.getFixRenderBox().width;
            exp.translate(offset, 0);
            offset += exp.getFixRenderBox().width;
            rightOp.translate(offset, 0);
        }
    }
};

/**
 * 组合操作符
 * 操作多个表达式组合在一起
 */
_p[36] = {
    value: function(require) {
        var kity = _p.r(34);
        return kity.createClass("CombinationOperator", {
            base: _p.r(41),
            constructor: function() {
                this.callBase("Combination");
            },
            applyOperand: function() {
                // 偏移量
                var offsetX = 0, // 操作数
                operands = arguments, // 操作对象最大高度
                maxHeight = 0, // 垂直距离最大偏移
                maxOffsetTop = 0, maxOffsetBottom = 0, cached = [], // 偏移集合
                offsets = [];
                kity.Utils.each(operands, function(operand) {
                    var box = operand.getFixRenderBox(), offsetY = operand.getOffset();
                    box.height -= offsetY.top + offsetY.bottom;
                    cached.push(box);
                    offsets.push(offsetY);
                    maxOffsetTop = Math.max(offsetY.top, maxOffsetTop);
                    maxOffsetBottom = Math.max(offsetY.bottom, maxOffsetBottom);
                    maxHeight = Math.max(box.height, maxHeight);
                });
                kity.Utils.each(operands, function(operand, index) {
                    var box = cached[index];
                    operand.translate(offsetX - box.x, (maxHeight - (box.y + box.height)) / 2 + maxOffsetBottom - offsets[index].bottom);
                    offsetX += box.width;
                });
                this.parentExpression.setOffset(maxOffsetTop, maxOffsetBottom);
                this.parentExpression.updateBoxSize();
            }
        });
    }
};

/*!
 * 上下标控制器
 */
_p[37] = {
    value: function(require) {
        var kity = _p.r(34), EmptyExpression = _p.r(20), defaultOptions = {
            subOffset: 0,
            supOffset: 0,
            // 上下标的默认缩放值
            zoom: .66
        };
        return kity.createClass("ScriptController", {
            constructor: function(opObj, target, sup, sub, options) {
                this.observer = opObj.getParentExpression();
                this.target = target;
                this.sup = sup;
                this.sub = sub;
                this.options = kity.Utils.extend({}, defaultOptions, options);
            },
            // 上下标记
            applyUpDown: function() {
                var target = this.target, sup = this.sup, sub = this.sub, options = this.options;
                sup.scale(options.zoom);
                sub.scale(options.zoom);
                var targetBox = target.getFixRenderBox();
                if (EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                    return {
                        width: targetBox.width,
                        height: targetBox.height,
                        top: 0,
                        bottom: 0
                    };
                } else {
                    // 上标
                    if (!EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                        return this.applyUp(target, sup);
                    } else if (EmptyExpression.isEmpty(sup) && !EmptyExpression.isEmpty(sub)) {
                        return this.applyDown(target, sub);
                    } else {
                        return this.applyUpDownScript(target, sup, sub);
                    }
                }
            },
            /**
         * 返回应用上下标后的空间占用情况，其中的key各自的意义是：
         * top: 上空间偏移
         * bottom: 下空间偏移
         * width: 当前整个图形的实际占用空间的width
         * height: 当前整个图形的实际占用空间的height
         * @returns {*}
         */
            applySide: function() {
                var target = this.target, sup = this.sup, sub = this.sub;
                if (EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                    var targetRectBox = target.getRenderBox(this.observer);
                    return {
                        width: targetRectBox.width,
                        height: targetRectBox.height,
                        top: 0,
                        bottom: 0
                    };
                } else {
                    // 下标处理
                    if (EmptyExpression.isEmpty(sup) && !EmptyExpression.isEmpty(sub)) {
                        return this.applySideSub(target, sub);
                    } else if (!EmptyExpression.isEmpty(sup) && EmptyExpression.isEmpty(sub)) {
                        return this.applySideSuper(target, sup);
                    } else {
                        return this.applySideScript(target, sup, sub);
                    }
                }
            },
            applySideSuper: function(target, sup) {
                sup.scale(this.options.zoom);
                var targetRectBox = target.getRenderBox(this.observer), supRectBox = sup.getRenderBox(this.observer), targetMeanline = target.getMeanline(this.observer), supBaseline = sup.getBaseline(this.observer), positionline = targetMeanline, diff = supBaseline - positionline, space = {
                    top: 0,
                    bottom: 0,
                    width: targetRectBox.width + supRectBox.width,
                    height: targetRectBox.height
                };
                sup.translate(targetRectBox.width, 0);
                if (this.options.supOffset) {
                    sup.translate(this.options.supOffset, 0);
                }
                if (diff > 0) {
                    target.translate(0, diff);
                    space.bottom = diff;
                    space.height += diff;
                } else {
                    sup.translate(0, -diff);
                }
                return space;
            },
            applySideSub: function(target, sub) {
                sub.scale(this.options.zoom);
                var targetRectBox = target.getRenderBox(this.observer), subRectBox = sub.getRenderBox(this.observer), subOffset = sub.getOffset(), targetBaseline = target.getBaseline(this.observer), // 下标定位线
                subPosition = (subRectBox.height + subOffset.top + subOffset.bottom) / 2, diff = targetRectBox.height - targetBaseline - subPosition, space = {
                    top: 0,
                    bottom: 0,
                    width: targetRectBox.width + subRectBox.width,
                    height: targetRectBox.height
                };
                // 定位下标位置
                sub.translate(targetRectBox.width, subOffset.top + targetBaseline - subPosition);
                if (this.options.subOffset) {
                    sub.translate(this.options.subOffset, 0);
                }
                if (diff < 0) {
                    space.top = -diff;
                    space.height -= diff;
                }
                return space;
            },
            applySideScript: function(target, sup, sub) {
                sup.scale(this.options.zoom);
                sub.scale(this.options.zoom);
                var targetRectBox = target.getRenderBox(this.observer), subRectBox = sub.getRenderBox(this.observer), supRectBox = sup.getRenderBox(this.observer), targetMeanline = target.getMeanline(this.observer), targetBaseline = target.getBaseline(this.observer), supBaseline = sup.getBaseline(this.observer), // 上下标都存在时， 下标的定位以上伸线为准
                subAscenderline = sub.getAscenderline(this.observer), supPosition = targetMeanline, subPosition = targetMeanline + (targetBaseline - targetMeanline) * 2 / 3, topDiff = supPosition - supBaseline, bottomDiff = targetRectBox.height - subPosition - (subRectBox.height - subAscenderline), space = {
                    top: 0,
                    bottom: 0,
                    width: targetRectBox.width + Math.max(subRectBox.width, supRectBox.width),
                    height: targetRectBox.height
                };
                sup.translate(targetRectBox.width, topDiff);
                sub.translate(targetRectBox.width, subPosition - subAscenderline);
                if (this.options.supOffset) {
                    sup.translate(this.options.supOffset, 0);
                }
                if (this.options.subOffset) {
                    sub.translate(this.options.subOffset, 0);
                }
                // 定位纠正
                if (topDiff > 0) {
                    if (bottomDiff < 0) {
                        targetRectBox.height -= bottomDiff;
                        space.top = -bottomDiff;
                    }
                } else {
                    target.translate(0, -topDiff);
                    sup.translate(0, -topDiff);
                    sub.translate(0, -topDiff);
                    space.height -= topDiff;
                    if (bottomDiff > 0) {
                        space.bottom = -topDiff;
                    } else {
                        space.height -= bottomDiff;
                        // 比较上下偏移， 获取正确的偏移值
                        topDiff = -topDiff;
                        bottomDiff = -bottomDiff;
                        if (topDiff > bottomDiff) {
                            space.bottom = topDiff - bottomDiff;
                        } else {
                            space.top = bottomDiff - topDiff;
                        }
                    }
                }
                return space;
            },
            applyUp: function(target, sup) {
                var supBox = sup.getFixRenderBox(), targetBox = target.getFixRenderBox(), space = {
                    width: Math.max(targetBox.width, supBox.width),
                    height: supBox.height + targetBox.height,
                    top: 0,
                    bottom: supBox.height
                };
                sup.translate((space.width - supBox.width) / 2, 0);
                target.translate((space.width - targetBox.width) / 2, supBox.height);
                return space;
            },
            applyDown: function(target, sub) {
                var subBox = sub.getFixRenderBox(), targetBox = target.getFixRenderBox(), space = {
                    width: Math.max(targetBox.width, subBox.width),
                    height: subBox.height + targetBox.height,
                    top: subBox.height,
                    bottom: 0
                };
                sub.translate((space.width - subBox.width) / 2, targetBox.height);
                target.translate((space.width - targetBox.width) / 2, 0);
                return space;
            },
            applyUpDownScript: function(target, sup, sub) {
                var supBox = sup.getFixRenderBox(), subBox = sub.getFixRenderBox(), targetBox = target.getFixRenderBox(), space = {
                    width: Math.max(targetBox.width, supBox.width, subBox.width),
                    height: supBox.height + subBox.height + targetBox.height,
                    top: 0,
                    bottom: 0
                };
                sup.translate((space.width - supBox.width) / 2, 0);
                target.translate((space.width - targetBox.width) / 2, supBox.height);
                sub.translate((space.width - subBox.width) / 2, supBox.height + targetBox.height);
                return space;
            }
        });
    }
};

/**
 * 分数操作符
 */
_p[38] = {
    value: function(require) {
        var kity = _p.r(34), ZOOM = _p.r(47).zoom;
        return kity.createClass("FractionOperator", {
            base: _p.r(41),
            constructor: function() {
                this.callBase("Fraction");
            },
            applyOperand: function(upOperand, downOperand) {
                upOperand.scale(ZOOM);
                downOperand.scale(ZOOM);
                var upWidth = Math.ceil(upOperand.getWidth()), downWidth = Math.ceil(downOperand.getWidth()), upHeight = Math.ceil(upOperand.getHeight()), downHeight = Math.ceil(downOperand.getHeight()), // 分数线overflow值
                overflow = 3, // 整体padding
                padding = 1, maxWidth = Math.max(upWidth, downWidth), maxHeight = Math.max(upHeight, downHeight), operatorShape = generateOperator(maxWidth, overflow);
                this.addOperatorShape(operatorShape);
                upOperand.translate((maxWidth - upWidth) / 2 + overflow, 0);
                operatorShape.translate(0, upHeight + 1);
                // 下部不需要偏移
                downOperand.translate((maxWidth - downWidth) / 2 + overflow, upHeight + operatorShape.getHeight() + 1 * 2);
                this.parentExpression.setOffset(maxHeight - upHeight, maxHeight - downHeight);
                this.parentExpression.expand(padding * 2, padding * 2);
                this.parentExpression.translateElement(padding, padding);
            }
        });
        function generateOperator(width, overflow) {
            return new kity.Rect(width + overflow * 2, 1).fill("black");
        }
    }
};

/**
 * 函数操作符
 */
_p[39] = {
    value: function(require) {
        var kity = _p.r(34), Text = _p.r(5), ScriptController = _p.r(37);
        return kity.createClass("FunctionOperator", {
            base: _p.r(41),
            constructor: function(funcName) {
                this.callBase("Function: " + funcName);
                this.funcName = funcName;
            },
            /*
         * 积分操作符应用操作数
         * @param expr 函数表达式
         * @param sup 上限
         * @param sub 下限
         */
            applyOperand: function(expr, sup, sub) {
                var opShape = generateOperator.call(this), expBox = expr.getFixRenderBox(), scriptHanlder = this.parentExpression.isSideScript() ? "applySide" : "applyUpDown", space = new ScriptController(this, opShape, sup, sub, {
                    zoom: .5
                })[scriptHanlder](), padding = 5, diff = (space.height + space.top + space.bottom - expBox.height) / 2;
                // 应用偏移， 使图形在正确的位置上
                opShape.translate(0, space.top);
                sup.translate(0, space.top);
                sub.translate(0, space.top);
                if (diff >= 0) {
                    expr.translate(space.width + padding, diff);
                } else {
                    diff = -diff;
                    opShape.translate(0, diff);
                    sup.translate(0, diff);
                    sub.translate(0, diff);
                    expr.translate(space.width + padding, 0);
                }
                // 只扩展左边， 不扩展右边， 所以padding不 *2
                this.parentExpression.expand(padding, padding * 2);
                this.parentExpression.translateElement(padding, padding);
            }
        });
        /* 返回操作符对象 */
        function generateOperator() {
            var opShape = new Text(this.funcName, "KF AMS ROMAN");
            this.addOperatorShape(opShape);
            // 为操作符图形创建baseline和meanline方法
            opShape.getBaseline = function() {
                return opShape.getFixRenderBox().height;
            };
            opShape.getMeanline = function() {
                return 0;
            };
            return opShape;
        }
    }
};

/**
 * 积分操作符：∫
 */
_p[40] = {
    value: function(require) {
        var kity = _p.r(34), ScriptController = _p.r(37);
        return kity.createClass("IntegrationOperator", {
            base: _p.r(41),
            constructor: function(type) {
                this.callBase("Integration");
                // 默认是普通单重积分
                this.opType = type || 1;
            },
            setType: function(type) {
                this.opType = type | 0;
            },
            // 重置类型
            resetType: function() {
                this.opType = 1;
            },
            applyOperand: function(exp, sup, sub) {
                var opShape = this.getOperatorShape(), padding = 3, expBox = exp.getFixRenderBox(), space = new ScriptController(this, opShape, sup, sub, {
                    supOffset: 3,
                    subOffset: -15
                }).applySide(), diff = (space.height + space.top - expBox.height) / 2;
                opShape.translate(0, space.top);
                sup.translate(0, space.top);
                sub.translate(0, space.top);
                if (diff >= 0) {
                    exp.translate(space.width + padding, diff);
                } else {
                    diff = -diff;
                    opShape.translate(0, diff);
                    sup.translate(0, diff);
                    sub.translate(0, diff);
                    exp.translate(space.width + padding, 0);
                }
                this.parentExpression.expand(padding, padding * 2);
                this.parentExpression.translateElement(padding, padding);
            },
            getOperatorShape: function() {
                var pathData = "M1.318,48.226c0,0,0.044,0.066,0.134,0.134c0.292,0.313,0.626,0.447,1.006,0.447c0.246,0.022,0.358-0.044,0.604-0.268   c0.782-0.782,1.497-2.838,2.324-6.727c0.514-2.369,0.938-4.693,1.586-8.448C8.559,24.068,9.9,17.878,11.978,9.52   c0.917-3.553,1.922-7.576,3.866-8.983C16.247,0.246,16.739,0,17.274,0c1.564,0,2.503,1.162,2.592,2.57   c0,0.827-0.424,1.386-1.273,1.386c-0.671,0-1.229-0.514-1.229-1.251c0-0.805,0.514-1.095,1.185-1.274   c0.022,0-0.291-0.29-0.425-0.379c-0.201-0.134-0.514-0.224-0.737-0.224c-0.067,0-0.112,0-0.157,0.022   c-0.469,0.134-0.983,0.939-1.453,2.234c-0.537,1.475-0.961,3.174-1.631,6.548c-0.424,2.101-0.693,3.464-1.229,6.727   c-1.608,9.185-2.949,15.487-5.006,23.756c-0.514,2.034-0.849,3.24-1.207,4.335c-0.559,1.698-1.162,2.95-1.811,3.799   c-0.514,0.715-1.385,1.408-2.436,1.408c-1.363,0-2.391-1.185-2.458-2.592c0-0.804,0.447-1.363,1.273-1.363   c0.671,0,1.229,0.514,1.229,1.251C2.503,47.757,1.989,48.047,1.318,48.226z", group = new kity.Group(), opGroup = new kity.Group(), opShape = new kity.Path(pathData).fill("black"), opBox = new kity.Rect(0, 0, 0, 0).fill("transparent"), tmpShape = null;
                opGroup.addShape(opShape);
                group.addShape(opBox);
                group.addShape(opGroup);
                this.addOperatorShape(group);
                for (var i = 1; i < this.opType; i++) {
                    tmpShape = new kity.Use(opShape).translate(opShape.getWidth() / 2 * i, 0);
                    opGroup.addShape(tmpShape);
                }
                opGroup.scale(1.6);
                tmpShape = null;
                // 为操作符图形创建baseline和meanline方法
                group.getBaseline = function() {
                    return opGroup.getFixRenderBox().height;
                };
                group.getMeanline = function() {
                    return 10;
                };
                return group;
            }
        });
    }
};

/**
 * 操作符抽象类
 * @abstract
 */
_p[41] = {
    value: function(require) {
        var kity = _p.r(34), GTYPE = _p.r(6);
        return kity.createClass("Operator", {
            base: _p.r(46),
            constructor: function(operatorName) {
                this.callBase();
                this.type = GTYPE.OP;
                // 该操作符所属的表达式
                this.parentExpression = null;
                // 操作符名称
                this.operatorName = operatorName;
                // 操作符图形
                this.operatorShape = new kity.Group();
                this.addShape(this.operatorShape);
            },
            applyOperand: function() {
                throw new Error("applyOperand is abstract");
            },
            setParentExpression: function(exp) {
                this.parentExpression = exp;
            },
            getParentExpression: function() {
                return this.parentExpression;
            },
            clearParentExpression: function() {
                this.parentExpression = null;
            },
            // 提供给具体实现类附加其绘制的操作符图形的接口
            addOperatorShape: function(shpae) {
                return this.operatorShape.addShape(shpae);
            },
            getOperatorShape: function() {
                return this.operatorShape;
            }
        });
    }
};

/**
 * 开方操作符
 */
_p[42] = {
    value: function(require) {
        var kity = _p.r(34), // 符号图形属性
        // 线条宽度
        SHAPE_DATA_WIDTH = 1, // 计算公式
        radians = 2 * Math.PI / 360, sin15 = Math.sin(15 * radians), cos15 = Math.cos(15 * radians), tan15 = Math.tan(15 * radians);
        return kity.createClass("RadicalOperator", {
            base: _p.r(41),
            constructor: function() {
                this.callBase("Radical");
            },
            applyOperand: function(radicand, exponent) {
                generateOperator.call(this, radicand, exponent);
            }
        });
        // 根据给定的操作数生成操作符的pathData
        // radicand 表示被开方数
        // exponent 表示指数
        function generateOperator(radicand, exponent) {
            var decoration = generateDecoration(radicand), vLine = generateVLine(radicand), padding = 5, hLine = generateHLine(radicand);
            this.addOperatorShape(decoration);
            this.addOperatorShape(vLine);
            this.addOperatorShape(hLine);
            adjustmentPosition.call(this, mergeShape(decoration, vLine, hLine), this.operatorShape, radicand, exponent);
            this.parentExpression.expand(0, padding * 2);
            this.parentExpression.translateElement(0, padding);
        }
        // 生成根号中的左边装饰部分
        function generateDecoration(radicand) {
            var shape = new kity.Path(), // 命名为a以便于精简表达式
            a = SHAPE_DATA_WIDTH, h = radicand.getHeight() / 3, drawer = shape.getDrawer();
            // 根号尾部左上角开始
            drawer.moveTo(0, cos15 * a * 6);
            drawer.lineBy(sin15 * a, cos15 * a);
            drawer.lineBy(cos15 * a * 3, -sin15 * a * 3);
            drawer.lineBy(tan15 * h, h);
            drawer.lineBy(sin15 * a * 3, -cos15 * a * 3);
            drawer.lineBy(-sin15 * h, -h);
            drawer.close();
            return shape.fill("black");
        }
        // 根据操作数生成根号的竖直线部分
        function generateVLine(operand) {
            var shape = new kity.Path(), // * 0.9 是为了在视觉上使斜线部分不至于太高
            h = operand.getHeight() * .9, drawer = shape.getDrawer();
            drawer.moveTo(tan15 * h, 0);
            drawer.lineTo(0, h);
            drawer.lineBy(sin15 * SHAPE_DATA_WIDTH * 3, cos15 * SHAPE_DATA_WIDTH * 3);
            drawer.lineBy(tan15 * h + sin15 * SHAPE_DATA_WIDTH * 3, -(h + 3 * SHAPE_DATA_WIDTH * cos15));
            drawer.close();
            return shape.fill("black");
        }
        // 根据操作数生成根号的水平线部分
        function generateHLine(operand) {
            // 表达式宽度
            var w = operand.getWidth() + 2 * SHAPE_DATA_WIDTH;
            return new kity.Rect(w, 2 * SHAPE_DATA_WIDTH).fill("black");
        }
        // 合并根号的各个部分， 并返回根号的关键点位置数据
        function mergeShape(decoration, vLine, hLine) {
            var decoBox = decoration.getFixRenderBox(), vLineBox = vLine.getFixRenderBox();
            vLine.translate(decoBox.width - sin15 * SHAPE_DATA_WIDTH * 3, 0);
            decoration.translate(0, vLineBox.height - decoBox.height);
            vLineBox = vLine.getFixRenderBox();
            hLine.translate(vLineBox.x + vLineBox.width - SHAPE_DATA_WIDTH / cos15, 0);
            // 返回关键点数据
            return {
                x: vLineBox.x + vLineBox.width - SHAPE_DATA_WIDTH / cos15,
                y: 0
            };
        }
        // 调整整个根号表达式的各个部分： 位置、操作符、被开方数、指数
        function adjustmentPosition(position, operator, radicand, exponent) {
            var exponentBox = null, opOffset = {
                x: 0,
                y: 0
            }, opBox = operator.getFixRenderBox();
            exponent.scale(.66);
            exponentBox = exponent.getFixRenderBox();
            if (exponentBox.width > 0 && exponentBox.height > 0) {
                opOffset.y = exponentBox.height - opBox.height / 2;
                // 指数不超出根号， 则移动指数
                if (opOffset.y < 0) {
                    exponent.translate(0, -opOffset.y);
                    opOffset.y = 0;
                }
                opOffset.x = exponentBox.width + opBox.height / 2 * tan15 - position.x;
            }
            operator.translate(opOffset.x, opOffset.y);
            radicand.translate(opOffset.x + position.x + SHAPE_DATA_WIDTH, opOffset.y + 2 * SHAPE_DATA_WIDTH);
        }
    }
};

/**
 * 上下标操作符
 */
_p[43] = {
    value: function(require) {
        var kity = _p.r(34), ScriptController = _p.r(37);
        return kity.createClass("ScriptOperator", {
            base: _p.r(41),
            constructor: function(operatorName) {
                this.callBase(operatorName || "Script");
            },
            applyOperand: function(operand, sup, sub) {
                var padding = 1, parent = this.parentExpression, space = new ScriptController(this, operand, sup, sub).applySide();
                this.getOperatorShape();
                space && parent.setOffset(space.top, space.bottom);
                parent.expand(4, padding * 2);
                parent.translateElement(2, padding);
            }
        });
    }
};

/**
 * 求和操作符：∑
 */
_p[44] = {
    value: function(require) {
        var kity = _p.r(34), ScriptController = _p.r(37);
        return kity.createClass("SummationOperator", {
            base: _p.r(41),
            constructor: function() {
                this.callBase("Summation");
                this.displayType = "equation";
            },
            applyOperand: function(expr, sup, sub) {
                var opShape = this.getOperatorShape(), expBox = expr.getFixRenderBox(), padding = 0, space = new ScriptController(this, opShape, sup, sub).applyUpDown(), diff = (space.height - space.top - space.bottom - expBox.height) / 2;
                if (diff >= 0) {
                    expr.translate(space.width + padding, diff + space.bottom);
                } else {
                    diff = -diff;
                    opShape.translate(0, diff);
                    sup.translate(0, diff);
                    sub.translate(0, diff);
                    expr.translate(space.width + padding, space.bottom);
                }
                this.parentExpression.setOffset(space.top, space.bottom);
                this.parentExpression.expand(padding, padding * 2);
                this.parentExpression.translateElement(padding, padding);
            },
            getOperatorShape: function() {
                var pathData = "M0.672,33.603c-0.432,0-0.648,0-0.648-0.264c0-0.024,0-0.144,0.24-0.432l12.433-14.569L0,0.96c0-0.264,0-0.72,0.024-0.792   C0.096,0.024,0.12,0,0.672,0h28.371l2.904,6.745h-0.6C30.531,4.8,28.898,3.72,28.298,3.336c-1.896-1.2-3.984-1.608-5.28-1.8   c-0.216-0.048-2.4-0.384-5.617-0.384H4.248l11.185,15.289c0.168,0.24,0.168,0.312,0.168,0.36c0,0.12-0.048,0.192-0.216,0.384   L3.168,31.515h14.474c4.608,0,6.96-0.624,7.464-0.744c2.76-0.72,5.305-2.352,6.241-4.848h0.6l-2.904,7.681H0.672z", operatorShape = new kity.Path(pathData).fill("black"), opBgShape = new kity.Rect(0, 0, 0, 0).fill("transparent"), group = new kity.Group(), opRenderBox = null;
                group.addShape(opBgShape);
                group.addShape(operatorShape);
                operatorShape.scale(1.6);
                this.addOperatorShape(group);
                opRenderBox = operatorShape.getFixRenderBox();
                if (this.displayType === "inline") {
                    operatorShape.translate(5, 15);
                    opBgShape.setSize(opRenderBox.width + 10, opRenderBox.height + 25);
                } else {
                    operatorShape.translate(2, 5);
                    opBgShape.setSize(opRenderBox.width + 4, opRenderBox.height + 8);
                }
                return group;
            }
        });
    }
};

/*!
 * 资源管理器
 * 负责管理资源的加载，并在资源ready之后提供Formula构造器
 */
_p[45] = {
    value: function(require) {
        var kity = _p.r(34), cbList = [], RES_CONF = _p.r(47).resource, FontInstall = _p.r(24), Formula = _p.r(31), // 资源管理器就绪状态
        __readyState = false, // 资源管理器是否已启动
        inited = false;
        return {
            // 初始化
            ready: function(cb, options) {
                if (!inited) {
                    inited = true;
                    init(options);
                }
                if (__readyState) {
                    window.setTimeout(function() {
                        cb(Formula);
                    }, 0);
                } else {
                    cbList.push(cb);
                }
            }
        };
        /**
     * 资源初始化
     */
        function init(options) {
            options = kity.Utils.extend({}, RES_CONF, options);
            if (!/^(https?:)?\/\//.test(options.path)) {
                options.path = getFullPath(options.path);
            }
            new FontInstall(document, options.path).mount(complete);
        }
        function complete() {
            kity.Utils.each(cbList, function(cb) {
                cb(Formula);
            });
        }
        function getFullPath(path) {
            var pathname = location.pathname.split("/"), pathPart;
            pathname.length -= 1;
            pathname = pathname.join("/") + "/";
            pathPart = [ location.protocol, "//", location.host, pathname, path.replace(/^\//, "") ];
            return pathPart.join("");
        }
    }
};

/*!
 * 所有符号的基类
 * @abstract
 */
_p[46] = {
    value: function(require) {
        var kity = _p.r(34), GTYPE = _p.r(6);
        return kity.createClass("SignGroup", {
            base: kity.Group,
            constructor: function() {
                this.callBase();
                this.box = new kity.Rect(0, 0, 0, 0);
                this.type = GTYPE.UNKNOWN;
                this.addShape(this.box);
                this.zoom = 1;
            },
            setZoom: function(zoom) {
                this.zoom = zoom;
            },
            getZoom: function() {
                return this.zoom;
            },
            setBoxSize: function(w, h) {
                return this.box.setSize(w, h);
            },
            setBoxWidth: function(w) {
                return this.box.setWidth(w);
            },
            setBoxHeight: function(h) {
                return this.box.setHeight(h);
            },
            getType: function() {
                return this.type;
            },
            getBaseHeight: function() {
                return this.getHeight();
            },
            getBaseWidth: function() {
                return this.getWidth();
            },
            addedCall: function() {}
        });
    }
};

/*!
 * 系统项目配置文件.
 */
_p[47] = {
    value: function(require) {
        return {
            zoom: .66,
            font: {
                meanline: Math.round(380 / 1e3 * 50),
                baseline: Math.round(800 / 1e3 * 50),
                baseHeight: 50,
                // 系统字体列表
                list: [ _p.r(29), _p.r(27), _p.r(28), _p.r(26), _p.r(30) ]
            },
            /*------------------------- 资源配置*/
            resource: {
                path: "src/resource/"
            },
            // 函数相关配置
            func: {
                // 上下标在函数名上下两侧的函数列表
                "ud-script": {
                    lim: true
                }
            }
        };
    }
};

/*!
 * 启动代码
 */
_p[48] = {
    value: function(require) {
        window.kf = {
            // base
            ResourceManager: _p.r(45),
            Operator: _p.r(41),
            // expression
            Expression: _p.r(21),
            CompoundExpression: _p.r(19),
            TextExpression: _p.r(22),
            EmptyExpression: _p.r(20),
            CombinationExpression: _p.r(12),
            FunctionExpression: _p.r(14),
            FractionExpression: _p.r(13),
            IntegrationExpression: _p.r(15),
            RadicalExpression: _p.r(16),
            ScriptExpression: _p.r(17),
            SuperscriptExpression: _p.r(9),
            SubscriptExpression: _p.r(8),
            SummationExpression: _p.r(18),
            // Brackets expressoin
            BracketsExpression: _p.r(11)
        };
    }
};

var moduleMapping = {
    "kf.start": 48
};

function use(name) {
    _p.r([ moduleMapping[name] ]);
}
/**
 * 模块暴露
 */

( function ( global ) {

    var oldGetRenderBox = kity.Shape.getRenderBox;

    kity.extendClass(kity.Shape, {
        getFixRenderBox: function () {
            return this.getRenderBox( this.container.container );
        },

        getTranslate: function () {
            return this.transform.translate;
        }
    });

    // build环境中才含有use
    try {
        use( 'kf.start' );
    } catch ( e ) {
    }

} )( this );
})();