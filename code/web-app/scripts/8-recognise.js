$(document).bind('pageinit', function () {
	if ($('#recognise').length) {
		$('#recognise').remove();
		setTimeout(function () {
			$.mobile.showPageLoadingMsg();
		});
		var gid = parseInt($('#gid').text(), 10) || 0;
		var session = $('#session').text().replace(/\s*/g, "");
		recognise(gid, session);
		$('form').bind('submit', function (e) {
			e.preventDefault();
		});
	}
});

function recognise(gid, session) {
	$.post(
		'recognise.php',
		{
			'gid': gid,
			'session': session
		},
		function (data) {
			$.mobile.hidePageLoadingMsg();
			finish(data);
		}
	);
}


function finish(results) {
	$('#studentlist').find('li h3').each(function () {
		var $students = $('select.students:first');
		$(this).replaceWith($students.clone());
	});

	$('#studentlist').find('img:not(.avatar)').each(function () {
		var url = $(this).attr('src');
		var filename = url.substring(url.lastIndexOf('/') + 1);
		if (typeof results[filename] !== 'undefined') {
			$(this).closest('li').find('select').val(
				results[filename]['sid']
			);
			$(this).closest('li').append(
				$('<p />').addClass('confidence')
					.css({
						clear: "both",
						margin: 0,
						padding: "10px 10px 0px 0px",
						'font-weight': 'normal'
					})
					.text('Confidence: ' + results[filename]['confidence'] + ' (lower is better)')
			);
		}
	});

	update();
	$('#studentlist')
		.trigger("create")
		.find('select')
		.bind('change', function () {
			$(this).closest('li').find('p.confidence').remove();
			update();
		});

	$('form').unbind('submit').bind('submit', function (e) {
		e.preventDefault();

		var gid = parseInt($('#gid')[0].innerHTML, 10) || 0;
		var session = $('#session')[0].innerHTML.replace(/\s*/g, "");

		var params = '';
		params += 'gid=' + gid;
		params += '&session=' + session;
		params += '&date=' + $('#date').val();
		params += '&time=' + $('#time').val();
		params += '&isLecture=' + $(this).find('[name=isLecture]:checked').val();			
		$('#studentlist').find('li').each(function () {
			var $select = $(this).find('select');
			if ($select.val() > 0) {
				params += '&students[]=' + $select.val();
				var $img = $(this).find('img:not(.avatar)');
				if ($img.length) {
					params += '&images[]=' + $img.attr('src');
				} else {
					params += '&images[]=0';
				}
			}
		});
		$.post(
			'ajax-take-attendance.php',
			params,
			function (data) {
				var $popup = $('<div></div>').hide().addClass('notification');
				if (data.success) {
					$popup.addClass('notice');
					$('form').hide();
				} else {
					$popup.addClass('error');
				}
				$popup
					.append(data.message)
					.prependTo('.content-primary')
					.show();
				$('body').trigger('create');
			}
		);
	});
}

function update() {
	var used = {};
	$('#studentlist').find('li select').each(function () {
		var val = $(this).val();
		if (typeof used[val] === 'undefined') {
			used[val] = 1;
		} else {
			$(this).val(0);
		}
	}).each(function () {
		$(this).find('option:not(:selected)').each(function () {
			var val = $(this).val();
			if (val > 0 && typeof used[val] !== 'undefined') {
				$(this).css('display', 'none');
			} else {
				$(this).css('display', '');
			}
		});
	});
}
