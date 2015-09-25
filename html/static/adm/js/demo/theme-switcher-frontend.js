/*!
 * Product:        Social - Premium Responsive Admin Template
 * Version:        2.1.3
 * Copyright:      2015 cesarlab.com
 * License:        http://themeforest.net/licenses
 * Live Preview:   http://go.cesarlab.com/SocialAdminTemplate2
 * Purchase:       http://go.cesarlab.com/PurchaseSocial2
 */
if (typeof jQuery === 'undefined') { throw new Error('Social\'s JavaScript requires jQuery'); }

$(function() {
  var changeElementsStyles, styles;
  styles = {
    "default": "inverse",
    "blue": "primary",
    "red": "danger",
    "green": "success",
    "orange": "warning"
  };

  /* */
  $(".theme-switcher > a").click(function(e) {
    e.preventDefault();
    $(".theme-switcher").toggleClass("open");
  });

  /* */
  changeElementsStyles = function(class_name) {
    var applyStyle, element, elems, _i, _len;
    elems = ["btn", "label"];
    applyStyle = function(element) {
      $("." + element).each(function() {
        var j;
        for (j in styles) {
          $(this).removeClass(element + "-" + styles[j]);
        }
        $(this).addClass(element + "-" + class_name);
      });
    };
    for (_i = 0, _len = elems.length; _i < _len; _i++) {
      element = elems[_i];
      applyStyle(element);
    }
  };

  /* */
  $(".theme-switcher select[name='colorpicker']").simplecolorpicker().on("change", function() {
    var $current_theme, theme_dir, theme_name;
    theme_dir = $(this).find("option:selected").data("theme");
    theme_name = theme_dir.replace(/^.*\/|\.[^.]*$/g, '');
    $current_theme = $('#current-theme');
    if (theme_name === 'default') {
      $current_theme.remove();
      changeElementsStyles(styles[theme_name]);
    } else if ($current_theme.length) {
      $current_theme.attr("href", theme_dir);
      changeElementsStyles(styles[theme_name]);
    } else {
      $("header").append("<link id=\"current-theme\" href=\"" + theme_dir + "\" rel=\"stylesheet\">");
      changeElementsStyles(styles[theme_name]);
    }
  });

  /* */
  $(".theme-switcher select[name='navbar']").on("change", function() {
    var val;
    val = $(this).val();
    if (val === "not-fixed") {
      $("#navbar").removeClass("navbar-fixed-top");
      $(".wrapper .main").css("margin-top", 0);
    } else {
      $("#navbar").addClass("navbar-fixed-top");
      $(".wrapper .main").css("margin-top", "60px");
    }
  });
});
