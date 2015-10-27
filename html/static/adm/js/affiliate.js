$(function(){
	$(window).on('load',function(){
		var el = $('input[name="password"],input[name="name"]').val('').attr('style','');
		el.each(function(i,v){
			if($(this).data('value') !== "undefined"){
				$(this).val( $(this).data('value') );
			}
		})
	});
	
	$(document).on('change','.chkall',function(e){
		$('table :checkbox').prop('checked',$(this).prop('checked'));
	});
	
	$(document).on('change','.chkallrow',function(e){
		$(this).parents('tr').find(':checkbox').prop('checked',$(this).prop('checked'));
	});
});
