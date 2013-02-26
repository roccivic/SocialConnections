$(document).bind('pageinit', function () {
	function setCheckboxes(state) {
		return $(this)
			.closest('form')
			.find(':checkbox')
			.prop('checked', state)
			.checkboxradio("refresh");
	}
	$('#checkall').bind('click', function () {
		setCheckboxes.call(this, true);
	});
	$('#uncheckall').bind('click', function () {
		setCheckboxes.call(this, false);
	});
});