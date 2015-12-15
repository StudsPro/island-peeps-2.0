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
	
	if($('[data-permissions]').length > 0){
		var p = $('[data-permissions]').data('permissions');
		var d = !!parseInt($('[data-permissions]').data('super'));
		for(var key in p){
			if (p.hasOwnProperty(key)) {
				for (var key2 in p[key]){
					if(p[key].hasOwnProperty(key2)){
						if(p[key][key2] == 1){
							$(':checkbox[name="permissions['+key+']['+key2+']"]').prop('checked',true).prop('disabled',d);
						}
					}
				}
			}
		}
		$('tbody tr').each(function(i,v){
			if($(this).find(':checkbox:checked').length === $(this).find(':checkbox').length - 1){
				$(this).find(':checkbox.chkallrow').prop('checked',true).prop('disabled',d);
			}
		});
		if($('.chkallrow:checked').length == $('.chkallrow').length){
			$('.chkall').prop('checked',true).prop('disabled',d);
		}
	}
});
