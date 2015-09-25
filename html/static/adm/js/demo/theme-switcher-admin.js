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
  var styles;
  styles = {
    "default": "inverse",
    "blue": "primary",
    "red": "danger",
    "green": "success",
    "orange": "warning"
  };
  $(".theme-switcher > a").click(function(e) {
    e.preventDefault();
    $(".theme-switcher").toggleClass("open");
  });
  $(".theme-switcher select[name='colorpicker']").simplecolorpicker().on("change", function() {
    var $current_theme, theme_dir, theme_name;
    theme_dir = $(this).find("option:selected").data("theme");
    theme_name = theme_dir.replace(/^.*\/|\.[^.]*$/g, '');
    $current_theme = $('#current-theme');
    if (theme_name === 'default') {
      $current_theme.remove();
    } else if ($current_theme.length) {
      $current_theme.attr("href", theme_dir);
    } else {
      $("header").append("<link id=\"current-theme\" href=\"" + theme_dir + "\" rel=\"stylesheet\">");
    }
  });
});
