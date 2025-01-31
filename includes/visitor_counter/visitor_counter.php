<?php
function cpp_create_visitor_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "visitor_counter";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        page_id BIGINT(20) NOT NULL,
        count BIGINT(20) NOT NULL DEFAULT 1,
        INDEX idx_page_id (page_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

add_action('init', 'cpp_create_visitor_table');

function cpp_track_visitor() {
    if (is_singular('number_plates')) {
        global $wpdb;
        $table_name = $wpdb->prefix . "visitor_counter";
        $post_id = get_queried_object_id();

        // Check if the entry exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT count FROM $table_name WHERE page_id = %d", $post_id));

        if ($exists !== null) {
            // If exists, increment count
            $wpdb->query($wpdb->prepare(
                "UPDATE $table_name SET count = count + 1, last_visited = NOW() WHERE page_id = %d",
                $post_id
            ));
        } else {
            // If not exists, insert new record
            $wpdb->insert(
                $table_name,
                ['page_id' => $post_id, 'count' => 1, 'last_visited' => current_time('mysql')],
                ['%d', '%d', '%s']
            );
        }
    }
}
add_action('wp', 'cpp_track_visitor');

function cpp_get_total_visits_for_user() {
    if (!is_user_logged_in()) {
        return '<p>You need to be logged in to add a number plate.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'visitor_counter';
    $user_id = get_current_user_id();

    // Get all post IDs for the logged-in user where post type is 'number_plates'
    $posts = get_posts([
        'post_type'      => 'number_plates',
        'author'         => $user_id,
        'fields'         => 'ids',
        'posts_per_page' => -1
    ]);

    if (empty($posts)) {
        return ['visitor_counts' => []];
    }

    $post_counts = [];

    foreach ($posts as $post_id) {
        $visitor_count = $wpdb->get_var(
            $wpdb->prepare("SELECT count FROM $table_name WHERE page_id = %d", $post_id)
        );

        $post_counts[$post_id] = $visitor_count ? intval($visitor_count) : 0;
    }

    return $post_counts;
}

add_action('wp', 'cpp_get_total_visits_for_user');

// add_action('wp_ajax_cpp_get_total_visits_for_user', 'cpp_get_total_visits_for_user');
// add_action('wp_ajax_nopriv_cpp_get_total_visits_for_user', 'cpp_get_total_visits_for_user');