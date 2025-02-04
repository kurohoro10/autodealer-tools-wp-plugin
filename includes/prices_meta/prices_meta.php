<?php
// Add meta box for prices to the listing post type only
function cpp_add_price_meta_boxes() {
    add_meta_box(
        'cpp_pricing_meta_box',
        __('Rental Pricing Information (Leave blank if for sale)', 'cpp'),
        'cpp_render_pricing_meta_box',
        'listing',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cpp_add_price_meta_boxes');

// Meta box callback function to render the fields for prices
function cpp_render_pricing_meta_box($post) {
    $hourly_price = get_post_meta($post->ID, '_cpp_hourly_price', true);
    $daily_price = get_post_meta($post->ID, '_cpp_daily_price', true);
    $weekly_price = get_post_meta($post->ID, '_cpp_weekly_price', true);

    if (file_exists(CPP_PLUGIN_TEMPLATES_PATH . 'prices_meta/prices_meta_backend.php')) {
        include_once CPP_PLUGIN_TEMPLATES_PATH . 'prices_meta/prices_meta_backend.php';
    }
}

// Save custom fields for prices
function cpp_save_pricing_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['cpp_hourly_price']) || !isset($_POST['cpp_daily_price']) || !isset($_POST['cpp_weekly_price'])) return;

    update_post_meta($post_id, '_cpp_hourly_price', sanitize_text_field($_POST['cpp_hourly_price']));
    update_post_meta($post_id, '_cpp_daily_price', sanitize_text_field($_POST['cpp_daily_price']));
    update_post_meta($post_id, '_cpp_weekly_price', sanitize_text_field($_POST['cpp_weekly_price']));
}

add_action('save_post', 'cpp_save_pricing_meta_box');