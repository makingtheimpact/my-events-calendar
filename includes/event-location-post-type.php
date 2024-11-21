<?php

function my_events_calendar_register_location_post_type() {
    $labels = array(
        'name' => 'Event Locations',
        'singular_name' => 'Event Location',
        'menu_name' => 'Event Locations',   
        'name_admin_bar' => 'Event Location',
        'add_new' => 'Add New Location',
        'add_new_item' => 'Add New Event Location',
        'new_item' => 'New Event Location',
        'edit_item' => 'Edit Event Location',
        'view_item' => 'View Event Location',
        'all_items' => 'All Event Locations',
        'search_items' => 'Search Event Locations',
        'not_found' => 'No event locations found.',
        'not_found_in_trash' => 'No event locations found in Trash.',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'event-locations'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 6,
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => false,
        'menu_icon' => 'dashicons-location',        
    );

    register_post_type('event_location', $args);
}

add_action('init', 'my_events_calendar_register_location_post_type');

add_action('add_meta_boxes', 'my_events_calendar_location_add_meta_boxes');

function my_events_calendar_location_add_meta_boxes() {
    add_meta_box(
        'event_location_details',
        __('Event Location Details', 'my-events-calendar'),
        'my_events_calendar_location_render_meta_box',
        'event_location',
        'normal',
        'high'
    );
}

function my_events_calendar_location_render_meta_box($post) {
    wp_nonce_field('my_events_calendar_location_save_meta_box_data', 'my_events_calendar_location_meta_box_nonce');

    $location_type = get_post_meta($post->ID, '_location_type', true);
    $location_address = get_post_meta($post->ID, '_location_address', true);
    $location_city = get_post_meta($post->ID, '_location_city', true);
    $location_state = get_post_meta($post->ID, '_location_state', true);
    $location_zip = get_post_meta($post->ID, '_location_zip', true);
    $location_url = get_post_meta($post->ID, '_location_url', true);

    echo '<div class="my-events-calendar-event-location-form">';
        echo '<h3>' . __('Location Details', 'my-events-calendar') . '</h3>';
        echo '<p class="mec-event-form-field mec-event-location-type"><label for="location_type">' . __('Location Type', 'my-events-calendar') . '</label>';
        echo '<select id="location_type" name="location_type">';
        echo '<option value="physical">' . __('Physical', 'my-events-calendar') . '</option>';
        echo '<option value="virtual">' . __('Virtual', 'my-events-calendar') . '</option>';
        echo '</select></p>';
        echo '<p class="mec-event-form-field mec-event-location-address"><label for="location_address">' . __('Address', 'my-events-calendar') . '</label>';
        echo '<input type="text" id="location_address" name="location_address" value="' . esc_attr($location_address) . '" /></p>';
        echo '<p class="mec-event-form-field mec-event-location-city"><label for="location_city">' . __('City', 'my-events-calendar') . '</label>';
        echo '<input type="text" id="location_city" name="location_city" value="' . esc_attr($location_city) . '" /></p>';
        echo '<p class="mec-event-form-field mec-event-location-state"><label for="location_state">' . __('State', 'my-events-calendar') . '</label>';
        echo '<input type="text" id="location_state" name="location_state" value="' . esc_attr($location_state) . '" /></p>';
        echo '<p class="mec-event-form-field mec-event-location-zip"><label for="location_zip">' . __('Zip', 'my-events-calendar') . '</label>';
        echo '<input type="text" id="location_zip" name="location_zip" value="' . esc_attr($location_zip) . '" /></p>';
        echo '<p class="mec-event-form-field mec-event-location-url"><label for="location_url">' . __('Event Location URL', 'my-events-calendar') . '</label>';
        echo '<input type="text" id="location_url" name="location_url" value="' . esc_attr($location_url) . '" /></p>';
    echo '</div>';
}

add_action('save_post', 'my_events_calendar_location_save_meta_box_data');

function my_events_calendar_location_save_meta_box_data($post_id) {
    if (!isset($_POST['my_events_calendar_location_meta_box_nonce']) || !wp_verify_nonce($_POST['my_events_calendar_location_meta_box_nonce'], 'my_events_calendar_location_save_meta_box_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['location_type'])) {
        update_post_meta($post_id, '_location_type', sanitize_text_field($_POST['location_type']));
    }
    if (isset($_POST['location_address'])) {
        update_post_meta($post_id, '_location_address', sanitize_text_field($_POST['location_address']));
    }
    if (isset($_POST['location_city'])) {
        update_post_meta($post_id, '_location_city', sanitize_text_field($_POST['location_city']));
    }
    if (isset($_POST['location_state'])) {
        update_post_meta($post_id, '_location_state', sanitize_text_field($_POST['location_state']));
    }
    if (isset($_POST['location_zip'])) {
        update_post_meta($post_id, '_location_zip', sanitize_text_field($_POST['location_zip']));
    }   
    if (isset($_POST['location_url'])) {
        update_post_meta($post_id, '_location_url', sanitize_text_field($_POST['location_url']));
    }
}

// Add custom columns to the locations post type
function my_events_calendar_location_add_custom_columns($columns) {
    // Create a new array to hold the reordered columns
    $new_columns = array();

    // Add the existing columns, ensuring the Title column comes first
    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            // Add the Title column first
            $new_columns['title'] = $value;

            // Add your custom columns next
            $new_columns['location_type'] = __('Location Type', 'my-events-calendar');
        } elseif ($key !== 'date') {
            // Add other columns except for the Date column
            $new_columns[$key] = $value;
        }
    }

    // Add the Date column at the end
    $new_columns['date'] = $columns['date'];

    return $new_columns;
}
add_filter('manage_event_location_posts_columns', 'my_events_calendar_location_add_custom_columns');

// Populate custom columns with data
function my_events_calendar_location_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'location_type':
            $location_type = get_post_meta($post_id, '_location_type', true);
            echo esc_html($location_type);
            break;
    }
}
add_action('manage_event_location_posts_custom_column', 'my_events_calendar_location_custom_column_content', 10, 2);

// Clean up metadata when a location is deleted
function my_events_calendar_cleanup_location_meta($post_id) {
    if (get_post_type($post_id) === 'event_location') {
        delete_post_meta($post_id, '_location_type');
        delete_post_meta($post_id, '_location_address');
        delete_post_meta($post_id, '_location_city');
        delete_post_meta($post_id, '_location_state');
        delete_post_meta($post_id, '_location_zip');
        delete_post_meta($post_id, '_location_url');
    }
}

// Make custom columns sortable for locations
function my_events_calendar_location_sortable_columns($columns) {
    $columns['location_type'] = 'location_type';
    return $columns;
}
add_filter('manage_edit-event_location_sortable_columns', 'my_events_calendar_location_sortable_columns');

function my_events_calendar_location_orderby_custom_columns($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'event_location') {
        return;
    }

    if ('location_type' === $query->get('orderby')) {
        $query->set('meta_key', '_location_type');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'my_events_calendar_location_orderby_custom_columns');