<?php
/**
 * Plugin Name: Custom Pricing Plugin
 * Description: Adds custom fields for hourly, daily, and weekly prices to the "listing" post type and provides shortcodes for display and frontend input.
 * Version: 2.1.1
 */

//  Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function cpp_enqueue_scripts() {
    wp_enqueue_script('cpp_custom_script', plugins_url('/script.js', __FILE__), array('jquery'), '1.0.0', true );

    wp_enqueue_style('cpp_custom_style', plugins_url('/style.css', __FILE__));

    wp_localize_script('cpp_custom_script', 'cpp_script_data', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cpp_nonce')
    ));
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
                    'post_content' => $plate_description
                ));

                if ($updated_post_id) {
                    update_post_meta($post_id, 'cpp_price', $plate_price);
                    update_post_meta($post_id, 'cpp_location', $plate_location);
                    $form_feedback = '<p style="color: green;">Number plate updated successfully!</p>';
                } else {
                    $form_feedback = '<p style="color: red;">Failed to update number plate. Please try again later.</p>';
                }
            } else {
                $form_feedback = '<p style="color: red;"> You do not have permission to edit this post.';
            }
        } else {
            // Insert the data into the table
            $new_plate_id = wp_insert_post(array(
                'post_title' => $plate_title,
                'post_content' => $plate_description,
                'post_type' => 'plates_lists',
                'post_status' => 'publish',
                'meta_input' => array(
                    'cpp_price' => $plate_price,
                    'cpp_location' => $plate_location,
                )
            ));

            // Provide feedback based on whether the insertion was successful
            if ($new_plate_id) {
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

    <form action="" method="post" id="cpp_number_plates_form" class="cmb-form">

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

// For displaying the list of number plates.
function cpp_display_number_plates_list_shortcode() {
     // If the user is not logged in, return a message and stop any further output
     if (!is_user_logged_in()) {
        return '<p>You need to be logged in to view number plates lists.</p>';
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

    if (!$query->have_posts()) {
        return '<p>No number plates found.</p>';
    }

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

                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    
                <div class="my-listings-item listing-item">
					<div class="d-flex align-items-center layout-my-listings">
						<div class="listing-thumbnail-wrapper d-none d-md-block">
                            <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('thumbnail');
                                }
                            ?>
							<div class="top-label"></div>
						</div>
						<div class="inner-info flex-grow-1 d-flex align-items-center layout-left">
							<div class="inner-info-left">
								<h3 class="listing-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
								<div class="listing-meta with-icon tagline">
                                <span class="value-suffix"></span>
                            </div>

                            <div class="listing-price d-flex align-items-baseline">
                                <div class="main-price">
                                    <span class="suffix">RM</span> 
                                    <span class="price-text">
                                        <?= get_post_meta(get_the_ID(), 'cpp_price', true); ?>
                                    </span>
                                </div>
                            </div>							
                        </div>

						<div class="listing-info-date-expiry d-none d-md-block">
							<div class="listing-table-info-content-expiry">
									--								
                            </div>
						</div>
						<div class="status-listing-wrapper d-none d-md-block">
							<span class="status-listing preview">
								Preview								
                            </span>
						</div>
						<div class="view-listing-wrapper d-none d-md-block">
							0							
                        </div>
						<div class="warpper-action-listing">
							<a data-toggle="tooltip" href="/number-plates/?plate_id=<?= get_the_ID() ?>&action=continue" class="edit-btn btn-action-icon edit  job-table-action" title="" data-bs-original-title="Continue">
								<i class="ti-arrow-top-right"></i>
							</a>
										
							<a data-toggle="tooltip" class="remove-btn btn-action-icon listing-table-action listing-button-delete" href="javascript:void(0)" data-listing_id="<?= get_the_ID() ?>" data-nonce="9cabffcf30" title="" data-bs-original-title="Remove">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7.06907 7.69027C7.37819 7.65937 7.65382 7.8849 7.68472 8.19405L8.05972 11.944C8.0907 12.2531 7.8651 12.5288 7.55602 12.5597C7.2469 12.5906 6.97124 12.3651 6.94033 12.0559L6.56533 8.30595C6.53442 7.99687 6.75995 7.72117 7.06907 7.69027Z" fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.931 7.69027C11.2401 7.72117 11.4656 7.99687 11.4347 8.30595L11.0597 12.0559C11.0288 12.3651 10.7532 12.5906 10.444 12.5597C10.135 12.5288 9.90943 12.2531 9.94033 11.944L10.3153 8.19405C10.3462 7.8849 10.6219 7.65937 10.931 7.69027Z" fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M7.59285 0.937514H10.129C10.2913 0.937409 10.4327 0.937319 10.5662 0.958642C11.0936 1.04287 11.5501 1.37186 11.7968 1.84563C11.8592 1.96555 11.9039 2.09971 11.9551 2.2537L12.0388 2.50488C12.053 2.5474 12.057 2.55943 12.0605 2.56891C12.1918 2.932 12.5323 3.17744 12.9183 3.18723C12.9284 3.18748 12.9409 3.18753 12.9859 3.18753H15.2359C15.5466 3.18753 15.7984 3.43936 15.7984 3.75003C15.7984 4.06069 15.5466 4.31253 15.2359 4.31253H2.48584C2.17518 4.31253 1.92334 4.06069 1.92334 3.75003C1.92334 3.43936 2.17518 3.18753 2.48584 3.18753H4.73591C4.78094 3.18753 4.79336 3.18748 4.80352 3.18723C5.18951 3.17744 5.53002 2.93202 5.66136 2.56893C5.66481 2.55938 5.66879 2.54761 5.68303 2.50488L5.76673 2.25372C5.81796 2.09973 5.86259 1.96555 5.92503 1.84563C6.17174 1.37186 6.62819 1.04287 7.15566 0.958642C7.28918 0.937319 7.43057 0.937409 7.59285 0.937514ZM6.61695 3.18753C6.65558 3.11176 6.68982 3.03303 6.71927 2.95161C6.72821 2.92688 6.73699 2.90056 6.74825 2.86675L6.82311 2.64219C6.89149 2.43706 6.90723 2.39522 6.92285 2.36523C7.00508 2.2073 7.15724 2.09764 7.33306 2.06956C7.36646 2.06423 7.41111 2.06253 7.62735 2.06253H10.0945C10.3107 2.06253 10.3553 2.06423 10.3888 2.06956C10.5646 2.09764 10.7168 2.2073 10.799 2.36523C10.8146 2.39522 10.8303 2.43705 10.8987 2.64219L10.9735 2.86662L11.0026 2.95162C11.032 3.03304 11.0663 3.11176 11.1049 3.18753H6.61695Z" fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3.83761 5.81377C4.14757 5.7931 4.41561 6.02763 4.43628 6.3376L4.78123 11.5119C4.84862 12.5228 4.89664 13.2262 5.00207 13.7554C5.10433 14.2688 5.24708 14.5405 5.45215 14.7323C5.6572 14.9241 5.93782 15.0485 6.45682 15.1164C6.99187 15.1864 7.6969 15.1875 8.71 15.1875H9.29004C10.3031 15.1875 11.0081 15.1864 11.5432 15.1164C12.0622 15.0485 12.3428 14.9241 12.5479 14.7323C12.7529 14.5405 12.8957 14.2688 12.998 13.7554C13.1034 13.2262 13.1514 12.5228 13.2188 11.5119L13.5638 6.3376C13.5844 6.02763 13.8524 5.7931 14.1624 5.81377C14.4724 5.83443 14.7069 6.10246 14.6863 6.41244L14.3387 11.6262C14.2745 12.5883 14.2228 13.3654 14.1013 13.9752C13.975 14.6092 13.7602 15.1388 13.3165 15.5538C12.8728 15.9689 12.3301 16.148 11.6891 16.2319C11.0726 16.3125 10.2938 16.3125 9.32957 16.3125H8.67047C7.70627 16.3125 6.92743 16.3125 6.3109 16.2319C5.66991 16.148 5.12725 15.9689 4.68356 15.5538C4.23986 15.1388 4.02505 14.6092 3.89875 13.9752C3.77727 13.3654 3.72547 12.5883 3.66136 11.6262L3.31377 6.41244C3.2931 6.10246 3.52763 5.83443 3.83761 5.81377Z" fill="currentColor"></path>
                                </svg>
							</a>
						</div>
						</div>
					</div>
				</div>

                <?php endwhile; ?>

            </div>
    </div>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('cpp_display_number_plates_list', 'cpp_display_number_plates_list_shortcode');

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

// meta box callback for price
function cpp_price_meta_box_callback($post) {
    $value = get_post_meta($post->ID, 'cpp_price', true);
    wp_nonce_field('cpp_np_save_meta', 'cpp_np_meta_nonce');
    echo '<label for="cpp_price">Enter Price:</label>';
    echo '<input type="text" id="cpp_price" name="cpp_price" value="' . esc_html($value) . '" style="width: 100%;" />';
}

// meta box callback for location
function cpp_location_meta_box_callback($post) {
    $value = get_post_meta($post->ID, 'cpp_location', true);
    wp_nonce_field('cpp_np_save_meta', 'cpp_np_meta_nonce');
    echo '<label for="cpp_location">Enter Location</label>';
    echo '<input type="text" id="cpp_location" name="cpp_location" value="' . esc_html($value) . '" style="width: 100%;" />';
}

// Saved meta box data
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