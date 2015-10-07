$(function() {
	UIElements.initWYSIWYGEditors();
	var isRTL = $("html").attr("dir") === "rtl" ? "rtl" : "ltr";
	CKEDITOR.replace('ckeditor', {
	  contentsLangDirection: isRTL
	});
});