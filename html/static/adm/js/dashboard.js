/**
 * Product:        Social - Premium Responsive Admin Template
 * Version:        v1.5.1
 * Copyright:      2013 CesarLab.com
 * License:        http://themeforest.net/licenses
 * Live Preview:   http://go.cesarlab.com/SocialAdminTemplate
 * Purchase:       http://go.cesarlab.com/PurchaseSocial
 *
*/

var Dashboard;

Dashboard = (function($) {
  "use strict";
  var config, handleBigChatExample, handleDateRangePicker, handleFlotExample, handleFullCalendarExample, handleIndicatorsStuff, handleJustGageExample, handleScrollFeedsList, handleSparklineExample, handleVMapExample, init, isRTLVersion;
  config = {
    urlAvatar: 'none'
  };
  /**/

  isRTLVersion = function() {
    return $("body").hasClass('rtl');
  };
  /**/

  init = function(options) {
    $.extend(config, options);
    handleVMapExample();
    handleJustGageExample();
    handleFlotExample();
    handleFullCalendarExample();
    handleSparklineExample();
    handleBigChatExample();
    handleScrollFeedsList();
    handleDateRangePicker();
    handleIndicatorsStuff();
  };
  /* vMap Example*/

  handleVMapExample = function() {
    var options, renderVmap, vMap, vMapParent;
    vMap = $("#vmap-world");
    vMapParent = vMap.parent();
    options = {
      map: "world_en",
      backgroundColor: "#fff",
      color: "#ccc",
      hoverOpacity: 0.7,
      selectedColor: "#666666",
      enableZoom: true,
      showTooltip: true,
      values: sample_data,
      scaleColors: ["#C8EEFF", "#006491"],
      normalizeFunction: "polynomial",
      onLabelShow: function(e, el, code){
	if(sample_data[code])
       el.html(el.html()+' ('+sample_data[code]+')');
    }
    };
    renderVmap = function(selector, options) {
      selector.vectorMap(options);
    };
    vMap.width("100%");
    renderVmap(vMap, options);
  };
  /* JustGage Examples*/

  handleJustGageExample = function() {

  };
  /* Flot Example*/

  handleFlotExample = function() {
 
   /* var   i, options, placeholder, plot, plotAccordingToChoices;
   
    options = {
      series: {
        lines: {
          show: true
        },
        points: {
          show: true
        }
      },
      legend: {
        noColumns: 2
      },
      xaxis: {
       mode: "time",
                tickSize: [1, "day"],
                tickLength: 0,
                axisLabel: "2012",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 10
      },
      yaxis: {
        min: 0
      },
      selection: {
        mode: "x"
      }
    };
    placeholder = $("#demo-plot");
    placeholder.bind("plotselected", function(event, ranges) {});
    plot = $.plot(placeholder, datasets, options);
    plotAccordingToChoices = function() {
      var data;
      data = void 0;
      data = [];
      if (data.length > 0) {
        return $.plot("#demo-plot", data, {
          yaxis: {
            min: 0
          },
          xaxis: {
            tickDecimals: 0
          }
        });
      }
    };
    i = 0;
    $.each(datasets, function(key, val) {
      val.color = i;
      return ++i;
    });
    plot.setSelection({
      xaxis: {
        from: 1994,
        to: 1995
      }
    });*/
  };
  /* Full Calendar Example*/

  handleFullCalendarExample = function() {
    var d, date, header, m, y;
    date = new Date();
    d = date.getDate();
    m = date.getMonth();
    y = date.getFullYear();
    header = {};
    if (isRTLVersion()) {
      header.right = "next,prev";
      header.center = "title";
      header.left = "agendaDay,agendaWeek,month";
    } else {
      header.left = "prev,next";
      header.center = "title";
      header.right = "month,agendaWeek,agendaDay";
    }
    $("#demo-calendar1").fullCalendar({
      isRTL: isRTLVersion(),
      header: header,
      editable: true,
      events: [
        {
          title: "All Day Event",
          start: new Date(y, m, 1)
        }, {
          title: "Long Event",
          start: new Date(y, m, d - 5),
          end: new Date(y, m, d - 2)
        }, {
          id: 999,
          title: "Repeating",
          start: new Date(y, m, d - 3, 16, 0),
          allDay: false
        }, {
          id: 999,
          title: "Repeating",
          start: new Date(y, m, d + 4, 16, 0),
          allDay: false
        }, {
          title: "Meeting",
          start: new Date(y, m, d, 10, 30),
          allDay: false
        }, {
          title: "Lunch",
          start: new Date(y, m, d, 12, 0),
          end: new Date(y, m, d, 14, 0),
          allDay: false
        }, {
          title: "Birthday Party",
          start: new Date(y, m, d + 1, 19, 0),
          end: new Date(y, m, d + 1, 22, 30),
          allDay: false
        }, {
          title: "Click for Google",
          start: new Date(y, m, 28),
          end: new Date(y, m, 29),
          url: "#http://google.com/"
        }
      ]
    });
    /* END Full Calendar Example*/

  };
  /* Sparkline Example*/

  handleSparklineExample = function() {
    $("#compositebar").sparkline([50, 60, 62, 35, 40, 50, 38, 38, 38, 40, 60, 38, 50, 60, 38, 45, 62, 38, 38, 40, 30], {
      type: "line",
      width: "100px",
      height: "29px",
      drawNormalOnTop: false
    });
  };
  /**/

  handleBigChatExample = function() {
    var chatWindow;
    chatWindow = $(".chat-messages-list .content");
    /* Activate the scrollbar for the chat window*/

    chatWindow.slimScroll({
      railVisible: true,
      alwaysVisible: true,
      start: "bottom",
      height: '400px',
      position: isRTLVersion() ? "left" : "right"
    });
  };
  /* Activate the scrollbar for the feed lists*/

  handleScrollFeedsList = function() {
    $("#feeds .content").slimScroll({
      height: '300px',
      position: isRTLVersion() ? "left" : "right"
    });
  };
  /* Date Range Picker*/

  handleDateRangePicker = function() {
    var calendarOpens, dashboardReportRange;
    if (isRTLVersion()) {
      return;
    } else {
      calendarOpens = 'right';
    }
    dashboardReportRange = $("#dashboard-reportrange");
    dashboardReportRange.daterangepicker({
      opens: calendarOpens,
      ranges: {
        Today: [new Date(), new Date()],
        Yesterday: [moment().subtract("days", 1), moment().subtract("days", 1)],
        "Last 7 Days": [moment().subtract("days", 6), new Date()],
        "Last 30 Days": [moment().subtract("days", 29), new Date()],
        "This Month": [moment().startOf("month"), moment().endOf("month")],
        "Last Month": [moment().subtract("month", 1).startOf("month"), moment().subtract("month", 1).endOf("month")]
      },
      format: "MM/DD/YYYY",
      separator: " to ",
      startDate: moment().subtract("days", 29),
      endDate: new Date(),
      minDate: "01/01/2012",
      maxDate: "12/31/2013",
      locale: {
        applyLabel: "Submit",
        fromLabel: "From",
        toLabel: "To",
        customRangeLabel: "Custom Range",
        daysOfWeek: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
        monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        firstDay: 1
      },
      showWeekNumbers: true,
      buttonClasses: ["btn-danger"],
      dateLimit: false
    }, function(start, end) {
      $("#dashboard-reportrange span").html(start.format("MMMM D, YYYY") + " - " + end.format("MMMM D, YYYY"));
    });
    $("#dashboard-reportrange span").html(moment().subtract("days", 29).format("MMMM D, YYYY") + " - " + moment().format("MMMM D, YYYY"));
  };
  /**/


  return {
    init: init
  };
})(jQuery);
