<?php

function my_events_calendar_register_post_type() {
    $labels = array(
        'name' => 'Events',
        'singular_name' => 'Event',
        'menu_name' => 'Events',
        'name_admin_bar' => 'Event',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Event',
        'new_item' => 'New Event',
        'edit_item' => 'Edit Event',
        'view_item' => 'View Event',
        'all_items' => 'All Events',
        'search_items' => 'Search Events',
        'not_found' => 'No events found.',
        'not_found_in_trash' => 'No events found in Trash.',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'events', 'with_front' => false),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest' => false,
        'menu_icon' => 'dashicons-calendar',
        'taxonomies' => array('event_category'),
    );

    register_post_type('event', $args);
}

add_action('init', 'my_events_calendar_register_post_type');

add_action('add_meta_boxes', 'my_events_calendar_add_meta_boxes');

function my_events_calendar_add_meta_boxes() {
    add_meta_box(
        'event_details',
        __('Event Details', 'my-events-calendar'),
        'my_events_calendar_render_meta_box',
        'event',
        'normal',
        'high'
    );
}

function my_events_calendar_render_meta_box($post) {
    $post_id = $post->ID;   
    $start_date = get_post_meta($post_id, '_start_date', true);
    $end_date = get_post_meta($post_id, '_end_date', true);
    $all_day_event = get_post_meta($post_id, '_all_day_event', true);
    $start_time = get_post_meta($post_id, '_start_time', true);
    $end_time = get_post_meta($post_id, '_end_time', true);
    $recurrence_type = get_post_meta($post_id, '_recurrence_type', true);
    $recurrence_count = get_post_meta($post_id, '_recurrence_count', true);
    $custom_dates = get_post_meta($post_id, '_custom_dates', true);
    $exclusion_dates = get_post_meta($post_id, '_exclusion_dates', true);
    $location_id = get_post_meta($post_id, '_location_id', true);
    $ticket_url = get_post_meta($post_id, '_ticket_url', true);
    $ticket_cost = get_post_meta($post_id, '_ticket_cost', true);
    $ticket_details = get_post_meta($post_id, '_ticket_details', true);    
    $parent_event_id = get_post_meta($post_id, '_parent_event_id', true);
    $modify_series = get_post_meta($post_id, '_modify_series', true);

    echo '<div class="my-events-calendar-event-form">';
    wp_nonce_field('my_events_calendar_save_meta_box_data', 'my_events_calendar_meta_box_nonce');
    echo '<h3>' . __('Event Dates and Times', 'my-events-calendar') . '</h3>';
    echo '<p class="mec-event-form-field mec-event-start-date"><label for="start_date">' . __('Start Date', 'my-events-calendar') . ':</label>';
    echo '<input type="date" id="start_date" name="start_date" value="' . esc_attr($start_date) . '" required /></p>';
    echo '<p class="mec-event-form-field mec-event-end-date"><label for="end_date">' . __('End Date', 'my-events-calendar') . ':</label>';
    echo '<input type="date" id="end_date" name="end_date" value="' . esc_attr($end_date) . '" required /></p>';
    echo '<p><label for="all_day_event"><input type="checkbox" id="all_day_event" name="all_day_event" value="1" ' . checked($all_day_event, 1, false) . '> ' . __('All Day Event', 'my-events-calendar') . '</label></p>';
    echo '<p class="mec-event-form-field mec-event-start-time"><label for="start_time">' . __('Start Time', 'my-events-calendar') . ':</label>';
    echo '<input type="time" id="start_time" name="start_time" value="' . esc_attr($start_time) . '" /></p>';
    echo '<p class="mec-event-form-field mec-event-end-time"><label for="end_time">' . __('End Time', 'my-events-calendar') . ':</label>';
    echo '<input type="time" id="end_time" name="end_time" value="' . esc_attr($end_time) . '" /></p>';

    // Location Details
    echo '<h3>' . __('Location Details', 'my-events-calendar') . '</h3>';
    $locations = get_posts(array('post_type' => 'event_location', 'numberposts' => -1));
    echo '<p class="mec-event-form-field mec-event-location"><label for="location_id">' . __('Select Location', 'my-events-calendar') . ':</label>';
    echo '<select id="location_id" name="location_id">';
    echo '<option value="">' . __('Select a location', 'my-events-calendar') . '</option>';
    foreach ($locations as $location) {
        echo '<option value="' . esc_attr($location->ID) . '" ' . selected($location_id, $location->ID, false) . '>' . esc_html($location->post_title) . '</option>';
    }
    echo '</select></p>';
    echo '<p><a href="' . admin_url('post-new.php?post_type=event_location') . '" target="_blank">' . __('Add New Location', 'my-events-calendar') . '</a></p>';
    
    // Ticket Information
    echo '<h3>' . __('Ticket Information', 'my-events-calendar') . '</h3>';
    echo '<p class="mec-event-form-field mec-event-ticket-cost"><label for="ticket_cost">' . __('Ticket Cost', 'my-events-calendar') . ':</label>';
    echo '<input type="text" id="ticket_cost" name="ticket_cost" value="' . esc_attr($ticket_cost) . '" placeholder="$100.00 each" /></p>';
    echo '<p class="mec-event-form-field mec-event-ticket-details"><label for="ticket_details">' . __('Ticket Details', 'my-events-calendar') . ':</label>';
    echo '<textarea id="ticket_details" name="ticket_details">' . esc_textarea($ticket_details) . '</textarea></p>';
    echo '<p class="mec-event-form-field mec-event-ticket-url"><label for="ticket_url">' . __('Ticket Purchase URL', 'my-events-calendar') . ':</label>';
    echo '<input type="url" id="ticket_url" name="ticket_url" value="' . esc_attr($ticket_url) . '" /></p>';
    
    // Recurring Event Settings
    echo '<h3>' . __('Recurring Event Settings', 'my-events-calendar') . '</h3>';
    echo '<p class="mec-event-form-field mec-event-recurrence-type"><label for="recurrence_type">' . __('Recurrence Type', 'my-events-calendar') . ':</label>';
    echo '<select id="recurrence_type" name="recurrence_type">';
    echo '<option value="none" ' . selected($recurrence_type, 'none', false) . '>None</option>';
    echo '<option value="daily" ' . selected($recurrence_type, 'daily', false) . '>Daily</option>';
    echo '<option value="weekly" ' . selected($recurrence_type, 'weekly', false) . '>Weekly</option>';
    echo '<option value="monthly_date" ' . selected($recurrence_type, 'monthly_date', false) . '>Monthly (Date)</option>';
    echo '<option value="monthly_day" ' . selected($recurrence_type, 'monthly_day', false) . '>Monthly (Day)</option>';
    echo '<option value="annually" ' . selected($recurrence_type, 'annually', false) . '>Annually</option>';
    echo '<option value="custom_dates" ' . selected($recurrence_type, 'custom_dates', false) . '>Custom Dates</option>';
    echo '</select></p>';

    echo '<div id="recurrence_fields" style="display: block;">';
    echo '<p class="mec-event-form-field mec-event-recurrence-count"><label for="recurrence_count">' . __('Times to Repeat', 'my-events-calendar') . ':</label>';
    echo '<input type="number" id="recurrence_count" name="recurrence_count" value="' . esc_attr($recurrence_count) . '" min="0" /></p>';
    echo '</div>';
    echo '<div id="custom_dates_fields" style="display: block;">';
    echo '<p class="mec-event-form-field mec-event-custom-dates"><label for="custom_dates">' . __('Custom Dates', 'my-events-calendar') . ':</label>';
    echo '<textarea id="custom_dates" name="custom_dates" placeholder="Enter dates in YYYY-MM-DD format, separated by commas">' . esc_textarea($custom_dates) . '</textarea></p>';
    echo '</div>';
    echo '<div id="exclusion_dates_fields" style="display: block;">';
    echo '<p class="mec-event-form-field mec-event-exclusion-dates"><label for="exclusion_dates">' . __('Exclusion Dates', 'my-events-calendar') . ':</label>';
    echo '<textarea id="exclusion_dates" name="exclusion_dates" placeholder="Enter dates in YYYY-MM-DD format, separated by commas">' . esc_textarea($exclusion_dates) . '</textarea></p>';
    echo '</div>';
    if (($recurrence_type !== '' && $recurrence_type !== 'none') || !empty($parent_event_id)) {
        echo '<p class="mec-event-form-field">';
        echo '<label for="modify_series">' . __('Modify Entire Event Series', 'my-events-calendar') . ':</label>';
        echo '<input type="checkbox" id="modify_series" name="modify_series" value="1" />';
        echo '</p>';
    }
    
    echo '</div>';
}

add_action('save_post', 'my_events_calendar_save_meta_box_data');

function my_events_calendar_save_meta_box_data($post_id) {
    // Verify Nonce
    if (!isset($_POST['my_events_calendar_meta_box_nonce']) || !wp_verify_nonce($_POST['my_events_calendar_meta_box_nonce'], 'my_events_calendar_save_meta_box_data')) {
        return; // Early return on nonce verification failure
    }

    // Check if it's an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permission to edit the post
    if (isset($_POST['post_type']) && $_POST['post_type'] === 'event') {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // Initialize an errors array to collect any validation issues
    $errors = array();

    // Simplified Validation Checks
    $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
    $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $all_day_event = isset($_POST['all_day_event']) ? 1 : 0; // Checkbox
    $start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
    $end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';
    $recurrence_type = isset($_POST['recurrence_type']) ? sanitize_text_field($_POST['recurrence_type']) : 'none';
    $recurrence_count = isset($_POST['recurrence_count']) ? (int) $_POST['recurrence_count'] : 0;
    $custom_dates = isset($_POST['custom_dates']) ? sanitize_textarea_field($_POST['custom_dates']) : '';
    $exclusion_dates = isset($_POST['exclusion_dates']) ? sanitize_textarea_field($_POST['exclusion_dates']) : '';
    $location_id = isset($_POST['location_id']) ? sanitize_text_field($_POST['location_id']) : '';
    $ticket_url = isset($_POST['ticket_url']) ? esc_url_raw($_POST['ticket_url']) : '';
    $ticket_cost = isset($_POST['ticket_cost']) ? sanitize_text_field($_POST['ticket_cost']) : '';
    $ticket_details = isset($_POST['ticket_details']) ? sanitize_textarea_field($_POST['ticket_details']) : '';
    $modify_series = isset($_POST['modify_series']) ? (bool) $_POST['modify_series'] : false;
    $parent_event_id = get_post_meta($post_id, '_parent_event_id', true); // get parent event id

    // Validate Dates
    if (empty($start_date)) {
        $errors[] = __('Start date is required.', 'my-events-calendar');
    }
    if (empty($end_date)) {
        $errors[] = __('End date is required.', 'my-events-calendar');
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $errors[] = __('End date must be the same as or after the start date.', 'my-events-calendar');
    }

    // Validate Time
    if (!empty($start_time) && !empty($end_time) && strtotime($end_time) <= strtotime($start_time)) {
        $errors[] = __('End time must be after the start time.', 'my-events-calendar');
    }

    // Validate Custom Dates Format
    if (!empty($custom_dates) && !preg_match('/^\d{4}-\d{2}-\d{2}(,\d{4}-\d{2}-\d{2})*$/', $custom_dates)) {
        $errors[] = __('Custom dates must be in the format YYYY-MM-DD, separated by commas.', 'my-events-calendar');
    }

    // Validate Exclusion Dates Format
    if (!empty($exclusion_dates) && !preg_match('/^\d{4}-\d{2}-\d{2}(,\d{4}-\d{2}-\d{2})*$/', $exclusion_dates)) {
        $errors[] = __('Exclusion dates must be in the format YYYY-MM-DD, separated by commas.', 'my-events-calendar');
    }

    // Validate Recurrence
    if ($recurrence_type !== 'none' && $recurrence_count <= 0) {
        $errors[] = __('Recurrence count is required for recurring events.', 'my-events-calendar');
    }
    if ($recurrence_type === 'custom_dates' && empty($custom_dates)) {
        $errors[] = __('Custom dates are required for custom recurrence.', 'my-events-calendar');
    }

    // Display Errors if Any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            if (is_array($error) && !empty($error)) {
                foreach ($error as $error_message) {
                    add_settings_error('my_events_calendar_errors', 'settings_error', esc_html($error_message), 'error');
                }
            } else {
                add_settings_error('my_events_calendar_errors', 'settings_error', esc_html($error), 'error');
            }
        }
        set_transient('my_events_calendar_errors', get_settings_errors(), 30);
        return; // Early return if errors
    }

    // Check for event categories
    $terms = wp_get_post_terms($post_id, 'event_category', array('fields' => 'ids'));
    if (empty($terms)) {
        // find the first category and set it as the default
        $event_categories = get_terms('event_category');
        if (!empty($event_categories)) {
            foreach ($event_categories as $category) {
                $default_category_id = $category->term_id; // Ensure you are using term IDs
                if (!empty($default_category_id)) {
                    my_events_calendar_update_event_terms($post_id, array($default_category_id)); // Pass an array of term IDs
                    $terms = wp_get_post_terms($post_id, 'event_category', array('fields' => 'ids'));
                    break;
                }
            }
        }
    }

    // Show notice for recurring events
    if ($recurrence_type !== 'none') {
        add_settings_error('my_events_calendar_options', 'recurrence_notice', __('Recurring events will be created in the background.', 'my-events-calendar'), 'updated');
    }

    // Show notice for modifications in a series
    if (isset($_POST['modify_series']) && $_POST['modify_series'] === 'yes') {
        add_settings_error('my_events_calendar_options', 'series_modification_notice', __('The other events in this series will be updated in the background.', 'my-events-calendar'), 'updated');
    }

    // Update Post Meta (if no errors)
    if (empty($start_time) && empty($end_time)) { // if time is not set, set all day event to true
        $all_day_event = 1;
        $start_time = '';
        $end_time = '';
    }     
    
    $update_current_event_meta = array(
        '_start_date' => $start_date,
        '_end_date' => $end_date,
        '_start_time' => $start_time,
        '_end_time' => $end_time,
        '_all_day_event' => $all_day_event,
        '_recurrence_type' => $recurrence_type,
        '_recurrence_count' => $recurrence_count,
        '_custom_dates' => $custom_dates,
        '_exclusion_dates' => $exclusion_dates,
        '_location_id' => $location_id,
        '_ticket_url' => $ticket_url,
        '_ticket_cost' => $ticket_cost,
        '_ticket_details' => $ticket_details,
    );
    my_events_calendar_update_event_meta($post_id, $update_current_event_meta);
    
    // Clear parent ID if the event is set to recur
    if ($recurrence_type !== 'none') {
        // delete parent id if set
        delete_post_meta($post_id, '_parent_event_id');
        if ($recurrence_type !== 'custom_dates') {
            delete_post_meta($post_id, '_custom_dates');         
        } else {
            delete_post_meta($post_id, '_exclusion_dates');
        }
        // Create recurring events
        my_events_calendar_create_recurring_events ($post_id);
    } 

    // Check if modifying an event in a series    
    if ($modify_series) {
        my_events_calendar_update_recurring_events($post_id);
    }    
}

function my_events_calendar_update_event_meta($event_id, $meta_data) {
    foreach ($meta_data as $meta_key => $meta_value) {
        update_post_meta($event_id, $meta_key, $meta_value);
    }
}

function my_events_calendar_update_event_meta_and_thumbnail($event_id, $meta_data, $thumbnail_id) {
    // Update meta data
    foreach ($meta_data as $meta_key => $meta_value) {
        update_post_meta($event_id, $meta_key, $meta_value);
    }

    // Handle featured image
    if ($thumbnail_id) {
        set_post_thumbnail($event_id, $thumbnail_id);
    } else {
        delete_post_thumbnail($event_id);
    }
}

function my_events_calendar_update_event_terms($event_id, $terms) {
    // Update the event categories for an event
    if (!empty($terms) && $event_id !== '') {
        wp_set_post_terms($event_id, $terms, 'event_category', false); // Replace existing terms
    }
}

// Add duplicate event link to the event post type
function my_events_calendar_duplicate_event_link($actions, $post) {
    if ($post->post_type == 'event') {
        $duplicate_link = wp_nonce_url(
            admin_url('admin.php?action=duplicate_event&post=' . $post->ID),
            'duplicate_event_' . $post->ID
        );
        $actions['duplicate'] = '<a href="' . $duplicate_link . '" title="Duplicate this event">Duplicate</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'my_events_calendar_duplicate_event_link', 10, 2);

// Get post details
function my_events_calendar_get_post_details($post_id) {
    $post_data = array();
    $post_data['title'] = get_the_title($post_id);
    $post_data['description'] = get_post_field('post_content', $post_id);
    $post_data['thumbnail_id'] = get_post_thumbnail_id($post_id);
    $post_data['start_date'] = get_post_meta($post_id, '_start_date', true);
    $post_data['end_date'] = get_post_meta($post_id, '_end_date', true);
    $post_data['start_time'] = get_post_meta($post_id, '_start_time', true);
    $post_data['end_time'] = get_post_meta($post_id, '_end_time', true);
    $post_data['all_day_event'] = get_post_meta($post_id, '_all_day_event', true);
    $post_data['location_id'] = get_post_meta($post_id, '_location_id', true);
    $post_data['ticket_url'] = get_post_meta($post_id, '_ticket_url', true);
    $post_data['ticket_cost'] = get_post_meta($post_id, '_ticket_cost', true);
    $post_data['ticket_details'] = get_post_meta($post_id, '_ticket_details', true);
    $post_data['terms'] = wp_get_post_terms($post_id, 'event_category', array('fields' => 'ids'));
    $post_data['recurrence_type'] = get_post_meta($post_id, '_recurrence_type', true);
    $post_data['recurrence_count'] = get_post_meta($post_id, '_recurrence_count', true);
    $post_data['custom_dates'] = get_post_meta($post_id, '_custom_dates', true);
    $post_data['exclusion_dates'] = get_post_meta($post_id, '_exclusion_dates', true);    
    $post_data['modify_series'] = get_post_meta($post_id, '_modify_series', true);
    $post_data['parent_event_id'] = get_post_meta($post_id, '_parent_event_id', true);
    return $post_data;
}

function my_events_calendar_create_recurring_events($post_id) {

    // Fetch post data to copy 
    $post_data = my_events_calendar_get_post_details($post_id);

    // Exclusion dates
    $exclusion_dates_array = !empty($post_data['exclusion_dates']) ? array_map('trim', explode(',', $post_data['exclusion_dates'])) : array();
    $custom_dates_array = !empty($post_data['custom_dates']) ? array_map('trim', explode(',', $post_data['custom_dates'])) : array();
    
    // Batch size
    $batch_size = 10;
    
    $dates = array(); // Initialize empty dates array

    // Handle custom dates
    if ($post_data['recurrence_type'] === 'custom_dates' && !empty($post_data['custom_dates'])) {
        $dates = array_map('trim', explode(',', $post_data['custom_dates'])); // Split custom dates by comma and trim whitespace
    } elseif ($post_data['recurrence_count'] > 0 && $post_data['recurrence_type'] !== 'none') {
        // Generate dates for standard recurrence
        for ($i = 1; $i <= $post_data['recurrence_count']; $i++) {
            $new_start_date = my_events_calendar_calculate_new_start_date($i, $post_id);
            $dates[] = $new_start_date; // Add to dates array
        }
    }

    // check the date list and remove any dates that are in the exclusion dates array
    if (!empty($exclusion_dates_array)) {
        $dates = array_diff($dates, $exclusion_dates_array);
    }

    // Remove the parent event start date from the dates array
    $dates = array_diff($dates, array($post_data['start_date']));

    // Check for related events that already exist on the same date
    // Loop through each date and check for related events
    foreach ($dates as $date) {
        $related_events = get_posts(array(
            'post_type' => 'event',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_parent_event_id',
                    'value' => $post_id,
                    'compare' => '='
                ), 
                array(
                    'key' => '_start_date',
                    'value' => $date,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1, // Limit results for efficiency
        ));

        // If an event is found, remove the date from the dates array
        if (!empty($related_events)) {
            $dates = array_diff($dates, array($date));
        }
    } 

    // remove the parent post start date from the dates array
    $dates = array_diff($dates, array($post_data['start_date']));

    // Split dates into batches
    $batches = array_chunk($dates, $batch_size);

    // Prep data for batch processing
    $title = $post_data['title'];
    $description = $post_data['description'];
    $thumbnail_id = $post_data['thumbnail_id'];
    $start_time = $post_data['start_time'];
    $end_time = $post_data['end_time'];
    $all_day_event = $post_data['all_day_event'];
    $location_id = $post_data['location_id'];
    $ticket_url = $post_data['ticket_url'];
    $ticket_cost = $post_data['ticket_cost'];
    $ticket_details = $post_data['ticket_details'];
    $terms = $post_data['terms'];

    foreach ($batches as $index => $batch) {
        $time_offset = $index * 60; // 1-minute interval between each batch
        $args = array(
            $batch,
            $post_id,
            $title ?? '',
            $description ?? '',
            $thumbnail_id ?? '',
            $start_time ?? '',
            $end_time ?? '',
            $all_day_event ?? '',
            $location_id ?? '',
            $ticket_url ?? '',
            $ticket_cost ?? '',
            $ticket_details ?? '',
            $terms ?? ''
        );

        if (!empty($batch)) {
            if (!wp_next_scheduled('process_event_batch', $args)) {
                wp_schedule_single_event(time() + $time_offset, 'process_event_batch', $args);
            }
        }
    }
}
add_action('process_event_batch', 'my_events_calendar_process_event_batch', 10, 13);



function my_events_calendar_process_event_batch($batch, $post_id, $title, $description, $thumbnail_id, $start_time, $end_time, $all_day_event, $location_id, $ticket_url, $ticket_cost, $ticket_details, $terms) {
    foreach ($batch as $new_start_date) {
        // Validate date format (for custom dates)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $new_start_date)) {
            continue;
        }

        $new_end_date = calculate_new_end_date($new_start_date, $post_id);

        // Create the new event
        $new_event_id = wp_insert_post(array(
            'post_type' => 'event',
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'publish',            
        ));

        $meta_data = array(
            '_start_date' => $new_start_date,
            '_end_date' => $new_end_date,
            '_start_time' => $start_time,
            '_end_time' => $end_time,
            '_all_day_event' => $all_day_event,
            '_location_id' => $location_id,
            '_ticket_url' => $ticket_url,
            '_ticket_cost' => $ticket_cost,
            '_ticket_details' => $ticket_details,
            '_parent_event_id' => $post_id,
            '_recurrence_type' => 'none',
        );

        my_events_calendar_update_event_meta_and_thumbnail($new_event_id, $meta_data, $thumbnail_id);
        my_events_calendar_update_event_terms($new_event_id, $terms);
    }
}

function my_events_calendar_update_recurring_events($post_id) {
    $parent_event_id = get_post_meta($post_id, '_parent_event_id', true);
    $recurrence_type = get_post_meta($post_id, '_recurrence_type', true);

    $post_id_to_copy = $post_id;

    // Determine the post to copy data from
    if ($parent_event_id && $recurrence_type == 'none') {
        // If editing a child event, copy data from the current child event to update the parent
        $related_to = $parent_event_id; // Update parent
    } else {
        // Otherwise, treat this as the parent event and update all children
        $related_to = $post_id; // Update children
    }

    // Fetch post data to copy 
    $post_data = my_events_calendar_get_post_details($post_id_to_copy);

    // Fetch list of related events IDs
    $related_events = get_posts(array(
        'post_type' => 'event',
        'meta_query' => array(
            array(
                'key' => '_parent_event_id',
                'value' => $related_to,
                'compare' => '='
            )
        ),
        'fields' => 'ids', // This ensures only IDs are returned
        'posts_per_page' => -1, // Retrieve all matching posts
    ));

    // Include the parent event explicitly if it's being updated
    if ($parent_event_id) {
        $related_events[] = $parent_event_id;
    }

    // Remove the current post from the update list
    $related_events = array_filter($related_events, function ($event_id) use ($post_id) {
        return $event_id !== $post_id;
    });

    // Return early if no events remain to update
    if (empty($related_events)) {
        error_log('No related events found for updating.');
        return;
    }

    // Split events into batches
    $batch_size = 10;
    $batches = array_chunk($related_events, $batch_size);

    // Prep data for batch processing    
    $title = $post_data['title'];
    $description = $post_data['description'];
    $thumbnail_id = $post_data['thumbnail_id'];
    $start_time = $post_data['start_time'];
    $end_time = $post_data['end_time'];
    $all_day_event = $post_data['all_day_event'];
    $location_id = $post_data['location_id'];
    $ticket_url = $post_data['ticket_url'];
    $ticket_cost = $post_data['ticket_cost'];
    $ticket_details = $post_data['ticket_details'];
    $terms = $post_data['terms'];

    foreach ($batches as $index => $batch) {
        $time_offset = $index * 60; // 1-minute interval between each batch
        $args = array(
            $batch,
            $post_id_to_copy,
            $title ?? '',
            $description ?? '',
            $thumbnail_id ?? '',
            $start_time ?? '',
            $end_time ?? '',
            $all_day_event ?? '',
            $location_id ?? '',
            $ticket_url ?? '',
            $ticket_cost ?? '',
            $ticket_details ?? '',
            $terms ?? ''
        );
        if (!empty($batch)) {
            if (!wp_next_scheduled('update_event_batch', $args)) {
                wp_schedule_single_event(time() + $time_offset, 'update_event_batch', $args);
            }
        }
    }
}
add_action('update_event_batch', 'my_events_calendar_update_event_batch', 10, 13);

function my_events_calendar_update_event_batch($batch, $post_id, $title, $description, $thumbnail_id, $start_time, $end_time, $all_day_event, $location_id, $ticket_url, $ticket_cost, $ticket_details, $terms) {
    foreach ($batch as $event_id) {
        // Ensure the event_id is valid
        if (!get_post($event_id)) {
            continue; // Skip invalid post IDs
        }
        $update_event_id = $event_id;

        // Update title and description
        wp_update_post(array(
            'ID' => $update_event_id,
            'post_content' => $description, 
            'post_title' => $title, 
        ));
        // Update meta data
        $meta_data = array(
            '_start_time' => $start_time,
            '_end_time' => $end_time,
            '_all_day_event' => $all_day_event,
            '_location_id' => $location_id,
            '_ticket_url' => $ticket_url,
            '_ticket_cost' => $ticket_cost,
            '_ticket_details' => $ticket_details,
        );  

        my_events_calendar_update_event_meta_and_thumbnail($update_event_id, $meta_data, $thumbnail_id);
        my_events_calendar_update_event_terms($update_event_id, $terms);
    }
}

function my_events_calendar_calculate_new_start_date($i, $post_id) {
    $start_date = get_post_meta($post_id, '_start_date', true);
    $recurrence_type = get_post_meta($post_id, '_recurrence_type', true);
    
    switch ($recurrence_type) {
        case 'daily':
            return date('Y-m-d', strtotime("+$i days", strtotime($start_date)));
        case 'weekly':
            return date('Y-m-d', strtotime("+$i weeks", strtotime($start_date)));
        case 'monthly_date':
            return date('Y-m-d', strtotime("+$i months", strtotime($start_date)));
        case 'monthly_day':
            // Create a DateTime object from the start date
            $date = new DateTime($start_date);
            $desired_weekday = date('N', strtotime($start_date)); // Get the weekday of the start date (1 = Monday, 7 = Sunday)
        
            // Determine the occurrence number (e.g., first, second, third, etc.)
            $day_of_month = (int)$date->format('j');
            $occurrence_number = (int)ceil($day_of_month / 7);
        
            // Calculate the new month and year based on the current iteration
            $new_month_date = clone $date;
            $new_month_date->modify("+$i months");
            $month = $new_month_date->format('m');
            $year = $new_month_date->format('Y');
        
            // Special case handling for 'last' occurrence
            if ($occurrence_number === 5) {
                $last_day_of_month = new DateTime("last day of $year-$month");
                while ($last_day_of_month >= new DateTime("first day of $year-$month")) {
                    if ($last_day_of_month->format('N') == $desired_weekday) {
                        return $last_day_of_month->format('Y-m-d');
                    }
                    $last_day_of_month->modify('-1 day');
                }
            }
        
            // Loop through the month to find the specified occurrence
            $first_day_of_month = new DateTime("first day of $year-$month");
            $occurrence_count = 0;
            while ($first_day_of_month->format('m') === $month) { // Ensure we stay within the same month
                if ($first_day_of_month->format('N') == $desired_weekday) {
                    $occurrence_count++;
                    if ($occurrence_count === $occurrence_number) {
                        return $first_day_of_month->format('Y-m-d');
                    }
                }
                $first_day_of_month->modify('+1 day');
            }
        
            // If no valid date is found, return the original start date
            return $start_date;
        case 'annually':
            return date('Y-m-d', strtotime("+$i years", strtotime($start_date)));
        default: // custom dates 
            return $start_date;
    }
}

function calculate_new_end_date($new_start_date, $post_id) {
    $start_date = get_post_meta($post_id, '_start_date', true);
    $end_date = get_post_meta($post_id, '_end_date', true);

    if ($start_date === $end_date) {
        return $new_start_date;
    } else {
        $duration = strtotime($end_date) - strtotime($start_date);
        return date('Y-m-d', strtotime($new_start_date . ' +' . $duration . ' seconds'));
    }
}

// Duplicate event action
function my_events_calendar_duplicate_event() {
    // Verify nonce
    if (!isset($_GET['post']) || !wp_verify_nonce($_GET['_wpnonce'], 'duplicate_event_' . $_GET['post'])) {
        wp_die('Invalid request.');
    }

    // Get the original post
    $post_id = absint($_GET['post']);
    $post = get_post($post_id);
    $terms = wp_get_post_terms($post_id, 'event_category', array('fields' => 'ids'));


    if ($post && $post->post_type == 'event') {
        // Create a new post with the same data
        $new_post_id = wp_insert_post(array(
            'post_title' => $post->post_title . ' (Copy)',
            'post_content' => $post->post_content,
            'post_thumbnail' => get_post_thumbnail_id($post_id),
            'post_status' => 'draft',
            'post_type' => 'event',
        ));

        // Copy post meta
        $meta = get_post_meta($post_id);
        foreach ($meta as $key => $values) {
            foreach ($values as $value) {
                add_post_meta($new_post_id, $key, maybe_unserialize($value));
            }
        }

        // Retrieve and assign terms if available
        if (!empty($terms)) {
            wp_set_post_terms($new_post_id, $terms, 'event_category');
        }

        // Redirect to the edit screen of the new post
        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;
    } else {
        wp_die('Event duplication failed.');
    }
}
add_action('admin_action_duplicate_event', 'my_events_calendar_duplicate_event');

// Add custom columns to the events post type
function my_events_calendar_add_custom_columns($columns) {
    // Create a new array to hold the reordered columns
    $new_columns = array();

    // Add the existing columns, ensuring the Title column comes first
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            // Add the Title column first
            $new_columns['title'] = $value;

            // Add your custom columns next
            $new_columns['start_date'] = __('Start Date', 'my-events-calendar');
            $new_columns['end_date'] = __('End Date', 'my-events-calendar');
            $new_columns['recurrence_type'] = __('Recurrence Type', 'my-events-calendar');
        } elseif ($key !== 'date') {
            // Add other columns except for the Date column
            $new_columns[$key] = $value;
        }
    }

    // Add the Date column at the end
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_event_posts_columns', 'my_events_calendar_add_custom_columns');

// Populate custom columns with data
function my_events_calendar_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'start_date':
            $start_date = get_post_meta($post_id, '_start_date', true);
            echo esc_html($start_date);
            break;
        case 'end_date':
            $end_date = get_post_meta($post_id, '_end_date', true);
            echo esc_html($end_date);
            break;
        case 'recurrence_type':
            $recurrence_type = get_post_meta($post_id, '_recurrence_type', true);
            echo esc_html($recurrence_type);
            break;
    }
}
add_action('manage_event_posts_custom_column', 'my_events_calendar_custom_column_content', 10, 2);

// Clean up metadata when an event is deleted
function my_events_calendar_cleanup_event_meta($post_id) {
    if (get_post_type($post_id) === 'event') {
        // Delete all post meta associated with the event
        delete_post_meta($post_id, '_start_date');
        delete_post_meta($post_id, '_end_date');
        delete_post_meta($post_id, '_all_day_event');
        delete_post_meta($post_id, '_start_time');
        delete_post_meta($post_id, '_end_time');
        delete_post_meta($post_id, '_recurrence_type');
        delete_post_meta($post_id, '_recurrence_count');
        delete_post_meta($post_id, '_custom_dates');
        delete_post_meta($post_id, '_exclusion_dates');
        delete_post_meta($post_id, '_location_id');
        delete_post_meta($post_id, '_ticket_url');
        delete_post_meta($post_id, '_ticket_cost');
        delete_post_meta($post_id, '_ticket_details');
        delete_post_meta($post_id, '_parent_event_id');
    }
}
add_action('before_delete_post', 'my_events_calendar_cleanup_event_meta');

// Make custom columns sortable for events
function my_events_calendar_sortable_columns($columns) {
    $columns['start_date'] = 'start_date';
    $columns['end_date'] = 'end_date';
    $columns['recurrence_type'] = 'recurrence_type';
    return $columns;
}
add_filter('manage_edit-event_sortable_columns', 'my_events_calendar_sortable_columns');

function my_events_calendar_orderby_custom_columns($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'event') {
        return;
    }

    if ('start_date' === $query->get('orderby')) {
        $query->set('meta_key', '_start_date');
        $query->set('orderby', 'meta_value');
    } elseif ('end_date' === $query->get('orderby')) {
        $query->set('meta_key', '_end_date');
        $query->set('orderby', 'meta_value');
    } elseif ('recurrence_type' === $query->get('orderby')) {
        $query->set('meta_key', '_recurrence_type');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'my_events_calendar_orderby_custom_columns');