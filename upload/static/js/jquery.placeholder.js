/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 
 //依赖：BROWSER
 //头部引入js：
 <!--[if lt IE 9]>
  <script src="static/js/jquery.placeholder.js" type="text/javascript"></script>
 <![endif]-->
 
  //调用示例
 jQuery(document).ready(function(e) {
	jQuery(':input[placeholder]').each(function(){
		jQuery(this).placeholder();
	});
});
 
  */
(function($)
{
    $.fn.placeholder = function (options) {
        var defaults = {
            pColor: "#666",
            pActive: "#999",
            pFont: "14px",
            activeBorder: "#080",
            posL: 16,
            zIndex: "999"
        },
        opts = $.extend(defaults, options);  
        return this.each(function () {
            if ("placeholder" in document.createElement("input")) return;
            $(this).parent().css("position", "relative");
            var isIE = BROWSER.ie,  version = BROWSER.ie;
            //不支持placeholder的浏览器
            var $this = $(this),
                msg = $this.attr("placeholder"),
                iH = $this.outerHeight(true),
                iW = $this.outerWidth(true),
                iX = $this.position().left,
                iY = $this.position().top,
                oInput = $("<span>", {
                "class": "wrapper-placeholder",
                "text": msg,
                "css": {
                    "position": "absolute",
                    "left": iX + "px",
                    "top": iY + "px",
                    "width": iW  + "px",
                    "padding-left": opts.posL + "px",
                    "height": iH + "px",
                    "line-height": iH + "px",
                    "color": opts.pColor,
                    "font-size": opts.pFont,
                    "z-index": opts.zIndex,
                    "cursor": "text"
                }
                }).insertBefore($this);
            //初始状态就有内容
            var value = $this.val();
            if (value.length > 0) {
           		 oInput.hide();
            };
  
            $this.on("focus", function () {
                var value = $(this).val();
                if (value.length > 0) {
                    oInput.hide();
                }
                oInput.css("color", opts.pActive);
                if(isIE && version <= 9){
                    var myEvent = "propertychange";
                }else{
                    var myEvent = "input";
                }
  
                $(this).on(myEvent, function () {
                    var value = $(this).val();
                    if (value.length == 0) {
                        oInput.show();
                    } else {
                        oInput.hide();
                    }
                });
  
            }).on("blur", function () {
                var value = $(this).val();
                if (value.length == 0) {
                    oInput.css("color", opts.pColor).show();
                }
            });
            oInput.on("click", function () {
                $this.trigger("focus");
                $(this).css("color", opts.pActive)
            });
            $this.filter(":focus").trigger("focus");
        });
    }
})(jQuery);
