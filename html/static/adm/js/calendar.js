$(function(){
	
    $('#calendar-min').fullCalendar({
        // put your options and callbacks here
		events: window.location.origin+'/admin/api/calendar_min',
		eventRender: function(event, element, view) {
			element.draggable = false;
			if (event.allDay === 'true') {
				event.allDay = true;
			} else {
				event.allDay = false;
			}
	   }
    });
	
	if($('#calendar-full').length > 0){
		var date = new Date();
		
		var year = date.getFullYear();
		
		var month = $('#calendar-full').data('month');
		if(typeof month === "undefined" || month === false){
			month = date.getMonth();
		}
		
		var calendar = $('#calendar-full').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: "month,agendaWeek,agendaDay",
			},
			year: year,
			month: month-1,
			// put your options and callbacks here
			events: window.location.origin+'/admin/api/calendar_full',
			eventRender: function(event, element, view) {
				element.draggable = false;
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
				if (title) 
				{
					var start = $.fullCalendar.formatDate(start, "yyyy-MM-dd HH:mm:ss");
					var end = $.fullCalendar.formatDate(end, "yyyy-MM-dd HH:mm:ss");
					$.ajax({
						url: window.location.origin+"/admin/api/custom_event",
						data: 'title='+ title+'&start='+ start +'&end='+ end  ,
						type: "POST",
						success: function(json){
							var data = JSON.parse(json);
							if(data.error == 1){
								sk.alert(data.message,'error');
							}else{
								calendar.fullCalendar('renderEvent', { title: title, start: start, end: end, allDay: true, className:'custday', backgroundColor: '#BBE2AE !important' },true );
								sk.alert('The event was successfully created','success');
							}
							calendar.fullCalendar('unselect');
						}
					});     
				}else{
					calendar.fullCalendar('unselect');
				}
			},
		});
		$(document).on('click','.fc-button-agendaDay,.fc-button-prev,.fc-button-next,.fc-button-today',function(){
			var div = $('.fc-view > div > div');
			setTimeout(function(){
				div.scrollTop(0);
			},500);
		});
	}
	
	

});