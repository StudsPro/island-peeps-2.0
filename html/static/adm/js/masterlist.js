$.fn.hasAttr = function(name) {  
   return this.attr(name) !== undefined && this.attr(name) !== false;
};

$(function(){
	var tbl = $('#newtable');
	if(tbl.find('tbody > tr').length > 1){
		var per = tbl.data('perpage');
		if(typeof per === undefined || per === false){
			per = 100;
		}
		var table = tbl.DataTable({
			"sDom": "<'row' <'col-xs-3'l><'col-xs-6'f>r>t<'row'<'col-xs-4'i><'col-xs-7'p> >",
			"aaSorting": [[0, "asc"]],
			"iDisplayLength": per,
			"responsive": true,
			"language": {
				"lengthMenu": "_MENU_ <span class='hidden-xs'>records per page </span>"
			},
			"pageLength": 5,
		});	
	}
	
	$('select[name="sort"]').on('change',function(e){
		if(this.value == 'invalid') return false;
		queryMasterList();
	});
	
	$('select[name="cat_id"]').on('change',function(e){
		if(this.value == 'invalid') return false;
		queryMasterList();
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
							for(var j=0;j<ids.length;j++){
								tbl.row( $('table tbody tr[data-id="'+ids[j]+'"]') ).remove();
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
});