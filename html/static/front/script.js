Object.defineProperty(HTMLMediaElement.prototype, 'playing', {
    get: function(){
        return !!(this.currentTime > 0 && !this.paused && !this.ended && this.readyState > 2);
    }
});

window.onkeydown = function(e){
    if(e.keyCode == 32 && e.target == document.body) {
        e.preventDefault();
        return false;
    }
};

function responsiveApp()
{
	resizeMenu();
	var s = '';
	s += resizeAdVideos();
	s += resizeSlideVideos();
	$('#otf-style').html(s).detach().appendTo('body');
}

function resizeAdVideos()
{
	var tclass = '.ad-lg video';

	var w = window.innerWidth;
	var base = {
		w: 1445,
		s: 2.88,
		h: 400
	};
	if(w <= base.w){
		return;
	}
	var d = w/base.w;
	var scale = (base.s * d).toFixed(2);
	var height = base.h * d;
	return tclass+' {\r\nheight:'+height+'px;\r\ntransform:scaleX('+scale+');\r\n}\r\n';
}

function resizeSlideVideos()
{
	var tclass = '.slider .owl-carousel .item .video-slideup video';
	var h = window.innerHeight;
	var base = {
		h:635,//base width
		s:1.01,//base scale
		t:-61,//base top offset
	};
	if(h <= base.h){
		return;
	}
	//determine diff in % of w vs window w
	var d = h / base.h; 
	var scale = (base.s * d).toFixed(2);
	var offset = Math.ceil(Math.abs(base.t) * d);
	offset -= (offset * 2).toString();
	var height = Math.ceil(122 * d);
	return tclass+' {\r\ntop:'+offset+'px;\r\ntransform:scaleX('+scale+');\r\nheight:calc(100vh + '+height+'px);\r\n}\r\n';
}

function resizeMenu()
{
	var w = window.innerWidth;
	$('.menu .list').addClass('hide');
	if($('.menu').hasClass('translated')){
		w -= 200;
	}
	if(w >= 1366){
		$('.menu .list.s-1366').removeClass('hide');
	}
	else if(w >= 1242){
		$('.menu .list.s-1242').removeClass('hide');
	}
	else if(w >= 1000){
		$('.menu .list.s-1000').removeClass('hide');
	}
	else if(w >= 766){
		$('.menu .list.s-766').removeClass('hide');
	}
	else{
		$('.menu .list.s-mobile').removeClass('hide');
	}
}

function menuSetCurrent()
{
	var el2 = $('.menu').find('li').not('.active').find('[data-href*="'+location.pathname+'"]');
	if(el2.length > 0){
		if($('[name="xmobile-go"] option[value="'+location.pathname+'"]').length > 0){
			$('[name="xmobile-go"]').val(location.pathname);
		}
		$('.menu li').removeClass('active');
		el2.parent('li').addClass('active');
	}else{
		$('.menu li.active').removeClass('.active');
		if($('[name="xmobile-go"]').val() !== location.pathname){
			$('[name="xmobile-go"]').val('');
		}
	}
}

$.fn.getComputed = function(){
	return parseInt(this.css('width').replace(/px/,''));
};

function googleTranslateElementInit() {
	$('[data-language]').one('DOMSubtreeModified', function() {
	  flipLanguage(1);  
	});
	$('.menu').one('DOMSubtreeModified',function(){
		resizeMenu();
	});
	new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false}, 'google_translate_element');
	resizeMenu();
}

function flipLanguage(x)
{
	if(x == 1){
		var language = 'Spanish';
		var text = 'English';
		$('.menu').addClass('translated');
	}else{
		$('.menu').removeClass('translated');
		var language = 'English';
		var text = 'Español';
	}
	$('[data-language]').attr('data-selected',language).html(text);
}

/*map*/
window.map = document.getElementById('map');

$.mapbox = {
	active: false,
	data: null,
	interval: null,
	unload: function(){
		clearInterval(map.interval);
		$.mapbox.interval = null;
		window.map.remove();
		$.mapbox.active = true;
	},
	load: function(){
		window.map = L.mapbox.map('map', 'derrickstuds.imab7m7e',{
			accessToken: 'pk.eyJ1IjoiZGVycmlja3N0dWRzIiwiYSI6ImlSS2VHQW8ifQ.sFDiNJZ-s-N87fEDHniqHg'
		}).setView([15, -74], 5);
		window.map.touchZoom.disable();
		window.map.doubleClickZoom.disable();
		window.map.scrollWheelZoom.disable();
		$.mapbox.quiet();
	},
	draw: function(){
		var myLayer = L.mapbox.featureLayer().addTo(window.map);
		var myLayer2 = L.mapbox.featureLayer().addTo(window.map);
		var data = $.mapbox.data;
		var geoJson = [];
		var geoJson2 = [
			{type: 'Feature',
				geometry: {type: 'Point',coordinates: [-77.387695,15.284185 ]},
				properties: {
					title: 'Bermuda',
					'change' : '7','lat' : '32.307800','long': '-64.750500',
					"icon": {
						"iconSize": [43, 22],
						"iconAnchor": [50, 50],
						"popupAnchor": [0, -55],
						"className": "dot",
					}
			}
			},
			{
				type: 'Feature',
				geometry: {
					type: 'Point',coordinates: [-77.629394,12.033948 ]
				},
				properties: {
					title: 'Hawaii',
					'change' : '7',
					'lat' : '19.896766',
					'long': '-155.582782',
					"icon": {
						"iconSize": [43, 22],
						"iconAnchor": [50, 50],
						"popupAnchor": [0, -55],
						"className": "dot"
					}
				}
			},

		];
		myLayer2.setGeoJSON(geoJson2);
		
		myLayer.on('layeradd', function(e) {
			var marker = e.layer,
				feature = marker.feature;

			marker.setIcon(L.icon(feature.properties.icon));
		});

		for(var i=0;i<data.message.length;i++){
			geoJson.push(data.message[i]);
			if(i == data.message.length-1){
				
				myLayer.setGeoJSON(geoJson);
			}
		}
		myLayer.on('mouseover',showInfo);
		// myLayer.on('mouseout',hideInfo);
		myLayer.on('click', function(e) { 
			e.layer.closePopup();
			var feature = e.layer.feature;
			$.app.go('/explore/'+feature.properties.uri,true);
		});
		
		myLayer2.on('click', function(e) { 
			e.layer.closePopup();e.layer.unbindPopup();var feature = e.layer.feature;
			if(feature.properties.change){
				window.map.remove();//<<Here comes the magic!
				window.map = L.mapbox.map('map', 'derrickstuds.imab7m7e').setView([feature.properties.lat , feature.properties.long], 7);
				$.mapbox.draw();

			}
			return false;   
		});
		
		
		
		function showInfo(e)
		{
			var feature= e.layer.feature;
			var html = '<div class="info map_details_con"><ul class="deta_map">';
			html +='<li><strong>Name : <span style="color:#206BEF;">' + feature.properties.title + '<span></strong><li>';
			html +='<li><strong>Capital</strong> : ' + feature.properties.capital + '</li>';
			html +='<li><strong>Population</strong> : ' + feature.properties.population + '</li>';
			html +='<li><strong>National Dish</strong> : ' + feature.properties.national_dish + '</li>';
			html +='<li>' + feature.properties.description + '</li></ul>';
			html +='<ul class="deta_map datali"><li id="category-data" style="width:100%; height:190px;"></li>';
			html +='<li id="ethnic-data" style="height:290px; width:100%;"></li></ul></div>';
			$('.map-info').html(html).fadeIn(50);
			createPie('ethnic-data',feature.properties.ethnic_data,'ETHNIC DISTRIBUTION');
			createLine('category-data',feature.properties.category_data,'PROFILE CATEGORIES');
		}
		
		function hideInfo(e)
		{
			var c = $(e.target);
			if(!c.hasClass('.map-info') && $('img.leaflet-marker-icon').index(c) == -1){
				$('.map-info').fadeOut(50).html('');
				$('.map-clickback').fadeOut(0);
			}else{
				if($('img.leaflet-marker-icon').index(c) != -1){
					c.trigger('click');
				}
			}
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

		function tick(j) {
			// Set the marker to be at the same point as one
			// of the segments or the line.
			if(geojson.coordinates[j+1] > geojson.length){
				j = 0;
			}
			marker.setLatLng(L.latLng(
			geojson.coordinates[j][1],
			geojson.coordinates[j][0]));
			marker2.setLatLng(L.latLng(
			geojson1.coordinates[j][1],
			geojson1.coordinates[j][0]));
			if (++j < geojson.coordinates.length){
				setTimeout(function(){tick(j);}, 100);
			}
		}
		
		setTimeout(function(){
			$.mapbox.interval = setInterval(function(){
				j = 0;tick(j);}, 2000
			);	
		},500);
		
	},
	quiet: function(){
		$.mapbox.active = true;
		
		if($.mapbox.data === null){
			$.getJSON(window.location.origin+'/api/v1/getMapData',function(data){
				$.mapbox.data = data;
				$.mapbox.draw();
			});	
		}else{
			$.mapbox.draw();
		}
	}
};


function randColor(){
	return '#'+Math.floor(Math.random()*16777215).toString(16);
}
/*endmap*/


String.prototype.entities = function(){
	var el = document.createElement('span');
	this.replace(/&(#(?:x[0-9a-f]+|\d+)|[a-z]+);/gi,function(str) {
			el.innerHTML= str;
			return el.textContent || el.innerText;
	});
	return this;
};

function createPie(target,data,title)
{
	
	var chart;var legend;var data = data;
	// PIE CHART
	chart = new AmCharts.AmPieChart();
	chart.addTitle(title,11,'#fff',0.8,false);
	chart.dataProvider = data;
	chart.titleField = "label";
	chart.valueField = "value";
	chart.outlineColor = "";
	chart.outlineAlpha = 0.8;
	chart.outlineThickness = 2;
	// this makes the chart 3D
	chart.depth3D = 10;chart.angle = 30;chart.labelText = "";
	chart.balloonText = "[[title]]: [[value]]% ";
	legend = new AmCharts.AmLegend();
	legend.markerType = "circle";
	legend.markerSize = "0";
	legend.fontSize = "10";
	legend.valueText = "";
	legend.useMarkerColorForLabels = true;
	chart.addLegend(legend);
	// WRITE
	chart.write(target);
}

function createLine(target,data,title,onGrid)
{
	
	var colors = "#76ba35,#00AFF0,#C72C95,#F8FF01,#FF6600,#04D215,#2A0CD0,#FF0F00,#B0DE09,#0D52D1,#0D5221,#76b035,#06AFF0,#C70C95,#58FF01,#B05209,#44D215,#2A0C95,#2F0F0F,#B05E09".split(",");
	var j=0;
	for(var i=0;i<data.length;i++)
	{
		j=i;
		if(j>colors.length){
			j=0;
		}
		data[i].color = colors[j];
		j++;
	}
	var chart;
	// SERIAL CHART
	var  chart = new AmCharts.AmSerialChart();
	if(title){
		chart.addTitle(title,11,'#fff',0.8,false);
	}
	chart.dataProvider = data;
	chart.categoryField = "name";
	chart.startDuration = 1;
	chart.autoMarginOffset = 2;
	// the following two lines makes chart 3D
	chart.depth3D = 20;chart.angle = 30;
	// AXES
	// category
	var categoryAxis = chart.categoryAxis;
	categoryAxis.labelRotation = 90;
	categoryAxis.fontSize = "10";
	categoryAxis.gridThickness = 0;
	categoryAxis.gridPosition = "start";
	categoryAxis.color = "#fff";
	categoryAxis.axisAlpha = 0;
	categoryAxis.autoGridCount  = false;
	categoryAxis.gridCount = data.length;
	
	if(onGrid){
		categoryAxis.fontSize = "12";
		categoryAxis.titleBold = true;
		categoryAxis.inside = true;
		categoryAxis.labelFunction = function(txt){
			return txt;
		};
	}
	
	// value
	var valueAxis = new AmCharts.ValueAxis();
	valueAxis.title = "";
	valueAxis.dashLength = 5;valueAxis.fontSize = 9;valueAxis.color = "#fff";valueAxis.axisAlpha = 0;chart.addValueAxis(valueAxis);
	// GRAPH
	var graph = new AmCharts.AmGraph();graph.valueField = "num";graph.colorField = "color";graph.balloonText = "<span style='font-size:14px'>[[title]]: <b>[[value]]</b></span>";graph.type = "column";graph.lineAlpha = 0;graph.fillAlphas = 1; chart.addGraph(graph);
	// CURSOR
	var chartCursor = new AmCharts.ChartCursor();
	chartCursor.cursorAlpha = 0;
	chartCursor.zoomable = false;
	chartCursor.categoryBalloonEnabled = false;
	chart.addChartCursor(chartCursor);
	//   chart.creditsPosition = "top-right";
	var balloon = chart.balloon;
	// set properties
	balloon.borderAlpha = 0;
	// WRITE
	chart.write(target);
}

//begin scrolling related
var scroll_lock = false;
var scroll_int = null;
var scroll_last = 0;
var scroll_last2 = 0;
var skel_created = false;
var firstLoadInterval = null;

function scrollHandler(e)
{
	e.preventDefault();
	
	var scroll = (window.pageYOffset || e.target.scrollTop);
	
	if(scroll > scroll_last2){
		if( scroll >= window.innerHeight && !$('body').hasClass('has-menu') ){
			$('body').addClass('has-menu');
		}
	}else{
		if(scroll-1 <= window.innerHeight && $('body').hasClass('has-menu') ){
			$('body').removeClass('has-menu');
		}
	}
	scroll_last2 = scroll;
	
	if(scroll_int !== null) {
		clearTimeout(scroll_int);
		scroll_int = null;
	}
	scroll_int = setTimeout(function(){
		
		var scroll = (window.pageYOffset || e.target.scrollTop);
		if( scroll-1 >= $(window).height() && !$('body').hasClass('has-menu')){
			$('body').addClass('has-menu');
			resizeMenu();
			slide.cleanup();
			if($('[data-slider]').find('video').length > 0){
				slide.hideVideos();
			}
		}
		if($('[data-slider]').inView() && $('body').hasClass('has-menu')){
			$('body').removeClass('has-menu');
		}
		if($('.menu').is(':visible')){
			menuSetCurrent();
		}
		var video = $('.ad-lg video').show();
		if(video.length > 0 && video.partiallyInView() && !video.get(0).playing){
			video.get(0).play();
		}
		if(scroll > scroll_last){
			scrollDown();
		}else{
			scrollUp();
		}
	},80);
}

function scrollUp()
{
	var el = $('[data-slug]:not(:parentofcurrent):not(:childofslug):not(:current):partial:abovecurrent').reduceToClosest();
	scrollTo(el);
}

function scrollTo(el)
{
	if(el.length > 0){
		if(el.data('slug') !== location.pathname){
			$.app.go(el.data('slug'),false);
		}
	}
}

function scrollDown()
{
	var el = $('[data-slug]:not(:parentofcurrent):not(:childofslug):not(:current):partial:belowcurrent').reduceToClosest();
	scrollTo(el);
}

$.fn.inView = function(){

    var rect = this[0].getBoundingClientRect();

	return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
	
};

$.fn.partiallyInView = function(){
	if(this.length > 0){
		var el = this[0];
		var top = el.offsetTop;
		var left = el.offsetLeft;
		var width = el.offsetWidth;
		var height = el.offsetHeight;

		while(el.offsetParent) {
			el = el.offsetParent;
			top += el.offsetTop;
			left += el.offsetLeft;
		}
		return (
			top < (window.pageYOffset + window.innerHeight) &&
			left < (window.pageXOffset + window.innerWidth) &&
			(top + height) > window.pageYOffset &&
			(left + width) > window.pageXOffset
		);	
	}else{
		return false;
	}
};

function recursibleScroll(__t)
{
	__t.add($('body')).waitForImages(function(){
		disableScroll();
		last_t = __t;
		scroll_last = __t.offset().top();
		
		$('html, body').animate({
			scrollTop: __t.offset().top,
		},500,'swing',function(){
			deferred_exec = null;
			$.app.done();
			if( scroll-1 >= $(window).height() && !$('body').hasClass('has-menu')){
				$('body').addClass('has-menu');
			}
			if($('[data-slider]').inView() && $('body').hasClass('has-menu')){
				$('body').removeClass('has-menu');
			}
			if($('.menu').is(':visible')){
				slide.cleanup();
				if($('[data-slider]').find('video').length > 0){
					slide.hideVideos();
				}
				menuSetCurrent();
			}
			enableScroll();
		});		
	});
}
var scroll_handles = 0;
var deferred_exec = null;
var scrollTimer2 = 0;
var last_t = null;
$.fn.ensureInview = function(scrollTo){
	clearTimeout(scrollTimer2);
	scrollTimer2 = setTimeout(function(){
		enableScroll();
	},505);
	if(!scroll_locked){
		var __t = this;
		var scrollTo = scrollTo || false;
		if(scrollTo && deferred_exec == null){
			disableScroll();
			
			var last_scroll_handles = scroll_handles;
			deferred_exec = function(){
				
				__t.waitForImages(function(){ recursibleScroll(__t) });
			};
			
			setTimeout(function(){
				if(scroll_handles == last_scroll_handles){
					deferred_exec();
					$.app.done();
				}
			},100);
		}else{
			scroll_handles+=1;
			$('body').waitForImages(function(){
				scroll_handles -=1;
				if(scroll_handles == 0)
				{
					$.app.done();
					if(typeof deferred_exec==='function'){
						deferred_exec();
						deferred_exec = null;
					}
				}
			});
		}
	}
};

function recursibleScroll(__t)
{
	
	last_t = __t;
	scroll_last = __t.offset().top;
	if(scroll_last == 0 && location.pathname.match(/(^\/{1}[a-z-0-9]+$)/) == null){
		setTimeout(function(){
			recursibleScroll(__t);
		},100);
	}
	
	$('html, body').animate({
		scrollTop: scroll_last,
	},500,'swing',function(){
		deferred_exec = null;
		$.app.done();
		if($('[data-slider]').inView() && $('body').hasClass('has-menu')){
			$('body').removeClass('has-menu');
		}
		if($('.menu').is(':visible')){
			slide.cleanup();
			if($('[data-slider]').find('video').length > 0){
				slide.hideVideos();
			}
			menuSetCurrent();
		}
	});		
}


$.fn.reduceToClosest = function(){
	var it = null;
	this.each(function(i,v){
		var z = $(this);
		var position = z.position().top - $(window).scrollTop();
		if (position <= 0) {
			it = z;
		}	
	});
	if(it == null){
		it = this.eq(0);
	}
	return it;
};

$.extend(	$.expr[':'], {  
	inview: function (el) {    
		return $(el).inView();
	},
	partial: function(el) {
		return $(el).partiallyInView();
	},
	current: function(el) {
		return $(el).data('slug') === location.pathname;
	},
	childofslug: function(el){
		return $(el).parents('[data-slug]').length > 0;
	},
	parentofcurrent: function(el){
		return $(el).find('[data-slug="'+location.pathname+'"]').length > 0;
	},
	abovecurrent: function(el){
		return $(el).offset().top < $('[data-slug="'+location.pathname+'"]').offset().top;
	},
	belowcurrent: function(el){
		return $(el).offset().top > $('[data-slug="'+location.pathname+'"]').offset().top;
	},
	failed: function(el){
		return el.complete && parseInt(el.naturalWidth) == 0;
	}
});




// end scroll related





function iE(el)
{
	  el.src = window.location.origin+'/static/front/img/broken.png';
}

function adRun_video()
{
	
	var v = $('[data-slug="'+location.pathname+'"]').find('video');
	if(v.length){
		v.get(0).play();
	}
}

function playvideo(el,timeout)
{
	var video = el.get(0);
	
	video.loop = true;
	$(video).one('canplay', function() {
		
		setTimeout(function() {
			video.play();
			if(!el.is(':visible')){
				el.fadeIn(100);
			}
		}, timeout || 0);
	});
}

function mapRun()
{
	$.mapbox.load();	
}

var slide = {
	el: null,
	created: false,
	go: function(a,scrollTo){
		slide.hideVideos();
		slideIt(a,scrollTo);
	},
	run: function(){
		
		if($('.video-slideup video').length > 0){
			
			$('.video-slideup').html('');
		}
		setTimeout(function(){
			try{
				if(location.pathname !== '/stats'){
					target = $('[data-slider]').find('.item:inview').find('img.lazyOwl');
					target.siblings('.video-slideup').html('<video src="'+target.data('video')+'" loop style="display:none;" preload="auto"></video>');
					target.siblings('.video-slideup').find('video').show().get(0).play();
				}
			}
			catch(e){
				
			}
			slide.handleCallbacks()
		},500);
	},
	handleCallbacks: function(){
		var slug = $('[data-slug="'+location.pathname+'"]');
		if(slug.length > 0 && typeof slug.data('callback') !== 'undefined' && slug.data('callback') !== false){
			var cb = slug.data('callback');
			try{
				if(window[cb]){
					window[cb].call(window,null);	
				}	
			}
			catch(e){}
		}
		if(location.pathname == '/stats' && $('iframe:visible').length >0 && $('iframe').inView()){
			$('iframe:visible:not([src])').addClass('m-progress').on('load',function(e){
				$(this).removeClass('m-progress');
			}).attr('src',function(){
				return $(this).data('src');
			});
		}
	},
	cleanup: function(){
		//a catchall function that can remove some things for us if necessary.
		try{
			if($.mapbox.active && location.pathname !== '/map'){
				$.mapbox.unload()
			}
		}
		catch(e){}
		if($('body > .searchbar').length > 0 && location.pathname !== '/stats'){
			$('.searchbar').appendTo('[data-slug="/stats"] .content');
			$('.searchbar .results').html('').removeClass('active');
			$('.searchgraph-results').html('').removeClass('active');
			$('.searchbar input').val('');
		}
		$.noty.closeAll();
	},
	hideVideos: function(){
		$('.video-slideup').html('');
	},
};

function navMin()
{
	$('body').addClass('nav-minimized');
}

function statsCb()
{
	navMin();
	$('.searchbar').appendTo('body');
}

var z = 0;
function slideIt( b , scrollTo)
{
	$.cookie('ip.slide',b);
	$('.nav > li.active').removeClass('active');
	var n = $('.nav > li a[data-href="/'+b+'"]').parent('li').addClass('active').index();
	slide.el.goTo(n);
	$('[data-slug="/"]').find('[data-viewpoint]').ensureInview(scrollTo);
	z=0;
	slide.cleanup();
	setTimeout(function(){
		
		slide.run();
	},200);
}

//setup the google analytics object.
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-68535823-1', 'auto');

/* * * DON'T EDIT BELOW THIS LINE * * */
function moveDisqusDiv(personeId){
$( "#disqus_thread" ).remove();
document.getElementById(personeId).innerHTML = "<div id='disqus_thread'></div>";
return true;
}



/* * * Disqus Reset Function * * */
function disqus(e) {
	
	window.onbeforeunload = function(e){
		$.removeCookie('ip.slide');
	};
	
	
	e.preventDefault();
	
	var div = $(this).parent().parent().siblings('.disqus-append');
	
	if(div.children('#disqus_thread').length == 0){
		$('#disqus_thread').remove();
		div.html('<div id="disqus_thread"></div>');	
		
		setTimeout(function(){
			$('#disqus_thread').ensureInview(true);
			if(typeof DISQUS === 'undefined' || DISQUS === false){
				var disqus_shortname = 'islandpeeps2015'; //
				var disqus_identifier = window.location.pathname.replace('/','-');
				var disqus_url = window.location.href;
				var disqus_config = function () {
					this.language = "en";
				};
				(function() {
					var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
					dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
					(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
				})();
			}else{
				DISQUS.reset({
				config: function () 
					{
						this.page.identifier = window.location.pathname;
						this.page.url = window.location.href;
						this.page.title = document.title;
						this.language = 'en';
					},
					reload: true
				});
			}	
		},0);	
	}
	return false;
};


function disableScroll()
{
	$(window).off('scroll',scrollHandler);
	scroll_locked = true;
}

var scroll_locked = false;

function enableScroll()
{
	$(window).on('scroll',scrollHandler);
	scroll_locked = false;
}
  
$(function(){
	
	$(document).on('click','.map-reset',function(e){
		e.preventDefault();
		$.mapbox.unload();
		$.mapbox.load();
		return false;
	});
	
	$(window).resize(responsiveApp);
	
	$.app = { 
		get : function(a,b){
			$.router.add(a,b);
			return $.app;
		}, 
		go: function(a,scrollTo){ 
			
			$.router.go(a,scrollTo); 
			return $.app; 
		},
		done: function(){
			//when done is called, we send the page view. 
			
			ga('send', 'pageview',{
				'page': window.location.pathname,
				'title': $('title').html()
			});
			$('body').waitForImages(function(){
				$('body').removeClass('preload');
			});
			return $.app;
		}
	};
	
	$(document).on('click','.nav-btns > div',function(e){
		var z = $(this).hasClass('next');
		if(z){
			var x = $('.nav > li.active').next().find('a').data('href')
		}else{
			var x = $('.nav > li.active').prev().find('a').data('href')
		}
		if(typeof x === 'undefined'){
			//here we know that were at the end of our rope. 
			var y = z ? 0 : -1;
			var x = $('.nav > li').eq(y).find('a').data('href');
		}
		$.app.go(x,true);
	});
	
	function loadCountry(country,callback,scrollTo,no_call)
	{
		
		$.getJSON(window.location.origin+'/api/v1/get_country?uri='+country,function(data){
			$('[data-slug="/explore/'+country+'"]').html(data.message).addClass('done');
			
			if(typeof no_call === 'undefined' || no_call === false){
				var c = $('[data-slug="/explore/'+country+'"]').find('.country').data('callback');
				if(typeof c !== 'undefined' && c !== false){
					window[c]();
				}
			}
			menuSetCurrent();
			if(typeof callback === 'function'){
				callback.apply(this,[]);
			}
			$('[data-slug="/explore/'+country+'"].done').closest('[data-viewpoint]').ensureInview(scrollTo);
		});
	}
	
	function loadProfile(country,uri)
	{
		var el = $('[data-slug="/explore/'+country+'"]').find('.country-profile').html('').attr('data-slug',location.pathname);
		$.getJSON(window.location.origin+'/api/v1/get_country_item?uri='+uri+'&c_uri='+country,function(data){
			el.html(data.message).closest('[data-viewpoint]').ensureInview(true);//always scroll to profiles
		});
	}
	
	function loadMeme(uri,scrollTo)
	{
		var el = $('.meme-viewer').html('').attr('data-slug',location.pathname);
		$.getJSON(window.location.origin+'/api/v1/get_meme?uri='+uri,function(data){
			el.html(data.message).closest('[data-viewpoint]').ensureInview(true);//always scroll to memex
		});
	}
	
	function loadRecent(scrollTo)
	{
		var el = 
		$.getJSON(window.location.origin+'/api/v1/get_recent',function(data){
			el.
			el.closest('[data-viewpoint]').ensureInview(scrollTo);	
		});
	}
	
	$.app
	.get('/',function(data,scrollTo){
		//index page redirects to slide.
		var slide = $.cookie('ip.slide');
		if(typeof slide === "undefined"){
			slide =	'home';
		}
		$.app.go('/'+slide,scrollTo);
	})
	.get('/explore/:country',function(data,scrollTo){
		//country pages
		var country = data.country;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,false,scrollTo);
		}else{
			$('[data-slug="/explore/'+country+'"].done').closest('[data-viewpoint]').ensureInview(scrollTo);
		}
		
	})
	.get('/explore/:country/people/:person',function(data,scrollTo){
		var country  = data.country, 
		person   	 = data.person;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,function(){
				loadProfile(country,person);
			},false);//skip scroll for country and continue on to loading the Profile in the callback.
		}else{
			loadProfile(country,person);
		}
		
	})
	.get('/extras/memes',function(data,scrollTo){
		var el = $('[data-memes]').closest('[data-viewpoint]').ensureInview(scrollTo);
	})
	.get('/extras/memes/:uri',function(data,scrollTo){
		var meme = data.uri;
		loadMeme(meme,scrollTo);
	})
	.get('/explore/:country/fun-fact/:uri',function(data){
		var country  = data.country, 
		person   	 = data.uri;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,function(){
				loadProfile(country,person);
			},false);//skip scroll for country and continue on to loading the Profile in the callback.
		}else{
			loadProfile(country,person);
		}
		
	})
	.get('/:page',function(data,scrollTo){
		slide.go(data.page,scrollTo);
	})
	.get('/profiles/recently-added',function(data,scrollTo){
		$('[data-slug="/profiles/recently-added"]').closest('[data-viewpoint]').ensureInview(scrollTo);
	});
	
	$(document).on('notFound',function(){
		
		$('body').load(window.location.origin+'/static/404.html',function(){
			$.app.done();
		});
	});
	
	var t_load = null;
	
	//this function is executed automatically
	!function(){
		t_load = setInterval(function(){
			
			if(slide.created && skel_created){
				clearInterval(t_load);	
				$(document).foundation();
				$('body').waitForImages(function(){
					$.app.go(location.pathname,true);
					$('body').append( $('<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"><\/script>')[0] );
				});
			}
		},50);
		$.getJSON(window.location.origin+'/api/v1/init',function(data){
			$('meta[name="csrf"]').attr('content',data.message.csrf);
			$('[data-slider]').append(data.message.slider).addClass('done');
			$('.menu').html(data.message.menu);
			$('[data-memes]').html(data.message.memes);
			$('[data-slug="/profiles/recently-added"]').html(data.message.recent).addClass('done');
			slide.el = $('.owl-carousel').owlCarousel({
				navigation : false,
				slideSpeed : 300,
				pagination : false,
				singleItem:true,
				mouseDrag:false,
				touchDrag:false,
				afterInit: function(){
					$('.owl-carousel').find('.item').addClass('done');
					firstLoadInterval = setInterval(function(){
						if(typeof slide.el === 'object'){
							slide.created = true;
							clearInterval(firstLoadInterval);	
						}
					},50);
				},
				afterAction: function(el){
					//remove class active
					this
					.$owlItems
					.removeClass('active')

					//add class active
					this
					.$owlItems
					.eq(this.currentItem)
					.addClass('active')
				},
				lazyLoad: true,
			}).data('owlCarousel');
			var z = [
				data.message.slugs.countries
			];
			var y = ['countries'];
			
			for(var j =0; j < z.length;j++)
			{
				var html = '';
				for(var i = 0; i < z[j].length;i++){
					html +='<div class="row below-fold" data-viewpoint><div class="column slug country-slug" data-slug="'+z[j][i]+'"></div></div>';
					if(i == z[j].length -1){
						$('[data-'+y[j]+']').html(html);
						setTimeout(function(){
							skel_created = true;
						},0)
					}
				}
			}
		});
	}();
	
	//handle click event
	$(document).on('click','.menu a[data-href]',function(){
		$('.menu li.active').removeClass('active');
		$(this).addClass('active');
	});
	$(document).on('click','[data-href]',function(e){
		var href = $(this).data('href');
		if(href !== location.pathname){
			$.app.go(href,true);
		}
	});
	
	$(document).foundation();
	
	$(window).on('scroll',scrollHandler);
	
	lightbox.option({
      'resizeDuration': 200,
      'wrapAround': true,
	  'showImageNumberLabel': false,
	  'positionFromTop': 140,
	  'maxHeight': $(window).height() - 190
    });
	
	$(document).on('click','.about li a',function(e){
		e.preventDefault();
		if(!$(this).hasClass('active')){
			$('.about li a.active').removeClass('active');
			$('.about-viewer').hide().html( decodeURIComponent( $(this).data('contents')) ).fadeIn(100);
			$(this).addClass('active');
		}
		return false;
	});
	
	//search handlers
	var search_int = null;
	$(document).on('keyup','.searchbar input',function(e){
		if(e.which == 13 && this.value != "")
		{
			$(this).blur().siblings('a').trigger('click');
		}else{
			if(search_int !== null) clearInterval(search_int);
			search_int = setTimeout(function(value){
				//do instant search but only if val not empty
				if(value==''){
					$('.searchbar .results').removeClass('active');
					return false;
				}
				$.getJSON(window.location.origin+'/api/v1/searchInstant?q='+encodeURIComponent(value),function(data){
					if(data.error == 0){
						$('.searchbar .results').addClass('active').html(data.message);
					}else{
						$('.searchbar .results').removeClass('active');
						sk.alert(data.message,'error');
					}
				});
			},100,this.value);
		}
	});
	
	$(document).on('click','.searchbar > a',function(e){
		e.preventDefault();
		//submit the search to generate a graph of results
		if($('.searchbar input').val() !== ""){
			$.getJSON(window.location.origin+'/api/v1/searchGraph?q='+$('.searchbar input').val(),function(data){
				if(data.error == 0){
					$('.searchbar .results').removeClass('active');
					$('.social-wall').addClass('sm').children('iframe').remove();
					$('.social-wall').append('<iframe src="'+window.location.origin+'/socialwall.php"></iframe>');
					$('.searchgraph-results').html(data.message).addClass('active');
					setTimeout(function(){
						var dataset = JSON.parse($('#graph-dataset').html());
						
						var i = dataset.length;
						while(i--){
							if(dataset[i] instanceof Array ){
								dataset.splice(i,1);
							}
						}
						function cmp(a,b) {
							if (a.name < b.name){
								return -1;
							}
							else if (a.name > b.name){
								return 1;
							}
							else{
								return 0;
							}
						}
						dataset.sort(cmp);
						createLine('graph-results',dataset,false,true)
					},0);
					$('.searchgraph-results .close').one('click',function(){
						$('.searchgraph-results').html('').removeClass('active');
						$('.social-wall').removeClass('sm').children('iframe').remove();
						$('.social-wall').append('<iframe src="'+window.location.origin+'/socialwall.php?wall"></iframe>');
					});
				}else{
					sk.alert(data.message,'error');
				}
			});	
		}
		return false;
	});
	
	$(document).on('change','[name="xmobile-go"]',function(e){
		$.app.go($(this).val(),true);
	});
	
	$(document).on('click','.by-country ul li a',function(e){
		$('.by-country li a').removeClass('active');
		$(this).addClass('active');
		var id = $(this).data('country').toString();
		$('.results-window').html($('.results-inner li[data-regions~='+id+']').clone(false));
		$('.results-window').show();
	});
	
	$(document).on('click','[data-language]',function(){
		console.log('clicked');
		var selected = $(this).attr('data-selected');
		if(selected == 'English'){
			var t = 1;
			var language = 'Spanish';
		}else{
			var t = 0;
			var language = 'English';
		}
		try{
			var x = $('.goog-te-menu-frame').eq(0).contents().find('span.text:contains("'+language+'")');
			if(x.length > 0){
				x.click();
				translate_recurse = 0;
				flipLanguage(t);
				resizeMenu();
			}else{
				throw 'cant find item';
			}
		}
		catch(ex){
			console.log(ex);
			flipLanguage(+!t);// +! will flip 1 to 0 and 0 to 1.
			sk.alert("there was a problem translating the page. please try again later",'error');
		}	
	});
	
	$(document).on('click','.disqus-btn button',disqus);
	responsiveApp();
});

//misc functions

function suggestDone(data)
{
	if(data.error == 0){
		this.trigger('reset'); //reset the form
		sk.alert('Your Suggestion has been submitted! You will receive a confirmation email as soon as we have reviewed your submission. Thanks!','success');
	}else{
		sk.alert(data.message,'error');
	}
}