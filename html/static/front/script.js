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
		$.mapbox.quiet();
	},
	quiet: function(){
		var myLayer = L.mapbox.featureLayer().addTo(window.map);
		$.mapbox.active = true;
		
		var coder = L.mapbox.geocoder('derrickstuds.imab7m7e',{accessToken:'pk.eyJ1IjoiZGVycmlja3N0dWRzIiwiYSI6ImlSS2VHQW8ifQ.sFDiNJZ-s-N87fEDHniqHg'});
		
		coder.reverseQuery({lat : 19.896766,lon: -155.582782},function(error,data){
			console.log(data);
		});
		$.getJSON(window.location.origin+'/api/v1/getMapData',function(data){
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
		})
		

		
		myLayer.on('click', function(e) { 
			e.layer.closePopup();
			e.layer.unbindPopup();
			var feature = e.layer.feature;
			sk.option(
			
				'What Do you want to do?',
				
				{
					text:'Explore',
					callback: function(feature){
						$.app.go('/explore/'+feature.properties.uri);
					},
					args:[feature]
				},
				{
					text:'Zoom in',
					callback: function(feature){
						$.mapbox.unload();
						window.map = L.mapbox.map('map', 'derrickstuds.imab7m7e').setView([feature.properties.lat , feature.properties.long], 7);
						$.mapbox.quiet();
						showInfo(feature);
						return false;   
					},
					args:[feature]
				}
			)
		});
		
		
		myLayer.on('mouseover',showInfo);
		myLayer.on('mouseout',hideInfo);
		// Clear the tooltip when map is clicked or moved.
		window.map.on('move click',hideInfo);
		
		function showInfo(e)
		{
			var feature= e.layer.feature;
			var content = '<ul><li><strong>Name : <span style="color:#206BEF;">' + feature.properties.title + '<span></strong><li><li><strong>Capital</strong> : ' + feature.properties.capital + '</li><li><strong>Population</strong> : ' + feature.properties.population + '</li></ul></ul>';
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

var scroll_lock = false;
var scroll_int = null;
var scroll_last = 0;
var skel_created = false;
var tmp_int_1 = null;

// left: 37, up: 38, right: 39, down: 40,
// spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36
var keys = {37: 1, 38: 1, 39: 1, 40: 1};

//throttle utility from underscore.js
function throttle(func, wait, options) {
	var context, args, result;
	var timeout = null;
	var previous = 0;
	if (!options) options = {};
	var later = function() {
	  previous = options.leading === false ? 0 : now2();
	  timeout = null;
	  result = func.apply(context, args);
	  if (!timeout) context = args = null;
	};
	return function() {
	  var now = now2();
	  if (!previous && options.leading === false) previous = now;
	  var remaining = wait - (now - previous);
	  context = this;
	  args = arguments;
	  if (remaining <= 0 || remaining > wait) {
		if (timeout) {
		  clearTimeout(timeout);
		  timeout = null;
		}
		previous = now;
		result = func.apply(context, args);
		if (!timeout) context = args = null;
	  } else if (!timeout && options.trailing !== false) {
		timeout = setTimeout(later, remaining);
	  }
	  return result;
	};
};

//now from underscore
function now2()
{
	return Date.now || function() {
		return new Date().getTime();
	};
}

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

function disableScroll() {
  if (window.addEventListener) // older FF
      window.addEventListener('DOMMouseScroll', preventDefault, false);
  window.onwheel = preventDefault; // modern standard
  window.onmousewheel = document.onmousewheel = preventDefault; // older browsers, IE
  window.ontouchmove  = preventDefault; // mobile
  document.onkeydown  = preventDefaultForScrollKeys;
}

function enableScroll() {
    if (window.removeEventListener)
        window.removeEventListener('DOMMouseScroll', preventDefault, false);
    window.onmousewheel = document.onmousewheel = null; 
    window.onwheel = null; 
    window.ontouchmove = null;  
    document.onkeydown = null;  
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



$.fn.ensureInview = function(repeat){
	if(!this.inView() || typeof repeat !== undefined){
		scroll_last = this.offset().top;
		scroll_lock = true;
		var $el = this;
		$('html, body').animate({
			scrollTop: scroll_last
		}, 100,'swing',function(){
			if( Math.abs( $(window).scrollTop() - $el.offset().top ) > 5 && $(window).scrollTop() + $(window).height() != $(document).height()) {
				console.log('recursing');
				setTimeout(function(){
					$el.ensureInview(true);
				},0);
			}else{
				view_i = 0;
				$('html, body').animate({
					scrollTop: $el.offset().top
				},50);
				var scroll = window.pageYOffset || window.scrollTop;
				if( scroll+1 >= $(window).height() && !$('body').hasClass('has-menu')){
					$('body').addClass('has-menu');
				}
				if($('[data-slider]').inView() && $('body').hasClass('has-menu')){
					$('body').removeClass('has-menu');
				}
				console.log('done');
				setTimeout(function(){
					setTimeout(function(){
						if($('.menu').is(':visible')){
							slide.cleanup();
							if($('[data-slider]').find('video').length > 0){
								slide.hideVideos();
							}
							var el2 = $('.menu').find('[data-href="'+location.pathname+'"]');
							console.log(location.pathname,el2)
							if(el2.length > 0){
								el2.parent('li').addClass('active').siblings().removeClass('active');
							}
						}
					},0)
					scroll_lock = false;	
				},0);
			}
		});
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


function iE(el)
{
	  el.src = window.location.origin+'/static/front/img/broken.png';
}

function adRun_video()
{
	var v = $('[data-slug="'+location.pathname+'"]').find('video');
	if(v.length > 0){
		playvideo(v);
	}
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
	setTimeout(function(){
		$.mapbox.load();	
	},1000);
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
				console.log(video);
				target.siblings('.video-slideup').append('<video src="'+video+'?cache-buster='+ (new Date().getTime() / 1000) +'" loop style="display:none;" preload="auto"></video>');
				setTimeout(function(){
					var el = target.siblings('.video-slideup').children('video');
					playvideo(el,1000);
				},0);
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
	},
	cleanup: function(){
		//a catchall function that can remove some things for us if necessary.
		try{
			if($.mapbox.active && location.pathname !== '/map'){
				$.mapbox.unload()
			}
		}
		catch(e){}
		console.log(location.pathname);
		if($('body').hasClass('nav-minimized')){
			if(location.pathname !== '/suggest' && location.pathname !== '/stats')
			{
				$('body').removeClass('nav-minimized');	
			}
		}
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
	if(slide.el == null){
		if(z > 50){
			throw 'slide.el failed to be constructed. abort';
			z = 0;
		}
		setTimeout(function(){
			slideIt(b);
			z++;
		},100);
	}else{
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
}

//setup the google analytics object.
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-68535823-1', 'auto');
  
$(function(){
	var emit = {};
	$.app = { 
		get : function(a,b){
			$.router.add(a,b);
			return $.app;
		}, 
		go: function(a){ 
			$.router.go(a); 
			return $.app; 
		},
		done: function(){
			//when done is called, we send the page view. 
			ga('send', 'pageview',{
				'page': window.location.pathname,
				'title': $('title').html()
			});
			setTimeout(function(){
				$('body').removeClass('preload');
			},0);
			return $.app;
		}
	};
	
	function loadJs(file,callback)
	{
		if($('script[src="'+window.location.origin+file+'"]').length > 0){
			return false;
		}
		var s = document.createElement('script');
		s.setAttribute('src', window.location.origin + file);
		s.onload = function(){
			if( typeof callback === 'function' ){
				callback.apply(this,[]);	
			}
		};
		document.body.appendChild( s );
	}
	
	function loadCss(file)
	{
		if($('link[href="'+window.location.origin+file+'"]').length > 0){
			return false;
		}
		var link = document.createElement('link');
		link.type = 'text/css';
		link.rel = 'stylesheet';
		link.href = window.location.origin + file;
		document.head.appendChild(link);
	}
	
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
		$('[data-slug="'+x+'"]').closest('[data-viewpoint]').ensureInview();
		$.app.go(x);
	});
	
	
	function loadCountry(country,callback)
	{
		
		$.getJSON(window.location.origin+'/api/v1/get_country?uri='+country,function(data){
			$('[data-slug="/explore/'+country+'"]').html(data.message).addClass('done').closest('[data-viewpoint]').ensureInview();
			
			if(typeof callback === 'function'){
				callback.apply(this,[]);
			}else{
				$.app.done()
			}
		});
	}
	
	function loadProfile(country,uri)
	{
		var el = $('[data-slug="/explore/'+country+'"]').find('.country-profile').html('').attr('data-slug',location.pathname);
		$.getJSON(window.location.origin+'/api/v1/get_country_item?uri='+uri+'&c_uri='+country,function(data){
			el.html(data.message).closest('[data-viewpoint]').ensureInview();
			$.app.done()
		});
	}
	
	function loadMeme(uri)
	{
		var el = $('.meme-viewer').html('').attr('data-slug',location.pathname);
		$.getJSON(window.location.origin+'/api/v1/get_meme?uri='+uri,function(data){
			el.html(data.message).closest('[data-viewpoint]').ensureInview();
			$.app.done()
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
			loadCountry(country,function(){
				console.log('here');
				var c = $('[data-slug="/explore/'+country+'"]').find('.country').data('callback');
				console.log(c);
				if(typeof c !== 'undefined' && c !== false){
					window[c]();
					$.app.done();
					$('[data-slug="/explore/'+country+'"].done').closest('[data-viewpoint]').ensureInview();
					
				}
			});
		}else{
			$.app.done();
			$('[data-slug="/explore/'+country+'"].done').closest('[data-viewpoint]').ensureInview();
		}
	})
	.get('/explore/:country/people/:person',function(data){
		var country  = data.country, 
		person   	 = data.person;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,function(){
				loadProfile(country,person);
			});
		}else{
			loadProfile(country,person);
		}
		
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
	})
	.get('/explore/:country/fun-fact/:uri',function(data){
		var country = data.country,
		funfact = data.uri;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,function(){
				loadProfile(country,funfact);
			});
		}else{
			loadProfile(country,funfact);
		}
	})
	.get('/:page',function(data){
		slide.go(data.page);
	})
	.get('/profiles/recently-added',function(){
		var el = $('[data-slug="/profiles/recently-added"]');
		if(!el.hasClass('done')){
			$.getJSON(window.location.origin+'/api/v1/get_recent',function(data){
				el.html(data.message).addClass('done');
				el.closest('[data-viewpoint]').ensureInview();
				$.app.done();
			});
		}else{
			el.closest('[data-viewpoint]').ensureInview();
			$.app.done();
		}
	});
	
	$(document).foundation();
	
	var t_load = null;
	$(window).load(function(){
		t_load = setInterval(function(){
			console.log('waiting..');
			if(slide.created && skel_created){
				clearInterval(t_load);	
				setTimeout(function(){
					// $('body').removeClass('preload');
					setTimeout(function(){
						$.app.go(location.pathname);
					},1000);
					
				},500);
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
					.$owlItems //owl internal $ object containing items
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
							$(document).foundation();
						},0)
					}
				}
			}
		});
	});
	
	//handle click event
	$(document).on('click','[data-href]',function(e){
		var href = $(this).data('href');
		if(href !== location.pathname){
			$.app.go(href);
		}
	});
	
	var scroll_fn = throttle(function(e){
		if(scroll_lock){
			disableScroll();
			setTimeout(enableScroll,50);
		}else{
			if(scroll_int != null) clearTimeout(scroll_int);
			scroll_int = setTimeout(function(){
				var scroll = window.pageYOffset || this.scrollTop;
				if(!scroll_lock){
					if(scroll > scroll_last){
						scrollDown();
					}else{
						scrollUp();
					}		
				}else{
					window.scrollTop = scroll_last;
				}
			},80);
		}
	},100);
	
	$(window).on('scroll',scroll_fn);
	
	function scrollUp()
	{
		var el = $('[data-slug]:not(:parentofcurrent):not(:childofslug):not(:current):partial:abovecurrent').reduceToClosest();
		scrollTo(el);
	}
	
	function scrollTo(el)
	{
		if(el.length > 0 && !scroll_lock){
			el.ensureInview();
			$.app.go(el.data('slug'));	
		}
	}
	
	function scrollDown()
	{
		var el = $('[data-slug]:not(:parentofcurrent):not(:childofslug):not(:current):partial:belowcurrent').reduceToClosest();
		scrollTo(el);
	}
	
	/*
	setInterval(function(e){
		$('img:failed').each(function(){
			iE(this);
		});
	},1500);
	*/
	
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
		if(e.which == 13 && $(this).val() != "")
		{
			//submit the search to generate a graph of results
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
	})

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