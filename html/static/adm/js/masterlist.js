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
			"aaSorting": [[2, "desc"]],
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
});