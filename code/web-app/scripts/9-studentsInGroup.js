$(document).bind('pageinit', function () {
	if ($('#manageStudentList').length) {
		$('#manageStudentList').find('a').click(function (e) {
			e.preventDefault();
			$.mobile.showPageLoadingMsg();
			var $link = $(this);
			$.get($link.attr('href'), { ajax:1 }, function (data) {
				if (data.success) {
					ajaxShowMessage(data.message, 'notice');
					$.mobile.hidePageLoadingMsg();
					$link.closest('li').slideUp(function () {
						$(this).remove();
					});
				} else {
					$.mobile.hidePageLoadingMsg();
					ajaxShowMessage(data.message, 'error');
				}
			});
		});
	}
});
