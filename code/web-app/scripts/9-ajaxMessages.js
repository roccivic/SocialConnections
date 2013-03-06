var ajax_message_count = 0;
function ajaxShowMessage(message, type, timeout)
{
    if (! message) {
        return true;
    }
    if (! type) {
        type = 'notice';
    }
    // Figure out whether (or after how long) to remove the notification
    if (timeout == undefined) {
        timeout = 2500;
    }
    // Create a parent element for the AJAX messages, if necessary
    if ($('#loading_parent').length == 0) {
        $('<div id="loading_parent"></div>')
        .prependTo("div.content-primary");
    }
    // Update message count to create distinct message elements every time
    ajax_message_count++;
    // Remove all old messages, if any
    $("span.ajax_notification[id^=ajax_message_num]").remove();
    /**
     * @var    $retval    a jQuery object containing the reference
     *                    to the created AJAX message
     */
    var $retval = $(
            '<span class="notification ajax_notification '
            + type
            + '" id="ajax_message_num_'
            + ajax_message_count +
            '"></span>'
    )
    .css('padding-left', '35px')
    .hide()
    .appendTo("#loading_parent")
    .html(message)
    .show();

    $retval
    .delay(timeout)
    .fadeOut('medium', function() {
        // Remove the notification
        $(this).remove();
    });

    return $retval;
}

function ajaxRemoveMessage($this_msgbox)
{
    if ($this_msgbox != undefined && $this_msgbox instanceof jQuery) {
        $this_msgbox
        .stop(true, true)
        .fadeOut('medium', function () {
        	$this_msgbox.remove();
        });
    }
}