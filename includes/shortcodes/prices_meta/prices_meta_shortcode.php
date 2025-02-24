<?php
// Shortcode to display prices
function cpp_display_prices_shortcode($atts) {
    $atts = shortcode_atts(['id' => get_the_ID()], $atts);

    if (!$atts['id'] || !get_post($atts['id'])) {
        return '<p>Invalid or missing post ID.</p>';
    }

    $hourly_price = get_post_meta($atts['id'], '_cpp_hourly_price', true) ?: 'N/A';
    $daily_price = get_post_meta($atts['id'], '_cpp_daily_price', true) ?: 'N/A';
    $weekly_price = get_post_meta($atts['id'], '_cpp_weekly_price', true) ?: 'N/A';

    $price_per_hour = $hourly_price !== 'N/A' ? number_format($hourly_price) : $hourly_price;
    $price_per_day = $daily_price !== 'N/A' ? number_format($daily_price) : $daily_price;
    $price_per_week = $weekly_price !== 'N/A' ? number_format($weekly_price) : $weekly_price;

    ob_start();
    
    if (file_exists(CPP_PLUGIN_TEMPLATES_PATH . 'prices_meta/display_prices_meta.php')) {
        include_once CPP_PLUGIN_TEMPLATES_PATH . 'prices_meta/display_prices_meta.php';
    }

    return ob_get_clean();
}

add_shortcode('cpp_display_prices', 'cpp_display_prices_shortcode');

// shortcode for frontend input form for prices
function cpp_frontend_input_shortcode($atts) {
    if (!is_user_logged_in()) return '<p>You need to be logged in to update pricing.</p>';

    $atts = shortcode_atts(['id' => get_the_ID()], $atts);

    if (!$atts['id']) return '<p>No listing ID provided.</p>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpp_update_pricing_nonce']) && wp_verify_nonce($_POST['cpp_update_pricing_nonce'], 'cpp_update_pricing')) {
        update_post_meta($atts['id'], '_cpp_hourly_price', sanitize_text_field($_POST['cpp_hourly_price']));
        update_post_meta($atts['id'], '_cpp_daily_price', sanitize_text_field($_POST['cpp_daily_price']));
        update_post_meta($atts['id'], '_cpp_weekly_price', sanitize_text_field($_POST['cpp_weekly_price']));
    }

    $hourly_price = get_post_meta($atts['id'], '_cpp_hourly_price', true);
    $daily_price = get_post_meta($atts['id'], '_cpp_daily_price', true);
    $weekly_price = get_post_meta($atts['id'], '_cpp_weekly_price', true);

    ob_start();
    
    if (file_exists(CPP_PLUGIN_TEMPLATES_PATH . 'prices_meta/prices_meta_frontend.php')) {
        include_once CPP_PLUGIN_TEMPLATES_PATH . 'prices_meta/prices_meta_frontend.php';
    }

    return ob_get_clean();
}

add_shortcode('cpp_frontend_input', 'cpp_frontend_input_shortcode');