/*map*/

function googleTranslateElementInit() {
	new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE, autoDisplay: false}, 'google_translate_element');
}

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
		$.mapbox.quiet();
	},
	draw: function(){
		var myLayer = L.mapbox.featureLayer().addTo(window.map);
		var data = $.mapbox.data;
		var geoJson = [
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
		for(var i=0;i<data.message.length;i++){
			geoJson.push(data.message[i]);
			if(i == data.message.length-1){
				console.log(geoJson);
				myLayer.setGeoJSON(geoJson);
			}
		}
		myLayer.on('mouseover',showInfo);
		myLayer.on('mouseout',hideInfo);
		myLayer.on('click', function(e) { 
			e.layer.closePopup();
			var feature = e.layer.feature;
			$.app.go('/explore/'+feature.properties.uri);
		});
		
		
		
		function showInfo(e)
		{
			var feature= e.layer.feature;
			var html = '<ul><li><strong>Name : <span style="color:#206BEF;">' + feature.properties.title + '<span></strong><li>';
			html +='<li><strong>Capital</strong> : ' + feature.properties.capital + '</li>';
			html +='<li><strong>Population</strong> : ' + feature.properties.population + '</li>';
			console.log(feature.properties.ethnic_data);
			html +='<li><strong>Ethnic Data</strong> : <div class="large-12 columns"><div style="margin:0 auto; width:200px">';
			html +='<canvas id="ethnic-data" width="200" height="200"></canvas></div></div></li></ul>';
			$('.map-info').html(html).fadeIn(50);
			createPie($('#ethnic-data'),feature.properties.ethnic_data);
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

		function tick() {
			// Set the marker to be at the same point as one
			// of the segments or the line.
			marker.setLatLng(L.latLng(
			geojson.coordinates[j][1],
			geojson.coordinates[j][0]));
			marker2.setLatLng(L.latLng(
			geojson1.coordinates[j][1],
			geojson1.coordinates[j][0]));
			if (++j < geojson.coordinates.length){
				setTimeout(tick, 100)
			}
		}
		
		setTimeout(function(){
			$.mapbox.interval = setInterval(function(){
				j = 0;tick();}, 2000
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

$(function(){
	$(document).on('click','.map-reset',function(e){
		e.preventDefault();
		$.mapbox.unload();
		$.mapbox.load();
		return false;
	});	
});
/*endmap*/


String.prototype.entities = function(){
	var el = document.createElement('span');
	this.replace(/&(#(?:x[0-9a-f]+|\d+)|[a-z]+);/gi,function(str) {
			el.innerHTML= str;
			return el.textContent || el.innerText;
	});
	return this;
};

function createPie(target,data)
{
	console.log(target);
	new Chart(target[0].getContext("2d")).Pie(data,
		{
			tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>%"
		}
	);
}

function createBar(target,labels,data)
{
	console.log(target);
	for(var i=0;i<labels.length;i++){
		labels[i] = labels[i].entities();
	}
	new Chart(target[0].getContext("2d")).Bar({
		labels: labels,
		datasets: [data]
	});
}

function createBar2(target,data)
{
	console.log(target);
	new Chart(target[0].getContext("2d")).Bar({
		labels: [data.label.entities()],
		datasets: [data]
	},{
		animation: false,
		showScale:false,
		showTooltips:false,
		scaleShowLabel:true,
	});
}


//begin scrolling related


// left: 37, up: 38, right: 39, down: 40,
// spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36
var keys = {37: 1, 38: 1, 39: 1, 40: 1};
var scroll_lock = false;
var scroll_int = null;
var scroll_last = 0;
var skel_created = false;
var tmp_int_1 = null;
var scroll_events = [];

function preventDefault(e) {
  e = e || window.event;
  if (e.preventDefault)
      e.preventDefault();
  e.returnValue = false;  
}

function preventDefaultForScrollKeys(e) {
    if (keys[e.keyCode]) {
        preventDefault(e);
        return false;
    }
}

function disableScroll() 
{
	$(window).off('scroll',scrollHandler);
	scroll_lock = true;
	if (window.addEventListener) // older FF
	  window.addEventListener('DOMMouseScroll', preventDefault, false);
	window.onwheel = preventDefault; // modern standard
	window.onmousewheel = document.onmousewheel = preventDefault; // older browsers, IE
	window.ontouchmove  = preventDefault; // mobile
	document.onkeydown  = preventDefaultForScrollKeys;
}

function enableScroll() 
{
	$(window).on('scroll',scrollHandler);
    if (window.removeEventListener)
        window.removeEventListener('DOMMouseScroll', preventDefault, false);
    window.onmousewheel = document.onmousewheel = null; 
    window.onwheel = null; 
    window.ontouchmove = null;  
    document.onkeydown = null; 
	scroll_lock = false;
}


var init_scroll_lock = true;
var scroll_unlock = setInterval(function(){
	enableScroll();
},2000);

function scrollHandler(e)
{
	e.preventDefault();
	console.log(location.pathname);
	console.log('scroll handler fired');
	if(scroll_int !== null) {
		console.log('clearing previous timeout.');
		clearTimeout(scroll_int);
		scroll_int = null;
	}
	if(scroll_unlock !== null){
		console.log('clearing scroll unlock interval');
		clearInterval(scroll_unlock);
		scroll_unlock = null;
	}
	scroll_int = setTimeout(function(){
		console.log('scroll int fired');
		if(!scrollLocked() && !init_scroll_lock)
		{
			console.log('scroll not locked, continuing.');
			var scroll = (window.pageYOffset || e.target.scrollTop);
			if( scroll+1 >= $(window).height() && !$('body').hasClass('has-menu')){
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
				var el2 = $('.menu').find('[data-href="'+location.pathname+'"]');
				if(el2.length > 0){
					$('.menu li').removeClass('active');
					el2.parent('li').addClass('active');
				}
			}
			if(scroll > scroll_last){
				console.log('scrolling is down');
				scrollDown();
			}else{
				console.log('scroll is up');
				scrollUp();
			}
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
	console.log('scrolling to target element',el);
	if(el.length > 0){
		disableScroll();
		$.app.go(el.data('slug'));
	}
}

function scrollDown()
{
	var el = $('[data-slug]:not(:parentofcurrent):not(:childofslug):not(:current):partial:belowcurrent').reduceToClosest();
	scrollTo(el);
}


function scrollLocked()
{
	return scroll_lock;
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
	
};

var previous_el = null;

$.fn.ensureInview = function(){
	if(!this.inView()){
		disableScroll(); //make sure scroll handler is disabled
		var __t = this;
		if(__t !== previous_el){
			previous_el == __t;
		}else{
			return;
		}
		__t.waitForImages(function(){
			scroll_last = __t.offset().top;
			$('html, body').animate({
				scrollTop: scroll_last
			},500,'swing',function(){
				console.log('target is in view');
				enableScroll();
				var scroll = window.pageYOffset || __t.scrollTop;
				if( scroll+1 >= $(window).height() && !$('body').hasClass('has-menu')){
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
					var el2 = $('.menu').find('[data-href="'+location.pathname+'"]');
					if(el2.length > 0){
						$('.menu li').removeClass('active');
						el2.parent('li').addClass('active');
					}
				}
			});	
		});
	}else{
		enableScroll();
	}
};


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
	console.log('adRun_video fired');
	var v = $('[data-slug="'+location.pathname+'"]').find('video').get(0).play();
}

function playvideo(el,timeout)
{
	var video = el.get(0);
	console.log(el);
	video.loop = true;
	$(video).one('canplay', function() {
		console.log('canplay');
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
	go: function(a){
		slideIt(a);
	},
	hideVideos: function(){
		$('.video-slideup').html('');
	},
	playCurrentVideo: function(){
		var target = $('[data-slider]').find('.item:inview').find('img.lazyOwl');
		if(target.length > 0){
			var video = target.data('video');
			if(typeof video !== 'undefined' && video != false && video != ''){
				target.siblings('.video-slideup').append('<video src="'+video+'" loop style="display:none;" preload="auto"></video>');
				setTimeout(function(){
					var el = target.siblings('.video-slideup').find('video').show().get(0).play();
				},200);
			}	
		}
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
		if($('iframe:visible').length >0 && $('iframe').inView()){
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
	}
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
function slideIt( b )
{
	//slide.el maybe hasn't been created yet. use recursion to poll until it is.
	$('.nav > li.active').removeClass('active');
	var n = $('.nav > li a[data-href="/'+b+'"]').parent('li').addClass('active').index();
	slide.el.goTo(n);
	$('[data-slug="/"]').find('[data-viewpoint]').ensureInview();
	z=0;
	slide.hideVideos();
	setTimeout(function(){
		slide.playCurrentVideo();
		slide.handleCallbacks();
		slide.cleanup();
	},500);
	$.app.done();
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
	e.preventDefault();
	
	var div = $(this).parent().parent().siblings('.disqus-append');
	
	if(div.children('#disqus_thread').length == 0){
		$('#disqus_thread').remove();
		div.html('<div id="disqus_thread"></div>');	
		
		setTimeout(function(){
			$('#disqus_thread').ensureInview();
			disableScroll();
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
				setTimeout(function(){
					enableScroll();
				},500);
			}	
		},0);	
	}
	return false;
};
  
$(function(){
	console.log('document ready');
	
	$.app = { 
		get : function(a,b){
			$.router.add(a,b);
			return $.app;
		}, 
		go: function(a){ 
			console.log('going to route '+a);
			$.router.go(a); 
			return $.app; 
		},
		done: function(){
			//when done is called, we send the page view. 
			console.log('$.app.done()');
			ga('send', 'pageview',{
				'page': window.location.pathname,
				'title': $('title').html()
			});
			$('body').waitForImages(function(){
				$('body').removeClass('preload');
				setTimeout(function(){
					init_scroll_lock = false;
				},100);
			});
			//backGroundTick();
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
		$.app.go(x);
	});
	
	
	function backGroundTick()
	{
		var els = $('.country-slug:not(.done)');
		var current_uri = location.pathname;
		for(var i=0;i<els.length;i++)
		{
			var div = $(els[i]);
			setTimeout(function(div,current_uri){
				if(location.pathname !== current_uri){
					return;
				}else{
					var country = div.data('slug').split('/').pop();
					console.log('loading slug: `/explore/'+country+'`');
					loadCountry(country,null,true,true);
				}	
			},(i+1) * 800,div,current_uri);
		}
	}
	
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
			
			if(typeof callback === 'function'){
				callback.apply(this,[]);
			}
			if(typeof scrollTo === 'undefined' || scrollTo === false){
				$('[data-slug="/explore/'+country+'"].done').closest('[data-viewpoint]').ensureInview();
			}
		});
	}
	
	function loadProfile(country,uri)
	{
		var el = $('[data-slug="/explore/'+country+'"]').find('.country-profile').html('').attr('data-slug',location.pathname);
		$.getJSON(window.location.origin+'/api/v1/get_country_item?uri='+uri+'&c_uri='+country,function(data){
			el.html(data.message).closest('[data-viewpoint]').ensureInview();
		});
	}
	
	function loadMeme(uri)
	{
		var el = $('.meme-viewer').html('').attr('data-slug',location.pathname);
		$.getJSON(window.location.origin+'/api/v1/get_meme?uri='+uri,function(data){
			el.html(data.message).closest('[data-viewpoint]').ensureInview();
		});
	}
	
	$.app
	.get('/',function(){
		//index page redirects to slide.
		$.app.go('/home');
	})
	.get('/explore/:country',function(data){
		//country pages
		var country = data.country;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country);
		}else{
			$('[data-slug="/explore/'+country+'"].done').closest('[data-viewpoint]').ensureInview();
		}
		$.app.done();
	})
	.get('/explore/:country/people/:person',function(data){
		var country  = data.country, 
		person   	 = data.person;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,function(){
				loadProfile(country,person);
			},true);//skip scroll for country and continue on to loading the Profile in the callback.
		}else{
			loadProfile(country,person);
		}
		$.app.done();
	})
	.get('/extras/memes',function(){
		var el = $('[data-memes]').closest('[data-viewpoint]');
		console.log(el);
		el.ensureInview();
		$.app.done()
	})
	.get('/extras/memes/:uri',function(data){
		var meme = data.uri;
		loadMeme(meme);
		$.app.done();
	})
	.get('/explore/:country/fun-fact/:uri',function(data){
		var country  = data.country, 
		person   	 = data.person;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,function(){
				loadProfile(country,person);
			},true);//skip scroll for country and continue on to loading the Profile in the callback.
		}else{
			loadProfile(country,person);
		}
		$.app.done();
	})
	.get('/:page',function(data){
		slide.go(data.page);
	})
	.get('/profiles/recently-added',function(){
		var el = $('[data-slug="/profiles/recently-added"]');
		if(!el.hasClass('done')){
			$.getJSON(window.location.origin+'/api/v1/get_recent',function(data){
				el.html(data.message).addClass('done').closest('[data-viewpoint]').ensureInview();
			});
		}else{
			el.closest('[data-viewpoint]').ensureInview();
			
		}
		$.app.done();
	});
	
	var t_load = null;
	$(window).on('start.loading',function(){
		t_load = setInterval(function(){
			console.log('waiting..');
			if(slide.created && skel_created){
				clearInterval(t_load);	
				$(document).foundation();
				$('body').waitForImages(function(){
					disableScroll();
					$.app.go(location.pathname);
					$('body').append( $('<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"><\/script>')[0] );
				});
			}
		},50);
		$.getJSON(window.location.origin+'/api/v1/init',function(data){
			$('meta[name="csrf"]').attr('content',data.message.csrf);
			$('[data-slider]').append(data.message.slider).addClass('done');
			$('.menu').html(data.message.menu);
			$('[data-memes]').html(data.message.memes);
			slide.el = $('.owl-carousel').owlCarousel({
				navigation : false,
				slideSpeed : 300,
				pagination : false,
				singleItem:true,
				mouseDrag:false,
				touchDrag:false,
				afterInit: function(){
					$('.owl-carousel').find('.item').addClass('done');
					tmp_int_1 = setInterval(function(){
						if(typeof slide.el === 'object'){
							slide.created = true;
							clearInterval(tmp_int_1);	
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
	});
	
	$(window).on('menuin',function(){
		
	});
	
	$(window).on('menuout',function(){
		
	});
	
	//handle click event
	$(document).on('click','[data-href]',function(e){
		var href = $(this).data('href');
		if(href !== location.pathname){
			$.app.go(href);
		}
	});
	
	$(document).foundation();
	
	$(window).trigger('start.loading');
	
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
			$(this).siblings('a').trigger('click');
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
						var labels = JSON.parse($('#graph-labels').html());
						createBar($('#graph-results'),labels,dataset);
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
	
	$(document).on('click','.by-country ul li a',function(e){
		$('.by-country li a').removeClass('active');
		$(this).addClass('active');
		var id = $(this).data('country').toString();
		$('.results-window').html($('.results-inner li[data-regions~='+id+']').clone(false));
		$('.graph-results').addClass('active');
		$('.chart').removeClass('active');
	});
	
	$(document).on('click','.back-to-chart',function(e){
		$('.graph-results').removeClass('active');
		$('.chart').addClass('active');
	});
	
	$(document).on('click','[data-language]',function(e){
		e.preventDefault();
		if($(this).data('selected') == 'English'){
			var language = 'Spanish';
			$('.menu').addClass('translated');
		}else{
			$('.menu').removeClass('translated');
			var language = 'English';
		}
		$(this).data('selected',language);
		$('.goog-te-menu-frame:first').contents().find('.goog-te-menu2-item span.text:contains('+language+')').get(0).click();
		return false;
	});
	
	$(document).on('click','.disqus-btn button',disqus);
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