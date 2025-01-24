<?php
/**
 * Plugin Name: Custom Pricing Plugin
 * Description: Adds custom fields for hourly, daily, and weekly prices to the "listing" post type and provides shortcodes for display and frontend input.
 * Version: 2.0.0
 */

//  Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add meta box to the listing post type only
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

// Meta box callback function to render the fields
function cpp_render_pricing_meta_box($post) {
    $hourly_price = get_post_meta($post->ID, '_cpp_hourly_price', true);
    $daily_price = get_post_meta($post->ID, '_cpp_daily_price', true);
    $weekly_price = get_post_meta($post->ID, '_cpp_weekly_price', true);

    ?>
        <p>
            <label for="cpp_hourly_price">Hourly Price:</label>
            <input type="number" step="0.01" name="cpp_hourly_price" id="cpp_hourly_price" value="<?= esc_attr($hourly_price); ?>">
        </p>

        <p>
            <label for="cpp_daily_price">Daily Price:</label>
            <input type="number" step="0.01" name="cpp_daily_price" id="cpp_daily_price" value="<?= esc_attr($daily_price); ?>">
        </p>

        <p>
            <label for="cpp_weekly_price">Weekly Price:</label>
            <input type="number" step="0.01" name="cpp_weekly_price" id="cpp_weekly_price" value="<?= esc_attr($weekly_price); ?>">
        </p>
    <?php
}

// Save custom fields
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


// shortcode for frontend input form
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
        <!-- <form action="" method="post"> -->
            <?php //wp_nonce_field('cpp_update_pricing', 'cpp_update_pricing_nonce'); ?>

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

            <!-- <div><button type="submit" name="submit-cmb-listing" value="Save &amp; Preview" class="btn btn-theme">Save &amp; Preview</button></div>
        </form> -->
    <?php
    return ob_get_clean();
}

add_shortcode('cpp_frontend_input', 'cpp_frontend_input_shortcode');

function create_custom_page_for_plates() {
    $page_title = 'Number Plates';
    $page_content = '[cpp_frontend_number_plates_input]';
    $page_slug = 'number-plates';

    if (!get_page_by_path($page_slug)) {
        $page_id = wp_insert_post([
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $page_slug
        ]);

        if ($page_id) {
            error_log('Page created succcessfully!');
        }
    }
}

add_action('init', 'create_custom_page_for_plates');

function cpp_frontend_number_plates_input_shortcode() {
    global $wpdb;
            
    // Create table name if it doesn't exists
    $table_name = $wpdb->prefix . 'number_plates';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        plate_title VARCHAR(255) NOT NULL,
        plate_description TEXT,
        plate_price DECIMAL(10, 2) NOT NULL,
        plate_location VARCHAR(255),
        date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);

    // If the user is not logged in, return a message and stop any further output
    if (!is_user_logged_in()) {
        return '<p>You need to be logged in to add a number plate.</p>';
    }

    // Initialize feedback
    $form_feedback = '';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpp_number_plates_nonce']) && wp_verify_nonce($_POST['cpp_number_plates_nonce'], 'cpp_number_plates')) {
        $plate_title = isset($_POST['cpp_plate_title']) ? sanitize_text_field($_POST['cpp_plate_title']) : '';
        $plate_description = isset($_POST['cpp_plate_description']) ? sanitize_textarea_field($_POST['cpp_plate_description']) : '';
        $plate_price = isset($_POST['cpp_plate_price']) ? floatval($_POST['cpp_plate_price']) : 0;
        $plate_location = isset($_POST['cpp_plate_location']) ? sanitize_text_field($_POST['cpp_plate_location']) : '';

        if (empty($plate_title) || $plate_price <= 0 || !is_numeric($plate_price)) {
            $form_feedback = '<p style="color: red;">Title and a valid positive price are required!</p>';
        } 
        // else {
            // Insert the data into the table
            $data = array(
                'user_id' => get_current_user_id(),
                'plate_title' => $plate_title,
                'plate_description' => $plate_description,
                'plate_price' => $plate_price,
                'plate_location' => $plate_location,
                'date_created' => current_time('mysql'),
                'date_updated' => current_time('mysql')
            );

            $format = array('%d', '%s', '%s', '%f', '%s', '%s', '%s');
            $inserted = $wpdb->insert($table_name, $data, $format);

            // Provide feedback based on whether the insertion was successful
            if ($inserted) {
                $form_feedback = '<p style="color: green;">Number plate added successfully!</p>';
            } else {
                $form_feedback = '<p style="color: red;">Failed to add number plate. Please try again later.</p>';
            }
        // }
    }

    // Only output the form for logged-in users
    ob_start();

    echo $form_feedback;
    ?>

    <form action="" method="post" id="cpp_number_plates_form" class="cmb-form">

        <?php wp_nonce_field('cpp_number_plates', 'cpp_number_plates_nonce'); ?>

        <div class="cmb2-wrap form-table">
            <div class="cmb2-metabox cmb-field-list">
                <div class="before-group-row before-group-row-0 active columns-1">
                    <div class="cmb-row cmb-type-title cmb2-id-custom-heading-1" data-fieldtype="title">
                        <div class="cmb-td">
                            <h5 class="cmb2-metabox-title">Number Plates</h5>
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-text cmb2-id--listing-title table-layout">
                        <div class="cmb-th">
                            <label for="cpp_plate_title">Title: <span class="required">(required)</span></label>
                        </div>
                        <div class="cmb-td">
                            <input type="text" name="cpp_plate_title" id="cpp_plate_title" value="" required>
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-text cmb2-id--listing-title table-layout">
                        <div class="cmb-th">
                            <label for="cpp_plate_description">Description:</label>
                        </div>
                        <div class="cmb-td">
                            <textarea name="cpp_plate_description" id="cpp_plate_description"></textarea>
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-text cmb2-id--listing-title table-layout">
                        <div class="cmb-th">
                            <label for="cpp_plate_price">Price: <span class="required">(required)</span></label>
                        </div>
                        <div class="cmb-td">
                            <input type="number" min=0 name="cpp_plate_price" id="cpp_plate_price" value="" required>
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-text cmb2-id--listing-title table-layout">
                        <div class="cmb-th">
                            <label for="cpp_plate_location">Location:</label>
                        </div>
                        <div class="cmb-td">
                            <input type="text" name="cpp_plate_location" id="cpp_plate_location" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="submit-button-wrapper">
            <button type="submit" name="submit-cmb-listing" value="submit" class="btn btn-theme">Submit</button>
        </div>
    </form>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cpp_frontend_number_plates_input', 'cpp_frontend_number_plates_input_shortcode');