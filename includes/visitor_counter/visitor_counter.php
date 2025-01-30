<?php
function cpp_create_visitor_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "visitor_counter";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page_id BIGINT(20) NOT NULL,
        count BIGINT(20) NOT NULL DEFAULT 1
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('init', 'cpp_create_visitor_table');

function cpp_track_number_plate_visits() {
    if (is_singular('number_plates')) {
        global $wpdb, $post;
        $table_name = $wpdb->prefix . "visitor_counter";
        $post_id = $post->ID;

        // Check if page ID exists
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE page_id = %d", $post_id));

        if ($row) {
            // Update count
            $wpdb->update($table_name, ['count' => $row->count + 1], ['page_id' => $post_id]);
        } else {
            // Insert new page visit
            $wpdb->insert($table_name, ['page_id' => $post_id, 'count' => 1]);
        }
    }
}

add_action('wp_head', 'cpp_track_number_plate_visits');

function cpp_get_number_plate_visits() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You need to logged in to view visitor stats.']);
        return;
    }

    $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

    // if (!$post_id) {
    //     wp_send_json_error(['message' => 'Invalid post ID']);
    //     return;
    // }

    global $wpdb;
    $table_name = $wpdb->prefix . "visitor_counter";
    $count = $wpdb->get_var($wpdb->prepare("SELECT count FROM $table_name WHERE page_id = %d", $post_id));

    wp_send_json_success([
        'post_id' => $post_id,
        'visitor_count' => $count ? $count : 0
    ]);
}

add_action('wp_ajax_cpp_get_number_plate_visits', 'cpp_get_number_plate_visits');
add_action('wp_ajax_nopriv_cpp_get_number_plate_visits', 'cpp_get_number_plate_visits');