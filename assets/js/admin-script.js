jQuery(document).ready(function($) {
    const recurrenceDropdown = $('#recurrence_type');
    const recurrenceCountField = $('#recurrence_fields');
    const customDatesField = $('#custom_dates_fields');
    const exclusionDatesField = $('#exclusion_dates_fields');
    const startDateField = $('#start_date'); // Assuming the ID for the start date input
    const endDateField = $('#end_date'); // Assuming the ID for the end date input
    const startTimeField = $('#start_time'); // Assuming the ID for the start time input
    const endTimeField = $('#end_time'); // Assuming the ID for the end time input
    const allDayEventCheckbox = $('#all_day_event'); // Assuming the ID for the all day event checkbox

    function my_event_calendar_toggle_recurrence_fields() {
        switch (recurrenceDropdown.val()) {
            case 'none':
                recurrenceCountField.hide();
                customDatesField.hide();
                exclusionDatesField.hide();
                break;
            case 'custom_dates':
                recurrenceCountField.hide();
                customDatesField.show();
                exclusionDatesField.hide();
                break;
            default:
                recurrenceCountField.show();
                customDatesField.hide();
                exclusionDatesField.show();
                break;
        }
    }

    // Initialize recurrence fields on page load
    if (recurrenceDropdown.length) {
        recurrenceDropdown.on('change', my_event_calendar_toggle_recurrence_fields);
        my_event_calendar_toggle_recurrence_fields(); // Initial toggle to set the correct display on page load
    }

    // Function to add hours to a time string (HH:MM)
    function my_event_calendar_add_hours(time, hoursToAdd) {
        // Split the time into hours and minutes
        const [hours, minutes] = time.split(':').map(Number);
        let endHours = hours + hoursToAdd;
        let endMinutes = minutes;

        // Handle overflow (e.g., 23:00 + 2 hours = 01:00 next day)
        if (endHours >= 24) {
            endHours = endHours % 24;
        }

        // Pad with leading zeros if necessary
        const formattedHours = endHours.toString().padStart(2, '0');
        const formattedMinutes = endMinutes.toString().padStart(2, '0');

        return `${formattedHours}:${formattedMinutes}`;
    }

    // Update end time when start time changes
    if (startTimeField.length && endTimeField.length) {
        startTimeField.on('change', function() {
            const startTime = $(this).val(); // Expecting format "HH:MM"

            if (startTime) {
                const newEndTime = my_event_calendar_add_hours(startTime, 2); // Add 2 hours
                endTimeField.val(newEndTime);

                if (allDayEventCheckbox.is(':checked')) {
                    // Clear checkbox if start time changes
                    allDayEventCheckbox.prop('checked', false);
                }
            } else {
                // If start time is empty, clear end time
                endTimeField.val('');
            }
        });
    }

    // Update end date when start date changes
    if (startDateField.length) {
        startDateField.on('change', function() {
            const startDate = $(this).val();
            endDateField.val(startDate); // Set end date to match start date
        });
    }


    // If all day event checkbox is checked, clear the time fields
    if (allDayEventCheckbox.length) {
        allDayEventCheckbox.on('change', function() {
            if ($(this).is(':checked')) {
                startTimeField.val('');
                endTimeField.val('');
            }
        });
    }    

    // Check if wpColorPicker is defined
    if (typeof $.fn.wpColorPicker !== 'undefined') {
        $('.mec-color-field').each(function() {
            try {
                // Attempt to initialize the color picker
                $(this).wpColorPicker();
            } catch (error) {
                console.error('Error initializing wpColorPicker:', error);
                // Check for the specific error related to setHSpace
                if (error.message && error.message.includes('setHSpace')) {
                    console.warn('Disabling wpColorPicker due to a setHSpace error.');

                    // Optionally disable the color picker field
                    $(this).replaceWith('<input type="text" class="mec-color-fallback" value="' + $(this).val() + '">');
                    $(this).remove(); // Remove original input with wpColorPicker to prevent issues
                }
            }
        });
    } else {
        // Fallback: Display a simple text input if wpColorPicker is not available
        console.warn('wpColorPicker is not available. Falling back to text input.');
        $('.mec-color-field').each(function() {
            $(this).replaceWith('<input type="text" class="mec-color-fallback" value="' + $(this).val() + '">');
        });
    }
});

