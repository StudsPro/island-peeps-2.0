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

  /* Graph Example */
  var d, data, data_sparlines, date, g1, g2, g3, m, map2, options, placeholder, plot, y;
  data = [
    {
      label: "United States",
      data: [[1990, 18.9], [1991, 18.7], [1992, 18.4], [1993, 19.3], [1994, 19.5], [1995, 19.3], [1996, 19.4], [1997, 20.2], [1998, 19.8], [1999, 19.9], [2000, 20.4], [2001, 20.1], [2002, 20.0], [2003, 19.8], [2004, 20.4]]
    }, {
      label: "Russia",
      data: [[1992, 13.4], [1993, 12.2], [1994, 10.6], [1995, 10.2], [1996, 10.1], [1997, 9.7], [1998, 9.5], [1999, 9.7], [2000, 9.9], [2001, 9.9], [2002, 9.9], [2003, 10.3], [2004, 10.5]]
    }, {
      label: "United Kingdom",
      data: [[1990, 10.0], [1991, 11.3], [1992, 9.9], [1993, 9.6], [1994, 9.5], [1995, 9.5], [1996, 9.9], [1997, 9.3], [1998, 9.2], [1999, 9.2], [2000, 9.5], [2001, 9.6], [2002, 9.3], [2003, 9.4], [2004, 9.79]]
    }, {
      label: "Germany",
      data: [[1990, 12.4], [1991, 11.2], [1992, 10.8], [1993, 10.5], [1994, 10.4], [1995, 10.2], [1996, 10.5], [1997, 10.2], [1998, 10.1], [1999, 9.6], [2000, 9.7], [2001, 10.0], [2002, 9.7], [2003, 9.8], [2004, 9.79]]
    }, {
      label: "Denmark",
      data: [[1990, 9.7], [1991, 12.1], [1992, 10.3], [1993, 11.3], [1994, 11.7], [1995, 10.6], [1996, 12.8], [1997, 10.8], [1998, 10.3], [1999, 9.4], [2000, 8.7], [2001, 9.0], [2002, 8.9], [2003, 10.1], [2004, 9.80]]
    }, {
      label: "Sweden",
      data: [[1990, 5.8], [1991, 6.0], [1992, 5.9], [1993, 5.5], [1994, 5.7], [1995, 5.3], [1996, 6.1], [1997, 5.4], [1998, 5.4], [1999, 5.1], [2000, 5.2], [2001, 5.4], [2002, 6.2], [2003, 5.9], [2004, 5.89]]
    }
  ];
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
      tickDecimals: 0
    },
    yaxis: {
      min: 0
    },
    selection: {
      mode: "x"
    }
  };
  placeholder = $("#graph-example");
  plot = $.plot(placeholder, data, options);
  plot.setSelection({
    xaxis: {
      from: 1993,
      to: 1997
    }
  });

  /* Vector Map Example */
  $("#vmap").vectorMap({
    map: "world_en",
    backgroundColor: null,
    color: "#ffffff",
    hoverOpacity: 0.7,
    selectedColor: "#666666",
    enableZoom: true,
    showTooltip: true,
    values: sample_data,
    scaleColors: ["#C8EEFF", "#006491"],
    normalizeFunction: "polynomial"
  });

  /* Easy Pie Charts Examples */
  $(".easy-pie-chart-examples .chart1").easyPieChart({
    barColor: "#3a87ad",
    onStep: function(from, to, percent) {
      $(this.el).find(".percent").text(Math.round(percent));
    }
  });
  $(".easy-pie-chart-examples .chart2").easyPieChart({
    barColor: "#b94a48",
    onStep: function(from, to, percent) {
      $(this.el).find(".percent").text(Math.round(percent));
    }
  });
  $(".easy-pie-chart-examples .chart3").easyPieChart({
    barColor: "#c09853",
    onStep: function(from, to, percent) {
      $(this.el).find(".percent").text(Math.round(percent));
    }
  });

  /* Sparklines Examples */
  data_sparlines = [2, 4, 5, 3, 6, 7, 6, 5, 4, 8, 4, 5, 6, 8, 10, 9];
  $("#sparklines-examples .sparkline1").sparkline(data_sparlines, {
    type: "bar",
    width: "100%",
    height: "90",
    barWidth: 7,
    barColor: "#647aab"
  });
  $("#sparklines-examples .sparkline2").sparkline(data_sparlines, {
    type: "line",
    width: "100%",
    height: "90",
    fillColor: "#dff0d8",
    spotRadius: 2.5,
    drawNormalOnTop: true
  });
  $("#sparklines-examples .sparkline3").sparkline([4, 3, 1, 2], {
    type: "pie",
    height: "90",
    sliceColors: ["#647AAB", "#62c462", "#5bc0de", "#ee5f5b"],
    borderWidth: 1,
    borderColor: "#e5e5e5"
  });

  /* gage Examples */
  g1 = new JustGage({
    id: "g1",
    value: getRandomInt(0, 1000),
    min: 0,
    max: 1000,
    relativeGaugeSize: true,
    donut: true
  });
  g2 = new JustGage({
    id: "g2",
    value: 72,
    min: 0,
    max: 100,
    gaugeWidthScale: 0.6,
    customSectors: [
      {
        color: "#ebccd1",
        lo: 0,
        hi: 25
      }, {
        color: "#d6e9c6",
        lo: 25,
        hi: 50
      }, {
        color: "#647aab",
        lo: 50,
        hi: 100
      }
    ],
    counter: true
  });
  g3 = new JustGage({
    id: "g3",
    value: getRandomInt(0, 100),
    min: 0,
    max: 100,
    label: "label",
    relativeGaugeSize: true,
    donut: true
  });

  /* Full Calendar Example */
  date = new Date();
  d = date.getDate();
  m = date.getMonth();
  y = date.getFullYear();
  $("#calendar-example").fullCalendar({
    isRTL: $("html").attr("dir") === "rtl" ? true : false,
    header: {
      left: "title",
      right: "prev,next,month,basicWeek,basicDay"
    },
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
        title: "Repeating Event",
        start: new Date(y, m, d - 3, 16, 0),
        allDay: false
      }, {
        id: 999,
        title: "Repeating Event",
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
        url: "http://google.com/"
      }
    ]
  });

  /* Panel */
  $(".panel-feed .scroll").slimScroll({
    height: '290px'
  });
  $(".panel-tasks .scroll").slimScroll({
    height: '240px'
  });
  $(".panel-chat .scroll").slimScroll({
    height: '240px',
    start: $('#chat_sender4')
  });

  /* Gmao Example */
  map2 = new GMaps({
    div: '#basic_map',
    lat: -12.043333,
    lng: -77.028333
  });
  map2.addMarker({
    lat: -12.042,
    lng: -77.028333,
    title: "Here she is"
  });

  /* PNotify Example */
  new PNotify({
    title: "Welcome!",
    text: "<strong>Social</strong> is a responsive admin & frontend template compatible with Bootstrap v3 and latest jQuery versions.",
    type: "info",
    animation: "fade"
  });
});
