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
			}
		});	
	}
});