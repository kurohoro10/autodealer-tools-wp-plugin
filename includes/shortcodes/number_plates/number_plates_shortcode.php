<?php
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
    
    if (file_exists(CPP_PLUGIN_TEMPLATES_PATH . 'number_plates/number_plates_frontend_form.php')) {
        include_once CPP_PLUGIN_TEMPLATES_PATH . 'number_plates/number_plates_frontend_form.php';
    }

    return ob_get_clean();
}

add_shortcode('cpp_frontend_number_plates_input', 'cpp_frontend_number_plates_input_shortcode');

// Display number plates list in listing page.
function cpp_display_number_plates_list_shortcode() {
    ob_start();
  
    if (file_exists(CPP_PLUGIN_TEMPLATES_PATH . 'number_plates/number_plates_display_list.php')) {
        include_once CPP_PLUGIN_TEMPLATES_PATH . 'number_plates/number_plates_display_list.php';
    }

    return ob_get_clean();
}

add_shortcode('cpp_number_plates_list', 'cpp_display_number_plates_list_shortcode');