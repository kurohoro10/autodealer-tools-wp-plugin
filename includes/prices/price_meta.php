<?php
// Add meta box for prices to the listing post type only
function cpp_add_price_meta_boxes() {
    add_meta_box(
        'cpp_pricing_meta_box',
        __('Pricing Information', 'cpp'),
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

    ?>
    <div>
        <div style="display: flex;gap: 2rem;width: 100%;align-items: center; padding: 1rem 0;">
            <div style="width: 100px;">
                <label for="cpp_hourly_price">Hourly Price:</label>
            </div>
            <div style="flex-grow: 1;">
                <input type="number" step="0.01" name="cpp_hourly_price" id="cpp_hourly_price" value="<?= esc_attr($hourly_price); ?>" style="width: 80%;">
            </div>
        </div>

        <div style="display: flex;gap: 2rem;width: 100%;align-items: center; padding-bottom: 1rem">
            <div style="width: 100px;">
                <label for="cpp_daily_price">Daily Price:</label>
            </div>
            <div style="flex-grow: 1;">
                <input type="number" step="0.01" name="cpp_daily_price" id="cpp_daily_price" value="<?= esc_attr($daily_price); ?>" style="width: 80%;">
            </div>
        </div>

        <div style="display: flex;gap: 2rem;width: 100%;align-items: center; padding-bottom: 1rem">
            <div style="width: 100px;">
                <label for="cpp_weekly_price">Weekly Price:</label>
            </div>
            <div style="flex-grow: 1;">
                <input type="number" step="0.01" name="cpp_weekly_price" id="cpp_weekly_price" value="<?= esc_attr($weekly_price); ?>" style="width: 80%;">
            </div>
        </div>
    </div>
    <?php
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

// Shortcode to display prices
function cpp_display_prices_shortcode($atts) {
    $atts = shortcode_atts(['id' => get_the_ID()], $atts);

    if (!$atts['id'] || !get_post($atts['id'])) {
        return '<p>Invalid or missing post ID.</p>';
    }

    $hourly_price = get_post_meta($atts['id'], '_cpp_hourly_price', true) ?: 'N/A';
    $daily_price = get_post_meta($atts['id'], '_cpp_daily_price', true) ?: 'N/A';
    $weekly_price = get_post_meta($atts['id'], '_cpp_weekly_price', true) ?: 'N/A';

    $price_per_hour = $hourly_price !== 'N/A' ? 'RM ' . $hourly_price : $hourly_price;
    $price_per_day = $daily_price !== 'N/A' ? 'RM ' . $daily_price : $daily_price;
    $price_per_week = $weekly_price !== 'N/A' ? 'RM ' . $weekly_price : $weekly_price;

    ob_start();
    ?>
        <div class="cpp-prices">
            <div><strong>Per Hour:</strong> <?= esc_html($price_per_hour); ?> </div>
            <div><strong>Per Day:</strong> <?= esc_html($price_per_day); ?> </div>
            <div><strong>Per Week:</strong> <?= esc_html($price_per_week); ?> </div>
        </div>
    <?php
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
    ?>

    <div>
        <label for="cpp_hourly_price">Hourly Price:</label>
        <input type="number" step="0.01" name="cpp_hourly_price" id="cpp_hourly_price" value="<?= esc_attr($hourly_price); ?>">
    </div>

    <div>
        <label for="cpp_daily_price">Daily Price:</label>
        <input type="number" step="0.01" name="cpp_daily_price" id="cpp_daily_price" value="<?= esc_attr($daily_price); ?>">
    </div>

    <div>
        <label for="cpp_weekly_price">Weekly Price:</label>
        <input type="number" step="0.01" name="cpp_weekly_price" id="cpp_weekly_price" value="<?= esc_attr($weekly_price); ?>">
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('cpp_frontend_input', 'cpp_frontend_input_shortcode');