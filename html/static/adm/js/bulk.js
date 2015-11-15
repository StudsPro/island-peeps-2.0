var bulk_html = '';

$(function(){
	$(window).load(function(){
		bulk_html = $('table > tbody').html();
		bulk_html = bulk_html.replace('<td>-</td>','<td><a href="#" class="remove-field">&times;</a></td>');
	});
	
	$(document).on('click','.add-field',function(e){
		e.preventDefault();
		
		$('table > tbody').append(bulk_html);
		numRows(1);
		return false;
	});
	
	$(document).on('click','.remove-field',function(e){
		e.preventDefault();
		
		$(this).parents('tr').remove();
		numRows(-1);
		return false;
	});
	
	function numRows(i)
	{
		var num = parseInt( $('input[name="num_rows"]').val() );
		$('input[name="num_rows"]').val( num + i );
		
		var j = 0;
		$('input[type="file"]').each(function(i,v){
			$(this).attr('name','file_'+j);
			j++;
		});
	}
});