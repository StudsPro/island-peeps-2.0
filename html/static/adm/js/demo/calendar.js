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

  /* Full Calendar Example */
  var addEvent, initExternalEvents;
  initExternalEvents = function() {
    $(".list-group .list-group-item").each(function() {
      var eventObject;
      eventObject = {
        title: $.trim($(this).text())
      };
      $(this).data("eventObject", eventObject);
      return $(this).draggable({
        zIndex: 999,
        revert: true,
        revertDuration: 0
      });
    });
  };
  addEvent = function() {
    var titleEvent, titleEventVal;
    titleEvent = $("#event-input");
    titleEventVal = (!titleEvent.val() || 0 === titleEvent.val().length ? "Untitle Event" : titleEvent.val());
    titleEvent.val("");
    $(".list-group").append("<div class='list-group-item list-group-item-warning ui-draggable'>" + titleEventVal + "</div>");
    return initExternalEvents();
  };
  $("#add-event").click(function() {
    addEvent();
  });
  $("#add-event-form").submit(function() {
    addEvent();
    return false;
  });

  /* initialize the external events */
  $("#external-events a.list-group-item").each(function() {
    var eventObject;
    eventObject = {
      title: $.trim($(this).text())
    };
    $(this).data("eventObject", eventObject);
    $(this).draggable({
      zIndex: 999,
      revert: true,
      revertDuration: 0
    });
  });

  /* Initialize the calendar */
  $("#calendar").fullCalendar({
    isRTL: $("html").attr("dir") === "rtl" ? true : false,
    header: {
      left: "prev,next today",
      center: "title",
      right: "month,agendaWeek,agendaDay"
    },
    editable: true,
    droppable: true,
    drop: function(date, allDay) {
      var copiedEventObject, originalEventObject;
      originalEventObject = $(this).data("eventObject");
      copiedEventObject = $.extend({}, originalEventObject);
      copiedEventObject.start = date;
      copiedEventObject.allDay = allDay;
      $("#calendar").fullCalendar("renderEvent", copiedEventObject, true);
      if ($("#drop-remove").is(":checked")) {
        $(this).remove();
      }
    }
  });
});
