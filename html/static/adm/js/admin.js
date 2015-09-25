$(function(){
	
	//no conflict for tooltips and jqUI
	var bootstrapTooltip = $.fn.tooltip.noConflict()
	$.fn.bootstrapTooltip = bootstrapTooltip;
	
	//updating users theme preference
	
	
	//updating users custom admin sidebar
	$("#sort_items ul").sortable({
		containment: "parent", 
		update: function(){
		
		}
	});
	
});