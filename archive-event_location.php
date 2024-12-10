<?php
get_header();
?>

<div class="mec-event-locations-archive">
    <header class="mec-archive-header">
        <h1 class="mec-archive-title"><?php _e('Event Locations', 'my-events-calendar'); ?></h1>
    </header>

    <?php if (have_posts()) : ?>
        <div class="mec-event-locations-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article class="mec-event-location-item">
                    <div class="mec-location-card">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="mec-location-image">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="mec-location-content">
                            <h2 class="mec-location-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>

                            <?php
                            $location_type = get_post_meta(get_the_ID(), '_location_type', true);
                            $location_address = get_post_meta(get_the_ID(), '_location_address', true);
                            $location_city = get_post_meta(get_the_ID(), '_location_city', true);
                            $location_state = get_post_meta(get_the_ID(), '_location_state', true);
                            ?>

                            <?php if ($location_type === 'physical' && ($location_address || $location_city || $location_state)) : ?>
                                <div class="mec-location-details">
                                    <?php if ($location_address) : ?>
                                        <p class="mec-address"><?php echo esc_html($location_address); ?></p>
                                    <?php endif; ?>
                                    
                                    <?php if ($location_city || $location_state) : ?>
                                        <p class="mec-city-state">
                                            <?php 
                                            $location_parts = array_filter(array($location_city, $location_state));
                                            echo esc_html(implode(', ', $location_parts));
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <p class="mec-virtual-label"><?php _e('Virtual Location', 'my-events-calendar'); ?></p>
                            <?php endif; ?>

                            <?php
                            // Count upcoming events at this location
                            $upcoming_events = new WP_Query(array(
                                'post_type' => 'event',
                                'meta_query' => array(
                                    array(
                                        'key' => '_location_id',
                                        'value' => get_the_ID(),
                                    ),
                                    array(
                                        'key' => '_start_date',
                                        'value' => date('Y-m-d'),
                                        'compare' => '>=',
                                        'type' => 'DATE'
                                    )
                                ),
                                'posts_per_page' => -1,
                            ));
                            $event_count = $upcoming_events->found_posts;
                            wp_reset_postdata();
                            ?>

                            <p class="mec-upcoming-events-count">
                                <?php 
                                printf(
                                    _n(
                                        '%s Upcoming Event',
                                        '%s Upcoming Events',
                                        $event_count,
                                        'my-events-calendar'
                                    ),
                                    number_format_i18n($event_count)
                                ); 
                                ?>
                            </p>

                            <a href="<?php the_permalink(); ?>" class="mec-view-location-link">
                                <?php _e('View Location', 'my-events-calendar'); ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php
        the_posts_pagination(array(
            'mid_size' => 2,
            'prev_text' => __('Previous', 'my-events-calendar'),
            'next_text' => __('Next', 'my-events-calendar'),
        ));
        ?>

    <?php else : ?>
        <div class="mec-no-locations">
            <p><?php _e('No locations found.', 'my-events-calendar'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
?> 