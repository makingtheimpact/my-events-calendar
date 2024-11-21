<?php

function my_events_calendar_register_taxonomy() {
    $labels = array(
        'name' => 'Event Categories',
        'singular_name' => 'Event Category',        
        'menu_name' => 'Event Categories',
        'name_admin_bar' => 'Event Category',
        'add_new' => 'Add New Category',
        'add_new_item' => 'Add New Event Category',
        'new_item_name' => 'New Event Category Name',
        'new_item' => 'New Event Category',
        'edit_item' => 'Edit Event Category',
        'view_item' => 'View Event Category',
        'all_items' => 'All Event Categories',
        'parent_item' => 'Parent Event Category',
        'parent_item_colon' => 'Parent Event Category:',
        'search_items' => 'Search Event Categories',
        'not_found' => 'No event categories found.',
        'not_found_in_trash' => 'No event categories found in Trash.',
    );

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'event-category'),
        'show_admin_column' => true,
        'menu_icon' => 'dashicons-category',
    );

    register_taxonomy('event_category', array('event'), $args);
}

add_action('init', 'my_events_calendar_register_taxonomy');

function my_events_calendar_add_category_fields($term) {
    $term_id = is_object($term) ? $term->term_id : 0;
    $color = get_term_meta($term_id, 'category_color', true);
    $text_color = get_term_meta($term_id, 'category_text_color', true);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_color"><?php _e('Category Color', 'my-events-calendar'); ?></label>
        </th>
        <td>
            <input type="color" name="category_color" id="category_color" value="<?php echo esc_attr($color) ? esc_attr($color) : '#007bff'; ?>">
            <p class="description"><?php _e('Select a color for this category.', 'my-events-calendar'); ?></p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_text_color"><?php _e('Category Text Color', 'my-events-calendar'); ?></label>
        </th>
        <td>
            <input type="color" name="category_text_color" id="category_text_color" value="<?php echo esc_attr($text_color) ? esc_attr($text_color) : '#ffffff'; ?>">
            <p class="description"><?php _e('Select a text color for this category.', 'my-events-calendar'); ?></p>
        </td>
    </tr>
    <?php
}

add_action('event_category_edit_form_fields', 'my_events_calendar_add_category_fields');
add_action('event_category_add_form_fields', 'my_events_calendar_add_category_fields');

function my_events_calendar_save_category_fields($term_id) {
    if (isset($_POST['category_color'])) {
        update_term_meta($term_id, 'category_color', sanitize_hex_color($_POST['category_color']));
    }
    if (isset($_POST['category_text_color'])) {
        update_term_meta($term_id, 'category_text_color', sanitize_hex_color($_POST['category_text_color']));
    }
}

add_action('edited_event_category', 'my_events_calendar_save_category_fields');
add_action('create_event_category', 'my_events_calendar_save_category_fields');