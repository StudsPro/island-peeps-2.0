var scroll_lock = false;
var scroll_int = null;
var scroll_last = 0;
var skel_created = false;

// left: 37, up: 38, right: 39, down: 40,
// spacebar: 32, pageup: 33, pagedown: 34, end: 35, home: 36
var keys = {37: 1, 38: 1, 39: 1, 40: 1};

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
			if( Math.abs( $(window).scrollTop() - $el.offset().top ) > 5 ) {
				console.log('recursing');
				setTimeout(function(){
					$el.ensureInview(true);
				},0);
			}else{
				$('html, body').animate({
					scrollTop: $el.offset().top
				},50);
				console.log('done');
				setTimeout(function(){
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
		var video = v[0];
		video.CurrentTime = 0;
		video.play();
		video.loop = true;
		video.onended = function(){
			video.CurrentTime = 0;
			video.play();
		};
	}
}

function adPlay_image()
{
	
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
	
	
	var slide = {
		el: null,
		created: false,
		go: function(a){
			slideIt(a);
		}
	};
	
	z = 0;
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
			$.app.done();
		}
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
			$('[data-slug="/explore/'+country+'"]').html(data.message).addClass('done').closest('[data-viewport]').ensureInview();
			
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
			el.html(data.message).closest('[data-viewport]').ensureInview();
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
		
	})
	.get('/extras/memes/:uri',function(data){
		var meme = data.uri;
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
				el.closest('[data-viewport]').ensureInview();
				$.app.done();
			});
		}else{
			el.closest('[data-viewport]').ensureInview();
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
			slide.el = $('.owl-carousel').owlCarousel({
				navigation : false,
				slideSpeed : 300,
				pagination : false,
				singleItem:true,
				mouseDrag:false,
				touchDrag:false,
				afterInit: function(){
					$('.owl-carousel').find('.item').addClass('done');
					setTimeout(function(){
						console.log(typeof slide.el);
						slide.created = true;
					},300);
				}
			}).data('owlCarousel');
			var z = [
				data.message.slugs.countries
			];
			var y = ['countries'];
			
			for(var j =0; j < z.length;j++)
			{
				var html = '';
				for(var i = 0; i < z[j].length;i++){
					html +='<div class="row" data-viewport><div class="column slug" data-slug="'+z[j][i]+'"></div></div>';
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
	
	//handle click event
	$(document).on('click','[data-href]',function(e){
		var href = $(this).data('href');
		if(href !== location.pathname){
			$.app.go(href);
		}
	});
	
	$(window).on('scroll',function(e){
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
				}
			},80);
		}
	})
	
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
	
	setInterval(function(e){
		$('img:failed').each(function(){
			iE(this);
		});
	},1500);
});