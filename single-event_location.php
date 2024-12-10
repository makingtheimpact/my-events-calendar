<?php
get_header();

while (have_posts()) :
    the_post();
    ?>
    <div class="mec-event-location-container">
        <div class="mec-event-location-header">
            <?php if (has_post_thumbnail()) : ?>
                <div class="mec-event-location-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>
            
            <h1 class="mec-event-location-title"><?php the_title(); ?></h1>
        </div>

        <div class="mec-event-location-content">
            <?php the_content(); ?>
        </div>

        <div class="mec-event-location-details">
            <?php
            $location_type = get_post_meta(get_the_ID(), '_location_type', true);
            $location_address = get_post_meta(get_the_ID(), '_location_address', true);
            $location_city = get_post_meta(get_the_ID(), '_location_city', true);
            $location_state = get_post_meta(get_the_ID(), '_location_state', true);
            $location_zip = get_post_meta(get_the_ID(), '_location_zip', true);
            $location_url = get_post_meta(get_the_ID(), '_location_url', true);
            ?>

            <?php if ($location_type === 'physical') : ?>
                <div class="mec-event-location-address-details">
                    <h3><?php _e('Address Details', 'my-events-calendar'); ?></h3>
                    <?php if ($location_address) : ?>
                        <p class="mec-address"><?php echo esc_html($location_address); ?></p>
                    <?php endif; ?>
                    
                    <p class="mec-city-state-zip">
                        <?php 
                        $location_parts = array_filter(array($location_city, $location_state, $location_zip));
                        echo esc_html(implode(', ', $location_parts));
                        ?>
                    </p>

                    <?php if ($location_url) : ?>
                        <p class="mec-location-url">
                            <a href="<?php echo esc_url($location_url); ?>" target="_blank" rel="noopener noreferrer">
                                <?php _e('Visit Website', 'my-events-calendar'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="mec-event-location-virtual">
                    <h3><?php _e('Virtual Event', 'my-events-calendar'); ?></h3>
                    <?php if ($location_url) : ?>
                        <p class="mec-location-url">
                            <a href="<?php echo esc_url($location_url); ?>" target="_blank" rel="noopener noreferrer">
                                <?php _e('Join Virtual Event', 'my-events-calendar'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Display upcoming events at this location
        $upcoming_events = new WP_Query(array(
            'post_type' => 'event',
            'meta_query' => array(
                array(
                    'key' => '_location_id',
                    'value' => get_the_ID(),
                ),
            ),
            'meta_key' => '_start_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'posts_per_page' => 5,
        ));

        if ($upcoming_events->have_posts()) : ?>
            <div class="mec-event-location-upcoming-events">
                <h3><?php _e('Upcoming Events at this Location', 'my-events-calendar'); ?></h3>
                <ul class="mec-events-list">
                    <?php while ($upcoming_events->have_posts()) : $upcoming_events->the_post(); ?>
                        <li class="mec-event-item">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            <?php 
                            $start_date = get_post_meta(get_the_ID(), '_start_date', true);
                            if ($start_date) {
                                echo ' - ' . date_i18n(get_option('date_format'), strtotime($start_date));
                            }
                            ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
            <?php 
            wp_reset_postdata();
        endif; 
        ?>
    </div>
    <?php
endwhile;

get_footer(); 