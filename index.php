<?php
/**
 * Plugin Name: Feedback Plugin
 * Description: A plugin for collecting user feedback.
 * Version: 1.0
 * Author: Soroush Paknezhad
 */

if (!defined('ABSPATH')) {
    exit; 
}


require_once plugin_dir_path(__FILE__) . 'includes/class-database-setup.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-feedback-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-feedback-admin.php'; 
require_once plugin_dir_path(__FILE__) . 'includes/class-init-rest-api.php'; 
require_once plugin_dir_path(__FILE__) . 'includes/class-role-manager.php'; 


register_activation_hook(__FILE__, ['Database_Setup', 'create_feedback_table']);


Feedback_Form::init();


Feedback_Admin::init(); 


add_action('wp_enqueue_scripts', 'mfp_enqueue_assets');
function mfp_enqueue_assets() {
    wp_enqueue_style('mfp-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('mfp-script', plugin_dir_url(__FILE__) . 'assets/script.js', [], null, true);
    wp_localize_script('mfp-script', 'mfp_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
}


// Initial AJAX for admin
add_action('admin_post_bulk_delete', [Feedback_Admin::class, 'handle_bulk_delete']);
add_action('wp_ajax_fetch_chart_data', ['Feedback_Admin', 'handle_chart_data']);




