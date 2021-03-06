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
	
	if(location.pathname == '/admin/masterlist'){
		$('a[href="#masterlist-ui"]').parent('li').addClass('active');
	}else{
		if($('#sort_items ul > li > a[href="'+window.location.href+'"]').length > 0){
			$('#sort_items ul > li > a[href="'+window.location.href+'"]').parent('li').addClass('active');
		}else{
			$('#sort_items ul > li > a[href*="'+location.pathname+'"]').parent('li').addClass('active');
		}	
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
	
	$(document).on('mouseover','.poptips',function(e){
		tooltip.pop(this, $(this).attr('href'));
	});
	
	$(window).load(function() {
	  imgErrors();
	  var theme = $('.simplecolorpicker').data('selected');
	  $('#'+theme).addClass('selected');
	});
	
	function imgErrors()
	{
		$('.img.catch-e').each(function() {
			if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
			  // image was broken, replace with your new image
			  this.src = window.location.origin+'/static/adm/img/no-img.png';
			}
			});
			$('.flag.catch-e').each(function() {
			if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
			  // image was broken, replace with your new image
			  this.src = window.location.origin+'/static/adm/img/no-flag.gif';
			}
		});
	}
	
	$(document).on('click','.themechange',function(e){
		var v = this.id;
		$('.themechange').removeClass('selected');
		$(this).addClass('selected');
		$('#theme').attr('href',window.location.origin+'/static/adm/css/themes/social.theme-'+v+'.css');
		$.getJSON(window.location.origin + '/admin/api/update_setting?x=theme&v='+v,function(data){
			if(data.error != 0 ){
				sk.alert(data.message);
			}else{
				sk.alert('Theme changed','success');
			}
		});
	});
	
	$(document).on('change','input[name="pageper"]',function(e){
		var v = $('input[name="pageper"][type=radio]:checked').val();
		$.getJSON(window.location.origin + '/admin/api/update_setting?x=perpage&v='+v,function(data){
			if(data.error != 0 ){
				sk.alert(data.message);
			}else{
				sk.alert('Your changes were saved. you may need to reload the page for them to take effect.','success');
			}
		});
	});
	
	if($('#mastername').length > 0){
		$.getJSON(window.location.origin+'/admin/api/mlist_names',function(data){
			$("#mastername").autocomplete({
				source: data.message,
				minLength: 1,
				cacheLength: 0,
				select: function(event, ui) {
					
				
				   
				}
			});
		})
	}
	
	$('#sort_items ul li[data-order="11"] .badge').html( $('#social-sidebar-menu').data('calendar'));
	
	
	if($('#dashboard_sort').length > 0){
		$('#dashboard_sort').sortable({
			tolerance: 'pointer',
			revert: 'invalid',
			placeholder: 'span2 well placeholder tile',
			forceHelperSize: true,
			update: function() {
				var v = '',ul = $('#dashboard_sort > div[data-order]');
				for(var i=0;i<ul.length;i++){
					v += ul.eq(i).data('order')+',';
					if(i == ul.length - 1){
						$.getJSON(window.location.origin + '/admin/api/update_setting?x=dashboard&v='+v.slice(0,-1),function(data){
							if(data.error != 0 ){
								sk.alert(data.message);
							}
						});
					}
				}
			}					
		});
		$.getJSON(window.location.origin+'/admin/api/getDashboard',function(data){
			$('#dashboard_sort div[data-order="0"] .panel-body').html(data.notification);
			$('#dashboard_sort div[data-order="1"] .users-feed .maxheight_recent').html(data.recent_profiles);
			$('#dashboard_sort div[data-order="1"] .activities-feed .maxheight_recent').html(data.affiliate_log);
			
			
			//hits by city
			(function(data){
				var chart = new AmCharts.AmSerialChart();
				chart.dataProvider = data;
				chart.categoryField = "city";chart.marginRight = 0;chart.marginTop = 0; 
				//chart.autoMarginOffset = 0;
				// the following two lines makes chart 3D
				chart.depth3D = 20;chart.angle = 30;
				// AXES // category
				var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
				categoryAxis.inside = true;categoryAxis.gridCount = data.length;categoryAxis.autoGridCount = false;
				// value
				var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Result";chart.addValueAxis(valueAxis);
				// GRAPH            
				var graph = new AmCharts.AmGraph();
				graph.valueField = "count";
				graph.colorField = "color";
				graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
				chart.addGraph(graph);
				// WRITE
				chart.write("vmap-world");   
			})(data.hits_by_city);
			
			//profile per country chart
			(function(data){
				var chart = new AmCharts.AmSerialChart();
				chart.dataProvider = data;
				chart.categoryField = "country";
				chart.marginRight = 0;
				chart.marginTop = 0; 
				chart.depth3D = 20;
				chart.angle = 30;
				var categoryAxis = chart.categoryAxis;
				categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
				categoryAxis.inside = true;categoryAxis.gridCount = data.length;
				categoryAxis.autoGridCount = false;
				var valueAxis = new AmCharts.ValueAxis();
				valueAxis.title = "Result";
				chart.addValueAxis(valueAxis);          
				var graph = new AmCharts.AmGraph();
				graph.valueField = "visits";
				graph.colorField = "color";
				graph.balloonText = "[[category]]: [[value]]";
				graph.type = "column";
				graph.lineAlpha = 0;
				graph.fillAlphas = 1;
				chart.addGraph(graph);
				chart.write("pie-profileperc");   	
			})(data.profile_per);
			//visits by location chart
			(function(data){
				var chart = new AmCharts.AmSerialChart();
				chart.dataProvider = data;
				chart.categoryField = "country";
				chart.marginRight = 0;
				chart.marginTop = 0; 
				chart.depth3D = 20;chart.angle = 30;
				// AXES // category
				var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
				categoryAxis.inside = true;categoryAxis.gridCount = data.length;categoryAxis.autoGridCount = false;
				// value
				var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Visit";chart.addValueAxis(valueAxis);
				// GRAPH            
				var graph = new AmCharts.AmGraph();graph.valueField = "visits";
				graph.colorField = "color";
				graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
				chart.addGraph(graph);
				// WRITE
				chart.write("pie-visitsperc"); 
			})(data.visits_per);
			
			(function(data){
				var chart = new AmCharts.AmSerialChart();
				chart.dataProvider = data;
				chart.categoryField = "day";chart.marginRight = 0;chart.marginTop = 0; 
				//chart.autoMarginOffset = 0;
				// the following two lines makes chart 3D
				chart.depth3D = 20;chart.angle = 30;
				// AXES // category
				var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
				categoryAxis.inside = true;categoryAxis.gridCount = data.length;categoryAxis.autoGridCount = false;
				// value
				var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Result";chart.addValueAxis(valueAxis);
				// GRAPH            
				var graph = new AmCharts.AmGraph();
				graph.valueField = "count";
				graph.colorField = "color";
				graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
				chart.addGraph(graph);
				// WRITE
				chart.write("demo-plot");   
			})(data.hits_by_day);
			
		});
	}
	
	if($('#stats_sort').length > 0){
		$('#stats_sort').sortable({
			handle: '.panel-heading',
			tolerance: 'pointer',
			revert: 'invalid',
			placeholder: 'span2 well placeholder tile',
			forceHelperSize: true,
			update: function() {
				var v = '',ul = $('#stats_sort > div[data-order]');
				for(var i=0;i<ul.length;i++){
					v += ul.eq(i).data('order')+',';
					if(i == ul.length - 1){
						$.getJSON(window.location.origin + '/admin/api/update_setting?x=stats&v='+v.slice(0,-1),function(data){
							if(data.error != 0 ){
								sk.alert(data.message);
							}
						});
					}
				}
			}					
		});
		$.getJSON(window.location.origin+'/admin/api/getAnalytics',function(data){
			
			(function(data){
				var pie = new AmCharts.AmPieChart();
				pie.dataProvider = data;
				pie.titleField = "k";
				pie.valueField = "v";
				pie.outlineColor = "#FFFFFF";
				pie.outlineAlpha = 0.8;
				pie.outlineThickness = 2;
				pie.labelRadius = -30;
				pie.labelText = "[[value]]";
				pie.startDuration = 0;
				// this makes the chart 3D
				pie.depth3D = 15;
				pie.angle = 30;
				pie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");
				
				var legend = new AmCharts.AmLegend();
				legend.position = "bottom";
				legend.borderAlpha = 0;
				legend.horizontalGap = 10;
				legend.switchType = "x"; // or v
				legend.valueText = "";
				pie.addLegend(legend);
				// WRITE
				pie.write("pie-browser");
			})(data.browser);
			
			(function (data){
				var	screenRespie = new AmCharts.AmPieChart();
				screenRespie.dataProvider = data;
				screenRespie.titleField = "k";
				screenRespie.valueField = "v";
				screenRespie.outlineColor = "#FFFFFF";
				screenRespie.outlineAlpha = 0.8;
				screenRespie.outlineThickness = 2;
				screenRespie.labelRadius = -30;
				screenRespie.labelText = "[[value]]";
				screenRespie.startDuration = 0;
				// this makes the chart 3D
				screenRespie.depth3D = 15;
				screenRespie.angle = 30;
				screenRespie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	screenReslegend = new AmCharts.AmLegend();
				screenReslegend.position = "bottom";
				screenReslegend.borderAlpha = 0;
				screenReslegend.horizontalGap = 10;
				screenReslegend.switchType = "x"; // or v
				screenReslegend.valueText = "";
				screenRespie.addLegend(screenReslegend);
				// WRITE
				screenRespie.write("pie-screen");
			})(data.screen_sizes);
			
			
			(function(data){
				$('#statsmonthlywiseinfo .panel-title').html('Month ('+data.name+') Hits - Total ('+data.total+') ');
				var	countrypie = new AmCharts.AmPieChart();
				countrypie.dataProvider = data.data;
				countrypie.titleField = "k";
				countrypie.valueField = "v";
				countrypie.outlineColor = "#FFFFFF";
				countrypie.outlineAlpha = 0.8;
				countrypie.outlineThickness = 2;
				countrypie.labelRadius = -30;
				countrypie.labelText = "[[value]]";
				countrypie.startDuration = 0;
				// this makes the chart 3D
				countrypie.depth3D = 15;
				countrypie.angle = 30;
				countrypie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	countrylegend = new AmCharts.AmLegend();
				countrylegend.position = "bottom";
				countrylegend.borderAlpha = 0;
				countrylegend.horizontalGap = 10;
				countrylegend.switchType = "x"; // or v
				countrylegend.valueText = "";
				countrypie.addLegend(countrylegend);
				// WRITE
				countrypie.write("pie-monthlydiv");
			})(data.this_month);
			
			(function(data){
				var	upie = new AmCharts.AmPieChart();
				upie.dataProvider = data;
				upie.titleField = "k";
				upie.valueField = "v";
				upie.outlineColor = "#FFFFFF";
				upie.outlineAlpha = 0.8;
				upie.outlineThickness = 2;
				upie.labelRadius = -30;
				upie.labelText = "[[value]]";
				upie.startDuration = 0;
				// this makes the chart 3D
				upie.depth3D = 15;
				upie.angle = 30;
				upie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	ulegend = new AmCharts.AmLegend();
				ulegend.position = "bottom";
				ulegend.borderAlpha = 0;
				ulegend.horizontalGap = 10;
				ulegend.switchType = "x"; // or v
				ulegend.valueText = "";
				upie.addLegend(ulegend);
				// WRITE
				upie.write("pie-usertype1");
			})(data.new_vs_returning);
			
			(function(data){ 
				var	countrypie = new AmCharts.AmPieChart();
				countrypie.dataProvider = data;
				countrypie.titleField = "k";
				countrypie.valueField = "v";
				countrypie.outlineColor = "#FFFFFF";
				countrypie.outlineAlpha = 0.8;
				countrypie.outlineThickness = 2;
				countrypie.labelRadius = -30;
				countrypie.labelText = "[[value]]";
				countrypie.startDuration = 0;
				// this makes the chart 3D
				countrypie.depth3D = 15;
				countrypie.angle = 30;
				countrypie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	countrylegend = new AmCharts.AmLegend();
				countrylegend.position = "bottom";
				countrylegend.borderAlpha = 0;
				countrylegend.horizontalGap = 10;
				countrylegend.switchType = "x"; // or v
				countrylegend.valueText = "";
				countrypie.addLegend(countrylegend);
				// WRITE
				countrypie.write("pie-country");
			})(data.hits_by_country);
			
			(function(data){
				var	city = new AmCharts.AmPieChart();
				city.dataProvider = data;
				city.titleField = "k";
				city.valueField = "v";
				city.outlineColor = "#FFFFFF";
				city.outlineAlpha = 0.8;
				city.outlineThickness = 2;
				city.labelRadius = -30;
				city.labelText = "[[value]]";
				city.startDuration = 0;
				// this makes the chart 3D
				city.depth3D = 15;
				city.angle = 30;
				city.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	citylegend = new AmCharts.AmLegend();
				citylegend.position = "bottom";
				citylegend.borderAlpha = 0;
				citylegend.horizontalGap = 10;
				citylegend.switchType = "x"; // or v
				citylegend.valueText = "";
				city.addLegend(citylegend);
				// WRITE
				city.write("pie-city");
			})(data.hits_by_city);
			
			(function(data){
				var ospie = new AmCharts.AmPieChart();
				ospie.dataProvider = data;
				ospie.titleField = "k";
				ospie.valueField = "v";
				ospie.outlineColor = "#FFFFFF";
				ospie.outlineAlpha = 0.8;
				ospie.outlineThickness = 2;
				ospie.labelRadius = -30;
				ospie.labelText = "[[value]]";
				ospie.startDuration = 0;
				// this makes the chart 3D
				ospie.depth3D = 15;
				ospie.angle = 30;
				ospie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	oslegend = new AmCharts.AmLegend();
				oslegend.position = "bottom";
				oslegend.borderAlpha = 0;
				oslegend.horizontalGap = 10;
				oslegend.switchType = "x"; // or v
				oslegend.valueText = "";
				ospie.addLegend(oslegend);
				// WRITE
				ospie.write("pie-os");
			})(data.devices);
			
			(function(data){
				var	networkLpie = new AmCharts.AmPieChart();
				networkLpie.dataProvider = data;
				networkLpie.titleField = "k";
				networkLpie.valueField = "v";
				networkLpie.outlineColor = "#FFFFFF";
				networkLpie.outlineAlpha = 0.8;
				networkLpie.outlineThickness = 2;
				networkLpie.labelRadius = -30;
				networkLpie.labelText = "[[value]]";
				networkLpie.startDuration = 0;
				// this makes the chart 3D
				networkLpie.depth3D = 15;
				networkLpie.angle = 30;
				networkLpie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	networkLlegend = new AmCharts.AmLegend();
				networkLlegend.position = "bottom";
				networkLlegend.borderAlpha = 0;
				networkLlegend.horizontalGap = 10;
				networkLlegend.switchType = "x"; // or v
				networkLlegend.valueText = "";
				networkLpie.addLegend(networkLlegend);
				// WRITE
				networkLpie.write("pie-isp");
			})(data.isp);
			
			(function(data){
				var	networkLpie = new AmCharts.AmPieChart();
				networkLpie.dataProvider = data;
				networkLpie.titleField = "k";
				networkLpie.valueField = "v";
				networkLpie.outlineColor = "#FFFFFF";
				networkLpie.outlineAlpha = 0.8;
				networkLpie.outlineThickness = 2;
				networkLpie.labelRadius = -30;
				networkLpie.labelText = "[[value]]";
				networkLpie.startDuration = 0;
				// this makes the chart 3D
				networkLpie.depth3D = 15;
				networkLpie.angle = 30;
				networkLpie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	networkLlegend = new AmCharts.AmLegend();
				networkLlegend.position = "bottom";
				networkLlegend.borderAlpha = 0;
				networkLlegend.horizontalGap = 10;
				networkLlegend.switchType = "x"; // or v
				networkLlegend.valueText = "";
				networkLpie.addLegend(networkLlegend);
				// WRITE
				networkLpie.write("demo-plot");
			})(data.pages);
			
			(function(data){
				var chart = new AmCharts.AmSerialChart();
				chart.dataProvider = data;
				chart.categoryField = "k";
				chart.marginRight = 0;chart.marginTop = 0; 
				//chart.autoMarginOffset = 0;
				// the following two lines makes chart 3D
				chart.depth3D = 20;chart.angle = 30;
				// AXES // category
				var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
				categoryAxis.inside = true;categoryAxis.gridCount = data.length;categoryAxis.autoGridCount = false;
				// value
				var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Result";chart.addValueAxis(valueAxis);
				// GRAPH            
				var graph = new AmCharts.AmGraph();
				graph.valueField = "v";
				graph.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");
				graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
				chart.addGraph(graph);
				// WRITE
				chart.write("pie-yearlydiv");   
			})(data.months_in_year);
			
			(function(data){
				var chart = new AmCharts.AmSerialChart();
				chart.dataProvider = data;
				chart.categoryField = "k";
				chart.marginRight = 0;chart.marginTop = 0; 
				//chart.autoMarginOffset = 0;
				// the following two lines makes chart 3D
				chart.depth3D = 20;chart.angle = 30;
				// AXES // category
				var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
				categoryAxis.inside = true;categoryAxis.gridCount = data.length;categoryAxis.autoGridCount = false;
				// value
				var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Result";chart.addValueAxis(valueAxis);
				// GRAPH            
				var graph = new AmCharts.AmGraph();
				graph.valueField = "v";
				graph.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");
				graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
				chart.addGraph(graph);
				// WRITE
				chart.write("pie-social");   
			})(data.social);
			
			(function(data){
				var	networkLpie = new AmCharts.AmPieChart();
				networkLpie.dataProvider = data;
				networkLpie.titleField = "k";
				networkLpie.valueField = "v";
				networkLpie.outlineColor = "#FFFFFF";
				networkLpie.outlineAlpha = 0.8;
				networkLpie.outlineThickness = 2;
				networkLpie.labelRadius = -30;
				networkLpie.labelText = "[[value]]";
				networkLpie.startDuration = 0;
				// this makes the chart 3D
				networkLpie.depth3D = 15;
				networkLpie.angle = 30;
				networkLpie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	networkLlegend = new AmCharts.AmLegend();
				networkLlegend.position = "bottom";
				networkLlegend.borderAlpha = 0;
				networkLlegend.horizontalGap = 10;
				networkLlegend.switchType = "x"; // or v
				networkLlegend.valueText = "";
				networkLpie.addLegend(networkLlegend);
				// WRITE
				networkLpie.write("pie-chart");
			})(data.pages);
			
			(function(data){
				var	networkLpie = new AmCharts.AmPieChart();
				networkLpie.dataProvider = data;
				networkLpie.titleField = "k";
				networkLpie.valueField = "v";
				networkLpie.outlineColor = "#FFFFFF";
				networkLpie.outlineAlpha = 0.8;
				networkLpie.outlineThickness = 2;
				networkLpie.labelRadius = -30;
				networkLpie.labelText = "[[value]]";
				networkLpie.startDuration = 0;
				// this makes the chart 3D
				networkLpie.depth3D = 15;
				networkLpie.angle = 30;
				networkLpie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	networkLlegend = new AmCharts.AmLegend();
				networkLlegend.position = "bottom";
				networkLlegend.borderAlpha = 0;
				networkLlegend.horizontalGap = 10;
				networkLlegend.switchType = "x"; // or v
				networkLlegend.valueText = "";
				networkLpie.addLegend(networkLlegend);
				// WRITE
				networkLpie.write("pie-device");
			})(data.device_type);
			
			(function(data){
				var	networkLpie = new AmCharts.AmPieChart();
				networkLpie.dataProvider = data;
				networkLpie.titleField = "k";
				networkLpie.valueField = "v";
				networkLpie.outlineColor = "#FFFFFF";
				networkLpie.outlineAlpha = 0.8;
				networkLpie.outlineThickness = 2;
				networkLpie.labelRadius = -30;
				networkLpie.labelText = "[[value]]";
				networkLpie.startDuration = 0;
				// this makes the chart 3D
				networkLpie.depth3D = 15;
				networkLpie.angle = 30;
				networkLpie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	networkLlegend = new AmCharts.AmLegend();
				networkLlegend.position = "bottom";
				networkLlegend.borderAlpha = 0;
				networkLlegend.horizontalGap = 10;
				networkLlegend.switchType = "x"; // or v
				networkLlegend.valueText = "";
				networkLpie.addLegend(networkLlegend);
				// WRITE
				networkLpie.write("pie-mobdiv");
			})(data.mobile_devices);
			
			(function(data){
				var	networkLpie = new AmCharts.AmPieChart();
				networkLpie.dataProvider = data;
				networkLpie.titleField = "k";
				networkLpie.valueField = "v";
				networkLpie.outlineColor = "#FFFFFF";
				networkLpie.outlineAlpha = 0.8;
				networkLpie.outlineThickness = 2;
				networkLpie.labelRadius = -30;
				networkLpie.labelText = "[[value]]";
				networkLpie.startDuration = 0;
				// this makes the chart 3D
				networkLpie.depth3D = 15;
				networkLpie.angle = 30;
				networkLpie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");

				var	networkLlegend = new AmCharts.AmLegend();
				networkLlegend.position = "bottom";
				networkLlegend.borderAlpha = 0;
				networkLlegend.horizontalGap = 10;
				networkLlegend.switchType = "x"; // or v
				networkLlegend.valueText = "";
				networkLpie.addLegend(networkLlegend);
				// WRITE
				networkLpie.write("donut-chart");
			})(data.search_terms);
			
			(function(data){
				var chart = new AmCharts.AmSerialChart();
				chart.dataProvider = data;
				chart.categoryField = "k";
				chart.marginRight = 0;chart.marginTop = 0; 
				//chart.autoMarginOffset = 0;
				// the following two lines makes chart 3D
				chart.depth3D = 20;chart.angle = 30;
				// AXES // category
				var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
				categoryAxis.inside = true;categoryAxis.gridCount = data.length;categoryAxis.autoGridCount = false;
				// value
				var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Result";chart.addValueAxis(valueAxis);
				// GRAPH            
				var graph = new AmCharts.AmGraph();
				graph.valueField = "v";
				graph.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");
				graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
				chart.addGraph(graph);
				// WRITE
				chart.write("piedaysdiv");   
			})(data.last_seven);
		});
	}
	
	if($('#mliststats_sort').length > 0){
		
		$('#mliststats_sort').sortable({
			handle: '.panel-heading',
			placeholder: 'span2 well placeholder tile',
			update: function() {
				var v = '',ul = $('#mliststats_sort > div[data-order]');
				for(var i=0;i<ul.length;i++){
					v += ul.eq(i).data('order')+',';
					if(i == ul.length - 1){
						$.getJSON(window.location.origin + '/admin/api/update_setting?x=mlist_stats&v='+v.slice(0,-1),function(data){
							if(data.error != 0 ){
								sk.alert(data.message);
							}
						});
					}
				}
			}					
		});
		
		$.getJSON(window.location.origin+'/admin/api/getMasterlistStats',function(data){
			
			(function(data,categories,selected){
				
				var html = '';
				for(var i=0;i<categories.length;i++){
					html+='<option value="'+categories[i].id+'">'+categories[i].name+'</option>';
					if(i== categories.length-1){
						$('#mlist-category-select').html(html).val(selected);
					}
				}
				barChart("pie-cat",data,'country','count');
			})(data.category_country,data.category_list,data.selected_cat);
			barChart('pie-actors',data.actors,'country','count');
			barChart('pie-singer',data.singers,'country','count');
			barChart('pie-athletes',data.athletes,'country','count');
			barChart('pie-politicians',data.politicians,'country','count');
			barChart('pie-gangsters',data.gangsters,'country','count');
			barChart('pie-authors',data.authors,'country','count');
			barChart('pie-properc',data.profiles_country,'name','count');
			barChart('pie-profilebyadmin',data.profiles_affiliate,'name','num');
			pieChart('pie-masterlists',data.profiles_type,'name','num');
			pieChart('pie-profilestatus',data.profiles_status,'name','num');
			pieChart('pie-suggestionkind',data.suggestions_type,'name','num');
			barChart('pie-suggestion',data.suggestions_country,'name','count');
			barChart('pie-profilebyadmin',data.profiles_affiliate,'name','num');
			barChart('pie-profilebob',data.birthdays_month,'month','num');
			barChart('pie-suggestiontopemail',data.suggestions_email,'email','num');
			var n = totalCalc(data.profiles_published_type_country);
			$('#mlistpublished .panel-title').html('&nbsp;Published By Type By Country <span class="pull-right" id="count-ttc">('+n+' total)</span>');
			barChart('pie-publish',data.profiles_published_type_country,'name','count');
		});
		
		
		function totalCalc(n)
		{
			var accumulator = 0;
			for(var i=0;i<n.length;i++){
				accumulator += n[i].count;
			}
			return accumulator;
		}
		
		$(document).on('change','#mlist-category-select',function(e){
			$.getJSON(window.location.origin+'/admin/api/switchMStatCategory?id='+$(this).val(),function(data){
				$('#pie-cat').html('');
				barChart("pie-cat",data,'country','count');
			});
		});
		
		$(document).on('change','#mlist-published-select',function(e){
			$.getJSON(window.location.origin+'/admin/api/switchMStatType?type_id='+$(this).val(),function(data){
				$('#pie-publish').html('');
				barChart("pie-publish",data,'name','count');
				var n = totalCalc(data);
				$('#mlistpublished .panel-title').html('&nbsp;Published By Type By Country <span class="pull-right" id="count-ttc">('+n+' total)</span>');
			});
		});
	}
	
	setTimeout(function(){
		$('.marquee').marquee({
			showSpeed:1000, //speed of drop down animation
			scrollSpeed: 10, //lower is faster
			yScroll: 'bottom',  // scroll direction on y-axis 'top' for down or 'bottom' for up
			direction: 'left', //scroll direction 'left' or 'right'
			pauseSpeed: 1000, // pause before scroll start in milliseconds
			pauseOnHover: true,
			duplicated: true  //continuous true or false
		});	
	},500);
	
	$('.clock-div').clock({
		'timestamp': parseFloat( $('.clock-div').data('time') ) * 1000,
		//'calendar': "false",
		"seconds":"false"
	});
	
	
	//file browser
	$('.file-menu li *').on('click',function(){
		$('.open .dropdown-toggle').dropdown('toggle');
	});
	
	$(document).on('change','.file-input :input[type="file"]',function(){
		var src = $(this).get(0).files[0].name || 'No File Selected';
		parent = $(this).parent().parent().parent();
		parent.find('.file-selected-view').html(src);
		parent.find('input[type="hidden"]').val('');
	});
	
	$(document).on('click','.file-browser-open',function(e){
		e.preventDefault();
		$('#file-browser')
		  .data('type',$(this).data('type'))
		  .data('target',$(this).data('target'))
		  .modal('show');
		return false;
	})

	$('#file-browser')
	.on('shown.bs.modal', function(){
		var type0 = $(this).data('type');
		type = type0.charAt(0).toUpperCase() + type0.slice(1);
		$(this).find('.modal-title').html('Browse Server '+type+'s');
		$.getJSON(window.location.origin+'/admin/api/browseFiles?type='+type0,function(data){
			for(var i=0;i<data.message.length;i++){
				var r = data.message[i];
				$('.img-selector').append('<div class="col-xs-3"><a href="#" class="imgselect" data-src="'+window.location.origin+'/uploads/'+r.src+'"><img src="'+window.location.origin+'/uploads/'+r.thumb+'" class="img-responsive"></a></div>');
			}
		});
	})
	.on('hidden.bs.modal', function(){
		$(this)
		.removeAttr('data-type')
		.removeAttr('data-target')
		.find('.img-selector').html('');
		$('#file-finish').prop('disabled',true);
	});
	

	$(document).on('click','.imgselect',function(e){
		e.preventDefault();
		$('.imgselect').removeClass('active');
		$(this).addClass('active');
		$('#file-finish').prop('disabled',false);
		return false;
	});
	
	$(document).on('click','#file-finish:enabled',function(e){
		var target = $('#file-browser').data('target');
		var selected = $('#file-browser').find('.imgselect.active');
		var src = selected.data('src');
		parent = $(target).val(src).parent();
		parent.find('.file-selected-view').html(src);
		parent.find('[type="file"]').val('');
		$('#file-browser').modal('hide');
	});
	
});


function pieChart(element,data,cat_field,count_field)
{
	var	upie = new AmCharts.AmPieChart();
	upie.dataProvider = data;
	upie.titleField = cat_field;
	upie.valueField = count_field;
upie.outlineColor = "#FFFFFF";
	upie.outlineAlpha = 0.8;
	upie.outlineThickness = 2;
	upie.labelRadius = -30;
	upie.labelText = "[[value]]";
	upie.startDuration = 0;
	// this makes the chart 3D
	upie.depth3D = 15;
	upie.angle = 30;
	upie.colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");
	
var	ulegend = new AmCharts.AmLegend();
	ulegend.position = "bottom";
	ulegend.borderAlpha = 0;
	ulegend.horizontalGap = 10;
	ulegend.switchType = "x"; // or v
	ulegend.valueText = "";
	upie.addLegend(ulegend);
	// WRITE
	upie.write(element);
}


function barChart(element,data,cat_field,count_field,color_field)
{
	chart = new AmCharts.AmSerialChart();
	chart.dataProvider = data;
	chart.categoryField = cat_field;
	chart.marginRight = 0;
	chart.marginTop = 0; 
	chart.depth3D = 20;chart.angle = 30;
	// AXES // category
	var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
	categoryAxis.inside = true;categoryAxis.gridCount = data.length;categoryAxis.autoGridCount = false;
	// value
	var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Result";chart.addValueAxis(valueAxis);
	// GRAPH            
	var graph = new AmCharts.AmGraph();
	graph.valueField = count_field;
	if(color_field){
		graph.colorField = color_field;
	}else{
		graph.colorField = "color";
		var tmp = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09,#F77979,#89FFFF,#D089FF,#965A15,#107ABC,#10BC4A,#BC104F,#C405FF,#DCEF9B,#759AB7".split(",");
		for(var i=0;i<data.length;i++){
			data[i].color = tmp[i];
		}
	}
	graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
	chart.addGraph(graph);
	// WRITE
	chart.write(element); 
}