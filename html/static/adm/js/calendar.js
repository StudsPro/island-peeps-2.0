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
		
		$('#calendar-full').fullCalendar({
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
		   }
		});
		$(document).on('click','.fc-button-agendaDay,.fc-button-prev,.fc-button-next,.fc-button-today',function(){
			var div = $('.fc-view > div > div');
			setTimeout(function(){
				div.scrollTop(0);
			},500);
		});
	}
	
	

});