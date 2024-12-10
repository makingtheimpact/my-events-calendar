<?php

function my_events_calendar_shortcode() {
    // Fetch events from the database
    $events = array();
    $options = get_option('my_events_calendar_options');
    $date_format = isset($options['date_format']) ? $options['date_format'] : 'm/d/Y';
    $time_format = isset($options['time_format']) ? $options['time_format'] : 'g:i A';

    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    $today = date('Y-m-d');
    $start_date = date('Y-m-d', strtotime('-3 months', strtotime($today)));
    $end_date = date('Y-m-d', strtotime('+3 months', strtotime($today)));

    $args = array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_start_date',
                'value' => array($start_date, $end_date),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            )
        )
    );

    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'event_category',
                'field' => 'slug',
                'terms' => explode(',', $category),
            ),
        );
    }

    $query = new WP_Query($args);

    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $category = get_the_terms($post_id, 'event_category');
        $category_color = '#007bff'; // Default blue
        $text_color = '#ffffff'; // Default white
        if ($category && !is_wp_error($category)) {
            $category_color = get_term_meta($category[0]->term_id, 'category_color', true);
            $text_color = get_term_meta($category[0]->term_id, 'category_text_color', true);
        }
        $all_day_event = get_post_meta($post_id, '_all_day_event', true);
        $category_slug = $category ? $category[0]->slug : 'default-category';
        // get location name from location post
        $location_id = get_post_meta($post_id, '_location_id', true);
        $location_name = $location_id ? get_the_title($location_id) : '';
        
        $start_time = get_post_meta($post_id, '_start_time', true);
        $end_time = get_post_meta($post_id, '_end_time', true);
        
        // Default values for all-day events or missing times
        if (empty($start_time)) {
            $start_time = '00:00'; // Default start time
        }
        if (empty($end_time)) {
            $end_time = '23:59'; // Default end time
        }

        // Generate ISO 8601 date-time strings
        if ($all_day_event) {
            $start_datetime = date('Y-m-d', strtotime(get_post_meta($post_id, '_start_date', true))) . 'T' . $start_time;
            $end_datetime = date('Y-m-d', strtotime(get_post_meta($post_id, '_end_date', true))) . 'T' . $end_time;
        } else {
            $start_datetime = date('Y-m-d\TH:i:s', strtotime(get_post_meta($post_id, '_start_date', true) . ' ' . $start_time));
            $end_datetime = date('Y-m-d\TH:i:s', strtotime(get_post_meta($post_id, '_end_date', true) . ' ' . $end_time));
        }

        // Ensure the image, description, and location are set
        $image_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
        $excerpt = wp_trim_words(get_the_content(), 20);
        $location_name = $location_id ? get_the_title($location_id) : '';

        $events[] = array(
            'title' => html_entity_decode(get_the_title()),
            'start' => $start_datetime,
            'end' => $end_datetime,
            'allDay' => $all_day_event ? true : false,
            'url' => get_permalink(),
            'backgroundColor' => $category_color,
            'textColor' => $text_color,
            'classNames' => ['mec-event-category-' . $category_slug],
            'extendedProps' => array(
                'image' => $image_url,
                'excerpt' => $excerpt,
                'location' => $location_name,
                'categories' => wp_list_pluck($category, 'name'),
                'categoryColor' => $category_color,
                'textColor' => $text_color,
            ),
        );
    }

    wp_reset_postdata();

    // CSS styles for event categories
    $css = '<style>';
    $event_cat_list = get_terms('event_category');
    foreach ($event_cat_list as $event_cat) {
        $category_color = get_term_meta($event_cat->term_id, 'category_color', true);
        $category_color = $category_color ? $category_color : '#007bff'; // Default fallback color
        $text_color = get_term_meta($event_cat->term_id, 'category_text_color', true);
        $text_color = $text_color ? $text_color : '#ffffff'; // Default fallback color
        $css .= '.mec-event-category-' . $event_cat->slug . ' { background-color: ' . $category_color . '; color: ' . $text_color . '; }
        .mec-event-category-' . $event_cat->slug . ' .fc-daygrid-event-dot { border-color: ' . $text_color . '; } 
        .mec-event-modal-category-' . $event_cat->slug . ' .mec-event-modal-header { background-color: ' . $category_color . '; color: ' . $text_color . '; } 
        .mec-event-modal-category-' . $event_cat->slug . ' .mec-event-modal-header h2 { color: ' . $text_color . '; } 
        .mec-event-modal-category-' . $event_cat->slug . ' .mec-event-modal-close-small { color: ' . $text_color . '; }
        .mec-event-modal-category-' . $event_cat->slug . ' .mec-event-modal-content { background-color: #ffffff; color: #333; }
        .mec-event-modal-category-' . $event_cat->slug . ' .mec-event-modal-view-details { background-color: ' . $category_color . '; color: ' . $text_color . '; }
        .mec-event-category-' . $event_cat->slug . ':hover { background-color: ' . $category_color . '; color: ' . $text_color . '; opacity: 0.8; }
        ';
    }
    // Get options for colors
    $options = mec_get_options();
    $accent_background_color = $options['accent_background_color'] ?? '#0067d4';
    $accent_background_color_hover = $options['accent_background_color_hover'] ?? '#0f7bee';
    $accent_text_color = $options['accent_text_color'] ?? '#ffffff';
    $accent_text_color_hover = $options['accent_text_color_hover'] ?? '#ffffff';

    $css .= '
        #calendar .fc-button-primary{
            background-color: ' . $accent_background_color . ' !important;
            color: ' . $accent_text_color . ' !important;
            border-color: ' . $accent_background_color . ' !important;
        }
        #calendar .fc-button-primary:hover, 
        #calendar .fc-button-primary:active, 
        #calendar .fc-button-primary:focus {
            background-color: ' . $accent_background_color_hover . ' !important;
            color: ' . $accent_text_color_hover . ' !important;
            border-color: ' . $accent_background_color_hover . ' !important;
        }
        #calendar .fc-today-button:disabled {
            background-color: #e9ecef !important;
            color: #777 !important;
            border-color: #ddd !important;
            opacity: 0.65;
        }';
    $css .= '</style>';


    echo $css;

    // Render calendar
    ob_start();
    ?>
    <div id="calendar" data-default-view="<?php echo esc_attr($options['default_view'] ?? 'dayGridMonth'); ?>"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var defaultView = calendarEl.getAttribute('data-default-view');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: defaultView,
                events: myEventsCalendarSettings.events,
                eventClassNames: function(eventInfo) {
                    return eventInfo.event.classNames || [];
                },
            });

            calendar.render();
        });
    </script>
    <?php
    return ob_get_clean();
}

add_shortcode('my_events_calendar', 'my_events_calendar_shortcode');

add_action('wp_ajax_nopriv_fetch_events', 'my_events_calendar_fetch_events');
add_action('wp_ajax_fetch_events', 'my_events_calendar_fetch_events');

function my_events_calendar_fetch_events() {
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    // event category
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $options = get_option('my_events_calendar_options');
    $date_format = isset($options['date_format']) ? $options['date_format'] : 'Y-m-d';
    $time_format = isset($options['time_format']) ? $options['time_format'] : 'H:i';

    $args = array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => '_start_date',
                'value' => array($start_date, $end_date),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            )
        )
    );

    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'event_category',
                'field' => 'slug',
                'terms' => explode(',', $category),
            ),
        );
    }

    $query = new WP_Query($args);

    ob_start();
    $events = array();
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $post_id = get_the_ID();
            $event_categories = wp_get_post_terms($post_id, 'event_category', array('fields' => 'names'));

            $start_time = get_post_meta($post_id, '_start_time', true);
            $end_time = get_post_meta($post_id, '_end_time', true);

            // Default values for all-day events or missing times
            if (empty($start_time)) {
                $start_time = '00:00'; // Default start time
            }
            if (empty($end_time)) {
                $end_time = '23:59'; // Default end time
            }

            $all_day_event = get_post_meta($post_id, '_all_day_event', true);

            // Generate ISO 8601 date-time strings
            $ev_start_date = get_post_meta($post_id, '_start_date', true);
            $ev_end_date = get_post_meta($post_id, '_end_date', true);

            if ($all_day_event) {
                $start_datetime = date('Y-m-d', strtotime(get_post_meta($post_id, '_start_date', true))) . 'T' . $start_time;
                $end_datetime = date('Y-m-d', strtotime(get_post_meta($post_id, '_end_date', true))) . 'T' . $end_time;
                $start_datetime_formatted = date($date_format, strtotime($ev_start_date));
                $end_datetime_formatted = date($date_format, strtotime($ev_end_date));
                $start_time_formatted = '';
                $end_time_formatted = '';
            } else {
                $start_datetime = date('Y-m-d\TH:i:s', strtotime(get_post_meta($post_id, '_start_date', true) . ' ' . $start_time));
                $end_datetime = date('Y-m-d\TH:i:s', strtotime(get_post_meta($post_id, '_end_date', true) . ' ' . $end_time));
                $start_datetime_formatted = date($date_format, strtotime($ev_start_date));
                $end_datetime_formatted = date($date_format, strtotime($ev_end_date));
                $start_time_formatted = date($time_format, strtotime($start_time));
                $end_time_formatted = date($time_format, strtotime($end_time));
            }

            $formatted_title = preg_replace('/[^a-zA-Z0-9\s]/', '', html_entity_decode(get_the_title()));

            $location_string = '';
            $location_id = get_post_meta($post_id, '_location_id', true);
            $location_name = get_post_meta($location_id, '_location_name', true);
            $location_type = get_post_meta($location_id, '_location_type', true);
            if ($location_type === 'physical') {
                // get location address
                $location_address = get_post_meta($location_id, '_location_address', true);
                $location_city = get_post_meta($location_id, '_location_city', true);
                $location_state = get_post_meta($location_id, '_location_state', true);
                $location_zip = get_post_meta($location_id, '_location_zip', true);
                // full location address string and format with commas if all are set   
                $full_location_address = $location_address ? $location_address . ', ' : '';
                $full_location_address .= $location_city ? $location_city . ', ' : '';
                $full_location_address .= $location_state ? $location_state . ' ' : '';
                $full_location_address .= $location_zip ? $location_zip : '';
                $location_string = $location_name ? $location_name . ' - ' . $full_location_address : $full_location_address;
            } else {
                $location_string = $location_name;
            }

            $excerpt = wp_trim_words(get_the_content(), 20);

            $events[] = array(
                'title' => get_the_title(),
                'start' => $start_datetime,
                'end' => $end_datetime,
                'url' => get_permalink(),
                'extendedProps' => array(
                    'image' => get_the_post_thumbnail_url($post_id, 'thumbnail'),
                    'excerpt' => $excerpt,
                    'location' => $location_string,
                    'categories' => $event_categories,
                    'all_day_event' => $all_day_event,
                    'start_datetime_formatted' => $start_datetime_formatted,
                    'end_datetime_formatted' => $end_datetime_formatted,
                    'start_time_formatted' => $start_time_formatted,
                    'end_time_formatted' => $end_time_formatted,
                    'formatted_title' => $formatted_title,
                ),
            );
        endwhile;
    endif;
    wp_reset_postdata();
    ob_get_clean();   

    wp_send_json_success($events);
}