<?php

function my_events_calendar_event_list_display($atts) {
    $options = get_option('my_events_calendar_options');

    // Set default attributes
    $atts = shortcode_atts(array(
        'compact' => 'no', // Default to 'no'
        'show_images' => 'yes', // Default to 'yes'
        'date_range' => 'all', // Default to 'all'
        'category' => '', // Default to empty
    ), $atts, 'my_events_calendar_list');

    // show images default to yes
    $show_images = $atts['show_images'] === 'no' ? false : true;

    // Calculate date range based on the attribute value
    $range_end_date = '';
    switch ($atts['date_range']) {
        case 'next_7_days':
            $range_end_date = date('Y-m-d', strtotime('+7 days'));
            break;
        case 'next_30_days':
            $range_end_date = date('Y-m-d', strtotime('+30 days'));
            break;
        case 'next_6_months':
            $range_end_date = date('Y-m-d', strtotime('+6 months'));
            break;
        case 'next_week':
            $range_end_date = date('Y-m-d', strtotime('next sunday'));
            break;
        case 'today':
            $range_end_date = date('Y-m-d');
            break;
        case 'this_week':
            $range_end_date = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'this_month':
            $range_end_date = date('Y-m-t'); // Last day of this month
            break;
        case 'this_year':
            $range_end_date = date('Y-12-31'); // Last day of this year
            break;
        default:
            // If 'all', no end date restriction
            $range_end_date = null;
            break;
    }

    $category = !empty($atts['category']) ? sanitize_text_field($atts['category']) : '';

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

    // Build WP_Query arguments
    $args = array(
        'post_type' => 'event',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'meta_key' => '_start_date',
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'paged' => $paged,
        'meta_query' => array(
            array(
                'key' => '_start_date',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            )
        )
    );

    // Add range end date to meta_query if applicable
    if ($range_end_date) {
        $args['meta_query'][] = array(
            'key' => '_end_date',
            'value' => $range_end_date,
            'compare' => '<=',
            'type' => 'DATE'
        );
    }

    // Add category filter if provided
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

    // Set list classes based on compact and images attributes
    $compact_class = $atts['compact'] === 'yes' ? 'mec-compact-event-list' : '';
    $show_images_class = $atts['show_images'] === 'no' ? 'mec-event-list-hide-images' : '';

    // Start output buffering
    ob_start();
    echo '<div class="mec-event-list-container ' . $compact_class . ' ' . $show_images_class . '">';
    if ($query->have_posts()) :
        $group_start_date = '';
        while ($query->have_posts()) : $query->the_post();
            $current_start_date = get_post_meta(get_the_ID(), '_start_date', true);
            if ($current_start_date !== $group_start_date) {
                $group_start_date = $current_start_date;
                echo '<div class="mec-event-list-group-header">' . date('l, F j, Y', strtotime($group_start_date)) . '</div>';
            }
            echo '<a href="' . get_permalink() . '" class="mec-event-list-item-container-link">';
            my_events_calendar_display_event_list_item(get_the_ID(), $show_images);
            echo '</a>';
        endwhile;

        // Display pagination
        echo '<div class="pagination-container">';
        echo paginate_links(array(
            'total' => $query->max_num_pages,
            'current' => $paged,
            'mid_size' => 2,
            'prev_text' => __('« Previous', 'my-events-calendar'),
            'next_text' => __('Next »', 'my-events-calendar'),
        ));
        echo '</div>';
    else: 
        // Display message if no events found
        $date_range_text = '';
        switch ($atts['date_range']) {
            case 'next_7_days':
                $date_range_text = ' within the next 7 days';
                break;
            case 'next_30_days':
                $date_range_text = ' within the next 30 days';
                break;
            case 'next_6_months':
                $date_range_text = ' within the next 6 months';    
                break;
            case 'next_week':
                $date_range_text = ' within the next week';
                break;
            case 'today':
                $date_range_text = ' today';
                break;
            case 'this_week':
                $date_range_text = ' this week';
                break;
            case 'this_month':
                $date_range_text = ' this month';
                break;
            case 'this_year':
                $date_range_text = ' this year';
                break;                
        }
        echo '<p class="mec-no-events-list-message">There are currently no upcoming events scheduled' . $date_range_text . '.</p>';
    endif;
    echo '</div>';
    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('my_events_calendar_list', 'my_events_calendar_event_list_display');

function my_events_calendar_display_event_list_item($event_id, $show_images = true) {
    $options = get_option('my_events_calendar_options');
    $date_format = isset($options['date_format']) ? $options['date_format'] : 'm/d/Y';
    $time_format = isset($options['time_format']) ? $options['time_format'] : 'g:i A';

    $event = get_post($event_id);
    // Get the category color and text colors
    $category = get_the_terms($event_id, 'event_category');
    $category_color = '#007bff'; // Default blue
    $text_color = '#ffffff'; // Default white
    if ($category && !is_wp_error($category)) {
        $category_color = get_term_meta($category[0]->term_id, 'category_color', true);
        $text_color = get_term_meta($category[0]->term_id, 'category_text_color', true);
    }
    $location_id = get_post_meta($event_id, '_location_id', true); // Retrieve location ID

    echo '<div class="mec-event-list-item" style="border-color: ' . esc_attr($category_color) . ';">
        <div class="container">
            <div class="row">';
                if ($show_images && has_post_thumbnail($event_id)) {
                    echo '<div class="col-md-2"><div class="mec-event-thumbnail">' . get_the_post_thumbnail($event_id, 'thumbnail') . '</div></div><div class="col-md-10">';
                } else {
                    echo '<div class="col-md-12">';
                }
                echo '<div class="mec-event-details">';
                    echo '<h2 class="mec-event-title">' . get_the_title($event_id) . '</h2>';

                    // Show the event dates and times   
                    $start_date = get_post_meta($event_id, '_start_date', true);
                    $end_date = get_post_meta($event_id, '_end_date', true);
                    $start_time = get_post_meta($event_id, '_start_time', true);
                    $end_time = get_post_meta($event_id, '_end_time', true);
                    $formatted_start_date = date($date_format, strtotime($start_date));
                    $formatted_end_date = date($date_format, strtotime($end_date));
                    $formatted_start_time = date($time_format, strtotime($start_time));
                    $formatted_end_time = date($time_format, strtotime($end_time));

                    $all_day_event = get_post_meta(get_the_ID(), '_all_day_event', true);

                    if ($start_date === $end_date) {
                        if (!$all_day_event && $start_time) {
                            echo '<div class="mec-event-date">' . $formatted_start_date . ' - ' . $formatted_end_date . ' at ' . $formatted_start_time . ' - ' . $formatted_end_time . '</div>';
                        } else {
                            echo '<div class="mec-event-date">' . $formatted_start_date . '</div>';
                        }
                    } else {
                        if (!$all_day_event && $start_time && $end_time) {
                            echo '<div class="mec-event-date">' . $formatted_start_date . ' at ' . $formatted_start_time . ' - ' . $formatted_end_date . ' at ' . $formatted_end_time . '</div>';
                        } else {
                            echo '<div class="mec-event-date">' . $formatted_start_date . ' - ' . $formatted_end_date . '</div>';
                        }
                    }
                    // Show the event description if enabled
                    echo '<div class="mec-event-description">' . wp_trim_words(get_the_content($event_id), 20) . '</div>';

                    // Location
                    if ($location_id) {
                        $location = get_post($location_id);
                        if ($location) {
                            // if location is physical, show the full address
                            if (get_post_meta($location_id, '_location_type', true) === 'physical') {
                                // add commas between address, city, state, and zip if they are not empty
                                $full_location_address = get_post_meta($location_id, '_location_address', true);
                                if ($full_location_address) {
                                    $full_location_address .= ', ';
                                }   
                                $full_location_address .= get_post_meta($location_id, '_location_city', true);
                                if ($full_location_address) {
                                    $full_location_address .= ', ';
                                }
                                $full_location_address .= get_post_meta($location_id, '_location_state', true);
                                if ($full_location_address) {
                                    $full_location_address .= ', ';
                                }
                                $full_location_address .= get_post_meta($location_id, '_location_zip', true);
                                echo '<div class="mec-event-location"><strong>Event Location:</strong> ' . esc_html($location->post_title) . ' - ' . esc_html($full_location_address) . '</div>';
                            } else {
                                echo '<div class="mec-event-location"><strong>Event Location:</strong> ' . esc_html($location->post_title) . '</div>';
                            }
                        }
                    }
                echo '</div>
                </div>';
        echo '</div>
        </div>
    </div>';
}
