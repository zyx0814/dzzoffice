/*
 * A colorselector for Twitter Bootstrap which lets you select a color from a predefined set of colors only.
 * https://github.com/flaute/bootstrap-colorselector
 *
 * Copyright (C) 2014 Flaute
 *
 * Licensed under the MIT license
 */

(function($) {
  "use strict";

  var ColorSelector = function(select, options) {
    this.options = options;
    this.$select = $(select);
    this._init();
  };

  ColorSelector.prototype = {

    constructor : ColorSelector,

    _init : function() {

      var callback = this.options.callback;

      var selectValue = this.$select.val();
      var selectColor = this.$select.find("option:selected").data("color");

      var $markupUl = $("<ul>").addClass("dropdown-menu").addClass("dropdown-caret");
      var $markupDiv = $("<div>").addClass("dropdown").addClass("dropdown-colorselector");
      var $markupSpan = $("<span>").addClass("btn-colorselector").css("background-color", selectColor);
      var $markupA = $("<a>").attr("data-toggle", "dropdown").addClass("dropdown-toggle").attr("href", "#").append($markupSpan);

      // create an li-tag for every option of the select
      $("option", this.$select).each(function() {

        var option = $(this);
        var value = option.attr("value");
        var color = option.data("color");
        var title = option.text();

        // create a-tag
        var $markupA = $("<a>").addClass("color-btn");
        if (option.prop("selected") === true || selectValue === color) {
          $markupA.addClass("selected");
        }
        $markupA.css("background-color", color);
        $markupA.attr("href", "#").attr("data-color", color).attr("data-value", value).attr("title", title);

        // create li-tag
        $markupUl.append($("<li>").append($markupA));
      });

      // append the colorselector
      $markupDiv.append($markupA);
      // append the colorselector-dropdown
      $markupDiv.append($markupUl);

      // hide the select
      this.$select.hide();
      
      // insert the colorselector
      this.$selector = $($markupDiv).insertAfter(this.$select);

      // register change handler
      this.$select.on("change", function() {

        var value = $(this).val();
        var color = $(this).find("option[value='" + value + "']").data("color");
        var title = $(this).find("option[value='" + value + "']").text();

        // remove old and set new selected color
        $(this).next().find("ul").find("li").find(".selected").removeClass("selected");
        $(this).next().find("ul").find("li").find("a[data-color='" + color + "']").addClass("selected");

        $(this).next().find(".btn-colorselector").css("background-color", color);
        
        callback(value, color, title);
      });

      // register click handler
      $markupUl.on('click.colorselector', $.proxy(this._clickColor, this));
    },

    _clickColor : function(e) {

      var a = $(e.target);

      if (!a.is(".color-btn")) {
        return false;
      }

      this.$select.val(a.data("value")).change();

      e.preventDefault();
      return true;
    },

    setColor : function(color) {
      // find value for color
      var value = $(this.$selector).find("li").find("a[data-color='" + color + "']").data("value");
      this.setValue(value);
    },

    setValue : function(value) {
      this.$select.val(value).change();
    },

  };

  $.fn.colorselector = function(option) {
    var args = Array.apply(null, arguments);
    args.shift();

    return this.each(function() {

      var $this = $(this), data = $this.data("colorselector"), options = $.extend({}, $.fn.colorselector.defaults, $this.data(), typeof option == "object" && option);

      if (!data) {
        $this.data("colorselector", (data = new ColorSelector(this, options)));
      }
      if (typeof option == "string") {
        data[option].apply(data, args);
      }
    });
  };

  $.fn.colorselector.defaults = {
    callback : function(value, color, title) {
    },
    colorsPerRow : 8
  };

  $.fn.colorselector.Constructor = ColorSelector;

})(jQuery, window, document);
