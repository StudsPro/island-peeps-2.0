if (!window.location.origin) {
  window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
}


$.fn.aSort = function(){

	 var cl = this.get(0);
	 var clTexts = new Array();

	 for(i = 2; i < cl.length; i++){
		clTexts[i-2] =
			cl.options[i].text.toUpperCase() + "," +
			cl.options[i].text + "," +
			cl.options[i].value + "," +
			cl.options[i].selected;
	 }

	 clTexts.sort();

	 for(i = 2; i < cl.length; i++){
		var parts = clTexts[i-2].split(',');

		cl.options[i].text = parts[1];
		cl.options[i].value = parts[2];
		if(parts[3] == "true"){
			cl.options[i].selected = true;
		}else{
		   cl.options[i].selected = false;
		}
	 }

}

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
	},

	confirm: function(_text,callback,args){
		noty({
			text: _text,
			layout: 'center',
			theme: 'relax',
			buttons: [
				{addClass: 'btn btn-primary', text: 'Continue', onClick: function($noty) {
						$noty.close();
						callback.apply(window,args);
					}
				},
				{addClass: 'btn btn-danger', text: 'Cancel', onClick: function($noty) {
						$noty.close();
						alertHandle('the action was cancelled','information');
					}
				}
			]
		});
	},
	
	option: function(_text,option1,option2){
		noty({
			text: _text,
			layout: 'center',
			theme: 'relax',
			buttons: [
				{addClass: 'btn btn-primary', text: option1.text, onClick: function($noty) {
						$noty.close();
						option1.callback.apply(window,option1.args);
					}
				},
				{addClass: 'btn btn-danger', text: option2.text, onClick: function($noty) {
						$noty.close();
						option2.callback.apply(window,option2.args);
					}
				}
			]
		});
	}
	
};

$(function(){
	//ajax form submit without file upload
	$(document).on('submit','form[method="post"]:not(.contains-file)',function(event){
		console.log('--ajax form v2--');
		event.preventDefault();
		var _this         = $(this),
		before_function   = _this.attr('data-before'),
		callback_function = _this.data('callback'),
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
			if(typeof callback_function === "undefined" || callback_function === false){
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
	
	$(document).on('submit','form[method="post"].contains-file', function(e){
		console.log('--ajax file upload v2--');
		e.preventDefault();
		var formData = new FormData(this),
		_this         = $(this);
		var before_function   = _this.attr('data-before'),
		callback_function = _this.data('callback'),
		endpoint          = _this.attr('action');
		formData.append('c',sk.csrf());
		_this.find('button[type="submit"]').prop('disabled',true).addClass('m-progress').promise().done(function(){
			if(typeof before_function !== "undefined"){
				if(window[before_function].call(_this) !== false){
					return;
				}
			}
			$.ajax({
				xhr: function(){
					var xhr = new window.XMLHttpRequest();
					xhr.upload.addEventListener("progress", function(evt){
						if (evt.lengthComputable) {
							//sk.progress.update( Math.round( (evt.loaded / evt.total) * 100 ) );
						}
					}, false);
					return xhr;
				},
				beforeSend: function (){
					//sk.progress.start();
				},
				url: endpoint,
				type: "POST",
				data: formData,
				contentType: false,
				cache: false,
				processData:false,
				success: function(data){
					window[callback_function].call( _this , JSON.parse(data) );
				},
				error: function(jqXHR, textStatus, errorThrown ){
					
					window[callback_function].call( _this , {'error':1,'message': jqXHR.status + textStatus} );
				} 	        
			}).always(function(){
				_this.find('button[type="submit"]').removeClass('m-progress').prop('disabled',false);
				//sk.progress.done();
			});
		});
		return false;
	});
	
	$('select.select-onload').each(function(index,value){
		if(typeof $(this).attr('data-selected') !== 'undefined'){
			if($(this).attr('data-selected') !== ''){
				$(this).val( $(this).attr('data-selected') );
				var c = $(this).attr('onchange');
				if(typeof c !== undefined){
					try{
						eval(c);
					}
					catch(e){
						console.log(e);
					}
				}
			}
		}
		if($(this).data('asort') !== 'undefined' && $(this).data('asort') !== false){
			$(this).aSort();
		}
	});
	
	$('.checkbox-onload').each(function(index,value){
		if(typeof $(this).attr('data-selected') !== 'undefined'){
			if($(this).attr('data-selected') == ''){
				return false;
			}
			$(this).find(':checkbox[value="'+$(this).attr('data-selected')+'"]').prop('checked',true);
		}
	});
	
	$('select.multi-select-onload').each(function(index,value){
		var t = $(this);
		if(typeof t.attr('data-selected') !== 'undefined'){
			if(t.attr('data-selected') == ''){
				return false;
			}
			var selected = t.data('selected').toString(); 
			if(selected.indexOf(',') !== -1){
				var use = selected.split(',');
			}else{
				var use = [selected];
			}
			for(i=0;i<use.length;i++){
				t.find('option[value="'+use[i]+'"]')[0].selected = true;
			}
		}
	});
	
	
});