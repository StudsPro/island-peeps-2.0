$(function() {
	UIElements.initWYSIWYGEditors();
	var isRTL = $("html").attr("dir") === "rtl" ? "rtl" : "ltr";
	
	var each = [
		'ckeditor',
		'ck2',
		'ck3'
	];
	
	for(var i=0;i< each.length;i++){
		CKEDITOR.replace(each[i], {
		  contentsLangDirection: isRTL
		});	
	}
	
});