<?php 

function my_events_calendar_share_buttons($post_id) {
    $options = get_option('my_events_calendar_options');
    $show_share_buttons = isset($options['show_share_buttons']) ? $options['show_share_buttons'] : 'yes';
    $show_facebook = isset($options['show_facebook']) ? $options['show_facebook'] : 'yes';
    $show_twitter = isset($options['show_twitter']) ? $options['show_twitter'] : 'yes';
    $show_linkedin = isset($options['show_linkedin']) ? $options['show_linkedin'] : 'yes';
    $show_whatsapp = isset($options['show_whatsapp']) ? $options['show_whatsapp'] : 'yes';
    $show_sms = isset($options['show_sms']) ? $options['show_sms'] : 'yes';
    $show_email = isset($options['show_email']) ? $options['show_email'] : 'yes';
    $show_copy_link = isset($options['show_copy_link']) ? $options['show_copy_link'] : 'yes';

    if ($show_share_buttons === 'yes') {
        $post_url = get_permalink($post_id);
        $post_title = get_the_title($post_id);

        echo '<div class="my-events-calendar-share-buttons">
            <div class="mec-share-button-header"><h3>Share this Event</h3></div>';

        echo '<div class="mec-share-button-container">';

        // Facebook Share Button
        if ($show_facebook === 'yes') {
            echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . urlencode($post_url) . '" target="_blank" class="mec-share-button mec-facebook-share-button"><span class="dashicons dashicons-facebook"></span></a>';
        }

        // Twitter/X Share Button
        if ($show_twitter === 'yes') {
            echo '<a href="https://twitter.com/intent/tweet?text=' . urlencode($post_title) . '&url=' . urlencode($post_url) . '" target="_blank" class="mec-share-button mec-twitter-share-button"><span class="dashicons dashicons-twitter"></span></a>';
        }
        
        // LinkedIn Share Button
        if ($show_linkedin === 'yes') {
            echo '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($post_url) . '" target="_blank" class="mec-share-button mec-linkedin-share-button"><span class="dashicons dashicons-linkedin"></span></a>';
        }

        // WhatsApp Share Button (only displays on mobile devices)
        if ($show_whatsapp === 'yes') {
            echo '<a href="https://wa.me/?text=' . urlencode($post_title . ' - ' . $post_url) . '" target="_blank" class="mec-share-button mec-whatsapp-share-button"><span class="dashicons dashicons-whatsapp"></span></a>';
        }

        // Email Share Button
        if ($show_email === 'yes') {
            $subject = urlencode($post_title); // Encode the subject
            $body = urlencode('Check out this event! ' . $post_url); // Encode the body with some text if needed
            echo '<a href="mailto:?subject=' . $subject . '&body=' . $body . '" class="mec-share-button mec-email-share-button"><span class="dashicons dashicons-email"></span></a>';
        }

        // SMS Share Button
        if ($show_sms === 'yes') {
            echo '<a href="sms:?body=' . urlencode($post_title . ' - ' . $post_url) . '" class="mec-share-button mec-sms-share-button"><span class="dashicons dashicons-smartphone"></span></a>';
        }

        // Copy Link Button
        if ($show_copy_link === 'yes') {
            echo '<a onclick="copyToClipboard()" class="mec-copy-url-button mec-share-button" data-url="' . esc_url($post_url) . '"><span class="dashicons dashicons-admin-links"></span></a>';
        }

        echo '</div></div>';
    }
}

function my_events_calendar_add_to_calendar_buttons($post_id) {
    $options = get_option('my_events_calendar_options');
    $show_add_to_calendar = isset($options['show_add_to_calendar']) ? $options['show_add_to_calendar'] : 'yes';
    $show_add_to_google_calendar = isset($options['show_add_to_google_calendar']) ? $options['show_add_to_google_calendar'] : 'yes';
    $show_add_to_apple_calendar = isset($options['show_add_to_apple_calendar']) ? $options['show_add_to_apple_calendar'] : 'yes';
    if ($show_add_to_calendar === 'yes') {
        // Fetch event metadata
        $post_title = strip_tags(get_the_title($post_id)); // strip html tags and special characters
        $post_url = get_permalink($post_id);
        $start_date = get_post_meta($post_id, '_start_date', true);
        $start_time = get_post_meta($post_id, '_start_time', true);
        $end_date = get_post_meta($post_id, '_end_date', true);
        $end_time = get_post_meta($post_id, '_end_time', true);
        $event_description = strip_tags(get_the_content($post_id)); // strip html tags and special characters
        // Generate Google Calendar link
        $google_calendar_link = 'https://www.google.com/calendar/render?action=TEMPLATE&text=' . urlencode($post_title);
        if ($start_date) {
            $google_calendar_link .= '&dates=' . date('Ymd\THis\Z', strtotime($start_date . ' ' . $start_time)) . '/' . date('Ymd\THis\Z', strtotime($end_date . ' ' . $end_time));
        }
        $google_calendar_link .= '&details=' . urlencode($event_description) . '&location=' . urlencode($post_url);

        // Generate Apple Calendar (iCal) download link
        $ics_content = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n";
        $ics_content .= "SUMMARY:" . str_replace(array("\r", "\n"), '', addcslashes($post_title, ",;")) . "\r\n";
        $ics_content .= "DESCRIPTION:" . str_replace(array("\r", "\n"), '', addcslashes($event_description, ",;")) . "\r\n";
        $ics_content .= "URL:" . addcslashes($post_url, ",;") . "\r\n";
        $ics_content .= "DTSTART:" . date('Ymd\THis\Z', strtotime($start_date . ' ' . $start_time)) . "\r\n";
        $ics_content .= "DTEND:" . date('Ymd\THis\Z', strtotime($end_date . ' ' . $end_time)) . "\r\n";
        $ics_content .= "END:VEVENT\r\nEND:VCALENDAR";

        $ics_url = 'data:text/calendar;charset=utf8,' . rawurlencode($ics_content);

        if (empty($start_date) || empty($end_date)) {
            echo '<script>console.error("Invalid start or end date for the event.");</script>';
        }
        // Output buttons
        echo '<div class="my-events-calendar-add-to-calendar-buttons">';
        // Google Calendar Button
        if ($show_add_to_google_calendar === 'yes') {
            echo '<a href="' . esc_url($google_calendar_link) . '" target="_blank" class="mec-google-calendar-button"><span class="dashicons dashicons-calendar-alt"></span> Add to Google Calendar</a>';
        }
        // Apple Calendar (iCal) Button
        if ($show_add_to_apple_calendar === 'yes') {
            echo '<a href="' . esc_url($ics_url) . '" download="event.ics" class="mec-apple-calendar-button"><span class="dashicons dashicons-calendar"></span> Add to Apple Calendar</a>';
        }
        echo '</div>';
    }
}


