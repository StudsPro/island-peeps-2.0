var scroll_lock = false;
var scroll_int = null;
var scroll_last = 0;

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



$.fn.ensureInview = function(){
	
	if(!this.inView()){
		scroll_lock = true;
		scroll_last = this.offset().top;
		$('html, body').animate({
			scrollTop: scroll_last
		}, 100);
		setTimeout(function(){ scroll_lock = false},500);
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
	}
	
});



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
				'page': window.location.pathname + window.location.search,
				'title': $('title').html()
			});
			$('body').removeClass('preload');
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
		$.getJSON(window.location.origin+'/api/v1/get_country?uri='+country,function(done){
			$('[data-slug="/explore/'+country+'"]').html(data.message).addClass('done');
			if(typeof callback === 'function'){
				callback.apply(this,[]);
			}
		});
	}
	
	function loadPerson(country,person)
	{
		
	}
	
	$.app
	.get('/',function(){
		//index page redirects to slide.
		$.app.go('/home');
	})
	.get('/explore/:country',function(data){
		//country pages
		var country = data.country;
		loadCountry(data.country);
	})
	.get('/explore/:country/people/:person',function(data){
		var country  = data.country, 
		person   	 = data.person;
		if($('[data-slug="/explore/'+country+'"].done').length  == 0){
			loadCountry(country,function(){
				loadProfile(country,person);
			});
		}else{
			
		}
		
	})
	.get('/meme/:uri',function(data){
		var meme = data.uri;
	})
	.get('/explore/:country/fun-fact/:uri',function(data){
		var country = data.country,
		funfact = data.uri;
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
	
	$(window).load(function(){
		$.getJSON(window.location.origin+'/api/v1/init',function(data){
			$('meta[name="csrf"]').attr('content',data.message.csrf);
			$('[data-slider]').append(data.message.slider).addClass('done');
			slide.el = $('.owl-carousel').owlCarousel({
				navigation : false,
				slideSpeed : 300,
				pagination : false,
				singleItem:true,
				mouseDrag:false,
				touchDrag:false,
				afterInit: function(){
					$('.owl-carousel').find('.item').addClass('done');
					slide.created = true;
					$.app.go(location.pathname);
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
					html +='<div class="column slug" data-slug="'+z[j][i]+'"></div>';
					if(i == z[j].length -1){
						$('[data-'+y[j]+']').html(html);
					}
				}
			}
		});
	});
	
	//handle click event
	$(document).on('click','[data-href]',function(e){
		var href = $(this).data('href');
		if(href !== location.pathname){
			$('[data-slug="'+href+'"]').closest('[data-viewpoint]').ensureInview();
			$.app.go(href);
		}
	});
	
	//handle scroll event
	$(window).on('scroll',function(){
		if(scroll_int != null) clearTimeout(scroll_int);
		scroll_int = setTimeout(function(){
			var scroll = window.pageYOffset || this.scrollTop;
			if(scroll > scroll_last){
				var el = $('[data-slug]:not(:parentofcurrent):not(:childofslug):not(:current):partial:belowcurrent').reduceToClosest();
			}else{
				var el = $('[data-slug]:not(:parentofcurrent):not(:childofslug):not(:current):partial:abovecurrent').reduceToClosest();
			}
			scroll_last = scroll;
			if(el.length > 0 && !scroll_lock){
				el.ensureInview();
				$.app.go(el.data('slug'));	
				
			}
			
		},50);
	});
});