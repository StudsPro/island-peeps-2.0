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

  /* Google Maps */
  var map1, map2, map3, path, polygon;
  map1 = GMaps.createPanorama({
    el: "#panorama",
    lat: 42.3455,
    lng: -71.0983
  });
  map2 = new GMaps({
    div: '#basic_map',
    lat: -12.043333,
    lng: -77.028333
  });
  map3 = new GMaps({
    div: "#advanced_map",
    lat: -12.043333,
    lng: -77.028333
  });
  path = [[-12.040397656836609, -77.03373871559225], [-12.040248585302038, -77.03993927003302], [-12.050047116528843, -77.02448169303511], [-12.044804866577001, -77.02154422636042]];
  polygon = map3.drawPolygon({
    paths: path,
    strokeColor: "#BBD8E9",
    strokeOpacity: 1,
    strokeWeight: 3,
    fillColor: "#BBD8E9",
    fillOpacity: 0.6
  });
  map3.addMarker({
    lat: -12.043333,
    lng: -77.03,
    title: "Lima",
    details: {
      database_id: 42,
      author: "HPNeo"
    },
    click: function(e) {
      if (console.log) {
        console.log(e);
      }
      alert("You clicked in this marker");
    }
  });
  map3.addMarker({
    lat: -12.042,
    lng: -77.028333,
    title: "Marker with InfoWindow",
    infoWindow: {
      content: "<p>HTML Content</p>"
    }
  });

  /* Vector Map Example */
  $("#vmap1").vectorMap({
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
  $('#vmap2').vectorMap({
    map: 'usa_en',
    backgroundColor: "#647aab",
    color: '#ffffff',
    hoverColor: '#999999',
    selectedColor: '#666666',
    enableZoom: true,
    showTooltip: true,
    selectedRegion: 'MO'
  });
});
