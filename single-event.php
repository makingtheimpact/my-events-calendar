<?php

if (wp_is_block_theme()) {
    get_header('block');
} else {
    get_header();
}

// Get color options
$options = mec_get_options();
$accent_background_color = $options['accent_background_color'] ?? '#0067d4';
$accent_background_color_hover = $options['accent_background_color_hover'] ?? '#0f7bee';
$accent_text_color = $options['accent_text_color'] ?? '#ffffff';
$accent_text_color_hover = $options['accent_text_color_hover'] ?? '#ffffff';

// Add inline styles for buttons
$css = '<style>';
$css .= "
    .mec-event-ticket-link {
        background-color: {$accent_background_color};
        color: {$accent_text_color};
        padding: 10px 20px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        margin: 10px 0;
        transition: all 0.3s ease;
    }
    .mec-event-ticket-link:hover, 
    .mec-event-ticket-link:active {
        background-color: {$accent_background_color_hover};
        color: {$accent_text_color_hover};
        text-decoration: none;
    }    
    .mec-share-button {
        color: {$accent_background_color};
    }
    .mec-share-button:hover,
    .mec-share-button:active {
        color: {$accent_background_color_hover};
    }
    #mec-event-map {
        height: 400px;
        width: 100%;
        margin: 20px 0;
        border-radius: 8px;
    }
";
$css .= '</style>';
echo $css;

echo '<div class="mec-event-details-container"><div class="container">';
while (have_posts()) : the_post();
    $options = get_option('my_events_calendar_options');
    $date_format = isset($options['date_format']) ? $options['date_format'] : 'm/d/Y';
    $time_format = isset($options['time_format']) ? $options['time_format'] : 'g:i A';

    $post_id = get_the_ID();
    $start_date = get_post_meta($post_id, '_start_date', true) ?: '';
    $end_date = get_post_meta($post_id, '_end_date', true) ?: '';
    $start_time = get_post_meta($post_id, '_start_time', true) ?: '';
    $end_time = get_post_meta($post_id, '_end_time', true) ?: '';
    $formatted_start_date = date($date_format, strtotime($start_date));
    $formatted_end_date = date($date_format, strtotime($end_date));
    $formatted_start_time = date($time_format, strtotime($start_time));
    $formatted_end_time = date($time_format, strtotime($end_time));
    $location_id = get_post_meta($post_id, '_location_id', true);
    if ($location_id) {
        $location_name = get_the_title($location_id);
        $location_type = get_post_meta($location_id, '_location_type', true);
        $location_url = get_post_meta($location_id, '_location_url', true);
        if ($location_type == 'physical') {
            $location_address = get_post_meta($location_id, '_location_address', true);
            $location_city = get_post_meta($location_id, '_location_city', true);
            $location_state = get_post_meta($location_id, '_location_state', true);
            $location_zip = get_post_meta($location_id, '_location_zip', true);
            // insert commas between location fields if they exist
            $location_full_address = $location_address ? $location_address . ', ' : '';
            $location_full_address .= $location_city ? $location_city . ', ' : '';
            $location_full_address .= $location_state ? $location_state . ' ' : '';
            $location_full_address .= $location_zip ? $location_zip : '';
        } else {
            $location_full_address = '';
        }
    } else {
        $location_type = $location_address = $location_city = $location_state = $location_zip = $location_full_address = $location_url = '';
    }
    $ticket_url = get_post_meta($post_id, '_ticket_url', true);
    $ticket_cost = get_post_meta($post_id, '_ticket_cost', true);
    $ticket_details = get_post_meta($post_id, '_ticket_details', true);
    $options = get_option('my_events_calendar_options');
    $show_share_buttons = isset($options['show_share_buttons']) ? $options['show_share_buttons'] : 'yes';
    $api_key = isset($options['google_maps_api_key']) ? $options['google_maps_api_key'] : '';
    $all_day_event = get_post_meta($post_id, '_all_day_event', true);
    $show_images = isset($options['show_images']) && $options['show_images'] === 'yes';

    // Get the first category color and text color
    $categories = get_the_terms(get_the_ID(), 'event_category');
    $event_category_bg = get_term_meta($categories[0]->term_id, 'category_color', true);
    $event_category_text_color = get_term_meta($categories[0]->term_id, 'category_text_color', true);

    $post_url = get_permalink($post_id); // Get the event URL

    // Get the display options
    $show_categories = isset($options['show_categories']) ? $options['show_categories'] : 'yes';

    // Only show categories if the setting is enabled
    $category_list = '';
    if ($show_categories === 'yes') {        
        if ($categories && !is_wp_error($categories)) {
            $category_list = '<div class="mec-event-category-bar" style="background-color: ' . esc_attr($event_category_bg) . '; color: ' . esc_attr($event_category_text_color) . ';">';
            
            // Create an array of category links
            $category_links = array();
            foreach ($categories as $category) {
                $category_links[] = '<a href="' . esc_url(get_term_link($category)) . '" style="color: ' . esc_attr($event_category_text_color) . ' !important;"><span class="mec-event-category-name">' . 
                                  esc_html($category->name) . '</span></a>';
            }
            
            // Join the category links with commas
            $category_list .= implode(', ', $category_links);
            
            $category_list .= '</div>';
        }
    }
    ?>

    <div class="row">
        <div class="col-md-12">
            <?php if ($show_categories === 'yes' && $category_list) : ?>
                <?php echo $category_list; ?>
            <?php endif; ?>
            <div class="mec-event-title">
                <h1 class="mec-event-title-text"><?php the_title(); ?></h1>
            </div>
            <div class="mec-event-image-container">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="mec-event-image">
                        <?php the_post_thumbnail('large'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="mec-event-date-details">
                <?php if ($start_date != $end_date) : ?>
                    <p class="mec-event-date-range"><?php echo $formatted_start_date; ?> - <?php echo $formatted_end_date; ?></p>
                <?php else : ?>
                    <p class="mec-event-date"><?php echo $formatted_start_date; ?></p>
                <?php endif; ?>
                <?php if (!$all_day_event) {
                    if (isset($start_time) && isset($end_time)) {
                        echo '<p class="mec-event-time">' . $formatted_start_time . ' - ' . $formatted_end_time . '</p>';
                    } elseif (isset($start_time)) {
                        echo '<p class="mec-event-time">' . $formatted_start_time . '</p>';
                    }
                } ?>
            </div>

            <div class="mec-event-location-summary">
                <?php if ($location_type === 'physical') : ?>
                    <p class="mec-event-location-name"><?php echo esc_html($location_name); ?></p>
                    <p class="mec-event-location-address"><?php echo esc_html($location_address); ?><br>
                    <?php echo esc_html($location_city) . ', ' . esc_html($location_state) . ' ' . esc_html($location_zip); ?></p>
                <?php elseif ($location_type === 'virtual') : ?>
                    <p class="mec-event-location-name"><?php echo esc_html($location_name); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-4">
            <?php if (($ticket_cost && $ticket_cost != '') || ($ticket_url && $ticket_url != '') || ($ticket_details && $ticket_details != '')) : ?>
                <div class="mec-event-ticket-details">  
                    <?php if (isset($ticket_cost) && $ticket_cost != '') : ?>
                        <p class="mec-event-ticket-cost"><span class="mec-event-ticket-cost-label">Ticket Cost:</span> <span class="mec-event-ticket-cost-value"><?php echo esc_html($ticket_cost); ?></span></p>
                    <?php endif; ?>            
                    <?php if ($ticket_url && $ticket_url != '') : ?>
                        <p class="mec-event-ticket-link-container"><a href="<?php echo esc_url($ticket_url); ?>" target="_blank" class="mec-event-ticket-link">Get Tickets</a></p>
                    <?php endif; ?>
                    <?php if (isset($ticket_details)) : ?>
                        <p class="mec-event-ticket-details-text"><?php echo esc_html($ticket_details); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="mec-event-add-to-calendar-buttons">
                <?php my_events_calendar_add_to_calendar_buttons(get_the_ID()); ?>
            </div>
            <div class="mec-event-share-buttons">
                <?php my_events_calendar_share_buttons(get_the_ID()); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="mec-event-description">
                <h3 class="mec-event-description-header">Event Description</h3>
                <?php the_content(); ?>
            </div>
        </div>
    </div>

    <?php if ($location_id && ($location_name || $location_address || $location_city || $location_state || $location_zip || $location_url)) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="mec-event-location-details">
                <div class="mec-event-location-details-header">
                    <h3>Event Location</h3>
                </div>
                <div class="mec-event-location-details-body">
                    <div class="container">
                        <div class="row">
                            <?php $location_thumbnail_set = has_post_thumbnail($location_id); ?>
                            <div class="<?php echo $location_thumbnail_set ? 'col-md-2' : 'col-md-12'; ?>">
                                <?php if (has_post_thumbnail($location_id)) {
                                    echo '<div class="mec-event-location-image">' . get_the_post_thumbnail($location_id, 'thumbnail') . '</div>';
                                } ?>
                            </div>
                            <div class="<?php echo $location_thumbnail_set ? 'col-md-10' : 'col-md-12'; ?>">
                                <?php if (isset($location_name) && $location_name != '') : ?>
                                    <p class="mec-event-location-name"><?php if (isset($location_url)) : ?><a href="<?php echo esc_url($location_url); ?>" target="_blank"><?php endif; echo esc_html($location_name); ?><?php if (isset($location_url)) : ?></a><?php endif; ?></p>
                                <?php endif; ?>
                                <?php if ($location_type === 'physical') : ?>
                                    <div class="mec-event-location">
                                        <?php if (isset($location_address)) : ?>
                                            <p class="mec-event-location-address"><?php echo esc_html($location_address); ?><?php if (isset($location_city) || isset($location_state) || isset($location_zip)) : ?><br><?php endif; ?>
                                            <?php echo esc_html($location_city) . ', ' . esc_html($location_state) . ' ' . esc_html($location_zip); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($location_url) && $location_url != '') : ?>
                                    <div class="mec-event-location-url">
                                        <p><span class="mec-event-location-link-label">Event Location Link:</span> <a href="<?php echo esc_url($location_url); ?>" target="_blank"><?php echo isset($location_name) ? esc_html($location_name) : 'Event Location Link'; ?></a></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                // Get the display options
                $options = get_option('my_events_calendar_options');
                $show_map = isset($options['show_map']) ? $options['show_map'] : 'yes';
                $api_key = isset($options['google_maps_api_key']) ? $options['google_maps_api_key'] : '';

                // Only show map if setting is enabled and we have an API key and physical location
                if ($show_map === 'yes' && !empty($api_key) && $location_type === 'physical' && !empty($location_address)) {
                    ?>
                    <div id="mec-event-map"></div>
                    <script>
                    let map;
                    let marker;

                    async function initMap() {
                        if (!google || !google.maps) {
                            console.error('Google Maps not loaded');
                            return;
                        }

                        const address = '<?php echo esc_js($location_address . ', ' . $location_city . ', ' . $location_state . ' ' . $location_zip); ?>';
                        const geocoder = new google.maps.Geocoder();
                        
                        // Initial map setup with US center
                        map = new google.maps.Map(document.getElementById('mec-event-map'), {
                            zoom: 4,
                            center: { lat: 39.8283, lng: -98.5795 }, // US center
                            mapId: 'event_map'
                        });

                        try {
                            // Geocode the address
                            const response = await geocoder.geocode({ address: address });
                            
                            if (response && response.results && response.results[0]) {
                                const location = response.results[0].geometry.location;
                                
                                // Center map on the location
                                map.setCenter(location);
                                map.setZoom(15);

                                // Create the marker
                                marker = new google.maps.marker.AdvancedMarkerElement({
                                    map: map,
                                    position: location,
                                    title: '<?php echo esc_js($location_name); ?>'
                                });
                            } else {
                                console.error('No results found for address:', address);
                            }
                        } catch (error) {
                            console.error('Geocoding error:', error);
                        }
                    }

                    // Make initMap available globally
                    window.initMap = initMap;
                    </script>
                    <p class="mec-event-location-link">
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($location_full_address); ?>" 
                            target="_blank" class="mec-map-direction-link">
                            Get Directions to <?php echo esc_html($location_name); ?>
                        </a>
                    </p>
                    <?php
                }
                ?>

            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <?php my_events_calendar_share_buttons(get_the_ID()); ?>
        </div>
    </div>
    </div>
</div>
<?php endwhile;
echo '<input type="text" id="mec_event_link_copy" value="' . esc_url($post_url) . '" style="position: absolute; left: -9999px;">';
echo '<script>
        function copyToClipboard() {
            var input = document.getElementById("mec_event_link_copy");
            input.select();
            document.execCommand("copy");
            alert("Event link copied to clipboard!");
        }
    </script>';

if (wp_is_block_theme()) {
    get_footer('block');
} else {
    get_footer();
}