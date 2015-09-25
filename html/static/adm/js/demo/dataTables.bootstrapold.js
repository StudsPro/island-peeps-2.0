/*!
 * Product:        Social - Premium Responsive Admin Template
 * Version:        2.1.3
 * Copyright:      2015 cesarlab.com
 * License:        http://themeforest.net/licenses
 * Live Preview:   http://go.cesarlab.com/SocialAdminTemplate2
 * Purchase:       http://go.cesarlab.com/PurchaseSocial2
 */
if (typeof jQuery === 'undefined') { throw new Error('Social\'s JavaScript requires jQuery'); }

$.extend(true, $.fn.dataTable.defaults, {
  sDom: "<'row'<'col-xs-6'l><'col-xs-6'f>r>t<'row'<'col-xs-6'i><'col-xs-6'p>>",
  sPaginationType: "bootstrap",
  oLanguage: {
    sLengthMenu: "_MENU_ records per page"
  }
});

$.extend($.fn.dataTableExt.oStdClasses, {
  sWrapper: "dataTables_wrapper form-inline",
  sFilter: "pull-right",
  sLength: ""
});

$.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings) {
  return {
    iStart: oSettings._iDisplayStart,
    iEnd: oSettings.fnDisplayEnd(),
    iLength: oSettings._iDisplayLength,
    iTotal: oSettings.fnRecordsTotal(),
    iFilteredTotal: oSettings.fnRecordsDisplay(),
    iPage: (oSettings._iDisplayLength === -1 ? 0 : Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength)),
    iTotalPages: (oSettings._iDisplayLength === -1 ? 0 : Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength))
  };
};

$.extend($.fn.dataTableExt.oPagination, {
  bootstrap: {
    fnInit: function(oSettings, nPaging, fnDraw) {
      var els, fnClickHandler, oLang;
      oLang = oSettings.oLanguage.oPaginate;
      fnClickHandler = function(e) {
        e.preventDefault();
        if (oSettings.oApi._fnPageChange(oSettings, e.data.action)) {
          fnDraw(oSettings);
        }
      };
      $(nPaging).append("<ul class=\"pagination\" style=\"margin: 0\">" + "<li class=\"prev disabled\"><a href=\"#\">&larr; " + oLang.sPrevious + "</a></li>" + "<li class=\"next disabled\"><a href=\"#\">" + oLang.sNext + " &rarr; </a></li>" + "</ul>");
      els = $("a", nPaging);
      $(els[0]).bind("click.DT", {
        action: "previous"
      }, fnClickHandler);
      $(els[1]).bind("click.DT", {
        action: "next"
      }, fnClickHandler);
    },
    fnUpdate: function(oSettings, fnDraw) {
      var an, i, iEnd, iHalf, iListLength, iStart, ien, j, oPaging, sClass;
      iListLength = 10;
      oPaging = oSettings.oInstance.fnPagingInfo();
      an = oSettings.aanFeatures.p;
      i = void 0;
      ien = void 0;
      j = void 0;
      sClass = void 0;
      iStart = void 0;
      iEnd = void 0;
      iHalf = Math.floor(iListLength / 2);
      if (oPaging.iTotalPages < iListLength) {
        iStart = 1;
        iEnd = oPaging.iTotalPages;
      } else if (oPaging.iPage <= iHalf) {
        iStart = 1;
        iEnd = iListLength;
      } else if (oPaging.iPage >= (oPaging.iTotalPages - iHalf)) {
        iStart = oPaging.iTotalPages - iListLength + 1;
        iEnd = oPaging.iTotalPages;
      } else {
        iStart = oPaging.iPage - iHalf + 1;
        iEnd = iStart + iListLength - 1;
      }
      i = 0;
      ien = an.length;
      while (i < ien) {
        $("li:gt(0)", an[i]).filter(":not(:last)").remove();
        j = iStart;
        while (j <= iEnd) {
          sClass = (j === oPaging.iPage + 1 ? "class=\"active\"" : "");
          $("<li " + sClass + "><a href=\"#\">" + j + "</a></li>").insertBefore($("li:last", an[i])[0]).bind("click", function(e) {
            e.preventDefault();
            oSettings._iDisplayStart = (parseInt($("a", this).text(), 10) - 1) * oPaging.iLength;
            fnDraw(oSettings);
          });
          j++;
        }
        if (oPaging.iPage === 0) {
          $("li:first", an[i]).addClass("disabled");
        } else {
          $("li:first", an[i]).removeClass("disabled");
        }
        if (oPaging.iPage === oPaging.iTotalPages - 1 || oPaging.iTotalPages === 0) {
          $("li:last", an[i]).addClass("disabled");
        } else {
          $("li:last", an[i]).removeClass("disabled");
        }
        i++;
      }
    }
  }
});
