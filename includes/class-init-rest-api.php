<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('FEEDBACK_API_KEY', 'sf_GyhwFYweoxgVEXLxF0IbCSyzCWyfp2qU896vpN27NtxFejSZxkV7UhiNT9vQgwwDTyv5tRCiQSyi1QLSu9vhOA39VtkfyIjlbwHqNuJAc2IUxb906ckwjzWeeUuaS');

add_action('rest_api_init', 'feedback_api_endpoints');


function feedback_api_endpoints() {
    // Register GET route
    register_rest_route('feedback/v1', '/feedbacks', [
        'methods' => 'GET',
        'callback' => 'feedback_get_feedbacks',
        'permission_callback' => 'feedback_verify_api_key',
    ]);

    // Register DELETE route
    register_rest_route('feedback/v1', '/feedbacks/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'feedback_delete_feedback',
        'permission_callback' => 'feedback_verify_api_key',
    ]);
}

// Verify API Key for all routes
function feedback_verify_api_key(WP_REST_Request $request) {
    $api_key = $request->get_header('api-key'); // Get API Key from request header

    if ($api_key && $api_key === FEEDBACK_API_KEY) {
        return true;
    }

    return new WP_Error(
        'unauthorized',
        'Invalid API Key.',
        ['status' => 401]
    );
}


// Callback function for GET request - Fetch feedbacks
function feedback_get_feedbacks(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';

    // Pagination logic
    $page = $request->get_param('page') ?: 1;
    $per_page = $request->get_param('per_page') ?: 10;
    $offset = ($page - 1) * $per_page;

    // Optional search query
    $search = sanitize_text_field($request->get_param('search'));
    $where = '';
    if ($search) {
        $where = $wpdb->prepare("WHERE name LIKE %s OR email LIKE %s", "%$search%", "%$search%");
    }

    // Query the database for feedbacks
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT %d, %d",
            $offset,
            $per_page
        )
    );

    // Return results as a JSON response
    return new WP_REST_Response($results, 200);
}

// Callback function for DELETE request - Delete a feedback
function feedback_delete_feedback(WP_REST_Request $request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';
    $feedback_id = (int) $request->get_param('id');

    // Check if feedback exists
    $feedback = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $feedback_id));

    if (!$feedback) {
        return new WP_REST_Response('Feedback not found.', 404); // Return 404 if feedback doesn't exist
    }

    // Perform the deletion
    $deleted = $wpdb->delete($table_name, ['id' => $feedback_id]);

    if ($deleted) {
        return new WP_REST_Response('Feedback deleted successfully.', 200);
    } else {
        return new WP_REST_Response('Error deleting feedback.', 500);
    }
}
