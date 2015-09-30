$.fn.inView =  function(){

    var rect = this[0].getBoundingClientRect();

    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
	
};

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
		}
	};
	
	
	var slide = {
		created: false,
		go: function(a){
			if(!slide.created){
				//create the slider.
			}
			//find a way to programmatically toggle slides.
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
	})
	.get('/explore/:country/:person',function(data){
		var country  = data.country, 
		person   = data.person;
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
			});
		});
	});
});