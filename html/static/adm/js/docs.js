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
  var $body, $window, navHeight;
  $window = $(window);
  $body = $(document.body);
  navHeight = $(".navbar").outerHeight(true) + 10;
  $body.scrollspy({
    target: ".bs-docs-sidebar"
  });
  $window.on("load", function() {
    $body.scrollspy("refresh");
  });
  setTimeout((function() {
    var $sideBar;
    $sideBar = $(".bs-docs-sidebar");
    $sideBar.affix({
      offset: {
        top: function() {
          var navOuterHeight, offsetTop, sideBarMargin;
          offsetTop = $sideBar.offset().top;
          sideBarMargin = parseInt($sideBar.children(0).css("margin-top"), 10);
          navOuterHeight = $(".bs-docs-nav").height();
          this.top = offsetTop - navOuterHeight - sideBarMargin;
        },
        bottom: function() {
          this.bottom = $(".bs-docs-footer").outerHeight(true);
        }
      }
    });
  }), 100);
  setTimeout((function() {
    $(".bs-top").affix();
  }), 100);
  $("[data-toggle=popover]").popover();
});
