(function($, window, document, undefined) {
  "use strict";
  $.fn.extend({
    panels: function(options) {
      var settings;
      this.defaultOptions = {};
      settings = $.extend({}, this.defaultOptions, options);
      return this.find(".panel").each(function() {
        var $body, $parent, $this;
        $this = $(this);
        $parent = $this.closest(".panel");
        $body = $parent.find(".panel-body");

        /* Handle Collapse action */
        $parent.find(".panel-tools [data-option=\"collapse\"]").click(function(e) {
          var icons;
          e.preventDefault();
          if ($(this).hasClass("fa")) {
            icons = ["fa-chevron-down", "fa-chevron-up"];
          } else if ($(this).hasClass("glyphicon")) {
            icons = ["glyphicon-chevron-down", "glyphicon-chevron-up"];
          } else if ($(this).hasClass("halflings")) {
            icons = ["chevron-down", "chevron-up"];
          }
          if ($(this).hasClass(icons[1])) {
            $(this).removeClass(icons[1]).addClass(icons[0]);
            $body.slideDown("200", function() {});
          } else {
            $(this).removeClass(icons[0]).addClass(icons[1]);
            $body.slideUp("200", function() {});
          }
        });

        /* Handle Remove action */
        $parent.find(".panel-tools [data-dismiss=\"panel\"]").click(function(e) {
          e.preventDefault();
          $parent.remove();
        });
      });
    }
  });
})(jQuery, window, document);

(function($, window, document, undefined) {
  "use strict";

  /* Create the defaults once */
  var SocialSidebar, defaults, pluginName, winWidth;
  pluginName = "socialSidebar";
  defaults = {
    toggle: ".social-navbar .navbar-toggle",
    position: "front",
    reducedWidth: "54px",
    expandedWidth: "200px",
    duration: 200
  };

  /* The actual plugin constructor */
  SocialSidebar = function(element, options) {
    this.settings = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;
    this.elem = element;
    this.$elem = $(element);
    this.isRTL = $("html").is("[dir]");
    this.HTMLDirAttr = $("html").attr("dir");
    if (typeof this.HTMLDirAttr === "undefined") {
      this.isRTL = false;
    } else {
      this.isRTL = this.HTMLDirAttr.toLowerCase() === "rtl";
    }
    this.init();
  };

  /* */
  SocialSidebar.prototype = {
    init: function() {

      /* Define variables */
      this.$body = $(document.body);
      this.toggle = this.settings.toggle;
      this.$toggle = $(this.settings.toggle);

      /* Call some function */
      this.handleSidebarToggle();
      this.handleAccordionMenu();
      this.handleScrollMenu();
      this.handleHoverSidebar();
      this.handleSidebarChat();
    },

    /* Handle Sidebar for reducing it or expanding it */
    handleSidebarToggle: function() {
      var that;
      that = this;
      $(".main").click(function() {
        if ((winWidth() <= 768) && (that.$body.hasClass("sidebar-offcanvas-front"))) {
          that.$toggle.trigger("click");
        }
      });
      this.$toggle.click(function() {
        var sidebarPosition;
        that.$elem.find(".menu ul.collapse").removeClass("in");
        that.$elem.find(".menu a[data-toggle='collapse']").closest("li").removeClass("open");
        if (winWidth() >= 768) {
          if (that.$body.hasClass("reduced-sidebar")) {
            that.$body.removeClass("reduced-sidebar");
            $(".social-navbar .navbar-header").css("width", "");
            that.$elem.css("width", "");
			$('#logo').attr('src',window.location.origin+'/static/adm/img/islandlogo.png').removeClass('small-logo');
          } else {
			$('#logo').attr('src',window.location.origin+'/static/adm/img/island_peeps_beach.png').addClass('small-logo');
            that.$body.addClass("reduced-sidebar");
          }
        } else {
          if (that.settings.position === "next") {
            sidebarPosition = "sidebar-offcanvas-next";
          } else {
            sidebarPosition = "sidebar-offcanvas-front";
          }
          that.$body.toggleClass(sidebarPosition);
        }
      });
    },

    /* Handle accordion effect for multi-level elements */
    handleAccordionMenu: function() {
      this.$elem.find(".menu ul.in").css("height", "auto");
      $(".menu a[data-toggle='collapse']").on("click", function(e) {
        e.preventDefault();
        $(this).closest("li").toggleClass("open").children("ul").collapse("toggle");
        $(this).closest("li").siblings().removeClass("open").children("ul.in").collapse("hide");
      });
    },

    /* Handle scroll behavior */
    handleScrollMenu: function() {
      var chatHeight, that;
      that = this;
      if (that.$elem.find(".chat").css('display') === "none") {
        chatHeight = 0;
      } else {
        chatHeight = that.$elem.find(".chat").height();
      }
      this.$elem.find(".social-sidebar-content").slimScroll({
        height: (that.$elem.height() - chatHeight) + "px",
        position: (that.isRTL ? "left" : "right")
      });
      $(window).resize(function() {
        var currentHeight;
        if (that.$elem.find(".chat").css('display') === "none") {
          chatHeight = 0;
        } else {
          chatHeight = that.$elem.find(".chat").height();
        }
        currentHeight = that.$elem.height() - chatHeight;
        that.$elem.find("> .slimScrollDiv").css("height", currentHeight + "px");
        that.$elem.find("> .slimScrollDiv > .social-sidebar-content").css("height", currentHeight + "px");
      });
    },

    /* Handle reduced sidebar mode visualization when mouse enters it and leaves it */
    handleHoverSidebar: function() {
      var that;
      that = this;
      this.$elem.off("mouseenter").on("mouseenter", function() {
        if (that.$body.hasClass("reduced-sidebar") && (winWidth() >= 768)) {

          /* */
          $(".social-navbar .navbar-header").stop().animate({
            width: that.settings.expandedWidth
          }, that.settings.duration);

          /* */
          that.$elem.stop().animate({
            width: that.settings.expandedWidth
          }, that.settings.duration, function() {
            var delay;
            delay = setTimeout(function() {
              that.$elem.addClass("on");
            }, that.settings.duration / 4);
          });
        }
      });
      this.$elem.off("mouseleave").on("mouseleave", function() {
        if (that.$body.hasClass("reduced-sidebar") && (winWidth() >= 768)) {

          /* */
          $(".social-navbar .navbar-header").stop().animate({
            width: that.settings.reducedWidth
          }, that.settings.duration);

          /* */
          that.$elem.find(".menu ul.collapse").removeClass("in");
          that.$elem.find(".menu li.open").removeClass("open");
          that.$elem.find(".user").removeClass("open");
          that.$elem.removeClass("on").stop().animate({
            width: that.settings.reducedWidth
          }, that.settings.duration, function() {
            that.$elem.removeClass("on");
          });
        }
      });
    },

    /* Handle interaction for the char section */
    handleSidebarChat: function() {
      var chatScrollOptions, that, usersChat;
      usersChat = this.$elem.find(".chat");
      that = this;
      chatScrollOptions = {
        height: usersChat.find(".users-list").height(),
        size: "8px",
        railColor: "#000",
        wheelStep: 15,
        position: (that.isRTL ? "left" : "right")
      };
      usersChat.find(".users-list").slimscroll(chatScrollOptions);

      /* */
      if (!$().resizable) {
        return;
      }
      usersChat.resizable({
        handles: "n",
        maxHeight: 400,
        minHeight: 110,
        resize: function(event, ui) {
          var currentHeight, padding;
          currentHeight = ui.size.height;
          padding = 3;
          $(this).height(currentHeight);
          $(this).css("top", "auto");
          usersChat.find(".slimScrollDiv, .users-list").height(currentHeight - 70);
          that.$elem.find("> .slimScrollDiv").height($(window).height() - currentHeight - padding);
          that.$elem.find(".social-sidebar-content").height($(window).height() - currentHeight - padding);
        }
      });
    }
  };

  /* Some action when the user resize the windows of the browser */
  $(window).resize(function() {
    if (winWidth() < 768) {
      if ($(document.body).hasClass("reduced-sidebar")) {
        $(document.body).removeClass("reduced-sidebar");
      }
    }
    $(".social-sidebar, .navbar-header").css("width", "");
  });

  /* Cross browser window width
      *Borrowed from jRespond source code
   */
  winWidth = function() {
    var w;
    w = 0;
    if (typeof window.innerWidth !== "number") {
      if (document.documentElement.clientWidth !== 0) {
        w = document.documentElement.clientWidth;
      } else {
        w = document.body.clientWidth;
      }
    } else {
      w = window.innerWidth;
    }
    return w;
  };
  $.fn[pluginName] = function(options) {
    return this.each(function() {
      if (!$.data(this, "plugin_" + pluginName)) {
        $.data(this, "plugin_" + pluginName, new SocialSidebar(this, options));
      }
    });
  };
})(jQuery, window, document);

var App;

App = (function($) {
  "use strict";
  /* Check for device touch support
     Based on https://github.com/Modernizr/Modernizr/blob/master/feature-detects/touchevents.js
  */

  var handleNumberSignLinks, handleSidebarChat, handleSidebarOptions, handleSuperMenu, handleUiPro, init, isRTLVersion, isTouchDevice;
  isTouchDevice = function() {
    if (("ontouchstart" in window) || window.DocumentTouch && document instanceof DocumentTouch) {
      return true;
    } else {
      return false;
    }
  };
  /**/

  isRTLVersion = function() {
    return $("body").hasClass('rtl');
  };
  /**/

  init = function() {
  
    handleNumberSignLinks();
    handleSidebarOptions();
    handleSidebarChat();
    handleUiPro();
    handleSuperMenu();
  };
  /* Disable certain links*/

  handleNumberSignLinks = function() {
    $("[href|='#']").click(function(e) {
      e.preventDefault();
    });
  };
  /*  Sidebar Options*/

  handleSidebarOptions = function() {
    var dividersTrigger, sidebar, wraper;
    sidebar = $(".social-sidebar");
    wraper = $(".wraper");
    return dividersTrigger = $("#panel #sidebar-dividers");
  };
  /**/

  handleSidebarChat = function() {
    if (typeof chatboxManager !== 'undefined') {
      chatboxManager.init({
        sender: {
          username: "Me",
          lastname: "Me"
        }
      });
      $(".chat-users .user-list li > a").click(function(event, ui) {
        var id;
        id = $(this).attr("data-userid");
        chatboxManager.addBox(id, {
          title: "chatbox" + id,
          firstname: $(this).attr("data-firstname"),
          lastname: $(this).attr("data-lastname"),
          status: $(this).attr("data-status")
        });
        event.preventDefault();
      });
      return;
    }
  };
  /**/

  handleUiPro = function() {
  
  
    if (isTouchDevice() === false) {
      if (isRTLVersion()) {
        $.uiPro({
          leftMenu: ".rightPanel",
          threshold: 15
        });
      } else {
        $.uiPro({
          rightMenu: ".rightPanel",
          threshold: 15
        });
      }
    }
 
  };
  /**/

  handleSuperMenu = function() {
    return $(document).on("click", ".social-sm .dropdown-menu", function(e) {
      e.stopPropagation();
    });
  };
  return {
    init: init,
    isTouchDevice: isTouchDevice
  };
})(jQuery);


$(function(){
	
	//theme code
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
	
	//updating users theme preference
	
	//updating sidebar preference
	$('input[name="sidebar"]').on('change',function(e){
		var v = $(this).is(':checked') ? 1 : 0;
		if(v){
			$('body').addClass('reduced-sidebar');
			$('#logo').attr('src',window.location.origin+'/static/adm/img/island_peeps_beach.png').addClass('small-logo');
		}else{
			$('body').removeClass('reduced-sidebar');
			$('#logo').attr('src',window.location.origin+'/static/adm/img/islandlogo.png').removeClass('small-logo');
		}
		$.getJSON(window.location.origin + '/admin/api/update_setting?x=sidebar&v='+v,function(data){
			if(data.error != 0 ){
				sk.alert(data.message);
			}
		});
	})
	
	//updating users custom admin sidebar
	$("#sort_items ul").sortable({
		containment: "parent", 
		update: function(){
			var v = '',ul = $('#sort_items ul > li[data-order]');
			for(var i=0;i<ul.length;i++){
				v += ul.eq(i).data('order')+',';
				if(i == ul.length - 1){
					$.getJSON(window.location.origin + '/admin/api/update_setting?x=menu&v='+v.slice(0,-1),function(data){
						if(data.error != 0 ){
							sk.alert(data.message);
						}
					});
				}
			}
		}
	});
	
	if($('#sort_items ul > li > a[href="'+window.location.href+'"]').length > 0){
		$('#sort_items ul > li > a[href="'+window.location.href+'"]').parent('li').addClass('active');
	}
	

	$(".social-sidebar").socialSidebar();
	$('.main').panels();
	$(".main a[href='#ignore']").click(function(e) {
		e.stopPropagation()
	});
	$(document).on('click', '.navbar-super .navbar-super-fw', function(e) {
		e.stopPropagation()
	});
	
	$(".Sidebarheight").slimScroll({
		  height: ($(window).height() - 96),
	});
	
	
	
});
