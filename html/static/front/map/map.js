window.map = document.getElementById('map');

function mapRun()
{
	$.mapbox.load();
}

$.mapbox = {
	active: false;
	data: null,
	interval: null,
	unload: function(){
		clearInterval(map.interval);
		$.mapbox.interval = null;
		window.map.remove();
		$.mapbox.active = true;
	},
	load: function(){
		
		window.map = L.mapbox.map('map', 'derrickstuds.imab7m7e').setView([15, -74], 5);
		$.mapbox.quiet();
	},
	quiet: function(){
		var myLayer = L.mapbox.featureLayer().addTo(window.map);
		$.mapbox.active = true;
		
		var geoJson = [
			{type: 'Feature',
				geometry: {type: 'Point',coordinates: [-77.387695,15.284185 ]},
				properties: {
					title: 'Bermuda',
					'change' : '7','lat' : '32.307800','long': '-64.750500',
					"icon": {
						"iconUrl": "http://islandpeeps.com/new_admin_panel/images/bermuda.png",
						"iconSize": [63, 42],
						"iconAnchor": [50, 50],"popupAnchor": [0, -55],"className": "dot",
					}
			}
			},
			{
				type: 'Feature',
				geometry: {type: 'Point',coordinates: [-77.629394,12.033948 ]},
				properties: {title: 'Hawaii','change' : '7','lat' : '19.896766','long': '-155.582782',"icon": {"iconUrl": "http://islandpeeps.com/new_admin_panel/images/hawaii.png","iconSize": [63, 42],"iconAnchor": [50, 50],"popupAnchor": [0, -55],"className": "dot",}}
			},

		];
		myLayer.setGeoJSON(geoJson);
		
		// Set a custom icon on each marker based on feature properties.
		myLayer.on('layeradd', function(e) {
			
			var marker = e.layer,feature = marker.feature;marker.setIcon(L.icon(feature.properties.icon));
		
		});
		
		
		myLayer.on('click', function(e) { 
			e.layer.closePopup();e.layer.unbindPopup();var feature = e.layer.feature;
			if(feature.properties.change){
				$.mapbox.unload();
				window.map = L.mapbox.map('map', 'derrickstuds.imab7m7e').setView([feature.properties.lat , feature.properties.long], 7);
				$.mapbox.quiet();
				showInfo(feature);
				return false;   
			}
		});
		
		
		myLayer.on('mouseover',showInfo);
		myLayer.on('mouseout',hideInfo);
		// Clear the tooltip when map is clicked or moved.
		window.map.on('move click',hideInfo);
		
		function showInfo(e)
		{
			var feature= e.layer.feature;
			var content = '<div class="info map_details_con"><ul class="deta_map"><li><strong>Name : <span style="color:#206BEF;">' + feature.properties.title + '<span></strong><li><li><strong>Capital</strong> : ' + stripslashes(feature.properties.capital) + '</li><li><strong>Population</strong> : ' + feature.properties.population + '</li><li><strong>National Dish</strong> : ' + feature.properties.national_dish + '</li><li>' + feature.properties.description + '</li></ul><ul class="deta_map datali"></span><li id="chart2" style="width:100%; height:190px;"></li><li id="chart3" style="height:190px; width:100%;"></li> </ul></div>';
			$('.map-info').html(content).fadeIn(50);
		}
		
		function hideInfo()
		{
			$('.map-info').fadeOut(50).html('');
		}
		
		

		

		var geojson = { 
			type: 'LineString', coordinates: [] 
		};
		var geojson1 = { 
			type: 'LineString', coordinates: [] 
		};
		
		var start = [-77.387695,15.284185 ];
		var momentum = [1.2637195,1.7023615];
		var start1 =  [-77.629394,12.033948 ];
		var momentum1 = [7.7953388,0.7862818];

		for (var i = 0; i < 11; i++) {
			geojson.coordinates.push(start.slice());
			geojson1.coordinates.push(start1.slice());
			start[0] += momentum[0];
			start[1] += momentum[1];
			start1[0] -= momentum1[0];
			start1[1] += momentum1[1];
		}

		// Add this generated geojson object to the map.
		L.geoJson(geojson).addTo(window.map);
		L.geoJson(geojson1).addTo(window.map);

		// Create a counter with a value of 0.
		var j = 0;
		// Create a marker and add it to the map.
		var marker = L.marker([0, 0], {icon: L.mapbox.marker.icon({'marker-color': '#f86767'})}).addTo(map);
		var marker2 = L.marker([0, 0], {icon: L.mapbox.marker.icon({'marker-color': '#f86767'})}).addTo(map);
		
		$.mapbox.interval = setInterval(function(){
			j = 0;tick();}, 2000
		);

		function tick() {
			// Set the marker to be at the same point as one
			// of the segments or the line.
			marker.setLatLng(L.latLng(
			geojson.coordinates[j][1],
			geojson.coordinates[j][0]));
			marker2.setLatLng(L.latLng(
			geojson1.coordinates[j][1],
			geojson1.coordinates[j][0]));
			if (++j < geojson.coordinates.length) setTimeout(tick, 100);
		}
	}
};


$(function(){
	$(document).on('click','.map-reset',function(e){
		e.preventDefault();
		map.unload();
		map.load();
		return false;
	});	
});