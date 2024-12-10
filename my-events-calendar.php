<?php
/*
Plugin Name: My Events Calendar
Description: A simple calendar and event management system for WordPress.
Version: 1.0.5
Author: Making The Impact LLC
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
GitHub Plugin URI: makingtheimpact/my-events-calendar
Text Domain: my-events-calendar
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 7.0
Tags: events, calendar, event management, scheduling, WordPress
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/event-location-post-type.php';
include_once plugin_dir_path(__FILE__) . 'includes/event-functions.php';
include_once plugin_dir_path(__FILE__) . 'includes/event-category-type.php';
include_once plugin_dir_path(__FILE__) . 'includes/event-post-type.php';
include_once plugin_dir_path(__FILE__) . 'includes/template-parts.php';
include_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';
include_once plugin_dir_path(__FILE__) . 'includes/calendar-display.php';
include_once plugin_dir_path(__FILE__) . 'includes/event-list-display.php';
include_once plugin_dir_path(__FILE__) . 'includes/github-updater.php';

// Activation hook
function my_events_calendar_activate() {
    // Trigger our function that registers the custom post type
    my_events_calendar_register_post_type();
    // Clear the permalinks after the post type has been registered
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'my_events_calendar_activate');

function my_events_calendar_template_include($template) {
    if (is_singular('event')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'single-event.php';
        if (file_exists($plugin_template)) {            
            return $plugin_template;
        }
    } elseif (is_singular('event_location')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'single-event_location.php';
        if (file_exists($plugin_template)) {            
            return $plugin_template;
        }
    } elseif (is_post_type_archive('event_location')) {
        $plugin_template = plugin_dir_path(__FILE__) . 'archive-event_location.php';
        if (file_exists($plugin_template)) {            
            return $plugin_template;
        }
    }
    return $template;
}

add_filter('template_include', 'my_events_calendar_template_include');

// Add the custom post type to the main query
function my_events_calendar_pre_get_posts($query) {
    if (is_home() && $query->is_main_query()) {
        $query->set('post_type', array('event'));
    }
}
add_action('pre_get_posts', 'my_events_calendar_pre_get_posts');

function my_events_calendar_enqueue_scripts() {
    // Enqueue jQuery (WordPress already includes jQuery by default, no need to enqueue unless custom scripts require it)
    wp_enqueue_script('jquery');

    // Enqueue Bootstrap CSS and JS (use a more recent version if necessary)
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', array('jquery'), '4.5.2', true);

    // Enqueue FullCalendar script
    wp_enqueue_script('fullcalendar', plugin_dir_url(__FILE__) . 'includes/js/fullcalendar-6-1-15.min.js', array('jquery'), '6.1.15', true);

    // Enqueue custom FullCalendar script
    wp_enqueue_script('fullcalendar-custom', plugin_dir_url(__FILE__) . 'includes/js/fullcalendar-custom.js', array('jquery', 'fullcalendar', 'bootstrap-js'), '1.0', true);

    // Enqueue public styles
    wp_enqueue_style('my-events-calendar-styles', plugin_dir_url(__FILE__) . 'assets/css/styles.css');

    // Enqueue public JS
    wp_enqueue_script('my-events-calendar-js', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0', true);

    // Fetch events from the database
    $events = array();
    $query = new WP_Query(array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $events[] = array(
            'title' => get_the_title(),
            'start' => get_post_meta($post_id, '_start_date', true) . 'T' . get_post_meta($post_id, '_start_time', true),
            'end' => get_post_meta($post_id, '_end_date', true) . 'T' . get_post_meta($post_id, '_end_time', true),
            'url' => get_permalink(),
            'extendedProps' => array(
                'image' => get_the_post_thumbnail_url($post_id, 'thumbnail'),
                'excerpt' => wp_trim_words(get_the_content(), 20),
                'location' => get_post_meta($post_id, '_location_name', true),
            ),
        );
    }
    wp_reset_postdata();

    // Localize script with settings and events
    $options = get_option('my_events_calendar_options');
    wp_localize_script('fullcalendar-custom', 'myEventsCalendarSettings', array(
        'defaultView' => isset($options['default_view']) ? $options['default_view'] : 'dayGridMonth',
        'events' => $events
    ));
    wp_localize_script('fullcalendar-custom', 'myCalendarAjax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));

    // Only load Google Maps API if we're on a single event page
    if (is_singular('event')) {
        $options = get_option('my_events_calendar_options');
        $api_key = isset($options['google_maps_api_key']) ? $options['google_maps_api_key'] : '';
        
        if (!empty($api_key)) {
            wp_enqueue_script(
                'google-maps-api',
                "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=marker&v=beta&callback=initMap",
                array(),
                null,
                true
            );
            wp_script_add_data('google-maps-api', 'async', true);
        }
    }
}
add_action('wp_enqueue_scripts', 'my_events_calendar_enqueue_scripts');

function my_events_calendar_admin_enqueue_scripts($hook) {
    // The correct hook for the settings page based on the submenu slug
    if ($hook === 'events-calendar_page_my-events-calendar-settings') {
        // Enqueue WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Enqueue your custom admin script with dependencies
        wp_enqueue_script(
            'my-events-calendar-admin-js',
            plugin_dir_url(__FILE__) . 'assets/js/admin-script.js',
            array('jquery', 'wp-color-picker'),
            '1.0',
            true
        );

        // Initialize color picker
        wp_add_inline_script('my-events-calendar-admin-js', '
            jQuery(document).ready(function($) {
                $(".mec-color-field").wpColorPicker();
            });
        ');
    }

    // Check if we are on the edit or add new event page
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        global $post;
        if ($post && $post->post_type === 'event') {
            wp_enqueue_script(
                'my-events-calendar-admin-js',
                plugin_dir_url(__FILE__) . 'assets/js/admin-script.js',
                array('jquery', 'wp-color-picker'),
                '1.0',
                true
            );
        }
    }

    // Enqueue admin styles globally for all admin pages
    wp_enqueue_style('my-events-calendar-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'my_events_calendar_admin_enqueue_scripts');

function my_events_calendar_enqueue_styles() {
    // Enqueue frontend styles
    wp_enqueue_style('my-events-calendar-styles', plugin_dir_url(__FILE__) . 'assets/css/styles.css');
}
add_action('wp_head', 'my_events_calendar_enqueue_styles');

function my_events_calendar_load_dashicons_front_end() {
    // Load Dashicons for frontend if necessary
    wp_enqueue_style('dashicons');
}
add_action('wp_enqueue_scripts', 'my_events_calendar_load_dashicons_front_end');
