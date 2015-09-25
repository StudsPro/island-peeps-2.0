/*!
 * Product:        Social - Premium Responsive Admin Template
 * Version:        2.1.3
 * Copyright:      2015 cesarlab.com
 * License:        http://themeforest.net/licenses
 * Live Preview:   http://go.cesarlab.com/SocialAdminTemplate2
 * Purchase:       http://go.cesarlab.com/PurchaseSocial2
 */
if (typeof jQuery === 'undefined') { throw new Error('Social\'s JavaScript requires jQuery'); }

var Tables;

Tables = (function($) {
  "use strict";

  /* */
  var bootstrap3Example, dataTableExample, initDynamicTables, initResponsiveTables, isRTL, xEditableExample;
  isRTL = function() {
    if ($("html").attr("dir") === "rtl") {
      return true;
    } else {
      return false;
    }
  };

  /* */
  initResponsiveTables = function() {
    $('#foo-table').footable();
  };

  /* */
  initDynamicTables = function() {
    bootstrap3Example();
    xEditableExample();
    dataTableExample();
  };

  /* */
  xEditableExample = function() {
    var countries;
    $.fn.editable.defaults.url = "https://posttestserver.com/post.php?dir=cesarlab";
    $.fn.editable.validate = function(value) {
      if ($.trim(value) === "") {
        return "This field is required";
      }
    };
    $("#enable").click(function() {
      $("#user .editable").editable("toggleDisabled");
    });
    $("#username").editable({
      rtl: true,
      type: "text",
      pk: 1,
      name: "username",
      title: "Enter username"
    });
    $("#firstname").editable({
      validate: function(value) {
        if ($.trim(value) === "") {
          return "This field is required";
        }
      }
    });
    $("#sex").editable({
      rtl: true,
      prepend: "not selected",
      source: [
        {
          value: 1,
          text: "Male"
        }, {
          value: 2,
          text: "Female"
        }
      ],
      display: function(value, sourceData) {
        var colors, elem;
        colors = {
          "": "gray",
          1: "green",
          2: "blue"
        };
        elem = $.grep(sourceData, function(o) {
          return o.value === value;
        });
        if (elem.length) {
          $(this).text(elem[0].text).css("color", colors[value]);
        } else {
          $(this).empty();
        }
      }
    });
    $("#status").editable();
    $("#group").editable({
      showbuttons: false,
      source: ["Alabama", "Alaska", "Arizona", "Arkansas", "California"]
    });
    $("#vacation").editable({
      datepicker: {
        todayBtn: "linked"
      }
    });
    $("#dob").editable();
    $("#event").editable({
      placement: "right",
      combodate: {
        firstItem: "name"
      }
    });
    $("#meeting_start").editable({
      format: "yyyy-mm-dd hh:ii",
      viewformat: "dd/mm/yyyy hh:ii",
      validate: function(v) {
        if (v && v.getDate() === 10) {
          return "Day cant be 10!";
        }
      },
      datetimepicker: {
        todayBtn: "linked",
        weekStart: 1
      }
    });
    $("#comments").editable({
      showbuttons: "bottom"
    });
    $("#note").editable();
    $("#pencil").click(function(e) {
      e.stopPropagation();
      e.preventDefault();
      $("#note").editable("toggle");
    });
    $("#state").editable({
      source: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]
    });
    $("#fruits").editable({
      pk: 1,
      limit: 3,
      source: [
        {
          value: 1,
          text: "banana"
        }, {
          value: 2,
          text: "peach"
        }, {
          value: 3,
          text: "apple"
        }, {
          value: 4,
          text: "watermelon"
        }, {
          value: 5,
          text: "orange"
        }
      ]
    });
    $("#tags").editable({
      inputclass: "input-large",
      select2: {
        tags: ["html", "javascript", "css", "ajax"],
        tokenSeparators: [",", " "]
      }
    });
    countries = [];
    $.each({
      BD: "Bangladesh",
      BE: "Belgium",
      BF: "Burkina Faso",
      BG: "Bulgaria",
      BA: "Bosnia and Herzegovina",
      BB: "Barbados",
      WF: "Wallis and Futuna",
      BL: "Saint Bartelemey",
      BM: "Bermuda",
      BN: "Brunei Darussalam",
      BO: "Bolivia",
      BH: "Bahrain",
      BI: "Burundi",
      BJ: "Benin",
      BT: "Bhutan",
      JM: "Jamaica",
      BV: "Bouvet Island",
      BW: "Botswana",
      WS: "Samoa",
      BR: "Brazil",
      BS: "Bahamas",
      JE: "Jersey",
      BY: "Belarus",
      O1: "Other Country",
      LV: "Latvia",
      RW: "Rwanda",
      RS: "Serbia",
      TL: "Timor-Leste",
      RE: "Reunion",
      LU: "Luxembourg",
      TJ: "Tajikistan",
      RO: "Romania",
      PG: "Papua New Guinea",
      GW: "Guinea-Bissau",
      GU: "Guam",
      GT: "Guatemala",
      GS: "South Georgia and the South Sandwich Islands",
      GR: "Greece",
      GQ: "Equatorial Guinea",
      GP: "Guadeloupe",
      JP: "Japan",
      GY: "Guyana",
      GG: "Guernsey",
      GF: "French Guiana",
      GE: "Georgia",
      GD: "Grenada",
      GB: "United Kingdom",
      GA: "Gabon",
      SV: "El Salvador",
      GN: "Guinea",
      GM: "Gambia",
      GL: "Greenland",
      GI: "Gibraltar",
      GH: "Ghana",
      OM: "Oman",
      TN: "Tunisia",
      JO: "Jordan",
      HR: "Croatia",
      HT: "Haiti",
      HU: "Hungary",
      HK: "Hong Kong",
      HN: "Honduras",
      HM: "Heard Island and McDonald Islands",
      VE: "Venezuela",
      PR: "Puerto Rico",
      PS: "Palestinian Territory",
      PW: "Palau",
      PT: "Portugal",
      SJ: "Svalbard and Jan Mayen",
      PY: "Paraguay",
      IQ: "Iraq",
      PA: "Panama",
      PF: "French Polynesia",
      BZ: "Belize",
      PE: "Peru",
      PK: "Pakistan",
      PH: "Philippines",
      PN: "Pitcairn",
      TM: "Turkmenistan",
      PL: "Poland",
      PM: "Saint Pierre and Miquelon",
      ZM: "Zambia",
      EH: "Western Sahara",
      RU: "Russian Federation",
      EE: "Estonia",
      EG: "Egypt",
      TK: "Tokelau",
      ZA: "South Africa",
      EC: "Ecuador",
      IT: "Italy",
      VN: "Vietnam",
      SB: "Solomon Islands",
      EU: "Europe",
      ET: "Ethiopia",
      SO: "Somalia",
      ZW: "Zimbabwe",
      SA: "Saudi Arabia",
      ES: "Spain",
      ER: "Eritrea",
      ME: "Montenegro",
      MD: "Moldova, Republic of",
      MG: "Madagascar",
      MF: "Saint Martin",
      MA: "Morocco",
      MC: "Monaco",
      UZ: "Uzbekistan",
      MM: "Myanmar",
      ML: "Mali",
      MO: "Macao",
      MN: "Mongolia",
      MH: "Marshall Islands",
      MK: "Macedonia",
      MU: "Mauritius",
      MT: "Malta",
      MW: "Malawi",
      MV: "Maldives",
      MQ: "Martinique",
      MP: "Northern Mariana Islands",
      MS: "Montserrat",
      MR: "Mauritania",
      IM: "Isle of Man",
      UG: "Uganda",
      TZ: "Tanzania, United Republic of",
      MY: "Malaysia",
      MX: "Mexico",
      IL: "Israel",
      FR: "France",
      IO: "British Indian Ocean Territory",
      FX: "France, Metropolitan",
      SH: "Saint Helena",
      FI: "Finland",
      FJ: "Fiji",
      FK: "Falkland Islands (Malvinas)",
      FM: "Micronesia, Federated States of",
      FO: "Faroe Islands",
      NI: "Nicaragua",
      NL: "Netherlands",
      NO: "Norway",
      NA: "Namibia",
      VU: "Vanuatu",
      NC: "New Caledonia",
      NE: "Niger",
      NF: "Norfolk Island",
      NG: "Nigeria",
      NZ: "New Zealand",
      NP: "Nepal",
      NR: "Nauru",
      NU: "Niue",
      CK: "Cook Islands",
      CI: "Cote d'Ivoire",
      CH: "Switzerland",
      CO: "Colombia",
      CN: "China",
      CM: "Cameroon",
      CL: "Chile",
      CC: "Cocos (Keeling) Islands",
      CA: "Canada",
      CG: "Congo",
      CF: "Central African Republic",
      CD: "Congo, The Democratic Republic of the",
      CZ: "Czech Republic",
      CY: "Cyprus",
      CX: "Christmas Island",
      CR: "Costa Rica",
      CV: "Cape Verde",
      CU: "Cuba",
      SZ: "Swaziland",
      SY: "Syrian Arab Republic",
      KG: "Kyrgyzstan",
      KE: "Kenya",
      SR: "Suriname",
      KI: "Kiribati",
      KH: "Cambodia",
      KN: "Saint Kitts and Nevis",
      KM: "Comoros",
      ST: "Sao Tome and Principe",
      SK: "Slovakia",
      KR: "Korea, Republic of",
      SI: "Slovenia",
      KP: "Korea, Democratic People's Republic of",
      KW: "Kuwait",
      SN: "Senegal",
      SM: "San Marino",
      SL: "Sierra Leone",
      SC: "Seychelles",
      KZ: "Kazakhstan",
      KY: "Cayman Islands",
      SG: "Singapore",
      SE: "Sweden",
      SD: "Sudan",
      DO: "Dominican Republic",
      DM: "Dominica",
      DJ: "Djibouti",
      DK: "Denmark",
      VG: "Virgin Islands, British",
      DE: "Germany",
      YE: "Yemen",
      DZ: "Algeria",
      US: "United States",
      UY: "Uruguay",
      YT: "Mayotte",
      UM: "United States Minor Outlying Islands",
      LB: "Lebanon",
      LC: "Saint Lucia",
      LA: "Lao People's Democratic Republic",
      TV: "Tuvalu",
      TW: "Taiwan",
      TT: "Trinidad and Tobago",
      TR: "Turkey",
      LK: "Sri Lanka",
      LI: "Liechtenstein",
      A1: "Anonymous Proxy",
      TO: "Tonga",
      LT: "Lithuania",
      A2: "Satellite Provider",
      LR: "Liberia",
      LS: "Lesotho",
      TH: "Thailand",
      TF: "French Southern Territories",
      TG: "Togo",
      TD: "Chad",
      TC: "Turks and Caicos Islands",
      LY: "Libyan Arab Jamahiriya",
      VA: "Holy See (Vatican City State)",
      VC: "Saint Vincent and the Grenadines",
      AE: "United Arab Emirates",
      AD: "Andorra",
      AG: "Antigua and Barbuda",
      AF: "Afghanistan",
      AI: "Anguilla",
      VI: "Virgin Islands, U.S.",
      IS: "Iceland",
      IR: "Iran, Islamic Republic of",
      AM: "Armenia",
      AL: "Albania",
      AO: "Angola",
      AN: "Netherlands Antilles",
      AQ: "Antarctica",
      AP: "Asia/Pacific Region",
      AS: "American Samoa",
      AR: "Argentina",
      AU: "Australia",
      AT: "Austria",
      AW: "Aruba",
      IN: "India",
      AX: "Aland Islands",
      AZ: "Azerbaijan",
      IE: "Ireland",
      ID: "Indonesia",
      UA: "Ukraine",
      QA: "Qatar",
      MZ: "Mozambique"
    }, function(k, v) {
      countries.push({
        id: k,
        text: v
      });
    });
    $("#country").editable({
      source: countries,
      select2: {
        width: 200,
        placeholder: "Select country",
        allowClear: true
      }
    });
    return $("#user .editable").on("hidden", function(e, reason) {
      var $next;
      if (reason === "save" || reason === "nochange") {
        $next = $(this).closest("tr").next().find(".editable");
        if ($("#autoopen").is(":checked")) {
          setTimeout((function() {
            $next.editable("show");
          }), 300);
        } else {
          $next.focus();
        }
      }
    });
  };

  /* */
  dataTableExample = function() {
    var data_columns, data_tables;
    data_tables = [
      {
        "DT_RowId": "row_1",
        "engine": "Trident",
        "browser": "Internet Explorer 4.0",
        "platform": "Win 95+",
        "version": "4",
        "grade": "X"
      }, {
        "DT_RowId": "row_2",
        "engine": "Trident",
        "browser": "Internet Explorer 5.0",
        "platform": "Win 95+",
        "version": "5",
        "grade": "C"
      }, {
        "DT_RowId": "row_3",
        "engine": "Trident",
        "browser": "Internet Explorer 5.5",
        "platform": "Win 95+",
        "version": "5.5",
        "grade": "A"
      }, {
        "DT_RowId": "row_4",
        "engine": "Trident",
        "browser": "Internet Explorer 6",
        "platform": "Win 98+",
        "version": "6",
        "grade": "A"
      }, {
        "DT_RowId": "row_5",
        "engine": "Trident",
        "browser": "Internet Explorer 7",
        "platform": "Win XP SP2+",
        "version": "7",
        "grade": "A"
      }, {
        "DT_RowId": "row_6",
        "engine": "Trident",
        "browser": "AOL browser (AOL desktop)",
        "platform": "Win XP",
        "version": "6",
        "grade": "A"
      }, {
        "DT_RowId": "row_7",
        "engine": "Gecko",
        "browser": "Firefox 1.0",
        "platform": "Win 98+ / OSX.2+",
        "version": "1.7",
        "grade": "A"
      }, {
        "DT_RowId": "row_8",
        "engine": "Gecko",
        "browser": "Firefox 1.5",
        "platform": "Win 98+ / OSX.2+",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_9",
        "engine": "Gecko",
        "browser": "Firefox 2.0",
        "platform": "Win 98+ / OSX.2+",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_10",
        "engine": "Gecko",
        "browser": "Firefox 3.0",
        "platform": "Win 2k+ / OSX.3+",
        "version": "1.9",
        "grade": "A"
      }, {
        "DT_RowId": "row_11",
        "engine": "Gecko",
        "browser": "Camino 1.0",
        "platform": "OSX.2+",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_12",
        "engine": "Gecko",
        "browser": "Camino 1.5",
        "platform": "OSX.3+",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_13",
        "engine": "Gecko",
        "browser": "Netscape 7.2",
        "platform": "Win 95+ / Mac OS 8.6-9.2",
        "version": "1.7",
        "grade": "A"
      }, {
        "DT_RowId": "row_14",
        "engine": "Gecko",
        "browser": "Netscape Browser 8",
        "platform": "Win 98SE+",
        "version": "1.7",
        "grade": "A"
      }, {
        "DT_RowId": "row_15",
        "engine": "Gecko",
        "browser": "Netscape Navigator 9",
        "platform": "Win 98+ / OSX.2+",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_16",
        "engine": "Gecko",
        "browser": "Mozilla 1.0",
        "platform": "Win 95+ / OSX.1+",
        "version": "1",
        "grade": "A"
      }, {
        "DT_RowId": "row_17",
        "engine": "Gecko",
        "browser": "Mozilla 1.1",
        "platform": "Win 95+ / OSX.1+",
        "version": "1.1",
        "grade": "A"
      }, {
        "DT_RowId": "row_18",
        "engine": "Gecko",
        "browser": "Mozilla 1.2",
        "platform": "Win 95+ / OSX.1+",
        "version": "1.2",
        "grade": "A"
      }, {
        "DT_RowId": "row_19",
        "engine": "Gecko",
        "browser": "Mozilla 1.3",
        "platform": "Win 95+ / OSX.1+",
        "version": "1.3",
        "grade": "A"
      }, {
        "DT_RowId": "row_20",
        "engine": "Gecko",
        "browser": "Mozilla 1.4",
        "platform": "Win 95+ / OSX.1+",
        "version": "1.4",
        "grade": "A"
      }, {
        "DT_RowId": "row_21",
        "engine": "Gecko",
        "browser": "Mozilla 1.5",
        "platform": "Win 95+ / OSX.1+",
        "version": "1.5",
        "grade": "A"
      }, {
        "DT_RowId": "row_22",
        "engine": "Gecko",
        "browser": "Mozilla 1.6",
        "platform": "Win 95+ / OSX.1+",
        "version": "1.6",
        "grade": "A"
      }, {
        "DT_RowId": "row_23",
        "engine": "Gecko",
        "browser": "Mozilla 1.7",
        "platform": "Win 98+ / OSX.1+",
        "version": "1.7",
        "grade": "A"
      }, {
        "DT_RowId": "row_24",
        "engine": "Gecko",
        "browser": "Mozilla 1.8",
        "platform": "Win 98+ / OSX.1+",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_25",
        "engine": "Gecko",
        "browser": "Seamonkey 1.1",
        "platform": "Win 98+ / OSX.2+",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_26",
        "engine": "Gecko",
        "browser": "Epiphany 2.20",
        "platform": "Gnome",
        "version": "1.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_27",
        "engine": "Webkit",
        "browser": "Safari 1.2",
        "platform": "OSX.3",
        "version": "125.5",
        "grade": "A"
      }, {
        "DT_RowId": "row_28",
        "engine": "Webkit",
        "browser": "Safari 1.3",
        "platform": "OSX.3",
        "version": "312.8",
        "grade": "A"
      }, {
        "DT_RowId": "row_29",
        "engine": "Webkit",
        "browser": "Safari 2.0",
        "platform": "OSX.4+",
        "version": "419.3",
        "grade": "A"
      }, {
        "DT_RowId": "row_30",
        "engine": "Webkit",
        "browser": "Safari 3.0",
        "platform": "OSX.4+",
        "version": "522.1",
        "grade": "A"
      }, {
        "DT_RowId": "row_31",
        "engine": "Webkit",
        "browser": "OmniWeb 5.5",
        "platform": "OSX.4+",
        "version": "420",
        "grade": "A"
      }, {
        "DT_RowId": "row_32",
        "engine": "Webkit",
        "browser": "iPod Touch / iPhone",
        "platform": "iPod",
        "version": "420.1",
        "grade": "A"
      }, {
        "DT_RowId": "row_33",
        "engine": "Webkit",
        "browser": "S60",
        "platform": "S60",
        "version": "413",
        "grade": "A"
      }, {
        "DT_RowId": "row_34",
        "engine": "Presto",
        "browser": "Opera 7.0",
        "platform": "Win 95+ / OSX.1+",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_35",
        "engine": "Presto",
        "browser": "Opera 7.5",
        "platform": "Win 95+ / OSX.2+",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_36",
        "engine": "Presto",
        "browser": "Opera 8.0",
        "platform": "Win 95+ / OSX.2+",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_37",
        "engine": "Presto",
        "browser": "Opera 8.5",
        "platform": "Win 95+ / OSX.2+",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_38",
        "engine": "Presto",
        "browser": "Opera 9.0",
        "platform": "Win 95+ / OSX.3+",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_39",
        "engine": "Presto",
        "browser": "Opera 9.2",
        "platform": "Win 88+ / OSX.3+",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_40",
        "engine": "Presto",
        "browser": "Opera 9.5",
        "platform": "Win 88+ / OSX.3+",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_41",
        "engine": "Presto",
        "browser": "Opera for Wii",
        "platform": "Wii",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_42",
        "engine": "Presto",
        "browser": "Nokia N800",
        "platform": "N800",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_43",
        "engine": "Presto",
        "browser": "Nintendo DS browser",
        "platform": "Nintendo DS",
        "version": "8.5",
        "grade": "C"
      }, {
        "DT_RowId": "row_44",
        "engine": "KHTML",
        "browser": "Konqureror 3.1",
        "platform": "KDE 3.1",
        "version": "3.1",
        "grade": "C"
      }, {
        "DT_RowId": "row_45",
        "engine": "KHTML",
        "browser": "Konqureror 3.3",
        "platform": "KDE 3.3",
        "version": "3.3",
        "grade": "A"
      }, {
        "DT_RowId": "row_46",
        "engine": "KHTML",
        "browser": "Konqureror 3.5",
        "platform": "KDE 3.5",
        "version": "3.5",
        "grade": "A"
      }, {
        "DT_RowId": "row_47",
        "engine": "Tasman",
        "browser": "Internet Explorer 4.5",
        "platform": "Mac OS 8-9",
        "version": null,
        "grade": "X"
      }, {
        "DT_RowId": "row_48",
        "engine": "Tasman",
        "browser": "Internet Explorer 5.1",
        "platform": "Mac OS 7.6-9",
        "version": "1",
        "grade": "C"
      }, {
        "DT_RowId": "row_49",
        "engine": "Tasman",
        "browser": "Internet Explorer 5.2",
        "platform": "Mac OS 8-X",
        "version": "1",
        "grade": "C"
      }, {
        "DT_RowId": "row_50",
        "engine": "Misc",
        "browser": "NetFront 3.1",
        "platform": "Embedded devices",
        "version": null,
        "grade": "C"
      }, {
        "DT_RowId": "row_51",
        "engine": "Misc",
        "browser": "NetFront 3.4",
        "platform": "Embedded devices",
        "version": null,
        "grade": "A"
      }, {
        "DT_RowId": "row_52",
        "engine": "Misc",
        "browser": "Dillo 0.8",
        "platform": "Embedded devices",
        "version": null,
        "grade": "X"
      }, {
        "DT_RowId": "row_53",
        "engine": "Misc",
        "browser": "Links",
        "platform": "Text only",
        "version": null,
        "grade": "X"
      }, {
        "DT_RowId": "row_54",
        "engine": "Misc",
        "browser": "Lynx",
        "platform": "Text only",
        "version": null,
        "grade": "X"
      }, {
        "DT_RowId": "row_55",
        "engine": "Misc",
        "browser": "IE Mobile",
        "platform": "Windows Mobile 6",
        "version": null,
        "grade": "C"
      }, {
        "DT_RowId": "row_56",
        "engine": "Misc",
        "browser": "PSP browser",
        "platform": "PSP",
        "version": null,
        "grade": "C"
      }, {
        "DT_RowId": "row_57",
        "engine": "Other browsers",
        "browser": "All others",
        "platform": "",
        "version": null,
        "grade": "U"
      }
    ];
    data_columns = [
      {
        mData: "browser"
      }, {
        mData: "engine"
      }, {
        mData: "platform"
      }, {
        mData: "version",
        sClass: "center"
      }, {
        mData: "grade",
        sClass: "center"
      }
    ];
    $("#datatable1").dataTable({
      "sDom": "<'row' <'col-xs-6'l><'col-xs-6'f>r>t<'row'<'col-xs-6'i><'col-xs-6'p> >",
      "aaSorting": [[2, "desc"]],
      "aaData": data_tables,
      "aoColumns": data_columns
    });
  };

  /* */
  bootstrap3Example = function() {

    /*
    I don't recommend using this plugin on large tables, I just wrote it to make the demo useable. It will work fine for smaller tables
    but will likely encounter performance issues on larger tables.
    
    <input type="text" class="form-control" id="dev-table-filter" data-action="filter" data-filters="#dev-table" placeholder="Filter Developers" />
    $(input-element).filterTable()
    
    The important attributes are 'data-action="filter"' and 'data-filters="#table-selector"'
     */
    var i;
    $.fn.extend({
      filterTable: function() {
        return this.each(function() {
          $(this).on("keyup", function(e) {
            var $rows, $target, $this, col_count, no_results, search, target;
            $(".filterTable_no_results").remove();
            $this = $(this);
            search = $this.val().toLowerCase();
            target = $this.attr("data-filters");
            $target = $(target);
            $rows = $target.find("tbody tr");
            if (search === "") {
              $rows.show();
            } else {
              $rows.each(function() {
                $this = $(this);
                if ($this.text().toLowerCase().indexOf(search) === -1) {
                  $this.hide();
                } else {
                  $this.show();
                }
              });
              if ($target.find("tbody tr:visible").size() === 0) {
                col_count = $target.find("tr").first().find("td").size();
                no_results = $("<tr class=\"filterTable_no_results\"><td colspan=\"" + col_count + "\">No results found</td></tr>");
                $target.find("tbody").append(no_results);
              }
            }
          });
        });
      }
    });
    $("[data-action=\"filter\"]").filterTable();
    $("[data-action=\"filter\"]").filterTable();
    $(".container").on("click", ".panel-heading span.filter", function(e) {
      var $panel, $this;
      $this = $(this);
      $panel = $this.parents(".panel");
      $panel.find(".panel-body").slideToggle();
      if ($this.css("display") !== "none") {
        $panel.find(".panel-body input").focus();
      }
    });
    $("[data-toggle=\"tooltip\"]").bootstrapTooltip();
    i = 9;
    $("#add_row").click(function() {
      $("#task" + i).html("<td>" + (i + 1) + "</td><td><input name='task" + i + "' type='text' placeholder='Task' class='form-control input-md'  /> </td><td><input  name='assignee" + i + "' type='text' placeholder='Assignee'  class='form-control input-md'></td><td><input  name='status" + i + "' type='text' placeholder='Status'  class='form-control input-md'></td>");
      $("#task-table").append("<tr id=\"task" + (i + 1) + "\"></tr>");
      i++;
    });
    $("#delete_row").click(function() {
      if (i > 1) {
        $("#task" + (i - 1)).html("");
        i--;
      }
    });
  };
  return {
    initResponsiveTables: initResponsiveTables,
    initDynamicTables: initDynamicTables
  };
})(jQuery);
