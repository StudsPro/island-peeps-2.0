/*!
 * Product:        Social - Premium Responsive Admin Template
 * Version:        2.1.3
 * Copyright:      2015 cesarlab.com
 * License:        http://themeforest.net/licenses
 * Live Preview:   http://go.cesarlab.com/SocialAdminTemplate2
 * Purchase:       http://go.cesarlab.com/PurchaseSocial2
 */
if (typeof jQuery === 'undefined') { throw new Error('Social\'s JavaScript requires jQuery'); }

var Charts;

Charts = (function($) {
  "use strict";
  var init, initAdvancedChart, initBarChart, initDonutPieChart, initEasyPieChart, initGageExamples, initLiveChart, initSparklineExamples;
  init = function() {
    initAdvancedChart();
    initLiveChart();
    initEasyPieChart();
    initSparklineExamples();
    initGageExamples();
    initBarChart();
    return initDonutPieChart();
  };
  initAdvancedChart = function() {

    /* Graph Example */
    var data, options, placeholder, plot;
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
  };

  /* Live Chart */
  initLiveChart = function() {
    var data, getRandomData, options, plot, totalPoints, update, updateInterval;
    data = [];
    totalPoints = 300;
    getRandomData = function() {
      var i, prev, res, y;
      if (data.length > 0) {
        data = data.slice(1);
      }
      while (data.length < totalPoints) {
        prev = (data.length > 0 ? data[data.length - 1] : 50);
        y = prev + Math.random() * 10 - 5;
        if (y < 0) {
          y = 0;
        }
        if (y > 100) {
          y = 100;
        }
        data.push(y);
      }
      res = [];
      i = 0;
      while (i < data.length) {
        res.push([i, data[i]]);
        ++i;
      }
      return res;
    };
    update = function() {
      plot.setData([getRandomData()]);
      plot.draw();
      return setTimeout(update, updateInterval);
    };
    data = [];
    totalPoints = 300;
    updateInterval = 30;
    $("#updateInterval").val(updateInterval).change(function() {
      var v;
      v = $(this).val();
      if (v && !isNaN(+v)) {
        updateInterval = +v;
        if (updateInterval < 1) {
          updateInterval = 1;
        }
        if (updateInterval > 2000) {
          updateInterval = 2000;
        }
        return $(this).val("" + updateInterval);
      }
    });
    options = {
      series: {
        shadowSize: 0,
        lines: {
          fill: true
        }
      },
      yaxis: {
        min: 0,
        max: 100
      },
      xaxis: {
        show: false
      },
      grid: {
        backgroundColor: {
          colors: ["#fff", "#e4f4f4"]
        }
      }
    };
    plot = $.plot($("#live-chart"), [getRandomData()], options);
    update();
  };

  /* Easy Pie Chart */
  initEasyPieChart = function() {
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
  };

  /* Sparklines Examples */
  initSparklineExamples = function() {
    var data_sparlines;
    data_sparlines = [2, 4, 5, 3, 6, 7, 6, 5, 4, 8, 4, 5, 6, 8, 10, 9];
    $(".sparklines-examples .sparkline1").sparkline(data_sparlines, {
      type: "bar",
      width: "100%",
      height: "90",
      barWidth: 7,
      barColor: "#647aab"
    });
    $(".sparklines-examples .sparkline2").sparkline(data_sparlines, {
      type: "line",
      width: "100%",
      height: "90",
      fillColor: "#dff0d8",
      spotRadius: 2.5,
      drawNormalOnTop: true
    });
    $(".sparklines-examples .sparkline3").sparkline([4, 3, 1, 2], {
      type: "pie",
      height: "90",
      sliceColors: ["#647AAB", "#62c462", "#5bc0de", "#ee5f5b"],
      borderWidth: 1,
      borderColor: "#e5e5e5"
    });
  };

  /* gage Examples */
  initGageExamples = function() {
    var g1, g2, g3;
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
  };

  /*Bar Chart */
  initBarChart = function() {
    var bars, d1, d2, d3, i, lines, plotWithOptions, stack, steps;
    d1 = [];
    i = 0;
    while (i <= 10) {
      d1.push([i, parseInt(Math.random() * 30)]);
      i += 1;
    }
    d2 = [];
    i = 0;
    while (i <= 10) {
      d2.push([i, parseInt(Math.random() * 30)]);
      i += 1;
    }
    d3 = [];
    i = 0;
    while (i <= 10) {
      d3.push([i, parseInt(Math.random() * 30)]);
      i += 1;
    }
    stack = 0;
    bars = true;
    lines = false;
    steps = false;
    plotWithOptions = function() {
      return $.plot($("#bar-chart"), [d1, d2, d3], {
        series: {
          stack: stack,
          lines: {
            show: lines,
            fill: true,
            steps: steps
          },
          bars: {
            show: bars,
            barWidth: 0.6
          }
        }
      });
    };
    plotWithOptions();
    $(".stackControls a").click(function(e) {
      e.preventDefault();
      stack = ($(this).text() === "With stacking" ? true : null);
      plotWithOptions();
    });
    $(".graphControls label").click(function(e) {
      e.preventDefault();
      bars = $(this).text().indexOf("Bars") !== -1;
      lines = $(this).text().indexOf("Lines") !== -1;
      steps = $(this).text().indexOf("steps") !== -1;
      plotWithOptions();
    });
  };

  /*Donut & Pie Chart */
  initDonutPieChart = function() {
    var data, i, series;
    data = [];
    series = Math.floor(Math.random() * 3) + 2;
    i = 0;
    while (i < series) {
      data[i] = {
        label: "Series" + (i + 1),
        data: Math.floor(Math.random() * 100) + 1
      };
      i++;
    }
    $.plot($("#pie-chart"), data, {
      series: {
        pie: {
          show: true,
          radius: 1,
          label: {
            show: true,
            radius: 3 / 4,
            formatter: function(label, series) {
              return "<div style=\"font-size:8pt;text-align:center;padding:2px;color:white;\">" + label + "<br/>" + Math.round(series.percent) + "%</div>";
            },
            background: {
              opacity: 0.5,
              color: "#000"
            }
          }
        }
      },
      legend: {
        show: false
      }
    });
    $.plot($("#donut-chart"), data, {
      series: {
        pie: {
          innerRadius: 0.5,
          show: true
        }
      }
    });
  };
  return {
    init: init
  };
})(jQuery);
