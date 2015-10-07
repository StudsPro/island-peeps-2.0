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

$(function(){
	var sampledata = null; 
	var userTypeChart = '<?= $userType ? 'yes' : ''?>';
	var pieDataUserType1 = [<?= rtrim($userType,',')?>];

	var deviceChart = '<?= $device ? 'yes' : ''?>';
	var pieDataDevice = [<?=rtrim($device,',')?>];

	var mobdivChart = '<?= $mobdiv ? 'yes' : ''?>';
	var pieDataMobileDevice = [<?=rtrim($mobdiv,',')?>];

	var sourceChart = '<?= $sourcemedium ? 'yes' : ''?>';
	var pieDataSource = [<?=rtrim($sourcemedium,',')?>];

	var keywordChart = '<?= $gkeyword ? 'yes' : ''?>';
	var pieDataKeyword = [<?=rtrim($gkeyword,',')?>];

	var socialChart = '<?= $socialtr ? 'yes' : ''?>';
	var pieDataSocial = [<?=rtrim($socialtr,',')?>];

	var countryChart = '<?= $countries ? 'yes' : ''?>';

	//var pieDataCountry = [<?=rtrim($countries,',')?>];
	var pieDataCountry = [<?=rtrim($countries,',')?>];


	//	var cityChart = '</?= $cities ? 'yes' : ''?>';
	//	var pieDataCity = [</?=$cities?>];

	var browsersChart = '<?= $browsers ? 'yes' : ''?>';
	var pieDataBrowsers = [<?=rtrim($browsers,',')?>];

	var osChart = '<?= $os ? 'yes' : ''?>';
	var pieDataOS = [<?=rtrim($os,',')?>];

	var networkLocationsChart = '<?= $networkLocations ? 'yes' : ''?>';
	var pieDataNetworkLocations = [<?=rtrim($networkLocations,',')?>];

	var screenResolutionsChart = '<?= $screenResolutions ? 'yes' : ''?>';
	var pieDataScreenResolutions = [<?=rtrim($screenResolutions,',')?>];

	var socialChart = '<?= $socialtr ? 'yes' : ''?>';
	var pieDataSocial = [<?=rtrim($socialtr,',')?>];

	var pageTrackChart = '<?= $pagetrackingviews ? 'yes' : ''?>';
	var chartDataPageTrack = [<?=rtrim($pagetrackingviews,',')?>];

	var propercChart = '<?= $visitspercountry ? 'yes' : ''?>';
	var pieDataproperc = [<?=rtrim($visitspercountry,',')?>];

	var profilepercChart = '<?= $profilepercountry ? 'yes' : ''?>';
	var pieDataprofileperc = [<?=rtrim($profilepercountry,',')?>];

	var pageTrackChart = '<?= $month_pagetrackingviews ? 'yes' : ''?>';
	var chartDataPageTrack = [<?=rtrim($month_pagetrackingviews,',')?>];

	if(profilepercChart){

	json = pieDataprofileperc;
	//			AmCharts.ready(function() {
	// SERIAL CHART
	chart = new AmCharts.AmSerialChart();
	chart.dataProvider = json;chart.categoryField = "country";chart.marginRight = 0;chart.marginTop = 0; 
	//chart.autoMarginOffset = 0;
	// the following two lines makes chart 3D
	chart.depth3D = 20;chart.angle = 30;
	// AXES // category
	var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
	categoryAxis.inside = true;categoryAxis.gridCount = json.length;categoryAxis.autoGridCount = false;
	// value
	var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Result";chart.addValueAxis(valueAxis);
	// GRAPH            
	var graph = new AmCharts.AmGraph();graph.valueField = "visits";
	graph.colorField = "color";
	graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
	chart.addGraph(graph);
	// WRITE
	chart.write("pie-profileperc");    

	}

	if(propercChart){

	json = pieDataproperc;
		//			AmCharts.ready(function() {
		// SERIAL CHART
		chart = new AmCharts.AmSerialChart();
		chart.dataProvider = json;chart.categoryField = "country";chart.marginRight = 0;chart.marginTop = 0; 
		//chart.autoMarginOffset = 0;
		// the following two lines makes chart 3D
		chart.depth3D = 20;chart.angle = 30;
		// AXES // category
		var categoryAxis = chart.categoryAxis;categoryAxis.labelRotation = 90;categoryAxis.gridPosition = "start";
		categoryAxis.inside = true;categoryAxis.gridCount = json.length;categoryAxis.autoGridCount = false;
		// value
		var valueAxis = new AmCharts.ValueAxis();valueAxis.title = "Visit";chart.addValueAxis(valueAxis);
		// GRAPH            
		var graph = new AmCharts.AmGraph();graph.valueField = "visits";
		graph.colorField = "color";
		graph.balloonText = "[[category]]: [[value]]";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1;
		chart.addGraph(graph);
		// WRITE
		chart.write("pie-visitsperc");    
	}
		
		
	var date = new Date();
	var d = date.getDate();
	var m = date.getMonth();
	var y = date.getFullYear();

	var calendar = $('#calendar1').fullCalendar({
		editable: true,
		header: {
			left: 'prev,next today',
			center: 'title',
			right: "month,agendaWeek,agendaDay",
		},
		events: "<?php echo $eventsurl;?>",
		// Convert the allDay from string to boolean
		eventRender: function(event, element, view) {
			if (event.type =='nd'){

				element.draggable = false;
			}
			if (event.allDay === 'true') {
				event.allDay = true;
			} else {
				event.allDay = false;
			}
		},
		selectable: true,
		selectHelper: true,
		select: function(start, end, allDay) {
			var title = prompt('Event Title:');

			if (title) {
				var start = $.fullCalendar.formatDate(start, "yyyy-MM-dd HH:mm:ss");
				var end = $.fullCalendar.formatDate(end, "yyyy-MM-dd HH:mm:ss");

				$.ajax({
					url:"<?=SITE_ADMIN_URL;?>calendar/add_events",
					data: 'title='+ title+'&start='+ start +'&end='+ end  ,
					type: "POST",
					success: function(json) {
					// alert(json);
					}
				});
				calendar.fullCalendar('renderEvent',
					{
					title: title,
					start: start,
					end: end,
					allDay: allDay
					},
					true // make the event "stick"
				);
			}
			calendar.fullCalendar('unselect');
		},
		editable: true,
		eventDrop: function(event, delta) {
			var start = $.fullCalendar.formatDate(event.start, "yyyy-MM-dd HH:mm:ss");
			var end = $.fullCalendar.formatDate(event.end, "yyyy-MM-dd HH:mm:ss");
			$.ajax({
				url: "<?=SITE_ADMIN_URL;?>calendar/update_events",
				data: 'title='+ event.title+'&start='+ start +'&end='+ end +'&id='+ event.id ,
				type: "POST",
				success: function(json) {
				// alert(json);
				}
			});
		},
		eventClick: function(event) {
			var decision = confirm("Do you really want to Delete?"); 
			if (decision) {
			$.ajax({
			type: "POST",
			url: "<?=SITE_ADMIN_URL;?>calendar/delete_events",

			data: "&id=" + event.id,
			type: "POST",
			success: function(json) {
			//alert("Updated Successfully");
			}
			});
			$('#calendar1').fullCalendar('removeEvents', event.id);

			} else {
			}
		},
		eventResize: function(event) {
			var start = $.fullCalendar.formatDate(event.start, "yyyy-MM-dd HH:mm:ss");
			var end = $.fullCalendar.formatDate(event.end, "yyyy-MM-dd HH:mm:ss");
			$.ajax({
				url: "<?=SITE_ADMIN_URL;?>calendar/update_events",
				data: 'title='+ event.title+'&start='+ start +'&end='+ end +'&id='+ event.id ,
				type: "POST",
				success: function(json) {
				//alert("Updated Successfully");
				}
			});
		}
	});
	
	$(".grid").sortable({
		tolerance: 'pointer',
		revert: 'invalid',
		placeholder: 'span2 well placeholder tile',
		forceHelperSize: true,
		update: function() {
			var dashmenuname =  Array(); 
			var dashmenuorder =  Array(); 
			var dashgetids="";
			var dli=0;
			$('.span2').each( function(e) {
				if($(this).attr('id')!= '') {
					dashgetids=$(this).attr('id');
					dashmenuname[dli] = dashgetids;  
					dashmenuorder[dli]= $(this).index() + 1;
					dli++;
				}
			});
			$.ajax({
				type: 'POST',
				cache: false,
				url:  '<?php echo base_url();?>admin/dashboard/dragdashboradmenu',
				data : {
				leftmenunames : dashmenuname,
				leftmenureorders : dashmenuorder
				},
				success: function(data){
					return false;
				}
			});  	
		}					
	});
})
