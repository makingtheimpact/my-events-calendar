<?php

function my_events_calendar_check_for_updates($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $response = wp_remote_get('https://api.github.com/repos/makingtheimpact/my-events-calendar/releases/latest', array(
        'headers' => array(
            'User-Agent' => 'My Events Calendar Plugin'
        )
    ));

    if (is_wp_error($response)) {
        error_log('GitHub API error: ' . $response->get_error_message());
        return $transient;
    }

    if (wp_remote_retrieve_response_code($response) === 200) {
        $release = json_decode(wp_remote_retrieve_body($response), true);
        $latest_version = $release['tag_name'];
        $current_version = get_plugin_data(__FILE__)['Version'];

        if (version_compare($latest_version, $current_version, '>')) {
            $plugin_slug = plugin_basename(__FILE__);

            $transient->response[$plugin_slug] = (object) array(
                'slug' => $plugin_slug,
                'new_version' => $latest_version,
                'url' => $release['html_url'],
                'package' => $release['zipball_url'],
            );
        }
    } else {
        error_log('GitHub API response code: ' . wp_remote_retrieve_response_code($response));
    }

    return $transient;
}

add_filter('site_transient_update_plugins', 'my_events_calendar_check_for_updates');

// Function to handle the update process
function my_events_calendar_update_plugin($transient) {
    if (isset($transient->response[plugin_basename(__FILE__)])) {
        $update = $transient->response[plugin_basename(__FILE__)];
        $result = wp_remote_get($update->package);

        if (!is_wp_error($result) && wp_remote_retrieve_response_code($result) === 200) {
            // Unzip and install the plugin
            $zip = $result['body'];
            $temp_file = tempnam(sys_get_temp_dir(), 'my_events_calendar');
            file_put_contents($temp_file, $zip);

            // Use the WordPress function to update the plugin
            $upgrader = new Plugin_Upgrader();
            $upgrader->install($temp_file);
            unlink($temp_file); // Clean up the temp file
        }
    }
}

add_action('upgrader_process_complete', 'my_events_calendar_update_plugin', 10, 2);