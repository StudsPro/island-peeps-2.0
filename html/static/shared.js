var sk = {
	keepalive : function(){
		var interval = 5 * 60000;
		var t = setInterval(function(){
			$.getJSON(window.location.origin+'/api/keepalive?c='+sk.csrf(),function(data){
				console.log(data);
				if(data.error == 1){
					clearInterval(t);
				}
			});
		}, interval );
	},
	
	csrf : function(){
		return encodeURIComponent($('meta[name="csrf"]').attr('content'));
	},
	loggedin: false,
	
	alert: function(_m,_t){
		console.log(_m);
		/*
		return noty({
			text: _m,
			type:_t,
			layout:'topCenter',
			theme: 'relax',
			timeout: 10000,
			animation: {
				open: 'animated pulse',
				close: 'animated flipOutX',
			}
		});
		*/
	},

	confirm: function(_text,confirmCB,args){
		/*
		noty({
			text: _text,
			layout: 'center',
			theme: 'relax',
			buttons: [
				{addClass: 'btn btn-sucess', text: 'Continue', onClick: function($noty) {
						$noty.close();
						window[confirmCB].call(window,args);
					}
				},
				{addClass: 'btn btn-danger', text: 'Cancel', onClick: function($noty) {
						$noty.close();
						alertHandle('the action was cancelled','information');
					}
				}
			]
		});
		*/
	}
	
};

$(function(){
	//ajax form submit without file upload
	$(document).on('submit','form[method="post"]:not(.contains-file)',function(event){
		console.log('--ajax form v2--');
		event.preventDefault();
		var _this         = $(this),
		before_function   = _this.attr('data-before'),
		callback_function = _this.attr('data-callback'),
		endpoint          = _this.attr('action'),
		query_string      = _this.serialize()+'&'+$.param({'c':sk.csrf()});
		_this.find('button[type="submit"]').prop('disabled',true).addClass('m-progress').promise().done(function(){
			if(typeof before_function !== "undefined" && before_function !== false){
				var before_error = window[before_function].call(_this);
				if(before_error !== false){
					sk.alert(before_error,'error');
					return;
				}
			}
			if(typeof callback_function === "undefined"){
				sk.alert('FORM DOES NOT SPECIFY CALLBACK. UNABLE TO CONTINUE.','error');
				return;
			}
			$.post(endpoint,query_string,function(data){
				var data = JSON.parse(data);
				window[callback_function].call( _this, data );
			}).fail(function( jqXHR, textStatus, errorThrown ){
				window[callback_function].call(_this,{'error':1,'message': jqXHR.status +' '+ jqXHR.responseText});
			}).always(function(){
				_this.find('button[type="submit"]').removeClass('m-progress').prop('disabled',false);
			});
		});
		return false;
	});
});