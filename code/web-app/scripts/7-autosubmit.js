$(document).bind('pageinit', function () {
	$('select.autosubmit').bind('change', function () {
		$(this).closest('form').submit();
	});
});