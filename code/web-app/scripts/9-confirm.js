$(document).bind('pageinit', function () {
	function confirmAction($elm) {
		return confirm($elm.text().replace(/\s+/g, ' '));
	}
	$('a.delete').click(function () {
		return confirmAction($(this).next());
	});
	$('input.delete').click(function () {
		return confirmAction($(this).parent().next());
	});
});