var init = {
	styles: [
		'http://fonts.googleapis.com/css?family=Pacifico|Aeolus|BlackJack|koala|Learning+Curve+Dashed',
		'/static/front/grid.css',
		'/static/front/style.css',
		'/static/front/owl-carousel/owl.carousel.css',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css',
		'/static/front/lightbox/css/lightbox.css',
		'https://api.tiles.mapbox.com/mapbox.js/v1.6.4/mapbox.css',
		'/static/shared.css',
		'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css',
	],

	loadCss : function(file)
	{
		console.log(file);
		if(typeof file === 'undefined' || file === false){
			return false;
		}
		if(file.indexOf('http://') === -1 || file.indexOf('https://') === -1){
			var url = window.location.origin + file;
		}else{
			var url = file;
		}
		if($('link[href="'+link+'"]').length > 0){
			return false;
		}
		var link = document.createElement('link');
		link.type = 'text/css';
		link.rel = 'stylesheet';
		link.href = url;
		document.head.appendChild(link);
	},

	run: function(){
		while(style = init.styles.shift() || init.styles.length){
			init.loadCss(style);
		}
	}
};

$(function(){
	
	init.run();
	
});
