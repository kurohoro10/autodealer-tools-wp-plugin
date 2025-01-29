<?php
    // Template for frontend form
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
                            <?php  
                                if (isset($post->ID)) :
                                    if (!empty(get_the_post_thumbnail_url($post->ID))) :
                            ?>
                            
                                <img id="image_preview"
                                    src="<?= esc_attr(get_the_post_thumbnail_url($post->ID)); ?>" 
                                    alt="<?= esc_attr(get_the_title($post->ID)); ?>"
                                >
                                <a class="wp-cardealer-remove-uploaded-file remove_btn_preview" href="#">[remove]</a>

                            <?php 
                                    endif;
                                endif; 
                            ?>
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