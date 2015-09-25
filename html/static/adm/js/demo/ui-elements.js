/*!
 * Product:        Social - Premium Responsive Admin Template
 * Version:        2.1.3
 * Copyright:      2015 cesarlab.com
 * License:        http://themeforest.net/licenses
 * Live Preview:   http://go.cesarlab.com/SocialAdminTemplate2
 * Purchase:       http://go.cesarlab.com/PurchaseSocial2
 */
if (typeof jQuery === 'undefined') { throw new Error('Social\'s JavaScript requires jQuery'); }

var UIElements;

UIElements = (function($) {
  "use strict";
  var initFileUploaders, initGeneralItems, initPanelsItems, initWYSIWYGEditors, initjQueryUIExamples;
  initGeneralItems = function() {

    /* General Items */
    $('button[data-toggle="tooltip"]').bootstrapTooltip({
      container: 'body'
    });
    $('button[data-toggle="popover"]').popover({
      container: 'body',
      title: 'Title'
    });
    $(".panel-feed .scroll").slimScroll({
      height: '290px'
    });
    $(".panel-chat .scroll").slimScroll({
      height: '240px',
      start: $('#chat_sender4')
    });

    /* PNotify Examples */
    $("#not-regular").click(function() {
      return new PNotify({
        title: 'Regular Notice',
        text: 'Check me out! I\'m a notice.'
      });
    });
    $("#not-iconless").click(function() {
      return new PNotify({
        title: 'No Icon Info',
        text: 'I have no icon.',
        type: 'info',
        icon: false
      });
    });
    $("#not-transparent").click(function() {
      return new PNotify({
        title: 'See Through Notice',
        text: 'No one ever pays attention to me. Why should they? I\'m practically invisible.',
        opacity: .8,
        type: 'error'
      });
    });
    $("#not-big").click(function() {
      return new PNotify({
        title: 'Big Notice',
        text: 'Check me out! I\'m tall and wide, even though my text isn\'t.',
        width: '400px',
        min_height: '200px',
        type: 'success'
      });
    });
  };

  /* Panels */
  initPanelsItems = function() {
    $("#panels-scollable .panel-body .scroll").slimScroll({
      height: '200px'
    });
  };

  /* WYSIWYGEditors */
  initWYSIWYGEditors = function() {
    $("#wysihtml5").wysihtml5();
  };

  /* Files Uploaders */
  initFileUploaders = function() {
    $("#demo-upload").dropzone({
      method: "POST",
      maxFilesize: 2,
      dictResponseError: "Error"
    });
    $("#fileupload").fileupload({
      url: "https://posttestserver.com/post.php?dir=cesarlab"
    });
    $("#fileupload").fileupload("option", "redirect", window.location.href.replace(/\/[^\/]*$/, "/cors/result.html?%s"));
    if (window.location.hostname === "blueimp.github.io") {
      $("#fileupload").fileupload("option", {
        url: "https://posttestserver.com/post.php?dir=cesarlab",
        disableImageResize: /Android(?!.*Chrome)|Opera/.test(window.navigator.userAgent),
        maxFileSize: 5000000,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
      });
      if ($.support.cors) {
        $.ajax({
          url: "https://posttestserver.com/post.php?dir=cesarlab",
          type: "HEAD"
        }).fail(function() {
          $("<div class=\"alert alert-danger\"/>").text("Upload server currently unavailable - " + new Date()).appendTo("#fileupload");
        });
      }
    } else {
      $("#fileupload").addClass("fileupload-processing");
      $.ajax({
        url: $("#fileupload").fileupload("option", "url"),
        dataType: "json",
        context: $("#fileupload")[0]
      }).always(function() {
        $(this).removeClass("fileupload-processing");
      }).done(function(result) {
        $(this).fileupload("option", "done").call(this, $.Event("done"), {
          result: result
        });
      });
    }
  };

  /* jQuery UI Examples */
  initjQueryUIExamples = function() {
    var hexFromRGB, refreshSwatch;
    $("#jquery-accordion").accordion({
      header: "h3"
    });

    /* Dialogs */

    /* Dialog Simple */
    $("#btn-dialog-simple").click(function() {
      $("#modal-simple").dialog("open");
      return false;
    });
    $("#modal-simple").dialog({
      autoOpen: false,
      width: 600,
      buttons: {
        Ok: function() {
          $(this).dialog("close");
        },
        Cancel: function() {
          $(this).dialog("close");
        }
      }
    });

    /* Dialog message */
    $("#btn-dialog-message").click(function() {
      $("#modal-message").dialog("open");
      return false;
    });
    $("#modal-message").dialog({
      autoOpen: false,
      modal: true,
      buttons: {
        Ok: function() {
          $(this).dialog("close");
        }
      }
    });

    /* Dialog multi button */
    $("#btn-dialog-button").click(function() {
      $("#modal-button").dialog("open");
      return false;
    });

    /* Dialog Button */
    $("#modal-button").dialog({
      autoOpen: false,
      width: 600,
      buttons: [
        {
          text: "Delete",
          click: function() {},
          "class": "ui-button-danger"
        }, {
          text: "Edit",
          click: function() {},
          "class": "ui-button-warning"
        }, {
          text: "other",
          click: function() {},
          "class": "ui-button-inverse"
        }, {
          text: "Close",
          click: function() {
            $(this).dialog("close");
          }
        }
      ]
    });

    /* Tabs */
    $("#tabs").tabs();

    /* Date Picker */
    $("#date-range .from").datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function(selectedDate) {
        $("#to").datepicker("option", "minDate", selectedDate);
      }
    });
    $("#date-range .to").datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      onClose: function(selectedDate) {
        $("#from").datepicker("option", "maxDate", selectedDate);
      }
    });

    /* ColorPicker */
    hexFromRGB = function(r, g, b) {
      var hex;
      hex = [r.toString(16), g.toString(16), b.toString(16)];
      $.each(hex, function(nr, val) {
        if (val.length === 1) {
          hex[nr] = "0" + val;
        }
      });
      return hex.join("").toUpperCase();
    };
    refreshSwatch = function() {
      var blue, green, hex, red;
      red = $("#colorpicker .red").slider("value");
      green = $("#colorpicker .green").slider("value");
      blue = $("#colorpicker .blue").slider("value");
      hex = hexFromRGB(red, green, blue);
      $("#colorpicker .swatch").css("background-color", "#" + hex);
    };
    $("#colorpicker .red, #colorpicker .green, #colorpicker .blue").slider({
      orientation: "horizontal",
      range: "min",
      max: 255,
      value: 127,
      slide: refreshSwatch,
      change: refreshSwatch
    });
    $("#colorpicker .red").slider("value", 255);
    $("#colorpicker .green").slider("value", 140);
    $("#colorpicker .blue").slider("value", 60);

    /* Sliders */
    $("#slider-range").slider({
      range: true,
      min: 0,
      max: 500,
      values: [75, 300],
      slide: function(event, ui) {
        $("#amount").val("$" + ui.values[0] + " - $" + ui.values[1]);
      }
    });
    $("#amount").val("$" + $("#slider-range").slider("values", 0) + " - $" + $("#slider-range").slider("values", 1));
    $("#slider-range-max").slider({
      range: "max",
      min: 1,
      max: 10,
      value: 2,
      slide: function(event, ui) {
        $("#amount2").val(ui.value);
      }
    });
    $("#amount2").val($("#slider-range-max").slider("value"));
    $("#slider").slider({
      value: 100,
      min: 0,
      max: 500,
      step: 50,
      slide: function(event, ui) {
        $("#amount3").val("$" + ui.value);
      }
    });
    $("#amount3").val("$" + $("#slider").slider("value"));
  };
  return {
    initGeneralItems: initGeneralItems,
    initWYSIWYGEditors: initWYSIWYGEditors,
    initPanelsItems: initPanelsItems,
    initFileUploaders: initFileUploaders,
    initjQueryUIExamples: initjQueryUIExamples
  };
})(jQuery);
