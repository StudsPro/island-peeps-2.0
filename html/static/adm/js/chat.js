$.fn.scrollDiv = function()
{		
	var scrollTo_int = $(this).prop('scrollHeight') + 'px';
	$(this).slimScroll({
		scrollTo : scrollTo_int,
		height: '380px',
		start: 'bottom',
		alwaysVisible: true,
		wheelStep: 20,
	});
}

$(function(){
	
	$(".maxheightt").slimScroll({
	  height: '450px',
	});
	 $(".maxheight_visit").slimScroll({
	  height: '450px',
	});
	$(".maxheight_resouce").slimScroll({
	  height: '450px',
	});

	$(".maxheight_calender").slimScroll({
	  height: '450px',
	});
	
	$(".maxheight_recent").slimScroll({
	  height: '435px',
	});
	
	
	$(window).on('load',function(){
		$.getJSON(window.location.origin + '/admin/api/chat_log',function(data){
			if(data.error == 0){
				chatAppend(data.message);
			}
		})
	});
	
	function chatAppend(msgs)
	{
		var html = '';
		for(var i=0; i < msgs.length; i++){
			var m = msgs[i];
			html += '<li class="left clearfix" data-id="'+m.id+'">'
			html += '<span class="chat-avatar pull-left"><img src="'+m.avatar+'" alt=""></span>'
			html += '<div class="chat-body clearfix">'       
			html += '<div class="header"><strong class="primary-font">'+m.username+' </strong></div>'
			html += '<p class="chat-body-content">'+m.message+'</p></div>'
			html += '<div><small class="text-muted time-div"><span class="fa fa-clock-o fa2 time-icon"></span><span data-livestamp="'+m.timestamp+'"></span></small><button class="btn btn-mini btn-danger chat-delete pull-right" type="button">Delete</button></div></li>'
			if(i == msgs.length -1){
				$('[data-chatlog]').append(html).scrollDiv();
			}
		}
	}
	
	function chatUpdate(n)
	{
		
		var last = $('[data-chatlog] li[data-id]').last().data('id');
		$.getJSON(window.location.origin+'/admin/api/chat_latest?last_id='+last,function(data){
			if(data.error == 0 && data.message.length > 0){
				chatAppend(data.message);
			}
		}).always(function(){
			if(t == false){
				t = setInterval(chatUpdate, n || 4500);
			}
		});
	}
	
	function chatSend() 
	{
		if($('#chat-input').val() == ""){
			return false;
		}
		$.getJSON(window.location.origin+'/admin/api/chat_post?msg='+encodeURIComponent($('#chat-input').val()),function(data){
			if(data.error == 0){
				$('#chat-input').val('');
				clearInterval(t);//need to disable update while we get our posted message.
				t = false;
				chatUpdate();//will update the chat and resume the interval. 
			}
		});
		
	}
	
	var t = false;
	chatUpdate(60000); //inits t into an interval.

	$(document).on("click",'#chat-button',function(event){
		if($('#chat-input').val() != ""){
			chatSend();
		}
	});
	
	$(document).on("keyup",'#chat-input',function(event){
		if(event.which == 13 && $(this).val() != "")
		{
			chatSend();
		}
	});
	
	$(document).on('click','.chat-delete',function(e){
		sk.confirm('You are about to delete this message. are you sure?',function(id){
			$.getJSON(window.location.origin+'/admin/api/chat_delete?id='+id,function(data){
				if(data.error == 0){
					sk.alert('The Message Was Removed Successfully','success');
					$('[data-chatlog] li[data-id='+id+']').hide().remove();
				}else{
					sk.alert(data.message,'error');
				}
			});
		},[$(this).closest('li').data('id')]);
	});
	
	$(document).on('click','.chat .panel-heading',function(){
		clearInterval(t);
		t= false;
		$('.chat').toggleClass('no-trans');
		if(!$('.chat').hasClass('minimized')){
			$('.chat').addClass('minimized');
			chatUpdate(60000);//reduce interval to 1 minute while minimized.
		}else{
			$('.chat').removeClass('minimized')
			$('[data-chatlog]').scrollDiv();
			chatUpdate();
		}
	});
});