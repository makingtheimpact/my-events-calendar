<?php

if (wp_is_block_theme()) {
    get_header('block');
} else {
    get_header();
}

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

    $post_url = get_permalink($post_id); // Get the event URL
    ?>

    <div class="row">
        <div class="col-md-12">
            <div class="mec-event-title">
                <h2><?php the_title(); ?></h2>
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
                <div class="container">
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
                </div>
                <?php if ($api_key && $location_type == 'physical' && $location_full_address != '') : ?>
                    <div id="mec-event-map" style="height: 400px;"></div>
                    <script>
                        function initMap() {
                            var geocoder = new google.maps.Geocoder();
                            geocoder.geocode({'address': '<?php echo esc_js($location_full_address); ?>'}, function(results, status) {
                                if (status === 'OK') {
                                    var map = new google.maps.Map(document.getElementById('mec-event-map'), {
                                        center: results[0].geometry.location,
                                        zoom: 15
                                    });
                                    var marker = new google.maps.Marker({
                                        map: map,
                                        position: results[0].geometry.location
                                    });
                                }
                            });
                        }
                    </script>
                    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr($api_key); ?>&callback=initMap"></script>
                    <p class="mec-event-location-link">Map and Directions for <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($location_full_address); ?>" target="_blank"><?php echo esc_html($location_full_address); ?></a></p>
                <?php endif; ?>
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