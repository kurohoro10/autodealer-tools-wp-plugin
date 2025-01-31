<?php
// Create custom page for number plates
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
            error_log('Page created successfully!');
        }
    }
}

add_action('init', 'create_custom_page_for_plates');

// Register AJAX function for fetching number plates
function cpp_get_number_plates() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You need to be logged in to view number plates lists.']);
        return;
    }

    $current_user_id = get_current_user_id();
    $args = array(
        'post_type' => 'number_plates',
        'posts_per_page' => -1,
        'author' => $current_user_id,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    $query = new WP_Query($args);

    $plates = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $plates[] = array(
                'id'        => get_the_ID(),
                'thumbnail' => get_the_post_thumbnail_url(get_the_ID(), 'full') ?: '',
                'permalink' => get_the_permalink(),
                'title'     => get_the_title(),
                'views'     => cpp_get_total_visits_for_user()[get_the_ID()],
                'price'     => get_post_meta(get_the_ID(), 'cpp_price', true),
                'nonce'     => wp_create_nonce('delete_number_plate_nonce')
            );
        }
        wp_reset_postdata();
    }
    wp_send_json_success($plates);
}

add_action('wp_ajax_get_number_plates', 'cpp_get_number_plates');

function cpp_delete_number_plate() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You need to be logged in to view number plates lists.']);
        return;
    }

    $nonce  = isset($_GET['delete_nonce']) ? sanitize_text_field($_GET['delete_nonce']) : '';
    $plate_id = isset($_GET['plate_id']) ? intval($_GET['plate_id']) : 0;

    if (!$nonce || !$plate_id) {
        wp_send_json_error(['message' => 'Invalid request parameters.']);
        return;
    }

    if (!wp_verify_nonce($nonce, 'delete_number_plate_nonce')) {
        wp_send_json_error(['message' => 'Invalid request.']);
        return;
    }

    $current_user_id = get_current_user_id();

    if ($plate_id > 0) {
        if (get_post_field('post_author', $plate_id) != $current_user_id) {
            wp_send_json_error(['message' => 'You do not have permission to delete this number plate.']);
            return;
        }

        // Get the featured image (Post thumbnail) ID
        $thumbnail_id = get_post_thumbnail_id($plate_id);

        if ($thumbnail_id) {
            wp_delete_attachment($thumbnail_id, true);
        }

        if (wp_delete_post($plate_id, true)) {
            wp_send_json_success(['message' => 'Number plate deleted successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete number plate. Please try again later.']);
        }
    } else {
        wp_send_json_error(['message' => 'Invalid number plate.']);
    }
}

add_action('wp_ajax_delete_number_plate', 'cpp_delete_number_plate');

// Create custom post type for number plates
function cpp_register_number_plates_cpt() {
    $labels = array(
        'name'               => 'Number Plates',
        'singular'           => 'Number Plate',
        'menu_name'          => 'Number Plates',
        'name_admin_bar'     => 'Number Plate',
        'add_new'            => 'Add Plate',
        'add_new_item'       => 'Add New Number Plate',
        'new_item'           => 'New Number Plate',
        'edit_item'          => 'Edit Number Plate',
        'view_item'          => 'View Number Plate',
        'all_items'          => 'All Number Plates',
        'search_items'       => 'Search Number Plates',
        'not_found'          => 'No Plates found.',
        'not_found_in_trash' => 'No Plates in trash.'
    );

    $args = array(
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array('slug' => 'plates-lists'),
        'supports'     => array('title', 'editor', 'thumbnail'),
        'taxonomies'   => array('category', 'post_tag'),
        'show_in_rest' =>true
    );

    register_post_type('number_plates', $args);
}

add_action('init', 'cpp_register_number_plates_cpt');

// Register custom fields for number plates
function cpp_register_number_plates_meta() {
    register_post_meta('number_plates', 'cpp_price', array(
        'type'          => 'string',
        'description'   => 'Price of the Number Plate',
        'single'        => true,
        'show_in_rest'  => true
    ));

    register_post_meta('number_plates', 'cpp_location', array(
        'type'          => 'string',
        'description'   => 'Location of the Number Plate',
        'single'        => true,
        'show_in_rest'  => true
    ));
}

add_action('init', 'cpp_register_number_plates_meta');

// Add meta box for price and location in the admin panel
function cpp_add_meta_boxes() {
    add_meta_box(
        'cpp_price_meta_box',
        'Price',
        'cpp_price_meta_box_callback',
        'number_plates',
        'normal'
    );

    add_meta_box(
        'cpp_location_meta_box',
        'Location',
        'cpp_location_meta_box_callback',
        'number_plates',
        'normal'
    );
}

add_action('add_meta_boxes', 'cpp_add_meta_boxes');

// meta box callback for number plates price
function cpp_price_meta_box_callback($post) {
    $value = get_post_meta($post->ID, 'cpp_price', true);
    wp_nonce_field('cpp_np_save_meta', 'cpp_np_meta_nonce');
    echo '<label for="cpp_price">Enter Price:</label>';
    echo '<input type="text" id="cpp_price" name="cpp_price" value="' . esc_html($value) . '" style="width: 100%;" />';
}

// meta box callback for number plates location
function cpp_location_meta_box_callback($post) {
    $value = get_post_meta($post->ID, 'cpp_location', true);
    wp_nonce_field('cpp_np_save_meta', 'cpp_np_meta_nonce');
    echo '<label for="cpp_location">Enter Location</label>';
    echo '<input type="text" id="cpp_location" name="cpp_location" value="' . esc_html($value) . '" style="width: 100%;" />';
}

// Saved meta box data for number plates
function cpp_save_meta_box_data($post_id) {
    if (!isset($_POST['cpp_np_meta_nonce']) || !wp_verify_nonce($_POST['cpp_np_meta_nonce'], 'cpp_np_save_meta')) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
 
    if (isset($_POST['cpp_location'])) {
        update_post_meta($post_id, 'cpp_location', sanitize_text_field($_POST['cpp_location']));
    }

    if (isset($_POST['cpp_price'])) {    
        $price = sanitize_text_field($_POST['cpp_price']);
        if (empty($price) || !is_numeric($price)) {
            add_action('admin_notices', 'cpp_show_price_error_notice');
            return;
        }
        update_post_meta($post_id, 'cpp_price', $price);
    }
}

add_action('save_post', 'cpp_save_meta_box_data');