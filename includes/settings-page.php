<?php

function mec_get_default_options() {
    return array(
        'event_slug' => 'events',
        'location_slug' => 'event-location',
        'category_slug' => 'event-category',
        'accent_background_color' => '#0067d4',
        'accent_background_color_hover' => '#0f7bee',
        'accent_text_color' => '#ffffff',
        'accent_text_color_hover' => '#ffffff',
        'show_map' => 'yes',
        'show_categories' => 'yes',
        'default_view' => 'month',
        'google_maps_api_key' => '',
        'map_center_lat' => '37.0902',
        'map_center_lng' => '-95.7129',
        'map_zoom' => '4'
    );
}

function mec_get_options() {
    $defaults = mec_get_default_options();
    $options = get_option('my_events_calendar_options', array());
    return wp_parse_args($options, $defaults);
}

function my_events_calendar_add_admin_menu() {
    add_menu_page(
        'Events Calendar',
        'Events Calendar',
        'manage_options',
        'my-events-calendar',
        'my_events_calendar_dashboard',
        'dashicons-calendar-alt'
    );

    add_submenu_page(
        'my-events-calendar',
        'View All Events',
        'View All Events',
        'manage_options',
        'edit.php?post_type=event'
    );

    add_submenu_page(
        'my-events-calendar',
        'Add Event',
        'Add Event',
        'manage_options',
        'post-new.php?post_type=event'
    );

    add_submenu_page(
        'my-events-calendar',
        'Event Locations',
        'Event Locations',
        'manage_options',
        'edit.php?post_type=event_location'
    );

    add_submenu_page(
        'my-events-calendar',
        'Event Categories',
        'Event Categories',
        'manage_options',
        'edit-tags.php?taxonomy=event_category&post_type=event'
    );

    add_submenu_page(
        'my-events-calendar',
        'Settings',
        'Settings',
        'manage_options',
        'my-events-calendar-settings',
        'my_events_calendar_settings_page'
    );
}

add_action('admin_menu', 'my_events_calendar_add_admin_menu');

function my_events_calendar_dashboard() {
    echo '<h1>Events Calendar</h1>';
    // Buttons to add events, locations, categories, and settings
    echo '<a href="post-new.php?post_type=event" class="mec-button mec-add-event-button">Add Event</a>';
    echo '<a href="edit.php?post_type=event_location" class="mec-button mec-add-location-button">Add Location</a>';
    echo '<a href="edit-tags.php?taxonomy=event_category&post_type=event" class="mec-button mec-add-category-button">Add Category</a>';
    echo '<a href="admin.php?page=my-events-calendar-settings" class="mec-button mec-settings-button">Settings</a>';

    // Instructions for what to do
    echo '<h3>Instructions</h3>';
    // Explain they should create some locations first, then some categories, then some events
    echo '<p>Creating an event calendar is simple! Just follow these steps:</p>';
    echo '<ol>';
    echo '<li>Create at least <a href="edit.php?post_type=event_location">one event location</a> where the event will take place.</li>';
    echo '<li>Create some <a href="edit-tags.php?taxonomy=event_category&post_type=event">categories</a> to classify your events. Assign some colors to the categories to make them more visually distinct.</li>';
    echo '<li>Add your <a href="post-new.php?post_type=event">events</a>! For each event, select the location, category, and add a featured image.</li>';
    echo '<li>Change the default view and other settings using the <a href="my-events-calendar-settings">Settings</a> page.</li>';
    echo '<li>Use the shortcodes below to embed the calendar or event list on any page or post.</li>';
    echo '<li>That\'s it! Your event calendar is ready to use.</li>';
    echo '</ol>';

    // Fetch all event categories
    $categories = get_terms(array(
        'taxonomy' => 'event_category',
        'hide_empty' => false,
    ));

    echo '<h3>Shortcodes</h3>';
    // Main shortcodes
    echo '<p>Use the following shortcodes to display the calendar or event list:</p>';
    echo '<h4>Calendar</h4>';
    $shortcode = '[my_events_calendar]';
    echo '<input type="text" value="' . esc_attr($shortcode) . '" class="mec-shortcode-input" readonly /> <button onclick="copyToClipboard(this)">Copy</button>';
    echo '<p>This will display the full calendar with all events.</p>'; 
    echo '<h4>Event List</h4>';
    $shortcode = '[my_events_calendar_list]';
    echo '<input type="text" value="' . esc_attr($shortcode) . '" class="mec-shortcode-input" readonly /> <button onclick="copyToClipboard(this)">Copy</button>';
    echo '<p>This will display a list of upcoming events.</p>';
    echo '<h4>Compact Event List</h4>';
    $compact_shortcode = '[my_events_calendar_list compact="yes" show_images="no"]';
    echo '<input type="text" value="' . esc_attr($compact_shortcode) . '" class="mec-shortcode-input" readonly /> <button onclick="copyToClipboard(this)">Copy</button>';
    echo '<p>This will display a compact version of the event list without images.</p>';
    echo '<h4>Event List with Date Range</h4>';
    $date_range_shortcode = '[my_events_calendar_list date_range="next_7_days"]';
    echo '<input type="text" value="' . esc_attr($date_range_shortcode) . '" class="mec-shortcode-input" readonly /> <button onclick="copyToClipboard(this)">Copy</button>';
    echo '<p>This will display a list of events for the next 7 days. Examples of other date ranges include "next_30_days", "next_6_months", "next_week", "today", "this_week", "this_month", "this_year".</p>';

    // Category shortcodes
    echo '<p>Use the following shortcodes to display events filtered by category:</p>';
    echo '<h4>Calendar</h4>';
    // if there are categories, display the shortcodes
    if ($categories) {
        echo '<ul>';
        foreach ($categories as $category) {
            $shortcode = '[my_events_calendar category="' . esc_attr($category->slug) . '"]';
            echo '<li>' . esc_html($category->name) . ': <input type="text" value="' . esc_attr($shortcode) . '" class="mec-shortcode-input" readonly /> <button onclick="copyToClipboard(this)">Copy</button></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No categories found. Please create some categories first. <a href="edit-tags.php?taxonomy=event_category&post_type=event" class="mec-button mec-add-category-button">Add a Category</a></p>';
    }

    echo '<h4>Event List</h4>';
    if ($categories) {
        echo '<ul>';
        foreach ($categories as $category) {
            $shortcode = '[my_events_calendar_list category="' . esc_attr($category->slug) . '"]';
            echo '<li>' . esc_html($category->name) . ': <input type="text" value="' . esc_attr($shortcode) . '" class="mec-shortcode-input" readonly /> <button onclick="copyToClipboard(this)">Copy</button></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No categories found. Please create some categories first. <a href="edit-tags.php?taxonomy=event_category&post_type=event" class="mec-button mec-add-category-button">Add a Category</a></p>';
    }

    // Example for multiple categories
    echo '<p>To filter by multiple categories, separate them with commas:</p>';
    echo '<input type="text" value="[my_events_calendar category=\'category1,category2\']" class="mec-shortcode-input" readonly /> <button onclick="copyToClipboard(this)">Copy</button>';
    echo '<p>This will display events from both Category 1 and Category 2. Replace the category slugs with your own.</p>';

    echo '<script>
        function copyToClipboard(button) {
            var input = button.previousElementSibling;
            input.select();
            document.execCommand("copy");
            alert("Shortcode copied to clipboard!");
        }
    </script>';
}

function my_events_calendar_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Events Calendar Settings', 'my-events-calendar'); ?></h1>
        <?php settings_errors('my_events_calendar_options'); ?>
        
        <form method="post" action="options.php">
            <?php
                settings_fields('my_events_calendar_options_group');
                wp_nonce_field('my_events_calendar_save_settings', 'my_events_calendar_settings_nonce');
            ?>

            <div class="mec-settings-section-header">
                <h2><?php _e('Event Display Settings', 'my-events-calendar'); ?></h2>
                <p class="description"><?php _e('Control what information is displayed on event pages.', 'my-events-calendar'); ?></p>
            </div>
            <div class="mec-setting-section">
                <?php my_events_calendar_show_categories_render(); ?>
            </div>

            <div class="mec-settings-section-header">
                <h2><?php _e('URL Slugs', 'my-events-calendar'); ?></h2>
                <p class="description"><?php _e('Customize the URL slugs for events and locations.', 'my-events-calendar'); ?></p>
            </div>
            <div class="mec-setting-section">
                <?php
                my_events_calendar_event_slug_render();
                my_events_calendar_location_slug_render();
                ?>
            </div>

            <div class="mec-settings-section-header">
                <h2><?php _e('Event Date and Time Settings', 'my-events-calendar'); ?></h2>
                <p class="description"><?php _e('Control how the date and time are displayed on event pages.', 'my-events-calendar'); ?></p>
            </div>
            <div class="mec-setting-section">
                <?php my_events_calendar_date_format_render(); ?>
                <?php my_events_calendar_time_format_render(); ?>
            </div>

            <div class="mec-settings-section-header">
                <h2><?php _e('Appearance Settings', 'my-events-calendar'); ?></h2>
                <p class="description"><?php _e('Customize the look and feel of your events calendar.', 'my-events-calendar'); ?></p>
            </div>
            <div class="mec-setting-section">
                <?php my_events_calendar_accent_colors_render(); ?>
            </div>

            <div class="mec-settings-section-header">
                <h2><?php _e('Social Sharing Settings', 'my-events-calendar'); ?></h2>
                <p class="description"><?php _e('Configure social sharing options for events.', 'my-events-calendar'); ?></p>
            </div>
            <div class="mec-setting-section">
                <?php
                my_events_calendar_show_share_buttons_render();
                my_events_calendar_show_facebook_render();
                my_events_calendar_show_twitter_render();
                my_events_calendar_show_email_render();
                my_events_calendar_show_linkedin_render();
                my_events_calendar_show_whatsapp_render();
                my_events_calendar_show_sms_render();
                my_events_calendar_show_copy_link_render();
                ?>
            </div>

            <div class="mec-settings-section-header">
                <h2><?php _e('Calendar Integration', 'my-events-calendar'); ?></h2>
                <p class="description"><?php _e('Enable options for adding events to calendars.', 'my-events-calendar'); ?></p>
            </div>
            <div class="mec-setting-section">
                <?php
                my_events_calendar_show_add_to_calendar_render();
                my_events_calendar_show_add_to_google_calendar_render();
                my_events_calendar_show_add_to_apple_calendar_render();
                ?>
            </div>

            <div class="mec-settings-section-header">
                <h2><?php _e('Google Maps Settings', 'my-events-calendar'); ?></h2>
                <p class="description"><?php _e('Configure Google Maps integration for event locations.', 'my-events-calendar'); ?></p>
            </div>
            <div class="mec-setting-section">
                <?php 
                my_events_calendar_show_map_render();
                my_events_calendar_google_maps_api_key_render(); 
                ?>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function my_events_calendar_settings_init() {
    register_setting('my_events_calendar_options_group', 'my_events_calendar_options', 'my_events_calendar_options_validate');

    add_settings_section(
        'my_events_calendar_section',
        __('Calendar Settings', 'my-events-calendar'),
        'my_events_calendar_section_callback',
        'my-events-calendar'
    );

    add_settings_field(
        'my_events_calendar_date_format',
        __('Date Format', 'my-events-calendar'),
        'my_events_calendar_date_format_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_time_format',
        __('Time Format', 'my-events-calendar'),
        'my_events_calendar_time_format_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_default_view',
        __('Default View', 'my-events-calendar'),
        'my_events_calendar_default_view_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_share_buttons',
        __('Show Social Share Buttons', 'my-events-calendar'),
        'my_events_calendar_show_share_buttons_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_facebook',
        __('Show Facebook Share Button', 'my-events-calendar'),
        'my_events_calendar_show_facebook_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_twitter',
        __('Show Twitter Share Button', 'my-events-calendar'),
        'my_events_calendar_show_twitter_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_email',
        __('Show Email Share Button', 'my-events-calendar'),
        'my_events_calendar_show_email_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_linkedin',
        __('Show LinkedIn Share Button', 'my-events-calendar'),
        'my_events_calendar_show_linkedin_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_whatsapp',
        __('Show WhatsApp Share Button', 'my-events-calendar'),
        'my_events_calendar_show_whatsapp_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_sms',
        __('Show SMS Share Button', 'my-events-calendar'),
        'my_events_calendar_show_sms_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_copy_link',
        __('Show Copy Link Button', 'my-events-calendar'),
        'my_events_calendar_show_copy_link_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_add_to_calendar',
        __('Show Add to Calendar Buttons', 'my-events-calendar'),
        'my_events_calendar_show_add_to_calendar_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_add_to_google_calendar',
        __('Show Add to Google Calendar Button', 'my-events-calendar'),
        'my_events_calendar_show_add_to_google_calendar_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    add_settings_field(
        'my_events_calendar_show_add_to_apple_calendar',
        __('Show Add to Apple Calendar Button', 'my-events-calendar'),
        'my_events_calendar_show_add_to_apple_calendar_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );

    // Google Maps API
    add_settings_field(
        'my_events_calendar_google_maps_api_key',
        __('Google Maps API Key', 'my-events-calendar'),
        'my_events_calendar_google_maps_api_key_render',
        'my-events-calendar',
        'my_events_calendar_section'
    );
}
add_action('admin_init', 'my_events_calendar_settings_init');

function my_events_calendar_section_callback() {
    echo __('Adjust the settings for the events calendar.', 'my-events-calendar');
    echo '<h2>Available Shortcodes</h2>';
    echo '<ul>';
    echo '<li><strong>[my_events_calendar]</strong> - Displays the full calendar.</li>';
    echo '<li><strong>[my_events_calendar_list]</strong> - Displays a list of upcoming events.</li>';
    echo '</ul>';
    echo '<h2>Instructions</h2>';
    echo '<p>Use the shortcodes above to embed the calendar or event list on any page or post. Customize the display options using the settings below.</p>';
}

function my_events_calendar_default_view_render() {
    $options = get_option('my_events_calendar_options');
    $default_view = isset($options['default_view']) ? $options['default_view'] : 'dayGridMonth';
    ?>
    <select name="my_events_calendar_options[default_view]">
        <option value="dayGridMonth" <?php selected($default_view, 'dayGridMonth'); ?>><?php _e('Monthly', 'my-events-calendar'); ?></option>
        <option value="timeGridWeek" <?php selected($default_view, 'timeGridWeek'); ?>><?php _e('Weekly', 'my-events-calendar'); ?></option>
        <option value="timeGridDay" <?php selected($default_view, 'timeGridDay'); ?>><?php _e('Daily', 'my-events-calendar'); ?></option>
    </select>
    <p class="description"><?php _e('Select the default view for the calendar on the front end.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_share_buttons_render() {
    $options = get_option('my_events_calendar_options');
    $show_share_buttons = isset($options['show_share_buttons']) ? $options['show_share_buttons'] : 'yes';
    ?>
    <label>
        <input type="checkbox" name="my_events_calendar_options[show_share_buttons]" value="yes" <?php checked($show_share_buttons, 'yes'); ?> />
        <?php _e('Enable social share buttons on the event details page.', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the social share buttons on the event details page.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_google_maps_api_key_render() {
    $options = get_option('my_events_calendar_options');
    $api_key = isset($options['google_maps_api_key']) ? $options['google_maps_api_key'] : '';
    ?>
    <input type='text' name='my_events_calendar_options[google_maps_api_key]' value='<?php echo esc_attr($api_key); ?>' />
    <p class="description"><?php _e('Enter your Google Maps API key to enable map features.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_facebook_render() {
    $options = get_option('my_events_calendar_options');
    $show_facebook = isset($options['show_facebook']) ? $options['show_facebook'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_facebook]' value='yes' <?php checked($show_facebook, 'yes'); ?> />
        <?php _e('Show Facebook Share Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the Facebook share button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_twitter_render() {
    $options = get_option('my_events_calendar_options');
    $show_twitter = isset($options['show_twitter']) ? $options['show_twitter'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_twitter]' value='yes' <?php checked($show_twitter, 'yes'); ?> />
        <?php _e('Show Twitter Share Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the Twitter share button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_email_render() {
    $options = get_option('my_events_calendar_options');
    $show_email = isset($options['show_email']) ? $options['show_email'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_email]' value='yes' <?php checked($show_email, 'yes'); ?> />
        <?php _e('Show Email Share Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the Email share button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_linkedin_render() {
    $options = get_option('my_events_calendar_options');
    $show_linkedin = isset($options['show_linkedin']) ? $options['show_linkedin'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_linkedin]' value='yes' <?php checked($show_linkedin, 'yes'); ?> />
        <?php _e('Show LinkedIn Share Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the LinkedIn share button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_whatsapp_render() {
    $options = get_option('my_events_calendar_options');
    $show_whatsapp = isset($options['show_whatsapp']) ? $options['show_whatsapp'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_whatsapp]' value='yes' <?php checked($show_whatsapp, 'yes'); ?> />
        <?php _e('Show WhatsApp Share Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the WhatsApp share button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_sms_render() {
    $options = get_option('my_events_calendar_options');
    $show_sms = isset($options['show_sms']) ? $options['show_sms'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_sms]' value='yes' <?php checked($show_sms, 'yes'); ?> />
        <?php _e('Show SMS Share Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the SMS share button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_copy_link_render() {
    $options = get_option('my_events_calendar_options');
    $show_copy_link = isset($options['show_copy_link']) ? $options['show_copy_link'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_copy_link]' value='yes' <?php checked($show_copy_link, 'yes'); ?> />
        <?php _e('Show Copy Link Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the Copy Link button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_add_to_calendar_render() {
    $options = get_option('my_events_calendar_options');
    $show_add_to_calendar = isset($options['show_add_to_calendar']) ? $options['show_add_to_calendar'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_add_to_calendar]' value='yes' <?php checked($show_add_to_calendar, 'yes'); ?> />
        <?php _e('Show Add to Calendar Buttons', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the Add to Calendar buttons.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_add_to_google_calendar_render() {
    $options = get_option('my_events_calendar_options');
    $show_add_to_google_calendar = isset($options['show_add_to_google_calendar']) ? $options['show_add_to_google_calendar'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_add_to_google_calendar]' value='yes' <?php checked($show_add_to_google_calendar, 'yes'); ?> />
        <?php _e('Show Add to Google Calendar Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the Add to Google Calendar button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_show_add_to_apple_calendar_render() {
    $options = get_option('my_events_calendar_options');
    $show_add_to_apple_calendar = isset($options['show_add_to_apple_calendar']) ? $options['show_add_to_apple_calendar'] : 'yes';
    ?>
    <label>
        <input type='checkbox' name='my_events_calendar_options[show_add_to_apple_calendar]' value='yes' <?php checked($show_add_to_apple_calendar, 'yes'); ?> />
        <?php _e('Show Add to Apple Calendar Button', 'my-events-calendar'); ?>
    </label>
    <p class="description"><?php _e('Check this box to show the Add to Apple Calendar button.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_options_validate($input) {
    $sanitized_input = array();

    if (isset($_POST['my_events_calendar_settings_nonce']) && wp_verify_nonce($_POST['my_events_calendar_settings_nonce'], 'my_events_calendar_save_settings')) {
        // Validate and sanitize URL slugs
        $sanitized_input['event_slug'] = sanitize_title($input['event_slug']);
        $sanitized_input['location_slug'] = sanitize_title($input['location_slug']);

        // If slugs have changed, schedule a rewrite flush
        $old_options = get_option('my_events_calendar_options');
        if ($sanitized_input['event_slug'] !== ($old_options['event_slug'] ?? 'events') ||
            $sanitized_input['location_slug'] !== ($old_options['location_slug'] ?? 'event-location')) {
            update_option('my_events_calendar_flush_rewrite_rules', true);
        }

        // Validate and sanitize each option
        $sanitized_input['default_view'] = in_array($input['default_view'], ['dayGridMonth', 'timeGridWeek', 'timeGridDay']) ? $input['default_view'] : 'dayGridMonth';
        $sanitized_input['date_format'] = in_array($input['date_format'], ['m/d/Y', 'Y-m-d', 'd/m/Y', 'F j, Y', 'jS F Y']) ? $input['date_format'] : 'Y-m-d';
        $sanitized_input['time_format'] = in_array($input['time_format'], ['g:i A', 'H:i', 'g:ia']) ? $input['time_format'] : 'H:i';        
        $sanitized_input['show_share_buttons'] = isset($input['show_share_buttons']) ? 'yes' : 'no';
        $sanitized_input['show_facebook'] = isset($input['show_facebook']) ? 'yes' : 'no';
        $sanitized_input['show_twitter'] = isset($input['show_twitter']) ? 'yes' : 'no';
        $sanitized_input['show_email'] = isset($input['show_email']) ? 'yes' : 'no';
        $sanitized_input['show_linkedin'] = isset($input['show_linkedin']) ? 'yes' : 'no';
        $sanitized_input['show_whatsapp'] = isset($input['show_whatsapp']) ? 'yes' : 'no';
        $sanitized_input['show_sms'] = isset($input['show_sms']) ? 'yes' : 'no';
        $sanitized_input['show_copy_link'] = isset($input['show_copy_link']) ? 'yes' : 'no';
        $sanitized_input['show_add_to_calendar'] = isset($input['show_add_to_calendar']) ? 'yes' : 'no';
        $sanitized_input['show_add_to_google_calendar'] = isset($input['show_add_to_google_calendar']) ? 'yes' : 'no';
        $sanitized_input['show_add_to_apple_calendar'] = isset($input['show_add_to_apple_calendar']) ? 'yes' : 'no';
        $sanitized_input['google_maps_api_key'] = sanitize_text_field($input['google_maps_api_key']);

        // Color fields
        $sanitized_input['accent_background_color'] = sanitize_text_field($input['accent_background_color']);
        $sanitized_input['accent_background_color_hover'] = sanitize_text_field($input['accent_background_color_hover']);
        $sanitized_input['accent_text_color'] = sanitize_text_field($input['accent_text_color']);
        $sanitized_input['accent_text_color_hover'] = sanitize_text_field($input['accent_text_color_hover']);

        // Add validation for show_categories
        $sanitized_input['show_categories'] = isset($input['show_categories']) ? 'yes' : 'no';

        // Add validation for show_map
        $sanitized_input['show_map'] = isset($input['show_map']) ? 'yes' : 'no';
    }

    return $sanitized_input;
}

function my_events_calendar_date_format_render() {
    $options = get_option('my_events_calendar_options');
    $date_format = isset($options['date_format']) ? $options['date_format'] : 'Y-m-d';
    ?>
    <label for='my_events_calendar_options[date_format]'><?php _e('Date Format', 'my-events-calendar'); ?></label>
    <select name='my_events_calendar_options[date_format]' id='my_events_calendar_options[date_format]'>
        <option value='m/d/Y' <?php selected($date_format, 'm/d/Y'); ?>><?php _e('MM/DD/YYYY', 'my-events-calendar'); ?></option>
        <option value='Y-m-d' <?php selected($date_format, 'Y-m-d'); ?>><?php _e('YYYY-MM-DD', 'my-events-calendar'); ?></option>
        <option value='d/m/Y' <?php selected($date_format, 'd/m/Y'); ?>><?php _e('DD/MM/YYYY', 'my-events-calendar'); ?></option>
        <option value='F j, Y' <?php selected($date_format, 'F j, Y'); ?>><?php _e('Month Day, Year', 'my-events-calendar'); ?></option>
        <option value='jS F Y' <?php selected($date_format, 'jS F Y'); ?>><?php _e('Day Month Year', 'my-events-calendar'); ?></option>
    </select>
    <p class="description"><?php _e('Select the format for displaying event dates.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_time_format_render() {
    $options = get_option('my_events_calendar_options');
    $time_format = isset($options['time_format']) ? $options['time_format'] : 'H:i';
    ?>
    <label for='my_events_calendar_options[time_format]'><?php _e('Time Format', 'my-events-calendar'); ?></label>
    <select name='my_events_calendar_options[time_format]' id='my_events_calendar_options[time_format]'>
        <option value='g:i A' <?php selected($time_format, 'g:i A'); ?>><?php _e('12-hour (HH:MM AM/PM)', 'my-events-calendar'); ?></option>
        <option value='H:i' <?php selected($time_format, 'H:i'); ?>><?php _e('24-hour (HH:MM)', 'my-events-calendar'); ?></option>
        <option value='g:ia' <?php selected($time_format, 'g:ia'); ?>><?php _e('12-hour (HH:MM am/pm)', 'my-events-calendar'); ?></option>
    </select>
    <p class="description"><?php _e('Select the format for displaying event times.', 'my-events-calendar'); ?></p>
    <?php
}

function my_events_calendar_accent_colors_render() {
    $options = get_option('my_events_calendar_options');

    // Set default values if options are not set
    $accent_background_color = isset($options['accent_background_color']) ? $options['accent_background_color'] : '#0067d4';
    $accent_background_color_hover = isset($options['accent_background_color_hover']) ? $options['accent_background_color_hover'] : '#0f7bee';
    $accent_text_color = isset($options['accent_text_color']) ? $options['accent_text_color'] : '#ffffff';
    $accent_text_color_hover = isset($options['accent_text_color_hover']) ? $options['accent_text_color_hover'] : '#ffffff';

    ?>
    <h3><?php _e('Accent Colors', 'my-events-calendar'); ?></h3>
    <p class="description"><?php _e('These colors will be used for the accent colors of the calendar.', 'my-events-calendar'); ?></p>

    <div class="mec-color-setting">
        <label for="accent_background_color"><?php _e('Background Color', 'my-events-calendar'); ?></label>
        <input type="text" 
               id="accent_background_color"
               name="my_events_calendar_options[accent_background_color]" 
               value="<?php echo esc_attr($accent_background_color); ?>" 
               class="mec-color-field" />
        <p class="description"><?php _e('This color is used for the background color of buttons and for the color of the social share icons.', 'my-events-calendar'); ?></p>
    </div>

    <div class="mec-color-setting">
        <label for="accent_background_color_hover"><?php _e('Background Color Hover', 'my-events-calendar'); ?></label>
        <input type="text" 
               id="accent_background_color_hover"
               name="my_events_calendar_options[accent_background_color_hover]" 
               value="<?php echo esc_attr($accent_background_color_hover); ?>" 
               class="mec-color-field" />
        <p class="description"><?php _e('This color is used for the hover background color of buttons.', 'my-events-calendar'); ?></p>
    </div>

    <div class="mec-color-setting">
        <label for="accent_text_color"><?php _e('Text Color', 'my-events-calendar'); ?></label>
        <input type="text" 
               id="accent_text_color"
               name="my_events_calendar_options[accent_text_color]" 
               value="<?php echo esc_attr($accent_text_color); ?>" 
               class="mec-color-field" />
        <p class="description"><?php _e('This color is used for the text color on buttons.', 'my-events-calendar'); ?></p>
    </div>

    <div class="mec-color-setting">
        <label for="accent_text_color_hover"><?php _e('Text Color Hover', 'my-events-calendar'); ?></label>
        <input type="text" 
               id="accent_text_color_hover"
               name="my_events_calendar_options[accent_text_color_hover]" 
               value="<?php echo esc_attr($accent_text_color_hover); ?>" 
               class="mec-color-field" />
        <p class="description"><?php _e('This color is used for the hover text color on buttons.', 'my-events-calendar'); ?></p>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.mec-color-field').wpColorPicker();
    });
    </script>
    <?php
}

function my_events_calendar_event_admin_notices() {
    if ($errors = get_transient('my_events_calendar_errors')) {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        delete_transient('my_events_calendar_errors');
    }
    if ($success = get_transient('my_events_calendar_success')) {
        echo '<div class="notice notice-success"><p>' . esc_html($success) . '</p></div>';
        delete_transient('my_events_calendar_success');
    }
    if ($recurrence_notice = get_settings_errors('my_events_calendar_options')) {
        foreach ($recurrence_notice as $notice) {
            echo '<div class="notice notice-success"><p>' . esc_html($notice['message']) . '</p></div>';
        }
    }
}
add_action('admin_notices', 'my_events_calendar_event_admin_notices');

function my_events_calendar_add_slug_settings($settings) {
    $settings['slugs'] = array(
        'title' => __('URL Slugs', 'my-events-calendar'),
        'fields' => array(
            'event_slug' => array(
                'label' => __('Events Slug', 'my-events-calendar'),
                'type' => 'text',
                'default' => 'events',
                'description' => __('The URL slug for events (default: events)', 'my-events-calendar'),
            ),
            'location_slug' => array(
                'label' => __('Location Slug', 'my-events-calendar'),
                'type' => 'text',
                'default' => 'event-location',
                'description' => __('The URL slug for event locations (default: event-location)', 'my-events-calendar'),
            ),
        )
    );
    return $settings;
}
add_filter('my_events_calendar_settings', 'my_events_calendar_add_slug_settings');

// Add these new functions for the slug settings
function my_events_calendar_event_slug_render() {
    $options = get_option('my_events_calendar_options');
    $event_slug = isset($options['event_slug']) ? $options['event_slug'] : 'events';
    ?>
    <div class="mec-slug-setting">
        <label for="my_events_calendar_options[event_slug]"><?php _e('Events Slug', 'my-events-calendar'); ?></label>
        <input type="text" id="my_events_calendar_options[event_slug]" 
               name="my_events_calendar_options[event_slug]" 
               value="<?php echo esc_attr($event_slug); ?>"
               class="regular-text">
        <p class="description">
            <?php _e('The base slug for event pages. Default: events', 'my-events-calendar'); ?>
        </p>
        <p class="description">
            <?php printf(__('Your events will be accessible at: %s', 'my-events-calendar'), 
                        '<code>' . esc_html(home_url('/' . $event_slug . '/')) . '</code>'); ?>
        </p>
    </div>
    <?php
}

function my_events_calendar_location_slug_render() {
    $options = get_option('my_events_calendar_options');
    $location_slug = isset($options['location_slug']) ? $options['location_slug'] : 'event-location';
    ?>
    <div class="mec-slug-setting">
        <label for="my_events_calendar_options[location_slug]"><?php _e('Location Slug', 'my-events-calendar'); ?></label>
        <input type="text" id="my_events_calendar_options[location_slug]" 
               name="my_events_calendar_options[location_slug]" 
               value="<?php echo esc_attr($location_slug); ?>"
               class="regular-text">
        <p class="description">
            <?php _e('The base slug for location pages. Default: event-location', 'my-events-calendar'); ?>
        </p>
        <p class="description">
            <?php printf(__('Your locations will be accessible at: %s', 'my-events-calendar'), 
                        '<code>' . esc_html(home_url('/' . $location_slug . '/')) . '</code>'); ?>
        </p>
    </div>
    <?php
}

// Add this after the existing my_events_calendar_event_admin_notices function
function my_events_calendar_permalink_notice() {
    if (get_option('my_events_calendar_flush_rewrite_rules', false)) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php _e('You have changed the URL slugs for your events or locations. Remember to ', 'my-events-calendar'); ?>
                <a href="<?php echo admin_url('options-permalink.php'); ?>">
                    <?php _e('refresh your permalinks', 'my-events-calendar'); ?>
                </a>
                <?php _e(' for these changes to take effect.', 'my-events-calendar'); ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'my_events_calendar_permalink_notice');

// Add this new function for the category display setting
function my_events_calendar_show_categories_render() {
    $options = get_option('my_events_calendar_options');
    $show_categories = isset($options['show_categories']) ? $options['show_categories'] : 'yes';
    ?>
    <div class="mec-display-setting">
        <label>
            <input type="checkbox" 
                   name="my_events_calendar_options[show_categories]" 
                   value="yes" 
                   <?php checked($show_categories, 'yes'); ?>>
            <?php _e('Show event categories on event detail pages', 'my-events-calendar'); ?>
        </label>
        <p class="description">
            <?php _e('When enabled, event categories will be displayed on the event detail pages.', 'my-events-calendar'); ?>
        </p>
    </div>
    <?php
}

// Add this new function for the map display setting
function my_events_calendar_show_map_render() {
    $options = get_option('my_events_calendar_options');
    $show_map = isset($options['show_map']) ? $options['show_map'] : 'yes';
    ?>
    <div class="mec-display-setting">
        <label>
            <input type="checkbox" 
                   name="my_events_calendar_options[show_map]" 
                   value="yes" 
                   <?php checked($show_map, 'yes'); ?>>
            <?php _e('Show Google Maps on event detail pages', 'my-events-calendar'); ?>
        </label>
        <p class="description">
            <?php _e('When enabled, a Google Map will be displayed for physical event locations.', 'my-events-calendar'); ?>
        </p>
    </div>
    <?php
}
