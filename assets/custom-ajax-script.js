jQuery(document).ready(function ($) {
    $('.ajax-button').on('click', function () {
        var button = $(this); // Store a reference to the clicked button
        var eventForm = button.closest('.ajax-form');
        var selectedRadio = eventForm.find('input[name="match_status"]:checked');
        var sendNotificationCheckbox = eventForm.find('.send-notification-checkbox');

        var eventData = {
            action: 'save_event_data',
            event_id: eventForm.data('event-id'),
            selected_date: eventForm.data('selected-date'),
            meta_value1: eventForm.find('input[name="meta_value1"]').val(),
            meta_value2: eventForm.find('input[name="meta_value2"]').val(),
            match_status: selectedRadio.val(), // Get the selected radio button value
        };

        // Check if the send notification checkbox is checked
        if (sendNotificationCheckbox.prop('checked')) {
            button.next('.update-match').text('Match Updated & Notify').fadeIn().delay(1000).fadeOut();
        } else {
            button.next('.update-match').text('Match Updated').fadeIn().delay(1000).fadeOut();
        }

        // AJAX request
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: eventData,
            success: function (response) {
                // Handle success
            },
            error: function (error) {
                // Handle error
                console.log(error.responseJSON.data);
            },
        });
    });

    // Handle change event for radio buttons
    $('.ajax-form').on('change', 'input[name="match_status"]', function () {
        var eventForm = $(this).closest('.ajax-form');
        var selectedValue = $(this).val();

        // Update post meta
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'update_post_meta',
                event_id: eventForm.data('event-id'),
                meta_key: 'match_live',
                meta_value: selectedValue,
            },
            success: function (response) {
                console.log(response.data);
            },
            error: function (error) {
                console.log(error.responseJSON.data);
            },
        });
    });
});
