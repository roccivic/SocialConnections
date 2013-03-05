var images = [];
var results = [];

$(document).bind('pageinit', function () {
	if ($('#recognise').length) {
		$('#recognise').remove();
		setTimeout(function () {
			$.mobile.showPageLoadingMsg();
		});
		var gid = parseInt($('#gid').text(), 10) || 0;
		var session = $('#session').text().replace(/\s*/g, "");
		$('#studentlist').find('img:not(.avatar)').each(function () {
			images.push($(this).attr('src'));
		});
		recognise(gid, session);
		$('form').bind('submit', function (e) {
			e.preventDefault();
		});
	}
});

function recognise(gid, session) {
	var image = images.shift();
	if (image) {
		$.post(
			'recognise.php',
			{
				'gid': gid,
				'session': session,
				'image': image
			},
			function (data) {
				results.push(parseInt(data));
				recognise(gid, session);
			}
		);
	} else {
		finish();
		$.mobile.hidePageLoadingMsg();
	}
}

function finish() {
	$('#studentlist').find('li h3').each(function () {
		var $students = $('select.students:first');
		$(this).replaceWith($students.clone());
	});
	for (var i in results) {
		$('#studentlist').find('li select').eq(i).val(results[i]);
	}
	update();
	$('#studentlist')
		.trigger("create")
		.find('select')
		.bind('change', function () {
			update();
		});



	$('form').unbind('submit').bind('submit', function (e) {
		e.preventDefault();

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

	console.log('done');
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
