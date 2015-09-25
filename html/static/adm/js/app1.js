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
  var $container, $resize;
  $(".carousel").carousel({
    interval: 50000
  });
  $(window).scroll(function() {
    if ($(window).scrollTop() > 60) {
      $("header .navbar").addClass("navbar-short");
    } else {
      $("header .navbar").removeClass("navbar-short");
    }
  });
  if ($(".isotopeWrapper").length) {
    $container = $(".isotopeWrapper");
    $resize = $(".isotopeWrapper").attr("id");
    $container.isotope({
      itemSelector: ".isotopeItem",
      resizable: false,
      masonry: {
        columnWidth: $container.width() / $resize
      }
    });
    $(".filter a").click(function() {
      var selector;
      $(".filter a").removeClass("current");
      $(this).addClass("current");
      selector = $(this).attr("data-filter");
      $container.isotope({
        filter: selector,
        animationOptions: {
          duration: 1000,
          easing: "easeOutQuart",
          queue: false
        }
      });
      return false;
    });
    $(window).smartresize(function() {
      $container.isotope({
        masonry: {
          columnWidth: $container.width() / $resize
        }
      });
    });
  }
});
