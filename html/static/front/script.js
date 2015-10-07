$.fn.inView =  function(){

    var rect = this[0].getBoundingClientRect();

    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
	
};

//setup the google analytics object.
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', 'UA-68535823-1', 'auto');
  
$(function(){
	var previous = null;
	
	$.app = { 
		get : function(a,b){
			$.router.add(a,b);
			return $.app;
		}, 
		go: function(a){ 
			previous = location.pathname; 
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
				callback.apply(this,callback,[]);	
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
		created: false,
		go: function(a){
			if(!slide.created){
				loadCss('/static/front/owl-carousel/owl.carousel.css');
				loadJs('/static/front/owl-carousel/owl.carousel.min.js',function(){
					$.getJSON(window.location.origin+'/api/v1/get_slider',function(data){
						$('[data-slider]').append(data.message);
						
					});	
				});
			}
			//find a way to programmatically toggle slides.
			$.app.done();
		}
	};
	
	$.app
	.get('/',function(){
		//index page redirects to slide.
		$.app.go('/home');
	})
	.get('/explore/:country',function(data){
		//country pages
		var country = data.country;
		$.getJSON(window.location.origin+'/api/v1/get_country?uri='+country,function(done){
			$('[data-slug="/explore/'+country+'"]').replaceWith(data.message);
		});
	})
	.get('/explore/:country/:person',function(data){
		var country  = data.country, 
		person   	 = data.person;
	})
	.get('/meme/:uri',function(data){
		var meme = data.uri;
	})
	.get('/fun-fact/:uri',function(data){
		var funfact = data.uri;
	})
	.get('/:page',function(data){
		slide.go(data.page);
	});
	
	$(document).foundation();
	
	$(window).load(function(){
		$.getJSON(window.location.origin+'/api/v1/init',function(data){
			$('meta[name="csrf"]').attr('content',data.message);
			$.getJSON(window.location.origin+'/api/v1/getframeskeleton',function(data){
				var slides = data.message.slides;
				var countries = data.message.countries;
				var memes = data.message.memes;
				var funfacts = data.message.funfacts;
				$.app.go(location.pathname);
			});
		});
	});
	
	function handleInteractions(e)
	{
		
		if(!target.inView()){
			if(target[0].hasOwnProperty('scrollIntoview')){
				target[0].scrollIntoView();
			}else{
				//find some fallback way of scrolling element into view. 
			}
		}
	}
	
	$(window).on('scroll',handleInteractions);
});