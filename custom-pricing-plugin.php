<?php
/**
 * Plugin Name: Custom Pricing Plugin
 * Description: Adds custom fields for hourly, daily, and weekly prices to the "listing" post type and provides shortcodes for display and frontend input.
 * Version: 2.2.7
 */

//  Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

define('CPP_FILE', __FILE__);
define('CPP_PLUGIN_BASE', plugin_basename(CPP_FILE));
define('CPP_PLUGIN_PATH', plugin_dir_path(CPP_FILE));
define('CPP_PLUGIN_ASSETS_PATH', CPP_PLUGIN_PATH . 'assets/');
define('CPP_PLUGIN_TEMPLATES_PATH', CPP_PLUGIN_PATH . 'templates/');
define('CPP_PLUGIN_BASE_PATH', CPP_PLUGIN_PATH . 'base/');
define('CPP_PLUGIN_INCLUDES_PATH', CPP_PLUGIN_PATH . 'includes/');
define('CPP_PLUGIN_URL', plugins_url('/', CPP_FILE));
define('CPP_PLUGIN_ASSETS_URL', CPP_PLUGIN_URL . 'assets/');
define('CPP_PLUGIN_TEMPLATES_URL', CPP_PLUGIN_URL . 'templates/');

function cpp_enqueue_scripts() {
    wp_enqueue_script('cpp_custom_script', plugins_url('/assets/js/script.js', __FILE__), array('jquery'), '1.0.0', true );

    wp_enqueue_style('cpp_custom_style', plugins_url('/assets/css/style.css', __FILE__));

    wp_localize_script('cpp_custom_script', 'cpp_script_data', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('cpp_nonce'),
    ]);
}

add_action('wp_enqueue_scripts', 'cpp_enqueue_scripts');

if (file_exists(CPP_PLUGIN_INCLUDES_PATH . 'prices_meta/prices_meta.php')) {
    require_once CPP_PLUGIN_INCLUDES_PATH . 'prices_meta/prices_meta.php';
}

if (file_exists(CPP_PLUGIN_INCLUDES_PATH . 'number_plates/number_plates.php')) {
    require_once CPP_PLUGIN_INCLUDES_PATH . 'number_plates/number_plates.php';
}