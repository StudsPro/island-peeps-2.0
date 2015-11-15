$.fn.hasAttr = function(name) {  
   return this.attr(name) !== undefined && this.attr(name) !== false;
};

$(function(){
	var tbl = $('#newtable');
	if(tbl.find('tbody > tr').length > 1){
		var per = tbl.data('perpage');
		if(typeof per === undefined || per === false || location.pathname == '/admin/countries'){
			per = 100;
		}
		var table = tbl.DataTable({
			"sDom": "<'row' <'col-xs-3'l><'col-xs-6'f>r>t<'row'<'col-xs-4'i><'col-xs-7'p> >",
			"aaSorting": [[1, "asc"]],
			"iDisplayLength": per,
			"responsive": true,
			"language": {
				"lengthMenu": "_MENU_ <span class='hidden-xs'>records per page </span>"
			},
			"bProcessing": true,
			"bLengthChange": true
		});	
	}
	
	$('select[name="sort"]').on('change',function(e){
		if(this.value == 'invalid'){
			var qs = '?';
			if($('table').hasAttr('data-type')){
				qs += 'type_id='+$('table').data('type')+'&';
			}
			if($('select[name="cat_id"]').val() !== 'invalid'){
				qs += 'cat_id='+$('select[name="cat_id"]').val()+'&';
			}
			if(qs.slice(-1) == '&'){
				qs = qs.slice(0,-1);
			}
			window.location = window.location.origin+'/admin/masterlist'+qs;
			
		}else{
			queryMasterList();
		};
	});
	
	$('select[name="cat_id"]').on('change',function(e){
		if(this.value == 'invalid'){
			var qs = '?';
			if($('table').hasAttr('data-type')){
				qs += 'type_id='+$('table').data('type')+'&';
			}
			if($('select[name="sort"]').val() !== 'invalid'){
				qs += 'sort='+$('select[name="sort"]').val()+'&';
			}
			if(qs.slice(-1) == '&'){
				qs = qs.slice(0,-1);
			}
			window.location = window.location.origin+'/admin/masterlist'+qs;
		}else{
			queryMasterList();
		};
	});
	
	function queryMasterList()
	{
		var qs = '?';
		if($('table').hasAttr('data-type')){
			qs += 'type_id='+$('table').data('type')+'&';
		}
		if($('select[name="cat_id"]').val() !== 'invalid'){
			qs += 'cat_id='+$('select[name="cat_id"]').val()+'&';
		}
		if($('select[name="sort"]').val() !== 'invalid'){
			qs += 'sort='+$('select[name="sort"]').val()+'&';
		}
		if(qs.slice(-1) == '&'){
			qs = qs.slice(0,-1);
		}
		window.location = window.location.origin+'/admin/masterlist'+qs;
	}
	
	$('#mlist-preview').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget);
	  var href = button.data('href');
	  var modal = $(this);
	  modal.find('.modal-body').find('.container').load(href);
	});
	
	$(document).on('click','.bulk-update',function(e){
		var checked = $('table tbody').find(':input:checked');
		if(checked.length > 0){
			$('.bulk-update').prop('disabled',true);
			var tbl = $(this).data('table');
			var col = $(this).data('column');
			var val = $(this).data('val');
			var callback = $(this).data('callback');
			var ids = '';
			for(var i =0;i<checked.length;i++){
				ids += checked.eq(i).val()+',';
				if(i == checked.length - 1){
					ids = ids.slice(0,-1);
					$.getJSON(window.location.origin+'/admin/api/bulk_update?table='+tbl+'&column='+col+'&value='+val+'&ids='+ids,function(data){
						window[callback].call(window,data,tbl,col,val,ids);
					}).always(function(){
						$('.bulk-update').prop('disabled',false);
					});
				}
			}
		}
	});
	
	$(document).on('click','.bulk-delete',function(e){
		var checked = $('table tbody').find(':input:checked');
		if(checked.length > 0){
			$('.bulk-delete').prop('disabled',true);
			var tbl = $(this).data('table');
			var callback = $(this).data('callback');
			var ids = '';
			for(var i =0;i<checked.length;i++){
				ids += checked.eq(i).val()+',';
				if(i == checked.length - 1){
					ids = ids.slice(0,-1);
					$.getJSON(window.location.origin+'/admin/api/bulk_delete?table='+tbl+'&ids='+ids,function(data){
						if(data.error==0){
							ids = ids.split(',');
							for(var j=0;j<ids.length;j++){
								table.row( $('tr[data-id="'+ids[j]+'"]') ).remove().draw();
								sk.alert('The items were deleted','information');
							}
						}else{
							sk.alert('You are not allowed to delete from this table (Permission Denied)','error');
						}
					}).always(function(){
						$('.bulk-delete').prop('disabled',false);
					});
				}
			}
		}
	});
	
	$(document).on('click','.bulk-suggest',function(e){
		var checked = $('table tbody').find(':input:checked');
		if(checked.length > 0){
			var cmd = $(this).data('cmd');
			var ids = '';
			for(var i =0;i<checked.length;i++){
				ids += checked.eq(i).val()+',';
				if(i == checked.length - 1){
					ids = ids.slice(0,-1);
					$.getJSON(window.location.origin+'/admin/api/bulk_suggest?cmd='+cmd+'&ids='+ids,function(data){
						if(data.error==0){
							ids = ids.split(',');
							for(var j=0;j<ids.length;j++){
								table.row( $('tr[data-id="'+ids[j]+'"]') ).remove().draw();
								sk.alert('The changes were saved','information');
							}
						}else{
							sk.alert(data.message,'error');
						}
					});
				}
			}
		}
	});
	
	
	$(document).on('click','#an-cat-n',function(e){
		e.preventDefault();
		
		var category = prompt('Category Name:');
		if (category) 
		{
			$.getJSON(window.location.origin+'/admin/api/create_category?n='+category,function(data){
				if(data.error=="0"){
					var x = data.message;
					var option = '<option value="'+x.id+'">'+x.name+'</option>';
					$('select[name="cat_id"]').append(option).aSort();
					sk.alert('The category was created and added to the category dropdown.','success');
				}else{
					sk.alert(data.message,'error');
				}
			});
		}else{
			sk.alert('Action canceled','information');
		}
				
	});
	
});