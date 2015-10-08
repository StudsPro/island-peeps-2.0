$.fn.inView = function(){

    var rect = this[0].getBoundingClientRect();

    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
	
};

$.fn.ensureInview = function(){
	if(!this.inView()){
		$('html, body').animate({
			scrollTop: this.offset().top
		}, 100);
		/*
		if(this[0].hasOwnProperty('scrollIntoView')){
			this[0].scrollIntoView();
		}
		*/
	}
	
};

$.extend(	$.expr[':'], {  
	inview: function (el) {    
		return $(el).inView();
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
			$.app.done();
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
	.get('/:country/fun-fact/:uri',function(data){
		var funfact = data.uri;
	})
	.get('/:page',function(data){
		slide.go(data.page);
	})
	.get('/profiles/recently-added',function(){
		var el = $('[data-slug="/profiles/recently-added"]');
		if(!el.hasClass('done')){
			$.getJSON(window.location.origin+'/api/v1/get_recent',function(data){
				el.html(data.message).addClass('done').ensureInview();
			});
		}else{
			el.ensureInview();
		}
	});
	
	$(document).foundation();
	
	$(window).load(function(){
		$.getJSON(window.location.origin+'/api/v1/init',function(data){
			$('meta[name="csrf"]').attr('content',data.message);
			$.getJSON(window.location.origin+'/api/v1/getframeskeleton',function(data){
				loadCss('/static/front/owl-carousel/owl.carousel.css');
				loadJs('/static/front/owl-carousel/owl.carousel.min.js',function(){
					$.getJSON(window.location.origin+'/api/v1/get_slider',function(data){
						$('[data-slider]').append(data.message).addClass('done');
						slide.el = $('.owl-carousel').owlCarousel({
							navigation : false,
							slideSpeed : 300,
							pagination : false,
							singleItem:true,
							mouseDrag:false,
							touchDrag:false,
						}).data('owlCarousel');
						$('.owl-carousel').find('.item').addClass('done');
						slide.created = true;
					});	
				});
				var z = [
					data.message.countries
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
				
				$.app.go(location.pathname);
			});
		});
	});
	
	
	
	$(document).on('click','[data-href]',function(e){
		var href = $(this).data('href');
		if(href !== location.pathname){
			$('[data-slug="'+href+'"]').closest('[data-viewpoint]').ensureInview();
			$.app.go(href);
		}
	});
	
	var scroll_int = null;
	$(window).on('scroll',function(){
		if(scroll_int != null) clearTimeout(scroll_int);
		scroll_int = setTimeout(function(){
			var el = $(':not(.done) [data-slug]:not(.done):inview:visible').eq(0);
			el.ensureInview();
			$.app.go(el.data('slug'));
		},50);
	});
});