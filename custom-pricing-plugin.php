<?php
/**
 * Plugin Name: Custom Pricing Plugin
 * Description: Adds custom fields for hourly, daily, and weekly prices to the "listing" post type and provides shortcodes for display and frontend input.
 * Version: 2.2.5
 */

//  Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function cpp_enqueue_scripts() {
    wp_enqueue_script('cpp_custom_script', plugins_url('/script.js', __FILE__), array('jquery'), '1.0.0', true );

    wp_enqueue_style('cpp_custom_style', plugins_url('/style.css', __FILE__));

    wp_localize_script('cpp_custom_script', 'cpp_script_data', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('cpp_nonce'),
    ]);
}

add_action('wp_enqueue_scripts', 'cpp_enqueue_scripts');

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
            error_log('Page created succcessfully!');
        }
    }
}

add_action('init', 'create_custom_page_for_plates');

// Frontend form for number plates
function cpp_frontend_number_plates_input_shortcode() {
    // If the user is not logged in, return a message and stop any further output
    if (!is_user_logged_in()) {
        return '<p>You need to be logged in to add a number plate.</p>';
    }

    // Initialize feedback
    $plate_featured_img = '';
    $form_feedback = '';
    $post = null;

    // check if the query parameter is for editing post
    if (isset($_GET['plate_id']) && isset($_GET['action']) && $_GET['action'] === 'continue') {
        $post_id = intval($_GET['plate_id']);
        $user_id = get_current_user_id();

        $post = get_post($post_id);

        if ($post) {
            if ($post->post_author == $user_id && current_user_can('edit_post', $post_id)) {
                
            }  else {
                echo 'You are not the author of this post.';
            }
        } else {
            echo 'No post found with the given ID.';
        }
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cpp_number_plates_nonce']) && wp_verify_nonce($_POST['cpp_number_plates_nonce'], 'cpp_number_plates')) {
        $plate_title = isset($_POST['cpp_plate_title']) ? sanitize_text_field($_POST['cpp_plate_title']) : '';
        $plate_description = isset($_POST['cpp_plate_description']) ? sanitize_textarea_field($_POST['cpp_plate_description']) : '';
        $plate_price = isset($_POST['cpp_plate_price']) ? floatval($_POST['cpp_plate_price']) : 0;
        $plate_location = isset($_POST['cpp_plate_location']) ? sanitize_text_field($_POST['cpp_plate_location']) : '';

        if (!empty($_FILES['featured_image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $uploaded_file = wp_handle_upload($_FILES['featured_image'], array('test_form' => false));

            if ($uploaded_file && !isset($uploaded_file['error'])) {
                $filename = $uploaded_file['file'];
                $wp_upload_dir = wp_upload_dir();

                $attachment = array(
                    'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
                    'post_mime_type' => $uploaded_file['type'],
                    'post_title' => sanitize_file_name(basename($filename)),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attachment_id = wp_insert_attachment($attachment, $filename);

                if (!is_wp_error($attachment_id)) {
                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $filename);
                    wp_update_attachment_metadata($attachment_id, $attachment_data);

                    $plate_featured_img = wp_get_attachment_url($attachment_id);
                } else {
                    $form_feedback = '<p style="color: red;">Failed to save featured image.</p>';
                }
            } else {
                $form_feedback = '<p style="color: red;">File upload error: ' . $uploaded_file['error'] . '</p>';
            }
        }

        if (empty($plate_title) || $plate_price <= 0 || !is_numeric($plate_price)) {
            $form_feedback = '<p style="color: red;">Title and a valid positive price are required!</p>';
        }

        if (isset($_POST['cpp_post_id']) && intval($_POST['cpp_post_id']) > 0) {
            $post_id = intval($_POST['cpp_post_id']);
            $post = get_post($post_id);

            if ($post && $post->post_author == get_current_user_id() && current_user_can('edit_post', $post_id)) {
                $updated_post_id = wp_update_post(array(
                    'ID' => $post_id,
                    'post_title' => $plate_title,
                    'post_content' => $plate_description,
                ));

                if ($updated_post_id) {
                    update_post_meta($post_id, 'cpp_price', $plate_price);
                    update_post_meta($post_id, 'cpp_location', $plate_location);

                    if (!empty($plate_featured_img)) {
                        set_post_thumbnail($post_id, $attachment_id);
                    } else if (empty($plate_featured_img)) {
                        delete_post_thumbnail($post_id);
                    }

                    if (isset($_POST['remove_featured_image']) && $_POST['remove_featured_image'] === '1') {
                        delete_post_thumbnail($post_id);
                    }

                    $form_feedback = '<p style="color: green;">Number plate updated successfully!</p>';
                } else {
                    $form_feedback = '<p style="color: red;">Failed to update number plate. Please try again later.</p>';
                }
            } else {
                $form_feedback = '<p style="color: red;"> You do not have permission to edit this post.';
            }
        } else {
            // Insert the data into post
            $new_plate_id = wp_insert_post(array(
                'post_title' => $plate_title,
                'post_content' => $plate_description,
                'post_type' => 'number_plates',
                'post_status' => 'publish',
                'meta_input' => array(
                    'cpp_price' => $plate_price,
                    'cpp_location' => $plate_location,
                )
            ));

            // Provide feedback based on whether the insertion was successful
            if ($new_plate_id) {
                if (!empty($plate_featured_img)) {
                    set_post_thumbnail($new_plate_id, $attachment_id);
                }
                $form_feedback = '<p style="color: green;">Number plate added successfully!</p>';
            } else {
                $form_feedback = '<p style="color: red;">Failed to add number plate. Please try again later.</p>';
            }
        }
    }

    // Only output the form for logged-in users
    ob_start();

    echo $form_feedback;
    ?>

    <form action="" method="post" id="cpp_number_plates_form" class="cmb-form" enctype="multipart/form-data">

        <?php wp_nonce_field('cpp_number_plates', 'cpp_number_plates_nonce'); ?>

        <input type="hidden" name="cpp_post_id" value="<?= isset($post->ID) ? esc_attr($post->ID) : ''; ?>">

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
                            <input type="text" name="cpp_plate_title" id="cpp_plate_title" value="<?= isset($post->post_title) ? esc_attr($post->post_title) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-text cmb2-id--listing-title table-layout">
                        <div class="cmb-th">
                            <label for="cpp_plate_description">Description:</label>
                        </div>
                        <div class="cmb-td">
                            <textarea name="cpp_plate_description" id="cpp_plate_description">
                                <?= isset($post->post_content) ? esc_textarea($post->post_content) : ''; ?>
                            </textarea>
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-text cmb2-id--listing-title table-layout">
                        <div class="cmb-th">
                            <label for="cpp_plate_price">Price: <span class="required">(required)</span></label>
                        </div>
                        <div class="cmb-td">
                            <input type="number" min=0 name="cpp_plate_price" id="cpp_plate_price" value="<?= isset($post) ? esc_attr(get_post_meta($post->ID, 'cpp_price', true)) : ''; ?>" required>
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-text cmb2-id--listing-title table-layout">
                        <div class="cmb-th">
                            <label for="cpp_plate_location">Location:</label>
                        </div>
                        <div class="cmb-td">
                            <input type="text" name="cpp_plate_location" id="cpp_plate_location" value="<?= isset($post) ? esc_attr(get_post_meta($post->ID, 'cpp_location', true)) : ''; ?>">
                        </div>
                    </div>

                    <div class="cmb-row cmb-type-wp-cardealer-file cmb2-id--listing-featured-image" data-fieldtype="wp_cardealer_file">
                        <div class="cmb-th">
                            Featured Image 
                            <span class="required"> (required)</span>
                        </div>
                        
                        <div class="cmb-td d_flex gap_x1">
                        <div class="wp-cardealer-uploaded-file">			
                            <span class="wp-cardealer-uploaded-file-preview cpp_preview">
                                <?php  if (!empty(get_the_post_thumbnail_url($post->ID))) :?>
                                    <img id="image_preview"
                                        src="<?= esc_attr(get_the_post_thumbnail_url($post->ID)); ?>" 
                                        alt="<?= esc_attr(get_the_title($post->ID)); ?>"
                                    >
                                    <a class="wp-cardealer-remove-uploaded-file remove_btn_preview" href="#">[remove]</a>
                                <?php endif; ?>
                            </span>
                        </div>

                            <input type="hidden" name="remove_featured_image" id="remove_featured_image" value="0">
                        
                            <input type="file" id="featured_image" class="hidden wp-cardealer-file-upload" accept="image/gif, image/jpeg, image/png, image/bmp, image/tiff, image/webp, image/avif, image/x-icon, image/heic" name="featured_image" data-file_limit="1" />

                            <div class="label-can-drag">
                                <div class="form-group group-upload">
                                    <div class="upload-file-btn">
                                        <span>Upload File</span>                   
                                    </div>
                                </div>
                            </div>
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

// Display number plates list in listing page.
function cpp_display_number_plates_list_shortcode() {
    ob_start();
    ?>

    <h2 class="title-profile">Number Plates</h2>
    <div class="box-white-dashboard">
        <div class="space-bottom-30">
            <!-- Search and sort table -->
            <div class="d-md-flex align-items-center top-dashboard-search">
                <!-- Search in list -->
                <div class="search-my-listings-form widget-search search-listings-form">
                    <form action="" method="get">
                        <div class="d-flex align-items-center">
                            <button class="search-submit btn btn-search">
                                <i class="flaticon-search"></i>
                            </button>
                            <input placeholder="Search ..." class="form-control" type="text" name="search" value="">
                        </div>
                    </form>
                </div>

                <!-- Sort lists -->
                <div class="sort-my-listings-form sortby-form ms-auto">
                    <div class="orderby-wrapper d-flex align-items-center">
                        <span class="text-sort">Sort by:</span>
                        <form class="my-listings-ordering" method="get">
                            <select name="orderby" class="orderby select2-hidden-accessible" tabindex="-1" aria-hidden="true">
                                <option value="menu_order">Default</option>
                                <option value="newest" selected="selected">Newest</option>
                                <option value="oldest">Oldest</option>
                            </select>
                            <input type="hidden" name="paged" value="1">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inner -->
        <div class="inner">
            <!-- Inner header -->
            <div class="layout-my-listings d-flex align-items-center header-layout">
                <div class="listing-thumbnail-wrapper d-none d-md-block">
                    Image			
                </div>
                <div class="layout-left d-flex align-items-center inner-info flex-grow-1">
                    <div class="inner-info-left">
                        Information				
                    </div>
                    <div class="d-none d-md-block">
                        Expiry				
                    </div>
                    <div class="d-none d-md-block">
                        Status				
                    </div>
                    <div class="d-none d-md-block">
                        View				
                    </div>
                    <div>
                        Action				
                    </div>
                </div>
            </div>

            <!-- Inner item -->
            <div class="number_plates_listing">
                <p class="loading">Loading number plates please wait ...</p>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('cpp_number_plates_list', 'cpp_display_number_plates_list_shortcode');

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

// Separate function for admin notice
function cpp_show_price_error_notice() {
    echo '<div class="notice notice-error"><p>Price must be a numeric value and cannot be empty.</p></div>';
}

add_action('save_post', 'cpp_save_meta_box_data');